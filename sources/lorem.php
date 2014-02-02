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
 * @package		core_themeing
 */

/**
 * Standard code module initialisation function.
 */
function init__lorem()
{
	global $LOREM_RANDOM_VAR,$LOREM_AVOID_GLOBALISE;
	$LOREM_RANDOM_VAR=0;
	$LOREM_AVOID_GLOBALISE=false;
}

/**
 * Get suitable placeholder text.
 *
 * @return string			Place holder text.
 */
function lorem_word()
{
	return 'Lorem';
}

/**
 * Get suitable placeholder text.
 *
 * @return string			Place holder text.
 */
function lorem_word_2()
{
	return 'Ipsum';
}

/**
 * Get suitable placeholder text.
 *
 * @return string			Place holder text.
 */
function lorem_phrase()
{
	return 'Lorem ipsum dolor';
}

/**
 * Get suitable placeholder text: title.
 *
 * @return tempcode		Place holder text.
 */
function lorem_title()
{
	return get_screen_title('Lorem Ipsum Dolor');
}

/**
 * Get suitable placeholder text: title.
 *
 * @return string			Place holder text.
 */
function placeholder_ip()
{
	return '123.45.6.4';
}

/**
 * Get suitable placeholder text.
 *
 * @return string			Place holder text.
 */
function lorem_sentence()
{
	return 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
}

/**
 * Get suitable placeholder text.
 *
 * @return string			Place holder text.
 */
function lorem_paragraph()
{
	return 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
}

/**
 * Get suitable placeholder text.
 *
 * @return string			Place holder text.
 */
