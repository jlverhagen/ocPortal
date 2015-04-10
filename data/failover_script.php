<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		failover
 */

// Find ocPortal base directory, and chdir into it
global $FILE_BASE,$RELATIVE_PATH;
$FILE_BASE=(strpos(__FILE__,'./')===false)?__FILE__:realpath(__FILE__);
$FILE_BASE=dirname($FILE_BASE);
if (!is_file($FILE_BASE.'/sources/global.php')) // Need to navigate up a level further perhaps?
{
	$RELATIVE_PATH=basename($FILE_BASE);
	$FILE_BASE=dirname($FILE_BASE);
} else
{
	$RELATIVE_PATH='';
}
@chdir($FILE_BASE);

require($FILE_BASE.'/info.php');

if (php_sapi_name()!='cli')
{
	header('Content-type: text/plain');
}

$required_settings=array(
	'fast_spider_cache',
	'any_guest_cached_too',
	'failover_mode',
	//'failover_message',	Actually, may be blank
	'failover_cache_miss_message',
	//'failover_loadtime_threshold',	Actually, may be blank
	//'failover_loadaverage_threshold',	Actually, may be blank
	'failover_email_contact',
	//'failover_check_urls',	Actually, may be blank
	'base_url',
);

global $SITE_INFO;
foreach ($required_settings as $setting)
{
	if (empty($SITE_INFO[$setting]))
	{
		exit('Missing/empty info.php setting: '.$setting);
	}
}

if ($SITE_INFO['failover_mode']!='auto_on' && $SITE_INFO['failover_mode']!='auto_off')
{
	exit(); // Not enabled. No message, we don't want to push noise back into the CRON system.
}

handle_failover_auto_switching();

/**
 * Switch over the failover mode appropriately if we detect a change in problem state.
 *
 * @param  integer					The check iteration (if deactivating we check a number of times to ensure all is still okay).
 */
function handle_failover_auto_switching($iteration=0)
{
	global $SITE_INFO;

	// Check URLs
	if (!empty($SITE_INFO['failover_check_urls']))
	{
		$context=stream_context_create(array(
			'http'=>array(
				'user_agent'=>'ocportal_failover_test',
				'timeout'=>floatval(isset($SITE_INFO['failover_loadtime_threshold'])?$SITE_INFO['failover_loadtime_threshold']:5)+1.0,
			)
		));
		$urls=explode(';',$SITE_INFO['failover_check_urls']);
		foreach ($urls as $url)
		{
			$full_url=$SITE_INFO['base_url'].'/'.$url;
			$full_url.=((strpos($full_url,'?')===false)?'?':'&').'keep_failover=0';

			$time_before=microtime(true);
			$data=@file_get_contents($full_url,false,$context);
			$time_after=microtime(true);
			$time=$time_after-$time_before;

			// Misc failure
			if ($data===false)
			{
				is_failing($full_url.' (failed load / slow load)');
			}

			// Bad HTTP status
			if (strpos($http_response_header[0],'200')===false)
			{
				is_failing($full_url.' (bad HTTP code; '.$http_response_header[0].')');
			}

			// Parse error or fatal error, with display errors on in php.ini (without display errors, PHP uses the correct HTTP status)
			$matches=array();
			if ((strlen($data)<500) && (preg_match('#<b>(\w+ error)</b>#',$data,$matches)!=0))
			{
				is_failing($full_url.' ('.$matches[1].')');
			}

			// Slowness
			if ((!empty($SITE_INFO['failover_loadtime_threshold'])) && ($time>=floatval($SITE_INFO['failover_loadtime_threshold'])))
			{
				is_failing($full_url.' (slow load; '.number_format($time,2).' seconds)');
			}
		}
	}

	if (!empty($SITE_INFO['failover_loadaverage_threshold']))
	{
		// Check loadaverage (Unix-like)
		if (function_exists('sys_getloadavg'))
		{
			$result=sys_getloadavg();
			$load=$result[1];
			if ($load>=floatval($SITE_INFO['failover_loadaverage_threshold']))
			{
				is_failing('slow server; load-average='.number_format($load,2));
			}
		}

		// Check loadaverage (Windows)
		if (class_exists('COM'))
		{
			$wmi=new COM('Winmgmts://');
			$server=$wmi->execquery('SELECT LoadPercentage FROM Win32_Processor');
			if (is_array($server))
			{
				$cpu_num=0;
				$load_total=0;
				foreach ($server as $cpu)
				{
					$cpu_num++;
					$load_total+=$cpu->loadpercentage;
				}
				$load=round((float)$load_total/(float)$cpu_num);
				if ($cpu_num!=0)
				{
					if ($load>=floatval($SITE_INFO['failover_loadaverage_threshold']))
					{
						is_failing('load-average='.number_format($load,2));
					}
				}
			}
		}
	}

	// If we got this far, no problems
	static $made_change_to_off=false;
	if ($SITE_INFO['failover_mode']=='auto_on')
	{
		set_failover_mode('auto_off');
		$made_change_to_off=true;

		$base_url=parse_url($SITE_INFO['base_url']);
		$subject='Failover mode DEactivated for '.$base_url['host'];
		$message="Failover mode deactivated, the site is now back online.";
		send_failover_email($subject,$message);
	}

	// Keep checking for around a minute more, every 10 seconds
	if ($made_change_to_off)
	{
		if ($iteration<6)
		{
			sleep(10);
			handle_failover_auto_switching($iteration+1);
		}
	}
}

