<?php
/**
 * Upstate Membership
 *
 * @package       UPSTATEMEM
 * @author        Upstate Hemp co
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Upstate Membership
 * Plugin URI:    https://upstatehempco.com/
 * Description:   Add restriction on new york users
 * Version:       1.0.0
 * Author:        Upstate Hemp co
 * Author URI:    upstatehempco.com
 * Text Domain:   upstate-membership
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Upstate Membership. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'UPSTATEMEM_NAME',			'Upstate Membership' );

// Plugin version
define( 'UPSTATEMEM_VERSION',		'1.0.0' );

// Plugin Root File
define( 'UPSTATEMEM_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'UPSTATEMEM_PLUGIN_BASE',	plugin_basename( UPSTATEMEM_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'UPSTATEMEM_PLUGIN_DIR',	plugin_dir_path( UPSTATEMEM_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'UPSTATEMEM_PLUGIN_URL',	plugin_dir_url( UPSTATEMEM_PLUGIN_FILE ) );

/**
 * Load the main class for the core functionality
 */
require_once UPSTATEMEM_PLUGIN_DIR . 'core/class-upstate-membership.php';

/**
 * The main function to load the only instance
 * of our master class.
 *
 * @author  Upstate Hemp co
 * @since   1.0.0
 * @return  object|Upstate_Membership
 */
function UPSTATEMEM() {
	return Upstate_Membership::instance();
}

UPSTATEMEM();

function custom_page_restrictions()
{
	$restricted_page_id = 17827;
	$redirection_page_id = 17943;
	$membership_plan_id = 17845;
	if (is_page($restricted_page_id) && is_user_logged_in() && function_exists('wc_memberships')) {
		$user_id = get_current_user_id();
		$redirect_user = true;
		$user_memberships = wc_memberships_get_user_memberships($user_id);
		foreach ($user_memberships as $membership) {
			if ($membership->get_plan_id() === $membership_plan_id && $membership->is_active()) {
				$redirect_user = false;
			}
		}
		if ($redirect_user) {
			wp_safe_redirect(get_permalink($redirection_page_id));
			die;
		}
	} elseif (is_page($restricted_page_id)) {
		wp_safe_redirect(get_permalink($redirection_page_id));
		die;
	}
}
add_action('template_redirect', 'custom_page_restrictions');

// Active Membership on Order Status Change
// Hook into WooCommerce order status changes
add_action('woocommerce_order_status_completed', 'activate_membership_on_order_completion');

function activate_membership_on_order_completion($order_id)
{
	// Get the order object
	$order = wc_get_order($order_id);

	// Check if the order is valid and its status changed to completed
	if ($order && $order->get_status() === 'completed') {
		// Get the current user's ID
		// $user_id = get_current_user_id();
		$user_id = $order->get_user_id();
		// Get the user's active memberships
		$active_memberships = wc_memberships_get_user_memberships($user_id, array('status' => 'active'));
		$membership_plan_id = 17845;
		if (!empty($active_memberships)) {
			foreach ($active_memberships as $membership) {
				// Replace 'your_membership_plan_id' with the actual membership plan ID

				if ($membership->plan_id == $membership_plan_id) {
					// Update the membership status to active if the plan matches
					wc_memberships_update_membership_status($membership->id, 'active');
				}
			}
		} else {

			// Create a user membership
			$user_membership = wc_memberships_create_user_membership(
				array(
					'user_id' => $user_id,
					'plan_id' => $membership_plan_id,
					'status' => 'active',
					// You can set the desired status, such as 'active', 'pending', etc.
					'start_date' => current_time('mysql'),
				)
			);

			if (is_wp_error($user_membership)) {
				echo 'Error creating membership: ' . $user_membership->get_error_message();
			} else {
				echo 'Membership created successfully.';
			}
		}
	}
}

// Validating New York Users to not purchase the membership for the restricted page
add_action('woocommerce_after_checkout_validation', 'custom_checkout_process', 999, 2);

function custom_checkout_process($data, $errors)
{
	// Get the submitted billing state value
	$billing_state = isset($_POST['billing_state']) ? sanitize_text_field($_POST['billing_state']) : '';
	$shipping_state = isset($_POST['shipping_state']) ? sanitize_text_field($_POST['shipping_state']) : '';

	// Perform validation: Check if billing state is "New York"
	if ('NY' == $billing_state || 'NY' == $shipping_state) {
		$errors->add('validation', 'Registrations are not open for your location right now! ');
	}
}

// 
add_filter('woocommerce_package_rates', 'exclude_product_from_goshippo_shipping', 10, 2);

function exclude_product_from_goshippo_shipping($rates, $package) {
    $excluded_product_ids = array(17848); // Replace with the actual product ID(s) to exclude    
    foreach ($package['contents'] as $item_id => $item_data) {
        if (in_array($item_data['product_id'], $excluded_product_ids)) {
            // Unset the GoShippo shipping method from the rates array
            unset($rates['goshippo']);
            break; // No need to continue looping if one excluded product is found
        }
    }
    
    return $rates;
}