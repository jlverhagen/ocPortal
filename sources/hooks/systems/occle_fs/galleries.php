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
 * @package		galleries
 */

require_code('content_fs');

class Hook_occle_fs_galleries extends content_fs_base
{
	var $folder_content_type='gallery';
	var $file_content_type=array('image','video');

	/**
	 * Standard modular introspection function.
	 *
	 * @return array			The properties available for the content type
	 */
	function _enumerate_folder_properties()
	{
		return array(
			'title'=>'SHORT_TRANS',
			'description'=>'LONG_TRANS',
			'notes'=>'LONG_TEXT',
			'accept_images'=>'BINARY',
			'accept_videos'=>'BINARY',
			'is_member_synched'=>'BINARY',
			'flow_mode_interface'=>'BINARY',
			'rep_image'=>'URLPATH',
			'watermark_top_left'=>'URLPATH',
			'watermark_top_right'=>'URLPATH',
			'watermark_bottom_left'=>'URLPATH',
			'watermark_bottom_right'=>'URLPATH',
			'allow_rating'=>'BINARY',
			'allow_comments'=>'SHORT_INTEGER',
			'add_date'=>'TIME',
			'owner'=>'member',
		);
	}

	/**
	 * Standard modular date fetch function for OcCLE-fs resource hooks. Defined when getting an edit date is not easy.
	 *
	 * @param  array			Content row (not full, but does contain the ID)
	 * @return ?TIME			The edit date or add date, whichever is higher (NULL: could not find one)
	 */
	function _get_folder_edit_date($row)
	{
		$query='SELECT MAX(date_and_time) FROM '.get_table_prefix().'adminlogs WHERE '.db_string_equal_to('param_a',$row['name']).' AND  ('.db_string_equal_to('the_type','ADD_GALLERY').' OR '.db_string_equal_to('the_type','EDIT_GALLERY').')';
		return $GLOBALS['SITE_DB']->query_value_if_there($query);
	}

	/**
	 * Standard modular add function for OcCLE-fs resource hooks. Adds some content with the given label and properties.
	 *
	 * @param  SHORT_TEXT	Filename OR Content label
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return ~ID_TEXT		The content ID (false: error)
	 */
	function _folder_add($filename,$path,$properties)
	{
		list($category_content_type,$category)=$this->folder_convert_filename_to_id($path);
		if ($category=='') return false; // Can't create more than one root

		list($properties,$label)=$this->_folder_magic_filter($filename,$path,$properties);

		require_code('galleries2');

		$name=$this->_create_name_from_label($label);
		$title=$this->_default_property_str($properties,'title');
		$description=$this->_default_property_str($properties,'description');
		$notes=$this->_default_property_str($properties,'notes');
		$parent_id=$category;
		$accept_images=$this->_default_property_int_modeavg($properties,'accept_images','galleries',1);
		$accept_videos=$this->_default_property_int_modeavg($properties,'accept_videos','galleries',1);
		$is_member_synched=$this->_default_property_int($properties,'is_member_synched');
		$flow_mode_interface=$this->_default_property_int($properties,'flow_mode_interface');
		$rep_image=$this->_default_property_str($properties,'rep_image');
		$watermark_top_left=$this->_default_property_str($properties,'watermark_top_left');
		$watermark_top_right=$this->_default_property_str($properties,'watermark_top_right');
		$watermark_bottom_left=$this->_default_property_str($properties,'watermark_bottom_left');
		$watermark_bottom_right=$this->_default_property_str($properties,'watermark_bottom_right');
		$allow_rating=$this->_default_property_int_modeavg($properties,'allow_rating','galleries',1);
		$allow_comments=$this->_default_property_int_modeavg($properties,'allow_comments','galleries',1);
		$add_date=$this->_default_property_int_null($properties,'add_date');
		$g_owner=$this->_default_property_int_null($properties,'owner');
		add_gallery($name,$title,$description,$notes,$parent_id,$accept_images,$accept_videos,$is_member_synched,$flow_mode_interface,$rep_image,$watermark_top_left,$watermark_top_right,$watermark_bottom_left,$watermark_bottom_right,$allow_rating,$allow_comments,false,$add_date,$g_owner);
		return $name;
	}

