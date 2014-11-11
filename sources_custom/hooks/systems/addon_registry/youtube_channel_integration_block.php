<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    youtube_channel_integration_block
 */
class Hook_addon_registry_youtube_channel_integration_block
{
    /**
     * Get a list of file permissions to set
     *
     * @return array                    File permissions to set
     */
    public function get_chmod_array()
    {
        return array();
    }

    /**
     * Get the version of ocPortal this addon is for
     *
     * @return float                    Version number
     */
    public function get_version()
    {
        return ocp_version_number();
    }

    /**
     * Get the addon category
     *
     * @return string                   The category
     */
    public function get_category()
    {
        return 'Third Party Integration';
    }

    /**
     * Get the addon author
     *
     * @return string                   The author
     */
    public function get_author()
    {
        return 'Jason Verhagen';
    }

    /**
     * Find other authors
     *
     * @return array                    A list of co-authors that should be attributed
     */
    public function get_copyright_attribution()
    {
        return array();
    }

    /**
     * Get the addon licence (one-line summary only)
     *
     * @return string                   The licence
     */
    public function get_licence()
    {
        return 'Creative Commons Attribution 3.0 Unported License (CC BY 3.0)';
    }

    /**
     * Get the description of the addon
     *
     * @return string                   Description of the addon
     */
    public function get_description()
    {
        return 'Integrate YouTube channels into your web site. Specify a YouTube channel or user name and some other parameters and you can integrate videos and video info in your web site. The block can automatically update with new content as it is added to the YouTube channel. This addon was developed under ocPortal v8.1.3, but should work for all 8.x versions and has been confirmed to work with the v9 betas as well. You can get detailed instructions on template usage in the YouTube Channel Integration discussion topic here: http://ocportal.com/forum/topicview/misc/addons/youtube-channel_2.htm';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array();
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array                    File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(
                'PHP JSON Extension',
            ),
            'recommends' => array(),
            'conflicts_with' => array()
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH                  Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/_generic_admin/component.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'sources_custom/hooks/systems/addon_registry/youtube_channel_integration_block.php',
            'lang_custom/EN/youtube_channel.ini',
            'sources_custom/blocks/youtube_channel.php',
            'themes/default/templates_custom/BLOCK_YOUTUBE_CHANNEL.tpl',
            'themes/default/templates_custom/BLOCK_YOUTUBE_CHANNEL_STYLE.tpl',
        );
    }
}
