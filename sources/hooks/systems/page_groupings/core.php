<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core
 */
class Hook_page_groupings_core
{

	/**
	 * Standard modular run function for do_next_menu hooks. They find links to put on standard navigation menus of the system.
	 *
	 * @param  ?MEMBER		Member ID to run as (NULL: current member)
	 * @return array			List of tuple of links (page grouping, icon, do-next-style linking data), label, help (optional) and/or nulls
	 */
	function run($member_id=NULL)
	{
		require_code('site');

		return array(
			array('','menu/adminzone/structure',array('admin',array('type'=>'structure'),get_module_zone('admin')),do_lang_tempcode('menus:STRUCTURE'),'menus:DOC_STRUCTURE'),
			array('','menu/adminzone/audit',array('admin',array('type'=>'audit'),get_module_zone('admin')),do_lang_tempcode('menus:AUDIT'),'menus:DOC_AUDIT'),
			array('','menu/adminzone/style',array('admin',array('type'=>'style'),get_module_zone('admin')),do_lang_tempcode('menus:STYLE'),'menus:DOC_STYLE'),
			array('','menu/adminzone/setup',array('admin',array('type'=>'setup'),get_module_zone('admin')),do_lang_tempcode('menus:SETUP'),'menus:DOC_SETUP'),
			array('','menu/adminzone/tools',array('admin',array('type'=>'tools'),get_module_zone('admin')),do_lang_tempcode('menus:TOOLS'),'menus:DOC_TOOLS'),
			array('','menu/adminzone/security',array('admin',array('type'=>'security'),get_module_zone('admin')),do_lang_tempcode('menus:SECURITY_GROUP_SETUP'),'menus:DOC_SECURITY'),
			array('','menu/adminzone/start',array('start',array(),'adminzone'),do_lang_tempcode('menus:DASHBOARD')),
			(_request_page('website','adminzone')===NULL)?array('','menu/adminzone/help',array('admin',array('type'=>'docs'),get_module_zone('admin')),do_lang_tempcode('menus:DOCS')):NULL,
			(_request_page('website','adminzone')===NULL)?NULL:array('','menu/adminzone/help',array('website',array(),'adminzone'),do_lang_tempcode('menus:DOCS')),
			array('','menu/cms/cms',array('cms',array('type'=>'cms'),get_module_zone('cms')),do_lang_tempcode('CMS'),'menus:DOC_CMS'),

			((has_some_edit_comcode_page_permission(COMCODE_EDIT_OWN | COMCODE_EDIT_ANY)) || (get_comcode_page_editability_per_zone()!=array()))?array('cms','menu/cms/comcode_page_edit',array('cms_comcode_pages',array('type'=>'misc'),get_module_zone('cms_comcode_pages')),do_lang_tempcode('ITEMS_HERE',do_lang_tempcode('menus:_COMCODE_PAGES'),make_string_tempcode(escape_html(integer_format($GLOBALS['SITE_DB']->query_value_if_there('SELECT COUNT(DISTINCT the_zone,the_page) FROM '.get_table_prefix().'comcode_pages WHERE '.db_string_not_equal_to('the_zone','!')))))),'zones:DOC_COMCODE_PAGE_EDIT'):NULL,

			array('structure','menu/adminzone/structure/zones/zones',array('admin_zones',array('type'=>'misc'),get_module_zone('admin_zones')),do_lang_tempcode('ZONES'),'zones:DOC_ZONES'),
			array('structure','menu/adminzone/structure/zones/zone_editor',array('admin_zones',array('type'=>'editor'),get_module_zone('admin_zones')),do_lang_tempcode('zones:ZONE_EDITOR'),'zones:DOC_ZONE_EDITOR'),
			array('structure','menu/adminzone/structure/menus',array('admin_menus',array('type'=>'misc'),get_module_zone('admin_menus')),do_lang_tempcode('menus:MENU_MANAGEMENT'),'menus:DOC_MENUS'),
			addon_installed('page_management')?array('structure','menu/adminzone/structure/sitemap/sitemap_editor',array('admin_sitemap',array('type'=>has_js()?'sitemap':'misc'),get_module_zone('admin_sitemap')),do_lang_tempcode(has_js()?'zones:SITEMAP_EDITOR':'zones:SITEMAP_TOOLS'),'zones:DOC_SITEMAP_EDITOR'):NULL,
			addon_installed('redirects_editor')?array('structure','menu/adminzone/structure/redirects',array('admin_redirects',array('type'=>'misc'),get_module_zone('admin_redirects')),do_lang_tempcode('redirects:REDIRECTS'),'redirects:DOC_REDIRECTS'):NULL,
			addon_installed('breadcrumbs')?array('structure','menu/adminzone/structure/breadcrumbs',array('admin_config',array('type'=>'xml_breadcrumbs'),get_module_zone('admin_config')),do_lang_tempcode('config:BREADCRUMB_OVERRIDES'),'config:DOC_BREADCRUMB_OVERRIDES'):NULL,
			array('structure','menu/adminzone/structure/addons',array('admin_addons',array('type'=>'misc'),get_module_zone('admin_addons')),do_lang_tempcode('addons:ADDONS'),'addons:DOC_ADDONS'),

			(get_forum_type()!='ocf' || !addon_installed('ocf_cpfs'))?NULL:array('audit','menu/adminzone/tools/users/custom_profile_fields',array('admin_ocf_customprofilefields',array('type'=>'stats'),get_module_zone('admin_ocf_customprofilefields')),do_lang_tempcode('ocf:CUSTOM_PROFILE_FIELD_STATS'),'ocf:DOC_CUSTOM_PROFILE_FIELDS_STATS'),
			addon_installed('errorlog')?array('audit','menu/adminzone/audit/errorlog',array('admin_errorlog',array(),get_module_zone('admin_errorlog')),do_lang_tempcode('errorlog:ERROR_LOG'),'errorlog:DOC_ERROR_LOG'):NULL,
			addon_installed('actionlog')?array('audit','menu/adminzone/audit/actionlog',array('admin_actionlog',array('type'=>'misc'),get_module_zone('admin_actionlog')),do_lang_tempcode('actionlog:VIEW_ACTIONLOGS'),'actionlog:DOC_ACTIONLOG'):NULL,
			addon_installed('securitylogging')?array('audit','menu/adminzone/audit/security_log',array('admin_security',array('type'=>'misc'),get_module_zone('admin_security')),do_lang_tempcode('security:SECURITY_LOG'),'security:DOC_SECURITY_LOG'):NULL,
			array('audit','menu/adminzone/audit/email_log',array('admin_email_log',array('type'=>'misc'),get_module_zone('admin_email_log')),do_lang_tempcode('EMAIL_LOG'),'DOC_EMAIL_LOG'),

			array('style','menu/adminzone/style/themes/themes',array('admin_themes',array('type'=>'misc'),get_module_zone('admin_themes')),do_lang_tempcode('THEMES'),'DOC_THEMES'),
			(get_forum_type()!='ocf')?NULL:array('style','menu/adminzone/style/emoticons',array('admin_ocf_emoticons',array('type'=>'misc'),get_module_zone('admin_ocf_emoticons')),do_lang_tempcode('EMOTICONS'),'ocf:DOC_EMOTICONS'),

			array('setup','menu/adminzone/setup/config/config',array('admin_config',array('type'=>'misc'),get_module_zone('admin_config')),do_lang_tempcode('CONFIGURATION'),'DOC_CONFIGURATION'),
			addon_installed('awards')?array('setup','menu/adminzone/setup/awards',array('admin_awards',array('type'=>'misc'),get_module_zone('admin_awards')),do_lang_tempcode('AWARDS'),'DOC_AWARDS'):NULL,
			(get_forum_type()=='ocf' || !addon_installed('welcome_emails'))?/*Is on members menu*/NULL:array('setup','menu/adminzone/setup/welcome_emails',array('admin_ocf_welcome_emails',array('type'=>'misc'),get_module_zone('admin_ocf_welcome_emails')),do_lang_tempcode('ocf_welcome_emails:WELCOME_EMAILS'),'ocf_welcome_emails:DOC_WELCOME_EMAILS'),
			((get_forum_type()=='ocf')/*Is on members menu*/ || (addon_installed('securitylogging')))?NULL:array('tools','menu/adminzone/tools/users/investigate_user',array('admin_lookup',array(),get_module_zone('admin_lookup')),do_lang_tempcode('lookup:INVESTIGATE_USER'),'lookup:DOC_INVESTIGATE_USER'),
			addon_installed('xml_fields')?array('setup','menu/adminzone/setup/xml_fields',array('admin_config',array('type'=>'xml_fields'),get_module_zone('admin_config')),do_lang_tempcode('config:FIELD_FILTERS'),'config:DOC_FIELD_FILTERS'):NULL,

			(get_forum_type()!='ocf')?NULL:array('tools','menu/adminzone/tools/users/member_add',array('admin_ocf_members',array('type'=>'misc'),get_module_zone('admin_ocf_members')),do_lang_tempcode('MEMBERS'),'ocf:DOC_MEMBERS'),

			//((get_forum_type()!='ocf')||(!has_privilege(get_member(),'control_usergroups')))?NULL:array('tools','menu/social/groups',array('groups',array('type'=>'misc'),get_module_zone('groups'),do_lang_tempcode('SWITCH_ZONE_WARNING')),do_lang_tempcode('SECONDARY_GROUP_MEMBERSHIP'),'DOC_SECONDARY_GROUP_MEMBERSHIP'),
			array('tools','menu/adminzone/tools/cleanup',array('admin_cleanup',array('type'=>'misc'),get_module_zone('admin_cleanup')),do_lang_tempcode('CLEANUP_TOOLS'),'DOC_CLEANUP_TOOLS'),

			array('security','menu/adminzone/security/permissions/permission_tree_editor',array('admin_permissions',array('type'=>'misc'),get_module_zone('admin_permissions')),do_lang_tempcode('permissions:PERMISSIONS_TREE'),'permissions:DOC_PERMISSIONS_TREE'),
			addon_installed('match_key_permissions')?array('security','menu/adminzone/security/permissions/match_keys',array('admin_permissions',array('type'=>'match_keys'),get_module_zone('admin_permissions')),do_lang_tempcode('permissions:PAGE_MATCH_KEY_ACCESS'),'permissions:DOC_PAGE_MATCH_KEY_ACCESS'):NULL,
			//array('security','menu/adminzone/security/permissions/permission_tree_editor',array('admin_permissions',array('type'=>'page'),get_module_zone('admin_permissions')),do_lang_tempcode('permissions:PAGE_ACCESS'),'permissions:DOC_PAGE_PERMISSIONS'),  // Disabled as not needed - but tree permission editor will redirect to it if no javascript available
			addon_installed('securitylogging')?array('security','menu/adminzone/security/ip_ban',array('admin_ip_ban',array('type'=>'misc'),get_module_zone('admin_ip_ban')),do_lang_tempcode('submitban:BANNED_ADDRESSES'),'submitban:DOC_IP_BAN'):NULL,
			array('security','menu/adminzone/security/permissions/privileges',array('admin_permissions',array('type'=>'privileges'),get_module_zone('admin_permissions')),do_lang_tempcode('permissions:GLOBAL_PRIVILEGES'),'permissions:DOC_PRIVILEGES'),
			(get_forum_type()!='ocf')?NULL:array('security','menu/social/groups',array('admin_ocf_groups',array('type'=>'misc'),get_module_zone('admin_ocf_groups')),do_lang_tempcode('ocf:USERGROUPS'),'ocf:DOC_GROUPS'),
			(get_forum_type()=='ocf')?NULL:array('security','menu/social/groups',array('admin_permissions',array('type'=>'absorb'),get_module_zone('admin_security')),do_lang_tempcode('permissions:ABSORB_PERMISSIONS'),'permissions:DOC_ABSORB_PERMISSIONS'),

			(is_null(get_value('brand_base_url')))?array('tools','menu/adminzone/tools/upgrade',array('admin_config',array('type'=>'upgrader'),get_module_zone('admin_config')),do_lang_tempcode('upgrade:FU_UPGRADER_TITLE'),'upgrade:FU_UPGRADER_INTRO'):NULL,
			(addon_installed('syndication'))?array('tools','action_links/rss',array('admin_config',array('type'=>'backend'),get_module_zone('admin_config')),do_lang_tempcode('FEEDS'),'rss:OPML_INDEX_DESCRIPTION'):NULL,
			(addon_installed('code_editor'))?array('tools','menu/adminzone/tools/code_editor',array('admin_config',array('type'=>'code_editor'),get_module_zone('admin_config')),do_lang_tempcode('CODE_EDITOR'),'DOC_CODE_EDITOR'):NULL,

			//(get_comcode_zone('start',false)===NULL)?NULL:array('','menu/start',array('start',array(),get_comcode_zone('start')),do_lang_tempcode('HOME')),	Attached to zone, so this is not needed
			array('','menu/pages',array('admin',array('type'=>'pages'),'adminzone'),do_lang_tempcode('PAGES')),
			array('','menu/rich_content',array('admin',array('type'=>'rich_content'),'adminzone'),do_lang_tempcode('menus:RICH_CONTENT')),
			array('','menu/site_meta',array('admin',array('type'=>'site_meta'),'adminzone'),do_lang_tempcode('menus:SITE_META')),
			array('','menu/social',array('admin',array('type'=>'social'),'adminzone'),do_lang_tempcode('SECTION_SOCIAL')),

			(get_comcode_zone('about',false)===NULL)?NULL:array('pages','menu/pages/about_us',array('about',array(),get_comcode_zone('about')),do_lang_tempcode('menus:ABOUT')),
			(get_comcode_zone('keymap',false)===NULL || get_option('collapse_user_zones')=='1'/*If collapsed then will show as child page of help page*/)?NULL:array('site_meta','menu/pages/keymap',array('keymap',array(),get_comcode_zone('keymap')),do_lang_tempcode('KEYBOARD_MAP')),
			(get_comcode_zone('privacy',false)===NULL || get_option('bottom_show_privacy_link')=='1')?NULL:array('site_meta','menu/pages/privacy_policy',array('privacy',array(),get_comcode_zone('privacy')),do_lang_tempcode('PRIVACY')),
			(get_comcode_zone('rules',false)===NULL || get_option('bottom_show_rules_link')=='1')?NULL:array('site_meta','menu/pages/rules',array('rules',array(),get_comcode_zone('rules')),do_lang_tempcode('RULES')),
			(get_comcode_zone('feedback',false)===NULL || get_option('bottom_show_feedback_link')=='1')?NULL:array('site_meta','menu/site_meta/contact_us',array('feedback',array(),get_comcode_zone('feedback')),do_lang_tempcode('FEEDBACK')),
			//(get_comcode_zone('sitemap',false)===NULL || get_option('bottom_show_sitemap_button')=='1')?NULL:array('site_meta','tool_buttons/sitemap',array('sitemap',array(),get_comcode_zone('sitemap')),do_lang_tempcode('SITEMAP')),	Redundant, menu itself is a sitemap
			// userguide_comcode is child of help_page

			(get_forum_type()=='none' || !is_guest($member_id))?NULL:array('site_meta','menu/site_meta/user_actions/login',array('login',array(),''),do_lang_tempcode('_LOGIN')),
			(get_forum_type()=='none' || is_guest($member_id))?NULL:array('site_meta','menu/site_meta/user_actions/logout',array('login',array(),''),do_lang_tempcode('LOGOUT')),

			(get_forum_type()=='ocf')?NULL:array('site_meta','menu/site_meta/user_actions/join',array('join',array(),get_module_zone('join')),do_lang_tempcode('_JOIN')),
			(get_forum_type()=='ocf')?NULL:array('site_meta','menu/site_meta/user_actions/lost_password',array('lost_password',array(),get_module_zone('lost_password')),do_lang_tempcode('ocf:LOST_PASSWORD')),
			(get_forum_type()=='ocf')?NULL:array('social','menu/social/groups',array('groups',array(),get_module_zone('groups')),do_lang_tempcode('ocf:USERGROUPS')),
			(get_forum_type()=='ocf')?NULL:array('social','menu/social/members',array('members',array(),get_module_zone('members')),do_lang_tempcode('ocf:MEMBER_DIRECTORY')),
			(get_forum_type()=='ocf')?NULL:array('social','menu/social/users_online',array('users_online',array(),get_module_zone('users_online')),do_lang_tempcode('USERS_ONLINE')),

			(get_forum_type()=='ocf' || get_forum_type()=='none')?NULL:get_forum_base_url(),
			(get_forum_type()=='ocf' || get_forum_type()=='none')?NULL:$GLOBALS['FORUM_DRIVER']->member_profile_url(get_member(),true),
			(get_forum_type()=='ocf' || get_forum_type()=='none')?NULL:$GLOBALS['FORUM_DRIVER']->join_url(),
			(get_forum_type()=='ocf' || get_forum_type()=='none')?NULL:$GLOBALS['FORUM_DRIVER']->users_online_url(),
		);
	}

}


