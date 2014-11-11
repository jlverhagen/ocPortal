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
 * @package		core_ocf
 */

/**
 * Standard code module initialisation function.
 */
function init__ocf_groups()
{
	global $USER_GROUPS_CACHED;
	$USER_GROUPS_CACHED=array();

	global $GROUP_MEMBERS_CACHE;
	$GROUP_MEMBERS_CACHE=array();

	global $PROBATION_GROUP;
	$PROBATION_GROUP=NULL;

	global $ALL_DEFAULT_GROUPS;
	$ALL_DEFAULT_GROUPS=array();
}

/**
 * Get a nice list for selection from the usergroups. Suitable for admin use only (does not check hidden status).
 *
 * @param  ?AUTO_LINK	Usergroup selected by default (NULL: no specific default).
 * @return tempcode		The list.
 */
function ocf_nice_get_usergroups($it=NULL)
{
	$group_count=$GLOBALS['FORUM_DB']->query_value('f_groups','COUNT(*)');
	$_m=$GLOBALS['FORUM_DB']->query_select('f_groups',array('id','g_name'),($group_count>200)?array('g_is_private_club'=>0):NULL,'ORDER BY g_order');
	$entries=new ocp_tempcode();
	foreach ($_m as $m)
	{
		$entries->attach(form_input_list_entry(strval($m['id']),$it===$m['id'],get_translated_text($m['g_name'],$GLOBALS['FORUM_DB'])));
	}

	return $entries;
}

/**
 * Find the first default group.
 *
 * @return GROUP 		The first default group.
 */
function get_first_default_group()
{
	$default_groups=ocf_get_all_default_groups(true);
	return array_pop($default_groups);
}

/**
 * Get a list of the default usergroups (the usergroups a member is put in when they join).
 *
 * @param  boolean	Whether to include the default primary (at the end of the list).
 * @param  boolean	The functionality does not usually consider configured default groups [unless there's just one], because this is a layer of uncertainity (the user PICKS one of these). If you want to return all configured default groups, set this parameter to true.
 * @return array 		The list of default IDs.
 */
function ocf_get_all_default_groups($include_primary=false,$include_all_configured_default_groups=false)
{
	if ((!$include_primary) && ($include_all_configured_default_groups))
		warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

	global $ALL_DEFAULT_GROUPS;
	if (array_key_exists($include_primary?1:0,$ALL_DEFAULT_GROUPS)) return $ALL_DEFAULT_GROUPS[$include_primary?1:0];

	$rows=$GLOBALS['FORUM_DB']->query_select('f_groups',array('id'),array('g_is_default'=>1,'g_is_presented_at_install'=>0),'ORDER BY g_order');
	$groups=collapse_1d_complexity('id',$rows);

	if ($include_primary)
	{
		$rows=$GLOBALS['FORUM_DB']->query_select('f_groups',array('id'),array('g_is_presented_at_install'=>1),'ORDER BY g_order');
		if (($include_all_configured_default_groups) || (count($rows)==1) || (get_option('show_first_join_page')=='0')) // If just 1 then we won't have presented a choice on the join form, so should inject that 1 as the default group as it is implied
		{
			$groups=array_merge($groups,collapse_1d_complexity('id',$rows));
		}

		if (count($rows)==0)
		{
			$test=$GLOBALS['FORUM_DB']->query_value_null_ok('f_groups','id',array('id'=>db_get_first_id()+8));
			if (!is_null($test))
				$groups[]=db_get_first_id()+8;
		}
	}

	$ALL_DEFAULT_GROUPS[$include_primary?1:0]=$groups;
	return $groups;
}

/**
 * Ensure a list of usergroups are cached in memory.
 *
 * @param  mixed	The list of usergroups (array) or '*'.
 */
function ocf_ensure_groups_cached($groups)
{
	global $USER_GROUPS_CACHED;

	if ($groups==='*')
	{
		$group_count=$GLOBALS['FORUM_DB']->query_value('f_groups','COUNT(*)');
		$rows=$GLOBALS['FORUM_DB']->query_select('f_groups',array('*'),($group_count>200)?array('g_is_private_club'=>0):NULL);
		foreach ($rows as $row)
		{
			$row['g__name']=get_translated_text($row['g_name'],$GLOBALS['FORUM_DB']);
			$row['g__title']=get_translated_text($row['g_title'],$GLOBALS['FORUM_DB']);
			$USER_GROUPS_CACHED[$row['id']]=$row;
		}
		return;
	}

	$groups_to_load='';
	$counter=0;
	foreach ($groups as $group)
	{
		if (!array_key_exists($group,$USER_GROUPS_CACHED))
		{
			if ($groups_to_load!='') $groups_to_load.=' OR ';
			$groups_to_load.='g.id='.strval($group);
			$counter++;
		}
	}
	if ($counter==0) return;
	$extra_groups=$GLOBALS['FORUM_DB']->query('SELECT g.* FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups g WHERE '.$groups_to_load,NULL,NULL,false,false,array('g_name','g_title'));

	if (count($extra_groups)!=$counter) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));

	//require_code('lang');
	foreach ($extra_groups as $extra_group)
	{
		if (function_exists('get_translated_text'))
		{
			$extra_group['g__name']=get_translated_text($extra_group['g_name'],$GLOBALS['FORUM_DB']);
			$extra_group['g__title']=get_translated_text($extra_group['g_title'],$GLOBALS['FORUM_DB']);
		}

		$USER_GROUPS_CACHED[$extra_group['id']]=$extra_group;
	}
}

