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
 * @package		core_fields
 */

class Hook_fields_url
{

	// ==============
	// Module: search
	// ==============

	/**
	 * Get special Tempcode for inputting this field.
	 *
	 * @param  array			The row for the field to input
	 * @return ?array			List of specially encoded input detail rows (NULL: nothing special)
	 */
	function get_search_inputter($row)
	{
		return NULL;
	}

	/**
	 * Get special SQL from POSTed parameters for this field.
	 *
	 * @param  array			The row for the field to input
	 * @param  integer		We're processing for the ith row
	 * @return ?array			Tuple of SQL details (array: extra trans fields to search, array: extra plain fields to search, string: an extra table segment for a join, string: the name of the field to use as a title, if this is the title, extra WHERE clause stuff) (NULL: nothing special)
	 */
	function inputted_to_sql_for_search($row,$i)
	{
		return NULL;
	}

	// ===================
	// Backend: fields API
	// ===================

	/**
	 * Get some info bits relating to our field type, that helps us look it up / set defaults.
	 *
	 * @param  ?array			The field details (NULL: new field)
	 * @param  ?boolean		Whether a default value cannot be blank (NULL: don't "lock in" a new default value)
	 * @param  ?string		The given default value as a string (NULL: don't "lock in" a new default value)
	 * @return array			Tuple of details (row-type,default-value-to-use,db row-type)
	 */
	function get_field_value_row_bits($field,$required=NULL,$default=NULL)
	{
		unset($field);
		return array('short_unescaped',$default,'short');
	}

	/**
	 * Convert a field value to something renderable.
	 *
	 * @param  array			The field details
	 * @param  mixed			The raw value
	 * @return mixed			Rendered field (tempcode or string)
	 */
	function render_field_value($field,$ev)
	{
		if (is_object($ev)) return $ev;

		if ($ev=='') return '';
		if ($ev=='http://') return '';

		$link_captions_title=$GLOBALS['SITE_DB']->query_value_null_ok('url_title_cache','t_title',array('t_url'=>$ev));

		if ((is_null($link_captions_title)) || (substr($link_captions_title,0,1)=='!'))
		{
			$link_captions_title='';
			require_code('files');
			$downloaded_at_link=http_download_file($ev,3000,false);
			if ((is_string($downloaded_at_link)) && (strpos($GLOBALS['HTTP_DOWNLOAD_MIME_TYPE'],'html')!==false) && ($GLOBALS['HTTP_MESSAGE']=='200'))
			{
				$matches=array();
				if (preg_match('#\s*<title[^>]*\s*>\s*(.*)\s*\s*<\s*/title\s*>#miU',$downloaded_at_link,$matches)!=0)
				{
					require_code('character_sets');

					$link_captions_title=trim(str_replace('&ndash;','-',str_replace('&mdash;','-',@html_entity_decode(convert_to_internal_encoding($matches[1]),ENT_QUOTES,get_charset()))));
					if (((strpos(strtolower($link_captions_title),'login')!==false) || (strpos(strtolower($link_captions_title),'log in')!==false)) && (substr($ev,0,strlen(get_base_url()))==get_base_url()))
						$link_captions_title=''; // don't show login screen titles for our own website. Better to see the link verbatim
				}
			}
			$GLOBALS['SITE_DB']->query_insert('url_title_cache',array(
				't_url'=>$ev,
				't_title'=>$link_captions_title,
			),false,true); // To stop weird race-like conditions
		}

		if ($link_captions_title=='') $link_captions_title=urldecode(basename($ev));

		return hyperlink((url_is_local($ev)?(get_base_url().'/'):'').$ev,escape_html($link_captions_title),true);
	}

	// ======================
	// Frontend: fields input
	// ======================

	/**
	 * Get form inputter.
	 *
	 * @param  string			The field name
	 * @param  string			The field description
	 * @param  array			The field details
	 * @param  ?string		The actual current value of the field (NULL: none)
	 * @param  boolean		Whether this is for a new entry
	 * @return ?tempcode		The Tempcode for the input field (NULL: skip the field - it's not input)
	 */
	function get_field_inputter($_cf_name,$_cf_description,$field,$actual_value,$new)
	{
		if (is_null($actual_value)) $actual_value=''; // Plug anomaly due to unusual corruption

		return form_input_line($_cf_name,$_cf_description,'field_'.strval($field['id']),$actual_value,$field['cf_required']==1,NULL,NULL,'url');
	}

	/**
	 * Find the posted value from the get_field_inputter field
	 *
	 * @param  boolean		Whether we were editing (because on edit, it could be a fractional edit)
	 * @param  array			The field details
	 * @param  ?string		Where the files will be uploaded to (NULL: do not store an upload, return NULL if we would need to do so)
	 * @param  ?string		Former value of field (NULL: none)
	 * @return ?string		The value (NULL: could not process)
	 */
	function inputted_to_field_value($editing,$field,$upload_dir='uploads/catalogues',$old_value=NULL)
	{
		$id=$field['id'];
		$tmp_name='field_'.strval($id);
		$value=post_param($tmp_name,$editing?STRING_MAGIC_NULL:'');
		if ($value!=STRING_MAGIC_NULL) $value=fixup_protocolless_urls($value);
		return $value;
	}

}


