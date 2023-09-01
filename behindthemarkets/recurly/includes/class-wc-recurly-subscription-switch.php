<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Recurly_Subscription_Switch class.
 *
 */

class WC_Recurly_Subscription_Switch {

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
        
		// add_action('save_post_product', [ $this, 'create_or_update_plans' ], 10, 3);
        add_action('add_meta_boxes', [ $this, 'upgrade_downgrade_meta_box']);
        add_action('wp_enqueue_scripts', [ $this, 'upgrade_downgrade_script']);
        add_action('admin_enqueue_scripts', [ $this, 'upgrade_downgrade_script']);
        add_action('wp_ajax_recurly_switch_plan_or_subscription_create', [ $this, 'recurly_switch_plan_or_subscription_create']);

        add_action( 'woocommerce_order_item_meta_end', __CLASS__ . '::recurly_print_switch_link', 10, 3 );
		
		add_filter( 'gform_field_value_product_id',  __CLASS__ . '::product_id_upgrade_downgrade', 10, 3  );
		
		add_filter( 'gform_pre_render_4', __CLASS__ . '::populate_plans_for_upgrade_downgrade' );
		
		add_filter( 'gform_pre_submission_4', __CLASS__ . '::pre_submission_upgrade_downgrade');
		
		add_filter( 'gform_entries_field_value', __CLASS__.'::gform_entries_subscription_link', 10, 4 );

