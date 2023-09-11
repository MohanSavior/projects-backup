<?php
/**
 * Astra functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Define Constants
 */
define( 'ASTRA_THEME_VERSION', '4.3.1' );
define( 'ASTRA_THEME_SETTINGS', 'astra-settings' );
define( 'ASTRA_THEME_DIR', trailingslashit( get_template_directory() ) );
define( 'ASTRA_THEME_URI', trailingslashit( esc_url( get_template_directory_uri() ) ) );
define( 'ASTRA_PRO_UPGRADE_URL', 'https://wpastra.com/pro/?utm_source=dashboard&utm_medium=free-theme&utm_campaign=upgrade-now' );
define( 'ASTRA_PRO_CUSTOMIZER_UPGRADE_URL', 'https://wpastra.com/pro/?utm_source=customizer&utm_medium=free-theme&utm_campaign=upgrade' );

/**
 * Minimum Version requirement of the Astra Pro addon.
 * This constant will be used to display the notice asking user to update the Astra addon to the version defined below.
 */
define( 'ASTRA_EXT_MIN_VER', '4.1.0' );

/**
 * Setup helper functions of Astra.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-theme-options.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-theme-strings.php';
require_once ASTRA_THEME_DIR . 'inc/core/common-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-icons.php';

/**
 * Update theme
 */
require_once ASTRA_THEME_DIR . 'inc/theme-update/astra-update-functions.php';
require_once ASTRA_THEME_DIR . 'inc/theme-update/class-astra-theme-background-updater.php';

/**
 * Fonts Files
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-font-families.php';
if ( is_admin() ) {
	require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts-data.php';
}

require_once ASTRA_THEME_DIR . 'inc/lib/webfont/class-astra-webfont-loader.php';
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-fonts.php';

require_once ASTRA_THEME_DIR . 'inc/dynamic-css/custom-menu-old-header.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/container-layouts.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/astra-icons.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-walker-page.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-enqueue-scripts.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-gutenberg-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-wp-editor-css.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/block-editor-compatibility.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/inline-on-mobile.php';
require_once ASTRA_THEME_DIR . 'inc/dynamic-css/content-background.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-dynamic-css.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-global-palette.php';

/**
 * Custom template tags for this theme.
 */
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-attr.php';
require_once ASTRA_THEME_DIR . 'inc/template-tags.php';

require_once ASTRA_THEME_DIR . 'inc/widgets.php';
require_once ASTRA_THEME_DIR . 'inc/core/theme-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/admin-functions.php';
require_once ASTRA_THEME_DIR . 'inc/core/sidebar-manager.php';

/**
 * Markup Functions
 */
require_once ASTRA_THEME_DIR . 'inc/markup-extras.php';
require_once ASTRA_THEME_DIR . 'inc/extras.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog-config.php';
require_once ASTRA_THEME_DIR . 'inc/blog/blog.php';
require_once ASTRA_THEME_DIR . 'inc/blog/single-blog.php';

/**
 * Markup Files
 */
require_once ASTRA_THEME_DIR . 'inc/template-parts.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-loop.php';
require_once ASTRA_THEME_DIR . 'inc/class-astra-mobile-header.php';

/**
 * Functions and definitions.
 */
require_once ASTRA_THEME_DIR . 'inc/class-astra-after-setup-theme.php';

// Required files.
require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-helper.php';

require_once ASTRA_THEME_DIR . 'inc/schema/class-astra-schema.php';

/* Setup API */
require_once ASTRA_THEME_DIR . 'admin/includes/class-astra-api-init.php';

if ( is_admin() ) {
	/**
	 * Admin Menu Settings
	 */
	require_once ASTRA_THEME_DIR . 'inc/core/class-astra-admin-settings.php';
	require_once ASTRA_THEME_DIR . 'admin/class-astra-admin-loader.php';
	require_once ASTRA_THEME_DIR . 'inc/lib/astra-notices/class-astra-notices.php';
}

/**
 * Metabox additions.
 */
require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-boxes.php';

require_once ASTRA_THEME_DIR . 'inc/metabox/class-astra-meta-box-operations.php';

/**
 * Customizer additions.
 */
require_once ASTRA_THEME_DIR . 'inc/customizer/class-astra-customizer.php';

/**
 * Astra Modules.
 */
require_once ASTRA_THEME_DIR . 'inc/modules/posts-structures/class-astra-post-structures.php';
require_once ASTRA_THEME_DIR . 'inc/modules/related-posts/class-astra-related-posts.php';

/**
 * Compatibility
 */
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gutenberg.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-jetpack.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/woocommerce/class-astra-woocommerce.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/edd/class-astra-edd.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/lifterlms/class-astra-lifterlms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/learndash/class-astra-learndash.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bb-ultimate-addon.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-contact-form-7.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-visual-composer.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-site-origin.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-gravity-forms.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-bne-flyout.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-ubermeu.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-divi-builder.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-amp.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-yoast-seo.php';
require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-starter-content.php';
require_once ASTRA_THEME_DIR . 'inc/addons/transparent-header/class-astra-ext-transparent-header.php';
require_once ASTRA_THEME_DIR . 'inc/addons/breadcrumbs/class-astra-breadcrumbs.php';
require_once ASTRA_THEME_DIR . 'inc/addons/scroll-to-top/class-astra-scroll-to-top.php';
require_once ASTRA_THEME_DIR . 'inc/addons/heading-colors/class-astra-heading-colors.php';
require_once ASTRA_THEME_DIR . 'inc/builder/class-astra-builder-loader.php';

