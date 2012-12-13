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

class Block_main_friends_list
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		if (get_forum_type()!='ocf') return NULL;

		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		$info['parameters']=array('member_id','max','start','mutual');
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
		$block_id=get_block_id($map);

		$member_id=array_key_exists('member_id',$map)?intval($map['member_id']):get_member();
		$max=get_param_integer($block_id.'_max',array_key_exists('max',$map)?intval($map['max']):12);
		$start=get_param_integer($block_id.'_start',array_key_exists('start',$map)?intval($map['start']):0);
		$mutual=((array_key_exists('mutual',$map)?$map['mutual']:'0')=='1');

		$text_id=do_lang_tempcode('FRIENDS',escape_html($GLOBALS['FORUM_DRIVER']->get_username($member_id)));

		$blocked=collapse_1d_complexity('member_blocked',$GLOBALS['SITE_DB']->query_select('chat_blocking',array('member_blocked'),array('member_blocker'=>$member_id)));
		$all_usergroups=$GLOBALS['FORUM_DRIVER']->get_usergroup_list(true);

		$where='';
		$friends_search=get_param('friends_search','');

		$dbsc=$GLOBALS['NO_DB_SCOPE_CHECK'];
		$GLOBALS['NO_DB_SCOPE_CHECK']=true;

		$msn=($GLOBALS['FORUM_DB']->connection_write!=$GLOBALS['SITE_DB']->connection_write);

		if (!$mutual)
		{
			if (($friends_search!='') && (!$msn))
				$where.=' AND (m1.m_username LIKE \''.db_encode_like('%'.$friends_search.'%').'\' OR m2.m_username LIKE \''.db_encode_like('%'.$friends_search.'%').'\')';

			$query=get_table_prefix().'chat_friends a LEFT JOIN '.get_table_prefix().'f_members m1 ON m1.id=a.member_likes LEFT JOIN '.get_table_prefix().'f_members m2 ON m2.id=a.member_liked LEFT JOIN '.get_table_prefix().'chat_friends b ON a.member_liked=b.member_likes AND a.member_liked='.strval($member_id).' WHERE (a.member_likes='.strval(intval($member_id)).' OR a.member_liked='.strval(intval($member_id)).') AND b.member_liked IS NULL'.$where;
			$rows=$GLOBALS['SITE_DB']->query('SELECT a.* FROM '.$query.' ORDER BY date_and_time',$max,$start);
		} else
		{
			if (($friends_search!='') && (!$msn))
				$where.=' AND m.m_username LIKE \''.db_encode_like('%'.$friends_search.'%').'\'';

			$query=$GLOBALS['SITE_DB']->get_table_prefix().'chat_friends LEFT JOIN '.get_table_prefix().'f_members m ON m.id=a.member_likes WHERE member_likes='.strval(intval($member_id)).$where;
			$rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$query.' ORDER BY date_and_time',$max,$start);
		}
		$max_rows=$GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*) FROM '.$query);

		$friends=array();
		foreach ($rows as $i=>$row)
		{
			$f_id=($row['member_liked']==$member_id)?$row['member_likes']:$row['member_liked'];

			if (($f_id==$row['member_likes']) || (!in_array($f_id,$blocked)))
			{
				$friend_username=$GLOBALS['FORUM_DRIVER']->get_username($f_id);

				if (($friends_search!='') && ($msn) && (strpos($friend_username,$friends_search)===false))
					continue;

				$appears_twice=false;
				foreach ($rows as $j=>$row2)
				{
					$f_id_2=($row2['member_liked']==$member_id)?$row2['member_likes']:$row2['member_liked'];
					if (($f_id_2==$f_id) && ($i!=$j))
					{
						$appears_twice=true;
						break;
					}
				}

				require_code('ocf_members2');
				require_lang('ocf');

				$friend_usergroup_id=$GLOBALS['FORUM_DRIVER']->get_member_row_field($f_id,'m_primary_group');
				$friend_usergroup=array_key_exists($friend_usergroup_id,$all_usergroups)?$all_usergroups[$friend_usergroup_id]:do_lang_tempcode('UNKNOWN');
				$mutual_label=do_lang('MUTUAL_FRIEND');
				$box=render_member_box($f_id,true,NULL,NULL,true,($f_id==get_member() || $member_id==get_member())?array($mutual_label=>do_lang($appears_twice?'YES':'NO')):NULL,false,'friends_list');
				if (!$box->is_empty_shell())
				{
					$friends[]=array(
						'USERGROUP'=>$friend_usergroup,
						'USERNAME'=>$friend_username,
						'URL'=>$GLOBALS['FORUM_DRIVER']->member_profile_url($f_id,false,true),
						'F_ID'=>strval($f_id),
						'BOX'=>$box,
					);
				}
			}
		}

		require_code('templates_pagination');
		$pagination=pagination($text_id,$start,$block_id.'_start',$max,$block_id.'_max',$max_rows);

		$GLOBALS['NO_DB_SCOPE_CHECK']=$dbsc;

		return do_template('BLOCK_MAIN_FRIENDS_LIST',array(
			'_GUID'=>'70b11d3c01ff551be42a0472d27dd207',
			'BLOCK_PARAMS'=>block_params_arr_to_str($map),
			'FRIENDS'=>$friends,
			'PAGINATION'=>$pagination,
			'MEMBER_ID'=>strval($member_id),

			'START'=>strval($start),
			'MAX'=>strval($max),
			'START_PARAM'=>$block_id.'_start',
			'MAX_PARAM'=>$block_id.'_max',
		));
	}

}

