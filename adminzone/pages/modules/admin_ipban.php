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
 * @package		securitylogging
 */

/**
 * Module page class.
 */
class Module_admin_ipban
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
		$info['version']=4;
		$info['locked']=true;
		$info['update_require_upgrade']=1;
		return $info;
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_if_exists('usersubmitban_ip');
		$GLOBALS['SITE_DB']->drop_if_exists('usersubmitban_member');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		if (is_null($upgrade_from))
		{
			$GLOBALS['SITE_DB']->create_table('usersubmitban_ip',array(
				'ip'=>'*IP',
				'i_descrip'=>'LONG_TEXT',
			));

			$GLOBALS['SITE_DB']->create_table('usersubmitban_member',array(
				'the_member'=>'*USER',
			));
		}
		if ((!is_null($upgrade_from)) && ($upgrade_from<3))
		{
			$GLOBALS['SITE_DB']->drop_if_exists('usersubmit');
		}
		if ((!is_null($upgrade_from)) && ($upgrade_from<4))
		{
			$GLOBALS['SITE_DB']->add_table_field('usersubmitban_ip','i_descrip','LONG_TEXT');
		}
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'IP_BANS');
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/ipban';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_censor';

		require_lang('submitban');
		require_code('submit');

		// What are we doing?
		$type=get_param('type','misc');

		if ($type=='misc') return $this->gui();
		if ($type=='actual') return $this->actual();
		return new ocp_tempcode();
	}

	/**
	 * The UI for managing banned IPs.
	 *
	 * @return tempcode		The UI
	 */
	function gui()
	{
		$title=get_page_title('IP_BANS');

		$lookup_url=build_url(array('page'=>'admin_lookup'),get_module_zone('admin_lookup'));
		$GLOBALS['HELPER_PANEL_TEXT']=comcode_to_tempcode(do_lang('IP_BANNING_WILDCARDS',$lookup_url->evaluate()));

		$bans='';
		$rows=$GLOBALS['SITE_DB']->query_select('usersubmitban_ip',array('ip','i_descrip'));
		foreach ($rows as $row)
		{
			$bans.=$row['ip'].' '.str_replace("\n",' ',$row['i_descrip']).chr(10);
		}

		$post_url=build_url(array('page'=>'_SELF','type'=>'actual'),'_SELF');

		require_code('form_templates');

		list($warning_details,$ping_url)=handle_conflict_resolution();

		return do_template('IPBAN_SCREEN',array('_GUID'=>'963d24852ba87e9aa84e588862bcfecb','PING_URL'=>$ping_url,'WARNING_DETAILS'=>$warning_details,'TITLE'=>$title,'BANS'=>$bans,'URL'=>$post_url));
	}

	/**
	 * The actualiser for managing banned IPs.
	 *
	 * @return tempcode		The UI
	 */
	function actual()
	{
		require_code('failure');

		$old_bans=collapse_1d_complexity('ip',$GLOBALS['SITE_DB']->query_select('usersubmitban_ip'));
		$bans=post_param('bans');
		$_bans=explode(chr(10),$bans);
		foreach ($old_bans as $ban)
		{
			if (preg_match('#^'.preg_quote($ban,'#').'(\s|$)#m',$bans)==0)
			{
				remove_ip_ban($ban);
			}
		}
		$matches=array();
		foreach ($_bans as $ban)
		{
			if (trim($ban)=='') continue;
			preg_match('#^([^\s]+)(.*)$#',$ban,$matches);
			$ip=$matches[1];
			if (preg_match('#^[a-f0-9\.]+$#U',$ip)==0)
			{
				attach_message(do_lang_tempcode('IP_ADDRESS_NOT_VALID',$ban),'warn');
			} else
			{
				if ($ip==get_ip_address())
				{
					attach_message(do_lang_tempcode('WONT_BAN_SELF',$ban),'warn');
				}
				elseif ($ip==ocp_srv('SERVER_ADDR'))
				{
					attach_message(do_lang_tempcode('WONT_BAN_SERVER',$ban),'warn');
				}
				if (!in_array($ip,$old_bans))
				{
					ban_ip($ip,trim($matches[2]));
					$old_bans[]=$ip;
				}
			}
		}

		// Show it worked / Refresh
		$title=get_page_title('IP_BANS');
		$refresh_url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$refresh_url,do_lang_tempcode('SUCCESS'));
	}

}


