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
 * @package		search
 */

class Block_side_tag_cloud
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
		$info['version']=3;
		$info['locked']=false;
		$info['parameters']=array('param','title','zone','max');
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
		$info['cache_on']='array(array_key_exists(\'title\',$map)?$map[\'title\']:do_lang(\'TAG_CLOUD\'),array_key_exists(\'max\',$map)?intval($map[\'max\']):30,array_key_exists(\'zone\',$map)?$map[\'zone\']:\'_SEARCH\',array_key_exists(\'param\',$map)?$map[\'param\']:\'\')';
		$info['ttl']=60*1;
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
		require_lang('search');
		require_css('search');

		$zone=array_key_exists('zone',$map)?$map['zone']:get_module_zone('search');
		$max_tags=array_key_exists('max',$map)?intval($map['max']):30;

		$tags=array();
		$largest_num=0;
		$smallest_num=mixed();

		$search_limiter=array('all_defaults'=>'1');

		// Find all keywords, hence all tags
		$limit_to=array_key_exists('param',$map)?$map['param']:''; // HACKHACK: No correlation between meta keywords and search hook names, so we have to specify both in here
		if ($limit_to!='')
		{
			$where='';
			foreach (explode(',',$limit_to) as $l)
			{
				if ($where!='') $where.=' OR ';
				$where.=db_string_equal_to('meta_for_type',$l);

				$search_limiter['search_'.$l]=1;
			}
			$search_limiter['all_defaults']='0';
		} else
		{
			$where='1=1';
		}
		$where.=' AND '.db_string_not_equal_to('text_original','');
		$meta_rows=$GLOBALS['SITE_DB']->query('SELECT meta_for_type,meta_for_id,text_original AS meta_keywords_nice,meta_keywords FROM '.get_table_prefix().'seo_meta m LEFT JOIN '.get_table_prefix().'translate t ON '.db_string_equal_to('language',user_lang()).' AND m.meta_keywords=t.id WHERE '.$where.' ORDER BY m.id DESC',300/*reasonable limit*/);
		foreach ($meta_rows as $mr)
		{
			if ($GLOBALS['RECORD_LANG_STRINGS_CONTENT'] || is_null($mr['meta_keywords_nice'])) $mr['meta_keywords_nice']=get_translated_text($mr['meta_keywords']);

			$keywords=explode(',',$mr['meta_keywords_nice']);
			foreach ($keywords as $keyword)
			{
				$keyword=trim($keyword);
				if ($keyword=='') continue;
				if (strlen(is_numeric($keyword)?strval(intval($keyword)):$keyword)<4) continue; // Won't be indexed, plus will uglify the tag list
				if (!array_key_exists($keyword,$tags)) $tags[$keyword]=0;
				$tags[$keyword]++;
			}
		}
		arsort($tags);
		$_tags=$tags;
		$tags=array();
		foreach ($_tags as $tag=>$count)
		{
			if (!is_string($tag)) $tag=strval($tag);
			$tags[$tag]=$count;
			if (count($tags)==$max_tags) break;
		}
		ksort($tags);

		if (count($tags)==0) return new ocp_tempcode();

		// Work out variation in sizings
		foreach ($tags as $tag=>$count)
		{
			if ((is_null($smallest_num)) || ($count<$smallest_num)) $smallest_num=$count;
			if ($count>$largest_num) $largest_num=$count;
		}

		// Scale tag sizings into em figures, and generally prepare for templating
		$max_em=2.5;
		$min_em=0.85;
		$tpl_tags=array();
		foreach ($tags as $tag=>$count)
		{
			if (!is_string($tag)) $tag=strval($tag);

			if ($smallest_num==$largest_num)
			{
				$em=1.0;
			} else
			{
				$fraction=floatval($count-$smallest_num)/floatval($largest_num);
				$em=$min_em+$fraction*($max_em-$min_em);
			}

			$tpl_tags[]=array(
				'TAG'=>$tag,
				'COUNT'=>strval($count),
				'EM'=>float_to_raw_string($em),
				'LINK'=>build_url(array('page'=>'search','type'=>'results','content'=>'"'.$tag.'"','days'=>-1,'only_search_meta'=>'1')+$search_limiter,$zone),
			);
		}

		$title=array_key_exists('title',$map)?$map['title']:do_lang('TAG_CLOUD');

		return do_template('BLOCK_SIDE_TAG_CLOUD',array('_GUID'=>'5cd3ece0f5c087fe1ce7db26d5356989','TAGS'=>$tpl_tags,'TITLE'=>$title));
	}

}


