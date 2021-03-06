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
 * @package		occle
 */

class Hook_set_comment_forum
{
	/**
	 * Standard modular run function for OcCLE hooks.
	 *
	 * @param  array	The options with which the command was called
	 * @param  array	The parameters with which the command was called
	 * @param  array	A reference to the OcCLE filesystem object
	 * @return array	Array of stdcommand, stdhtml, stdout, and stderr responses
	 */
	function run($options,$parameters,&$occle_fs)
	{
		if ((array_key_exists('h',$options)) || (array_key_exists('help',$options))) return array('',do_command_help('set_comment_forum',array('h'),array(true,true,true)),'','');
		else
		{
			if (!array_key_exists(0,$parameters)) return array('','','',do_lang('MISSING_PARAM','1','set_comment_forum'));
			if (!array_key_exists(1,$parameters)) return array('','','',do_lang('MISSING_PARAM','2','set_comment_forum'));
			if (!array_key_exists(2,$parameters)) return array('','','',do_lang('MISSING_PARAM','3','set_comment_forum'));

			list($feedback_code,$category_id,$forum_id)=$parameters;

			require_code('feedback2');
			set_comment_forum_for($feedback_code,$category_id,$forum_id);

			$result=do_lang('SUCCESS');

			return array('',$result,'','');
		}
	}

}
