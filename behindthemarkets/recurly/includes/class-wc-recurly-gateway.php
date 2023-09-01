<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * WC_Recurly_Gateway class.
 *
 * @extends WC_Payment_Gateway
 */

class WC_Recurly_Gateway extends WC_Payment_Gateway
{
    const ID = 'recurly';

    /**
     * The delay between retries.
     *
     * @var int
     */
    public $retry_interval;

    /**
     * Should we capture Credit cards
     *
     * @var bool
     */
    public $capture;

    /**
     * Alternate credit card statement name
     *
     * @var bool
     */
    public $statement_descriptor;

    /**
     * Should we store the users credit cards?
     *
     * @var bool
     */
    public $saved_cards;

    /**
     * API access secret key
     *
     * @var string
     */
    public $secret_key;

    /**
     * Api access publishable key
     *
     * @var string
     */
    public $publishable_key;

    /**
     * Do we accept Payment Request?
     *
     * @var bool
     */
    public $payment_request;

    /**
     * Is test mode active?
     *
     * @var bool
     */
    public $testmode;

    /**
     * Inline CC form styling
     *
     * @var string
     */
    public $inline_cc_form;

    /**
     * Pre Orders Object
     *
     * @var object
     */
    public $pre_orders;

    /**
     * Creating a Client
     *
     * @var object
     */
    public $client;

