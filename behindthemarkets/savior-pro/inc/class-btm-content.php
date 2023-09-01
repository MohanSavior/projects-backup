<?php
if ( ! defined( 'ABSPATH' ) && ! is_admin()) {
	exit;
}
class BTM_Content {

	private static $instance = NULL;
	/**
     * Class constructor
     */
	public function __construct() {

		// add_action('save_post_product', [ $this, 'create_or_update_plans' ], 10, 3);
		add_action( 'admin_menu', [ $this, 'btm_content_cpts'] );
		// 		add_action( 'admin_init', [ $this, 'my_settings_init'] );
		add_action( 'admin_menu', [ $this, 'register_survey'] );
		
		add_action( 'admin_init', function () {
			global $pagenow;
			# Check current admin page.
			if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] == 'product_redirect' ) {
				wp_redirect( admin_url( '/edit.php?post_type=product' ) );
				exit;
			}

		});
		
	}
	public function btm_content_cpts() {
		add_menu_page(
			'BTM Content',
			'BTM Content',
			'manage_options',
			'edit.php?post_type=btm_products',
			'',
			'dashicons-calendar',
			5 // Position
		);

		$cpts = array( 'btm_products','latest_articles', 'stock_portfolio','welcome_video','expect_from_service','bonus_reports' );

		/* Get CPT Object */
		foreach( $cpts as $cpt ){
			$cpt_obj = get_post_type_object( $cpt );

			add_submenu_page(
				'edit.php?post_type=btm_products',                      // parent slug
				$cpt_obj->labels->name,            // page title
				$cpt_obj->labels->menu_name,       // menu title
				'manage_options',         // capability
				'edit.php?post_type=' . $cpt       // menu slug
			);
		}
	}   

	/**
 ** Add Menu In Admin
 **/


	public function register_survey() {
		global $current_user; 
		wp_get_current_user();
		$username = $current_user->user_login;

		$user = new WP_User( get_current_user_id() );		
		if ( in_array( 'customerserviceagent', (array) $user->roles ) || in_array( 'customerserviceeditor', (array) $user->roles )  ) {

			add_menu_page(
				'Subscription',
				'Subscription',
				'manage_options',
				'edit.php?post_type=shop_subscription',
				'',
				'dashicons-money-alt',
				4 // Position
			);
			add_submenu_page(
				'edit.php?post_type=shop_subscription', 
				__('Create Subscription', 'woocommerce'), 
				__('Create Subscription', 'woocommerce'), 
				'manage_woocommerce', 
				'admin.php?page=missing_subscription'
			);
			add_menu_page(
				'Memberships',
				'Memberships',
				'manage_options',
				'edit.php?post_type=wc_user_membership',
				'',
				'dashicons-admin-post',
				5 // Position
			);
			add_menu_page(
				'Customers',
				'Customers',
				'manage_options',
				'admin.php?page=wc-admin&path=%2Fcustomers',
				'',
				'dashicons-admin-users',
				6 // Position
			);
			add_menu_page(
				'Requests',
				'Requests',
				'manage_options',
				'admin.php?page=gf_entries&id=4',
				'',
				'dashicons-controls-repeat',
				7 // Position
			);
			add_menu_page(
				'Plans',
				'Plans',
				'manage_options',
				'edit.php?post_type=product_redirect',
				'',
				'dashicons-database',
				8 // Position
			);
			add_menu_page(
				'Analytics',
				'Analytics',
				'manage_options',
				'admin.php?page=wc-admin&path=%2Fanalytics%2Foverview',
				'',
				'dashicons-chart-bar',
				9 // Position
			);
			add_menu_page(
				'Reports',
				'Reports',
				'manage_options',
				'admin.php?page=wc-reports',
				'',
				'dashicons-chart-line',
				10 // Position
			);
			add_menu_page(
				'Marketing',
				'Marketing',
				'manage_options',
				'admin.php?page=wc-admin&path=%2Fmarketing',
				'',
				'dashicons-megaphone',
				11 // Position
			);
// 			add_menu_page(
// 				'Woo Settings',
// 				'Woo Settings',
// 				'manage_options',
// 				'admin.php?page=wc-settings',
// 				'',
// 				'dashicons-admin-settings',
// 				12 // Position
// 			);
			add_menu_page(
				'Mailchimp Access',
				'Mailchimp Access',
				'manage_options',
				'admin.php?page=check-member-on-mailchimp',
				'',
				'https://cdn.behindthemarkets.com/2022/02/mailchimp1.png',
				13 // Position
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('Legal pages', 'woocommerce'), 
				__('Legal pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=legal-pages'
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('General Pages', 'woocommerce'), 
				__('General pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=general-pages'
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('Restricted Pages', 'woocommerce'), 
				__('Restricted pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=restricted-pages'
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('Product Pages', 'woocommerce'), 
				__('Product pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=product-pages'
			);
			
			$marketing_menu = array( 'shop_coupon' );
			/* Get CPT Object */
			foreach( $marketing_menu as $marketing ){
				$marketing_obj = get_post_type_object( $marketing );

				add_submenu_page(
					'admin.php?page=wc-admin&path=%2Fmarketing',                      // parent slug
					$marketing_obj->labels->name,            // page title
					$marketing_obj->labels->menu_name,       // menu title
					$marketing_obj->cap->edit_posts,         // capability
					'edit.php?post_type=' . $marketing       // menu slug
				);
			}

			$analytics_menu = array( "Product"=> "products", "Revenue"=> "revenue", "Orders"=> "orders", "Variations"=> "variations", "Categories"=> "categories", "Coupons"=> "coupons", "Taxes"=> "taxes", "Downloads"=> "downloads", "Stock"=> "stock", "Settings"=> "settings" );
			/* Get CPT Object */
			foreach( $analytics_menu as $key=> $analytics ){
// 				$analytics_obj = get_post_type_object( $analytics );

				add_submenu_page(
					'admin.php?page=wc-admin&path=%2Fanalytics%2Foverview',                      // parent slug
					$key,            // page title
					$key,       // menu title
					'manage_options',        // capability
					'admin.php?page=wc-admin&path=/analytics/' . $analytics,       // menu slug
					''
				);
			}
			

		}

		if( in_array('editor', (array) $user->roles ) ) {
			add_menu_page(
				'Contact Form',
				'Contact Form',
				'manage_options',
				'admin.php?page=gf_edit_forms&id=2',
				'',
				'dashicons-email',
				11 // Position
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('Legal pages', 'woocommerce'), 
				__('Legal pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=legal-pages'
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('General Pages', 'woocommerce'), 
				__('General pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=general-pages'
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('Restricted Pages', 'woocommerce'), 
				__('Restricted pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=restricted-pages'
			);
			add_submenu_page(
				'edit.php?post_type=page', 
				__('Product Pages', 'woocommerce'), 
				__('Product pages', 'woocommerce'), 
				'manage_options', 
				'edit.php?post_type=page&page_category=product-pages'
			);
		}
		
		/**/
// 		$user = new WP_User( get_current_user_id() );		
		if ( in_array( 'chargebacksupport', (array) $user->roles ) ) {
			//The user has the "author" role
			add_menu_page(
				'Subscription',
				'Subscription',
				'manage_options',
				'edit.php?post_type=shop_subscription',
				'',
				'dashicons-money-alt',
				4 // Position
			);
			add_menu_page(
				'Memberships',
				'Memberships',
				'manage_options',
				'edit.php?post_type=wc_user_membership',
				'',
				'dashicons-admin-post',
				5 // Position
			);
			add_menu_page(
				'Customers',
				'Customers',
				'manage_options',
				'admin.php?page=wc-admin&path=%2Fcustomers',
				'',
				'dashicons-admin-users',
				6 // Position
			);
		}

	
		
	}


	public static function instance() {
		if ( empty(self::$instance) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}
BTM_Content::instance();