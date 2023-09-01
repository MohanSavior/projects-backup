<?php
$atm_saviorpro_discord_client_id    = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_id' ) ) );
$discord_client_secret          	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_client_secret' ) ) );
$discord_bot_token              	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_bot_token' ) ) );
$atm_saviorpro_discord_redirect_url = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_redirect_url' ) ) );
$atm_discord_roles              	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_role_mapping' ) ) );
$atm_saviorpro_discord_guild_id     = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_guild_id' ) ) );
$current_screen                 	= atm_saviorpro_discord_get_current_screen_url();
?>
<form method="post" action="<?php echo get_site_url() . '/wp-admin/admin-post.php'; ?>">
 <input type="hidden" name="action" value="saviorpro_discord_save_application_details">
 <input type="hidden" name="referrer" value="<?php echo $current_screen; ?>" />
	<?php wp_nonce_field( 'save_discord_settings', 'atm_discord_save_settings' ); ?>
	<div class="atm-input-group">
	  <label><?php echo __( 'Client ID', 'saviorpro-discord' ); ?> :</label>
		<input type="text" class="atm-input" name="atm_saviorpro_discord_client_id" value="<?php if ( isset( $atm_saviorpro_discord_client_id ) ) { echo esc_attr( $atm_saviorpro_discord_client_id ); } ?>" required placeholder="Discord Client ID">
	</div>
	<div class="atm-input-group">
	  <label><?php echo __( 'Client Secret', 'saviorpro-discord' ); ?> :</label>
		<input type="password" class="atm-input" name="atm_saviorpro_discord_client_secret" value="<?php if ( isset( $discord_client_secret ) ) { echo esc_attr( $discord_client_secret ); } ?>" required placeholder="Discord Client Secret">
	</div>
	<div class="atm-input-group">
	  <label><?php echo __( 'Redirect URL', 'saviorpro-discord' ); ?> :</label>
		<input type="text" class="atm-input" name="atm_saviorpro_discord_redirect_url" placeholder="Discord Redirect Url" value="<?php if ( isset( $atm_saviorpro_discord_redirect_url ) ) { echo esc_attr( $atm_saviorpro_discord_redirect_url ); } ?>" required>
		<p class="description"><?php echo __( 'Registered Discord APP URL', 'saviorpro-discord' ); ?>
    <?php if($atm_saviorpro_discord_client_id) {  ?>
	  <a target="_blank" href="<?php echo sprintf( 'https://discord.com/developers/applications/%d/oauth2/general', $atm_saviorpro_discord_client_id ); ?>">Open Discord.com/developers/applications</a>
    <?php } ?>
  </p>
	</div>
	<div class="atm-input-group">
	  <label><?php echo __( 'Bot Token', 'saviorpro-discord' ); ?> :</label>
		<input type="password" class="atm-input" name="atm_saviorpro_discord_bot_token" value="<?php if ( isset( $discord_bot_token ) ) { echo esc_attr( $discord_bot_token ); } ?>" required placeholder="Discord Bot Token">
	</div>
	<div class="atm-input-group">
	  <label><?php echo __( 'Server ID', 'saviorpro-discord' ); ?> :</label>
		<input type="text" class="atm-input" name="atm_saviorpro_discord_guild_id" placeholder="Discord Server Id" value="<?php if ( isset( $atm_saviorpro_discord_guild_id ) ) { echo esc_attr( $atm_saviorpro_discord_guild_id ); } ?>" required>
	</div>
	<?php if ( empty( $atm_saviorpro_discord_client_id ) || empty( $discord_client_secret ) || empty( $discord_bot_token ) || empty( $atm_saviorpro_discord_redirect_url ) || empty( $atm_saviorpro_discord_guild_id ) ) { ?>
	  <p class="atm-danger-text description">
		<?php echo __( 'Please save your form', 'saviorpro-discord' ); ?>
	  </p>
	<?php } ?>
	<p>
	  <button type="submit" name="submit" value="atm_submit" class="atm-submit atm-bg-green">
		<?php echo __( 'Save Settings', 'saviorpro-discord' ); ?>
	  </button>
	  <?php if ( get_option( 'atm_saviorpro_discord_client_id' ) ) : ?>
		<a href="?action=discord-connectToBot" class="atm-btn saviorpro-btn-connect-to-bot" id="saviorpro-connect-discord-bot"><?php echo __( 'Connect your Bot', 'saviorpro-discord' ); ?> <i class='fab fa-discord'></i></a>
	  <?php endif; ?>
	</p>
</form>
