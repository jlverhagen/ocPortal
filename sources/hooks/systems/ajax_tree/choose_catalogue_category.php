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
 * @package		catalogues
 */

class Hook_choose_catalogue_category
{

	/**
	 * Standard modular run function for ajax-tree hooks. Generates XML for a tree list, which is interpreted by Javascript and expanded on-demand (via new calls).
	 *
	 * @param  ?ID_TEXT		The ID to do under (NULL: root)
	 * @param  array			Options being passed through
	 * @param  ?ID_TEXT		The ID to select by default (NULL: none)
	 * @return string			XML in the special category,entry format
	 */
	function run($id,$options,$default=NULL)
	{
		require_code('catalogues');
		require_lang('catalogues');

		$catalogue_name=array_key_exists('catalogue_name',$options)?$options['catalogue_name']:NULL;
		$addable_filter=array_key_exists('addable_filter',$options)?($options['addable_filter']):false;
		$compound_list=array_key_exists('compound_list',$options)?$options['compound_list']:false;
		$stripped_id=($compound_list?preg_replace('#,.*$#','',$id):$id);

		if (is_null($catalogue_name))
		{
			$tree=array();
			$catalogues=$GLOBALS['SITE_DB']->query_select('catalogues',array('c_name'));
			foreach ($catalogues as $catalogue)
			{
				$tree=array_merge($tree,get_catalogue_category_tree($catalogue['c_name'],is_null($id)?NULL:intval($id),NULL,NULL,1,$addable_filter,$compound_list));
			}
		} else
		{
			$tree=get_catalogue_category_tree($catalogue_name,is_null($id)?NULL:intval($id),NULL,NULL,1,$addable_filter,$compound_list);
		}
		if (!has_actual_page_access(NULL,'catalogues')) $tree=array();

		$out='';

		foreach ($tree as $t)
		{
			if ($compound_list)
			{
				$_id=$t['compound_list'];
			} else
			{
				$_id=strval($t['id']);
			}

			if ($stripped_id===strval($t['id'])) continue; // Possible when we look under as a root
			$title=$t['title'];
			$has_children=($t['child_count']!=0);
			$selectable=(($addable_filter!==true) || $t['addable']);

			$tag='category'; // category
			$out.='<'.$tag.' id="'.$_id.'" title="'.xmlentities($title).'" has_children="'.($has_children?'true':'false').'" selectable="'.($selectable?'true':'false').'"></'.$tag.'>';
		}

		// Mark parent cats for pre-expansion
		if ((!is_null($default)) && ($default!=''))
		{
			$cat=intval($default);
			while (!is_null($cat))
			{
				$out.='<expand>'.strval($cat).'</expand>';
				$cat=$GLOBALS['SITE_DB']->query_value_null_ok('catalogue_categories','cc_parent_id',array('id'=>$cat));
			}
		}

		$tag='result'; // result
		return '<'.$tag.'>'.$out.'</'.$tag.'>';
	}

	/**
	 * Standard modular simple function for ajax-tree hooks. Returns a normal <select> style <option>-list, for fallback purposes
	 *
	 * @param  ?ID_TEXT		The ID to do under (NULL: root) - not always supported
	 * @param  array			Options being passed through
	 * @param  ?ID_TEXT		The ID to select by default (NULL: none)
	 * @return tempcode		The nice list
	 */
	function simple($id,$options,$it=NULL)
	{
		unset($id);

		require_code('catalogues');

		$catalogue_name=array_key_exists('catalogue_name',$options)?$options['catalogue_name']:NULL;
		$addable_filter=array_key_exists('addable_filter',$options)?($options['addable_filter']):false;
		$compound_list=array_key_exists('compound_list',$options)?$options['compound_list']:false;

		if (is_null($catalogue_name))
		{
			$out='';
			$catalogues=$GLOBALS['SITE_DB']->query_select('catalogues',array('c_name'));
			foreach ($catalogues as $catalogue)
			{
				$out.=static_evaluate_tempcode(nice_get_catalogue_category_tree($catalogue['c_name'],is_null($it)?NULL:intval($it),$addable_filter,$compound_list));
			}
			return make_string_tempcode($out);
		} else
		{
			return nice_get_catalogue_category_tree($catalogue_name,is_null($it)?NULL:intval($it),$addable_filter,$compound_list);
		}
	}

}


