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
 * @package		core_feedback_features
 */

/**
 * Find who submitted a piece of feedbackable content.
 *
 * @param  ID_TEXT		Content type
 * @param  ID_TEXT		Content ID
 * @return array			A tuple: Content title (NULL: unknown), Submitter (NULL: unknown), URL (for use within current browser session), URL (for use in emails / sharing)
 */
function get_details_behind_feedback_code($type_id,$id)
{
	require_code('content');

	$cma_hook=convert_ocportal_type_codes('feedback_type_code',$type_id,'cma_hook');
	if ($cma_hook!='')
	{
		require_code('hooks/systems/content_meta_aware/'.$cma_hook);
		$cma_ob=object_factory('Hook_content_meta_aware_'.$cma_hook);
		$info=$cma_ob->info();
		list($content_title,$submitter_id,,,$content_url,$content_url_email_safe)=content_get_details($cma_hook,$id);
		return array($content_title,$submitter_id,$content_url,$content_url_email_safe);
	}

	return array(NULL,NULL,NULL,NULL);
}

/**
 * Given a particular bit of feedback content, check if the user may access it.
 *
 * @param  MEMBER			User to check
 * @param  ID_TEXT		Content type
 * @param  ID_TEXT		Content ID
 * @return boolean		Whether there is permission
 */
function may_view_content_behind_feedback_code($member_id,$type_id,$id)
{
	require_code('content');

	$permission_type_code=convert_ocportal_type_codes('feedback_type_code',$type_id,'permissions_type_code');

	$module=convert_ocportal_type_codes('permissions_type_code',$type_id,'module');
	if ($module=='') $module=$id;

	$category_id=mixed();
	$award_hook=convert_ocportal_type_codes('award_hook',$type_id,'permissions_type_code');
	if ($award_hook!='')
	{
		require_code('hooks/systems/awards/'.$award_hook);
		$award_hook_ob=object_factory('Hook_awards_'.$award_hook);
		$info=$award_hook_ob->info();
		if (isset($info['category_field']))
		{
			$cma_hook=convert_ocportal_type_codes('award_hook',$award_hook,'cma_hook');
			list(,,,$content)=content_get_details($cma_hook,$id);
			$category_id=$content[$info['category_field']];
		}
	}

	return ((has_actual_page_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),$module)) && (($permission_type_code=='') || (is_null($category_id)) || (has_category_access($GLOBALS['FORUM_DRIVER']->get_guest_id(),$permission_type_code,$category_id))));
}

/**
 * Main wrapper function to embed miscellaneous feedback systems into a module output.
 *
 * @param  ID_TEXT		The page name
 * @param  ID_TEXT		Content ID
 * @param  BINARY			Whether rating is allowed
 * @param  integer		Whether comments/reviews is allowed (reviews allowed=2)
 * @set 0 1 2
 * @param  BINARY			Whether trackbacks are allowed
 * @param  BINARY			Whether the content is validated
 * @param  ?MEMBER		Content owner (NULL: none)
 * @param  mixed			URL to view the content
 * @param  SHORT_TEXT	Content title
 * @param  ?string		Forum to post comments in (NULL: site-wide default)
 * @return array			Tuple: Rating details, Comment details, Trackback details
 */
function embed_feedback_systems($page_name,$id,$allow_rating,$allow_comments,$allow_trackbacks,$validated,$submitter,$self_url,$self_title,$forum)
{
	// Sign up original poster for notifications
	$auto_monitor_contrib_content=$GLOBALS['OCF_DRIVER']->get_member_row_field($submitter,'m_auto_monitor_contrib_content');
	if ($auto_monitor_contrib_content==1)
	{
		$test=$GLOBALS['SITE_DB']->query_value_null_ok('notifications_enabled','l_setting',array(
			'l_member_id'=>$submitter,
			'l_notification_code'=>'comment_posted',
			'l_code_category'=>$page_name.'_'.$id,
		));
		if (is_null($test))
		{
			require_code('notifications');
			enable_notifications('comment_posted',$page_name.'_'.$id,$submitter);
		}
	}

	do_rating($allow_rating==1,$page_name,$id,$self_url,$self_title);
	if ((!is_null(post_param('title',NULL))) || ($validated==1))
		do_comments($allow_comments>=1,$page_name,$id,$self_url,$self_title,$forum);
	//do_trackback($allow_trackbacks==1,$page_name,$id);
	$rating_details=get_rating_details($self_url,$self_title,$page_name,$id,$allow_rating==1);
	$comment_details=get_comment_details($page_name,$allow_comments==1,$id,false,$forum,NULL,NULL,false,false,$submitter,$allow_comments==2);
	$trackback_details=get_trackback_details($page_name,$id,$allow_trackbacks==1);

	if (is_object($self_url)) $self_url=$self_url->evaluate();

	$serialized_options=serialize(array($page_name,$id,$allow_comments,$submitter,$self_url,$self_title,$forum));
	global $SITE_INFO;
	$hash=md5($serialized_options.$SITE_INFO['admin_password']); // A little security, to ensure $serialized_options is not tampered with

	// AJAX support
	require_javascript('javascript_ajax');
	require_javascript('javascript_more');
	require_javascript('javascript_thumbnails');
	$comment_details->attach(do_template('COMMENT_AJAX_HANDLER',array(
		'OPTIONS'=>$serialized_options,
		'HASH'=>$hash,
	)));

	return array($rating_details,$comment_details,$trackback_details);
}