	/**
	 * Standard modular load function for OcCLE-fs resource hooks. Finds the properties for some content.
	 *
	 * @param  SHORT_TEXT	Filename
	 * @param  string			The path (blank: root / not applicable)
	 * @return ~array			Details of the content (false: error)
	 */
	function _folder_load($filename,$path)
	{
		list($content_type,$content_id)=$this->file_convert_filename_to_id($filename);

		$rows=$GLOBALS['SITE_DB']->query_select('galleries',array('*'),array('name'=>$content_id),'',1);
		if (!array_key_exists(0,$rows)) return false;
		$row=$rows[0];

		return array(
			'label'=>$row['name'],
			'title'=>$row['fullname'],
			'description'=>$row['description'],
			'notes'=>$row['notes'],
			'accept_images'=>$row['accept_images'],
			'accept_videos'=>$row['accept_videos'],
			'is_member_synched'=>$row['is_member_synched'],
			'flow_mode_interface'=>$row['flow_mode_interface'],
			'rep_image'=>$row['rep_image'],
			'watermark_top_left'=>$row['watermark_top_left'],
			'watermark_top_right'=>$row['watermark_top_right'],
			'watermark_bottom_left'=>$row['watermark_bottom_left'],
			'watermark_bottom_right'=>$row['watermark_bottom_right'],
			'allow_rating'=>$row['allow_rating'],
			'allow_comments'=>$row['allow_comments'],
			'add_date'=>$row['add_date'],
			'owner'=>$row['g_owner'],
		);
	}

	/**
	 * Standard modular edit function for OcCLE-fs resource hooks. Edits the content to the given properties.
	 *
	 * @param  ID_TEXT		The filename
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return boolean		Success status
	 */
	function folder_edit($filename,$path,$properties)
	{
		list($content_type,$content_id)=$this->file_convert_filename_to_id($filename);

		require_code('galleries2');

		$label=$this->_default_property_str($properties,'label');
		$name=$this->_create_name_from_label($label);
		$title=$this->_default_property_str($properties,'title');
		$description=$this->_default_property_str($properties,'description');
		$notes=$this->_default_property_str($properties,'notes');
		$parent_id=$category;
		$accept_images=$this->_default_property_int_modeavg($properties,'accept_images','galleries',1);
		$accept_videos=$this->_default_property_int_modeavg($properties,'accept_videos','galleries',1);
		$is_member_synched=$this->_default_property_int($properties,'is_member_synched');
		$flow_mode_interface=$this->_default_property_int($properties,'flow_mode_interface');
		$rep_image=$this->_default_property_str($properties,'rep_image');
		$watermark_top_left=$this->_default_property_str($properties,'watermark_top_left');
		$watermark_top_right=$this->_default_property_str($properties,'watermark_top_right');
		$watermark_bottom_left=$this->_default_property_str($properties,'watermark_bottom_left');
		$watermark_bottom_right=$this->_default_property_str($properties,'watermark_bottom_right');
		$allow_rating=$this->_default_property_int_modeavg($properties,'allow_rating','galleries',1);
		$allow_comments=$this->_default_property_int_modeavg($properties,'allow_comments','galleries',1);
		$add_date=$this->_default_property_int_null($properties,'add_date');
		$g_owner=$this->_default_property_int_null($properties,'owner');

		edit_gallery($content_id,$name,$title,$description,$notes,$parent_id,$accept_images,$accept_videos,$is_member_synched,$flow_mode_interface,$rep_image,$watermark_top_left,$watermark_top_right,$watermark_bottom_left,$watermark_bottom_right,$meta_keywords,$meta_description,$allow_rating,$allow_comments,$g_owner,$add_time,true);

		return true;
	}

	/**
	 * Standard modular delete function for OcCLE-fs resource hooks. Deletes the content.
	 *
	 * @param  ID_TEXT		The filename
	 * @return boolean		Success status
	 */
	function folder_delete($filename)
	{
		list($content_type,$content_id)=$this->folder_convert_filename_to_id($filename);

		require_code('galleries2');
		delete_gallery($content_id);

		return true;
	}

	/**
	 * Standard modular introspection function.
	 *
	 * @return array			The properties available for the content type
	 */
	function _enumerate_file_properties()
	{
		return array(
			'description'=>'LONG_TRANS',
			'url'=>'URLPATH',
			'thumb_url'=>'URLPATH',
			'validated'=>'BINARY',
			'allow_rating'=>'BINARY',
			'allow_comments'=>'SHORT_INTEGER',
			'allow_trackbacks'=>'BINARY',
			'notes'=>'LONG_TEXT',
			'meta_keywords'=>'LONG_TRANS',
			'meta_description'=>'LONG_TRANS',
			'video_length'=>'INTEGER',
			'video_width'=>'INTEGER',
			'video_height'=>'INTEGER',
			'views'=>'INTEGER',
			'submitter'=>'member',
			'add_date'=>'TIME',
			'edit_date'=>'?TIME',
		);
	}

