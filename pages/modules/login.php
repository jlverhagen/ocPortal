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
 * @package		core
 */

/**
 * Module page class.
 */
class Module_login
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
		$info['version']=2;
		$info['locked']=true;
		return $info;
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_if_exists('failedlogins');
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		$GLOBALS['SITE_DB']->create_table('failedlogins',array(
			'id'=>'*AUTO',
			'failed_account'=>'ID_TEXT',
			'date_and_time'=>'TIME',
			'ip'=>'IP'
		));
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		if (is_guest()) return array('misc'=>'_LOGIN');
		$ret=array('misc'=>'_LOGIN','logout'=>'LOGOUT','concede'=>'CONCEDED_MODE');
		if (get_option('is_on_invisibility')=='1') $ret['invisible']='INVISIBLE';
		return $ret;
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$type=get_param('type','misc');

		if ($type=='login') return $this->login_after();
		if ($type=='logout') return $this->logout();
		if ($type=='concede') return $this->concede();
		if ($type=='invisible') return $this->invisible();
		if ($type=='misc') return $this->login_before();

		return new ocp_tempcode();
	}

	/**
	 * The UI for logging in.
	 *
	 * @return tempcode	The UI.
	 */
	function login_before()
	{
		$title=get_screen_title('_LOGIN');

		global $EXTRA_HEAD;
		$EXTRA_HEAD->attach('<meta name="robots" content="noindex" />'); // XHTMLXHTML

		global $ZONE;

		// Where we will be redirected to after login
		// It's intentional that we will only go to the zone-default page if an explicitly blank redirect URL is given. If not we go to the given redirect URL or to the made-safe current URL (made-safe=zone root if we posted as we do not want to re-post). Note that 'redirect_passon' is simulated by injecting the self-URL as a URL parameter by access_denied() - as we want re-post in this case.
		$redirect_default=get_self_url(true,true); // Still won't redirect to root if we have $_POST, because we relay $_POST values and have intelligence later on
		$redirect=get_param('redirect',$redirect_default);
		if (($redirect!='') && (strpos($redirect,'page=login')===false) && (strpos($redirect,'/login.htm')===false) && (strpos($redirect,'/login/logout.htm')===false) && (strpos($redirect,'/login/misc.htm')===false) && (strpos($redirect,'/pg/login')===false))
		{
			$passion=form_input_hidden('redirect',$redirect);
		} else
		{
			$_url=build_url(array('page'=>$ZONE['zone_default_page']),'_SELF');
			$url=$_url->evaluate();
			$passion=form_input_hidden('redirect',$url);
		}

		$login_url=build_url(array('page'=>'_SELF','type'=>'login'),'_SELF');

		// Test to see if we have any redirect issue that blocks form submissions
		if (ocp_srv('HTTP_USER_AGENT')!='ocPortal' && strpos(ocp_srv('HTTP_REFERER'),'logout')===false && running_script('index'))
		{
			$this_proper_url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
			$_login_url=$this_proper_url->evaluate();
			$test=http_download_file($_login_url,0,false,true); // Should return a 200 blank, not an HTTP error or a redirect; actual data would be an ocP error
			if ((is_null($test)) && ($GLOBALS['HTTP_MESSAGE']!=='200') && ($GLOBALS['HTTP_MESSAGE']!=='401') && ((!is_file(get_file_base().'/install.php')) || ($GLOBALS['HTTP_MESSAGE']!=='500')))
			{
				if ((($GLOBALS['HTTP_MESSAGE']=='no-data') || (strpos($GLOBALS['HTTP_MESSAGE'],'Connection refused')!==false)) && (get_option('ip_forwarding')=='0'))
				{
					attach_message(do_lang_tempcode('config:ENABLE_IP_FORWARDING',do_lang('config:IP_FORWARDING')),'warn');
				} else
				{
					attach_message(do_lang_tempcode((substr(get_base_url(),0,11)=='http://www.')?'HTTP_REDIRECT_PROBLEM_WITHWWW':'HTTP_REDIRECT_PROBLEM_WITHOUTWWW',escape_html(get_base_url().'/config_editor.php')),'warn');
				}
			}
		}

		if (count($_FILES)==0) // Only if we don't have _FILES (which could never be relayed)
		{
			$passion->attach(build_keep_post_fields(array('redirect')));
			$redirect_passon=post_param('redirect',NULL);
			if (!is_null($redirect_passon))
				$passion->attach(form_input_hidden('redirect_passon',$redirect_passon));
		}

		$username=trim(get_param('username',''));
		if (!is_guest()) $username=$GLOBALS['FORUM_DRIVER']->get_username(get_member());

		if (get_forum_type()=='ocf')
		{
			require_lang('ocf');
			$forgotten_link=build_url(array('page'=>'lostpassword'),get_module_zone('lostpassword'));
			$extra=do_lang_tempcode('IF_FORGOTTEN_PASSWORD',escape_html($forgotten_link->evaluate()));
		} else $extra=new ocp_tempcode();

		require_css('login');

		breadcrumb_set_parents(array());

		return do_template('LOGIN_SCREEN',array('_GUID'=>'0940dbf2c42493c53b7e99eb50ca51f1','EXTRA'=>$extra,'USERNAME'=>$username,'JOIN_URL'=>$GLOBALS['FORUM_DRIVER']->join_url(),'TITLE'=>$title,'LOGIN_URL'=>$login_url,'PASSION'=>$passion));
	}

	/**
	 * The actualiser for logging in.
	 *
	 * @return tempcode	The UI.
	 */
	function login_after()
	{
		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('_LOGIN'))));

		$username=trim(post_param('login_username'));
		$feedback=$GLOBALS['FORUM_DRIVER']->forum_authorise_login($username,NULL,apply_forum_driver_md5_variant(trim(post_param('password')),$username),trim(post_param('password')));
		$id=$feedback['id'];
		if (!is_null($id))
		{
			$title=get_screen_title('LOGGED_IN');
			$url=enforce_sessioned_url(either_param('redirect'));
			//set_session_id(get_session_id()); // Just in case something earlier set it to a pre-logged-in one     Not needed

			if (count($_POST)<=4) // Only the login username, password, remember-me and redirect
			{
				require_code('site2');
				assign_refresh($url,0.0);
				$post=new ocp_tempcode();
				$refresh=new ocp_tempcode();
			} else
			{
				$post=build_keep_post_fields(array('redirect','redirect_passon'));
				$redirect_passon=post_param('redirect_passon',NULL);
				if (!is_null($redirect_passon))
					$post->attach(form_input_hidden('redirect',$redirect_passon));
				$refresh=do_template('JS_REFRESH',array('_GUID'=>'c7d2f9e7a2cc637f3cf9ac4d1cf97eca','FORM_NAME'=>'redir_form'));
			}
			decache('side_users_online');

			return do_template('LOGIN_REDIRECT_SCREEN',array('_GUID'=>'82e056de9150bbed185120eac3571f40','REFRESH'=>$refresh,'TITLE'=>$title,'TEXT'=>do_lang_tempcode('_LOGIN_TEXT'),'URL'=>$url,'POST'=>$post));
		} else
		{
			$title=get_screen_title('USER_LOGIN_ERROR');

			$text=$feedback['error'];

			attach_message($text,'warn');

			if (get_forum_type()=='ocf')
			{
				require_lang('ocf');
				$forgotten_link=build_url(array('page'=>'lostpassword'),get_module_zone('lostpassword'));
				$extra=do_lang_tempcode('IF_FORGOTTEN_PASSWORD',escape_html($forgotten_link->evaluate()));

				attach_message($extra,'inform');
			}

			return $this->login_before();
		}
	}

	/**
	 * The actualiser for logging out.
	 *
	 * @return tempcode	The UI.
	 */
	function logout()
	{
		decache('side_users_online');

		$title=get_screen_title('LOGGED_OUT');

		$url=get_param('redirect',NULL);
		if (is_null($url))
		{
			$_url=build_url(array('page'=>''),'',array('keep_session'=>1));
			$url=$_url->evaluate();
		}
		return redirect_screen($title,$url,do_lang_tempcode('_LOGGED_OUT'));
	}

	/**
	 * The actualiser for entering conceded mode.
	 *
	 * @return tempcode	The UI.
	 */
	function concede()
	{
		$title=get_screen_title('CONCEDED_MODE');

		$GLOBALS['SITE_DB']->query_update('sessions',array('session_confirmed'=>0),array('the_user'=>get_member(),'the_session'=>get_session_id()),'',1);
		global $SESSION_CACHE;
		if ($SESSION_CACHE[get_session_id()]['the_user']==get_member()) // A little security
		{
			$SESSION_CACHE[get_session_id()]['session_confirmed']=0;
			if (get_value('session_prudence')!=='1')
			{
				persistent_cache_set('SESSION_CACHE',$SESSION_CACHE);
			}
		}

		$url=get_param('redirect',NULL);
		if (is_null($url))
		{
			$_url=build_url(array('page'=>''),'');
			$url=$_url->evaluate();
		}
		return redirect_screen($title,$url,do_lang_tempcode('LOGIN_CONCEDED'));
	}

	/**
	 * The actualiser for toggling invisible mode.
	 *
	 * @return tempcode	The UI.
	 */
	function invisible()
	{
		if (get_option('is_on_invisibility')=='1')
		{
			$visible=(array_key_exists(get_session_id(),$GLOBALS['SESSION_CACHE'])) && ($GLOBALS['SESSION_CACHE'][get_session_id()]['session_invisible']==0);
		} else
		{
			$visible=false; // Small fudge: always say thay are not visible now, so this will make them visible -- because they don't have permission to be invisible
		}

		$title=get_screen_title($visible?'INVISIBLE':'BE_VISIBLE');

		$GLOBALS['SITE_DB']->query_update('sessions',array('session_invisible'=>$visible?1:0),array('the_user'=>get_member(),'the_session'=>get_session_id()),'',1);
		global $SESSION_CACHE;
		if ($SESSION_CACHE[get_session_id()]['the_user']==get_member()) // A little security
		{
			$SESSION_CACHE[get_session_id()]['session_invisible']=$visible?1:0;
			if (get_value('session_prudence')!=='1')
			{
				persistent_cache_set('SESSION_CACHE',$SESSION_CACHE);
			}
		}

		decache('side_users_online');

		// Store in cookie, if we have login cookies around
		if (array_key_exists(get_member_cookie(),$_COOKIE))
		{
			require_code('users_active_actions');
			ocp_setcookie(get_member_cookie().'_invisible',strval($visible?1:0));
			$_COOKIE[get_member_cookie().'_invisible']=strval($visible?1:0);
		}

		$url=get_param('redirect',NULL);
		if (is_null($url))
		{
			$_url=build_url(array('page'=>''),'');
			$url=$_url->evaluate();
		}
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

}


