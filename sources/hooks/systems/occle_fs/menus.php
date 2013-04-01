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
 * @package		core_menus
 */

require_code('content_fs');

class Hook_occle_fs_menus extends content_fs_base
{
	var $folder_content_type='menu';
	var $file_content_type='menu_item';

	/**
	 * Standard modular introspection function.
	 *
	 * @return array			The properties available for the content type
	 */
	function _enumerate_folder_properties()
	{
		return array(
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
		$query='SELECT MAX(date_and_time) FROM '.get_table_prefix().'adminlogs WHERE '.db_string_equal_to('param_a',$row['i_menu']).' AND  ('.db_string_equal_to('the_type','ADD_MENU').' OR '.db_string_equal_to('the_type','EDIT_MENU').')';
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
		if ($category!='') return false; // Only one depth allowed for this content type

		list($properties,$label)=$this->_folder_magic_filter($filename,$path,$properties);

		require_code('menus2');

		$menu=$this->_create_name_from_label($label);

		$order=db_get_first_id();
		$parent=NULL;
		$caption=do_lang('FRONT_PAGE');
		$url='_SELF:start';
		$check_permissions=1;
		$page_only='';
		$expanded=1;
		$new_window=0;
		$caption_long='';
		$theme_image_code='';

		add_menu_item($menu,$order,$parent,$caption,$url,$check_permissions,$page_only,$expanded,$new_window,$caption_long,$theme_image_code);

		log_it('ADD_MENU',$menu);

		return $menu;
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

		return array(
			'label'=>$content_id,
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
		return false;
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

		require_code('menus2');
		delete_menu($content_id);

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
			'order'=>'INTEGER',
			'parent'=>'?menu_item',
			'caption_long'=>'SHORT_TRANS',
			'url'=>'SHORT_TEXT',
			'check_permissions'=>'BINARY',
			'expanded'=>'BINARY',
			'new_window'=>'BINARY',
			'page_only'=>'ID_TEXT',
			'theme_img_code'=>'ID_TEXT',
		);
	}

	/**
	 * Standard modular date fetch function for OcCLE-fs resource hooks. Defined when getting an edit date is not easy.
	 *
	 * @param  array			Content row (not full, but does contain the ID)
	 * @return ?TIME			The edit date or add date, whichever is higher (NULL: could not find one)
	 */
	function _get_file_edit_date($row)
	{
		$query='SELECT MAX(date_and_time) FROM '.get_table_prefix().'adminlogs WHERE '.db_string_equal_to('param_a',strval($row['id'])).' AND  ('.db_string_equal_to('the_type','ADD_MENU_ITEM').' OR '.db_string_equal_to('the_type','EDIT_MENU_ITEM').')';
		return $GLOBALS['SITE_DB']->query_value_if_there($query);
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

		require_code('menus2');

		$order=$this->_default_property_int($properties,'order');
		$parent=$this->_default_property_int_null($properties,'parent');
		$url=$this->_default_property_str($properties,'url');
		$check_permissions=$this->_default_property_int($properties,'check_permissions');
		$page_only=$this->_default_property_str($properties,'page_only');
		$expanded=$this->_default_property_int($properties,'expanded');
		$new_window=$this->_default_property_int($properties,'new_window');
		$caption_long=$this->_default_property_str($properties,'caption_long');
		$theme_image_code=$this->_default_property_str($properties,'theme_image_code');

		$id=add_menu_item($category,$order,$parent,$label,$url,$check_permissions,$page_only,$expanded,$new_window,$caption_long,$theme_image_code);
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

		$rows=$GLOBALS['SITE_DB']->query_select('menu_items',array('*'),array('id'=>intval($content_id)),'',1);
		if (!array_key_exists(0,$rows)) return false;
		$row=$rows[0];

		return array(
			'label'=>$row['i_caption'],
			'order'=>$row['i_order'],
			'parent'=>$row['i_parent'],
			'caption_long'=>$row['i_caption_long'],
			'url'=>$row['i_url'],
			'check_permissions'=>$row['i_check_permissions'],
			'expanded'=>$row['i_expanded'],
			'new_window'=>$row['i_new_window'],
			'page_only'=>$row['i_page_only'],
			'theme_img_code'=>$row['i_theme_img_code'],
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
	function file_edit($filename,$path,$properties)
	{
		list($content_type,$content_id)=$this->file_convert_filename_to_id($filename);
		list($category_content_type,$category)=$this->folder_convert_filename_to_id($path);
		list($properties,)=$this->_file_magic_filter($filename,$path,$properties);

		require_code('menus2');

		$label=$this->_default_property_str($properties,'label');
		$order=$this->_default_property_int($properties,'order');
		$parent=$this->_default_property_int_null($properties,'parent');
		$url=$this->_default_property_str($properties,'url');
		$check_permissions=$this->_default_property_int($properties,'check_permissions');
		$page_only=$this->_default_property_str($properties,'page_only');
		$expanded=$this->_default_property_int($properties,'expanded');
		$new_window=$this->_default_property_int($properties,'new_window');
		$caption_long=$this->_default_property_str($properties,'caption_long');
		$theme_image_code=$this->_default_property_str($properties,'theme_image_code');

		edit_menu_item(intval($content_id),$category,$order,$parent,$label,$url,$check_permissions,$page_only,$expanded,$new_window,$caption_long,$theme_image_code);

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

		require_code('menus2');
		delete_menu_item(intval($content_id));

		return true;
	}
}
