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
 * @package		chat
 */

/**
 * Block a member.
 *
 * @param  MEMBER			The member blocking
 * @param  MEMBER			The member being blocked
 * @param  ?TIME			The logged time of the block (NULL: now)
 */
function blocking_add($blocker,$blocked,$time=NULL)
{
	if (is_null($time)) $time=time();

	$GLOBALS['SITE_DB']->query_delete('chat_blocking',array(
		'member_blocker'=>$blocker,
		'member_blocked'=>$blocked
	),'',1); // Just in case page refreshed

	$GLOBALS['SITE_DB']->query_insert('chat_blocking',array(
		'member_blocker'=>$blocker,
		'member_blocked'=>$blocked,
		'date_and_time'=>$time
	));

	log_it('BLOCK_MEMBER',strval($blocker),strval($blocked));
}

/**
 * Unblock a member.
 *
 * @param  MEMBER			The member unblocking
 * @param  MEMBER			The member being unblocked
 */
function blocking_remove($blocker,$blocked)
{
	$GLOBALS['SITE_DB']->query_delete('chat_blocking',array(
		'member_blocker'=>$blocker,
		'member_blocked'=>$blocked
	),'',1); // Just in case page refreshed

	log_it('UNBLOCK_MEMBER',strval($blocker),strval($blocked));
}

/**
 * Add a buddy.
 *
 * @param  MEMBER			The member befriending
 * @param  MEMBER			The member being befriended
 * @param  ?TIME			The logged time of the friendship (NULL: now)
 */
function buddy_add($likes,$liked,$time=NULL)
{
	if (is_null($time)) $time=time();

	$GLOBALS['SITE_DB']->query_delete('chat_buddies',array(
		'member_likes'=>$likes,
		'member_liked'=>$liked
	),'',1); // Just in case page refreshed

	$GLOBALS['SITE_DB']->query_insert('chat_buddies',array(
		'member_likes'=>$likes,
		'member_liked'=>$liked,
		'date_and_time'=>$time
	));

	// Send a notification
	if (is_null($GLOBALS['SITE_DB']->query_value_null_ok('chat_buddies','date_and_time',array('member_likes'=>$liked,'member_liked'=>$likes))))
	{
		require_code('notifications');
		$to_name=$GLOBALS['FORUM_DRIVER']->get_username($liked);
		$from_name=$GLOBALS['FORUM_DRIVER']->get_username($likes);
		$subject_tag=do_lang('YOURE_MY_BUDDY_SUBJECT',$from_name,get_site_name(),NULL,get_lang($liked));
		$befriend_url=build_url(array('page'=>'chat','type'=>'buddy_add','member_id'=>$likes),get_module_zone('chat'),NULL,false,false,true);
		$message_raw=do_lang('YOURE_MY_BUDDY_BODY',comcode_escape($to_name),comcode_escape(get_site_name()),array($befriend_url->evaluate(),comcode_escape($from_name)),get_lang($liked));
		dispatch_notification('new_buddy',NULL,$subject_tag,$message_raw,array($liked),$likes);

		// Log the action
		log_it('MAKE_BUDDY',strval($likes),strval($liked));
		syndicate_described_activity('chat:PEOPLE_NOW_FRIENDS',$to_name,'','','_SEARCH:members:view:'.strval($liked),'_SEARCH:members:view:'.strval($likes),'','chat',1,$likes);
		syndicate_described_activity('chat:PEOPLE_NOW_FRIENDS',$to_name,'','','_SEARCH:members:view:'.strval($liked),'_SEARCH:members:view:'.strval($likes),'','chat',1,$liked);
	}
}

/**
 * Remove ('dump') a buddy.
 *
 * @param  MEMBER			The member befriending
 * @param  MEMBER			The member being dumped
 */
function buddy_remove($likes,$liked)
{
	$GLOBALS['SITE_DB']->query_delete('chat_buddies',array(
		'member_likes'=>$likes,
		'member_liked'=>$liked
	),'',1); // Just in case page refreshed

	log_it('DUMP_BUDDY',strval($likes),strval($liked));
}

