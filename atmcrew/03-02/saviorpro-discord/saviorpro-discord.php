<?php
/**
 * Plugin Name: Savior Pro Connect to Discord
 * Plugin URI:  https://savior.im
 * Description: Connect your site to your discord server, enable your members to be part of your community.
 * Version: 1.0.0
 * Author: Savior Marketing Pvt. Ltd.
 * Author URI: https://savior.im
 * Text Domain: saviorpro-discord
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// create plugin version constant.
define( 'ATM_SAVIORPRO_VERSION', '1.0.0' );

// create plugin url constant.
define( 'ATM_SAVIORPRO_DISCORD_URL', plugin_dir_url( __FILE__ ) );

// create plugin path constant.
define( 'ATM_SAVIORPRO_DISCORD_PATH', plugin_dir_path( __FILE__ ) );

// discord API url.
define( 'ATM_DISCORD_API_URL', 'https://discord.com/api/v10/' );

// discord Bot Permissions.
define( 'ATM_DISCORD_BOT_PERMISSIONS', 8 );

// discord api call scopes.
define( 'ATM_DISCORD_OAUTH_SCOPES', 'identify email connections guilds guilds.join gdm.join rpc rpc.notifications.read rpc.voice.read rpc.voice.write rpc.activities.write bot webhook.incoming applications.builds.upload applications.builds.read applications.commands applications.store.update applications.entitlements activities.read activities.write relationships.read' );

// define group name for action scheduler actions.
define( 'ATM_DISCORD_AS_GROUP_NAME', 'atm-saviorpro-discord' );

// define interval to keep checking and send membership expiration warning DM.
define( 'ATM_SAVIORPRO_DISOCRD_EXPIRATION_WARNING_CRON', 5 );

// Follwing response codes not cosider for re-try API calls.
define( 'ATM_SAVIORPRO_DISCORD_DONOT_RETRY_THESE_API_CODES', array( 0, 10003, 50033, 10004, 50025, 10013, 10011 ) );

// following http response codes should not get re-try. except 429 !
define( 'ATM_SAVIORPRO_DISCORD_DONOT_RETRY_HTTP_CODES', array( 400, 401, 403, 404, 405, 502 ) );
/**
 * Class to connect discord app
 */
class Atm_SaviorPro_Discord {
	function __construct() {
		// Add internal classes
		require_once ATM_SAVIORPRO_DISCORD_PATH . 'libraries/action-scheduler/action-scheduler.php';
		require_once ATM_SAVIORPRO_DISCORD_PATH . 'includes/functions.php';
		require_once ATM_SAVIORPRO_DISCORD_PATH . 'includes/classes/class-saviorpro-discord-admin-setting.php';
		require_once ATM_SAVIORPRO_DISCORD_PATH . 'includes/classes/class-discord-api.php';
		require_once ATM_SAVIORPRO_DISCORD_PATH . 'includes/classes/class-discord-logs.php';

		// initiate cron event
		register_activation_hook( __FILE__, array( $this, 'atm_saviorpro_discord_set_up_plugin' ) );
		add_action('admin_init', array( $this, 'atm_saviorpro_discord_check_plugin_state' ) );
	}

	/**
	 * Description: set up the plugin upon activation.
	 *
	 * @param None
	 * @return None
	 */

