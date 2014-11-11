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
 * @package    chat
 */
class Hook_content_meta_aware_chat
{
    /**
     * Get content type details. Provides information to allow task reporting, randomisation, and add-screen linking, to function.
     *
     * @param  ?ID_TEXT                 The zone to link through to (NULL: autodetect).
     * @return ?array                   Map of award content-type info (NULL: disabled).
     */
    public function info($zone = null)
    {
        return array(
            'supports_custom_fields' => false,

            'content_type_label' => 'chat:CHATROOM',

            'connection' => $GLOBALS['SITE_DB'],
            'table' => 'chat_rooms',
            'id_field' => 'id',
            'id_field_numeric' => true,
            'parent_category_field' => null,
            'parent_category_meta_aware_type' => null,
            'is_category' => true,
            'is_entry' => false,
            'category_field' => null, // For category permissions
            'category_type' => null, // For category permissions
            'parent_spec__table_name' => null,
            'parent_spec__parent_name' => null,
            'parent_spec__field_name' => null,
            'category_is_string' => false,

            'title_field' => 'room_name',
            'title_field_dereference' => false,
            'description_field' => null,
            'thumb_field' => null,

            'view_page_link_pattern' => '_SEARCH:chat:room:_WILD',
            'edit_page_link_pattern' => '_SEARCH:cms_chat:room:_WILD',
            'view_category_page_link_pattern' => '_SEARCH:chat:room:_WILD',
            'add_url' => (function_exists('has_submit_permission') && has_submit_permission('mid', get_member(), get_ip_address(), 'cms_chat')) ? (get_module_zone('cms_chat') . ':cms_chat:ac') : null,
            'archive_url' => ((!is_null($zone)) ? $zone : get_module_zone('chat')) . ':chat',

            'support_url_monikers' => true,

            'views_field' => null,
            'submitter_field' => null,
            'add_time_field' => null,
            'edit_time_field' => null,
            'date_field' => null,
            'validated_field' => null,

            'seo_type_code' => null,

            'feedback_type_code' => null,

            'permissions_type_code' => null, // NULL if has no permissions

            'search_hook' => null,

            'addon_name' => 'chat',

            'cms_page' => 'cms_chat',
            'module' => 'chat',

            'occle_filesystem_hook' => 'chat',
            'occle_filesystem__is_folder' => false,

            'rss_hook' => null,

            'actionlog_regexp' => '\w+_CHAT',
        );
    }

    /**
     * Run function for content hooks. Renders a content box for an award/randomisation.
     *
     * @param  array                    The database row for the content
     * @param  ID_TEXT                  The zone to display in
     * @param  boolean                  Whether to include context (i.e. say WHAT this is, not just show the actual content)
     * @param  boolean                  Whether to include breadcrumbs (if there are any)
     * @param  ?ID_TEXT                 Virtual root to use (NULL: none)
     * @param  boolean                  Whether to copy through any filter parameters in the URL, under the basis that they are associated with what this box is browsing
     * @param  ID_TEXT                  Overridden GUID to send to templates (blank: none)
     * @return tempcode                 Results
     */
    public function run($row, $zone, $give_context = true, $include_breadcrumbs = true, $root = null, $attach_to_url_filter = false, $guid = '')
    {
        require_code('chat');

        return render_chat_box($row, $zone, $give_context, $guid);
    }
}
