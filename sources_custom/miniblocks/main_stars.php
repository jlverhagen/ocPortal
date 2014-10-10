<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.

*/

i_solemnly_declare(I_UNDERSTAND_SQL_INJECTION | I_UNDERSTAND_XSS | I_UNDERSTAND_PATH_INJECTION);

require_code('ocf_groups');
require_code('ocf_members');
require_lang('ocf');

echo '<div class="wide_table_wrap"><table class="wide_table results_table spaced_table autosized_table">';
echo '<tr><th>'.do_lang('AVATAR').'</th><th>'.do_lang('DETAILS').'</th><th>'.do_lang('SIGNATURE').'</th></tr>';

if (multi_lang_content())
{
	$sql='SELECT gift_to,SUM(amount) as cnt FROM '.get_table_prefix().'gifts g WHERE '.$GLOBALS['SITE_DB']->translate_field_ref('reason').' LIKE \''.db_encode_like($map['param']).': %\' AND gift_from<>'.strval($GLOBALS['FORUM_DRIVER']->get_guest_id()).' GROUP BY gift_to ORDER BY cnt DESC';
} else
{
	$sql='SELECT gift_to,SUM(amount) as cnt FROM '.get_table_prefix().'gifts g WHERE reason LIKE \''.db_encode_like($map['param']).': %\' AND gift_from<>'.strval($GLOBALS['FORUM_DRIVER']->get_guest_id()).' GROUP BY gift_to ORDER BY cnt DESC';
}
$gifts=$GLOBALS['SITE_DB']->query($sql,10);
$count=0;
foreach ($gifts as $gift)
{
	$member_id=$gift['gift_to'];
	$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id,true);
	if (!is_null($username))
	{
		$link=$GLOBALS['FORUM_DRIVER']->member_profile_url($member_id);
		$avatar_url=$GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id);
		$signature=get_translated_tempcode('gifts',$GLOBALS['FORUM_DRIVER']->get_member_row($member_id),'m_signature',$GLOBALS['FORUM_DB']);
		$points=$gift['cnt'];
		$rank=get_translated_text(ocf_get_group_property(ocf_get_member_primary_group($member_id),'name'),$GLOBALS['FORUM_DB']);
		if ($avatar_url=='')
		{
			$avatar='';
		} else
		{
			$avatar='<img style="max-width: 100%" alt="" src="'.escape_html($avatar_url).'" />';
		}
		echo '<tr><td>'.$avatar.'</td><td>'.do_lang('MEMBER').': <a href="'.escape_html($link).'">'.escape_html($username).'</a><br /><br />Role points: '.integer_format($points).'<br /><br />'.do_lang('RANK').': '.$rank.'</td><td style="font-size: 0.8em;">'.$signature->evaluate().'</td></td>';

		$count++;
	}
}
if ($count==0)
{
	echo '<tr><td colspan="3" style="font-weight: bold; padding: 10px">Nobody yet &ndash; could you be here?</td></tr>';
}

echo '</table></div>';