/**
 * Get form fields for adding/editing a chatroom.
 *
 * @param  boolean		Whether the room is being made as a private room by the current member
 * @param  SHORT_TEXT	The room name
 * @param  LONG_TEXT		The welcome message
 * @param  SHORT_TEXT	The owner username
 * @param  LONG_TEXT		The comma-separated list of users that may access it (blank: no restriction)
 * @param  LONG_TEXT		The comma-separated list of usergroups that may access it (blank: no restriction)
 * @param  LONG_TEXT		The comma-separated list of users that may NOT access it (blank: no restriction)
 * @param  LONG_TEXT		The comma-separated list of usergroups that may NOT access it (blank: no restriction)
 * @return tempcode		The fields
 */
function get_chatroom_fields($is_made_by_me=false,$room_name='',$welcome='',$username='',$allow2='',$allow2_groups='',$disallow2='',$disallow2_groups='')
{
	require_code('form_templates');

	$fields=new ocp_tempcode();

	$fields->attach(form_input_line(do_lang_tempcode('ROOM_NAME'),do_lang_tempcode('DESCRIPTION_ROOM_NAME'),'room_name',$room_name,true));
	$fields->attach(form_input_line_comcode(do_lang_tempcode('WELCOME_MESSAGE'),do_lang_tempcode('DESCRIPTION_WELCOME_MESSAGE'),'c_welcome',$welcome,false));
	if (!$is_made_by_me) $fields->attach(form_input_username(do_lang_tempcode('ROOM_OWNER'),do_lang_tempcode('DESCRIPTION_ROOM_OWNER'),'room_owner',$username,false));
	$langs=find_all_langs();
	if (count($langs)>1)
		$fields->attach(form_input_list(do_lang_tempcode('ROOM_LANG'),do_lang_tempcode('DESCRIPTION_ROOM_LANG'),'room_lang',nice_get_langs()));
	require_lang('permissions');
	$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('SECTION_HIDDEN'=>$allow2=='' && $allow2_groups=='' && !$is_made_by_me,'TITLE'=>do_lang_tempcode($is_made_by_me?'PERMISSIONS':'LOWLEVEL_PERMISSIONS'))));
	$fields->attach(form_input_username_multi(do_lang_tempcode('ALLOW_LIST'),do_lang_tempcode('DESCRIPTION_ALLOW_LIST'),'allow_list',array_map(array($GLOBALS['FORUM_DRIVER'],'get_username'),($allow2=='')?array():array_map('intval',explode(',',$allow2))),0,true));
	if ((!$is_made_by_me) || (get_option('group_private_chatrooms')=='1'))
	{
		$usergroup_list=new ocp_tempcode();
		$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);
		foreach ($groups as $key=>$val)
		{
			if ($key!=db_get_first_id())
			{
				if (get_forum_type()=='ocf')
				{
					require_code('ocf_groups2');
					$num_members=ocf_get_group_members_raw_count($key);
					if (($num_members>=1) && ($num_members<=6))
					{
						$group_members=ocf_get_group_members_raw($key);
						$group_member_usernames='';
						foreach ($group_members as $group_member)
						{
							if ($group_member_usernames!='') $group_member_usernames=do_lang('LIST_SEP');
							$group_member_usernames.=$GLOBALS['FORUM_DRIVER']->get_username($group_member);
						}
						$val=do_lang('GROUP_MEMBERS_SPECIFIC',$val,$group_member_usernames);
					} else
					{
						$val=do_lang('GROUP_MEMBERS',$val,$num_members);
					}
				}
				$usergroup_list->attach(form_input_list_entry(strval($key),($allow2_groups=='*') || count(array_intersect(array($key),($allow2_groups=='')?array():explode(',',$allow2_groups)))!=0,$val));
			}
		}

		$fields->attach(form_input_multi_list(do_lang_tempcode('ALLOW_LIST_GROUPS'),do_lang_tempcode($is_made_by_me?'DESCRIPTION_ALLOW_LIST_GROUPS_SIMPLE':'DESCRIPTION_ALLOW_LIST_GROUPS'),'allow_list_groups',$usergroup_list));
	}
	$fields->attach(do_template('FORM_SCREEN_FIELD_SPACER',array('SECTION_HIDDEN'=>$disallow2=='' && $disallow2_groups=='','TITLE'=>do_lang_tempcode('ADVANCED'))));
	$fields->attach(form_input_username_multi(do_lang_tempcode('DISALLOW_LIST'),do_lang_tempcode('DESCRIPTION_DISALLOW_LIST'),'disallow_list',array_map(array($GLOBALS['FORUM_DRIVER'],'get_username'),($disallow2=='')?array():array_map('intval',explode(',',$disallow2))),0,true));
	if ((!$is_made_by_me) || (get_option('group_private_chatrooms')=='1'))
	{
		$usergroup_list=new ocp_tempcode();
		$groups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);
		foreach ($groups as $key=>$val)
		{
			if ($key!=db_get_first_id())
			{
				if (get_forum_type()=='ocf')
				{
					require_code('ocf_groups2');
					$num_members=ocf_get_group_members_raw_count($key);
					if (($num_members>=1) && ($num_members<=6))
					{
						$group_members=ocf_get_group_members_raw($key);
						$group_member_usernames='';
						foreach ($group_members as $group_member)
						{
							if ($group_member_usernames!='') $group_member_usernames=do_lang('LIST_SEP');
							$group_member_usernames.=$GLOBALS['FORUM_DRIVER']->get_username($group_member);
						}
						$val=do_lang('GROUP_MEMBERS_SPECIFIC',$val,$group_member_usernames);
					} else
					{
						$val=do_lang('GROUP_MEMBERS',$val,$num_members);
					}
				}
				$usergroup_list->attach(form_input_list_entry(strval($key),($disallow2_groups=='*') || count(array_intersect(array($key),($disallow2_groups=='')?array():explode(',',$disallow2_groups)))!=0,$val));
			}
		}

		$fields->attach(form_input_multi_list(do_lang_tempcode('DISALLOW_LIST_GROUPS'),do_lang_tempcode('DESCRIPTION_DISALLOW_LIST_GROUPS'),'disallow_list_groups',$usergroup_list));
	}

	return $fields;
}

