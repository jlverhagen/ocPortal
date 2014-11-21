<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core
 */

/* This file exists split from database.php to alleviate PHP memory usage / load time. */

/**
 * Standard code module initialisation function.
 */
function init__database_helper()
{
    if (defined('DB_MAX_KEY_SIZE')) {
        return;
    }

    // Limits (we also limit field names to not conflict with keywords - those defined in database_action.php)
    define('DB_MAX_KEY_SIZE', 500);
    define('DB_MAX_PRIMARY_KEY_SIZE', 251);
    define('DB_MAX_ROW_SIZE', 8000);
    define('DB_MAX_FIELD_IDENTIFIER_SIZE', 31);
    define('DB_MAX_IDENTIFIER_SIZE', 32);
    // We have to take into account that chars might take 3 bytes - but we'll assume unicode is only used on db's with higher limits
    define('DB_MAX_KEY_SIZE_UNICODE', 1000);
    define('DB_MAX_ROW_SIZE_UNICODE', 24000);
}

/**
 * Check a set of fields aren't going to exceed key limits.
 *
 * @param  boolean                      Whether this will be in a primary key
 * @param  array                        The fields (a map between field name and field type [field type must start '*' if it is to be counted])
 * @param  ID_TEXT                      The name of what we are checking (only used to generate clear error messages)
 * @param  boolean                      Whether to skip the size check for the table (only do this for addon modules that don't need to support anything other than mySQL)
 * @param  boolean                      Whether to skip the check for NULL string fields
 */
