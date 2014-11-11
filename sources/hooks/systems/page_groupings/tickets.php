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
 * @package    tickets
 */
class Hook_page_groupings_tickets
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
        if (!addon_installed('tickets')) {
            return array();
        }

        return array(
            array('setup', 'menu/site_meta/tickets', array('admin_tickets', array('type' => 'misc'), get_module_zone('admin_tickets')), do_lang_tempcode('tickets:SUPPORT_TICKETS'), 'tickets:DOC_TICKETS'),
            array('site_meta', 'menu/site_meta/tickets', array('tickets', array(), get_module_zone('tickets')), do_lang_tempcode('tickets:SUPPORT_TICKETS')),
        );
    }
}
