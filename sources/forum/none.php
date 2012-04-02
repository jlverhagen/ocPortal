<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_forum_drivers
 */

class forum_driver_none extends forum_driver_base
{

	/**
	 * Get the administration username for the site.
	 *
	 * @return string			The admin username
	 */
	function get_admin_username()
	{
		global $SITE_INFO;
		$ret=array_key_exists('admin_username',$SITE_INFO)?$SITE_INFO['admin_username']:'admin';
		if ($ret=='') $ret='admin';
		return $ret;
	}

	/**
	 * Get the rows for the top given number of posters on the forum.
	 *
	 * @param  integer		The limit to the number of top posters to fetch
	 * @return array			The rows for the given number of top posters in the forum
	 */
	function get_top_posters($limit)
	{
		unset($limit);
		return array(array(1));
	}

	/**
	 * Attempt to to find the member's language from their forum profile. It converts between language-identifiers using a map (lang/map.ini).
	 *
	 * @param  MEMBER			The member who's language needs to be fetched
	 * @return ?LANGUAGE_NAME The member's language (NULL: unknown)
	 */
	function forum_get_lang($member)
	{
		unset($member);
		return NULL;
	}

	/**
	 * Find if login cookie is md5-hashed.
	 *
	 * @return boolean		Whether the login cookie is md5-hashed
	 */
	function is_hashed()
	{
		return true;
	}

	/**
	 * Find if the login cookie contains the login name instead of the member id.
	 *
	 * @return boolean		Whether the login cookie contains a login name or a member id
	 */
	function is_cookie_login_name()
	{
		return true;
	}

	/**
	 * Find the member id of the forum guest member.
	 *
	 * @return MEMBER			The member id of the forum guest member
	 */
	function get_guest_id()
	{
		return 0;
	}

	/**
	 * Add the specified custom field to the forum (some forums implemented this using proper custom profile fields, others through adding a new field).
	 *
	 * @param  string			The name of the new custom field
	 * @param  integer		The length of the new custom field
	 * @return boolean		Whether the custom field was created successfully
	 */
	function install_create_custom_field($name,$length)
	{
		unset($name);
		unset($length);
		return false;
	}

	/**
	 * Get an array of attributes to take in from the installer. Almost all forums require a table prefix, which the requirement there-of is defined through this function.
	 * The attributes have 4 values in an array
	 * - name, the name of the attribute for info.php
	 * - default, the default value (perhaps obtained through autodetection from forum config)
	 * - description, a textual description of the attributes
	 * - title, a textual title of the attribute
	 *
	 * @return array			The attributes for the forum
	 */
	function install_specifics()
	{
		$c=array();
		$c['name']='admin_username';
		$c['default']='admin';
		$c['description']=do_lang('DESCRIPTION_ADMIN_USERNAME');
		$c['title']=do_lang('ADMIN_USERNAME');
		return array($c);
	}

	/**
	 * Searches for forum auto-config at this path.
	 *
	 * @param  PATH			The path in which to search
	 * @return boolean		Whether the forum auto-config could be found
	 */
	function install_test_load_from($path)
	{
		unset($path);
		global $INFO;
		$INFO=array();
		$INFO['sql_database']='ocp';
		$INFO['sql_user']=$GLOBALS['DB_STATIC_OBJECT']->db_default_user();
		$INFO['sql_pass']=$GLOBALS['DB_STATIC_OBJECT']->db_default_password();
		return true;
	}

	/**
	 * Get an array of paths to search for config at.
	 *
	 * @return array			The paths in which to search for the forum config
	 */
	function install_get_path_search_list()
	{
		return array();
	}

	/**
	 * Get an emoticon chooser template.
	 *
	 * @param  string			The ID of the form field the emoticon chooser adds to
	 * @return tempcode		The emoticon chooser template
	 */
	function get_emoticon_chooser($field_name='post')
	{
		require_code('comcode_text');
		$emoticons=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'f_emoticons WHERE e_relevance_level=0');
		$em=new ocp_tempcode();
		foreach ($emoticons as $emo)
		{
			$code=$emo['e_code'];

			$em->attach(do_template('EMOTICON_CLICK_CODE',array('_GUID'=>'0b51492b6e170db4466be74fdf312260','FIELD_NAME'=>$field_name,'CODE'=>$code,'IMAGE'=>apply_emoticons($code))));
		}
	
