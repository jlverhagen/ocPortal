<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_language_editing
 */

/**
 * Rebuild database indices, using correct rules for new field types.
 *
 * @param  boolean		Whether to only rebuild translatable field indexes
 */
function rebuild_indices($only_trans=false)
{
	global $TABLE_LANG_FIELDS_CACHE;

	$GLOBALS['NO_DB_SCOPE_CHECK']=true;

	$indices=$GLOBALS['SITE_DB']->query_select('db_meta_indices',array('*'));
	foreach ($indices as $index)
	{
		$fields=explode(',',$index['i_fields']);
		$ok=false;
		foreach ($fields as $field)
		{
			if ((isset($TABLE_LANG_FIELDS_CACHE[$index['i_table']][$field])) || (!$only_trans))
			{
				$ok=true;
				break;
			}
		}
		if ($ok)
		{
			$GLOBALS['SITE_DB']->delete_index_if_exists($index['i_table'],$index['i_name']);
			$GLOBALS['SITE_DB']->create_index($index['i_table'],$index['i_name'],$fields);
		}
	}
}

/**
 * Disable content translation.
 */
function disable_content_translation()
{
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;
	$GLOBALS['NO_QUERY_LIMIT']=true;

	if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));
	if (!multi_lang_content()) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

	if (function_exists('set_time_limit')) @set_time_limit(0);

	$db=$GLOBALS['SITE_DB'];

	$type_remap=$db->static_ob->db_get_type_remap();

	$_table_lang_fields=$db->query('SELECT m_table,m_name,m_type FROM '.$db->get_table_prefix().'db_meta WHERE m_type LIKE \''.db_encode_like('%_TRANS%').'\' ORDER BY m_table,m_name');
	foreach ($_table_lang_fields as $field)
	{
		if (running_script('execute_temp')) @var_dump($field);

		// Add new implied fields for holding extra Comcode details, and new field to hold main Comcode
		$to_add=array('new'=>'LONG_TEXT');
		if (strpos($field['m_type'],'__COMCODE')!==false)
		{
			$to_add+=array('text_parsed'=>'LONG_TEXT','source_user'=>'MEMBER');
		}
		foreach ($to_add as $sub_name=>$sub_type)
		{
			$sub_name=$field['m_name'].'__'.$sub_name;
			$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' ADD '.$sub_name.' '.$type_remap[$sub_type];
			if ($sub_name=='text_parsed')
			{
				$query.=' DEFAULT \'\'';
			} elseif ($sub_name=='new')
			{
				$query.=' DEFAULT \'\''; // Has a default of '' for now, will be removed further down
			} elseif ($sub_name=='source_user')
			{
				$query.=' DEFAULT '.strval(db_get_first_id());
			}
			$query.=' NOT NULL';
			$db->_query($query);
		}

		// Copy from translate table
		$query='UPDATE '.$db->table_prefix.$field['m_table'].' a SET ';
		$query.='a.'.$field['m_name'].'__new=IFNULL((SELECT b.text_original FROM '.$db->table_prefix.'translate b WHERE b.id=a.'.$field['m_name'].' ORDER BY broken), \'\')';
		if (strpos($field['m_type'],'__COMCODE')!==false)
		{
			$query.=', a.'.$field['m_name'].'__source_user=IFNULL((SELECT b.source_user FROM '.$db->table_prefix.'translate b WHERE b.id=a.'.$field['m_name'].' ORDER BY broken), '.strval(db_get_first_id()).')';
			$query.=', a.'.$field['m_name'].'__text_parsed=\'\'';
		}
		$db->_query($query);

		// Delete old main field
		$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' DROP COLUMN '.$field['m_name'];
		$db->_query($query);

		// Rename Comcode field to main field, and don't put default of '' on it anymore
		$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' CHANGE '.$field['m_name'].'__new '.$field['m_name'].' '.$type_remap['LONG_TEXT'].' NOT NULL';
		$db->_query($query);

		// Create fulltext search index
		$GLOBALS['SITE_DB']->create_index($field['m_table'],'#'.$field['m_name'],array($field['m_name']));

		reload_lang_fields();
	}

	global $HAS_MULTI_LANG_CONTENT;
	$HAS_MULTI_LANG_CONTENT=false;

	// Empty translate table
	$GLOBALS['SITE_DB']->query_delete('translate');

	_update_base_config_for_content_translation(false);

	rebuild_indices(true);
}

/**
 * Enable content translation.
 */
