<?php
/*
 * Plugin Name: WooCommerce Recurly Payment Gateway
 * Plugin URI: https://savior.im
 * Description: Take credit card payments on your store.
 * Author: Savior 
 * Author URI: http://savior.im
 * Version: 1.0.0
 */
/*
 * This action hook registers our PHP class as a WooCommerce payment gateway
 */
define( 'WC_RECURLY_VERSION', '1.0.0' ); // WRCS: DEFINED_VERSION.
add_filter( 'woocommerce_payment_gateways', 'recurly_add_gateway_class' );
function recurly_add_gateway_class( $gateways ) {
	$gateways[] = 'WC_Recurly_Gateway'; // your class name is here
	return $gateways;
}

/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'recurly_init_gateway_class' );
function recurly_init_gateway_class() {
    // load the main recurly client class
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
    
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-exception.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-logger.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-helper.php';
    include_once dirname( __FILE__ ) . '/includes/class-wc-recurly-api.php';
    
    include_once dirname( __FILE__ ) . '/includes/class-wc-recurly-plan.php'; 

    require_once dirname( __FILE__ ) . '/includes/abstracts/abstract-wc-recurly-payment-gateway.php';

    // require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-webhook-state.php';
    // require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-webhook-handler.php';

    // require_once dirname( __FILE__ ) . '/includes/compat/class-wc-recurly-pre-orders-compat.php';

    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-gateway.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-subscription-switch.php';
    // require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-feature-flags.php';

    // require_once dirname( __FILE__ ) . '/includes/payment-methods/class-wc-recurly-payment-request.php';
    
    require_once dirname( __FILE__ ) . '/includes/compat/class-wc-recurly-subs-compat.php';
    require_once dirname( __FILE__ ) . '/includes/compat/class-wc-recurly-woo-compat-utils.php';
    // require_once dirname( __FILE__ ) . '/includes/compat/class-wc-recurly-sepa-subs-compat.php';

    // require_once dirname( __FILE__ ) . '/includes/connect/class-wc-recurly-connect.php';
    // require_once dirname( __FILE__ ) . '/includes/connect/class-wc-recurly-connect-api.php';

    // require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-order-handler.php';
    // require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-payment-tokens.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-customer.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-intent-controller.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-webhook-controller.php';
    require_once dirname( __FILE__ ) . '/includes/class-wc-recurly-create-missing-subscription.php';

    // require_once dirname( __FILE__ ) . '/includes/admin/class-wc-recurly-inbox-notes.php';
    // require_once dirname( __FILE__ ) . '/includes/admin/class-wc-recurly-upe-compatibility-controller.php';

    // if ( is_admin() ) {
    //     require_once dirname( __FILE__ ) . '/includes/admin/class-wc-recurly-admin-notices.php';
    //     require_once dirname( __FILE__ ) . '/includes/admin/class-wc-recurly-settings-controller.php';

    //     new WC_Stripe_Settings_Controller();

    //     if ( WC_Stripe_Feature_Flags::is_upe_settings_redesign_enabled() ) {
    //         require_once dirname( __FILE__ ) . '/includes/admin/class-wc-recurly-onboarding-controller.php';
    //         new WC_Stripe_Onboarding_Controller();
    //     }
    // }

}