		return $em;
	}

	/**
	 * Find the base URL to the emoticons.
	 *
	 * @return URLPATH		The base URL
	 */
	function get_emo_dir()
	{
		return '';
	}
	
	/**
	 * Get a map between smiley codes and templates representing the HTML-image-code for this smiley. The smilies present of course depend on the forum involved.
	 *
	 * @return array			The map
	 */
	function find_emoticons()
	{
		global $IN_MINIKERNEL_VERSION;
		if ($IN_MINIKERNEL_VERSION==1) return array();
	
		global $EMOTICON_CACHE,$EMOTICON_LEVELS;
		if (!is_null($EMOTICON_CACHE)) return $EMOTICON_CACHE;
		$EMOTICON_CACHE=array();
		$EMOTICON_LEVELS=array();
		$rows=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'f_emoticons WHERE e_relevance_level<4');
		foreach ($rows as $myrow)
		{
			$EMOTICON_CACHE[$myrow['e_code']]=array('EMOTICON_IMG_CODE_THEMED',$myrow['e_theme_img_code'],$myrow['e_code']);
			$EMOTICON_LEVELS[$myrow['e_code']]=$myrow['e_relevance_level'];
		}
		uksort($EMOTICON_CACHE,'strlen_sort');
		$EMOTICON_CACHE=array_reverse($EMOTICON_CACHE);
		return $EMOTICON_CACHE;
	}
	
	/**
	 * Set a custom profile fields value. It should not be called directly.
	 *
	 * @param  MEMBER			The member id
	 * @param  string			The field name
	 * @param  string			The value
	 */
	function set_custom_field($member,$field,$amount)
	{
		unset($member);
		unset($field);
		unset($amount);
	}

	/**
	 * Get custom profile fields values for all 'ocp_' prefixed keys.
	 *
	 * @param  MEMBER			The member id
	 * @return ?array			A map of the custom profile fields, key_suffix=>value (NULL: no fields)
	 */
	function get_custom_fields($member)
	{
		unset($member);
		return array();
	}

	/**
	 * Get a member profile-row for the member of the given name.
	 *
	 * @param  SHORT_TEXT	The member name
	 * @return ?array			The profile-row (NULL: no row)
	 */
	function pget_row($name)
	{
		if ($name==$this->get_admin_username()) return array(1);
		return NULL;
	}
	
	/**
	 * Get a member row.
	 *
	 * @param  AUTO_LINK		The member ID
	 * @return array			The profile-row
	 */
	function get_member_row($id)
	{
		unset($id);
		return array(0);
	}

	/**
	 * Get a member row.
	 *
	 * @param  AUTO_LINK		The member ID
	 * @param  ID_TEXT		The field
	 * @return ?array			The result (NULL: unknown)
	 */
	function get_member_row_field($id,$field)
	{
		unset($id);
		return NULL;
	}

	/**
	 * From a member profile-row, get the member's primary usergroup.
	 *
	 * @param  array			The profile-row
	 * @return GROUP			The member's primary usergroup
	 */
	function pname_group($r)
	{
		if ($r[0]==1) return 1;
		return 0;
	}

	/**
	 * From a member profile-row, get the member's member id.
	 *
	 * @param  array			The profile-row
	 * @return MEMBER			The member id
	 */
	function pname_id($r)
	{
		return $r[0];
	}

	/**
	 * From a member profile-row, get the member's last visit date.
	 *
	 * @param  array			The profile-row
	 * @return TIME			The last visit date
	 */
	function pnamelast_visit($r)
	{
		unset($r);
		return time();
	}

	/**
	 * From a member profile-row, get the member's name.
	 *
	 * @param  array			The profile-row
	 * @return string			The member name
	 */
	function pname_name($r)
	{
		return $this->get_username($r[0]);
	}

	/**
	 * From a member profile-row, get the member's e-mail address.
	 *
	 * @param  array			The profile-row
	 * @return SHORT_TEXT	The member e-mail address
	 */
	function pname_email($r)
	{
		return $this->get_member_email_address($r[0]);
	}

	/**
	 * Get a URL to the specified member's home (control panel).
	 *
	 * @param  MEMBER			The member id
	 * @return URLPATH		The URL to the members home
	 */
	function member_home_url($id)
	{
		unset($id);
		return get_base_url();
	}

	/**
	 * Get the photo thumbnail URL for the specified member id.
	 *
	 * @param  MEMBER			The member id
	 * @return URLPATH		The URL (blank: none)
	 */
	function get_member_photo_url($member)
	{
		unset($member);

		return '';
	}

	/**
	 * Get the avatar URL for the specified member id.
	 *
	 * @param  MEMBER			The member id
	 * @return URLPATH		The URL (blank: none)
	 */
	function get_member_avatar_url($member)
	{
		unset($member);

		return '';
	}

	/**
	 * Get a URL to the specified member's profile.
	 *
	 * @param  MEMBER			The member id
	 * @return URLPATH		The URL to the member profile
	 */
	function _member_profile_url($id)
	{
		if (!addon_installed('authors')) return get_base_url();
		
		if ($id==1)
		{
			$url=build_url(array('page'=>'authors','type'=>'misc','id'=>$this->get_admin_username()),get_module_zone('authors'),NULL,false,false,true);
			return $url->evaluate();
		}
		$url=build_url(array('page'=>'authors','type'=>'misc','id'=>do_lang('GUEST')),get_module_zone('authors'),NULL,false,false,true);
		return $url->evaluate();
	}

	/**
	 * Get a URL to the specified member's profile, from the username.
	 *
	 * @param  SHORT_TEXT	The username
	 * @return URLPATH		The URL to the member profile
	 */
	function member_profile_url_name($name)
	{
		if (!addon_installed('authors')) return get_base_url();
		
		if ((addon_installed('staff')) || (!addon_installed('authors')))
		{
			$url=build_url(array('page'=>'staff','id'=>$name),get_module_zone('staff'));
			return $url->evaluate();
		}
		$url=build_url(array('page'=>'authors','id'=>$name),get_module_zone('authors'),NULL,false,false,true);
		return $url->evaluate();
	}

	/**
	 * Get a URL to the registration page (for people to create member accounts).
	 *
	 * @return URLPATH		The URL to the registration page
	 */
	function _join_url()
	{
		return 'index.php';
	}

	/**
	 * Get a URL to the members-online page.
	 *
	 * @return URLPATH		The URL to the members-online page
	 */
	function _online_members_url()
	{
		return '';
	}

	/**
	 * Get a URL to send a private/personal message to the given member.
	 *
	 * @param  MEMBER			The member id
	 * @return URLPATH		The URL to the private/personal message page
	 */
	function _member_pm_url($id)
	{
		unset($id);
		return 'mailto:'.get_option('staff_address');
	}

	/**
	 * Get a URL to the specified forum.
	 *
	 * @param  integer		The forum ID
	 * @return URLPATH		The URL to the specified forum
	 */
	function _forum_url($id)
	{
		unset($id);
		return '';
	}

	/**
	 * Get the forum ID from a forum name.
	 *
	 * @param  SHORT_TEXT	The forum name
	 * @return integer		The forum ID
	 */
	function forum_id_from_name($forum_name)
	{
		unset($forum_name);
		return 0;
	}

	/**
	 * Get the topic ID from a topic identifier in the specified forum. It is used by comment topics, which means that the unique-topic-name assumption holds valid.
	 *
	 * @param  string			The forum name / ID
	 * @param  SHORT_TEXT	The topic identifier
	 * @return ?integer		The topic ID (NULL: not found)
	 */
	function find_topic_id_for_topic_identifier($forum,$topic_identifier)
	{
		return NULL;
	}

	/**
	 * Makes a post in the specified forum, in the specified topic according to the given specifications. If the topic doesn't exist, it is created along with a spacer-post.
	 * Spacer posts exist in order to allow staff to delete the first true post in a topic. Without spacers, this would not be possible with most forum systems. They also serve to provide meta information on the topic that cannot be encoded in the title (such as a link to the content being commented upon).
	 *
	 * @param  SHORT_TEXT	The forum name
	 * @param  SHORT_TEXT	The topic identifier (usually <content-type>_<content-id>)
	 * @param  MEMBER			The member ID
	 * @param  LONG_TEXT		The post title
	 * @param  LONG_TEXT		The post content in Comcode format
	 * @param  string			The topic title; must be same as content title if this is for a comment topic
	 * @param  string			This is put together with the topic identifier to make a more-human-readable topic title or topic description (hopefully the latter and a $content_title title, but only if the forum supports descriptions)
	 * @param  ?URLPATH		URL to the content (NULL: do not make spacer post)
	 * @param  ?TIME			The post time (NULL: use current time)
	 * @param  ?IP				The post IP address (NULL: use current members IP address)
	 * @param  ?BINARY		Whether the post is validated (NULL: unknown, find whether it needs to be marked unvalidated initially). This only works with the OCF driver.
	 * @param  ?BINARY		Whether the topic is validated (NULL: unknown, find whether it needs to be marked unvalidated initially). This only works with the OCF driver.
	 * @param  boolean		Whether to skip post checks
	 * @param  SHORT_TEXT	The name of the poster
	 * @param  ?AUTO_LINK	ID of post being replied to (NULL: N/A)
	 * @param  boolean		Whether the reply is only visible to staff
	 * @return array			Topic ID (may be NULL), and whether a hidden post has been made
	 */
	function make_post_forum_topic($forum_name,$topic_identifier,$member_id,$post_title,$_post,$content_title,$topic_identifier_encapsulation_prefix,$content_url=NULL,$time=NULL,$ip=NULL,$validated=NULL,$topic_validated=1,$skip_post_checks=false,$poster_name_if_guest='',$parent_id=NULL,$staff_only=false)
	{
		return array(NULL,false);
	}

	/**
	 * Get an array of maps for the topic in the given forum.
	 *
	 * @param  integer		The topic ID
	 * @return mixed			The array of maps (Each map is: title, message, member, date) (-1 for no such forum, -2 for no such topic)
	 */
	function get_forum_topic_posts($topic_id)
	{
		unset($topic_id);
		return (-1);
	}

	/**
	 * Get a URL to the specified topic ID. Most forums don't require the second parameter, but some do, so it is required in the interface.
	 *
	 * @param  integer		The topic ID
	 * @param string			  The forum ID
	 * @return URLPATH		The URL to the topic
	 */
	function topic_url($id,$forum)
	{
		unset($forum);
		$url=build_url(array('page'=>'news','id'=>$id),get_module_zone('news'),NULL,false,false,true);
		return $url->evaluate();
	}

	/**
	 * Get a URL to the specified post id.
	 *
	 * @param  integer		The post id
	 * @param string			  The forum ID
	 * @return URLPATH		The URL to the post
	 */
	function post_url($id,$forum)
	{
		unset($forum);
		$url=build_url(array('page'=>'news','id'=>$id),get_module_zone('news'),NULL,false,false,true);
		return $url->evaluate();
	}

	/**
	 * Get an array of topics in the given forum. Each topic is an array with the following attributes:
	 * - id, the topic ID
	 * - title, the topic title
	 * - lastusername, the username of the last poster
	 * - lasttime, the timestamp of the last reply
	 * - closed, a Boolean for whether the topic is currently closed or not
	 * - firsttitle, the title of the first post
	 * - firstpost, the first post (only set if $show_first_posts was true)
	 *
	 * @param  SHORT_TEXT	The forum name
	 * @param  integer		The limit
	 * @param  integer		The start position
	 * @param  integer		The total rows (not a parameter: returns by reference)
	 * @param  SHORT_TEXT	The topic title filter
	 * @param  boolean		Whether to show the first posts
	 * @param  string			The date key to sort by
	 * @set    lasttime firsttime
	 * @param  boolean		Whether to limit to hot topics
	 * @param  SHORT_TEXT	The topic description filter
	 * @return ?array			The array of topics (NULL: error)
	 */
	function show_forum_topics($name,$limit,$start,&$max_rows,$filter_topic_title='',$show_first_posts=false,$date_key='lasttime',$hot=false,$filter_topic_description='')
	{
		unset($name);
		unset($limit);
		unset($filter_topic_title);
		unset($show_first_posts);
		unset($date_key);
		unset($hot);
		unset($filter_topic_description);
		return NULL;
	}

	/**
	 * Get an array of members who are in at least one of the given array of usergroups.
	 *
	 * @param  array			The array of usergroups
	 * @param  ?integer		Return up to this many entries for primary members and this many entries for secondary members (NULL: no limit, only use no limit if querying very restricted usergroups!)
	 * @param  integer		Return primary members after this offset and secondary members after this offset
	 * @return ?array			The array of members (NULL: no members)
	 */
	function member_group_query($groups,$max=NULL,$start=0)
	{
		if (in_array(1,$groups)) return array(array(1));

		return array();
	}

	/**
	 * This is the opposite of the get_next_member function.
	 *
	 * @param  MEMBER			The member id to decrement
	 * @return ?MEMBER		The previous member id (NULL: no previous member)
	 */
	function get_previous_member($member)
	{
		unset($member);
		return NULL; // Guest doesn't count
	}

	/**
	 * Get the member id of the next member after the given one, or NULL.
	 * It cannot be assumed there are no gaps in member ids, as members may be deleted.
	 *
	 * @param  MEMBER			The member id to increment
	 * @return ?MEMBER		The next member id (NULL: no next member)
	 */
	function get_next_member($member)
	{
		if ($member<1) return 1; else return NULL;
	}

	/**
	 * Try to find a member with the given IP address
	 *
	 * @param  IP				The IP address
	 * @return array			The distinct rows found
	 */
	function probe_ip($ip)
	{
		unset($ip);
		return array();
	}

	/**
	 * Get the name relating to the specified member id.
	 * If this returns NULL, then the member has been deleted. Always take potential NULL output into account.
	 *
	 * @param  MEMBER			The member id
	 * @return ?SHORT_TEXT	The member name (NULL: member deleted)
	 */
	function _get_username($member)
	{
		if ($member==$this->get_guest_id()) return do_lang('GUEST');
		if ($member==1) return $this->get_admin_username();
		return do_lang('GUEST'); // For now
	}

	/**
	 * Get the e-mail address for the specified member id.
	 *
	 * @param  MEMBER			The member id
	 * @return SHORT_TEXT	The e-mail address
	 */
	function _get_member_email_address($member)
	{
		if ($member==1) return get_option('staff_address');
		return '';
	}

	/**
	 * Find if this member may have e-mails sent to them
	 *
	 * @param  MEMBER			The member id
	 * @return boolean		Whether the member may have e-mails sent to them
	 */
	function get_member_email_allowed($member)
	{
		unset($member);
		return true;
	}

	/**
	 * Get the timestamp of a member's join date.
	 *
	 * @param  MEMBER			The member id
	 * @return TIME			The timestamp
	 */
	function get_member_join_timestamp($member)
	{
		unset($member);
		return filectime(get_file_base().'/info.php');
	}

	/**
	 * Find all members with a name matching the given SQL LIKE string.
	 *
	 * @param  string			The pattern
	 * @param  ?integer		Maximum number to return (limits to the most recent active) (NULL: no limit)
	 * @return ?array			The array of matched members (NULL: none found)
	 */
	function get_matching_members($pattern,$limit=NULL)
	{
		unset($pattern);
		unset($limit);
		return array();
	}

	/**
	 * Get the given member's post count.
	 *
	 * @param  MEMBER			The member id
	 * @return integer		The post count
	 */
	function get_post_count($member)
	{
		unset($member);
		return 0;
	}

	/**
	 * Get the given member's topic count.
	 *
	 * @param  MEMBER			The member id
	 * @return integer		The topic count
	 */
	function get_topic_count($member)
	{
		unset($member);
		return 0;
	}
	
	/**
	 * Find out if the given member id is banned.
	 *
	 * @param  MEMBER			The member id
	 * @return boolean		Whether the member is banned
	 */
	function is_banned($member)
	{
		unset($member);
		return false;
	}

	/**
	 * Try to find the theme that the logged-in/guest member is using, and map it to an ocPortal theme.
	 * The themes/map.ini file functions to provide this mapping between forum themes, and ocPortal themes, and has a slightly different meaning for different forum drivers. For example, some drivers map the forum themes theme directory to the ocPortal theme name, whilst others made the humanly readeable name.
	 *
	 * @return ID_TEXT		The theme
	 */
	function _get_theme()
	{
		return 'default';
	}

	/**
	 * Find if the specified member id is marked as staff or not.
	 *
	 * @param  MEMBER			The member id
	 * @return boolean		Whether the member is staff
	 */
	function _is_staff($member)
	{
		return ($member==1);
	}

	/**
	 * Find if the specified member id is marked as a super admin or not.
	 *
	 * @param  MEMBER			The member id
	 * @return boolean		Whether the member is a super admin
	 */
	function _is_super_admin($member)
	{
		return ($member==1);
	}

	/**
	 * Get the number of members currently online on the forums.
	 *
	 * @return ?integer		The number of members (NULL: NA)
	 */
	function get_num_users_forums()
	{
		return NULL;
	}

	/**
	 * Get the number of members registered on the forum.
	 *
	 * @return integer		The number of members
	 */
	function get_members()
	{
		return 1;
	}

	/**
	 * Get the total topics ever made on the forum.
	 *
	 * @return integer		The number of topics
	 */
	function get_topics()
	{
		return 0;
	}

	/**
	 * Get the total posts ever made on the forum.
	 *
	 * @return integer		The number of posts
	 */
	function get_num_forum_posts()
	{
		return 0;
	}

	/**
	 * Get the number of new forum posts.
	 *
	 * @return integer		The number of posts
	 */
	function _get_num_new_forum_posts()
	{
		return 0;
	}

	/**
	 * Get a member id from the given member's username.
	 *
	 * @param  SHORT_TEXT	The member name
	 * @return MEMBER			The member id
	 */
	function get_member_from_username($name)
	{
		if ($name==$this->get_admin_username()) return 1;
		if ($name==do_lang('GUEST')) return 0;
		return 0;
	}

	/**
	 * Get the ids of the admin usergroups.
	 *
	 * @return array			The admin usergroup ids
	 */
	function _get_super_admin_groups()
	{
		return array(1);
	}

	/**
	 * Get the ids of the moderator usergroups.
	 * It should not be assumed that a member only has one usergroup - this depends upon the forum the driver works for. It also does not take the staff site filter into account.
	 *
	 * @return array			The moderator usergroup ids
	 */
	function _get_moderator_groups()
	{
		return array();
	}

	/**
	 * Get the forum usergroup list.
	 *
	 * @return array			The usergroup list
	 */
	function _get_usergroup_list()
	{
		return array(0=>do_lang('GUESTS'),1=>do_lang('ADMINISTRATORS'));
	}

	/**
	 * Get the forum usergroup relating to the specified member id.
	 *
	 * @param  MEMBER			The member id
	 * @return array			The array of forum usergroups
	 */
	function _get_members_groups($member)
	{
		if ($member==1) return array(db_get_first_id()+1);
		return array(0);
	}

	/**
	 * Find if the given member id and password is valid. If username is NULL, then the member id is used instead.
	 * All authorisation, cookies, and form-logins, are passed through this function.
	 * Some forums do cookie logins differently, so a Boolean is passed in to indicate whether it is a cookie login.
	 *
	 * @param  ?SHORT_TEXT	The member username (NULL: don't use this in the authentication - but look it up using the ID if needed)
	 * @param  MEMBER			The member id
	 * @param  MD5				The md5-hashed password
	 * @param  string			The raw password
	 * @param  boolean		Whether this is a cookie login
	 * @return array			A map of 'id' and 'error'. If 'id' is NULL, an error occurred and 'error' is set
	 */
	function forum_authorise_login($username,$userid,$password_hashed,$password_raw,$cookie_login=false)
	{
		unset($cookie_login);

		$out=array();
		$out['id']=NULL;

		if (($username!=$this->get_admin_username()) && ($userid!=1)) // All hands to lifeboats
		{
			$out['error']=(do_lang_tempcode('_USER_NO_EXIST',$username));
			return $out;
		}

		if (!check_master_password($password_raw))
		{
			$out['error']=(do_lang_tempcode('USER_BAD_PASSWORD'));
			return $out;
		}

		$out['id']=1;
		return $out;
	}

	/**
	 * Get a first known IP address of the given member.
	 *
	 * @param  MEMBER			The member id
	 * @return IP				The IP address
	 */
	function get_member_ip($id)
	{
		unset($id);
		return '';
	}

}