function _check_sizes($primary_key, $fields, $id_name, $skip_size_check = false, $skip_null_check = false)
{
    // Check constraints
    $take_unicode_into_account = 3;
    $data_sizes = array(  // The maximum size fields could be from a database-neutral perspective
        'AUTO' => 4,
        'AUTO_LINK' => 4,
        'INTEGER' => 4,
        'UINTEGER' => 4,
        'REAL' => 4,
        'SHORT_INTEGER' => 2,
        'BINARY' => 1,
        'MEMBER' => 4,
        'GROUP' => 4,
        'TIME' => 4,
        'LONG_TRANS' => 4,
        'SHORT_TRANS' => 4,
        'LONG_TRANS__COMCODE' => 255 + 1,
        'SHORT_TRANS__COMCODE' => 255 + 1,
        'SHORT_TEXT' => $primary_key ? (150) : (255 + 1), /* We underestimate for primary key, as it is very unlikely to be very high and the limit only exists on our own 'xml' database driver as a run-time limit */
        'LONG_TEXT' => 255 + 1,
        'ID_TEXT' => $primary_key ? (16) : (80 + 1), /* We underestimate for primary key, as it is very unlikely to be very high and the limit only exists on our own 'xml' database driver as a run-time limit */
        'MINIID_TEXT' => 40 + 1,
        'IP' => 15 + 1,
        'LANGUAGE_NAME' => 5 + 1,
        'URLPATH' => 255 + 1,
        'MD5' => 33 + 1,
        'unicode_SHORT_TEXT' => $take_unicode_into_account * 255 + 1,
        'unicode_LONG_TEXT' => $take_unicode_into_account * 255 + 1,
        'unicode_ID_TEXT' => $take_unicode_into_account * 80 + 1,
        'unicode_IP' => $take_unicode_into_account * 15 + 1,
        'unicode_LANGUAGE_NAME' => $take_unicode_into_account * 5 + 1,
        'unicode_URLPATH' => $take_unicode_into_account * 255 + 1,
        'unicode_MD5' => $take_unicode_into_account * 33 + 1
    );
    $keywords = get_db_keywords();
    //if (in_array(strtoupper($table_name),$keywords)) fatal_exit($table_name.' is a keyword'); // No point, as we have table prefixes
    $key_size = 0;
    $total_size = 0;
    $key_size_unicode = 0;
    $total_size_unicode = 0;
    foreach ($fields as $name => $field) {
        $key = ($field[0] == '*');
        if ($key) {
            $field = substr($field, 1);
        }
        $full_text = ($field[0] == '#');
        if ($full_text) {
            $field = substr($field, 1);
        }
        $null = ($field[0] == '?');
        if ($null) {
            $field = substr($field, 1);
        }
        $size_restricted = (strpos($name, '(') !== false);
        if ($size_restricted) {
            $name = preg_replace('#\(.*\)$#', '', $name);
        }

        if ($key) {
            $key_size += $data_sizes[$field];
        }
        if (!isset($data_sizes[$field])) {
            $data_sizes[$field] = 10/*10=arbitrary default*/
            ;
        }
        $total_size += $data_sizes[$field];
        if ($key) {
            $key_size_unicode += $data_sizes[(array_key_exists('unicode_' . $field, $data_sizes) ? 'unicode_' : '') . $field];
        }
        $total_size_unicode += $data_sizes[(array_key_exists('unicode_' . $field, $data_sizes) ? 'unicode_' : '') . $field];

        if (($null) && (!$skip_null_check) && (($field == 'MINIID_TEXT') || ($field == 'ID_TEXT') || ($field == 'LANGUAGE_NAME') || ($field == 'MD5') || ($field == 'IP') || ($field == 'URLPATH') || ($field == 'LONG_TEXT') || ($field == 'SHORT_TEXT'))) { // Needed for Oracle, really
            fatal_exit('You may not have a NULL string field');
        }
        //if (($key) && (substr($id_name,0,1)!='#') && (!$size_restricted) && (($field=='LONG_TEXT'))) fatal_exit('You may not use a '.$field.' field for part of a key');    We now size restrict using "(255)"
        if (($key) && ($primary_key) && ($null)) {
            fatal_exit('No field that may be NULL may be a part of a primary key');
        }
        if (in_array(strtoupper($name), $keywords)) {
            fatal_exit($name . ' is a keyword');
        }
        if ((preg_match('#^[\w]+$#', $name) == 0) || (strlen($name) > DB_MAX_FIELD_IDENTIFIER_SIZE)) {
            fatal_exit('Inappropriate identifier: ' . $name);
        }
    }
    if ((!$skip_size_check) && (substr($id_name, 0, 1) != '#')) {
        if ($key_size >= ($primary_key ? DB_MAX_PRIMARY_KEY_SIZE : DB_MAX_KEY_SIZE)) {
            fatal_exit('Key too long at ' . integer_format($key_size) . ' bytes [' . $id_name . ']'); // 252 for firebird
        }
        if ($total_size >= DB_MAX_ROW_SIZE) {
            fatal_exit('Fieldset (row) too long at ' . integer_format($total_size) . ' bytes [' . $id_name . ']');
        }
        if ($key_size_unicode >= DB_MAX_KEY_SIZE_UNICODE) {
            fatal_exit('Unicode version of key too long at ' . integer_format($key_size_unicode) . ' bytes [' . $id_name . ']'); // 252 for firebird
        }
        if ($total_size_unicode >= DB_MAX_ROW_SIZE_UNICODE) {
            fatal_exit('Unicode version of fieldset (row) too long at ' . integer_format($total_size_unicode) . ' bytes [' . $id_name . ']');
        }
    }
}

/**
 * Create a table with the given name and the given array of field name to type mappings.
 * If a field type starts '*', then it is part of that field's key. If it starts '?', then it is an optional field.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  array                        The fields
 * @param  boolean                      Whether to skip the size check for the table (only do this for addon modules that don't need to support anything other than mySQL)
 * @param  boolean                      Whether to skip the check for NULL string fields
 */
