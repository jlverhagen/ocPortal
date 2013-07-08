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
 * Get list of staff contextual actions.
 *
 * @return string		The list
 */
function get_staff_actions_list()
{
	require_lang('lang');
	$list=array(
		'view'=>do_lang_tempcode('SCREEN_DEV_TOOLS'),
	);
	$list+=array(
		'spacer_1'=>do_lang_tempcode('THEME'),
			'show_edit_links'=>do_lang_tempcode('TEMPLATES_WITH_EDIT_LINKS'),
			'show_markers'=>do_lang_tempcode('TEMPLATES_WITH_HTML_COMMENT_MARKERS'),
			'tree'=>do_lang_tempcode('TEMPLATE_TREE'),
			'templates'=>do_lang_tempcode('TEMPLATES'),
			'theme_images'=>do_lang_tempcode('THEME_IMAGE_EDITING'),
			'code'=>do_lang_tempcode('VALIDATION'),
			'site_tree'=>do_lang_tempcode('FIND_IN_SITE_TREE'),
	);
	if (get_param_integer('keep_no_minify',0)==0) // When minification on we need to hard-code CSS list as cannot be auto-detected
	{
		$is_admin=$GLOBALS['FORUM_DRIVER']->is_super_admin(get_member());
		$zone_name=get_zone_name();
		$grouping_codename='merged__';
		$grouping_codename.=$zone_name;
		if ($is_admin) $grouping_codename.='__admin';
		$value=get_value_newer_than($grouping_codename.'.css',time()-60*60*24);
		if ($value!==NULL)
		{
			$_value=explode('::',$value);
			$resources=explode(',',$_value[0]);
			foreach ($resources as $resource)
			{
				$list[$resource.'.css']=($resource=='global')?do_lang_tempcode('CONTEXTUAL_CSS_EDITING_GLOBAL','global.css'):do_lang_tempcode('CONTEXTUAL_CSS_EDITING',escape_html($resource.'.css'));
			}
		}
	}
	require_code('lang2');
	$list+=array(
		'spacer_2'=>do_lang_tempcode('LANGUAGE'),
	);
	$all_langs=multi_lang()?find_all_langs():array(user_lang()=>'lang_custom');
	$tcode=do_lang('lang:TRANSLATE_CODE');
	foreach (array_keys($all_langs) as $lang)
	{
		$list+=array(
			'lang_'.$lang=>$tcode.((count($all_langs)==1)?'':(': '.lookup_language_full_name($lang))),
		);
	}
	if (multi_lang())
	{
		$tcontent=do_lang('TRANSLATE_CONTENT');
		foreach (array_keys($all_langs) as $lang)
		{
			$list['lang_content_'.$lang]=$tcontent.': '.lookup_language_full_name($lang);
		}
	}
	$list+=array(
		'spacer_3'=>do_lang_tempcode('DEVELOPMENT_VIEWS'),
			'query'=>do_lang_tempcode('VIEW_PAGE_QUERIES'),
			'ide_linkage'=>do_lang_tempcode('IDE_LINKAGE'),
	);
	if (function_exists('xdebug_enable'))
	{
		$list['profile']=do_lang_tempcode('PROFILING');
	}
	if (function_exists('memory_get_usage'))
	{
		$list['memory']=do_lang_tempcode('_MEMORY_USAGE');
	}
	$special_page_type=get_param('special_page_type','view');
	$staff_actions='';
	$started_opt_group=false;
	foreach ($list as $name=>$text)
	{
		$is_group=(($name[0]=='s') && (substr($name,0,7)=='spacer_'));
		if ($is_group)
		{
			if ($started_opt_group) $staff_actions.='</optgroup>';
			$staff_actions.='<optgroup label="'.(is_object($text)?$text->evaluate():escape_html($text)).'">';
			$started_opt_group=true;
		}
		$staff_actions.='<option'.(($staff_actions=='')?' disabled="disabled" class="label"':'').' '.(($name==$special_page_type)?'selected="selected" ':'').'value="'.escape_html($name).'">'.(is_object($text)?$text->evaluate():escape_html($text)).'</option>'; // XHTMLXHTML
		//$staff_actions.=static_evaluate_tempcode(form_input_list_entry($name,($name==$special_page_type),$text,false,$disabled));	Disabled 'proper' way for performance reasons
	}
	if ($started_opt_group) $staff_actions.='</optgroup>';
	return $staff_actions;
}