/**
 * Do an AJAX comment post
 */
function post_comment_script()
{
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

	// Read in context of what we're doing
	$options=post_param('options');
	list($page_name,$id,$allow_comments,$submitter,$self_url,$self_title,$forum)=unserialize($options);

	// Check security
	$hash=post_param('hash');
	global $SITE_INFO;
	if (md5($options.$SITE_INFO['admin_password'])!=$hash)
	{
		header('Content-Type: text/plain; charset='.get_charset());
		exit();
	}

	// Post comment
	do_comments($allow_comments>=1,$page_name,$id,$self_url,$self_title,$forum);

	// Get new comments state
	$comment_details=get_comment_details($page_name,$allow_comments==1,$id,false,$forum,NULL,NULL,false,false,$submitter,$allow_comments==2);

	// And output as text
	header('Content-Type: text/plain; charset='.get_charset());
	$comment_details->evaluate_echo();
}

/**
 * Get tempcode for doing ratings (sits above get_rating_simple_array)
 *
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  ID_TEXT		The type (download, etc) that this rating is for
 * @param  ID_TEXT		The ID of the type that this rating is for
 * @param  boolean		Whether this resource allows rating (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ?array			List of extra rating type steams (e.g. Quality would lookup download_Quality) (NULL: none).
 * @return tempcode		Tempcode for complete rating box
 */
function get_rating_details($self_url,$self_title,$rating_for_type,$id,$allow_rating,$extra_ratings=NULL)
{
	if ($allow_rating)
	{
		return display_rating($self_url,$self_title,$rating_for_type,$id,'RATING_BOX',$extra_ratings);
	}

	return new ocp_tempcode();
}

/**
 * Display rating using images
 *	
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  ID_TEXT		The type (download, etc) that this rating is for
 * @param  ID_TEXT		The ID of the type that this rating is for
 * @param  ID_TEXT		The template to use to display the rating box
 * @param  ?array			List of extra rating type steams (e.g. Quality would lookup download_Quality) (NULL: none).
 * @return tempcode		Tempcode for complete trackback box
 */
function display_rating($self_url,$self_title,$rating_for_type,$id,$tpl='RATING_INLINE',$extra_ratings=NULL)
{
	$rating_data=get_rating_simple_array($self_url,$self_title,$rating_for_type,$id,'RATING_INSIDE',$extra_ratings);

	if (is_null($rating_data))
		return new ocp_tempcode();

	return do_template($tpl,$rating_data);
}

/**
 * Get rating information for the specified resource.
 *
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  ID_TEXT		The type (download, etc) that this rating is for
 * @param  ID_TEXT		The ID of the type that this rating is for
 * @param  ID_TEXT		The template to use to display the rating box
 * @param  ?array			List of extra rating type steams (e.g. Quality would lookup download_Quality) (NULL: none).
 * @return ?array			Current rating information (ready to be passed into a template). RATING is the rating (out of 10), NUM_RATINGS s the number of ratings so far, RATING_INSIDE is the tempcode of the rating box (NULL: rating disabled)
 */
function get_rating_simple_array($self_url,$self_title,$rating_for_type,$id,$tpl='RATING_INSIDE',$extra_ratings=NULL)
{
	if (get_option('is_on_rating')=='1')
	{
		// Ratings
		$num_ratings=$GLOBALS['SITE_DB']->query_value('rating','COUNT(*)',array('rating_for_type'=>$rating_for_type,'rating_for_id'=>$id));
		if ((!is_null($num_ratings)) && ($num_ratings>0))
		{
			$_rating=array();
			$rating=$GLOBALS['SITE_DB']->query_value('rating','SUM(rating)',array('rating_for_type'=>$rating_for_type,'rating_for_id'=>$id));

			$calculated_rating=intval(round($rating/floatval($num_ratings)));

			$_rating[]=array('TITLE'=>is_null($extra_ratings)?'':do_lang('GENERAL'),'RATING'=>make_string_tempcode(integer_format($calculated_rating)));

			$GLOBALS['META_DATA']+=array(
				'rating'=>strval($calculated_rating),
			);

			if (!is_null($extra_ratings))
			{
				foreach ($extra_ratings as $stem)
				{
					$_num_ratings=$GLOBALS['SITE_DB']->query_value('rating','COUNT(*)',array('rating_for_type'=>$rating_for_type.'_'.$stem,'rating_for_id'=>$id));
					if ($_num_ratings==0) $_num_ratings=1;
					$rating=$GLOBALS['SITE_DB']->query_value('rating','SUM(rating)',array('rating_for_type'=>$rating_for_type.'_'.$stem,'rating_for_id'=>$id));
					$_rating[]=array('TITLE'=>$stem,'RATING'=>make_string_tempcode(integer_format(intval(round($rating/$_num_ratings)))));
				}
			}
		} else $_rating=NULL;
		
		// The possible rating criteria
		$titles=array(array('TITLE'=>'','TYPE'=>''));
		if (!is_null($extra_ratings))
		{
			$titles=array(array('TITLE'=>do_lang('GENERAL'),'TYPE'=>''));
			foreach ($extra_ratings as $type)
			{
				$titles[]=array('TITLE'=>$type,'TYPE'=>$type);
			}
		}

		// Work out possible errors that mighr prevent rating being allowed
		$error=NULL;
		$rate_url='';
		if (!has_specific_permission(get_member(),'rate',get_page_name()))
		{
			$error=do_lang_tempcode('RATE_DENIED');
		}
		elseif (already_rated($rating_for_type,$id))
		{
			$error=do_lang_tempcode('NORATE');
		} else
		{
			$rate_url=get_self_url();
		}

		// Templating
		$rating_inside=do_template($tpl,array('SELF_URL'=>$self_url,'SELF_TITLE'=>$self_title,'ERROR'=>$error,'TYPE'=>'','ROOT_TYPE'=>$rating_for_type,'ID'=>$id,'URL'=>$rate_url,'TITLES'=>$titles,'SIMPLISTIC'=>count($titles)==1));
		return array('ROOT_TYPE'=>$rating_for_type,'ID'=>$id,'_RATING'=>$_rating,'NUM_RATINGS'=>integer_format($num_ratings),'RATING_INSIDE'=>$rating_inside);
	}
	return NULL;
}

