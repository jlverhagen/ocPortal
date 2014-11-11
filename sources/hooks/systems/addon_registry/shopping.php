<?php /*

 ocPortal
 Copyright (c) ocProducts, 2004-2014

 See text/EN/licence.txt for full licencing information.


 NOTE TO PROGRAMMERS:
   Do not edit this file. If you need to make changes, save your changed file to the appropriate *_custom folder
   **** If you ignore this advice, then your website upgrades (e.g. for bug fixes) will likely kill your changes ****

*/

/**
 * @license    http://opensource.org/licenses/cpal_1.0 Common Public Attribution License
 * @copyright  ocProducts Ltd
 * @package    shopping
 */
class Hook_addon_registry_shopping
{
    /**
     * Get a list of file permissions to set
     *
     * @return array                    File permissions to set
     */
    public function get_chmod_array()
    {
        return array();
    }

    /**
     * Get the version of ocPortal this addon is for
     *
     * @return float                    Version number
     */
    public function get_version()
    {
        return ocp_version_number();
    }

    /**
     * Get the description of the addon
     *
     * @return string                   Description of the addon
     */
    public function get_description()
    {
        return 'Online store functionality.';
    }

    /**
     * Get a list of tutorials that apply to this addon
     *
     * @return array                    List of tutorials
     */
    public function get_applicable_tutorials()
    {
        return array(
            'tut_ecommerce',
        );
    }

    /**
     * Get a mapping of dependency types
     *
     * @return array                    File permissions to set
     */
    public function get_dependencies()
    {
        return array(
            'requires' => array(
                'ecommerce',
                'catalogues',
            ),
            'recommends' => array(),
            'conflicts_with' => array(),
        );
    }

    /**
     * Explicitly say which icon should be used
     *
     * @return URLPATH                  Icon
     */
    public function get_default_icon()
    {
        return 'themes/default/images/icons/48x48/menu/rich_content/ecommerce/shopping_cart.png';
    }

