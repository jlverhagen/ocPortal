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
 */

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    core_rich_media
 */
class Hook_comcode_link_handler_media_rendering
{
    /**
     * Bind function for Comcode link handler hooks. They see if they can bind a pasted URL to a lump of handler Tempcode.
     *
     * @param  URLPATH                  Link to use or reject
     * @param  boolean                  Whether we are allowed to proceed even if this tag is marked as 'dangerous'
     * @param  string                   A special identifier to mark where the resultant tempcode is going to end up (e.g. the ID of a post)
     * @param  integer                  The position this tag occurred at in the Comcode
     * @param  MEMBER                   The member who is responsible for this Comcode
     * @param  boolean                  Whether to check as arbitrary admin
     * @param  object                   The database connection to use
     * @param  string                   The whole chunk of Comcode
     * @param  boolean                  Whether this is only a structure sweep
     * @param  boolean                  Whether we are in semi-parse-mode (some tags might convert differently)
     * @param  ?array                   A list of words to highlight (NULL: none)
     * @return ?tempcode                Handled link (NULL: reject due to inappropriate link pattern)
     */
    public function bind($url, $comcode_dangerous, $pass_id, $pos, $source_member, $as_admin, $connection, $comcode, $structure_sweep, $semiparse_mode, $highlight_bits)
    {
        require_code('media_renderer');
        return render_media_url($url, $url, array('context' => 'comcode_link'), $as_admin, $source_member);
    }
}
