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
 * @package		core
 */

/**
 * Standard code module initialisation function.
 */
function init__themes()
{
	global $THEME_IMAGES_CACHE,$CDN_CONSISTENCY_CHECK,$RECORD_THEME_IMAGES_CACHE,$RECORDED_THEME_IMAGES;
	$THEME_IMAGES_CACHE=array();
	$CDN_CONSISTENCY_CHECK=array();
	$RECORD_THEME_IMAGES_CACHE=false;
	$RECORDED_THEME_IMAGES=array();
}

/**
 * Find the URL to the theme image of the specified ID. It searches various priorities, including language and theme overrides.
 *
 * @param  ID_TEXT			The theme image ID
 * @param  boolean			Whether to silently fail (i.e. not give out an error message when a theme image cannot be found)
 * @param  boolean			Whether to leave URLs as relative local URLs
 * @param  ?ID_TEXT			The theme to search in (NULL: users current theme)
 * @param  ?LANGUAGE_NAME  The language to search for (NULL: users current language)
 * @param  ?object			The database to use (NULL: site database)
 * @param  boolean			Whether to only search the default 'images' filesystem
 * @return URLPATH			The URL found (blank: not found)
 */
function find_theme_image($id,$silent_fail=false,$leave_local=false,$theme=NULL,$lang=NULL,$db=NULL,$pure_only=false)
{
	if ((substr($id,0,4)=='ocf_') && (is_file(get_file_base().'/themes/default/images/avatars/index.html'))) // Allow debranding of theme img dirs
	{
		$id=substr($id,4);
	}

	if ((isset($_GET['keep_theme_seed'])) && (get_param('keep_theme_seed',NULL)!==NULL) && (function_exists('has_privilege')) && (has_privilege(get_member(),'view_profiling_modes')))
	{
		require_code('themewizard');
		$test=find_theme_image_themewizard_preview($id);
		if ($test!==NULL) return $test;
	}

	if ($db===NULL) $db=$GLOBALS['SITE_DB'];

	global $RECORD_THEME_IMAGES_CACHE;
	if ($RECORD_THEME_IMAGES_CACHE)
	{
		global $RECORDED_THEME_IMAGES;
		if ((isset($GLOBALS['FORUM_DB'])) && ($db->connection_write!==$GLOBALS['FORUM_DB']->connection_write))
			$RECORDED_THEME_IMAGES[serialize(array($id,$theme,$lang))]=1;
	}

	$true_theme=$GLOBALS['FORUM_DRIVER']->get_theme();
	if ($theme===NULL) $theme=$true_theme;

	global $USER_LANG_CACHED;
	$true_lang=($USER_LANG_CACHED===NULL)?user_lang():$USER_LANG_CACHED;
	if ($lang===NULL) $lang=$true_lang;

	$truism=($theme==$true_theme) && ($lang==$true_lang);

	$site=($GLOBALS['SITE_DB']==$db)?'site':'forums';

	global $THEME_IMAGES_CACHE;
	if (!isset($THEME_IMAGES_CACHE[$site]))
	{
		$cache=NULL;
		if ($site=='site')
		{
			$cache=persistent_cache_get('THEME_IMAGES');
		}

		if (!isset($cache[$true_theme][$true_lang]))
		{
			$THEME_IMAGES_CACHE[$site]=$db->query_select('theme_images',array('id','path'),array('theme'=>$true_theme,'lang'=>$true_lang));
			$THEME_IMAGES_CACHE[$site]=collapse_2d_complexity('id','path',$THEME_IMAGES_CACHE[$site]);

			if ($site=='site')
			{
				if ($cache===NULL) $cache=array();
				$cache[$theme][$lang]=$THEME_IMAGES_CACHE[$site];
				persistent_cache_set('THEME_IMAGES',$cache);
			}
		} else
		{
			$THEME_IMAGES_CACHE[$site]=$cache[$true_theme][$true_lang];
		}
	}

	if ((!$truism) && (!$pure_only)) // Separate lookup, cannot go through $THEME_IMAGES_CACHE
	{
		$path=$db->query_select_value_if_there('theme_images','path',array('theme'=>$theme,'lang'=>$lang,'id'=>$id));
		if ($path!==NULL)
		{
			if ((url_is_local($path)) && (!$leave_local))
			{
				$path=(($db->connection_write!=$GLOBALS['SITE_DB']->connection_write)?get_forum_base_url():((substr($path,0,22)=='themes/default/images/')?get_base_url():get_custom_base_url())).'/'.$path;
			}

			return cdn_filter($path);
		}
	}

	if (($pure_only) || (!isset($THEME_IMAGES_CACHE[$site][$id])) || (!$truism))
	{
		$path=NULL;

		$priorities=array();
		if (!$pure_only) // Should do "images_custom" first, as this will also do a DB search
		{
			$priorities=array_merge($priorities,array(
				array($theme,$lang,'images_custom'),
				array($theme,'','images_custom'),
				($lang==fallback_lang())?NULL:array($theme,fallback_lang(),'images_custom'),
			));
		}
		// This will not do a DB search, just a filesystem search. The Theme Wizard makes these though
		$priorities=array_merge($priorities,array(
			array($theme,$lang,'images'),
			array($theme,'','images'),
			($lang==fallback_lang())?NULL:array($theme,fallback_lang(),'images'),
		));
		if ($theme!='default')
		{
			if (!$pure_only)
			{
				$priorities=array_merge($priorities,array(
					array('default',$lang,'images_custom'),
					array('default','','images_custom'),
					($lang==fallback_lang())?NULL:array('default',fallback_lang(),'images_custom'),
				));
			}
			$priorities=array_merge($priorities,array(
				array('default',$lang,'images'),
				array('default','','images'),
				($lang==fallback_lang())?NULL:array('default',fallback_lang(),'images'),
			));
		}

		foreach ($priorities as $i=>$priority)
		{
			if ($priority===NULL) continue;

			if (($priority[2]=='images_custom') && ($priority[1]!='')) // Likely won't auto find
			{
				$smap=array('id'=>$id,'theme'=>$priority[0],'lang'=>$priority[1]);
				$nql_backup=$GLOBALS['NO_QUERY_LIMIT'];
				$GLOBALS['NO_QUERY_LIMIT']=true;
				$truism_b=($priority[0]==$true_theme) && ((!multi_lang()) || ($priority[1]=='') || ($priority[1]===$true_lang));
				$path=$truism_b?NULL:$db->query_select_value_if_there('theme_images','path',$smap);
				$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;

				if ($path!==NULL) // Make sure this isn't just the result file we should find at a lower priority
				{
					if (strpos($path,'/images/'.$id.'.')!==false) continue;
					if ((array_key_exists('lang',$smap)) &&  (strpos($path,'/images/'.$smap['lang'].'/'.$id.'.')!==false)) continue;
					break;
				}
			}

			$test=_search_img_file($priority[0],$priority[1],$id,$priority[2]);
			if ($test!==NULL)
			{
				$path_bits=explode('/',$test);
				$path='';
				foreach ($path_bits as $bit)
				{
					if ($path!='') $path.='/';
					$path.=rawurlencode($bit);
				}
				break;
			}
		}

		if ($db->connection_write==$GLOBALS['SITE_DB']->connection_write) // If guard is here because a MSN site can't make assumptions about the file system of the central site
		{
			if ((($path!==NULL) && ($path!='')) || (($silent_fail) && ($GLOBALS['SEMI_DEV_MODE'])))
			{
				$nql_backup=$GLOBALS['NO_QUERY_LIMIT'];
				$GLOBALS['NO_QUERY_LIMIT']=true;
				$db->query_delete('theme_images',array('id'=>$id,'theme'=>$theme,'lang'=>$lang)); // Allow for race conditions
				$db->query_insert('theme_images',array('id'=>$id,'theme'=>$theme,'path'=>($path===NULL)?'':$path,'lang'=>$lang),false,true); // Allow for race conditions
				$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;
				persistent_cache_delete('THEME_IMAGES');
			}
		}

		if ($path===NULL)
		{
			if (!$silent_fail)
			{
				require_code('site');
				attach_message(do_lang_tempcode('NO_SUCH_IMAGE',escape_html($id)),'warn');
			}
			return '';
		}
		if ($truism) $THEME_IMAGES_CACHE[$site][$id]=$path; // only cache if we are looking up for our own theme/lang
	} else
	{
		$path=$THEME_IMAGES_CACHE[$site][$id];

		global $SITE_INFO;

		if (($path!='') && ((!isset($SITE_INFO['disable_smart_decaching'])) || ($SITE_INFO['disable_smart_decaching']!='1')) && (url_is_local($path)) && (!is_file(get_custom_file_base().'/'.rawurldecode($path)))) // Missing image, so erase to re-search for it
		{
			unset($THEME_IMAGES_CACHE[$site][$id]);
			return find_theme_image($id,$silent_fail,$leave_local,$theme,$lang,$db,$pure_only);
		}
	}
	if ((url_is_local($path)) && (!$leave_local) && ($path!=''))
	{
		if ($db->connection_write!=$GLOBALS['SITE_DB']->connection_write)
		{
			$base_url=get_forum_base_url();
		} else
		{
			global $SITE_INFO;
			$missing=(!$pure_only) && (((!isset($SITE_INFO['disable_smart_decaching'])) || ($SITE_INFO['disable_smart_decaching']!='1')) && (!is_file(get_file_base().'/'.rawurldecode($path)) && (!is_file(get_custom_file_base().'/'.rawurldecode($path)))));
			if ((substr($path,0,22)=='themes/default/images/') || ($missing)) // Not found, so throw away custom theme image and look in default theme images to restore default
			{
				if ($missing)
				{
					return find_theme_image($id,$silent_fail,$leave_local,$theme,$lang,$db,true);
				}

				$base_url=get_base_url();
			} else
			{
				$base_url=get_custom_base_url();
			}
		}

		$path=$base_url.'/'.$path;
	}

	return cdn_filter($path);
}

