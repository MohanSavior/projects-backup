<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Recurly_Plan class.
 *
 */

class WC_Recurly_Plan {

    /**
	 * Recurly client
	 *
	 */
    private static $instance = NULL;

    public $admin_notices = array();

    /**
     * Class constructor
     */
    public function __construct() {
        
		add_action('save_post_product', [ $this, 'create_or_update_plans' ], 10, 3);
        // Removing on add to cart if an item is already in cart
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'remove_before_add_to_cart'], 10, 1 );
        //Subscription Status Change
        add_action( 'woocommerce_subscription_status_updated', [ $this, 'recurly_subscription_status_updated'], 10, 3 );

        add_shortcode( 'all-subscriptions', [ $this, 'recurly_all_subscription'] );

        //Subscription Time Period Option Func Start
        add_filter('woocommerce_subscription_period_interval_strings', [ $this, 'recurly_woocommerce_subscription_period_interval_strings'], 10, 1 );
		
		add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'recurly_add_plan_code_field_to_variations'], 10, 3 );
		
		add_action( 'woocommerce_save_product_variation', [ $this, 'recurly_save_add_plan_code_field_variations'], 10, 2 );

        add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'recurly_display_order_data_in_admin'] );

        add_action( 'save_post_shop_subscription', [ $this, 'type_of_refund_save'], 10, 1);
        //Update customer
        add_action( 'woocommerce_update_customer', [ $this, 'recurly_update_customer'], 10, 2 );

        add_action( 'profile_update', [ $this, 'action_woocommerce_profile_update'], 10, 2 ); 
    }

    public function action_woocommerce_profile_update( $user_id, $old_user_data)
    {
        $user = get_userdata( $user_id );
        $new_user_email = $user->user_email;
        $account_id = get_user_meta($user_id,'_recurly_account_code', true);
        $billing_info = json_encode(
            array(
                'email'         => $new_user_email,
                'first_name'    => get_user_meta($user_id, 'first_name', true),
                'last_name'     => get_user_meta($user_id, 'last_name', true),
                'company'       => get_user_meta($user_id, 'billing_company', true),
                'address'=> array(
                    'phone'       => get_user_meta($user_id, 'billing_phone', true),
                    'street1'     => get_user_meta($user_id, 'billing_address_1', true),
                    'street2'     => get_user_meta($user_id, 'billing_address_2', true),
                    'city'        => get_user_meta($user_id, 'billing_city', true),
                    'region'      => get_user_meta($user_id, 'billing_state', true),
                    'postal_code' => get_user_meta($user_id, 'billing_postcode', true),
                    'country'     => get_user_meta($user_id, 'billing_country', true)
                )
            )
        );
        $response = WC_Recurly_API::request( $billing_info ,'accounts/code-' . $account_id ,'PUT');
        if ( ! empty( $response->error ) ) {
            // error_log(print_r('error', true));
            // error_log(print_r($response->error, true));
        }else{
            // error_log(print_r('Response', true));
            // error_log(print_r($response, true));
        }
    }

    public function type_of_refund_save($post_id)
    {
        if(isset($_POST['order_status']) && $_POST['order_status'] === 'wc-expired'){
            update_post_meta($post_id,'type_of_refund', $_POST['type_of_refund']);
        }
    }
    public function recurly_display_order_data_in_admin( $order ){  
        if(get_post_type() === 'shop_subscription'){
            $value = get_post_meta( $order->get_id(), 'type_of_refund', true );
            if(empty($value)){$value = 'none';}
            ?>
            <div id="type_of_refund" class="order_data_column" style="display:none;">
                <h4><?php _e( 'Type Of Refund' ); ?></h4>
                <p>
                    <input id="type_of_refund_none" type="radio" name="type_of_refund" value="none" <?php checked( $value, 'none' ); ?>>
                    <label for="type_of_refund_none">None</label>
                </p>  
                <p>
                    <input id="type_of_refund_full" type="radio" name="type_of_refund" value="full" <?php checked( $value, 'full' ); ?>>
                    <label for="type_of_refund_full">Full</label>
                </p> 
                <p>
                    <input id="type_of_refund_partial" type="radio" name="type_of_refund" value="partial" <?php checked( $value, 'partial' ); ?>>
                    <label for="type_of_refund_partial">Partial</label>
                </p>                       
                    
            </div>
            <script>
                jQuery(document).ready(function($){
                    $('#order_status').on('change',function(){
                        if( this.value === 'wc-expired'){
                            $('#type_of_refund').slideDown();
                        }else{
                            $('#type_of_refund').slideUp();
                        }
                    });
                });
            </script>
            <?php 
        }
    }

    public function recurly_woocommerce_subscription_period_interval_strings($intervals){
        $intervals = array( 1 => _x( 'every', 'period interval (eg "$10 _every_ 2 weeks")', 'woocommerce-subscriptions' ) );

        foreach ( range( 2, 50 ) as $i ) {
            $intervals[ $i ] = sprintf( _x( 'every %s', 'period interval with ordinal number (e.g. "every 2nd"', 'woocommerce-subscriptions' ), WC_Subscriptions::append_numeral_suffix( $i ) );
        }
        if ( empty( $interval ) ) {
            return $intervals;
        } else {
            return $intervals[ $interval ];
        }
    }

    public function recurly_update_plan($variation_data)
    {       
        //main
        if('week' == get_post_meta($variation_data['id'], '_subscription_period', true)){
            $interval_length = (int)get_post_meta($variation_data['id'], '_subscription_period_interval', true) * 7;
            $interval_unit = 'day';
        }else{
            $interval_length = (int)get_post_meta($variation_data['id'], '_subscription_period_interval', true);
            $interval_unit = get_post_meta($variation_data['id'], '_subscription_period', true);
        }
        if((int)get_post_meta($variation_data['id'], '_subscription_length', true) == 0){
            if($interval_unit == 'year'){
                $total_billing_cycles = 50;
            }elseif($interval_unit == 'month'){
                $total_billing_cycles = 600;
            }else{
                $total_billing_cycles = 999;
            }
        }else{
            $total_billing_cycles = (int)get_post_meta($variation_data['id'], '_subscription_length', true);
        }
        //Trial
        if('week' == get_post_meta($variation_data['id'], '_subscription_trial_period', true)){
            $trial_length = (int)get_post_meta($variation_data['id'], '_subscription_trial_length', true) * 7;
            $trial_unit = 'day';
        }else{
            $trial_length = (int)get_post_meta($variation_data['id'], '_subscription_trial_length', true);
            $trial_unit = get_post_meta($variation_data['id'], '_subscription_trial_period', true);//_subscription_trial_period
        }    
        $setup_fee = (float)get_post_meta($variation_data['id'], '_subscription_sign_up_fee', true);
        $unit_amount = (float)get_post_meta($variation_data['id'], '_subscription_price', true);
		$_recurly_plan_code = get_post_meta( $variation_data['id'], '_recurly_plan_code', true );
		return $plan_meta = [
			'code' =>	(string)$_recurly_plan_code,
			'name' =>	$variation_data['name'],
			'currencies' => [
                'currency' => 'USD',
                'setup_fee' => $setup_fee,
                'unit_amount' => $unit_amount,
			],
            'auto_renew' => true,
			'total_billing_cycles' => $total_billing_cycles,
			'trial_unit' => $trial_unit,
			'trial_length' => $trial_length
		];
    }

    public function recurly_create_plan($variation_data,$update)
	{
        //main
        if('week' == get_post_meta($variation_data['id'], '_subscription_period', true)){
            $interval_length = (int)get_post_meta($variation_data['id'], '_subscription_period_interval', true) * 7;
            $interval_unit = 'day';
        }else{
            $interval_length = (int)get_post_meta($variation_data['id'], '_subscription_period_interval', true);
            $interval_unit = get_post_meta($variation_data['id'], '_subscription_period', true);
        }

        if((int)get_post_meta($variation_data['id'], '_subscription_length', true) == 0){
            if($interval_unit == 'year'){
                $total_billing_cycles = 50;
            }elseif($interval_unit == 'month'){
                $total_billing_cycles = 600;
                $interval_unit = 'months';
            }else{
                $total_billing_cycles = 999;
                $interval_unit = 'days';
            }
        }
        else{
            $total_billing_cycles = (int)get_post_meta($variation_data['id'], '_subscription_length', true);
        }
        if($interval_unit == 'year'){
            $interval_unit = 'months';
            $interval_length =  $interval_length * 12;
        }
        //Trial
        if('week' == get_post_meta($variation_data['id'], '_subscription_trial_period', true)){
            $trial_length = (int)get_post_meta($variation_data['id'], '_subscription_trial_length', true) * 7;
            $trial_unit = 'day';
        }else{
            $trial_length = (int)get_post_meta($variation_data['id'], '_subscription_trial_length', true);
            $trial_unit = get_post_meta($variation_data['id'], '_subscription_trial_period', true);//_subscription_trial_period
        }
        if($trial_length > 1)
        {
            $trial_unit = $trial_unit.'s';
        }
        $setup_fee = (float)get_post_meta($variation_data['id'], '_subscription_sign_up_fee', true);
        $unit_amount = (float)get_post_meta($variation_data['id'], '_subscription_price', true);
		
		$_recurly_plan_code = get_post_meta( $variation_data['id'], '_recurly_plan_code', true );
                
		$plan_meta = [
			'code' =>	(string)$_recurly_plan_code,
			'name' =>	$variation_data['name'],
			'currencies' => [
                'currency' => 'USD',
                'setup_fee' => $setup_fee,
                'unit_amount' => $unit_amount
			],
            'auto_renew' => true,
			// 'interval_length' => (int)$interval_length,
			// 'interval_unit' => $interval_unit,
			'total_billing_cycles' => $total_billing_cycles,
			'trial_unit' => $trial_unit,
			'trial_length' => $trial_length
		];
        if($update){
            $plan_meta['interval_length'] = (int)$interval_length;
            $plan_meta['interval_unit'] = $interval_unit;
        }
        return $plan_meta;
	}

	public function create_or_update_plans( $post_id, $post, $update )
	{
        
        $product = wc_get_product( $post_id );
		if($update && $post->post_status =='publish'){
			if(!$product->is_type( 'variable-subscription' )){
				return;
			}
            $variations = $product->get_available_variations();
			foreach($variations as $variation){
				$variation_id = $variation['variation_id'];
				$variation = wc_get_product($variation_id);
			    $variation_data = $variation->get_data(); 

                $plan_id = 'code-'.$variation_id;
                $plan_update = json_encode( apply_filters('recurly_plans_args', $this->recurly_update_plan($variation_data)));
                $response = WC_Recurly_API::request( $plan_update ,'plans/' . $plan_id ,'PUT');
                if ( ! empty( $response->error ) ) {
                    if($response->error->type === 'not_found'){
                        $plan_create = json_encode( apply_filters('recurly_plans_args', $this->recurly_create_plan($variation_data,$update)));
                        $response = WC_Recurly_API::request( $plan_create ,'plans');						
                    }elseif($response->error->type === 'validation'){
                        // error_log(print_r('---------validation----------',true));
                        // error_log(print_r($response,true));
						return;
                    }elseif($response->error->type === 'internal_server_error'){
                        // error_log(print_r('---------internal_server_error----------',true));
                        // error_log(print_r($response,true));
						return;
                    }elseif($response->error->type === 'http_request_failed'){
                        // error_log(print_r('---------else----------',true));
                        // error_log(print_r($response,true));
						return;
                    }
                }                  
			}
		}elseif($post->post_status =='publish'){
            $variations = $product->get_available_variations();
            foreach($variations as $variation){
				$variation_id = $variation['variation_id'];
				$variation = wc_get_product($variation_id);
			    $variation_data = $variation->get_data(); 
                $plan_create = json_encode( apply_filters('recurly_plans_args', $this->recurly_create_plan($variation_data,$update)));
                $response = WC_Recurly_API::request( $plan_create ,'plans');
            }
		}
	}

    // Removing on add to cart if an item is already in cart
    public function remove_before_add_to_cart( $cart_item_data ) {
        WC()->cart->empty_cart();
        return $cart_item_data;
    }

    public function recurly_subscription_status_updated($subscription, $new_status, $old_status)
    {
        $subscription_data          = $subscription->get_data();//array
        $order_id                   = $subscription_data['parent_id'];
        $subscription_id            = $subscription_data['id'];
        $subscription_status        = $subscription_data['status'];//pending, active, on-hold, pending-cancel, cancelled, or expired
        $recurly_subscription_id    = get_post_meta($order_id,'_recurly_subscription_id', true);
        $get_subscription = WC_Recurly_API::request( [] ,'subscriptions/'.$recurly_subscription_id,'GET');
        if ( empty( $get_subscription->error ) && !empty($recurly_subscription_id) ) {
            switch ($new_status) {
                case 'pending':
                    if($get_subscription->state !== 'paused'){
//                         $response = WC_Recurly_API::request( [] ,'subscriptions/'.$recurly_subscription_id.'/pause','PUT');
                        error_log(print_r('pending plan by api',true));
                        // error_log(print_r($response,true));
                    }
                    break;
                case 'expired':
                    if($get_subscription->state !== 'paused'){ 
                        $refund = get_post_meta($subscription->get_id(), 'type_of_refund', true);
//                         if($subscription->get_meta( 'recurly_subscriptions_plan_change',true)){
//                             $response = WC_Recurly_API::request( [] ,'subscriptions/' . $recurly_subscription_id.'/cancel','PUT');
//                         }else{
//                             $response = WC_Recurly_API::request( json_encode(array('refund'=>$refund)) ,'subscriptions/' . $recurly_subscription_id,'DELETE');
//                         }
                        // error_log(print_r($response,true));
                    }
                    break;
                case 'on-hold':
                    if($get_subscription->state !== 'canceled'){ 
//                         $response = WC_Recurly_API::request( [] ,'subscriptions/'.$recurly_subscription_id.'/cancel','PUT');
                        error_log(print_r('on-hold plan by api',true));
                        // error_log(print_r($response,true));
                    }
                    break;
                case 'cancelled':
                    if($get_subscription->state !== 'canceled'){
//                         $response = WC_Recurly_API::request( [] ,'subscriptions/'.$recurly_subscription_id.'/cancel','PUT');
                        error_log(print_r('cancelled plan by api',true));
                        // error_log(print_r($response,true));
                    }
                    break;
                case 'active': 
                    if($get_subscription->state !== 'active'){
                        $response = WC_Recurly_API::request( [] ,'subscriptions/'.$recurly_subscription_id.'/reactivate','PUT');
                        error_log(print_r('active plan by api',true));
                        // error_log(print_r($response,true));
                    }
                    break;
            }
        }
        // error_log(print_r('recurly_subscription_status_updated',true));
        // error_log(print_r($get_subscription,true));
    }

    public function recurly_all_subscription()
    {
        $response = wp_safe_remote_post( 'https://v3.recurly.com/subscriptions/pplq27gdaimf/cancel', array(
            'method' => 'PUT',
            'headers' => array(
                'Authorization'=>'Basic ZDk2YWJiNThhZTI2NDViYTg1ZmIzMTc5ZGUwOWE2NDg6',
                'Recurly-Version'=>'recurly.v2021-02-25',
                'Accept'=>'application/vnd.recurly.v2021-02-25',
                'Content-Type' => 'application/json; charset=UTF-8',
                'Content-Length' => 0
                )
            )
        );
        // error_log(print_r('---------recurly_all_subscription-----------',true)); 
        // error_log(print_r(json_decode( $response['body'] ),true)); 
    }
	
	public function recurly_add_plan_code_field_to_variations( $loop, $variation_data, $variation ) {
		woocommerce_wp_text_input( array(
			'id' => 'recurly_plan_code[' . $loop . ']',
			'class' => 'short',
			'label' => __( 'Recurly Plan code *', 'woocommerce' ),
			'required' => true,
			'custom_attributes' => array( 'required' => 'required' ),
			'value' => get_post_meta( $variation->ID, '_recurly_plan_code', true ),
			'description' => __( '', 'woocommerce' )
		) );
	}
	
	public function recurly_save_add_plan_code_field_variations( $variation_id, $i ) {
		$_recurly_plan_code = $_POST['recurly_plan_code'][$i];
		if ( isset( $_recurly_plan_code ) ) update_post_meta( $variation_id, '_recurly_plan_code', esc_attr( $_recurly_plan_code ) );
	}

    public static function recurly_update_customer( $customer_id, $customer )
    {
        $_recurly_account_code = get_user_meta($customer_id, '_recurly_customer_id', true);
        if(empty($_recurly_account_code))
            $_recurly_account_code =  get_user_meta($customer_id, '_recurly_account_code', true) ? 'code-'.get_user_meta($customer_id, '_recurly_account_code', true) : 'code-'.get_user_meta($customer_id, 'wp__recurly_account_code', true);
            // https://v3.recurly.com/accounts/{account_id}
            //https://v3.recurly.com/accounts/{account_id}/billing_info
            
        $customer = new WC_Customer( $customer_id );

        $username     = $customer->get_username(); // Get username
        $user_email   = $customer->get_email(); // Get account email
        $first_name   = $customer->get_first_name();
        $last_name    = $customer->get_last_name();
        $update_customer = array(
            'email'         => $user_email,
            'first_name'    => $first_name,
            'last_name'     => $last_name,
            'username'      => $username,
            'company'       => $customer->get_billing_company(),
            'address'       => array(
                'street1'       => $customer->get_billing_address_1(),
                'street2'       => $customer->get_billing_address_2(),
                'city'          => $customer->get_billing_city(),
                'region'        => $customer->get_billing_state(),
                'postal_code'   => $customer->get_billing_postcode(),
                'country'       => $customer->get_billing_country(),
                'phone'         => $customer->get_billing_phone()
            )
        );
        $account = WC_Recurly_API::request( json_encode($update_customer) ,'accounts/'.$_recurly_account_code,'PUT');
        unset($update_customer['email']);
        unset($update_customer['username']);
        // error_log(print_r($account,true));
        $response = WC_Recurly_API::request( json_encode($update_customer) ,'accounts/'.$_recurly_account_code.'/billing_info','PUT');
        if ( empty( $response->error ) )
        {
            // error_log(print_r('Update user',true));
            // error_log(print_r($response,true));
        }else{
            // error_log(print_r('Update user error',true));
            // error_log(print_r($response,true));
        }
        

    }

	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
WC_Recurly_Plan::instance();