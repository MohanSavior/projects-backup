<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://savior.im/
 * @since      1.0.0
 */
// If uninstall not called from WordPress, then exit.
if ( defined( 'WP_UNINSTALL_PLUGIN' )
		&& $_REQUEST['plugin'] == 'saviorpro-discord/saviorpro-discord.php'
		&& $_REQUEST['slug'] == 'saviorpro-discord'
	&& wp_verify_nonce( $_REQUEST['_ajax_nonce'], 'updates' )
  ) {
	global $wpdb;
	  $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . "usermeta WHERE `meta_key` LIKE '_atm_saviorpro_discord%'" );
	  $wpdb->query( 'DELETE FROM ' . $wpdb->prefix . "options WHERE `option_name` LIKE 'atm_saviorpro_discord_%'" );
}