/**
 * Find whether you have rated the specified resource before.
 *
 * @param  ID_TEXT		The type (download, etc) that this rating is for
 * @param  ID_TEXT		The ID of the type that this rating is for
 * @return boolean		Whether the resource has already been rated
 */
function already_rated($rating_for_type,$id)
{
	$more=(!is_guest())?' OR rating_member='.strval((integer)get_member()):'';
	$has_rated=$GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT rating FROM '.get_table_prefix().'rating WHERE '.db_string_equal_to('rating_for_type',$rating_for_type).' AND '.db_string_equal_to('rating_for_id',$id).' AND (rating_ip=\''.get_ip_address().'\''.$more.')');
	return (!is_null($has_rated));
}

/**
 * Actually adds a rating to the specified resource.
 * It performs full checking of inputs, and will log a hackattack if the rating is not between 1 and 10.
 *
 * @param  boolean		Whether this resource allows rating (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ID_TEXT		The type (download, etc) that this rating is for
 * @param  ID_TEXT		The ID of the type that this rating is for
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  ?array			List of extra rating type steams (e.g. Quality would lookup download_Quality) (NULL: none).
 */
function do_rating($allow_rating,$rating_for_type,$id,$self_url,$self_title,$extra_ratings=NULL)
{
	if ((get_option('is_on_rating')=='0') || (!$allow_rating)) return;

	if (is_null($extra_ratings)) $extra_ratings=array();
	$extra_ratings[]='';

	foreach ($extra_ratings as $type)
	{
		// Has there actually been any rating?
		$rating=post_param_integer('rating__'.$type.'__'.$id,NULL);
		if (is_null($rating)) return;

		do_specific_rating($rating,get_page_name(),get_member(),$rating_for_type,$type,$id,$self_url,$self_title);

		// Ok, so just thank 'em
		attach_message(do_lang_tempcode('THANKYOU_FOR_RATING'),'inform');
	}

	if ((!is_guest()) && (addon_installed('points')))
	{
		require_code('points');
		$_count=point_info(get_member());
		$count=array_key_exists('points_gained_rating',$_count)?$_count['points_gained_rating']:0;
		$GLOBALS['FORUM_DRIVER']->set_custom_field(get_member(),'points_gained_rating',$count+1);
	}
}

/**
 * Implement a rating at the quantum level.
 *
 * @param  integer		Rating given
 * @range 1 10
 * @param  ID_TEXT		The page name the rating is on
 * @param  MEMBER			The member doing the rating
 * @param  ID_TEXT		The type (download, etc) that this rating is for
 * @param  ID_TEXT		The second level type (probably blank)
 * @param  ID_TEXT		The ID of the type that this rating is for
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 */
function do_specific_rating($rating,$page_name,$member_id,$rating_for_type,$type,$id,$self_url,$self_title)
{
	if (($rating>10) || ($rating<1)) log_hack_attack_and_exit('VOTE_CHEAT');

	if (!has_specific_permission($member_id,'rate',$page_name)) return;
	if (already_rated($rating_for_type.(($type=='')?'':('_'.$type)),$id)) return;

	$GLOBALS['SITE_DB']->query_insert('rating',array('rating_for_type'=>$rating_for_type.(($type=='')?'':('_'.$type)),'rating_for_id'=>$id,'rating_member'=>$member_id,'rating_ip'=>get_ip_address(),'rating_time'=>time(),'rating'=>$rating));

	// Top rating / liked
	if (/*(get_value('likes')==='1') && */($rating==10) && ($type==''))
	{
		list(,$submitter)=get_details_behind_feedback_code($rating_for_type,$id);
		if ((!is_null($submitter)) && (!is_guest($submitter)))
		{
			// Give points
			if (addon_installed('points'))
			{
				require_code('points2');
				require_lang('points');
				system_gift_transfer(do_lang('CONTENT_LIKED'),intval(get_option('points_if_liked')),$submitter);
			}

			// Notification
			require_code('notifications');
			$subject=do_lang('CONTENT_LIKED_NOTIFICATION_MAIL_SUBJECT',get_site_name(),$self_title);
			$mail=do_lang('CONTENT_LIKED_NOTIFICATION_MAIL',comcode_escape(get_site_name()),comcode_escape($self_title),array(is_object($self_url)?$self_url->evaluate():$self_url));
			dispatch_notification('like',NULL,$subject,$mail,array($submitter));
		}

		// Put on activity wall / whatever
		if (may_view_content_behind_feedback_code($GLOBALS['FORUM_DRIVER']->get_guest_id(),$rating_for_type,$id))
			syndicate_described_activity('LIKES',$self_title,'','',url_to_pagelink($self_url),'','',convert_ocportal_type_codes('feedback_type_code',$rating_for_type,'addon_name'));
	}

	// Enter them for a prize draw to win a free jet
	// NOT IMPLEMENTED- Anyone want to donate the jet?
}