function _helper_create_table($this_ref, $table_name, $fields, $skip_size_check = false, $skip_null_check = false)
{
    require_code('database_action');

    if ((preg_match('#^[\w]+$#', $table_name) == 0) || (strlen($table_name) + 7 > DB_MAX_IDENTIFIER_SIZE)) {
        fatal_exit('Inappropriate identifier: ' . $table_name); // (the +7 is for prefix: max length of 7 chars allocated for prefix)
    }

    if (!$skip_size_check) {
        _check_sizes(true, $fields, $table_name, false, false);
    }

    // Note that interbase has a 31000byte limit on LONG_TEXT/LONG_TRANS, because we can't use blobs on it (those have too many restraints)

    $fields_copy = $fields;
    foreach ($fields_copy as $name => $type) {
        if (($table_name != 'db_meta') && ($table_name != 'db_meta_indices')) {
            $this_ref->query_insert('db_meta', array('m_table' => $table_name, 'm_name' => $name, 'm_type' => $type), false, true); // Allow errors because sometimes bugs when developing can call for this happening twice
        }

        if (!multi_lang_content()) {
            if (strpos($type, '_TRANS') !== false) {
                if (strpos($type, '__COMCODE') !== false) {
                    $fields[$name . '__text_parsed'] = 'LONG_TEXT';
                    $fields[$name . '__source_user'] = 'MEMBER';
                }

                $fields[$name] = 'LONG_TEXT'; // In the DB layer, it must now save as such
            }
        }
    }
    if (count($this_ref->connection_write) > 4) { // Okay, we can't be lazy anymore
        $this_ref->connection_write = call_user_func_array(array($this_ref->static_ob, 'db_get_connection'), $this_ref->connection_write);
        _general_db_init();
    }
    $this_ref->static_ob->db_create_table($this_ref->table_prefix . $table_name, $fields, $this_ref->connection_write, $table_name);

    // Considering tabes in a DB reference may be in multiple (if they point to same actual DB's), make sure all our DB objects have their cache cleared
    if (isset($GLOBALS['SITE_DB'])) {
        unset($GLOBALS['SITE_DB']->table_exists_cache[$table_name]);
    }
    if (isset($GLOBALS['FORUM_DB'])) {
        unset($GLOBALS['FORUM_DB']->table_exists_cache[$table_name]);
    }
    // Then safely update our own
    $this_ref->table_exists_cache[$table_name] = true;

    if (!multi_lang_content()) {
        foreach ($fields_copy as $name => $type) {
            if (strpos($type, '_TRANS') !== false) {
                $GLOBALS['SITE_DB']->create_index($table_name, '#' . $name, array($name));
            }
        }
    }

    reload_lang_fields();
}

/**
 * Add an index to a table without disturbing the contents, after the table has been created.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  ID_TEXT                      The index name
 * @param  array                        The fields
 * @param  ID_TEXT                      The name of the unique key field for the table
 */
function _helper_create_index($this_ref, $table_name, $index_name, $fields, $unique_key_field = 'id')
{
    require_code('database_action');

    //if ($GLOBALS['DEV_MODE']) {  Actually, no we want to run in installer, which would not be in debug mode
        if ($table_name != 'db_meta') {
            $fields_full = array();
            foreach ($fields as $field) {
                $_field = preg_replace('#\(.*\)$#', '', $field);

                $db_type = $this_ref->query_select_value_if_there('db_meta', 'm_type', array('m_table' => $table_name, 'm_name' => $_field));
                if (is_null($db_type)) {
                    $db_type = 'SHORT_TEXT';
                    if (running_script('install')) {
                        fatal_exit('It seems we are creating an index on a table that is not yet created.');
                    }
                }
                if (substr($db_type, 0, 1) != '*') {
                    $db_type = '*' . $db_type;
                }
                $fields_full[$field] = $db_type;
            }
            _check_sizes(false, $fields_full, $index_name, false, true);
        }
    //}

    $keywords = get_db_keywords();
    if (in_array(strtoupper(str_replace('#', '', $index_name)), $keywords)) {
        fatal_exit($index_name . ' is a keyword');
    }
    if (preg_match('#^[\#\w]+$#', $index_name) == 0) {
        fatal_exit('Inappropriate identifier: ' . $index_name);
    }
    if (strlen($index_name) + 7 > DB_MAX_IDENTIFIER_SIZE) {
        fatal_exit('Inappropriate identifier, too long: ' . $index_name);
    }

    $ok_to_create = true;

    $_fields = '';
    foreach ($fields as $field) {
        if ($_fields != '') {
            $_fields .= ',';
        }
        $_fields .= $field;

        if ((!multi_lang_content()) && (substr($index_name, 0, 1) != '#')) {
            global $TABLE_LANG_FIELDS_CACHE;
            if (isset($TABLE_LANG_FIELDS_CACHE[$table_name][$field])) {
                $_fields .= '(255)';
            }
        }

        if ((multi_lang_content()) && (strpos($index_name, '__combined') !== false) && (substr($index_name, 0, 1) == '#') && ($table_name != 'translate')) {
            $ok_to_create = false;
        }
    }
    $this_ref->query_insert('db_meta_indices', array('i_table' => $table_name, 'i_name' => $index_name, 'i_fields' => implode(',', $fields)), false, true); // Allow errors because sometimes bugs when developing can call for this happening twice

    if ($ok_to_create) {
        if (count($this_ref->connection_write) > 4) { // Okay, we can't be lazy anymore
            $this_ref->connection_write = call_user_func_array(array($this_ref->static_ob, 'db_get_connection'), $this_ref->connection_write);
            _general_db_init();
        }

        $this_ref->static_ob->db_create_index($this_ref->table_prefix . $table_name, $index_name, $_fields, $this_ref->connection_write, $unique_key_field);
    }
}

