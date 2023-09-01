<?php
/**
 * Admin setting
 */
class Atm_SaviorPro_Admin_Setting {
	function __construct() {
		// Add new menu option in the admin menu.
		add_action( 'admin_menu', array( $this, 'atm_saviorpro_discord_add_new_menu' ) );
		// Add script for back end.
		add_action( 'admin_enqueue_scripts', array( $this, 'atm_saviorpro_discord_add_admin_script' ) );

		// Add script for front end.
		add_action( 'admin_enqueue_scripts', array( $this, 'atm_saviorpro_discord_add_script' ) );

		// Add script for front end.
		add_action( 'wp_enqueue_scripts', array( $this, 'atm_saviorpro_discord_add_script' ) );

		// Add new button in saviorpro profile
		add_shortcode( 'discord_connect_button', array( $this, 'atm_saviorpro_discord_add_connect_discord_button' ) );

		add_action( 'saviorpro_show_user_profile', array( $this, 'atm_saviorpro_show_discord_button' ) );

		add_action( 'wp_body_open', array( $this, 'atm_saviorpro_discord_add_inline_css_checkout' ) );

		// change hook call on cancel and change
		add_action( 'saviorpro_after_change_membership_level', array( $this, 'atm_saviorpro_discord_as_schdule_job_saviorpro_cancel' ), 10, 3 );

		// Saviorpro expiry
		add_action( 'saviorpro_membership_post_membership_expiry', array( $this, 'atm_saviorpro_discord_as_schdule_job_saviorpro_expiry' ), 10, 2 );

		add_action( 'admin_post_saviorpro_discord_save_application_details', array( $this, 'atm_saviorpro_discord_save_application_details' ), 10 );

		add_action( 'admin_post_saviorpro_discord_save_role_mapping', array( $this, 'atm_saviorpro_discord_save_role_mapping' ), 10 );

		add_action( 'admin_post_saviorpro_discord_save_advance_settings', array( $this, 'atm_saviorpro_discord_save_advance_settings' ), 10 );

		add_action( 'admin_post_saviorpro_discord_save_appearance_settings', array( $this, 'atm_saviorpro_discord_save_appearance_settings' ), 10 );

		add_action( 'saviorpro_delete_membership_level', array( $this, 'atm_saviorpro_discord_as_schedule_job_saviorpro_level_deleted' ), 10, 1 );

		add_action( 'saviorpro_checkout_after_pricing_fields', array( $this, 'atm_saviorpro_discord_checkout_after_email' ) );

		// add_action( 'woocommerce_after_order_notes', array( $this, 'atm_saviorpro_discord_checkout_after_email' ) );

		add_filter( 'saviorpro_manage_memberslist_custom_column', array( $this, 'atm_saviorpro_discord_saviorpro_extra_cols_body' ), 10, 2 );

		add_filter( 'saviorpro_manage_memberslist_columns', array( $this, 'atm_saviorpro_discord_manage_memberslist_columns' ) );

		add_filter( 'action_scheduler_queue_runner_batch_size', array( $this, 'atm_saviorpro_discord_queue_batch_size' ) );

		add_filter( 'action_scheduler_queue_runner_concurrent_batches', array( $this, 'atm_saviorpro_discord_concurrent_batches' ) );

		add_filter( 'saviorpro_change_level', array( $this, 'atm_saviorpro_discord_handle_cancel_on_next_payment' ), 99, 4 );

		add_filter( 'atm_saviorpro_show_connect_button_on_profile', array( $this, 'atm_saviorpro_discord_show_connect_button_on_profile' ), 10, 1 );
	}
	/**
	 * set action scheuduler concurrent batches number
	 *
	 * @param INT $batch_size
	 * @return INT $batch_size
	 */
	public function atm_saviorpro_discord_concurrent_batches( $batch_size ) {
		if ( atm_saviorpro_discord_get_all_pending_actions() !== false ) {
			return absint( get_option( 'atm_saviorpro_discord_job_queue_concurrency' ) );
		} else {
			return $batch_size;
		}
	}
	/**
	 * set action scheuduler batch size.
	 *
	 * @param INT $concurrent_batches
	 * @return INT $concurrent_batches
	 */
	public function atm_saviorpro_discord_queue_batch_size( $concurrent_batches ) {
		if ( atm_saviorpro_discord_get_all_pending_actions() !== false ) {
			return absint( get_option( 'atm_saviorpro_discord_job_queue_batch_size' ) );
		} else {
			return $concurrent_batches;
		}
	}
	/**
	 * Add button to make connection in between user and discord
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_add_connect_discord_button() {
		ob_start();
		wp_enqueue_style( 'atm_saviorpro_add_discord_style' );
		wp_enqueue_script( 'atm_saviorpro_add_discord_script' );
		$user_id = sanitize_text_field( trim( get_current_user_id() ) );

		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		$allow_none_member              	= sanitize_text_field( trim( get_option( 'atm_saviorpro_allow_none_member' ) ) );
		$default_role                   	= sanitize_text_field( trim( get_option( '_atm_saviorpro_discord_default_role_id' ) ) );
		$atm_saviorpro_discord_role_mapping 	= json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
		$all_roles                      	= unserialize( get_option( 'atm_saviorpro_discord_all_roles' ) );
		$roles_color                    	= unserialize( get_option( 'atm_saviorpro_discord_roles_color' ) );
		$btn_color                      	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_btn_color' ) ) );
		$atm_saviorpro_btn_disconnect_color = sanitize_text_field( trim( get_option( 'atm_saviorpro_btn_disconnect_color' ) ) );
		$loggedout_btn_text             	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_loggedout_btn_text' ) ) );
		$loggedin_btn_text              	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_loggedin_btn_text' ) ) );
		$atm_saviorpro_disconnect_btn_text  = sanitize_text_field( trim( get_option( 'atm_saviorpro_disconnect_btn_text' ) ) );
		if ( $btn_color == '' || empty( $btn_color ) ) {
			$btn_color = '#5865f2';
		}
		if ( $atm_saviorpro_btn_disconnect_color == '' || empty( $atm_saviorpro_btn_disconnect_color ) ) {
			$atm_saviorpro_btn_disconnect_color = '#ff0000';
		}
		if ( $loggedout_btn_text == '' || empty( $loggedout_btn_text ) ) {
			$loggedout_btn_text = 'Login With Discord';
		}
		if ( $loggedin_btn_text == '' || empty( $loggedin_btn_text ) ) {
			$loggedin_btn_text = 'Connect To Discord';
		}
		if ( $atm_saviorpro_disconnect_btn_text == '' || empty( $atm_saviorpro_disconnect_btn_text ) ) {
			$atm_saviorpro_disconnect_btn_text = 'Disconnect From Discord';
		}

		if ( isset( $_GET['level'] ) && $_GET['level'] > 0 ) {
			$curr_level_id = $_GET['level'];
		} else {
			  $curr_level_id = atm_saviorpro_discord_get_current_level_id( $user_id );
		}

		$mapped_role_name = '';
		if ( $curr_level_id && is_array( $all_roles ) ) {
			if ( is_array( $atm_saviorpro_discord_role_mapping ) && array_key_exists( 'saviorpro_level_id_' . $curr_level_id, $atm_saviorpro_discord_role_mapping ) ) {
				$mapped_role_id = $atm_saviorpro_discord_role_mapping[ 'saviorpro_level_id_' . $curr_level_id ];
				if ( array_key_exists( $mapped_role_id, $all_roles ) ) {
					$mapped_role_name = '<span> <i style="background-color:#' . dechex( $roles_color[ $mapped_role_id ] ) . '">' . $all_roles[ $mapped_role_id ] . '</i></span>';
				}
			}
		}
		$default_role_name = '';
		if ( $default_role != 'none' && is_array( $all_roles ) && array_key_exists( $default_role, $all_roles ) ) {
						$default_role_name = '<span> <i style="background-color:#' . dechex( $roles_color[ $default_role ] ) . '">' . $all_roles[ $default_role ] . '</i></span>';
		}
		$saviorpro_connecttodiscord_btn = '';
		if ( Check_saved_settings_status() ) {
			if ( $access_token ) {
				$discord_user_name           = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_username', true ) ) );
				$saviorpro_connecttodiscord_btn .= '<div>';
				// $saviorpro_connecttodiscord_btn .= '<label class="atm-connection-lbl">' . esc_html__( 'Discord connection', 'saviorpro-discord' ) . '</label>';
				$saviorpro_connecttodiscord_btn .= '<style>.saviorpro-btn-disconnect{background-color: ' . $atm_saviorpro_btn_disconnect_color . ';}</style><a style="text-decoration: none;text-align: center;display: flex;width: fit-content;line-height: 24px;" href="#" class="atm-btn saviorpro-btn-disconnect" id="saviorpro-disconnect-discord" data-user-id="' . esc_attr( $user_id ) . '"><svg xmlns="http://www.w3.org/2000/svg" fill="#fff" viewBox="0 0 640 512" style="width: 24px;height: 24px;position: relative;margin-right: 5px;"><path d="M524.531,69.836a1.5,1.5,0,0,0-.764-.7A485.065,485.065,0,0,0,404.081,32.03a1.816,1.816,0,0,0-1.923.91,337.461,337.461,0,0,0-14.9,30.6,447.848,447.848,0,0,0-134.426,0,309.541,309.541,0,0,0-15.135-30.6,1.89,1.89,0,0,0-1.924-.91A483.689,483.689,0,0,0,116.085,69.137a1.712,1.712,0,0,0-.788.676C39.068,183.651,18.186,294.69,28.43,404.354a2.016,2.016,0,0,0,.765,1.375A487.666,487.666,0,0,0,176.02,479.918a1.9,1.9,0,0,0,2.063-.676A348.2,348.2,0,0,0,208.12,430.4a1.86,1.86,0,0,0-1.019-2.588,321.173,321.173,0,0,1-45.868-21.853,1.885,1.885,0,0,1-.185-3.126c3.082-2.309,6.166-4.711,9.109-7.137a1.819,1.819,0,0,1,1.9-.256c96.229,43.917,200.41,43.917,295.5,0a1.812,1.812,0,0,1,1.924.233c2.944,2.426,6.027,4.851,9.132,7.16a1.884,1.884,0,0,1-.162,3.126,301.407,301.407,0,0,1-45.89,21.83,1.875,1.875,0,0,0-1,2.611,391.055,391.055,0,0,0,30.014,48.815,1.864,1.864,0,0,0,2.063.7A486.048,486.048,0,0,0,610.7,405.729a1.882,1.882,0,0,0,.765-1.352C623.729,277.594,590.933,167.465,524.531,69.836ZM222.491,337.58c-28.972,0-52.844-26.587-52.844-59.239S193.056,219.1,222.491,219.1c29.665,0,53.306,26.82,52.843,59.239C275.334,310.993,251.924,337.58,222.491,337.58Zm195.38,0c-28.971,0-52.843-26.587-52.843-59.239S388.437,219.1,417.871,219.1c29.667,0,53.307,26.82,52.844,59.239C470.715,310.993,447.538,337.58,417.871,337.58Z"></path></svg>' . esc_html( $atm_saviorpro_disconnect_btn_text ) . '</a>';
				// $saviorpro_connecttodiscord_btn .= '<span class="atm-spinner"></span><p class="atm_assigned_role">';
				$saviorpro_connecttodiscord_btn .= '<span class="atm-spinner"></span>';
				if ( $mapped_role_name || $default_role_name ) {
					// $saviorpro_connecttodiscord_btn .= esc_html__( 'Following Roles was assigned to you in Discord: ', 'saviorpro-discord' );
				}
				if ( $mapped_role_name ) {
					// $saviorpro_connecttodiscord_btn .= atm_saviorpro_discord_allowed_html( $mapped_role_name );
				}
				if ( $default_role_name && $mapped_role_name ) {
					// $saviorpro_connecttodiscord_btn .= ' , ';
				}
				if ( $default_role_name ) {
					// $saviorpro_connecttodiscord_btn .= atm_saviorpro_discord_allowed_html( $default_role_name );
				}
				$saviorpro_connecttodiscord_btn .= '</div>';

				// $saviorpro_connecttodiscord_btn .= '</p><p class="atm_assigned_role">';
				// $saviorpro_connecttodiscord_btn .= esc_html__( 'Connected account: ' . $discord_user_name, 'memberpress-discord-add-on' );
				// $saviorpro_connecttodiscord_btn .= '</p></div>';
			} elseif ( $allow_none_member == 'yes' ) {
				$btn_text = $user_id ? $loggedin_btn_text : $loggedout_btn_text;

				$current_url                 = atm_saviorpro_discord_get_current_screen_url();
				// $saviorpro_connecttodiscord_btn .= '<style>.saviorpro-btn-connect{background-color: ' . $btn_color . ';}</style><div><label class="atm-connection-lbl">' . esc_html__( 'Discord connection', 'saviorpro-discord' ) . '</label>';
				$saviorpro_connecttodiscord_btn .= '<style>.saviorpro-btn-connect{background-color: ' . $btn_color . ';}</style><div>';
				$saviorpro_connecttodiscord_btn .= '<a style="text-decoration: none;text-align: center;display: flex;width: fit-content;line-height: 24px;" href="?action=discord-login&url=' . $current_url . '" class="saviorpro-btn-connect atm-btn" ><svg xmlns="http://www.w3.org/2000/svg" fill="#fff" viewBox="0 0 640 512" style="width: 24px;height: 24px;position: relative;margin-right: 5px;"><path d="M524.531,69.836a1.5,1.5,0,0,0-.764-.7A485.065,485.065,0,0,0,404.081,32.03a1.816,1.816,0,0,0-1.923.91,337.461,337.461,0,0,0-14.9,30.6,447.848,447.848,0,0,0-134.426,0,309.541,309.541,0,0,0-15.135-30.6,1.89,1.89,0,0,0-1.924-.91A483.689,483.689,0,0,0,116.085,69.137a1.712,1.712,0,0,0-.788.676C39.068,183.651,18.186,294.69,28.43,404.354a2.016,2.016,0,0,0,.765,1.375A487.666,487.666,0,0,0,176.02,479.918a1.9,1.9,0,0,0,2.063-.676A348.2,348.2,0,0,0,208.12,430.4a1.86,1.86,0,0,0-1.019-2.588,321.173,321.173,0,0,1-45.868-21.853,1.885,1.885,0,0,1-.185-3.126c3.082-2.309,6.166-4.711,9.109-7.137a1.819,1.819,0,0,1,1.9-.256c96.229,43.917,200.41,43.917,295.5,0a1.812,1.812,0,0,1,1.924.233c2.944,2.426,6.027,4.851,9.132,7.16a1.884,1.884,0,0,1-.162,3.126,301.407,301.407,0,0,1-45.89,21.83,1.875,1.875,0,0,0-1,2.611,391.055,391.055,0,0,0,30.014,48.815,1.864,1.864,0,0,0,2.063.7A486.048,486.048,0,0,0,610.7,405.729a1.882,1.882,0,0,0,.765-1.352C623.729,277.594,590.933,167.465,524.531,69.836ZM222.491,337.58c-28.972,0-52.844-26.587-52.844-59.239S193.056,219.1,222.491,219.1c29.665,0,53.306,26.82,52.843,59.239C275.334,310.993,251.924,337.58,222.491,337.58Zm195.38,0c-28.971,0-52.843-26.587-52.843-59.239S388.437,219.1,417.871,219.1c29.667,0,53.307,26.82,52.844,59.239C470.715,310.993,447.538,337.58,417.871,337.58Z"></path></svg>' . esc_html( $btn_text ) . '</a>';
				// $saviorpro_connecttodiscord_btn .= '<p class="atm_assigned_role">';
				// if ( $mapped_role_name || $default_role_name ) {
				// 	// $saviorpro_connecttodiscord_btn .= esc_html__( 'Following Roles will be assigned to you in Discord: ', 'saviorpro-discord' );
				// }
				// if ( $mapped_role_name ) {
				// 	$saviorpro_connecttodiscord_btn .= atm_saviorpro_discord_allowed_html( $mapped_role_name );
				// }
				// if ( $default_role_name && $mapped_role_name ) {
				// 	// $saviorpro_connecttodiscord_btn .= ' , ';
				// }
				// if ( $default_role_name ) {
				// 	$saviorpro_connecttodiscord_btn .= atm_saviorpro_discord_allowed_html( $default_role_name );
				// }
				// $saviorpro_connecttodiscord_btn .= '</p></div>';
				$saviorpro_connecttodiscord_btn .= '</div>';
			}
		}
		echo $saviorpro_connecttodiscord_btn;
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
		// return $saviorpro_connecttodiscord_btn;

	}

	/**
	 * Show status of PMPro connection with user
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_show_discord_button() {
		$show = apply_filters( 'atm_saviorpro_show_connect_button_on_profile', true );
		if ( $show ) {
			echo do_shortcode( '[discord_connect_button]' );
		}
	}

	/**
	 * Method to queue all members into cancel job when saviorpro level is deleted.
	 *
	 * @param INT $level_id
	 * @return NONE
	 */
	public function atm_saviorpro_discord_as_schedule_job_saviorpro_level_deleted( $level_id ) {
		global $wpdb;
		$result                         = $wpdb->get_results( $wpdb->prepare( 'SELECT `user_id` FROM ' . $wpdb->prefix . 'saviorpro_memberships_users' . ' WHERE `membership_id` = %d GROUP BY `user_id`', array( $level_id ) ) );
		$atm_saviorpro_discord_role_mapping = json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
		update_option( 'atm_admin_level_deleted', true );
		foreach ( $result as $key => $ids ) {
			$user_id      = $ids->user_id;
			$access_token = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
			if ( $access_token ) {
				as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_handle_saviorpro_cancel', array( $user_id, $level_id, $level_id ), ATM_DISCORD_AS_GROUP_NAME );
			}
		}
	}

