<?php

function init__ocf_join($in=NULL)
{
	// More referral fields in form
	$in=str_replace('list($fields,$_hidden)=ocf_get_member_fields(true,NULL,$groups);','list($fields,$_hidden)=ocf_get_member_fields(true,NULL,$groups); $fields->attach(get_referrer_field());',$in);

	// Better referral detection, and proper qualification management
	$in=str_replace("\$GLOBALS['FORUM_DB']->query_update('f_invites',array('i_taken'=>1),array('i_email_address'=>\$email_address,'i_taken'=>0),'',1);",'set_from_referrer_field();',$in);

	// Handle signup referrals
	$in=str_replace('return array($message);','require_code(\'referrals\'); assign_referral_awards($member_id,\'join\'); return array($message);',$in);

	return $in;
}

function get_referrer_field()
{
	require_lang('referrals');
	$by_url=get_param('keep_referrer','');
	if ($by_url!='')
	{
		if (is_numeric($by_url))
		{
			$by_url=$GLOBALS['FORUM_DRIVER']->get_username($by_url);
			if (is_null($by_url)) $by_url='';
		}
	}
	$field=form_input_username(do_lang_tempcode('TYPE_REFERRER'),do_lang_tempcode('DESCRIPTION_TYPE_REFERRER'),'referrer',$by_url,false,true);
	return $field;
}

function set_from_referrer_field()
{
	require_lang('referrals');

	$referrer=post_param('referrer','');
	if ($referrer=='') return; // NB: This doesn't mean failure, it may already have been set by the recommend module when the recommendation was *made*

	$referrer_member=$GLOBALS['FORUM_DB']->query_value_null_ok_full('SELECT id FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members WHERE '.db_string_equal_to('m_username',$referrer).' OR '.db_string_equal_to('m_email_address',$referrer));
	if (!is_null($referrer_member))
	{
		$GLOBALS['FORUM_DB']->query_delete('f_invites',array(
			'i_email_address'=>post_param('email_address'),
		),'',1); // Delete old invites for this email address
		$GLOBALS['FORUM_DB']->query_insert('f_invites',array(
			'i_inviter'=>$referrer_member,
			'i_email_address'=>post_param('email_address'),
			'i_time'=>time(),
			'i_taken'=>0
		));
	}
}