function enable_content_translation()
{
	$GLOBALS['NO_DB_SCOPE_CHECK']=true;
	$GLOBALS['NO_QUERY_LIMIT']=true;

	if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));
	if (multi_lang_content()) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));

	if (function_exists('set_time_limit')) @set_time_limit(0);

	$db=$GLOBALS['SITE_DB'];

	$type_remap=$db->static_ob->db_get_type_remap();

	$_table_lang_fields=$db->query('SELECT m_table,m_name,m_type FROM '.$db->get_table_prefix().'db_meta WHERE m_type LIKE \''.db_encode_like('%_TRANS%').'\' ORDER BY m_table,m_name');
	foreach ($_table_lang_fields as $field)
	{
		if (running_script('execute_temp')) @var_dump($field);

		// Remove old fulltext search index
		$GLOBALS['SITE_DB']->delete_index_if_exists($field['m_table'],'#'.$field['m_name']);

		// Rename main field to temporary one
		$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' CHANGE '.$field['m_name'].' '.$field['m_name'].'__old '.$type_remap['LONG_TEXT'];
		$db->_query($query);

		$_type=$field['m_type'];
		if (substr($_type,0,1)=='*') $_type=substr($_type,1);
		if (substr($_type,0,1)=='?') $_type=substr($_type,1);

		// Add new field for translate reference
		$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' ADD '.$field['m_name'].' '.$type_remap[$_type];
		$query.=' DEFAULT 0';
		if (substr($field['m_type'],0,1)!='?') $query.=' NOT NULL';
		$db->_query($query);
		// Now alter it without the default
		$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' CHANGE '.$field['m_name'].' '.$field['m_name'].' '.$type_remap[$_type];
		if (substr($field['m_type'],0,1)!='?') $query.=' NOT NULL';
		$db->_query($query);

		$has_comcode=(strpos($field['m_type'],'__COMCODE')!==false);

		// Copy to translate table
		$start=0;
		do
		{
			$trans=$db->query_select($field['m_table'],array('*'),NULL,'',100,$start,false,array()/*Needs to disable auto-field-grabbing as DB state is currently inconsistent*/);
			foreach ($trans as $t)
			{
				$insert_map=array(
					'language'=>get_site_default_lang(),
					'importance_level'=>3,
					'text_original'=>$t[$field['m_name'].'__old'],
					'text_parsed'=>$has_comcode?$t[$field['m_name'].'__text_parsed']:'',
					'broken'=>0,
					'source_user'=>$has_comcode?$t[$field['m_name'].'__source_user']:$GLOBALS['FORUM_DRIVER']->get_guest_id(),
				);
				$ins_id=$db->query_insert('translate',$insert_map,true);
				$GLOBALS['SITE_DB']->query_update($field['m_table'],array($field['m_name']=>$ins_id),$t,'',1);
			}
			$start+=100;
		}
		while (count($trans)>0);

		// Delete old fields
		$to_delete=array('old');
		if ($has_comcode)
		{
			// Delete old implied fields for holding extra Comcode details
			$to_delete=array_merge($to_delete,array('text_parsed','source_user'));
		}
		foreach ($to_delete as $sub_name)
		{
			$sub_name=$field['m_name'].'__'.$sub_name;
			$query='ALTER TABLE '.$db->table_prefix.$field['m_table'].' DROP COLUMN '.$sub_name;
			$db->_query($query);
		}

		reload_lang_fields();
	}

	global $HAS_MULTI_LANG_CONTENT;
	$HAS_MULTI_LANG_CONTENT=true;

	_update_base_config_for_content_translation(true);

	rebuild_indices(true);
}

/**
 * Change content translation setting in th config file.
 *
 * @param  boolean		New setting value (i.e. on or off)
 */
function _update_base_config_for_content_translation($new_setting)
{
	$config_path=get_file_base().'/_config.php';
	$config_file=file_get_contents($config_path);
	$has='$SITE_INFO[\'multi_lang_content\']=\''.($new_setting?'0':'1').'\';';
	$wants='$SITE_INFO[\'multi_lang_content\']=\''.($new_setting?'1':'0').'\';';
	if (strpos($config_file,$has)!==false || strpos($config_file,$wants)!==false)
	{
		$config_file=str_replace($has,$wants,$config_file);
		$config_file=str_replace($wants,$wants,$config_file);
	} else
	{
		$config_file=rtrim($config_file)."\n".$wants."\n";
	}
	file_put_contents($config_path,$config_file);
	sync_file($config_path);
}