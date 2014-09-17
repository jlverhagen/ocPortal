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
 * @package		ocf_cpfs
 */

require_code('crud_module');

/**
 * Module page class.
 */
class Module_admin_ocf_customprofilefields extends standard_crud_module
{
	var $lang_type='CUSTOM_PROFILE_FIELD';
	var $select_name='NAME';
	var $menu_label='CUSTOM_PROFILE_FIELDS';
	var $orderer='cf_name';
	var $table='f_custom_fields';
	var $title_is_multi_lang=true;

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @param  boolean	Whether to check permissions.
	 * @param  ?MEMBER	The member to check permissions as (NULL: current user).
	 * @param  boolean	Whether to allow cross links to other modules (identifiable via a full-page-link rather than a screen-name).
	 * @param  boolean	Whether to avoid any entry-point (or even return NULL to disable the page in the Sitemap) if we know another module, or page_group, is going to link to that entry-point. Note that "!" and "misc" entry points are automatically merged with container page nodes (likely called by page-groupings) as appropriate.
	 * @return ?array		A map of entry points (screen-name=>language-code/string or screen-name=>[language-code/string, icon-theme-image]) (NULL: disabled).
	 */
	function get_entry_points($check_perms=true,$member_id=NULL,$support_crosslinks=true,$be_deferential=false)
	{
		if (get_forum_type()!='ocf') return NULL;

		$ret=array();
		if (!$be_deferential)
		{
			$ret+=array(
				'misc'=>array('CUSTOM_PROFILE_FIELDS','menu/adminzone/tools/users/custom_profile_fields'),
			);
		}

		$ret+=array(
			'stats'=>array('CUSTOM_PROFILE_FIELD_STATS','menu/adminzone/tools/users/custom_profile_fields'),
		);

		if (!$be_deferential)
		{
			$ret+=parent::get_entry_points();
		}

		return $ret;
	}

	var $title;

	/**
	 * Standard modular pre-run function, so we know meta-data for <head> before we start streaming output.
	 *
	 * @param  boolean		Whether this is running at the top level, prior to having sub-objects called.
	 * @param  ?ID_TEXT		The screen type to consider for meta-data purposes (NULL: read from environment).
	 * @return ?tempcode		Tempcode indicating some kind of exceptional output (NULL: none).
	 */
	function pre_run($top_level=true,$type=NULL)
	{
		$type=get_param('type','misc');

		require_lang('ocf');
		require_css('ocf_admin');

		set_helper_panel_tutorial('tut_adv_members');

		if ($type=='misc')
		{
			breadcrumb_set_parents(array(array('_SEARCH:admin_ocf_members:misc',do_lang_tempcode('MEMBERS'))));
		}

		if ($type=='stats')
		{
			breadcrumb_set_parents(array());
		}

		if ($type=='_stats')
		{
			breadcrumb_set_parents(array(array('_SEARCH:_SELF:stats',do_lang_tempcode('CUSTOM_PROFILE_FIELD_STATS'))));
		}

		if ($type=='stats' || $type=='_stats')
		{
			$this->title=get_screen_title('CUSTOM_PROFILE_FIELD_STATS');
		}

		return parent::pre_run($top_level);
	}

	/**
	 * Standard crud_module run_start.
	 *
	 * @param  ID_TEXT		The type of module execution
	 * @return tempcode		The output of the run
	 */
	function run_start($type)
	{
		$GLOBALS['NO_DB_SCOPE_CHECK']=true;

		if (get_forum_type()!='ocf') warn_exit(do_lang_tempcode('NO_OCF')); else ocf_require_all_forum_stuff();
		require_code('ocf_members_action');
		require_code('ocf_members_action2');
		require_lang('fields');

		$this->add_one_label=do_lang_tempcode('ADD_CUSTOM_PROFILE_FIELD');
		$this->edit_this_label=do_lang_tempcode('EDIT_THIS_CUSTOM_PROFILE_FIELD');
		$this->edit_one_label=do_lang_tempcode('EDIT_CUSTOM_PROFILE_FIELD');

		if ($type=='misc') return $this->misc();
		if ($type=='stats') return $this->stats();
		if ($type=='_stats') return $this->_stats();
		return new ocp_tempcode();
	}

