<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		activity_feed
 */

/*
TODO: Support if JS disabled, possibly remove jQuery dependency
*/

/**
 * Get SQL for querying activities, appropriate to the given settings.
 *
 * @param  MEMBER			The viewing member; permissions are checked against this, NOT against the member_ids parameter
 * @param  ID_TEXT		The view mode
 * @set some_members friends all
 * @param  array			A list of member IDs
 * @return array			A pair: SQL WHERE clause to use on the activities table, a boolean indicating whether it is worth querying
 */
function get_activity_querying_sql($viewer_id,$mode,$member_ids)
{
	$proceed_selection=true; // There are some cases in which even glancing at the database is a waste of precious time.

	require_all_lang();

	/*if (isset($member_ids[0])) // Useful for testing
		$viewer_id=$member_ids[0];*/

	$guest_id=$GLOBALS['FORUM_DRIVER']->get_guest_id();
	$is_guest=is_guest($viewer_id); // Can't be doing with overcomplicated SQL breakages. Weed it out.

	// Find out your blocks, and who is blocking you - both must be respected
	$blocking='';
	$blocked_by='';
	if (addon_installed('chat'))
	{
		if (!$is_guest) // If not a guest, get all blocks
		{
			// Grabbing who you're blocked-by
			$_blocked_by=$GLOBALS['SITE_DB']->query_select('chat_blocking',array('member_blocker'),array('member_blocked'=>$viewer_id));
			$blocked_by=implode(',',collapse_1d_complexity('member_blocker',$_blocked_by));

			// Grabbing who you've blocked
			$_blocking=$GLOBALS['SITE_DB']->query_select('chat_blocking',array('member_blocked'),array('member_blocker'=>$viewer_id));
			$blocking=implode(',',collapse_1d_complexity('member_blocked',$_blocking));
		}
	}

	switch ($mode)
	{
		case 'some_members': // This is used to view one's own activity (e.g. on a profile)
			$where_clause='';
			foreach ($member_ids as $member_id)
			{
				if ($where_clause!='') $where_clause.=' AND ';

				$_where_clause='';
				$_where_clause.='(';
				$_where_clause.='a_member_id='.strval($member_id);
				$_where_clause.=' OR ';
				$_where_clause.='(';
				$_where_clause.='a_also_involving='.strval($member_id);
				if ($blocking!='')
					$_where_clause.=' AND a_member_id NOT IN ('.$blocking.')';
				if (addon_installed('chat')) // Limit to stuff from this member's friends about them
				{
					$_where_clause.=' AND a_member_id IN (SELECT member_liked FROM '.get_table_prefix().'chat_buddies WHERE member_likes='.strval($member_id).')';
				}
				$_where_clause.=')';
				$_where_clause.=')';

				// If the chat addon is installed then there may be 'friends-only'
				// posts, which we may need to filter out. Otherwise we don't need to care.
				if (($member_id!=$viewer_id) && (addon_installed('chat')))
				{
					if (!$is_guest)
					{
						$friends_check_where='member_likes='.strval($member_id).' AND member_liked='.strval($viewer_id);
						if ($blocked_by!='')
							$friends_check_where.=' AND member_likes NOT IN ('.$blocked_by.')';

						$view_private=!is_null($GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT member_likes FROM '.get_table_prefix().'chat_buddies WHERE '.$friends_check_where));
					} else
					{
						$view_private=false;
					}

					if (!$view_private) // If not friended by this person, the view is filtered.
						$_where_clause='('.$_where_clause.' AND a_is_public=1)';
				}

				$where_clause.=$_where_clause;
			}
	      break;

		case 'friends':
			$where_clause='';

			// "friends" only makes sense if the chat addon is installed
			if ((addon_installed('chat')) && (!$is_guest)) // If not a guest, get all reciprocal friendships.
			{
				// Working on the principle that you only want to see people you like on this, only those you like and have not blocked will be selected
				// Exclusions will be based on whether they like and have not blocked you.

				// Select mutual likes you haven't blocked and haven't blocked you
				$table='chat_buddies a JOIN '.get_table_prefix().'chat_buddies b ON a.member_liked=b.member_likes AND a.member_likes=b.member_liked AND a.member_likes='.strval($viewer_id);
				if ($blocking!='')
					$table.=' AND a.member_liked NOT IN ('.$blocking.')';
				if ($blocked_by!='')
					$table.=' AND a.member_liked NOT IN ('.$blocked_by.')';
				$like_mutual=$GLOBALS['SITE_DB']->query_select($table,array('a.member_liked AS liked'));
				$lm_ids='';
				foreach ($like_mutual as $l_m)
				{
					if ($lm_ids!='') $lm_ids.=',';
					$lm_ids.=strval($l_m['liked']);
				}

				// Also look at friends we like but they don't like back - and include public statuses from them
				$_where_clause='member_likes='.strval($viewer_id);
				if ($blocking!='')
					$_where_clause=' AND member_liked NOT IN ('.$blocking.')';
				if ($lm_ids!='')
					$_where_clause.=' AND member_liked NOT IN ('.$lm_ids.')';
				$like_outgoing=$GLOBALS['SITE_DB']->query_select('chat_buddies',array('member_liked'),NULL,' WHERE '.$_where_clause);
				$lo_ids='';
				foreach ($like_outgoing as $l_o)
				{
					if ($lo_ids!='') $lo_ids.=',';
					$lo_ids.=strval($l_o['member_liked']);
				}

				// Build query
				if ($lm_ids=='' && $lo_ids=='') // We have no friends yet, so optimise out the query
				{
					$proceed_selection=false;
				} else
				{
					$where_clause='(';
					if ($lm_ids!='')
						$where_clause.='a_member_id IN ('.$lm_ids.')';
					if ($lo_ids!='')
					{
						if ($where_clause!='(')
							$where_clause.=' OR ';
						$where_clause.='(a_member_id IN ('.$lo_ids.') AND a_is_public=1)';
					}
					$where_clause.=')';
				}
			} else
			{
				$proceed_selection=false; // Optimise out the query
			}
			break;

		case 'all': // Frontpage, 100% permissions dependent.
		default:
			// Work out what the private content the current member can view
			$vp='';
			if ((addon_installed('chat')) && (!$is_guest))
			{
				$friends_check_where='member_liked='.strval($viewer_id);
				if ($blocked_by!='') $friends_check_where.=' AND member_likes NOT IN ('.$blocked_by.')';

				$view_private=$GLOBALS['SITE_DB']->query_select('chat_buddies',array('member_likes'),NULL,' WHERE '.$friends_check_where.';');
				$view_private[]=array('member_likes'=>$viewer_id);
				foreach ($view_private as $v_p)
				{
					if ($vp!='') $vp.=',';
					$vp.=strval($v_p['member_likes']);
				}
			}

			// Build query
			$where_clause='((a_is_public=1 AND a_member_id<>'.strval($guest_id).')';
			if ($vp!='')
				$where_clause.=' OR (a_member_id IN ('.$vp.'))';
			$where_clause.=')';
			if ($blocking!='')
				$where_clause.=' AND a_member_id NOT IN ('.$blocking.')';
	      break;
	}

	return array($proceed_selection,$where_clause);
}

/**
 * Render an activity to Tempcode/HTML.
 *
 * @param  array			Database row
 * @param  boolean		Whether the rendered activity will be shown in a live ocPortal (as opposed to being e-mailed, for example)
 * @return tempcode		Rendered activity
 */
function render_activity($row,$use_inside_ocp=true)
{
	$guest_id=$GLOBALS['FORUM_DRIVER']->get_guest_id();

	// Details of member
	$member_id=$row['a_member_id'];
	$memberpic=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id);
	$member_url=$GLOBALS['FORUM_DRIVER']->member_profile_url($member_id,false,$use_inside_ocp);

	$datetime=$row['a_time'];

	$message=new ocp_tempcode();

	$test=do_lang($row['a_language_string_code'],'{1}','{2}','{3}');

	// Convert our parameters and links to Tempcode
	$label=array();
	$link=array();
	for ($i=1;$i<=3;$i++)
	{
		$label[$i]=comcode_to_tempcode($row['a_label_'.strval($i)],$guest_id,false,NULL);
		$link[$i]=($row['a_pagelink_'.strval($i)]=='')?new ocp_tempcode():pagelink_to_tempcode($row['a_pagelink_'.strval($i)],!$use_inside_ocp);
		if (($row['a_pagelink_'.strval($i)]!='') && (strpos($test,'{'.strval($i+3).'}')===false))
		{
			$label[$i]=hyperlink($link[$i],$label[$i]->evaluate());
		}
	}

	// Render primary language string
	$extra_lang_string_params=array(
		$label[3],
		symbol_tempcode('ESCAPE',array($link[1])),
		symbol_tempcode('ESCAPE',array($link[2])),
		symbol_tempcode('ESCAPE',array($link[3]))
	);
	if (!is_null($row['a_also_involving']))
	{
		$_username=$GLOBALS['FORUM_DRIVER']->get_username($row['a_also_involving']);
		$url=$GLOBALS['FORUM_DRIVER']->member_profile_url($row['a_also_involving'],false,$use_inside_ocp);
		$hyperlink=hyperlink($url,$_username,false,true);

		$extra_lang_string_params[]=$hyperlink;
	} else
	{
		$extra_lang_string_params[]=do_lang_tempcode('GUEST');
	}
	$message->attach(do_lang_tempcode($row['a_language_string_code'],
		$label[1],
		$label[2],
		$extra_lang_string_params
	));

	// Lang string may not use all params, so add extras on if were unused
	for ($i=1;$i<=3;$i++)
	{
		if ((strpos($row['a_language_string_code'],'_UNTYPED')===false) && (strpos($test,'{1}')===false) && (strpos($test,'{2}')===false) && (strpos($test,'{3}')===false) && ($row['a_label_'.strval($i)]!=''))
		{
			if (!$message->is_empty())
				$message->attach(': ');

			$message->attach($label[$i]->evaluate());
		}
	}

	return array($message,$memberpic,$datetime,$member_url,$row['a_language_string_code']);
}

/**
 * Convert a page link into a tempcode.
 *
 * @param  string			The page link
 * @param  boolean		Whether the link is for putting out externally to the site (so no keep_* parameters)
 * @return array			tempcode url
 */
function pagelink_to_tempcode($pagelink,$external=false)
{
	list($zone,$map,$hash)=page_link_decode($pagelink);

	return build_url($map,$zone,array(),false,false,$external,$hash);
}
