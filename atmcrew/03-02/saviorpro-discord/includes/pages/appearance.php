<?php
$btn_color                          = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_btn_color' ) ) );
$atm_saviorpro_btn_disconnect_color                          = sanitize_text_field( trim( get_option( 'atm_saviorpro_btn_disconnect_color' ) ) );
$btn_text                        	= sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_loggedout_btn_text' ) ) );
$loggedin_btn_text                  = sanitize_text_field( trim( get_option( 'atm_saviorpro_discord_loggedin_btn_text' ) ) );
$atm_saviorpro_disconnect_btn_text                  = sanitize_text_field( trim( get_option( 'atm_saviorpro_disconnect_btn_text' ) ) );
$current_screen = atm_saviorpro_discord_get_current_screen_url();
?>
<form method="post" action="<?php echo get_site_url().'/wp-admin/admin-post.php' ?>">
 <input type="hidden" name="action" value="saviorpro_discord_save_appearance_settings">
 <input type="hidden" name="referrer" value="<?php echo $current_screen; ?>" />
<?php wp_nonce_field( 'save_discord_aprnc_settings', 'atm_discord_save_aprnc_settings' ); ?>
  <table class="form-table" role="presentation">
	<tbody>
    <tr>
		<th scope="row"><?php echo __( 'Connect/Login Button color', 'saviorpro-discord' ); ?></th>
		<td> <fieldset>
		<input name="atm_saviorpro_btn_color" type="text" id="atm_saviorpro_btn_color" value="<?php if ( $btn_color ) {echo $btn_color; }?>" data-default-color="#5865f2">
		</fieldset></td> 
	</tr>
  <tr>
		<th scope="row"><?php echo __( 'Disconnect Button color', 'saviorpro-discord' ); ?></th>
		<td> <fieldset>
		<input name="atm_saviorpro_btn_disconnect_color" type="text" id="atm_saviorpro_btn_disconnect_color" value="<?php if ( $atm_saviorpro_btn_disconnect_color ) {echo $atm_saviorpro_btn_disconnect_color; }?>" data-default-color="#ff0000">
		</fieldset></td> 
	</tr>
	<tr>
		<th scope="row"><?php echo __( 'Text on the Button for logged-in users', 'saviorpro-discord' ); ?></th>
		<td> <fieldset>
		<input name="atm_saviorpro_loggedin_btn_text" type="text" id="atm_saviorpro_loggedin_btn_text" value="<?php if ( $loggedin_btn_text ) {echo $loggedin_btn_text; }?>">
		</fieldset></td> 
	</tr>
	<tr>
		<th scope="row"><?php echo __( 'Text on the Button for non-login users', 'saviorpro-discord' ); ?></th>
		<td> <fieldset>
		<input name="atm_saviorpro_loggedout_btn_text" type="text" id="atm_saviorpro_loggedout_btn_text" value="<?php if ( $btn_text ) { echo $btn_text; } ?>">
		</fieldset></td> 
	</tr>	
  <tr>
		<th scope="row"><?php echo __( 'Text on the Disconnect Button', 'saviorpro-discord' ); ?></th>
		<td> <fieldset>
		<input name="atm_saviorpro_disconnect_btn_text" type="text" id="atm_saviorpro_disconnect_btn_text" value="<?php if ( $atm_saviorpro_disconnect_btn_text ) { echo $atm_saviorpro_disconnect_btn_text; } ?>">
		</fieldset></td> 
	</tr>	
	</tbody>
  </table>
  <div class="bottom-btn">
	<button type="submit" name="apr_submit" value="atm_submit" class="atm-submit atm-bg-green">
	  <?php echo __( 'Save Settings', 'saviorpro-discord' ); ?>
	</button>
  </div>
</form>