/**
 * Get a rendered link to a usergroup.
 *
 * @param  GROUP		The ID of the group.
 * @return tempcode	The link.
 */
function ocf_get_group_link($id)
{
	$_row=$GLOBALS['FORUM_DB']->query_select('f_groups',array('*'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$_row)) return make_string_tempcode(do_lang('UNKNOWN'));
	$row=$_row[0];

	if ($row['id']==db_get_first_id()) return make_string_tempcode(escape_html(get_translated_text($row['g_name'],$GLOBALS['FORUM_DB'])));

	$name=ocf_get_group_name($row['id']);

	$see_hidden=has_specific_permission(get_member(),'see_hidden_groups');
	if ((!$see_hidden) && ($row['g_hidden']==1))
	{
		return make_string_tempcode(escape_html($name));
	}

	return hyperlink(build_url(array('page'=>'groups','type'=>'view','id'=>$row['id']),get_module_zone('groups')),$name,false,true);
}

/**
 * Get a usergroup name.
 *
 * @param  GROUP		The ID of the group.
 * @return string		The usergroup name.
 */
function ocf_get_group_name($group)
{
	$name=ocf_get_group_property($group,'name');
	if (is_string($name)) return $name;
	return get_translated_text($name,$GLOBALS['FORUM_DB']);
}

/**
 * Get a certain property of a certain.
 *
 * @param  GROUP		The ID of the group.
 * @param  ID_TEXT	The identifier of the property.
 * @return mixed		The property value.
 */
function ocf_get_group_property($group,$property)
{
	ocf_ensure_groups_cached(array($group));
	global $USER_GROUPS_CACHED;

	if (($property=='name') && ($USER_GROUPS_CACHED[$group]['g_hidden']==1) && (!has_specific_permission(get_member(),'see_hidden_groups')))
	{
		return do_lang('UNKNOWN');
	}

	return $USER_GROUPS_CACHED[$group]['g_'.$property];
}

/**
 * Get the best value of all values of a property for a member (due to members being in multiple usergroups).
 *
 * @param  MEMBER		The ID of the member.
 * @param  ID_TEXT	The identifier of the property.
 * @return mixed		The property value.
 */
function ocf_get_member_best_group_property($member_id,$property)
{
	return ocf_get_best_group_property($GLOBALS['OCF_DRIVER']->get_members_groups($member_id,false,true),$property);
}

/**
 * Get the best value of all values of a property for a list of usergroups.
 *
 * @param  array		The list of usergroups.
 * @param  ID_TEXT	The identifier of the property.
 * @return mixed		The best property value ('best' is dependant on the property we are looking at).
 */
function ocf_get_best_group_property($groups,$property)
{
	$big_is_better=array('gift_points_per_day','gift_points_base','enquire_on_new_ips','is_super_admin','is_super_moderator','max_daily_upload_mb','max_attachments_per_post','max_avatar_width','max_avatar_height','max_post_length_comcode','max_sig_length_comcode');
	//$small_and_perfectly_formed=array('flood_control_submit_secs','flood_control_access_secs'); Not needed by elimination, but nice to have here as a note

	$go_super_size=in_array($property,$big_is_better);

	global $USER_GROUPS_CACHED;
	ocf_ensure_groups_cached($groups);
	$best_value_so_far=0; // Initialise type to integer
	$best_value_so_far=NULL;
	foreach ($groups as $group)
	{
		$this_value=$USER_GROUPS_CACHED[$group]['g_'.$property];
		if ((is_null($best_value_so_far)) ||
			(($best_value_so_far<$this_value) && ($go_super_size)) ||
			(($best_value_so_far>$this_value) && (!$go_super_size)))
				$best_value_so_far=$this_value;
	}
	return $best_value_so_far;
}

/**
 * Get a list of the usergroups a member is in (keys say the usergroups, values are irrelevant).
 *
 * @param  ?MEMBER	The member to find the usergroups of (NULL: current member).
 * @param  boolean	Whether to skip looking at secret usergroups.
 * @param  boolean	Whether to take probation into account
 * @return array		Reverse list (e.g. array(1=>1,2=>1,3=>1) for someone in (1,2,3)).
 */
