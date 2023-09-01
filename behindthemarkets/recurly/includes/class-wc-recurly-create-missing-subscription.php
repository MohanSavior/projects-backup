<?php
/**
 * REST API: WC_Recurly_Create_Missing_Subscription class
 *
 */

class WC_Recurly_Create_Missing_Subscription
{
    /**
     * Namespace.
     *
     * @since 4.7.0
     * @var string
     */
    private static $instance = null;

    protected static $plugin_file = __FILE__;
    /**
     * Constructor.
     *
     * @since 4.7.0
     *
     * @param string $taxonomy Taxonomy key.
     */
    public function __construct()
    {
        add_action( 'admin_menu', [&$this, 'missing_subscription_menu'], 10 );
        add_action( 'admin_enqueue_scripts', [&$this, 'enqueue_scripts_missing_subscription'] );
        add_action( 'wp_ajax_missing_subscription_request', [&$this,'missing_subscription_request'] ); 
        add_action( 'wp_ajax_missing_subscription_create', [&$this,'missing_subscription_create'] ); 
        add_action( 'wp_ajax_nopriv_missing_subscription_create', [&$this,'missing_subscription_create'] ); 
        add_action( 'admin_footer', [&$this,'admin_css_enqueue'] ); // For back-end
    }
    public function admin_css_enqueue()
    {
        if($_REQUEST['page'] === 'missing_subscription')
        {
            ?>
            <style>
                .lds-dual-ring.hidden {display: none;}.overlay {position: fixed;top: 0;left: 0;width: 100%;height: 100vh;background: rgba(0,0,0,.8);z-index: 999;opacity: 1;transition: all 0.5s;}/*Spinner Styles*/.lds-dual-ring {display: inline-block;width: 80px;height: 80px;}.lds-dual-ring:after {content: " ";display: block;width: 64px;height: 64px;margin: 5% auto;border-radius: 50%;border: 6px solid #fff;border-color: #fff transparent #fff transparent;animation: lds-dual-ring 1.2s linear infinite;position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);margin: 0 auto;}@keyframes lds-dual-ring {0% {transform: rotate(0deg);}100% {transform: rotate(360deg);}} table {width: 100%;margin-bottom: 30px;} th, td {border-color: #96D4D4;border: 1px solid #dddddd;text-align: left;padding: 8px;}th {text-align: left;}div#loader {width: 100%;height: 100vh;}
                div#search_subscription input#search-subscription {
                    width: 260px;
                }
                div#subscriber-subscription-details table thead tr>td,
                div#subscriber-account-details table thead tr>td {
                    font-weight: 600;
                    font-size: 16px;
                    text-transform: uppercase;
                }
                div#subscriber-account-details,
                div#subscriber-subscription-details {
                    padding: 20px;
                    border: 1px solid #dddddd;
                    border-radius: 5px;
                    box-shadow: 1px 13px 20px 3px #bcbcbc;
                    background-color: #fff;
                }
                select#existing_plans {
                    margin-bottom: 20px;
                }
                div#subscriber-account-details {
                    margin-bottom: 50px;
                    margin-top: 30px;
                }
                div#subscriber-account-details h1,
                div#subscriber-subscription-details h1 {
                    font-weight: 600;
                    margin-bottom: 20px;
                    text-transform: uppercase;
                }
                div#subscriber-subscription-details table tbody tr>td ,
                div#subscriber-account-details table tbody tr>td {
                    font-size: 15px;
                }
            </style>
            <?php
        }
    }

    public function missing_subscription_menu()
    {
        add_submenu_page(
            'woocommerce', 
            __('Create Subscription', 'woocommerce'), 
            __('Create Subscription', 'woocommerce'), 
            'manage_woocommerce', 
            'missing_subscription', 
            [&$this, 'create_missing_subscription_fn']
        );
    }
    public function create_missing_subscription_fn(){
        ob_start();
         ?>
        <div class="wrap">
            <div id="loader" class="lds-dual-ring hidden overlay"></div>
            <h1 class="wp-heading-inline">Create Missing Subscription</h1>
            <div class="notice notice-warning">
            <p>If there is any subscription which is present in the recurly but not showing in the woocommerce, due to which customer is not able to see that product in his/her Behind the markets account then please use this tool to add that subscription.</p></div>
            <div id="search_subscription">
                <label for="search-subscription"></label>
                <input type="text" id="search-subscription" placeholder="Please enter recurly subscription uuid">
                <button id="search_subscription_by_uuid" class="button">Get Subscription Details</button>
            </div>
            <div id="show-subscription"></div>
            <div id="subscription-details"></div>
        </div>
        <?php
        $string = ob_get_contents();
        // ob_end_clean();
        return $string;
    
    }
    public function enqueue_scripts_missing_subscription() {
		wp_enqueue_script( 'missing-subscription', plugins_url('../assets/js/get-create-subscription.js?t='.time(), __FILE__) , array('jquery'), '', true );
        wp_localize_script(
            'missing-subscription',
            'ajax_obj',
            array(
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce('ajax-nonce')
            )
        );
	}
    public function missing_subscription_request()
    {
        $nonce = $_POST['nonce'];

        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            wp_die(json_encode(array('success'=> false,'message'=>'Nonce value cannot be verified.')));
        }
     
        // The $_REQUEST contains all the data sent via ajax
        if ( isset($_POST) ) {         
                    //https://v3.recurly.com/subscriptions/{subscription_id}
            $subscription = WC_Recurly_API::request([], 'subscriptions/uuid-'.$_REQUEST['uuid'], 'GET');
            // print_r($subscription);
            $args = array(
                'post_type'     => 'product_variation',
                'post_status'   => array( 'publish' ),
                'numberposts'   => -1,
                'fields'        => 'ids'
            );
            $loops = get_posts( $args );
            $product_code = [];
            $product_variation_id = null;
            $existing_plans = '';
            $comp_code = array('comp-btm-bronze' =>'btm-bronze', 'comp-btm-silver' =>'btm-silver', 'comp-btm-platinum' =>'btm-platinum', 'comp-btm-lifetime' =>'btm-lifetime', 'comp-btm-alli' =>'btm-alli', 'comp-bi-annual' =>'bi-annual', 'comp-bi-two-year' =>'bi-two-year', 'comp-bi-lifetime' =>'bi-lifetime', 'comp-bw-annual' =>'bw-annual', 'comp-bw-two-year' =>'bw-two-year', 'comp-bw-lifetime' =>'bw-lifetime', 'comp-hmp-annual' =>'hmp-annual', 'comp-hmp-two-year' =>'hmp-two-year', 'comp-hmp-lifetime' =>'hmp-lifetime', 'comp-tt-annual' =>'tt-annual', 'comp-tt-two-year' =>'tt-two-year', 'comp-tt-lifetime' =>'tt-lifetime','bi-upsell-annual'=>'bi-annual','bi-upsell-lifetime' => 'bi-lifetime');
            
            foreach($loops as $loop )
            {   
                $product_code[] = trim(get_post_meta($loop,'_recurly_plan_code', true)); 
                $subscription_plan_code = (string)$subscription->plan->code;
                if(in_array($subscription_plan_code,array_keys($comp_code))){
                    $subscription_plan_code = $comp_code[$subscription_plan_code];
                }
                if(trim(get_post_meta($loop,'_recurly_plan_code', true)) == $subscription_plan_code)
                {   
                    $product_variation_id = $loop;
                }

                $existing_plans .= '<option value="'.$loop.'">'.get_the_title($loop).'</option>';
            }
            // print_r($subscription_plan_code);
            // exit;
            $account_detail ='';
            $existing_plans_html ='';
            if(! in_array($subscription->plan->code, $product_code))
            {
                $account_detail = '<div class="notice notice-error"><p><b>'.$subscription->plan->name.' ('.$subscription->plan->code.')</b> not matched with woocommerce product <b>plan name & code.</b></p></div>';
                $existing_plans_html .= '<div class="notice"><p>Here are the active plans which you can assign this subscriptions</p></div>';
                $existing_plans_html .= '<select id="existing_plans" name="existing_plans">';
                $existing_plans_html .= '<option selected disabled>Please select </option>';
                $existing_plans_html .= $existing_plans;
                $existing_plans_html .= '</select>';
                // wp_die(json_encode(array('success'=> false,'message'=>$account_detail.$existing_plans_html,'data'=> $product_code)));
            }

            if(!empty($subscription->error))
            {
                $message = '<div class="notice notice-error"><p>'.$subscription->error->message.'</p></div>';
                wp_die(json_encode(array('success'=> false,'message'=>$message,'data'=> $subscription)));
            }else{
                $accounts = WC_Recurly_API::request([], 'accounts/'.$subscription->account->id, 'GET');
                if(empty($accounts->error))
                {
                    if(email_exists($subscription->account->code) || email_exists($subscription->account->email))
                    {
                        $user = get_user_by( 'email', $subscription->account->email ) ? get_user_by( 'email', $subscription->account->email ) : get_user_by( 'email', $subscription->account->code );
                        $user_exists = '<b>User already exists</b>';
                        $user_check = 1;
                    }else{
                        $user_check = 0;
                        $user = $subscription->account->email ? $subscription->account->email : $subscription->account->code;
                        $user_exists = '<b>User not exists</b>';
                    }
                    $get_subscriptions = get_posts( array(
                        'post_type'     => 'shop_subscription',
                        'posts_per_page'=> -1,
                        'post_status'   => 'wc-active',
                        'fields'        => 'ids',
                        'meta_query'    => array(
                            array(
                                'key'   => '_recurly_subscription_uuid',
                                'value' => array($subscription->uuid)
                            )
                        )
                    ));$sub = '';
                    if(!empty($get_subscriptions))
                    {                           
                        foreach($get_subscriptions as $subscription_id)
                        { 
                            $sub_id = $subscription_id;
                            $sub .= 'Subscription already exists, Subscription ID <a href="'. site_url('wp-admin/post.php?post='.$sub_id.'&action=edit').'">'.$sub_id.'</a></br>';
                        }
                        
                        $subscription_create = '';
                    }elseif($subscription->state == 'expired' || $subscription->state == 'failed'){
                        $sub = '';
                        $sub = 'Subscription status is <b>'.$subscription->state.' </b>' ;
                    }else{
                        $sub = 'Subscription not present in woocommerce database';
                        $sub_data = array(
                            'user_check'=> $user_check,
                            'product_variation_id'      => $product_variation_id, 
                            'subscription_uuid'         => $subscription->uuid, 
                            'action'                    => 'missing_subscription_create',
                            'status'                    => $subscription->state,
                            'current_period_started_at' => $subscription->current_period_started_at,
                            'current_period_ends_at'    => $subscription->current_period_ends_at,
                            'address' => array(
                                'first_name'    => $accounts->first_name ? $accounts->first_name :'',
                                'last_name'     => $accounts->last_name ? $accounts->last_name : '',
                                'email'         => $accounts->email ? $accounts->email : '',
                                'address_1'     => $accounts->address->street1 ? $accounts->address->street1 : '',
                                'address_2'     => $accounts->address->street2 ? $accounts->address->street2 : '',
                                'city'          => $accounts->address->city ? $accounts->address->city : '',
                                'state'         => $accounts->address->region ? $accounts->address->region : '',
                                'postcode'      => $accounts->address->postal_code ? $accounts->address->postal_code : '',
                                'country'       => $accounts->address->country ? $accounts->address->country : '',
                                'phone'         => $accounts->address->phone ? $accounts->address->phone : '',
                            )                             
                        );
                        if($user_check)
                        {
                            $sub_data['user'] = $user->ID;
                        }else{
                            $sub_data['user'] = $user;
                        }
                        $subscription_data = json_encode($sub_data, true);
                        $show = ($sub_data['product_variation_id'] != null) ? 'block' : 'none';
                        $account_detail .= $existing_plans_html;
                        $subscription_create = "<button style='display: ".$show."' class='button button-primary button-large' data-subscription_data='".$subscription_data."' id='create_missing_subscription'>Create subscription </button>";
                    }
                    $account_detail .=   '<div id="subscriber-account-details">
                                            <h1>Subscriber Account Details</h1>
                                            <table>
                                                <thead>
                                                    <tr>
                                                        <td>Name</td>
                                                        <td>Email</td>
                                                        <td>Address</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td>'.$accounts->first_name.' '.$accounts->last_name.'</td>
                                                        <td>'.$accounts->email.'</td>
                                                        <td>
                                                            '.$accounts->address->street1.' 
                                                            '.$accounts->address->street2.' 
                                                            '.$accounts->address->city.' 
                                                            '.$accounts->address->region.' 
                                                            '.$accounts->address->postal_code.' 
                                                            '.$accounts->address->country.' 
                                                            '.$accounts->address->phone.
                                                        '</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div class="notice notice-success">
                                            <p>'.$user_exists.'</p>
                                            </div>
                                        </div>';
                }
                else
                {
                    $account_detail = '';
                }
                $account_detail .=  '<div id="subscriber-subscription-details">
                                        <h1>Subscriber Subscription Details</h1>
                                        <table>
                                            <thead>
                                                <tr>
                                                    <td>Plan name</td>
                                                    <td>Plan amount</td>
                                                    <td>Created at</td>
                                                    <td>Activated </td>
                                                    <td>Auto Renew </td>
                                                    <td>Next billing</td>
                                                    <td>Uuid</td>                                            
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>'.$subscription->plan->name.'</td>
                                                    <td>'.$subscription->subtotal.'</td>
                                                    <td>'.$subscription->created_at.'</td>
                                                    <td>'.$subscription->activated_at.'</td>
                                                    <td>'.$subscription->auto_renew.'</td>
                                                    <td>'.$subscription->current_term_ends_at.'</td>
                                                    <td>'.$subscription->uuid.'</td>   
                                                </tr>
                                            </tbody>
                                        </table>
                                        <div class="notice notice-warning">
                                        <p>'.$sub.'</p>
                                        </div>
                                        <p>'.$subscription_create.'</p>
                                    </div>';
                wp_die(json_encode(array('success'=> true,'message'=>'','data'=> $account_detail)));
            }
        }else{
            wp_die(json_encode(array('success'=> false,'message'=>'','data'=> $subscription)));
        }
    }
    public function missing_subscription_create()
    {
        $nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            wp_die(json_encode(array('success'=> false,'message'=>'Nonce value cannot be verified.')));
        }
     
        // The $_REQUEST contains all the data sent via ajax
        if ( isset($_POST) )
        {
            extract($_POST);
            if ( $product_variation_id == null) {
                $message = '<div class="notice notice-error"><p>Please select a valid active plan.</p></div>';
                wp_die(json_encode(array('success'=> false,'message'=>$message)));
            }
            $message = '';
            // var_dump($user_check);
            // exit;
            if(! boolval($user_check))
            {
                $password = wp_generate_password(12, true);
                $result = wp_insert_user(array(
                    'user_login'    => $address['email'],
                    'user_pass'     => $password,
                    'user_email'    => $address['email'],
                    'first_name'    => $address['first_name'],
                    'last_name'     => $address['last_name'],
                    'display_name'  => $address['first_name'] . ' ' . $address['last_name'],
                    'role'          => 'customer'
                ));
                // $result =  wp_create_user( string $username, string $password, string $email = '' );
                if(is_wp_error($result)){
                    $error = $result->get_error_message();
                    $message .= '<div class="notice notice-error"><p>'.$error.'</p></div>';
                    wp_die(json_encode(array('success'=> false,'message'=> $message)));
                }else{
                    // $user = get_user_by('id', $result);
                    $user_id = $result;
                    $message .= '<div class="notice notice-success"><p>User: <b>'.$address['first_name'] . ' ' . $address['last_name'].'</b> created successfully.</p></div>';
                }           
                
            }else{
                $user_id = $user;
                $message .= '<div class="notice notice-success"><p>User checking success.</p></div>';
            }
            if((int)$user_id)
            {
                $sub_status = $status == 'active' ? $status : 'pending';
                // Now we create the order
                $order = wc_create_order(array('customer_id' => $user_id));

                if (is_wp_error($order)) {
                    $error = $order->get_error_message();
                    $message .= '<div class="notice notice-error"><p>'.$error.'</p></div>';
                    wp_die(json_encode(array('success'=> false,'message'=> $message)));
                }
                $product = wc_get_product($product_variation_id);
                $order->add_product($product, 1);
                $order->set_address($address, 'billing');
                $order->calculate_totals();
                $order->update_meta_data('_recurly_subscription_uuid', $subscription_uuid);
                $order->save();

                $start_date = gmdate('Y-m-d H:i:s', strtotime($current_period_started_at));// +10 SEC
                $sub = wcs_create_subscription(array(
                    'order_id'          => $order->get_id(),
                    'status'            => $sub_status,
                    'billing_period'    => WC_Subscriptions_Product::get_period($product),
                    'billing_interval'  => WC_Subscriptions_Product::get_interval($product)
                ));

                if (is_wp_error($sub)) {
                    $error = $sub->get_error_message();
                    $message .= '<div class="notice notice-error"><p>'.$error.'</p></div>';
                    wp_die(json_encode(array('success'=> false,'message'=> $message)));
                }
                else
                {
                    $message .= '<div class="notice notice-success"><p>Subscription created successfully <a href="'. site_url('wp-admin/post.php?post='.$sub->get_id().'&action=edit').'">'.$sub->get_id().'</a>.</p></div>';
                }
                
                // Add product to subscription
                $sub->add_product($product, 1);
                $sub->update_meta_data('_recurly_subscription_uuid', $subscription_uuid);
                $next_payment = gmdate('Y-m-d H:i:s', strtotime($current_period_started_at));// +10 SEC
                $dates = array(
                    'start'             => $start_date,
                    'next_payment'      => WC_Subscriptions_Product::get_first_renewal_payment_date($product, $start_date),
                    'end'               => WC_Subscriptions_Product::get_expiration_date($product, $start_date),
                    'trial_end'         => WC_Subscriptions_Product::get_trial_expiration_date($product, $start_date)
                );

                $sub->update_dates($dates);
                $sub->calculate_totals();
                $sub->set_address($address, 'billing');

                // Update order status with custom note
                $note = !empty($note) ? $note : __('Subscription created by manually admin option.');
                ($sub_status == 'active') ? $order->update_status('completed', $note, true) : $order->update_status($sub_status, $note, true);
                // Also update subscription status to active from pending (and add note)
                $sub->update_status($sub_status, $note, true);
                update_post_meta( $sub->get_id(), '_requires_manual_renewal', "true" );
                
                if ( function_exists( 'wc_memberships' ) ) {
                    $plan_id = null;
                    foreach(wc_memberships_get_membership_plans() as $membership_plan){
                        $get_product_ids = $membership_plan->get_product_ids();
                        if(in_array($product_variation_id, $membership_plan->get_product_ids()))
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
                            'plan_id'       => $plan_id,
                            'user_id'       => $user_id,
                            'product_id'    => $product_variation_id, 
                            'order_id'      => $order->get_id(),
                        );
                        if(empty($active_membership_ids)){
                            $membership = wc_memberships_create_user_membership( $args );
                            $message .= '<div class="notice notice-success"><p>Membership created successfully.</p></div>';
                        }
                        elseif(!in_array($plan_id,$active_membership_ids))
                        {
                            $membership = wc_memberships_create_user_membership( $args );
                            $message .= '<div class="notice notice-success"><p>Membership created successfully.</p></div>';
                        } 
                    }
                }                
                wp_die(json_encode(array('success'=> true,'message'=> $message)));
            }

        }else{
            wp_die(json_encode(array('success'=> false,'message'=>'Something error please reload page')));
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
WC_Recurly_Create_Missing_Subscription::instance();