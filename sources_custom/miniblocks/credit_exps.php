<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2012

 See text/EN/licence.txt for full licencing information.

*/

/**
 * @license		http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright	ocProducts Ltd
 * @package		ocportalcom_support_credits
 */

$budget_minutes = is_null($result = get_option('support_budget_priority',true))? '10': strval(integer_format($result));
$normal_minutes =  is_null($result = get_option('support_normal_priority',true))? '8': strval(integer_format($result));
$day_minutes =  is_null($result = get_option('support_day_priority',true))? '7': strval(integer_format($result));
$high_minutes =  is_null($result = get_option('support_high_priority',true))? '5': strval(integer_format($result));
$emergency_minutes =  is_null($result = get_option('support_emergency_priority',true))? '3': strval(integer_format($result));
$s_currency = is_null($result = get_option('currency',true))? 'USD': strval($result);

require_lang('customers');

$priority_level = do_lang_tempcode('PRIORITY_LEVEL');
$num_minutes = do_lang_tempcode('NUMBER_OF_MINUTES');
$minutes = do_lang_tempcode('SUPPORT_minutes');
$label_b= do_lang_tempcode('SUPPORT_PRIORITY_budget');
$label_n = do_lang_tempcode('SUPPORT_PRIORITY_normal');
$label_d = do_lang_tempcode('SUPPORT_PRIORITY_day');
$label_h = do_lang_tempcode('SUPPORT_PRIORITY_high');
$label_e = do_lang_tempcode('SUPPORT_PRIORITY_emergency');
$label_buy = do_lang_tempcode('SUPPORT_CREDITS_Buy');

require_code('ecommerce');
require_code('hooks/systems/ecommerce/support_credits');

$ob=new Hook_support_credits();
$products=$ob->get_products();

$credit_kinds=array();
foreach ($products as $p=>$v)
{
	$num_credits=str_replace('_CREDITS','',$p);
	if ((intval($num_credits)<1) && (is_null($GLOBALS['SITE_DB']->query_value_null_ok_full('SELECT id FROM mantis_sponsorship_table WHERE user_id='.strval(get_member())))))
	{
		continue;
	}
	$msg = do_lang('BLOCK_CREDITS_EXP_INNER_MSG',strval($num_credits),strval($s_currency),float_format($v[1]));
	$credit_kinds[]=array(
		'NUM_CREDITS'=>$num_credits,
		'PRICE'=>float_format($v[1]),
		'S_CURRENCY'=>$s_currency,
		'TH_PRIORITY'=>$priority_level,
		'TH_MINUTES'=>$num_minutes,
		'MINUTES'=>$minutes,
		'L_B'=>$label_b,
		'B_MINUTES'=>$budget_minutes,
		'L_N'=>$label_n,
		'N_MINUTES'=>$normal_minutes,
		'L_D'=>$label_d,
		'D_MINUTES'=>$day_minutes,
		'L_H'=>$label_h,
		'H_MINUTES'=>$high_minutes,
		'L_E'=>$label_e,
		'E_MINUTES'=>$emergency_minutes
	);
}

$tpl=do_template('BLOCK_CREDIT_EXPS_INNER',array('_GUID'=>'6c6134a1b7157637dae280b54e90a877','CREDIT_KINDS'=>$credit_kinds,'LABEL_BUY'=>$label_buy));
$tpl->evaluate_echo();