	/**
	 * Get the filename for a content ID. Note that filenames are unique across all folders in a filesystem.
	 *
	 * @param  ID_TEXT	The content type
	 * @param  ID_TEXT	The content ID
	 * @return ID_TEXT	The filename
	 */
	function _file_convert_id_to_filename($content_type,$content_id)
	{
		if ($content_type=='video')
			return 'VIDEO-'.parent::_file_convert_id_to_filename($content_type,$content_id,'video');

		return parent::_file_convert_id_to_filename($content_type,$content_id);
	}

	/**
	 * Get the content ID for a filename. Note that filenames are unique across all folders in a filesystem.
	 *
	 * @param  ID_TEXT	The filename, or filepath
	 * @return array		A pair: The content type, the content ID
	 */
	function file_convert_filename_to_id($filename)
	{
		if (substr($filename,0,6)=='VIDEO-')
			return parent::file_convert_filename_to_id(substr($filename,6),'video');

		return parent::file_convert_filename_to_id($filename,'image');
	}

	/**
	 * Standard modular add function for OcCLE-fs resource hooks. Adds some content with the given label and properties.
	 *
	 * @param  SHORT_TEXT	Filename OR Content label
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return ~ID_TEXT		The content ID (false: error, could not create via these properties / here)
	 */
	function file_add($filename,$path,$properties)
	{
		list($category_content_type,$category)=$this->folder_convert_filename_to_id($path);
		list($properties,$label)=$this->_file_magic_filter($filename,$path,$properties);

		if ($category=='') return false;

		require_code('galleries2');

		$description=$this->_default_property_str($properties,'description');
		$url=$this->_default_property_str($properties,'url');
		$thumb_url=$this->_default_property_str($properties,'thumb_url');
		$validated=$this->_default_property_int_null($properties,'validated');
		if (is_null($validated)) $validated=1;
		$notes=$this->_default_property_str($properties,'notes');
		$submitter=$this->_default_property_int_null($properties,'submitter');
		$add_date=$this->_default_property_int_null($properties,'add_date');
		$edit_date=$this->_default_property_int_null($properties,'edit_date');
		$views=$this->_default_property_int($properties,'views');
		$meta_keywords=$this->_default_property_str($properties,'meta_keywords');
		$meta_description=$this->_default_property_str($properties,'meta_description');

		require_code('images');
		if ((is_image($url)) && ((!array_key_exists('video_length',$properties)) || ($properties['video_length']=='')))
		{
			$allow_rating=$this->_default_property_int_modeavg($properties,'allow_rating','images',1);
			$allow_comments=$this->_default_property_int_modeavg($properties,'allow_comments','images',1);
			$allow_trackbacks=$this->_default_property_int_modeavg($properties,'allow_trackbacks','images',1);

			$accept_images=$GLOBALS['SITE_DB']->query_select_value('galleries','accept_images',array('name'=>$category));
			if ($accept_images==0) return false;

			$id=add_image($label,$cat,$description,$url,$thumb_url,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$submitter,$add_date,$edit_date,$views,NULL,$meta_keywords,$meta_description);
		} else
		{
			$allow_rating=$this->_default_property_int_modeavg($properties,'allow_rating','videos',1);
			$allow_comments=$this->_default_property_int_modeavg($properties,'allow_comments','videos',1);
			$allow_trackbacks=$this->_default_property_int_modeavg($properties,'allow_trackbacks','videos',1);

			$accept_videos=$GLOBALS['SITE_DB']->query_select_value('galleries','accept_videos',array('name'=>$category));
			if ($accept_videos==0) return false;

			$video_length=$this->_default_property_int($properties,'video_length');
			$video_width=$this->_default_property_int_null($properties,'video_width');
			if (is_null($video_width)) $video_width=720;
			$video_height=$this->_default_property_int_null($properties,'video_height');
			if (is_null($video_height)) $video_height=576;

			$id=add_video($label,$cat,$description,$url,$thumb_url,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$video_length,$video_width,$video_height,$submitter,$add_date,$edit_date,$views,NULL,$meta_keywords,$meta_description);
		}

		return strval($id);
	}

