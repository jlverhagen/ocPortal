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

class Hook_Profiles_Tabs_galleries
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
		return has_specific_permission($member_id_of,'have_personal_category','cms_galleries') && !is_null($GLOBALS['SITE_DB']->query_value_null_ok('galleries','is_member_synched',array('is_member_synched'=>1)));
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
		require_lang('galleries');

		$title=do_lang_tempcode('GALLERIES');

		$order=30;

		if ($leave_to_ajax_if_possible) return array($title,NULL,$order);

		$galleries=new ocp_tempcode();
		require_code('galleries');
		require_css('galleries');
		$rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'galleries WHERE name LIKE \''.db_encode_like('member\_'.strval($member_id_of).'\_%').'\'');
		$actual_rows=array();
		foreach ($rows as $i=>$row)
		{
			$gallery_rendered=show_gallery_box($row,'root',false,get_module_zone('galleries'),$member_id_of!=$member_id_viewing/*Hide if empty and not your own*/);
			if (!$gallery_rendered->is_empty()) $actual_rows[]=$row;
			$galleries->attach(do_template('GALLERY_SUBGALLERY_WRAP',array('CONTENT'=>$gallery_rendered)));
			$this->attach_gallery_subgalleries($row['name'],$galleries,$member_id_of,$member_id_viewing);
		}

		$add_gallery_url=new ocp_tempcode();
		$add_image_url=new ocp_tempcode();
		$add_video_url=new ocp_tempcode();
		if ($member_id_of==$member_id_viewing)
		{
			if (count($rows)==0) // No gallery yet, so create via implication
			{
				$test=$GLOBALS['SITE_DB']->query_select('galleries',array('accept_images','accept_videos','name'),array('is_member_synched'=>1));
				if (array_key_exists(0,$test))
				{
					if ($test[0]['accept_images']==1)
					{
						$add_image_url=build_url(array('page'=>'cms_galleries','type'=>'ad','cat'=>'member_'.strval($member_id_of).'_'.$test[0]['name']),get_module_zone('cms_galleries'));
					}
					if ($test[0]['accept_videos']==1)
					{
						$add_video_url=build_url(array('page'=>'cms_galleries','type'=>'av','cat'=>'member_'.strval($member_id_of).'_'.$test[0]['name']),get_module_zone('cms_galleries'));
					}
				}
			} else // Or invite them to explicitly add a gallery (they can add images/videos from their existing gallery now)
			{
				if ((has_actual_page_access(NULL,'cms_galleries',NULL,NULL)) && (has_submit_permission('cat_mid',get_member(),get_ip_address(),'cms_galleries')))
				{
					$add_gallery_url=build_url(array('page'=>'cms_galleries','type'=>'ac','cat'=>$rows[0]['name']),get_module_zone('cms_galleries'));
				}
				if (count($actual_rows)==1)
				{
					if ($actual_rows[0]['accept_images']==1)
					{
						$add_image_url=build_url(array('page'=>'cms_galleries','type'=>'ad','cat'=>$actual_rows[0]['name']),get_module_zone('cms_galleries'));
					}
					if ($actual_rows[0]['accept_videos']==1)
					{
						$add_video_url=build_url(array('page'=>'cms_galleries','type'=>'av','cat'=>$actual_rows[0]['name']),get_module_zone('cms_galleries'));
					}
				}
			}
		}

		$content=do_template('OCF_MEMBER_PROFILE_GALLERIES',array(
			'MEMBER_ID'=>strval($member_id_of),
			'GALLERIES'=>$galleries,
			'ADD_GALLERY_URL'=>$add_gallery_url,
			'ADD_IMAGE_URL'=>$add_image_url,
			'ADD_VIDEO_URL'=>$add_video_url,
		));

		return array($title,$content,$order);
	}

	/**
	 * Show subgalleries belonging to member.
	 *
	 * @param  ID_TEXT		Gallery name
	 * @param  tempcode		The output goes in here (passed by reference)
	 * @param  MEMBER			The ID of the member who is being viewed
	 * @param  MEMBER			The ID of the member who is doing the viewing
	 */
	function attach_gallery_subgalleries($gallery_name,&$galleries,$member_id_of,$member_id_viewing)
	{
		$rows=$GLOBALS['SITE_DB']->query_select('galleries',array('*'),array('parent_id'=>$gallery_name),'ORDER BY add_date DESC');
		foreach ($rows as $i=>$row)
		{
			$galleries->attach(do_template('GALLERY_SUBGALLERY_WRAP',array('CONTENT'=>show_gallery_box($row,'root',false,get_module_zone('galleries'),$member_id_of!=$member_id_viewing/*Hide if empty and not your own*/))));
			$this->attach_gallery_subgalleries($row['name'],$galleries,$member_id_of,$member_id_viewing);
		}
	}

}


