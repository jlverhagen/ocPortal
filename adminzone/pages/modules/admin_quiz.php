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
 * @package		quizzes
 */

/**
 * Module page class.
 */
class Module_admin_quiz
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
	 * Standard modular run function.
	 *
	 * @return tempcode	The result of execution.
	 */
	function run()
	{
		$GLOBALS['HELPER_PANEL_PIC']='pagepics/quiz';
		$GLOBALS['HELPER_PANEL_TUTORIAL']='tut_quizzes';

		require_lang('quiz');
		require_code('quiz');

		$type=get_param('type','misc');

		if ($type=='misc') return $this->misc();
		if ($type=='find_winner') return $this->find_winner();
		if ($type=='_find_winner') return $this->_find_winner();
		if ($type=='survey_results') return $this->survey_results();
		if ($type=='_survey_results') return $this->_survey_results();
		if ($type=='__survey_results') return $this->__survey_results();
		if ($type=='export') return $this->export_quiz();	
		if ($type=='_export') return $this->_export_quiz();

		return new ocp_tempcode();
	}

	/**
	 * Standard modular entry-point finder function.
	 *
	 * @return ?array	A map of entry points (type-code=>language-code) (NULL: disabled).
	 */
	function get_entry_points()
	{
		return array('misc'=>'MANAGE_QUIZZES','find_winner'=>'FIND_WINNER','survey_results'=>'SURVEY_RESULTS','export'=>'EXPORT_QUIZ');
	}

	/**
	 * The do-next manager for before setup management.
	 *
	 * @return tempcode		The UI
	 */
	function misc()
	{
		require_lang('quiz');

		require_lang('menus');
		$also_url=build_url(array('page'=>'cms_quiz'),get_module_zone('cms_quiz'));
		attach_message(do_lang_tempcode('ALSO_SEE_CMS',escape_html($also_url->evaluate())),'inform');

		require_code('templates_donext');
		return do_next_manager(get_screen_title('MANAGE_QUIZZES'),comcode_lang_string('DOC_QUIZZES'),
					array(
						/*	 type							  page	 params													 zone	  */
						array('findwinners',array('_SELF',array('type'=>'find_winner'),'_SELF'),do_lang('FIND_WINNERS')),
						array('survey_results',array('_SELF',array('type'=>'survey_results'),'_SELF'),do_lang('SURVEY_RESULTS')),
						array('export',array('_SELF',array('type'=>'export'),'_SELF'),do_lang('EXPORT_QUIZ')),
					),
					do_lang('MANAGE_QUIZZES')
		);
	}

	/**
	 * Standard aed_module list function.
	 *
	 * @return tempcode		The selection list
	 */
	function nice_get_entries()
	{
		require_code('form_templates');

		$_m=$GLOBALS['SITE_DB']->query_select('quizzes',array('id','q_name'),NULL,'ORDER BY q_add_date DESC',300);
		$entries=new ocp_tempcode();
		foreach ($_m as $m)
		{
			$entries->attach(form_input_list_entry(strval($m['id']),false,get_translated_text($m['q_name'])));
		}

		return $entries;
	}

	/**
	 * Standard aed_module delete actualiser.
	 *
	 * @return tempcode		The UI
	 */
	function export_quiz()
	{
		$title=get_screen_title('EXPORT_QUIZ');

		$fields=new ocp_tempcode();
		$quiz_list=$this->nice_get_entries();

		//Add all cal option
		//$quiz_list->attach(form_input_list_entry('0',true,do_lang_tempcode('ALL_QUIZZES')));

		$fields->attach(form_input_list(do_lang_tempcode('QUIZZES_EXPORT'),do_lang_tempcode('DESCRIPTION_QUIZZES_EXPORT'),'quiz_id',$quiz_list));

		$post_url=build_url(array('page'=>'_SELF','type'=>'_export'),'_SELF');
		$submit_name=do_lang_tempcode('EXPORT_QUIZ');

		return do_template('FORM_SCREEN',array('_GUID'=>'3110ee0e917e2e0f83a41ab27ec7eafe','TITLE'=>$title,'TEXT'=>do_lang_tempcode('EXPORT_QUIZ_TEXT'),'HIDDEN'=>'','FIELDS'=>$fields,'SUBMIT_NAME'=>$submit_name,'URL'=>$post_url,'POST'=>true));
	}

	/**
	 * Standard aed_module delete actualiser.
	 */
	function _export_quiz()
	{
		require_code('files2');
		$quiz_id=post_param_integer('quiz_id');
		$data=get_quiz_data_for_csv($quiz_id);
		make_csv($data,'quiz.csv');
	}

	/**
	 * UI: find quiz winner.
	 *
	 * @return tempcode	The result of execution.
	 */
	function find_winner()
	{
		$title=get_screen_title('FIND_WINNERS');

		require_code('form_templates');

		$_m=$GLOBALS['SITE_DB']->query('SELECT * FROM '.$GLOBALS['SITE_DB']->get_table_prefix().'quizzes WHERE '.db_string_equal_to('q_type','COMPETITION').' ORDER BY q_validated DESC,q_add_date DESC',300);
		$entries=new ocp_tempcode();
		foreach ($_m as $m)
		{
			$entries->attach(form_input_list_entry(strval($m['id']),false,get_translated_text($m['q_name'])));
		}
		if ($entries->is_empty()) inform_exit(do_lang_tempcode('NO_ENTRIES'));

		$fields=new ocp_tempcode();
		$fields->attach(form_input_list(do_lang_tempcode('QUIZ'),'','id',$entries,NULL,true));

		$post_url=build_url(array('page'=>'_SELF','type'=>'_find_winner'),'_SELF');
		$submit_name=do_lang_tempcode('CHOOSE_WINNERS');
		$text=do_lang_tempcode('CHOOSE_WINNERS');

		breadcrumb_set_self(do_lang_tempcode('CHOOSE'));
		breadcrumb_set_parents(array(array('_SELF:_SELF',do_lang_tempcode('MANAGE_QUIZZES'))));

		return do_template('FORM_SCREEN',array('HIDDEN'=>'','SKIP_VALIDATION'=>true,'TITLE'=>$title,'TEXT'=>$text,'URL'=>$post_url,'FIELDS'=>$fields,'SUBMIT_NAME'=>$submit_name));
	}

	/**
	 * Actualiser: find quiz winner.
	 *
	 * @return tempcode	The result of execution.
	 */
	function _find_winner()
	{
		$id=post_param_integer('id');

		// Test to see if we have not yet chosen winners
		$winners=$GLOBALS['SITE_DB']->query_select('quiz_winner',array('q_entry'),array('q_quiz'=>$id));
		if (!array_key_exists(0,$winners))
		{
			// Close competition
			$close_time=$GLOBALS['SITE_DB']->query_value('quizzes','q_close_time',array('id'=>$id));
			if (is_null($close_time))
			{
				$GLOBALS['SITE_DB']->query_update('quizzes',array('q_close_time'=>time()),array('id'=>$id),'',1);
			}

			// Choose all entries
			$entries=$GLOBALS['SITE_DB']->query('SELECT id,q_member,q_results FROM '.get_table_prefix().'quiz_entries WHERE q_quiz='.strval($id).' AND q_member<>'.strval($GLOBALS['FORUM_DRIVER']->get_guest_id()).' ORDER BY q_results DESC');

			// Choose the maximum number of rows we'll need who could potentially win
			$num_winners=$GLOBALS['SITE_DB']->query_value('quizzes','q_num_winners',array('id'=>$id));
			if ($num_winners==0) $num_winners=3; // Having 0 helps nobody, and having more than 0 if zero set hurts nobody
			if ($num_winners<0) inform_exit(do_lang_tempcode('NO_ENTRIES'));
			if ($num_winners>=count($entries)) $min=0; else $min=$entries[$num_winners]['q_results'];
			$filtered_entries=array();
			foreach ($entries as $entry)
			{
				if ($entry['q_results']>=$min)
				{
					if (!array_key_exists($entry['q_results'],$filtered_entries)) $filtered_entries[$entry['q_results']]=array();

					// Shuffle around this level
					$temp=$filtered_entries[$entry['q_results']];
					$temp[]=$entry;
					shuffle($temp);
					$filtered_entries[$entry['q_results']]=$temp;
				}
			}

			if (count($filtered_entries)==0)
			{
				warn_exit(do_lang_tempcode('NO_POSSIBLE_WINNERS'));
			}

			// Pick winners: store
			for ($i=0;$i<$num_winners;$i++)
			{
				$k=array_keys($filtered_entries);
				rsort($k);
				$temp=$filtered_entries[$k[0]];
				$_entry=array_shift($temp);
				if (!is_null($_entry))
				{
					$filtered_entries[$k[0]]=$temp;
					$winners[]=array('q_entry'=>$_entry['id']);

					$GLOBALS['SITE_DB']->query_insert('quiz_winner',array(
						'q_quiz'=>$id,
						'q_entry'=>$_entry['id'],
						'q_winner_level'=>$i
					));
				} else break;
			}
		}

		$_winners=new ocp_tempcode();
		foreach ($winners as $i=>$winner)
		{
			$member_id=$GLOBALS['SITE_DB']->query_value('quiz_entries','q_member',array('id'=>$winner['q_entry']));
			$url=$GLOBALS['FORUM_DRIVER']->member_profile_url($member_id,false,true);
			switch ($i)
			{
				case 0:
					$name=do_lang_tempcode('WINNER_FIRST',integer_format($i+1),$GLOBALS['FORUM_DRIVER']->get_username($member_id));
					break;
				case 1:
					$name=do_lang_tempcode('WINNER_SECOND',integer_format($i+1),$GLOBALS['FORUM_DRIVER']->get_username($member_id));
					break;
				case 2:
					$name=do_lang_tempcode('WINNER_THIRD',integer_format($i+1),$GLOBALS['FORUM_DRIVER']->get_username($member_id));
					break;
				default:
					$name=do_lang_tempcode('WINNER',integer_format($i+1),$GLOBALS['FORUM_DRIVER']->get_username($member_id));
					break;
			}
			$_winners->attach(do_template('INDEX_SCREEN_ENTRY',array('_GUID'=>'85f558c8dc99b027dbf4de821de0e419','URL'=>$url,'NAME'=>$name,'TARGET'=>'_blank')));
		}

		breadcrumb_set_parents(array(array('_SELF:_SELF',do_lang_tempcode('MANAGE_QUIZZES')),array('_SELF:_SELF:find_winner',do_lang_tempcode('CHOOSE'))));

		// Show the winners
		$title=get_screen_title('FIND_WINNERS');
		return do_template('INDEX_SCREEN',array('_GUID'=>'d427ec7300a325ee4f00020ea59468e2','TITLE'=>$title,'CONTENT'=>$_winners,'PRE'=>do_lang_tempcode('WINNERS_FOUND_AS_FOLLOWS'),'POST'=>''));
	}

	/**
	 * Choose survey to view results of.
	 *
	 * @return tempcode	The result of execution.
	 */
	function survey_results()
	{
		$title=get_screen_title('SURVEY_RESULTS');

		$GLOBALS['HELPER_PANEL_PIC']='pagepics/survey_results';

		require_code('form_templates');

		$_m=$GLOBALS['SITE_DB']->query_select('quizzes',array('*'),array('q_type'=>'SURVEY'),'ORDER BY q_validated DESC,q_add_date DESC',300);
		$entries=new ocp_tempcode();
		foreach ($_m as $m)
		{
			$entries->attach(form_input_list_entry(strval($m['id']),false,get_translated_text($m['q_name'])));
		}
		if ($entries->is_empty()) inform_exit(do_lang_tempcode('NO_ENTRIES'));

		$fields=new ocp_tempcode();
		$fields->attach(form_input_list(do_lang_tempcode('SURVEY'),'','id',$entries,NULL,true));

		$post_url=build_url(array('page'=>'_SELF','type'=>'_survey_results'),'_SELF',NULL,false,true);
		$submit_name=do_lang_tempcode('SURVEY_RESULTS');

		breadcrumb_set_self(do_lang_tempcode('CHOOSE'));

		return do_template('FORM_SCREEN',array('SKIP_VALIDATION'=>true,'HIDDEN'=>'','GET'=>true,'TITLE'=>$title,'TEXT'=>'','URL'=>$post_url,'FIELDS'=>$fields,'SUBMIT_NAME'=>$submit_name));
	}

	/**
	 * View survey results.
	 *
	 * @return tempcode	The result of execution.
	 */
	function _survey_results()
	{
		$title=get_screen_title('SURVEY_RESULTS');

		breadcrumb_set_parents(array(array('_SELF:_SELF',do_lang_tempcode('MANAGE_QUIZZES'))));

		$GLOBALS['HELPER_PANEL_PIC']='pagepics/survey_results';

		$id=get_param_integer('id'); // quiz ID

		$fields=new ocp_tempcode();

		require_code('templates_results_table');
		require_code('templates_map_table');

		// Show summary
		$question_rows=$GLOBALS['SITE_DB']->query_select('quiz_questions',array('*'),array('q_quiz'=>$id),'ORDER BY id');
		foreach ($question_rows as $q)
		{
			$question=get_translated_text($q['q_question_text']);

			$answers=new ocp_tempcode();
			$answer_rows=$GLOBALS['SITE_DB']->query_select('quiz_question_answers',array('*'),array('q_question'=>$q['id']),'ORDER BY id');
			$all_answers=array();
			foreach ($answer_rows as $i=>$a)
			{
				$answer=get_translated_text($a['q_answer_text']);
				$count=$GLOBALS['SITE_DB']->query_value('quiz_entry_answer','COUNT(*)',array('q_answer'=>strval($a['id'])));

				$all_answers[serialize(array($answer,$i))]=$count;
			}
			arsort($all_answers);
			foreach ($all_answers as $bits=>$count)
			{
				list($answer,$i)=unserialize($bits);

				$answers->attach(paragraph(do_lang_tempcode('SURVEY_ANSWER_RESULT',escape_html($answer),integer_format($count),integer_format($i+1))));
			}
			if ($answers->is_empty()) $answers=do_lang_tempcode('FREE_ENTRY_ANSWER');

			$fields->attach(map_table_field($question,$answers,true));
		}
		$summary=do_template('MAP_TABLE',array('_GUID'=>'2b0c2ba0070ba810c5e4b5b4aedcb15f','WIDTH'=>'300','FIELDS'=>$fields));

		// Show results table
		$start=get_param_integer('start',0);
		$max=get_param_integer('max',50);
		$sortables=array('q_time'=>do_lang_tempcode('DATE'));
		$test=explode(' ',get_param('sort','q_time DESC'),2);
		if (count($test)==1) $test[1]='DESC';
		list($sortable,$sort_order)=$test;
		if (((strtoupper($sort_order)!='ASC') && (strtoupper($sort_order)!='DESC')) || (!array_key_exists($sortable,$sortables)))
			log_hack_attack_and_exit('ORDERBY_HACK');
		global $NON_CANONICAL_PARAMS;
		$NON_CANONICAL_PARAMS[]='sort';
		$max_rows=$GLOBALS['SITE_DB']->query_value('quiz_entries','COUNT(*)',array('q_quiz'=>$id));
		$rows=$GLOBALS['SITE_DB']->query_select('quiz_entries',array('id','q_time','q_member'),array('q_quiz'=>$id),'ORDER BY '.$sortable.' '.$sort_order,$max,$start);
		if (count($rows)==0)
		{
			return inform_screen($title,do_lang_tempcode('NO_ENTRIES'));
		}
		$fields=new ocp_tempcode();
		$fields_title=results_field_title(array(do_lang_tempcode('DATE'),do_lang_tempcode('USERNAME')),$sortables,'sort',$sortable.' '.$sort_order);
		foreach ($rows as $myrow)
		{
			$date_link=hyperlink(build_url(array('page'=>'_SELF','type'=>'__survey_results','id'=>$myrow['id']),'_SELF'),escape_html(get_timezoned_date($myrow['q_time'])));
			$member_link=$GLOBALS['FORUM_DRIVER']->member_profile_hyperlink($myrow['q_member']);

			$fields->attach(results_entry(array($date_link,$member_link),false));
		}
		if ($fields->is_empty()) inform_exit(do_lang_tempcode('NO_ENTRIES'));
		$results=results_table(do_lang_tempcode('SURVEY_RESULTS'),$start,'start',$max,'max',$max_rows,$fields_title,$fields,$sortables,$sortable,$sort_order,'sort');

		return do_template('SURVEY_RESULTS_SCREEN',array('_GUID'=>'3f38ac1b94fb4de8219b8f7108c7b0a3','TITLE'=>$title,'SUMMARY'=>$summary,'RESULTS'=>$results));
	}

	/**
	 * View a single filled-in survey.
	 *
	 * @return tempcode	The result of execution.
	 */
	function __survey_results()
	{
		$title=get_screen_title('SURVEY_RESULTS');

		$GLOBALS['HELPER_PANEL_PIC']='pagepics/survey_results';

		require_code('templates_map_table');

		$id=get_param_integer('id'); // entry ID

		$fields=new ocp_tempcode();

		$rows=$GLOBALS['SITE_DB']->query_select('quiz_entries',array('q_time','q_member'),array('id'=>$id),'',1);
		if (!array_key_exists(0,$rows)) warn_exit(do_lang_tempcode('MISSING_RESOURCE'));
		$member_id=$rows[0]['q_member'];
		$username=$GLOBALS['FORUM_DRIVER']->get_username($member_id);
		if (is_null($username)) $username=do_lang('UNKNOWN');
		$date=get_timezoned_date($rows[0]['q_time']);

		$question_rows=$GLOBALS['SITE_DB']->query_select('quiz_questions q LEFT JOIN '.$GLOBALS['SITE_DB']->get_table_prefix().'quiz_entry_answer a ON q.id=a.q_question',array('q.id','q_question_text','q_answer','q_quiz'),array('q_entry'=>$id),'ORDER BY q.id');
		foreach ($question_rows as $q)
		{
			$quiz_id=$q['q_quiz'];
			$answer=$q['q_answer'];
			if (is_numeric($answer))
			{
				$answer_rows=$GLOBALS['SITE_DB']->query_select('quiz_question_answers',array('q_answer_text'),array('q_question'=>$q['id'],'id'=>intval($answer)),'ORDER BY id');
				if (array_key_exists(0,$answer_rows)) $answer=get_translated_text($answer_rows[0]['q_answer_text']);
			}
			$fields->attach(map_table_field(get_translated_text($q['q_question_text']),$answer));
		}

		breadcrumb_set_parents(array(array('_SELF:_SELF',do_lang_tempcode('MANAGE_QUIZZES')),array('_SELF:_SELF:_survey_results:id='.strval($quiz_id),do_lang_tempcode('SURVEY_RESULTS'))));
		breadcrumb_set_self(do_lang_tempcode('RESULT'));

		$member_url=get_base_url();
		if (!is_guest($member_id))
		{
			$member_url=$GLOBALS['FORUM_DRIVER']->member_profile_url($member_id,false,true);
			if (is_object($member_url)) $member_url=$member_url->evaluate();
		}

		return do_template('MAP_TABLE_SCREEN',array('_GUID'=>'02b4dd6d52feaf3844e631e56395c4da','TITLE'=>$title,'TEXT'=>do_lang_tempcode('SURVEY_WAS_ENTERED_AS_FOLLOWS',escape_html($username),escape_html($member_url),escape_html($date)),'FIELDS'=>$fields));
	}

}


