<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

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

class Hook_Profiles_Tabs_blog
{

	/**
	 * Find whether this hook is active.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @return boolean		Whether this hook is active
	 */
	function is_active($member_id_of,$member_id_viewing)
	{
		return has_specific_permission($member_id_of,'have_personal_category','cms_news');
	}

	/**
	 * Standard modular render function for profile tab hooks.
	 *
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 * @param  boolean		Whether to leave the tab contents NULL, if tis hook supports it, so that AJAX can load it later
	 * @return array			A triple: The tab title, the tab contents, the suggested tab order
	 */
	function render_tab($member_id_of,$member_id_viewing,$leave_to_ajax_if_possible=false)
	{
		require_lang('news');

		$title=do_lang_tempcode('BLOG');

		$order=50;

		if ($leave_to_ajax_if_possible) return array($title,NULL,$order);

		require_css('news');

		$max_rows=0;

		$max=get_param_integer('blogs_max',5);
		$start=get_param_integer('blogs_start',0);

		// Show recent blog posts
		$recent_blog_posts=new ocp_tempcode();
		$rss_url=new ocp_tempcode();
		$news_cat=$GLOBALS['SITE_DB']->query_select('news_categories',array('*'),array('nc_owner'=>$member_id_of),'',1);
		if ((array_key_exists(0,$news_cat)) && (has_category_access($member_id_viewing,'news',strval($news_cat[0]['id']))))
		{
			$rss_url=make_string_tempcode(find_script('backend').'?type=rss2&mode=news&filter='.strval($news_cat[0]['id']));

			// How many results? (not 100% accurate, if a news item is in a primary cat and same secondary cat)
			$max_rows+=$GLOBALS['SITE_DB']->query_value('news','COUNT(*)',array('news_category'=>$news_cat[0]['id']));
			$max_rows+=$GLOBALS['SITE_DB']->query_value('news n LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'news_category_entries c ON n.id=c.news_entry','COUNT(*)',array('news_category'=>$news_cat[0]['id']));

			// Fetch and sort
			$news1=$GLOBALS['SITE_DB']->query_select('news',array('*'),array('news_category'=>$news_cat[0]['id']),'ORDER BY date_and_time DESC',$max+$start);
			$news2=$GLOBALS['SITE_DB']->query_select('news n LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'news_category_entries c ON n.id=c.news_entry',array('n.*'),array('news_category'=>$news_cat[0]['id']),'ORDER BY date_and_time DESC',$max+$start);
			$news=array();
			foreach ($news1 as $row) $news[$row['id']]=$row;
			foreach ($news2 as $row) $news[$row['id']]=$row;
			unset($news1);
			unset($news2);
			global $M_SORT_KEY;
			$M_SORT_KEY='date_and_time';
			usort($news,'multi_sort');
			$news=array_reverse($news);

			// Output
			$done=0;
			foreach ($news as $i=>$myrow)
			{
				if ($i<$start) continue;
				if ($done==$max) break;

				$news_id=$myrow['id'];
				$news_date=get_timezoned_date($myrow['date_and_time']);
				$author_url='';
				$author=$myrow['author'];
				$news_title=get_translated_tempcode($myrow['title']);
				$news_summary=get_translated_tempcode($myrow['news']);
				if ($news_summary->is_empty())
				{
					$news_summary=get_translated_tempcode($myrow['news_article']);
					$truncate=true;
				} else $truncate=false;
				$news_full_url=build_url(array('page'=>'news','type'=>'view','id'=>$news_id,'filter'=>$news_cat[0]['id'],'blog'=>1),get_module_zone('news'));
				$news_img=($news_cat[0]['nc_img']=='')?'':find_theme_image($news_cat[0]['nc_img']);
				if (is_null($news_img)) $news_img='';
				if ($myrow['news_image']!='')
				{
					$news_img=$myrow['news_image'];
					if (url_is_local($news_img)) $news_img=get_base_url().'/'.$news_img;
				}
				$news_category=get_translated_text($news_cat[0]['nc_title']);
				$seo_bits=seo_meta_get_for('news',strval($news_id));
				$map2=array('TAGS'=>get_loaded_tags('news',explode(',',$seo_bits[0])),'TRUNCATE'=>$truncate,'BLOG'=>false,'ID'=>strval($news_id),'SUBMITTER'=>strval($myrow['submitter']),'CATEGORY'=>$news_category,'IMG'=>$news_img,'DATE'=>$news_date,'DATE_RAW'=>strval($myrow['date_and_time']),'NEWS_TITLE'=>$news_title,'AUTHOR'=>$author,'AUTHOR_URL'=>$author_url,'NEWS'=>$news_summary,'FULL_URL'=>$news_full_url);
				if ((get_option('is_on_comments')=='1') && (!has_no_forum()) && ($myrow['allow_comments']>=1)) $map2['COMMENT_COUNT']='1';
				$recent_blog_posts->attach(do_template('NEWS_BOX',$map2));

				$done++;
			}
		}

		// Add link
		if ($member_id_of==$member_id_viewing)
			$add_blog_post_url=build_url(array('page'=>'cms_blogs','type'=>'ad'),get_module_zone('cms_blogs'));
		else
			$add_blog_post_url=new ocp_tempcode();

		// Pagination
		require_code('templates_pagination');
		$pagination=pagination(do_lang_tempcode('BLOGS_POSTS'),NULL,$start,'blogs_start',$max,'blogs_max',$max_rows,NULL,'view',true,false,7,NULL,'tab__blog');

		// Wrap it all up
		$content=do_template('OCF_MEMBER_PROFILE_BLOG',array('_GUID'=>'f76244bc259c3e7da8c98b28fff85953','PAGINATION'=>$pagination,'RSS_URL'=>$rss_url,'ADD_BLOG_POST_URL'=>$add_blog_post_url,'MEMBER_ID'=>strval($member_id_of),'RECENT_BLOG_POSTS'=>$recent_blog_posts));

		return array($title,$content,$order);
	}

}
