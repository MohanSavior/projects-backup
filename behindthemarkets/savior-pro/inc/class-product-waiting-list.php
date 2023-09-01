<?php

/*
 * Class for product waiting list
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
class ProductWaitingList
{
    private static $instance = NULL;
	/**
     * Class constructor
     */
	public function __construct() {
        add_action('init', [ $this, 'add_option_product_waiting'] );
        add_action('wp_footer', [ $this, 'add_enqueue_scripts'] );
        add_action( "wp_ajax_product_waiting_list", [ $this, "product_waiting_list_wp_ajax"] );
        add_action( "wp_ajax_nopriv_product_waiting_list", [ $this, "product_waiting_list_wp_ajax"] );
    }

    public function add_option_product_waiting()
    {
        if( function_exists('acf_add_options_page') ) {
            acf_add_options_sub_page(array(
                'page_title'  		=> __('Product waiting list', 'savior-pro'),
                'menu_title'  		=> __('Product waiting', 'savior-pro'),
                'parent_slug'		=> 'woocommerce',
                'menu_slug' 		=> 'product-waiting-list',
                'capability' 		=> 'manage_options',
                'updated_message' 	=> __( "Product waiting list updated", 'savior-pro' ),
            ));
        }
    }

    public function add_enqueue_scripts()
    {
        if(get_field('enabled_waiting','option')){//get_field('test_mode','option') && is_page(2444)
// 			global $woocommerce;
// 			$woocommerce->cart->empty_cart();
            ?>
            <script>
                (function($){
                    let productNonce = '<?php echo wp_create_nonce('product_waiting_list');?>'
                    // $(window).load(function(){
                        $('.subs-order-btn').each(function(){
// 							if( $(this).find('a').attr('href') !== '/cart/?add-to-cart=1294'){
								$(this).find('a').attr( 'data-url', $(this).find('a').attr('href') );
								$(this).find('a').attr( 'href','javascript:void(0)' );            
// 							}
                        });
                        $('body').on('click', '.subs-order-btn a', function(e){
							let spinnerEl = $(this).find('span.elementor-button-content-wrapper'); 
							let productID = parseInt( $(this).data('url').replace(/[^0-9]/g,'') );
// 							if( productID == 1294){
// 								return;
// 							}
                            e.preventDefault();

                            if(productID){
                                let spinner = '<i class="fas fa-sync fa-spin"></i>';
                                $.ajax({
                                    url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                                    method: 'post',
                                    data: {
                                        'action':'product_waiting_list',
                                        'productID': productID,
                                        'productNonce': productNonce
                                    },
                                    beforeSend: function() {
                                        spinnerEl.append(spinner);
                                        console.log(spinnerEl);
                                    },
                                    success:function(data) {
                                        $('body').find('span.elementor-button-content-wrapper i').remove();
                                        if(data.success){
                                            elementorProFrontend.modules.popup.showPopup( { id: 166452 } );
                                            setTimeout(()=>{
                                                $('#field_7_5').html(data.data.productData);
                                                $('#input_7_6').val(data.data.product_title);
                                                $('#input_7_7').val(data.data.product_id);
                                                $('#input_7_8').val(data.data.product_price);
                                            },100);
                                        }
                                    },
                                    error: function(errorThrown){
                                        console.log(errorThrown);
                                    }
                                });
                            }
                        });
                    // });
                })(jQuery)
            </script>
            <?php
        }
    }

    public function product_waiting_list_wp_ajax()
    {
        if ( !wp_verify_nonce($_POST['productNonce'], 'product_waiting_list') ){ 
            die('Permission Denied.'); 
        }
        if(!empty($_POST['productID']))
        {
            $variation_ID = $_POST['productID'];
            // $_product = wc_get_product( $_POST['productID'] );
             $product_variation = new WC_Product_Variation( $variation_ID );
            $get_regular_price = $product_variation->get_regular_price();
            $get_sale_price = $product_variation->get_sale_price();
            $get_price = $product_variation->get_price();
            $price='';
            if($get_regular_price){
                $price = $get_regular_price;
            }elseif($get_sale_price){
                $price = $get_sale_price;
            }else{
                $price = $get_price;
            }
            $get_product_data = $product_variation->get_data();
            $get_parent_data = $product_variation->get_parent_data();
            $membership_type = $get_product_data['attributes']['membership-type'];
            $product_title = $get_parent_data['title'];
            $image_id  = $product_variation->get_image_id();
            $image_url = wp_get_attachment_image_url( $image_id, 'full' );        
            $productHtml = '<div class="product-detail-sec">
                                <h3>You applied for</h3>
                                <div class="product-main-details-cls">
                                    <div class="product-image">
                                        <img src="'.$image_url.'">
                                    </div>
                                    <div class="product-title">
                                        <h4>' . $product_title .'</h4>
                                        <h5>'. $membership_type .'</h5>
                                    </div>
                                    <div class="product-price">
                                        <h4>$'.$price.'</h4>
                                    </div>
                                </div>
                            </div>';
            wp_send_json_success(
                array(
                    'productData'   =>$productHtml,
                    'product_id'    =>$variation_ID, 
                    'product_title' =>$product_title.'-'.$membership_type,
                    'product_price' =>$price
                )
            );
        }else{
            wp_send_json_error();
        }
    }

	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
ProductWaitingList::instance();



