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
 * @package		core_ocf
 */

class Hook_addon_registry_core_ocf
{

	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'The ocPortal member/usergroup system.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array(),
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(

			'sources/hooks/systems/snippets/email_exists.php',
			'sources/hooks/systems/snippets/invite_missing.php',
			'sources/hooks/systems/snippets/exists_usergroup.php',
			'sources/hooks/systems/snippets/exists_emoticon.php',
			'themes/default/images/pagepics/usergroups_temp.png',
			'themes/default/images/bigicons/usergroups_temp.png',
			'sources/hooks/systems/config_default/club_forum_parent_category.php',
			'sources/hooks/systems/config_default/club_forum_parent_forum.php',
			'sources/hooks/systems/config_default/allow_alpha_search.php',
			'sources/hooks/systems/config_default/allow_email_disable.php',
			'sources/hooks/systems/config_default/allow_international.php',
			'sources/hooks/systems/config_default/max_member_title_length.php',
			'sources/hooks/systems/config_default/random_avatars.php',
			'sources/hooks/systems/config_default/decryption_key.php',
			'sources/hooks/systems/config_default/default_preview_guests.php',
			'sources/hooks/systems/config_default/delete_trashed_pts.php',
			'sources/hooks/systems/config_default/encryption_key.php',
			'sources/hooks/systems/config_default/forced_preview_option.php',
			'sources/hooks/systems/config_default/forum_posts_per_page.php',
			'sources/hooks/systems/config_default/forum_topics_per_page.php',
			'sources/hooks/systems/config_default/hot_topic_definition.php',
			'sources/hooks/systems/config_default/httpauth_is_enabled.php',
			'sources/hooks/systems/config_default/invites_per_day.php',
			'sources/hooks/systems/config_default/is_on_anonymous_posts.php',
			'sources/hooks/systems/config_default/is_on_coppa.php',
			'sources/hooks/systems/config_default/is_on_invisibility.php',
			'sources/hooks/systems/config_default/is_on_invites.php',
			'sources/hooks/systems/config_default/is_on_post_titles.php',
			'sources/hooks/systems/config_default/is_on_timezone_detection.php',
			'sources/hooks/systems/config_default/is_on_topic_descriptions.php',
			'sources/hooks/systems/config_default/is_on_topic_emoticons.php',
			'sources/hooks/systems/config_default/maximum_password_length.php',
			'sources/hooks/systems/config_default/maximum_username_length.php',
			'sources/hooks/systems/config_default/minimum_password_length.php',
			'sources/hooks/systems/config_default/minimum_username_length.php',
			'sources/hooks/systems/config_default/no_dob_ask.php',
			'sources/hooks/systems/config_default/one_per_email_address.php',
			'sources/hooks/systems/config_default/overt_whisper_suggestion.php',
			'sources/hooks/systems/config_default/post_history_days.php',
			'sources/hooks/systems/config_default/prevent_shouting.php',
			'sources/hooks/systems/config_default/privacy_fax.php',
			'sources/hooks/systems/config_default/privacy_postal_address.php',
			'sources/hooks/systems/config_default/probation_usergroup.php',
			'sources/hooks/systems/config_default/prohibit_password_whitespace.php',
			'sources/hooks/systems/config_default/prohibit_username_whitespace.php',
			'sources/hooks/systems/config_default/reported_posts_forum.php',
			'sources/hooks/systems/config_default/require_new_member_validation.php',
			'sources/hooks/systems/config_default/restricted_usernames.php',
			'sources/hooks/systems/config_default/send_staff_message_post_validation.php',
			'sources/hooks/systems/config_default/show_first_join_page.php',
			'sources/hooks/systems/config_default/skip_email_confirm_join.php',
			'sources/hooks/systems/realtime_rain/ocf.php',
			'sources/hooks/systems/content_meta_aware/member.php',
			'sources/hooks/systems/content_meta_aware/group.php',
			'sources/hooks/systems/disposable_values/ocf_member_count.php',
			'sources/hooks/systems/disposable_values/ocf_topic_count.php',
			'sources/hooks/systems/disposable_values/ocf_post_count.php',
			'sources/hooks/systems/disposable_values/ocf_newest_member_id.php',
			'sources/hooks/systems/disposable_values/ocf_newest_member_username.php',
			'sources/hooks/modules/members/.htaccess',
			'sources/hooks/modules/members/index.html',
			'sources/hooks/modules/search/ocf_members.php',
			'sources/hooks/systems/addon_registry/core_ocf.php',
			'adminzone/pages/modules/admin_ocf_emoticons.php',
			'adminzone/pages/modules/admin_ocf_groups.php',
			'adminzone/pages/modules/admin_ocf_merge_members.php',
			'adminzone/pages/modules/admin_ocf_join.php',
			'sources/hooks/modules/admin_import/csv_members.php',
			'OCF_EMOTICON_CELL.tpl',
			'OCF_EMOTICON_ROW.tpl',
			'OCF_EMOTICON_TABLE.tpl',
			'OCF_JOIN_STEP1_SCREEN.tpl',
			'OCF_MEMBERS_ONLINE_SCREEN.tpl',
			'OCF_MEMBER_ACTION.tpl',
			'OCF_MEMBER_DIRECTORY_SCREEN.tpl',
			'OCF_MEMBER_ONLINE_ROW.tpl',
			'OCF_MEMBER_PROFILE_SCREEN.tpl',
			'OCF_MEMBER_PROFILE_ABOUT.tpl',
			'OCF_MEMBER_PROFILE_EDIT.tpl',
			'JAVASCRIPT_PROFILE.tpl',
			'OCF_USER_MEMBER.tpl',
			'OCF_VIEW_GROUP_SCREEN.tpl',
			'OCF_VIEW_GROUP_MEMBER.tpl',
			'OCF_VIEW_GROUP_MEMBER_PROSPECTIVE.tpl',
			'OCF_VIEW_GROUP_MEMBER_SECONDARY.tpl',
			'validateip.php',
			'themes/default/images/pagepics/usergroups.png',
			'themes/default/images/ocf_emoticons/birthday.png',
			'themes/default/images/EN/ocf_emoticons/index.html',
			'themes/default/images/ocf_emoticons/angry.png',
			'themes/default/images/ocf_emoticons/blink.gif',
			'themes/default/images/ocf_emoticons/blush.png',
			'themes/default/images/ocf_emoticons/cheeky.png',
			'themes/default/images/ocf_emoticons/christmas.png',
			'themes/default/images/ocf_emoticons/confused.png',
			'themes/default/images/ocf_emoticons/constipated.png',
			'themes/default/images/ocf_emoticons/cool.png',
			'themes/default/images/ocf_emoticons/cry.png',
			'themes/default/images/ocf_emoticons/cyborg.png',
			'themes/default/images/ocf_emoticons/depressed.png',
			'themes/default/images/ocf_emoticons/devil.gif',
			'themes/default/images/ocf_emoticons/drool.png',
			'themes/default/images/ocf_emoticons/dry.png',
			'themes/default/images/ocf_emoticons/glee.png',
			'themes/default/images/ocf_emoticons/grin.png',
			'themes/default/images/ocf_emoticons/guitar.gif',
			'themes/default/images/ocf_emoticons/hand.png',
			'themes/default/images/ocf_emoticons/hippie.png',
			'themes/default/images/ocf_emoticons/index.html',
			'themes/default/images/ocf_emoticons/king.png',
			'themes/default/images/ocf_emoticons/kiss.png',
			'themes/default/images/ocf_emoticons/lol.gif',
			'themes/default/images/ocf_emoticons/mellow.png',
			'themes/default/images/ocf_emoticons/nerd.png',
			'themes/default/images/ocf_emoticons/ninja2.gif',
			'themes/default/images/ocf_emoticons/nod.gif',
			'themes/default/images/ocf_emoticons/offtopic.png',
			'themes/default/images/ocf_emoticons/party.png',
			'themes/default/images/ocf_emoticons/ph34r.png',
			'themes/default/images/ocf_emoticons/puppyeyes.png',
			'themes/default/images/ocf_emoticons/rockon.gif',
			'themes/default/images/ocf_emoticons/rolleyes.gif',
			'themes/default/images/ocf_emoticons/sad.png',
			'themes/default/images/ocf_emoticons/sarcy.png',
			'themes/default/images/ocf_emoticons/shake.gif',
			'themes/default/images/ocf_emoticons/shocked.png',
			'themes/default/images/ocf_emoticons/shutup.gif',
			'themes/default/images/ocf_emoticons/sick.png',
			'themes/default/images/ocf_emoticons/sinner.png',
			'themes/default/images/ocf_emoticons/smile.png',
			'themes/default/images/ocf_emoticons/thumbs.png',
			'themes/default/images/ocf_emoticons/upsidedown.png',
			'themes/default/images/ocf_emoticons/whistle.png',
			'themes/default/images/ocf_emoticons/wink.png',
			'themes/default/images/ocf_emoticons/wub.png',
			'themes/default/images/ocf_emoticons/zzz.png',
			'themes/default/images/ocf_emoticons/none.png',
			'lang/EN/ocf.ini',
			'lang/EN/ocf_config.ini',
			'sources/forum/ocf.php',
			'sources/ocf_forum_driver_helper.php',
			'sources/ocf_forum_driver_helper_install.php',
			'sources/hooks/modules/admin_cleanup/ocf.php',
			'sources/hooks/modules/admin_cleanup/ocf_members.php',
			'sources/hooks/modules/admin_unvalidated/ocf_members.php',
			'sources/hooks/systems/ocf_cpf_filter/.htaccess',
			'sources/hooks/systems/ocf_cpf_filter/index.html',
			'sources/hooks/systems/ocf_cpf_filter/sms.php',
			'sources/hooks/systems/rss/ocf_birthdays.php',
			'sources/hooks/systems/rss/ocf_members.php',
			'sources/ocf_forums.php',
			'sources/ocf_forums2.php',
			'sources/ocf_forums_action.php',
			'sources/ocf_forums_action2.php',
			'sources/ocf_general.php',
			'sources/ocf_notifications.php',
			'sources/ocf_general_action.php',
			'sources/ocf_general_action2.php',
			'sources/ocf_groups.php',
			'sources/ocf_groups2.php',
			'sources/ocf_groups_action.php',
			'sources/ocf_groups_action2.php',
			'sources/ocf_install.php',
			'sources/ocf_members.php',
			'sources/ocf_members2.php',
			'sources/ocf_members_action.php',
			'sources/ocf_members_action2.php',
			'sources/ocf_moderation.php',
			'sources/ocf_moderation_action.php',
			'sources/ocf_moderation_action2.php',
			'sources/ocf_polls.php',
			'sources/ocf_polls_action.php',
			'sources/ocf_polls_action2.php',
			'sources/ocf_posts.php',
			'sources/ocf_posts2.php',
			'sources/ocf_posts_action.php',
			'sources/ocf_posts_action2.php',
			'sources/ocf_posts_action3.php',
			'sources/ocf_topics.php',
			'sources/ocf_topics_action.php',
			'sources/ocf_topics_action2.php',
			'site/pages/modules/groups.php',
			'site/pages/modules/members.php',
			'site/pages/modules/contactmember.php',
			'site/pages/modules/onlinemembers.php',
			'sources/hooks/modules/admin_occle_fs/members.php',
			'sources/hooks/systems/awards/member.php',
			'sources/hooks/systems/awards/group.php',
			'sources/hooks/modules/admin_stats/ocf_demographics.php',
			'themes/default/images/bigicons/statistics_demographics.png',
			'themes/default/images/bigicons/customprofilefields.png',
			'themes/default/images/pagepics/customprofilefields.png',
			'themes/default/images/bigicons/addmember.png',
			'themes/default/images/bigicons/editmember.png',
			'themes/default/images/bigicons/emoticons.png',
			'themes/default/images/bigicons/merge_members.png',
			'themes/default/images/EN/page/join.png',
			'themes/default/images/pagepics/addmember.png',
			'themes/default/images/pagepics/editmember.png',
			'themes/default/images/pagepics/emoticons.png',
			'themes/default/images/pagepics/mergemembers.png',
			'pages/modules/join.php',
			'sources/ocf_join.php',
			'pages/modules/lostpassword.php',
			'OCF_AUTO_TIME_ZONE_ENTRY.tpl',
			'OCF_DELURK_CONFIRM.tpl',
			'OCF_JOIN_STEP2_SCREEN.tpl',
			'lang/EN/ocf_lurkers.ini',
			'sources/ocf_profiles.php',
			'sources/hooks/systems/profiles_tabs/index.html',
			'sources/hooks/systems/profiles_tabs/about.php',
			'sources/hooks/systems/profiles_tabs/edit.php',
			'sources/hooks/systems/profiles_tabs_edit/index.html',
			'sources/hooks/systems/profiles_tabs_edit/profile.php',
			'sources/hooks/systems/profiles_tabs_edit/delete.php',
			'themes/default/images/pagepics/deletelurkers.png',
			'themes/default/images/bigicons/deletelurkers.png',
			'themes/default/images/bigicons/download_csv.png',
			'themes/default/images/pagepics/import_csv.png',
			'themes/default/images/bigicons/import_csv.png',
			'sources/ocf_popups.php',
			'sources/hooks/systems/preview/ocf_emoticon.php',
			'sources/hooks/systems/cron/ocf_confirm_reminder.php',
			'themes/default/images/EN/page/invite_member.png',
			'OCF_TOPIC_POST_AVATAR.tpl',
			'OCF_GROUP_DIRECTORY_SCREEN.tpl',
			'ocf.css',
			'data/username_check.php',
			'sources/hooks/systems/ocf_auth/aef.php',
			'sources/hooks/systems/ocf_auth/converge.php',
			'sources/hooks/systems/ocf_auth/phpbb3.php',
			'sources/hooks/systems/ocf_auth/smf.php',
			'sources/hooks/systems/ocf_auth/vb3.php',
			'sources/hooks/systems/ocf_auth/index.html',
			'POSTING_FIELD.tpl',
			'OCF_POSTER_DETAILS.tpl',
			'OCF_RANK_IMAGE.tpl',
		);
	}


	/**
	* Get mapping between template names and the method of this class that can render a preview of them
	*
	* @return array			The mapping
	*/
	function tpl_previews()
	{
		return array(
				'OCF_DELURK_CONFIRM.tpl'=>'administrative__ocf_delurk_confirm',
				'OCF_JOIN_STEP1_SCREEN.tpl'=>'ocf_join_step1_screen',
				'OCF_JOIN_STEP2_SCREEN.tpl'=>'ocf_join_step2_screen',
				'OCF_AUTO_TIME_ZONE_ENTRY.tpl'=>'ocf_auto_time_zone_entry',
				'OCF_USER_MEMBER.tpl'=>'ocf_user_member',
				'OCF_MEMBER_ACTION.tpl'=>'ocf_member_profile_screen',
				'OCF_EMOTICON_ROW.tpl'=>'ocf_emoticon_table',
				'OCF_EMOTICON_CELL.tpl'=>'ocf_emoticon_table',
				'OCF_EMOTICON_TABLE.tpl'=>'ocf_emoticon_table',
				'OCF_MEMBER_DIRECTORY_SCREEN.tpl'=>'ocf_member_directory_screen',
				'OCF_MEMBER_PROFILE_SCREEN.tpl'=>'ocf_member_profile_screen',
				'OCF_MEMBER_PROFILE_ABOUT.tpl'=>'ocf_member_profile_screen',
				'OCF_MEMBER_PROFILE_EDIT.tpl'=>'ocf_member_profile_screen',
				'OCF_MEMBER_ONLINE_ROW.tpl'=>'ocf_members_online_screen',
				'OCF_MEMBERS_ONLINE_SCREEN.tpl'=>'ocf_members_online_screen',
				'OCF_GROUP_DIRECTORY_SCREEN.tpl'=>'ocf_group_directory_screen',
				'OCF_VIEW_GROUP_MEMBER.tpl'=>'ocf_view_group_screen',
				'OCF_VIEW_GROUP_MEMBER_PROSPECTIVE.tpl'=>'ocf_view_group_screen',
				'OCF_VIEW_GROUP_MEMBER_SECONDARY.tpl'=>'ocf_view_group_screen',
				'OCF_VIEW_GROUP_SCREEN.tpl'=>'ocf_view_group_screen',
				);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__administrative__ocf_delurk_confirm()
	{
		return array(
			lorem_globalise(
				do_lorem_template('OCF_DELURK_CONFIRM',array(
					'TITLE'=>lorem_title(),
					'LURKERS'=>array(array('ID'=>"1",'USERNAME'=>lorem_word(),'PROFILE_URL'=>placeholder_url()),array('ID'=>"2",'USERNAME'=>lorem_word_2(),'PROFILE_URL'=>placeholder_url())),
					'URL'=>placeholder_url(),
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_join_step1_screen()
	{
		$group_select	=	new ocp_tempcode();

		foreach(placeholder_array() as $key=>$value)
			$group_select->attach(form_input_list_entry(strval($key),false,$value));

		return array(
			lorem_globalise(
				do_lorem_template('OCF_JOIN_STEP1_SCREEN',array(
					'TITLE'=>lorem_title(),
					'GENERATE_HOST'=>'',
					'RULES'=>lorem_chunk_html(),
					'URL'=>placeholder_url(),
					'GROUP_SELECT'=>$group_select,
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_join_step2_screen()
	{
		require_lang('dates');
		$fields = new ocp_tempcode();

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_LINE',array('MAXLENGTH'=>'3','TABINDEX'=>placeholder_number(),'REQUIRED'=>'','NAME'=>$name,'DEFAULT'=>''));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_PASSWORD',array('TABINDEX'=>placeholder_number(),'REQUIRED'=>'','NAME'=>$name,'VALUE'=>''));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_PASSWORD',array('TABINDEX'=>placeholder_number(),'REQUIRED'=>'','NAME'=>$name,'VALUE'=>''));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_EMAIL',array('TABINDEX'=>placeholder_number(),'REQUIRED'=>'','NAME'=>$name,'DEFAULT'=>''));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_DATE',array('NULL_OK'=>'','DISABLED'=>'','TABINDEX'=>placeholder_number(),'YEARS'=>placeholder_options(),'MONTHS'=>placeholder_options(),'DAYS'=>placeholder_options(),'STUB'=>$name,'NULL'=>'','TIME'=>''));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>true,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_TICK',array('CHECKED'=>'true','TABINDEX'=>placeholder_number(),'NAME'=>$name));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD_SPACER',array('TITLE'=>lorem_phrase(),'THEME_ALSO_INCLUDE_PAGES'=>false)));

		$name=lorem_word().placeholder_random();
		$timezone_list = new ocp_tempcode();
		foreach (placeholder_array() as $key=>$value)
		{
			$timezone_list->attach(do_lorem_template('OCF_AUTO_TIME_ZONE_ENTRY',array('HOUR'=>$value,'DW'=>placeholder_date_raw(),'NAME'=>$name,'SELECTED'=>'','CLASS'=>'','TEXT'=>lorem_phrase())));
		}
		$input = do_lorem_template('FORM_SCREEN_INPUT_LIST',array('TABINDEX'=>placeholder_number(),'REQUIRED'=>'','NAME'=>$name,'CONTENT'=>$timezone_list,'INLINE_LIST'=>false));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$name=lorem_word().placeholder_random();
		$input = do_lorem_template('FORM_SCREEN_INPUT_TICK',array('CHECKED'=>'true','TABINDEX'=>placeholder_number(),'NAME'=>$name));
		$fields->attach(do_lorem_template('FORM_SCREEN_FIELD',array('REQUIRED'=>true,'SKIP_LABEL'=>false,'BORING_NAME'=>$name,'NAME'=>lorem_phrase(),'DESCRIPTION'=>lorem_sentence_html(),'DESCRIPTION_SIDE'=>'','INPUT'=>$input,'COMCODE'=>'')));

		$form = do_lorem_template('FORM',array('TEXT'=>'','HIDDEN'=>'','FIELDS'=>$fields,'SUBMIT_NAME'=>do_lang_tempcode('PROCEED'),'URL'=>placeholder_url(),'BATCH_IMPORT_ARCHIVE_CONTENTS'=>lorem_phrase(),));

		return array(
			lorem_globalise(
				do_lorem_template('OCF_JOIN_STEP2_SCREEN',array(
					'JAVASCRIPT'=>'',
					'TITLE'=>lorem_title(),
					'FORM'=>$form,
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_auto_time_zone_entry()
	{
		return array(
			lorem_globalise(
				do_lorem_template('OCF_AUTO_TIME_ZONE_ENTRY',array(
					'HOUR'=>placeholder_number(),
					'DW'=>date('w',time()),
					'NAME'=>lorem_word(),
					'SELECTED'=>false,
					'CLASS'=>'',
					'TEXT'=>lorem_word(),
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_user_member()
	{
		return array(
			lorem_globalise(
				do_lorem_template('OCF_USER_MEMBER',array(
					'COLOUR'=>'',
					'PROFILE_URL'=>placeholder_url(),
					'USERNAME'=>lorem_word(),
					'USERGROUP'=>lorem_word_2(),
					'AT'=>lorem_phrase(),
				)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_emoticon_table()
	{
		$content=new ocp_tempcode();
		$cols=4;
		$current_row=new ocp_tempcode();
		for($i=0;$i<10;$i++)
		{
			if (($i%$cols==0) && ($i!=0))
			{
				$content->attach(do_lorem_template('OCF_EMOTICON_ROW',array('CELLS'=>$current_row)));
				$current_row=new ocp_tempcode();
			}
			$current_row->attach(do_lorem_template('OCF_EMOTICON_CELL',array('FIELD_NAME'=>lorem_word(),'CODE_ESC'=>'','THEME_IMG_CODE'=>'ocf_emoticons/smile','CODE'=>':)')));
		}
		if (!$current_row->is_empty())
			$content->attach(do_lorem_template('OCF_EMOTICON_ROW',array('CELLS'=>$current_row)));

		$content=do_lorem_template('OCF_EMOTICON_TABLE',array('ROWS'=>$content));

		return array(
			lorem_globalise(
				$content,NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_member_directory_screen()
	{
		return array(
			lorem_globalise(
				do_lorem_template('OCF_MEMBER_DIRECTORY_SCREEN',array(
					'USERGROUPS'=>lorem_phrase(),
					'HIDDEN'=>'',
					'MAX'=>'30',
					'SYMBOLS'=>array(array('START'=>'0','SYMBOL'=>'a'),array('START'=>'1','SYMBOL'=>'b'),array('START'=>'3','SYMBOL'=>'c')),
					'SEARCH'=>lorem_phrase(),
					'GET_URL'=>placeholder_url(),
					'TITLE'=>lorem_title(),
					'RESULTS_TABLE'=>placeholder_table(),
					'MEMBER_BOXES'=>array(),
					'RESULTS_BROWSER'=>'',
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_member_profile_screen()
	{
		require_lang('news');
		$sections=array('contact'=>lorem_word(),'profile'=>lorem_word_2(),'views'=>lorem_word(),'usage'=>lorem_word_2(),'content'=>lorem_word());
		$actions=array();
		$i	=	0;
		$links	=	new ocp_tempcode();
		foreach ($sections as $section_code=>$section_title)
		{
			$links->attach(do_lorem_template('OCF_MEMBER_ACTION',array('ID'=>"$i",'URL'=>placeholder_url(),'LANG'=>lorem_word(),'REL'=>'')));
			$actions[$section_code]=do_lorem_template('OCF_MEMBER_ACTION',array('ID'=>"$i",'URL'=>placeholder_url(),'LANG'=>lorem_word(),'REL'=>'','NAME'=>$section_title,'VALUE'=>$links));
			$i++;
		}

		require_lang('menus');
		
		$tabs=array();
		$tab_content=do_lorem_template('OCF_MEMBER_PROFILE_ABOUT',array(
			'RIGHT_MARGIN'=>lorem_phrase(),
			'AVATAR_WIDTH'=>placeholder_id(),
			'PHOTO_WIDTH'=>placeholder_id(),
			'MOST_ACTIVE_FORUM'=>lorem_phrase(),
			'TIME_FOR_THEM'=>placeholder_time(),
			'TIME_FOR_THEM_RAW'=>placeholder_date_raw(),
			'SUBMIT_DAYS_AGO'=>lorem_phrase(),
			'SUBMIT_TIME_RAW'=>placeholder_time(),
			'LAST_VISIT_TIME_RAW'=>placeholder_date_raw(),
			'ONLINE_NOW'=>lorem_phrase(),
			'_ONLINE_NOW'=>false,
			'BANNED'=>lorem_phrase(),
			'USER_AGENT'=>lorem_phrase(),
			'OPERATING_SYSTEM'=>lorem_phrase(),
			'DOB'=>lorem_phrase(),
			'IP_ADDRESS'=>lorem_phrase(),
			'COUNT_POSTS'=>placeholder_number(),
			'COUNT_POINTS'=>placeholder_number(),
			'PRIMARY_GROUP'=>lorem_phrase(),
			'PRIMARY_GROUP_ID'=>placeholder_id(),
			'PHOTO_URL'=>placeholder_image_url(),
			'PHOTO_THUMB_URL'=>placeholder_image_url(),
			'EMAIL_ADDRESS'=>lorem_word(),
			'AVATAR_URL'=>placeholder_avatar(),
			'SIGNATURE'=>lorem_phrase(),
			'JOIN_DATE'=>placeholder_time(),
			'JOIN_DATE_RAW'=>placeholder_date_raw(),
			'CUSTOM_FIELDS'=>array(array('NAME'=>lorem_phrase(),'VALUE'=>lorem_phrase(),'ENCRYPTED_VALUE'=>'')),
			'ACTIONS_contact'=>$actions['contact'],
			'ACTIONS_profile'=>$actions['profile'],
			'ACTIONS_views'=>$actions['views'],
			'ACTIONS_usage'=>$actions['usage'],
			'ACTIONS_content'=>$actions['content'],
			'USERNAME'=>lorem_word(),
			'MEMBER_ID'=>placeholder_id(),
			'SECONDARY_GROUPS'=>placeholder_array(),
			'VIEW_PROFILES'=>true,
			'ON_PROBATION'=>lorem_phrase(),
			'USERGROUP'=>lorem_word(),
			'CLUBS'=>lorem_phrase(),
		));
		$tabs[]=array('TAB_TITLE'=>lorem_title(),'TAB_CONTENT'=>$tab_content,'TAB_FIRST'=>true,'TAB_LAST'=>false);
		$tabs2=array();
		$tabs2[]=array('TAB_TITLE'=>lorem_title(),'TAB_FIELDS'=>new ocp_tempcode(),'TAB_TEXT'=>lorem_paragraph(),'TAB_FIRST'=>true,'TAB_LAST'=>true);
		$tab_content=do_lorem_template('OCF_MEMBER_PROFILE_EDIT',array(
			'URL'=>placeholder_url(),
			'SUBMIT_NAME'=>lorem_phrase(),
			'AUTOCOMPLETE'=>false,
			'SKIP_VALIDATION'=>true,
			'TABS'=>$tabs2,
		));
		$tabs[]=array('TAB_TITLE'=>lorem_title(),'TAB_CONTENT'=>$tab_content,'TAB_FIRST'=>false,'TAB_LAST'=>true);

		return array(
			lorem_globalise(
				do_lorem_template('OCF_MEMBER_PROFILE_SCREEN',array(
					'TITLE'=>lorem_title(),
					'MEMBER_ID'=>placeholder_id(),
					'TABS'=>$tabs,
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_members_online_screen()
	{
		$rows	=	new ocp_tempcode();
		foreach(placeholder_array() as $key=>$value)
		{
			$rows->attach(do_lorem_template('OCF_MEMBER_ONLINE_ROW',array('IP'=>placeholder_ip(),'AT_URL'=>placeholder_url(),'LOCATION'=>lorem_word(),'MEMBER'=>placeholder_link(),'TIME'=>placeholder_time())));
		}

		return array(
			lorem_globalise(
				do_lorem_template('OCF_MEMBERS_ONLINE_SCREEN',array(
					'TITLE'=>lorem_title(),
					'ROWS'=>$rows,
						)
			),NULL,'',true),
		);
	}
	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_group_directory_screen()
	{
		$_rank = array(placeholder_array());
		$ranks=array();
		foreach($_rank as $g_id=>$_rank)
		{
			$rank = new ocp_tempcode();
			foreach ($_rank as $row)
			{
				$entries = new ocp_tempcode();
				foreach (placeholder_array() as $k=>$v)
				{
					$cells = new ocp_tempcode();
					$values = array(placeholder_link(),placeholder_number(),lorem_word());

					foreach ($values as $k=>$v)
					{
						$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>$v),NULL,false,'RESULTS_TABLE_FIELD'));
					}
					$entries->attach(do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false,'RESULTS_TABLE_ENTRY'));
				}

				$rank->attach($entries);
			}

			$selectors = new ocp_tempcode();
			foreach (placeholder_array() as $k=>$v)
			{
				$selectors->attach(do_lorem_template('RESULTS_BROWSER_SORTER',array('SELECTED'=>'','NAME'=>lorem_word(),'VALUE'=>lorem_word())));
			}
			$sort = do_lorem_template('RESULTS_BROWSER_SORT',array('HIDDEN'=>'','SORT'=>lorem_word(),'RAND'=>placeholder_random(),'URL'=>placeholder_url(),'SELECTORS'=>$selectors));

			$results_browser = placeholder_result_browser();

			$fields_title = new ocp_tempcode();
			foreach (placeholder_array() as $k=>$v)
			{
				$fields_title->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>$v)));
			}

			$ranks[]=do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$rank,'MESSAGE'=>new ocp_tempcode(),'SORT'=>$sort,'BROWSER'=>$results_browser,'WIDTHS'=>array()),NULL,false,'RESULTS_TABLE');
		}

		return array(
			lorem_globalise(
				do_lorem_template('OCF_GROUP_DIRECTORY_SCREEN',array(
					'TITLE'=>lorem_title(),
					'STAFF'=>lorem_phrase(),
					'OTHERS'=>lorem_phrase(),
					'RANKS'=>$ranks,
						)
			),NULL,'',true),
		);
	}

	/**
	* Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	* Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	* Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	*
	* @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	*/
	function tpl_preview__ocf_view_group_screen()
	{
		$_primary_members =	placeholder_array();
		$primary_members = new ocp_tempcode();
		$_secondary_members = new ocp_tempcode();
		$secondary_members = new ocp_tempcode();
		$prospective_members = new ocp_tempcode();
		$_prospective_members = new ocp_tempcode();
		foreach ($_primary_members as $i=>$primary_member)
		{
			$temp=do_lorem_template('OCF_VIEW_GROUP_MEMBER',array('NAME'=>$primary_member,'URL'=>placeholder_url()));

			//results_entry starts
			$cells = do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>$temp),NULL,false);
			$entries = do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false);
			//results_entry ends

			$primary_members->attach($entries);
		}

		$fields_title = do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>lorem_word()));
		
		//results_table
		$primary_members = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$primary_members,'MESSAGE'=>'','SORT'=>'','BROWSER'=>'','WIDTHS'=>array()),NULL,false);

		$temp = new ocp_tempcode();
		foreach (placeholder_array() as $i=>$v)
		{
			$temp=do_lorem_template('OCF_VIEW_GROUP_MEMBER_SECONDARY',array(
						'URL'=>placeholder_url(),
						'REMOVE_URL'=>placeholder_url(),
						'NAME'=>$v,
							)
				);
			$cells = do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>$temp),NULL,false);
		/*	$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>"$i"),NULL,false));
			$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>"$i"),NULL,false));*/
			$entries = do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false);

			$_secondary_members->attach($entries);
		}
		$fields_title = do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>lorem_word()));
		/*$fields_title->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>lorem_word_2())));
		$fields_title->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>lorem_word_2())));*/

		//results_table
		$secondary_members = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$_secondary_members,'MESSAGE'=>'','SORT'=>'','BROWSER'=>'','WIDTHS'=>array()),NULL,false);

		foreach (placeholder_array() as $i=>$v)
		{
			$temp = do_lorem_template('OCF_VIEW_GROUP_MEMBER_PROSPECTIVE',array(
					'ACCEPT_URL'=>placeholder_url(),
					'DECLINE_URL'=>placeholder_url(),
					'NAME'=>lorem_word(),
					'URL'=>placeholder_url(),
						)
			);
			$cells = do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>$temp),NULL,false);
			//$cells->attach(do_lorem_template('RESULTS_TABLE_FIELD',array('VALUE'=>"$i"),NULL,false));
			$entries = do_lorem_template('RESULTS_TABLE_ENTRY',array('VALUES'=>$cells),NULL,false);

			$_prospective_members->attach($entries);
		}
		$fields_title = do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>lorem_word()));
		//$fields_title->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE',array('VALUE'=>lorem_word_2())));

		//results_table
		$prospective_members = do_lorem_template('RESULTS_TABLE',array('FIELDS_TITLE'=>$fields_title,'FIELDS'=>$_prospective_members,'MESSAGE'=>'','SORT'=>'','BROWSER'=>'','WIDTHS'=>array()),NULL,false);
		

		return array(
			lorem_globalise(
				do_lorem_template('OCF_VIEW_GROUP_SCREEN',array(
					'GROUP_NAME'=>lorem_phrase(),
					'ID'=>placeholder_id(),
					'FORUM'=>'',
					'CLUB'=>false,
					'EDIT_URL'=>placeholder_url(),
					'TITLE'=>lorem_title(),
					'LEADER'=>lorem_phrase(),
					'NAME'=>lorem_word(),
					'PROMOTION_INFO'=>new ocp_tempcode(),
					'ADD_URL'=>placeholder_url(),
					'APPLY_URL'=>placeholder_url(),
					'APPLY_TEXT'=>lorem_sentence(),
					'PRIMARY_MEMBERS'=>$primary_members,
					'SECONDARY_MEMBERS'=>$secondary_members,
					'PROSPECTIVE_MEMBERS'=>$prospective_members,
						)
			),NULL,'',true),
		);
	}
}