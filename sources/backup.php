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
 * @package		backup
 */

/**
 * Standard code module initialisation function.
 */
function init__backup()
{
	global $STARTED_BACKUP;
	$STARTED_BACKUP=false;
}

/**
 * Write PHP code for the restoration of database data into file.
 *
 * @param  resource			The logfile to write to
 * @param  ID_TEXT			The meta tablename
 * @param  ID_TEXT			The index-meta tablename
 * @param  resource			File to write in to
 */
function get_table_backup($logfile,$db_meta,$db_meta_indices,&$install_php_file)
{
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;

	// Get a list of tables
	$tables=$GLOBALS['SITE_DB']->query_select($db_meta,array('DISTINCT m_table AS m_table'));

	// For each table, build up an ocPortal table creation command
	foreach ($tables as $_table)
	{
		$table=$_table['m_table'];

		$fields=$GLOBALS['SITE_DB']->query_select($db_meta,array('*'),array('m_table'=>$table));

		fwrite($install_php_file,preg_replace('#^#m','//',"   \$GLOBALS['SITE_DB']->drop_if_exists('$table');\n"));
		$array='';
		foreach ($fields as $field)
		{
			$name=$field['m_name'];
			$type=$field['m_type'];

			if ($array!='') $array.=",\n";
			$array.="		'".$name."'=>'".$type."'";
		}
		fwrite($install_php_file,preg_replace('#^#m','//',"   \$GLOBALS['SITE_DB']->create_table('$table',array(\n$array),true,true);\n"));

		if (($table=='stats') || ($table=='incoming_uploads') || ($table=='cache') || ($table=='url_title_cache') || ($table=='ip_country'))
		{
			$data=array();
		} else
		{
			if (($table!='edit_pings') && ($table!='cache'))
			{
				$start=0;
				do
				{
					$data=$GLOBALS['SITE_DB']->query_select($table,array('*'),NULL,'',100,$start,false,array());
					foreach ($data as $d)
					{
						$list='';
						$value=mixed();
						foreach ($d as $name=>$value)
						{
							if (($table=='translate') && ($name=='text_parsed')) $value='';

							if (is_null($value)) continue;
							if ($list!='') $list.=',';
							$list.="'".(is_string($name)?$name:strval($name))."'=>";
							if (is_integer($value))
							{
								$list.=strval($value);
							}
							elseif (is_float($value))
							{
								$list.=float_to_raw_string($value);
							} else
							{
								$list.='"'.php_addslashes($value).'"';
							}
						}
						fwrite($install_php_file,preg_replace('#^#m','//',"   \$GLOBALS['SITE_DB']->query_insert('$table',array($list));\n"));
					}

					$start+=100;
				}
				while (count($data)!=0);
			}
		}

		fwrite($logfile,'Backed up table '.$table."\n");
	}

	// For each index, build up an ocPortal index creation command
	$indices=$GLOBALS['SITE_DB']->query_select($db_meta_indices,array('*'));
	foreach ($indices as $index)
	{
		if (fwrite($install_php_file,preg_replace('#^#m','//','   $GLOBALS[\'SITE_DB\']->create_index(\''.$index['i_table'].'\',\''.$index['i_name'].'\',array(\''.str_replace(',','\',\'',$index['i_fields']).'\'));'."\n"))==0)
			warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
	}

	$GLOBALS['NO_DB_SCOPE_CHECK']=false;
}

/**
 * Backend function to do a backup (meant to be run as a shutdown function - essentially a background task).
 *
 * @param  ?string		The filename to backup to (NULL: get global)
 * @param  ?string		The type of backup to do (NULL: get global)
 * @set    full incremental
 * @param  ?integer		The maximum size of a file to include in the backup (NULL: get global)
 */
