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
 * @package		calendar
 */

class Hook_addon_registry_calendar
{
	/**
	 * Get a list of file permissions to set
	 *
	 * @return array			File permissions to set
	 */
	function get_chmod_array()
	{
		return array();
	}

	/**
	 * Get the version of ocPortal this addon is for
	 *
	 * @return float			Version number
	 */
	function get_version()
	{
		return ocp_version_number();
	}

	/**
	 * Get the description of the addon
	 *
	 * @return string			Description of the addon
	 */
	function get_description()
	{
		return 'An advanced community calendar.';
	}

	/**
	 * Get a mapping of dependency types
	 *
	 * @return array			File permissions to set
	 */
	function get_dependencies()
	{
		return array(
			'requires'=>array(),
			'recommends'=>array(),
			'conflicts_with'=>array()
		);
	}

	/**
	 * Get a list of files that belong to this addon
	 *
	 * @return array			List of files
	 */
	function get_file_list()
	{
		return array(
			'sources/hooks/systems/snippets/calendar_recurrence_suggest.php',
			'sources/hooks/systems/notifications/calendar_reminder.php',
			'sources/hooks/systems/notifications/calendar_event.php',
			'sources/hooks/systems/config_default/calendar_show_stats_count_events.php',
			'sources/hooks/systems/config_default/calendar_show_stats_count_events_this_month.php',
			'sources/hooks/systems/config_default/calendar_show_stats_count_events_this_week.php',
			'sources/hooks/systems/config_default/calendar_show_stats_count_events_this_year.php',
			'sources/hooks/systems/realtime_rain/calendar.php',
			'sources/hooks/systems/content_meta_aware/event.php',
			'sources/hooks/systems/content_meta_aware/calendar_type.php',
			'sources/hooks/systems/meta/events.php',
			'sources/hooks/blocks/side_stats/stats_calendar.php',
			'sources/hooks/systems/preview/calendar_type.php',
			'sources/hooks/modules/admin_import_types/calendar.php',
			'sources/hooks/modules/admin_setupwizard/calendar.php',
			'sources/hooks/modules/admin_themewizard/calendar.php',
			'sources/hooks/systems/addon_registry/calendar.php',
			'CALENDAR_MAIN_SCREEN.tpl',
			'CALENDAR_DAY.tpl',
			'CALENDAR_DAY_ENTRY.tpl',
			'CALENDAR_DAY_ENTRY_FREE.tpl',
			'CALENDAR_DAY_HOUR.tpl',
			'CALENDAR_DAY_STREAM_HOUR.tpl',
			'CALENDAR_EVENT_CONFLICT.tpl',
			'CALENDAR_EVENT_TYPE.tpl',
			'CALENDAR_MONTH.tpl',
			'CALENDAR_MONTH_DAY.tpl',
			'CALENDAR_MONTH_ENTRY.tpl',
			'CALENDAR_MONTH_ENTRY_FREE.tpl',
			'CALENDAR_MONTH_WEEK.tpl',
			'CALENDAR_EVENT_SCREEN.tpl',
			'CALENDAR_EVENT_SCREEN_PERSONAL_SUBSCRIPTION.tpl',
			'CALENDAR_EVENT_SCREEN_SUBSCRIPTION.tpl',
			'CALENDAR_WEEK.tpl',
			'CALENDAR_WEEK_HOUR_DAY.tpl',
			'CALENDAR_WEEK_ENTRY.tpl',
			'CALENDAR_WEEK_ENTRY_FREE.tpl',
			'CALENDAR_WEEK_HOUR.tpl',
			'CALENDAR_YEAR.tpl',
			'CALENDAR_YEAR_MONTH.tpl',
			'CALENDAR_YEAR_MONTH_DAY_ACTIVE.tpl',
			'CALENDAR_YEAR_MONTH_DAY_FREE.tpl',
			'CALENDAR_YEAR_MONTH_DAY_ROW.tpl',
			'CALENDAR_YEAR_MONTH_DAY_SPACER.tpl',
			'CALENDAR_YEAR_MONTH_ROW.tpl',
			'BLOCK_SIDE_CALENDAR.tpl',
			'BLOCK_SIDE_CALENDAR_LISTING.tpl',
			'CALENDAR_EVENT_BOX.tpl',
			'themes/default/images/EN/page/add_event.png',
			'sources/hooks/systems/awards/event.php',
			'sources/hooks/systems/trackback/events.php',
			'cms/pages/modules/cms_calendar.php',
			'lang/EN/calendar.ini',
			'site/pages/modules/calendar.php',
			'sources/blocks/side_calendar.php',
			'sources/calendar.php',
			'sources/calendar2.php',
			'sources/calendar_ical.php',
			'sources/hooks/modules/admin_import/icalendar.php',
			'sources/hooks/modules/admin_newsletter/calendar.php',
			'sources/hooks/modules/admin_unvalidated/calendar.php',
			'sources/hooks/modules/members/calendar.php',
			'sources/hooks/modules/search/calendar.php',
			'sources/hooks/systems/attachments/calendar.php',
			'sources/hooks/systems/cron/calendar.php',
			'sources/hooks/systems/do_next_menus/calendar.php',
			'sources/hooks/systems/preview/calendar.php',
			'sources/hooks/systems/rss/calendar.php',
			'calendar.css',
			'themes/default/images/bigicons/calendar.png',
			'themes/default/images/calendar/activity.png',
			'themes/default/images/calendar/anniversary.png',
			'themes/default/images/calendar/appointment.png',
			'themes/default/images/calendar/birthday.png',
			'themes/default/images/calendar/commitment.png',
			'themes/default/images/calendar/duty.png',
			'themes/default/images/calendar/festival.png',
			'themes/default/images/calendar/general.png',
			'themes/default/images/calendar/public_holiday.png',
			'themes/default/images/calendar/index.html',
			'themes/default/images/calendar/priority_1.png',
			'themes/default/images/calendar/priority_2.png',
			'themes/default/images/calendar/priority_3.png',
			'themes/default/images/calendar/priority_4.png',
			'themes/default/images/calendar/priority_5.png',
			'themes/default/images/calendar/priority_na.png',
			'themes/default/images/calendar/rss.png',
			'themes/default/images/calendar/system_command.png',
			'themes/default/images/pagepics/calendar.png'
		);
	}