/**
 * Get the tempcode containing all the comments posted, and the comments posting form for the specified resource.
 *
 * @param  ID_TEXT		The type (download, etc) that this commenting is for
 * @param  boolean		Whether this resource allows comments (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ID_TEXT		The ID of the type that this commenting is for
 * @param  boolean		Whether the comment box will be invisible if there are not yet any comments (and you're not staff)
 * @param  ?string		The name of the forum to use (NULL: default comment forum)
 * @param  ?string		The default post to use (NULL: standard courtesy warning)
 * @param  ?mixed			The raw comment array (NULL: lookup). This is useful if we want to pass it through a filter
 * @param  boolean		Whether to skip permission checks
 * @param  boolean		Whether to reverse the posts
 * @param  ?MEMBER		User to highlight the posts of (NULL: none)
 * @param  boolean		Whether to allow ratings along with the comment (like reviews)
 * @return tempcode		The tempcode for the comment box
 */
function get_comment_details($type,$allow_comments,$id,$invisible_if_no_comments=false,$forum=NULL,$post_warning=NULL,$_comments=NULL,$explicit_allow=false,$reverse=false,$highlight_by_user=NULL,$allow_reviews=false)
{
	if ($allow_reviews) $allow_comments=true; // These options are meant to be conflated/tied
	
	$comment_details=new ocp_tempcode();

	if (((get_option('is_on_comments')=='1') && (get_forum_type()!='none') && ($allow_comments)) || ($explicit_allow))
	{
		require_lang('comcode');

		// Comment posts
		if (is_null($forum)) $forum=get_option('comments_forum_name');
		$full_title=$type.'_'.$id; // Actual full title not required for lookup
		$count=0;
		$results_browser=NULL;
		if (is_null($_comments))
		{
			$max_comments=get_param_integer('max_comments',40);
			$start_comments=get_param_integer('start_comments',0);
			$_comments=$GLOBALS['FORUM_DRIVER']->get_forum_topic_posts($forum,$full_title,$full_title,$count,$max_comments,$start_comments,false,$reverse);
			if ($count>$max_comments)
			{
				require_code('templates_results_browser');
				$results_browser=results_browser(do_lang_tempcode('COMMENTS'),NULL,$start_comments,'start_comments',$max_comments,'max_comments',$count,NULL,NULL,true);
			}
		}

		$GLOBALS['FEED_URL_2']=find_script('backend').'?mode=comments&forum='.urlencode($forum).'&filter='.urlencode($full_title); // Advertise RSS

		if (get_forum_type()=='ocf')
		{
			if (!is_integer($forum))
			{
				$forum_id=$GLOBALS['FORUM_DRIVER']->forum_id_from_name($forum);
				if (is_null($forum_id)) return new ocp_tempcode();
			}
			else $forum_id=(integer)$forum;

			$topic_id=$GLOBALS['FORUM_DRIVER']->get_tid_from_topic($type.'_'.$id,$forum_id,$type.'_'.$id);
		}

		if ($_comments!==-1)
		{
			$comments='';
			$staff_forum_link='';
			$forum_link='';
			$form=new ocp_tempcode();

			// Show existing comments
			if ($_comments!==-2)
			{
				$GLOBALS['META_DATA']+=array(
					'numcomments'=>strval(count($_comments)),
				);

				if ((get_forum_type()=='ocf') && ($allow_reviews))
				{
					$all_individual_review_ratings=$GLOBALS['SITE_DB']->query_select('review_supplement',array('*'),array('r_topic_id'=>$topic_id));
				} else
				{
					$all_individual_review_ratings=array();
				}

				foreach ($_comments as $comment)
				{
					if (is_null($comment)) continue;

					$datetime_raw=$comment['date'];
					$datetime=get_timezoned_date($comment['date']);
					$poster_link=is_guest($comment['user'])?new ocp_tempcode():$GLOBALS['FORUM_DRIVER']->member_profile_link($comment['user'],false,true);
					$poster_name=array_key_exists('username',$comment)?$comment['username']:$GLOBALS['FORUM_DRIVER']->get_username($comment['user']);
					if (is_null($poster_name)) $poster_name=do_lang('UNKNOWN');
					$highlight=($highlight_by_user===$comment['user']);
					
					// Find review, if there is one
					$individual_review_ratings=array();
					foreach ($all_individual_review_ratings as $potential_individual_review_rating)
					{
						if ($potential_individual_review_rating['r_post_id']==$comment['id'])
						{
							$individual_review_ratings[$potential_individual_review_rating['r_rating_type']]=array(
								'REVIEW_TITLE'=>$potential_individual_review_rating['r_rating_type'],
								'REVIEW_RATING'=>float_to_raw_string($potential_individual_review_rating['r_rating']),
							);
						}
					}

					$edit_post_url=new ocp_tempcode();
					require_code('ocf_posts');
					if ((get_forum_type()=='ocf') && (ocf_may_edit_post_by($comment['user'],$forum_id)))
					{
						$edit_post_url=build_url(array('page'=>'topics','type'=>'edit_post','id'=>$comment['id'],'redirect'=>get_self_url(true)),get_module_zone('topics'));
					}

					$tpl_post=do_template('POST',array('_GUID'=>'eb7df038959885414e32f58e9f0f9f39','POSTER_ID'=>strval($comment['user']),'EDIT_URL'=>$edit_post_url,'INDIVIDUAL_REVIEW_RATINGS'=>$individual_review_ratings,'HIGHLIGHT'=>$highlight,'TITLE'=>$comment['title'],'TIME_RAW'=>strval($datetime_raw),'TIME'=>$datetime,'POSTER_LINK'=>$poster_link,'POSTER_NAME'=>$poster_name,'POST'=>$comment['message'],'POST_COMCODE'=>isset($comment['message_comcode'])?$comment['message_comcode']:NULL));
					$comments.=$tpl_post->evaluate();
				}

				$tid=$GLOBALS['FORUM_DRIVER']->get_tid_from_topic($full_title,$forum,$full_title);
				$forum_link=$GLOBALS['FORUM_DRIVER']->topic_link($tid,$forum);
				if ($GLOBALS['FORUM_DRIVER']->is_staff(get_member()) || ($forum==get_option('comments_forum_name')))
				{
					$staff_forum_link=$forum_link;
				}
			} else
			{
				if ($invisible_if_no_comments) return new ocp_tempcode();
			}

			// Existing review ratings
			$review_titles=array();
			if ((get_forum_type()=='ocf') && ($allow_reviews))
			{
				$_rating=$GLOBALS['SITE_DB']->query_value('review_supplement','AVG(r_rating)',array('r_rating_type'=>'','r_topic_id'=>$topic_id));
				$rating=mixed();
				$rating=is_null($_rating)?NULL:$_rating;
				$review_titles[]=array('REVIEW_TITLE'=>'','REVIEW_RATING'=>make_string_tempcode(is_null($rating)?'':float_format($rating)));
				if (!is_null($rating))
				{
					$GLOBALS['META_DATA']+=array(
						'rating'=>float_to_raw_string($rating),
					);
				}
			} else $review_titles=NULL;

			// Make-a-comment form
			if (has_specific_permission(get_member(),'comment',get_page_name()))
			{
				$em=$GLOBALS['FORUM_DRIVER']->get_emoticon_chooser();
				require_javascript('javascript_editing');
				$comcode_help=build_url(array('page'=>'userguide_comcode'),get_comcode_zone('userguide_comcode',false));
				$comment_text=get_option('comment_text');
				if (is_null($comment_text)) $comment_text=''; // Weird fix for problem people seem to get
				if (is_null($post_warning)) $post_warning=do_lang('POST_WARNING');
				require_javascript('javascript_validation');
				$comment_url=get_self_url();

				if (addon_installed('captcha'))
				{
					require_code('captcha');
					$use_captcha=use_captcha();
					if ($use_captcha)
					{
						generate_captcha();
					}
				} else $use_captcha=false;
				$title=do_lang_tempcode($allow_reviews?'POST_REVIEW':'MAKE_COMMENT');

				$join_bits=new ocp_tempcode();
				if (is_guest())
				{
					$redirect=get_self_url(true,true);
					$login_url=build_url(array('page'=>'login','type'=>'misc','redirect'=>$redirect),get_module_zone('login'));
					$join_url=$GLOBALS['FORUM_DRIVER']->join_link();
					$join_bits=do_template('JOIN_OR_LOGIN',array('LOGIN_URL'=>$login_url,'JOIN_URL'=>$join_url));
				}

				$form=do_template('COMMENTS',array('_GUID'=>'c87025f81ee64c885f0ac545efa5f16c','EXPAND_TYPE'=>'contract','FIRST_POST_URL'=>'','FIRST_POST'=>'','JOIN_BITS'=>$join_bits,'REVIEWS'=>$allow_reviews,'COMMENTS'=>$comments,'TYPE'=>$type,'ID'=>$id,'REVIEW_TITLES'=>$review_titles,'USE_CAPTCHA'=>$use_captcha,'GET_EMAIL'=>false,'EMAIL_OPTIONAL'=>true,'GET_TITLE'=>true,'POST_WARNING'=>$post_warning,'COMMENT_TEXT'=>$comment_text,'EM'=>$em,'DISPLAY'=>'block','COMMENT_URL'=>$comment_url,'TITLE'=>$title));
			}

			// Show comments/form
			$comment_details=do_template('COMMENTS_WRAPPER',array('_GUID'=>'a89cacb546157d34vv0994ef91b2e707','RESULTS_BROWSER'=>$results_browser,'TYPE'=>$type,'ID'=>$id,'REVIEW_TITLES'=>is_null($review_titles)?array():$review_titles,'FORUM_LINK'=>$forum_link,'STAFF_FORUM_LINK'=>$staff_forum_link,'FORM'=>$form,'COMMENTS'=>$comments));
		} else
		{
			attach_message(do_lang_tempcode('MISSING_FORUM',escape_html($forum)),'warn');
		}
	}

	return $comment_details;
}

