<div class="error-log">
<?php
	$uuid     = get_option( 'atm_saviorpro_discord_uuid_file_name' );
	$filename = $uuid . SaviorPro_Discord_Logs::$log_file_name;
	$handle   = fopen( WP_CONTENT_DIR . '/' . $filename, 'a+' );
  if( $handle ){
    while ( ! feof( $handle ) ) {
      echo fgets( $handle ) . '<br />';
    }
  }
	fclose( $handle );
?>
</div>
<div class="saviorpro-clrbtndiv">
	<div class="form-group">
		<input type="button" class="saviorpro-clrbtn atm-submit atm-bg-red" id="saviorpro-clrbtn" name="saviorpro_clrbtn" value="Clear Logs !">
		<span class="clr-log spinner" ></span>
	</div>
	<div class="form-group">
		<input type="button" class="atm-submit atm-bg-green" value="Refresh" onClick="window.location.reload()">
	</div>
  <div class="form-group">
		<a href="<?php echo esc_attr( content_url('/') . $filename ); ?>" class="atm-submit atm-saviorpro-bg-download" download><?php echo __( 'Download', 'saviorpro-discord'  ); ?></a>
	</div>
	<div class="form-group">
		<a href="<?php echo get_site_url();?>/wp-admin/tools.php?page=action-scheduler&status=pending&s=saviorpro" class="atm-submit atm-bg-green"><?php echo __( 'API Queue', 'saviorpro-discord'  ); ?></a>
	</div>
</div>
