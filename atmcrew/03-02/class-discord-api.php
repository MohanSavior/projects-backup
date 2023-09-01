<?php
/**
 * Class to handle discord API calls
 */
class SaviorPro_Discord_API {
	function __construct() {
		// Discord api callback
		add_action( 'init', array( $this, 'atm_saviorpro_discord_discord_api_callback' ) );

		// execute this call back for certain $_GET['action']
		add_action( 'init', array( $this, 'atm_saviorpro_discord_act_on_url_action' ) );

		// front ajax function to disconnect from discord
		add_action( 'wp_ajax_disconnect_from_discord', array( $this, 'atm_saviorpro_discord_disconnect_from_discord' ) );

		// disconnect from discord on user deletion
		// add_action( 'delete_user', array( $this, 'atm_saviorpro_discord_disconnect_on_delete_user' ), 10, 3 );

		// front ajax function to disconnect from discord
		add_action( 'wp_ajax_atm_saviorpro_discord_load_discord_roles', array( $this, 'atm_saviorpro_discord_load_discord_roles' ) );

		// Handle membership status change
		// add_action( 'wc_memberships_user_membership_status_changed', array( $this, 'atm_saviorpro_discord_user_membership_status_change' ), 10, 3 );
		add_action( 'wc_memberships_user_membership_saved', array( $this, 'atm_saviorpro_memberships_saved' ), 20, 2 );


		add_action( 'saviorpro_after_change_membership_level', array( $this, 'atm_saviorpro_discord_change_discord_role_from_saviorpro' ), 10, 4 );
		add_action( 'atm_saviorpro_discord_as_handle_saviorpro_expiry', array( $this, 'atm_saviorpro_discord_as_handler_saviorpro_expiry' ), 10, 2 );
		add_action( 'atm_saviorpro_discord_as_handle_saviorpro_cancel', array( $this, 'atm_saviorpro_discord_as_handler_saviorpro_cancel' ), 10, 3 );

		add_action( 'atm_saviorpro_discord_as_handle_add_member_to_guild', array( $this, 'atm_saviorpro_discord_as_handler_add_member_to_guild' ), 10, 3 );

		add_action( 'atm_saviorpro_discord_as_schedule_delete_member', array( $this, 'atm_saviorpro_discord_as_handler_delete_member_from_guild' ), 10, 2 );

		add_action( 'atm_saviorpro_discord_as_schedule_member_put_role', array( $this, 'atm_saviorpro_discord_as_handler_put_memberrole' ), 10, 3 );

		add_action( 'atm_saviorpro_discord_as_schedule_delete_role', array( $this, 'atm_saviorpro_discord_as_handler_delete_memberrole' ), 10, 3 );

		add_action( 'wp_ajax_atm_saviorpro_discord_member_table_run_api', array( $this, 'atm_saviorpro_discord_member_table_run_api' ) );

		add_action( 'saviorpro_stripe_subscription_deleted', array( $this, 'atm_saviorpro_discord_stripe_subscription_deleted' ), 10, 1 );

		add_action( 'saviorpro_subscription_payment_failed', array( $this, 'atm_saviorpro_discord_subscription_payment_failed' ), 10, 1 );

		add_action( 'action_scheduler_failed_execution', array( $this, 'atm_saviorpro_discord_reschedule_failed_action' ), 10, 3 );

		add_action( 'atm_saviorpro_discord_as_send_dm', array( $this, 'atm_saviorpro_discord_handler_send_dm' ), 10, 3 );

	}
	/**
	 * Remove discord member roles.
	 *
	 * @param user_membership
	 * @param old_status
	 * @param new_status
	 */
	public function atm_saviorpro_discord_user_membership_status_change( $user_membership, $old_status, $new_status )
	{
		$user_id 				= $user_membership->user_id;
		$memberships			= wc_memberships_get_user_memberships( $user_id );
		$active_memberships 	= [];
		foreach ($memberships as $key => $membership) {
			$active_memberships[] = $membership->get_status();			
		}
		$access_token         	= sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		if ( $access_token ) {			
			if( in_array( 'active', $active_memberships ) )
			{
				$atm_saviorpro_discord_role_mapping 	= json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
				if( !empty( $atm_saviorpro_discord_role_mapping ) && is_array( $atm_saviorpro_discord_role_mapping ) )
				{
					foreach ( $atm_saviorpro_discord_role_mapping as $key => $role_id ) {
						$this->atm_saviorpro_discord_as_handler_put_memberrole( $user_id, $role_id, false );
					}
				}
			}else{
				if( !empty( $this->atm_saviorpro_discord_assigned_member_role_ids( $user_id ) ) )
				{
					foreach ( $this->atm_saviorpro_discord_assigned_member_role_ids( $user_id ) as $key => $atm_role_id ) {
						$this->atm_saviorpro_discord_as_handler_delete_memberrole( $user_id, $atm_role_id );
					}
				}
			}
		}		
	}

	/**
	 * @since
	 * 1.3.8
	 * @param
	 * /WC_Memberships_Membership_Plan $membership_plan The plan that user was granted access to
	 *	@param
	 *	array $args 
	 *	@type int|string $user_id user ID for the membership
	 *	@type int|string $user_membership_id post ID for the new user membership
	 *	@type bool $is_update true if the membership is being updated, false if new
	*/
	public function atm_saviorpro_memberships_saved( $membership_plan, $args )
	{
		if( !empty($membership_plan) )
		{
			$user_id 				= $args['user_id'];
			$memberships			= wc_memberships_get_user_memberships( $user_id );
			$active_memberships 	= [];
			foreach ($memberships as $key => $membership) {
				$active_memberships[] = $membership->get_status();			
			}
			$access_token         	= sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
			if ( $access_token ) {			
				$atm_saviorpro_discord_role_mapping 	= json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
				$membership_asgin_discod_role_id 		= $atm_saviorpro_discord_role_mapping['saviorpro_level_id_' . $membership_plan->get_id() ];
				if( (bool) $args['is_update'] )
				{
					if( in_array( 'active', $active_memberships ) )
					{	
	
						$this->atm_saviorpro_discord_as_handler_put_memberrole( $user_id, $membership_asgin_discod_role_id, false );
					}else{
	
						$this->atm_saviorpro_discord_as_handler_delete_memberrole( $user_id, $membership_asgin_discod_role_id );
					}
				}else{
					$this->atm_saviorpro_discord_as_handler_put_memberrole( $user_id, $membership_asgin_discod_role_id, false );
				}
			}else{
			}
		}
	}

