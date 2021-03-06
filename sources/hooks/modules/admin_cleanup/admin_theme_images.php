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
 * @package		core_cleanup_tools
 */

class Hook_admin_theme_images
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['title']=do_lang_tempcode('THEME_IMAGES_CACHE');
		$info['description']=do_lang_tempcode('DESCRIPTION_THEME_IMAGES_CACHE');
		$info['type']='cache';

		return $info;
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	Results
	 */
	function run()
	{
		$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'theme_images WHERE path LIKE \'themes/%/images/%\'');

		persistent_cache_delete('THEME_IMAGES');

		$paths=$GLOBALS['SITE_DB']->query_select('theme_images',array('path','id'));
		foreach ($paths as $path)
		{
			if ($path['path']=='')
			{
				$GLOBALS['SITE_DB']->query_delete('theme_images',$path,'',1);
			}
			elseif (preg_match('#^themes/[^/]+/images_custom/+'.str_replace('#','\#',preg_quote($path['id'])).'\.#',$path['path'])!=0)
			{
				if ((!file_exists(get_custom_file_base().'/'.$path['path'])) && (!file_exists(get_file_base().'/'.$path['path'])))
				{
					$GLOBALS['SITE_DB']->query_delete('theme_images',$path,'',1);
				}
			}
		}

		return new ocp_tempcode();
	}

}


