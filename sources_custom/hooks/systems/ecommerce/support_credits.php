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

/**
 * Handling of adding support credits to a member's account.
 *
 * @param  ID_TEXT	The key.
 * @param  array	Details relating to the product.
 * @param  ID_TEXT	The product.
 */
function handle_support_credits($_key,$details,$product)
{
	unset($product);

	$row=$GLOBALS['SITE_DB']->query_select('credit_purchases',array('member_id','num_credits'),array('purchase_validated'=>0,'purchase_id'=>intval($_key)),'',1);
	if (count ($row) != 1) return;
	$member_id=$row[0]['member_id'];
	if (is_null($member_id)) return;
	$num_credits=$row[0]['num_credits'];


	require_code('mantis');
	$cpf_id = strval(get_credits_profile_field_id());
	if(is_null($cpf_id)) return;

	// Increment the number of credits this customer has
	require_code('ocf_members_action2');
	$fields=ocf_get_custom_field_mappings($member_id);
	ocf_set_custom_field($member_id,$cpf_id,intval($fields['field_'.$cpf_id])+intval($num_credits));

	// Update the row in the credit_purchases table
	$GLOBALS['SITE_DB']->query_update('credit_purchases',array('purchase_validated'=>1),array('purchase_id'=>intval($_key)));

}

/**
 * eCommerce product hook.
 */
class Hook_support_credits
{
	/**
	 * Get the products handled by this eCommerce hook.
	 *
	 * @return array	A map of product name to list of product details.
	 */
	function get_products()
	{
		if (get_forum_type()!='ocf') return array();
		require_lang('customers');
		$products=array();
		$bundles=array(1,2,3,4,5,6,9,20,25,35,50,90,180,550);
		foreach ($bundles as $bundle)
		{
			$products[strval($bundle).'_CREDITS']=array(
				PRODUCT_PURCHASE_WIZARD,
				float_to_raw_string($bundle*floatval(get_option('support_credit_value'))),
				'handle_support_credits',
				NULL,
				do_lang('CUSTOMER_SUPPORT_CREDIT')
			);
		}

		return $products;
	}

	/**
	 * Get the message for use in the purchase wizard.
	 *
	 * @param  string	The product in question.
	 * @return tempcode	The message.
	 */
	function get_message($product)
	{
		return do_lang('SUPPORT_CREDITS_PRODUCT_DESCRIPTION');
	}

	function get_agreement()
	{
		require_code('textfiles');
		return read_text_file('support_credits_licence','EN');
	}

	/**
	 * Find the corresponding member to a given key.
	 *
	 * @param  ID_TEXT		The key.
	 * @return ?MEMBER		The member (NULL: unknown / can't perform operation).
	 */
	function member_for($key)
	{
		return $GLOBALS['SITE_DB']->query_value_null_ok('credit_purchases','member_id',array('purchase_id'=>intval($key)));
	}

	function get_needed_fields()
	{
		if (!has_actual_page_access(get_member(),'admin_ecommerce',get_module_zone('admin_ecommerce'))) return NULL;

		// Check if we've already been passed a member ID and use it to pre-populate the field
		$member_id=get_param_integer('member_id',NULL);
		if (!is_null($member_id)) $username=$GLOBALS['FORUM_DRIVER']->get_username($member_id);
		else $username=$GLOBALS['FORUM_DRIVER']->get_username(get_member());

		return form_input_username(do_lang('USERNAME'),do_lang('USERNAME_CREDITS_FOR'),'member_username',$username,true);
	}

	/**
	 * Get the filled in fields and do something with them.
	 *
	 * @param  ID_TEXT	The product name
	 * @return ID_TEXT		The purchase id.
	 */
	function set_needed_fields($product)
	{
		$product_array=explode('_',$product,2);
		$num_credits = intval($product_array[0]);
		if($num_credits == 0) return;
		$manual=0;
		$member_id=get_member();

		// Allow admins to specify the member who should receive the credits with the field in get_needed_fields
		if (has_actual_page_access(get_member(),'admin_ecommerce',get_module_zone('admin_ecommerce')))
		{
			$id=post_param_integer('member_id',NULL);
			if (!is_null($id))
			{
				$manual=1;
				$member_id=$id;
			}
			else
			{
				$username=post_param('member_username',NULL);
				if (!is_null($username))
				{
					$manual=1;
					$member_id=$GLOBALS['FORUM_DRIVER']->get_member_from_username($username);
				}
			}
		}

		return strval($GLOBALS['SITE_DB']->query_insert('credit_purchases',array('member_id'=>$member_id,'date_and_time'=>time(),'num_credits'=>$num_credits,'is_manual'=>$manual,'purchase_validated'=>0),true));
	}

	/**
	 * Check whether the product code is available for purchase by the member.
	 *
	 * @param  ID_TEXT	The product.
	 * @param  MEMBER		The member.
	 * @return boolean	Whether it is.
	 */
	function is_available($product,$member)
	{
		return ($member!=$GLOBALS['FORUM_DRIVER']->get_guest_id())?ECOMMERCE_PRODUCT_AVAILABLE:ECOMMERCE_PRODUCT_NO_GUESTS;
	}
}
