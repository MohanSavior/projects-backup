<?php
$user_id             = sanitize_text_field( trim( get_current_user_id() ) );
$saviorpro_levels    = wc_memberships_get_membership_plans();

$membership_level    = atm_saviorpro_discord_get_current_level_id( $user_id );
$default_role        = sanitize_text_field( trim( get_option( '_atm_saviorpro_discord_default_role_id' ) ) );
$allow_none_member_s = sanitize_text_field( trim( get_option( 'atm_saviorpro_allow_none_member' ) ) );
$current_screen = atm_saviorpro_discord_get_current_screen_url();
?>
<div class="notice notice-info atm-notice">
    <p>
		<i class='fas fa-info'></i>
        <?php echo __( 'Make sure the BOT role has high priority in the discord.com server than the roles it is supposed to manage.', 'saviorpro-discord' ); ?>
    </p>
</div>
<div class="notice notice-warning atm-notice">
    <p>
		<i class='fas fa-info'></i>
        <?php echo __( 'Drag and Drop the Discord Roles over to the SAVIORPRO Levels', 'saviorpro-discord' ); ?>
	</p>
</div>
<div class="notice notice-warning atm-notice">
    <p><i class='fas fa-info'></i> <?php echo __( 'Note: Inactive levels will not display', 'saviorpro-discord' ); ?>
    </p>
</div>
<div class="row-container">
    <div class="atm-column saviorpro-discord-roles-col">
        <h2><?php echo __( 'Discord Roles', 'saviorpro-discord' ); ?></h2>
        <hr>
        <div class="saviorpro-discord-roles">
            <span class="spinner"></span>
        </div>
    </div>
    <div class="atm-column">
        <h2><?php echo __( 'ATMCREW Membership Levels', 'saviorpro-discord' ); ?></h2>
        <hr>
        <div class="saviorpro-levels">
            <?php
				foreach ( $saviorpro_levels as $key => $value ) {
					if ( $value->post->post_status === 'publish' ) :
						?>
						<div class="makeMeDroppable" data-saviorpro_level_id="<?php echo esc_attr($value->id); ?>">
							<span><?php echo esc_html($value->name); ?></span></div>
						<?php
					endif;
				}
			?>
        </div>
    </div>
</div>
<form method="post" action="<?php echo get_site_url().'/wp-admin/admin-post.php' ?>">
    <input type="hidden" name="action" value="saviorpro_discord_save_role_mapping">
    <input type="hidden" name="referrer" value="<?php echo $current_screen; ?>" />
    <table class="form-table" role="presentation">
        <tbody>
            <tr>
                <th scope="row"><label
                        for="saviorpro-defaultRole"><?php echo __( 'Default Role', 'saviorpro-discord' ); ?></label>
                </th>
                <td>
                    <?php wp_nonce_field( 'discord_role_mappings_nonce', 'atm_saviorpro_discord_role_mappings_nonce' ); ?>
                    <input type="hidden" id="selected_default_role" value="<?php echo esc_attr( $default_role ); ?>">
                    <select id="saviorpro-defaultRole" name="saviorpro_defaultRole">
                        <option value="none"><?php echo __( '-None-', 'saviorpro-discord' ); ?></option>
                    </select>
                    <p class="description">
                        <?php echo __( 'This Role will be assigned to all level members', 'saviorpro-discord' ); ?></p>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php echo __( 'Allow non-members', 'saviorpro-discord' ); ?></label></th>
                <td>
                    <fieldset>
                        <label>
							<input type="radio" name="allow_none_member" value="yes" <?php
									if ( $allow_none_member_s == 'yes' ) {
										echo 'checked="checked"'; }
									?>> 
								<span><?php echo __( 'Yes', 'saviorpro-discord' ); ?></span></label><br>
												<label><input type="radio" name="allow_none_member" value="no" <?php
									if ( empty( $allow_none_member_s ) || $allow_none_member_s == 'no' ) {
										echo 'checked="checked"'; }
									?>> 
								<span><?php echo __( 'No', 'saviorpro-discord' ); ?></span>
						</label>
                        <p class="description">
                            <?php echo __( 'This setting will apply on Cancel and Expiry of Membership', 'saviorpro-discord' ); ?>
                        </p>
                    </fieldset>
                </td>
            </tr>
        </tbody>
    </table>
    <br>
    <div class="mapping-json">
        <textarea id="saviorpro_maaping_json_val" name="atm_saviorpro_discord_role_mapping">
			<?php
			if ( isset( $atm_discord_roles ) ) {
				echo stripslashes( esc_html( $atm_discord_roles ));}
			?>
	</textarea>
    </div>
    <div class="bottom-btn">
        <button type="submit" name="submit" value="atm_submit" class="atm-submit atm-bg-green">
            <?php echo __( 'Save Settings', 'saviorpro-discord' ); ?>
        </button>
        <button id="revertMapping" name="flush" class="atm-submit atm-bg-red">
            <?php echo __( 'Flush Mappings', 'saviorpro-discord' ); ?>
        </button>
    </div>
</form>