/**
 * A page is not validated, so show a warning.
 *
 * @param  ID_TEXT		The zone the page is being loaded from
 * @param  ID_TEXT		The codename of the page
 * @param  tempcode		The edit URL (blank if no edit access)
 * @return tempcode		The warning
 */
function get_page_warning_details($zone,$codename,$edit_url)
{
	$warning_details=new ocp_tempcode();
	if ((!has_privilege(get_member(),'jump_to_unvalidated')) && (addon_installed('unvalidated')))
		access_denied('PRIVILEGE','jump_to_unvalidated');
	$uv_warning=do_lang_tempcode((get_param_integer('redirected',0)==1)?'UNVALIDATED_TEXT_NON_DIRECT':'UNVALIDATED_TEXT'); // Wear sun cream
	if (!$edit_url->is_empty())
	{
		$menu_links=$GLOBALS['SITE_DB']->query('SELECT DISTINCT i_menu FROM '.get_table_prefix().'menu_items WHERE '.db_string_equal_to('i_url',$zone.':'.$codename).' OR '.db_string_equal_to('i_url','_SEARCH:'.$codename));
		if (count($menu_links)!=0)
		{
			$menu_items_linking=new ocp_tempcode();;
			foreach ($menu_links as $menu_link)
			{
				if (!$menu_items_linking->is_empty()) $menu_items_linking->attach(do_lang_tempcode('LIST_SEP'));
				$menu_edit_url=build_url(array('page'=>'admin_menus','type'=>'edit','id'=>$menu_link['i_menu']),get_module_zone('admin_menus'));
				$menu_items_linking->attach(hyperlink($menu_edit_url,$menu_link['i_menu'],false,true));
			}
			$uv_warning=do_lang_tempcode('UNVALIDATED_TEXT_STAFF',$menu_items_linking);
		}
	}
	$warning_details->attach(do_template('WARNING_BOX',array('_GUID'=>'ee79289f87986bcb916a5f1810a25330','WARNING'=>$uv_warning)));
	return $warning_details;
}

/**
 * Assign a page refresh to the specified URL.
 *
 * @sets_output_state
 *
 * @param  mixed			Refresh to this URL (URLPATH or Tempcode URL)
 * @param  float			Take this many times longer than a 'standard ocPortal refresh'
 */
function assign_refresh($url,$multiplier=0.0)
{
	if (is_object($url)) $url=$url->evaluate();

	if (strpos($url,'keep_session')!==false) $url=enforce_sessioned_url($url); // In case the session changed in transit (this refresh URL may well have been relayed from a much earlier point)

	$special_page_type=get_param('special_page_type','view');

	$must_show_message=($multiplier!=0.0);

	if (!$must_show_message)
	{
		// Preferably server is gonna redirect before page is shown. This is for accessibility reasons
		if ((strpos($url,chr(10))!==false) || (strpos($url,chr(13))!==false))
			log_hack_attack_and_exit('HEADER_SPLIT_HACK');

		global $FORCE_META_REFRESH;
		if (($special_page_type=='view') && (running_script('index')) && (!headers_sent()) && (!$FORCE_META_REFRESH))
		{
			header('Location: '.$url);
			if (strpos($url,'#')===false)
				$GLOBALS['QUICK_REDIRECT']=true;
		}
	}

	if ($special_page_type=='view')
	{
		global $REFRESH_URL;
		$REFRESH_URL[0]=$url;
		$REFRESH_URL[1]=2.5*$multiplier;
	}
}