        add_filter( 'woocommerce_new_customer_data', function( $data ) {
            $data['user_login'] = $data['user_email'];        
            return $data;
        } );
    }

    public function upgrade_downgrade_script()
    {
        wp_enqueue_style( 'upgrade_downgrade', plugins_url('../assets/css/recurly-admin.css', __FILE__), array(), '', 'all' );
        wp_enqueue_script('upgrade_downgrade', plugins_url('../assets/js/upgrade_downgrade.js', __FILE__) , array('jquery'), '', true);
        wp_localize_script('upgrade_downgrade', 'updownAjax', array('ajaxurl' => admin_url('admin-ajax.php'),'subscription_link' => admin_url()));
    }

    public function upgrade_downgrade_meta_box()
    {
    
        add_meta_box(
            'upgrade-downgrade',
            __('Upgrade/Downgrade', 'btm'),
            [ $this, 'upgrade_downgrade_meta_box_callback'],
            'shop_subscription',
            'side'
        );
    }

    public function upgrade_downgrade_meta_box_callback($post)
    {
        ?>
        <h4>Select Plan (Upgrade/Downgrade)</h4>
        <?php
        if($post->post_status != 'wc-active'){
            echo "<b style='color:red;'>Subscription status is not active.</b>";
            return;
        }
        $subscription = new WC_Subscription($post->ID);
        $user_id = $subscription->get_user_id();
        $relared_orders_ids_array = $subscription->get_related_orders();
        $order_id = $subscription->get_parent_id();
        $order = wc_get_order($order_id);
		if(empty($order)) return;
        $items = $order->get_items();
        $product_id = null;
        $product_variation_id = null;
        foreach ($items as $item) {
            $product_name = $item->get_name();
            $product_id = $item->get_product_id();
            $product_variation_id = $item->get_variation_id();
        }
        $product = wc_get_product($product_id);
		if(empty($product)) return;
        $product_variations = $product->get_children();
        $_recurly_subscription_id = get_post_meta($order_id, '_recurly_subscription_id', true);
    
        $recurly_sub_nonce = wp_create_nonce( 'recurly_sub_nonce-' . $_recurly_subscription_id );
        $get_id = (isset($_REQUEST['plan_id']) && !empty($_REQUEST['plan_id'])) ? $_REQUEST['plan_id'] :'';
        ?>
        <select name="upgrade_downgrade_<?=$post->ID?>" id="upgrade_downgrade_<?=$post->ID?>" style="width:100%;">
            <?php 
            echo'<option selected disabled>Select Plan</option>';
            foreach($product_variations as $product_variation){
                if( $product_variation != $product_variation_id){
					$recurly_plan_code = get_post_meta($product_variation, '_recurly_plan_code', true);
                    $var_selected = ($product_variation == $get_id) ? 'selected' : '';
                    echo'<option value="'.$recurly_plan_code.'" 
                    data-recurly_sub_id="'.$_recurly_subscription_id.'" 
                    '.$var_selected.'
                    data-recurly_sub_nonce="'.$recurly_sub_nonce.'" 
                    data-user_id="'.$user_id.'"
                    data-order_id="'.$order_id.'"
                    data-subscription_id="'.$post->ID.'"
                    data-product_id="'.$product_variation.'"
                    >'.get_the_title($product_variation).'</option>';
                }else{
                    // echo'<option value="'.$product_variation.'" data-recurly_sub_id="'.$_recurly_subscription_id.'" data-recurly_sub_nonce="'.$recurly_sub_nonce.'" data-user_id="'.$user_id.'">'.get_the_title($product_variation).'</option>';
                }
            }
            ?>
        </select>
        <ul class="order_actions submitbox">
			<li class="wide" style="padding:6px 0;border-bottom:0;">
				<button type="button" id="change-plan" class="button " style="float:right;" value="Change">Change <div class=""></div></button>                
			</li>
		</ul>
        <?php
    }
    public function recurly_switch_plan_or_subscription_create()
    {
        extract($_POST);
        if ( ! wp_verify_nonce( $recurly_sub_nonce, 'recurly_sub_nonce-' . $recurly_sub_id ) ) {			
            wp_die(json_encode(array('type'=>'error','message'=> 'Nonce not matched!!!!')));
        } else {
            $plan_change = json_encode( array('plan_code'=> (string)$plan_code, 'timeframe' => 'now'));
            $response = WC_Recurly_API::request( $plan_change ,'subscriptions/' . $recurly_sub_id .'/change');
            if ( ! empty( $response->error ) ) {
                wp_die(json_encode(array('type'=>'error','message'=> $response->error->message)));
            } else {
                $order_update = false;
                $order         = wc_get_order( $order_id );
                $subscriptions = wcs_get_subscriptions_for_order( $order_id, array( 'order_type' => 'any' ) ); 
                foreach( $subscriptions as $subscription_id => $subscription ){
                    if ($subscription->has_status('active'))
                    {
                        $subscription->update_meta_data( 'recurly_subscriptions_plan_change', 1 );
                        //membership
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
                                    break;
                                }
                            }
                            if(!empty($plan_id))
                            {
                                $user_membership = wc_memberships_get_user_membership( $user_id, $plan_id );
                                if(!empty($user_membership))
                                {
                                    $user_membership->update_status( 'expired' );
                                }
                            }
                        //membership end
                        $subscription->update_status( 'expired' );
                        $subscription->update_meta_data('_recurly_subscription_uuid', '');
                        wc_delete_order_item($subscription_id);  
                        foreach ( $order->get_items() as $item_id => $item ) {
                            wc_delete_order_item($item_id); 
                            $order->calculate_totals();
                            $order_update = true;
                        }
                    }
                }   
                if($order_update){
                    $new_order_sub = $this->Woo_change_subscription($product_id, $user_id, $order_id, $subscription_id, $note = '');
                    if(is_object($new_order_sub) && !empty($new_order_sub)){
                        $sub_id = $new_order_sub->get_id();						
                        wp_die(json_encode(array('type' => 'success','message'=>'Subscription has been changed','url'=> esc_url_raw(site_url('/wp-admin/post.php?post='.$sub_id.'&action=edit') ))));
                    }else{
						wp_die(json_encode(array('type'=>'error','message'=> 'Something went wrong please try again')));
					}
                }else{					
                    wp_die(json_encode(array('type'=>'error','message'=> 'Something went wrong please try again')));
                }
            }
        }
    }

    public function Woo_change_subscription($plan_code, $user_id, $order_id, $subscription_id, $note = '')
    {
        // First make sure all required functions and classes exist
        if (!function_exists('wcs_create_subscription') || !class_exists('WC_Subscriptions_Product')) {
            return false;
        }    
        $order = wc_get_order($order_id);
        if (is_wp_error($order)) {
            return false;
        }    
        $user = get_user_by('ID', $user_id);
        
        $product = wc_get_product($plan_code);    
        $order->add_product($product, 1);
        $order->calculate_totals();
        $order->save();
    
        $sub = wcs_create_subscription(array(
            'order_id' => $order->get_id(),
            'status' => 'pending', // Status should be initially set to pending to match how normal checkout process goes
            'billing_period' => WC_Subscriptions_Product::get_period($product),
            'billing_interval' => WC_Subscriptions_Product::get_interval($product),
        ));
   
        if (is_wp_error($sub)) {
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
        $note = !empty($note) ? $note : __('Programmatically added subscription.');
        $order->update_status('completed', $note, true);
        // Also update subscription status to active from pending (and add note)
        $sub->update_status('active', $note, true);
		$uuid = get_post_meta($order->get_id(), '_recurly_subscription_uuid', true);
        $sub->update_meta_data('_recurly_subscription_uuid', $uuid);
        //membership
        if ( function_exists( 'wc_memberships' ) ) {
            $membership_plan_args = array(
                'post_type' 	=> 'wc_membership_plan',
                'post_status'	=> 'publish',		
                'meta_query' => array(
                    'relation' => 'OR',
                    array(
                        'key'     => '_product_ids',
                        'value'   => $plan_code,
                        'compare' => 'LIKE'
                    ),
                ),
                'fields' => 'ids',
                'numberposts' => -1
            );
            $membership_active_plans = get_posts($membership_plan_args);
            if(!empty($membership_active_plans))
            {
                $args = array(
                    'plan_id'       => $membership_active_plans[0],
                    'user_id'       => $user_id
                );
                $membership = wc_memberships_create_user_membership( $args );
            }
        }
        return $sub;
    }

    public static function recurly_print_switch_link( $item_id, $item, $subscription )
    {
		if(!is_page(1289) && is_admin())
			return;		
		
        $subscription_data = $item->get_data();
        $order_id = $subscription->get_parent_id();
        $product_id = $subscription_data['product_id'];
        $variation_id = $subscription_data['variation_id'];
        $_recurly_subscription_id = get_post_meta($order_id, '_recurly_subscription_id', true);

        $product = wc_get_product($product_id);
		if(empty($product)){ echo "No plans available."; return; }
        $product_variations = $product->get_children();
    
		$variation_ids = array();

        $is_my_product = false;
        $product = wc_get_product( 104623 );
        $specific_products = $product->get_children();
        if ( sizeof( $subscription_items = $subscription->get_items() ) > 0 ) {
            foreach ( $subscription_items as $item_id => $item ) {
                $product = $item->get_product();
                if ( in_array( $product->get_id(), $specific_products ) ) {
                    $is_my_product = true;
                    break;
                }
            }
        }
        if ( $is_my_product ) 
        { 
            echo "<style> .wcs-switch-link.button{display:none !important;} </style>";
            return;
        }

        ?>
		<div id="request_up_down" class="sdfsdf" style="display:none;">
        	<span class="close-form"><i aria-hidden="true" class="far fa-window-close"></i></span>	
            <div class="up-down-plans" style="height: 250px;">		
                <?php $i = 1;
                $p_var = array_diff($product_variations, array($variation_id));
                foreach($p_var as $product_variation){
    //                 if( $product_variation != $variation_id){
                        $recurly_plan_code = get_post_meta($product_variation, '_recurly_plan_code', true);
                        $variation_ids[] = $product_variation;
                        
                        $plan_change = json_encode( array('plan_code'=> $recurly_plan_code, 'timeframe' => 'now'));
                        $response_pre = WC_Recurly_API::request( $plan_change ,'subscriptions/' . $_recurly_subscription_id .'/change/preview');
                        if ( ! empty( $response_pre->error ) ) {
                            return;
                            wp_die(json_encode(array('type'=>'error','message'=> $response_pre->error->message)));
                        } else {
                            $balance = !empty($response_pre->invoice_collection->charge_invoice->balance) ? $response_pre->invoice_collection->charge_invoice->balance : 0;
                        }
                        $up_dw = ($balance > 0) ? 'Upgrade' : 'Downgrade';
                        $balance = $balance < 1 ? 0 : $balance;
                        echo '<p>'.$i.'. '.get_the_title($product_variation). ' ('.$up_dw .' : $'. $balance . ').</p>';
                        $i++;
    //                 }
                }
            echo'</div>';
		$active_subscription = $subscription_data['name'];
		$variation_ids = implode('|',$variation_ids);
		echo do_shortcode('[gravityform id="4" title="false" description="false" ajax="true" field_values="active_subscription='.$active_subscription.'&product_id='.$variation_ids.'&subscription_id='.$subscription->get_id().'" ]'); ?>
        </div>
        <?php
    }
	
	public static function populate_plans_for_upgrade_downgrade( $form ) { 
		?>
		<script type="text/javascript">
			jQuery(document).ready(function(){
				/* apply only to a textarea with a class of gf_readonly */
				jQuery("#input_4_4").attr("readonly","readonly");
			});
		</script>
		<?php
		return $form;
	}
	
	public static function pre_submission_upgrade_downgrade(  $form  )
	{
		if(!is_admin()){
			$product_variation = wc_get_product(rgpost( 'input_12' ));
			$p_data = $product_variation->get_data();
			$url = esc_url( admin_url('post.php?post='.rgpost( 'input_11' ).'&action=edit&plan_id='.rgpost( 'input_12' )) );
			$_POST['input_7'] =  $url;
			$_POST['input_12'] =  $p_data['id'];
		}
		return $form;
	}
	
	public static function product_id_upgrade_downgrade( $value, $field, $name )
	{
		if(is_admin()){ return;}
			$values = explode('|', $value);
			if ( $field->id == 12 && !is_admin() && !empty($value)) {	
				$choices = array();
				foreach ( $values as $post ) {
					$product_variation = wc_get_product($post); 
					$p_data = $product_variation->get_data();
					$choices[] = array( 'text' => $p_data['name'], 'value' => $p_data['id'] );
				}
				$field->choices = $choices; 
			}
			return $field;
		
	}

	public static function gform_entries_subscription_link( $value, $form_id, $field_id, $entry ) {
		
		if ( $form_id == 4 && $field_id == 7 ) {
			$value = sprintf( '<a href="%s">%s</a>', esc_url( $value ), 'Upgrade/Downgrade' );
		}

		return $value;
	}

	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
WC_Recurly_Subscription_Switch::instance();