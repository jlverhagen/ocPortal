<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core
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

// Put code that you temporarily want executed into the function. DELETE THE CODE WHEN YOU'RE DONE.
// This is useful when performing quick and dirty upgrades (e.g. adding tables to avoid a reinstall)

require_code('database_action');
require_code('config2');
require_code('menus2');
$out=execute_temp();
if (!headers_sent())
{
	header('Content-Type: text/plain; charset='.get_charset());
	safe_ini_set('ocproducts.xss_detect','0');
	if (!is_null($out)) echo is_object($out)?$out->evaluate():(is_bool($out)?($out?'true':'false'):$out);
	echo do_lang('SUCCESS');
}

/**
 * Execute some temporary code put into this function.
 *
 * @return  mixed		Arbitrary result to output, if no text has already gone out
 */
function execute_temp()
{
	$data=file_get_contents('/Library/WebServer/Documents/ocportal/themes/default/images/action_small.png');
	$min_length=6;

	$data=str_replace("\0",'',$data); // Strip out interleaved nulls because they are used in wide-chars, obscuring the data
	$mash='';
	$needs_delimiter_next=false;
	$in_portion=false;
	for ($i=0;$i<strlen($data);$i++)
	{
		$ch=$data[$i];
		$chx=1;
		$next_ok=_is_valid_data_mash_char($ch);
		if (($next_ok) && (!$in_portion))
		{
			$x=$ch;
			for ($j=$i+1;$j<strlen($data);$j++)
			{
				$_ch=$data[$j];
				$_next_ok=_is_valid_data_mash_char($_ch);
				if ($_next_ok)
				{
					$x.=$_ch;
					$chx++;
				} else
				{
					break;
				}
			}
			if ((strlen($x)<$min_length) || ($x==strtoupper($x)) || ($x=='Microsoft Word Document') || ($x=='WordDocument') || ($x=='SummaryInformation') || ($x=='DocumentSummaryInformation'))
			{
				$chx=0;
			}
		}

		if (($next_ok) && ($in_portion))
		{
			$mash.=$ch;
		}
		elseif (($next_ok) && ($chx>=$min_length))
		{
			if ($needs_delimiter_next)
			{
				$mash.=' ';
				$needs_delimiter_next=false;
			}
			$mash.=$ch;
			$in_portion=true;
		} else
		{
			if ($in_portion)
			{
				$needs_delimiter_next=true;
				$in_portion=false;
			}
		}
	}

	@print($mash);
}

function _is_valid_data_mash_char(&$ch)
{
	$c=ord($ch);
	if (($c==145) || ($c==146)) $ch="'";
	return (($c>=65 && $c<=90) || ($c>=97 && $c<=122) || ($ch=="'") || ($ch=='-'));
}
