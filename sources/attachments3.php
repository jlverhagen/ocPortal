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
 * @package		core_rich_media
 */

/**
 * Delete the specified attachment
 *
 * @param  AUTO_LINK		The attachment ID to delete
 * @param  object			The database connection to use
 * @set    ocp forum
 */
function _delete_attachment($id,$connection)
{
	$connection->query_delete('attachment_refs',array('a_id'=>$id));

	// Get attachment details
	$_attachment_info=$connection->query_select('attachments',array('a_url','a_thumb_url'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$_attachment_info)) return; // Already gone
	$attachment_info=$_attachment_info[0];

	// Delete url and thumb_url if local
	if ((url_is_local($attachment_info['a_url'])) && (substr($attachment_info['a_url'],0,19)=='uploads/attachments'))
	{
		$url=rawurldecode($attachment_info['a_url']);
		@unlink(get_custom_file_base().'/'.$url);
		sync_file($url);
		if (($attachment_info['a_thumb_url']!='') && (strpos($attachment_info['a_thumb_url'],'uploads/filedump/')===false))
		{
			$thumb_url=rawurldecode($attachment_info['a_thumb_url']);
			@unlink(get_custom_file_base().'/'.$thumb_url);
			sync_file($thumb_url);
		}
	}

	// Delete attachment
	$connection->query_delete('attachments',array('id'=>$id),'',1);
}

/**
 * Deletes all the attachments a given language code holds. Well, not quite! It deletes all references, and any attachments have through it, run out of references.
 *
 * @param  ID_TEXT		The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT		The id in the set of the arbitrary types that the attached is for
 * @param  ?object		The database connection to use (NULL: standard site connection)
 */
function delete_comcode_attachments($type,$id,$connection=NULL)
{
	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	require_lang('comcode');

	$refs=$connection->query_select('attachment_refs',array('a_id','id'),array('r_referer_type'=>$type,'r_referer_id'=>$id));
	$connection->query_delete('attachment_refs',array('r_referer_type'=>$type,'r_referer_id'=>$id));
	foreach ($refs as $ref)
	{
		// Was that the last reference to this attachment? (if so -- delete attachment)
		$test=$connection->query_value_null_ok('attachment_refs','id',array('a_id'=>$ref['a_id']));
		if (is_null($test))
			_delete_attachment($ref['a_id'],$connection);
	}
}

/**
 * This function is the same as delete_comcode_attachments, except that it deletes the language code as well.
 *
 * @param  integer		The language id
 * @param  ID_TEXT		The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT		The id in the set of the arbitrary types that the attached is for
 * @param  ?object		The database connection to use (NULL: standard site connection)
 */
function delete_lang_comcode_attachments($lang_id,$type,$id,$connection=NULL)
{
	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	delete_comcode_attachments($type,$id,$connection);
	$connection->query_delete('translate',array('id'=>$lang_id),'',1);
}

/**
 * Update a language code, in such a way that new attachments are created if they were specified.
 *
 * @param  integer		The language id
 * @param  LONG_TEXT		The new text
 * @param  ID_TEXT		The arbitrary type that the attached is for (e.g. download)
 * @param  ID_TEXT		The id in the set of the arbitrary types that the attached is for
 * @param  ?object		The database connection to use (NULL: standard site connection)
 * @param  boolean		Whether to backup the language string before changing it
 * @param  ?MEMBER		The member to use for ownership permissions (NULL: current member)
 * @return integer		The language id
 */
function update_lang_comcode_attachments($lang_id,$text,$type,$id,$connection=NULL,$backup_string=false,$for_member=NULL)
{
	if ($lang_id==0) return insert_lang_comcode_attachments(3,$text,$type,$id,$connection,false,$for_member);

	if ($text===STRING_MAGIC_NULL) return $lang_id;

	if (is_null($connection)) $connection=$GLOBALS['SITE_DB'];

	require_lang('comcode');

	_check_attachment_count();

	$test=$connection->query_value_null_ok('translate','text_original',array('id'=>$id,'language'=>user_lang()));

	if ($backup_string)
	{
		$current=$connection->query_select('translate',array('*'),array('id'=>$lang_id,'language'=>user_lang()));
		if (!array_key_exists(0,$current))
		{
			$current=$connection->query_select('translate',array('*'),array('id'=>$lang_id));
		}

		$connection->query_insert('translate_history',array(
			'lang_id'=>$lang_id,
			'language'=>$current[0]['language'],
			'text_original'=>$current[0]['text_original'],
			'broken'=>$current[0]['broken'],
			'action_member'=>get_member(),
			'action_time'=>time()
		));
	}

	$member=(function_exists('get_member'))?get_member():$GLOBALS['FORUM_DRIVER']->get_guest_id();

	$_info=do_comcode_attachments($text,$type,$id,false,$connection,NULL,$for_member);
	$text2='';//Actually we'll let it regenerate with the correct permissions ($member, not $for_member) $_info['tempcode']->to_assembly();
	$remap=array('text_original'=>$_info['comcode'],'text_parsed'=>$text2);
	if (((ocp_admirecookie('use_wysiwyg','1')=='0') && (get_value('edit_with_my_comcode_perms')==='1')) || (!has_specific_permission($member,'allow_html')) || (!has_specific_permission($member,'use_very_dangerous_comcode')))
		$remap['source_user']=$member;
	if (!is_null($test)) // Good, we save into our own language, as we have a translation for the lang entry setup properly
	{
		$connection->query_update('translate',$remap,array('id'=>$lang_id,'language'=>user_lang()));
	} else // Darn, we'll have to save over whatever we did load from
	{
		$connection->query_update('translate',$remap,array('id'=>$lang_id));
	}
	return $lang_id;
}