/**
 * Delete an index from a table.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  ID_TEXT                      The index name
 */
function _helper_delete_index_if_exists($this_ref, $table_name, $index_name)
{
    $full_index_name = $index_name;
    if (substr($index_name, 0, 1) == '#') {
        $index_name = substr($index_name, 1);
    }
    $query = 'DROP INDEX ' . $index_name . ' ON ' . $this_ref->get_table_prefix() . $table_name;
    $this_ref->query($query, null, null, true);

    if (isset($GLOBALS['XML_CHAIN_DB'])) {
        // DB chaining: It's a write query, so needs doing on chained DB too
        $GLOBALS['XML_CHAIN_DB']->_query($query, null, null, true);
    }

    $this_ref->query_delete('db_meta_indices', array('i_table' => $table_name, 'i_name' => $full_index_name));
}

/**
 * Drop the given table, or if it doesn't exist, silently return.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 */
function _helper_drop_table_if_exists($this_ref, $table)
{
    if (($table != 'db_meta') && ($table != 'db_meta_indices')) {
        if ((function_exists('mass_delete_lang')) && (multi_lang_content())) {
            $attrs = $this_ref->query_select('db_meta', array('m_name', 'm_type'), array('m_table' => $table));
            $_attrs = array();
            foreach ($attrs as $attr) {
                if (in_array(preg_replace('#[^\w]#', '', $attr['m_type']), array('SHORT_TRANS', 'LONG_TRANS', 'SHORT_TRANS__COMCODE', 'LONG_TRANS__COMCODE'))) {
                    $_attrs[] = $attr['m_name'];
                }
            }
            mass_delete_lang($table, $_attrs, $this_ref);
        }

        $this_ref->query_delete('db_meta', array('m_table' => $table));
        $this_ref->query_delete('db_meta_indices', array('i_table' => $table));
    }
    if (count($this_ref->connection_write) > 4) { // Okay, we can't be lazy anymore
        $this_ref->connection_write = call_user_func_array(array($this_ref->static_ob, 'db_get_connection'), $this_ref->connection_write);
        _general_db_init();
    }
    $this_ref->static_ob->db_drop_table_if_exists($this_ref->table_prefix . $table, $this_ref->connection_write);

    if (function_exists('persistent_cache_delete')) {
        persistent_cache_delete('TABLE_LANG_FIELDS_CACHE');
    }
}

/**
 * Rename the given table.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The old table name
 * @param  ID_TEXT                      The new table name
 */
function _helper_rename_table($this_ref, $old, $new)
{
    $query = 'ALTER TABLE ' . $this_ref->table_prefix . $old . ' RENAME ' . $this_ref->table_prefix . $new;
    $this_ref->query($query);

    if (isset($GLOBALS['XML_CHAIN_DB'])) {
        // DB chaining: It's a write query, so needs doing on chained DB too
        $GLOBALS['XML_CHAIN_DB']->_query($query);
    }

    $this_ref->query_update('db_meta', array('m_table' => $new), array('m_table' => $old));
    $this_ref->query_update('db_meta_indices', array('i_table' => $new), array('i_table' => $old));

    if (function_exists('persistent_cache_delete')) {
        persistent_cache_delete('TABLE_LANG_FIELDS_CACHE');
    }
}

