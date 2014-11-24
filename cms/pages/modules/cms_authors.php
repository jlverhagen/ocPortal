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
 * @package    authors
 */

/**
 * Module page class.
 */
class Module_cms_authors
{
    /**
     * Find details of the module.
     *
     * @return ?array                   Map of module info (null: module is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 3;
        $info['locked'] = false;
        return $info;
    }

    /**
     * Find entry-points available within this module.
     *
     * @param  boolean                  Whether to check permissions.
     * @param  ?MEMBER                  The member to check permissions as (null: current user).
     * @param  boolean                  Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
     * @param  boolean                  Whether to avoid any entry-point (or even return NULL to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "misc" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
     * @return ?array                   A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (null: disabled).
     */
    public function get_entry_points($check_perms = true, $member_id = null, $support_crosslinks = true, $be_deferential = false)
    {
        $ret = array(
            'misc' => array('AUTHOR_MANAGE', 'menu/rich_content/authors'),
            '_add' => array('EDIT_MY_AUTHOR_PROFILE', 'menu/cms/author_set_own_profile'),
            'edit' => array('EDIT_MERGE_AUTHORS', 'menu/_generic_admin/edit_one'),
        );

        if ($support_crosslinks) {
            require_code('fields');
            $ret += manage_custom_fields_entry_points('author');
        }
        return $ret;
    }

    /**
     * Find privileges defined as overridable by this module.
     *
     * @return array                    A map of privileges that are overridable; privilege to 0 or 1. 0 means "not category overridable". 1 means "category overridable".
     */
    public function get_privilege_overrides()
    {
        require_lang('authors');
        return array('submit_midrange_content' => array(0, 'ADD_AUTHOR'), 'edit_own_midrange_content' => array(0, 'EDIT_OWN_AUTHOR'), 'edit_midrange_content' => array(0, 'EDIT_MERGE_AUTHORS'), 'delete_own_midrange_content' => array(0, 'DELETE_OWN_AUTHOR'), 'delete_midrange_content' => array(0, 'DELETE_AUTHOR'));
    }

    public $title;
    public $author;

    /**
     * Module pre-run function. Allows us to know meta-data for <head> before we start streaming output.
     *
     * @return ?tempcode                Tempcode indicating some kind of exceptional output (null: none).
     */
    public function pre_run()
    {
        $type = get_param('type', 'misc');

        require_lang('authors');

        if ($type == '_add') {
            inform_non_canonical_parameter('author');

            breadcrumb_set_parents(array(array('_SELF:_SELF:misc', do_lang_tempcode('AUTHOR_MANAGE'))));

            $author = get_param('author', $GLOBALS['FORUM_DRIVER']->get_username(get_member()));
            if (get_param('author', null) === null) {
                $this->title = get_screen_title('DEFINE_AUTHOR');
            } else {
                $this->title = get_screen_title('_DEFINE_AUTHOR', true, array($author));
            }

            $this->author = $author;
        }

        if ($type == '__ad') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:misc', do_lang_tempcode('AUTHOR_MANAGE'))));
            breadcrumb_set_self(do_lang_tempcode('DONE'));

            $this->title = get_screen_title('DEFINE_AUTHOR');
        }

        if ($type == '_merge') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:misc', do_lang_tempcode('AUTHOR_MANAGE'))));
            breadcrumb_set_self(do_lang_tempcode('DONE'));

