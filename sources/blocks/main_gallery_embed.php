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
 * @package		galleries
 */

class Block_main_gallery_embed
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		$info['parameters']=array('ocselect','param','select','video_select','zone','max','title','sort','days','render_if_empty');
		return $info;
	}

	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	function cacheing_environment()
	{
		$info=array();
		$info['cache_on']='array(array_key_exists(\'ocselect\',$map)?$map[\'ocselect\']:\'\',array_key_exists(\'render_if_empty\',$map)?$map[\'render_if_empty\']:\'0\',array_key_exists(\'days\',$map)?$map[\'days\']:\'\',array_key_exists(\'sort\',$map)?$map[\'sort\']:\'add_date DESC\',get_param_integer(\'mge_start\',0),$GLOBALS[\'FORUM_DRIVER\']->get_members_groups(get_member(),false,true),array_key_exists(\'param\',$map)?$map[\'param\']:db_get_first_id(),array_key_exists(\'zone\',$map)?$map[\'zone\']:\'\',((is_null($map)) || (!array_key_exists(\'select\',$map)))?\'*\':$map[\'select\'],((is_null($map)) || (!array_key_exists(\'video_select\',$map)))?\'*\':$map[\'video_select\'],get_param_integer(\'mge_max\',array_key_exists(\'max\',$map)?intval($map[\'max\']):30),array_key_exists(\'title\',$map)?$map[\'title\']:\'\')';
		$info['ttl']=60*2;
		return $info;
	}

	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		require_css('galleries');
		require_lang('galleries');
		require_code('galleries');
		require_code('images');
		require_code('feedback');

		require_code('ocfiltering');
		$cat=array_key_exists('param',$map)?$map['param']:'root';
		$cat_select=ocfilter_to_sqlfragment($cat,'cat','galleries','parent_id','cat','name',false,false);

		$ocselect=array_key_exists('ocselect',$map)?$map['ocselect']:'';

		$title=array_key_exists('title',$map)?$map['title']:'';

		/*if ((file_exists(get_custom_file_base().'/themes/default/templates_custom/JAVASCRIPT_GALLERIES.tpl')) || (file_exists(get_file_base().'/themes/default/templates/JAVASCRIPT_GALLERIES.tpl')))
			require_javascript('javascript_galleries');*/

		$max=get_param_integer('mge_max',array_key_exists('max',$map)?intval($map['max']):30);
		$start=get_param_integer('mge_start',0);

		if (!array_key_exists('select',$map)) $map['select']='*';
		$image_select=ocfilter_to_sqlfragment($map['select'],'id');

		if (!array_key_exists('video_select',$map)) $map['video_select']='*';
		$video_select=ocfilter_to_sqlfragment($map['video_select'],'id');

		$zone=array_key_exists('zone',$map)?$map['zone']:get_module_zone('galleries');

		$_days=array_key_exists('days',$map)?$map['days']:'';
		$days=mixed();
		$days=($_days=='')?NULL:intval($_days);
		$where_sup='';
		if (!is_null($days)) $where_sup.=' AND add_date>='.strval(time()-$days*60*60*24);

		$sort=array_key_exists('sort',$map)?$map['sort']:'add_date DESC';
		if (($sort!='random ASC') && ($sort!='fixed_random ASC') && ($sort!='compound_rating DESC') && ($sort!='compound_rating ASC') && ($sort!='add_date DESC') && ($sort!='add_date ASC') && ($sort!='url DESC') && ($sort!='url ASC')) $sort='add_date DESC';
		list($_sort,$_dir)=explode(' ',$sort,2);

		// ocSelect support
		if ($ocselect!='')
		{
			// Convert the filters to SQL
			require_code('ocselect');
			$content_type='image';
			if ($map['video_select']=='') $content_type='image';
			elseif ($map['select']=='') $content_type='video';

			list($extra_select,$extra_join,$extra_where)=ocselect_to_sql($GLOBALS['SITE_DB'],parse_ocselect($ocselect),$content_type,'');
			$extra_select_sql=implode('',$extra_select);
			$extra_join_sql=implode('',$extra_join);
			$where_sup.=$extra_where;
		} else
		{
			$extra_select_sql='';
			$extra_join_sql='';
		}

		$total_images=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT COUNT(*)'.$extra_select_sql.' FROM '.get_table_prefix().'images r'.$extra_join_sql.' WHERE '.$cat_select.' AND '.$image_select.$where_sup);
		$total_videos=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT COUNT(*)'.$extra_select_sql.' FROM '.get_table_prefix().'videos r'.$extra_join_sql.' WHERE '.$cat_select.' AND '.$video_select.$where_sup);

		if ($_sort=='random')
		{
			$start=0;
			$max=min($total_images+$total_videos,$max);
			$done_images='1=1';
			$done_videos='1=1';
			$rows_images=array();
			$rows_videos=array();
			for ($i=0;$i<$max;$i++)
			{
				if ((mt_rand(0,1)==0) || ($total_videos-count($rows_videos)==0))
				{
					$rows=$GLOBALS['SITE_DB']->query('SELECT *'.$extra_select_sql.' FROM '.get_table_prefix().'images r'.$extra_join_sql.' WHERE '.$cat_select.' AND '.$image_select.$where_sup.' AND '.$done_images,1,mt_rand(0,$total_images-count($rows_images)-1));
					$rows_images[]=$rows[0];
					$done_images.=' AND ';
					$done_images.='id<>'.strval($rows[0]['id']);
				} else
				{
					$rows=$GLOBALS['SITE_DB']->query('SELECT *'.$extra_select_sql.' FROM '.get_table_prefix().'videos r'.$extra_join_sql.' WHERE '.$cat_select.' AND '.$video_select.$where_sup.' AND '.$done_videos,1,mt_rand(0,$total_videos-count($rows_videos)-1));
					$rows_videos[]=$rows[0];
					$done_videos.=' AND ';
					$done_videos.='id<>'.strval($rows[0]['id']);
				}
			}
		} else
		{
			if ($_sort=='compound_rating')
			{
				$rating_sort=',(SELECT AVG(rating) FROM '.get_table_prefix().'rating WHERE '.db_string_equal_to('rating_for_type','images').' AND rating_for_id=r.id) AS compound_rating';
			} elseif ($_sort=='fixed_random')
			{
				$rating_sort=',(MOD(id,3.142)) AS fixed_random';
			} else
			{
				$rating_sort='';
			}
			$rows_images=$GLOBALS['SITE_DB']->query('SELECT *'.$rating_sort.$extra_select_sql.' FROM '.get_table_prefix().'images r'.$extra_join_sql.' WHERE '.$cat_select.' AND '.$image_select.$where_sup.' ORDER BY '.$sort,$max+$start);
			if ($_sort=='compound_rating')
			{
				$rating_sort=',(SELECT AVG(rating) FROM '.get_table_prefix().'rating WHERE '.db_string_equal_to('rating_for_type','videos').' AND rating_for_id=r.id) AS compound_rating';
			} elseif ($_sort=='fixed_random')
			{
				$rating_sort=',(MOD(id,3.142)) AS fixed_random';
			} else
			{
				$rating_sort='';
			}
			$rows_videos=$GLOBALS['SITE_DB']->query('SELECT *'.$rating_sort.$extra_select_sql.' FROM '.get_table_prefix().'videos r'.$extra_join_sql.' WHERE '.$cat_select.' AND '.$video_select.$where_sup.' ORDER BY '.$sort,$max+$start);
		}

		// Sort
		$combined=array();
		foreach ($rows_images as $row_image) $combined[]=array($row_image,'image',($_sort=='random')?NULL:$row_image[$_sort]);
		foreach ($rows_videos as $row_video) $combined[]=array($row_video,'video',($_sort=='random')?NULL:$row_video[$_sort]);
		if ($_sort=='random')
		{
			shuffle($combined);
		} else
		{
			global $M_SORT_KEY;
			$M_SORT_KEY=2;
			usort($combined,'multi_sort');
			if ($_dir=='DESC')
				$combined=array_reverse($combined);
		}

		// Display
		$entries=new ocp_tempcode();
		foreach ($combined as $i=>$c)
		{
			if ($i>=$start)
			{
				switch ($c[1])
				{
					case 'image':
						// Display image
						$row_image=$c[0];
						$view_url=build_url(array('page'=>'galleries','type'=>'image','wide'=>1,'id'=>$row_image['id']),$zone);
						$thumb_url=ensure_thumbnail($row_image['url'],$row_image['thumb_url'],'galleries','images',$row_image['id']);
						$thumb=do_image_thumb($thumb_url,'',true);
						$full_url=$row_image['url'];
						$file_size=url_is_local($full_url)?file_exists(get_custom_file_base().'/'.rawurldecode($full_url))?strval(filesize(get_custom_file_base().'/'.rawurldecode($full_url))):'':'';
						if (url_is_local($full_url)) $full_url=get_custom_base_url().'/'.$full_url;
						$thumb_url=$row_image['thumb_url'];
						if (url_is_local($thumb_url)) $thumb_url=get_custom_base_url().'/'.$thumb_url;

						$entry_rating_details=($row_image['allow_rating']==1)?display_rating($view_url,get_translated_text($row_image['title']),'images',strval($row_image['id']),'RATING_INLINE_STATIC',$row_image['submitter']):NULL;

						$entry_map=array('_GUID'=>'043ac7d15ce02715ac02309f6e8340ff','RATING_DETAILS'=>$entry_rating_details,'TITLE'=>get_translated_text($row_image['title']),'DESCRIPTION'=>get_translated_tempcode($row_image['comments']),'ID'=>strval($row_image['id']),'FILE_SIZE'=>$file_size,'SUBMITTER'=>strval($row_image['submitter']),'FULL_URL'=>$full_url,'THUMB_URL'=>$thumb_url,'CAT'=>$cat,'THUMB'=>$thumb,'VIEW_URL'=>$view_url,'VIEWS'=>strval($row_image['image_views']),'ADD_DATE_RAW'=>strval($row_image['add_date']),'EDIT_DATE_RAW'=>is_null($row_image['edit_date'])?'':strval($row_image['edit_date']));
						$entry=do_template('GALLERY_IMAGE',$entry_map);
						$entries->attach(do_template('GALLERY_ENTRY_WRAP',array('_GUID'=>'13134830e1ebea158ab44885eeec0953','ENTRY'=>$entry)+$entry_map));

						break;

					case 'video':
						// Display video
						$row_video=$c[0];
						$view_url=build_url(array('page'=>'galleries','type'=>'video','wide'=>1,'id'=>$row_video['id']),$zone);
						$thumb_url=$row_video['thumb_url'];
						if (($thumb_url!='') && (url_is_local($thumb_url))) $thumb_url=get_custom_base_url().'/'.$thumb_url;
						if ($thumb_url=='') $thumb_url=find_theme_image('na');
						$thumb=do_image_thumb($thumb_url,'',true);
						$full_url=$row_video['url'];
						if (url_is_local($full_url)) $full_url=get_custom_base_url().'/'.$full_url;
						$thumb_url=$row_video['thumb_url'];
						if (($thumb_url!='') && (url_is_local($thumb_url))) $thumb_url=get_custom_base_url().'/'.$thumb_url;

						$entry_rating_details=($row_video['allow_rating']==1)?display_rating($view_url,get_translated_text($row_video['title']),'videos',strval($row_video['id']),'RATING_INLINE_STATIC',$row_video['submitter']):NULL;

						$entry_map=array('_GUID'=>'66b7fb4d3b61ef79d6803c170d102cbf','RATING_DETAILS'=>$entry_rating_details,'TITLE'=>get_translated_text($row_video['title']),'DESCRIPTION'=>get_translated_tempcode($row_video['comments']),'ID'=>strval($row_video['id']),'CAT'=>$cat,'THUMB'=>$thumb,'VIEW_URL'=>$view_url,'SUBMITTER'=>strval($row_video['submitter']),'FULL_URL'=>$full_url,'THUMB_URL'=>$thumb_url,'VIDEO_DETAILS'=>show_video_details($row_video),'VIEWS'=>strval($row_video['video_views']),'ADD_DATE_RAW'=>strval($row_video['add_date']),'EDIT_DATE_RAW'=>is_null($row_video['edit_date'])?'':strval($row_video['edit_date']));
						$entry=do_template('GALLERY_VIDEO',$entry_map);
						$entries->attach(do_template('GALLERY_ENTRY_WRAP',array('_GUID'=>'a0ff010ae7fd1f7b3341993072ed23cf','ENTRY'=>$entry)+$entry_map));

						break;
				}
			}

			$i++;
			if ($i==$start+$max) break;
		}

		if ((!isset($map['render_if_empty'])) || ($map['render_if_empty']!='1'))
		{
			if ($entries->is_empty())
			{
				if ((has_actual_page_access(NULL,'cms_galleries',NULL,NULL)) && (has_submit_permission('mid',get_member(),get_ip_address(),'cms_galleries',array('galleries',$cat))) && (can_submit_to_gallery($cat)))
				{
					$submit_url=build_url(array('page'=>'cms_galleries','type'=>'ad','cat'=>$cat,'redirect'=>SELF_REDIRECT),get_module_zone('cms_galleries'));
				} else $submit_url=new ocp_tempcode();
				return do_template('BLOCK_NO_ENTRIES',array('_GUID'=>'bf84d65b8dd134ba6cd7b1b7bde99de2','HIGH'=>false,'TITLE'=>do_lang_tempcode('GALLERY'),'MESSAGE'=>do_lang_tempcode('NO_ENTRIES'),'ADD_NAME'=>do_lang_tempcode('ADD_IMAGE'),'SUBMIT_URL'=>$submit_url));
			}
		}

		// Results browser
		require_code('templates_pagination');
		$_selectors=array_map('intval',explode(',',get_option('gallery_selectors')));
		$root=get_param('root','root');
		$pagination=pagination(do_lang('ENTRY'),$cat,$start,'mge_start',$max,'mge_max',$total_videos+$total_images,$root,'misc',true,false,10,$_selectors);

		$tpl=do_template('BLOCK_MAIN_GALLERY_EMBED',array('_GUID'=>'b7b969c8fe8c398dd6e3af7ee06717ea','IMAGE_SELECT'=>$map['select'],'VIDEO_SELECT'=>$map['video_select'],'DAYS'=>$_days,'SORT'=>$sort,'BLOCK_PARAMS'=>block_params_arr_to_str($map),'PAGINATION'=>$pagination,'TITLE'=>$title,'CAT'=>$cat,'IMAGES'=>$entries,'MAX'=>strval($max),'ZONE'=>$zone,'TOTAL_VIDEOS'=>strval($total_videos),'TOTAL_IMAGES'=>strval($total_images),'TOTAL'=>strval($total_videos+$total_images)));
		return $tpl;
	}

}