/**
 * Adds a field to an existing table.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  ID_TEXT                      The field name
 * @param  ID_TEXT                      The field type
 * @param  ?mixed                       The default value (null: no default)
 */
function _helper_add_table_field($this_ref, $table_name, $name, $_type, $default = null)
{
    $lang_level = 3;
    $default_st = null;

    if (is_null($default)) {
        switch (str_replace(array('*', '?'), array('', ''), $_type)) {
            case 'AUTO':
                $default = null;
                break;

            case 'AUTO_LINK':
            case 'UINTEGER':
            case 'INTEGER':
            case 'SHORT_INTEGER':
            case 'REAL':
            case 'BINARY':
            case 'MEMBER':
            case 'GROUP':
            case 'TIME':
                $default = ($_type[0] == '?') ? null : 1;
                break;

            case 'LONG_TRANS':
            case 'SHORT_TRANS':
                $default = null;
                break;

            case 'LONG_TRANS__COMCODE':
            case 'SHORT_TRANS__COMCODE':
                $default = multi_lang_content() ? null : '';
                break;

            case 'SHORT_TEXT':
            case 'LONG_TEXT':
            case 'ID_TEXT':
            case 'MINIID_TEXT':
            case 'IP':
            case 'LANGUAGE_NAME':
            case 'URLPATH':
            case 'MD5':
                $default = '';
                break;
        }
    }

    $type_remap = $this_ref->static_ob->db_get_type_remap();

    $final_type = $_type;
    if (strpos($_type, '_TRANS') !== false) {
        if ((is_null($default)) && (strpos($_type, '?') === false)) {
            $default = '';
        }

        if (multi_lang_content()) {
            if (is_string($default)) {
                $default_st = $default;
                $default = 0;
            }
        } else {
            $final_type = 'LONG_TEXT'; // In the DB layer, it must now save as such
        }
    }

    if ($final_type[0] == '?') {
        $tag = ' NULL';
    } else {
        $tag = ' NOT NULL';
    }
    $type = str_replace(array('*', '?'), array('', ''), $final_type);
    $extra = '';
    if (($final_type != 'LONG_TEXT') || (get_db_type() == 'postgresql')) {
        $extra = is_null($default) ? 'DEFAULT NULL' : (' DEFAULT ' . (is_string($default) ? ('\'' . db_escape_string($default) . '\'') : strval($default)));
    }
    $query = 'ALTER TABLE ' . $this_ref->table_prefix . $table_name;
    $query .= ' ADD ' . $name . ' ' . $type_remap[$type] . ' ' . $extra . ' ' . $tag;
    if (substr($_type, 0, 1) == '*') {
        $query .= ', ADD PRIMARY KEY (' . $name . ')';
    }
    $this_ref->_query($query);

    if (isset($GLOBALS['XML_CHAIN_DB'])) {
        // DB chaining: It's a write query, so needs doing on chained DB too
        $GLOBALS['XML_CHAIN_DB']->_query($query);
    }

    if (multi_lang_content()) {
        if (!is_null($default_st)) {
            $start = 0;
            do {
                $rows = $this_ref->_query('SELECT * FROM ' . $this_ref->get_table_prefix() . $table_name, 1000, $start);
                foreach ($rows as $row) {
                    $this_ref->query_update($table_name, insert_lang($name, $default_st, $lang_level), $row);
                }
                $start += 1000;
            }
            while (count($rows) > 0);
        }
    }

    $this_ref->query_insert('db_meta', array('m_table' => $table_name, 'm_name' => $name, 'm_type' => $_type));
    reload_lang_fields();

    if (!multi_lang_content()) {
        if (strpos($_type, '_TRANS') !== false) {
            $GLOBALS['SITE_DB']->create_index($table_name, '#' . $name, array($name));
        }
    }

    if ((!multi_lang_content()) && (strpos($_type, '__COMCODE') !== false)) {
        foreach (array('text_parsed' => 'LONG_TEXT', 'source_user' => 'MEMBER') as $sub_name => $sub_type) {
            $sub_name = $name . '__' . $sub_name;
            $query = 'ALTER TABLE ' . $this_ref->table_prefix . $table_name . ' ADD ' . $sub_name . ' ' . $type_remap[$sub_type];
            if ($sub_name == 'text_parsed') {
                $query .= ' DEFAULT \'\'';
            } elseif ($sub_name == 'source_user') {
                $query .= ' DEFAULT ' . strval(db_get_first_id());
            }
            $query .= ' NOT NULL';
            $this_ref->_query($query);

            if (isset($GLOBALS['XML_CHAIN_DB'])) {
                // DB chaining: It's a write query, so needs doing on chained DB too
                $GLOBALS['XML_CHAIN_DB']->_query($query);
            }
        }
    }

    if (function_exists('persistent_cache_delete')) {
        persistent_cache_delete('TABLE_LANG_FIELDS_CACHE');
    }
}