// Elementor Compatibility requires PHP 5.4 for namespaces.
if ( version_compare( PHP_VERSION, '5.4', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-elementor-pro.php';
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-web-stories.php';
}

// Beaver Themer compatibility requires PHP 5.3 for anonymus functions.
if ( version_compare( PHP_VERSION, '5.3', '>=' ) ) {
	require_once ASTRA_THEME_DIR . 'inc/compatibility/class-astra-beaver-themer.php';
}

require_once ASTRA_THEME_DIR . 'inc/core/markup/class-astra-markup.php';

/**
 * Load deprecated functions
 */
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-filters.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-hooks.php';
require_once ASTRA_THEME_DIR . 'inc/core/deprecated/deprecated-functions.php';

// ------------------------------Start WP_CLI_Command----------------------------------
if ( class_exists( 'WP_CLI_Command' ) )
{
	WP_CLI::add_command( 'update_customer_billing_address', 'update_customer_billing_address' ); 
	
}else{
	error_log(print_r('Error WP_CLI_Command', true));
}

function update_customer_billing_address( $args = array(), $assoc_args = array() )
{
	global $wpdb;
    $customers_report_export = $wpdb->prefix . 'customers_report_export';
	$user = isset($assoc_args['u']) && !empty($assoc_args['u']) ? $assoc_args['u'] : 1;
	$customers_report_export_data = $wpdb->get_results("SELECT * FROM $customers_report_export WHERE `status`= 0 LIMIT $user");
	if(!empty($customers_report_export_data)) 
	{
		foreach($customers_report_export_data as $user_data)
		{
			// print_r($user_data);
			$user_id = email_exists($user_data->email);
			if(!$user_id)
			{
				WP_CLI::success( 'User not exists ');
				$username = $user_data->email;
				$password = wp_generate_password();
				$user_id = wp_create_user($username, $password, $username);
				
				if (is_wp_error($user_id)) {
					// Error occurred while creating the user
					WP_CLI::error( 'Error: ' . $user_id->get_error_message());
				} else {
					// User created successfully
					WP_CLI::success('User created with ID : ' . $user_id);
					$name = explode(' ', $user_data->name);
					$billing_first_name = isset($name[0])? $name[0] : '';
					$billing_last_name = isset($name[1]) ? $name[1] :'';
					update_user_meta( $user_id, 'first_name', $billing_first_name);
					update_user_meta( $user_id, 'last_name', $billing_last_name);

					$user = new WP_User($user_id); // Get the WP_User Object instance from user ID
					// $user->set_role('customer');   // Set the WooCommerce "customer" user role
					// Get all WooCommerce emails Objects from WC_Emails Object instance
					// $emails = WC()->mailer()->get_emails();
					// Send WooCommerce "Customer New Account" email notification with the password
					// $emails['WC_Email_Customer_New_Account']->trigger( $user_id, $password, true );
					// =============================Create a user membership =============================
					if ( function_exists( 'wc_memberships' ) ) {
						$user_membership = wc_memberships_create_user_membership(
							array(
								'user_id' => $user_id,
								'plan_id' => 17845
							)
						);

						if (is_wp_error($user_membership)) {
							WP_CLI::warning('Error creating membership: ' . $user_membership->get_error_message());
						} else {
							WP_CLI::success('Membership created successfully.');
						}
					}
					// =============================End Create a user membership =============================
				}
			} else {
				// Email already exists
				WP_CLI::success( 'Email already registered with user ID ' . $user_id);
			}
			if($user_id)
			{
				$name = explode(' ', $user_data->name);
				$billing_first_name = isset($name[0])? $name[0] : '';
				$billing_last_name = isset($name[1]) ? $name[1] :'';
				update_user_meta( $user_id, 'billing_first_name', $billing_first_name);
				update_user_meta( $user_id, 'billing_last_name', $billing_last_name);
		
				update_user_meta( $user_id, 'billing_city', isset($user_data->city) ? $user_data->city : '');
				update_user_meta( $user_id, 'billing_state', isset($user_data->region) ? $user_data->region : '');
				update_user_meta( $user_id, 'billing_postcode', isset($user_data->postal_code) ? $user_data->postal_code : '');
				update_user_meta( $user_id, 'billing_country', isset($user_data->country) ? $user_data->country : '');
				update_user_meta( $user_id, 'billing_email', isset($user_data->email) ? $user_data->email : '');
				WP_CLI::success( 'Customers billing address updated!!!!!!!!!!!!' );


				$update_customers_report_export = $wpdb->update($customers_report_export, array('status'=> 1), array( 'id' => $user_data->id ));
				if($update_customers_report_export)
					WP_CLI::success( 'Customers report export table updated' );
			}
		}
	}
}
// ------------------------------End WP_CLI_Command----------------------------------
function custom_cron_intervals($schedules) {
    $schedules['every_one_minute'] = array(
        'interval' => 60, // 300 seconds = 5 minutes
        'display'  => __('Every one Minute', 'savior-pro'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'custom_cron_intervals');
function schedule_custom_cron_job() {
    if (!wp_next_scheduled('update_customer_billing_address_cron')) {
        wp_schedule_event(time(), 'every_one_minute', 'update_customer_billing_address_cron');
    }
}
add_action('wp', 'schedule_custom_cron_job');
function update_customer_billing_address_function() {
    $output = shell_exec("wp update_customer_billing_address");
	echo "<pre>".$output."</pre>";
}
add_action('update_customer_billing_address_cron', 'update_customer_billing_address_function');