/**
 * Render the site as closed.
 */
function closed_site()
{
	if ((get_page_name()!='login') && (get_page_name()!='join') && (get_page_name()!='lost_password'))
	{
		@ob_end_clean();

		if (!headers_sent())
		{
			if ((!browser_matches('ie')) && (strpos(ocp_srv('SERVER_SOFTWARE'),'IIS')===false)) header('HTTP/1.0 503 Service Temporarily Unavailable');
		}

		log_stats('/closed',0);

		$GLOBALS['SCREEN_TEMPLATE_CALLED']='';

		if (count($_POST)>0)
		{
			$redirect=build_url(array('page'=>''),'',array('keep_session'=>1));
		} else
		{
			$redirect=build_url(array('page'=>'_SELF'),'_SELF',array('keep_session'=>1),true);
		}
		if (is_object($redirect)) $redirect=$redirect->evaluate();
		$login_url=build_url(array('page'=>'login','type'=>'misc','redirect'=>$redirect),get_module_zone('login'));
		$join_url=(get_forum_type()=='none')?'':$GLOBALS['FORUM_DRIVER']->join_url();
		$middle=do_template('CLOSED_SITE',array('_GUID'=>'4e753c50eca7c98344d2107fc18c4554','CLOSED'=>comcode_to_tempcode(get_option('closed'),NULL,true),'LOGIN_URL'=>$login_url,'JOIN_URL'=>$join_url));
		$echo=globalise($middle,NULL,'',true);
		$echo->evaluate_echo();
		exit();
	}
}

/**
 * Render that the page wasn't found. Show alternate likely candidates based on misspellings.
 *
 * @param  ID_TEXT		The codename of the page to load
 * @param  ID_TEXT		The zone the page is being loaded in
 * @return tempcode		Message
 */
function page_not_found($codename,$zone)
{
	set_http_status_code('404');

	// Maybe problem with SEO URLs
	$url_scheme=get_option('url_scheme');
	if ((get_zone_name()=='') && ((($url_scheme=='HTM') || ($url_scheme=='SIMPLE'))) && (has_zone_access(get_member(),'adminzone')))
	{
		$self_url=get_self_url_easy();
		$zones=find_all_zones();
		foreach ($zones as $_zone)
		{
			if (($_zone!='') && ($_zone!='site') && (strpos($self_url,'/'.$_zone.'/')!==false))
			{
				attach_message(do_lang_tempcode('HTACCESS_SEO_PROBLEM'),'warn');
			}
		}
	}

	// "Did you mean?" support
	$all_pages_in_zone=array_keys(find_all_pages_wrap($zone));
	$did_mean=array();
	foreach ($all_pages_in_zone as $possibility)
	{
		if (is_integer($possibility)) $possibility=strval($possibility); // e.g. '404' page has been converted to integer by PHP, grr

		$from=str_replace('cms_','',str_replace('admin_','',$possibility));
		$to=str_replace('cms_','',str_replace('admin_','',$codename));
		//$dist=levenshtein($from,$to);  If we use this, change > to < also
		//$threshold=4;
		$dist=0.0;
		similar_text($from,$to,$dist);
		$threshold=75.0;
		if (($dist>$threshold) && (has_page_access(get_member(),$codename,$zone)))
			$did_mean[$dist]=$possibility;
	}
	ksort($did_mean);
	$_did_mean=array_pop($did_mean);
	if ($_did_mean=='') $_did_mean=NULL;

	require_code('global4');
	if ((ocp_srv('HTTP_REFERER')!='') && (!handle_has_checked_recently('request-'.$zone.':'.$codename)))
	{
		require_code('failure');
		relay_error_notification(do_lang('_MISSING_RESOURCE',$zone.':'.$codename).' '.do_lang('REFERRER',ocp_srv('HTTP_REFERER'),substr(get_browser_string(),0,255)),false,'error_occurred_missing_page');
	}

	$title=get_screen_title('ERROR_OCCURRED');
	$add_access=has_add_comcode_page_permission($zone);
	$redirect_access=addon_installed('redirects_editor') && has_actual_page_access(get_member(),'admin_redirects');
	require_lang('zones');
	$add_url=$add_access?build_url(array('page'=>'cms_comcode_pages','type'=>'_ed','page_link'=>$zone.':'.$codename),get_module_zone('cms_comcode_pages')):new ocp_tempcode();
	$add_redirect_url=$redirect_access?build_url(array('page'=>'admin_redirects','type'=>'misc','page_link'=>$zone.':'.$codename),get_module_zone('admin_redirects')):new ocp_tempcode();
	return do_template('MISSING_SCREEN',array('_GUID'=>'22f371577cd2ba437e7b0cb241931575','TITLE'=>$title,'DID_MEAN'=>$_did_mean,'ADD_URL'=>$add_url,'ADD_REDIRECT_URL'=>$add_redirect_url,'PAGE'=>$codename));
}