/**
 * Topic titles may be encoded for both human readable data, and a special ID code: this will extract just the ID code, or return the whole thing if only an ID code
 *
 * @param  string			Potentially complex topic title
 * @return string			Simplified topic title
*/
function sanitise_topic_title($title)
{
	$matches=array();
	if (preg_match('# \(\#(.*)\)$#',$title,$matches)!=0)
	{
		return $matches[1];
	}
	return $title;
}

/**
 * Similar to sanitise_topic_title, but operates on topic descriptions.
 *
 * @param  string			Potentially complex topic description
 * @return string			Simplified topic description
*/
function sanitise_topic_description($description)
{
	$matches=array();
	if (preg_match('#: \#(.*)$#',$description,$matches)!=0)
	{
		return $matches[1];
	}
	return $description;
}

/**
 * Add comments to the specified resource.
 *
 * @param  boolean		Whether this resource allows comments (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ID_TEXT		The type (download, etc) that this commenting is for
 * @param  ID_TEXT		The ID of the type that this commenting is for
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  ?string		The name of the forum to use (NULL: default comment forum)
 * @param  boolean		Whether to not require a captcha
 * @param  ?BINARY		Whether the post is validated (NULL: unknown, find whether it needs to be marked unvalidated initially). This only works with the OCF driver (hence is the last parameter).
 * @param  boolean		Whether to force allowance
 * @param  boolean		Whether to skip a success message
 * @param  boolean		Whether posts made should not be shared
 * @return boolean		Whether a hidden post has been made
 */
