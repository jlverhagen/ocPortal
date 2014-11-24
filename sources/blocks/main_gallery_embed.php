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
 * @package    galleries
 */

/**
 * Block class.
 */
class Block_main_gallery_embed
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
        $info['parameters'] = array('ocselect', 'param', 'select', 'video_select', 'zone', 'title', 'sort', 'days', 'render_if_empty', 'max', 'start', 'pagination', 'root', 'guid', 'as_guest');
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
        $info['cache_on'] = '((addon_installed(\'content_privacy\')) && (!(array_key_exists(\'as_guest\',$map)?($map[\'as_guest\']==\'1\'):false)))?null:(preg_match(\'#<\w+>#\',(array_key_exists(\'ocselect\',$map)?$map[\'ocselect\']:\'\'))!=0)?null:array(array_key_exists(\'as_guest\',$map)?($map[\'as_guest\']==\'1\'):false,array_key_exists(\'guid\',$map)?$map[\'guid\']:\'\',$GLOBALS[\'FORUM_DRIVER\']->get_members_groups(get_member(),false,true),get_param_integer($block_id.\'_max\',array_key_exists(\'max\',$map)?intval($map[\'max\']):NULL),get_param_integer($block_id.\'_start\',array_key_exists(\'start\',$map)?intval($map[\'start\']):0),((array_key_exists(\'pagination\',$map)?$map[\'pagination\']:\'0\')==\'1\'),((array_key_exists(\'root\',$map)) && ($map[\'root\']!=\'\'))?$map[\'root\']:get_param(\'keep_gallery_root\',NULL),array_key_exists(\'ocselect\',$map)?$map[\'ocselect\']:\'\',array_key_exists(\'render_if_empty\',$map)?$map[\'render_if_empty\']:\'0\',array_key_exists(\'days\',$map)?$map[\'days\']:\'\',array_key_exists(\'sort\',$map)?$map[\'sort\']:\'add_date DESC\',get_param_integer(\'mge_start\',0),array_key_exists(\'param\',$map)?$map[\'param\']:db_get_first_id(),array_key_exists(\'zone\',$map)?$map[\'zone\']:\'\',((is_null($map)) || (!array_key_exists(\'select\',$map)))?\'*\':$map[\'select\'],((is_null($map)) || (!array_key_exists(\'video_select\',$map)))?\'*\':$map[\'video_select\'],array_key_exists(\'title\',$map)?$map[\'title\']:\'\')';
        $info['ttl'] = (get_value('no_block_timeout') === '1') ? 60 * 60 * 24 * 365 * 5/*5 year timeout*/ : 60 * 2;
        return $info;
    }

    /**
     * Execute the block.
     *
     * @param  array                    A map of parameters.
     * @return tempcode                 The result of execution.
     */
    public function run($map)
    {
        require_css('galleries');
        require_lang('galleries');
        require_code('galleries');
        require_code('images');
        require_code('feedback');
        require_javascript('galleries');

        $block_id = get_block_id($map);

        $max = get_param_integer($block_id . '_max', array_key_exists('max', $map) ? intval($map['max']) : get_default_gallery_max());
        $start = get_param_integer($block_id . '_start', array_key_exists('start', $map) ? intval($map['start']) : 0);
        $do_pagination = ((array_key_exists('pagination', $map) ? $map['pagination'] : '0') == '1');
        $root = ((array_key_exists('root', $map)) && ($map['root'] != '')) ? $map['root'] : get_param('keep_gallery_root', null);
        $guid = array_key_exists('guid', $map) ? $map['guid'] : '';

        require_code('ocfiltering');
        $cat = array_key_exists('param', $map) ? $map['param'] : 'root';
        $cat_select = ocfilter_to_sqlfragment($cat, 'cat', 'galleries', 'parent_id', 'cat', 'name', false, false);

        $title = array_key_exists('title', $map) ? $map['title'] : '';
        $zone = array_key_exists('zone', $map) ? $map['zone'] : get_module_zone('galleries');

        $where_sup = '';
        if ((!has_privilege(get_member(), 'see_unvalidated')) && (addon_installed('unvalidated'))) {
            $where_sup .= ' AND r.validated=1';
        }

        // ocFilter
        if (!array_key_exists('select', $map)) {
            $map['select'] = '*';
        }
        $image_select = ocfilter_to_sqlfragment($map['select'], 'id');
        if (!array_key_exists('video_select', $map)) {
            $map['video_select'] = '*';
        }
        $video_select = ocfilter_to_sqlfragment($map['video_select'], 'id');

        // Day filtering
        $_days = array_key_exists('days', $map) ? $map['days'] : '';
        $days = mixed();
        $days = ($_days == '') ? null : intval($_days);
        if (!is_null($days)) {
            $where_sup .= ' AND add_date>=' . strval(time() - $days * 60 * 60 * 24);
        }

        // Sorting
        $sort = array_key_exists('sort', $map) ? $map['sort'] : 'add_date DESC';
        if (($sort != 'fixed_random ASC') && ($sort != 'average_rating DESC') && ($sort != 'average_rating ASC') && ($sort != 'compound_rating DESC') && ($sort != 'compound_rating ASC') && ($sort != 'add_date DESC') && ($sort != 'add_date ASC') && ($sort != 'edit_date DESC') && ($sort != 'edit_date ASC') && ($sort != 'url DESC') && ($sort != 'url ASC')) {
            $sort = 'add_date DESC';
        }
        list($_sort, $_dir) = explode(' ', $sort, 2);

        // ocSelect support
        $extra_select_sql = '';
        $extra_join_sql = '';
        $ocselect = array_key_exists('ocselect', $map) ? $map['ocselect'] : '';
        if ($ocselect != '') {
            // Convert the filters to SQL
            require_code('ocselect');
            $content_type = 'image';
            if ($map['video_select'] != '') {
                list($extra_select, $extra_join, $extra_where) = ocselect_to_sql($GLOBALS['SITE_DB'], parse_ocselect($ocselect), 'video', '');
                $extra_select_sql .= implode('', $extra_select);
                $extra_join_sql .= implode('', $extra_join);
                $where_sup .= $extra_where;
            } elseif ($map['select'] != '') {
                list($extra_select, $extra_join, $extra_where) = ocselect_to_sql($GLOBALS['SITE_DB'], parse_ocselect($ocselect), 'image', '');
                $extra_select_sql .= implode('', $extra_select);
                $extra_join_sql .= implode('', $extra_join);
                $where_sup .= $extra_where;
            }
        }

        $extra_join_image = '';
        $extra_join_video = '';
        $extra_where_image = '';
        $extra_where_video = '';

        if (addon_installed('content_privacy')) {
            require_code('content_privacy');
            $as_guest = array_key_exists('as_guest', $map) ? ($map['as_guest'] == '1') : false;
            $viewing_member_id = $as_guest ? $GLOBALS['FORUM_DRIVER']->get_guest_id() : mixed();
            list($privacy_join_image, $privacy_where_image) = get_privacy_where_clause('image', 'r', $viewing_member_id);
            list($privacy_join_video, $privacy_where_video) = get_privacy_where_clause('video', 'r', $viewing_member_id);
            $extra_join_image .= $privacy_join_image;
            $extra_join_video .= $privacy_join_video;
            $extra_where_image .= $privacy_where_image;
            $extra_where_video .= $privacy_where_video;
        }

        // Get rows
        if ($do_pagination) {
            $total_images = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*)' . $extra_select_sql . ' FROM ' . get_table_prefix() . 'images r' . $extra_join_sql . $extra_join_image . ' WHERE ' . $cat_select . ' AND ' . $image_select . $where_sup . $extra_where_image, false, true);
            $total_videos = $GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(*)' . $extra_select_sql . ' FROM ' . get_table_prefix() . 'videos r' . $extra_join_sql . $extra_join_video . ' WHERE ' . $cat_select . ' AND ' . $video_select . $where_sup . $extra_where_video, false, true);
        } else {
            $total_images = 0;
            $total_videos = 0;
        }
        if ($_sort == 'average_rating') {
            $rating_sort = ',(SELECT AVG(rating) FROM ' . get_table_prefix() . 'rating WHERE ' . db_string_equal_to('rating_for_type', 'images') . ' AND rating_for_id=r.id) AS average_rating';
        } elseif ($_sort == 'compound_rating') {
            $rating_sort = ',(SELECT SUM(rating-1) FROM ' . get_table_prefix() . 'rating WHERE ' . db_string_equal_to('rating_for_type', 'images') . ' AND rating_for_id=r.id) AS compound_rating';
        } elseif ($_sort == 'fixed_random') {
            $rating_sort = ',(MOD(r.id,' . date('d') . ')) AS fixed_random';
        } else {
            $rating_sort = '';
        }
        $rows_images = $GLOBALS['SITE_DB']->query('SELECT *' . $rating_sort . $extra_select_sql . ' FROM ' . get_table_prefix() . 'images r' . $extra_join_sql . $extra_join_image . ' WHERE ' . $cat_select . ' AND ' . $image_select . $where_sup . $extra_where_image . ' ORDER BY ' . $sort, $max + $start, null, false, true);
        if ($_sort == 'average_rating') {
            $rating_sort = ',(SELECT AVG(rating) FROM ' . get_table_prefix() . 'rating WHERE ' . db_string_equal_to('rating_for_type', 'videos') . ' AND rating_for_id=r.id) AS average_rating';
        } elseif ($_sort == 'compound_rating') {
            $rating_sort = ',(SELECT SUM(rating-1) FROM ' . get_table_prefix() . 'rating WHERE ' . db_string_equal_to('rating_for_type', 'videos') . ' AND rating_for_id=r.id) AS compound_rating';
        } elseif ($_sort == 'fixed_random') {
            $rating_sort = ',(MOD(r.id,' . date('d') . ')) AS fixed_random';
        } else {
            $rating_sort = '';
        }
        $rows_videos = $GLOBALS['SITE_DB']->query('SELECT *' . $rating_sort . $extra_select_sql . ' FROM ' . get_table_prefix() . 'videos r' . $extra_join_sql . $extra_join_video . ' WHERE ' . $cat_select . ' AND ' . $video_select . $where_sup . $extra_where_video . ' ORDER BY ' . $sort, $max + $start, null, false, true);

        // Sort
        $combined = array();
        foreach ($rows_images as $row_image) {
            $combined[] = array($row_image, 'image', $row_image[$_sort]);
        }
        foreach ($rows_videos as $row_video) {
            $combined[] = array($row_video, 'video', $row_video[$_sort]);
        }
        sort_maps_by($combined, ($_dir == 'DESC') ? '!2' : '2');

        // Display
        $entries = new Tempcode();
        foreach ($combined as $i => $c) {
            if ($i >= $start) {
                $just_media_row = db_map_restrict($c[0], array('id', 'description'));

                switch ($c[1]) {
                    case 'image':
                        // Display image
                        $row_image = $c[0];
                        $entry_title = get_translated_text($row_image['title']);
                        $view_url = build_url(array('page' => 'galleries', 'type' => 'image', 'id' => $row_image['id'], 'root' => $root), $zone);
                        $thumb_url = ensure_thumbnail($row_image['url'], $row_image['thumb_url'], 'galleries', 'images', $row_image['id']);
                        $thumb = do_image_thumb($thumb_url, $entry_title, true);
                        $full_url = $row_image['url'];
                        $file_size = url_is_local($full_url) ? file_exists(get_custom_file_base() . '/' . rawurldecode($full_url)) ? strval(filesize(get_custom_file_base() . '/' . rawurldecode($full_url))) : '' : '';
                        if (url_is_local($full_url)) {
                            $full_url = get_custom_base_url() . '/' . $full_url;
                        }
                        $thumb_url = $row_image['thumb_url'];
                        if (url_is_local($thumb_url)) {
                            $thumb_url = get_custom_base_url() . '/' . $thumb_url;
                        }

                        $entry_rating_details = ($row_image['allow_rating'] == 1) ? display_rating($view_url, get_translated_text($row_image['title']), 'images', strval($row_image['id']), 'RATING_INLINE_STATIC', $row_image['submitter']) : null;

                        $_edit_url = new Tempcode();
                        if (has_delete_permission('mid', get_member(), $row_image['submitter'], 'cms_galleries', array('gallery', $row_image['cat']))) {
                            $_edit_url = build_url(array('page' => 'cms_galleries', 'type' => '__edit', 'id' => $row_image['id'], 'redirect' => get_self_url(true)), get_module_zone('cms_galleries'));
                            if ($row_image['submitter'] == get_member()) {
                                $GLOBALS['DO_NOT_CACHE_THIS'] = true; // If delete was due to groups, groups is a cache key anyways
                            }
                        }

                        $entry_map = array(
                            '_GUID' => ($guid != '') ? $guid : '043ac7d15ce02715ac02309f6e8340ff',
                            'RATING_DETAILS' => $entry_rating_details,
                            'TITLE' => $entry_title,
                            'DESCRIPTION' => get_translated_tempcode('images', $just_media_row, 'description'),
                            'ID' => strval($row_image['id']),
                            'MEDIA_TYPE' => 'image',
                            'FILE_SIZE' => $file_size,
                            'SUBMITTER' => strval($row_image['submitter']),
                            'FULL_URL' => $full_url,
                            'THUMB_URL' => $thumb_url,
                            'CAT' => $cat,
                            'THUMB' => $thumb,
                            'VIEW_URL' => $view_url,
                            'VIEWS' => strval($row_image['image_views']),
                            'ADD_DATE_RAW' => strval($row_image['add_date']),
                            'EDIT_DATE_RAW' => is_null($row_image['edit_date']) ? '' : strval($row_image['edit_date']),
                            'START' => strval($start),
                            '_EDIT_URL' => $_edit_url,
                        );
                        $entry = do_template('GALLERY_IMAGE', $entry_map);
                        $entries->attach(do_template('GALLERY_ENTRY_WRAP', array('_GUID' => ($guid != '') ? $guid : '13134830e1ebea158ab44885eeec0953', 'ENTRY' => $entry) + $entry_map));

                        break;

                    case 'video':
                        // Display video
                        $row_video = $c[0];
                        $entry_title = get_translated_text($row_video['title']);
                        $view_url = build_url(array('page' => 'galleries', 'type' => 'video', 'id' => $row_video['id'], 'root' => $root), $zone);
                        $thumb_url = $row_video['thumb_url'];
                        if (($thumb_url != '') && (url_is_local($thumb_url))) {
                            $thumb_url = get_custom_base_url() . '/' . $thumb_url;
                        }
                        if ($thumb_url == '') {
                            $thumb_url = find_theme_image('na');
                        }
                        $thumb = do_image_thumb($thumb_url, $entry_title, true);
                        $full_url = $row_video['url'];
                        if (url_is_local($full_url)) {
                            $full_url = get_custom_base_url() . '/' . $full_url;
                        }
                        $thumb_url = $row_video['thumb_url'];
                        if (($thumb_url != '') && (url_is_local($thumb_url))) {
                            $thumb_url = get_custom_base_url() . '/' . $thumb_url;
                        }

                        $entry_rating_details = ($row_video['allow_rating'] == 1) ? display_rating($view_url, get_translated_text($row_video['title']), 'videos', strval($row_video['id']), 'RATING_INLINE_STATIC', $row_video['submitter']) : null;

                        $_edit_url = new Tempcode();
                        if (has_delete_permission('mid', get_member(), $row_video['submitter'], 'cms_galleries', array('gallery', $row_video['cat']))) {
                            $_edit_url = build_url(array('page' => 'cms_galleries', 'type' => '__edit_other', 'id' => $row_video['id'], 'redirect' => get_self_url(true)), get_module_zone('cms_galleries'));
                            if ($row_video['submitter'] == get_member()) {
                                $GLOBALS['DO_NOT_CACHE_THIS'] = true; // If delete was due to groups, groups is a cache key anyways
                            }
                        }

                        $entry_map = array(
                            '_GUID' => ($guid != '') ? $guid : '66b7fb4d3b61ef79d6803c170d102cbf',
                            'RATING_DETAILS' => $entry_rating_details,
                            'TITLE' => $entry_title,
                            'DESCRIPTION' => get_translated_tempcode('videos', $just_media_row, 'description'),
                            'ID' => strval($row_video['id']),
                            'MEDIA_TYPE' => 'video',
                            'CAT' => $cat,
                            'THUMB' => $thumb,
                            'VIEW_URL' => $view_url,
                            'SUBMITTER' => strval($row_video['submitter']),
                            'FULL_URL' => $full_url,
                            'THUMB_URL' => $thumb_url,
                            'VIDEO_DETAILS' => show_video_details($row_video),
                            'VIEWS' => strval($row_video['video_views']),
                            'ADD_DATE_RAW' => strval($row_video['add_date']),
                            'EDIT_DATE_RAW' => is_null($row_video['edit_date']) ? '' : strval($row_video['edit_date']),
                            'START' => strval($start),
                            '_EDIT_URL' => $_edit_url,
                        );
                        $entry = do_template('GALLERY_VIDEO', $entry_map);
                        $entries->attach(do_template('GALLERY_ENTRY_WRAP', array('_GUID' => ($guid != '') ? $guid : 'a0ff010ae7fd1f7b3341993072ed23cf', 'ENTRY' => $entry) + $entry_map));

                        break;
                }
            }

            $i++;
            if ($i == $start + $max) {
                break;
            }
        }

        // Empty? Bomb out somehow
        if ($entries->is_empty()) {
            if ((!isset($map['render_if_empty'])) || ($map['render_if_empty'] == '0')) {
                if ((has_actual_page_access(null, 'cms_galleries', null, null)) && (has_submit_permission('mid', get_member(), get_ip_address(), 'cms_galleries', array('galleries', $cat))) && (can_submit_to_gallery($cat))) {
                    $submit_url = build_url(array('page' => 'cms_galleries', 'type' => 'add', 'cat' => $cat, 'redirect' => SELF_REDIRECT), get_module_zone('cms_galleries'));
                } else {
                    $submit_url = new Tempcode();
                }
                return do_template('BLOCK_NO_ENTRIES', array(
                    '_GUID' => ($guid != '') ? $guid : 'bf84d65b8dd134ba6cd7b1b7bde99de2',
                    'HIGH' => false,
                    'TITLE' => do_lang_tempcode('GALLERY'),
                    'MESSAGE' => do_lang_tempcode('NO_ENTRIES'),
                    'ADD_NAME' => do_lang_tempcode('ADD_IMAGE'),
                    'SUBMIT_URL' => $submit_url,
                ));
            }
        }

        // Pagination
        $pagination = new Tempcode();
        if ($do_pagination) {
            require_code('templates_pagination');
            $_selectors = array_map('intval', explode(',', get_option('gallery_selectors')));
            $pagination = pagination(do_lang('MEDIA'), $start, $block_id . '_start', $max, $block_id . '_max', $total_videos + $total_images, false, 5, $_selectors);
        }

        // Render
        return do_template('BLOCK_MAIN_GALLERY_EMBED', array(
            '_GUID' => ($guid != '') ? $guid : 'b7b969c8fe8c398dd6e3af7ee06717ea',
            'IMAGE_SELECT' => $map['select'],
            'VIDEO_SELECT' => $map['video_select'],
            'DAYS' => $_days,
            'SORT' => $sort,
            'BLOCK_PARAMS' => block_params_arr_to_str($map),
            'PAGINATION' => $pagination,
            'TITLE' => $title,
            'CAT' => $cat,
            'ENTRIES' => $entries,
            'TOTAL_VIDEOS' => strval($total_videos),
            'TOTAL_IMAGES' => strval($total_images),
            'TOTAL' => strval($total_videos + $total_images),

            'START' => strval($start),
            'MAX' => strval($max),
            'START_PARAM' => $block_id . '_start',
            'MAX_PARAM' => $block_id . '_max',
        ));
    }
}