    /**
     * Get a list of files that belong to this addon
     *
     * @return array                    List of files
     */
    public function get_file_list()
    {
        return array(
            'themes/default/images/icons/24x24/menu/adminzone/audit/ecommerce/orders.png',
            'themes/default/images/icons/48x48/menu/adminzone/audit/ecommerce/orders.png',
            'themes/default/images/icons/24x24/menu/rich_content/ecommerce/orders.png',
            'themes/default/images/icons/48x48/menu/rich_content/ecommerce/orders.png',
            'themes/default/images/icons/24x24/menu/adminzone/audit/ecommerce/undispatched_orders.png',
            'themes/default/images/icons/48x48/menu/adminzone/audit/ecommerce/undispatched_orders.png',
            'themes/default/images/icons/24x24/menu/rich_content/ecommerce/shopping_cart.png',
            'themes/default/images/icons/48x48/menu/rich_content/ecommerce/shopping_cart.png',
            'themes/default/images/icons/24x24/buttons/cart_add.png',
            'themes/default/images/icons/24x24/buttons/cart_checkout.png',
            'themes/default/images/icons/24x24/buttons/cart_empty.png',
            'themes/default/images/icons/24x24/buttons/cart_update.png',
            'themes/default/images/icons/24x24/buttons/cart_view.png',
            'themes/default/images/icons/48x48/buttons/cart_add.png',
            'themes/default/images/icons/48x48/buttons/cart_checkout.png',
            'themes/default/images/icons/48x48/buttons/cart_empty.png',
            'themes/default/images/icons/48x48/buttons/cart_update.png',
            'themes/default/images/icons/48x48/buttons/cart_view.png',
            'sources/hooks/systems/notifications/order_dispatched.php',
            'sources/hooks/systems/notifications/new_order.php',
            'sources/hooks/systems/notifications/low_stock.php',
            'sources/hooks/modules/admin_setupwizard_installprofiles/shopping.php',
            'sources/hooks/systems/config/allow_opting_out_of_tax.php',
            'sources/hooks/systems/config/shipping_cost_factor.php',
            'sources/hooks/systems/config/cart_hold_hours.php',
            'sources/hooks/systems/ecommerce/catalogue_items.php',
            'sources/hooks/systems/ecommerce/cart_orders.php',
            'sources/hooks/blocks/main_staff_checklist/ecommerce_orders.php',
            'sources/shopping.php',
            'site/pages/modules/shopping.php',
            'themes/default/templates/CATALOGUE_products_CATEGORY_SCREEN.tpl',
            'themes/default/templates/CATALOGUE_products_CATEGORY_EMBED.tpl',
            'themes/default/templates/CATALOGUE_products_ENTRY_SCREEN.tpl',
            'themes/default/templates/CATALOGUE_products_GRID_ENTRY_FIELD.tpl',
            'themes/default/templates/CATALOGUE_products_FIELDMAP_ENTRY_FIELD.tpl',
            'themes/default/templates/CATALOGUE_products_GRID_ENTRY_WRAP.tpl',
            'themes/default/templates/RESULTS_products_TABLE.tpl',
            'themes/default/templates/JAVASCRIPT_SHOPPING.tpl',
            'themes/default/templates/CATALOGUE_ENTRY_CART_BUTTONS.tpl',
            'adminzone/pages/modules/admin_orders.php',
            'lang/EN/shopping.ini',
            'sources/hooks/systems/addon_registry/shopping.php',
            'sources/hooks/systems/ocf_cpf_filter/shopping_cart.php',
            'themes/default/css/shopping.css',
            'themes/default/templates/ECOM_ADMIN_ORDER_ACTIONS.tpl',
            'themes/default/templates/ECOM_CART_LINK.tpl',
            'themes/default/templates/ECOM_ADMIN_ORDERS_DETAILS_SCREEN.tpl',
            'themes/default/templates/ECOM_ADMIN_ORDERS_SCREEN.tpl',
            'themes/default/templates/ECOM_ORDERS_DETAILS_SCREEN.tpl',
            'themes/default/templates/ECOM_ORDERS_SCREEN.tpl',
            'themes/default/templates/ECOM_SHIPPING_ADDRESS.tpl',
            'themes/default/templates/ECOM_CART_BUTTON_VIA_PAYPAL.tpl',
            'themes/default/templates/ECOM_ITEM_DETAILS.tpl',
            'themes/default/templates/ECOM_SHOPPING_CART_PROCEED.tpl',
            'themes/default/templates/ECOM_SHOPPING_CART_STAGE_PAY.tpl',
            'themes/default/templates/ECOM_SHOPPING_CART_SCREEN.tpl',
            'themes/default/templates/ECOM_SHOPPING_ITEM_QUANTITY_FIELD.tpl',
            'themes/default/templates/ECOM_SHOPPING_ITEM_REMOVE_FIELD.tpl',
            'themes/default/templates/RESULTS_cart_TABLE.tpl',
            'themes/default/templates/RESULTS_TABLE_cart_ENTRY.tpl',
            'themes/default/templates/RESULTS_TABLE_cart_FIELD.tpl',
        );
    }


