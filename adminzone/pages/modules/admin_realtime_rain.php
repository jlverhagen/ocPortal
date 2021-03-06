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
 * @package		realtime_rain
 */

/**
 * Module page class.
 */
class Module_admin_realtime_rain
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=1;
		$info['locked']=false;
		return $info;
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('!'=>'REALTIME_RAIN');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		add_config_option('REALTIME_RAIN_BUTTON','bottom_show_realtime_rain_button','tick','return \'0\';','FEATURE','BOTTOM_LINKS');
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		delete_config_option('bottom_show_realtime_rain_button');
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		require_javascript('javascript_realtime_rain');
		require_javascript('javascript_ajax');
		require_javascript('javascript_more');
		require_lang('realtime_rain');
		require_css('realtime_rain');

		$title=get_screen_title('REALTIME_RAIN');

		if (!has_js())
		{
			// Send them to the page permissions screen
			$url=build_url(array('page'=>'admin_stats','type'=>'misc'),'_SELF');
			require_code('site2');
			assign_refresh($url,5.0);
			return do_template('REDIRECT_SCREEN',array('_GUID'=>'d364f4b7afc82e32d1d7c59316908a50','URL'=>$url,'TITLE'=>$title,'TEXT'=>do_lang_tempcode('NO_JS_REALTIME')));
		}

		if (!has_js())
		{
			// Send them to the stats screen
			$url=build_url(array('page'=>'admin_stats','type'=>'misc'),'_SELF');
			require_code('site2');
			assign_refresh($url,5.0);
			return do_template('REDIRECT_SCREEN',array('_GUID'=>'7b7f4d3e565f010723aa5c414a64b467','URL'=>$url,'TITLE'=>$title,'TEXT'=>do_lang_tempcode('NO_JS_ADVANCED_SCREEN_REALTIME_RAIN')));
		}

		$min_time=$GLOBALS['SITE_DB']->query_value('stats','MIN(date_and_time)');
		if (is_null($min_time)) $min_time=time();
		return do_template('REALTIME_RAIN_OVERLAY',array('MIN_TIME'=>strval($min_time)));
	}

}