/**
 * Load Comcode page from disk, then cache it.
 *
 * @param  PATH			The relative (to ocPortal's base directory) path to the page (e.g. pages/comcode/EN/start.txt)
 * @param  ID_TEXT		The zone the page is being loaded from
 * @param  ID_TEXT		The codename of the page
 * @param  PATH			The file base to load from
 * @param  ?array			Row from database (holds submitter etc) (NULL: no row, originated first from disk)
 * @param  array			New row for database, used if necessary (holds submitter etc)
 * @param  boolean		Whether the page is being included from another
 * @return array			A tuple: The page HTML (as Tempcode), New Comcode page row, Title, Raw Comcode
 */
function _load_comcode_page_not_cached($string,$zone,$codename,$file_base,$comcode_page_row,$new_comcode_page_row,$being_included=false)
{
	global $COMCODE_PARSE_TITLE;

	$nql_backup=$GLOBALS['NO_QUERY_LIMIT'];
	$GLOBALS['NO_QUERY_LIMIT']=true;

	// Not cached :(
	$result=file_get_contents($file_base.'/'.$string);
	apply_comcode_page_substitutions($result);
	$result=fix_bad_unicode($result);

	if (is_null($new_comcode_page_row['p_submitter']))
	{
		$as_admin=true;
		$members=$GLOBALS['FORUM_DRIVER']->member_group_query($GLOBALS['FORUM_DRIVER']->get_super_admin_groups(),1);
		if (count($members)!=0)
		{
			$new_comcode_page_row['p_submitter']=$GLOBALS['FORUM_DRIVER']->mrow_id($members[key($members)]);
		} else
		{
			$new_comcode_page_row['p_submitter']=db_get_first_id()+1; // On OCF and most forums, this is the first admin member
		}
	}

	if (is_null($comcode_page_row)) // Default page. We need to find an admin to assign it to.
	{
		$page_submitter=$new_comcode_page_row['p_submitter'];
	} else
	{
		$as_admin=false; // Will only have admin privileges if $page_submitter has them
		$page_submitter=$comcode_page_row['p_submitter'];
	}
	if (is_null($page_submitter)) $page_submitter=get_member();

	// Parse and work out how to add
	$lang=user_lang();
	global $LAX_COMCODE;
	$temp=$LAX_COMCODE;
	$LAX_COMCODE=true;
	require_code('attachments2');
	$_new=do_comcode_attachments($result,'comcode_page',$zone.':'.$codename,false,NULL,$as_admin/*Ideally we assign $page_submitter based on this as well so it is safe if the Comcode cache is emptied*/,$page_submitter);
	$_text2=$_new['tempcode'];
	$LAX_COMCODE=$temp;
	$text2=$_text2->to_assembly();

	// Check it still needs inserting (it might actually be there, but not translated)
	$trans_key=$GLOBALS['SITE_DB']->query_select_value_if_there('cached_comcode_pages','string_index',array('the_page'=>$codename,'the_zone'=>$zone,'the_theme'=>$GLOBALS['FORUM_DRIVER']->get_theme()));
	if (is_null($COMCODE_PARSE_TITLE)) $COMCODE_PARSE_TITLE='';
	$title_to_use=clean_html_title($COMCODE_PARSE_TITLE);
	if (is_null($trans_key))
	{
		$index=$GLOBALS['SITE_DB']->query_insert('translate',array('source_user'=>$page_submitter,'broken'=>0,'importance_level'=>1,'text_original'=>$result,'text_parsed'=>$text2,'language'=>$lang),true,false,true);
		$GLOBALS['SITE_DB']->query_insert('cached_comcode_pages',array('the_zone'=>$zone,'the_page'=>$codename,'string_index'=>$index,'the_theme'=>$GLOBALS['FORUM_DRIVER']->get_theme(),'cc_page_title'=>insert_lang(clean_html_title($COMCODE_PARSE_TITLE),1,NULL,false,NULL,NULL,false,NULL,NULL,60,true,true)),false,true); // Race conditions
		decache('main_comcode_page_children');

		// Try and insert corresponding page; will silently fail if already exists. This is only going to add a row for a page that was not created in-system
		if (is_null($comcode_page_row))
		{
			$comcode_page_row=$new_comcode_page_row;
			$GLOBALS['SITE_DB']->query_insert('comcode_pages',$comcode_page_row,false,true);

			if (addon_installed('content_reviews'))
			{
				require_code('content_reviews');
				schedule_content_review('comcode_page',$zone.':'.$codename,intval(get_option('comcode_page_default_review_freq')));
			}
		}
	} else
	{
		$_comcode_page_row=$GLOBALS['SITE_DB']->query_select('comcode_pages',array('*'),array('the_zone'=>$zone,'the_page'=>$codename),'',1);
		if (array_key_exists(0,$_comcode_page_row))
		{
			$comcode_page_row=$_comcode_page_row[0];
		} else
		{
			$comcode_page_row=$new_comcode_page_row;
			$GLOBALS['SITE_DB']->query_insert('comcode_pages',$comcode_page_row,false,true);

			if (addon_installed('content_reviews'))
			{
				require_code('content_reviews');
				schedule_content_review('comcode_page',$zone.':'.$codename,intval(get_option('comcode_page_default_review_freq')));
			}
		}

		// Check to see if it needs translating
		$test=$GLOBALS['SITE_DB']->query_select_value_if_there('translate','id',array('id'=>$trans_key,'language'=>$lang));
		if (is_null($test))
		{
			$GLOBALS['SITE_DB']->query_insert('translate',array('id'=>$trans_key,'source_user'=>$page_submitter,'broken'=>0,'importance_level'=>1,'text_original'=>$result,'text_parsed'=>$text2,'language'=>$lang),false,true);
			$index=$trans_key;

			$trans_cc_page_title_key=$GLOBALS['SITE_DB']->query_select_value('cached_comcode_pages','cc_page_title',array('the_page'=>$codename,'the_zone'=>$zone,'the_theme'=>$GLOBALS['FORUM_DRIVER']->get_theme()));
			$test=$GLOBALS['SITE_DB']->query_select_value_if_there('translate','id',array('id'=>$trans_cc_page_title_key,'language'=>$lang));
			if (is_null($test))
			{
				$GLOBALS['SITE_DB']->query_insert('translate',array('id'=>$trans_cc_page_title_key,'source_user'=>$page_submitter,'broken'=>0,'importance_level'=>1,'text_original'=>$title_to_use,'text_parsed'=>'','language'=>$lang),true);
			}
		}
	}

	$GLOBALS['NO_QUERY_LIMIT']=$nql_backup;

	return array($_text2,$title_to_use,$comcode_page_row,$result);
}