/**
 * Filter a path so it runs through a CDN.
 *
 * @param  URLPATH			Input URL
 * @return URLPATH			Output URL
 */
function cdn_filter($path)
{
	static $cdn=NULL;
	if ($cdn===NULL) $cdn=get_option('cdn');
	static $knm=NULL;
	if ($knm===NULL) $knm=get_param_integer('keep_no_minify',0);

	if (($cdn!='') && ($knm==0))
	{
		if ($cdn=='<autodetect>')
		{
			$cdn=get_value('cdn');
			if ($cdn==NULL)
			{
				require_code('themes2');
				$cdn=autoprobe_cdns();
			}
		}

		global $CDN_CONSISTENCY_CHECK;

		if (isset($CDN_CONSISTENCY_CHECK[$path])) return $CDN_CONSISTENCY_CHECK[$path];

		$cdn_parts=explode(',',$cdn);

		$sum_asc=0;
		$path_len=strlen($path);
		for ($i=0;$i<$path_len;$i++) $sum_asc+=ord($path[$i]);

		$cdn_part=$cdn_parts[$sum_asc%count($cdn_parts)]; // To make a consistent but fairly even distribution we do some modular arithmetic against the total of the ascii values
		$out=preg_replace('#(^https?://)'.str_replace('#','#',preg_quote(get_domain())).'(/)#','${1}'.$cdn_part.'${2}',$path);
		$CDN_CONSISTENCY_CHECK[$path]=$out;
		return $out;
	}

	return $path;
}