	/**
	 * Method for allow user to login with discord account.
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_checkout_after_email() {
		wp_enqueue_style( 'atm_saviorpro_add_discord_style' );
		if ( ! is_user_logged_in() ) {
			$default_role                   = sanitize_text_field( trim( get_option( '_atm_saviorpro_discord_default_role_id' ) ) );
			$atm_saviorpro_discord_role_mapping = json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
			$all_roles                      = unserialize( get_option( 'atm_saviorpro_discord_all_roles' ) );
			$member_discord_login           = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_login_with_discord' ) ) );
			$btn_color                      = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_btn_color' ) ) );
			$btn_text                       = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_loggedout_btn_text' ) ) );
			echo '<style>.saviorpro-btn-connect{background-color: ' . $btn_color . ';}</style>';
			if ( $member_discord_login ) {
				$curr_level_id     = isset( $_GET['level'] ) ? $_GET['level'] : '';
				$mapped_role_name  = '';
				$default_role_name = '';
				if ( $default_role != 'none' && is_array( $all_roles ) && array_key_exists( $default_role, $all_roles ) ) {
					$default_role_name = $all_roles[ $default_role ];
				}
				if ( $curr_level_id && is_array( $all_roles ) ) {
					if ( is_array( $atm_saviorpro_discord_role_mapping ) && array_key_exists( 'saviorpro_level_id_' . $curr_level_id, $atm_saviorpro_discord_role_mapping ) ) {
						$mapped_role_id = $atm_saviorpro_discord_role_mapping[ 'saviorpro_level_id_' . $curr_level_id ];
						if ( array_key_exists( $mapped_role_id, $all_roles ) ) {
							$mapped_role_name = $all_roles[ $mapped_role_id ];
						}
					}
				}
				$current_url = atm_saviorpro_discord_get_current_screen_url();
				echo '<a href="?action=discord-login&fromcheckout=1&url=' . $current_url . '" class="saviorpro-btn-connect atm-btn" >' . esc_html( $btn_text ) . '<i class="fab fa-discord"></i></a>';
				$saviorpro_connecttodiscord_btn = '';
				if ( $mapped_role_name ) {
					// $saviorpro_connecttodiscord_btn .= '<p class="atm_assigned_role">' . esc_html__( 'Following Roles will be assigned to you in Discord: ', 'saviorpro-discord' );
					// $saviorpro_connecttodiscord_btn .= esc_html( $mapped_role_name );
					// if ( $default_role_name ) {
					// 	$saviorpro_connecttodiscord_btn .= ', ' . esc_html( $default_role_name );
					// }
					// $saviorpro_connecttodiscord_btn .= '</p>';

					echo $saviorpro_connecttodiscord_btn;
				}
			}
		}
	}

	/**
	 * Method to save job queue for cancelled saviorpro members.
	 *
	 * @param INT $level_id
	 * @param INT $user_id
	 * @param INT $cancel_level
	 * @return NONE
	 */
	public function atm_saviorpro_discord_as_schdule_job_saviorpro_cancel( $level_id, $user_id, $cancel_level ) {
		$membership_status = sanitize_text_field( trim( $this->atm_check_current_membership_status( $user_id ) ) );
		$access_token      = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		$next_payment      = saviorpro_next_payment( $user_id );
		global $saviorpro_next_payment_timestamp;

		if ( ! empty( $cancel_level ) || $membership_status == 'admin_cancelled' ) {
			$args = array(
				'hook'    => 'atm_saviorpro_discord_as_handle_saviorpro_cancel',
				'args'    => array( $level_id, $user_id, $cancel_level ),
				'status'  => ActionScheduler_Store::STATUS_PENDING,
				'orderby' => 'date',
			);

			// check if member is already added to job queue.
			$cancl_arr_already_added = as_get_scheduled_actions( $args, ARRAY_A );

			if ( count( $cancl_arr_already_added ) === 0 && $access_token && ( $membership_status == 'cancelled' || $membership_status == 'admin_cancelled' ) ) {
				as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_handle_saviorpro_cancel', array( $user_id, $level_id, $cancel_level ), ATM_DISCORD_AS_GROUP_NAME );
			}
		}
	}

