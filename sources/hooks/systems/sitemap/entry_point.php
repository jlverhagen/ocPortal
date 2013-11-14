<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

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

class Hook_sitemap_entry_point extends Hook_sitemap_base
{
	/**
	 * Find if a page-link will be covered by this node.
	 *
	 * @param  ID_TEXT		The page-link.
	 * @return integer		A SITEMAP_NODE_* constant.
	 */
	function handles_pagelink($pagelink)
	{
		if (preg_match('#^cms:cms_catalogues:add_catalogue:#',$pagelink))
			return SITEMAP_NODE_HANDLED;

		$matches=array();
		if (preg_match('#^([^:]*):([^:]*):([^:]*)$#',$pagelink,$matches)!=0)
		{
			$zone=$matches[1];
			$page=$matches[2];
			$type=$matches[3];

			$details=$this->_request_page_details($page,$zone);

			if ($details!==false)
			{
				$path=end($details);
				if (is_file(get_file_base().'/'.str_replace('/modules_custom/','/modules/',$path)))
					$path=str_replace('/modules_custom/','/modules/',$path);

				if ($details[0]=='MODULES' || $details[0]=='MODULES_CUSTOM')
				{
					$functions=extract_module_functions(get_file_base().'/'.$path,array('get_entry_points'),array(/*$check_perms=*/true,/*$member_id=*/NULL,/*$support_crosslinks=*/true));
					if (!is_null($functions[0]))
					{
						if (is_file(get_file_base().'/'.str_replace('/modules_custom/','/modules/',$path)))
						{
							$path=str_replace('/modules_custom/','/modules/',$path);
							$functions=extract_module_functions(get_file_base().'/'.$path,array('get_entry_points','get_wrapper_icon'),array(/*$check_perms=*/true,/*$member_id=*/NULL,/*$support_crosslinks=*/true));
						}
					}
					if (!is_null($functions[0]))
					{
						$entry_points=is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);

						if (isset($entry_points['misc']))
						{
							unset($entry_points['misc']);
						} else
						{
							array_shift($entry_points);
						}

						if (isset($entry_points[$type]))
							return SITEMAP_NODE_HANDLED;
					}
				}
			}
		}
		return SITEMAP_NODE_NOT_HANDLED;
	}

	/**
	 * Find details of a position in the Sitemap.
	 *
	 * @param  ID_TEXT  		The page-link we are finding.
	 * @param  ?string  		Callback function to send discovered page-links to (NULL: return).
	 * @param  ?array			List of node types we will return/recurse-through (NULL: no limit)
	 * @param  ?integer		How deep to go from the Sitemap root (NULL: no limit).
	 * @param  integer		Our recursion depth (used to limit recursion, or to calculate importance of page-link, used for instance by XML Sitemap [deeper is typically less important]).
	 * @param  boolean		Only go so deep as needed to find nodes with permission-support (typically, stopping prior to the entry-level).
	 * @param  ID_TEXT		The zone we will consider ourselves to be operating in (needed due to transparent redirects feature)
	 * @param  boolean		Whether to make use of page groupings, to organise stuff with the hook schema, supplementing the default zone organisation.
	 * @param  boolean		Whether to filter out non-validated content.
	 * @param  boolean		Whether to consider secondary categorisations for content that primarily exists elsewhere.
	 * @param  integer		A bitmask of SITEMAP_GATHER_* constants, of extra data to include.
	 * @param  ?array			Database row (NULL: lookup).
	 * @param  boolean		Whether to return the structure even if there was a callback. Do not pass this setting through via recursion due to memory concerns, it is used only to gather information to detect and prevent parent/child duplication of default entry points.
	 * @return ?array			Node structure (NULL: working via callback / error).
	 */
	function get_node($pagelink,$callback=NULL,$valid_node_types=NULL,$child_cutoff=NULL,$max_recurse_depth=NULL,$recurse_level=0,$require_permission_support=false,$zone='_SEARCH',$use_page_groupings=false,$consider_secondary_categories=false,$consider_validation=false,$meta_gather=0,$row=NULL,$return_anyway=false)
	{
		$matches=array();
		preg_match('#^([^:]*):([^:]*):([^:]*)(:.*|$)#',$pagelink,$matches);
		$page=$matches[2];
		$type=$matches[3];

		$orig_pagelink=$pagelink;
		$this->_make_zone_concrete($zone,$pagelink);

		$details=$this->_request_page_details($page,$zone);

		$path=end($details);

		if (($type=='add_catalogue') && ($matches[4]!='') && ($matches[4][1]=='_'))
		{
			require_code('fields');
			$entry_points=manage_custom_fields_entry_points(substr($matches[4],2));
			$entry_point=$entry_points[$orig_pagelink];
		} else
		{
			$functions=extract_module_functions(get_file_base().'/'.$path,array('get_entry_points'),array(/*$check_perms=*/true,/*$member_id=*/NULL,/*$support_crosslinks=*/true));
			if (is_null($functions[0]))
			{
				if (is_file(get_file_base().'/'.str_replace('/modules_custom/','/modules/',$path)))
				{
					$path=str_replace('/modules_custom/','/modules/',$path);
					$functions=extract_module_functions(get_file_base().'/'.$path,array('get_entry_points','get_wrapper_icon'),array(/*$check_perms=*/true,/*$member_id=*/NULL,/*$support_crosslinks=*/true));
				}
			}

			$entry_points=is_array($functions[0])?call_user_func_array($functions[0][0],$functions[0][1]):eval($functions[0]);

			if ($matches[4]=='')
			{
				$entry_point=$entry_points[$type];
			} else
			{
				$entry_point=$entry_points[$orig_pagelink];
			}
		}

		$icon=mixed();
		if (is_array($entry_point))
		{
			$_title=$entry_point[0];
			$icon=$entry_point[1];
		} else
		{
			$_title=$entry_point;
		}
		$title=(preg_match('#^[A-Z\_]+$#',$_title)==0)?make_string_tempcode($_title):do_lang_tempcode($_title);

		$struct=array(
			'title'=>$title,
			'content_type'=>'page',
			'content_id'=>$zone,
			'pagelink'=>$pagelink,
			'extra_meta'=>array(
				'description'=>NULL,
				'image'=>($icon===NULL)?NULL:find_theme_image('icons/24x24/'.$icon),
				'image_2x'=>($icon===NULL)?NULL:find_theme_image('icons/48x48/'.$icon),
				'add_date'=>(($meta_gather & SITEMAP_GATHER_TIMES)!=0)?filectime(get_file_base().'/'.$path):NULL,
				'edit_date'=>(($meta_gather & SITEMAP_GATHER_TIMES)!=0)?filemtime(get_file_base().'/'.$path):NULL,
				'submitter'=>NULL,
				'views'=>NULL,
				'rating'=>NULL,
				'meta_keywords'=>NULL,
				'meta_description'=>NULL,
				'categories'=>NULL,
				'validated'=>NULL,
				'db_row'=>NULL,
			),
			'permissions'=>array(
				array(
					'type'=>'zone',
					'zone_name'=>$zone,
					'is_owned_at_this_level'=>false,
				),
				array(
					'type'=>'page',
					'zone_name'=>$zone,
					'page_name'=>$page,
					'is_owned_at_this_level'=>false,
				),
			),
			'children'=>NULL,
			'has_possible_children'=>false,

			// These are likely to be changed in individual hooks
			'sitemap_priority'=>SITEMAP_IMPORTANCE_MEDIUM,
			'sitemap_refreshfreq'=>'monthly',

			'permission_page'=>NULL,
		);

		if (!$this->_check_node_permissions($struct)) return NULL;

		if ($callback!==NULL)
			call_user_func($callback,$struct);

		return ($callback===NULL || $return_anyway)?$struct:NULL;
	}
}
