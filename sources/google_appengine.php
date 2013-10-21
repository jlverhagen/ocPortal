<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2013

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		google_appengine
 */

/**
 * Standard code module initialisation function.
 */
function init__google_appengine()
{
	$uri=preg_replace('#\?.*#','',$_SERVER['REQUEST_URI']);
	if (substr($uri,0,1)=='/') $uri=substr($uri,1);
	$matches=array();

	// RULES START	
	if (preg_match('#^([^=]*)pages/(modules|modules\_custom)/([^/]*)\.php$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$3');
	if (preg_match('#^([^=]*)pg/s/([^\&\?]*)/index\.php$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=wiki&id=$2');
	if (preg_match('#^([^=]*)pg/galleries/image/([^\&\?]*)/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=galleries&type=image&id=$2$3');
	if (preg_match('#^([^=]*)pg/galleries/video/([^\&\?]*)/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=galleries&type=video&id=$2$3');
	if (preg_match('#^([^=]*)pg/iotds/view/([^\&\?]*)/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=iotds&type=view&id=$2$3');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)/([^/\&\?]*)/([^\&\?]*)/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2&type=$3&id=$4$5');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)/([^/\&\?]*)/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2&type=$3$4');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2$3');
	if (preg_match('#^([^=]*)pg/index\.php(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$3');
	if (preg_match('#^([^=]*)pg/s/([^\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=wiki&id=$2');
	if (preg_match('#^([^=]*)pg/galleries/image/([^\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=galleries&type=image&id=$2$3');
	if (preg_match('#^([^=]*)pg/galleries/video/([^\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=galleries&type=video&id=$2$3');
	if (preg_match('#^([^=]*)pg/iotds/view/([^\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=iotds&type=view&id=$2');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)/([^/\&\?]*)/([^\&\?]*)/$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2&type=$3&id=$4');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)/([^/\&\?]*)/([^\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2&type=$3&id=$4');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)/([^/\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2&type=$3');
	if (preg_match('#^([^=]*)pg/([^/\&\?]*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?page=$2');
	if (preg_match('#^([^=]*)pg/s/([^\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$3&page=wiki&id=$2');
	if (preg_match('#^([^=]*)pg/galleries/image/([^/\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$5&page=galleries&type=image&id=$2&$3');
	if (preg_match('#^([^=]*)pg/galleries/video/([^/\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$5&page=galleries&type=video&id=$2&$3');
	if (preg_match('#^([^=]*)pg/iotds/view/([^/\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$3&page=iotds&type=view&id=$2');
	if (preg_match('#^([^=]*)pg/([^/\&\?\.]*)/([^/\&\?\.]*)/([^/\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$5&page=$2&type=$3&id=$4');
	if (preg_match('#^([^=]*)pg/([^/\&\?\.]*)/([^/\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$4&page=$2&type=$3');
	if (preg_match('#^([^=]*)pg/([^/\&\?\.]*)&(.*)$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1index.php\?$3&page=$2');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/s/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=wiki&id=$2');
	if (preg_match('#^s/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'index\.php\?page=wiki&id=$1');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/galleries/image/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=galleries&type=image&id=$2');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/galleries/video/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=galleries&type=video&id=$2');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/iotds/view/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=iotds&type=view&id=$2');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/([^/\&\?]+)/([^/\&\?]*)/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=$2&type=$3&id=$4');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/([^/\&\?]+)/([^/\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=$2&type=$3');
	if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/([^/\&\?]+)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'$1/index.php\?page=$2');
	if (preg_match('#^([^/\&\?]+)/([^/\&\?]*)/([^\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'index.php\?page=$1&type=$2&id=$3');
	if (preg_match('#^([^/\&\?]+)/([^/\&\?]*)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'index.php\?page=$1&type=$2');
	if (preg_match('#^([^/\&\?]+)\.htm$#',$uri,$matches)!=0)
		return _roll_gae_redirect($matches,'index.php\?page=$1');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/s/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=wiki&id=$2');
	//if (preg_match('#^s/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'index\.php\?page=wiki&id=$1');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/galleries/image/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=galleries&type=image&id=$2');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/galleries/video/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=galleries&type=video&id=$2');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/iotds/view/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=iotds&type=view&id=$2');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/([^/\&\?]+)/([^/\&\?]*)/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=$2&type=$3&id=$4');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/([^/\&\?]+)/([^/\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=$2&type=$3');
	//if (preg_match('#^(site|forum|adminzone|cms|collaboration|docs)/([^/\&\?]+)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'$1/index.php\?page=$2');
	//if (preg_match('#^([^/\&\?]+)/([^/\&\?]*)/([^\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'index.php\?page=$1&type=$2&id=$3');
	//if (preg_match('#^([^/\&\?]+)/([^/\&\?]*)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'index.php\?page=$1&type=$2');
	//if (preg_match('#^([^/\&\?]+)$#',$uri,$matches)!=0)
	//	return _roll_gae_redirect($matches,'index.php\?page=$1');
	// RULES END
}

/**
 * Find whether the current user is an admin, from the perspective of the Google Console.
 *
 * @param  array			URL segments matched
 * @param  string			Redirect pattern
 */
function _roll_gae_redirect($matches,$to)
{
	$to=str_replace('\\?','?',$to);
	foreach ($matches as $i=>$match)
	{
		$to=str_replace('$'.strval($i),urlencode($match),$to);
	}

	$qs=preg_replace('#^[^\?]*(\?|$)#','',$to);
	$_SERVER['QUERY_STRING']=$qs;
	if ($qs!='')
	{
		if (strpos($_SERVER['REQUEST_URI'],'?')===false)
		{
			$_SERVER['REQUEST_URI'].='?'.$qs;
		} else
		{
			$_SERVER['REQUEST_URI'].='&'.$qs;
		}
	}
	parse_str($qs,$arr);
	$_GET+=$arr;
}

/**
 * Find whether the current user is an admin, from the perspective of the Google Console.
 *
 * @return boolean		Current user is admin
 */
function gae_is_admin()
{
	require_once('google/appengine/api/users/User.php');
	require_once('google/appengine/api/users/UserService.php');

	$_userservice='google\appengine\api\users\UserService';
	$userservice=new $_userservice();

	$user=$userservice->getCurrentUser();

	if ($user!==NULL)
	{
		return $userservice->isCurrentUserAdmin();
	}
	return false;
}