<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2015

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    news
 */

/**
 * Block class.
 */
class Block_bottom_news
{
    /**
     * Find details of the block.
     *
     * @return ?array                   Map of block info (null: block is disabled).
     */
    public function info()
    {
        $info = array();
        $info['author'] = 'Chris Graham';
        $info['organisation'] = 'ocProducts';
        $info['hacked_by'] = null;
        $info['hack_version'] = null;
        $info['version'] = 2;
        $info['locked'] = false;
        $info['parameters'] = array('param', 'filter', 'filter_and', 'zone', 'blogs', 'as_guest');
        return $info;
    }

    /**
     * Find cacheing details for the block.
     *
     * @return ?array                   Map of cache details (cache_on and ttl) (null: block is disabled).
     */
    public function cacheing_environment()
    {
        $info = array();
        $info['cache_on'] = 'array(array_key_exists(\'as_guest\',$map)?($map[\'as_guest\']==\'1\'):false,array_key_exists(\'zone\',$map)?$map[\'zone\']:get_module_zone(\'news\'),array_key_exists(\'filter\',$map)?$map[\'filter\']:get_param(\'news_filter\',\'\'),array_key_exists(\'param\',$map)?intval($map[\'param\']):5,array_key_exists(\'blogs\',$map)?$map[\'blogs\']:\'-1\',array_key_exists(\'filter_and\',$map)?$map[\'filter_and\']:\'\')';
        $info['special_cache_flags'] = CACHE_AGAINST_DEFAULT | CACHE_AGAINST_PERMISSIVE_GROUPS;
        if (addon_installed('content_privacy')) {
            $info['special_cache_flags'] |= CACHE_AGAINST_MEMBER;
        }
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 15;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array                    $map A map of parameters.
     * @return tempcode                 The result of execution.
     */
    public function run($map)
    {
        $max = array_key_exists('param', $map) ? intval($map['param']) : 5;
        $zone = array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('news');
        $blogs = array_key_exists('blogs', $map) ? intval($map['blogs']) : -1;
        $filter_and = array_key_exists('filter_and', $map) ? $map['filter_and'] : '';
        require_lang('news');

        $content = new Tempcode();

        // News Query
        require_code('ocfiltering');
        $filter = array_key_exists('filter', $map) ? $map['filter'] : get_param('news_filter', '*');
        $filters_1 = ocfilter_to_sqlfragment($filter, 'p.news_category', 'news_categories', null, 'p.news_category', 'id'); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
        $filters_2 = ocfilter_to_sqlfragment($filter, 'd.news_entry_category', 'news_categories', null, 'd.news_category', 'id'); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
        $q_filter = '(' . $filters_1 . ' OR ' . $filters_2 . ')';
        if ($blogs === 0) {
            if ($q_filter != '') {
                $q_filter .= ' AND ';
            }
            $q_filter .= 'nc_owner IS NULL';
        } elseif ($blogs === 1) {
            if ($q_filter != '') {
                $q_filter .= ' AND ';
            }
            $q_filter .= '(nc_owner IS NOT NULL)';
        }
        if ($blogs != -1) {
            $join = ' LEFT JOIN ' . $GLOBALS['SITE_DB']->get_table_prefix() . 'news_categories c ON c.id=p.news_category';
        } else {
            $join = '';
        }

        if ($filter_and != '') {
            $filters_and_1 = ocfilter_to_sqlfragment($filter_and, 'p.news_category', 'news_categories', null, 'p.news_category', 'id'); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
            $filters_and_2 = ocfilter_to_sqlfragment($filter_and, 'd.news_entry_category', 'news_categories', null, 'd.news_category', 'id'); // Note that the parameters are fiddled here so that category-set and record-set are the same, yet SQL is returned to deal in an entirely different record-set (entries' record-set)
            $q_filter .= ' AND (' . $filters_and_1 . ' OR ' . $filters_and_2 . ')';
        }

        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            $as_guest = array_key_exists('as_guest', $map) ? ($map['as_guest'] == '1') : false;
            $viewing_member_id = $as_guest ? $GLOBALS['FORUM_DRIVER']->get_guest_id() : mixed();
            list($privacy_join, $privacy_where) = get_privacy_where_clause('news', 'p', $viewing_member_id);
            $join .= $privacy_join;
            $q_filter .= $privacy_where;
        }

        $news = $GLOBALS['SITE_DB']->query('SELECT p.* FROM ' . get_table_prefix() . 'news p LEFT JOIN ' . get_table_prefix() . 'news_category_entries d ON d.news_entry=p.id' . $join . ' WHERE ' . $q_filter . ' AND validated=1 ORDER BY date_and_time DESC', $max, null, false, true);

        $_postdetailss = array();

        foreach ($news as $item) {
            if (has_category_access(get_member(), 'news', strval($item['news_category']))) {
                $url_map = array('page' => 'news', 'type' => 'view', 'id' => $item['id']);
                if ($filter != '*') {
                    $url_map['filter'] = $filter;
                }
                if (($filter_and != '*') && ($filter_and != '')) {
                    $url_map['filter_and'] = $filter_and;
                }
                if ($blogs === 1) {
                    $url_map['blog'] = 1;
                }
                $full_url = build_url($url_map, $zone);

                $just_news_row = db_map_restrict($item, array('id', 'title', 'news', 'news_article'));
                $_title = get_translated_tempcode('news', $just_news_row, 'title');
                $date = get_timezoned_date($item['date_and_time'], false);

                $_postdetailss[] = array('DATE' => $date, 'FULL_URL' => $full_url, 'NEWS_TITLE' => $_title);
            }
        }

        return do_template('BLOCK_BOTTOM_NEWS', array('_GUID' => 'a2076520b171bdf36e5369f0541f92c5', 'BLOG' => $blogs === 1, 'POSTS' => $_postdetailss));
    }
}