	/**
	 * Standard modular load function for OcCLE-fs resource hooks. Finds the properties for some content.
	 *
	 * @param  SHORT_TEXT	Filename
	 * @param  string			The path (blank: root / not applicable)
	 * @return ~array			Details of the content (false: error)
	 */
	function _file_load($filename,$path)
	{
		list($content_type,$content_id)=$this->file_convert_filename_to_id($filename);

		$rows=$GLOBALS['SITE_DB']->query_select($content_type.'s',array('*'),array('id'=>intval($content_id)),'',1);
		if (!array_key_exists(0,$rows)) return false;
		$row=$rows[0];

		$ret=array(
			'label'=>$row['title'],
			'description'=>$row['description'],
			'url'=>$row['url'],
			'thumb_url'=>$row['thumb_url'],
			'validated'=>$row['validated'],
			'allow_rating'=>$row['allow_rating'],
			'allow_comments'=>$row['allow_comments'],
			'allow_trackbacks'=>$row['allow_trackbacks'],
			'notes'=>$row['notes'],
			'meta_keywords'=>$this->get_meta_keywords($content_type,strval($row['id'])),
			'meta_description'=>$this->get_meta_description($content_type,strval($row['id'])),
			'submitter'=>$row['submitter'],
			'add_date'=>$row['add_date'],
			'edit_date'=>$row['edit_date'],
		);
		if ($content_type=='video')
		{
			$ret+=array(
				'views'=>$row['video_views'],
				'video_length'=>$row['video_length'],
				'video_width'=>$row['video_width'],
				'video_height'=>$row['video_height'],
			);
		} else
		{
			$ret+=array(
				'views'=>$row['image_views'],
			);
		}
		return $ret;
	}

	/**
	 * Standard modular edit function for OcCLE-fs resource hooks. Edits the content to the given properties.
	 *
	 * @param  ID_TEXT		The filename
	 * @param  string			The path (blank: root / not applicable)
	 * @param  array			Properties (may be empty, properties given are open to interpretation by the hook but generally correspond to database fields)
	 * @return boolean		Success status
	 */
	function file_edit($filename,$path,$properties)
	{
		list($content_type,$content_id)=$this->file_convert_filename_to_id($filename);
		list($category_content_type,$category)=$this->folder_convert_filename_to_id($path);
		list($properties,)=$this->_file_magic_filter($filename,$path,$properties);

		require_code('galleries2');

		$label=$this->_default_property_str($properties,'label');
		$description=$this->_default_property_str($properties,'description');
		$url=$this->_default_property_str($properties,'url');
		$thumb_url=$this->_default_property_str($properties,'thumb_url');
		$validated=$this->_default_property_int_null($properties,'validated');
		if (is_null($validated)) $validated=1;
		$notes=$this->_default_property_str($properties,'notes');
		$submitter=$this->_default_property_int_null($properties,'submitter');
		$add_date=$this->_default_property_int_null($properties,'add_date');
		$edit_date=$this->_default_property_int_null($properties,'edit_date');
		$views=$this->_default_property_int($properties,'views');
		$meta_keywords=$this->_default_property_str($properties,'meta_keywords');
		$meta_description=$this->_default_property_str($properties,'meta_description');

		if ($content_type=='image')
		{
			$allow_rating=$this->_default_property_int_modeavg($properties,'allow_rating','images',1);
			$allow_comments=$this->_default_property_int_modeavg($properties,'allow_comments','images',1);
			$allow_trackbacks=$this->_default_property_int_modeavg($properties,'allow_trackbacks','images',1);

			$accept_images=$GLOBALS['SITE_DB']->query_select_value('galleries','accept_images',array('name'=>$category));
			if ($accept_images==0) return false;

			edit_image(intval($content_id),$label,$cat,$description,$url,$thumb_url,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$meta_keywords,$meta_description,$edit_time,$add_time,$views,$submitter,true);
		} else
		{
			$allow_rating=$this->_default_property_int_modeavg($properties,'allow_rating','videos',1);
			$allow_comments=$this->_default_property_int_modeavg($properties,'allow_comments','videos',1);
			$allow_trackbacks=$this->_default_property_int_modeavg($properties,'allow_trackbacks','videos',1);

			$accept_videos=$GLOBALS['SITE_DB']->query_select_value('galleries','accept_videos',array('name'=>$category));
			if ($accept_videos==0) return false;

			$video_length=$this->_default_property_int($properties,'video_length');
			$video_width=$this->_default_property_int_null($properties,'video_width');
			if (is_null($video_width)) $video_width=720;
			$video_height=$this->_default_property_int_null($properties,'video_height');
			if (is_null($video_height)) $video_height=576;

			edit_video(intval($content_id),$label,$cat,$description,$url,$thumb_url,$validated,$allow_rating,$allow_comments,$allow_trackbacks,$notes,$video_length,$video_width,$video_height,$meta_keywords,$meta_description,$edit_time,$add_time,$views,$submitter,true);
		}

		return true;
	}

	/**
	 * Standard modular delete function for OcCLE-fs resource hooks. Deletes the content.
	 *
	 * @param  ID_TEXT		The filename
	 * @return boolean		Success status
	 */
	function file_delete($filename)
	{
		list($content_type,$content_id)=$this->file_convert_filename_to_id($filename);

		require_code('galleries2');
		require_code('images');
		if ($content_type=='image')
		{
			delete_image(intval($content_id));
		} else
		{
			delete_video(intval($content_id));
		}

		return true;
	}
}
