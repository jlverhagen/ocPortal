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
 * @package    recommend
 */
class Hook_realtime_rain_recommend
{
    /**
     * Run function for realtime-rain hooks.
     *
     * @param  TIME                     Start of time range.
     * @param  TIME                     End of time range.
     * @return array                    A list of template parameter sets for rendering a 'drop'.
     */
    public function run($from, $to)
    {
        $drops = array();

        if ((has_actual_page_access(get_member(), 'admin_ocf_members')) && (get_forum_type() == 'ocf')) {
            $rows = $GLOBALS['FORUM_DB']->query('SELECT i_email_address,i_inviter AS member_id,i_time AS timestamp FROM ' . $GLOBALS['FORUM_DB']->get_table_prefix() . 'f_invites WHERE i_time BETWEEN ' . strval($from) . ' AND ' . strval($to));

            foreach ($rows as $row) {
                $timestamp = $row['timestamp'];
                $member_id = $row['member_id'];

                $invited_member = $GLOBALS['FORUM_DB']->query_select_value_if_there('f_members', 'id', array('m_email_address' => $row['i_email_address']));

                $drops[] = rain_get_special_icons(null, $timestamp) + array(
                        'TYPE' => 'recommend',
                        'FROM_MEMBER_ID' => strval($member_id),
                        'TO_MEMBER_ID' => is_null($invited_member) ? '' : strval($invited_member),
                        'TITLE' => do_lang('RECOMMEND_SITE'),
                        'IMAGE' => is_guest($member_id) ? find_theme_image('recommend') : $GLOBALS['FORUM_DRIVER']->get_member_avatar_url($member_id),
                        'TIMESTAMP' => strval($timestamp),
                        'RELATIVE_TIMESTAMP' => strval($timestamp - $from),
                        'TICKER_TEXT' => null,
                        'URL' => build_url(array('page' => 'points', 'type' => 'member', 'id' => $member_id), '_SEARCH'),
                        'IS_POSITIVE' => true,
                        'IS_NEGATIVE' => false,

                        // These are for showing connections between drops. They are not discriminated, it's just three slots to give an ID code that may be seen as a commonality with other drops.
                        'FROM_ID' => 'member_' . strval($member_id),
                        'TO_ID' => is_null($invited_member) ? '' : ('member_' . strval($invited_member)),
                        'GROUP_ID' => null,
                    );
            }
        }

        return $drops;
    }
}
