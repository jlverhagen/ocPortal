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
 * @package		core
 */

/**
 * Remove an item from the general cache (most commonly used for blocks).
 *
 * @param  ID_TEXT		The type of what we are cacheing (e.g. block name)
 * @param  ?array			A map of identifiying characteristics (NULL: no identifying characteristics, decache all)
 */
function _decache($cached_for,$identifier=NULL)
{
	// NB: If we use persistent cache we still need to decache from DB, in case we're switching between for whatever reason. Or maybe some users use persistent cache and others don't. Or maybe some nodes do and others don't.

	if ($GLOBALS['MEM_CACHE']!==NULL)
	{
		persistent_cache_delete(array('CACHE',$cached_for));
	}

	$where=db_string_equal_to('cached_for',$cached_for);
	if ($identifier!==NULL)
	{
		$where.=' AND (';
		$done_first=false;

		// For combinations of implied parameters
		$bot_statuses=array(true,false);
		$timezones=array_keys(get_timezone_list());
		foreach ($bot_statuses as $bot_status)
		{
			foreach ($timezones as $timezone)
			{
				$_cache_identifier=$identifier;
				$_cache_identifier[]=$timezone;
				$_cache_identifier[]=$bot_status;
				if ($done_first) $where.=' OR ';
				$where.=db_string_equal_to('identifier',md5(serialize($_cache_identifier)));
				$done_first=true;
			}
		}

		// And finally for no implied parameters (raw API usage)
		$_cache_identifier=$identifier;
		if ($done_first) $where.=' OR ';
		$where.=db_string_equal_to('identifier',md5(serialize($_cache_identifier)));
		$done_first=true;

		$where.=')';
	}
	$GLOBALS['SITE_DB']->query('DELETE FROM '.get_table_prefix().'cache WHERE '.$where,NULL,NULL,false,true);
}

/**
 * Request that CRON loads up a block's caching in the background.
 *
 * @param  ID_TEXT		The codename of the block
 * @param  ?array			Parameters to call up block with if we have to defer caching (NULL: none)
 * @param  boolean		Whether we are cacheing Tempcode (needs special care)
 */
function request_via_cron($codename,$map,$tempcode)
{
	global $TEMPCODE_SETGET;
	$map=array(
		'c_theme'=>$GLOBALS['FORUM_DRIVER']->get_theme(),
		'c_lang'=>user_lang(),
		'c_codename'=>$codename,
		'c_map'=>serialize($map),
		'c_timezone'=>get_users_timezone(get_member()),
		'c_is_bot'=>is_null(get_bot_type())?0:1,
		'c_store_as_tempcode'=>$tempcode?1:0,
	);
	if (is_null($GLOBALS['SITE_DB']->query_select_value_if_there('cron_caching_requests','id',$map)))
		$GLOBALS['SITE_DB']->query_insert('cron_caching_requests',$map);
}

/**
 * Put a result into the cache.
 *
 * @param  ID_TEXT			The codename to check for cacheing
 * @param  integer			The TTL of what is being cached in minutes
 * @param  LONG_TEXT			The requisite situational information (a serialized map) [-> further restraints when reading]
 * @param  mixed				The result we are cacheing
 * @param  ?array				A list of the language files that need loading to use tempcode embedded in the cache (NULL: none required)
 * @param  ?array				A list of the javascript files that need loading to use tempcode embedded in the cache (NULL: none required)
 * @param  ?array				A list of the css files that need loading to use tempcode embedded in the cache (NULL: none required)
 * @param  boolean			Whether we are cacheing Tempcode (needs special care)
 * @param  ?ID_TEXT			The theme this is being cached for (NULL: current theme)
 * @param  ?LANGUAGE_NAME	The language this is being cached for (NULL: current language)
 */
function put_into_cache($codename,$ttl,$cache_identifier,$cache,$_langs_required=NULL,$_javascripts_required=NULL,$_csss_required=NULL,$tempcode=false,$theme=NULL,$lang=NULL)
{
	if ($theme===NULL) $theme=$GLOBALS['FORUM_DRIVER']->get_theme();
	if ($lang===NULL) $lang=user_lang();

	global $KEEP_MARKERS,$SHOW_EDIT_LINKS;
	if ($KEEP_MARKERS || $SHOW_EDIT_LINKS) return;

	$dependencies=(is_null($_langs_required))?'':implode(':',$_langs_required);
	$dependencies.='!';
	$dependencies.=(is_null($_javascripts_required))?'':implode(':',$_javascripts_required);
	$dependencies.='!';
	$dependencies.=(is_null($_csss_required))?'':implode(':',$_csss_required);

	if (!is_null($GLOBALS['MEM_CACHE']))
	{
		$pcache=persistent_cache_get(array('CACHE',$codename));
		if (is_null($pcache)) $pcache=array();
		$pcache[$cache_identifier][$lang][$theme]=array('dependencies'=>$dependencies,'date_and_time'=>time(),'the_value'=>$cache);
		persistent_cache_set(array('CACHE',$codename),$pcache,false,$ttl*60);
	} else
	{
		$GLOBALS['SITE_DB']->query_delete('cache',array('lang'=>$lang,'the_theme'=>$theme,'cached_for'=>$codename,'identifier'=>md5($cache_identifier)),'',1);
		$GLOBALS['SITE_DB']->query_insert('cache',array('dependencies'=>$dependencies,'lang'=>$lang,'cached_for'=>$codename,'the_value'=>$tempcode?$cache->to_assembly($lang):serialize($cache),'date_and_time'=>time(),'the_theme'=>$theme,'identifier'=>md5($cache_identifier)),false,true);
	}
}