	/**
	 * If the cancel on next payment is enabled.
	 */
	public function atm_saviorpro_discord_handle_cancel_on_next_payment( $level, $user_id, $old_level_status, $cancel_level ) {
		global $wpdb;
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$cancel_on_next_payment = is_plugin_active( 'saviorpro-cancel-on-next-payment-date/saviorpro-cancel-on-next-payment-date.php' );
		$access_token           = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );

		if ( $cancel_on_next_payment && $old_level_status == 'cancelled' && $cancel_level ) {
			$end_date           = $wpdb->get_var( $wpdb->prepare( "SELECT enddate FROM $wpdb->saviorpro_memberships_users WHERE status=%s AND membership_id=%d AND user_id=%d", 'active', $level, $user_id ) );
			$end_date_timestamp = date_timestamp_get( date_create( $end_date ) );
			if ( $end_date_timestamp !== false ) {
				if ( $access_token ) {
					as_schedule_single_action( $end_date_timestamp, 'atm_saviorpro_discord_as_handle_saviorpro_cancel', array( $user_id, $level, $cancel_level ), ATM_DISCORD_AS_GROUP_NAME );
				}
			}
		}
		return $level;
	}

	/*
	* Action schedule to schedule a function to run upon SAVIORPRO Expiry.
	*
	* @param INT $user_id
	* @param INT $level_id
	* @return NONE
	*/
	public function atm_saviorpro_discord_as_schdule_job_saviorpro_expiry( $user_id, $level_id ) {
		$existing_members_queue = sanitize_text_field( trim( get_option( 'atm_queue_of_saviorpro_members' ) ) );
		  $membership_status    = sanitize_text_field( trim( $this->atm_check_current_membership_status( $user_id ) ) );
		  $access_token         = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		if ( $membership_status == 'expired' && $access_token ) {
			as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_handle_saviorpro_expiry', array( $user_id, $level_id ), ATM_DISCORD_AS_GROUP_NAME );
		}
	}


	/**
	 * Localized script and style
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_add_script() {
		wp_register_style(
			'atm_saviorpro_add_discord_style',
			ATM_SAVIORPRO_DISCORD_URL . 'assets/css/atm-saviorpro-discord-style.css',
			false,
			ATM_SAVIORPRO_VERSION
		);

		wp_register_script(
			'atm_saviorpro_add_discord_script',
			ATM_SAVIORPRO_DISCORD_URL . 'assets/js/atm-saviorpro-add-discord-script.js',
			array( 'jquery' ),
			ATM_SAVIORPRO_VERSION
		);

		$script_params = array(
			'admin_ajax'        => admin_url( 'admin-ajax.php' ),
			'permissions_const' => ATM_DISCORD_BOT_PERMISSIONS,
			'is_admin'          => is_admin(),
			'atm_discord_nonce' => wp_create_nonce( 'atm-discord-ajax-nonce' ),
		);
		wp_localize_script( 'atm_saviorpro_add_discord_script', 'atmSaviorproParams', $script_params );

	}

	/**
	 * Localized admin script and style
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_add_admin_script() {

		wp_register_style(
			'atm_saviorpro_add_skeletabs_style',
			ATM_SAVIORPRO_DISCORD_URL . 'assets/css/skeletabs.css',
			false,
			ATM_SAVIORPRO_VERSION
		);
		wp_enqueue_style( 'atm_saviorpro_add_skeletabs_style' );

		wp_register_script(
			'atm_saviorpro_add_skeletabs_script',
			ATM_SAVIORPRO_DISCORD_URL . 'assets/js/skeletabs.js',
			array( 'jquery' ),
			ATM_SAVIORPRO_VERSION
		);
	}

	/**
	 * Add menu in membership dashboard sub-menu
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_add_new_menu() {
		// Add sub-menu into PmPro main-menus list
		// add_submenu_page( 'saviorpro-dashboard', __( 'Discord Settings', 'saviorpro-discord' ), __( 'Discord Settings', 'saviorpro-discord' ), 'manage_options', 'discord-options', array( $this, 'atm_saviorpro_discord_setting_page' ) );

		add_menu_page(
			__( 'Discord Settings', 'saviorpro-discord' ),
			__( 'Discord Settings', 'saviorpro-discord' ),
			'manage_options',
			'saviorpro-discord',
			array( &$this, 'atm_saviorpro_discord_setting_page' ),
			ATM_SAVIORPRO_DISCORD_URL. 'assets/images/discord-logo.png',
			6
		);
	}

	/**
	 * Get user membership status by user_id
	 *
	 * @param INT $user_id
	 * @return STRING $status
	 */
	public function atm_check_current_membership_status( $user_id ) {
		global $wpdb;
		$sql    = $wpdb->prepare( 'SELECT `status` FROM ' . $wpdb->prefix . 'saviorpro_memberships_users' . ' WHERE `user_id`= %d ORDER BY `id` DESC limit 1', array( $user_id ) );
		$result = $wpdb->get_results( $sql );
		return $result[0]->status;
	}

	/**
	 * Define plugin settings rules
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_setting_page() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		wp_enqueue_style( 'atm_saviorpro_add_discord_style' );
		wp_enqueue_script( 'atm_saviorpro_add_skeletabs_script' );
		wp_enqueue_script( 'atm_saviorpro_add_discord_script' );
		wp_enqueue_script( 'jquery-ui-draggable' );
		wp_enqueue_script( 'jquery-ui-droppable' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style( 'wp-color-picker' );
		$log_api_res = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_log_api_response' ) ) );
		if ( isset( $_GET['save_settings_msg'] ) ) {
			?>
<div class="notice notice-success is-dismissible support-success-msg">
    <p><?php echo esc_html( $_GET['save_settings_msg'] ); ?></p>
</div>
<?php
		}
		if ( $log_api_res ) {
			echo '<div class="notice notice-error is-dismissible"> <p>SAVIORPRO - Discord logging is currently enabled. Since logs may contain sensitive information, please ensure that you only leave it enabled for as long as it is needed for troubleshooting. If you currently have a support ticket open, please do not disable logging until the Support Team has reviewed your logs.</p> </div>';
		}
		?>
<h1><?php echo __( 'ATM Discord Settings', 'saviorpro-discord' ); ?></h1>

<div id="outer" class="skltbs-theme-light" data-skeletabs='{ "startIndex": 1 }'>
    <ul class="skltbs-tab-group">
        <li class="skltbs-tab-item">
            <button class="skltbs-tab"
                data-identity="settings"><?php echo __( 'Application Details', 'saviorpro-discord' ); ?><span
                    class="initialtab spinner"></span></button>
        </li>
        <?php if ( Check_saved_settings_status() ) : ?>
        <li class="skltbs-tab-item">
            <button class="skltbs-tab"
                data-identity="level-mapping"><?php echo __( 'Role Mappings', 'saviorpro-discord' ); ?></button>
        </li>
        <?php endif; ?>
        <li class="skltbs-tab-item">
            <button class="skltbs-tab" data-identity="advanced" data-toggle="tab"
                data-event="atm_advanced"><?php echo __( 'Advanced', 'saviorpro-discord' ); ?>
            </button>
        </li>
        <li class="skltbs-tab-item">
            <button class="skltbs-tab" data-identity="appearance" data-toggle="tab"
                data-event="atm_appearance"><?php echo __( 'Appearance', 'saviorpro-discord' ); ?>
            </button>
        </li>
        <li class="skltbs-tab-item">
            <button class="skltbs-tab" data-identity="logs" data-toggle="tab"
                data-event="atm_logs"><?php echo __( 'Logs', 'saviorpro-discord' ); ?>
            </button>
        </li>
    </ul>
    <div class="skltbs-panel-group">
        <div id="atm_saviorpro_application_details" class="skltbs-panel">
            <?php include ATM_SAVIORPRO_DISCORD_PATH . 'includes/pages/discord-settings.php'; ?>
        </div>
        <?php if ( Check_saved_settings_status() ) : ?>
        <div id="atm_saviorpro_role_mapping" class="skltbs-panel">
            <?php include ATM_SAVIORPRO_DISCORD_PATH . 'includes/pages/discord-role-level-map.php'; ?>
        </div>
        <?php endif; ?>
        <div id="atm_saviorpro_advance_settings" class="skltbs-panel">
            <?php include ATM_SAVIORPRO_DISCORD_PATH . 'includes/pages/advanced.php'; ?>
        </div>
        <div id="atm_saviorpro_appearance" class="skltbs-panel">
            <?php include ATM_SAVIORPRO_DISCORD_PATH . 'includes/pages/appearance.php'; ?>
        </div>
        <div id="atm_saviorpro_error_log" class="skltbs-panel">
            <?php include ATM_SAVIORPRO_DISCORD_PATH . 'includes/pages/error_log.php'; ?>
        </div>
    </div>
</div>
<?php
		$this->get_Support_Data();
	}


	/**
	 * Save application details
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_save_application_details() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$atm_saviorpro_discord_client_id = isset( $_POST['atm_saviorpro_discord_client_id'] ) ? sanitize_text_field( trim( $_POST['atm_saviorpro_discord_client_id'] ) ) : '';

		$discord_client_secret = isset( $_POST['atm_saviorpro_discord_client_secret'] ) ? sanitize_text_field( trim( $_POST['atm_saviorpro_discord_client_secret'] ) ) : '';

		$discord_bot_token = isset( $_POST['atm_saviorpro_discord_bot_token'] ) ? sanitize_text_field( trim( $_POST['atm_saviorpro_discord_bot_token'] ) ) : '';

		$atm_saviorpro_discord_redirect_url = isset( $_POST['atm_saviorpro_discord_redirect_url'] ) ? sanitize_text_field( trim( $_POST['atm_saviorpro_discord_redirect_url'] ) ) : '';

		$atm_saviorpro_discord_guild_id = isset( $_POST['atm_saviorpro_discord_guild_id'] ) ? sanitize_text_field( trim( $_POST['atm_saviorpro_discord_guild_id'] ) ) : '';

		if ( isset( $_POST['submit'] ) && ! isset( $_POST['atm_saviorpro_discord_role_mapping'] ) ) {
			if ( isset( $_POST['atm_discord_save_settings'] ) && wp_verify_nonce( $_POST['atm_discord_save_settings'], 'save_discord_settings' ) ) {
				if ( $atm_saviorpro_discord_client_id ) {
					update_option( 'atm_saviorpro_discord_client_id', $atm_saviorpro_discord_client_id );
				}

				if ( $discord_client_secret ) {
					update_option( 'atm_saviorpro_discord_client_secret', $discord_client_secret );
				}

				if ( $discord_bot_token ) {
					update_option( 'atm_saviorpro_discord_bot_token', $discord_bot_token );
				}

				if ( $atm_saviorpro_discord_redirect_url ) {
					// add a query string param `via` GH #185.
					$atm_saviorpro_discord_redirect_url = get_formated_discord_redirect_url( $atm_saviorpro_discord_redirect_url );
					update_option( 'atm_saviorpro_discord_redirect_url', $atm_saviorpro_discord_redirect_url );
				}

				if ( $atm_saviorpro_discord_guild_id ) {
					update_option( 'atm_saviorpro_discord_guild_id', $atm_saviorpro_discord_guild_id );
				}
				$message      = 'Your settings are saved successfully.';
				$pre_location = $_POST['referrer'] . '&save_settings_msg=' . $message . '#atm_saviorpro_application_details';
				wp_safe_redirect( $pre_location );
			}
		}
	}

	/**
	 * Save Role mappiing settings
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_save_role_mapping() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$atm_discord_roles = isset( $_POST['atm_saviorpro_discord_role_mapping'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_role_mapping'] ) ) : '';

		$_atm_saviorpro_discord_default_role_id = isset( $_POST['saviorpro_defaultRole'] ) ? sanitize_textarea_field( trim( $_POST['saviorpro_defaultRole'] ) ) : '';

		$allow_none_member = isset( $_POST['allow_none_member'] ) ? sanitize_textarea_field( trim( $_POST['allow_none_member'] ) ) : '';

		$atm_discord_roles   = stripslashes( $atm_discord_roles );
		$save_mapping_status = update_option( 'atm_saviorpro_discord_role_mapping', $atm_discord_roles );
		if ( isset( $_POST['atm_saviorpro_discord_role_mappings_nonce'] ) && wp_verify_nonce( $_POST['atm_saviorpro_discord_role_mappings_nonce'], 'discord_role_mappings_nonce' ) ) {
			if ( ( $save_mapping_status || isset( $_POST['atm_saviorpro_discord_role_mapping'] ) ) && ! isset( $_POST['flush'] ) ) {
				if ( $_atm_saviorpro_discord_default_role_id ) {
					update_option( '_atm_saviorpro_discord_default_role_id', $_atm_saviorpro_discord_default_role_id );
				}

				if ( $allow_none_member ) {
					update_option( 'atm_saviorpro_allow_none_member', $allow_none_member );
				}
				$message = 'Your mappings are saved successfully.';
			}
			if ( isset( $_POST['flush'] ) ) {
				delete_option( 'atm_saviorpro_discord_role_mapping' );
				delete_option( '_atm_saviorpro_discord_default_role_id' );
				delete_option( 'atm_saviorpro_allow_none_member' );
				$message = 'Your settings flushed successfully.';
			}
			$pre_location = $_POST['referrer'] . '&save_settings_msg=' . $message . '#atm_saviorpro_role_mapping';
			wp_safe_redirect( $pre_location );
		}
	}

	/**
	 * Save advance settings
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_save_advance_settings() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

		$set_job_cnrc = isset( $_POST['set_job_cnrc'] ) ? sanitize_textarea_field( trim( $_POST['set_job_cnrc'] ) ) : '';

		$set_job_q_batch_size = isset( $_POST['set_job_q_batch_size'] ) ? sanitize_textarea_field( trim( $_POST['set_job_q_batch_size'] ) ) : '';

		$retry_api_count = isset( $_POST['atm_saviorpro_retry_api_count'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_retry_api_count'] ) ) : '';

		$atm_saviorpro_discord_send_expiration_warning_dm = isset( $_POST['atm_saviorpro_discord_send_expiration_warning_dm'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_send_expiration_warning_dm'] ) ) : false;

		$atm_saviorpro_discord_expiration_warning_message = isset( $_POST['atm_saviorpro_discord_expiration_warning_message'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_expiration_warning_message'] ) ) : '';

		$atm_saviorpro_discord_send_membership_expired_dm = isset( $_POST['atm_saviorpro_discord_send_membership_expired_dm'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_send_membership_expired_dm'] ) ) : false;

		$atm_saviorpro_discord_expiration_expired_message = isset( $_POST['atm_saviorpro_discord_expiration_expired_message'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_expiration_expired_message'] ) ) : '';

		$atm_saviorpro_discord_send_welcome_dm = isset( $_POST['atm_saviorpro_discord_send_welcome_dm'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_send_welcome_dm'] ) ) : false;

		$atm_saviorpro_discord_welcome_message = isset( $_POST['atm_saviorpro_discord_welcome_message'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_welcome_message'] ) ) : '';

		$atm_saviorpro_discord_send_membership_cancel_dm = isset( $_POST['atm_saviorpro_discord_send_membership_cancel_dm'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_send_membership_cancel_dm'] ) ) : '';

		$atm_saviorpro_discord_cancel_message = isset( $_POST['atm_saviorpro_discord_cancel_message'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_cancel_message'] ) ) : '';

		$atm_saviorpro_discord_embed_messaging_feature = isset( $_POST['atm_saviorpro_discord_embed_messaging_feature'] ) ? sanitize_textarea_field( trim( $_POST['atm_saviorpro_discord_embed_messaging_feature'] ) ) : '';

		if ( isset( $_POST['adv_submit'] ) ) {
			if ( isset( $_POST['atm_discord_save_adv_settings'] ) && wp_verify_nonce( $_POST['atm_discord_save_adv_settings'], 'save_discord_adv_settings' ) ) {
				if ( isset( $_POST['upon_failed_payment'] ) ) {
					update_option( 'atm_saviorpro_discord_payment_failed', true );
				} else {
					update_option( 'atm_saviorpro_discord_payment_failed', false );
				}

				if ( isset( $_POST['log_api_res'] ) ) {
					update_option( 'atm_saviorpro_discord_log_api_response', true );
				} else {
					update_option( 'atm_saviorpro_discord_log_api_response', false );
				}

				if ( isset( $_POST['retry_failed_api'] ) ) {
					update_option( 'atm_saviorpro_retry_failed_api', true );
				} else {
					update_option( 'atm_saviorpro_retry_failed_api', false );
				}

				if ( isset( $_POST['member_kick_out'] ) ) {
					update_option( 'atm_saviorpro_member_kick_out', true );
				} else {
					update_option( 'atm_saviorpro_member_kick_out', false );
				}

				if ( isset( $_POST['member_force_discord_login'] ) ) {
					update_option( 'atm_saviorpro_discord_force_login_with_discord', true );
					update_option( 'atm_saviorpro_discord_login_with_discord', true );
				} else {
					update_option( 'atm_saviorpro_discord_force_login_with_discord', false );
				}

				if ( isset( $_POST['member_discord_login'] ) ) {
					update_option( 'atm_saviorpro_discord_login_with_discord', true );
				} elseif ( isset( $_POST['member_force_discord_login'] ) ) {
					update_option( 'atm_saviorpro_discord_login_with_discord', true );
				} else {
					update_option( 'atm_saviorpro_discord_login_with_discord', false );
				}

				if ( isset( $_POST['atm_saviorpro_discord_send_welcome_dm'] ) ) {
					update_option( 'atm_saviorpro_discord_send_welcome_dm', true );
				} else {
					update_option( 'atm_saviorpro_discord_send_welcome_dm', false );
				}

				if ( isset( $_POST['atm_saviorpro_discord_send_expiration_warning_dm'] ) ) {
					update_option( 'atm_saviorpro_discord_send_expiration_warning_dm', true );
				} else {
					update_option( 'atm_saviorpro_discord_send_expiration_warning_dm', false );
				}

				if ( isset( $_POST['atm_saviorpro_discord_welcome_message'] ) && $_POST['atm_saviorpro_discord_welcome_message'] != '' ) {
					update_option( 'atm_saviorpro_discord_welcome_message', $atm_saviorpro_discord_welcome_message );
				} else {
					update_option( 'atm_saviorpro_discord_expiration_warning_message', 'Your membership is expiring' );
				}

				if ( isset( $_POST['atm_saviorpro_discord_expiration_warning_message'] ) && $_POST['atm_saviorpro_discord_expiration_warning_message'] != '' ) {
					update_option( 'atm_saviorpro_discord_expiration_warning_message', $atm_saviorpro_discord_expiration_warning_message );
				} else {
					update_option( 'atm_saviorpro_discord_expiration_warning_message', 'Your membership is expiring' );
				}

				if ( isset( $_POST['atm_saviorpro_discord_expiration_expired_message'] ) && $_POST['atm_saviorpro_discord_expiration_expired_message'] != '' ) {
					update_option( 'atm_saviorpro_discord_expiration_expired_message', $atm_saviorpro_discord_expiration_expired_message );
				} else {
					update_option( 'atm_saviorpro_discord_expiration_expired_message', 'Your membership is expired' );
				}

				if ( isset( $_POST['atm_saviorpro_discord_send_membership_expired_dm'] ) ) {
					update_option( 'atm_saviorpro_discord_send_membership_expired_dm', true );
				} else {
					update_option( 'atm_saviorpro_discord_send_membership_expired_dm', false );
				}

				if ( isset( $_POST['atm_saviorpro_discord_send_membership_cancel_dm'] ) ) {
					update_option( 'atm_saviorpro_discord_send_membership_cancel_dm', true );
				} else {
					update_option( 'atm_saviorpro_discord_send_membership_cancel_dm', false );
				}

				if ( isset( $_POST['atm_saviorpro_discord_cancel_message'] ) && $_POST['atm_saviorpro_discord_cancel_message'] != '' ) {
					update_option( 'atm_saviorpro_discord_cancel_message', $atm_saviorpro_discord_cancel_message );
				} else {
					update_option( 'atm_saviorpro_discord_cancel_message', 'Your membership is cancled' );
				}

				if ( isset( $_POST['set_job_cnrc'] ) ) {
					if ( $set_job_cnrc < 1 ) {
						update_option( 'atm_saviorpro_discord_job_queue_concurrency', 1 );
					} else {
						update_option( 'atm_saviorpro_discord_job_queue_concurrency', $set_job_cnrc );
					}
				}

				if ( isset( $_POST['set_job_q_batch_size'] ) ) {
					if ( $set_job_q_batch_size < 1 ) {
						update_option( 'atm_saviorpro_discord_job_queue_batch_size', 1 );
					} else {
						update_option( 'atm_saviorpro_discord_job_queue_batch_size', $set_job_q_batch_size );
					}
				}

				if ( isset( $_POST['atm_saviorpro_retry_api_count'] ) ) {
					if ( $retry_api_count < 1 ) {
						update_option( 'atm_saviorpro_retry_api_count', 1 );
					} else {
						update_option( 'atm_saviorpro_retry_api_count', $retry_api_count );
					}
				}

				if ( isset( $_POST['atm_saviorpro_discord_embed_messaging_feature'] ) ) {
					update_option( 'atm_saviorpro_discord_embed_messaging_feature', true );
				} else {
					update_option( 'atm_saviorpro_discord_embed_messaging_feature', false );
				}
				$message      = 'Your settings are saved successfully.';
				$pre_location = $_POST['referrer'] . '&save_settings_msg=' . $message . '#atm_saviorpro_advance_settings';
				wp_safe_redirect( $pre_location );
			}
		}

	}

	/**
	 * Save apearance settings
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function atm_saviorpro_discord_save_appearance_settings() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

		$atm_saviorpro_btn_color            = isset( $_POST['atm_saviorpro_btn_color'] ) && $_POST['atm_saviorpro_btn_color'] !== '' ? sanitize_text_field( trim( $_POST['atm_saviorpro_btn_color'] ) ) : '#5865f2';
		$atm_saviorpro_btn_disconnect_color = isset( $_POST['atm_saviorpro_btn_disconnect_color'] ) && $_POST['atm_saviorpro_btn_disconnect_color'] != '' ? sanitize_text_field( trim( $_POST['atm_saviorpro_btn_disconnect_color'] ) ) : '#ff0000';
		$atm_saviorpro_loggedin_btn_text    = isset( $_POST['atm_saviorpro_loggedin_btn_text'] ) && $_POST['atm_saviorpro_loggedin_btn_text'] != '' ? sanitize_text_field( trim( $_POST['atm_saviorpro_loggedin_btn_text'] ) ) : 'Connect To Discord';
		$atm_saviorpro_loggedout_btn_text   = isset( $_POST['atm_saviorpro_loggedout_btn_text'] ) && $_POST['atm_saviorpro_loggedout_btn_text'] != '' ? sanitize_text_field( trim( $_POST['atm_saviorpro_loggedout_btn_text'] ) ) : 'Login With Discord';
		$atm_saviorpro_disconnect_btn_text  = $_POST['atm_saviorpro_disconnect_btn_text'] ? sanitize_text_field( trim( $_POST['atm_saviorpro_disconnect_btn_text'] ) ) : 'Disconnect From Discord';

		if ( isset( $_POST['apr_submit'] ) ) {

			if ( isset( $_POST['atm_discord_save_aprnc_settings'] ) && wp_verify_nonce( $_POST['atm_discord_save_aprnc_settings'], 'save_discord_aprnc_settings' ) ) {
				if ( $atm_saviorpro_btn_color ) {
					update_option( 'atm_saviorpro_discord_btn_color', $atm_saviorpro_btn_color );
				}
				if ( $atm_saviorpro_btn_disconnect_color ) {
					update_option( 'atm_saviorpro_btn_disconnect_color', $atm_saviorpro_btn_disconnect_color );
				}
				if ( $atm_saviorpro_loggedout_btn_text ) {
					update_option( 'atm_saviorpro_discord_loggedout_btn_text', $atm_saviorpro_loggedout_btn_text );
				}
				if ( $atm_saviorpro_loggedin_btn_text ) {
					update_option( 'atm_saviorpro_discord_loggedin_btn_text', $atm_saviorpro_loggedin_btn_text );
				}
				if ( $atm_saviorpro_disconnect_btn_text ) {
					update_option( 'atm_saviorpro_disconnect_btn_text', $atm_saviorpro_disconnect_btn_text );
				}
				$message      = 'Your settings are saved successfully.';
				$pre_location = $_POST['referrer'] . '&save_settings_msg=' . $message . '#atm_saviorpro_appearance';
				wp_safe_redirect( $pre_location );
			}
		}

	}
	/**
	 * Send mail to support form current user
	 *
	 * @param NONE
	 * @return NONE
	 */
	public function get_Support_Data() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

		if ( isset( $_POST['save'] ) ) {
			// Check for nonce security
			if ( ! wp_verify_nonce( $_POST['atm_discord_get_support'], 'get_support' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
			}
			$etsUserName  = isset( $_POST['atm_user_name'] ) ? sanitize_text_field( trim( $_POST['atm_user_name'] ) ) : '';
			$etsUserEmail = isset( $_POST['atm_user_email'] ) ? sanitize_text_field( trim( $_POST['atm_user_email'] ) ) : '';
			$message      = isset( $_POST['atm_support_msg'] ) ? sanitize_text_field( trim( $_POST['atm_support_msg'] ) ) : '';
			$sub          = isset( $_POST['atm_support_subject'] ) ? sanitize_text_field( trim( $_POST['atm_support_subject'] ) ) : '';

			if ( $etsUserName && $etsUserEmail && $message && $sub ) {

				$subject   = $sub;
				$to        = 'dev@savior.im';
				$content   = 'Name: ' . $etsUserName . '<br>';
				$content  .= 'Contact Email: ' . $etsUserEmail . '<br>';
				$content  .= 'Message: ' . $message;
				$headers   = array();
				$blogemail = get_bloginfo( 'admin_email' );
				$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $blogemail . '>' . "\r\n";
				$mail      = wp_mail( $to, $subject, $content, $headers );

				if ( $mail ) {
					?>
					<div class="notice notice-success is-dismissible support-success-msg">
						<p><?php echo __( 'Your request have been successfully submitted!', 'saviorpro-discord' ); ?></p>
					</div>
					<?php
				}
			}
		}
	}

	/*
	* Add extra column body into saviorpro members list
	* @param STRING $colname
	* @param INT $user
	* @return NONE
	*/
	public function atm_saviorpro_discord_saviorpro_extra_cols_body( $colname, $user_id ) {
		wp_enqueue_style( 'atm_saviorpro_add_discord_style' );
		wp_enqueue_script( 'atm_saviorpro_add_discord_script' );
		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		if ( 'discord' === $colname ) {
			if ( $access_token ) {
				$discord_username = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_username', true ) ) );
				echo '<p class="' . esc_attr( $user_id ) . ' atm-save-success">Success</p><a class="button button-primary atm-run-api" data-uid="' . esc_attr( $user_id ) . '" href="#">';
				echo __( 'Run API', 'saviorpro-discord' );
				echo '</a><span class="' . esc_attr( $user_id ) . ' spinner"></span>';
				echo esc_html( $discord_username );
			} else {
				echo __( 'Not Connected', 'saviorpro-discord' );
			}
		}

		if ( 'joined_date' === $colname ) {
			echo esc_html( get_user_meta( $user_id, '_atm_saviorpro_discord_join_date', true ) );
		}
	}
	/*
	* Add extra column into saviorpro members list
	* @param ARRAY $columns
	* @return ARRAY $columns
	*/
	public function atm_saviorpro_discord_manage_memberslist_columns( $columns ) {
		$columns['discord']     = __( 'Discord', 'saviorpro-discord' );
		$columns['joined_date'] = __( 'Joined Date', 'saviorpro-discord' );
		return $columns;
	}

	/*
	* Add extra css
	* @param NONE
	* @return NONE
	*/
	public function atm_saviorpro_discord_add_inline_css_checkout() {
		$member_force_discord_login = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_force_login_with_discord' ) ) );
		$member_discord_login       = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_login_with_discord' ) ) );
		if ( in_array( 'saviorpro-checkout', get_body_class() ) && $member_force_discord_login && $member_discord_login ) {
			if ( ! is_user_logged_in() ) {
				$custom_css = 'body.saviorpro-checkout div#saviorpro_user_fields,body.saviorpro-checkout div#saviorpro_billing_address_fields,body.saviorpro-checkout div#saviorpro_payment_information_fields,body.saviorpro-checkout div.saviorpro_submit{display: none!important;}';
			} else {
				$custom_css = '';
			}
			wp_add_inline_style( 'atm_saviorpro_add_discord_style', $custom_css );
		}
	}

	/**
	 *  Filter call back to show or hide the Connect Discord button on profile page.
	 *
	 * @param bool $show By default True.
	 */
	public function atm_saviorpro_discord_show_connect_button_on_profile( $show = true ) {
		return $show;
	}
}
new Atm_SaviorPro_Admin_Setting();