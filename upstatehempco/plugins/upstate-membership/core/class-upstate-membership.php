<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'Upstate_Membership' ) ) :

	/**
	 * Main Upstate_Membership Class.
	 *
	 * @package		UPSTATEMEM
	 * @subpackage	Classes/Upstate_Membership
	 * @since		1.0.0
	 * @author		Upstate Hemp co
	 */
	final class Upstate_Membership {

		/**
		 * The real instance
		 *
		 * @access	private
		 * @since	1.0.0
		 * @var		object|Upstate_Membership
		 */
		private static $instance;

		/**
		 * UPSTATEMEM helpers object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Upstate_Membership_Helpers
		 */
		public $helpers;

		/**
		 * UPSTATEMEM settings object.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @var		object|Upstate_Membership_Settings
		 */
		public $settings;

		/**
		 * Throw error on object clone.
		 *
		 * Cloning instances of the class is forbidden.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to clone this class.', 'upstate-membership' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @access	public
		 * @since	1.0.0
		 * @return	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'You are not allowed to unserialize this class.', 'upstate-membership' ), '1.0.0' );
		}

		/**
		 * Main Upstate_Membership Instance.
		 *
		 * Insures that only one instance of Upstate_Membership exists in memory at any one
		 * time. Also prevents needing to define globals all over the place.
		 *
		 * @access		public
		 * @since		1.0.0
		 * @static
		 * @return		object|Upstate_Membership	The one true Upstate_Membership
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Upstate_Membership ) ) {
				self::$instance					= new Upstate_Membership;
				self::$instance->base_hooks();
				self::$instance->includes();
				self::$instance->helpers		= new Upstate_Membership_Helpers();
				self::$instance->settings		= new Upstate_Membership_Settings();

				//Fire the plugin logic
				new Upstate_Membership_Run();

				/**
				 * Fire a custom action to allow dependencies
				 * after the successful plugin setup
				 */
				do_action( 'UPSTATEMEM/plugin_loaded' );
			}

			return self::$instance;
		}

		/**
		 * Include required files.
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function includes() {
			require_once UPSTATEMEM_PLUGIN_DIR . 'core/includes/classes/class-upstate-membership-helpers.php';
			require_once UPSTATEMEM_PLUGIN_DIR . 'core/includes/classes/class-upstate-membership-settings.php';

			require_once UPSTATEMEM_PLUGIN_DIR . 'core/includes/classes/class-upstate-membership-run.php';
		}

		/**
		 * Add base hooks for the core functionality
		 *
		 * @access  private
		 * @since   1.0.0
		 * @return  void
		 */
		private function base_hooks() {
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access  public
		 * @since   1.0.0
		 * @return  void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'upstate-membership', FALSE, dirname( plugin_basename( UPSTATEMEM_PLUGIN_FILE ) ) . '/languages/' );
		}

	}

endif; // End if class_exists check.