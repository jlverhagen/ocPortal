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
 * @package		core_rich_media
 */

class Hook_config_oembed_html_whitelist
{

	/**
	 * Gets the details relating to the config option.
	 *
	 * @return ?array		The details (NULL: disabled)
	 */
	function get_details()
	{
		return array(
			'human_name'=>'OEMBED_HTML_WHITELIST',
			'type'=>'text',
			'category'=>'FEATURE',
			'group'=>'MEDIA',
			'explanation'=>'CONFIG_OPTION_oembed_html_whitelist',
			'shared_hosting_restricted'=>'0',
			'list_options'=>'',

			'addon'=>'core_rich_media',
		);
	}

	/**
	 * Gets the default value for the config option.
	 *
	 * @return ?string		The default value (NULL: option is disabled)
	 */
	function get_default()
	{
		return "youtube.com\nyoutu.be\nvimeo.com\ndailymotion.com\nslideshare.net\nscribd.com\nsoundcloud.com\ntwitter.com\nembed.ly\nmaps.google.com\nmaps.google.co.uk\nimgur.com";
	}

}