function do_comments($allow_comments,$type,$id,$self_url,$self_title,$forum=NULL,$avoid_captcha=false,$validated=NULL,$explicit_allow=false,$no_success_message=false,$private=false)
{
	if (!$explicit_allow)
	{
		if ((get_option('is_on_comments')=='0') || (!$allow_comments)) return false;
	
		if (!has_specific_permission(get_member(),'comment',get_page_name())) return false;
	}

	if (running_script('preview')) return false;

	$forum_tie=(get_option('is_on_strong_forum_tie')=='1');

	if (addon_installed('captcha'))
	{
		if (((array_key_exists('post',$_POST)) && ($_POST['post']!='')) && (!$avoid_captcha))
		{
			require_code('captcha');
			enforce_captcha();
		}
	}

	$title=post_param('title',NULL);
	if ((is_null($title)) && (!$forum_tie)) return false;

	$post=post_param('post',NULL);
	if (($post=='') && ($title!==''))
	{
		$post=$title;
		$title='';
	}
	if ($post==='') warn_exit(do_lang_tempcode('NO_PARAMETER_SENT','post'));
	if (is_null($post)) $post='';
	$email=trim(post_param('email',''));
	if ($email!='')
	{
		$body='> '.str_replace(chr(10),chr(10).'> ',$post);
		if (substr($body,-2)=='> ') $body=substr($body,0,strlen($body)-2);
		if (get_page_name()!='tickets') $post.='[staff_note]';
		$post.="\n\n".'[email subject="Re: '.comcode_escape($title).' ['.get_site_name().']" body="'.comcode_escape($body).'"]'.$email.'[/email]'."\n\n";
		if (get_page_name()!='tickets') $post.='[/staff_note]';
	}

	$home_link=is_null($self_title)?new ocp_tempcode():hyperlink($self_url,escape_html($self_title));

	if (is_null($forum)) $forum=get_option('comments_forum_name');

	$self_title=strip_comcode($self_title);

	$self_url_flat=(is_object($self_url)?$self_url->evaluate():$self_url);

	$full_title=is_null($self_title)?($type.'_'.$id):($self_title.' (#'.$type.'_'.$id.')');
	$poster_name_if_guest=post_param('poster_name_if_guest','');
	$result=$GLOBALS['FORUM_DRIVER']->make_post_forum_topic(
		$forum,
		$full_title,
		get_member(),
		$post,
		is_null($title)?'':$title,
		$home_link,
		NULL,
		NULL,
		$validated,
		$explicit_allow?1:NULL,
		$explicit_allow,
		array(
			is_null($self_title)?($type.'_'.$id):($self_title), /* Prettier topic title, used if the forum driver supports topic descriptions (where we will instead store the technical back-reference) */
			do_lang('COMMENT').': #'.$type.'_'.$id, /* Topic description, used if the forum driver supports topic descriptions */
			$self_url_flat),
		$poster_name_if_guest
	);
	if (is_null($result))
	{
		$result=false;
	} elseif ((get_forum_type()=='ocf') && (!is_null($GLOBALS['LAST_POST_ID'])))
	{
		$extra_review_ratings=array();
		$extra_review_ratings[]='';

		foreach ($extra_review_ratings as $rating_type)
		{
			// Has there actually been any rating?
			$rating=post_param_integer('review_rating',NULL);

			if (!is_null($rating))
			{
				if (($rating>10) || ($rating<1)) log_hack_attack_and_exit('VOTE_CHEAT');

				$GLOBALS['SITE_DB']->query_insert('review_supplement',array(
					'r_topic_id'=>$GLOBALS['LAST_TOPIC_ID'],
					'r_post_id'=>$GLOBALS['LAST_POST_ID'],
					'r_rating_type'=>$rating_type,
					'r_rating_for_type'=>$type,
					'r_rating_for_id'=>$id,
					'r_rating'=>$rating,
				));
			}
		}
	}

	if (!$private)
	{
		// Notification
		require_code('notifications');
		$username=$GLOBALS['FORUM_DRIVER']->get_username(get_member());
		$subject=do_lang('NEW_COMMENT_SUBJECT',get_site_name(),$self_title,array(post_param('title'),$username),get_site_default_lang());
		$username=$GLOBALS['FORUM_DRIVER']->get_username(get_member());
		$message_raw=do_lang('NEW_COMMENT_BODY',comcode_escape(get_site_name()),comcode_escape($self_title),array(post_param('title'),post_param('post'),$self_url_flat,comcode_escape($username)),get_site_default_lang());
		dispatch_notification('comment_posted',$type.'_'.$id,$subject,$message_raw);

		// Is the user gonna automatically enable notifications for this?
		$auto_monitor_contrib_content=$GLOBALS['OCF_DRIVER']->get_member_row_field(get_member(),'m_auto_monitor_contrib_content');
		if ($auto_monitor_contrib_content==1)
			enable_notifications('comment_posted',$type.'_'.$id);

		// Activity
		if (may_view_content_behind_feedback_code($GLOBALS['FORUM_DRIVER']->get_guest_id(),$type,$id))
			syndicate_described_activity('ADDED_COMMENT_ON',$self_title,'','',url_to_pagelink(is_object($self_url)?$self_url->evaluate():$self_url),'','',convert_ocportal_type_codes('feedback_type_code',$type,'addon_name'));
	}

	if (($post!='') && ($forum_tie) && (!$no_success_message))
	{
		require_code('site2');
		assign_refresh($GLOBALS['FORUM_DRIVER']->topic_link($GLOBALS['FORUM_DRIVER']->get_tid_from_topic($type.'_'.$id,$forum,$type.'_'.$id),$forum),0.0);
	}

	if (($post!='') && (!$no_success_message)) attach_message(do_lang_tempcode('SUCCESS'));

	return $result;
}

