<?php

// shortcode to show the module
  function showmodule_shortcode($moduleid) {
    extract(shortcode_atts(array('id' =>'*'),$moduleid));  
    return do_shortcode('[et_pb_section global_module="'.$id.'"][/et_pb_section]');
}
add_shortcode('showmodule', 'showmodule_shortcode');

// Display current year 
function year_shortcode() {
$year = date_i18n('Y');
return $year; } add_shortcode
(
'year', 'year_shortcode');


add_filter( 'woocommerce_single_product_zoom_options', 'custom_single_product_zoom_options', 10, 3 );function custom_single_product_zoom_options( $zoom_options ) { $zoom_options['magnify'] = 0;    return $zoom_options;}