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
 * @package		ocf_cpfs
 */

class Hook_addon_registry_ocf_cpfs
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
		return 'Custom profile fields, so members may save additional details. If this is uninstalled any existing custom profile fields will remain in the system.';
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
			'conflicts_with'=>array()
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
			'adminzone/pages/modules/admin_ocf_customprofilefields.php',
			'OCF_CPF_STATS_LINE.tpl',
			'OCF_CPF_STATS_SCREEN.tpl',
			'uploads/ocf_cpf_upload/index.html',
			'uploads/ocf_cpf_upload/.htaccess',
			'OCF_CPF_PERMISSIONS_TAB.tpl',
			'lang/EN/ocf_privacy.ini',
			'sources/hooks/systems/profiles_tabs_edit/privacy.php',
			'sources/hooks/systems/addon_registry/ocf_cpfs.php'
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
			'OCF_CPF_PERMISSIONS_TAB.tpl'=>'ocf_cpf_permissions_tab',
			'OCF_CPF_STATS_LINE.tpl'=>'administrative__ocf_cpf_stats_screen',
			'OCF_CPF_STATS_SCREEN.tpl'=>'administrative__ocf_cpf_stats_screen'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__ocf_cpf_permissions_tab()
	{
		return array(
			lorem_globalise(do_lorem_template('OCF_CPF_PERMISSIONS_TAB', array(
				'FIELDS'=>placeholder_fields()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__administrative__ocf_cpf_stats_screen()
	{
		$lines=new ocp_tempcode();
		foreach (placeholder_array() as $value)
		{
			$lines->attach(do_lorem_template('OCF_CPF_STATS_LINE', array(
				'CNT'=>placeholder_number(),
				'VAL'=>lorem_phrase()
			)));
		}

		return array(
			lorem_globalise(do_lorem_template('OCF_CPF_STATS_SCREEN', array(
				'TITLE'=>lorem_title(),
				'STATS'=>$lines
			)), NULL, '', true)
		);
	}

}
