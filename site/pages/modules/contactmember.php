<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2011

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		core_ocf
 */

/**
 * Module page class.
 */
class Module_contactmember
{

	/**
	 * Standard modular info function.
	 *
	 * @return ?array	Map of module info (NULL: module is disabled).
	 */
	function info()
	{
		$info=array();
		$info['author']='Chris Graham';
		$info['organisation']='ocProducts';
		$info['hacked_by']=NULL;
		$info['hack_version']=NULL;
		$info['version']=2;
		$info['locked']=false;
		return $info;
	}
	
	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array();
	}
	
	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		require_lang('mail');
		require_lang('comcode');
	
		if (get_forum_type()!='ocf') warn_exit(do_lang_tempcode('NO_OCF')); else ocf_require_all_forum_stuff();
	
		$type=get_param('type','misc');
	
		$member_id=get_param_integer('id');
		if (($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_email_address')=='') || ((get_option('allow_email_disable')=='1') && ($GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_allow_emails')==0)) || (is_guest($member_id)))
			warn_exit(do_lang_tempcode('NO_ACCEPT_EMAILS'));

		if ($type=='misc') return $this->gui();
		if ($type=='actual') return $this->actual();
	
		return new ocp_tempcode();
	}
	
	/**
	 * The UI to contact a member.
	 *
	 * @return tempcode		The UI
	 */
	function gui()
	{
		$member_id=get_param_integer('id');
		$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id);
		if (is_null($username)) warn_exit(do_lang_tempcode('USER_NO_EXIST'));
	
		$title=get_page_title('EMAIL_MEMBER',true,array(escape_html($username)));

		global $EXTRA_HEAD;
		$EXTRA_HEAD->attach('<meta name="robots" content="noindex" />'); // XHTMLXHTML

		$text=do_lang_tempcode('EMAIL_MEMBER_TEXT');

		$fields=new ocp_tempcode();
		require_code('form_templates');
		$fields->attach(form_input_line(do_lang_tempcode('SUBJECT'),'','subject',get_param('subject',''),true));
		$default_email=(is_guest())?'':$GLOBALS['FORUM_DRIVER']->get_member_row_field(get_member(),'m_email_address');
		$default_name=(is_guest())?'':$GLOBALS['FORUM_DRIVER']->get_member_row_field(get_member(),'m_username');
		$fields->attach(form_input_line(do_lang_tempcode('NAME'),do_lang_tempcode('_DESCRIPTION_NAME'),'name',$default_name,true));
		$fields->attach(form_input_email(do_lang_tempcode('EMAIL_ADDRESS'),do_lang_tempcode('YOUR_ADDRESS'),'email_address',$default_email,true));
		$fields->attach(form_input_text(do_lang_tempcode('MESSAGE'),'','message',get_param('message',''),true));
		if (addon_installed('captcha'))
		{
			require_code('captcha');
			if (use_captcha())
			{
				$fields->attach(form_input_captcha());
				$text->attach(' ');
				$text->attach(do_lang_tempcode('FORM_TIME_SECURITY'));
			}
		}
		$size=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_max_email_attach_size_mb');
		$hidden=new ocp_tempcode();
		if ($size!=0)
		{
			handle_max_file_size($hidden);
			$fields->attach(form_input_upload_multi(do_lang_tempcode('_ATTACHMENT'),do_lang_tempcode('EMAIL_ATTACHMENTS',integer_format($size)),'attachment',false));
		}
		$submit_name=do_lang_tempcode('SEND');
		$redirect=get_param('redirect','');
		if ($redirect=='')
		{
			$redirect=$GLOBALS['FORUM_DRIVER']->member_profile_link($member_id,false,true);
			if (is_object($redirect)) $redirect=$redirect->evaluate();
		}
		$post_url=build_url(array('page'=>'_SELF','type'=>'actual','id'=>$member_id,'redirect'=>$redirect),'_SELF');

		return do_template('FORM_SCREEN',array('_GUID'=>'e06557e6eceacf1f46ee930c99ac5bb5','TITLE'=>$title,'HIDDEN'=>$hidden,'JAVASCRIPT'=>function_exists('captcha_ajax_check')?captcha_ajax_check():'','FIELDS'=>$fields,'TEXT'=>$text,'SUBMIT_NAME'=>$submit_name,'URL'=>$post_url));
	}

	/**
	 * The actualiser to contact a member.
	 *
	 * @return tempcode		The UI
	 */
	function actual()
	{
		if (addon_installed('captcha'))
		{
			require_code('captcha');
			enforce_captcha();
		}

		$member_id=get_param_integer('id');
		$email_address=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_email_address');
		if (is_null($email_address)) fatal_exit(do_lang_tempcode('INTERNAL_ERROR'));
		$to_name=$GLOBALS['FORUM_DRIVER']->get_username($member_id);

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('EMAIL_MEMBER',escape_html($to_name)))));

		if (is_null($to_name)) warn_exit(do_lang_tempcode('USER_NO_EXIST'));

		$from_email=trim(post_param('email_address'));
		require_code('type_validation');
		if (!is_valid_email_address($from_email)) warn_exit(do_lang_tempcode('INVALID_EMAIL_ADDRESS'));
		$from_name=post_param('name');

		$title=get_page_title('EMAIL_MEMBER',true,array(escape_html($GLOBALS['FORUM_DRIVER']->get_username($member_id))));

		require_code('mail');
		$attachments=array();
		$size_so_far=0;
		require_code('uploads');
		is_swf_upload(true);
		foreach ($_FILES as $file)
		{
			if ((is_swf_upload()) || (is_uploaded_file($file['tmp_name'])))
			{
				$attachments[$file['tmp_name']]=$file['name'];
				$size_so_far+=$file['size'];
			} else
			{
				if ((defined('UPLOAD_ERR_NO_FILE')) && (array_key_exists('error',$file)) && ($file['error']!=UPLOAD_ERR_NO_FILE))
					warn_exit(do_lang_tempcode('ERROR_UPLOADING_ATTACHMENTS'));
			}
		}
		$size=$GLOBALS['FORUM_DRIVER']->get_member_row_field($member_id,'m_max_email_attach_size_mb');
		if ($size_so_far>$size*1024*1024)
		{
			warn_exit(do_lang_tempcode('EXCEEDED_ATTACHMENT_SIZE',integer_format($size)));
		}
		mail_wrap(do_lang('EMAIL_MEMBER_SUBJECT',get_site_name(),post_param('subject'),NULL,get_lang($member_id)),post_param('message'),array($email_address),$to_name,$from_email,$from_name,3,$attachments,false,get_member());

		log_it('EMAIL',strval($member_id),$to_name);

		breadcrumb_set_self(do_lang_tempcode('DONE'));

		$url=get_param('redirect');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

}