	/**
	 * The do-next manager for before content management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		require_code('templates_donext');
		return do_next_manager(get_screen_title('CUSTOM_PROFILE_FIELDS'),comcode_lang_string('DOC_CUSTOM_PROFILE_FIELDS'),
			array(
				array('menu/_generic_admin/add_one',array('_SELF',array('type'=>'ad'),'_SELF'),do_lang('ADD_CUSTOM_PROFILE_FIELD')),
				array('menu/_generic_admin/edit_one',array('_SELF',array('type'=>'ed'),'_SELF'),do_lang('EDIT_CUSTOM_PROFILE_FIELD')),
			),
			do_lang('CUSTOM_PROFILE_FIELDS')
		);
	}

	/**
	 * Get tempcode for adding/editing form.
	 *
	 * @param  SHORT_TEXT	The name of the custom profile field
	 * @param  LONG_TEXT		The description of the field
	 * @param  LONG_TEXT		The default value of the field
	 * @param  BINARY			Whether the field is publicly viewable
	 * @param  BINARY			Whether the field may be viewed by the owner
	 * @param  BINARY			Whether the owner may set the value of the field
	 * @param  BINARY			Whether the field is encrypted
	 * @param  ID_TEXT		The type of the field
	 * @set    short_text long_text short_trans long_trans integer upload picture url list tick
	 * @param  BINARY			Whether the field is required to be filled in
	 * @param  BINARY			Whether the field is to be shown on the join form
	 * @param  BINARY			Whether the field is shown in posts
	 * @param  BINARY			Whether the field is shown in post previews
	 * @param  ?integer		The order the field is given relative to the order of the other custom profile fields (NULL: last)
	 * @param  LONG_TEXT  	The usergroups that this field is confined to (comma-separated list).
	 * @param  BINARY			Whether the field is locked
	 * @return array			A pair: the tempcode for the visible fields, and the tempcode for the hidden fields
	 */
	function get_form_fields($name='',$description='',$default='',$public_view=1,$owner_view=1,$owner_set=1,$encrypted=0,$type='long_text',$required=0,$show_on_join_form=0,$show_in_posts=0,$show_in_post_previews=0,$order=NULL,$only_group='',$locked=0)
	{
		$fields=new ocp_tempcode();
		$hidden=new ocp_tempcode();

		require_code('form_templates');
		require_code('encryption');
		require_lang('fields');

		if ($locked==0)
			$fields->attach(form_input_line(do_lang_tempcode('NAME'),do_lang_tempcode('DESCRIPTION_NAME'),'name',$name,true));
		else
			$hidden->attach(form_input_hidden('name',$name));

		$fields->attach(form_input_line_comcode(do_lang_tempcode('DESCRIPTION'),do_lang_tempcode('DESCRIPTION_DESCRIPTION'),'description',$description,false));
		$fields->attach(form_input_line(do_lang_tempcode('DEFAULT_VALUE'),do_lang_tempcode('DESCRIPTION_DEFAULT_VALUE_CPF'),'default',$default,false,NULL,10000));
		$fields->attach(form_input_tick(do_lang_tempcode('OWNER_VIEW'),do_lang_tempcode('DESCRIPTION_OWNER_VIEW'),'owner_view',$owner_view==1));
		$fields->attach(form_input_tick(do_lang_tempcode('OWNER_SET'),do_lang_tempcode('DESCRIPTION_OWNER_SET'),'owner_set',$owner_set==1));
		$fields->attach(form_input_tick(do_lang_tempcode('PUBLIC_VIEW'),do_lang_tempcode('DESCRIPTION_PUBLIC_VIEW'),'public_view',$public_view==1));
		if ((is_encryption_enabled()) && ($name==''))
		{
			require_lang('encryption');
			$fields->attach(form_input_tick(do_lang_tempcode('ENCRYPTED'),do_lang_tempcode('DESCRIPTION_ENCRYPTED'),'encrypted',$encrypted==1));
		}

		require_code('fields');
		$type_list=create_selection_list_field_type($type,$name!='');
		$fields->attach(form_input_list(do_lang_tempcode('TYPE'),do_lang_tempcode('DESCRIPTION_FIELD_TYPE'),'type',$type_list));

		$fields->attach(form_input_tick(do_lang_tempcode('REQUIRED'),do_lang_tempcode('DESCRIPTION_REQUIRED'),'required',$required==1));
		$fields->attach(form_input_tick(do_lang_tempcode('SHOW_ON_JOIN_FORM'),do_lang_tempcode('DESCRIPTION_SHOW_ON_JOIN_FORM'),'show_on_join_form',$show_on_join_form==1));
		$orderlist=new ocp_tempcode();
		$num_cpfs=$GLOBALS['FORUM_DB']->query_select_value('f_custom_fields','COUNT(*)');
		if ($name=='') $num_cpfs++;
		$selected_one=false;
		for ($i=0;$i<(is_null($order)?$num_cpfs:max($num_cpfs,$order));$i++)
		{
			$selected=(($i===$order) || (($name=='') && ($i==$num_cpfs-1)));
			if ($selected) $selected_one=true;
			$orderlist->attach(form_input_list_entry(strval($i),$selected,integer_format($i+1)));
		}
		if (!$selected_one)
		{
			$orderlist->attach(form_input_list_entry(strval($order),true,integer_format($order+1)));
		}
		$fields->attach(form_input_list(do_lang_tempcode('ORDER'),do_lang_tempcode('DESCRIPTION_FIELD_ORDER'),'order',$orderlist));
		$fields->attach(form_input_tick(do_lang_tempcode('SHOW_IN_POSTS'),do_lang_tempcode('DESCRIPTION_SHOW_IN_POSTS'),'show_in_posts',$show_in_posts==1));
		$fields->attach(form_input_tick(do_lang_tempcode('SHOW_IN_POST_PREVIEWS'),do_lang_tempcode('DESCRIPTION_SHOW_IN_POST_PREVIEWS'),'show_in_post_previews',$show_in_post_previews==1));
		$rows=$GLOBALS['FORUM_DB']->query_select('f_groups',array('id','g_name','g_is_super_admin'),array('g_is_private_club'=>0));
		if ($locked==0)
		{
			$groups=new ocp_tempcode();
			//$groups=form_input_list_entry('-1',false,do_lang_tempcode('_ALL'));
			foreach ($rows as $group)
			{
				if ($group['id']!=db_get_first_id())
					$groups->attach(form_input_list_entry(strval($group['id']),count(array_intersect(array($group['id']),explode(',',$only_group)))!=0,get_translated_text($group['g_name'],$GLOBALS['FORUM_DB'])));
			}
			$fields->attach(form_input_multi_list(do_lang_tempcode('GROUP'),do_lang_tempcode('DESCRIPTION_FIELD_ONLY_GROUP'),'only_group',$groups));
		} else
		{
			$hidden->attach(form_input_hidden('only_group',''));
		}

		return array($fields,$hidden);
	}