/**
 * Read in chat permission fields, from the complex posted data.
 *
 * @return array			A tuple of permission fields
 */
function read_in_chat_perm_fields()
{
	$allow2='';
	$_x=post_param('allow_list_0','');
	$x=$GLOBALS['FORUM_DRIVER']->get_member_from_username($_x);
	if (!is_null($x)) $allow2.=strval($x);
	foreach ($_POST as $key=>$_x)
	{
		if (substr($key,0,strlen('allow_list'))!='allow_list') continue;
		if ($key=='allow_list_0') continue;
		if ($key=='allow_list_groups') continue;
		if (get_magic_quotes_gpc()) $_x=stripslashes($_x);
		if ($_x=='') continue;
		$x=$GLOBALS['FORUM_DRIVER']->get_member_from_username($_x);
		if (!is_null($x))
		{
			if ($allow2!='') $allow2.=',';
			$allow2.=strval($x);
		}
	}
	$allow2_groups=array_key_exists('allow_list_groups',$_POST)?implode(',',$_POST['allow_list_groups']):'';
	$disallow2='';
	$_x=post_param('disallow_list_0','');
	$x=$GLOBALS['FORUM_DRIVER']->get_member_from_username($_x);
	if (!is_null($x)) $disallow2.=strval($x);
	foreach ($_POST as $key=>$_x)
	{
		if (substr($key,0,strlen('disallow_list'))!='disallow_list') continue;
		if ($key=='disallow_list_0') continue;
		if ($key=='disallow_list_groups') continue;
		if (get_magic_quotes_gpc()) $_x=stripslashes($_x);
		if ($_x=='') continue;
		$x=$GLOBALS['FORUM_DRIVER']->get_member_from_username($_x);
		if (!is_null($x))
		{
			if ($disallow2!='') $disallow2.=',';
			$disallow2.=strval($x);
		}
	}
	$disallow2_groups=array_key_exists('disallow_list_groups',$_POST)?implode(',',$_POST['disallow_list_groups']):'';

	return array($allow2,$allow2_groups,$disallow2,$disallow2_groups);
}