function ocf_get_members_groups($member_id=NULL,$skip_secret=false,$handle_probation=true)
{
	if (is_guest($member_id))
	{
		$ret=array();
		$ret[db_get_first_id()]=1;
		return $ret;
	}

	if (is_null($member_id)) $member_id=get_member();

	if ($handle_probation)
	{
		$opt=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_on_probation_until');
		if ((!is_null($opt)) && ($opt>time()))
		{
			global $PROBATION_GROUP;
			if (is_null($PROBATION_GROUP))
			{
				$probation_group=get_option('probation_usergroup');
				$PROBATION_GROUP=$GLOBALS['FORUM_DB']->query_value_null_ok('f_groups g LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'translate t ON t.id=g.g_name','g.id',array('text_original'=>$probation_group));
				if (is_null($PROBATION_GROUP)) $PROBATION_GROUP=false;
			}
			if ($PROBATION_GROUP!==false) return array($PROBATION_GROUP=>1);
		}
	}

	$skip_secret=(($skip_secret) && ((/*For installer*/!function_exists('get_member')) || ($member_id!=get_member())) && ((!function_exists('has_specific_permission')) || (!has_specific_permission(get_member(),'see_hidden_groups'))));

	global $GROUP_MEMBERS_CACHE;
	if (isset($GROUP_MEMBERS_CACHE[$member_id][$skip_secret][$handle_probation])) return $GROUP_MEMBERS_CACHE[$member_id][$skip_secret][$handle_probation];

	$groups=array();

	// Now implicit usergroup hooks
	$hooks=find_all_hooks('systems','ocf_implicit_usergroups');
	foreach (array_keys($hooks) as $hook)
	{
		require_code('hooks/systems/ocf_implicit_usergroups/'.$hook);
		$ob=object_factory('Hook_implicit_usergroups_'.$hook);
		if ($ob->is_member_within($member_id)) $groups[$ob->get_bound_group_id()]=1;
	}

	require_code('ocf_members');
	if ((!function_exists('ocf_is_ldap_member')/*can happen if said in safe mode and detecting safe mode when choosing whether to avoid a custom file via admin permission which requires this function to run*/) || (!ocf_is_ldap_member($member_id)))
	{
		$_groups=$GLOBALS['FORUM_DB']->query_select('f_group_members m LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_groups g ON g.id=m.gm_group_id',array('gm_group_id','g_hidden'),array('gm_member_id'=>$member_id,'gm_validated'=>1),'ORDER BY g.g_order');
		foreach ($_groups as $group)
			$groups[$group['gm_group_id']]=1;
		if (!isset($GLOBALS['OCF_DRIVER'])) // We didn't init fully (MICRO_BOOTUP), but now we dug a hole - get out of it
		{
			if (method_exists($GLOBALS['FORUM_DRIVER'],'forum_layer_initialise')) $GLOBALS['FORUM_DRIVER']->forum_layer_initialise();
		}
		$primary_group=$GLOBALS['OCF_DRIVER']->get_member_row_field($member_id,'m_primary_group');
		if (is_null($primary_group)) $primary_group=db_get_first_id();
		$groups[$primary_group]=1;
		foreach (array_keys($groups) as $group_id)
		{
			$groups[$group_id]=1;
		}

		$GROUP_MEMBERS_CACHE[$member_id][false][$handle_probation]=$groups;
		$groups2=$groups;
		foreach ($_groups as $group)
		{
			if ($group['g_hidden']==1)
				unset($groups2[$group['gm_group_id']]);
		}
		$GROUP_MEMBERS_CACHE[$member_id][true][$handle_probation]=$groups2;
		if ($skip_secret) $groups=$groups2;
	} else
	{
		$groups=ocf_get_members_groups_ldap($member_id);
		$GROUP_MEMBERS_CACHE[$member_id][false][$handle_probation]=$groups;
		$GROUP_MEMBERS_CACHE[$member_id][true][$handle_probation]=$groups;

		// Mirror to f_group_members table, so direct queries will also get it (we need to do listings of group members, for instance)
		$GLOBALS['FORUM_DB']->query_delete('f_group_members',array('gm_member_id'=>$member_id));
		foreach (array_keys($groups) as $group_id)
		{
			$GLOBALS['FORUM_DB']->query_delete('f_group_members',array('gm_member_id'=>$member_id,'gm_group_id'=>$group_id),'',1);
			$GLOBALS['FORUM_DB']->query_insert('f_group_members',array(
				'gm_group_id'=>$group_id,
				'gm_member_id'=>$member_id,
				'gm_validated'=>1
			));
		}
	}

	return $groups;
}

/**
 * Get the ID for a usergroup if we only know the title. Warning: Only use this with custom code, never core code! It assumes a single language and that usergroups aren't renamed.
 *
 * @param  SHORT_TEXT	The title.
 * @return ?AUTO_LINK	The ID (NULL: could not find).
 */
function find_usergroup_id($title)
{
	$usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list();
	foreach ($usergroups as $id=>$usergroup)
	{
		if ($usergroup==$title)
		{
			return $id;
		}
	}
	return NULL;
}

