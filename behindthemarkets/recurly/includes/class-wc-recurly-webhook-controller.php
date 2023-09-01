<?php
/**
 * REST API: WP_Recurly_Webhook_Controller class
 *
 * @package WordPress
 * @subpackage REST_API
 * @since 4.7.0
 */

/**
 * Core class used to managed terms associated with a taxonomy via the REST API.
 *
 * @since 4.7.0
 *
 * @see WP_REST_Controller
 */

define("HTML_EMAIL_HEADERS", array('Content-Type: text/html; charset=UTF-8'));

class WP_Recurly_Webhook_Controller extends WP_REST_Controller
{

    /**
     * Namespace.
     *
     * @since 4.7.0
     * @var string
     */
    private static $instance = null;

    public $namespace;

    public $XMLData;

    public $xmltoArray;

    public $notification_name;

    public $rest_base;

    private $redis;
    
    private $redis_base;

    private $redis_webhook_queue;

    /**
     * Constructor.
     *
     * @since 4.7.0
     *
     * @param string $taxonomy Taxonomy key.
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
        $this->namespace = 'recurly/v1';
        $this->rest_base = 'notifications';
        $this->redis_base = 'webhookqueue';

        // Initialize Redis client
        $this->redis_webhook_queue = 'redis_webhook_queue';
        $this->redis = new Predis\Client(
            [
                'scheme' => 'tcp',
                'host' => 'localhost',
                'port' => 6379,
            ]
        );
        if ( class_exists( 'WP_CLI_Command' ) )
        {
            WP_CLI::add_command( 'webhook_queue_list', [$this, 'webhook_queue_list'] ); 
            WP_CLI::add_command( 'webhook_queue_process', [$this, 'webhook_queue_process'] ); 
        }else{
            error_log(print_r('Error get_webhook_queue_list', true));
        }
    }

    public function webhook_queue_list()
    {
        if(!empty($this->redis->lrange($this->redis_webhook_queue, 0, -1)))
        {   
            WP_CLI::success( count($this->redis->lrange($this->redis_webhook_queue, 0, -1)).' Queues are pending.' );
        }else{
            WP_CLI::success( 'Queue is empty' );
        }
    }

    public function webhook_queue_process( $args, $assoc_args )
    {
        $process_queue = isset($assoc_args['process_queue']) && !empty($assoc_args['process_queue']) ? $assoc_args['process_queue'] : 2;
        $number_of_data_stored = $this->redis->lrange($this->redis_webhook_queue, 0, -1);
        if($process_queue && !empty($number_of_data_stored) && isset($number_of_data_stored))
        {
            if($process_queue > count($number_of_data_stored))
            {
                $process_queue = count($number_of_data_stored);
            }
            for ($i=1; $i <= $process_queue; $i++) { 
                $this->get_top_index();
            }
            $message = $process_queue > 1 ? $process_queue.' Queues have been successfully processed.' : $process_queue.' Queue have been successfully processed.';
            WP_CLI::success( $message );
        }else{
            WP_CLI::success( 'Queue is empty' );
        }
    }

    // Method to define the custom cron interval
    public function add_redis_cron_interval($schedules)
    {
        $schedules['redis_two_mint_interval'] = array(
            'interval' => 120, // 1 minutes in seconds
            'display' => __('Every 2 Minutes')
        );
        error_log(print_r(time()."Every 2 Minutes", true));
        return $schedules;
    }

    public function storeWebhookResponse($xml_data)
    {
        $this->redis->rpush($this->redis_webhook_queue, $xml_data);
        error_log(print_r('$storeWebhookResponse', true));
        error_log(print_r($xml_data, true));
    }

    public function executeWebhookResponse($xml_data)
    {
        if ($xml_data === null) {
            return;
        }
        if ($xml_data !== false) {
            error_log(print_r('executeWebhookResponse', true));
            $response = $this->execute_redis_response($xml_data);
            // error_log(print_r($response, true));
            // error_log(print_r($xml_data, true));
            if($response)
                // $this->redis->del($response_id);
                error_log(print_r(time()." ==== Delete ".$this->redis_webhook_queue, true));
        } else {
            error_log(print_r("Error parsing XML data." . PHP_EOL, true));
        }
    }

    /**
     * Registers the routes for terms.
     *
     * @since 4.7.0
     *
     * @see register_rest_route()
     */
    public function register_routes()
    {

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => array($this, 'get_notification'),
                    'permission_callback' => array($this, 'get_notification_permissions_check'),
                ),
            )
        );

        // register_rest_route(
        //     $this->namespace,
        //     '/webhookqueuelist',
        //     array(
        //         array(
        //             'methods' => WP_REST_Server::READABLE,
        //             'callback' => array($this, 'get_webhook_queue_list'),
        //             'permission_callback' => array($this, 'get_redis_permissions_check'),
        //         ),
        //     )
        // );

        // register_rest_route(
        //     $this->namespace,
        //     '/' . $this->redis_base,
        //     array(
        //         array(
        //             'methods' => WP_REST_Server::READABLE,
        //             'callback' => array($this, 'redis_get_notification'),
        //             'permission_callback' => array($this, 'get_redis_permissions_check'),
        //         ),
        //     )
        // );
    }

    public function get_notification_permissions_check($request)
    {
        return true;
    }
    public function get_redis_permissions_check($request)
    {
        return true;
    }

    public function get_webhook_queue_list()
    {
        if(!empty($this->redis->lrange($this->redis_webhook_queue, 0, -1)))
        {   $notification_names = array();
            foreach($this->redis->lrange($this->redis_webhook_queue, 0, -1) as $xml_data)
            {
                $xmltoArray = simplexml_load_string($xml_data);
                $notification_names[] = $xmltoArray->getName();
            }
            return wp_send_json_success( 
                array( 
                    'no_of_data'=> count($this->redis->lrange($this->redis_webhook_queue, 0, -1)), 
                    'notification_names'=> $notification_names, 
                    'data'=> $this->redis->lrange($this->redis_webhook_queue, 0, -1) 
                ));
        }else{
            wp_send_json_success( array('message' => 'Queue is empty') );
        }
    }

    public function get_notification(WP_REST_Request $request)
    {
        if (empty($request->get_body())) {
            return new WP_Error('empty_notification', 'Somthing went wrong.', array('status' => 404));
        } else {
            $response = $request->get_body();
            $this->storeWebhookResponse($response);  
            return new WP_REST_Response(array('success' => true), 200);      
        }
    }

    public function redis_get_notification(WP_REST_Request $request)
    {
        //https://btmnew.saviormarketing.com/wp-json/recurly/v1/webhookqueue
        $parameters = $request->get_params();
        $number_of_data_stored = $this->redis->lrange($this->redis_webhook_queue, 0, -1);
        if(!isset($parameters['process_queue']))
        {
            $process_queue = 2;
        }else{
            $process_queue = $parameters['process_queue'];
        }
        if($process_queue && !empty($number_of_data_stored) && isset($number_of_data_stored))
        {
            if($process_queue > count($number_of_data_stored))
            {
                $process_queue = count($number_of_data_stored);
            }
            for ($i=1; $i <= $process_queue; $i++) { 
                $this->get_top_index();
            }
            $message = $process_queue > 1 ?' Queues have been successfully processed.' : ' Queue have been successfully processed.';
            wp_send_json_success( array('message' => $process_queue.$message) );
        }else{
            wp_send_json_success( array('message' => 'Queue is empty') );
        }
    }

    public function get_top_index()
    {
        $get_top_xml_response = $this->redis->lindex($this->redis_webhook_queue, 0);
        if($get_top_xml_response)
        {
            $execute_redis_response = $this->execute_redis_response($get_top_xml_response);
            error_log(print_r('get_top_index=execute_redis_response', true));
            error_log(print_r($execute_redis_response, true));
            if($execute_redis_response)
            {
                $remove_top_index = $this->remove_top_index( $get_top_xml_response );
                if($remove_top_index)
                {
                    error_log(print_r('removed_top_index==========', true));
                }
            }
        }else{
            error_log(print_r('no get_top_xml_response', true));
        }
    }

    public function remove_top_index( $index )
    {
        return $this->redis->lrem($this->redis_webhook_queue, 1, $index);
    }

    public function execute_redis_response($response)
    {
        $xmltoArray = simplexml_load_string($response);
        $event_name = $xmltoArray->getName();
        error_log(print_r('execute_redis_response', true));
        error_log(print_r($event_name, true));
        try {
            //code...
            switch ($event_name) {
                //New Account -> Sent when a new account is created.
                case 'new_account_notification':
                    self::new_account_notification($xmltoArray);
                    break;
    
                //Updated Account
                case 'updated_account_notification':
                    self::updated_account_notification($xmltoArray);
    
                    break;
                case 'billing_info_updated_notification':
                    $user = self::get_user_id($xmltoArray);
                    
                    $billing_info = self::get_billing_and_shipping_address($xmltoArray->account->account_code);
                    // error_log(print_r($billing_info,true));
                    if($user) 
                    {
                        $billing_first_name = get_user_meta($user->ID, 'billing_first_name', true); 
                        $billing_last_name  = get_user_meta($user->ID, 'billing_last_name', true); 
                        $billing_phone      = get_user_meta($user->ID, 'billing_phone', true); 
                        $billing_company    = get_user_meta($user->ID, 'billing_company', true); 
                        $billing_email      = get_user_meta($user->ID, 'billing_email', true); 
                        $billing_address_1  = get_user_meta($user->ID, 'billing_address_1', true); 
                        $billing_address_2  = get_user_meta($user->ID, 'billing_address_2', true); 
                        $billing_city       = get_user_meta($user->ID, 'billing_city', true); 
                        $billing_state      = get_user_meta($user->ID, 'billing_state', true); 
                        $billing_postcode   = get_user_meta($user->ID, 'billing_postcode', true); 
                        $billing_country    = get_user_meta($user->ID, 'billing_country', true); 
                        $address = array(
                            'first_name'            => !empty($billing_info->first_name) ? $billing_info->first_name : $billing_first_name ,
                            'last_name'             => !empty($billing_info->last_name) ? $billing_info->last_name : $billing_last_name,
                            'user_email'            => !empty((string) $xmltoArray->account->email) ? (string) $xmltoArray->account->email : $billing_email,
                            
                            'billing_first_name'    => !empty($billing_info->first_name) ? $billing_info->first_name : $billing_first_name ,
                            'billing_last_name'     => !empty($billing_info->last_name) ? $billing_info->last_name : $billing_last_name,
                            'billing_company'       => !empty($billing_info->company) ? $billing_info->company : $billing_company,
                            'billing_email'         => !empty((string) $xmltoArray->account->email) ? (string) $xmltoArray->account->email : $billing_email,
                            'billing_phone'         => !empty($billing_info->address->phone) ? $billing_info->address->phone : $billing_phone,
                            'billing_address_1'     => !empty($billing_info->address->street1) ? $billing_info->address->street1 :$billing_address_1,
                            'billing_address_2'     => !empty($billing_info->address->street2) ? $billing_info->address->street2 : $billing_address_2,
                            'billing_city'          => !empty($billing_info->address->city) ? $billing_info->address->city : $billing_city,
                            'billing_state'         => !empty($billing_info->address->region) ? $billing_info->address->region : $billing_state,
                            'billing_postcode'      => !empty($billing_info->address->postal_code) ? $billing_info->address->postal_code : $billing_postcode,
                            'billing_country'       => !empty($billing_info->address->country) ? $billing_info->address->country : $billing_country
                        );
    
                        $customer = new WC_Customer($user->ID);
    
                        $last_order = $customer->get_last_order();
                        if ($last_order) {
                            if (empty(get_post_meta($last_order->get_id(), '_created_via', true))) {

                                update_post_meta( $last_order->get_id(), '_billing_first_name', $address['billing_first_name']);
                                update_post_meta( $last_order->get_id(), '_billing_last_name', $address['billing_last_name']);
                                update_post_meta( $last_order->get_id(), '_billing_company', $address['billing_company']);
                                update_post_meta( $last_order->get_id(), '_billing_address_1', $address['billing_address_1']);
                                update_post_meta( $last_order->get_id(), '_billing_address_2', $address['billing_address_2']);
                                update_post_meta( $last_order->get_id(), '_billing_city', $address['billing_city']);
                                update_post_meta( $last_order->get_id(), '_billing_state', $address['billing_state']);
                                update_post_meta( $last_order->get_id(), '_billing_postcode', $address['billing_postcode']);
                                update_post_meta( $last_order->get_id(), '_billing_country', $address['billing_country']);
                                update_post_meta( $last_order->get_id(), '_billing_email', $address['billing_email']);
                                update_post_meta( $last_order->get_id(), '_billing_phone', $address['billing_phone']);
                                
                                $subscriptions_ids = wcs_get_subscriptions_for_order( $last_order->get_id());
                                foreach( $subscriptions_ids as $subscription_id => $subscription_obj ){
                                    if($subscription_obj->get_parent_id() == $last_order->get_id())
                                    {
                                        update_post_meta( $subscription_id, '_billing_first_name', $address['billing_first_name']);
                                        update_post_meta( $subscription_id, '_billing_last_name', $address['billing_last_name']);
                                        update_post_meta( $subscription_id, '_billing_company', $address['billing_company']);
                                        update_post_meta( $subscription_id, '_billing_address_1', $address['billing_address_1']);
                                        update_post_meta( $subscription_id, '_billing_address_2', $address['billing_address_2']);
                                        update_post_meta( $subscription_id, '_billing_city', $address['billing_city']);
                                        update_post_meta( $subscription_id, '_billing_state', $address['billing_state']);
                                        update_post_meta( $subscription_id, '_billing_postcode', $address['billing_postcode']);
                                        update_post_meta( $subscription_id, '_billing_country', $address['billing_country']);
                                        update_post_meta( $subscription_id, '_billing_email', $address['billing_email']);
                                        update_post_meta( $subscription_id, '_billing_phone', $address['billing_phone']);                                        
                                    }
                                }
                            }
                        }
                        foreach ($address as $meta_key => $meta_value) {
                            update_user_meta($user->ID, $meta_key, $meta_value);
                        }
    
                        return true;
                    } else {
                        return false;
                    }
                    break;
                case 'new_shipping_address_notification':
                    self::new_shipping_address_notification($xmltoArray);
                    break;
    
                //An existing shipping address is edited
                case 'updated_shipping_address_notification':
                    self::new_shipping_address_notification($xmltoArray);
                    break;
                //Subscription Notifications ->Subscriptions will return an array of add-ons if the subscription includes add-ons.
                //If you have a plan on your site with a usage-based add-on, you will start seeing these additional add-on attributes when add-ons are returned:
                case 'new_subscription_notification':
                    self::new_subscription_notification($xmltoArray);
                    break;
                //Updated Subscription
                case 'updated_subscription_notification':
                        $uuid = (string) $xmltoArray->subscription->uuid;
                        $plan_code = (string) $xmltoArray->subscription->plan->plan_code;  
                        $args1 = array(
                            'meta_key' => '_recurly_subscription_uuid',
                            'meta_value' => $uuid,
                            'post_type' => 'shop_subscription',
                            'post_status' => 'any',
                            'fields' => 'ids'
                        );
                        $subscription_ids = get_posts($args1);
                        // $plan_code = (string) $xmltoArray->subscription->plan->plan_code;
                        if($plan_code == 'bi-upsell-annual')
                        {
                            $plan_code = 'bi-annual';
                        }
                        if($plan_code == 'bi-upsell-lifetime')
                        {
                            $plan_code = 'bi-lifetime';
                        }
                        $args = array(
                            'post_type' => 'product_variation',
                            'meta_query' => array(
                                array(
                                    'key' => '_recurly_plan_code',
                                    'value' => $plan_code,
                                ),
                            ),
                            'fields' => 'ids',
                        );                        
                        $pro_variation_ids = get_posts($args);
    
                        if (!empty($subscription_ids) && !empty($pro_variation_ids)) {
    
                            $subscription_cr = wcs_get_subscription( $subscription_ids[0] );
    
                            $user_id = $subscription_cr->get_user_id();
                            $subscription_check = false;
                            $users_subscriptions = wcs_get_users_subscriptions($user_id);
                            foreach($users_subscriptions as $subscription){
                                if ($subscription->has_status(array('active','pending','on-hold'))) {
                                    foreach ($subscription->get_items() as $item_id => $item_data) {
                                        
                                        if(get_post_meta((string)$item_data['variation_id'], '_recurly_plan_code', true) === $plan_code)
                                        {
                                            $subscription_check = true;
                                        }
                                    }
                                }
                            }
                            if($subscription_check)
                            {
                                return new WP_REST_Response(array('success' => true,'message'=> 'Subscription already exist.'), 200);
                            }
                            $order_id = $subscription_cr->get_parent_id();
                            $order = wc_get_order($order_id);
                            if (is_wp_error($order)) {
                                return false;
                            }    
                            $order_update = false;
                            $subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) ); 
                            foreach( $subscriptions as $subscription_id => $subscription ){  
                                if ($subscription->has_status('active')) {                                     
                                    $subscription->update_meta_data( 'recurly_subscriptions_plan_change', 1 );
                                    ///======
                                        $variation_id = null;
                                        foreach($subscription->get_items() as $items)
                                        {
                                            $variation_id = $items['variation_id'];
                                        }
                                        $plan_id = null;
                                        foreach(wc_memberships_get_membership_plans() as $membership_plan){
                                            $get_product_ids = $membership_plan->get_product_ids();
                                            if(in_array($variation_id, $membership_plan->get_product_ids()))
                                            {
                                                $plan_id = $membership_plan->get_id();
                                            }
                                        }
                                        if(!empty($plan_id))
                                        {
                                            $user_membership = wc_memberships_get_user_membership( $user_id, $plan_id );
                                            if(!empty($user_membership))
                                            {                 
                                                $user_membership->update_status( 'expired' );
                                                $end_date = date('Y-m-d H:i:s', strtotime('now + 2 minutes'));
                                                $user_membership->set_end_date($end_date);
                                            }
                                        }
                                    //======
    
                                    $subscription->update_status( 'expired' );
                                    $subscription->update_meta_data('_recurly_subscription_uuid', '');
                                    wc_delete_order_item($subscription_id); 
                                    $order->calculate_totals();
                                    $order->save(); 
                                    foreach ( $order->get_items() as $item_id => $item ) {
                                        wc_delete_order_item($item_id); 
                                        $order->calculate_totals();
                                        $order->save();
                                        $order_update = true;
                                    }
                                }
                            } 
                            self::updated_subscription_notification($pro_variation_ids, $subscription_ids, $order_update);  
                        } 
                        return true;                          
                    break;
                //Canceled Subscription -> The canceled_subscription_notification is sent when a subscription is canceled.
                case 'canceled_subscription_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::subscription_status_change($uuid, 'on-hold');
                    break;
                //Expired Subscription -> The expired_subscription_notification is sent when a subscription is no longer valid.
                case 'expired_subscription_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::subscription_status_change($uuid, 'expired');
                    break;
                //Renewed Subscription
                case 'renewed_subscription_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::renewed_subscription_notification($uuid);
                    break;
                //Reactivated Subscription-> Sent when a subscription is reactivated after having been canceled.
                //Note that while the webhooks is called the “reactivated_account_notification” this is actually sent when a subscription is reactivated.
                case 'reactivated_account_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::subscription_status_change($uuid, 'active');
                    break;
                //Paused Subscription
                case 'subscription_paused_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::subscription_status_change($uuid, 'on-hold');
                    break;
    
                //Resumed Subscription
                case 'subscription_resumed_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::subscription_status_change($uuid, 'active');
                    break;
                //Subscription Pause Canceled
                case 'subscription_pause_canceled_notification':
                    $uuid = (string) $xmltoArray->subscription->uuid;
                    self::subscription_status_change($uuid, 'active');      
                    break;
                default:
                    # code...
                    break;
            }
            return true;
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }

    public static function new_account_notification($xmltoArray)
    {
        
        $user = self::get_user_id($xmltoArray);
        if ($user->ID) {
            update_user_meta($user->ID, '_recurly_account_code', (string) $xmltoArray->account->account_code);
            $data = array('success' => true);
            return true;
        } else {
            $data = array('success' => false);
            return false;
        }
    }

    public static function updated_account_notification($xmltoArray)
    {
        $account_code = (string) $xmltoArray->account->account_code;
        $response = WC_Recurly_API::request([], 'accounts/code-' . $account_code, 'GET');
        // error_log(print_r($response,true));
        if (!empty($response->error)) {
            return false;
        } else {
            $user = self::get_user_id($xmltoArray);
            
            if(!empty($user)){
                //Shipping
                do_action( 'profile_update', $user->ID, $user);
                $shipping = $response->shipping_addresses;
                if(!empty($shipping))
                {   $shipping = $shipping[0];
                    $address = array(
                        'shipping_first_name'    => !empty($shipping->first_name) ? $shipping->first_name : '' ,
                        'shipping_last_name'     => !empty($shipping->last_name) ? $shipping->last_name : '',
                        'shipping_company'       => !empty($shipping->company) ? $shipping->company : '',
                        'shipping_email'         => !empty($shipping->email) ? $shipping->email : '',
                        'shipping_phone'         => !empty($shipping->phone) ? $shipping->phone : '',
                        'shipping_address_1'     => !empty($shipping->street1) ? $shipping->street1 :'',
                        'shipping_address_2'     => !empty($shipping->street2) ? $shipping->street2 : '',
                        'shipping_city'          => !empty($shipping->city) ? $shipping->city : '',
                        'shipping_state'         => !empty($shipping->region) ? $shipping->region : '',
                        'shipping_postcode'      => !empty($shipping->postal_code) ? $shipping->postal_code : '',
                        'shipping_country'       => !empty($shipping->country) ? $shipping->country : ''
                    );                
                    foreach ($address as $meta_key => $meta_value) {
                        update_user_meta($user->ID, $meta_key, $meta_value);
                    }
                }
                //Billing
                $billing_data = array(
                    'first_name'		=> $response->first_name ? $response->first_name : '',
                    'last_name'		    => $response->last_name ? $response->last_name :'',
                    'billing_email'     => !empty($response->email) ? $response->email : $response->email,
                    'billing_first_name'=> !empty($response->billing_info->first_name) ? $response->billing_info->first_name : $response->first_name,
                    'billing_last_name'	=> !empty($response->billing_info->last_name) ? $response->billing_info->last_name : $response->last_name,
                    'billing_company'	=> !empty($response->billing_info->company) ? $response->billing_info->company : $response->company,

                    'billing_address_1'	=> !empty($response->billing_info->address->street1) ? $response->billing_info->address->street1 : $response->address->street1,
                    'billing_address_2'	=> !empty($response->billing_info->address->street2) ? $response->billing_info->address->street2 : $response->address->street2,
                    'billing_city'      => !empty($response->billing_info->address->city) ? $response->billing_info->address->city : $response->address->city,
                    'billing_state'		=> !empty($response->billing_info->address->region) ? $response->billing_info->address->region : $response->address->region,
                    'billing_postcode'  => !empty($response->billing_info->address->postal_code) ? $response->billing_info->address->postal_code : $response->address->postal_code,
                    'billing_country'	=> !empty($response->billing_info->address->country) ? $response->billing_info->address->country : $response->address->country,
                    'billing_phone'     => !empty($response->billing_info->address->phone) ? $response->billing_info->address->phone : $response->address->phone,
                );
                $args = array(
                    'ID'         => $user->ID,
                    'user_email' => esc_attr( $response->email )
                );
                wp_update_user( $args );
                foreach ($billing_data as $billing_meta_key => $billing_meta_value ) {
                    update_user_meta( $user->ID, $billing_meta_key, $billing_meta_value );
                }
                // do_action( 'woocommerce_save_account_details', $user->ID ); 
                return true;
            }
        }
    }

    public static function new_shipping_address_notification($args)
    {
        //https://v3.recurly.com/accounts/{account_id}/shipping_addresses
        $account_code = (string)$args->account->account_code;
        $response = WC_Recurly_API::request([], 'accounts/code-' . $account_code . '/shipping_addresses', 'GET');
        if (!empty($response->error)) {
            // error_log(print_r($response, true));
            return false;
        } else {
            $shipping = $response->data[0];
            $user = self::get_user_id($args);
            if(!empty($user)){
                $address = array(
                    'shipping_first_name'    => !empty($shipping->first_name) ? $shipping->first_name : '' ,
                    'shipping_last_name'     => !empty($shipping->last_name) ? $shipping->last_name : '',
                    'shipping_company'       => !empty($shipping->company) ? $shipping->company : '',
                    'shipping_email'         => !empty($shipping->email) ? $shipping->email : '',
                    'shipping_phone'         => !empty($shipping->phone) ? $shipping->phone : '',
                    'shipping_address_1'     => !empty($shipping->street1) ? $shipping->street1 :'',
                    'shipping_address_2'     => !empty($shipping->street2) ? $shipping->street2 : '',
                    'shipping_city'          => !empty($shipping->city) ? $shipping->city : '',
                    'shipping_state'         => !empty($shipping->region) ? $shipping->region : '',
                    'shipping_postcode'      => !empty($shipping->postal_code) ? $shipping->postal_code : '',
                    'shipping_country'       => !empty($shipping->country) ? $shipping->country : ''
                );
                
                foreach ($address as $meta_key => $meta_value) {
                    update_user_meta($user->ID, $meta_key, $meta_value);
                }
            }
            return new WP_REST_Response(array('success' => true), 200);
        }
            
    }
    public static function get_user_id($xmltoArray)
    {
        $args  = array(
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'       => '_recurly_account_code',
                    'value'     => (string)$xmltoArray->account->account_code,
                    'compare'   => '='
                ),
                array(
                    'key'       => 'wp__recurly_account_code',
                    'value'     => (string)$xmltoArray->account->account_code,
                    'compare'   => '='
                ),
                array(
                    'key'       => 'user_email',
                    'value'     => (string)$xmltoArray->account->account_code,
                    'compare'   => 'LIKE'
                )
            )
        );
         
        $user_query = new WP_User_Query( $args );
        if (empty($user_query->results) && !email_exists((string) $xmltoArray->account->email )) {
            $password = 'welcome2BTM';//wp_generate_password(12, true);
            $user_id = wp_insert_user(array(
                'user_login'    => (string) $xmltoArray->account->email,
                'user_pass'     => $password,
                'user_email'    => (string) $xmltoArray->account->email,
                'first_name'    => (string) $xmltoArray->account->first_name,
                'last_name'     => (string) $xmltoArray->account->last_name,
                'display_name'  => (string) $xmltoArray->account->first_name . ' ' . (string) $xmltoArray->account->last_name,
                'role'          => 'customer',
            ));
            if(is_wp_error($user_id)){
                $user = get_user_by('email', (string) $xmltoArray->account->account_code);
                update_user_meta($user->ID, '_recurly_account_code', (string) $xmltoArray->account->account_code);
                return $user;
                //handle error here
            }else{
                $user           = get_user_by('id', $user_id);
                $email          = $user->data->user_login;
                $subject        = 'Your Account Has Been Created!';
                $headers[]      = 'Reply-To: Behind The Markets <support@behindthemarkets.com>';
                $headers[]      = 'Content-Type: text/html; charset=UTF-8';
                $message        = self::welcome_email_users($xmltoArray->account->first_name, $email, $password);
                // $mail           = wp_mail( $email, $subject, $message,$headers );
                // if($mail){
                // }
                update_user_meta($user_id, '_recurly_account_code', (string) $xmltoArray->account->account_code);
                return $user;
            }
        } else {   
            if(empty($user_query->results[0]))         
            {
                $user = get_user_by('email', (string) $xmltoArray->account->account_code) ? get_user_by('email', (string) $xmltoArray->account->account_code) : get_user_by('email', (string) $xmltoArray->account->email);
            }else{
                $user = $user_query->results[0];
            }
                
            return $user;
        }
    }

    public static function get_billing_and_shipping_address($account_code)
    {
        $response = WC_Recurly_API::request([], 'accounts/code-' . $account_code . '/billing_info', 'GET');
        if (!empty($response->error)) {
            return false;
        } else {
            return $response;
        }
    }

    public static function get_subscription_id_by_uuid($uuid)
    {
        //https://v3.recurly.com/subscriptions/{subscription_id}
        $response = WC_Recurly_API::request([], 'subscriptions/uuid-' . $uuid, 'GET');
        if (!empty($response->error)) {
            error_log(print_r('web-hook-controller get_subscription_id_by_uuid', true));
            error_log(print_r($response->error, true));
            return false;
        }
        return $response->id;
    }

    public static function new_subscription_notification($xmltoArray)
    {
        global $woocommerce;
        $user = self::get_user_id($xmltoArray);
        $plan_code = (string) $xmltoArray->subscription->plan->plan_code;
        if($plan_code == 'bi-upsell-annual')
        {
            $plan_code = 'bi-annual';
        }
        if($plan_code == 'bi-upsell-lifetime')
        {
            $plan_code = 'bi-lifetime';
        }
        $comp_code = array(
            'comp-btm-bronze'   => 'btm-bronze',
            'comp-btm-silver'   => 'btm-silver',
            'comp-btm-platinum' => 'btm-platinum',
            'comp-btm-lifetime' => 'btm-lifetime',
            'comp-btm-alli'     => 'btm-alli',
            'comp-bi-annual'    => 'bi-annual',
            'comp-bi-two-year'  => 'bi-two-year',
            'comp-bi-lifetime'  => 'bi-lifetime',
            'comp-bw-annual'    => 'bw-annual',
            'comp-bw-two-year'  => 'bw-two-year',
            'comp-bw-lifetime'  => 'bw-lifetime',
            'comp-hmp-annual'   => 'hmp-annual',
            'comp-hmp-two-year' => 'hmp-two-year',
            'comp-hmp-lifetime' => 'hmp-lifetime',
            'comp-tt-annual'    => 'tt-annual',
            'comp-tt-two-year'  => 'tt-two-year',
            'comp-tt-lifetime'  => 'tt-lifetime'
        );
        if(in_array($plan_code,array_keys($comp_code))){
            $plan_code = $comp_code[$plan_code];
        }

        $args = array(
            'post_type' => 'product_variation',
            'meta_query' => array(
                array(
                    'key' => '_recurly_plan_code',
                    'value' => $plan_code,
                ),
            ),
            'fields' => 'ids',
        );
        $pro_ids = get_posts($args);
        if (!empty($pro_ids)) {
            $product_id = $pro_ids[0];
            $billing_info = self::get_billing_and_shipping_address($xmltoArray->account->account_code);

                $billing_first_name     = get_user_meta($user->ID, 'billing_first_name', true); //get_billing_first_name();
                $billing_last_name      = get_user_meta($user->ID, 'billing_last_name', true); //get_billing_last_name();
                $billing_phone          = get_user_meta($user->ID, 'billing_phone', true); //get_billing_phone();
                $billing_company        = get_user_meta($user->ID, 'billing_company', true); //get_billing_company();
                $billing_address_1      = get_user_meta($user->ID, 'billing_address_1', true); //get_billing_address_1();
                $billing_address_2      = get_user_meta($user->ID, 'billing_address_2', true); //get_billing_address_2();
                $billing_city           = get_user_meta($user->ID, 'billing_city', true); //get_billing_city();
                $billing_state          = get_user_meta($user->ID, 'billing_state', true); //get_billing_state();
                $billing_postcode       = get_user_meta($user->ID, 'billing_postcode', true); //get_billing_postcode();
                $billing_country        = get_user_meta($user->ID, 'billing_country', true); //get_billing_country();
                $address = array(
                    'first_name'    => $billing_first_name ? $billing_first_name : (!empty($billing_info->first_name) ? $billing_info->first_name : ''),
                    'last_name'     => $billing_last_name ? $billing_last_name : (!empty($billing_info->last_name) ? $billing_info->last_name : ''),
                    'company'       => $billing_company ? $billing_company : (!empty($billing_info->company) ? $billing_info->company : ''),
                    'email'         => $user->data->user_email ? $user->data->user_email : (!empty((string) $xmltoArray->account->email) ? (string) $xmltoArray->account->email : ''),
                    'phone'         => $billing_phone ? $billing_phone : (!empty($billing_info->address->phone) ? $billing_info->address->phone : ''),
                    'address_1'     => $billing_address_1 ? $billing_address_1 : (!empty($billing_info->address->street1) ? $billing_info->address->street1 : ''),
                    'address_2'     => $billing_address_2 ? $billing_address_2 : (!empty($billing_info->address->street2) ? $billing_info->address->street2 : ''),
                    'city'          => $billing_city ? $billing_city : (!empty($billing_info->address->city) ? $billing_info->address->city : ''),
                    'state'         => $billing_state ? $billing_state : (!empty($billing_info->address->region) ? $billing_info->address->region : ''),
                    'postcode'      => $billing_postcode ? $billing_postcode : (!empty($billing_info->address->postal_code) ? $billing_info->address->postal_code : ''),
                    'country'       => $billing_country ? $billing_country : (!empty($billing_info->address->country) ? $billing_info->address->country : '')
                );
                // Enum: "active" "canceled" "expired" "failed" "future" "paused"
                //if ( $current_status == 'active' || $current_status == 'on-hold' || $current_status == 'pending' )
                $sub_status = (string) $xmltoArray->subscription->state == 'active' ? (string) $xmltoArray->subscription->state : 'pending';
                $user_id = $user->ID;
                $uuid = (string) $xmltoArray->subscription->uuid;
                $total_amount_in_cents = (int) $xmltoArray->subscription->total_amount_in_cents;
                $subscription = self::create_order_with_subscription($product_id, $user_id, $address, $sub_status, $uuid, $total_amount_in_cents);
                if (is_object($subscription) && !empty($subscription)) {
                    return true;
                } else {
                    return false;
                }
        } 
    }

    public static function create_order_with_subscription($product_id, $user_id, $address, $sub_status, $uuid, $total_amount_in_cents)
    {
        $subscription_id = self::get_subscription_id_by_uuid($uuid);
        // if ($subscription_id) {
            $customer_orders = get_posts(array(
                'post_type' => 'shop_order',
                'post_status' => 'wc-completed',
                'meta_query' => array(
                    'relation' => 'AND',
                    array(
                        'key' => '_recurly_subscription_uuid',
                        'value' => $uuid,
                        'compare' => '='
                    ),
                    array(
                        'key' => '_customer_user',
                        'value' => $user_id,
                        'compare' => '='
                    )
                ),
                'numberposts' => -1
            ));
            if (empty($customer_orders)) {
                // Now we create the order
                $order = wc_create_order(array('customer_id' => $user_id,'status'=> 'completed'));

                if (is_wp_error($order)) {
                    error_log(print_r('wc_create_order error', true));
                    error_log(print_r($order, true));
                    return false;
                }else{
                    // error_log(print_r('$order created',true));
                }
                $product = wc_get_product($product_id);
                $default_args = array(
                    'subtotal'     => $total_amount_in_cents/100,
                    'total'        => $total_amount_in_cents/100,
                    'quantity'     => 1,
                );
                $order->add_product($product, 1, $default_args);
                $order->set_address($address, 'billing');
                $order->calculate_totals();
                $order->update_meta_data('_recurly_subscription_id', $subscription_id);
                $order->update_meta_data('_recurly_subscription_uuid', $uuid);
                $order->save();

                $sub = wcs_create_subscription(array(
                    'order_id'          => $order->get_id(),
                    'status'            => 'pending',
                    'customer_id'       => $user_id,
                    'billing_period'    => WC_Subscriptions_Product::get_period($product),
                    'billing_interval'  => WC_Subscriptions_Product::get_interval($product),
                ));

                if (is_wp_error($sub)) {
                    error_log(print_r('wcs_create_subscription error1', true));
                    error_log(print_r($sub, true));
                    return false;
                }
                $start_date = gmdate('Y-m-d H:i:s');
                // Add product to subscription
                $sub->add_product($product, 1, $default_args);
                $sub->set_address($address, 'billing');
                $sub->update_meta_data('_recurly_subscription_uuid', $uuid);

                $dates = array(
                    'trial_end' => WC_Subscriptions_Product::get_trial_expiration_date($product, $start_date),
                    'next_payment' => WC_Subscriptions_Product::get_first_renewal_payment_date($product, $start_date),
                    'end' => WC_Subscriptions_Product::get_expiration_date($product, $start_date),
                );

                $sub->update_dates($dates);
                $sub->calculate_totals();

                // Update order status with custom note
                $note = !empty($note) ? $note : __('Subscription created by webhook.');
                ($sub_status == 'active') ? $order->update_status('completed', $note, true) : $order->update_status($sub_status, $note, true);
                // Also update subscription status to active from pending (and add note)
                $sub->update_status($sub_status, $note, true);
                update_post_meta( $sub->get_id(), '_requires_manual_renewal', "true" );
                if ( function_exists( 'wc_memberships' ) ) {
                    $plan_id = null;
                    foreach(wc_memberships_get_membership_plans() as $membership_plan){
                        $get_product_ids = $membership_plan->get_product_ids();
                        if(in_array($product_id, $membership_plan->get_product_ids()))
                        {
                            $plan_id = $membership_plan->get_id();
                        }
                    }
                    if(!empty($plan_id))
                    {
                        $args = array( 
                            'status' => 'active'
                        ); 
                        
                        $active_memberships = wc_memberships_get_user_memberships( $user_id, $args );
                        foreach($active_memberships as $active_membership)
                        {
                            $active_membership_ids[] = $active_membership->plan_id;
                        }
                        $args = array(
                            'plan_id' => $plan_id,
                            'user_id' => $user_id
                        );
                        if(empty($active_membership_ids)){
                            $membership = wc_memberships_create_user_membership( $args );
                        }
                        elseif(!in_array($plan_id,$active_membership_ids))
                        {
                            $membership = wc_memberships_create_user_membership( $args );
                        } 
                    }
                }                
                return $sub;
            }else{  
                error_log(print_r('Order or subscription already created!!!!',true));
            }
        // }

    }
    //Update
    public static function updated_subscription_notification($pro_variation_ids, $subscription_ids, $order_update)
    {       
        if($order_update)
        {            
            $subscription_cr = new WC_Subscription($subscription_ids[0]);
            $order = wc_get_order($subscription_cr->get_parent_id());
            $product = wc_get_product($pro_variation_ids[0]);    
            $order->add_product($product, 1);
            $user_id = $order->get_user_id();
            $order->calculate_totals();
            $order->save();
            
            $sub = wcs_create_subscription(array(
                'order_id' => $order->get_id(),
                'status' => 'pending',
                'billing_period' => WC_Subscriptions_Product::get_period($product),
                'billing_interval' => WC_Subscriptions_Product::get_interval($product),
            ));
            
            if (is_wp_error($sub)) {
                error_log(print_r('updated_subscription_notification error1123', true));
                error_log(print_r($sub, true));
                return false;			
            } 
            $start_date = gmdate('Y-m-d H:i:s');
            // Add product to subscription
            $sub->add_product($product, 1);
            
            $dates = array(
                'trial_end' => WC_Subscriptions_Product::get_trial_expiration_date($product, $start_date),
                'next_payment' => WC_Subscriptions_Product::get_first_renewal_payment_date($product, $start_date),
                'end' => WC_Subscriptions_Product::get_expiration_date($product, $start_date),
            );
            
            $sub->update_dates($dates);
            $sub->calculate_totals();
            
            // Update order status with custom note
            $note = !empty($note) ? $note : __('Subscription changed by webhook.');
            // Also update subscription status to active from pending (and add note)
            $sub->update_status('active', $note, true);
            $uuid = get_post_meta($order->get_id(), '_recurly_subscription_uuid', true);
            $sub->update_meta_data('_recurly_subscription_uuid', $uuid);

            if ( function_exists( 'wc_memberships' ) ) {
                $plan_id = null;
                foreach(wc_memberships_get_membership_plans() as $membership_plan){
                    $get_product_ids = $membership_plan->get_product_ids();
                    if(in_array($pro_variation_ids[0], $membership_plan->get_product_ids()))
                    {
                        $plan_id = $membership_plan->get_id();
                    }
                }
                if(!empty($plan_id))
                {
                    $args = array( 
                        'status' => 'active'
                    ); 
                    
                    $active_memberships = wc_memberships_get_user_memberships( $user_id, $args );
                    foreach($active_memberships as $active_membership)
                    {
                        $active_membership_ids[] = $active_membership->plan_id;
                    }
                    $args = array(
                        'plan_id' => $plan_id,
                        'user_id' => $user_id
                    );
                    if(empty($active_membership_ids)){
                        $membership = wc_memberships_create_user_membership( $args );
                    }
                    elseif(!in_array($plan_id,$active_membership_ids))
                    {
                        $membership = wc_memberships_create_user_membership( $args );
                    } 
                }
            }
            
            return $sub;
        }
    }

    public static function subscription_status_change($uuid, $status)
    {
        $args = array(
            'meta_key'      => '_recurly_subscription_uuid',
            'meta_value'    => $uuid,
            'post_type'     => 'shop_subscription',
            'post_status'   => 'any',
            'fields'        => 'ids'
        );
        $subscription_ids = get_posts($args);
        if (isset($subscription_ids) && !empty($subscription_ids)) {
            foreach ($subscription_ids as $subscription_id) {
                $subscription = new WC_Subscription($subscription_id);
                //Recurly status : "active" "canceled" "expired" "failed" "future" "paused"
                if ($status == 'expired') {  
                    //error_log(print_r('expired12',true));  
                    $variation_id = null;
                    foreach($subscription->get_items() as $items)
                    {
                        $variation_id = $items['variation_id'];
                    }
                    $plan_id = null;
                    foreach(wc_memberships_get_membership_plans() as $membership_plan){
                        $get_product_ids = $membership_plan->get_product_ids();
                        if(in_array($variation_id, $membership_plan->get_product_ids()))
                        {
                            $plan_id = $membership_plan->get_id();
                        }
                    }
                    if(!empty($plan_id))
                    {
                        // Get the user ID (or customer ID)
                        $user_id = $subscription->get_user_id();    
                        $user_membership = wc_memberships_get_user_membership( $user_id, $plan_id );
                        if(!empty($user_membership))
                        {                 
                            $user_membership->update_status( 'expired' );
                            $end_date = date('Y-m-d H:i:s', strtotime('now + 2 minutes'));
                            $user_membership->set_end_date($end_date);
                        }
                    }
                    //======       
                    if((string)$subscription->get_status() !== 'expired'){
                        $subscription->update_status($status, __('Upadate status by webhook.'), true);        
                    }
                }elseif((string)$subscription->get_status() == 'expired'){
                }elseif((string)$subscription->get_status() == 'cancelled'){
                }else{
                    if((string)$subscription->get_status() !== 'expired'){
                        /**
                         * Check if the current user has an active subscription.
                         * wcs_user_has_subscription( $user_id = 0, $product_id = '', $status = 'any' )
                         * @param int (optional) The ID of a user in the store. If left empty, the current user's ID will be used.
                         * @param int (optional) The ID of a product in the store. If left empty, the function will see if the user has any subscription.
                         * @param string (optional) A valid subscription status. If left empty, the function will see if the user has a subscription of any status.
                         */
                        // Get the user ID (or customer ID)
                        $user_id = $subscription->get_user_id();
                        //##104630 => Lifetime (btm-alli)
                        //##127534 => CSR ALLIANCE (csr-btm-alli)
                        if( wcs_user_has_subscription($user_id, 127534, 'active') && (string)$subscription->get_status() == 'active')
                        {
                            $subscription->update_status('on-hold', __('Upadate status by webhook.'), true);     //on-hold
                        }elseif( wcs_user_has_subscription($user_id, 104630, 'active') && (string)$subscription->get_status() == 'active')
                        {
                            $subscription->update_status('on-hold', __('Upadate status by webhook.'), true);     //on-hold
                        }else{
                            $subscription->update_status($status, __('Upadate status by webhook.'), true);        
                        }
                    }
                }
            }
            return true;
        }else{
            return new WP_REST_Response(array('success' => false,'message'=>'Subscription Not found...'), 400);
        }
    }

    public static function renewed_subscription_notification($uuid)
    {    
        $args = array(
            'meta_key' => '_recurly_subscription_uuid',
            'meta_value' => $uuid,
            'post_type' => 'shop_subscription',
            'post_status' => array('wc-active','wc-on-hold','wc-pending'),
            'fields' => 'ids'
        );
        $subscription_ids = get_posts($args);
        if (isset($subscription_ids) && !empty($subscription_ids)) {
            $subscription_id = $subscription_ids[0];
            $subscription = wcs_get_subscription($subscription_id);
            $last_order_id = $subscription->get_last_order();

            $order = wc_get_order($last_order_id);
            // error_log(print_r($last_order_id,true));
            $note = 'Renewed subscription updated by webhook';
            if($order){
                if($order->get_status() != 'completed')
                    $order->update_status('completed',$note, true);
            }
            $subscription->update_status('active');
            return new WP_REST_Response(array('success' => true,'message'=>'Successfully subscription renewal payment...'), 200);
        }else{
            return new WP_REST_Response(array('success' => false,'message'=>'Subscription Not found...'), 400);
            // error_log(print_r("New renewed subscription else => ",true));
        }
    }

    public static function welcome_email_users($first_name, $username, $password)
    {
        ob_start();
        ?>
        <!doctype html>
            <html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
                <head>
                    <title></title>
                    <!--[if !mso]><!-->
                    <meta http-equiv="X-UA-Compatible" content="IE=edge">
                    <!--<![endif]-->
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1">
                    <style type="text/css">
                        #outlook a{padding:0;}body{margin:0;padding:0;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;}table,td{border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;}img{border:0;height:auto;line-height:100%;outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;}p{display:block;margin:0;}
                    </style>
                    <!--[if mso]> 
                    <noscript>
                        <xml>
                            <o:OfficeDocumentSettings>
                                <o:AllowPNG/>
                                <o:PixelsPerInch>96</o:PixelsPerInch>
                            </o:OfficeDocumentSettings>
                        </xml>
                    </noscript>
                    <![endif]-->
                    <!--[if lte mso 11]>
                    <style type="text/css">
                        .ogf{width:100% !important;}
                    </style>
                    <![endif]-->
                    <!--[if !mso]><!-->
                    <link href="https://fonts.googleapis.com/css?family=Open Sans:400,700i,700" rel="stylesheet" type="text/css">
                    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700i,700" rel="stylesheet" type="text/css">
                    <style type="text/css">
                    </style>
                    <!--<![endif]-->
                    <style type="text/css">
                        @media only screen and (min-width:599px){.xc568{width:568px!important;max-width:568px;}.xc536{width:536px!important;max-width:536px;}.pc100{width:100%!important;max-width:100%;}.pc71{width:71%!important;max-width:71%;}.pc3{width:3%!important;max-width:3%;}.pc25{width:25%!important;max-width:25%;}.pc0{width:0%!important;max-width:0%;}}
                    </style>
                    <style media="screen and (min-width:599px)">.moz-text-html .xc568{width:568px!important;max-width:568px;}.moz-text-html .xc536{width:536px!important;max-width:536px;}.moz-text-html .pc100{width:100%!important;max-width:100%;}.moz-text-html .pc71{width:71%!important;max-width:71%;}.moz-text-html .pc3{width:3%!important;max-width:3%;}.moz-text-html .pc25{width:25%!important;max-width:25%;}.moz-text-html .pc0{width:0%!important;max-width:0%;}</style>
                    <style type="text/css">
                        @media only screen and (max-width:599px){table.fwm{width:100%!important;}td.fwm{width:auto!important;}}
                    </style>
                    <style type="text/css">
                        u+.emailify a,#MessageViewBody a,a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important;font-size:inherit!important;font-family:inherit!important;font-weight:inherit!important;line-height:inherit!important;}span.MsoHyperlink{mso-style-priority:99;color:inherit;}span.MsoHyperlinkFollowed{mso-style-priority:99;color:inherit;}u+.emailify .glist{margin-left:0!important;}
                        @media only screen and (max-width:599px){.emailify{height:100%!important;margin:0!important;padding:0!important;width:100%!important;}u+.emailify .glist{margin-left:25px!important;}td.x{padding-left:0!important;padding-right:0!important;}br.sb{display:none!important;}.hd-1{display:block!important;height:auto!important;overflow:visible!important;}div.r.pr-16>table>tbody>tr>td{padding-right:16px!important}div.r.pl-16>table>tbody>tr>td{padding-left:16px!important}td.b.fw-1>table{width:100%!important}td.fw-1>table>tbody>tr>td>a{display:block!important;width:100%!important;padding-left:0!important;padding-right:0!important;}td.b.fw-1>table{width:100%!important}td.fw-1>table>tbody>tr>td{width:100%!important;padding-left:0!important;padding-right:0!important;}}
                    </style>
                    <meta name="color-scheme" content="light dark">
                    <meta name="supported-color-schemes" content="light dark">
                    <!--[if gte mso 9]>
                    <style>li{text-indent:-1em;}</style>
                    <![endif]-->
                </head>
                <body class="emailify" style="word-spacing:normal;background-color:#e5e5e5;">
                    <div style="background-color:#e5e5e5;">
                        <!--[if mso | IE]>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:16px 16px 16px 16px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                    <![endif]-->
                                                                    <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td style="background-color:transparent;border:none;vertical-align:middle;padding:10px 0px 0px 0px;">
                                                                                        <table border="0" cellpadding="0" cellspacing="0" style="" width="100%">
                                                                                            <tbody>
                                                                                                <tr>
                                                                                                    <td align="center" class="i " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;">
                                                                                                            <tbody>
                                                                                                                <tr>
                                                                                                                    <td style="width:568px;"> <img alt="" height="auto" src="https://s3.amazonaws.com/btm.website.storage/2022/01/d50d32491a6a6e0471d3d6079204b2d5.png" style="border:0;border-radius:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="568"></td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:10px 32px 10px 32px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:536px;">
                                                                    <![endif]-->
                                                                    <div class="xc536 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" class="d " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                        <p style="border-top:solid 1px #cccccc;font-size:1px;margin:0px auto;width:100%;"></p>
                                                                                        <!--[if mso | IE]>
                                                                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="border-top:solid 1px #cccccc;font-size:1px;margin:0px auto;width:536px;" width="536px">
                                                                                            <tr>
                                                                                                <td style="height:0;line-height:0;"> &nbsp;</td>
                                                                                            </tr>
                                                                                        </table>
                                                                                        <![endif]-->
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:10px 32px 16px 32px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:536px;">
                                                                    <![endif]-->
                                                                    <div class="xc536 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" class="x " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                        <div style="font-family:Open Sans,Arial,sans-serif;font-size:28px;line-height:32px;text-align:center;color:#000000;">
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:28px;font-family:Open Sans,Arial,sans-serif;font-weight:400;color:#000000;line-height:32px;">Welcome to </span><span style="mso-line-height-rule:exactly;font-size:28px;font-family:Open Sans,Arial,sans-serif;font-weight:700;font-style:italic;color:#000000;line-height:32px;">Behind the Markets!</span></p>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:20px 32px 20px 32px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:536px;">
                                                                    <![endif]-->
                                                                    <div class="xc536 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="left" class="x " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                        <div style="font-family:Roboto,Arial,sans-serif;font-size:16px;line-height:24px;text-align:left;color:#000000;">
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Hello <?php echo $first_name; ?>,</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Congratulations and thank you for joining the Behind the Markets family! Your new account has been created.</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Your account has been created. To access the new user account, use <b>your email</b> and <b>new password</b> below:</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><b>Username:</b> <?php echo $username; ?></span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><b>Password:</b> <?php echo $password; ?></span></p>
                                                                                            
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">You can log in to your account <a href="https://behindthemarkets.com/login/" target="_blank" style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><b>here.</b></a> </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">
                                                                                                <a href="https://behindthemarkets.com/login/" style="display:inline-block;width:200px;background:#ff9911;color:#ffffff;font-family:Roboto,Arial,sans-serif;font-size:13px;font-weight:normal;line-height:100%;margin:0;text-decoration:none;text-transform:none;padding:12px 0px 12px 0px;mso-padding-alt:0;border-radius:60px 60px 60px 60px; text-align: center;" target="_blank"> <span style="mso-line-height-rule:exactly;font-size:14px;font-family:Roboto,Arial,sans-serif;font-weight:700;color:#ffffff;line-height:16px;text-decoration:underline;">Access Account Now</span></a>
                                                                                            </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            
																							<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"> You should receive an additional email with all of your bonus reports, but you can also access these reports on the website in the “Bonus Reports” section once you&#39;re logged in. </p>
																							
																							<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
																							
																							<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">“The buck Stops Here”</p>
																							
																							<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
																							
																							<p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">Dylan Jovine, CEO &amp; Founder <br> <b><i>Behind the Markets</i></b></p>
																							
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"> <b>P.S.</b> You can also reset your password by going to: </span> <span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><a href="https://behindthemarkets.com/login/" target="_blank">https://behindthemarkets.com/login/ </a> and selecting "forgot password" then follow the steps or if you are already logged into your account you can go to "my account" in your customer dashboard and follow the steps to change your password.</span></p>
                                                                                            
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">&nbsp;</span></p>
                                                                                            
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;">If you have any issues, please contact us at: </span> <span style="mso-line-height-rule:exactly;font-size:16px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#777777;line-height:24px;"><a href="mailto:support@behindthemarkets.com" target="_blank">support@behindthemarkets.com </a> or <a href="tel:1-800-851-1965" target="_blank">1-800-851-1965 </a>.</span></p>
                                                                                            
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:0px 16px 20px 16px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                    <![endif]-->
                                                                    <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td style="background-color:transparent;border:none;vertical-align:middle;padding:0px 32px 0px 32px;">
                                                                                        <table border="0" cellpadding="0" cellspacing="0" style="" width="100%">
                                                                                            <tbody>
                                                                                                <tr>
                                                                                                    <td align="left" class="i fw-1 " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;" class="fwm">
                                                                                                            <tbody>
                                                                                                                <tr>
                                                                                                                    <td style="width:300px;" class="fwm"> <img alt="" height="auto" src="https://s3.amazonaws.com/btm.website.storage/2022/01/26d742660939a76923f0c0edfb003bdf.png" style="border:0;border-radius:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="300"></td>
                                                                                                                </tr>
                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                    </td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#ffffff">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#ffffff;background-color:#ffffff;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#ffffff;background-color:#ffffff;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:24px 16px 24px 16px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                    <![endif]-->
                                                                    <!-- <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" class="x m" style="font-size:0;padding:0;padding-bottom:8px;word-break:break-word;">
                                                                                        <div style="font-family:Roboto,Arial,sans-serif;font-size:28px;line-height:34px;text-align:center;color:#000000;">
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:28px;font-family:Roboto,Arial,sans-serif;font-weight:700;color:#000000;line-height:34px;text-decoration:underline;"><a href="https://www.behindthemarkets.com/login/" style="color:#000000;text-decoration:underline;" target="_blank">Click Here To Login To Your Account</a></span></p>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td class="s m" style="font-size:0;padding:0;padding-bottom:8px;word-break:break-word;">
                                                                                        <div style="height:4px;line-height:4px;">&#8202;</div>
                                                                                    </td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <td align="center" vertical-align="middle" class="b fw-1 " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:separate;width:117px;line-height:100%;">
                                                                                            <tbody>
                                                                                                <tr>
                                                                                                    <td align="center" bgcolor="#ff9911" style="border:none;border-radius:60px 60px 60px 60px;cursor:auto;mso-padding-alt:12px 0px 12px 0px;background:#ff9911;" valign="middle"> <a href="https://www.behindthemarkets.com/login/" style="display:inline-block;width:117px;background:#ff9911;color:#ffffff;font-family:Roboto,Arial,sans-serif;font-size:13px;font-weight:normal;line-height:100%;margin:0;text-decoration:none;text-transform:none;padding:12px 0px 12px 0px;mso-padding-alt:0;border-radius:60px 60px 60px 60px;" target="_blank"> <span style="mso-line-height-rule:exactly;font-size:14px;font-family:Roboto,Arial,sans-serif;font-weight:700;color:#ffffff;line-height:16px;text-decoration:underline;">Login Here!</span></a></td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div> -->
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#eeeeee">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#eeeeee;background-color:#eeeeee;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#eeeeee;background-color:#eeeeee;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:16px 16px 0px 16px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                    <![endif]-->
                                                                    <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" class="i " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                        <table border="0" cellpadding="0" cellspacing="0" style="border-collapse:collapse;border-spacing:0;">
                                                                                            <tbody>
                                                                                                <tr>
                                                                                                    <td style="width:156px;"> <img alt="" height="auto" src="https://s3.amazonaws.com/btm.website.storage/2022/01/c7e0f35c91630df02982ad7a9f922f45.png" style="border:0;border-radius:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="156"></td>
                                                                                                </tr>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <table align="center" border="0" cellpadding="0" cellspacing="0" class="r-outlook -outlook pr-16-outlook pl-16-outlook -outlook" style="width:600px;" width="600" bgcolor="#eeeeee">
                            <tr>
                                <td style="line-height:0;font-size:0;mso-line-height-rule:exactly;">
                                    <![endif]-->
                                    <div class="r pr-16 pl-16 " style="background:#eeeeee;background-color:#eeeeee;margin:0px auto;border-radius:0;max-width:600px;">
                                        <table align="center" border="0" cellpadding="0" cellspacing="0" style="background:#eeeeee;background-color:#eeeeee;width:100%;border-radius:0;">
                                            <tbody>
                                                <tr>
                                                    <td style="border:none;direction:ltr;font-size:0;padding:5px 16px 16px 16px;text-align:left;">
                                                        <!--[if mso | IE]>
                                                        <table border="0" cellpadding="0" cellspacing="0">
                                                            <tr>
                                                                <td class="c-outlook -outlook -outlook" style="vertical-align:middle;width:568px;">
                                                                    <![endif]-->
                                                                    <div class="xc568 ogf c " style="font-size:0;text-align:left;direction:ltr;display:inline-block;vertical-align:middle;width:100%;">
                                                                        <table border="0" cellpadding="0" cellspacing="0" style="background-color:transparent;border:none;vertical-align:middle;" width="100%">
                                                                            <tbody>
                                                                                <tr>
                                                                                    <td align="center" class="x " style="font-size:0;padding:0;padding-bottom:0;word-break:break-word;">
                                                                                        <div style="font-family:Roboto,Arial,sans-serif;font-size:11px;line-height:13px;text-align:center;color:#000000;">
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets is a newsletter offered to the public on a subscription basis. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">While subscribers receive the benefit of Behind the Markets opinions, none of the information contained therein constitutes a recommendation from Behind the Markets that any particular security, portfolio of securities, transaction, or investment strategy is suitable for any specific person. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">You further understand that we will not advise you personally concerning the nature, potential, value or suitability of any particular security, portfolio of securities, transaction, investment strategy or other matter. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">To the extent any of the information contained in Behind the Markets may be deemed to be investment advice, such information is impersonal and not tailored to the investment needs of any specific person. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets&rsquo; past results are not necessarily indicative of future performance. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Employees of Behind the Markets are subject to certain restrictions in transacting for their own benefit. SPECIFICALLY, EMPLOYEES ARE NOT PERMITTED TO BUY OR SELL ANY SECURITY RECOMMENDED FOR THREE (3) TRADING DAYS FOLLOWING THE ISSUE OF A REPORT OR UPDATE.</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets&rsquo; Newsletter contains Behind the Markets&rsquo; own opinions, and none of the information contained therein constitutes a recommendation by Behind the Markets that any particular security, portfolio of securities, transaction, or investment strategy is suitable for any specific person. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Behind the Markets&rsquo; past results are not necessarily indicative of future performance. DO NOT EMAIL Behind the Markets SEEKING PERSONALIZED INVESTMENT ADVICE, WHICH WE CANNOT PROVIDE. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The Editor's personal investing goals and risk tolerance may be substantially different from those discussed in the Newsletter and/or circumstances may have changed by the expiration of the three day restricted period, the investment actions taken by the Editor in the accounts the Editor directly or indirectly owns may vary from (and may even be contrary to) the advice and recommendations in the Newsletter.</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Investing involves substantial risk. Neither the Editor, the publisher, nor any of their respective affiliates make any guarantee or other promise as to any results that may be obtained from using the Newsletter. While past performance may be analyzed in the Newsletter, past performance should not be considered indicative of future performance. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">No subscriber should make any investment decision without first consulting his or her own personal financial advisor and conducting his or her own research and due diligence, including carefully reviewing the prospectus and other public filings of the issuer. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">To the maximum extent permitted by law, the Editor, the publisher and their respective affiliates disclaim any and all liability in the event any information, commentary, analysis, opinions, advice and/or recommendations in the Newsletter prove to be inaccurate, incomplete or unreliable, or result in any investment or other losses. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The Newsletter's commentary, analysis, opinions, advice and recommendations represent the personal and subjective views of the Editor and are subject to change at any time</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">without notice.</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The information provided in the Newsletter is obtained from sources which the Editor believes to be reliable. However, the Editor has not independently verified or otherwise investigated all such information. Neither the Editor, the publisher, nor any of their respective affiliates guarantees the accuracy or completeness of any such information. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">The Newsletter is not a solicitation or offer to buy or sell any securities. Further, the Newsletter is in no way intended to be a solicitation for any services offered by Behind the Markets </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Neither the Editor, the publisher, nor any of their respective affiliates are responsible for any errors or omissions in the Newsletter. </span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">Want to change how you receive these emails?</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">You can update your preferences or unsubscribe from this list.</span></p>
                                                                                            <p style="Margin:0;"><span style="mso-line-height-rule:exactly;font-size:11px;font-family:Roboto,Arial,sans-serif;font-weight:400;color:#aaaaaa;line-height:13px;">&nbsp;</span></p>
                                                                                        </div>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                    <!--[if mso | IE]>
                                                                </td>
                                                            </tr>
                                                        </table>
                                                        <![endif]-->
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <!--[if mso | IE]>
                                </td>
                            </tr>
                        </table>
                        <![endif]-->
                    </div>
                </body>
            </html>
        <?php
        $html = ob_get_contents();
        ob_get_clean();
        return $html;
    }

    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
WP_Recurly_Webhook_Controller::instance();