	public function atm_saviorpro_discord_set_up_plugin() {

		/**    
		 * Check if WooCommerce is activated    
		 */   
		if( ! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) ){ 
			$this->atm_saviorpro_discord_deactivate_plugin();
			wp_die( 'Could not be activated. ' . $this->atm_saviorpro_discord_admin_notices() );
		}
		if( ! in_array('woocommerce-memberships/woocommerce-memberships.php', apply_filters('active_plugins', get_option('active_plugins'))) ){ 
			$this->atm_saviorpro_discord_deactivate_plugin();
			wp_die( 'Could not be activated. ' . $this->atm_saviorpro_discord_admin_notices() );
		}
		$this->set_redirect_url_on_saviorpro_activation();
		$this->set_default_setting_values();
		update_option( 'atm_saviorpro_discord_uuid_file_name', wp_generate_uuid4() );
		// wp_schedule_event( time(), 'hourly', 'atm_pmrpo_discord_schedule_expiration_warnings' );
	}

	/**
     * Writing the admin notice
     */
	protected function atm_saviorpro_discord_admin_notices() {
		return sprintf(
			'%1$s requires <strong>WooCommerce</strong> and <strong>Membership</strong> plugins are installed and actived. You can download WooCommerce latest version %2$s OR go back to %3$s.',
			'<strong>ATM Crew Discord </strong>',
			'<strong><a href="https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip">from here</a></strong>',
			'<strong><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">plugins page</a></strong>'
		);
	}

	/**
     * Amin load check woocommerce and membership plugins are activated
     */
	public function atm_saviorpro_discord_check_plugin_state() {
		if ( ! is_plugin_active('woocommerce/woocommerce.php') || ! is_plugin_active('woocommerce-memberships/woocommerce-memberships.php') ){
			add_action( 'admin_notices', function(){
				?>
					<div class="error alert-danger notice is-dismissible">
						<p>Sorry, but <strong>ATM Crew Discord</strong> plugin requires <strong>WooCommerce</strong> and <strong>Membership</strong> in order to work.
							So please ensure that WooCommerce is both installed and activated.
						</p>
					</div>
				<?php
			} );
			$this->atm_saviorpro_discord_deactivate_plugin();
		}
	}
    /**
     * Function to deactivate the plugin
     */
    protected function atm_saviorpro_discord_deactivate_plugin() {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        deactivate_plugins( plugin_basename( __FILE__ ) );
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
	/**
	 * To to save redirect url
	 *
	 * @param None
	 * @return None
	 */
	public function set_redirect_url_on_saviorpro_activation() {
		$atm_pre_saved_url         = get_option( 'atm_saviorpro_discord_redirect_url' );
		$atm_saviorpro_profile_page_id = get_option( 'saviorpro_member_profile_edit_page_id' );
		if ( $atm_saviorpro_profile_page_id && empty( $atm_pre_saved_url ) ) {
			$atm_saviorpro_discord_redirect_url = get_formated_discord_redirect_url( get_permalink( $atm_saviorpro_profile_page_id ) );
			update_option( 'atm_saviorpro_discord_redirect_url', $atm_saviorpro_discord_redirect_url );
		}
	}
	/**
	 * Set default settings on activation
	 */
	public function set_default_setting_values() {
		update_option( 'atm_saviorpro_discord_payment_failed', true );
		update_option( 'atm_saviorpro_discord_log_api_response', false );
		update_option( 'atm_saviorpro_retry_failed_api', true );
		update_option( 'atm_saviorpro_discord_job_queue_concurrency', 1 );
		update_option( 'atm_saviorpro_member_kick_out', 0 );
		update_option( 'atm_saviorpro_discord_btn_color', '#5865f2' );
		update_option( 'atm_saviorpro_btn_disconnect_color', '#ff0000' );
		update_option( 'atm_saviorpro_discord_loggedout_btn_text', 'Connect To Discord' );
		update_option( 'atm_saviorpro_discord_loggedin_btn_text', 'Connect To Discord' );
		update_option( 'atm_saviorpro_disconnect_btn_text', 'Disconnect From Discord' );
		update_option( 'atm_saviorpro_discord_job_queue_batch_size', 7 );
		update_option( 'atm_saviorpro_allow_none_member', 'yes' );
		update_option( 'atm_saviorpro_retry_api_count', '5' );
		update_option( 'atm_saviorpro_discord_send_welcome_dm', true );
		update_option( 'atm_saviorpro_discord_welcome_message', 'Hi [MEMBER_USERNAME] ([MEMBER_EMAIL]), Welcome, Your membership [MEMBERSHIP_LEVEL] is starting from [MEMBERSHIP_STARTDATE] at [SITE_URL] the last date of your membership is [MEMBERSHIP_ENDDATE] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'atm_saviorpro_discord_send_expiration_warning_dm', true );
		update_option( 'atm_saviorpro_discord_expiration_warning_message', 'Hi [MEMBER_USERNAME] ([MEMBER_EMAIL]), Your membership [MEMBERSHIP_LEVEL] is expiring at [MEMBERSHIP_ENDDATE] at [SITE_URL] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'atm_saviorpro_discord_send_membership_expired_dm', true );
		update_option( 'atm_saviorpro_discord_expiration_expired_message', 'Hi [MEMBER_USERNAME] ([MEMBER_EMAIL]), Your membership [MEMBERSHIP_LEVEL] is expired at [MEMBERSHIP_ENDDATE] at [SITE_URL] Thanks, Kind Regards, [BLOG_NAME]' );
		update_option( 'atm_saviorpro_discord_send_membership_cancel_dm', true );
		update_option( 'atm_saviorpro_discord_cancel_message', 'Hi [MEMBER_USERNAME], ([MEMBER_EMAIL]), Your membership [MEMBERSHIP_LEVEL] at [BLOG_NAME] is cancelled, Regards, [SITE_URL]' );
		update_option( 'atm_saviorpro_discord_embed_messaging_feature', false );
	}

}
new Atm_SaviorPro_Discord();
