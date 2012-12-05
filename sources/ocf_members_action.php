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
 * Add a member.
 *
 * @param  SHORT_TEXT		The username.
 * @param  SHORT_TEXT		The password.
 * @param  SHORT_TEXT		The e-mail address.
 * @param  ?array				A list of usergroups (NULL: default/current usergroups).
 * @param  ?integer			Day of date of birth (NULL: unknown).
 * @param  ?integer			Month of date of birth (NULL: unknown).
 * @param  ?integer			Year of date of birth (NULL: unknown).
 * @param  array				A map of custom field values (field-id=>value).
 * @param  ?ID_TEXT			The member timezone (NULL: auto-detect).
 * @param  ?GROUP				The member's primary (NULL: default).
 * @param  BINARY				Whether the profile has been validated.
 * @param  ?TIME				When the member joined (NULL: now).
 * @param  ?TIME				When the member last visited (NULL: now).
 * @param  ID_TEXT			The member's default theme.
 * @param  ?URLPATH			The URL to the member's avatar (blank: none) (NULL: choose one automatically).
 * @param  LONG_TEXT			The member's signature (blank: none).
 * @param  BINARY				Whether the member is permanently banned.
 * @param  BINARY				Whether posts are previewed before they are made.
 * @param  BINARY				Whether the member's age may be shown.
 * @param  SHORT_TEXT		The member's title (blank: get from primary).
 * @param  URLPATH			The URL to the member's photo (blank: none).
 * @param  URLPATH			The URL to the member's photo thumbnail (blank: none).
 * @param  BINARY				Whether the member sees signatures in posts.
 * @param  ?BINARY			Whether the member automatically is enabled for notifications for content they contribute to (NULL: get default from config).
 * @param  ?LANGUAGE_NAME	The member's language (NULL: auto detect).
 * @param  BINARY				Whether the member allows e-mails via the site.
 * @param  BINARY				Whether the member allows e-mails from staff via the site.
 * @param  LONG_TEXT			Personal notes of the member.
 * @param  ?IP					The member's IP address (NULL: IP address of current user).
 * @param  SHORT_TEXT		The code required before the account becomes active (blank: already entered).
 * @param  boolean			Whether to check details for correctness.
 * @param  ?ID_TEXT			The compatibility scheme that the password operates in (blank: none) (NULL: none [meaning normal ocPortal salted style] or plain, depending on whether passwords are encrypted).
 * @param  SHORT_TEXT		The password salt (blank: password compatibility scheme does not use a salt / auto-generate).
 * @param  BINARY				Whether the member likes to view zones without menus, when a choice is available.
 * @param  ?TIME				The time the member last made a submission (NULL: set to now).
 * @param  ?AUTO_LINK		Force an ID (NULL: don't force an ID)
 * @param  BINARY				Whether the member username will be highlighted.
 * @param  SHORT_TEXT		Usergroups that may PT the member.
 * @param  LONG_TEXT			Rules that other members must agree to before they may start a PT with the member.
 * @return AUTO_LINK			The ID of the new member.
 */
function ocf_make_member($username,$password,$email_address,$groups,$dob_day,$dob_month,$dob_year,$custom_fields,$timezone=NULL,$primary_group=NULL,$validated=1,$join_time=NULL,$last_visit_time=NULL,$theme='',$avatar_url=NULL,$signature='',$is_perm_banned=0,$preview_posts=0,$reveal_age=1,$title='',$photo_url='',$photo_thumb_url='',$views_signatures=1,$auto_monitor_contrib_content=NULL,$language=NULL,$allow_emails=1,$allow_emails_from_staff=1,$personal_notes='',$ip_address=NULL,$validated_email_confirm_code='',$check_correctness=true,$password_compatibility_scheme=NULL,$salt='',$zone_wide=1,$last_submit_time=NULL,$id=NULL,$highlighted_name=0,$pt_allow='*',$pt_rules_text='')
{
	if (is_null($auto_monitor_contrib_content))
	{
		$auto_monitor_contrib_content=(get_value('no_auto_notifications')==='1')?0:1;
	}

	if (is_null($password_compatibility_scheme))
	{
		if (get_value('no_password_hashing')==='1')
		{
			$password_compatibility_scheme='plain';
		} else
		{
			$password_compatibility_scheme='';
		}
	}
	if (is_null($language)) $language='';
	if (is_null($signature)) $signature='';
	if (is_null($title)) $title='';
	if (is_null($timezone)) $timezone=get_site_timezone();
	if (is_null($allow_emails)) $allow_emails=1;
	if (is_null($allow_emails_from_staff)) $allow_emails_from_staff=1;
	if (is_null($personal_notes)) $personal_notes='';
	if (is_null($avatar_url))
	{
		if (($GLOBALS['IN_MINIKERNEL_VERSION']==1) || (!addon_installed('ocf_member_avatars')))
		{
			$avatar_url='';
		} else
		{
			if ((get_option('random_avatars')=='1') && (!running_script('stress_test_loader')))
			{
				require_code('themes2');
				$codes=get_all_image_ids_type('ocf_default_avatars/default_set',false,$GLOBALS['FORUM_DB']);
				shuffle($codes);
				$results=array();
				foreach ($codes as $code)
				{
					if (strpos($code,'ocp_fanatic')!==false) continue;

					$count=$GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT SUM(m_cache_num_posts) FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE '.db_string_equal_to('m_avatar_url',find_theme_image($code,false,true)));
					if (is_null($count)) $count=0;
					$results[$code]=$count;
				}
				@asort($results); // @'d as type checker fails for some odd reason
				$found_avatars=array_keys($results);
				$avatar_url=find_theme_image(array_shift($found_avatars),true,true);
			}

			if (is_null($avatar_url))
			{
				$GLOBALS['SITE_DB']->query_delete('theme_images',array('id'=>'ocf_default_avatars/default','path'=>'')); // In case failure cached, gets very confusing
				$avatar_url=find_theme_image('ocf_default_avatars/default',true,true);
				if (is_null($avatar_url))
					$avatar_url='';
			}
		}
	}

	if ($check_correctness)
	{
		if (!in_array($password_compatibility_scheme,array('ldap','httpauth'))) ocf_check_name_valid($username,NULL,($password_compatibility_scheme=='')?$password:NULL);
		if ((!function_exists('has_actual_page_access')) || (!has_actual_page_access(get_member(),'admin_ocf_join')))
		{
			require_code('type_validation');
			if ((!is_valid_email_address($email_address)) && ($email_address!=''))
				warn_exit(do_lang_tempcode('_INVALID_EMAIL_ADDRESS',escape_html($email_address)));
		}
	}

	require_code('ocf_members');
	require_code('ocf_groups');
	if (is_null($last_submit_time)) $last_submit_time=time();
	if (is_null($join_time)) $join_time=time();
	if (is_null($last_visit_time)) $last_visit_time=time();
	if (is_null($primary_group))
	{
		$primary_group=get_first_default_group(); // This is members
		$secondary_groups=ocf_get_all_default_groups();
	} else $secondary_groups=ocf_get_all_default_groups();
	if (is_null($ip_address)) $ip_address=get_ip_address();

	if ((($password_compatibility_scheme=='') || ($password_compatibility_scheme=='temporary')) && (get_value('no_password_hashing')==='1'))
	{
		$password_compatibility_scheme='plain';
		$salt='';
	}

	if (($salt=='') && (($password_compatibility_scheme=='') || ($password_compatibility_scheme=='temporary')))
	{
		$salt=produce_salt();
		$password_salted=md5($salt.md5($password));
	} else
	{
		$password_salted=$password;
	}

	// Supplement custom field values given with defaults, and check constraints
	if (is_null($groups)) $groups=ocf_get_all_default_groups(true);
	$all_fields=list_to_map('id',ocf_get_all_custom_fields_match($groups));
	require_code('fields');
	foreach ($all_fields as $field)
	{
		$field_id=$field['id'];

		if (array_key_exists($field_id,$custom_fields))
		{
			if (($check_correctness) && ($field[array_key_exists('cf_show_on_join_form',$field)?'cf_show_on_join_form':'cf_required']==0) && ($field['cf_owner_set']==0) && (!has_actual_page_access(get_member(),'admin_ocf_join')))
			{
				access_denied('I_ERROR');
			}
		} else
		{
			$custom_fields[$field_id]='';
		}
	}

	if (!addon_installed('unvalidated')) $validated=1;
	$map=array(
		'm_username'=>$username,
		'm_pass_hash_salted'=>$password_salted,
		'm_pass_salt'=>$salt,
		'm_theme'=>$theme,
		'm_avatar_url'=>$avatar_url,
		'm_validated'=>$validated,
		'm_validated_email_confirm_code'=>$validated_email_confirm_code,
		'm_cache_num_posts'=>0,
		'm_cache_warnings'=>0,
		'm_max_email_attach_size_mb'=>5,
		'm_join_time'=>$join_time,
		'm_timezone_offset'=>$timezone,
		'm_primary_group'=>$primary_group,
		'm_last_visit_time'=>$last_visit_time,
		'm_last_submit_time'=>$last_submit_time,
		'm_signature'=>insert_lang_comcode($signature,4,$GLOBALS['FORUM_DB']),
		'm_is_perm_banned'=>$is_perm_banned,
		'm_preview_posts'=>$preview_posts,
		'm_notes'=>$personal_notes,
		'm_dob_day'=>$dob_day,
		'm_dob_month'=>$dob_month,
		'm_dob_year'=>$dob_year,
		'm_reveal_age'=>$reveal_age,
		'm_email_address'=>$email_address,
		'm_title'=>$title,
		'm_photo_url'=>$photo_url,
		'm_photo_thumb_url'=>$photo_thumb_url,
		'm_views_signatures'=>$views_signatures,
		'm_auto_monitor_contrib_content'=>$auto_monitor_contrib_content,
		'm_highlighted_name'=>$highlighted_name,
		'm_pt_allow'=>$pt_allow,
		'm_pt_rules_text'=>insert_lang_comcode($pt_rules_text,4,$GLOBALS['FORUM_DB']),
		'm_language'=>$language,
		'm_ip_address'=>$ip_address,
		'm_zone_wide'=>$zone_wide,
		'm_allow_emails'=>$allow_emails,
		'm_allow_emails_from_staff'=>$allow_emails_from_staff,
		'm_password_change_code'=>'',
		'm_password_compat_scheme'=>$password_compatibility_scheme,
		'm_on_probation_until'=>NULL
	);
	if (!is_null($id)) $map['id']=$id;
	$member_id=$GLOBALS['FORUM_DB']->query_insert('f_members',$map,true);

	if ($check_correctness)
	{
		// If it was an invite/recommendation, award the referrer
		if (addon_installed('recommend'))
		{
			$inviter=$GLOBALS['FORUM_DB']->query_value_null_ok('f_invites','i_inviter',array('i_email_address'=>$email_address),'ORDER BY i_time');
			if (!is_null($inviter))
			{
				if (addon_installed('points'))
				{
					require_code('points2');
					require_lang('recommend');
					system_gift_transfer(do_lang('RECOMMEND_SITE_TO',$username,get_site_name()),intval(get_option('points_RECOMMEND_SITE')),$inviter);
				}
				if (addon_installed('chat'))
				{
					require_code('chat2');
					friend_add($inviter,$member_id);
					friend_add($member_id,$inviter);
				}
			}
		}
	}

	$value=mixed();

	// Store custom fields
	$row=array('mf_member_id'=>$member_id);
	$all_fields_types=collapse_2d_complexity('id','cf_type',$all_fields);
	foreach ($custom_fields as $field_num=>$value)
	{
		if (!array_key_exists($field_num,$all_fields_types)) continue; // Trying to set a field we're not allowed to (doesn't apply to our group)

		$ob=get_fields_hook($all_fields_types[$field_num]);
		list(,,$storage_type)=$ob->get_field_value_row_bits($all_fields[$field_num]);

		if (strpos($storage_type,'_trans')!==false)
		{
			$value=insert_lang($value,3,$GLOBALS['FORUM_DB']);
		}
		$row['field_'.strval($field_num)]=$value;
	}

	// Set custom field row
	$all_fields_regardless=$GLOBALS['FORUM_DB']->query_select('f_custom_fields',array('id','cf_type'));
	foreach ($all_fields_regardless as $field)
	{
		if (!array_key_exists('field_'.strval($field['id']),$row))
		{
			$ob=get_fields_hook($field['cf_type']);
			list(,,$storage_type)=$ob->get_field_value_row_bits($field);

			$value='';
			if (strpos($storage_type,'_trans')!==false)
			{
				$value=insert_lang($value,3,$GLOBALS['FORUM_DB']);
			}
			$row['field_'.strval($field['id'])]=$value;
		}
	}
	$GLOBALS['FORUM_DB']->query_insert('f_member_custom_fields',$row);

	// Any secondary work
	foreach ($secondary_groups as $g)
	{
		if ($g!=$primary_group)
		{
			$GLOBALS['FORUM_DB']->query_insert('f_group_members',array(
				'gm_group_id'=>$g,
				'gm_member_id'=>$member_id,
				'gm_validated'=>1
			));
		}
	}

	if ($check_correctness)
	{
		if (function_exists('decache')) decache('side_stats');
	}

	return $member_id;
}

/**
 * Make a custom profile field from one of the predefined templates (this is often used by importers).
 *
 * @param  ID_TEXT	The identifier of the boiler custom profile field.
 * @return AUTO_LINK The ID of the new custom profile field.
 */
function ocf_make_boiler_custom_field($type)
{
	$_type='long_trans';

	if (substr($type,0,3)=='im_' || substr($type,0,3)=='sn_') $_type='short_text';

	elseif ($type=='location') $_type='short_text';
	elseif ($type=='occupation') $_type='short_text';
	elseif ($type=='website') $_type='short_trans';

	$public_view=1;
	$owner_view=1;
	$owner_set=1;

	if ($type=='staff_notes')
	{
		$public_view=0;
		$owner_view=0;
		$owner_set=0;
	}

	global $CUSTOM_FIELD_CACHE;
	$CUSTOM_FIELD_CACHE=array();

	return ocf_make_custom_field(do_lang('DEFAULT_CPF_'.$type.'_NAME'),0,do_lang('DEFAULT_CPF_'.$type.'_DESCRIPTION'),'',$public_view,$owner_view,$owner_set,0,$_type,0,0,0,NULL,'',true);
}

/**
 * Find how to store a field in the database.
 *
 * @param  ID_TEXT	The field type.
 * @return array		A pair: the DB field type, whether to index.
 */
function get_cpf_storage_for($type)
{
	require_code('fields');
	$ob=get_fields_hook($type);
	list(,,$storage_type)=$ob->get_field_value_row_bits(array('id'=>NULL,'cf_type'=>$type,'cf_default'=>''));
	$_type='SHORT_TEXT';
	switch ($storage_type)
	{
		case 'short_trans':
			$_type='SHORT_TRANS';
			break;
		case 'long_trans':
			$_type='LONG_TRANS';
			break;
		case 'long':
			$_type='LONG_TEXT';
			break;
	}

	$index=true;
	switch ($type)
	{
		case 'short_trans':
		case 'short_trans_multi':
		case 'long_trans':
		case 'posting_field':
		case 'tick':
		case 'integer':
		case 'float':
		case 'color':
		case 'content_link':
		case 'date':
		case 'just_date':
		case 'just_time':
		case 'picture':
		case 'password':
		case 'page_link':
		case 'upload':
			$index=false;
			break;
	}

	return array($_type,$index);
}

/**
 * Make a custom profile field.
 *
 * @param  SHORT_TEXT	Name of the field.
 * @param  BINARY			Whether the field is locked (i.e. cannot be deleted from the system).
 * @param  SHORT_TEXT 	Description of the field.
 * @param  LONG_TEXT  	The default value for the field.
 * @param  BINARY			Whether the field is publicly viewable.
 * @param  BINARY			Whether the field is viewable by the owner.
 * @param  BINARY			Whether the field may be set by the owner.
 * @param  BINARY			Whether the field is encrypted.
 * @param  ID_TEXT		The type of the field.
 * @set    short_text long_text short_trans long_trans integer upload picture url list tick float
 * @param  BINARY			Whether it is required that every member have this field filled in.
 * @param  BINARY			Whether this field is shown in posts and places where member details are highlighted (such as an image in a member gallery).
 * @param  BINARY			Whether this field is shown in preview places, such as in the teaser for a member gallery.
 * @param  ?integer		The order of this field relative to other fields (NULL: next).
 * @param  LONG_TEXT 	The usergroups that this field is confined to (comma-separated list).
 * @param  boolean		Whether to check that no field has this name already.
 * @param  BINARY			Whether the field is to be shown on the join form
 * @return AUTO_LINK  	The ID of the new custom profile field.
 */
function ocf_make_custom_field($name,$locked=0,$description='',$default='',$public_view=0,$owner_view=0,$owner_set=0,$encrypted=0,$type='long_text',$required=0,$show_in_posts=0,$show_in_post_previews=0,$order=NULL,$only_group='',$no_name_dupe=false,$show_on_join_form=0)
{
	$dbs_back=$GLOBALS['NO_DB_SCOPE_CHECK'];
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;

	if ($only_group=='-1') $only_group='';

	// Can only encrypt things if encryption support is available
	require_code('encryption');
	//if (!is_encryption_enabled()) $encrypted=0;

	// Can't have publicly-viewable encrypted fields
	if ($encrypted==1)
	{
		$public_view=0;
	}

	if ($no_name_dupe)
	{
		$test=$GLOBALS['FORUM_DB']->query_value_null_ok('f_custom_fields f LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'translate t ON f.cf_name=t.id','f.id',array('text_original'=>$name));
		if (!is_null($test))
		{
			$GLOBALS['NO_DB_SCOPE_CHECK']=$dbs_back;
			return $test;
		}
	}

	if (is_null($order))
	{
		$order=$GLOBALS['FORUM_DB']->query_value('f_custom_fields','MAX(cf_order)');
		if (is_null($order)) $order=0; else $order++;
	}

	$map=array(
		'cf_name'=>insert_lang($name,2,$GLOBALS['FORUM_DB']),
		'cf_locked'=>$locked,
		'cf_description'=>insert_lang($description,2,$GLOBALS['FORUM_DB']),
		'cf_default'=>$default,
		'cf_public_view'=>$public_view,
		'cf_owner_view'=>$owner_view,
		'cf_owner_set'=>$owner_set,
		'cf_type'=>$type,
		'cf_required'=>$required,
		'cf_show_in_posts'=>$show_in_posts,
		'cf_show_in_post_previews'=>$show_in_post_previews,
		'cf_order'=>$order,
		'cf_only_group'=>$only_group,
		'cf_show_on_join_form'=>$show_on_join_form
	);
	$id=$GLOBALS['FORUM_DB']->query_insert('f_custom_fields',$map+array('cf_encrypted'=>$encrypted),true,true);
	if (is_null($id)) $id=$GLOBALS['FORUM_DB']->query_insert('f_custom_fields',$map,true); // Still upgrading, cf_encrypted does not exist yet

	list($_type,$index)=get_cpf_storage_for($type);

	require_code('database_action');
	// ($index?'#':'').
	$GLOBALS['FORUM_DB']->add_table_field('f_member_custom_fields','field_'.strval($id),$_type); // Default will be made explicit when we insert rows
	if ($index)
	{
		$indices_count=$GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT COUNT(*) FROM '.get_table_prefix().'f_custom_fields WHERE '.db_string_not_equal_to('cf_type','integer').' AND '.db_string_not_equal_to('cf_type','tick').' AND '.db_string_not_equal_to('cf_type','long_trans').' AND '.db_string_not_equal_to('cf_type','short_trans'));
		if ($indices_count<60) // Could be 64 but trying to be careful here...
		{
			$GLOBALS['FORUM_DB']->create_index('f_member_custom_fields','#mcf'.strval($id),array('field_'.strval($id)),'mf_member_id');
		}
	}

	log_it('ADD_CUSTOM_PROFILE_FIELD',strval($id),$name);

	$GLOBALS['NO_DB_SCOPE_CHECK']=$dbs_back;
	return $id;
}


