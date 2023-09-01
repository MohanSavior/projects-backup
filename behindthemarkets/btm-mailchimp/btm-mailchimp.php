<?php
/*
 * Plugin Name: BTM Mailchimp
 * Plugin URI: https://savior.im
 * Description: BTM Mailchimp.
 * Author: Savior
 * Author URI: http://savior.im
 * Version: 1.0.0
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
// add_action('init', 'btm_mailchimp_loader');
// function btm_mailchimp_loader()
// {
    $active_plugins = apply_filters('active_plugins', get_option('active_plugins'));

    if (in_array('woocommerce/woocommerce.php', $active_plugins) && class_exists('acf') && in_array('woocommerce-subscriptions/woocommerce-subscriptions.php', $active_plugins)) {
        // Plugin is active
        
        require_once dirname(__FILE__) . '/inc/vendor/autoload.php';
        
        // $client = new MailchimpMarketing\ApiClient();
        class BTM_Mailchimp
        {
            public $client;
            private static $instance = null;
            
            public function __construct()
            {
                $this->client = new MailchimpMarketing\ApiClient();
                $this->client->setConfig([
                    'apiKey' => get_field('config_api_key', 'option'),
                    'server' => get_field('config_server', 'option'),
                ]);                
// 				error_log(print_r($this->client->lists->getListMembersInfo('2c7b47b448'),true));
                add_action('woocommerce_subscription_status_updated', [$this, 'btm_client_subscription_status_updated'], 10, 3);
                //Customized options page
                add_action('acf/init', [$this, 'btm_mailchimp_acf_op_init']);
                add_action( 'admin_menu', [&$this, 'btm_mailchimp_add_sub_menu'], 105 );
                add_action( 'admin_enqueue_scripts', [&$this, 'enqueue_scripts_mailchimp'] );
                add_action( 'admin_footer', [&$this,'admin_css_enqueue'] );
                add_action( 'wp_ajax_mailchimp_check_member', [&$this,'mailchimp_check_member'] ); 
                add_action( 'wp_ajax_mailchimp_update_member', [&$this,'mailchimp_update_member'] ); 
                //Update customer
                add_action( 'profile_update', [ &$this, 'mailchimp_update_email'], 80, 2 );
            }            

            public function btm_mailchimp_curl_connect( $url, $request_type, $api_key, $data = array() ) {
                if( $request_type == 'GET' )
                    $url .= '?' . http_build_query($data);
                
                $mch = curl_init();
                $headers = array(
                    'Content-Type: application/json',
                    'Authorization: Basic '.base64_encode( 'user:'. $api_key )
                );
                curl_setopt($mch, CURLOPT_URL, $url );
                curl_setopt($mch, CURLOPT_HTTPHEADER, $headers);
                //curl_setopt($mch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
                curl_setopt($mch, CURLOPT_RETURNTRANSFER, true); // do not echo the result, write it into variable
                curl_setopt($mch, CURLOPT_CUSTOMREQUEST, $request_type); // according to MailChimp API: POST/GET/PATCH/PUT/DELETE
                curl_setopt($mch, CURLOPT_TIMEOUT, 10);
                curl_setopt($mch, CURLOPT_SSL_VERIFYPEER, false); // certificate verification for TLS/SSL connection
                
                if( $request_type != 'GET' ) {
                    curl_setopt($mch, CURLOPT_POST, true);
                    curl_setopt($mch, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
                }
            
                return curl_exec($mch);
            }


            public function mailchimp_update_email( $user_id, $old_user_data )
            {
                $old_user_email = $old_user_data->data->user_email; 
                $user = get_userdata( $user_id );
                $new_user_email = $user->user_email;
                if ( $new_user_email !== $old_user_email ) {
                    // Do something if old and new email aren't the same
                    $api_key = get_field('config_api_key', 'option');
                    $data = array(
                        'fields' => 'lists',
                        'email' => $old_user_email
                    );
                    $subscriberHash = md5(strtolower($old_user_email));
                    
                    $url = 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/';
                    $result = json_decode( $this->btm_mailchimp_curl_connect( $url, 'GET', $api_key, $data) );
                    if( !empty($result->lists) ) {
                        foreach( $result->lists as $list ){
                            error_log(print_r('lists id',true));
                            error_log(print_r($list->id,true));
                            try {
                                $data = array(
                                    'email_address'  => $new_user_email
                                );
                                $url = 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/'.$list->id.'/members/'.$subscriberHash;
                                $response = json_decode( $this->btm_mailchimp_curl_connect( $url, 'PATCH', $api_key, $data) );
                                error_log(print_r('updateListMember try',true));
                                // error_log(print_r($response,true));
                            } catch (\EXCEPTION $e) {
                                error_log(print_r('updateListMember catch',true));
                                error_log(print_r($e->getMessage(),true));
                            } 
                        }
                    }
                }else{
                    error_log(print_r('updateListMember else',true));
                }
            }
		
			public function btm_client_getListMember($listID, $user_email)
            {
				try {
					$subscriberHash = md5(strtolower($user_email));
					$response = $this->client->lists->getListMember($listID, $subscriberHash);
					return $response;
				} catch (Exception $e) {
					return $e->getMessage();
				}
            }

            public function btm_client_setListMember($listID, $user_email, $status)
            {
                $subscriberHash = md5(strtolower($user_email));			
				$user 				= get_user_by( 'email', $user_email );
				$firstName 			= get_user_meta($user->ID, 'first_name', true) ? get_user_meta($user->ID, 'first_name', true): 'Test';
				$lastName 			= get_user_meta($user->ID, 'last_name', true) ? get_user_meta($user->ID, 'last_name', true): 'Test';
				$billing_address_1 	= get_user_meta($user->ID, 'billing_address_1', true) ? get_user_meta($user->ID, 'billing_address_1', true): 'Test';
				$billing_city 		= get_user_meta($user->ID, 'billing_city', true) ? get_user_meta($user->ID, 'billing_city', true): 'Test';
				$billing_state 		= get_user_meta($user->ID, 'billing_state', true) ? get_user_meta($user->ID, 'billing_state', true): 'test';
				$billing_postcode 	= get_user_meta($user->ID, 'billing_postcode', true) ? get_user_meta($user->ID, 'billing_postcode', true): '10001';
				$billing_country 	= get_user_meta($user->ID, 'billing_country', true) ? get_user_meta($user->ID, 'billing_country', true): 'US';
				
                $subscriberHash = md5(strtolower($user_email));
				// if($status =='unsubscribed')
				try{
                    $response = $this->client->lists->setListMember($listID, $subscriberHash, 
                        [
                            "email_address" => $user_email,
                            "status_if_new" => $status,
                            "status" => $status,
                            "merge_fields" => [
                                "FNAME" => $firstName,
                                "LNAME" => $lastName,
                                "ADDRESS" => [
                                    "addr1"   => $billing_address_1,
                                    "city"    => $billing_city,
                                    "state"   => $billing_state,
                                    "country" => $billing_country,
                                    "zip"     => $billing_postcode
                                ]  
                            ]
                        ]
                    );
                    // error_log(print_r('btm_client_getListMember',true));
                    // error_log(print_r($response,true));
                    if(!empty($response->id))
                    {
                        update_user_meta($user->ID, 'mailchimp_member_id', $response->id);
                    }
					return $response;
				} catch (\Exception $e) {
                    // error_log(print_r('getMessage',true));
                    // error_log(print_r($e,true));
					return $e->getMessage();
				} 
            }

            public function btm_mailchimp_getlist_id_by_product_id($product_id)
            {
                $list_id = '';
                if (have_rows('product_assign_with_mailchimp_list_ids', 'option')):
                    while (have_rows('product_assign_with_mailchimp_list_ids', 'option')): the_row();
                        if ($product_id === get_sub_field('btm_product')) {
                            $list_id = get_sub_field('btm_mailchimp_list_ids');
                            break;
                        }
                    endwhile;
                endif;

                return $list_id;
            }

            public function btm_mailchimp_getlist_id()
            {
                $list_id = [];
                if (have_rows('product_assign_with_mailchimp_list_ids', 'option')):
                    $i = 1;
                    while (have_rows('product_assign_with_mailchimp_list_ids', 'option')): the_row();
                            $list_id[get_sub_field('btm_product')] = get_sub_field('btm_mailchimp_list_ids');
                            $i++;
                    endwhile;
                endif;

                return $list_id;
            }

            public function btm_client_subscription_status_updated($subscription, $new_status, $old_status)
            {
                $subscription_data = $subscription->get_data(); //array
                $order_id 			= $subscription_data['parent_id'];
                $subscription_id	= $subscription_data['id'];
				$user_id 			= $subscription->get_user_id();
				$userdata 			= WP_User::get_data_by( 'id', $user_id );
                $subscription_status = $subscription_data['status']; //pending, active, on-hold, pending-cancel, cancelled, or expired
                $recurly_subscription_id = get_post_meta($order_id, '_recurly_subscription_id', true);
                $user_email = !empty($userdata->user_email) ? $userdata->user_email : $subscription->get_billing_email();

                $product_id = '';
                $order_items = $subscription->get_items();

                // Loop through order items
                foreach ($order_items as $item_id => $item) {
                    $product = $item->get_product();
                    $product_id = $item->get_product_id();
                }
                $list_id = $this->btm_mailchimp_getlist_id_by_product_id($product_id);
                if (!empty($list_id)) {
                    switch ($new_status) {
                        case 'active':
                            $status = 'subscribed';
                            $this->btm_client_setListMember($list_id, $user_email, $status);
                            break;
						default:
							$status = 'unsubscribed';
                            if(! $this->btm_mailchimp_get_user_active_subscription_variation_ids( $user_id, $product_id ))
                            {
                                $this->btm_client_setListMember($list_id, $user_email, $status);
                            }
						break;
                    }
                }
            }
            
            public function btm_mailchimp_get_user_active_subscription_variation_ids( $user_id, $product_id )
            {
                $subscriptions = wcs_get_users_subscriptions($user_id);
                $pro_ids = [];
                foreach( $subscriptions as $subscription )
                {
                    if ($subscription->has_status(array('active'))) {
                        foreach($subscription->get_items() as $item)
                        {
                            $pro_data = $item->get_data();
                            $pro_ids[] = $pro_data['variation_id'];
                        }
                    }
                }
                $product        = wc_get_product($product_id);
                $variations     = $product->get_available_variations();
                $variations_id  = wp_list_pluck( $variations, 'variation_id' );
                $check = array_intersect($pro_ids, $variations_id);
                return !empty($check) ? true : false;
            }
            public function btm_mailchimp_acf_op_init()
            {
                if (function_exists('acf_add_options_page')) {
                    $option_page = acf_add_options_page(array(
                        'page_title' => __('MailChimp Settings'),
                        'menu_title' => __('MailChimp Settings'),
                        'menu_slug' => 'mailchimp-general-settings',
                        'capability' => 'edit_posts',
                        'redirect' => false,
                    ));
                }
            }
            public function btm_mailchimp_add_sub_menu()
            {
                add_submenu_page(
                    'mailchimp-general-settings', 
                    __('Check member on mailchimp', 'mailchimp'), 
                    __('Check member on mailchimp', 'mailchimp'), 
                    'manage_options', 
                    'check-member-on-mailchimp', 
                    [&$this, 'check_member_in_mailchimp_list_id']
                );
            }
            public function check_member_in_mailchimp_list_id(){
                ob_start();
                 ?>
                <div class="wrap">
                    <div id="loader" class="lds-dual-ring hidden overlay"></div>
                    <h1 class="wp-heading-inline">Check member on mailchimp</h1>                    
                    <div id="search_member">
                        <label for="search-member"></label>
                        <input type="text" id="search-member" placeholder="Please enter email">
                        <button id="search_member_by_email" class="button">Get Member Details</button>
                    </div>
                    <div id="show-member"></div>
                    <!-- <div id="member-details"></div> -->
                </div>
                <?php
                $string = ob_get_contents();
                // ob_end_clean();
                return $string;
            
            }
            public function enqueue_scripts_mailchimp() {
                wp_enqueue_script( 'mailchimp-member', plugins_url('assets/js/member-list.js?t='.time(), __FILE__) , array('jquery'), '', true );
                wp_localize_script(
                    'mailchimp-member',
                    'ajax_obj',
                    array(
                        'ajaxurl' => admin_url( 'admin-ajax.php' ),
                        'nonce' => wp_create_nonce('ajax-nonce')
                    )
                );
            }
            public function mailchimp_check_member()
            {   extract($_POST);
                $nonce = $_POST['nonce'];
                if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
                    wp_die(json_encode(array('success'=> false,'message'=>'Nonce value cannot be verified.')));
                }
             
                // The $_REQUEST contains all the data sent via ajax
                if ( isset($_POST) )
                {
                    if(email_exists( $id ))
                    {
                        $user = get_user_by('email',$id);
                        // echo"<pre>";
                        // print_r($this->btm_mailchimp_getlist_id());
                        // echo"</pre>";
                        $users_subscriptions = wcs_get_users_subscriptions($user->ID);
                        $subscriptions_data = '';
                        if(!empty($users_subscriptions))
                        {   
                            $subscriptions_data .=  '<div id="member-details">
                                                    <h1>Subscriber With MailChimp Details</h1>
                                                                    <table>
                                                                        <thead>
                                                                            <tr>
                                                                                <td>Subscription Details</td>
                                                                                <td>MailChimp Details</td>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>';
                            $list_data = [];
                            $subscriptions_div = [];
                            $list_id = '';
                            foreach ($users_subscriptions as $subscription){
                                if ($subscription->has_status(array('active'))) {
                                    if ( sizeof( $subscription_items = $subscription->get_items() ) > 0 ) {
                                        foreach ( $subscription_items as $item_id => $item ) {
                                            $product = $item->get_product();
                                            $item_data = $item->get_data();
                                            $lists = $this->btm_mailchimp_getlist_id();
                                            $list_id = $lists[$item_data['product_id']];
                                            
                                            $subscriptions_div[] =  '<tr id="'.$list_id.'">';
                                            $subscriptions_div[] = "<td class='subscription-item-data'>";

                                            $subscriptions_div[] = $item_data['name'];
                                            $subscriptions_div[] = "</td>";
                                            $subscriptions_div[] = "<td class='mailchimp-item-data'>";
                                            if (!empty($list_id)) {
                                                $getListMember = $this->btm_client_getListMember($list_id, trim($id));
                                                if(!empty($getListMember->status)){
                                                    $subscriptions_div[] = "<input type='radio' data-mailchimp='".$id."' ".checked( $getListMember->status,'subscribed',false)." id='".$item_id."_subscribed' name='".$list_id."' value='subscribed'/><label for='".$item_id."_subscribed' class='mailchimp-status subscribed'>Subscribed</label>"; 
                                                    $subscriptions_div[] = "<input type='radio' data-mailchimp='".$id."' ".checked( $getListMember->status,'unsubscribed',false)." id='".$item_id."_unsubscribed' name='".$list_id."' value='unsubscribed'/><label for='".$item_id."_unsubscribed' class='mailchimp-status unsubscribed'>Unsubscribed</label>"; 
                                                }else{
                                                    // $subscriptions_div[] = ' Not found in list ID do you want to ';
                                                    $subscriptions_div[] = "<input type='radio' data-mailchimp='".$id."' id='".$item_id."_subscribed' name='".$list_id."' value='subscribed'/><label for='".$item_id."_subscribed' class='mailchimp-status subscribed'>Subscribe</label>"; 
                                                }
                                            }else{
                                                // $subscriptions_div[] = 'List ID : '.$list_id.' Product ID '.$item_data['product_id'];
                                                $subscriptions_div[] = ' Not in list ID';
                                            }
                                            $subscriptions_div[] = "</td>";
                                            $subscriptions_div[] =  '</tr>';
                                        }
                                    }
                                    //                          break;
                                    if(is_array($subscriptions_div)){
                                        $list_data[] = implode('', $subscriptions_div);
                                        // print_r($subscriptions_div);
                                        unset($subscriptions_div);
                                    }
                                }
                            }
                            // print_r($list_data);
                            $subscriptions_data .= implode('', $list_data);
                            $subscriptions_data .= '</tbody>
                                                    </table>
                                                    <div id="update-response" class=""></div>
                                                </div>';
                            wp_die(json_encode(array('success'=> true,'message'=> $subscriptions_data)));
                        }else{
                            wp_die(json_encode(array('success'=> false, 'message'=> '<div class="notice notice-warning"><p>No subscription found this user!!!!</p></div>')));
                        }
                    }else{
                        wp_die(json_encode(array('success'=> false, 'message'=> '<div class="notice notice-error"><p>User not exists!</p></div>')));
                    }
                    wp_die(json_encode(array('success'=> false,'message'=>$_POST)));
                }else{
                    wp_die(json_encode(array('success'=> false,'message'=>'Something error please reload page')));
                }
            }
            public function mailchimp_update_member()
            {   extract($_POST);
                $nonce = $_POST['nonce'];
                if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
                    wp_die(json_encode(array('success'=> false,'message'=>'Nonce value cannot be verified.')));
                }
                if ( isset($_POST) )
                {
                    $id = trim($id);
                    $response = $this->btm_client_setListMember($list_id, $id, $status);
                    if(!empty($response->status))
                    {
                        wp_die(json_encode(array('success'=> true, 'message'=> '<div class="notice notice-success"><p>User <b>'.$response->status.'</b> successfully.</p></div>')));
                    }else{
                        wp_die(json_encode(array('success'=> false,'message'=>'Something error please reload page..')));
                    }                    
                }else{
                    wp_die(json_encode(array('success'=> false,'message'=>'<div class="notice notice-error"><p>Something error please reload page</p></div>')));
                }
            }
            public function admin_css_enqueue()
            {
                if($_REQUEST['page'] === 'check-member-on-mailchimp')
                {
                    ?>
                    <style>
                        .lds-dual-ring.hidden {display: none;}.overlay {position: fixed;top: 0;left: 0;width: 100%;height: 100vh;background: rgba(0,0,0,.8);z-index: 999;opacity: 1;transition: all 0.5s;}/*Spinner Styles*/.lds-dual-ring {display: inline-block;width: 80px;height: 80px;}.lds-dual-ring:after {content: " ";display: block;width: 64px;height: 64px;margin: 5% auto;border-radius: 50%;border: 6px solid #fff;border-color: #fff transparent #fff transparent;animation: lds-dual-ring 1.2s linear infinite;position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);margin: 0 auto;}@keyframes lds-dual-ring {0% {transform: rotate(0deg);}100% {transform: rotate(360deg);}} table {width: 100%;margin-bottom: 20px;} th, td {border-color: #96D4D4;border: 1px solid #dddddd;text-align: left;padding: 8px;}th {text-align: left;}div#loader {width: 100%;height: 100vh;}
                        div#search_member input#search-member {
                            width: 260px;
                        }
                        div#search_member {
                            margin-bottom: 30px;
                        }
                        div#member-details table thead tr>td,
                        div#subscriber-account-details table thead tr>td {
                            font-weight: 600;
                            font-size: 16px;
                            text-transform: uppercase;
                        }
                        div#subscriber-account-details,
                        div#member-details {
                            padding: 20px;
                            border: 1px solid #dddddd;
                            border-radius: 5px;
                            box-shadow: 1px 13px 20px 3px #bcbcbc;
                            background-color: #fff;
                        }
                        div#subscriber-account-details h1,
                        div#member-details h1 {
                            font-weight: 600;
                            margin-bottom: 20px;
                            text-transform: uppercase;
                        }
                        div#member-details table tbody tr>td ,
                        div#subscriber-account-details table tbody tr>td {
                            font-size: 15px;
                        }
                        label.mailchimp-status {
                            display: inline-block;
                            vertical-align: top;
                            box-sizing: border-box;
                            margin: 1px 12px 1px 2px;
                            padding: 0 5px;
                            min-width: 18px;
                            height: 18px;
                            border-radius: 9px;                            
                            color: #fff;
                            font-size: 11px;
                            line-height: 1.6;
                            text-align: center;
                            z-index: 26;
                        }
                        label.unsubscribed {
                            background-color: #d63638;
                        }
                        label.subscribed {
                            background-color: #008608;
                        }
                    </style>
                    <?php
                }
            }

            public static function instance()
            {
                if (empty(self::$instance)) {
                    self::$instance = new self();
                }
                return self::$instance;
            }
        }
        BTM_Mailchimp::instance();
    }
// }