/**
 * Add a chatroom.
 *
 * @param  SHORT_TEXT		The welcome message
 * @param  SHORT_TEXT		The room name
 * @param  MEMBER				The room owner
 * @param  LONG_TEXT			The comma-separated list of users that may access it (blank: no restriction)
 * @param  LONG_TEXT			The comma-separated list of usergroups that may access it (blank: no restriction)
 * @param  LONG_TEXT			The comma-separated list of users that may NOT access it (blank: no restriction)
 * @param  LONG_TEXT			The comma-separated list of usergroups that may NOT access it (blank: no restriction)
 * @param  LANGUAGE_NAME	The room language
 * @param  BINARY				Whether it is an IM room
 * @return AUTO_LINK			The chat room ID
 */
function add_chatroom($welcome,$roomname,$room_owner,$allow2,$allow2_groups,$disallow2,$disallow2_groups,$roomlang,$is_im=0)
{
	$id=$GLOBALS['SITE_DB']->query_insert('chat_rooms',array('is_im'=>$is_im,'c_welcome'=>insert_lang($welcome,2),'room_name'=>$roomname,'room_owner'=>$room_owner,'allow_list'=>$allow2,'allow_list_groups'=>$allow2_groups,'disallow_list'=>$disallow2,'disallow_list_groups'=>$disallow2_groups,'room_language'=>$roomlang),true);

	log_it('ADD_CHATROOM',strval($id),$roomname);

	decache('side_shoutbox');

	return $id;
}

/**
 * Edit a chatroom.
 *
 * @param  AUTO_LINK			The chat room ID
 * @param  SHORT_TEXT		The welcome message
 * @param  SHORT_TEXT		The room name
 * @param  MEMBER				The room owner
 * @param  LONG_TEXT			The comma-separated list of users that may access it (blank: no restriction)
 * @param  LONG_TEXT			The comma-separated list of usergroups that may access it (blank: no restriction)
 * @param  LONG_TEXT			The comma-separated list of users that may NOT access it (blank: no restriction)
 * @param  LONG_TEXT			The comma-separated list of usergroups that may NOT access it (blank: no restriction)
 * @param  LANGUAGE_NAME	The room language
 */
function edit_chatroom($id,$welcome,$roomname,$room_owner,$allow2,$allow2_groups,$disallow2,$disallow2_groups,$roomlang)
{
	$c_welcome=$GLOBALS['SITE_DB']->query_value('chat_rooms','c_welcome',array('id'=>$id));

	$GLOBALS['SITE_DB']->query_update('chat_rooms',array('c_welcome'=>lang_remap($c_welcome,$welcome),'room_name'=>$roomname,'room_owner'=>$room_owner,'allow_list'=>$allow2,'allow_list_groups'=>$allow2_groups,'disallow_list'=>$disallow2,'disallow_list_groups'=>$disallow2_groups,'room_language'=>$roomlang),array('id'=>$id),'',1);

	decache('side_shoutbox');

	require_code('urls2');
	suggest_new_idmoniker_for('chat','room',strval($id),$roomname);

	log_it('EDIT_CHATROOM',strval($id),$roomname);
}

/**
 * Delete a chatroom.
 *
 * @param  AUTO_LINK		The chat room ID
 */