function lorem_chunk()
{
	return "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies cursus egestas. Nunc orci lacus, viverra a ultrices nec, volutpat eu velit. Maecenas imperdiet tortor eget eros varius mattis. Nullam eget lacus in tellus mollis ornare in lobortis sapien. Duis lectus felis, consequat in ullamcorper at, elementum sed est. In viverra tellus turpis, in tincidunt leo. Donec sagittis rhoncus urna quis eleifend. Nam imperdiet, orci quis bibendum porta, odio neque ullamcorper erat, sed malesuada ante libero vel ligula. Ut porttitor est egestas erat placerat eget placerat lectus ultricies. Morbi eu dolor metus, nec vestibulum nisl. Praesent eget massa tortor, in consequat velit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque eget eros ut erat vestibulum facilisis. Duis eleifend odio in neque pellentesque semper pulvinar dolor feugiat. Proin sed lectus et lectus fringilla gravida. Aliquam a nisl metus. In risus risus, tempus interdum viverra ac, laoreet at sem. Sed sem nunc, rutrum quis convallis eu, hendrerit non libero.\n\nSed sollicitudin, dolor ac posuere bibendum, tellus eros hendrerit magna, non accumsan ligula sapien at enim. Curabitur hendrerit lacinia ligula, et dapibus diam porttitor sit amet. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec nisi arcu, placerat vel ullamcorper non, hendrerit cursus nisl. Aliquam tincidunt, magna sed tempus auctor, enim dolor consequat massa, rhoncus euismod tortor orci fringilla arcu. Nulla et egestas augue. Fusce non enim vitae dolor imperdiet pulvinar vitae sed neque. Sed augue neque, volutpat non tincidunt ac, volutpat eu tellus. Suspendisse sollicitudin nulla eu leo placerat posuere id sit amet metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean fermentum sollicitudin porttitor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed imperdiet scelerisque turpis, eleifend tristique justo euismod non. Pellentesque at elit tristique sem venenatis auctor eu vitae dui. Nam hendrerit sapien sit amet risus suscipit vitae interdum arcu blandit. Phasellus quis massa sed mi mollis hendrerit.\n\nNunc at elit eget elit convallis auctor sit amet non nisi. Curabitur consequat, nisl sed venenatis feugiat, felis purus vehicula purus, sed scelerisque nulla tellus ac neque. Morbi convallis semper pulvinar. Integer auctor mi ante. Cras aliquam egestas lobortis. Maecenas sodales mi at felis ullamcorper tristique. Fusce viverra laoreet sapien, et vestibulum purus interdum sit amet. Sed at ante quis ipsum pellentesque pretium. Praesent volutpat justo in orci ullamcorper cursus. In non nulla sit amet turpis ultrices dignissim eu cursus justo. Etiam lacinia lacinia odio sit amet fringilla. Vestibulum at auctor nisl.";
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function lorem_word_html()
{
	$text='<strong>Lorem</strong>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function lorem_word_2_html()
{
	$text='<strong>Ipsum</strong>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function lorem_phrase_html()
{
	$text='<strong>Lorem ipsum</strong> dolor';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function lorem_sentence_html()
{
	$text='<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function lorem_paragraph_html()
{
	$text='<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function lorem_chunk_html()
{
	$text='<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec ultricies cursus egestas. Nunc orci lacus, viverra a ultrices nec, volutpat eu velit. Maecenas imperdiet tortor eget eros varius mattis. Nullam eget lacus in tellus mollis ornare in lobortis sapien. Duis lectus felis, consequat in ullamcorper at, elementum sed est. In viverra tellus turpis, in tincidunt leo. Donec sagittis rhoncus urna quis eleifend. Nam imperdiet, orci quis bibendum porta, odio neque ullamcorper erat, sed malesuada ante libero vel ligula. Ut porttitor est egestas erat placerat eget placerat lectus ultricies. Morbi eu dolor metus, nec vestibulum nisl. Praesent eget massa tortor, in consequat velit. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Pellentesque eget eros ut erat vestibulum facilisis. Duis eleifend odio in neque pellentesque semper pulvinar dolor feugiat. Proin sed lectus et lectus fringilla gravida. Aliquam a nisl metus. In risus risus, tempus interdum viverra ac, laoreet at sem. Sed sem nunc, rutrum quis convallis eu, hendrerit non libero.</p><p>Sed sollicitudin, dolor ac posuere bibendum, tellus eros hendrerit magna, non accumsan ligula sapien at enim. Curabitur hendrerit lacinia ligula, et dapibus diam porttitor sit amet. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec nisi arcu, placerat vel ullamcorper non, hendrerit cursus nisl. Aliquam tincidunt, magna sed tempus auctor, enim dolor consequat massa, rhoncus euismod tortor orci fringilla arcu. Nulla et egestas augue. Fusce non enim vitae dolor imperdiet pulvinar vitae sed neque. Sed augue neque, volutpat non tincidunt ac, volutpat eu tellus. Suspendisse sollicitudin nulla eu leo placerat posuere id sit amet metus. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean fermentum sollicitudin porttitor. Class aptent taciti sociosqu ad litora torquent per conubia nostra, per inceptos himenaeos. Sed imperdiet scelerisque turpis, eleifend tristique justo euismod non. Pellentesque at elit tristique sem venenatis auctor eu vitae dui. Nam hendrerit sapien sit amet risus suscipit vitae interdum arcu blandit. Phasellus quis massa sed mi mollis hendrerit.</p><p>Nunc at elit eget elit convallis auctor sit amet non nisi. Curabitur consequat, nisl sed venenatis feugiat, felis purus vehicula purus, sed scelerisque nulla tellus ac neque. Morbi convallis semper pulvinar. Integer auctor mi ante. Cras aliquam egestas lobortis. Maecenas sodales mi at felis ullamcorper tristique. Fusce viverra laoreet sapien, et vestibulum purus interdum sit amet. Sed at ante quis ipsum pellentesque pretium. Praesent volutpat justo in orci ullamcorper cursus. In non nulla sit amet turpis ultrices dignissim eu cursus justo. Etiam lacinia lacinia odio sit amet fringilla. Vestibulum at auctor nisl.</p>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function placeholder_form()
{
	require_css('forms');

	$text='<p>(A form would go here.)</p>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get fields
 *
 * @return tempcode		Place holder text.
 */
function placeholder_fields()
{
	require_css('forms');

	$text='<tr><th>(Some field key would go here.)</th><td>(Some field value would go here.)</td></tr>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get fields, but composed of divs (used by forum)
 *
 * @return tempcode		Place holder text.
 */
function placeholder_fields_as_divs()
{
	require_css('forms');

	$text='<div><div>(Some field key would go here.)</div><div>(Some field value would go here.)</div></div>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get form with a field
 *
 * @param  ID_TEXT		The field name wanted.
 * @return tempcode		Place holder text.
 */
function placeholder_form_with_field($field_name)
{
	require_css('forms');

	$text='<p>(A form would go here.)</p>';

	require_code('form_templates');
	$hidden=form_input_hidden($field_name,'0');

	//$field->attach(form_input_line_auto_load('test','test.','test','',false,'news'));

	$form=do_lorem_template('FORM',array('TABINDEX'=>placeholder_number(),'HIDDEN'=>$hidden,'TEXT'=>$text,'FIELDS'=>placeholder_fields(),'URL'=>placeholder_url(),'SUBMIT_ICON'=>'buttons__proceed','SUBMIT_NAME'=>'proceed'));

	return $form;
}

/**
 * Get suitable placeholder text.
 *
 * @return tempcode		Place holder text.
 */
function placeholder_table()
{
	$text='<table class="results_table" width="100%"><tbody><tr><th>(Cell 1)</th><td>(Cell 2)</td></tr><tr><td>(Cell 3)</td><td>(Cell 4)</td></tr></tbody></table>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder URL.
 *
 * @return tempcode		Place holder URL.
 */
function placeholder_url()
{
	$text='http://www.example.com/';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder link.
 *
 * @return tempcode		Place holder link.
 */
function placeholder_link()
{
	$text='<a href="http://www.example.com/">test link</a>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get a random number
 *
 * @return string			Random number
 */
function placeholder_random()
{
	global $LOREM_RANDOM_VAR;
	$x=$LOREM_RANDOM_VAR;
	$LOREM_RANDOM_VAR++;
	return strval($x);
}

/**
 * Get a random ID
 *
 * @return string			Random ID
 */
function placeholder_random_id()
{
	global $LOREM_RANDOM_VAR;
	$x=$LOREM_RANDOM_VAR;
	$LOREM_RANDOM_VAR++;
	return 'id_'.strval($x);
}

/**
 * Get a button
 *
 * @return tempcode		Button
 */
function placeholder_button()
{
	$text='<p>( Buttons would go here.)</p>';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get options for drop-down box
 *
 * @return string		Place holder text
 */
function placeholder_options()
{
	$text='';
	for ($i=1;$i<=3;$i++)
	{
		$text .= '<option value="'.lorem_word().'">'.lorem_word().'</option>';
	}

	return $text;
}

/**
 * Get an array
 *
 * @param  integer		Number of array elements.
 * @return array			Place holder array.
 */
function placeholder_array($num_elements=3)
{
	$array=array();
	for ($i=1;$i<=$num_elements;$i++)
	{
		$array[]='test'.strval($i);
	}

	return $array;
}

/**
 * Get a list
 *
 * @return string			Place holder text.
 */
function placeholder_list_item()
{
	return '<li>A list will display here</li>';
}

/**
 * Get some set of data
 *
 * @return string			Place holder text.
 */
function placeholder_types()
{
	return 'Type 1, type 2, type3 etc';
}

/**
 * Get an image
 *
 * @return tempcode		Place holder text.
 */
function placeholder_image()
{
	$text='<img src="'.escape_html(find_theme_image('logo/-logo')).'" title="test" alt="test" />';
	if (function_exists('ocp_mark_as_escaped')) ocp_mark_as_escaped($text);
	return make_string_tempcode($text);
}

/**
 * Get suitable placeholder date.
 *
 * @return string			Place holder text.
 */
function placeholder_date()
{
	return get_timezoned_date(12345-60*60*24*200);
}

/**
 * Get time
 *
 * @return string			Place holder text.
 */
function placeholder_time()
{
	return get_timezoned_date(12345);
}

/**
 * Get suitable placeholder timestamp.
 *
 * @return string			Place holder text.
 */
function placeholder_date_raw()
{
	return strval(12345-60*60*24*200);
}

/**
 * Get suitable placeholder number.
 *
 * @return string			Place holder text.
 */
function placeholder_number()
{
	return strval(123);
}

/**
 * Get suitable placeholder ID.
 *
 * @return string			Place holder text.
 */
function placeholder_id()
{
	return strval(123);
}

/**
 * Get suitable placeholder filesize.
 *
 * @return string			Place holder text.
 */
function placeholder_filesize()
{
	require_code('files');
	return clean_file_size(123);
}

/**
 * Get javascript code
 *
 * @return string			Place holder text.
 */
function placeholder_javascript_code()
{
	return "alert('test');";
}

/**
 * Get image url
 *
 * @return string			Image url
 */
function placeholder_image_url()
{
	return find_theme_image('logo/-logo');
}

/**
 * Get a blank screen, for a parameter which is not visible and typically blank.
 *
 * @return string		Place holder text.
 */
function placeholder_blank()
{
	return '';
}

/**
 * Get suitable placeholder breadcrumbs.
 *
 * @return tempcode		Place holder text.
 */
function placeholder_breadcrumbs()
{
	$tpl_url=new ocp_tempcode();
	$tpl_url->attach(hyperlink(placeholder_url(),escape_html(lorem_phrase()),false,false,do_lang_tempcode('GO_BACKWARDS_TO',lorem_phrase()),NULL,NULL,'up'));
	$tpl_url->attach(do_lorem_template('BREADCRUMB_SEPARATOR'));
	$tpl_url->attach(hyperlink(placeholder_url(),escape_html(lorem_phrase()),false,false,do_lang_tempcode('GO_BACKWARDS_TO',lorem_phrase()),NULL,NULL,'up'));
	return $tpl_url;
}

/**
 * Lorem version of do_template. It will reprocess the template into something that is "stable XHTML" and thus can work inside an XHTML editor
 *
 * @param  ID_TEXT			The codename of the template being loaded
 * @param  ?array				A map of parameters for the template (key to value) (NULL: no parameters)
 * @param  ?LANGUAGE_NAME 	The language to load the template in (templates can embed language references) (NULL: users own language)
 * @param  boolean			Whether to not produce a stack dump if the template is missing
 * @param  ?ID_TEXT			Alternate template to use if the primary one does not exist (NULL: none)
 * @param  string				File type suffix of template file (e.g. .tpl)
 * @param  string				Subdirectory type to look in
 * @set    templates css
 * @return tempcode			The tempcode for this template
 */
function do_lorem_template($codename,$parameters=NULL,$lang=NULL,$light_error=false,$fallback=NULL,$suffix='.tpl',$type='templates')
{
	return do_template($codename,$parameters,$lang,$light_error,$fallback,$suffix,$type);
}

/**
 * Lorem version of globalise. It will wrap the input into something that is "stable XHTML" and thus can work inside an XHTML editor.
 *
 * @param  tempcode		The tempcode to put into a nice frame
 * @param  ?mixed			'Additional' message (NULL: none)
 * @param  string			The type of special message
 * @set    inform warn ""
 * @param  boolean		Whether to include the header/footer/panels
 * @return tempcode		Standalone page
 */
function lorem_globalise($middle,$message=NULL,$type='',$include_header_and_footer=false)
{
	restore_output_state(true); // Here we reset some Tempcode environmental stuff, because template compilation or preprocessing may have dirtied things

	global $LOREM_AVOID_GLOBALISE;

	if (($LOREM_AVOID_GLOBALISE) || is_full_screen_template(NULL, $middle))
		return $middle;

	$out=new ocp_tempcode();
	$out->attach(do_lorem_template('GLOBAL_HTML_WRAP',array(
		'MIDDLE'=>$middle,
	)));

	$out->handle_symbol_preprocessing();

	return $out;
}

/**
 * Get an array of emoticons.
 *
 * @return array		emoticons
 */
function placeholder_emoticons()
{
	$emoticons=array();
	$emoticons[':constipated:'][]='EMOTICON_IMG_CODE_THEMED';
	$emoticons[':constipated:'][]='ocf_emoticons/constipated';
	$emoticons[':constipated:'][]=':constipated:';

	$emoticons[':upsidedown:'][]='EMOTICON_IMG_CODE_THEMED';
	$emoticons[':upsidedown:'][]='ocf_emoticons/upsidedown';
	$emoticons[':upsidedown:'][]=':upsidedown:';

	$emoticons[':depressed:'][]='EMOTICON_IMG_CODE_THEMED';
	$emoticons[':depressed:'][]='ocf_emoticons/depressed';
	$emoticons[':depressed:'][]=':depressed:';

	$emoticons[':christmas:'][]='EMOTICON_IMG_CODE_THEMED';
	$emoticons[':christmas:'][]='ocf_emoticons/christmas';
	$emoticons[':christmas:'][]=':christmas:';

	return $emoticons;
}

/**
 * Get an avatar image.
 *
 * @return URLPATH		image
 */
function placeholder_avatar()
{
	return find_theme_image('ocf_default_avatars/system',true);
}

/**
 * Get a table of emoticons.
 *
 * @return tempcode		emotocons
 */
function placeholder_emoticon_chooser()
{
	$em=new ocp_tempcode();
	foreach (placeholder_emoticons() as $emo)
	{
		$code=$emo[2];
		$em->attach(do_lorem_template('EMOTICON_CLICK_CODE',array('_GUID'=>'93968e9ff0308fff92d1d45e433557e2','FIELD_NAME'=>'post','CODE'=>$code,'IMAGE'=>apply_emoticons($code))));
	}
	return $em;
}

/**
 * Get a theme image code.
 *
 * @param  string			The theme image directory to find a code under
 * @return string			The code
 */
function placeholder_img_code($type='')
{
	$path=get_file_base().'/themes/default/images/'.$type;
	if (!file_exists($path)) $path=get_file_base().'/themes/default/images/'.fallback_lang().'/'.$type;
	$dh=opendir($path);
	while (($f=readdir($dh))!==false)
	{
		if (substr($f,-4)=='.png') return basename($f,'.png');
		if (substr($f,-4)=='.jpg') return basename($f,'.jpg');
		if (substr($f,-4)=='.jpeg') return basename($f,'.jpeg');
		if (substr($f,-4)=='.gif') return basename($f,'.gif');
		if (substr($f,-4)=='.ico') return basename($f,'.ico');
	}
	return '';
}

/**
 * Get pagination.
 *
 * @return tempcode		Pagination.
 */
function placeholder_pagination()
{
	$selectors=new ocp_tempcode();
	foreach (placeholder_array() as $k => $v)
	{
		$selectors->attach(do_lorem_template('PAGINATION_PER_PAGE_OPTION',array(
			'SELECTED' => true,
			'VALUE' => strval($k),
			'NAME' => $v
		)));
	}
	$per_page=do_lorem_template('PAGINATION_PER_PAGE',array(
		'HIDDEN' => '',
		'URL' => placeholder_url(),
		'MAX_NAME' => 'max',
		'SELECTORS' => $selectors
	));

	$parts=new ocp_tempcode();
	foreach (placeholder_array() as $k => $v)
	{
		$j=$k + 1;
		if ($k==0)
		{
			$parts->attach(do_lorem_template('PAGINATION_PAGE_NUMBER',array(
				'P' => strval($j)
			)));
		} else
		{
			$parts->attach(do_lorem_template('PAGINATION_PAGE_NUMBER_LINK',array(
				'P' => strval($j),
				'URL' => placeholder_url(),
				'TITLE' => lorem_phrase()
			)));
		}
	}
	$first=do_lorem_template('PAGINATION_CONTINUE_FIRST',array(
		'TITLE' => lorem_phrase(),
		'P' => placeholder_number(),
		'FIRST_URL' => placeholder_url()
	));
	$previous=do_lorem_template('PAGINATION_PREVIOUS_LINK',array(
		'TITLE' => lorem_phrase(),
		'P' => placeholder_date_raw(),
		'URL' => placeholder_url()
	));
	$previous->attach(do_lorem_template('PAGINATION_PREVIOUS',array(
		'TITLE' => lorem_phrase(),
		'P' => placeholder_date_raw(),
	)));
	$next=do_lorem_template('PAGINATION_NEXT_LINK',array(
		'REL' => NULL,
		'TITLE' => lorem_phrase(),
		'NUM_PAGES' => placeholder_number(),
		'P' => placeholder_number(),
		'URL' => placeholder_url()
	));
	$next->attach(do_lorem_template('PAGINATION_NEXT',array(
		'TITLE' => lorem_phrase(),
		'P' => placeholder_date_raw(),
	)));
	$continues=do_lorem_template('PAGINATION_CONTINUE',array());
	$last=do_lorem_template('PAGINATION_CONTINUE_LAST',array(
		'TITLE' => lorem_phrase(),
		'P' => placeholder_number(),
		'LAST_URL' => placeholder_url()
	));
	$pages_list=do_lorem_template('PAGINATION_LIST_PAGES',array(
		'URL' => placeholder_url(),
		'HIDDEN' => '',
		'START_NAME' => 'start',
		'LIST' => placeholder_options()
	));

	return do_lorem_template('PAGINATION_WRAP',array(
		'TEXT_ID' => lorem_phrase(),
		'PER_PAGE' => $per_page,
		'PREVIOUS' => $previous,
		'CONTINUES_LEFT' => $continues,
		'CONTINUES_RIGHT' => $continues,
		'NEXT' => $next,
		'PARTS' => $parts,
		'FIRST' => $first,
		'LAST' => $last,
		'PAGES_LIST' => $pages_list,
		'START' => placeholder_number(),
		'MAX' => placeholder_number(),
		'MAX_ROWS' => placeholder_number(),
		'NUM_PAGES' => placeholder_number(),
	));
}

/**
 * Get all Comcode files.
 *
 * @return array		List of Comcode files.
 */
function find_comcodes()
{
	$zones=find_all_zones();
	$files=array();
	foreach($zones as $zone)
	{
		$z=$zone==''?'pages':$zone;
		$files[$z]=find_all_pages($zone,'comcode/'.fallback_lang(),'txt');
	}
	return $files;
}

/**
 * Get all HTML files.
 *
 * @return array		List of HTML files.
 */
function find_html()
{
	$zones=find_all_zones();
	$files=array();
	foreach($zones as $zone)
	{
		$z=$zone==''?'pages':$zone;
		$files[$z]=find_all_pages($zone,'html/'.fallback_lang(),'htm');
	}
	return $files;
}

/**
 * Find the template/screen previews
 *
 * @return array		The map of previews (template to a tuple of preview details)
 */
function find_all_previews__by_template()
{
	$all_previews=array();

	$hooks=find_all_hooks('systems','addon_registry');
	ksort($hooks);
	foreach (array_keys($hooks) as $hook)
	{
		require_code('hooks/systems/addon_registry/'.$hook);
		$ob=object_factory('Hook_addon_registry_'.$hook);

		if (method_exists($ob,'tpl_previews'))
		{
			$previews=$ob->tpl_previews();

			foreach ($previews as $tpl=>$function)
			{
				$all_previews[$tpl]=array($hook,'tpl_preview__'.$function);
			}
		}
	}

	return $all_previews;
}

/**
 * Find the template/screen previews
 *
 * @return array		The map of previews (screen to a list of templates)
 */
function find_all_previews__by_screen()
{
	$all_previews=array();

	$hooks=find_all_hooks('systems','addon_registry');
	foreach (array_keys($hooks) as $hook)
	{
		require_code('hooks/systems/addon_registry/'.$hook);
		$ob=object_factory('Hook_addon_registry_'.$hook);

		if (method_exists($ob,'tpl_previews'))
		{
			$previews=$ob->tpl_previews();
			foreach ($previews as $tpl=>$function)
			{
				if (!array_key_exists('tpl_preview__'.$function,$all_previews)) $all_previews['tpl_preview__'.$function]=array();
				$all_previews['tpl_preview__'.$function][]=$tpl;
			}
		}
	}

	return $all_previews;
}

/**
 * Shows the preview of a screen
 *
 * @param  ID_TEXT		The template to be previewed
 * @param  ?ID_TEXT		The hook the preview is in (NULL: search)
 * @param  ID_TEXT		The name of the screen preview
 * @return tempcode		The previewed screen
 */
function render_screen_preview($template,$hook,$function)
{
	if (is_null($hook))
	{
		$hooks=find_all_hooks('systems','addon_registry');
		foreach (array_keys($hooks) as $hook)
		{
			require_code('hooks/systems/addon_registry/'.$hook);
			$ob=object_factory('Hook_addon_registry_'.$hook);

			if (method_exists($ob,'tpl_previews'))
			{
				$previews=$ob->tpl_previews();
				foreach ($previews as $_function)
				{
					if ($function=='tpl_preview__'.$_function) break 2;
				}
			}
		}
	}

	require_code('hooks/systems/addon_registry/'.$hook);
	$ob=object_factory('Hook_addon_registry_'.$hook);

	// Load all ini/js/css
	$files=$ob->get_file_list();
	foreach ($files as $file)
	{
		if ((substr($file,-4)=='.ini') && (substr($file,0,8)=='lang/EN/'))
			require_lang(basename($file,'.ini'));

		if ((substr($file,-4)=='.css') && (substr($file,0,7)=='themes/'))
			require_css(basename($file,'.css'));

		if ((substr($file,-4)=='.tpl') && (substr($file,0,7)=='themes/') && (substr($file,0,11)=='JAVASCRIPT_') && ($file!='JAVASCRIPT_NEED.tpl') && ($file!='JAVASCRIPT_NEED_INLINE.tpl'))
			require_javascript(strtolower(basename($file,'.tpl')));
	}
	$temp_name=substr($template,0,-4);

	if (is_full_screen_template($temp_name))
	{
		$complete_html=true;
	} else
	{
		$complete_html=false;
	}
	if (is_plain_text_template($temp_name))
	{
		//@header('Content-type: text/plain');		Let it show with WITH_WHITESPACE
		$text=true;
	} else
	{
		$text=false;
	}

	// Render preview
	$previews=call_user_func(array($ob,$function));

	if ($text) $previews[0]=do_template('WITH_WHITESPACE',array('_GUID'=>'bcc1c95427d7f70524501955ba046d56','CONTENT'=>$previews[0]));
	$tmp=substr($function, 13);

	if (($complete_html) && (get_page_name()=='admin_themes'))
	{
		exit($previews[0]->evaluate());
	}

	return $previews[0];
}

/**
 * Get an additional list of templates that should be treated as text.
 *
 * @return array			The list of templates
 */
function get_text_templates()
{
	$text_templates=array(
		'JS_BLOCK',
		'CSS_NEED',
		'CSS_NEED_FULL',
		'CSS_NEED_INLINE',
		'HTML_EDIT',
		'JAVASCRIPT_NEED',
		'JAVASCRIPT_NEED_INLINE',
		'AJAX_PAGINATION',
		'JAVA_DETECT',
		'JS_REFRESH',
		'META_REFRESH_LINE',
		'OCF_AUTO_TIME_ZONE_ENTRY',
		'PREVIEW_SCRIPT_CODE',
		'QUICK_JS_LOADER',
		'TRACKBACK_XML_WRAPPER',
		'HANDLE_CONFLICT_RESOLUTION',
		'TRACKBACK_XML',
		'POLL_RSS_SUMMARY',
		'WYSIWYG_SETTINGS',
		'ATTACHMENT_UI_DEFAULTS',
		'FONT_SIZER',
		'FORM_SCREEN_FIELD_DESCRIPTION',
		'FORM_SCREEN_ARE_REQUIRED',
		'GALLERY_POPULAR',
		'AUTOCOMPLETE_LOAD',
	);
	return $text_templates;
}

/**
 * Checks if the template is a text template
 *
 * @param  string		Name of the template
 * @return boolean	Whether it is
 */
function is_plain_text_template($temp_name)
{
	return (
		$temp_name=='BLOCK_TOP_NOTIFICATIONS' || $temp_name=='MENU_BRANCH_zone' || $temp_name=='MENU_SPACER_zone' || $temp_name=='MENU_zone' || // In header, and uses IDs, so can't be used except in isolation
		substr($temp_name,0,5)=='MAIL_' ||
		substr($temp_name,0,11)=='JAVASCRIPT_' && $temp_name!='JAVASCRIPT_NEED' && $temp_name!='JAVASCRIPT_NEED_INLINE' ||
		$temp_name=='JAVASCRIPT.tpl' ||
		substr($temp_name,-9)==='_FCOMCODE' ||
		substr($temp_name,-5)==='_MAIL' ||
		substr($temp_name,-13)==='_FCOMCODEPAGE' ||
		substr($temp_name,0,14)=='TRACKBACK_XML_' ||
		$temp_name=='OPENSEARCH' ||
		$temp_name=='WYSIWYG_SETTINGS' ||
		$temp_name=='ATTACHMENT_UI_DEFAULTS' ||
		substr($temp_name,0,5)=='OPML_' ||
		substr($temp_name,0,5)=='ATOM_' || substr($temp_name,0,4)=='RSS_' ||
		in_array($temp_name,get_text_templates())
	);
}

/**
 * Checks if the template is a full screen template
 *
 * @param  ?string		Name of the template (NULL: do not use as criteria, use other as criteria, which must iself be non-NULL)
 * @param  ?tempcode		The instantiated template (NULL: do not use as criteria, use other as criteria, which must iself be non-NULL)
 * @return boolean		Whether it is
 */
function is_full_screen_template($temp_name=NULL,$tempcode=NULL)
{
	if ($temp_name===NULL)
	{
		$pos=strpos($tempcode->evaluate(),'<html');
		return ($pos!==false) && ($pos<400);
	}

	return (
		$temp_name=='GLOBAL_HTML_WRAP' || 
		$temp_name=='RESTORE_HTML_WRAP' || 
		$temp_name=='BASIC_HTML_WRAP' || 
		$temp_name=='STANDALONE_HTML_WRAP' || 
		$temp_name=='MAIL'
	);
}

