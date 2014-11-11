<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    developer_sync
 */
class Hook_page_groupings_developer_sync
{
    /**
     * Run function for do_next_menu hooks. They find links to put on standard navigation menus of the system.
     *
     * @param  ?MEMBER                  Member ID to run as (NULL: current member)
     * @param  boolean                  Whether to use extensive documentation tooltips, rather than short summaries
     * @return array                    List of tuple of links (page grouping, icon, do-next-style linking data), label, help (optional) and/or nulls
     */
    public function run($member_id = null, $extensive_docs = false)
    {
        return array(
            array('tools', 'menu/_generic_admin/tool', array('sql_dump', array(), get_page_zone('sql_dump')), make_string_tempcode('Backup tools: Create SQL dump (MySQL syntax)')),
            array('tools', 'menu/_generic_admin/tool', array('tar_dump', array(), get_page_zone('tar_dump')), make_string_tempcode('Backup tools: Create files dump (TAR file)')),
        );
    }
}
