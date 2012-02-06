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
 * @package		banners
 */

class Block_main_banner_wave
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
		$info['version']=2;
		$info['locked']=false;
		$info['parameters']=array('param','extra','max');
		return $info;
	}
	
	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	function cacheing_environment()
	{
		$info=array();
		$info['cache_on']='array(array_key_exists(\'param\',$map)?$map[\'param\']:\'\',array_key_exists(\'extra\',$map)?$map[\'extra\']:\'\',array_key_exists(\'max\',$map)?intval($map[\'max\']):100)';
		$info['ttl']=5; // due to shuffle, can't cache long
		return $info;
	}

	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		if (!array_key_exists('param',$map)) $map['param']='';
		if (!array_key_exists('extra',$map)) $map['extra']='';
		$max=array_key_exists('max',$map)?intval($map['max']):100;

		require_code('banners');

		$b_type=$map['param'];
		$myquery='SELECT * FROM '.get_table_prefix().'banners WHERE ((((the_type<>1) OR ((campaign_remaining>0) AND ((expiry_date IS NULL) or (expiry_date>'.strval(time()).')))) AND '.db_string_not_equal_to('name','').')) AND validated=1 AND '.db_string_equal_to('b_type',$b_type).' ORDER BY name';
		$banners=$GLOBALS['SITE_DB']->query($myquery,200/*just in case of insane amounts of data*/);
		$assemble=new ocp_tempcode();

		if (count($banners)>$max)
		{
			shuffle($banners);
			$banners=array_slice($banners,0,$max);
		}

		foreach ($banners as $i=>$banner)
		{
			$bd=show_banner($banner['name'],$banner['b_title_text'],get_translated_tempcode($banner['caption']),$banner['img_url'],'',$banner['site_url'],$banner['b_type']);
			$more_coming=($i<count($banners)-1);
			$assemble->attach(do_template('BLOCK_MAIN_BANNER_WAVE_BWRAP',array('EXTRA'=>$map['extra'],'TYPE'=>$map['param'],'BANNER'=>$bd,'MORE_COMING'=>$more_coming)));
		}

		return do_template('BLOCK_MAIN_BANNER_WAVE',array('EXTRA'=>$map['extra'],'TYPE'=>$map['param'],'ASSEMBLE'=>$assemble));
	}

}