/**
 * Change the type of a DB field in a table. Note: this function does not support ascession/decession of translatability
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  ID_TEXT                      The field name
 * @param  ID_TEXT                      The new field type
 * @param  ?ID_TEXT                     The new field name (null: leave name)
 */
function _helper_alter_table_field($this_ref, $table_name, $name, $_type, $new_name = null)
{
    $type_remap = $this_ref->static_ob->db_get_type_remap();

    if ((strpos($_type, '__COMCODE') !== false) && (!is_null($new_name)) && ($new_name != $name)) {
        foreach (array('text_parsed' => 'LONG_TEXT', 'source_user' => 'MEMBER') as $sub_name => $sub_type) {
            $sub_name = $name . '__' . $sub_name;
            $sub_new_name = $new_name . '__' . $sub_name;
            $query = 'ALTER TABLE ' . $this_ref->table_prefix . $table_name . ' CHANGE ' . $sub_name . ' ' . $sub_new_name . ' ' . $type_remap[$sub_type];
            if ($sub_name == 'text_parsed') {
                $query .= ' DEFAULT \'\'';
            } elseif ($sub_name == 'source_user') {
                $query .= ' DEFAULT ' . strval(db_get_first_id());
            }
            $query .= ' NOT NULL';
            $this_ref->_query($query);

            if (isset($GLOBALS['XML_CHAIN_DB'])) {
                // DB chaining: It's a write query, so needs doing on chained DB too
                $GLOBALS['XML_CHAIN_DB']->_query($query);
            }
        }
    }

    if ($_type[0] == '?') {
        $tag = ' NULL';
    } else {
        $tag = ' NOT NULL';
    }
    $type = str_replace(array('*', '?'), array('', ''), $_type);
    $extra = (!is_null($new_name)) ? $new_name : $name;
    $extra2 = '';
    if (substr(get_db_type(), 0, 5) == 'mysql') {
        $extra2 = 'IGNORE ';
    }
    $query = 'ALTER ' . $extra2 . 'TABLE ' . $this_ref->table_prefix . $table_name;
    $query .= ' CHANGE ';
    if (strpos(get_db_type(), 'mysql') !== false) {
        $query .= '`' . $name . '`'; // In case we renamed due to change in keywords
    } else {
        $query .= $name;
    }
    $query .= $extra . ' ' . $type_remap[$type] . ' ' . $tag;
    if (substr($_type, 0, 1) == '*') {
        $query .= ', ADD PRIMARY KEY (' . ((!is_null($new_name)) ? $new_name : $name) . ')';
    }
    $this_ref->_query($query);

    if (isset($GLOBALS['XML_CHAIN_DB'])) {
        // DB chaining: It's a write query, so needs doing on chained DB too
        $GLOBALS['XML_CHAIN_DB']->_query($query);
    }

    $update_map = array('m_type' => $_type);
    if (!is_null($new_name)) {
        $update_map['m_name'] = $new_name;
    }
    $this_ref->query_update('db_meta', $update_map, array('m_table' => $table_name, 'm_name' => $name));

    if (function_exists('persistent_cache_delete')) {
        persistent_cache_delete('TABLE_LANG_FIELDS_CACHE');
    }
}

