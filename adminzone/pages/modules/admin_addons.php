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
 * @package		core_addon_management
 */

/**
 * Module page class.
 */
class Module_admin_addons
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
		$info['version']=3;
		$info['locked']=true;
		$info['update_require_upgrade']=1;
		return $info;
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'ADDONS','modules'=>'MODULE_MANAGEMENT','addon_import'=>'IMPORT_ADDON','addon_export'=>'EXPORT_ADDON');
	}

	/**
	 * Standard modular uninstall function.
	 */
	function uninstall()
	{
		$GLOBALS['SITE_DB']->drop_if_exists('addons');
		$GLOBALS['SITE_DB']->drop_if_exists('addons_files');
		$GLOBALS['SITE_DB']->drop_if_exists('addons_dependencies');

		deldir_contents(get_custom_file_base().'/exports/addons',true);
	}

	/**
	 * Standard modular install function.
	 *
	 * @param  ?integer	What version we're upgrading from (NULL: new install)
	 * @param  ?integer	What hack version we're upgrading from (NULL: new-install/not-upgrading-from-a-hacked-version)
	 */
	function install($upgrade_from=NULL,$upgrade_from_hack=NULL)
	{
		$GLOBALS['SITE_DB']->create_table('addons',array(
			'addon_name'=>'*SHORT_TEXT',
			'addon_author'=>'SHORT_TEXT',
			'addon_organisation'=>'SHORT_TEXT',
			'addon_version'=>'SHORT_TEXT',
			'addon_description'=>'LONG_TEXT',
			'addon_install_time'=>'TIME'
		));

		$GLOBALS['SITE_DB']->create_table('addons_files',array(
			'id'=>'*AUTO', // Because two SHORT_TEXT's as keys exceeds the 500 mysql key limit
			'addon_name'=>'SHORT_TEXT',
			'filename'=>'SHORT_TEXT'
		));

		$GLOBALS['SITE_DB']->create_table('addons_dependencies',array(
			'id'=>'*AUTO', // Because two SHORT_TEXT's as keys exceeds the 500 mysql key limit
			'addon_name'=>'SHORT_TEXT',
			'addon_name_dependant_upon'=>'SHORT_TEXT',
			'addon_name_incompatibility'=>'BINARY' // 0=dependency,1=incompatibility
		));
	}

	/**
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		if (get_file_base()!=get_custom_file_base()) warn_exit(do_lang_tempcode('SHARED_INSTALL_PROHIBIT'));

		require_lang('addons');
		require_code('addons');
		require_code('menus2');
		require_css('addons_editor');

		disable_php_memory_limit(); // Choice of what to export, or tricky import

		// Decide what we're doing
		$type=get_param('type','misc');

		if ($type=='misc') return $this->gui();
		if ($type=='addon_export') return $this->addon_export();
		if ($type=='_addon_export') return $this->_addon_export();
		if ($type=='__addon_export') return $this->__addon_export();
		if ($type=='addon_import') return $this->addon_import();
		if ($type=='_addon_import') return $this->_addon_import();
		if ($type=='addon_install') return $this->addon_install();
		if ($type=='_addon_install') return $this->_addon_install();
		if ($type=='addon_uninstall') return $this->addon_uninstall();
		if ($type=='_addon_uninstall') return $this->_addon_uninstall();
		if ($type=='multi_action') return $this->multi_action();
		if ($type=='_multi_action') return $this->_multi_action();
		if ($type=='reinstall') return $this->reinstall_module();
		if ($type=='uninstall') return $this->uninstall_module();
		if ($type=='upgrade') return $this->upgrade_module();
		if ($type=='modules') return $this->modules_interface();
		if ($type=='view') return $this->modules_view();

		return new ocp_tempcode();
	}

	/**
	 * The main UI.
	 *
	 * @return tempcode		The UI
	 */
	function gui()
	{
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/addons';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_adv_configuration';

		if (function_exists('set_time_limit')) @set_time_limit(180); // So it can scan inside addons

		$title=get_screen_title('ADDONS');

		$addons_installed=find_installed_addons();
		$addons_available_for_installation=find_available_addons();

		$_tpl_addons=array();

		$updated_addons_arr=find_updated_addons();
		$updated_addons='';
		foreach ($updated_addons_arr as $updated_addon)
		{
			if ($updated_addons!='') $updated_addons.=',';
			$updated_addons.=strval($updated_addon[0]);
		}

		// Show installed addons
		foreach ($addons_installed as $row)
		{
			$actions=do_template('COLUMNED_TABLE_ACTION_DELETE_ENTRY',array('GET'=>true,'NAME'=>$row['addon_name'],'URL'=>build_url(array('page'=>'_SELF','type'=>'addon_uninstall','name'=>$row['addon_name']),'_SELF')));
			$updated=array_key_exists($row['addon_name'],$updated_addons_arr);
			$status=do_lang_tempcode($updated?'STATUS_OUTOFDATE':'STATUS_INSTALLED');
			$colour=$updated?'red':'green';
			$description=$row['addon_description'];
			$file_list=$row['addon_files'];
			$_tpl_addons[$row['addon_name']]=array('_GUID'=>'9a06f5a9c9e3085c10ab7fb17c3efcd1','UPDATED_ADDONS'=>$updated,'DESCRIPTION'=>$description,'FILE_LIST'=>$file_list,'COLOUR'=>$colour,'STATUS'=>$status,'NAME'=>$row['addon_name'],'FILENAME'=>do_lang_tempcode('NA_EM'),'AUTHOR'=>$row['addon_author'],'ORGANISATION'=>$row['addon_organisation'],'VERSION'=>$row['addon_version'],'ACTIONS'=>$actions,'TYPE'=>'uninstall','PASSTHROUGH'=>$row['addon_name']);
		}

		// Show addons available for installation
		foreach ($addons_available_for_installation as $filename=>$addon)
		{
			if (!array_key_exists($addon['name'],$addons_installed))
			{
				$actions=do_template('COLUMNED_TABLE_ACTION_INSTALL_ENTRY',array('_GUID'=>'e6e2bdac62c0d3afcd5251b3d525a1c9','GET'=>true,'NAME'=>$addon['name'],'HIDDEN'=>'','URL'=>build_url(array('page'=>'_SELF','type'=>'addon_install','file'=>$filename),'_SELF')));
				$status=do_lang_tempcode('STATUS_NOT_INSTALLED');
				$description=$addon['description'];
				$file_list=$addon['files'];
				if ($addon['version']=='(version-synched)') $addon['version']=float_to_raw_string(ocp_version_number());
				$_tpl_addons[$addon['name']]=array('_GUID'=>'cb61bdb9ce0cef5cd520440c5f62008f','UPDATED_ADDONS'=>false,'DESCRIPTION'=>$description,'FILE_LIST'=>$file_list,'COLOUR'=>'orange','STATUS'=>$status,'NAME'=>$addon['name'],'FILENAME'=>$filename,'AUTHOR'=>$addon['author'],'ORGANISATION'=>$addon['organisation'],'VERSION'=>$addon['version'],'ACTIONS'=>$actions,'TYPE'=>'install','PASSTHROUGH'=>$filename);
			}
		}

		global $M_SORT_KEY; // TODO: Update in v10
		$M_SORT_KEY='!COLOUR,NAME';
		usort($_tpl_addons,'multi_sort');

		$tpl_addons=new ocp_tempcode();
		foreach ($_tpl_addons as $t)
		{
			$tpl_addons->attach(do_template('ADDON_SCREEN_ADDON',$t));
		}

		$multi_action=build_url(array('page'=>'_SELF','type'=>'multi_action'),'_SELF');

		return do_template('ADDON_SCREEN',array('_GUID'=>'ed6c80c29fcae333323ef03619954b6b','TITLE'=>$title,'ADDONS'=>$tpl_addons,'MULTI_ACTION'=>$multi_action,'UPDATED_ADDONS'=>$updated_addons));
	}

	/**
	 * The UI to get an addon from some source.
	 *
	 * @return tempcode		The UI
	 */
	function addon_import()
	{
		$title=get_screen_title('IMPORT_ADDON');

		require_code('form_templates');

		$fields=new ocp_tempcode();
		$set_name='addon';
		$required=true;
		$set_title=do_lang_tempcode('SOURCE');
		$field_set=alternate_fields_set__start($set_name);

		$field_set->attach(form_input_upload(do_lang_tempcode('UPLOAD'),do_lang_tempcode('DESCRIPTION_UPLOAD'),'file',false,NULL,NULL,true,'tar'));

		$to_import=get_param('to_import',NULL);

		$field_set->attach(form_input_tree_list(do_lang_tempcode('DOWNLOAD'),do_lang_tempcode('DESCRIPTION_DOWNLOAD_OCPORTALCOM'),'url',NULL,'choose_ocportalcom_addon',array(),false,$to_import,false,NULL,true));

		$fields->attach(alternate_fields_set__end($set_name,$set_title,'',$field_set,$required));

		$hidden=new ocp_tempcode();
		handle_max_file_size($hidden);

		$submit_name=do_lang_tempcode('IMPORT_ADDON');

		$post_url=build_url(array('page'=>'_SELF','type'=>'_addon_import','uploading'=>1),'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS'))));

		$text=new ocp_tempcode();
		$text->attach(paragraph(do_lang_tempcode('HELP_IMPORTING_ADDON')));

		require_code('files2');
		$max=floatval(get_max_file_size())/floatval(1024*1024);
		if ($max<30.0)
		{
			$config_url=get_upload_limit_config_url();
			$text->attach(paragraph(do_lang_tempcode(is_null($config_url)?'MAXIMUM_UPLOAD':'MAXIMUM_UPLOAD_STAFF',escape_html(($max>10.0)?integer_format(intval($max)):float_format($max)),escape_html(is_null($config_url)?'':$config_url))));
		}

		return do_template('FORM_SCREEN',array('_GUID'=>'7f50130c5a46e0f6e8a95e936ce7bf47','SKIP_VALIDATION'=>true,'HIDDEN'=>$hidden,'TITLE'=>$title,'SUBMIT_NAME'=>$submit_name,'FIELDS'=>$fields,'TEXT'=>$text,'URL'=>$post_url));
	}

	/**
	 * The UI to retrieve a specified addon.
	 *
	 * @return tempcode		The UI
	 */
	function _addon_import()
	{
		$title=get_screen_title('IMPORT_ADDON');

		require_code('uploads');

		$url_map=array('page'=>'_SELF','type'=>'multi_action');

		$__url=post_param('url','');
		foreach (explode(',',$__url) as $i=>$url)
		{
			if (is_numeric($url))
			{
				$_POST['url']='http://ocportal.com/site/dload.php?id='.$url;
			} else
			{
				$_POST['url']=$url; // In case it was submitted in array form, which is possible on some UAs (based on an automated bug report)
			}

			//if ($url=='')
			//{
				$urls=get_url('url','file','imports/addons',0,0,false,'','',true);
			//}
			//else
			//{
			//	$urls=array($url);
			//}

			$full=get_custom_file_base().'/'.$urls[0];
			if (strtolower(substr($full,-4))!='.tar')
			{
				return warn_screen(get_screen_title('ERROR_OCCURRED'),do_lang_tempcode('ADDON_NOT_TAR'));
			}

			$url_map['install_'.strval($i)]=basename($urls[0]);
		}

		// Show it worked / Refresh
		$_url=build_url($url_map,'_SELF');
		return redirect_screen($title,$_url,do_lang_tempcode('ADDON_IMPORTED'));
	}

	/**
	 * The UI to confirm a combined action on addons.
	 *
	 * @return tempcode		The UI
	 */
	function multi_action()
	{
		$title=get_screen_title('INSTALL_AND_UNINSTALL');

		$warnings=new ocp_tempcode();
		$install_files=new ocp_tempcode();
		$uninstall_files=new ocp_tempcode();

		$installing=array();
		$uninstalling=array();

		$hidden=new ocp_tempcode();

		foreach ($_POST+$_GET as $key=>$passed)
		{
			if (substr($key,0,8)=='install_')
			{
				$installing[]=$passed;
				$hidden->attach(form_input_hidden($key,$passed));
			}

			if (substr($key,0,10)=='uninstall_')
			{
				$uninstalling[]=$passed;
				$hidden->attach(form_input_hidden($key,$passed));
			}
		}

		foreach ($uninstalling as $name)
		{
			list($_warnings,$_files)=inform_about_addon_uninstall($name,$uninstalling);
			$warnings->attach($_warnings);
			$uninstall_files->attach($_files);
		}

		foreach ($installing as $file)
		{
			list($_warnings,$_files,$info)=inform_about_addon_install($file,$uninstalling,$installing);
			$warnings->attach($_warnings);
			$install_files->attach($_files);
		}

		if ((count($installing)==0) && (count($uninstalling)==0))
			warn_exit(do_lang_tempcode('NOTHING_SELECTED'));

		$url=build_url(array('page'=>'_SELF','type'=>'_multi_action'),'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS'))));

		return do_template('ADDON_MULTI_CONFIRM_SCREEN',array('_GUID'=>'bd6b7e012825bb0c873a76a9f4b19cf1','TITLE'=>$title,'HIDDEN'=>$hidden,'URL'=>$url,'INSTALL_FILES'=>$install_files,'UNINSTALL_FILES'=>$uninstall_files,'WARNINGS'=>$warnings));
	}

	/**
	 * The actualiser to perform a combined action on addons.
	 *
	 * @return tempcode		The UI
	 */
	function _multi_action()
	{
		$title=get_screen_title('INSTALL_AND_UNINSTALL');

		if (function_exists('set_time_limit')) @set_time_limit(0);

		require_code('abstract_file_manager');
		force_have_afm_details();

		foreach ($_POST as $key=>$passed)
		{
			if (substr($key,0,8)=='install_')
			{
				install_addon($passed);
			}

			if (substr($key,0,10)=='uninstall_')
			{
				$name=$passed;

				if (
					(!file_exists(get_file_base().'/sources_custom/hooks/systems/addon_registry/'.filter_naughty_harsh($name,true).'.php')) &&
					(!file_exists(get_file_base().'/sources/hooks/systems/addon_registry/'.filter_naughty_harsh($name,true).'.php')) &&
					(is_null($GLOBALS['SITE_DB']->query_value_null_ok('addons','addon_name',array('addon_name'=>$name))))
				)
					continue;

				$addon_row=read_addon_info($name);

				// Archive it off to exports/addons
				if (file_exists(get_file_base().'/sources_custom/hooks/systems/addon_registry/'.$name.'.php') || file_exists(get_file_base().'/sources/hooks/systems/addon_registry/'.$name.'.php')) // New ocProducts style (assumes maintained by ocProducts if it's done like this)
				{
					$file=preg_replace('#^[\_\.\-]#','x',preg_replace('#[^\w\.\-]#','_',$name)).'.tar';
				} else // Traditional ocPortal style
				{
					$file=preg_replace('#^[\_\.\-]#','x',preg_replace('#[^\w\.\-]#','_',$name)).date('-dmY-Hm',time()).'.tar';
				}
				create_addon($file,$addon_row['addon_files'],$addon_row['addon_name'],implode(',',$addon_row['addon_incompatibilities']),implode(',',$addon_row['addon_dependencies']),$addon_row['addon_author'],$addon_row['addon_organisation'],$addon_row['addon_version'],$addon_row['addon_description'],'imports/addons');

				uninstall_addon($name);
			}
		}

		// Clear some cacheing
		require_code('view_modes');
		require_code('zones2');
		require_code('zones3');
		erase_comcode_page_cache();
		erase_tempcode_cache();
		//persistent_cache_delete('OPTIONS');  Done by set_option
		persistent_cache_empty();
		erase_cached_templates();

		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The UI to confirm the install of an addon.
	 *
	 * @return tempcode		The UI
	 */
	function addon_install()
	{
		$title=get_screen_title('INSTALL_ADDON');

		$file=get_param('file');
		list($warnings,$files,$info)=inform_about_addon_install($file);

		$url=build_url(array('page'=>'_SELF','type'=>'_addon_install'),'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS'))));

		$_description=comcode_to_tempcode(str_replace('\n',"\n",$info['description']),$GLOBALS['FORUM_DRIVER']->get_guest_id());
		if ($info['version']=='(version-synched)') $info['version']=float_to_raw_string(ocp_version_number());
		return do_template('ADDON_INSTALL_CONFIRM_SCREEN',array('_GUID'=>'79b8c0e900a498cfb166392163295a07','TITLE'=>$title,'FILE'=>$file,'URL'=>$url,'FILES'=>$files,'WARNINGS'=>$warnings,'NAME'=>$info['name'],'AUTHOR'=>$info['author'],'ORGANISATION'=>$info['organisation'],'VERSION'=>$info['version'],'DESCRIPTION'=>$_description));
	}

	/**
	 * The actualiser to install an addon.
	 *
	 * @return tempcode		The UI
	 */
	function _addon_install()
	{
		$title=get_screen_title('INSTALL_ADDON');

		require_code('abstract_file_manager');
		force_have_afm_details();

		$file=filter_naughty(post_param('file'));

		$theme=mixed();

		$files=array();
		foreach (array_keys($_POST) as $key)
		{
			if (substr($key,0,5)=='file_')
			{
				$value=post_param($key);
				$files[]=$value;

				$matches=array();
				if (preg_match('#^themes/([^/]+)/#',$value,$matches)!=0)
				{
					$theme=$matches[1];
				}
			}
		}

		install_addon($file,$files);

		// Show it worked / Refresh
		if ((!is_null($theme)) && ($theme!='default'))
		{
			$url=build_url(array('page'=>'admin_themes','type'=>'edit_theme','theme'=>$theme),'adminzone');
			return redirect_screen($title,$url,do_lang_tempcode('INSTALL_THEME_SUCCESS'));
		}
		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The UI to uninstall an addon.
	 *
	 * @return tempcode		The UI
	 */
	function addon_uninstall()
	{
		$title=get_screen_title('UNINSTALL_ADDON');

		$name=get_param('name');

		list($warnings,$files)=inform_about_addon_uninstall($name);

		$url=build_url(array('page'=>'_SELF','type'=>'_addon_uninstall'),'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS'))));

		return do_template('ADDON_UNINSTALL_CONFIRM_SCREEN',array('_GUID'=>'fe96098c1f09d091fc10785134803135','TITLE'=>$title,'URL'=>$url,'NAME'=>$name,'WARNINGS'=>$warnings,'FILES'=>$files));
	}

	/**
	 * The UI to uninstall an addon.
	 *
	 * @return tempcode		The UI
	 */
	function _addon_uninstall()
	{
		$title=get_screen_title('UNINSTALL_ADDON');

		require_code('abstract_file_manager');
		force_have_afm_details();

		$name=post_param('name');

		$addon_row=read_addon_info($name);

		// Archive it off to exports/addons
		if (file_exists(get_file_base().'/sources_custom/hooks/systems/addon_registry/'.$name.'.php') || file_exists(get_file_base().'/sources/hooks/systems/addon_registry/'.$name.'.php')) // New ocProducts style (assumes maintained by ocProducts if it's done like this)
		{
			$file=preg_replace('#^[\_\.\-]#','x',preg_replace('#[^\w\.\-]#','_',$name)).'.tar';
		} else // Traditional ocPortal style
		{
			$file=preg_replace('#^[\_\.\-]#','x',preg_replace('#[^\w\.\-]#','_',$name)).date('-dmY-Hm',time()).'.tar';
		}

		$new_addon_files=array();
		foreach ($addon_row['addon_files'] as $_file)
		{
			if (substr($_file,-9)!='.editfrom') // This would have been added back in automatically
				$new_addon_files[]=$_file;
		}

		create_addon($file,$new_addon_files,$addon_row['addon_name'],implode(',',$addon_row['addon_incompatibilities']),implode(',',$addon_row['addon_dependencies']),$addon_row['addon_author'],$addon_row['addon_organisation'],$addon_row['addon_version'],$addon_row['addon_description'],'imports/addons');

		uninstall_addon($name);

		// Clear some cacheing
		require_code('view_modes');
		require_code('zones2');
		require_code('zones3');
		erase_comcode_page_cache();
		erase_tempcode_cache();
		//persistent_cache_delete('OPTIONS');  Done by set_option
		persistent_cache_empty();
		erase_cached_templates();

		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The UI to export an addon (1).
	 *
	 * @return tempcode		The UI
	 */
	function addon_export()
	{
		$title=get_screen_title('EXPORT_ADDON');

		// Lang packs
		$url=build_url(array('page'=>'_SELF','type'=>'_addon_export','exp'=>'lang'),'_SELF');
		$all_langs=find_all_langs();
		ksort($all_langs);
		$i=0;
		$tpl_languages=new ocp_tempcode();
		$_lang_file_map=get_custom_file_base().'/lang_custom/langs.ini';
		if (!file_exists($_lang_file_map))
			$_lang_file_map=get_file_base().'/lang/langs.ini';
		$lang_file_map=better_parse_ini_file($_lang_file_map);
		foreach ($all_langs as $lang=>$dir)
		{
			if ($dir=='lang_custom')
			{
				$files=$this->do_dir($dir.'/'.$lang);
				$frm_langs=new ocp_tempcode();
				$frm_langs->attach(form_input_hidden('lang',$lang));
				foreach (array_keys($files) as $file)
				{
					$frm_langs->attach(form_input_hidden('file_'.strval($i),$file));
					$i++;
				}
				$nice_name=array_key_exists($lang,$lang_file_map)?$lang_file_map[$lang]:$lang;
				$tpl_languages->attach(do_template('ADDON_EXPORT_LINE',array('_GUID'=>'4e2f56799bdb3c4930396315236e2383','NAME'=>$nice_name,'URL'=>$url,'FILES'=>$frm_langs)));
			}
		}

		// Theme packs
		$url=build_url(array('page'=>'_SELF','type'=>'_addon_export','exp'=>'theme'),'_SELF');
		require_code('themes2');
		$all_themes=find_all_themes();
		ksort($all_themes);
		$i=0;
		$tpl_themes=new ocp_tempcode();
		foreach ($all_themes as $theme=>$theme_title)
		{
			if ($theme!='default')
			{
				$files=$this->do_dir('themes/'.$theme);
				$frm_themes=new ocp_tempcode();
				foreach (array_keys($files) as $file)
				{
					$frm_themes->attach(form_input_hidden('file_'.strval($i),$file));
					$i++;
				}
				$tpl_themes->attach(do_template('ADDON_EXPORT_LINE',array('_GUID'=>'9c1dab6d6e6c13b5e01c86c83c3acde1','NAME'=>$theme_title,'URL'=>$url,'FILES'=>$frm_themes)));
			}
		}

		// Files for choice export
		$url=build_url(array('page'=>'_SELF','type'=>'_addon_export','exp'=>'custom'),'_SELF');
		$files=$this->do_dir('');
		ksort($files);
		$frm_files=new ocp_tempcode();
		$i=0;
		foreach (array_keys($files) as $file)
		{
			$frm_files->attach(do_template('ADDON_EXPORT_FILE_CHOICE',array('_GUID'=>'77a91b947259c5e0cc7b5240b24425ca','ID'=>strval($i),'PATH'=>$file)));
			$i++;
		}
		$tpl_files=do_template('ADDON_EXPORT_LINE_CHOICE',array('_GUID'=>'525b161afe5d84268360e960da5e759f','URL'=>$url,'FILES'=>$frm_files));

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS'))));

		return do_template('ADDON_EXPORT_SCREEN',array('_GUID'=>'d89367c0bbc3d6b8bd19f736d9474dfa','TITLE'=>$title,'LANGUAGES'=>$tpl_languages,'FILES'=>$tpl_files,'THEMES'=>$tpl_themes));
	}

	/**
	 * (Recursively) find all files we can choose to export.
	 *
	 * @param  PATH				The directory to search
	 * @return array				A map, path=>1 (inverted list)
	 */
	function do_dir($dir)
	{
		$full=get_file_base().'/'.(($dir=='')?'':($dir.'/'));
		$temp=array();
		$_dir=@opendir($full);
		if ($_dir!==false)
		{
			while (false!==($file=readdir($_dir)))
			{
				if (!should_ignore_file((($dir=='')?'':($dir.'/')).$file,IGNORE_EDITFROM_FILES | IGNORE_REVISION_FILES))
				{
					$temp[$file]=1;
				}
			}
			closedir($_dir);
		}

		$out=array();
		foreach (array_keys($temp) as $file)
		{
			if (is_integer($file)) $file=strval($file);

			if (is_dir($full.$file)) // If there is a custom equiv we don't do it: we only do custom, or indeterminate-custom
			{
				if ((!array_key_exists($file.'_custom',$temp)) || (substr($dir,0,7)=='themes/'))
				{
					$under=$this->do_dir($dir.'/'.$file);
					if ((count($under)!=1) || (!array_key_exists((($dir=='')?'':($dir.'/')).$file.'/index.html',$under)) || (substr($dir,0,7)=='themes/'))
						$out=array_merge($out,$under);
				}
			} else $out[$dir.'/'.$file]=1;
		}

		return $out;
	}

	/**
	 * The UI to export an addon (2).
	 *
	 * @return tempcode		The UI
	 */
	function _addon_export()
	{
		$hidden=build_keep_post_fields();

		$theme=get_param('theme',NULL,true);

		$title=get_screen_title('EXPORT_ADDON');

		// Default meta data
		$name='';
		$author=$GLOBALS['FORUM_DRIVER']->get_username(get_member());
		$organisation=get_site_name();
		$description='';

		// ... but the theme might already define some of this
		if (!is_null($theme))
		{
			$ini_file=(($theme=='default')?get_file_base():get_custom_file_base()).'/themes/'.filter_naughty($theme).'/theme.ini';
			if (file_exists($ini_file))
			{
				$details=better_parse_ini_file($ini_file);
				if (array_key_exists('title',$details)) $name=$details['title'];
				if (array_key_exists('description',$details)) $description=$details['description'];
				if (array_key_exists('author',$details)) $author=$details['author'];
			}
		}
		if (get_param('exp','custom')=='lang')
		{
			$lang=post_param('lang');
			$ini_file=get_custom_file_base().'/lang_custom/langs.ini';
			if (!file_exists($ini_file))
				$ini_file=get_file_base().'/lang/langs.ini';
			if (file_exists($ini_file))
			{
				$details=better_parse_ini_file($ini_file);
				if (array_key_exists($lang,$details))
				{
					$name=$details[$lang];
					$description=$details[$lang];
				}
			}
		}

		$fields=''; /*XHTMLXHTML*/
		require_code('form_templates');
		$field=form_input_line(do_lang_tempcode('NAME'),do_lang_tempcode('DESCRIPTION_NAME'),'name',$name,true);
		$fields.=$field->evaluate();
		$field=form_input_line(do_lang_tempcode('AUTHOR'),do_lang_tempcode('DESCRIPTION_AUTHOR'),'author',$author,true);
		$fields.=$field->evaluate();
		$field=form_input_line(do_lang_tempcode('ORGANISATION'),do_lang_tempcode('DESCRIPTION_ORGANISATION'),'organisation',$organisation,false);
		$fields.=$field->evaluate();
		$field=form_input_line(do_lang_tempcode('_VERSION'),do_lang_tempcode('DESCRIPTION_VERSION'),'version','1.0',true);
		$fields.=$field->evaluate();
		$field=form_input_text(do_lang_tempcode('DESCRIPTION'),do_lang_tempcode('DESCRIPTION_DESCRIPTION'),'description',$description,true);
		$fields.=$field->evaluate();
		$field=form_input_line(do_lang_tempcode('DEPENDENCIES'),do_lang_tempcode('DESCRIPTION_DEPENDENCIES'),'dependencies','',false);
		$fields.=$field->evaluate();
		$field=form_input_line(do_lang_tempcode('INCOMPATIBILITIES'),do_lang_tempcode('DESCRIPTION_INCOMPATIBILITIES'),'incompatibilities','',false);
		$fields.=$field->evaluate();

		if (get_param('exp','custom')=='theme')
		{
			$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_releasing_themes';

			if (!is_null($theme))
			{
				// Option for selecting exactly what files are used
				$field=do_template('FORM_SCREEN_FIELD_SPACER',array('SECTION_HIDDEN'=>true,'TITLE'=>do_lang_tempcode('COUNT_FILES')));
				$fields.=$field->evaluate();
				$files=$this->do_dir('themes/'.$theme);
				$i=0;
				foreach (array_keys($files) as $file)
				{
					$field=form_input_tick(str_replace(array('/','_'),array('/ ','_ '),preg_replace('#^themes/'.str_replace('#','\#',preg_quote($theme)).'/#','',$file)),'','file_'.strval($i),true,NULL,$file);
					$fields.=$field->evaluate();
					$i++;
				}

				// Option for selecting Comcode pages
				require_lang('themes');
				$field=do_template('FORM_SCREEN_FIELD_SPACER',array('SECTION_HIDDEN'=>false,'TITLE'=>do_lang_tempcode('PAGES'),'HELP'=>do_lang_tempcode('THEME_ALSO_INCLUDE_PAGES')));
				$fields.=$field->evaluate();
				$files=$this->do_dir('');
				ksort($files);
				$fields_after='';
				foreach (array_keys($files) as $file)
				{
					if ((substr($file,0,strlen($theme)+2)==$theme.'__'))
						$file=substr($file,strlen($theme)+2);

					if ((substr($file,-4)=='.txt') && (strpos($file,'/comcode_custom/')!==false))
					{
						$matches=array();
						if ((preg_match('#^/((\w+)/)?pages/comcode_custom/[^/]*/(\w+)\.txt$#',$file,$matches)!=0) && ($matches[1]!='docs'.strval(ocp_version())))
						{
							$auto_ticked=false;
							if ($matches[1]=='')
							{
								$auto_ticked=($matches[3]=='start') || (substr($matches[3],0,6)=='panel_');
							}
							$field=form_input_tick($matches[1].': '.$matches[3],'','file_'.strval($i),$auto_ticked,NULL,$file);
							if ($auto_ticked)
								$fields.=$field->evaluate();
							else
								$fields_after.=$field->evaluate();
							$i++;
						}
					}
				}
				$fields.=$fields_after;
			}
		}

		$submit_name=do_lang_tempcode('EXPORT_ADDON');

		$map=array('page'=>'_SELF','type'=>'__addon_export');
		if (!is_null($theme))
		{
			$_redirect=build_url(array('page'=>'admin_themes','type'=>'misc'),'adminzone');
			$redirect=$_redirect->evaluate();
			$map['redirect']=$redirect;
			$map['theme']=$theme;
		}
		$post_url=build_url($map,'_SELF');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS')),array('_SELF:_SELF:misc',do_lang_tempcode('EXPORT_ADDON'))));
		breadcrumb_set_self(do_lang_tempcode('CONFIRM'));

		return do_template('FORM_SCREEN',array('_GUID'=>'dd8bea111b0dfc7df7ddc7e2246f0ef9','HIDDEN'=>$hidden,'TITLE'=>$title,'SUBMIT_NAME'=>$submit_name,'FIELDS'=>$fields,'TEXT'=>'','URL'=>$post_url));
	}

	/**
	 * The actualiser to export an addon.
	 *
	 * @return tempcode		The UI
	 */
	function __addon_export()
	{
		$title=get_screen_title('EXPORT_ADDON');

		$file=preg_replace('#^[\_\.\-]#','x',preg_replace('#[^\w\.\-]#','_',post_param('name'))).date('-dmY-Hm',time()).'.tar';

		$files=array();
		foreach ($_POST as $key=>$val)
		{
			if (!is_string($val)) continue;

			if (get_magic_quotes_gpc()) $val=stripslashes($val);

			if (substr($key,0,5)=='file_')
			{
				$files[]=$val;
			}
		}

		create_addon($file,$files,post_param('name'),post_param('incompatibilities'),post_param('dependencies'),post_param('author'),post_param('organisation'),post_param('version'),post_param('description'));

		$download_url=get_custom_base_url().'/exports/addons/'.$file;

		log_it('EXPORT_ADDON',$file);

		// Show it worked / Refresh
		$_url=build_url(array('page'=>'_SELF','type'=>'misc'),'_SELF');
		$url=$_url->evaluate();
		$url=get_param('redirect',$url);
		return redirect_screen($title,$url,do_lang_tempcode('ADDON_CREATED',escape_html($download_url)));
	}

	/**
	 * The UI to choose a zone (or blocks) to manage.
	 *
	 * @return tempcode		The UI
	 */
	function modules_interface()
	{
		$title=get_screen_title('MODULE_MANAGEMENT');

		require_code('form_templates');
		require_code('zones2');
		require_code('zones3');
		$list=nice_get_zones();
		$list->attach(form_input_list_entry('_block',false,do_lang_tempcode('BLOCKS')));

		$post_url=build_url(array('page'=>'_SELF','type'=>'view'),'_SELF',NULL,false,true);
		$fields=form_input_list(do_lang_tempcode('ZONE_OR_BLOCKS'),'','id',$list,NULL,true);
		$submit_name=do_lang_tempcode('PROCEED');

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS'))));

		return do_template('FORM_SCREEN',array('_GUID'=>'43cc3d9031a3094b62e78461eb99fb5d','GET'=>true,'SKIP_VALIDATION'=>true,'HIDDEN'=>'','TITLE'=>$title,'TEXT'=>do_lang_tempcode('CHOOSE_ZONE_OF_MODULES'),'FIELDS'=>$fields,'URL'=>$post_url,'SUBMIT_NAME'=>$submit_name));
	}

	/**
	 * The UI to manage the modules (or blocks).
	 *
	 * @return tempcode		The UI
	 */
	function modules_view()
	{
		$title=get_screen_title('MODULE_MANAGEMENT');

		$zone=get_param('id');
		$tpl_modules=new ocp_tempcode();

		require_code('templates_columned_table');
		require_code('zones2');

		if ($zone!='_block')
		{
			$module_rows=list_to_map('module_the_name',$GLOBALS['SITE_DB']->query_select('modules',array('*')));
		} else
		{
			$module_rows=list_to_map('block_name',$GLOBALS['SITE_DB']->query_select('blocks',array('*')));
		}

		if ($zone=='_block')
		{
			require_code('zones3');
			$modules=find_all_blocks();
		} else
		{
			$modules=find_all_modules($zone);
		}
		ksort($modules);
		foreach ($modules as $module=>$type)
		{
			if ($zone!='_block')
			{
				$module_path=zone_black_magic_filterer(($zone=='')?('pages/'.$type.'/'.filter_naughty_harsh($module).'.php'):($zone.'/pages/'.$type.'/'.filter_naughty_harsh($module).'.php'),true);
				$prefix='module';
			} else
			{
				$module_path=$type.'/blocks/'.filter_naughty_harsh($module).'.php';
				$prefix='block';
			}

			$info=extract_module_info(get_file_base().'/'.$module_path);
			if (is_null($info)) continue;
			if (get_param_integer('keep_module_dangerous',0)==1) $info['locked']=false;

			$author=$info['author'];
			$organisation=$info['organisation'];
			$version=$info['version'];
			$hacked_by=$info['hacked_by'];
			$hack_version=$info['hack_version'];

			$actions=new ocp_tempcode();
			$status=new ocp_tempcode();

			if (array_key_exists($module,$module_rows))
			{
				$row=$module_rows[$module];
				if (!$info['locked'])
				{
					$hidden=new ocp_tempcode();
					$hidden->attach(form_input_hidden('zone',$zone));
					$hidden->attach(form_input_hidden('module',$module));
					$actions->attach(do_template('COLUMNED_TABLE_ACTION_DELETE_ENTRY',array('_GUID'=>'331afd26f5e62a6a4cdc4e2c520a4114','HIDDEN'=>$hidden,'NAME'=>$module,'URL'=>build_url(array('page'=>'_SELF','type'=>'uninstall'),'_SELF'))));
				}
				if ($row[$prefix.'_version']<$version)
				{
					$status=do_lang_tempcode('STATUS_TO_UPGRADE');
					$hidden=new ocp_tempcode();
					$hidden->attach(form_input_hidden('zone',$zone));
					$hidden->attach(form_input_hidden('module',$module));
					$actions->attach(do_template('COLUMNED_TABLE_ACTION_UPGRADE_ENTRY',array('_GUID'=>'e5d012cb8c839e0e869f1edfa008dacd','HIDDEN'=>$hidden,'NAME'=>$module,'URL'=>build_url(array('page'=>'_SELF','type'=>'upgrade'),'_SELF'))));
				}
				elseif ((!is_null($hack_version)) && ($row[$prefix.'_hack_version']<$hack_version))
				{
					$status=do_lang_tempcode('STATUS_TO_HACK');
					$hidden=new ocp_tempcode();
					$hidden->attach(form_input_hidden('zone',$zone));
					$hidden->attach(form_input_hidden('module',$module));
					$actions->attach(do_template('COLUMNED_TABLE_ACTION_UPGRADE_ENTRY',array('_GUID'=>'42c4473bf31dfd329e921e443ccc2ec3','HIDDEN'=>$hidden,'NAME'=>$module,'URL'=>build_url(array('page'=>'_SELF','type'=>'upgrade'),'_SELF'))));
				} else
				{
					$status=do_lang_tempcode('STATUS_CURRENT');
				}
				if (!$info['locked'])
				{
					$hidden=new ocp_tempcode();
					$hidden->attach(form_input_hidden('zone',$zone));
					$hidden->attach(form_input_hidden('module',$module));
					$actions->attach(do_template('COLUMNED_TABLE_ACTION_REINSTALL_ENTRY',array('_GUID'=>'c2d820af4b9a2f8633f6f5a4e3de76bc','HIDDEN'=>$hidden,'NAME'=>$module,'URL'=>build_url(array('page'=>'_SELF','type'=>'reinstall'),'_SELF'))));
				}
			} else
			{
				$hidden=new ocp_tempcode();
				$hidden->attach(form_input_hidden('zone',$zone));
				$hidden->attach(form_input_hidden('module',$module));
				$actions->attach(do_template('COLUMNED_TABLE_ACTION_INSTALL_ENTRY',array('_GUID'=>'6b438e07cfe154afc21439479fd76978','HIDDEN'=>$hidden,'NAME'=>$module,'URL'=>build_url(array('page'=>'_SELF','type'=>'reinstall'),'_SELF'))));
			}

			if (is_null($hacked_by)) $hacked_by=do_lang_tempcode('NA_EM');
			if (is_null($hack_version)) $hack_version=do_lang_tempcode('NA_EM');
			$tpl_modules->attach(do_template('MODULE_SCREEN_MODULE',array('_GUID'=>'cf19adfd129c44a7ef1d6789002c6535','STATUS'=>$status,'NAME'=>$module,'AUTHOR'=>$author,'ORGANISATION'=>$organisation,'VERSION'=>strval($version),'HACKED_BY'=>$hacked_by,'HACK_VERSION'=>$hack_version,'ACTIONS'=>$actions)));
		}

		breadcrumb_set_parents(array(array('_SELF:_SELF:misc',do_lang_tempcode('ADDONS')),array('_SELF:_SELF:modules',do_lang_tempcode('MODULE_MANAGEMENT'))));

		return do_template('MODULE_SCREEN',array('_GUID'=>'132b23107b49a23e0b11db862de1dd56','TITLE'=>$title,'MODULES'=>$tpl_modules));
	}

	/**
	 * The actualiser to upgrade a module.
	 *
	 * @return tempcode		The UI
	 */
	function upgrade_module()
	{
		$module=post_param('module');
		$zone=post_param('zone');

		require_code('zones2');

		if ($zone!='_block')
			upgrade_module($zone,$module);
		else upgrade_block($module);

		$title=get_screen_title('UPGRADE_MODULE');

		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$zone),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The actualiser to uninstall a module.
	 *
	 * @return tempcode		The UI
	 */
	function uninstall_module()
	{
		$module=post_param('module');
		$zone=post_param('zone');

		require_code('zones2');

		if ($zone!='_block') uninstall_module($zone,$module); else uninstall_block($module);

		$title=get_screen_title('UNINSTALL_MODULE');

		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$zone),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

	/**
	 * The actualiser to reinstall a module.
	 *
	 * @return tempcode		The UI
	 */
	function reinstall_module()
	{
		$module=post_param('module');
		$zone=post_param('zone');

		require_code('zones2');

		if ($zone!='_block') reinstall_module($zone,$module); else reinstall_block($module);

		$title=get_screen_title('REINSTALL_MODULE');

		// Show it worked / Refresh
		$url=build_url(array('page'=>'_SELF','type'=>'view','id'=>$zone),'_SELF');
		return redirect_screen($title,$url,do_lang_tempcode('SUCCESS'));
	}

}