    /**
     * Get mapping between template names and the method of this class that can render a preview of them
     *
     * @return array                    The mapping
     */
    public function tpl_previews()
    {
        return array(
            'ECOM_ADMIN_ORDER_ACTIONS.tpl' => 'administrative__ecom_admin_orders_details_screen',
            'ECOM_ADMIN_ORDERS_SCREEN.tpl' => 'administrative__ecom_admin_orders_screen',
            'ECOM_SHIPPING_ADDRESS.tpl' => 'administrative__ecom_admin_orders_details_screen',
            'ECOM_ADMIN_ORDERS_DETAILS_SCREEN.tpl' => 'administrative__ecom_admin_orders_details_screen',
            'ECOM_ITEM_DETAILS.tpl' => 'ecommerce_item_details',
            'ECOM_SHOPPING_ITEM_QUANTITY_FIELD.tpl' => 'shopping_cart_screen',
            'ECOM_SHOPPING_ITEM_REMOVE_FIELD.tpl' => 'shopping_cart_screen',
            'ECOM_CART_BUTTON_VIA_PAYPAL.tpl' => 'ecom_cart_button_via_paypal',
            'ECOM_SHOPPING_CART_PROCEED.tpl' => 'shopping_cart_screen',
            'ECOM_SHOPPING_CART_SCREEN.tpl' => 'shopping_cart_screen',
            'ECOM_ORDERS_SCREEN.tpl' => 'ecom_orders_screen',
            'ECOM_ORDERS_DETAILS_SCREEN.tpl' => 'ecom_orders_details_screen',
            'RESULTS_cart_TABLE.tpl' => 'shopping_cart_screen',
            'RESULTS_TABLE_cart_ENTRY.tpl' => 'shopping_cart_screen',
            'RESULTS_TABLE_cart_FIELD.tpl' => 'shopping_cart_screen',
            'ECOM_CART_LINK.tpl' => 'products_entry_screen',
            'CATALOGUE_products_CATEGORY_EMBED.tpl' => 'grid_category_screen__products',
            'CATALOGUE_products_ENTRY_SCREEN.tpl' => 'products_entry_screen',
            'CATALOGUE_products_FIELDMAP_ENTRY_FIELD.tpl' => 'products_entry_screen',
            'CATALOGUE_ENTRY_CART_BUTTONS.tpl' => 'products_entry_screen',
            'CATALOGUE_products_CATEGORY_SCREEN.tpl' => 'grid_category_screen__products',
            'CATALOGUE_products_GRID_ENTRY_FIELD.tpl' => 'grid_category_screen__products',
            'CATALOGUE_products_GRID_ENTRY_WRAP.tpl' => 'grid_category_screen__products',
            'RESULTS_products_TABLE.tpl' => 'results_products_table',
            'ECOM_SHOPPING_CART_STAGE_PAY.tpl' => 'shopping_cart_stage_pay'
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__shopping_cart_stage_pay()
    {
        return array(
            lorem_globalise(do_lorem_template('ECOM_SHOPPING_CART_STAGE_PAY', array(
                'TRANSACTION_BUTTON' => placeholder_button(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__ecom_admin_orders_screen()
    {
        return array(
            lorem_globalise(do_lorem_template('ECOM_ADMIN_ORDERS_SCREEN', array(
                'TITLE' => lorem_title(),
                'CURRENCY' => lorem_phrase(),
                'RESULTS_TABLE' => placeholder_table(),
                'PAGINATION' => placeholder_pagination(),
                'SEARCH_URL' => placeholder_url(),
                'SEARCH_VAL' => lorem_phrase(),
                'HIDDEN' => '',
            )), null, '', true)
        );
    }

    /**
     * Function to display custom result tables
     *
     * @param   ID_TEXT       Tpl set name
     * @return tempcode                 Tempcode
     */
    public function show_custom_tables($tplset)
    {
        $fields_title = new ocp_tempcode();
        foreach (array(
                     lorem_word(),
                     lorem_word_2(),
                     lorem_word(),
                     lorem_word_2()
                 ) as $k => $v) {
            $fields_title->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE', array(
                'VALUE' => $v,
            )));
        }
        $entries = new ocp_tempcode();
        foreach (placeholder_array() as $k => $v) {
            $cells = new ocp_tempcode();

            $entry_data = array(
                lorem_word(),
                placeholder_date(),
                lorem_word(),
                lorem_word()
            );

            foreach ($entry_data as $_k => $_v) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_' . $tplset . 'FIELD', array(
                    'VALUE' => $_v,
                )));
            }
            $entries->attach(do_lorem_template('RESULTS_TABLE_' . $tplset . 'ENTRY', array(
                'VALUES' => $cells,
            )));
        }

        $selectors = new ocp_tempcode();
        foreach (placeholder_array(11) as $k => $v) {
            $selectors->attach(do_lorem_template('PAGINATION_SORTER', array(
                'SELECTED' => '',
                'NAME' => $v,
                'VALUE' => $v,
            )));
        }

        $sort = do_lorem_template('PAGINATION_SORT', array(
            'HIDDEN' => '',
            'SORT' => lorem_word(),
            'URL' => placeholder_url(),
            'SELECTORS' => $selectors,
        ));

        return do_lorem_template('RESULTS_' . $tplset . 'TABLE', array(
            'FIELDS_TITLE' => $fields_title,
            'FIELDS' => $entries,
            'MESSAGE' => new ocp_tempcode(),
            'SORT' => $sort,
            'PAGINATION' => placeholder_pagination(),
            'WIDTHS' => array(
                placeholder_number()
            )
        ));
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__administrative__ecom_admin_orders_details_screen()
    {
        $order_actions = do_lorem_template('ECOM_ADMIN_ORDER_ACTIONS', array(
            'ORDER_TITLE' => lorem_phrase(),
            'ORDER_ACTUALISE_URL' => placeholder_url(),
            'ORDER_STATUS' => lorem_word(),
        ));

        $shipping_address = do_lorem_template('ECOM_SHIPPING_ADDRESS', array(
            'FIRST_NAME' => lorem_phrase(),
            'LAST_NAME' => lorem_phrase(),
            'ADDRESS_NAME' => lorem_phrase(),
            'ADDRESS_STREET' => lorem_phrase(),
            'ADDRESS_CITY' => lorem_phrase(),
            'ADDRESS_STATE' => lorem_phrase(),
            'ADDRESS_ZIP' => lorem_phrase(),
            'ADDRESS_COUNTRY' => lorem_phrase(),
            'RECEIVER_EMAIL' => lorem_phrase(),
            'CONTACT_PHONE' => lorem_phrase(),
        ));

        return array(
            lorem_globalise(do_lorem_template('ECOM_ADMIN_ORDERS_DETAILS_SCREEN', array(
                'TITLE' => lorem_title(),
                'TEXT' => lorem_sentence(),
                'RESULTS_TABLE' => placeholder_table(),
                'PAGINATION' => placeholder_pagination(),
                'ORDER_NUMBER' => placeholder_number(),
                'ADD_DATE' => placeholder_date(),
                'CURRENCY' => lorem_phrase(),
                'TOTAL_PRICE' => placeholder_number(),
                'ORDERED_BY_MEMBER_ID' => placeholder_id(),
                'ORDERED_BY_USERNAME' => lorem_word(),
                'ORDER_STATUS' => lorem_phrase(),
                'NOTES' => lorem_phrase(),
                'ORDER_ACTIONS' => $order_actions,
                'SHIPPING_ADDRESS' => $shipping_address,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ecommerce_item_details()
    {
        return array(
            lorem_globalise(do_lorem_template('ECOM_ITEM_DETAILS', array(
                'FIELDS' => placeholder_fields(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ecom_cart_button_via_paypal()
    {
        $items = array();
        foreach (placeholder_array() as $k => $v) {
            $items[] = array(
                'PRODUCT_NAME' => lorem_word(),
                'PRICE' => placeholder_number(),
                'QUANTITY' => placeholder_number(),
            );
        }
        return array(
            lorem_globalise(do_lorem_template('ECOM_CART_BUTTON_VIA_PAYPAL', array(
                'ITEMS' => $items,
                'CURRENCY' => lorem_phrase(),
                'PAYMENT_ADDRESS' => lorem_word(),
                'IPN_URL' => placeholder_url(),
                'ORDER_ID' => placeholder_id(),
                'NOTIFICATION_TEXT' => lorem_sentence_html(),
                'MEMBER_ADDRESS' => placeholder_array(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__shopping_cart_screen()
    {
        //results_table starts
        //results_entry starts


        $shopping_cart = new ocp_tempcode();
        foreach (placeholder_array() as $k => $v) {
            $cells = new ocp_tempcode();
            foreach (placeholder_array(8) as $_v) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE', array(
                    'VALUE' => $_v,
                )));
            }
            $fields_title = $cells;

            $product_image = placeholder_image();
            $product_link = placeholder_link();
            $currency = lorem_word();
            $edit_qnty = do_lorem_template('ECOM_SHOPPING_ITEM_QUANTITY_FIELD', array(
                'PRODUCT_ID' => strval($k),
                'QUANTITY' => lorem_phrase(),
            ));
            $del_item = do_lorem_template('ECOM_SHOPPING_ITEM_REMOVE_FIELD', array(
                'PRODUCT_ID' => strval($k),
            ));

            $values = array(
                $product_image,
                $product_link,
                $edit_qnty,
                $currency . (string)placeholder_number(),
                $currency . (string)placeholder_number(),
                $currency . (string)placeholder_number(),
                $currency . placeholder_number(),
                $del_item
            );
            $cells = new ocp_tempcode();
            foreach ($values as $value) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_cart_FIELD', array(
                    'VALUE' => $value,
                    'CLASS' => '',
                )));
            }
            $shopping_cart->attach(do_lorem_template('RESULTS_TABLE_cart_ENTRY', array(
                'VALUES' => $cells,
            )));
        }
        //results_entry ends

        $selectors = new ocp_tempcode();
        foreach (placeholder_array() as $k => $v) {
            $selectors->attach(do_lorem_template('PAGINATION_SORTER', array(
                'SELECTED' => '',
                'NAME' => lorem_word(),
                'VALUE' => lorem_word(),
            )));
        }
        $sort = do_lorem_template('PAGINATION_SORT', array(
            'HIDDEN' => '',
            'SORT' => lorem_word(),
            'URL' => placeholder_url(),
            'SELECTORS' => $selectors,
        ));

        $results_table = do_lorem_template('RESULTS_cart_TABLE', array(
            'WIDTHS' => array(),
            'FIELDS_TITLE' => $fields_title,
            'FIELDS' => $shopping_cart,
            'MESSAGE' => new ocp_tempcode(),
            'SORT' => $sort,
            'PAGINATION' => lorem_word(),
        ));
        //results_table ends

        $proceed_box = do_lorem_template('ECOM_SHOPPING_CART_PROCEED', array(
            'SUB_TOTAL' => float_format(floatval(placeholder_number())),
            'SHIPPING_COST' => float_format(floatval(placeholder_number())),
            'GRAND_TOTAL' => float_format(floatval(placeholder_number())),
            'PROCEED' => lorem_phrase(),
            'CURRENCY' => lorem_word(),
            'PAYMENT_FORM' => placeholder_form(),
        ));

        return array(
            lorem_globalise(do_lorem_template('ECOM_SHOPPING_CART_SCREEN', array(
                'TITLE' => lorem_title(),
                'RESULTS_TABLE' => $results_table,
                'FORM_URL' => placeholder_url(),
                'CONT_SHOPPING_URL' => placeholder_url(),
                'MESSAGE' => lorem_phrase(),
                'PRO_IDS' => placeholder_id(),
                'EMPTY_CART_URL' => placeholder_url(),
                'PROCEED_BOX' => $proceed_box,
                'ALLOW_OPTOUT_TAX' => lorem_phrase(),
                'ALLOW_OPTOUT_TAX_VALUE' => lorem_phrase(),
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ecom_orders_screen()
    {
        $orders = array();
        foreach (placeholder_array() as $v) {
            $orders[] = array(
                'ORDER_DET_URL' => placeholder_url(),
                'ORDER_TITLE' => lorem_word(),
                'AMOUNT' => placeholder_id(),
                'TIME' => placeholder_time(),
                'STATE' => lorem_word_2(),
                'NOTE' => lorem_phrase(),
            );
        }
        return array(
            lorem_globalise(do_lorem_template('ECOM_ORDERS_SCREEN', array(
                'TITLE' => lorem_title(),
                'CURRENCY' => lorem_phrase(),
                'ORDERS' => $orders,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__ecom_orders_details_screen()
    {
        $orders = array();
        foreach (placeholder_array() as $v) {
            $orders[] = array(
                'PRODUCT_DET_URL' => placeholder_url(),
                'PRODUCT_NAME' => lorem_word(),
                'AMOUNT' => placeholder_id(),
                'QUANTITY' => '2',
                'DISPATCH_STATUS' => lorem_word_2(),
            );
        }
        return array(
            lorem_globalise(do_lorem_template('ECOM_ORDERS_DETAILS_SCREEN', array(
                'TITLE' => lorem_title(),
                'CURRENCY' => lorem_phrase(),
                'PRODUCTS' => $orders,
            )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__products_entry_screen()
    {
        require_lang('shopping');
        require_lang('catalogues');
        require_lang('ecommerce');
        require_css('catalogues');
        require_lang('catalogues');

        $fields = new ocp_tempcode();
        $fields_table = new ocp_tempcode();

        foreach (placeholder_array() as $v) {
            $_field = do_lorem_template('CATALOGUE_products_FIELDMAP_ENTRY_FIELD', array(
                'ENTRYID' => placeholder_random_id(),
                'CATALOGUE' => lorem_phrase(),
                'TYPE' => lorem_word(),
                'FIELD' => lorem_word(),
                'FIELDID' => placeholder_random_id(),
                '_FIELDID' => placeholder_random_id(),
                'FIELDTYPE' => lorem_word(),
                'VALUE_PLAIN' => lorem_phrase(),
                'VALUE' => lorem_phrase(),
            ));
            $fields->attach($_field);
        }

        $cart_buttons = do_lorem_template('CATALOGUE_ENTRY_CART_BUTTONS', array(
            'OUT_OF_STOCK' => lorem_phrase(),
            'ACTION_URL' => placeholder_url(),
            'PRODUCT_ID' => placeholder_id(),
            'ALLOW_OPTOUT_TAX' => lorem_phrase(),
            'PURCHASE_ACTION_URL' => placeholder_url(),
            'CART_URL' => placeholder_url(),
        ));
        $cart_link = do_lorem_template('ECOM_CART_LINK', array(
            'URL' => placeholder_url(),
            'TITLE' => lorem_phrase(),
        ), null, false);

        $rating_inside = new ocp_tempcode();

        $map = array(
            'FIELD_0' => lorem_phrase(),
            'FIELD_1' => lorem_phrase(),
            'PRODUCT_CODE' => placeholder_id(),
            'FIELD_9' => lorem_phrase(),
            'FIELD_2' => placeholder_number(),
            'PRICE' => placeholder_number(),
            'RATING' => $rating_inside,
            'FIELD_7_THUMB' => placeholder_image(),
            'FIELD_7' => placeholder_image(),
            'FIELD_7_PLAIN' => placeholder_url(),
            'MAP_TABLE' => placeholder_table(),
            'CART_BUTTONS' => $cart_buttons,
            'CART_LINK' => $cart_link,
            'ADD_TO_CART' => placeholder_url(),
            'FIELDS' => $fields,
            'ENTRY_SCREEN' => true,
            'GIVE_CONTEXT' => false,
        );
        $entry = do_lorem_template('CATALOGUE_DEFAULT_FIELDMAP_ENTRY_WRAP', $map);

        return array(
            lorem_globalise(do_lorem_template('CATALOGUE_products_ENTRY_SCREEN', $map + array(
                    'TITLE' => lorem_title(),
                    'WARNINGS' => '',
                    'ENTRY' => $entry,
                    'EDIT_URL' => placeholder_url(),
                    '_EDIT_LINK' => placeholder_link(),
                    'TRACKBACK_DETAILS' => lorem_phrase(),
                    'RATING_DETAILS' => lorem_phrase(),
                    'COMMENT_DETAILS' => lorem_phrase(),
                )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__grid_category_screen__products()
    {
        require_lang('shopping');
        require_lang('catalogues');
        require_lang('ecommerce');
        require_css('catalogues');
        require_lang('catalogues');

        $fields = new ocp_tempcode();
        $fields_table = new ocp_tempcode();

        foreach (placeholder_array() as $v) {
            $_field = do_lorem_template('CATALOGUE_products_GRID_ENTRY_FIELD', array(
                'ENTRYID' => placeholder_random_id(),
                'CATALOGUE' => lorem_phrase(),
                'TYPE' => lorem_word(),
                'FIELD' => lorem_word(),
                'FIELDID' => placeholder_random_id(),
                '_FIELDID' => placeholder_random_id(),
                'FIELDTYPE' => lorem_word(),
                'VALUE_PLAIN' => lorem_phrase(),
                'VALUE' => lorem_phrase(),
            ));
            $fields->attach($_field);
        }

        $cart_buttons = do_lorem_template('CATALOGUE_ENTRY_CART_BUTTONS', array(
            'OUT_OF_STOCK' => lorem_phrase(),
            'ACTION_URL' => placeholder_url(),
            'PRODUCT_ID' => placeholder_id(),
            'ALLOW_OPTOUT_TAX' => lorem_phrase(),
            'PURCHASE_ACTION_URL' => placeholder_url(),
            'CART_URL' => placeholder_url(),
        ));
        $cart_link = do_lorem_template('ECOM_CART_LINK', array(
            'URL' => placeholder_url(),
            'TITLE' => lorem_phrase(),
        ), null, false);

        $rating_inside = new ocp_tempcode();

        $map = array(
            'FIELD_0' => lorem_phrase(),
            'FIELD_1' => lorem_phrase(),
            'PRODUCT_CODE' => placeholder_id(),
            'FIELD_9' => lorem_phrase(),
            'FIELD_2' => placeholder_number(),
            'PRICE' => placeholder_number(),
            'RATING' => $rating_inside,
            'FIELD_7_THUMB' => placeholder_image(),
            'FIELD_7_PLAIN' => placeholder_url(),
            'MAP_TABLE' => placeholder_table(),
            'CART_BUTTONS' => $cart_buttons,
            'CART_LINK' => $cart_link,
            'ADD_TO_CART' => placeholder_url(),
            'FIELDS' => $fields,
            'URL' => placeholder_url(),
            'VIEW_URL' => placeholder_url(),
        );
        $entry = do_lorem_template('CATALOGUE_products_GRID_ENTRY_WRAP', $map);

        $entries = do_lorem_template('CATALOGUE_products_CATEGORY_EMBED', array(
            'DISPLAY_TYPE' => 'FIELDMAPS',
            'ENTRIES' => $entry,
            'ROOT' => placeholder_id(),
            'BLOCK_PARAMS' => '',

            'CART_LINK' => placeholder_link(),

            'START' => '0',
            'MAX' => '10',
            'START_PARAM' => 'x_start',
            'MAX_PARAM' => 'x_max',
        ));

        return array(
            lorem_globalise(do_lorem_template('CATALOGUE_products_CATEGORY_SCREEN', $map + array(
                    'ID' => placeholder_id(),
                    'ADD_DATE_RAW' => placeholder_time(),
                    'TITLE' => lorem_title(),
                    '_TITLE' => lorem_phrase(),
                    'TAGS' => '',
                    'CATALOGUE' => lorem_word_2(),
                    'ADD_ENTRY_URL' => placeholder_url(),
                    'ADD_CAT_URL' => placeholder_url(),
                    'EDIT_CAT_URL' => placeholder_url(),
                    'EDIT_CATALOGUE_URL' => placeholder_url(),
                    'ENTRIES' => $entries,
                    'SUBCATEGORIES' => '',
                    'DESCRIPTION' => lorem_sentence(),
                    'CART_LINK' => placeholder_link(),
                    'TREE' => lorem_phrase(),
                    'DISPLAY_TYPE' => '0',
                )), null, '', true)
        );
    }

    /**
     * Get a preview(s) of a (group of) template(s), as a full standalone piece of HTML in Tempcode format.
     * Uses sources/lorem.php functions to place appropriate stock-text. Should not hard-code things, as the code is intended to be declaritive.
     * Assumptions: You can assume all Lang/CSS/JavaScript files in this addon have been pre-required.
     *
     * @return array                    Array of previews, each is Tempcode. Normally we have just one preview, but occasionally it is good to test templates are flexible (e.g. if they use IF_EMPTY, we can test with and without blank data).
     */
    public function tpl_preview__results_products_table()
    {
        require_css('catalogues');

        //results_entry starts
        $array = placeholder_array();
        $cells = new ocp_tempcode();
        foreach ($array as $k => $v) {
            if ($k == 1) {
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE', array(
                    'VALUE' => $v,
                )));
            } else {
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD_TITLE_SORTABLE', array(
                    'VALUE' => $v,
                    'SORT_URL_DESC' => placeholder_url(),
                    'SORT_DESC_SELECTED' => lorem_word(),
                    'SORT_ASC_SELECTED' => lorem_word(),
                    'SORT_URL_ASC' => placeholder_url(),
                )));
            }
        }
        $fields_title = $cells;

        $order_entries = new ocp_tempcode();
        foreach ($array as $k1 => $_v) {
            $cells = new ocp_tempcode();
            foreach ($array as $k2 => $v) {
                $tick = do_lorem_template('RESULTS_TABLE_TICK', array(
                    'ID' => placeholder_id() . '_' . strval($k1) . '_' . strval($k2),
                ));
                $cells->attach(do_lorem_template('RESULTS_TABLE_FIELD', array(
                    'VALUE' => $tick,
                )));
            }
            $order_entries->attach(do_lorem_template('RESULTS_TABLE_ENTRY', array(
                'VALUES' => $cells,
            )));
        }
        //results_entry ends

        $selectors = new ocp_tempcode();
        $sortable = null;
        foreach ($array as $k => $v) {
            $selectors->attach(do_lorem_template('PAGINATION_SORTER', array(
                'SELECTED' => '',
                'NAME' => $v,
                'VALUE' => $v,
            )));
        }
        $sort = do_lorem_template('PAGINATION_SORT', array(
            'HIDDEN' => '',
            'SORT' => lorem_word(),
            'URL' => placeholder_url(),
            'SELECTORS' => $selectors,
        ));

        return array(
            lorem_globalise(do_lorem_template('RESULTS_products_TABLE', array(
                'TEXT_ID' => lorem_phrase(),
                'FIELDS_TITLE' => $fields_title,
                'FIELDS' => $order_entries,
                'SORT' => $sort,
                'PAGINATION' => placeholder_pagination(),
                'MESSAGE' => lorem_phrase(),
                'WIDTHS' => array(
                    placeholder_number()
                )
            )), null, '', true)
        );
    }
}