            $this->title = get_screen_title('MERGE_AUTHORS');
        }

        if ($type == 'edit') {
            breadcrumb_set_parents(array(array('_SELF:_SELF:misc', do_lang_tempcode('AUTHOR_MANAGE'))));

            $this->title = get_screen_title('EDIT_MERGE_AUTHORS');
        }

        set_helper_panel_tutorial('tut_authors');

        return null;
    }

    /**
     * Execute the module.
     *
     * @return tempcode                 The result of execution.
     */
    public function run()
    {
        require_code('authors');

        // Decide what we're doing
        $type = get_param('type', 'misc');

        if ($type == 'misc') {
            return $this->misc();
        }
        if ($type == '_add') {
            return $this->_add();
        }
        if ($type == '__ad') {
            return $this->__ad();
        }
        if ($type == '_merge') {
            return $this->_merge();
        }
        if ($type == 'edit') {
            return $this->edit();
        }

        return new Tempcode();
    }

    /**
     * The do-next manager for before content management.
     *
     * @return tempcode                 The UI
     */
    public function misc()
    {
        require_code('fields');
        require_code('templates_donext');
        return do_next_manager(get_screen_title('AUTHOR_MANAGE'), comcode_lang_string('DOC_AUTHORS'),
            array_merge(array(
                has_privilege(get_member(), 'set_own_author_profile') ? array('menu/cms/author_set_own_profile', array('_SELF', array('type' => '_add'), '_SELF'), do_lang('EDIT_MY_AUTHOR_PROFILE')) : null,
                has_privilege(get_member(), 'edit_midrange_content', 'cms_authors') ? array('menu/_generic_admin/add_one', array('_SELF', array('type' => '_add', 'author' => ''), '_SELF'), do_lang('ADD_AUTHOR')) : null,
                has_privilege(get_member(), 'edit_midrange_content', 'cms_authors') ? array('menu/_generic_admin/edit_one', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('EDIT_MERGE_AUTHORS')) : null,
            ), manage_custom_fields_donext_link('author')),
            do_lang('AUTHOR_MANAGE')
        );
    }

    /**
     * The UI to add an author.
     *
     * @return tempcode                 The UI
     */
    public function _add()
    {
        require_code('form_templates');

        url_default_parameters__enable();

        $author = $this->author;
        if (!has_edit_author_permission(get_member(), $author)) {
            if (get_author_id_from_name($author) == get_member()) {
                access_denied('PRIVILEGE', 'set_own_author_profile');
            }
            access_denied('PRIVILEGE', 'edit_midrange_content');
        }

        $rows = $GLOBALS['SITE_DB']->query_select('authors', array('description', 'url', 'skills', 'member_id'), array('author' => $author), '', 1);
        if (array_key_exists(0, $rows)) {
            $myrow = $rows[0];
            $description = get_translated_text($myrow['description']);
            $url = $myrow['url'];
            $skills = get_translated_text($myrow['skills']);
            $handle = $myrow['member_id'];
            $may_delete = true;
        } else {
            $description = '';
            if (get_forum_type() == 'ocf') {
                require_code('ocf_members');
                require_lang('ocf');
                $info = ocf_get_all_custom_fields_match_member(get_member());
                if (array_key_exists(do_lang('DEFAULT_CPF_SELF_DESCRIPTION_NAME'), $info)) {
                    $description = $info[do_lang('DEFAULT_CPF_SELF_DESCRIPTION_NAME')]['RENDERED'];
                    if (is_object($description)) {
                        $description = $description->evaluate();
                    }
                }
            }
            $url = '';
            $skills = '';
            $handle = null;
            $may_delete = false;
        }

        if (is_null($handle)) {
            $handle = $GLOBALS['FORUM_DRIVER']->get_member_from_username($author);
            if (!is_null($handle)) {
                $handle = strval($handle);
            }
        }

        $post_url = build_url(array('page' => '_SELF', 'type' => '__ad', 'author' => $author), '_SELF');
        $submit_name = do_lang_tempcode('SAVE');

        $fields = new Tempcode();
        $hidden = new Tempcode();

        if (is_null($handle)) {
            $fields->attach(form_input_line(do_lang_tempcode('AUTHOR'), do_lang_tempcode('DESCRIPTION_NAME'), 'author', $author, true));
        }
        $fields->attach(form_input_line(do_lang_tempcode('AUTHOR_URL'), do_lang_tempcode('DESCRIPTION_AUTHOR_URL'), 'url', $url, false));
        $fields->attach(form_input_line_comcode(do_lang_tempcode('SKILLS'), do_lang_tempcode('DESCRIPTION_SKILLS'), 'skills', $skills, false));
        $fields->attach(form_input_text_comcode(do_lang_tempcode('DESCRIPTION'), do_lang_tempcode('DESCRIPTION_MEMBER_DESCRIPTION'), 'description', $description, false));

        if (has_privilege(get_member(), 'edit_midrange_content', 'cms_authors')) {
            $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => 'b18ab131f72a024039eaa92814f0f4a9', 'SECTION_HIDDEN' => !is_null($handle), 'TITLE' => do_lang_tempcode('ADVANCED'))));
            $fields->attach(form_input_username(do_lang_tempcode('MEMBER'), do_lang_tempcode('DESCRIPTION_MEMBER_AUTHOR'), 'member_id', is_null($handle) ? '' : $GLOBALS['FORUM_DRIVER']->get_username(intval($handle)), false));
        } else {
            $hidden->attach(form_input_hidden('member_id', strval($handle)));
        }

        require_code('fields');
        if (has_tied_catalogue('author')) {
            append_form_custom_fields('author', $author, $fields, $hidden);
        }

        require_code('seo2');
        $fields->attach(seo_get_fields('authors', $author));

        // Awards?
        if (addon_installed('awards')) {
            require_code('awards');
            $fields->attach(get_award_fields('author', $author));
        }

        if (addon_installed('content_reviews')) {
            require_code('content_reviews2');
            $fields->attach(content_review_get_fields('author', $author));
        }

        if ($may_delete) {
            $fields->attach(do_template('FORM_SCREEN_FIELD_SPACER', array('_GUID' => '8a83b3253a6452c90e92699d629b9d03', 'TITLE' => do_lang_tempcode('ACTIONS'))));
            $fields->attach(form_input_tick(do_lang_tempcode('DELETE'), do_lang_tempcode('DESCRIPTION_DELETE'), 'delete', false));
        }

        url_default_parameters__disable();

        return do_template('FORM_SCREEN', array('_GUID' => '1d71c934e3e23fe394f5611191089630', 'PREVIEW' => true, 'HIDDEN' => $hidden, 'TITLE' => $this->title, 'TEXT' => '', 'FIELDS' => $fields, 'URL' => $post_url, 'SUBMIT_ICON' => 'buttons__save', 'SUBMIT_NAME' => $submit_name));
    }

    /**
     * The actualiser to add an author.
     *
     * @return tempcode                 The UI
     */
    public function __ad()
    {
        require_code('content2');
        $author = post_param('author', get_param('author'));
        if (!has_edit_author_permission(get_member(), $author)) {
            access_denied('PRIVILEGE', 'edit_midrange_content');
        }
        if ($author == '') {
            $member_id_string = post_param('member_id', strval(get_member()));
            $author = is_numeric($member_id_string) ? $GLOBALS['FORUM_DRIVER']->get_username(intval($member_id_string)) : $member_id_string;
            if (is_null($author)) {
                $author = do_lang('UNKNOWN');
            }
        }

        $_member_id = post_param('member_id', null);
        if ($_member_id == '') {
            $_member_id = null;
        }
        if (!is_null($_member_id)) {
            $member_id = is_numeric($_member_id) ? intval($_member_id) : $GLOBALS['FORUM_DRIVER']->get_member_from_username($_member_id);
        } else {
            $member_id = null;
        }

        if (post_param_integer('delete', 0) == 1) {
            if (!has_delete_author_permission(get_member(), $author)) {
                access_denied('PRIVILEGE', 'delete_midrange_content');
            }
            delete_author($author);
            $author = null;

            require_code('fields');
            if (has_tied_catalogue('author')) {
                delete_form_custom_fields('author', $author);
            }
        } else {
            $_url = post_param('url');
            if ((strpos($_url, '@') !== false) && (strpos($_url, 'mailto:') === false)) {
                $_url = 'mailto:' . $_url;
            }
            $url = (strpos($_url, 'mailto:') === false) ? fixup_protocolless_urls($_url) : $_url;

            $meta_data = actual_meta_data_get_fields('author', null);

            add_author($author, $url, $member_id, post_param('description'), post_param('skills'), post_param('meta_keywords', ''), post_param('meta_description', ''));

            require_code('fields');
            if (has_tied_catalogue('author')) {
                save_form_custom_fields('author', $author);
            }

            if (addon_installed('awards')) {
                require_code('awards');
                handle_award_setting('author', $author);
            }

            if (addon_installed('content_reviews')) {
                require_code('content_reviews2');
                content_review_set('author', $author);
            }
        }

        return $this->do_next_manager($this->title, do_lang_tempcode('SUCCESS'), $author);
    }

    /**
     * The do-next manager for after author content management.
     *
     * @param  tempcode                 The title (output of get_screen_title)
     * @param  tempcode                 Some description to show, saying what happened
     * @param  ?SHORT_TEXT              The author we were working with (null: not working with one)
     * @return tempcode                 The UI
     */
    public function do_next_manager($title, $description, $author = null)
    {
        require_code('templates_donext');
        return do_next_manager($title, $description,
            null,
            null,
            /* TYPED-ORDERED LIST OF 'LINKS'  */
            has_privilege(get_member(), 'edit_midrange_content', 'cms_authors') ? array('_SELF', array('type' => '_add', 'author' => ''), '_SELF') : null, // Add one
            is_null($author) ? null : array('_SELF', array('type' => '_add', 'author' => $author), '_SELF'), // Edit this
            has_privilege(get_member(), 'edit_midrange_content', 'cms_authors') ? array('_SELF', array('type' => 'edit'), '_SELF') : null, // Edit one
            is_null($author) ? null : array('authors', array('type' => 'misc', 'id' => $author), get_module_zone('authors')), // View this
            null, // View archive
            null, // Add to category
            null, // Add one category
            null, // Edit one category
            null, // Edit this category
            null, // View this category
            /* SPECIALLY TYPED 'LINKS' */
            array(
                has_privilege(get_member(), 'delete_midrange_content', 'cms_authors') ? array('menu/_generic_admin/merge', array('_SELF', array('type' => 'edit'), '_SELF'), do_lang('MERGE_AUTHORS')) : null
            )
        );
    }

    /**
     * The UI to edit an author (effectively deleting and re-adding them).
     *
     * @return tempcode                 The UI
     */
    public function edit()
    {
        $authors = $this->create_selection_list_authors();
        if ($authors->is_empty()) {
            inform_exit(do_lang_tempcode('NO_ENTRIES'));
        }

        require_code('form_templates');
        $fields = form_input_list(do_lang_tempcode('NAME'), '', 'author', $authors, null, true);
        $post_url = build_url(array('page' => '_SELF', 'type' => '_add'), '_SELF');
        $submit_name = do_lang_tempcode('SETUP');
        $define_form = do_template('FORM', array('_GUID' => '1109c0cfdd598bf87134de1838709c39', 'TABINDEX' => strval(get_form_field_tabindex()), 'HIDDEN' => '', 'TEXT' => '', 'FIELDS' => $fields, 'GET' => true, 'URL' => $post_url, 'SUBMIT_ICON' => 'menu___generic_admin__edit_this', 'SUBMIT_NAME' => $submit_name));

        if (has_privilege(get_member(), 'delete_midrange_content')) {
            $fields = new Tempcode();
            $fields->attach(form_input_list(do_lang_tempcode('PARAMETER_A'), '', 'mauthor', $authors));
            $fields->attach(form_input_list(do_lang_tempcode('PARAMETER_B'), do_lang_tempcode('DESCRIPTION_NAME'), 'mauthor2', $authors));
            $post_url = build_url(array('page' => '_SELF', 'type' => '_merge'), '_SELF');
            $submit_name = do_lang_tempcode('MERGE_AUTHORS');
            $merge_form = do_template('FORM', array('_GUID' => 'd0dd075a54b72cfe47d3c2d9fe987c89', 'TABINDEX' => strval(get_form_field_tabindex()), 'SECONDARY_FORM' => true, 'HIDDEN' => '', 'TEXT' => '', 'FIELDS' => $fields, 'URL' => $post_url, 'SUBMIT_ICON' => 'menu___generic_admin__merge', 'SUBMIT_NAME' => $submit_name));
        } else {
            $merge_form = new Tempcode();
        }

        return do_template('AUTHOR_MANAGE_SCREEN', array('_GUID' => '84f8de5d53090d138cb653bb861f2f70', 'TITLE' => $this->title, 'MERGE_FORM' => $merge_form, 'DEFINE_FORM' => $define_form));
    }

    /**
     * The actualiser to merge two authors.
     *
     * @return tempcode                 The UI
     */
    public function _merge()
    {
        check_privilege('delete_midrange_content');

        $from = post_param('mauthor');
        $to = post_param('mauthor2');

        merge_authors($from, $to);

        return $this->do_next_manager($this->title, do_lang_tempcode('SUCCESS'));
    }

    /**
     * Get a list of authors.
     *
     * @param  ?ID_TEXT                 The author to select by default (null: no specific default)
     * @return tempcode                 The list
     */
    public function create_selection_list_authors($it = null)
    {
        $author_fields = $GLOBALS['SITE_DB']->query('SELECT m_name,m_table FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'db_meta WHERE m_name LIKE \'' . db_encode_like('%author') . '\'');
        $authors = array();
        foreach ($author_fields as $field) {
            if (($field['m_table'] != 'modules') && ($field['m_table'] != 'blocks') && ($field['m_table'] != 'addons')) {
                $rows_new = $GLOBALS['SITE_DB']->query('SELECT DISTINCT ' . $field['m_name'] . ' FROM ' . $GLOBALS['SITE_DB']->get_table_prefix() . $field['m_table'] . ' WHERE ' . db_string_not_equal_to($field['m_name'], '') . ' ORDER BY ' . $field['m_name']);
                foreach ($rows_new as $row) {
                    $authors[] = $row[$field['m_name']];
                }
            }
        }
        $authors = array_unique($authors);
        sort($authors);
        $out = new Tempcode();
        foreach ($authors as $author) {
            $selected = ($author == $it);
            $out->attach(form_input_list_entry($author, $selected, $author));
        }

        return $out;
    }
}
