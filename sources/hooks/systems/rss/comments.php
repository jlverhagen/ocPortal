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
 * @package		core_feedback_features
 */

class Hook_rss_comments
{

	/**
	 * Standard modular run function for RSS hooks.
	 *
	 * @param  string			A list of categories we accept from
	 * @param  TIME			Cutoff time, before which we do not show results from
	 * @param  string			Prefix that represents the template set we use
	 * @set    RSS_ ATOM_
	 * @param  string			The standard format of date to use for the syndication type represented in the prefix
	 * @param  integer		The maximum number of entries to return, ordering by date
	 * @return ?array			A pair: The main syndication section, and a title (NULL: error)
	 */
	function run($full_title,$cutoff,$prefix,$date_string,$max)
	{
		require_code('content');

		// Check permissions (this is HARD, we have to tunnel through content_meta_aware hooks)
		$parts=explode('_',$full_title,2);
		$hook=convert_ocportal_type_codes('feedback_type_code',$parts[0],'cma_hook');
		if ($hook!='')
		{
			require_code('hooks/systems/content_meta_aware/'.filter_naughty_harsh($hook));
			$ob=object_factory('Hook_content_meta_aware_'.filter_naughty_harsh($hook),true);
			if (is_null($ob)) return NULL;
			$info=$ob->info();

			// Category access
			$permissions_field=$info['permissions_type_code'];
			if (!is_null($permissions_field))
			{
				$cat=$GLOBALS['SITE_DB']->query_value_null_ok($info['table'],$info['parent_category_field'],array($info['id_field']=>$parts[1]));
				if (is_null($cat)) return NULL;
				if (!has_category_access(get_member(),$permissions_field,$cat)) return NULL;
			}
			
			// Page/Zone access
			if (!is_null($info['view_pagelink_pattern']))
			{
				$view_pagelink_bits=explode(':',$info['view_pagelink_pattern']);
				$zone=$view_pagelink_bits[0];
				if ($zone=='_SEARCH') $zone=get_module_zone($view_pagelink_bits[1]);
				if (!has_actual_page_access(get_member(),$view_pagelink_bits[1],$zone)) return NULL;
			}
		} else
		{
			$zone=get_page_zone($parts[0],false);
			if (is_null($zone)) return NULL;
			if (!has_actual_page_access(get_member(),$parts[0],$zone)) return NULL;
		}

		$title=NULL;

		$content=new ocp_tempcode();
		// Comment posts
		$forum=get_param('forum',get_option('comments_forum_name'));
		$count=0;
		$start=0;
		do
		{
			$_comments=$GLOBALS['FORUM_DRIVER']->get_forum_topic_posts($forum,$full_title,$full_title,$count,min($max,1000),$start);
			if (is_array($_comments))
			{
				$_comments=array_reverse($_comments);
			
				foreach ($_comments as $i=>$comment)
				{
					if (is_null($comment)) continue;
					if ($i+$start>$max) break 2;

					$datetime_raw=$comment['date'];
					if ($datetime_raw<$cutoff) break 2;

					$if_comments=new ocp_tempcode();

					$id=strval($comment['id']);
					$author=$GLOBALS['FORUM_DRIVER']->get_username($comment['user']);
					if (is_null($author)) $author=do_lang('UNKNOWN');

					$news_date=date($date_string,$datetime_raw);
					$edit_date=escape_html('');

					$news_title=xmlentities($comment['title']);
					if (($news_title!='') && (is_null($title))) $title=$comment['title'];
					$_summary=$comment['message'];
					if (is_object($_summary)) $_summary=$_summary->evaluate();
					$summary=xmlentities($_summary);
					$news=escape_html('');

					$category='';
					$category_raw='';

					$content->attach(do_template($prefix.'ENTRY',array('VIEW_URL'=>new ocp_tempcode(),'SUMMARY'=>$summary,'EDIT_DATE'=>$edit_date,'IF_COMMENTS'=>$if_comments,'TITLE'=>$news_title,'CATEGORY_RAW'=>$category_raw,'CATEGORY'=>$category,'AUTHOR'=>$author,'ID'=>$id,'NEWS'=>$news,'DATE'=>$news_date)));
				}
			}
			
			$start+=1000;
		}
		while (count($_comments)==1000);
		
		if (is_null($title)) $title=do_lang('COMMENTS');

		return array($content,$title);
	}

}


