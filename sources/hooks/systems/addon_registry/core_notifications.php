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
 * @package		core_notifications
 */

class Hook_addon_registry_core_notifications
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
		return 'Sends out action-triggered notifications to members listening to them.';
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
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(

			'sources/hooks/systems/addon_registry/core_notifications.php',
			'sources/notifications.php',
			'lang/EN/notifications.ini',
			'sources/hooks/systems/cron/notification_digests.php',
			'sources/hooks/systems/notifications/.htaccess',
			'sources/hooks/systems/notifications/index.html',
			'themes/default/images/EN/page/disable_notifications.png',
			'themes/default/images/EN/page/enable_notifications.png',
			'themes/default/images/EN/pageitem/disable_notifications.png',
			'themes/default/images/EN/pageitem/enable_notifications.png',
			'sources/hooks/systems/profiles_tabs_edit/notifications.php',
		);
	}

}
