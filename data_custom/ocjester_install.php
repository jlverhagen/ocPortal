<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
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

global $NON_PAGE_SCRIPT;
$NON_PAGE_SCRIPT=1;
global $FORCE_INVISIBLE_GUEST;
$FORCE_INVISIBLE_GUEST=0;
if (!is_file($FILE_BASE.'/sources/global.php')) exit('<!DOCTYPE html>'.chr(10).'<html lang="EN"><head><title>Critical startup error</title></head><body><h1>ocPortal startup error</h1><p>The second most basic ocPortal startup file, sources/global.php, could not be located. This is almost always due to an incomplete upload of the ocPortal system, so please check all files are uploaded correctly.</p><p>Once all ocPortal files are in place, ocPortal must actually be installed by running the installer. You must be seeing this message either because your system has become corrupt since installation, or because you have uploaded some but not all files from our manual installer package: the quick installer is easier, so you might consider using that instead.</p><p>ocProducts maintains full documentation for all procedures and tools, especially those for installation. These may be found on the <a href="http://ocportal.com">ocPortal website</a>. If you are unable to easily solve this problem, we may be contacted from our website and can help resolve it for you.</p><hr /><p style="font-size: 0.8em">ocPortal is a website engine created by ocProducts.</p></body></html>'); require($FILE_BASE.'/sources/global.php');


require_code('database_action');
require_code('config2');
require_code('menus2');

add_config_option('OCJESTER_STRING_CHANGES','ocjester_string_changes','text','return "it\'s=its\nits=it\'s";','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_STRING_CHANGES_SHOWN_FOR','ocjester_string_changes_shown_for','line','return \'\';','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_EMOTICON_MAGNET_SHOWN_FOR','ocjester_emoticon_magnet_shown_for','line','return \'\';','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_NAME_CHANGES','ocjester_name_changes','text','return "Angelic\nBaubles\nChristmas\nDasher\nEvergreen\nFestive\nGifted\nHoliday\nIcicles\nJolly\nKingly\nEnlightened\nMerry\nNoel\nOrnamental\nParty\nKingly\nRudolph\nSeasonal\nTinsel\nYuletide\nVisionary\nWiseman\nXmas\nYuletide\nXmas";','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_NAME_CHANGES_SHOWN_FOR','ocjester_name_changes_shown_for','line','return \'\';','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_AVATAR_SWITCH_SHOWN_FOR','ocjester_avatar_switch_shown_for','line','return \'\';','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_LEET_SHOWN_FOR','ocjester_leet_shown_for','line','return \'\';','FEATURE','OCJESTER_TITLE');
add_config_option('OCJESTER_PIGLATIN_SHOWN_FOR','ocjester_piglatin_shown_for','line','return \'\';','FEATURE','OCJESTER_TITLE');

echo 'Installed';