/**
 * If any Comcode substitutions are configured, apply them.
 *
 * @param  string			The Comcode page contents
 */
function apply_comcode_page_substitutions(&$result)
{
	global $SITE_INFO;
	if (isset($SITE_INFO['reps']))
	{
		foreach ($SITE_INFO['reps'] as $search=>$replace)
		{
			$result=str_replace($search,$replace,$result);
		}
	}
}

/**
 * Load Comcode page from disk.
 *
 * @param  PATH			The relative (to ocPortal's base directory) path to the page (e.g. pages/comcode/EN/start.txt)
 * @param  ID_TEXT		The zone the page is being loaded from
 * @param  ID_TEXT		The codename of the page
 * @param  PATH			The file base to load from
 * @param  array			New row for database, used if nesessary (holds submitter etc)
 * @param  boolean		Whether the page is being included from another
 * @return array			A tuple: The page HTML (as Tempcode), New Comcode page row, Title, Raw Comcode
 */
function _load_comcode_page_cache_off($string,$zone,$codename,$file_base,$new_comcode_page_row,$being_included=false)
{
	global $COMCODE_PARSE_TITLE;

	if (is_null($new_comcode_page_row['p_submitter']))
	{
		$as_admin=true;
		$members=$GLOBALS['FORUM_DRIVER']->member_group_query($GLOBALS['FORUM_DRIVER']->get_super_admin_groups(),1);
		if (count($members)!=0)
		{
			$new_comcode_page_row['p_submitter']=$GLOBALS['FORUM_DRIVER']->mrow_id($members[key($members)]);
		} else
		{
			$new_comcode_page_row['p_submitter']=db_get_first_id()+1; // On OCF and most forums, this is the first admin member
		}
	}

	$_comcode_page_row=$GLOBALS['SITE_DB']->query_select('comcode_pages',array('*'),array('the_zone'=>$zone,'the_page'=>$codename),'',1);

	$comcode=file_get_contents($file_base.'/'.$string);
	apply_comcode_page_substitutions($comcode);

	global $LAX_COMCODE;
	$temp=$LAX_COMCODE;
	$LAX_COMCODE=true;
	require_code('attachments2');
	$_new=do_comcode_attachments($comcode,'comcode_page',$zone.':'.$codename,false,NULL,(!array_key_exists(0,$_comcode_page_row)) || (is_guest($_comcode_page_row[0]['p_submitter'])),array_key_exists(0,$_comcode_page_row)?$_comcode_page_row[0]['p_submitter']:get_member());
	$html=$_new['tempcode'];
	$LAX_COMCODE=$temp;
	$title_to_use=is_null($COMCODE_PARSE_TITLE)?NULL:clean_html_title($COMCODE_PARSE_TITLE);

	// Try and insert corresponding page; will silently fail if already exists. This is only going to add a row for a page that was not created in-system
	if (array_key_exists(0,$_comcode_page_row))
	{
		$comcode_page_row=$_comcode_page_row[0];
	} else
	{
		$comcode_page_row=$new_comcode_page_row;
		$GLOBALS['SITE_DB']->query_insert('comcode_pages',$comcode_page_row,false,true);

		if (addon_installed('content_reviews'))
		{
			require_code('content_reviews');
			schedule_content_review('comcode_page',$zone.':'.$codename,intval(get_option('comcode_page_default_review_freq')));
		}
	}

	return array($html,$comcode_page_row,$title_to_use,$comcode);
}

/**
 * Turn an HTML title, which could be complex with images, into a nice simple string we can use in <title> and ;.
 *
 * @param  string			The relative (to ocPortal's base directory) path to the page (e.g. pages/comcode/EN/start.txt)
 * @return string			Fixed
 */
function clean_html_title($title)
{
	$_title=trim(strip_tags($title));
	if ($_title=='') // Complex case
	{
		$matches=array();
		if (preg_match('#<img[^>]*alt="([^"]+)"#',$title,$matches)!=0)
		{
			return $matches[1];
		}
		return $title;
	}
	return $_title;
}