function make_backup_2($file=NULL,$b_type=NULL,$max_size=NULL) // This is called as a shutdown function and thus cannot script-timeout
{
	global $STARTED_BACKUP;
	if ($STARTED_BACKUP) return;
	$STARTED_BACKUP=true;

	if (is_null($file))
	{
		global $MB2_FILE,$MB2_B_TYPE,$MB2_MAX_SIZE;
		$file=$MB2_FILE;
		$b_type=$MB2_B_TYPE;
		$max_size=$MB2_MAX_SIZE;
	}

	if (function_exists('set_time_limit')) @set_time_limit(0);
	$logfile_path=get_custom_file_base().'/exports/backups/'.$file.'.txt';
	$logfile=@fopen($logfile_path,'wt') OR intelligent_write_error($logfile_path); // .txt file because IIS doesn't allow .log download
	safe_ini_set('log_errors','1');
	safe_ini_set('error_log',$logfile_path);
	fwrite($logfile,'This is a log file for an ocPortal backup. The backup is not complete unless this log terminates with a completion message.'."\n\n");

	require_code('tar');
	$myfile=tar_open(get_custom_file_base().'/exports/backups/'.filter_naughty($file).'.tmp','wb');

	// Write readme.txt file
	tar_add_file($myfile,'readme.txt',do_lang('BACKUP_README',get_timezoned_date(time())),0664,time());

	// Write restore.php file
	$template=get_custom_file_base().'/data_custom/modules/admin_backup/restore.php.pre';
	if (!file_exists($template)) $template=get_file_base().'/data/modules/admin_backup/restore.php.pre';
	$_install_php_file=file_get_contents($template);
	$place=strpos($_install_php_file,'{!!DB!!}');
	$__install_php_file=ocp_tempnam('ocpbak');
	$__install_data_php_file=ocp_tempnam('ocpbak_data');
	$install_php_file=fopen($__install_php_file,'wb');
	$install_data_php_file=fopen($__install_data_php_file,'wb');
	fwrite($install_php_file,substr($_install_php_file,0,$place));
	fwrite($install_data_php_file,"<"."?php

//COMMANDS BEGIN
//\$GLOBALS['SITE_DB']->drop_if_exists('db_meta');
//\$GLOBALS['SITE_DB']->create_table('db_meta',array(
//	'm_table'=>'*ID_TEXT',
//	'm_name'=>'*ID_TEXT',
//	'm_type'=>'ID_TEXT'
//));
//
//\$GLOBALS['SITE_DB']->drop_if_exists('db_meta_indices');
//\$GLOBALS['SITE_DB']->create_table('db_meta_indices',array(
//	'i_table'=>'*ID_TEXT',
//	'i_name'=>'*ID_TEXT',
//	'i_fields'=>'*ID_TEXT',
//));
");
	get_table_backup($logfile,'db_meta','db_meta_indices',$install_data_php_file);
	if (fwrite($install_php_file,substr($_install_php_file,$place+8))==0) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
	fclose($install_php_file);
	fclose($install_data_php_file);

	tar_add_file($myfile,'restore.php',$__install_php_file,0664,time(),true);
	tar_add_file($myfile,'restore_data.php',$__install_data_php_file,0664,time(),true);
	@unlink($__install_php_file);

	if ($b_type=='full')
	{
		set_value('last_backup',strval(time()));
		$original_files=(get_param_integer('keep_backup_alien',0)==1)?unserialize(file_get_contents(get_file_base().'/data/files.dat')):NULL;
		$root_only_dirs=array_merge(find_all_zones(false,false,true),array(
			'data','data_custom',
			'exports','imports',
			'lang','lang_custom',
			'lang_cached',
			'pages',
			'persistent_cache','safe_mode_temp',
			'sources','sources_custom',
			'text','text_custom',
			'themes',
			'uploads',

			'site', // In case of collapsed zones blocking in
		));
		tar_add_folder($myfile,$logfile,get_file_base(),$max_size,'',$original_files,$root_only_dirs,!running_script('cron_bridge'));
	} elseif ($b_type=='incremental')
	{
		$threshold=intval(get_value('last_backup'));

		set_value('last_backup',strval(time()));
		$directory=tar_add_folder_incremental($myfile,$logfile,get_file_base(),$threshold,$max_size);
		$_directory='';
		foreach ($directory as $d)
		{
			$a='';
			foreach ($d as $k=>$v)
			{
				if ($a!='') $a.=", ";
				$a.=$k.'='.$v;
			}
			$_directory.=$a."\n";
		}
		tar_add_file($myfile,'DIRECTORY',$_directory,0664,time());
	} else
	{
		set_value('last_backup',strval(time()));
	}
	tar_close($myfile);
	if (!file_exists(get_custom_file_base().'/exports/backups/'.filter_naughty($file).'.tmp')) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
	rename(get_custom_file_base().'/exports/backups/'.filter_naughty($file).'.tmp',get_custom_file_base().'/exports/backups/'.filter_naughty($file).'.tar');
	sync_file('exports/backups/'.filter_naughty($file).'.tar');
	fix_permissions('exports/backups/'.filter_naughty($file).'.tar');

	$url=get_base_url().'/exports/backups/'.$file.'.tar';
	if (function_exists('gzopen'))
	{
		if (fwrite($logfile,"\n".do_lang('COMPRESSING')."\n")==0) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));

		$myfile=gzopen(get_custom_file_base().'/exports/backups/'.$file.'.tar.gz.tmp','wb') OR intelligent_write_error(get_custom_file_base().'/exports/backups/'.$file.'.tar.gz.tmp');
		$tar_path=get_custom_file_base().'/exports/backups/'.filter_naughty($file).'.tar';

		$fp_in=fopen($tar_path,'rb');
		while (!feof($fp_in))
		{
			$read=fread($fp_in,8192);
		   gzwrite($myfile,$read,strlen($read));
		}
		fclose($fp_in);
		gzclose($myfile);

		rename(get_custom_file_base().'/exports/backups/'.$file.'.tar.gz.tmp',get_custom_file_base().'/exports/backups/'.$file.'.tar.gz');

		fix_permissions(get_custom_file_base().'/exports/backups/'.$file.'.tar.gz');
		sync_file('exports/backups/'.filter_naughty($file).'.tar.gz');
		$url=get_base_url().'/exports/backups/'.$file.'.tar.gz';
	}

	if (fwrite($logfile,"\n".do_lang('SUCCESS')."\n")==0) warn_exit(do_lang_tempcode('COULD_NOT_SAVE_FILE'));
	fclose($logfile);
	sync_file($logfile_path);
	fix_permissions($logfile_path);
	sync_file($logfile_path);

	// Remote backup
	$copy_server=get_option('backup_server_hostname');
	if ($copy_server!='')
	{
		$path_stub=get_custom_file_base().'/exports/backups/';
		if (file_exists($path_stub.$file.'.tar.gz')) $_file=$file.'.tar.gz';
		elseif (file_exists($path_stub.$file.'.tar')) $_file=$file.'.tar';
		else $file=NULL;

		if (!is_null($file)) // If the backup was actually made
		{
			$copy_port=get_option('backup_server_port');
			if ($copy_port=='') $copy_port='21';
			$copy_user=get_option('backup_server_user');
			if ($copy_user=='') $copy_user='anonymous';
			$copy_password=get_option('backup_server_password');
			if (is_null($copy_password)) $copy_password=get_option('staff_address');
			$copy_path=get_option('backup_server_path');
			if ($copy_path=='') $copy_path=$_file;
			elseif ((substr($copy_path,-1)=='/') || ($copy_path=='')) $copy_path.=$_file;
			$ftp_connection=@ftp_connect($copy_server,intval($copy_port));
			if ($ftp_connection!==false)
			{
				if (@ftp_login($ftp_connection,$copy_user,$copy_password))
				{
					@ftp_delete($ftp_connection,$path_stub.$_file);
					@ftp_put($ftp_connection,$copy_path,$path_stub,FTP_BINARY);
				}
				@ftp_close($ftp_connection);
			}
		}
	}

	require_code('notifications');
	dispatch_notification('backup_finished',NULL,do_lang('BACKUP',NULL,NULL,NULL,get_site_default_lang()),do_lang('BACKUP_FINISHED',comcode_escape($url),get_site_default_lang()),NULL,A_FROM_SYSTEM_PRIVILEGED);
}