    /**
     * Class constructor
     */
    public function __construct()
    {

        $this->retry_interval = 1;
        $this->id = self::ID;
        $this->method_title = __('Recurly Gateway', 'woocommerce-gateway-recurly');
        $this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
        $this->has_fields = true; // in case you need a custom credit card form
        $this->method_description = 'Description of Recurly payment gateway'; // will be displayed on the options page

        // gateways can support subscriptions, refunds, saved payment methods,
        // but in this tutorial we begin with simple payments
        $this->supports = array(
            'products',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
            'multiple_subscriptions',
        );

        // Method with all the options fields
        $this->init_form_fields();

        // Load the settings.
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->testmode = 'yes' === $this->get_option('testmode');
        $this->inline_cc_form = 'yes'; // === $this->get_option( 'inline_cc_form' );
        $this->capture = 'yes' === $this->get_option('capture', 'yes');
        $this->statement_descriptor = WC_Recurly_Helper::clean_statement_descriptor($this->get_option('statement_descriptor'));
        $this->saved_cards = 'yes' === $this->get_option('saved_cards');
        $this->private_key = $this->testmode ? $this->get_option('demo_private_key') : $this->get_option('private_key');
        $this->publishable_key = $this->testmode ? $this->get_option('demo_publishable_key') : $this->get_option('publishable_key');
        $this->payment_request = 'yes' === $this->get_option('payment_request', 'yes');

        // Recurly client object
        $this->client = new \Recurly\Client($this->private_key);

        WC_Recurly_API::set_secret_key($this->secret_key);

        // This action hook saves the settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));

        // We need custom JavaScript to obtain a token
        add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));

        // You can also register a webhook here
        // add_action( 'woocommerce_api_{webhook name}', array( $this, 'webhook' ) );
        add_action('woocommerce_checkout_update_order_meta', array($this, 'update_order_meta_token'));
    }

    /**
     * Plugin options, we deal with it in Step 3 too
     */
    public function init_form_fields()
    {

        $this->form_fields = array(
            'enabled' => array(
                'title' => 'Enable/Disable',
                'label' => 'Enable Recurly Gateway',
                'type' => 'checkbox',
                'description' => '',
                'default' => 'no',
            ),
            'title' => array(
                'title' => 'Title',
                'type' => 'text',
                'description' => 'This controls the title which the user sees during checkout.',
                'default' => 'Credit Card',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => 'Description',
                'type' => 'textarea',
                'description' => 'This controls the description which the user sees during checkout.',
                'default' => 'Pay with your credit card via our super-cool payment gateway.',
            ),
            'testmode' => array(
                'title' => 'Test mode',
                'label' => 'Enable Test Mode',
                'type' => 'checkbox',
                'description' => 'Place the payment gateway in test mode using test API keys.',
                'default' => 'yes',
                'desc_tip' => true,
            ),
            'demo_publishable_key' => array(
                'title' => 'Test Publishable Key',
                'type' => 'text',
            ),
            'demo_private_key' => array(
                'title' => 'Test Private Key',
                'type' => 'password',
            ),
            'publishable_key' => array(
                'title' => 'Live Publishable Key',
                'type' => 'text',
            ),
            'private_key' => array(
                'title' => 'Live Private Key',
                'type' => 'password',
            ),
        );
    }

    /**
     * You will need it if you want your custom credit card form, Step 4 is about it
     */
    public function payment_fields()
    {
        // ok, let's display some description before the payment form
        if ($this->description) {
            // you can instructions for test mode, I mean test card numbers etc.
            if ($this->testmode) {
                $this->description .= ' TEST MODE ENABLED. In test mode, you can use the card numbers listed in <a href="#">documentation</a>.';
                $this->description = trim($this->description);
            }
            // display the description with <p> tags etc.
            echo wpautop(wp_kses_post($this->description));
        }

        // I will echo() the form, but you can close PHP tags and print it directly in HTML
        echo '<fieldset id="wc-' . esc_attr($this->id) . '-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">';

        // Add this action hook if you want your custom payment gateway to support it
        do_action('woocommerce_credit_card_form_start', $this->id);

        // I recommend to use inique IDs, because other gateways could already use #ccNo, #expdate, #cvc
        ?>
        <div id="recurly-elements">
                <!-- Recurly Elements will be attached here -->
        </div>
        <div class="recurly-source-errors" role="alert"></div>
			<br />
        <?php

        do_action('woocommerce_credit_card_form_end', $this->id);

        echo '<div class="clear"></div></fieldset>';

    }
    /**
     * Returns the JavaScript configuration object used on the product, cart, and checkout pages.
     *
     * @return array  The configuration object to be loaded to JS.
     */
    public function javascript_params()
    {
        global $wp;

        $recurly_params = [
            'key' => $this->publishable_key,
            'i18n_terms' => __('Please accept the terms and conditions first', 'woocommerce-gateway-recurly'),
            'i18n_required_fields' => __('Please fill in required checkout fields first', 'woocommerce-gateway-recurly'),
        ];

        // If we're on the pay page we need to pass recurly.js the address of the order.
        if (isset($_GET['pay_for_order']) && 'true' === $_GET['pay_for_order']) { // wpcs: csrf ok.
            $order_id = wc_clean($wp->query_vars['order-pay']); // wpcs: csrf ok, sanitization ok, xss ok.
            $order = wc_get_order($order_id);

            if (is_a($order, 'WC_Order')) {
                $recurly_params['billing_first_name'] = $order->get_billing_first_name();
                $recurly_params['billing_last_name'] = $order->get_billing_last_name();
                $recurly_params['billing_address_1'] = $order->get_billing_address_1();
                $recurly_params['billing_address_2'] = $order->get_billing_address_2();
                $recurly_params['billing_state'] = $order->get_billing_state();
                $recurly_params['billing_city'] = $order->get_billing_city();
                $recurly_params['billing_postcode'] = $order->get_billing_postcode();
                $recurly_params['billing_country'] = $order->get_billing_country();
                $recurly_params['get_view_order_url'] = $order->get_view_order_url();
            }
        }

        $recurly_params['recurly_locale'] = WC_Recurly_Helper::convert_wc_locale_to_recurly_locale(get_locale());
        $recurly_params['no_prepaid_card_msg'] = __('Sorry, we\'re not accepting prepaid cards at this time. Your credit card has not been charged. Please try with alternative payment method.', 'woocommerce-gateway-recurly');
        $recurly_params['no_sepa_owner_msg'] = __('Please enter your IBAN account name.', 'woocommerce-gateway-recurly');
        $recurly_params['no_sepa_iban_msg'] = __('Please enter your IBAN account number.', 'woocommerce-gateway-recurly');
        $recurly_params['payment_intent_error'] = __('We couldn\'t initiate the payment. Please try again.', 'woocommerce-gateway-recurly');
        $recurly_params['sepa_mandate_notification'] = apply_filters('wc_recurly_sepa_mandate_notification', 'email');
        $recurly_params['allow_prepaid_card'] = apply_filters('wc_recurly_allow_prepaid_card', true) ? 'yes' : 'no';
        $recurly_params['inline_cc_form'] = $this->inline_cc_form ? 'yes' : 'no';
        $recurly_params['is_checkout'] = (is_checkout() && empty($_GET['pay_for_order'])) ? 'yes' : 'no'; // wpcs: csrf ok.
        // $recurly_params['return_url']                = $this->get_recurly_return_url();
        $recurly_params['ajaxurl'] = WC_AJAX::get_endpoint('%%endpoint%%');
        $recurly_params['recurly_nonce'] = wp_create_nonce('_wc_recurly_nonce');
        $recurly_params['statement_descriptor'] = $this->statement_descriptor;
        $recurly_params['elements_options'] = apply_filters('wc_recurly_elements_options', []);
        $recurly_params['invalid_owner_name'] = __('Billing First Name and Last Name are required.', 'woocommerce-gateway-recurly');
        $recurly_params['is_change_payment_page'] = isset($_GET['change_payment_method']) ? 'yes' : 'no'; // wpcs: csrf ok.
        $recurly_params['is_add_payment_page'] = is_wc_endpoint_url('add-payment-method') ? 'yes' : 'no';
        $recurly_params['is_pay_for_order_page'] = is_wc_endpoint_url('order-pay') ? 'yes' : 'no';
        $recurly_params['elements_styling'] = apply_filters('wc_recurly_elements_styling', false);
        $recurly_params['elements_classes'] = apply_filters('wc_recurly_elements_classes', false);
        $recurly_params['add_card_nonce'] = wp_create_nonce('wc_recurly_create_si');
        // $recurly_params['account_code']              = is_user_logged_in() ? get_user_meta(get_current_user_id(), 'account_code', true) : uniqid();

        // Merge localized messages to be use in JS.
        $recurly_params = array_merge($recurly_params, WC_Recurly_Helper::get_localized_messages());

        return $recurly_params;
    }

    /*
     * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
     */
    public function payment_scripts()
    {
        // we need JavaScript to process a token only on cart/checkout pages, right?
        if (!is_cart() && !is_checkout() && !isset($_GET['pay_for_order'])) {
            return;
        }

        // if our payment gateway is disabled, we do not have to enqueue JS too
        if ('no' === $this->enabled) {
            return;
        }

        // no reason to enqueue JavaScript if API keys are not set
        if (empty($this->private_key) || empty($this->publishable_key)) {
            return;
        }

        // do not work with card detailes without SSL unless your website is in a test mode
        if (!$this->testmode && !is_ssl()) {
            return;
        }

        // let's suppose it is our payment processor JavaScript and css that allows to obtain a token or style
        wp_enqueue_style('recurly_styles', '//js.recurly.com/v4/recurly.css', [], '', 'all');

        wp_enqueue_script('recurly_js', '//js.recurly.com/v4/recurly.js', [], '', true);

        // and this is our custom JS in your plugin directory that works with token.js
        wp_register_script('woocommerce_recurly', plugins_url('../assets/js/recurly-card.js', __FILE__), array('jquery', 'recurly_js'), '', true);

        // in most payment processors you have to use PUBLIC KEY to obtain a token
        wp_localize_script(
            'woocommerce_recurly',
            'wc_recurly_params',
            apply_filters('wc_recurly_params', $this->javascript_params())
        );

        wp_enqueue_script('woocommerce_recurly');

    }
    /**
     * Checks if a source object represents a prepaid credit card and
     * throws an exception if it is one, but that is not allowed.
     *
     * @since 4.2.0
     * @param object $prepared_source The object with source details.
     * @throws WC_Recurly_Exception An exception if the card is prepaid, but prepaid cards are not allowed.
     */
    public function maybe_disallow_prepaid_card($prepared_source)
    {
        // Check if we don't allow prepaid credit cards.
        if (apply_filters('wc_recurly_allow_prepaid_card', true) || !$this->is_prepaid_card($prepared_source->source_object)) {
            return;
        }

        $localized_message = __('Sorry, we\'re not accepting prepaid cards at this time. Your credit card has not been charged. Please try with alternative payment method.', 'woocommerce-gateway-recurly');
        throw new WC_Recurly_Exception(print_r($prepared_source->source_object, true), $localized_message);
    }
    /*
     * Fields validation, more in Step 5
     */
    public function validate_fields()
    {

        // if( empty( $_POST[ 'billing_first_name' ]) ) {
        //     wc_add_notice(  'First name is required!', 'error' );
        //     return false;
        // }
        // return true;

    }

    public function update_order_meta_token($order_id)
    {
        if (isset($_POST['recurly-token']) && !empty($_POST['recurly-token'])) {
            update_post_meta($order_id, '_recurly_token_id', esc_attr($_POST['recurly-token']));
        }

        //_membership_plan_code
    }
    /**
     * Is $order_id a subscription?
     *
     * @param  int $order_id
     * @return boolean
     */
    public function has_subscription($order_id)
    {
        return (function_exists('wcs_order_contains_subscription') && (wcs_order_contains_subscription($order_id) || wcs_is_subscription($order_id) || wcs_order_contains_renewal($order_id)));
    }
    /*
     * We're processing the payments here, everything about it is in Step 5
     */
    public function process_payment($order_id)
    {
        try {
            $order = wc_get_order($order_id);
            $Payment_Gateway = new WC_Recurly_Payment_Gateway();
            $switch_order_data = wcs_get_objects_property($order, 'subscription_switch_data');
            // if we don't have an switch data, there is nothing to do here. Switch orders created prior to v2.1 won't have any data to process.
            if (!empty($switch_order_data) || is_array($switch_order_data)) {
                $subscription_id = $order->get_meta('_subscription_switch'); //_subscription_switch
                $subscription_old = wcs_get_subscription($subscription_id);
                $get_order = $subscription_old->get_data();
                $_recurly_subscription_id = get_post_meta($get_order['parent_id'], '_recurly_subscription_id', true);
                //=======================================New plan code === Variation id=======================================
                $variation_id_new_plan_code = null;
                foreach (WC()->cart->get_cart() as $recurring_cart) {
                    $variation_id_new_plan_code = $recurring_cart['variation_id'];
                }

                //=======================================Card change==========================================
                //Current order recurly token id
                $_recurly_token_id = get_post_meta($order_id, '_recurly_token_id', true);
                $source_id = wc_clean(wp_unslash($_recurly_token_id));
                $customer = new WC_Recurly_Customer(wp_get_current_user()->ID);

                //Card update
                $payment_method_change = $customer->attach_source($source_id);

                if (!empty($payment_method_change->error)) {
                    wc_add_notice( $payment_method_change->error->message, 'error' );
                } else {

                    //https://v3.recurly.com/subscriptions/{subscription_id}/change
                    $change_create = [
                        "plan_code" => $variation_id_new_plan_code,
                        "timeframe" => "now",
                    ];
                    $swich_subscription_obj = WC_Recurly_API::request(json_encode($change_create), 'subscriptions/' . $_recurly_subscription_id . '/change', 'POST');

                    if (!empty($swich_subscription_obj->error)) {
                        wc_add_notice( $swich_subscription_obj->error->message, 'error' );

                    } else {

                        update_post_meta($order_id, '_recurly_subscription_id', $swich_subscription_obj->subscription_id);
                        update_post_meta($order_id, '_recurly_plan_id', $swich_subscription_obj->plan->id);

                        $activated = $swich_subscription_obj->activated;//True/False
                        $charge_invoice = $swich_subscription_obj->invoice_collection->charge_invoice;//Charge object
                        $transactions = $charge_invoice->transactions[0];////Transactions object

                        if ($activated) { // Remove cart.
                            $Payment_Gateway->check_source($swich_subscription_obj);
                            $Payment_Gateway->save_source_to_order($order, $swich_subscription_obj);
                            WC_Recurly_Logger::log("Info: Begin processing payment for order $order_id for the amount of {$order->get_total()}");
                            $Payment_Gateway->process_response($swich_subscription_obj, $order);

                            if (isset(WC()->cart)) {
                                WC()->cart->empty_cart();
                            }
                            $order->update_status('completed');
                            return [
                                'result' => 'success',
                                'redirect' => $this->get_return_url($order),
                            ];

                        }elseif (!empty($transactions) && is_object($transactions)) {
                            if (isset(WC()->cart)) {
                                WC()->cart->empty_cart();
                            }
                            if($transactions->status == 'success'){
                                $order->update_status("wc-completed", 'Completed', true);
                                return [
                                    'result' => 'success',//
                                    'redirect' => $this->get_return_url($order),
                                ];
                            }elseif($transactions->status == 'pending'){
                                $order->update_status("wc-pending", 'Pending payment', true);
                                return ['result' => 'Pending payment','redirect' => $this->get_return_url($order)];
                            }elseif($transactions->status == 'processing'){
                                $order->update_status("wc-processing", 'Processing', true);
                                return ['result' => 'Processing','redirect' => $this->get_return_url($order)];
                            }elseif($transactions->status == 'pending'){
                                $order->update_status("wc-on-hold", 'On hold', true);
                                return ['result' => 'On hold','redirect' => $this->get_return_url($order)];
                            }elseif($transactions->status == 'chargeback'){
                                $order->update_status("wc-refunded", 'Refunded', true);
                                return ['result' => 'Refunded','redirect' => $this->get_return_url($order)];
                            }elseif($transactions->status == 'declined' || $transactions->status == 'error'){
                                $order->update_status("wc-failed ", 'Failed', true);
                                return ['result' => 'Failed','redirect' => $this->get_return_url($order)];
                            }else{
                                $order->update_status('failed');
                                return [
                                    'result' => 'fail',
                                    'redirect' => '',
                                ];
                            }

                            
                        }else{
                            wc_add_notice($e->getLocalizedMessage(), 'error');
                            WC_Recurly_Logger::log('Error: ' . $e->getMessage());

                            do_action('wc_gateway_recurly_process_payment_error', $e, $order);

                            /* translators: error message */
                            $order->update_status('failed');
                            return [
                                'result' => 'fail',
                                'redirect' => '',
                            ];
                        }

                    }

                }
                //=========================================Card change========================================

            } else {
                //============================================New Subscription careate=======================
                try {
                    if ($this->has_subscription($order_id)) {
                        $force_save_source = true;
                    }
                    $recurly_customer_id = null;
                    if (get_user_meta(get_current_user_id(), '_recurly_account_code', true)) {
                        $recurly_customer_id = get_user_meta(get_current_user_id(), '_recurly_account_code', true);
                    }
                    
                    $prepared_source = $Payment_Gateway->prepare_source(get_current_user_id(), $force_save_source, $recurly_customer_id);
                    $purchase_source = $Payment_Gateway->create_new_purchase($order, $prepared_source);

                    $Payment_Gateway->check_source($purchase_source);
                    $Payment_Gateway->save_source_to_order($order, $purchase_source);

                    // if ( 0 >= $order->get_total() ) {
                    //     return $this->complete_free_order( $order, $prepared_source, $force_save_source );
                    // }

                    WC_Recurly_Logger::log("Info: Begin processing payment for order $order_id for the amount of {$order->get_total()}");
                    // wc_add_notice( 'Checking', 'error' );
                    $Payment_Gateway->process_response($purchase_source, $order);

                    // Remove cart.
                    if (isset(WC()->cart)) {
                        WC()->cart->empty_cart();
                    }

                    // Unlock the order.
                    $Payment_Gateway->unlock_order_payment($order);

                    // Return thank you page redirect.
                    return [
                        'result' => 'success',
                        'redirect' => $this->get_return_url($order),
                    ];
                } catch (WC_Recurly_Exception $e) {
                    wc_add_notice($e->getLocalizedMessage(), 'error');
                    WC_Recurly_Logger::log('Error: ' . $e->getMessage());

                    do_action('wc_gateway_recurly_process_payment_error', $e, $order);

                    /* translators: error message */
                    $order->update_status('failed');
                    return [
                        'result' => 'fail',
                        'redirect' => '',
                    ];
                }
            }

        } catch (WC_Recurly_Exception $e) {
            wc_add_notice($e->getLocalizedMessage(), 'error');
            WC_Recurly_Logger::log('Error: ' . $e->getMessage());

            do_action('wc_gateway_recurly_process_payment_error', $e, $order);

            /* translators: error message */
            $order->update_status('failed');
            return [
                'result' => 'fail',
                'redirect' => '',
            ];
        }
    }

    /*
     * In case you need a webhook, like PayPal IPN etc
     */
    public function webhook()
    {

//    ...

    }
}