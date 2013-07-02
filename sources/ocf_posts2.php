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
 * Show a post, isolated of the other posts in it's topic.
 *
 * @param  array		The post row.
 * @param  boolean	Whether to use the post title, as opposed to the post's topic's title.
 * @return tempcode  The isolated post.
 */
function render_post_box($row,$use_post_title=false)
{
	require_code('ocf_groups');
	require_css('ocf');

	// Poster title
	$primary_group=$GLOBALS['FORUM_DRIVER']->get_member_row_field($row['p_poster'],'m_primary_group');
	if (!is_null($primary_group))
	{
		if (addon_installed('ocf_member_titles'))
		{
			$poster_title=$GLOBALS['OCF_DRIVER']->get_member_row_field($row['p_poster'],'m_title');
			if ($poster_title=='') $poster_title=get_translated_text(ocf_get_group_property($primary_group,'title'),$GLOBALS['FORUM_DB']);
		} else $poster_title='';
		$avatar=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($row['p_poster']);
		$posters_groups=$GLOBALS['FORUM_DRIVER']->get_members_groups($row['p_poster'],true);
	} else
	{
		$poster_title='';
		$avatar='';
		$posters_groups=array();
	}

	// Avatar
	if (is_guest($row['p_poster']))
	{
		if ($row['p_poster_name_if_guest']==do_lang('SYSTEM'))
		{
			$avatar=find_theme_image('ocf_default_avatars/default_set/ocp_fanatic',true);
		}
	}
	if ($avatar!='')
	{
		$post_avatar=do_template('OCF_TOPIC_POST_AVATAR',array('_GUID'=>'f5769e8994880817dc441f70bbeb070e','AVATAR'=>$avatar));
	} else $post_avatar=new ocp_tempcode();

	// Rank images
	$rank_images=new ocp_tempcode();
	foreach ($posters_groups as $group)
	{
		$rank_image=ocf_get_group_property($group,'rank_image');
		$group_leader=ocf_get_group_property($group,'group_leader');
		$group_name=ocf_get_group_name($group);
		$rank_image_pri_only=ocf_get_group_property($group,'rank_image_pri_only');
		if (($rank_image!='') && (($rank_image_pri_only==0) || ($group==$primary_group)))
		{
			$rank_images->attach(do_template('OCF_RANK_IMAGE',array('_GUID'=>'4b1724a9d97f93e097cf49b50eeafa66','GROUP_NAME'=>$group_name,'USERNAME'=>$GLOBALS['FORUM_DRIVER']->get_username($row['p_poster']),'IMG'=>$rank_image,'IS_LEADER'=>$group_leader==$row['p_poster'])));
		}
	}

	// Poster details
	if ((!is_guest($row['p_poster'])) && (!is_null($primary_group)))
	{
		require_code('ocf_members2');
		$poster_details=render_member_box($row['p_poster'],false,NULL,NULL,false);
	} else
	{
		$custom_fields=new ocp_tempcode();
		$poster_details=new ocp_tempcode();
	}

	if ((!is_guest($row['p_poster'])) && (!is_null($primary_group)))
	{
		$poster=do_template('OCF_POSTER_MEMBER',array('ONLINE'=>member_is_online($row['p_poster']),'ID'=>strval($row['p_poster']),'POSTER_DETAILS'=>$poster_details,'PROFILE_URL'=>$GLOBALS['FORUM_DRIVER']->member_profile_url($row['p_poster'],false,true),'POSTER_USERNAME'=>$GLOBALS['FORUM_DRIVER']->get_username($row['p_poster']),'HIGHLIGHT_NAME'=>NULL));
	} else
	{
		$poster=do_template('OCF_POSTER_GUEST',array('LOOKUP_IP_URL'=>'','POSTER_DETAILS'=>$poster_details,'POSTER_USERNAME'=>($row['p_poster_name_if_guest']!='')?$row['p_poster_name_if_guest']:do_lang('GUEST')));
	}

	// Last edited
	if (!is_null($row['p_last_edit_time']))
	{
		$last_edited=do_template('OCF_TOPIC_POST_LAST_EDITED',array('LAST_EDIT_DATE_RAW'=>is_null($row['p_last_edit_time'])?'':strval($row['p_last_edit_time']),'LAST_EDIT_DATE'=>get_timezoned_date($row['p_last_edit_time']),'LAST_EDIT_PROFILE_URL'=>is_null($row['p_last_edit_by'])?'':$GLOBALS['FORUM_DRIVER']->member_profile_url($row['p_last_edit_by'],false,true),'LAST_EDIT_USERNAME'=>is_null($row['p_last_edit_by'])?'':$GLOBALS['FORUM_DRIVER']->get_username($row['p_last_edit_by'])));
	} else $last_edited=new ocp_tempcode();
	$last_edited_raw=is_null($row['p_last_edit_time'])?'':strval($row['p_last_edit_time']);

	// Misc stuff
	$poster_id=$row['p_poster'];
	$breadcrumbs=ocf_forum_breadcrumbs($row['p_cache_forum_id']);
	$post_url=build_url(array('page'=>'topicview','type'=>'findpost','id'=>$row['id']),get_module_zone('topicview'));
	$post_url->attach('#post_'.strval($row['id']));
	if ((get_page_name()!='search') && (array_key_exists('text_parsed',$row)) && (!is_null($row['text_parsed'])) && ($row['text_parsed']!='') && ($row['p_post']!=0))
	{
		$post=new ocp_tempcode();
		if (!$post->from_assembly($row['text_parsed'],true))
			$post=get_translated_tempcode($row['p_post'],$GLOBALS['FORUM_DB']);
	} else
	{
		$post=get_translated_tempcode($row['p_post'],$GLOBALS['FORUM_DB']);
	}
	$post_date=get_timezoned_date($row['p_time']);
	$post_date_raw=$row['p_time'];
	if ($use_post_title)
	{
		$post_title=$row['p_title'];
	} else
	{
		$post_title=$GLOBALS['FORUM_DB']->query_value('f_topics','t_cache_first_title',array('id'=>$row['p_topic_id']));
		if ($row['p_title']!=$post_title) $post_title.=': '.$row['p_title'];
	}
	//if ($post_title=='') $post_title=do_lang_tempcode('ISOLATED_POST_TITLE',strval($row['id']));

	$emphasis=new ocp_tempcode();
	if ($row['p_is_emphasised']==1)
	{
		$emphasis=do_lang_tempcode('IMPORTANT');
	}
	elseif (!is_null($row['p_intended_solely_for']))
	{
		$pp_to_username=$GLOBALS['FORUM_DRIVER']->get_username($row['p_intended_solely_for']);
		if (is_null($pp_to_username)) $pp_to_username=do_lang('UNKNOWN');
		$emphasis=do_lang('PP_TO',$pp_to_username);
	}

	require_code('feedback');
	actualise_rating(true,'post',strval($row['id']),get_self_url(),$row['p_title']);
	$rating=display_rating(get_self_url(),$row['p_title'],'post',strval($row['id']),'RATING_INLINE_DYNAMIC',$row['p_poster']);

	// Render
	$map=array(
		'ID'=>strval($row['id']),
		'TOPIC_FIRST_POST_ID'=>'',
		'TOPIC_FIRST_POSTER'=>'',
		'POST_ID'=>strval($row['id']),
		'URL'=>$post_url,
		'CLASS'=>($row['p_is_emphasised']==1)?'ocf_post_emphasis':((!is_null($row['p_intended_solely_for']))?'ocf_post_personal':''),
		'EMPHASIS'=>$emphasis,
		'FIRST_UNREAD'=>'',
		'POSTER_TITLE'=>$poster_title,
		'POST_TITLE'=>$post_title,
		'POST_DATE_RAW'=>strval($post_date_raw),
		'POST_DATE'=>$post_date,
		'POST'=>$post,
		'TOPIC_ID'=>is_null($row['p_topic_id'])?'':strval($row['p_topic_id']),
		'LAST_EDITED_RAW'=>$last_edited_raw,
		'LAST_EDITED'=>$last_edited,
		'POSTER_ID'=>strval($poster_id),
		'POSTER'=>$poster,
		'POSTER_DETAILS'=>$poster_details,
		'POST_AVATAR'=>$post_avatar,
		'RANK_IMAGES'=>$rank_images,
		'BUTTONS'=>'',
		'SIGNATURE'=>'',
		'UNVALIDATED'=>'',
		'DESCRIPTION'=>'',
		'PREVIEWING'=>true,
		'RATING'=>$rating,
	);
	return do_template('OCF_POST_BOX',array(
		'_GUID'=>'9456f4fe4b8fb1bf34f606fcb2bcc9d7',
		'BREADCRUMBS'=>$breadcrumbs,
		'POST'=>do_template('OCF_TOPIC_POST',$map)
	)+$map+array('ACTUAL_POST'=>$post));
}