/**
 * Search for a specified image file within a theme for a specified language.
 *
 * @param  ID_TEXT			The theme
 * @param  ?LANGUAGE_NAME	The language (NULL: try generally, under no specific language)
 * @param  ID_TEXT			The theme image ID
 * @param  ID_TEXT			Directory to search
 * @return ?string			The path to the image (NULL: was not found)
 */
function _search_img_file($theme,$lang,$id,$dir='images')
{
	$extensions=array('png','jpg','jpeg','gif','ico');
	$base=(($theme=='default')?get_file_base():get_custom_file_base()).'/themes/';
	$url_base='themes/';
	foreach (array(get_file_base(),get_custom_file_base()) as $_base)
	{
		$base=$_base.'/themes/';

		foreach ($extensions as $extension)
		{
			$file_path=$base.$theme.'/';
			if ($dir!='') $file_path.=$dir.'/';
			if (($lang!==NULL) && ($lang!='')) $file_path.=$lang.'/';
			$file_path.=$id.'.'.$extension;
			if (is_file($file_path)) // Theme+Lang
			{
				$path=$url_base.rawurlencode($theme).'/'.$dir.'/';
				if (($lang!==NULL) && ($lang!='')) $path.=rawurlencode($lang).'/';
				$path.=$id.'.'.$extension;
				return $path;
			}
		}
	}
	return NULL;
}