/**
 * Update the spacer post of a comment topic, after an edit.
 *
 * @param  boolean		Whether this resource allows comments (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ID_TEXT		The type (download, etc) that this commenting is for
 * @param  ID_TEXT		The ID of the type that this commenting is for
 * @param  mixed			The URL to where the commenting will pass back to (to put into the comment topic header) (URLPATH or Tempcode)
 * @param  ?string		The title to where the commenting will pass back to (to put into the comment topic header) (NULL: don't know, but not first post so not important)
 * @param  ?string		The name of the forum to use (NULL: default comment forum)
 * @param  ?AUTO_LINK	ID of spacer post (NULL: unknown)
 */
function update_spacer_post($allow_comments,$type,$id,$self_url,$self_title,$forum=NULL,$post_id=NULL)
{
	if ((get_option('is_on_comments')=='0') || (!$allow_comments)) return;
	if (get_forum_type()!='ocf') return;

	$home_link=is_null($self_title)?new ocp_tempcode():hyperlink($self_url,escape_html($self_title));

	if (is_null($forum)) $forum=get_option('comments_forum_name');
	if (!is_integer($forum))
	{
		$forum_id=$GLOBALS['FORUM_DRIVER']->forum_id_from_name($forum);
		if (is_null($forum_id)) return;
	}
	else $forum_id=(integer)$forum;

	$self_title=strip_comcode($self_title);

	if (is_null($post_id))
	{
		$topic_id=$GLOBALS['FORUM_DRIVER']->get_tid_from_topic($type.'_'.$id,$forum_id,$type.'_'.$id);
		if (is_null($topic_id)) return;
		$post_id=$GLOBALS['FORUM_DB']->query_value_null_ok('f_posts','MIN(id)',array('p_topic_id'=>$topic_id));
		if (is_null($post_id)) return;
	} else
	{
		$topic_id=$GLOBALS['FORUM_DB']->query_value('f_posts','p_topic_id',array('id'=>$post_id));
	}

	$spacer_title=is_null($self_title)?($type.'_'.$id):($self_title.' (#'.$type.'_'.$id.')');
	$spacer_post='[semihtml]'.do_lang('SPACER_POST',$home_link->evaluate(),'','',get_site_default_lang()).'[/semihtml]';

	if (get_forum_type()=='ocf')
	{
		require_code('ocf_posts_action3');
		ocf_edit_post($post_id,1,is_null($self_title)?$spacer_title:$self_title,$spacer_post,0,0,NULL,false,false,'',false);
		require_code('ocf_topics_action2');
		ocf_edit_topic($topic_id,do_lang('COMMENT').': #'.$type.'_'.$id,NULL,NULL,NULL,NULL,NULL,NULL,'',NULL,$home_link->evaluate(),false);
	}
}