function delete_chatroom($id)
{
	$rows=$GLOBALS['SITE_DB']->query_select('chat_rooms',array('c_welcome','room_name','is_im'),array('id'=>$id),'',1);
	if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));

	delete_lang($rows[0]['c_welcome']);

	$GLOBALS['SITE_DB']->query_delete('chat_rooms',array('id'=>$id),'',1);

	delete_chat_messages(array('room_id'=>$id));

	decache('side_shoutbox');

	if ($rows[0]['is_im']==0)
		log_it('DELETE_ROOM',strval($id),$rows[0]['room_name']);
}

/**
 * Delete chat messages.
 *
 * @param  array			Where query to specify what to delete
 */
function delete_chat_messages($where)
{
	if (function_exists('set_time_limit')) @set_time_limit(0);
	do
	{
		$messages=$GLOBALS['SITE_DB']->query_select('chat_messages',array('id','the_message'),$where,'',400);
		foreach ($messages as $message)
		{
			delete_lang($message['the_message']);
			$GLOBALS['SITE_DB']->query_delete('chat_messages',array('id'=>$message['id']),'',1);
		}
	}
	while ($messages!=array());
}

/**
 * Delete all chatrooms.
 */
function delete_all_chatrooms()
{
	if (function_exists('set_time_limit')) @set_time_limit(0);
	do
	{
		$c_welcomes=$GLOBALS['SITE_DB']->query_select('chat_rooms',array('id','c_welcome'),array('is_im'=>0),'',400);
		foreach ($c_welcomes as $c_welcome)
		{
			delete_lang($c_welcome['c_welcome']);
			$GLOBALS['SITE_DB']->query_delete('chat_rooms',array('id'=>$c_welcome['id']));
			delete_chat_messages(array('room_id'=>$c_welcome['id']));
		}
	}
	while ($c_welcomes!=array());

	decache('side_shoutbox');

	log_it('DELETE_ALL_ROOMS');
}

/**
 * Ban a member from a chatroom.
 *
 * @param  MEMBER			The member to ban
 * @param  AUTO_LINK		The chat room ID
 */
function chatroom_ban_to($member_id,$id)
{
	log_it('CHAT_BAN',strval($id),$GLOBALS['FORUM_DRIVER']->get_username($member_id));

	$disallow_list=$GLOBALS['SITE_DB']->query_value('chat_rooms','disallow_list',array('id'=>$id));
	if ($disallow_list=='') $disallow_list=strval($member_id); else $disallow_list.=','.strval($member_id);
	$GLOBALS['SITE_DB']->query_update('chat_rooms',array('disallow_list'=>$disallow_list),array('id'=>$id),'',1);
}

/**
 * Unban a member from a chatroom.
 *
 * @param  MEMBER			The member to unban
 * @param  AUTO_LINK		The chat room ID
 */
function chatroom_unban_to($member_id,$id)
{
	log_it('CHAT_UNBAN',strval($id),$GLOBALS['FORUM_DRIVER']->get_username($member_id));

	$disallow_list=$GLOBALS['SITE_DB']->query_value('chat_rooms','disallow_list',array('id'=>$id));
	$_disallow_list=explode(',',$disallow_list);
	$_disallow_list2=array();
	$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id);
	foreach ($_disallow_list as $dis)
	{
		if (((strval($member_id)!=$dis)) && ($dis!=$username)) $_disallow_list2[]=$dis;
	}
	$disallow_list=implode(',',$_disallow_list2);
	$GLOBALS['SITE_DB']->query_update('chat_rooms',array('disallow_list'=>$disallow_list),array('id'=>$id),'',1);
}

/**
 * Delete all messages in a chatroom.
 *
 * @param  AUTO_LINK		The chat room ID
 */
function delete_chatroom_messages($id)
{
	delete_chat_messages(array('room_id'=>$id));

	log_it('DELETE_ALL_MESSAGES',strval($id));

	decache('side_shoutbox');
}


