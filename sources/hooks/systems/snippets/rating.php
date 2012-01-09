<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_feedback_features
 */

class Hook_rating
{

	/**
	 * Standard modular run function for snippet hooks. Generates XHTML to insert into a page using AJAX.
	 *
	 * @return tempcode  The snippet
	 */
	function run()
	{
		if (get_option('is_on_rating')=='0') return do_lang_tempcode('INTERNAL_ERROR');

		// Has there actually been any rating?
		$rating=post_param_integer('rating');
		$type=get_param('root_type');
		$type2=get_param('type');
		if ($type2!='') $type.='_'.$type2;
		$id=get_param('id');

		$self_url=get_param('self_url');
		$title=get_param('self_title');

		require_code('feedback');
		do_specific_rating($rating,get_page_name(),get_member(),$type,$type2,$id,$self_url,$title);

		$template=get_param('template');
		if (($type2=='') && ($template!=''))
		{
			return display_rating($self_url,$title,$type,$id,$template);
		}

		return do_lang_tempcode('THANKYOU_FOR_RATING_SHORT');
	}

}

