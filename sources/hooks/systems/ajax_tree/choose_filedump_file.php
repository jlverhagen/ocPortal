<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		filedump
 */

class Hook_choose_filedump_file
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
		require_code('files2');
		require_code('images');
		$fullpath=get_custom_file_base().'/uploads/filedump';
		if ($id!='')
			$fullpath.='/'.$id;

		$out='';

		if (has_actual_page_access(NULL,'filedump'))
		{
			$files=get_directory_contents($fullpath,'',false,false);
			foreach ($files as $f)
			{
				if ((isset($options['attachment_ready'])) && ($options['attachment_ready']))
				{
					$entry_id='url_'.'uploads/filedump/'.(($id=='')?'':(rawurlencode($id).'/')).'/'.rawurlencode($f);
				} else
				{
					$entry_id='uploads/filedump/'.(($id=='')?'':(rawurlencode($id).'/')).'/'.rawurlencode($f);
				}

				if (is_dir($fullpath.'/'.$f))
				{
					$has_children=(count(get_directory_contents($fullpath.'/'.$f,'',false,false))>0);

					$out.='<category id="'.xmlentities($f).'" title="'.xmlentities($f).'" has_children="'.($has_children?'true':'false').'" selectable="false"></category>';
				} else
				{
					if ((!isset($options['only_images'])) || (!$options['only_images']) || (is_image($f)))
						$out.='<entry id="'.xmlentities($entry_id).'" title="'.xmlentities($f).'" selectable="true"></entry>';
				}
			}

			// Mark parent cats for pre-expansion
			if ((!is_null($default)) && ($default!=''))
			{
				$cat='';
				foreach (explode('/',$default) as $_cat)
				{
					if ($_cat!='')
					{
						$cat.='/';
						$cat.=$_cat;
					}
					$out.='<expand>'.$cat.'</expand>';
				}
			}
		}

		return '<result>'.$out.'</result>';
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
		$out='';

		if (has_actual_page_access(NULL,'filedump'))
		{
			require_code('images');
			require_code('files2');
			$fullpath=get_custom_file_base().'/uploads/filedump';
			if ($id!='')
				$fullpath.='/'.$id;
			$tree=get_directory_contents($fullpath,'');

			foreach ($tree as $f)
			{
				if ((!isset($options['only_images'])) || (!$options['only_images']) || (is_image($f)))
				{
					$rel=preg_replace('#^'.str_replace('#','\#',preg_quote($id)).'/#','',$f);
					$out.='<option value="'.escape_html('uploads/filedump/'.$f).'"'.(($it===$f)?' selected="selected"':'').'>'.escape_html($rel).'</option>';
				}
			}
		}
		
		return make_string_tempcode($out);
	}

}


