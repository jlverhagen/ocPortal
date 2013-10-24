<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		news
 */

class Hook_task_import_rss
{
	/**
	 * Run the task hook.
	 *
	 * @param  BINARY			Whether to import as validated
	 * @param  BINARY			Whether to download remote images
	 * @param  BINARY			Whether to import everything to the task initiator's account
	 * @param  BINARY			Whether to import comments
	 * @param  BINARY			Whether to import everything to blog news categories
	 * @param  object			The parsed RSS feed
	 * @return ?array			A tuple of at least 2: Return mime-type, content (either Tempcode, or a string, or a filename and file-path pair to a temporary file), map of HTTP headers if transferring immediately, map of ini_set commands if transferring immediately (NULL: show standard success message)
	 */
	function run($is_validated,$download_images,$to_own_account,$import_blog_comments,$import_to_blog,$rss)
	{
		require_code('rss');
		require_code('files');
		require_lang('news');
		require_code('news');
		require_code('news2');

		$GLOBALS['LAX_COMCODE']=true;

		if (!is_null($rss->error))
		{
			return array(NULL,$rss->error);
		}

		$imported_news=array();
		$imported_pages=array();

		$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(false,true);

		// Preload news categories
		$NEWS_CATS=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('nc_owner'=>NULL));
		$NEWS_CATS=list_to_map('id',$NEWS_CATS);
		foreach ($rss->gleamed_items as $i=>$item)
		{
			// What is it, being imported?
			$is_page=false;
			$is_news=true;
			if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_TYPE']))
			{
				$is_page=($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_TYPE']=='page');
				$is_news=($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_TYPE']=='post');
			}
			if ((!$is_page) && (!$is_news)) continue;

			// Check for existing owner categories, if not create blog category for creator
			if (($to_own_account==0) && (array_key_exists('author',$item)))
			{
				$creator=$item['author'];
				$submitter_id=$GLOBALS['FORUM_DRIVER']->get_member_from_username($creator);
				if (is_null($submitter_id)) $submitter_id=get_member();
			} else
			{
				$submitter_id=get_member();
			}
			$author=array_key_exists('author',$item)?$item['author']:$GLOBALS['FORUM_DRIVER']->get_username(get_member());

			// Post name
			$post_name=$item['title'];
			if ((isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_NAME'])) && ($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_NAME']!=''))
			{
				$post_name=$item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_NAME'];
			}

			// Dates
			$add_date=array_key_exists('clean_add_date',$item)?$item['clean_add_date']:(array_key_exists('add_date',$item)?strtotime($item['add_date']):time());
			if ($add_date===false) $add_date=time(); // We've seen this situation in an error email, it's if the add date won't parse by PHP
			$edit_date=array_key_exists('clean_edit_date',$item)?$item['clean_edit_date']:(array_key_exists('edit_date',$item)?strtotime($item['edit_date']):NULL);
			if ($edit_date===false) $edit_date=NULL;
			if ($add_date>time()) $add_date=time();
			if ($add_date<0) $add_date=time();

			// Validation status
			$validated=$is_validated;
			if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:STATUS']))
			{
				if ($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:STATUS']=='publish')
					$validated=1;
				else
					$validated=0;
				if (!addon_installed('unvalidated')) $validated=1;
			}

			// Whether to allow comments
			$allow_comments=1;
			if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:COMMENT_STATUS']))
			{
				if ($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:COMMENT_STATUS']=='open')
					$allow_comments=1;
				else
					$allow_comments=0;
			}

			// Whether to allow trackbacks
			$allow_trackbacks=$is_news?1:0;
			if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:PING_STATUS']))
			{
				if ($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:PING_STATUS']!='open') $allow_trackbacks=0;
			}

			// Password
			$password='';
			if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_PASSWORD']))
			{
				$password=$item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_PASSWORD'];
			}

			// Categories
			if (!array_key_exists('category',$item)) $item['category']=do_lang('NC_general');
			$cats_to_process=array($item['category']);
			if (array_key_exists('extra_categories',$item))
				$cats_to_process=array_merge($cats_to_process,$item['extra_categories']);

			// Now import, whatever this is
			if ($is_news)
			{
				// Work out categories
				$owner_category_id=mixed();
				$cat_ids=array();
				foreach ($cats_to_process as $j=>$cat)
				{
					if ($cat=='Uncategorized') continue; // Skip blank category creation

					$cat_id=mixed();
					foreach ($NEWS_CATS as $_cat=>$news_cat)
					{				
						if (get_translated_text($news_cat['nc_title'])==$cat)
						{
							$cat_id=$_cat;					
						}
					}
					if (is_null($cat_id)) // Could not find existing category, create new
					{
						$cat_id=add_news_category($cat,'newscats/general','',NULL);
						foreach (array_keys($groups) as $group_id)
							$GLOBALS['SITE_DB']->query_insert('group_category_access',array('module_the_name'=>'news','category_name'=>strval($cat_id),'group_id'=>$group_id));
						// Need to reload now
						$NEWS_CATS=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('nc_owner'=>NULL));
						$NEWS_CATS=list_to_map('id',$NEWS_CATS);
					}

					if (($j==0) && ($import_to_blog==0))
					{
						$owner_category_id=$cat_id; // Primary
					} else
					{
						$cat_ids[]=$cat_id; // Secondary
					}
				}
				if (is_null($owner_category_id))
				{
					$owner_category_id=$GLOBALS['SITE_DB']->query_select_value_if_there('news_categories','id',array('nc_owner'=>$submitter_id));
				}

				// Work out rep-image
				if (!file_exists(get_custom_file_base().'/uploads/grepimages'))
				{
					require_code('files2');
					make_missing_directory(get_custom_file_base().'/uploads/grepimages');
				}
				$rep_image='';
				if (array_key_exists('rep_image',$item))
				{
					$rep_image=$item['rep_image'];
					if ($download_images==1)
					{
						$stem='uploads/grepimages/'.basename(urldecode($rep_image));
						$target_path=get_custom_file_base().'/'.$stem;
						$rep_image='uploads/grepimages/'.basename($rep_image);
						while (file_exists($target_path))
						{
							$uniqid=uniqid('',true);
							$stem='uploads/grepimages/'.$uniqid.'_'.basename(urldecode($rep_image));
							$target_path=get_custom_file_base().'/'.$stem;
							$rep_image='uploads/grepimages/'.$uniqid.'_'.basename($rep_image);
						}
						$target_handle=fopen($target_path,'wb') OR intelligent_write_error($target_path);
						$result=http_download_file($item['rep_image'],NULL,false,false,'ocPortal',NULL,NULL,NULL,NULL,NULL,$target_handle);
						fclose($target_handle);
						sync_file($target_path);
						fix_permissions($target_path);
					}
				}

				// Content
				$news=array_key_exists('news',$item)?import_foreign_news_html($item['news']):'';
				$news_article=array_key_exists('news_article',$item)?import_foreign_news_html($item['news_article']):'';
				if ($password!='')
				{
					$news_article='[highlight]'.do_lang('POST_ACCESS_IS_RESTRICTED').'[/highlight]'."\n\n".'[if_in_group="Administrators"]'.$news_article.'[/if_in_group]';
				}

				// Add news
				$id=add_news(
					$item['title'],
					$news,
					$author,
					$validated,
					1,
					$allow_comments,
					$allow_trackbacks,
					'',
					$news_article,
					$owner_category_id,
					$cat_ids,
					$add_date,
					$submitter_id,
					0,
					$edit_date,
					NULL,
					$rep_image
				);
				require_code('seo2');
				seo_meta_set_for_explicit('news',strval($id),implode(',',$cats_to_process),$news);

				// Track import IDs
				$rss->gleamed_items[$i]['import_id']=$id;
				$rss->gleamed_items[$i]['import__news']=$news;
				$rss->gleamed_items[$i]['import__news_article']=$news_article;
				$imported_news[]=$rss->gleamed_items[$i];

				// Needed for adding comments/trackbacks
				$comment_identifier='news_'.strval($id);
				$content_url=build_url(array('page'=>'news','type'=>'view','id'=>$id),get_module_zone('news'),NULL,false,false,true);
				$content_title=$item['title'];
				$trackback_for_type='news';
				$trackback_id=$id;
			} else
			{
				// If we don't have permission to write comcode pages, skip the page
				if (!has_submit_permission('high',get_member(),get_ip_address(),NULL,NULL)) continue;

				// Save articles as new comcode pages
				$zone='site';
				$lang=fallback_lang();
				$file=preg_replace('#[^\w\-]#','_',$post_name); // Filter non alphanumeric charactors
				$fullpath=zone_black_magic_filterer(get_custom_file_base().'/'.$zone.'/pages/comcode_custom/'.$lang.'/'.$file.'.txt');

				// Content
				$_content="[title]".comcode_escape($item['title'])."[/title]\n\n";
				$_content.='[surround]'.import_foreign_news_html(array_key_exists('news_article',$item)?$item['news_article']:$item['news']).'[/surround]';
				$_content.="\n\n[block]main_comcode_page_children[/block]";
				if ($allow_comments==1)
				{
					$_content.="\n\n[block=\"main\"]main_comments[/block]";
				}
				if ($allow_trackbacks==1)
				{
					$_content.="\n\n[block id=\"0\"]main_trackback[/block]";
				}

				// Add to the database
				$GLOBALS['SITE_DB']->query_delete('comcode_pages',array(
					'the_zone'=>$zone,
					'the_page'=>$file,
				),'',1);
				$GLOBALS['SITE_DB']->query_insert('comcode_pages',array(
					'the_zone'=>$zone,
					'the_page'=>$file,
					'p_parent_page'=>'',
					'p_validated'=>$validated,
					'p_edit_date'=>$edit_date,
					'p_add_date'=>$add_date,
					'p_submitter'=>$submitter_id,
					'p_show_as_edit'=>0
				));

				// Save to disk
				if (!file_exists(dirname($fullpath)))
				{
					require_code('files2');
					make_missing_directory(dirname($fullpath));
				}
				$myfile=@fopen($fullpath,GOOGLE_APPENGINE?'wb':'wt');
				if ($myfile===false) intelligent_write_error($fullpath);
				if (fwrite($myfile,$_content)<strlen($_content))
				{
					return array(NULL,do_lang_tempcode('COULD_NOT_SAVE_FILE'));
				}
				fclose($myfile);
				sync_file($fullpath);

				// Meta
				require_code('seo2');
				seo_meta_set_for_explicit('comcode_page',$zone.':'.$file,implode(',',$cats_to_process),'');

				// Track import IDs etc
				$parent_page=mixed();
				if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_PARENT']))
				{
					$parent_page=intval($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_PARENT']);
					if ($parent_page==0) $parent_page=NULL;
				}
				$page_id=mixed();
				if (isset($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_ID']))
				{
					$page_id=intval($item['extra']['HTTP://WORDPRESS.ORG/EXPORT/1.2/:POST_ID']);
				}
				$imported_pages[]=array(
					'contents'=>$_content,
					'zone'=>$zone,
					'page'=>$file,
					'path'=>$fullpath,
					'parent_page'=>$parent_page,
					'id'=>$page_id,
				);

				// Restricted access
				if ($password!='')
				{
					$usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list();
					foreach (array_keys($usergroups) as $group_id)
					{
						$GLOBALS['SITE_DB']->query_delete('group_page_access',array('page_name'=>$file,'zone_name'=>$zone,'group_id'=>$group_id),'',1);
						$GLOBALS['SITE_DB']->query_insert('group_page_access',array('page_name'=>$file,'zone_name'=>$zone,'group_id'=>$group_id));
					}
				}

				// Needed for adding comments/trackbacks
				$comment_identifier=$file.'_main';
				$content_url=build_url(array('page'=>$file),$zone,NULL,false,false,true);
				$content_title=$item['title'];
				$trackback_for_type=$file;
				$trackback_id=0;
			}

			// Add comments
			if ($import_blog_comments==1)
			{
				if (array_key_exists('comments',$item))
				{
					$comment_mapping=array();
					foreach ($item['comments'] as $comment)
					{
						if (!array_key_exists('COMMENT_CONTENT',$comment)) continue;

						$comment_content=import_foreign_news_html($comment['COMMENT_CONTENT']);
						$comment_author=array_key_exists('COMMENT_AUTHOR',$comment)?$comment['COMMENT_AUTHOR']:do_lang('GUEST');
						$comment_parent=array_key_exists('COMMENT_PARENT',$comment)?$comment['COMMENT_PARENT']:'';
						$comment_date_gmt=array_key_exists('COMMENT_DATE_GMT',$comment)?$comment['COMMENT_DATE_GMT']:date('D/m/Y H:i:s',time());
						$author_ip=array_key_exists('COMMENT_AUTHOR_IP',$comment)?$comment['COMMENT_AUTHOR_IP']:get_ip_address();
						$comment_approved=array_key_exists('COMMENT_APPROVED',$comment)?$comment['COMMENT_APPROVED']:'1';
						$comment_id=array_key_exists('COMMENT_ID',$comment)?$comment['COMMENT_ID']:'';
						$comment_type=array_key_exists('COMMENT_TYPE',$comment)?$comment['COMMENT_TYPE']:'';

						$comment_author_url=array_key_exists('COMMENT_AUTHOR_URL',$comment)?$comment['COMMENT_AUTHOR_URL']:'';
						$comment_author_email=array_key_exists('COMMENT_AUTHOR_EMAIL',$comment)?$comment['COMMENT_AUTHOR_EMAIL']:'';

						$comment_add_date=strtotime($comment_date_gmt);
						if ($comment_add_date>time()) $comment_add_date=time();
						if ($comment_add_date<0) $comment_add_date=time();

						if (($comment_type=='trackback') || ($comment_type=='pingback'))
						{
							$GLOBALS['SITE_DB']->query_insert('trackbacks',array(
								'trackback_for_type'=>$trackback_for_type,
								'trackback_for_id'=>strval($trackback_id),
								'trackback_ip'=>$author_ip,
								'trackback_time'=>$comment_add_date,
								'trackback_url'=>$comment_author_url,
								'trackback_title'=>'',
								'trackback_excerpt'=>$comment_content,
								'trackback_name'=>$comment_author,
							));
							continue;
						}

						if ($comment_author_url!='')
							$comment_content.="\n\n".do_lang('WEBSITE').': [url]'.$comment_author_url.'[/url]';
						if ($comment_author_email!='')
							$comment_content.="[staff_note]\n\n".do_lang('EMAIL').': [email]'.$comment_author_email."[/email][/staff_note]";

						$submitter=$GLOBALS['FORUM_DB']->query_select_value_if_there('f_members','id',array('m_username'=>$comment_author));
						if (is_null($submitter)) $submitter=$GLOBALS['FORUM_DRIVER']->get_guest_id(); // If comment is made by a non-member, assign comment to guest account

						$forum=(is_null(find_overridden_comment_forum('news')))?get_option('comments_forum_name'):find_overridden_comment_forum('news');

						$comment_parent_id=mixed();
						if ((get_forum_type()=='ocf') && (!is_null($comment_parent)) && (isset($comment_mapping[$comment_parent])))
						{
							$comment_parent_id=$comment_mapping[$comment_parent];
						}
						if ($comment_parent_id==0) $comment_parent_id=NULL;

						$result=$GLOBALS['FORUM_DRIVER']->make_post_forum_topic(
							$forum,
							$comment_identifier,
							$submitter,
							'', // Would be post title
							$comment_content,
							$content_title,
							do_lang('COMMENT'),
							$content_url->evaluate(),
							$comment_add_date,
							$author_ip,
							intval($comment_approved),
							1,
							false,
							$comment_author,
							$comment_parent_id,
							false,
							NULL,
							NULL,
							time()
						);

						if (get_forum_type()=='ocf')
						{
							$comment_mapping[$comment_id]=$GLOBALS['LAST_POST_ID'];
						}
					}
				}
			}
		}

		// Download images etc
		foreach ($imported_news as $item)
		{
			$news=$item['import__news'];
			$news_article=$item['import__news_article'];
			_news_import_grab_images_and_fix_links($download_images==1,$news,$imported_news);
			_news_import_grab_images_and_fix_links($download_images==1,$news_article,$imported_news);
			lang_remap_comcode($GLOBALS['SITE_DB']->query_select_value('news','news',array('id'=>$item['import_id'])),$news);
			lang_remap_comcode($GLOBALS['SITE_DB']->query_select_value('news','news_article',array('id'=>$item['import_id'])),$news_article);
		}
		foreach ($imported_pages as $item)
		{
			$contents=$item['contents'];
			$zone=$item['zone'];
			$page=$item['page'];
			_news_import_grab_images_and_fix_links($download_images==1,$contents,$imported_news);
			$myfile=fopen($item['path'],'wb');
			fwrite($myfile,$contents);
			fclose($myfile);
			sync_file($item['path']);
			fix_permissions($item['path']);
			if (!is_null($item['parent_page']))
			{
				$parent_page=mixed();
				foreach ($imported_pages as $item2)
				{
					if ($item2['id']==$item['parent_page']) $parent_page=$item2['page'];
				}
				if (!is_null($parent_page))
				{
					$GLOBALS['SITE_DB']->query_update('comcode_pages',array('p_parent_page'=>$parent_page),array('the_zone'=>$zone,'the_page'=>$page),'',1);
				}
			}
		}

		$ret=do_lang_tempcode('IMPORT_NEWS_DONE');
		return array('text/html',$ret);
	}
}
