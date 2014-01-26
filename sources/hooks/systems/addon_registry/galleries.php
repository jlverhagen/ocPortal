<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

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

class Hook_addon_registry_galleries
{
	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'Galleries, including support for video galleries, and member personal galleries.';
	}

	/**
	 * Get a list of tutorials that apply to this addon
	 *
	 * @return array			List of tutorials
	 */
	function get_applicable_tutorials()
	{
		return array(
			'tut_galleries',
			'tut_adv_galleries',
		);
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array(),
		);
	}

	/**
	 * Explicitly say which icon should be used
	 *
	 * @return URLPATH		Icon
	 */
	function get_default_icon()
	{
		return 'themes/default/images/icons/48x48/menu/rich_content/galleries.png';
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(
			'themes/default/images/icons/24x24/menu/rich_content/galleries.png',
			'themes/default/images/icons/48x48/menu/rich_content/galleries.png',
			'themes/default/images/icons/24x24/menu/cms/galleries/add_one_image.png',
			'themes/default/images/icons/24x24/menu/cms/galleries/add_one_video.png',
			'themes/default/images/icons/24x24/menu/cms/galleries/edit_one_image.png',
			'themes/default/images/icons/24x24/menu/cms/galleries/edit_one_video.png',
			'themes/default/images/icons/48x48/menu/cms/galleries/add_one_image.png',
			'themes/default/images/icons/48x48/menu/cms/galleries/add_one_video.png',
			'themes/default/images/icons/48x48/menu/cms/galleries/edit_one_image.png',
			'themes/default/images/icons/48x48/menu/cms/galleries/edit_one_video.png',
			'themes/default/images/icons/24x24/menu/cms/galleries/index.html',
			'themes/default/images/icons/48x48/menu/cms/galleries/index.html',
			'themes/default/images/icons/24x24/buttons/slideshow.png',
			'themes/default/images/icons/48x48/buttons/slideshow.png',
			'data/zencoder_receive.php',
			'sources/hooks/systems/notifications/gallery_entry.php',
			'sources/hooks/systems/snippets/exists_gallery.php',
			'sources/hooks/modules/admin_setupwizard_installprofiles/portfolio.php',
			'sources/hooks/systems/config/audio_bitrate.php',
			'sources/hooks/systems/config/default_video_height.php',
			'sources/hooks/systems/config/default_video_width.php',
			'sources/hooks/systems/config/ffmpeg_path.php',
			'sources/hooks/systems/config/transcoding_zencoder_api_key.php',
			'sources/hooks/systems/config/transcoding_zencoder_ftp_path.php',
			'sources/hooks/systems/config/transcoding_server.php',
			'sources/hooks/systems/config/galleries_show_stats_count_galleries.php',
			'sources/hooks/systems/config/galleries_show_stats_count_images.php',
			'sources/hooks/systems/config/galleries_show_stats_count_videos.php',
			'sources/hooks/systems/config/gallery_name_order.php',
			'sources/hooks/systems/config/gallery_selectors.php',
			'sources/hooks/systems/config/max_personal_gallery_images_high.php',
			'sources/hooks/systems/config/max_personal_gallery_images_low.php',
			'sources/hooks/systems/config/max_personal_gallery_videos_high.php',
			'sources/hooks/systems/config/max_personal_gallery_videos_low.php',
			'sources/hooks/systems/config/maximum_image_size.php',
			'sources/hooks/systems/config/points_ADD_IMAGE.php',
			'sources/hooks/systems/config/points_ADD_VIDEO.php',
			'sources/hooks/systems/config/reverse_thumb_order.php',
			'sources/hooks/systems/config/show_empty_galleries.php',
			'sources/hooks/systems/config/show_gallery_counts.php',
			'sources/hooks/systems/config/video_bitrate.php',
			'sources/hooks/systems/config/video_height_setting.php',
			'sources/hooks/systems/config/video_width_setting.php',
			'sources/hooks/systems/content_meta_aware/image.php',
			'sources/hooks/systems/content_meta_aware/video.php',
			'sources/hooks/systems/content_meta_aware/gallery.php',
			'sources/hooks/systems/occle_fs/galleries.php',
			'sources/hooks/systems/meta/image.php',
			'sources/hooks/systems/meta/video.php',
			'sources/hooks/systems/meta/gallery.php',
			'sources/hooks/blocks/side_stats/stats_galleries.php',
			'sources/hooks/systems/addon_registry/galleries.php',
			'sources/hooks/modules/admin_import_types/galleries.php',
			'sources/hooks/systems/symbols/GALLERY_VIDEO_FOR_URL.php',
			'sources/hooks/systems/profiles_tabs/galleries.php',
			'sources/hooks/systems/sitemap/gallery.php',
			'sources/hooks/systems/sitemap/image.php',
			'sources/hooks/systems/sitemap/video.php',
			'themes/default/templates/GALLERY_POPULAR.tpl',
			'themes/default/templates/GALLERY_ENTRY_WRAP.tpl',
			'themes/default/templates/BLOCK_MAIN_GALLERY_EMBED.tpl',
			'themes/default/templates/GALLERY_BOX.tpl',
			'themes/default/templates/GALLERY_IMAGE_BOX.tpl',
			'themes/default/templates/GALLERY_VIDEO_BOX.tpl',
			'themes/default/templates/GALLERY_ENTRY_SCREEN.tpl',
			'themes/default/templates/GALLERY_FLOW_ENTRY.tpl',
			'themes/default/templates/GALLERY_FLOW_MODE_SCREEN.tpl',
			'themes/default/templates/GALLERY_ENTRY_LIST_LINE.tpl',
			'themes/default/templates/GALLERY_NAV.tpl',
			'themes/default/templates/GALLERY_IMAGE.tpl',
			'themes/default/templates/GALLERY_FLOW_MODE_IMAGE.tpl',
			'themes/default/templates/GALLERY_FLOW_MODE_VIDEO.tpl',
			'themes/default/templates/GALLERY_VIDEO.tpl',
			'themes/default/templates/GALLERY_VIDEO_INFO.tpl',
			'themes/default/templates/GALLERY_REGULAR_MODE_SCREEN.tpl',
			'themes/default/templates/BLOCK_SIDE_GALLERIES.tpl',
			'themes/default/templates/BLOCK_SIDE_GALLERIES_LINE.tpl',
			'themes/default/templates/BLOCK_SIDE_GALLERIES_LINE_CONTAINER.tpl',
			'themes/default/templates/BLOCK_SIDE_GALLERIES_LINE_DEPTH.tpl',
			'themes/default/templates/BLOCK_MAIN_IMAGE_FADER.tpl',
			'themes/default/templates/GALLERY_IMPORT_SCREEN.tpl',
			'uploads/galleries/index.html',
			'uploads/galleries_thumbs/index.html',
			'uploads/repimages/index.html',
			'uploads/watermarks/index.html',
			'themes/default/css/galleries.css',
			'cms/pages/modules/cms_galleries.php',
			'lang/EN/galleries.ini',
			'site/pages/modules/galleries.php',
			'sources/blocks/side_galleries.php',
			'uploads/galleries/pre_transcoding/index.html',
			'sources/transcoding.php',
			'sources/galleries.php',
			'sources/galleries2.php',
			'sources/galleries3.php',
			'sources/hooks/modules/admin_import/galleries.php',
			'sources/hooks/modules/admin_newsletter/galleries.php',
			'sources/hooks/modules/admin_setupwizard/galleries.php',
			'sources/hooks/modules/galleries_users/.htaccess',
			'sources/hooks/modules/galleries_users/index.html',
			'sources/hooks/modules/search/galleries.php',
			'sources/hooks/systems/page_groupings/galleries.php',
			'sources/hooks/systems/module_permissions/galleries.php',
			'sources/hooks/systems/rss/galleries.php',
			'sources/hooks/modules/admin_unvalidated/videos.php',
			'sources/hooks/modules/search/videos.php',
			'sources/hooks/systems/ajax_tree/choose_video.php',
			'sources/hooks/systems/preview/video.php',
			'sources/hooks/systems/trackback/videos.php',
			'sources/hooks/modules/admin_unvalidated/images.php',
			'sources/hooks/modules/search/images.php',
			'sources/hooks/systems/trackback/images.php',
			'sources/hooks/systems/ajax_tree/choose_gallery.php',
			'sources/hooks/systems/ajax_tree/choose_image.php',
			'site/download_gallery.php',
			'sources/hooks/systems/preview/image.php',
			'sources/blocks/main_gallery_embed.php',
			'sources/blocks/main_image_fader.php',
			'sources/blocks/main_personal_galleries_list.php',
			'themes/default/templates/BLOCK_MAIN_PERSONAL_GALLERIES_LIST.tpl',
			'uploads/galleries/.htaccess',
			'uploads/galleries_thumbs/.htaccess',
			'uploads/repimages/.htaccess',
			'uploads/watermarks/.htaccess',
			'themes/default/images/audio_thumb.png',
			'themes/default/images/video_thumb.png',
			'themes/default/templates/JAVASCRIPT_GALLERIES.tpl',
			'themes/default/templates/OCF_MEMBER_PROFILE_GALLERIES.tpl',
			'sources/hooks/systems/block_ui_renderers/galleries.php',
			'sources/hooks/systems/config/galleries_default_sort_order.php',
			'sources/hooks/systems/config/galleries_subcat_narrowin.php',
			'sources/hooks/systems/config/gallery_entries_flow_per_page.php',
			'sources/hooks/systems/config/gallery_entries_regular_per_page.php',
			'sources/hooks/systems/config/gallery_feedback_fields.php',
			'sources/hooks/systems/config/gallery_member_synced.php',
			'sources/hooks/systems/config/gallery_mode_is.php',
			'sources/hooks/systems/config/gallery_permissions.php',
			'sources/hooks/systems/config/gallery_rep_image.php',
			'sources/hooks/systems/config/gallery_watermarks.php',
			'sources/hooks/systems/config/subgallery_link_limit.php',
			'sources/hooks/systems/config/personal_under_members.php',
			'sources/hooks/systems/config/manual_gallery_codename.php',
			'sources/hooks/systems/config/manual_gallery_media_types.php',
			'sources/hooks/systems/config/manual_gallery_parent.php',
			'sources/hooks/systems/config/enable_ecards.php',
			'sources/hooks/systems/tasks/download_gallery.php',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array			The mapping
	*/
	function tpl_previews()
	{
		return array(
				'BLOCK_MAIN_IMAGE_FADER.tpl'=>'block_main_image_fader',
				'GALLERY_IMPORT_SCREEN.tpl'=>'administrative__gallery_import_screen',
				'GALLERY_POPULAR.tpl'=>'gallery_popular',
				'GALLERY_IMAGE.tpl'=>'gallery_image',
				'GALLERY_ENTRY_WRAP.tpl'=>'gallery_regular_mode_screen',
				'GALLERY_VIDEO.tpl'=>'gallery_regular_mode_screen',
				'BLOCK_MAIN_GALLERY_EMBED.tpl'=>'block_main_gallery_embed',
				'BLOCK_SIDE_GALLERIES_LINE_DEPTH.tpl'=>'block_side_galleries',
				'BLOCK_SIDE_GALLERIES_LINE.tpl'=>'block_side_galleries',
				'BLOCK_SIDE_GALLERIES_LINE_CONTAINER.tpl'=>'block_side_galleries',
				'BLOCK_SIDE_GALLERIES.tpl'=>'block_side_galleries',
				'GALLERY_VIDEO_INFO.tpl'=>'gallery_video_info',
				'GALLERY_BOX.tpl'=>'gallery_regular_mode_screen',
				'GALLERY_ENTRY_LIST_LINE.tpl'=>'gallery_entry_list_line',
				'GALLERY_FLOW_MODE_IMAGE.tpl'=>'gallery_flow_mode_image',
				'GALLERY_FLOW_MODE_VIDEO.tpl'=>'gallery_flow_mode_video',
				'GALLERY_FLOW_ENTRY.tpl'=>'gallery_flow_mode_image',
				'GALLERY_FLOW_MODE_SCREEN.tpl'=>'gallery_flow_mode_image',
				'GALLERY_REGULAR_MODE_SCREEN.tpl'=>'gallery_regular_mode_screen',
				'GALLERY_ENTRY_SCREEN.tpl'=>'gallery_entry_screen',
				'GALLERY_NAV.tpl'=>'gallery_entry_screen',
				'OCF_MEMBER_PROFILE_GALLERIES.tpl'=>'ocf_member_profile_galleries',
				'BLOCK_MAIN_PERSONAL_GALLERIES_LIST.tpl'=>'ocf_member_profile_galleries',
				'GALLERY_VIDEO_BOX.tpl'=>'gallery_video_box',
				'GALLERY_IMAGE_BOX.tpl'=>'gallery_image_box',
				);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_image_box()
	{
		$tab_content=do_lorem_template('GALLERY_IMAGE_BOX',array(
			'TITLE'=>lorem_phrase(),
			'THUMB'=>placeholder_image(),
			'BREADCRUMBS'=>lorem_phrase(),
			'ADD_DATE_RAW'=>placeholder_date_raw(),
			'ID'=>placeholder_id(),
			'NOTES'=>'',
			'GALLERY_TITLE'=>lorem_phrase(),
			'CAT'=>placeholder_id(),
			'VIEWS'=>placeholder_number(),
			'URL'=>placeholder_url(),
			'IMAGE_URL'=>placeholder_image_url(),
			'DESCRIPTION'=>lorem_paragraph(),
			'THUMB_URL'=>placeholder_image_url(),
			'GIVE_CONTEXT'=>true,
		));
		return array(
			lorem_globalise($tab_content,NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_video_box()
	{
		$tab_content=do_lorem_template('GALLERY_VIDEO_BOX',array(
			'TITLE'=>lorem_phrase(),
			'THUMB'=>placeholder_image(),
			'BREADCRUMBS'=>lorem_phrase(),
			'ADD_DATE_RAW'=>placeholder_date_raw(),
			'ID'=>placeholder_id(),
			'NOTES'=>'',
			'GALLERY_TITLE'=>lorem_phrase(),
			'CAT'=>placeholder_id(),
			'VIEWS'=>placeholder_number(),
			'URL'=>placeholder_url(),
			'VIDEO_URL'=>placeholder_url(),
			'DESCRIPTION'=>lorem_paragraph(),
			'THUMB_URL'=>placeholder_image_url(),
			'VIDEO_WIDTH'=>placeholder_number(),
			'VIDEO_HEIGHT'=>placeholder_number(),
			'VIDEO_LENGTH'=>placeholder_number(),
			'GIVE_CONTEXT'=>true,
		));
		return array(
			lorem_globalise($tab_content,NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__ocf_member_profile_galleries()
	{
		$galleries=do_lorem_template('BLOCK_MAIN_PERSONAL_GALLERIES_LIST',array(
			'GALLERIES'=>lorem_paragraph_html(),
			'PAGINATION'=>placeholder_pagination(),
			'BLOCK_PARAMS'=>'',

			'START'=>'0',
			'MAX'=>'10',
			'START_PARAM'=>'x_start',
			'MAX_PARAM'=>'x_max',
		));

		$tab_content=do_lorem_template('OCF_MEMBER_PROFILE_GALLERIES',array(
			'MEMBER_ID'=>placeholder_id(),
			'GALLERIES'=>$galleries,
			'ADD_GALLERY_URL'=>placeholder_url(),
			'ADD_IMAGE_URL'=>placeholder_url(),
			'ADD_VIDEO_URL'=>placeholder_url(),
		));
		return array(
			lorem_globalise($tab_content,NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_main_image_fader()
	{
		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_MAIN_IMAGE_FADER',array(
					'GALLERY_URL'=>placeholder_url(),
					'FIRST_URL'=>placeholder_image_url(),
					'PREVIOUS_URL'=>placeholder_image_url(),
					'NEXT_URL'=>placeholder_image_url(),
					'FIRST_URL_FULL'=>placeholder_image_url(),
					'PREVIOUS_URL_FULL'=>placeholder_image_url(),
					'NEXT_URL_FULL'=>placeholder_image_url(),
					'TITLES'=>array(),
					'IMAGES'=>array(),
					'IMAGES_FULL'=>array(),
					'HTML'=>array(),
					'MILL'=>'3000',
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__gallery_import_screen()
	{
		require_css('forms');

		//Need to create the form fields (instead of using placeholder_form()) because javascript is
		//using a field called 'files' (list type).
		require_lang('dearchive');
		$fields=new ocp_tempcode();
		$orphaned_content=new ocp_tempcode();
		$orphaned_content->attach(do_lorem_template('FORM_SCREEN_INPUT_LIST_ENTRY',array('SELECTED'=>false,'DISABLED'=>false,'CLASS'=>'','NAME'=>'test','TEXT'=>'test')));
		$input=do_lorem_template('FORM_SCREEN_INPUT_LIST',array('TABINDEX'=>placeholder_id(),'REQUIRED'=>'_required','NAME'=>'files','CONTENT'=>$orphaned_content,'INLINE_LIST'=>true));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'NAME'=>'files','PRETTY_NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_paragraph_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>new ocp_tempcode())));

		$form=do_lorem_template('FORM',array('TABINDEX'=>placeholder_number(),'FIELDS'=>$fields,'SUBMIT_ICON'=>'menu___generic_admin__import','SUBMIT_NAME'=>lorem_word(),'URL'=>placeholder_url(),'TEXT'=>lorem_phrase(),'HIDDEN'=>'','BATCH_IMPORT_ARCHIVE_CONTENTS'=>lorem_phrase()));

		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_IMPORT_SCREEN',array(
					'TITLE'=>lorem_title(),
					'FORM2'=>placeholder_form(),
					'FORM'=>$form,
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_popular()
	{
		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_POPULAR',array(
					'CHILDREN'=>lorem_sentence_html(),
					'CAT'=>'root',
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_image()
	{
		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_IMAGE',array(
					'VIEW_URL'=>placeholder_url(),
					'THUMB'=>placeholder_image(),
					'DESCRIPTION'=>lorem_phrase(),
					'ADD_DATE_RAW'=>placeholder_time(),
					'EDIT_DATE_RAW'=>placeholder_time(),
					'VIEWS'=>placeholder_number(),
					'SUBMITTER'=>placeholder_id(),
					'ID'=>placeholder_id(),
					'_EDIT_URL'=>placeholder_url(),
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_main_gallery_embed()
	{
		//Create the 'GALLERY_ENTRY_WRAP' template
		$entries=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$map=array('MEDIA_TYPE'=>'image','TITLE'=>lorem_phrase(),'DESCRIPTION'=>lorem_paragraph(),'TYPE'=>'image','ID'=>strval($k),'FILE_SIZE'=>lorem_word(),'SUBMITTER'=>lorem_word(),'FULL_URL'=>placeholder_url(),'THUMB_URL'=>placeholder_url(),'CAT'=>lorem_word(),'THUMB'=>placeholder_image(),'VIEW_URL'=>placeholder_url(),'ADD_DATE_RAW'=>lorem_word(),'EDIT_DATE_RAW'=>placeholder_time(),'VIEWS'=>placeholder_id(),'_EDIT_URL'=>placeholder_url());
			$entry=do_lorem_template('GALLERY_IMAGE',$map);
			$entries->attach(do_lorem_template('GALLERY_ENTRY_WRAP',array('ENTRY'=>$entry)+$map));
		}

		//Create 'BLOCK_MAIN_GALLERY_EMBED' with 'GALLERY_ENTRY_WRAP' as sub-template
		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_MAIN_GALLERY_EMBED',array(
					'TITLE'=>lorem_phrase(),
					'CAT'=>placeholder_id(),
					'ENTRIES'=>$entries,
					'TOTAL_VIDEOS'=>placeholder_number(),
					'TOTAL_IMAGES'=>placeholder_number(),
					'TOTAL'=>lorem_phrase(),
					'PAGINATION'=>placeholder_pagination(),
					'BLOCK_PARAMS'=>'',

					'START'=>'0',
					'MAX'=>'10',
					'START_PARAM'=>'x_start',
					'MAX_PARAM'=>'x_max',
				)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with andplaceholder_date_raw() without blank data).
	*/
	function tpl_preview__block_side_galleries()
	{
		$content=new ocp_tempcode();
		foreach (placeholder_array() as $k=>$v)
		{
			$out=new ocp_tempcode();
			foreach (placeholder_array() as $_k=>$_v)
			{
				$out->attach(do_lorem_template('BLOCK_SIDE_GALLERIES_LINE_DEPTH',array('TITLE'=>lorem_word(),'URL'=>placeholder_url())));
			}
			$out->attach(do_lorem_template('BLOCK_SIDE_GALLERIES_LINE',array('TITLE'=>lorem_word(),'URL'=>placeholder_url())));

			$content->attach(do_lorem_template('BLOCK_SIDE_GALLERIES_LINE_CONTAINER',array('ID'=>placeholder_random(),'CAPTION'=>lorem_phrase(),'CONTENTS'=>$out)));
		}

		return array(
			lorem_globalise(
				do_lorem_template('BLOCK_SIDE_GALLERIES',array(
					'DEPTH'=>true,
					'CONTENT'=>$content,
				)
			),NULL,'',true),
		);
	}
	/**placeholder_date_raw()
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__gallery_video_info()
	{
		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_VIDEO_INFO',array(
					'HEIGHT'=>placeholder_number(),
					'WIDTH'=>placeholder_number(),
					'LENGTH'=>placeholder_number(),
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_entry_list_line()
	{
		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_ENTRY_LIST_LINE',array(
					'BREADCRUMBS'=>lorem_phrase(),
					'URL'=>placeholder_url(),
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_flow_mode_video()
	{
		$video=do_lorem_template('GALLERY_FLOW_MODE_VIDEO',array(
			'MAIN'=>lorem_phrase(),
			'DESCRIPTION'=>lorem_paragraph_html(),
			'FILE_SIZE'=>placeholder_filesize(),
			'CAT'=>placeholder_id(),
			'RATING_DETAILS'=>'',
			'THUMB_URL'=>placeholder_image_url(),
			'FULL_URL'=>placeholder_url(),
			'ID'=>placeholder_id(),
			'VIEWS'=>lorem_phrase(),
			'ADD_DATE_RAW'=>placeholder_date_raw(),
			'EDIT_DATE_RAW'=>placeholder_date_raw(),
			'SUBMITTER'=>lorem_word_html(),
			'VIDEO_PLAYER'=>placeholder_image(),
			'VIDEO_DETAILS'=>lorem_phrase(),
			'VIEW_URL'=>placeholder_url(),
			'EDIT_URL'=>placeholder_url(),
		));
		$tags=do_lorem_template('TAGS',array('TAG'=>lorem_word(),'TAGS'=>placeholder_array(),'LINK_FULLSCOPE'=>lorem_word(),'TYPE'=>NULL));

		$entries=new ocp_tempcode();
		foreach (placeholder_array(10) as $k=>$v)
		{
			$entries->attach(do_lorem_template('GALLERY_FLOW_ENTRY',array('DESCRIPTION'=>lorem_paragraph_html(),'_TITLE'=>lorem_title(),'ID'=>strval($k),'VIEWS'=>placeholder_number(),'ADD_DATE_RAW'=>placeholder_time(),'EDIT_DATE_RAW'=>placeholder_date_raw(),'SUBMITTER'=>lorem_word(),'CLASS'=>lorem_word(),'THUMB'=>placeholder_image(),'VIEW_URL'=>placeholder_url(),'VIEW_URL_2'=>placeholder_url(),'TYPE'=>lorem_word())));
		}

		$comment_details=do_lorem_template('COMMENTS_POSTING_FORM',array('JOIN_BITS'=>lorem_phrase_html(),'USE_CAPTCHA'=>false,'EMAIL_OPTIONAL'=>lorem_word(),'POST_WARNING'=>'','COMMENT_TEXT'=>'','GET_EMAIL'=>true,'GET_TITLE'=>true,'EM'=>placeholder_emoticon_chooser(),'DISPLAY'=>'block','COMMENT_URL'=>placeholder_url(),'TITLE'=>lorem_phrase(),'MAKE_POST'=>true,'CREATE_TICKET_MAKE_POST'=>true,'FIRST_POST_URL'=>'','FIRST_POST'=>''));

		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_FLOW_MODE_SCREEN',array(
					'_TITLE'=>lorem_phrase(),
					'VIEW_URL'=>placeholder_url(),
					'FULL_URL'=>placeholder_url(),
					'PROBE_TYPE'=>lorem_phrase(),
					'ENTRY_TITLE'=>lorem_phrase(),
					'ENTRY_DESCRIPTION'=>lorem_paragraph_html(),
					'ENTRY_SUBMITTER'=>placeholder_id(),
					'ENTRY_VIEWS'=>placeholder_number(),
					'ENTRY_ADD_DATE_RAW'=>placeholder_date_raw(),
					'ENTRY_EDIT_DATE_RAW'=>placeholder_date_raw(),
					'ENTRY_TRACKBACK_DETAILS'=>lorem_sentence_html(),
					'ENTRY_RATING_DETAILS'=>lorem_sentence_html(),
					'ENTRY_COMMENT_DETAILS'=>lorem_sentence_html(),
					'ENTRY_EDIT_URL'=>placeholder_url(),
					'WARNING_DETAILS'=>'',
					'TAGS'=>$tags,
					'RATING_DETAILS'=>lorem_sentence_html(),
					'COMMENT_DETAILS'=>$comment_details,
					'REP_IMAGE_URL'=>placeholder_image_url(),
					'TITLE'=>lorem_title(),
					'MEMBER_DETAILS'=>lorem_phrase(),
					'DESCRIPTION'=>lorem_paragraph_html(),
					'CHILDREN'=>lorem_paragraph_html(),
					'CURRENT_ENTRY'=>$video,
					'ENTRIES'=>$entries,
					'EDIT_URL'=>placeholder_url(),
					'ADD_GALLERY_URL'=>placeholder_url(),
					'IMAGE_URL'=>placeholder_image_url(),
					'MEMBER_ID'=>placeholder_id(),
					'VIDEO_URL'=>placeholder_url(),
					'MAY_DOWNLOAD'=>lorem_phrase(),
					'CAT'=>placeholder_id(),
					'FIRST_ENTRY_ID'=>placeholder_id(),
					'SORTING'=>lorem_phrase(),
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_flow_mode_image()
	{
		$image=do_lorem_template('GALLERY_FLOW_MODE_IMAGE',array(
			'MAIN'=>lorem_phrase(),
			'DESCRIPTION'=>lorem_paragraph_html(),
			'FILE_SIZE'=>placeholder_filesize(),
			'RATING_DETAILS'=>'',
			'CAT'=>placeholder_id(),
			'THUMB_URL'=>placeholder_image_url(),
			'FULL_URL'=>placeholder_url(),
			'ID'=>placeholder_id(),
			'VIEWS'=>lorem_phrase(),
			'ADD_DATE_RAW'=>placeholder_date_raw(),
			'EDIT_DATE_RAW'=>placeholder_date_raw(),
			'SUBMITTER'=>lorem_word_html(),
			'THUMB'=>placeholder_url(),
			'VIEW_URL'=>placeholder_url(),
			'EDIT_URL'=>placeholder_url(),
		));
		$tags=do_lorem_template('TAGS',array('TAG'=>lorem_word(),'TAGS'=>placeholder_array(),'LINK_FULLSCOPE'=>lorem_word(),'TYPE'=>NULL));

		$entries=new ocp_tempcode();
		foreach (placeholder_array(10) as $k=>$v)
		{
			$entries->attach(do_lorem_template('GALLERY_FLOW_ENTRY',array('ID'=>strval($k),'VIEWS'=>placeholder_number(),'ADD_DATE_RAW'=>placeholder_time(),'EDIT_DATE_RAW'=>placeholder_date_raw(),'SUBMITTER'=>lorem_word(),'CLASS'=>lorem_word(),'THUMB'=>placeholder_image(),'VIEW_URL'=>placeholder_url(),'VIEW_URL_2'=>placeholder_url(),'TYPE'=>lorem_word(),'_EDIT_URL'=>placeholder_url())));
		}

		$comment_details=do_lorem_template('COMMENTS_POSTING_FORM',array('JOIN_BITS'=>lorem_phrase_html(),'USE_CAPTCHA'=>false,'EMAIL_OPTIONAL'=>lorem_word(),'POST_WARNING'=>'','COMMENT_TEXT'=>'','GET_EMAIL'=>true,'GET_TITLE'=>true,'EM'=>placeholder_emoticon_chooser(),'DISPLAY'=>'block','COMMENT_URL'=>placeholder_url(),'TITLE'=>lorem_phrase(),'MAKE_POST'=>true,'CREATE_TICKET_MAKE_POST'=>true,'FIRST_POST_URL'=>'','FIRST_POST'=>''));

		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_FLOW_MODE_SCREEN',array(
					'_TITLE'=>lorem_phrase(),
					'VIEW_URL'=>placeholder_url(),
					'FULL_URL'=>placeholder_url(),
					'PROBE_TYPE'=>lorem_phrase(),
					'ENTRY_VIEWS'=>placeholder_number(),
					'ENTRY_ADD_DATE_RAW'=>placeholder_date_raw(),
					'ENTRY_EDIT_DATE_RAW'=>placeholder_date_raw(),
					'ENTRY_TRACKBACK_DETAILS'=>lorem_sentence_html(),
					'ENTRY_RATING_DETAILS'=>lorem_sentence_html(),
					'ENTRY_COMMENT_DETAILS'=>lorem_sentence_html(),
					'ENTRY_EDIT_URL'=>placeholder_url(),
					'WARNING_DETAILS'=>'',
					'TAGS'=>$tags,
					'RATING_DETAILS'=>lorem_sentence_html(),
					'COMMENT_DETAILS'=>$comment_details,
					'REP_IMAGE_URL'=>placeholder_image_url(),
					'TITLE'=>lorem_title(),
					'MEMBER_DETAILS'=>lorem_paragraph_html(),
					'DESCRIPTION'=>lorem_paragraph_html(),
					'CHILDREN'=>lorem_paragraph_html(),
					'CURRENT_ENTRY'=>$image,
					'ENTRIES'=>$entries,
					'EDIT_URL'=>placeholder_url(),
					'ADD_GALLERY_URL'=>placeholder_url(),
					'IMAGE_URL'=>placeholder_image_url(),
					'MEMBER_ID'=>placeholder_id(),
					'VIDEO_URL'=>placeholder_url(),
					'MAY_DOWNLOAD'=>lorem_phrase(),
					'CAT'=>placeholder_id(),
					'FIRST_ENTRY_ID'=>placeholder_id(),
					'SORTING'=>lorem_phrase(),
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_regular_mode_screen()
	{
		$tags=do_lorem_template('TAGS',array('LINK_FULLSCOPE'=>lorem_word(),'TAG'=>lorem_word(),'TAGS'=>placeholder_array(),'TYPE'=>NULL));

		$entry=new ocp_tempcode();
		$map=array('MEDIA_TYPE'=>'image','TITLE'=>lorem_phrase(),'DESCRIPTION'=>lorem_paragraph(),'TYPE'=>'image','ID'=>placeholder_id(),'FILE_SIZE'=>lorem_word(),'SUBMITTER'=>lorem_word(),'FULL_URL'=>placeholder_url(),'THUMB_URL'=>placeholder_url(),'CAT'=>lorem_word(),'THUMB'=>placeholder_image(),'VIEW_URL'=>placeholder_url(),'EDIT_DATE_RAW'=>placeholder_time(),'ADD_DATE_RAW'=>placeholder_time(),'VIEWS'=>placeholder_number(),'_EDIT_URL'=>placeholder_url());
		$entry=do_lorem_template('GALLERY_IMAGE',$map);
		$entries=new ocp_tempcode();
		$entries->attach(do_lorem_template('GALLERY_ENTRY_WRAP',array('ENTRY'=>$entry)+$map));

		$video_details=do_lorem_template('GALLERY_VIDEO_INFO',array('HEIGHT'=>placeholder_number(),'WIDTH'=>placeholder_number(),'LENGTH'=>placeholder_number()));
		$map=array('MEDIA_TYPE'=>'video','TITLE'=>lorem_phrase(),'VIDEO_DETAILS'=>$video_details,'DESCRIPTION'=>lorem_phrase(),'ADD_DATE_RAW'=>placeholder_time(),'EDIT_DATE_RAW'=>placeholder_time(),'VIEWS'=>placeholder_number(),'VIEW_URL'=>placeholder_url(),'SUBMITTER'=>placeholder_id(),'ID'=>placeholder_id(),'THUMB'=>placeholder_image(),'_EDIT_URL'=>placeholder_url());
		$entry=do_lorem_template('GALLERY_VIDEO',$map);
		$entries->attach(do_lorem_template('GALLERY_ENTRY_WRAP',array('ENTRY'=>$entry)+$map));

		$children=do_lorem_template('GALLERY_BOX',array('GIVE_CONTEXT'=>false,'THUMB'=>'','NUM_VIDEOS'=>lorem_word(),'NUM_IMAGES'=>lorem_word(),'NUM_CHILDREN'=>lorem_word(),'ID'=>lorem_word(),'LANG'=>lorem_word(),'ADD_DATE_RAW'=>placeholder_date_raw(),'ADD_DATE'=>lorem_word(),'MEMBER_INFO'=>lorem_paragraph(),'URL'=>placeholder_url(),'PIC'=>placeholder_image_url(),'TITLE'=>lorem_phrase(),'DESCRIPTION'=>lorem_paragraph()));

		$comment_details=do_lorem_template('COMMENTS_POSTING_FORM',array('JOIN_BITS'=>lorem_phrase_html(),'USE_CAPTCHA'=>false,'EMAIL_OPTIONAL'=>lorem_word(),'POST_WARNING'=>'','COMMENT_TEXT'=>'','GET_EMAIL'=>true,'GET_TITLE'=>true,'EM'=>placeholder_emoticon_chooser(),'DISPLAY'=>'block','COMMENT_URL'=>placeholder_url(),'TITLE'=>lorem_phrase(),'MAKE_POST'=>true,'CREATE_TICKET_MAKE_POST'=>true,'FIRST_POST_URL'=>'','FIRST_POST'=>''));

		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_REGULAR_MODE_SCREEN',array(
					'_TITLE'=>lorem_phrase(),
					'TAGS'=>$tags,
					'CAT'=>lorem_word(),
					'MEMBER_DETAILS'=>lorem_sentence_html(),
					'RATING_DETAILS'=>lorem_sentence_html(),
					'COMMENT_DETAILS'=>$comment_details,
					'ADD_GALLERY_URL'=>placeholder_url(),
					'EDIT_URL'=>placeholder_url(),
					'CHILDREN'=>$children,
					'TITLE'=>lorem_title(),
					'DESCRIPTION'=>lorem_paragraph_html(),
					'IMAGE_URL'=>placeholder_url(),
					'VIDEO_URL'=>placeholder_url(),
					'MAY_DOWNLOAD'=>lorem_phrase(),
					'ENTRIES'=>$entries,
					'SORTING'=>lorem_phrase(),
				)
			),NULL,'',true),
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__gallery_entry_screen()
	{
		$nav=do_lorem_template('GALLERY_NAV',array('BACK_URL'=>placeholder_url(),'SLIDESHOW'=>false,'_X'=>placeholder_number(),'_N'=>placeholder_number(),'X'=>placeholder_number(),'N'=>placeholder_number(),'SLIDESHOW_URL'=>placeholder_url(),'SLIDESHOW_NEXT_URL'=>placeholder_url(),'PREVIOUS_URL'=>placeholder_url(),'NEXT_URL'=>placeholder_url(),'MORE_URL'=>placeholder_url(),'CATEGORY_NAME'=>lorem_word()));

		$comment_details=do_lorem_template('COMMENTS_POSTING_FORM',array('JOIN_BITS'=>lorem_phrase_html(),'USE_CAPTCHA'=>false,'EMAIL_OPTIONAL'=>lorem_word(),'POST_WARNING'=>'','COMMENT_TEXT'=>'','GET_EMAIL'=>true,'GET_TITLE'=>true,'EM'=>placeholder_emoticon_chooser(),'DISPLAY'=>'block','COMMENT_URL'=>placeholder_url(),'TITLE'=>lorem_phrase(),'MAKE_POST'=>true,'CREATE_TICKET_MAKE_POST'=>true,'FIRST_POST_URL'=>'','FIRST_POST'=>''));

		$video=lorem_sentence_html();

		return array(
			lorem_globalise(
				do_lorem_template('GALLERY_ENTRY_SCREEN',array(
					'CAT'=>placeholder_id(),
					'MEDIA_TYPE'=>'video',
					'ID'=>placeholder_id(),
					'SLIDESHOW'=>false,
					'TRUE_GALLERY_TITLE'=>lorem_phrase(),
					'E_TITLE'=>lorem_phrase(),
					'GALLERY_TITLE'=>lorem_phrase(),
					'MEMBER_ID'=>placeholder_id(),
					'TAGS'=>lorem_word_html(),
					'TITLE'=>lorem_title(),
					'SUBMITTER'=>lorem_word_html(),
					'URL'=>placeholder_url(),
					'VIDEO_DETAILS'=>lorem_sentence_html(),
					'MEMBER_DETAILS'=>lorem_sentence_html(),
					'X'=>lorem_phrase(),
					'N'=>lorem_phrase(),
					'VIEWS'=>lorem_phrase(),
					'ADD_DATE_RAW'=>placeholder_date_raw(),
					'EDIT_DATE_RAW'=>placeholder_date_raw(),
					'ADD_DATE'=>placeholder_date(),
					'EDIT_DATE'=>placeholder_date(),
					'RATING_DETAILS'=>lorem_sentence_html(),
					'TRACKBACK_DETAILS'=>lorem_sentence_html(),
					'COMMENT_DETAILS'=>$comment_details,
					'EDIT_URL'=>placeholder_url(),
					'THUMB_URL'=>placeholder_image_url(),
					'NAV'=>$nav,
					'DESCRIPTION'=>lorem_phrase(),
					'VIDEO'=>$video,
					'WARNING_DETAILS'=>'',
				)
			),NULL,'',true),
		);
	}
}
