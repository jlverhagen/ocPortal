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

class Hook_cron_ocf_birthdays
{

	/**
	 * Standard modular run function for CRON hooks. Searches for tasks to perform.
	 */
	function run()
	{
		$this_birthday_day=date('d/m/Y');
		if (get_long_value('last_birthday_day')!==$this_birthday_day)
		{
			set_long_value('last_birthday_day',$this_birthday_day);

			$_birthdays=ocf_find_birthdays();
			$birthdays=new ocp_tempcode();
			foreach ($_birthdays as $_birthday)
			{
				$member_link=$GLOBALS['OCF_DRIVER']->member_profile_link($_birthday['id'],false,false);
				$username=$_birthday['username'];
				$birthday_link=build_url(array('page'=>'topics','type'=>'birthday','id'=>$_birthday['username']),get_module_zone('topics'));

				require_code('notifications');

				$subject=do_lang('BIRTHDAY_NOTIFICATION_MAIL_SUBJECT',get_site_name(),$username);
				$mail=do_lang('BIRTHDAY_NOTIFICATION_MAIL',comcode_escape(get_site_name()),comcode_escape($username),array($member_link->evaluate(),$birthday_link->evaluate()));

				if (addon_installed('chat'))
				{
					$friends=$GLOBALS['SITE_DB']->query_select('chat_buddies',array('member_likes'),array('member_liked'=>$_birthday['id']));
					dispatch_notification('ocf_friend_birthday',NULL,$subject,$mail,collapse_1d_complexity('member_likes',$friends));
				}

				dispatch_notification('ocf_birthday',NULL,$subject,$mail);
			}
		}
	}

}