/**
 * Change the primary key of a table.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The name of the table to create the index on
 * @param  array                        A list of fields to put in the new key
 */
function _helper_change_primary_key($this_ref, $table_name, $new_key)
{
    if (count($this_ref->connection_write) > 4) { // Okay, we can't be lazy anymore
        $this_ref->connection_write = call_user_func_array(array($this_ref->static_ob, 'db_get_connection'), $this_ref->connection_write);
        _general_db_init();
    }
    $this_ref->static_ob->db_change_primary_key($this_ref->table_prefix . $table_name, $new_key, $this_ref->connection_write);
}

/**
 * If a text field has picked up Comcode support, we will need to run this.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  ID_TEXT                      The field name
 * @param  ID_TEXT                      The tables key field name
 * @param  integer                      The translation level to use
 * @set    1 2 3 4
 * @param  boolean                      Whether our data is already stored in Tempcode assembly format
 */
function _helper_promote_text_field_to_comcode($this_ref, $table_name, $name, $key = 'id', $level = 2, $in_assembly = false)
{
    $rows = $this_ref->query_select($table_name, array($name, $key));
    $this_ref->delete_table_field($table_name, $name);
    $this_ref->add_table_field($table_name, $name, 'SHORT_TRANS__COMCODE');
    foreach ($rows as $row) {
        if ($in_assembly) {
            $map = insert_lang($name, '', $level, $this_ref, true, null, null, false, null, $row[$name]);
        } else {
            $map = insert_lang($name, $row[$name], $level, $this_ref);
        }
        $this_ref->query_update($table_name, $map, array($key => $row[$key]));
    }

    if (function_exists('persistent_cache_delete')) {
        persistent_cache_delete('TABLE_LANG_FIELDS_CACHE');
    }
}

/**
 * Delete the specified field from the specified table.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The table name
 * @param  ID_TEXT                      The field name
 */
function _helper_delete_table_field($this_ref, $table_name, $name)
{
    $type = $this_ref->query_select_value_if_there('db_meta', 'm_type', array('m_table' => $table_name, 'm_name' => $name));
    if (is_null($type)) {
        $type = 'SHORT_TEXT';
    }

    if ((strpos($type, '_TRANS') !== false) && (multi_lang_content())) {
        require_code('database_action');
        mass_delete_lang($table_name, array($name), $this_ref);
    }

    $cols_to_delete = array($name);
    if (strpos($type, '_TRANS__COMCODE') !== false) {
        if (!multi_lang_content()) {
            $cols_to_delete[] = $name . '__text_parsed';
            $cols_to_delete[] = $name . '__source_user';
        }
    }

    foreach ($cols_to_delete as $col) {
        $query = 'ALTER TABLE ' . $this_ref->table_prefix . $table_name . ' DROP COLUMN ' . $col;
        $this_ref->_query($query);

        if (isset($GLOBALS['XML_CHAIN_DB'])) {
            // DB chaining: It's a write query, so needs doing on chained DB too
            $GLOBALS['XML_CHAIN_DB']->_query($query);
        }
    }

    $this_ref->query_delete('db_meta', array('m_table' => $table_name, 'm_name' => $name));
    $this_ref->query_delete('db_meta_indices', array('i_table' => $table_name, 'i_fields' => $name));

    if (function_exists('persistent_cache_delete')) {
        persistent_cache_delete('TABLE_LANG_FIELDS_CACHE');
    }
}

/**
 * If we've changed what $type is stored as, this function will need to be called to change the typing in the DB.
 *
 * @param  object                       Link to the real database object
 * @param  ID_TEXT                      The field type
 */
function _helper_refresh_field_definition($this_ref, $type)
{
    $do = array();
    $rows = $this_ref->query_select('db_meta', array('*'), array('m_type' => $type));
    foreach ($rows as $row) {
        $do[] = array($row['m_table'], $row['m_name']);
    }

    foreach ($do as $it) {
        $this_ref->alter_table_field($it[0], $it[1], $type);
    }
}