/**
 * Get the tempcode containing all the trackbacks received, and the trackback posting form for the specified resource.
 *
 * @param  ID_TEXT		The type (download, etc) that this trackback is for
 * @param  ID_TEXT		The ID of the type that this trackback is for
 * @param  boolean		Whether this resource allows trackback (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ID_TEXT		The type of details being fetched (currently: blank or XML)
 * @return tempcode		Tempcode for complete trackback box
 */
function get_trackback_details($trackback_for_type,$id,$allow_trackback,$type='')
{
	if (($type!='') && ($type!='xml')) $type='';

	if ((get_option('is_on_trackbacks')=='1') && ($allow_trackback))
	{
		require_lang('trackbacks');
		
		$trackbacks=$GLOBALS['SITE_DB']->query_select('trackbacks',array('*'),array('trackback_for_type'=>$trackback_for_type,'trackback_for_id'=>$id),'ORDER BY trackback_time DESC',300);

		$content=new ocp_tempcode();
		$items=new ocp_tempcode();

		global $CURRENT_SCREEN_TITLE;

		if (is_null($CURRENT_SCREEN_TITLE)) $CURRENT_SCREEN_TITLE='';

		foreach ($trackbacks as $value)
		{
			if ($type=='') $content->attach(do_template('TRACKBACK',array('_GUID'=>'128e21cdbc38a3037d083f619bb311ae','ID'=>strval($value['id']),'TIME_RAW'=>strval($value['trackback_time']),'TIME'=>get_timezoned_date($value['trackback_time']),'URL'=>$value['trackback_url'],'TITLE'=>$value['trackback_title'],'EXCERPT'=>$value['trackback_excerpt'],'NAME'=>$value['trackback_name'])));
			else $items->attach(do_template('TRACKBACK_XML',array('_GUID'=>'a3fa8ab9f0e58bf2ad88b0980c186245','TITLE'=>$value['trackback_title'],'LINK'=>$value['trackback_url'],'EXCERPT'=>$value['trackback_excerpt'])));
		}

		if ((count($trackbacks)<1) && ($type=='xml')) $content->attach(do_template('TRACKBACK_XML_ERROR',array('_GUID'=>'945e2fcb510816caf323ba3704209430','TRACKBACK_ERROR'=>do_lang_tempcode('NO_TRACKBACKS'))));

		if ($type=='') $output=do_template('TRACKBACK_WRAPPER',array('_GUID'=>'1bc2c42a54fdf4b0a10e8e1ea45f6e4f','TRACKBACKS'=>$content,'TRACKBACK_PAGE'=>$trackback_for_type,'TRACKBACK_ID'=>$id,'TRACKBACK_TITLE'=>$CURRENT_SCREEN_TITLE));
		else
		{
			//global $CURRENT_SCREEN_TITLE;

			$content->attach(do_template('TRACKBACK_XML_LISTING',array('_GUID'=>'3bff402f15395f4648a2b5af33de8285','ITEMS'=>$items,'LINK_PAGE'=>$trackback_for_type,'LINK_ID'=>$id)));
			$output=$content;
		}
	}
	else $output=new ocp_tempcode();

	return $output;
}

/**
 * Add trackbacks to the specified resource.
 *
 * @param  boolean		Whether this resource allows trackback (if not, this function does nothing - but it's nice to move out this common logic into the shared function)
 * @param  ID_TEXT		The type (download, etc) that this trackback is for
 * @param  ID_TEXT		The ID of the type that this trackback is for
 * @return boolean		Whether trackbacks are on
 */
function do_trackback($allow_trackbacks,$trackback_for_type,$id)
{
	if ((get_option('is_on_trackbacks')=='0') || (!$allow_trackbacks)) return false;

	$url=either_param('url',NULL);
	if (is_null($url)) return false;
	$title=either_param('title',$url);
	$excerpt=either_param('excerpt','');
	$name=either_param('blog_name',$url);

	$GLOBALS['SITE_DB']->query_insert('trackbacks',array('trackback_for_type'=>$trackback_for_type,'trackback_for_id'=>$id,'trackback_ip'=>get_ip_address(),'trackback_time'=>time(),'trackback_url'=>$url,'trackback_title'=>$title,'trackback_excerpt'=>$excerpt,'trackback_name'=>$name));

	return true;
}
