<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2010

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		stats_block
 */

class Block_main_activities
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
		$info['version']=1;
		$info['update_require_upgrade']=1;
		$info['locked']=false;
		$info['parameters']=array();
		return $info;
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_if_exists('main_activities');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		$GLOBALS['SITE_DB']->create_table('main_activities',array(
			'id'=>'*AUTO',
			'a_member_id'=>'*USER',
			'a_language_string_code'=>'*ID_TEXT',
			'a_label_1'=>'SHORT_TEXT',
			'a_label_2'=>'SHORT_TEXT',
			'a_label_3'=>'SHORT_TEXT',
			'a_pagelink_1'=>'SHORT_TEXT',
			'a_pagelink_2'=>'SHORT_TEXT',
			'a_pagelink_3'=>'SHORT_TEXT',
			'a_time'=>'TIME',
			'a_addon'=>'ID_TEXT',
			'a_is_public'=>'SHORT_TEXT'
		));
	}

	// CACHE MESSES WITH POST REMOVAL
	/**
	 * Standard modular cache function.
	 *
	 * @return ?array	Map of cache details (cache_on and ttl) (NULL: module is disabled).
	 */
	/*function cacheing_environment()
	{
		$info=array();
		$info['cache_on']='array(array_key_exists(\'param\',$map)?$map[\'param\']:do_lang(\'ACTIVITIES_TITLE\'),array_key_exists(\'mode\',$map)?$map[\'mode\']:\'all\',get_member())';
		$info['ttl']=3;
		return $info;
	}*/

	/**
	 * Standard modular run function.
	 *
	 * @param  array		A map of parameters.
	 * @return tempcode	The result of execution.
	 */
	function run($map)
	{
		require_lang('main_activities');
		require_css('activities');
		require_javascript('javascript_activities');
		require_javascript('javascript_jquery');
		require_javascript('javascript_base64');
		$stored_max=$GLOBALS['SITE_DB']->query_value_null_ok('values', 'the_value', array('the_name'=>get_zone_name()."_".get_page_name()."_update_max"));

		if (is_null($stored_max))
		{
			if (!array_key_exists('max',$map))
			{
				$map['max']='10';
			}

			$GLOBALS['SITE_DB']->query_insert('values', array('the_value'=>$map['max'], 'the_name'=>get_zone_name()."_".get_page_name()."_update_max", 'date_and_time'=>time()));
		}
		else
		{
			if (!array_key_exists('max',$map))
			{
				$map['max']=$stored_max;
			}
			else
			{
				$GLOBALS['SITE_DB']->query_update('values', array('the_value'=>$map['max'], 'date_and_time'=>time()), array('the_name'=>get_zone_name()."_".get_page_name()."_update_max"));
			}
		}

		if (array_key_exists('param',$map))
			$title=$map['param'];
		else
			$title=do_lang_tempcode('ACTIVITIES_TITLE');

		// See if we're displaying for a specific member
		if (array_key_exists('member',$map))
		{
			// Assume that we've been given a member ID
			$username=$GLOBALS['FORUM_DRIVER']->get_member_row_field(intval($map['member']),'m_username');
			// See if that worked
			if (is_null($username))
			{
				// If not then we can try treatign it as a username, if the forum
				// supports it
				if (method_exists($GLOBALS['FORUM_DRIVER'],'get_member_from_username'))
				{
					$username = $map['member'];
					$member_id = $GLOBALS['FORUM_DRIVER']->get_member_from_username($map['member']);
				}
				// If we've still got nothing then forget the parameter completely
				if (is_null($username))
				{
					return do_lang_tempcode('_USER_NO_EXIST',escape_html($map['member']));
				}
			}
			else
			{
				// It worked, so the parameter must have been a member ID
				$member_id = intval($map['member']);
			}
		}
		else
		{
			// No specific user. Use ourselves.
			$member_id = get_member();
			$username = $GLOBALS['FORUM_DRIVER']->get_member_from_username($member_id);
		}

		require_css('side_blocks');
		require_lang('main_activities');
		require_code('support');

		/*
		 * Relationship is member_likes is the user who has friended member_liked
		 */
		$proceed_selection=true; //There are some cases in which even glancing at the database is a waste of precious time.

		$mode=(array_key_exists('mode',$map))?$map['mode']:'all';

		$is_guest=false; //Can't be doing with overcomplicated SQL breakages. Weed it out.
		$guest_id=intval($GLOBALS['FORUM_DRIVER']->get_guest_id());
		$viewer_id=intval(get_member()); //We'll need this later anyway.
		if ($guest_id==$viewer_id)
			$is_guest=true;

		$can_remove_others = (has_zone_access($viewer_id,'adminzone'))?true:false;

		if (addon_installed('chat'))
		{
			if ($is_guest===false) //If not a guest, get all blocks
			{
				//Grabbing who you're blocked-by
				$blocked_by=$GLOBALS['SITE_DB']->query_select('chat_blocking', array('member_blocker'), array('member_blocked'=>$viewer_id));

				if (count($blocked_by)>0)
				{
					if (count($blocked_by)>1)
					{
						collapse_1d_complexity('member_blocker', $blocked_by);
						$blocked_by=implode(',',$blocked_by);
					}
					else
					{
						$blocked_by=current($blocked_by);
						$blocked_by=$blocked_by['member_blocker'];
					}
				}
				else
					$blocked_by = '';

				//Grabbing who you've blocked
				$blocking=$GLOBALS['SITE_DB']->query_select('chat_blocking', array('member_blocked'), array('member_blocker'=>$viewer_id));

				if (count($blocking)>0)
				{
					if (count($blocking)>1)
					{
						collapse_1d_complexity('member_blocked', $blocking);
						$blocking=implode(',',$blocking);
					}
					else
					{
						$blocking=current($blocking);
						$blocking=$blocking['member_blocked']; //If it's pointing to anything other than the only possible item, PHP needs fixing.
					}
				}
				else
					$blocking = '';
			}
		}
		else
		{
			$blocking = '';
			$blocked_by = '';
		}

		switch ($mode)
		{
			case 'own': //This is used to view one's own activity (eg. on a profile)

				$whereville='a_member_id='.intval($member_id);

				// If the chat addon is installed then there may be 'friends-only'
				// posts, which we may need to filter out. Otherwise we don't need
				// to care.
				if (($member_id!=$viewer_id) && addon_installed('chat'))
				{
					$view_private=NULL;        //Set to default denial level and only bother asking for perms if not a guest.
					if (($is_guest===false))
					{
						if (strlen($blocked_by)>0) //On the basis that you've sought this view out, your blocking them doesn't hide their messages.
							$short_where=' WHERE (member_likes='.$member_id.' AND member_liked='.$viewer_id.' AND member_likes NOT IN('.$blocked_by.'))';
						else
							$short_where=' WHERE (member_likes='.$member_id.' AND member_liked='.$viewer_id.')';

						$view_private=$GLOBALS['SITE_DB']->query_value_null_ok('chat_buddies', 'member_likes',NULL, $short_where);
					}

					if (is_null($view_private)) //If not friended by this person, the view is filtered.
						$whereville='('.$whereville.' AND a_is_public=1)';

				}
		      break;
			case 'friends':
				// "friends" only makes sense if the chat addon is installed
				if (addon_installed('chat') && $is_guest===false) //If not a guest, get all reciprocal friendships.
				{
					$like_outgoing=array();
					//Working on the principle that you only want to see people you like on this, only those you like and have not blocked will be selected
					//Exclusions will be based on whether they like and have not blocked you.

					//Select mutual likes you haven't blocked.
					$tables_and_joins ='chat_buddies a JOIN '.get_table_prefix().'chat_buddies b';
					$tables_and_joins.=' ON (a.member_liked=b.member_likes AND a.member_likes=b.member_liked AND a.member_likes=';
					$tables_and_joins.=$viewer_id;

					$extra_not='';
					if (strlen($blocking)>0) //Also setting who gets discarded from outgoing like selection
					{
						$tables_and_joins.=' AND a.member_liked NOT IN('.$blocking.')';
						$extra_not.=' AND member_liked NOT IN('.$blocking.')';
					}

					if (strlen($blocked_by)>0)
					{
						$tables_and_joins.=' AND a.member_liked NOT IN('.$blocked_by.')';
					}

					$tables_and_joins.=')';
					$extra_not.=');';

					$like_mutual=$GLOBALS['SITE_DB']->query_select($tables_and_joins, array('a.member_liked AS liked'));

					if (count($like_mutual)>1) //More than one mutual friend
					{
						$lm_ids='';

						foreach ($like_mutual as $l_m)
						{
							$lm_ids.=','.$l_m['liked'];
						}

						$lm_ids=substr($lm_ids, 1);

						$like_outgoing=$GLOBALS['SITE_DB']->query_select('chat_buddies', array('member_liked'), NULL, ' WHERE (member_likes='.$viewer_id.' AND member_liked NOT IN('.$lm_ids.')'.$extra_not);

						if (count($like_outgoing)>1) //Likes more than one non-mutual friend
						{
							$lo_ids='';
							foreach ($like_outgoing as $l_o)
							{
								$lo_ids.=','.$l_o['member_liked'];
							}

							$lo_ids=substr($lo_ids, 1);

							$whereville='(a_member_id IN('.$lm_ids.') OR (a_member_id IN('.$lo_ids.') AND a_is_public=1))';
						}
						elseif (count($like_outgoing)>0) //Likes one non-mutual friend
						{
							$whereville='(a_member_id IN('.$lm_ids.') OR (a_member_id='.intval($like_outgoing[0]['member_liked']).' AND a_is_public=1))';
						}
						else //Only has mutual friends
						{
							$whereville='a_member_id IN('.$lm_ids.')';
						}
					}
					elseif (count($like_mutual)>0) //Has one mutual friend
					{
						$like_outgoing=$GLOBALS['SITE_DB']->query_select('chat_buddies', array('member_liked'), NULL, ' WHERE (member_likes='.$viewer_id.' AND member_liked!='.intval($like_mutual[0]['liked']).$extra_not);

						if (count($like_outgoing)>1) //Likes more than one non-mutual friend
						{
							$lo_ids='';
							foreach ($like_outgoing as $l_o)
							{
								$lo_ids.=','.$l_o['member_liked'];
							}

							$lo_ids=substr($lo_ids, 1);

							$whereville='(a_member_id='.intval($like_mutual[0]['liked']).' OR (a_member_id IN('.$lo_ids.') AND a_is_public=1))';
						}
						elseif (count($like_outgoing)>0) //Likes one non-mutual friend
						{
							$whereville='(a_member_id='.intval($like_mutual[0]['liked']).' OR (a_member_id='.intval($like_outgoing[0]['member_liked']).' AND a_is_public=1))';
						}
						else
						{
							$whereville='a_member_id='.intval($like_mutual[0]['liked']); //Has one mutual friend and no others
						}
					}
					else //Has no mutual friends
					{
						$like_outgoing=$GLOBALS['SITE_DB']->query_select('chat_buddies', array('member_liked'), NULL, ' WHERE (member_likes='.$viewer_id.$extra_not);

						if (count($like_outgoing)>1) //Likes more than one person
						{
							$lo_ids='';
							foreach ($like_outgoing as $l_o)
							{
								$lo_ids.=','.$l_o['member_liked'];
							}

							$lo_ids=substr($lo_ids, 1);

							$whereville='(a_member_id IN('.$lo_ids.') AND a_is_public=1)';
						}
						elseif (count($like_outgoing)>0) //Likes one person
							$whereville='(a_member_id='.intval($like_outgoing[0]['member_liked']).' AND a_is_public=1)';
						else //Has no friends, the case with _all_ new members.
							$proceed_selection=false;
					}
				}
				else
					$proceed_selection=false;
				break;
			case 'all': //Frontpage, 100% permissions dependent.
			default:
				$view_private=array();
				if (addon_installed('chat') && $is_guest===false)
				{
					$short_where='member_liked='.$viewer_id;
					if (strlen($blocked_by)>0)
						$short_where='('.$short_where.' AND member_likes NOT IN ('.$blocked_by.'))';
					
					$view_private=$GLOBALS['SITE_DB']->query_select('chat_buddies', array('member_likes'), NULL, ' WHERE '.$short_where.';');
					$view_private[]=array('member_likes'=>$viewer_id);
				}

				if (count($view_private)>1)
				{
					$vp='';

					foreach($view_private as $v_p)
					{
						$vp.=','.$v_p['member_likes'];
					}

					$vp=substr($vp, 1);

					$whereville='(a_member_id IN('.$vp.') OR (a_is_public=1 AND a_member_id!='.$guest_id.'))';
				}
				elseif (count($view_private)>0)
				{
					$view_private=current($view_private);
					$whereville='(a_member_id='.$view_private['member_likes'].' OR (a_is_public=1 AND a_member_id!='.$guest_id.'))';
				}
				else
				{
					$whereville='(a_is_public=1 AND a_member_id!='.$guest_id.')';
				}
		      break;
		}

		$content=array();

		if ($proceed_selection===true)
		{
			$activities=$GLOBALS['SITE_DB']->query('SELECT * FROM '.get_table_prefix().'main_activities WHERE '.$whereville.' ORDER BY a_time DESC',$map['max']);

			if (!is_null($activities) && (count($activities)>0))
			{
				$bits=array();
				foreach ($activities as $row)
				{
					$link_1=($row['a_pagelink_1']=='')?new ocp_tempcode():pagelink_to_tempcode($row['a_pagelink_1']);
					$link_2=($row['a_pagelink_2']=='')?new ocp_tempcode():pagelink_to_tempcode($row['a_pagelink_2']);
					$link_3=($row['a_pagelink_3']=='')?new ocp_tempcode():pagelink_to_tempcode($row['a_pagelink_3']);
					$id = $row['a_member_id'];
					$memberpic=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($id);
					$member_url=build_url(array('page'=>'members','type'=>'view','id'=>$id),get_page_zone('members'));

					$member = $row['a_label_1'];
					$datetime = $row['a_time'];

					$tempcode=do_lang_tempcode($row['a_language_string_code'],comcode_to_tempcode(escape_html($row['a_label_1']),$guest_id,false,NULL),comcode_to_tempcode(escape_html($row['a_label_2']),$guest_id,false,NULL),array(comcode_to_tempcode(escape_html($row['a_label_3']),$guest_id,false,NULL),escape_html($link_1->evaluate()),escape_html($link_2->evaluate()),escape_html($link_3->evaluate())));

					$bits[]=$tempcode;
					$content[] = array('BITS'=>$tempcode,'MEMPIC'=>$memberpic,'NAME'=>$member, 'DATETIME'=>strval($datetime), 'URL'=>$member_url, 'LIID'=>strval($row['id']), 'ALLOW_REMOVE'=>(($row['a_member_id']==$viewer_id) || $can_remove_others)?'1':'0');

				}
				return do_template('BLOCK_MAIN_ACTIVITIES',array(
					'TITLE'=>$title,
					'MODE'=>strval($mode),
					'MEMBER_ID'=>strval($member_id),
					'CONTENT'=>$content,
					'GROW'=>(array_key_exists('grow',$map)? $map['grow']=='1' : true),
					'MAX'=>$map['max'],
				));
			}
		}

		switch($mode)
		{
			case 'own':
				$memberpic=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id); //Get avatar if available
				$donkey_url=build_url(array('page'=>'members', 'type'=>'view', 'id'=>$member_id), 'site'); //Drop in a basic url that just comes straight back
				$member=$GLOBALS['FORUM_DB']->query_value_null_ok('f_members', 'm_username', array('id'=>$member_id));
		      if (is_null($member)) $member='no-one'; //Make sure it's not allowed to be null in a graceful fashion
		      break;
			case 'friends':
			case 'all':
			default:
				$memberpic='';
				$donkey_url=build_url(array('page'=>'members', 'type'=>'view', 'id'=>$member_id), 'site');
				$member='no-one';
		      break;
		}

		$content[] = array('BITS'=>do_lang('NO_ACTIVITIES'),'MEMPIC'=>$memberpic, 'NAME'=>$member, 'DATETIME'=>strval(time()), 'URL'=>$donkey_url, 'LIID'=>'-1', 'ALLOW_REMOVE'=>false);

		return do_template('BLOCK_MAIN_ACTIVITIES',array(
			'TITLE'=>$title,
			'MODE'=>strval($mode),
			'CONTENT'=>$content,
			'MEMBER_ID'=>strval($member_id),
			'GROW'=>(array_key_exists('grow',$map)? $map['grow']=='1' : true),
			'MAX'=>$map['max'],
		));
	}

}