	/**
	 * Get mapping between template names and the method of this class that can render a preview of them
	 *
	 * @return array			The mapping
	 */
	function tpl_previews()
	{
		return array(
			'CALENDAR_YEAR_MONTH_DAY_SPACER.tpl'=>'calendar_year_view',
			'CALENDAR_YEAR_MONTH_DAY_FREE.tpl'=>'calendar_year_view',
			'CALENDAR_YEAR_MONTH_DAY_ACTIVE.tpl'=>'calendar_year_view',
			'CALENDAR_YEAR_MONTH_DAY_ROW.tpl'=>'calendar_year_view',
			'BLOCK_SIDE_CALENDAR.tpl'=>'block_side_calendar',
			'BLOCK_SIDE_CALENDAR_LISTING.tpl'=>'block_side_calendar_listing',
			'CALENDAR_EVENT_CONFLICT.tpl'=>'calendar_event_conflict',
			'CALENDAR_EVENT_TYPE.tpl'=>'calendar_year_view',
			'CALENDAR_MAIN_SCREEN.tpl'=>'calendar_month_view',
			'CALENDAR_DAY_ENTRY.tpl'=>'calendar_day_view',
			'CALENDAR_DAY_ENTRY_FREE.tpl'=>'calendar_day_view',
			'CALENDAR_DAY_STREAM_HOUR.tpl'=>'calendar_day_view',
			'CALENDAR_DAY_HOUR.tpl'=>'calendar_day_view',
			'CALENDAR_DAY.tpl'=>'calendar_day_view',
			'CALENDAR_WEEK_ENTRY.tpl'=>'calendar_week_view',
			'CALENDAR_WEEK_ENTRY_FREE.tpl'=>'calendar_week_view',
			'CALENDAR_WEEK_HOUR_DAY.tpl'=>'calendar_week_view',
			'CALENDAR_WEEK_HOUR.tpl'=>'calendar_week_view',
			'CALENDAR_WEEK.tpl'=>'calendar_week_view',
			'CALENDAR_MONTH_ENTRY_FREE.tpl'=>'calendar_month_view',
			'CALENDAR_MONTH_DAY.tpl'=>'calendar_month_view',
			'CALENDAR_MONTH_WEEK.tpl'=>'calendar_month_view',
			'CALENDAR_MONTH_ENTRY.tpl'=>'calendar_month_view',
			'CALENDAR_MONTH.tpl'=>'calendar_month_view',
			'CALENDAR_YEAR_MONTH_ROW.tpl'=>'calendar_year_view',
			'CALENDAR_YEAR_MONTH.tpl'=>'calendar_year_view',
			'CALENDAR_YEAR.tpl'=>'calendar_year_view',
			'CALENDAR_EVENT_SCREEN_SUBSCRIPTION.tpl'=>'calendar_event_screen',
			'CALENDAR_EVENT_SCREEN_PERSONAL_SUBSCRIPTION.tpl'=>'calendar_event_screen',
			'CALENDAR_EVENT_SCREEN.tpl'=>'calendar_event_screen',
			'CALENDAR_EVENT_BOX.tpl'=>'calendar_event_box'
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_year_view()
	{
		return array(
			lorem_globalise($this->calendar_main_screen('year'), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_month_view()
	{
		return array(
			lorem_globalise($this->calendar_main_screen('month'), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_week_view()
	{
		return array(
			lorem_globalise($this->calendar_main_screen('week'), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_day_view()
	{
		return array(
			lorem_globalise($this->calendar_main_screen('day'), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_side_calendar()
	{
		$_entries=new ocp_tempcode();
		$__entries=new ocp_tempcode();
		$dotw=0;
		for ($j=1; $j <= 31; $j++)
		{
			if ($j==10)
				$__entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_FREE', array(
					'CLASS'=>lorem_word(),
					'CURRENT'=>lorem_word(),
					'DAY_URL'=>placeholder_url(),
					'DATE'=>placeholder_date(),
					'DAY'=>lorem_word_2()
				)));
			else
				$__entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_ACTIVE', array(
					'CURRENT'=>lorem_word(),
					'DAY_URL'=>placeholder_url(),
					'DATE'=>placeholder_date(),
					'TITLE'=>'',
					'TIME'=>'',
					'URL'=>'',
					'ID'=>'',
					'PRIORITY'=>lorem_word(),
					'DAY'=>placeholder_number(),
					'ICON'=>'',
					'COUNT'=>placeholder_number(),
					'EVENTS_AND_PRIORITY_LANG'=>lorem_phrase()
				)));

			if ($dotw==6)
			{
				$_entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_ROW', array(
					'ENTRIES'=>$__entries
				)));
				$__entries=new ocp_tempcode();
				$dotw=0;
			}
			else
				$dotw++;
		}

		for ($j=$dotw; $j < 7; $j++)
		{
			$__entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_SPACER'));
		}
		$_entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_ROW', array(
			'ENTRIES'=>$__entries
		)));

		require_lang('dates');
		return array(
			lorem_globalise(do_lorem_template('BLOCK_SIDE_CALENDAR', array(
				'CALENDAR_URL'=>placeholder_url(),
				'ENTRIES'=>$_entries,
				'_MONTH'=>lorem_phrase(),
				'MONTH'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__block_side_calendar_listing()
	{
		return array(
			lorem_globalise(do_lorem_template('BLOCK_SIDE_CALENDAR_LISTING', array(
				'DAYS'=>placeholder_array(),
				'EVENTS'=>placeholder_array(),
				'CALENDAR_URL'=>placeholder_url(),
				'TITLE'=>lorem_word(),
				'TIME'=>placeholder_time(),
				'VIEW_URL'=>placeholder_url(),
				'ICON'=>'calendar/activity',
				'T_TITLE'=>lorem_word(),
				'DESCRIPTION'=>lorem_paragraph_html(),
				'TIME_VCAL'=>placeholder_number()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_event_conflict()
	{
		return array(
			lorem_globalise(do_lorem_template('CALENDAR_EVENT_CONFLICT', array(
				'URL'=>placeholder_url(),
				'ID'=>placeholder_id(),
				'TITLE'=>lorem_word()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_event_box()
	{
		return array(
			lorem_globalise(do_lorem_template('CALENDAR_EVENT_BOX', array(
				'URL'=>placeholder_url(),
				'SUMMARY'=>lorem_paragraph_html(),
				'TITLE'=>lorem_phrase()
			)), NULL, '', true)
		);
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @param  string			View type.
	 * @return tempcode		Preview.
	 */
	function calendar_main_screen($view)
	{
		require_lang('dates');
		switch ($view)
		{
			case 'day':
				$hours=new ocp_tempcode();
				for ($i=0; $i < 24; $i++)
				{
					$_streams=new ocp_tempcode();
					foreach (placeholder_array(2) as $k=>$v)
					{
						$entry=do_lorem_template('CALENDAR_DAY_ENTRY_FREE', array(
							'CLASS'=>lorem_word(),
							'TEXT'=>lorem_phrase()
						));
						$_streams->attach(do_lorem_template('CALENDAR_DAY_STREAM_HOUR', array(
							'CURRENT'=>lorem_word(),
							'ADD_URL'=>placeholder_url(),
							'PRIORITY'=>lorem_phrase(),
							'DOWN'=>'1',
							'ENTRY'=>$entry
						)));
					}
					foreach (placeholder_array(2) as $v)
					{
						$entries=do_lorem_template('CALENDAR_DAY_ENTRY', array(
							'ID'=>placeholder_id(),
							'URL'=>placeholder_url(),
							'TIME'=>placeholder_time(),
							'T_TITLE'=>lorem_phrase(),
							'TITLE'=>lorem_word(),
							'DESCRIPTION'=>lorem_word_2(),
							'RECURRING'=>false
						));
						$_streams->attach(do_lorem_template('CALENDAR_DAY_STREAM_HOUR', array(
							'CURRENT'=>lorem_word(),
							'ADD_URL'=>placeholder_url(),
							'PRIORITY'=>lorem_phrase(),
							'DOWN'=>'1',
							'ENTRY'=>$entry
						)));
					}

					$hours->attach(do_lorem_template('CALENDAR_DAY_HOUR', array(
						'_HOUR'=>placeholder_number(),
						'HOUR'=>lorem_word(),
						'STREAMS'=>$_streams
					)));
				}

				$main=do_lorem_template('CALENDAR_DAY', array(
					'HOURS'=>$hours,
					'PERIOD_START'=>placeholder_date_raw(),
					'PERIOD_END'=>placeholder_date_raw()
				));
				break;

			case 'week':
				$hours=new ocp_tempcode();
				for ($i=0; $i < 24; $i++)
				{
					$days=new ocp_tempcode();
					for ($j=0; $j < 7; $j++)
					{
						if ($i % 2==0)
							$entries=do_lorem_template('CALENDAR_WEEK_ENTRY_FREE', array(
								'CLASS'=>lorem_word(),
								'TEXT'=>''
							));
						else
						{
							$entries=do_lorem_template('CALENDAR_WEEK_ENTRY', array(
								'ID'=>placeholder_id(),
								'URL'=>placeholder_url(),
								'TIME'=>placeholder_time(),
								'TITLE'=>lorem_word(),
								'E'=>lorem_word(),
								'ICON'=>'calendar/general',
								'RECURRING'=>false
							));
						}
						$days->attach(do_lorem_template('CALENDAR_WEEK_HOUR_DAY', array(
							'CURRENT'=>lorem_word(),
							'ADD_URL'=>placeholder_url(),
							'DOWN'=>'1',
							'DAY'=>lorem_word(),
							'HOUR'=>lorem_word(),
							'CLASS'=>lorem_word(),
							'ENTRIES'=>$entries
						)));
					}

					$hours->attach(do_lorem_template('CALENDAR_WEEK_HOUR', array(
						'_HOUR'=>placeholder_number(),
						'HOUR'=>lorem_word(),
						'DAYS'=>$days
					)));
				}

				$main=do_lorem_template('CALENDAR_WEEK', array(
					'MONDAY_DATE'=>lorem_word(),
					'TUESDAY_DATE'=>lorem_word(),
					'WEDNESDAY_DATE'=>lorem_word(),
					'THURSDAY_DATE'=>lorem_word(),
					'FRIDAY_DATE'=>lorem_word(),
					'SATURDAY_DATE'=>lorem_word(),
					'SUNDAY_DATE'=>lorem_word(),
					'MONDAY_URL'=>placeholder_url(),
					'TUESDAY_URL'=>placeholder_url(),
					'WEDNESDAY_URL'=>placeholder_url(),
					'THURSDAY_URL'=>placeholder_url(),
					'FRIDAY_URL'=>placeholder_url(),
					'SATURDAY_URL'=>placeholder_url(),
					'SUNDAY_URL'=>placeholder_url(),
					'HOURS'=>$hours,
					'PERIOD_START'=>placeholder_date_raw(),
					'PERIOD_END'=>placeholder_date_raw()
				));
				break;

			case 'month':
				$empty_entry=do_lorem_template('CALENDAR_MONTH_ENTRY_FREE', array(
					'CLASS'=>lorem_word(),
					'TEXT'=>''
				));

				$days=new ocp_tempcode();
				foreach (placeholder_array() as $k=>$v)
				{
					$entries=new ocp_tempcode();
					foreach (placeholder_array() as $_k=>$_v)
					{
						$entries->attach(do_lorem_template('CALENDAR_MONTH_ENTRY', array(
							'ID'=>placeholder_id(),
							'T_TITLE'=>lorem_phrase(),
							'PRIORITY'=>lorem_word(),
							'ICON'=>'calendar/' . placeholder_img_code('calendar'),
							'TIME'=>placeholder_number(),
							'TITLE'=>lorem_word(),
							'URL'=>placeholder_url(),
							'RECURRING'=>lorem_word()
						)));
					}

					$days->attach(do_lorem_template('CALENDAR_MONTH_DAY', array(
						'CURRENT'=>false,
						'DAY_URL'=>'',
						'CLASS'=>'',
						'DAY'=>'',
						'ENTRIES'=>$entries
					)));
				}

				$weeks=new ocp_tempcode();
				foreach (placeholder_array() as $k=>$v)
				{
					$weeks->attach(do_lorem_template('CALENDAR_MONTH_WEEK', array(
						'WEEK_URL'=>placeholder_url(),
						'WEEK_DATE'=>lorem_word(),
						'DAYS'=>$days
					)));
				}

				$main=do_lorem_template('CALENDAR_MONTH', array(
					'WEEKS'=>$weeks,
					'PERIOD_START'=>placeholder_date_raw(),
					'PERIOD_END'=>placeholder_date_raw()
				));
				break;

			case 'year':
				$months='';
				$month_rows=new ocp_tempcode();
				for ($i=1; $i <= 12; $i++)
				{
					if ((($i - 1) % 3==0) && ($i != 1))
					{
						$month_rows->attach(do_lorem_template('CALENDAR_YEAR_MONTH_ROW', array(
							'MONTHS'=>$months,
							'MONTH_A_URL'=>placeholder_url(),
							'MONTH_B_URL'=>placeholder_url(),
							'MONTH_C_URL'=>placeholder_url(),
							'MONTH_A'=>lorem_word(),
							'MONTH_B'=>lorem_word(),
							'MONTH_C'=>lorem_word()
						)));
						$months='';
					}

					$_entries=new ocp_tempcode();
					$__entries=new ocp_tempcode();
					$dotw=0;
					for ($j=1; $j <= 31; $j++)
					{
						if ($j==10)
							$__entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_FREE', array(
								'CLASS'=>lorem_word(),
								'CURRENT'=>lorem_word(),
								'DAY_URL'=>placeholder_url(),
								'DATE'=>placeholder_date(),
								'DAY'=>lorem_word_2()
							)));
						else
							$__entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_ACTIVE', array(
								'CURRENT'=>lorem_word(),
								'DAY_URL'=>placeholder_url(),
								'DATE'=>placeholder_date(),
								'TITLE'=>'',
								'TIME'=>'',
								'URL'=>'',
								'ID'=>'',
								'PRIORITY'=>lorem_word(),
								'DAY'=>placeholder_number(),
								'ICON'=>'',
								'COUNT'=>placeholder_number(),
								'EVENTS_AND_PRIORITY_LANG'=>lorem_phrase()
							)));

						if ($dotw==6)
						{
							$_entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_ROW', array(
								'ENTRIES'=>$__entries
							)));
							$__entries=new ocp_tempcode();
							$dotw=0;
						}
						else
							$dotw++;
					}

					for ($j=$dotw; $j < 7; $j++)
					{
						$__entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_SPACER'));
					}
					$_entries->attach(do_lorem_template('CALENDAR_YEAR_MONTH_DAY_ROW', array(
						'ENTRIES'=>$__entries
					)));

					$month=do_lorem_template('CALENDAR_YEAR_MONTH', array(
						'ENTRIES'=>$_entries
					));
					$months.=$month->evaluate() /*FUDGEFUDGE*/ ;
				}
				$month_rows->attach(do_lorem_template('CALENDAR_YEAR_MONTH_ROW', array(
					'MONTHS'=>$months,
					'MONTH_A_URL'=>placeholder_url(),
					'MONTH_B_URL'=>placeholder_url(),
					'MONTH_C_URL'=>placeholder_url(),
					'MONTH_A'=>lorem_word(),
					'MONTH_B'=>lorem_word(),
					'MONTH_C'=>lorem_word()
				)));

				$main=do_lorem_template('CALENDAR_YEAR', array(
					'MONTH_ROWS'=>$month_rows,
					'PERIOD_START'=>placeholder_date_raw(),
					'PERIOD_END'=>placeholder_date_raw()
				));
				break;
		}
		$events1=do_lorem_template('CALENDAR_EVENT_TYPE', array(
			'S'=>'I',
			'INTERESTED'=>'interested',
			'TYPE'=>lorem_phrase(),
			'TYPE_ID'=>placeholder_id()
		));
		$events2=do_lorem_template('CALENDAR_EVENT_TYPE', array(
			'S'=>'F',
			'INTERESTED'=>'not_interested',
			'TYPE'=>lorem_phrase(),
			'TYPE_ID'=>placeholder_id()
		));
		return do_lorem_template('CALENDAR_MAIN_SCREEN', array(
			'RSS_FORM'=>placeholder_form(),
			'DAY_URL'=>placeholder_url(),
			'WEEK_URL'=>placeholder_url(),
			'MONTH_URL'=>placeholder_url(),
			'YEAR_URL'=>placeholder_url(),
			'PREVIOUS_URL'=>placeholder_url(),
			'NEXT_URL'=>placeholder_url(),
			'ADD_URL'=>placeholder_url(),
			'TITLE'=>lorem_title(),
			'BACK_URL'=>placeholder_url(),
			'MAIN'=>$main,
			'FILTER_URL'=>placeholder_url(),
			'EVENT_TYPES_1'=>$events1,
			'INTERESTS_URL'=>placeholder_url(),
			'EVENT_TYPES_2'=>$events2,
			'PREVIOUS_NO_FOLLOW'=>true,
			'NEXT_NO_FOLLOW'=>true
		));
	}

	/**
	 * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
	 * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
	 * Assumptions: You can assume all Lang/CSS/Javascript files in this addon have been pre-required.
	 *
	 * @return array			Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
	 */
	function tpl_preview__calendar_event_screen()
	{
		$sub=new ocp_tempcode();
		foreach (placeholder_array() as $v)
		{
			$sub->attach(do_lorem_template('CALENDAR_EVENT_SCREEN_PERSONAL_SUBSCRIPTION', array(
				'UNSUBSCRIBE_URL'=>placeholder_url(),
				'TIME'=>placeholder_time()
			)));
		}
		$subed=new ocp_tempcode();
		foreach (placeholder_array() as $v)
		{
			$subed->attach(do_lorem_template('CALENDAR_EVENT_SCREEN_SUBSCRIPTION', array(
				'MEMBER_URL'=>placeholder_url(),
				'USERNAME'=>lorem_word()
			)));
		}
		$comment_details=do_lorem_template('COMMENTS_POSTING_FORM', array(
			'JOIN_BITS'=>lorem_phrase_html(),
			'USE_CAPTCHA'=>false,
			'EMAIL_OPTIONAL'=>lorem_word(),
			'POST_WARNING'=>'',
			'COMMENT_TEXT'=>'',
			'GET_EMAIL'=>true,
			'GET_TITLE'=>true,
			'EM'=>placeholder_emoticon_chooser(),
			'DISPLAY'=>'block',
			'COMMENT_URL'=>placeholder_url(),
			'TITLE'=>lorem_phrase(),
			'MAKE_POST'=>true,
			'CREATE_TICKET_MAKE_POST'=>true,
			'FIRST_POST_URL'=>'',
			'FIRST_POST'=>''
		));
		return array(
			lorem_globalise(do_lorem_template('CALENDAR_EVENT_SCREEN', array(
				'ID'=>placeholder_id(),
				'TAGS'=>lorem_word_html(),
				'WARNING_DETAILS'=>'',
				'SUBMITTER'=>placeholder_id(),
				'ADD_DATE'=>placeholder_date_raw(),
				'ADD_DATE_RAW'=>placeholder_date_raw(),
				'EDIT_DATE_RAW'=>placeholder_date_raw(),
				'VIEWS'=>lorem_phrase(),
				'LOGO'=>placeholder_img_code(''),
				'DAY'=>placeholder_time(),
				'RECURRENCE'=>placeholder_number(),
				'IS_PUBLIC'=>lorem_phrase(),
				'PRIORITY'=>lorem_phrase(),
				'PRIORITY_LANG'=>lorem_phrase(),
				'TYPE'=>lorem_phrase(),
				'TIME'=>placeholder_time(),
				'TIME_RAW'=>placeholder_date_raw(),
				'TIME_VCAL'=>placeholder_date_raw(),
				'EDIT_URL'=>placeholder_url(),
				'SUBSCRIPTIONS'=>$sub,
				'SUBSCRIBE_URL'=>placeholder_url(),
				'TITLE'=>lorem_title(),
				'BACK_URL'=>placeholder_url(),
				'CONTENT'=>lorem_phrase(),
				'SUBSCRIBED'=>$subed,
				'RATING_DETAILS'=>lorem_sentence_html(),
				'TRACKBACK_DETAILS'=>lorem_sentence_html(),
				'COMMENT_DETAILS'=>$comment_details
			)), NULL, '', true)
		);
	}
}