	/**
	 * Standard crud_module table function.
	 *
	 * @param  array			Details to go to build_url for link to the next screen.
	 * @return array			A pair: The choose table, Whether re-ordering is supported from this screen.
	 */
	function create_selection_list_choose_table($url_map)
	{
		require_code('templates_results_table');
		$form_id='selection_table';
		$current_ordering=get_param('sort','cf_order ASC');
		if (strpos($current_ordering,' ')===false) warn_exit(do_lang_tempcode('INTERNAL_ERROR'));
		list($sortable,$sort_order)=explode(' ',$current_ordering,2);
		$sortables=array(
			'cf_name'=>do_lang_tempcode('NAME'),
			'cf_owner_view'=>do_lang_tempcode('OWNER_VIEW'),
			'cf_owner_set'=>do_lang_tempcode('OWNER_SET'),
			'cf_public_view'=>do_lang_tempcode('PUBLIC_VIEW'),
			'cf_required'=>do_lang_tempcode('REQUIRED'),
			'cf_order'=>do_lang_tempcode('ORDER'),
		);
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');

		$fh=array(
			do_lang_tempcode('NAME'),
			do_lang_tempcode('OWNER_VIEW'),
			do_lang_tempcode('OWNER_SET'),
			do_lang_tempcode('PUBLIC_VIEW'),
			do_lang_tempcode('REQUIRED'),
		);
		$fh[]=do_lang_tempcode('SHOW_ON_JOIN_FORM');
		//$fh[]=do_lang_tempcode('SHOW_IN_POSTS');	Save space
		//$fh[]=do_lang_tempcode('SHOW_IN_POST_PREVIEWS');
		$fh[]=do_lang_tempcode('ORDER');
		$fh[]=do_lang_tempcode('ACTIONS');
		$header_row=results_field_title($fh,$sortables,'sort',$sortable.' '.$sort_order);

		// Load up filters
		$hooks=find_all_hooks('systems','ocf_cpf_filter');
		$to_keep=array();
		foreach (array_keys($hooks) as $hook)
		{
			require_code('hooks/systems/ocf_cpf_filter/'.$hook);
			$_hook=object_factory('Hook_ocf_cpf_filter_'.$hook,true);
			if (is_null($_hook)) continue;
			$to_keep+=$_hook->to_enable();
		}
		$record_start=get_param_integer('start',0);
		$record_max=get_param_integer('max',20);
		$record_counter=$record_start;
		$fields=new ocp_tempcode();
		list($rows,$max_rows)=$this->get_entry_rows(false,$current_ordering,NULL);
		$changed=false;
		//sorting code starts from here//
		if (strtoupper($sort_order)!='DESC')
		{
			for ($record_counter=$record_start;$record_counter<$record_start+$record_max;$record_counter++)
			{
				$new_order_value=post_param_integer('order_'.strval($record_counter),NULL);
				if ($new_order_value!==$record_counter && !is_null($new_order_value))
				{
					$this->change_order($new_order_value,$record_counter,$current_ordering);
					$changed=true;
				}
			}
		} else
		{
			if (!$record_start)
			{
				$record_start=$max_rows;
			} else
			{
				$record_start=$max_rows-$record_start;
			}
			$record_max=$record_start-$record_max;
			for ($record_counter=$record_max;$record_counter<=$record_start;$record_counter++)
			{
				$new_order_value=post_param_integer('order_'.strval($record_counter),NULL);
				if ($new_order_value!==$record_counter && !is_null($new_order_value))
				{
					$this->change_order($new_order_value,$record_counter,$current_ordering);
					$changed=true;
				}
			}
		}
		//sorting code ends here//
		if ($changed)
		{
			list($rows,$max_rows)=$this->get_entry_rows(false,$current_ordering);
		}

		require_code('form_templates');
		foreach ($rows as $row)
		{
			$trans=get_translated_text($row['cf_name'],$GLOBALS['FORUM_DB']);

			$used=true;
			if ((substr($trans,0,4)=='ocp_') && (get_param_integer('keep_all_cpfs',0)!=1))
			{
				// See if it gets filtered
				if (!array_key_exists(substr($trans,4),$to_keep)) $used=false;

				$test=do_lang('SPECIAL_CPF__'.$trans,NULL,NULL,NULL,NULL,false);
				if (!is_null($test)) $trans=$test;
			}

			$edit_link=build_url($url_map+array('id'=>$row['id']),'_SELF');

			$orderlist=new ocp_tempcode();
			$num_cpfs=$GLOBALS['FORUM_DB']->query_select_value('f_custom_fields','COUNT(*)');
			$selected_one=false;
			$order=$row['cf_order'];
			for ($i=0;$i<max($num_cpfs,$order);$i++)
			{
				$selected=($i===$order);
				if ($selected) $selected_one=true;
				$orderlist->attach(form_input_list_entry(strval($i),$selected,integer_format($i+1)));
			}
			if (!$selected_one)
			{
				$orderlist->attach(form_input_list_entry(strval($order),true,integer_format($order+1)));
			}
			$orderer=do_template('COLUMNED_TABLE_ROW_CELL_SELECT',array('_GUID'=>'0c35279246e34d94fd4a41c432cdffed','LABEL'=>do_lang_tempcode('ORDER'),'NAME'=>'order_'.strval($row['cf_order']),'LIST'=>$orderlist));

			$fr=array();
			$fr[]=$trans;
			$fr[]=($row['cf_owner_view']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			$fr[]=($row['cf_owner_set']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			$fr[]=($row['cf_public_view']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			$fr[]=($row['cf_required']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			$fr[]=($row['cf_show_on_join_form']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			//$fr[]=($row['cf_show_in_posts']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			//$fr[]=($row['cf_show_in_post_previews']==1)?do_lang_tempcode('YES'):do_lang_tempcode('NO');
			$fr[]=protect_from_escaping($orderer);
			if ($used)
			{
				$edit_link=hyperlink($edit_link,do_lang_tempcode('EDIT'),false,true,do_lang('EDIT').' #'.strval($row['id']));
			} else
			{
				$edit_link=do_lang_tempcode('UNUSED_CPF');
			}
			$fr[]=protect_from_escaping($edit_link);

			$fields->attach(results_entry($fr,true));
		}
		$this->javascript_for_choose='
			var select_elements=document.getElementById(\''.$this->form_id.'\').getElementsByTagName(\'select\');
			var select_submit=document.getElementById(\'selection_submit\');
			var select_element_length=select_elements.length;
			for (var counter=0;counter<select_element_length;counter++)
			{
				select_elements[counter].onchange=change_handler;
				function change_handler()
				{
					this.form.submit();
				}
			}
			select_submit.style.display=\'none\';
		';
		return array(results_table(do_lang($this->menu_label),get_param_integer('start',0),'start',either_param_integer('max',20),'max',$max_rows,$header_row,$fields,$sortables,$sortable,$sort_order,'sort',NULL,NULL,NULL,8,'gdfg43tfdgdfgdrfgd',true),true);
	}

	/**
	 * Method to change the order of custom profile fields.
	 * @param  integer		Record start count.
	 * @param  integer		Record end count.
	 */
	function change_order($start_order=0, $end_order=0)
	{
		$rows=null;
		$row_cf_order=0;
		$table=get_table_prefix().$this->table;
		$db=((substr($table,0,2)=='f_') && (get_forum_type()!='none'))?$GLOBALS['FORUM_DB']:$GLOBALS['SITE_DB'];
		if ($start_order>$end_order)
		{
			$rows=$db->_query('SELECT * FROM '.$table.' WHERE cf_order BETWEEN '.strval($end_order).' AND '.strval($start_order).' ORDER BY cf_order ASC');
			foreach ($rows as $row)
			{
				$row_cf_order=$row['cf_order'];
				if ($row_cf_order==$end_order)
				{
					$GLOBALS['FORUM_DB']->query_update('f_custom_fields',array('cf_order'=>$start_order),array('id'=>$row['id']),'',1);	
				} else
				{
					$GLOBALS['FORUM_DB']->query_update('f_custom_fields',array('cf_order'=>--$row_cf_order),array('id'=>$row['id']),'',1);	
				}
			}
		} else
		{
			$rows=$db->_query('SELECT * FROM '.$table.' WHERE cf_order BETWEEN '.strval($start_order).' AND '.strval($end_order).' ORDER BY cf_order ASC');
			foreach ($rows as $row)
			{
				$row_cf_order=$row['cf_order'];
				if ($row_cf_order==$end_order)
				{
					$GLOBALS['FORUM_DB']->query_update('f_custom_fields',array('cf_order'=>$start_order),array('id'=>$row['id']),'',1);	
				} else
				{
					$GLOBALS['FORUM_DB']->query_update('f_custom_fields',array('cf_order'=>++$row_cf_order),array('id'=>$row['id']),'',1);	
				}
			}
		}
		return true;
	}

	/**
	 * Standard crud_module delete possibility checker.
	 *
	 * @param  ID_TEXT		The entry being potentially deleted
	 * @return boolean		Whether it may be deleted
	 */
	function may_delete_this($_id)
	{
		$id=intval($_id);
		$locked=$GLOBALS['FORUM_DB']->query_select_value('f_custom_fields','cf_locked',array('id'=>$id));
		return ($locked==0);
	}

	/**
	 * Standard crud_module edit form filler.
	 *
	 * @param  ID_TEXT		The entry being edited
	 * @return array			A pair: the tempcode for the visible fields, and the tempcode for the hidden fields
	 */
	function fill_in_edit_form($id)
	{
		$rows=$GLOBALS['FORUM_DB']->query_select('f_custom_fields',array('*'),array('id'=>intval($id)));
		if (!array_key_exists(0,$rows))
		{
			warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		}
		$myrow=$rows[0];

		$name=get_translated_text($myrow['cf_name'],$GLOBALS['FORUM_DB']);
		$description=get_translated_text($myrow['cf_description'],$GLOBALS['FORUM_DB']);
		$default=$myrow['cf_default'];
		require_code('encryption');
		$encrypted=(($myrow['cf_encrypted']==1) && (is_encryption_enabled()));
		$public_view=(($myrow['cf_public_view']==1) && (!$encrypted))?1:0;
		$owner_view=$myrow['cf_owner_view'];
		$owner_set=$myrow['cf_owner_set'];
		$type=$myrow['cf_type'];
		$required=$myrow['cf_required'];
		$show_in_posts=$myrow['cf_show_in_posts'];
		$show_in_post_previews=$myrow['cf_show_in_post_previews'];
		$order=$myrow['cf_order'];
		$only_group=$myrow['cf_only_group'];
		if (!array_key_exists('cf_show_on_join_form',$myrow))
		{
			$GLOBALS['FORUM_DB']->add_table_field('f_custom_fields','cf_show_on_join_form','BINARY',0);
			$GLOBALS['FORUM_DB']->query('UPDATE '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_custom_fields SET cf_show_on_join_form=cf_required');
			$rows=$GLOBALS['FORUM_DB']->query_select('f_custom_fields',array('*'),array('id'=>intval($id)));
			$myrow=$rows[0];
		}
		$show_on_join_form=$myrow['cf_show_on_join_form'];

		return $this->get_form_fields($name,$description,$default,$public_view,$owner_view,$owner_set,$encrypted,$type,$required,$show_on_join_form,$show_in_posts,$show_in_post_previews,$order,$only_group,$myrow['cf_locked']);
	}

	/**
	 * Standard crud_module add actualiser.
	 *
	 * @return ID_TEXT		The entry added
	 */
	function add_actualisation()
	{
		$only_group=array_key_exists('only_group',$_POST)?(is_array($_POST['only_group'])?implode(',',$_POST['only_group']):post_param('only_group')):'';
		$id=ocf_make_custom_field(post_param('name'),0,post_param('description'),post_param('default'),
										post_param_integer('public_view',0),post_param_integer('owner_view',0),post_param_integer('owner_set',0),post_param_integer('encrypted',0),
										post_param('type'),post_param_integer('required',0),post_param_integer('show_in_posts',0),post_param_integer('show_in_post_previews',0),post_param_integer('order'),$only_group,false,post_param_integer('show_on_join_form',0));
		return strval($id);
	}

	/**
	 * Standard crud_module edit actualiser.
	 *
	 * @param  ID_TEXT		The entry being edited
	 */
	function edit_actualisation($id)
	{
		$only_group=array_key_exists('only_group',$_POST)?(is_array($_POST['only_group'])?implode(',',$_POST['only_group']):post_param('only_group')):'';
		ocf_edit_custom_field(intval($id),post_param('name'),post_param('description'),post_param('default'),post_param_integer('public_view',0),post_param_integer('owner_view',0),post_param_integer('owner_set',0),post_param_integer('encrypted',0),post_param_integer('required',0),post_param_integer('show_in_posts',0),post_param_integer('show_in_post_previews',0),post_param_integer('order'),$only_group,post_param('type'),post_param_integer('show_on_join_form',0));
	}

	/**
	 * Standard crud_module delete actualiser.
	 *
	 * @param  ID_TEXT		The entry being deleted
	 */
	function delete_actualisation($id)
	{
		ocf_delete_custom_field(intval($id));
	}

	/**
	 * Show value statistics for a custom profile field (choose).
	 *
	 * @return tempcode		The UI
	 */
	function stats()
	{
		$fields=new ocp_tempcode();

		$rows=$GLOBALS['FORUM_DB']->query_select('f_custom_fields',array('id','cf_name','cf_type'));

		require_code('form_templates');
		require_code('fields');

		$list=new ocp_tempcode();
		$_list=array();
		foreach ($rows as $row)
		{
			$ob=get_fields_hook($row['cf_type']);
			list(,,$storage_type)=$ob->get_field_value_row_bits(NULL);

			if (strpos($storage_type,'_trans')===false)
			{
				$id=$row['id'];
				$text=get_translated_text($row['cf_name'],$GLOBALS['FORUM_DB']);
				$_list[$id]=$text;
			}
		}
		asort($_list);
		foreach ($_list as $id=>$text)
		{
			$list->attach(form_input_list_entry(strval($id),false,$text));
		}
		if ($list->is_empty())
		{
			return inform_screen($this->title,do_lang_tempcode('NO_ENTRIES'));
		}

		require_lang('dates');
		$fields->attach(form_input_list(do_lang_tempcode('NAME'),'','id',$list));
		$fields->attach(form_input_date(do_lang_tempcode('FROM'),do_lang_tempcode('DESCRIPTION_MEMBERS_JOINED_FROM'),'start',true,false,false,time()-60*60*24*30,10,intval(date('Y'))-10));
		$fields->attach(form_input_date(do_lang_tempcode('TO'),do_lang_tempcode('DESCRIPTION_MEMBERS_JOINED_TO'),'end',true,false,false,time(),10,intval(date('Y'))-10));

		$post_url=build_url(array('page'=>'_SELF','type'=>'_stats'),'_SELF',NULL,false,true);
		$submit_name=do_lang_tempcode('CUSTOM_PROFILE_FIELD_STATS');

		return do_template('FORM_SCREEN',array('_GUID'=>'393bac2180c9e135ae9c31565ddf7761','GET'=>true,'SKIP_VALIDATION'=>true,'TITLE'=>$this->title,'HIDDEN'=>'','FIELDS'=>$fields,'TEXT'=>'','URL'=>$post_url,'SUBMIT_ICON'=>'buttons__proceed','SUBMIT_NAME'=>$submit_name));
	}

	/**
	 * Show value statistics for a custom profile field (show).
	 *
	 * @return tempcode		The statistics
	 */
	function _stats()
	{
		$f_name='field_'.strval(get_param_integer('id'));
		$_a=get_input_date('start');
		$a=is_null($_a)?'1=1':('m_join_time>'.strval($_a));
		$_b=get_input_date('end');
		$b=is_null($_b)?'1=1':('m_join_time<'.strval($_b));
		$members_in_range=$GLOBALS['FORUM_DB']->query('SELECT '.$f_name.',COUNT('.$f_name.') AS cnt FROM '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_members m LEFT JOIN '.$GLOBALS['FORUM_DB']->get_table_prefix().'f_member_custom_fields f ON m.id=f.mf_member_id WHERE '.$a.' AND '.$b.' GROUP BY '.$f_name.' ORDER BY cnt',300/*reasonable limit*/);
		if (count($members_in_range)==300) attach_message(do_lang_tempcode('TOO_MUCH_CHOOSE__TOP_ONLY',escape_html(integer_format(300))),'warn');
		$lines=new ocp_tempcode();
		foreach ($members_in_range as $row)
		{
			if (!is_null($row[$f_name]))
			{
				$val=$row[$f_name];
				$lines->attach(do_template('OCF_CPF_STATS_LINE',array('_GUID'=>'874c9b780f625ff4566e7b909f554288','CNT'=>integer_format($row['cnt']),'VAL'=>is_integer($val)?integer_format($val):$val)));
			}
		}
		if ($lines->is_empty()) warn_exit(do_lang_tempcode('NO_DATA'));

		return do_template('OCF_CPF_STATS_SCREEN',array('_GUID'=>'bb7be7acf936cd008e16bd515f7f39ac','TITLE'=>$this->title,'STATS'=>$lines));
	}
}


