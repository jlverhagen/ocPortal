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
 * @package		core_notifications
 */

/**
 * Module page class.
 */
class Module_admin_notifications
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
		$info['locked']=false;
		return $info;
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'NOTIFICATIONS_LOCKDOWN');
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_table_if_exists('notification_lockdown');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		$GLOBALS['SITE_DB']->create_table('notification_lockdown',array(
			'l_notification_code'=>'*ID_TEXT',
			'l_setting'=>'INTEGER',
		));
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		require_lang('notifications');

		$title=get_screen_title('NOTIFICATIONS_LOCKDOWN');

		require_css('notifications');
		require_javascript('javascript_notifications');
		require_code('notifications');
		require_code('notifications2');
		require_all_lang();

		$_notification_types=array(
			A__CHOICE=>'_CHOICE',
			A__STATISTICAL=>'_STATISTICAL',
		);
		$_notification_types=$_notification_types+_get_available_notification_types();

		$lockdown=collapse_2d_complexity('l_notification_code','l_setting',$GLOBALS['SITE_DB']->query_select('notification_lockdown',array('*')));

		$current_setting=mixed();

		$notification_sections=array();
		$hooks=find_all_hooks('systems','notifications');
		foreach (array_keys($hooks) as $hook)
		{
			if ((substr($hook,0,4)=='ocf_') && (get_forum_type()!='ocf')) continue;
			require_code('hooks/systems/notifications/'.$hook);
			$ob=object_factory('Hook_Notification_'.$hook);
			$_notification_codes=$ob->list_handled_codes();
			foreach ($_notification_codes as $notification_code=>$notification_details)
			{
				$allowed_setting=$ob->allowed_settings($notification_code);

				$current_setting=array_key_exists($notification_code,$lockdown)?$lockdown[$notification_code]:NULL;

				$notification_types=array();
				$save_query=false;
				foreach ($_notification_types as $possible=>$ntype)
				{
					$save_query=($save_query) || (post_param_integer('notification_'.$notification_code.'_'.$ntype,0)==1);
				}
				foreach ($_notification_types as $possible=>$ntype)
				{
					$available=($possible==A__CHOICE) || ($possible==A__STATISTICAL) || (($possible & $allowed_setting) != 0);

					if ($save_query)
					{
						$checked=false; // Will strictly read from POST
					} else
					{
						if (is_null($current_setting))
						{
							$checked=($possible==A__CHOICE);
						} else
						{
							if ($possible==A__STATISTICAL)
							{
								$checked=($current_setting==A__STATISTICAL);
							}
							elseif ($possible==A__CHOICE)
							{
								$checked=false;
							}
							elseif ($current_setting==-1)
							{
								$checked=false;
							} else
							{
								$checked=(($possible & $current_setting) != 0);
							}
						}
					}

					$_checked=post_param_integer('notification_'.$notification_code.'_'.$ntype,((strtoupper(ocp_srv('REQUEST_METHOD'))!='POST') && $checked)?1:0);

					$notification_types[]=array(
						'NTYPE'=>$ntype,
						'LABEL'=>do_lang_tempcode('ENABLE_NOTIFICATIONS_'.$ntype),
						'CHECKED'=>($_checked==1),
						'RAW'=>strval($possible),
						'AVAILABLE'=>$available,
						'SCOPE'=>$notification_code,
					);
				}

				if (!isset($notification_sections[$notification_details[0]]))
				{
					$notification_sections[$notification_details[0]]=array(
						'NOTIFICATION_SECTION'=>$notification_details[0],
						'NOTIFICATION_CODES'=>array(),
					);
				}
				$notification_sections[$notification_details[0]]['NOTIFICATION_CODES'][]=array(
					'NOTIFICATION_CODE'=>$notification_code,
					'NOTIFICATION_LABEL'=>$notification_details[1],
					'NOTIFICATION_TYPES'=>$notification_types,
					'SUPPORTS_CATEGORIES'=>false,
					'PRIVILEGED'=>!$ob->member_could_potentially_enable($ntype,$GLOBALS['FORUM_DRIVER']->get_guest_id()),
				);
			}
		}

		// Save
		if (strtoupper(ocp_srv('REQUEST_METHOD'))=='POST')
		{
			$GLOBALS['SITE_DB']->query_delete('notification_lockdown');

			foreach ($notification_sections as $notification_section)
			{
				foreach ($notification_section['NOTIFICATION_CODES'] as $notification_code)
				{
					$new_setting=A_NA;
					foreach ($notification_code['NOTIFICATION_TYPES'] as $notification_type)
					{
						$ntype=$notification_type['NTYPE'];
						if (post_param_integer('notification_'.$notification_code['NOTIFICATION_CODE'].'_'.$ntype,0)==1)
						{
							$new_setting=$new_setting | intval($notification_type['RAW']);
						}
					}

					if ($new_setting!=A__CHOICE)
					{
						$GLOBALS['SITE_DB']->query_insert('notification_lockdown',array(
							'l_notification_code'=>$notification_code['NOTIFICATION_CODE'],
							'l_setting'=>$new_setting,
						));
					}
				}
			}

			attach_message(do_lang_tempcode('SUCCESS'));
		}

		// Sort labels
		ksort($notification_sections);
		foreach (array_keys($notification_sections) as $i)
		{
			sort_maps_by($notification_sections[$i]['NOTIFICATION_CODES'],'NOTIFICATION_LABEL');
		}

		$css_path=get_custom_file_base().'/themes/'.$GLOBALS['FORUM_DRIVER']->get_theme().'/templates_cached/'.user_lang().'/global.css';
		$color='FF00FF';
		if (file_exists($css_path))
		{
			$tmp_file=file_get_contents($css_path);
			$matches=array();
			if (preg_match('#(\n|\})th[\s,][^\}]*(\s|\{)background-color:\s*\#([\dA-Fa-f]*);color:\s*\#([\dA-Fa-f]*);#sU',$tmp_file,$matches)!=0)
			{
				$color=$matches[3].'&fgcolor='.$matches[4];
			}
		}

		$notification_types_titles=array();
		foreach ($_notification_types as $possible=>$ntype)
		{
			$notification_types_titles[]=array(
				'NTYPE'=>$ntype,
				'LABEL'=>do_lang_tempcode('ENABLE_NOTIFICATIONS_'.$ntype),
				'RAW'=>strval($possible),
			);
		}

		$interface=do_template('NOTIFICATIONS_MANAGE',array('_GUID'=>'55dc192d339b570b060d61039c43b96d','SHOW_PRIVILEGES'=>true,'COLOR'=>$color,'NOTIFICATION_TYPES_TITLES'=>$notification_types_titles,'NOTIFICATION_SECTIONS'=>$notification_sections));

		return do_template('NOTIFICATIONS_MANAGE_SCREEN',array(
			'_GUID'=>'4f6af291a40c519377879555e24c2c81',
			'TITLE'=>$title,
			'INTERFACE'=>$interface,
			'ACTION_URL'=>get_self_url(),
		));
	}

}