/**
 * A check has failed, inform contact(s) about it then exit.
 *
 * @param  string					Check that failed.
 */
function is_failing($url)
{
	global $SITE_INFO;
	if ($SITE_INFO['failover_mode']=='auto_off')
	{
		set_failover_mode('auto_on');

		$base_url=parse_url($SITE_INFO['base_url']);
		$subject='Failover mode activated for '.$base_url['host'];
		$message="Failover mode activated when running the following check:\n".$url."\n\nWhen the problem has been corrected it will automatically disable.\nIf this is a false alarm somehow you can force failover mode off manually by setting \$SITE_INFO['failover_mode']='off'; in info.php";
		send_failover_email($subject,$message);
	}

	exit();
}

/**
 * Send a failover email.
 *
 * @param  string					Subject.
 * @param  string					Body.
 */
function send_failover_email($subject,$message)
{
	global $SITE_INFO;
	$emails=explode(';',$SITE_INFO['failover_email_contact']);
	foreach ($emails as $email)
	{
		mail($email,$subject,$message);
	}
}

/**
 * Set failover mode to a new value.
 *
 * @param  string					New failover mode.
 */
function set_failover_mode($new_mode)
{
	global $FILE_BASE,$SITE_INFO;

	$path=$FILE_BASE.'/info.php';
	$config_contents=file_get_contents($path);
	$orig_config_contents=$config_contents;
	$config_contents=preg_replace('#^(\$SITE_INFO\[\'failover_mode\'\]\s*=\s*\')[^\']+(\';)#m','$1'.addslashes($new_mode).'$2',$config_contents);

	if ($orig_config_contents==$config_contents) return; // No change needed

	file_put_contents($path,$config_contents,LOCK_EX);

	$SITE_INFO['failover_mode']=$new_mode;

	if ((!empty($SITE_INFO['failover_apache_rewritemap_file'])) && (is_file($FILE_BASE.'/data_custom/failover_rewritemap.txt')))
	{
		$htaccess_contents=file_get_contents($FILE_BASE.'/.htaccess');

		$htaccess_contents=preg_replace('#^RewriteMap.*\n+#s','',$htaccess_contents);

		$new_code='#FAILOVER STARTS'."\n";
		if ($new_mode=='auto_on' || $new_mode=='on')
		{
			$new_code.='RewriteEngine on'."\n";
			$new_code.='RewriteRule ^((static_cache|themes|uploads|data|data_custom)/.*) \$1 [L]'."\n";

			if ($SITE_INFO['failover_apache_rewritemap_file']=='-')
			{
				$new_code.='RewriteCond %{QUERY_STRING} !keep_failover [NC]'."\n";
				$new_code.='RewriteRule ^(.*) sources/static_cache.php [L,QSA]'."\n";
			} else
			{
				// The set of browsers
				$browsers=array(
									// Implication by technology claims
									'WML',
									'WAP',
									'Wap',
									'MIDP', // Mobile Information Device Profile

									// Generics
									'Mobile',
									'Smartphone',
									'WebTV',

									// Well known/important browsers/brands
									'Minimo', // By Mozilla
									'Fennec', // By Mozilla (being outmoded by minimo)
									'Mobile Safari', // Usually Android
									'lynx',
									'Links',
									'iPhone',
									'iPod',
									'Opera Mobi',
									'Opera Mini',
									'BlackBerry',
									'Windows Phone',
									'Windows CE',
									'Symbian',
									'nook browser', // Barnes and Noble
									'Blazer', // Palm
									'PalmOS',
									'webOS', // Palm
									'SonyEricsson',

									// Games consoles
									'Nintendo',
									'PlayStation Portable',

									// Less well known but common browsers
									'UP.Browser', // OpenWave
									'UP.Link', // OpenWave again?
									'NetFront',
									'Teleca',
									'UCWEB',

									// Specific lamely-identified devices/brands
									'DDIPOCKET',
									'SEMC-Browser',
									'DoCoMo',
									'Xda',
									'ReqwirelessWeb', // Siemens/Samsung

									// Specific services
									'AvantGo',
									);
				$regexp='('.str_replace(' ','\ ',implode('|',$browsers)).')';

				//$new_code.='RewriteMap failover_mode txt:'.$FILE_BASE.'/data_custom/failover_rewritemap.txt'."\n";	Has to be defined in main Apache config
				$new_code.='RewriteCond %{QUERY_STRING} !keep_failover [NC]'."\n";
				$new_code.='RewriteRule ^(.*) ${failover_mode:\$1} [L,QSA]'."\n";
				//$new_code.='RewriteMap failover_mode__mobile txt:'.$FILE_BASE.'/data_custom/failover_rewritemap__mobile.txt'."\n";
				$new_code.='RewriteCond %{QUERY_STRING} !keep_failover [NC]'."\n";
				$new_code.='RewriteCond %{HTTP_USER_AGENT} '.$regexp."\n";
				$new_code.='RewriteRule ^(.*) ${failover_mode__mobile:\$1} [L,QSA]'."\n";
			}
		}
		$new_code.='#FAILOVER ENDS'."\n\n";

		$htaccess_contents=preg_replace('/#FAILOVER STARTS.*#FAILOVER ENDS\n+/s',$new_code,$htaccess_contents);

		file_put_contents($FILE_BASE.'/.htaccess',$htaccess_contents,LOCK_EX);
	}
}
