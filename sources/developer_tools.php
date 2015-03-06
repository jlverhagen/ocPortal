<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/*EXTRA FUNCTIONS: (var_export)|(debug_print_backtrace)|(memory_get_usage)*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core
 */

/**
 * Standard code module initialisation function.
 */
function init__developer_tools()
{
	global $MEMORY_POINTS;
	$MEMORY_POINTS=array();

	global $PREVIOUS_XSS_STATE;
	$PREVIOUS_XSS_STATE=array('1');
}

/**
 * Remove ocPortal's strictness, to help integration of third-party code.
 *
 * @param  boolean		Whether to also set the content type to plain-HTML
 * @param  boolean		Whether to destrictify MySQL commands over the ocPortal database driver
 */
function destrictify($change_content_type=true,$mysql_too=false)
{
	// Turn off strictness
	if ((!headers_sent()) && ($change_content_type))
		@header('Content-type: text/html; charset='.get_charset());
	$GLOBALS['SCREEN_TEMPLATE_CALLED']='';
	$GLOBALS['TITLE_CALLED']=true;
	error_reporting(E_ALL ^ E_NOTICE);
	if (function_exists('set_time_limit')) @set_time_limit(200);
	if ((get_forum_type()=='ocf') && ($mysql_too)) $GLOBALS['SITE_DB']->query('SET sql_mode=\'\'',NULL,NULL,true);
	@ini_set('ocproducts.type_strictness','0');
	global $PREVIOUS_XSS_STATE;
	array_push($PREVIOUS_XSS_STATE,ini_get('ocproducts.xss_detect'));
	$include_path='./';
	$include_path.=PATH_SEPARATOR.get_file_base().'/';
	$include_path.=PATH_SEPARATOR.get_file_base().'/sources_custom/';
	$include_path.=PATH_SEPARATOR.get_file_base().'/uploads/website_specific/';
	if (get_zone_name()!='') $include_path.=PATH_SEPARATOR.get_file_base().'/'.get_zone_name().'/';
	@ini_set('include_path',$include_path);
	//disable_php_memory_limit();	Don't do this, recipe for disaster
	@ini_set('allow_url_fopen','1');
	@ini_set('suhosin.executor.disable_emodifier','0');
	@ini_set('suhosin.executor.multiheader','0');
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;
	$GLOBALS['NO_QUERY_LIMIT']=true;
}

/**
 * Add ocPortal's strictness, after finishing with third-party code. To be run optionally at some point after destrictify().
 */
function restrictify()
{
	global $_CREATED_FILES,$_MODIFIED_FILES;

	// Reset functions
	if (isset($_CREATED_FILES)) $_CREATED_FILES=array();
	if (isset($_MODIFIED_FILES)) $_MODIFIED_FILES=array();

	// Put back strictness
	error_reporting(E_ALL);
	if (function_exists('set_time_limit')) @set_time_limit(25);
	if (get_forum_type()=='ocf') $GLOBALS['SITE_DB']->query('SET sql_mode=STRICT_ALL_TABLES',NULL,NULL,true);
	if ($GLOBALS['DEBUG_MODE'])
	{
		@ini_set('ocproducts.type_strictness','1');
		global $PREVIOUS_XSS_STATE;
		//safe_ini_set('ocproducts.xss_detect',array_pop($PREVIOUS_XSS_STATE));		We don't maintain this in v8, since we increased checking strength but are not fixing all the new false-positives. Real issues are found in v9 and back-ported.
	}
	@ini_set('include_path','');
	@ini_set('allow_url_fopen','0');
	@ini_set('suhosin.executor.disable_emodifier','1');
	@ini_set('suhosin.executor.multiheader','1');
	$GLOBALS['NO_DB_SCOPE_CHECK']=false;
	//$GLOBALS['NO_QUERY_LIMIT']=false;	Leave off, may have been set elsewhere than destrictify();
}

/**
 * Output whatever arguments are given for debugging. If possible it'll output with plain text, but if output has already started it will attach messages.
 */
function inspect()
{
	$args=func_get_args();

	_inspect($args,false);
}

/**
 * Output whatever arguments are given for debugging as text and exit. If possible it'll output with plain text, but if output has already started it will attach messages.
 */
function inspect_plain()
{
	$args=func_get_args();

	_inspect($args,true);
}

/**
 * Output whatever arguments are given for debugging. If possible it'll output with plain text, but if output has already started it will attach messages.
 *
 * @param  array			Arguments to output
 * @param  boolean		Whether to force text output
 */
function _inspect($args,$force_plain=false)
{
	$plain=headers_sent() || $force_plain || !running_script('index');

	if ($plain)
	{
		@ini_set('ocproducts.xss_detect','0');

		$GLOBALS['SCREEN_TEMPLATE_CALLED']='';

		if (!headers_sent())
		{
			header('Content-type: text/plain; charset='.get_charset());
			header('Content-Disposition: inline'); // Override what might have been set
		}

		echo 'DEBUGGING. INSPECTING VARIABLES...'.chr(10);
	} else
	{
		header('Content-type: text/html; charset='.get_charset());
		header('Content-Disposition: inline'); // Override what might have been set
	}

	foreach ($args as $arg_name=>$arg_value)
	{
		if (!is_string($arg_name)) $arg_name=strval($arg_name+1);

		if ($plain)
		{
			echo chr(10).chr(10).$arg_name.' is...'.chr(10);
			if ((is_object($arg_value) && (is_a($arg_value,'ocp_tempcode'))))
			{
				echo 'Tempcode: '.$arg_value->evaluate().' (';
				var_dump($arg_value);
				echo ')';
			} else
			{
				var_dump($arg_value);
			}
		} else
		{
			if ((is_object($arg_value) && (is_a($arg_value,'ocp_tempcode'))))
			{
				attach_message($arg_name.' is...'.chr(10).'Tempcode: '.$arg_value->evaluate());
			} else
			{
				attach_message($arg_name.' is...'.chr(10).var_export($arg_value,true));
			}
		}
	}

	if ($plain)
	{
		echo chr(10).chr(10).'--------------------'.chr(10).chr(10).'STACK TRACE FOLLOWS...'.chr(10).chr(10);

		debug_print_backtrace();
		exit();
	}
}

/**
 * Record the memory usage at this point.
 *
 * @param  ?string		The name of the memory point (NULL: use a simple counter)
 */
function memory_trace_point($name=NULL)
{
	global $MEMORY_POINTS;
	if (is_null($name)) $name='#'.integer_format(count($MEMORY_POINTS)+1);
	$MEMORY_POINTS[]=array(memory_get_usage(),$name);
}

/**
 * Output whatever memory points we collected up.
 */
function show_memory_points()
{
	@header('Content-type: text/plain; charset='.get_charset());

	@ini_set('ocproducts.xss_detect','0');

	$GLOBALS['SCREEN_TEMPLATE_CALLED']='';

	global $MEMORY_POINTS;
	$before=mixed();
	foreach ($MEMORY_POINTS as $point)
	{
		list($memory,$name)=$point;
		echo 'Memory at '.$name.' is'."\t".integer_format($memory).' (growth of '.(is_null($before)?'N/A':integer_format($memory-$before)).')'."\n";
		$before=$memory;
	}
	exit();
}