	/**
	 * Discord DM a member using bot.
	 *
	 * @param INT    $user_id
	 * @param STRING $type (warning|expired)
	 */
	public function atm_saviorpro_discord_handler_send_dm( $user_id, $membership_level_id, $type = 'warning' ) {
		$discord_user_id                              = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
		$discord_bot_token                            = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$atm_saviorpro_discord_expiration_warning_message = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_expiration_warning_message' ) ) );
		$atm_saviorpro_discord_expiration_expired_message = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_expiration_expired_message' ) ) );
		$atm_saviorpro_discord_welcome_message            = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_welcome_message' ) ) );
		$atm_saviorpro_discord_cancel_message             = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_cancel_message' ) ) );
		$embed_messaging_feature                      = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_embed_messaging_feature' ) ) );
		// Check if DM channel is already created for the user.
		$user_dm = get_user_meta( $user_id, '_atm_saviorpro_discord_dm_channel', true );

		if ( ! isset( $user_dm['id'] ) || $user_dm === false || empty( $user_dm ) ) {
			$this->atm_saviorpro_discord_create_member_dm_channel( $user_id );
			$user_dm       = get_user_meta( $user_id, '_atm_saviorpro_discord_dm_channel', true );
			$dm_channel_id = $user_dm['id'];
		} else {
			$dm_channel_id = $user_dm['id'];
		}

		// if ( $type == 'warning' ) {
		// 	update_user_meta( $user_id, '_atm_saviorpro_discord_expitration_warning_dm_for_' . $membership_level_id, true );
		// 	$message = atm_saviorpro_discord_get_formatted_dm( $user_id, $membership_level_id, $atm_saviorpro_discord_expiration_warning_message );
		// }
		// if ( $type == 'expired' ) {
		// 	update_user_meta( $user_id, '_atm_saviorpro_discord_expired_dm_for_' . $membership_level_id, true );
		// 	$message = atm_saviorpro_discord_get_formatted_dm( $user_id, $membership_level_id, $atm_saviorpro_discord_expiration_expired_message );
		// }
		// if ( $type == 'welcome' ) {
		// 	update_user_meta( $user_id, '_atm_saviorpro_discord_welcome_dm_for_' . $membership_level_id, true );
		// 	$message = atm_saviorpro_discord_get_formatted_dm( $user_id, $membership_level_id, $atm_saviorpro_discord_welcome_message );
		// }

		// if ( $type == 'cancel' ) {
		// 	update_user_meta( $user_id, '_atm_saviorpro_discord_cancel_dm_for_' . $membership_level_id, true );
		// 	$message = atm_saviorpro_discord_get_formatted_dm( $user_id, $membership_level_id, $atm_saviorpro_discord_cancel_message );
		// }

		$creat_dm_url = ATM_DISCORD_API_URL . '/channels/' . $dm_channel_id . '/messages';
		if ( $embed_messaging_feature ) {
			$dm_args = array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
				'body'    => atm_saviorpro_disocrd_get_rich_embed_message( trim( $message ) ),

			);
		} else {
			$dm_args = array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
				'body'    => json_encode(
					array(
						'content' => sanitize_text_field( trim( wp_unslash( $message ) ) ),
					)
				),
			);
		}
		$dm_response = wp_remote_post( $creat_dm_url, $dm_args );
		atm_saviorpro_discord_log_api_response( $user_id, $creat_dm_url, $dm_args, $dm_response );
		$dm_response_body = json_decode( wp_remote_retrieve_body( $dm_response ), true );
		if ( atm_saviorpro_discord_check_api_errors( $dm_response ) ) {
			SaviorPro_Discord_Logs::write_api_response_logs( $dm_response_body, $user_id, debug_backtrace()[0] );
			// this should be catch by Action schedule failed action.
			throw new Exception( 'Failed in function atm_saviorpro_discord_send_dm' );
		}
	}

	/**
	 * Get discord channel by channel ID
	 *
	 * @param INT $user_id
	 * @param INT $channel_id
	 */
	private function atm_saviorpro_discord_get_dm_channel( $user_id, $channel_id ) {
		$discord_bot_token       = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$get_channel_url         = ATM_DISCORD_API_URL . '/channels/' . $channel_id;
		$get_channel_args        = array(
			'method'  => 'GET',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
		);
		$response_arr            = wp_remote_get( $get_channel_url, $get_channel_args );
		$getchannel_response_arr = json_decode( wp_remote_retrieve_body( $response_arr ), true );
		return $getchannel_response_arr;
	}


	/**
	 * Create DM channel for a give user_id
	 *
	 * @param INT $user_id
	 * @return MIXED
	 */
	public function atm_saviorpro_discord_create_member_dm_channel( $user_id ) {
		$discord_user_id       = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
		$discord_bot_token     = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$create_channel_dm_url = ATM_DISCORD_API_URL . '/users/@me/channels';
		$dm_channel_args       = array(
			'method'  => 'POST',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
			'body'    => json_encode(
				array(
					'recipient_id' => $discord_user_id,
				)
			),
		);

		$created_dm_response = wp_remote_post( $create_channel_dm_url, $dm_channel_args );
		atm_saviorpro_discord_log_api_response( $user_id, $create_channel_dm_url, $dm_channel_args, $created_dm_response );
		$response_arr = json_decode( wp_remote_retrieve_body( $created_dm_response ), true );

		if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
			// check if there is error in create dm response
			if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) ) {
				SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( atm_saviorpro_discord_check_api_errors( $created_dm_response ) ) {
					// this should be catch by Action schedule failed action.
					throw new Exception( 'Failed in function atm_saviorpro_discord_create_member_dm_channel' );
				}
			} else {
				update_user_meta( $user_id, '_atm_saviorpro_discord_dm_channel', $response_arr );
			}
		}
		return $response_arr;
	}

	/**
	 * Check if the failed action is the SAVIORPRO Discord Add-on and re-schedule it
	 *
	 * @param INT            $action_id
	 * @param OBJECT         $e
	 * @param OBJECT context
	 * @return NONE
	 */
	public function atm_saviorpro_discord_reschedule_failed_action( $action_id, $e, $context ) {
		// First check if the action is for SAVIORPRO discord.
		$action_data = atm_saviorpro_discord_as_get_action_data( $action_id );
		if ( $action_data !== false ) {
			$hook              = $action_data['hook'];
			$args              = json_decode( $action_data['args'] );
			$retry_failed_api  = sanitize_text_field( trim( get_option( 'atm_saviorpro_retry_failed_api' ) ) );
			$hook_failed_count = atm_saviorpro_discord_count_of_hooks_failures( $hook );
			$retry_api_count   = absint( sanitize_text_field( trim( get_option( 'atm_saviorpro_retry_api_count' ) ) ) );
			if ( $hook_failed_count < $retry_api_count && $retry_failed_api == true && $action_data['as_group'] == ATM_DISCORD_AS_GROUP_NAME && $action_data['status'] = 'failed' ) {
				as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), $hook, array_values( $args ), 'atm-saviorpro-discord' );
			}
		}
	}
	/**
	 * Create authentication token for discord API
	 *
	 * @param STRING $code
	 * @param INT    $user_id
	 * @return OBJECT API response
	 */
	public function create_discord_auth_token( $code, $user_id ) {		
		$discord_token_api_url = ATM_DISCORD_API_URL . 'oauth2/token';
		if ( ! is_user_logged_in() ) {
			if ( ! empty( $code ) && $user_id == 'new_created' ) {
				$args     = array(
					'method'  => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body'    => array(
						'client_id'     => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_id' ) ) ),
						'client_secret' => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_secret' ) ) ),
						'grant_type'    => 'authorization_code',
						'code'          => $code,
						'redirect_uri'  => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_redirect_url' ) ) ),
					),
				);
				$response = wp_remote_post( $discord_token_api_url, $args );
				return $response;
			} else {
				wp_send_json_error( 'Unauthorized user', 401 );
				exit();
			}
		}

		// stop users who having the direct URL of discord Oauth.
		// We must check IF NONE members is set to NO and user having no active membership.
		$allow_none_member = sanitize_text_field( trim( get_option( 'atm_saviorpro_allow_none_member' ) ) );
		$curr_level_id     = sanitize_text_field( trim( atm_saviorpro_discord_get_current_level_id( $user_id ) ) );

		if ( $curr_level_id == null && $allow_none_member == 'no' ) {
			return;
		}
		$response          = '';
		$refresh_token     = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_refresh_token', true ) ) );
		$pre_token         = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		$token_expiry_time = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_expires_in', true ) ) );
		if ( $refresh_token && $pre_token ) {
			$date              = new DateTime();
			$current_timestamp = $date->getTimestamp();

			if ( $current_timestamp > $token_expiry_time ) {
				$args     = array(
					'method'  => 'POST',
					'headers' => array(
						'Content-Type' => 'application/x-www-form-urlencoded',
					),
					'body'    => array(
						'client_id'     => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_id' ) ) ),
						'client_secret' => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_secret' ) ) ),
						'grant_type'    => 'refresh_token',
						'refresh_token' => $refresh_token,
						'redirect_uri'  => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_redirect_url' ) ) ),
						'scope'         => ATM_DISCORD_OAUTH_SCOPES,
					),
				);
				$response = wp_remote_post( $discord_token_api_url, $args );
				atm_saviorpro_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
				if ( atm_saviorpro_discord_check_api_errors( $response ) ) {
					$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
					SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				}
			}
		} else {
			$args     = array(
				'method'  => 'POST',
				'headers' => array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				),
				'body'    => array(
					'client_id'     => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_id' ) ) ),
					'client_secret' => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_secret' ) ) ),
					'grant_type'    => 'authorization_code',
					'code'          => $code,
					'redirect_uri'  => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_redirect_url' ) ) ),
				),
			);
			$response = wp_remote_post( $discord_token_api_url, $args );
			atm_saviorpro_discord_log_api_response( $user_id, $discord_token_api_url, $args, $response );
			if ( atm_saviorpro_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			}
		}
		return $response;
	}

	/**
	 * Get Discord user details from API
	 *
	 * @param STRING $access_token
	 * @return OBJECT REST API response
	 */
	public function get_discord_current_user( $access_token ) {
		if ( $access_token ) {
			$discord_cuser_api_url = ATM_DISCORD_API_URL . 'users/@me';
			$param                 = array(
				'headers' => array(
					'Content-Type'  => 'application/x-www-form-urlencoded',
					'Authorization' => 'Bearer ' . $access_token,
				),
			);
			$user_response         = wp_remote_get( $discord_cuser_api_url, $param );
			$response_arr          = json_decode( wp_remote_retrieve_body( $user_response ), true );
			$user_id               = get_current_user_id();
			if ( $user_id ) {
				atm_saviorpro_discord_log_api_response( $user_id, $discord_cuser_api_url, $param, $user_response );
				SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			}

			$user_body = json_decode( wp_remote_retrieve_body( $user_response ), true );
			return $user_body;
		} else {
			return '';
		}
	}

	/**
	 * Add new member into discord guild
	 *
	 * @param INT    $_atm_saviorpro_discord_user_id
	 * @param INT    $user_id
	 * @param STRING $access_token
	 * @return NONE
	 */
	private function add_discord_member_in_guild( $_atm_saviorpro_discord_user_id, $user_id, $access_token ) {
		$curr_level_id = sanitize_text_field( trim( atm_saviorpro_discord_get_current_level_id( $user_id ) ) );
		if ( $curr_level_id !== null ) {
			// It is possible that we may exhaust API rate limit while adding members to guild, so handling off the job to queue.
			as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_handle_add_member_to_guild', array( $_atm_saviorpro_discord_user_id, $user_id, $access_token ), ATM_DISCORD_AS_GROUP_NAME );
		}
	}

	/**
	 * Method to add new members to discord guild.
	 *
	 * @param INT    $_atm_saviorpro_discord_user_id
	 * @param INT    $user_id
	 * @param STRING $access_token
	 * @return NONE
	 */
	public function atm_saviorpro_discord_as_handler_add_member_to_guild( $_atm_saviorpro_discord_user_id, $user_id, $access_token ) {
		// Since we using a queue to delay the API call, there may be a condition when a member is delete from DB. so put a check.
		if ( get_userdata( $user_id ) === false ) {
			return;
		}

		$guild_id                          = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
		$discord_bot_token                 = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$default_role                      = sanitize_text_field( trim( get_option( '_atm_saviorpro_discord_default_role_id' ) ) );
		$atm_saviorpro_discord_role_mapping= json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
		$discord_role                      = '';
		$discord_roles                     = [];
		$curr_level_id                     = sanitize_text_field( trim( atm_saviorpro_discord_get_current_level_id( $user_id ) ) );
		$atm_saviorpro_discord_send_welcome_dm = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_send_welcome_dm' ) ) );

		if ( isset( $curr_level_id ) && is_array( $atm_saviorpro_discord_role_mapping ) && array_key_exists( 'saviorpro_level_id_' . $curr_level_id, $atm_saviorpro_discord_role_mapping ) ) {
			$discord_role = sanitize_text_field( trim( $atm_saviorpro_discord_role_mapping[ 'saviorpro_level_id_' . $curr_level_id ] ) );
		} elseif ( $discord_role = '' && $default_role ) {
			$discord_role = $default_role;
		}

		$discord_assigned_member_role_ids = $this->atm_saviorpro_discord_assigned_member_role_ids( $user_id );
		if( !empty( $discord_assigned_member_role_ids ) && is_array( $discord_assigned_member_role_ids ) )
		{
			$discord_roles = array_unique( array_merge( $discord_assigned_member_role_ids, array( $default_role ) ) );
			update_user_meta( $user_id, '_atm_saviorpro_discord_role_ids', $discord_roles );
			if( in_array( 1039884848145379378, $discord_assigned_member_role_ids ) || in_array( 1014640051998691409, $discord_assigned_member_role_ids ))
			{
				update_user_meta( $user_id, '_atm_saviorpro_discord_is_premium_plays', true );
			}			
		}else{
			$discord_roles = array_merge( array( $default_role ), array( $discord_role ) );
			$guilds_memeber_api_url = ATM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_atm_saviorpro_discord_user_id;
			$guild_args             = array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
				'body'    => json_encode(
					array(
						'access_token' => $access_token,
						'roles'        => array_unique( $discord_roles ),
					)
				),
			);
			$statuses = array(
				'status' => array( 'active', 'free_trial' )
			);
			$user_active_memberships = wc_memberships_get_user_memberships( $user_id, $statuses );
			if( !empty( $user_active_memberships ) && !empty( array_values( $atm_saviorpro_discord_role_mapping ) ) )
			{
				$discord_roles = array_merge( array( $discord_role ), array( $default_role ) );
				$guild_args['body'] = json_encode(
						array(
							'access_token' => $access_token,
							'roles'        => array_unique( $discord_roles ),
						)
					);
			}
			$guild_response         = wp_remote_post( $guilds_memeber_api_url, $guild_args );
			atm_saviorpro_discord_log_api_response( $user_id, $guilds_memeber_api_url, $guild_args, $guild_response );
			if ( atm_saviorpro_discord_check_api_errors( $guild_response ) ) {
				
				$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
				SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				// this should be catch by Action schedule failed action.
				throw new Exception( 'Failed in function atm_as_handler_add_member_to_guild' );
			}
	
			update_user_meta( $user_id, '_atm_saviorpro_discord_role_id', $discord_role );
			$discord_roles = array_merge( $discord_roles, $discord_assigned_member_role_ids);

			update_user_meta( $user_id, '_atm_saviorpro_discord_role_ids', $discord_roles );
			if( in_array( 1039884848145379378, $discord_roles ) || in_array( 1014640051998691409, $discord_roles ))
			{
				update_user_meta( $user_id, '_atm_saviorpro_discord_is_premium_plays', true );
			}
// 			if ( empty( $discord_roles ) ) {
// 				$this->put_discord_role_api( $user_id, $default_role );
// 			}
	
			// if ( $default_role && $default_role != 'none' && isset( $user_id ) ) {
			// 	$this->put_discord_role_api( $user_id, $default_role );
			// }
			if ( empty( get_user_meta( $user_id, '_atm_saviorpro_discord_join_date', true ) ) ) {
				update_user_meta( $user_id, '_atm_saviorpro_discord_join_date', current_time( 'Y-m-d H:i:s' ) );
			}
	
			// Send welcome message.
			// if ( $atm_saviorpro_discord_send_welcome_dm == true ) {
			// 	as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_send_dm', array( $user_id, $curr_level_id, 'welcome' ), 'atm-saviorpro-discord' );
			// }
		}
	}
	/**
	 * Add new member into discord guild
	 *
	 * @return OBJECT REST API response
	 */
	public function atm_saviorpro_discord_load_discord_roles() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['atm_discord_nonce'], 'atm-discord-ajax-nonce' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$user_id = get_current_user_id();

		$guild_id          = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
		$discord_bot_token = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		if ( $guild_id && $discord_bot_token ) {
			$discod_server_roles_api = ATM_DISCORD_API_URL . 'guilds/' . $guild_id . '/roles';
			$guild_args              = array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
			);
			$guild_response          = wp_remote_post( $discod_server_roles_api, $guild_args );

			atm_saviorpro_discord_log_api_response( $user_id, $discod_server_roles_api, $guild_args, $guild_response );

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );

			if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
				if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) ) {
					SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				} else {
					$response_arr['previous_mapping'] = get_option( 'atm_saviorpro_discord_role_mapping' );

					$discord_roles = array();
					foreach ( $response_arr as $key => $value ) {
						$isbot = false;
						if ( is_array( $value ) ) {
							if ( array_key_exists( 'tags', $value ) ) {
								if ( array_key_exists( 'bot_id', $value['tags'] ) ) {
									$isbot = true;
								}
							}
						}
						if ( $key != 'previous_mapping' && $isbot == false && isset( $value['name'] ) && $value['name'] != '@everyone' ) {
							$discord_roles[ $value['id'] ]       = $value['name'];
							$discord_roles_color[ $value['id'] ] = $value['color'];
						}
					}
					update_option( 'atm_saviorpro_discord_all_roles', serialize( $discord_roles ) );
					update_option( 'atm_saviorpro_discord_roles_color', serialize( $discord_roles_color ) );
				}
			}
				return wp_send_json( $response_arr );
		}

	}

	/**
	 * Get all role to assigned discord guild member
	 *
	 * @return OBJECT REST API response
	 */
	public function atm_saviorpro_discord_assigned_member_role_ids( $user_id = false) {
		if ( $user_id == false ) {
			$user_id = get_current_user_id();
		}

		$guild_id          					= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
		$discord_bot_token 					= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$_atm_saviorpro_discord_user_id		= sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
		$_atm_saviorpro_discord_role_ids 	= [];
		if ( $guild_id && $discord_bot_token && !empty( $_atm_saviorpro_discord_user_id ) && is_numeric( $_atm_saviorpro_discord_user_id ) ) {
			$discod_server_roles_api = ATM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_atm_saviorpro_discord_user_id;
			$guild_args              = array(
				'method'  => 'GET',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bot ' . $discord_bot_token,
				),
			);
			$guild_response          = wp_remote_post( $discod_server_roles_api, $guild_args );
			atm_saviorpro_discord_log_api_response( $user_id, $discod_server_roles_api, $guild_args, $guild_response );

			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			
			if ( is_array( $response_arr ) && ! empty( $response_arr ) ) {
				if ( array_key_exists( 'code', $response_arr ) || array_key_exists( 'error', $response_arr ) && array_key_exists( 'code', $response_arr ) ) {
					SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
					return $_atm_saviorpro_discord_role_ids;
				}else{
					$_atm_saviorpro_discord_role_ids =  $response_arr['roles'];
				}				
			}
		}
		return $_atm_saviorpro_discord_role_ids;
	}

	/*
	* Get action from $_GET['action']
	*/
	public function atm_saviorpro_discord_act_on_url_action() {
		// when discord-login initiated
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'discord-login' ) {
			$params                    = array(
				'client_id'     => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_id' ) ) ),
				'redirect_uri'  => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_redirect_url' ) ) ),
				'response_type' => 'code',
				'scope'         => 'identify email connections guilds guilds.join',
			);
			$discord_authorise_api_url = ATM_DISCORD_API_URL . 'oauth2/authorize?' . http_build_query( $params );
			// cache the url param for 1 minute
			if ( isset( $_GET['url'] ) ) {
				setcookie( 'atm_discord_page', $_GET['url'], time() + 60, '/' );
			}
			wp_redirect( $discord_authorise_api_url, 302, get_site_url() );
			exit;
		}
		// when admin initiated bot connection
		if ( isset( $_GET['action'] ) && $_GET['action'] == 'discord-connectToBot' ) {
			if ( ! current_user_can( 'administrator' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
			}
			$params                    = array(
				'client_id'   => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_id' ) ) ),
				'permissions' => ATM_DISCORD_BOT_PERMISSIONS,
				'scope'       => 'bot',
				'guild_id'    => sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) ),
			);
			$discord_authorise_api_url = ATM_DISCORD_API_URL . 'oauth2/authorize?' . http_build_query( $params );

			wp_redirect( $discord_authorise_api_url, 302, get_site_url() );
			exit;
		}
	}

	/**
	 * For authorization process call discord API
	 *
	 * @param NONE
	 * @return OBJECT REST API response
	 */
	public function atm_saviorpro_discord_discord_api_callback() {		
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
			if ( isset( $_GET['code'] ) && isset( $_GET['via'] ) && $_GET['via'] == 'discord' ) {
				$code     = sanitize_text_field( trim( $_GET['code'] ) );
				$response = $this->create_discord_auth_token( $code, $user_id );
				if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
					$res_body = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( is_array( $res_body ) ) {
						if ( array_key_exists( 'access_token', $res_body ) ) {
							$access_token 		= sanitize_text_field( trim( $res_body['access_token'] ) );
							$user_body          = $this->get_discord_current_user( $access_token );
							$discord_user_email = $user_body['email'];
							$current_user 		= wp_get_current_user();
							$wp_user_email		= $current_user->user_email;
							if( $wp_user_email == $discord_user_email )
							{
								$this->catch_discord_auth_callback( $res_body, $user_id );
								// Method `catch_discord_auth_callback` set the usermeta key _atm_saviorpro_discord_user_id, accessed in below line
								$discord_user_id = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
								$this->add_discord_member_in_guild( $discord_user_id, $user_id, $access_token );
							}else{
								echo "<script>window.addEventListener('load', (event) => { jQuery('<div style=\'color:red;\'>Please connect with same email ".$wp_user_email."</div>').insertBefore('.saviorpro-btn-connect');});</script>";
							}
						}
					}
				}
			}
		} else {
			if ( isset( $_GET['code'] ) && isset( $_GET['via'] ) && $_GET['via'] == 'discord' ) {
				$code     = sanitize_text_field( trim( $_GET['code'] ) );
				$response = $this->create_discord_auth_token( $code, 'new_created' );
				if ( ! empty( $response ) && ! is_wp_error( $response ) ) {
					$res_body = json_decode( wp_remote_retrieve_body( $response ), true );
					if ( is_array( $res_body ) ) {
						if ( array_key_exists( 'access_token', $res_body ) ) {
							$access_token       = sanitize_text_field( trim( $res_body['access_token'] ) );
							$user_body          = $this->get_discord_current_user( $access_token );
							$discord_user_email = $user_body['email'];
							$password           = wp_generate_password( 12, true, false );
							if ( email_exists( $discord_user_email ) ) {
								$current_user 	= get_user_by( 'email', $discord_user_email );
								$user_id      	= $current_user->ID;
							} else {
								$user_id = wp_create_user( $discord_user_email, $password, $discord_user_email );
								//wp_new_user_notification( $user_id, null, null );
								$user = new WP_User($user_id);
 
								$user_login = stripslashes($user->user_login);
								$user_email = stripslashes($user->user_email);
								$message  = __('Hi there,') . "<br>";
								$message .= sprintf(__("Welcome to %s! Here's how to log in:"), get_option('blogname')) . "<br><br>";
								$message .= site_url('login') . "<br><br>";
								$message .= sprintf(__('Username: %s'), $user_login) . "<br>";
								$message .= sprintf(__('Password: %s'), $password) . "<br><br>";
								$message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "<br><br>";
								$message .= __('Adios!');
								//$from_user = get_option('admin_email');
								$from_user = 'atmcrewgg@gmail.com';
								$headers = "From: ATMCrew <$from_user>\r\n".
									"MIME-Version: 1.0" . "\r\n" .
									"Content-type: text/html; charset=UTF-8" . "\r\n";

								wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message, $headers);
							}
							$this->catch_discord_auth_callback( $res_body, $user_id );
							$credentials = array(
								'user_login'    => $discord_user_email,
								'user_password' => $password,
							);
							wp_set_auth_cookie( $user_id, false, '', '' );
							wp_signon( $credentials, '' );
							$discord_user_id = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
							$this->add_discord_member_in_guild( $discord_user_id, $user_id, $access_token );
							if ( isset( $_COOKIE['atm_discord_page'] ) ) {
								//wp_safe_redirect( urldecode_deep( $_COOKIE['atm_discord_page'] ) );
								wp_safe_redirect( site_url('dashboard-page') );
								exit();
							}
						}
					}
				}
			}
		}
	}

	/*
	* Method to catch the discord auth response and process it.
	*
	* @param ARRAY $res_body
	*/
	private function catch_discord_auth_callback( $res_body, $user_id ) {
		$discord_exist_user_id = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
		$access_token          = sanitize_text_field( trim( $res_body['access_token'] ) );
		update_user_meta( $user_id, '_atm_saviorpro_discord_access_token', $access_token );
		if ( array_key_exists( 'refresh_token', $res_body ) ) {
			$refresh_token = sanitize_text_field( trim( $res_body['refresh_token'] ) );
			update_user_meta( $user_id, '_atm_saviorpro_discord_refresh_token', $refresh_token );
		}
		if ( array_key_exists( 'expires_in', $res_body ) ) {
			$expires_in = $res_body['expires_in'];
			$date       = new DateTime();
			$date->add( DateInterval::createFromDateString( $expires_in . ' seconds' ) );
			$token_expiry_time = $date->getTimestamp();
			update_user_meta( $user_id, '_atm_saviorpro_discord_expires_in', $token_expiry_time );
		}
		$user_body = $this->get_discord_current_user( $access_token );

		if ( is_array( $user_body ) && array_key_exists( 'discriminator', $user_body ) ) {
			$discord_user_number           = $user_body['discriminator'];
			$discord_user_name             = $user_body['username'];
			$discord_user_name_with_number = $discord_user_name . '#' . $discord_user_number;
			update_user_meta( $user_id, '_atm_saviorpro_discord_username', $discord_user_name_with_number );
		}
		if ( is_array( $user_body ) && array_key_exists( 'id', $user_body ) ) {
			$_atm_saviorpro_discord_user_id = sanitize_text_field( trim( $user_body['id'] ) );
			if ( $discord_exist_user_id == $_atm_saviorpro_discord_user_id ) {
				$_atm_saviorpro_discord_role_id = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_role_id', true ) ) );
				if ( ! empty( $_atm_saviorpro_discord_role_id ) && $_atm_saviorpro_discord_role_id != 'none' ) {
					$this->delete_discord_role( $user_id, $_atm_saviorpro_discord_role_id );
				}
			}
			update_user_meta( $user_id, '_atm_saviorpro_discord_user_id', $_atm_saviorpro_discord_user_id );
		}

	}

	/**
	 * Schedule delete existing user from guild
	 *
	 * @param INT  $user_id
	 * @param BOOL $is_schedule
	 * @param NONE
	 */
	public function delete_member_from_guild( $user_id, $is_schedule = true ) {
		if ( $is_schedule && isset( $user_id ) ) {
			as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_schedule_delete_member', array( $user_id, $is_schedule ), ATM_DISCORD_AS_GROUP_NAME );
		} else {
			if ( isset( $user_id ) ) {
				$this->atm_saviorpro_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule );
			}
		}
	}

	/**
	 * AS Handling member delete from huild
	 *
	 * @param INT  $user_id
	 * @param BOOL $is_schedule
	 * @return OBJECT API response
	 */
	public function atm_saviorpro_discord_as_handler_delete_member_from_guild( $user_id, $is_schedule ) {
		$guild_id                      = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
		$discord_bot_token             = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$_atm_saviorpro_discord_user_id    = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
		$guilds_delete_memeber_api_url = ATM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_atm_saviorpro_discord_user_id;
		$guild_args                    = array(
			'method'  => 'DELETE',
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bot ' . $discord_bot_token,
			),
		);
		$guild_response                = wp_remote_post( $guilds_delete_memeber_api_url, $guild_args );

		atm_saviorpro_discord_log_api_response( $user_id, $guilds_delete_memeber_api_url, $guild_args, $guild_response );
		if ( atm_saviorpro_discord_check_api_errors( $guild_response ) ) {
			$response_arr = json_decode( wp_remote_retrieve_body( $guild_response ), true );
			SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
			if ( $is_schedule ) {
				// this exception should be catch by action scheduler.
				throw new Exception( 'Failed in function atm_saviorpro_discord_as_handler_delete_member_from_guild' );
			}
		}

		/*Delete all usermeta related to discord connection*/
		delete_user_meta( $user_id, '_atm_saviorpro_discord_user_id' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_access_token' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_refresh_token' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_role_id' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_username' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_expires_in' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_role_ids' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_join_date' );
		delete_user_meta( $user_id, '_atm_saviorpro_discord_is_premium_plays' );
	}

	/**
	 * API call to change discord user role
	 *
	 * @param INT  $user_id
	 * @param INT  $role_id
	 * @param BOOL $is_schedule
	 * @return object API response
	 */
	public function put_discord_role_api( $user_id, $role_id, $is_schedule = true ) {
		if ( $is_schedule ) {
			as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_schedule_member_put_role', array( $user_id, $role_id, $is_schedule ), ATM_DISCORD_AS_GROUP_NAME );
		} else {
			$this->atm_saviorpro_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule );
		}
	}

	/**
	 * Action Schedule handler for mmeber change role discord.
	 *
	 * @param INT  $user_id
	 * @param INT  $role_id
	 * @param BOOL $is_schedule
	 * @return object API response
	 */
	public function atm_saviorpro_discord_as_handler_put_memberrole( $user_id, $role_id, $is_schedule ) {
		$access_token                = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true ) ) );
		$guild_id                    = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
		$_atm_saviorpro_discord_user_id  = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
		$discord_bot_token           = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
		$discord_change_role_api_url = ATM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_atm_saviorpro_discord_user_id . '/roles/' . $role_id;

		if ( $access_token && $_atm_saviorpro_discord_user_id ) {
			$param = array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Authorization'  => 'Bot ' . $discord_bot_token,
					'Content-Length' => 0,
				),
			);

			$response = wp_remote_get( $discord_change_role_api_url, $param );
			atm_saviorpro_discord_log_api_response( $user_id, $discord_change_role_api_url, $param, $response );
			if ( atm_saviorpro_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( $is_schedule ) {
					// this exception should be catch by action scheduler.
					throw new Exception( 'Failed in function atm_saviorpro_discord_as_handler_put_memberrole' );
				}
			}
		}
	}

	/**
	 * Schedule delete discord role for a member
	 *
	 * @param INT  $user_id
	 * @param INT  $atm_role_id
	 * @param BOOL $is_schedule
	 * @return OBJECT API response
	 */
	public function delete_discord_role( $user_id, $atm_role_id, $is_schedule = true ) {
		if ( $is_schedule ) {
			as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_schedule_delete_role', array( $user_id, $atm_role_id, $is_schedule ), ATM_DISCORD_AS_GROUP_NAME );
		} else {
			$this->atm_saviorpro_discord_as_handler_delete_memberrole( $user_id, $atm_role_id, $is_schedule );
		}
	}

	/**
	 * Action Schedule handler to process delete role of a member.
	 *
	 * @param INT  $user_id
	 * @param INT  $atm_role_id
	 * @param BOOL $is_schedule
	 * @return OBJECT API response
	 */
	public function atm_saviorpro_discord_as_handler_delete_memberrole( $user_id, $atm_role_id, $is_schedule = true ) {
			$guild_id                    		= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
			$_atm_saviorpro_discord_user_id  	= sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_user_id', true ) ) );
			$discord_bot_token           		= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
			$discord_delete_role_api_url 		= ATM_DISCORD_API_URL . 'guilds/' . $guild_id . '/members/' . $_atm_saviorpro_discord_user_id . '/roles/' . $atm_role_id;
		if ( $_atm_saviorpro_discord_user_id ) {
			$param = array(
				'method'  => 'DELETE',
				'headers' => array(
					'Content-Type'   => 'application/json',
					'Authorization'  => 'Bot ' . $discord_bot_token,
					'Content-Length' => 0,
				),
			);

			$response = wp_remote_request( $discord_delete_role_api_url, $param );
			atm_saviorpro_discord_log_api_response( $user_id, $discord_delete_role_api_url, $param, $response );
			if ( atm_saviorpro_discord_check_api_errors( $response ) ) {
				$response_arr = json_decode( wp_remote_retrieve_body( $response ), true );
				SaviorPro_Discord_Logs::write_api_response_logs( $response_arr, $user_id, debug_backtrace()[0] );
				if ( $is_schedule ) {
					// this exception should be catch by action scheduler.
					throw new Exception( 'Failed in function atm_saviorpro_discord_as_handler_delete_memberrole' );
				}
			}
			return $response;
		}
	}

	/**
	 * Disconnect user from discord
	 *
	 * @param NONE
	 * @return OBJECT JSON response
	 */
	public function atm_saviorpro_discord_disconnect_from_discord() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['atm_discord_nonce'], 'atm-discord-ajax-nonce' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
		}
		$user_id         = sanitize_text_field( trim( $_POST['user_id'] ) );
		$member_kick_out = sanitize_text_field( trim( get_option( 'atm_saviorpro_member_kick_out' ) ) );
		if ( $user_id ) {
			if ( $member_kick_out == true ) {
				$this->delete_member_from_guild( $user_id, false );
			}
			delete_user_meta( $user_id, '_atm_saviorpro_discord_refresh_token' );
			// GH#279
			$default_role                   = sanitize_text_field( trim( get_option( '_atm_saviorpro_discord_default_role_id' ) ) );
			$_atm_saviorpro_discord_role_id = sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_role_id', true ) ) );
			$atm_saviorpro_discord_role_mapping = json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
			$curr_level_id                  = sanitize_text_field( trim( atm_saviorpro_discord_get_current_level_id( $user_id ) ) );
			$previous_default_role          = get_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', true );
			$access_token                   = get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true );
			if ( ! empty( $access_token ) ) {
				// delete already assigned role.
				if ( isset( $_atm_saviorpro_discord_role_id ) && $_atm_saviorpro_discord_role_id != '' && $_atm_saviorpro_discord_role_id != 'none' ) {
					$this->delete_discord_role( $user_id, $_atm_saviorpro_discord_role_id, true );
					delete_user_meta( $user_id, '_atm_saviorpro_discord_role_id', true );
				}
				// Assign role which is saved as default.
				if ( $default_role != 'none' ) {
					if ( isset( $previous_default_role ) && $previous_default_role != '' && $previous_default_role != 'none' ) {
							$this->delete_discord_role( $user_id, $previous_default_role, true );
					}
					delete_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', true );
					$this->put_discord_role_api( $user_id, $default_role, true );
					update_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', $default_role );
				} elseif ( $default_role == 'none' ) {
					if ( isset( $previous_default_role ) && $previous_default_role != '' && $previous_default_role != 'none' ) {
						$this->delete_discord_role( $user_id, $previous_default_role, true );
					}
					update_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', $default_role );
				}
				delete_user_meta( $user_id, '_atm_saviorpro_discord_access_token' );
			}
		}
		$event_res = array(
			'status'  => 1,
			'message' => 'Successfully disconnected',
		);
		wp_send_json( $event_res );
	}

	/**
	 * Disconnect user from discord on delete wp user
	 *
	 * @param NONE
	 * @return OBJECT JSON response
	 */
	public function atm_saviorpro_discord_disconnect_on_delete_user( $user_id, $reassign, $user ) {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}

		if ( $user_id ) {
			$this->delete_member_from_guild( $user_id, false );
			delete_user_meta( $user_id, '_atm_saviorpro_discord_access_token' );
		}

	}

	/**
	 * Manage user roles api calls
	 *
	 * @param NONE
	 * @return OBJECT JSON response
	 */
	public function atm_saviorpro_discord_member_table_run_api() {
		if ( ! is_user_logged_in() && current_user_can( 'edit_user' ) ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}

		// Check for nonce security
		if ( ! wp_verify_nonce( $_POST['atm_discord_nonce'], 'atm-discord-ajax-nonce' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
		}
		$user_id = sanitize_text_field( $_POST['user_id'] );
		$this->atm_saviorpro_discord_set_member_roles( $user_id, false, false, false );

		$event_res = array(
			'status'  => 1,
			'message' => __( 'success', 'saviorpro-discord' ),
		);
		return wp_send_json( $event_res );
	}

	/**
	 * Method to adjust level mapped and default role of a member.
	 *
	 * @param INT  $user_id
	 * @param INT  $expired_level_id
	 * @param INT  $cancel_level_id
	 * @param BOOL $is_schedule
	 */
	private function atm_saviorpro_discord_set_member_roles( $user_id, $expired_level_id = false, $cancel_level_id = false, $is_schedule = true ) {
		$allow_none_member                            		= sanitize_text_field( trim( get_option( 'atm_saviorpro_allow_none_member' ) ) );
		$default_role                                 		= sanitize_text_field( trim( get_option( '_atm_saviorpro_discord_default_role_id' ) ) );
		$_atm_saviorpro_discord_role_id                   	= sanitize_text_field( trim( get_user_meta( $user_id, '_atm_saviorpro_discord_role_id', true ) ) );
		$atm_saviorpro_discord_role_mapping               		= json_decode( get_option( 'atm_saviorpro_discord_role_mapping' ), true );
		$curr_level_id                                		= sanitize_text_field( trim( atm_saviorpro_discord_get_current_level_id( $user_id ) ) );
		$previous_default_role                        		= get_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', true );
		$atm_saviorpro_discord_send_membership_expired_dm 	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_send_membership_expired_dm' ) ) );
		$atm_saviorpro_discord_send_membership_cancel_dm  	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_send_membership_cancel_dm' ) ) );
		$access_token                                 		= get_user_meta( $user_id, '_atm_saviorpro_discord_access_token', true );
		if ( ! empty( $access_token ) ) {
			if ( $expired_level_id ) {
				$curr_level_id = $expired_level_id;
			}
			if ( $cancel_level_id ) {
				$curr_level_id = $cancel_level_id;
			}
			// delete already assigned role.
			if ( isset( $_atm_saviorpro_discord_role_id ) && $_atm_saviorpro_discord_role_id != '' && $_atm_saviorpro_discord_role_id != 'none' ) {
					$this->delete_discord_role( $user_id, $_atm_saviorpro_discord_role_id, $is_schedule );
					delete_user_meta( $user_id, '_atm_saviorpro_discord_role_id', true );
			}
			if ( $curr_level_id !== null ) {
				// Assign role which is mapped to the mmebership level.
				if ( is_array( $atm_saviorpro_discord_role_mapping ) && array_key_exists( 'saviorpro_level_id_' . $curr_level_id, $atm_saviorpro_discord_role_mapping ) ) {
					$mapped_role_id = sanitize_text_field( trim( $atm_saviorpro_discord_role_mapping[ 'saviorpro_level_id_' . $curr_level_id ] ) );
					if ( $mapped_role_id && $expired_level_id == false && $cancel_level_id == false ) {
						$this->put_discord_role_api( $user_id, $mapped_role_id, $is_schedule );
						update_user_meta( $user_id, '_atm_saviorpro_discord_role_id', $mapped_role_id );
					}
				}
			}
			// Assign role which is saved as default.
			if ( $default_role != 'none' ) {
				if ( isset( $previous_default_role ) && $previous_default_role != '' && $previous_default_role != 'none' ) {
						$this->delete_discord_role( $user_id, $previous_default_role, $is_schedule );
				}
				delete_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', true );
				$this->put_discord_role_api( $user_id, $default_role, $is_schedule );
				update_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', $default_role );
			} elseif ( $default_role == 'none' ) {
				if ( isset( $previous_default_role ) && $previous_default_role != '' && $previous_default_role != 'none' ) {
					$this->delete_discord_role( $user_id, $previous_default_role, $is_schedule );
				}
				update_user_meta( $user_id, '_atm_saviorpro_discord_default_role_id', $default_role );
			}

			if ( isset( $user_id ) && $allow_none_member == 'no' && $curr_level_id == null ) {
				$this->delete_member_from_guild( $user_id, false );
			}

			delete_user_meta( $user_id, '_atm_saviorpro_discord_expitration_warning_dm_for_' . $curr_level_id );

			// Send DM about expiry, but only when allow_none_member setting is yes
			// if ( $atm_saviorpro_discord_send_membership_expired_dm == true && $expired_level_id !== false && $allow_none_member = 'yes' ) {
			// 	as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_send_dm', array( $user_id, $expired_level_id, 'expired' ), 'atm-saviorpro-discord' );
			// }

			// // Send DM about cancel, but only when allow_none_member setting is yes
			// if ( $atm_saviorpro_discord_send_membership_cancel_dm == true && $cancel_level_id !== false && $allow_none_member = 'yes' ) {
			// 	as_schedule_single_action( atm_saviorpro_discord_get_random_timestamp( atm_saviorpro_discord_get_highest_last_attempt_timestamp() ), 'atm_saviorpro_discord_as_send_dm', array( $user_id, $cancel_level_id, 'cancel' ), 'atm-saviorpro-discord' );
			// }
		}
	}
	/**
	 * Manage user roles on cancel payment
	 *
	 * @param INT $user_id
	 */
	public function atm_saviorpro_discord_stripe_subscription_deleted( $user_id ) {
		if ( isset( $user_id ) ) {
			$this->atm_saviorpro_discord_set_member_roles( $user_id, false, false, true );
		}
	}

	/**
	 * Manage user roles on subscription  payment failed
	 *
	 * @param ARRAY $old_order
	 */
	public function atm_saviorpro_discord_subscription_payment_failed( $old_order ) {
		$user_id         = $old_order->user_id;
		$atm_payment_fld = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_payment_failed' ) ) );

		if ( $atm_payment_fld == true && isset( $user_id ) ) {
			$this->atm_saviorpro_discord_set_member_roles( $user_id, false, false, true );
		}
	}

	/*
	* Action scheduler method to process expired saviorpro members.
	* @param INT $user_id
	* @param INT $expired_level_id
	*/
	public function atm_saviorpro_discord_as_handler_saviorpro_expiry( $user_id, $expired_level_id ) {
		$this->atm_saviorpro_discord_set_member_roles( $user_id, $expired_level_id, false, true );
	}

	/*
	* Method to process queue of canceled saviorpro members.
	*
	* @param INT $user_id
	* @param INT $level_id
	* @param INT $cancel_level_id
	* @return NONE
	*/
	public function atm_saviorpro_discord_as_handler_saviorpro_cancel( $user_id, $level_id, $cancel_level_id ) {
		$this->atm_saviorpro_discord_set_member_roles( $user_id, false, $cancel_level_id, true );
	}

	/**
	 * Change discord role from admin user edit.
	 *
	 * @param INT $level_id
	 * @param INT $user_id
	 * @param INT $cancel_level
	 * @return NONE
	 */
	public function atm_saviorpro_discord_change_discord_role_from_saviorpro( $level_id, $user_id, $cancel_level ) {
    	$is_schedule = true;
		$is_schedule = apply_filters( 'atm_saviorpro_discord_schedule_change_renew_api_calls', $is_schedule );
		$this->atm_saviorpro_discord_set_member_roles( $user_id, false, false, $is_schedule );
	}
}
new SaviorPro_Discord_API();
