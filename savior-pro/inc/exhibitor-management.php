<?php
// Define the ExhibitorManagement class
class ExhibitorManagement {
    public function __construct() {
      // Hook into WordPress admin_menu to add the Exhibitor Management page
      add_action('admin_menu', array($this, 'add_exhibitor_management_page'));
  
      // Hook into admin_post action to handle CSV export
      add_action('admin_post_export_exhibitors_csv', array($this, 'export_exhibitors_csv'));
      add_action('wp_ajax_get_exhibitor_members', array($this, 'get_exhibitor_members'));
      add_action('wp_ajax_nopriv_get_exhibitor_members', array($this, 'get_exhibitor_members'));
      add_filter( 'gform_confirmation_16', array($this, 'exhibitor_members_admin_confirmation'), 10, 4 );
      // add_filter( 'gform_after_submission_16', array($this, 'remove_form_entry'), 10 );

      add_filter( 'gform_user_registration_update_user_id', array($this, 'gform_user_registration_update_user_id'), 10, 4 );
      add_filter('woocommerce_payment_complete', array($this, 'exhibitor_members_payment_complete'));

      add_action('admin_head', array($this, 'exhibitor_members_style'));
      add_action('admin_footer', array($this, 'exhibitor_management_scripts'));
      add_action('wp_ajax_update_user_status', array($this, 'update_user_status_callback'));
      // add_action( 'woocommerce_order_status_completed', array( $this, 'exhibitor_members_payment_complete' ) );//order_status_completed
      add_action('wp_ajax_assign_booth_products', array($this, 'assign_booth_products'));
      add_action('wp_ajax_booth_number_current_year', array($this, 'booth_number_current_year'));

      add_filter('acf/validate_value/key=field_6477148a02f19', array($this, 'restrict_repeater_rows'), 10, 4);
      add_action('acf/save_post', array($this, 'save_exhibitor_booth_data_yearly'));
      // add_action('pre_get_posts', array($this, 'filter_repeater_data_yearly'));
      add_filter('acf/load_value/key=field_6477148a02f19', array($this, 'filter_repeater_data_yearly'), 10, 3);
    }

    public function exhibitor_members_style()
    {
      ?>
      <style>
        #exhibitor-members-list_wrapper {
              width: 95%;
              background-color: #fff;
              padding: 15px;
              border-radius: 10px;
              margin-top: 20px;
          }
          div.dt-buttons {
              float: left;
              margin-right: 10px;
          }
          .year-filter {
              /* margin-top: 10px; */
          }
          .dt-buttons {
              display: flex;
              gap: 20px;
          }
          #exhibitor-members-list_length select {
              width: 50%;
          }
          #exhibitor-members-list_wrapper .dt-btn-split-drop {
              margin-left: -2px;
              border: 1px solid rgba(0, 0, 0, 0.3);
          }
          #exhibitor-members-list_wrapper .dt-btn-split-drop:hover {
              border: 1px solid #666;
          }
          #exhibitor-members-list_wrapper .dt-button.buttons-csv.buttons-html5 {
              border-radius: 0;
          }
          #exhibitor-members-list_length {
              width: 10%;
          }
          #exhibitor-container {
            display: flex;gap: 25px;
          }
          #gravity-form-container {
            width: 60%;
          }
          #exhibitor-container .gform_required_legend, #exhibitor-container .gfield--type-column_start, #exhibitor-container .gfield--type-column_end {
              display: none;
          }
          #exhibitor-container .exhibitors-contact-res-heading em {
              font-size: 22px;
              line-height: 32px;
              font-weight: 500;
              font-style: normal;
          }
          #field_15_18 .registration-form-heading {
              padding-bottom: 0;
              background-color: transparent;
          }

          #exhibitor-container div#gform_wrapper_15, #exhibitor-profile div#gform_wrapper_16, #exhibitor-send-invitation div#gform_wrapper_17 {
            background-color: #fff;
            padding: 40px 30px 30px 30px;
            border-radius: 10px;
          }
          #exhibitor-container .gform_title, #exhibitor-profile .gform_title, #exhibitor-send-invitation .gform_title {
              font-size: 30px;
              line-height: 1.5em;
              margin-top: 0;
              text-align: center;
          }
          #exhibitor-container .registration-form-heading, #exhibitor-profile .registration-form-heading {
              font-size: 22px;
              line-height: 32px;
              margin: 0;
              padding: 10px;
              background: #bdbdbd70;
          }
          #exhibitor-container label, #exhibitor-profile label, #field_15_18 .registration-form-heading, #field_16_18 .registration-form-heading, #exhibitor-send-invitation label, #field_17_2 legend {
              padding: 0 0 10px 0;
              margin: 0;
              font-size: 16px;
              font-weight: 600;
          }
          p.exhibitors-contact-res-heading {
              background-color: #bdbdbd70;
              padding: 5px;
          }
          #exhibitor-profile p.gform_required_legend {
              display: none;
          }
          #exhibitor-container .exhibitors-contact-res-heading em, #exhibitor-profile .exhibitors-contact-res-heading em {
              font-size: 22px;
              line-height: 32px;
              font-weight: 400;
              font-style: normal;
          }
          #field_15_18 .registration-form-heading, #field_16_18 .registration-form-heading {
              background: transparent;
              padding: 0;
          }
          #exhibitor-container .registration-required-heading, #exhibitor-profile .registration-required-heading {
              position: absolute;
              right: 0;
              top: 65px;
              font-size: 15px;
              margin: 0;
          }
          #exhibitor-container #gform_submit_button_15, #exhibitor-profile #gform_submit_button_16, #exhibitor-send-invitation #gform_submit_button_17 {
              background: #F7C338;
              color: #080E41;
              border: 0;
              border-radius: 0;
              font-size: 18px;
              padding: 6px 50px;
              margin-bottom: 0;
          }
          #exhibitor-container #gform_submit_button_15:hover, #exhibitor-profile #gform_submit_button_16:hover, #exhibitor-send-invitation #gform_submit_button_17:hover {
              background: #080E41;
              color: #F7C338;
              transition: 0.25s all;
          }
          #exhibitor-container select, #exhibitor-profile select {
              height: 48px;
              padding: 8px;
              box-shadow: 0 0 0 transparent;
              border-radius: 4px;
              border: 1px solid #8c8f94;
              background-color: #fff;
              color: #2c3338;
          }
          #exhibitor-container .gfield--type-choice label, #exhibitor-profile .gfield--type-choice label {
              padding-bottom: 0;
          }
          #exhibitor-members-list_length select {
              padding: 2.5px 5px;
          }
          .exhibitor-profile-wrap {
              display: flex;
              gap: 20px;
          }
          #exhibitor-assistant-container {
              background-color: #fff;
              padding: 40px 30px 30px 30px;
              border-radius: 10px;
          }
          #exhibitor-assistant-container h1 {
              font-size: 30px;
              line-height: 1.5em;
              font-weight: 600;
              padding: 0;
              margin: 0px auto 1em auto !important;
              display: table !important;
          }
          #exhibitor-assistant-container .ui-accordion-header {
              font-size: 16px;
              line-height: 26px;
              padding: 11px 20px;
              margin: 10px 0 0 0;
              background: #F7C338;
              color: #252F86;
              outline: none !important;
              border: 0 !important;
          }
          #exhibitor-assistant-container .ui-accordion-header span {
              padding-left: 2px;
          }
          #exhibitor-assistant-container .ui-accordion-content {
              position: relative;
              padding: 20px 20px !important;
          }
          #exhibitor-assistant-container .assistants-billing-address p, #exhibitor-assistant-container .assistants-billing-address label {
              font-size: 16px;
              line-height: 26px;
              margin: 10px 0px;
              color: #000000;
              font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
          }
          #exhibitor-assistant-container .assistants-billing-address p span {
              font-weight: 600;
          }
          #exhibitor-assistant-container .assistants-billing-address input {
              border-color: #000000;
          }
          #exhibitor-assistant-container .assistant-form label {
              padding: 0 0 10px 0;
              margin: 0;
              font-size: 16px;
              font-weight: 600;
              color: #3c434a;
              font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
          }
          #exhibitor-assistant-container .assistant-form input[type="text"], #exhibitor-assistant-container .assistant-form input[type="email"], #exhibitor-assistant-container .assistant-form select {
              font-size: 15px;
              padding: 8px;
              width: 100%;
              margin-top: 9px;
              outline: none !important;
              box-shadow: none !important;
          }
          #exhibitor-assistant-container .assistant-form input[type="text"]:focus, #exhibitor-assistant-container .assistant-form input[type="email"]:focus, #exhibitor-assistant-container .assistant-form select:focus {
              border-color: #2c3338;
          }
          .assistants-billing-address-update {
              margin-top: 13px;
          }
          .assistants-billing-form-btn {
              background: #F7C338;
              color: #080E41;
              border: 0;
              border-radius: 0;
              font-size: 18px !important;
              padding: 16px 30px;
              margin-bottom: 0;
              cursor: pointer;
          }
          .assistants-billing-form-btn:hover {
              background: #080E41;
              color: #F7C338;
              transition: 0.25s all;
          }
          #exhibitor-assistant-container .assistant-form select {
              color: #2c3338;
          }
          #assistant-spinner {
              position: absolute;
              width: 96.5%;
              height: 100%;
              background: #898989a6;
          }
          #assistant-spinner:before {
              content: "";
              position: absolute;
              width: 50px;
              height: 50px;
              border-radius: 50%;
              border: 2px solid #333;
              border-top-color: transparent;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%);
          }
          #assistant-spinner:before {
              animation: spin 1.5s linear infinite;
              top: 44.8%;
              left: 45.5%;
          }

          @keyframes spin {
            0% {
              transform: rotate(0);
            }
            100% {
              transform: rotate(360deg);
            }
          }
          .assign-booth-products {
              background-color: #fff;
              padding: 40px 30px 30px 30px;
              border-radius: 10px;
              height: max-content;
          }
          .assign-booth-products h1 {
              font-size: 22px;
              line-height: 1.5em;
              font-weight: 600;
              padding: 0;
              margin: 0px auto 1em auto !important;
          }
          #assign-booth-product-exhibitor #booth-products {
              font-size: 16px !important;
              border: 1px solid;
              padding: 10px 5px;
              color: #000000;
              box-shadow: none;
          }
          #send-invoice-assign-booth {
              background: #F7C338;
              color: #080E41;
              border: 0;
              border-radius: 0;
              font-size: 18px !important;
              padding: 6px 20px;
              cursor: pointer;
              margin-top: 20px;
          }
          #send-invoice-assign-booth:hover {
              background: #080E41;
              color: #F7C338;
              transition: 0.25s all;
          }
          .exhibitor-profile-wrap {
              flex-wrap: wrap;
              justify-content: center;
          }
          .exhibitor-profile-wrap .assign-booth-products, .exhibitor-profile-wrap .assign-booth-number-current-year, .exhibitor-profile-wrap .booth-number-container {
              width: 29% !important;
          }
          #exhibitor-profile, #exhibitor-assistant-container {
              width: 45% !important;
          }
          .assign-booth-products {
              background-color: #fff;
              padding: 40px 30px 30px 30px;
              border-radius: 10px;
              height: max-content;
              width: 33%;
              max-width: 100%;
          }
          .assign-booth-products .select2-container--default .select2-selection--multiple {
              padding-bottom: 0px !important;
              width: 100% !important;
          }
          .assign-booth-number-current-year {
              width: 33%;
              background: #FFFFFF;
              padding: 40px 30px 30px 30px;
              border-radius: 10px;
              max-width: 100%;
          }
          .assign-booth-number-current-year input.button , button#add-new-assistant {
              background: #F7C338 !important;
              color: #000000 !important;
              font-weight: 400;
              border: none !important;
              border-radius: 0 !important;
              padding: 4px 13px !important;
              font-size: 13px !important;
          }
          .booth-number-container {
              background: #fff;
              width: 33%;
              padding: 40px 30px 30px 30px;
              border-radius: 10px;
          }
          .assign-booth-number-current-year h1, .booth-number-container h1 {
              color: #1D2327;
              font-size: 22px;
              font-weight: 600;
              line-height: 1.5em;
          }
          .assign-booth-number-current-year label, .assign-booth-number-current-year label, .assign-booth-products p {
              font-size: 16px;
              padding: 0px 0px 10px 0px !important;
              font-weight: 600;
              display: block;
          }
          .booth-number-container h2.booth-years {
              font-size: 16px;
              font-weight: 600;
          }
          div#exhibitor-profile {
              width: 45% !important;
          }
          div#exhibitor-assistant-container {
              width: 50% !important;
          }
          button#hide-new-assistant:hover {
          background: #080E41  !important;
          color:#F7C338 !important;
          }
          .assign-booth-number-current-year input.button:hover , button#add-new-assistant:hover{
          background:#080E41;
          color:#F7C338 !important;
          }
          #exhibitor-assistant-container button.add_repeater_item {
              background: #F7C338;
              color: #080E41;
              font-size: 13px;
              font-weight: 400 !important;
              padding: 4px 13px;
          }
          #exhibitor-assistant-container button.add_repeater_item:hover
          { background:#080E41;
          color:#F7C338 !important;}
          #exhibitor-assistant-container input#gform_submit_button_14 {
              font-size: 18px;
              padding: 6px 20px;
              background: #F7C338;
              border: 0;
              border-radius: 0;
              color: #080E41 !important;
          }
          #exhibitor-assistant-container input#gform_submit_button_14:hover
          { background:#080E41;
          color:#F7C338 !important;}
          button#hide-new-assistant {
              background: #F7C338;
              padding: 5px 13px;
              border: 0;
              font-size: 13px !important;
              font-weight: 400;
              margin-left: 3px;
          }
          button#hide-new-assistant {
              background: #F7C338 !important;
              padding: 4px 13px !important;
              border: 0 !important;
              font-size: 13px !important;
              font-weight: 400 !important;
              margin-left: 3px !important;
          }
          .assign-booth-number-current-year input.button:hover , button#add-new-assistant:hover{
          background:#080E41 !important;
          color:#F7C338 !important;
          }
          .assign-booth-number-current-year input[type=checkbox]:focus, input[type=color]:focus, input[type=date]:focus, input[type=datetime-local]:focus, input[type=datetime]:focus, input[type=email]:focus, input[type=month]:focus, input[type=number]:focus, input[type=password]:focus, input[type=radio]:focus, input[type=search]:focus, input[type=tel]:focus, input[type=text]:focus, input[type=time]:focus, input[type=url]:focus, input[type=week]:focus, select:focus, textarea:focus {
          box-shadow: none !important;
          outline: 0 !important;
          }
          .assign-booth-number-current-year input {
              font-size: 15px;
              padding: 8px;
          width:100% !important
          }
          .booth-number-container li {
              font-size: 15px !important;
              font-weight: 500;
          }
          input.select2-search__field {
              font-size: 15px !important;
              color: #2C3338 !important;
          }
          .assign-booth-number-current-year input:focus {
              border-color: #2C3338;
          }
          #exhibitor-assistant-container .gform_wrapper.gravity-theme .gfield_repeater_wrapper input {
              border: 1px solid #8C8F94;
              border-radius: 4px;
              width: 100%;
          }
          label.gfield_label.gform-field-label {
          color: #1D2327 !important;
              font-size: 16px !important;
              font-weight: 600 !important;
          }
          span.select2.select2-container.select2-container--default {
              width: 100% !important;
          }

          #exhibitor-assistant-container .gform_wrapper.gravity-theme .gfield_repeater_wrapper input:focus {
              border-color: #2C3338;
          }
          li.ui-tabs-tab.ui-corner-top.ui-state-default.ui-tab.ui-tabs-active.ui-state-active {
              background: #F0F0F1;
              border-bottom: 1px solid #F0F0F1 !important;
          }
          li.ui-tabs-tab.ui-corner-top.ui-state-default.ui-tab.ui-tabs-active.ui-state-active a {
              color: #000;
          }
          li.ui-tabs-tab.ui-corner-top.ui-state-default.ui-tab {
              font-size: 14px;
              font-weight: 600;
              padding: 5px 10px;
              line-height:1.71428571;
              margin-left:0.5em;
              border-bottom: 0 !important;
              border-radius: 0 !important;
              border: solid 1px #C3C4C7;
              background:#dcdcde;
          }
          li.ui-tabs-tab.ui-corner-top.ui-state-default.ui-tab a {color:#50575e}
          li.ui-tabs-tab.ui-corner-top.ui-state-default.ui-tab a:hover {color:#3c434a}
          li.ui-tabs-tab.ui-corner-top.ui-state-default.ui-tab a:focus {
              box-shadow: none !important;
              outline: 0 !important;
          }
          .ui-tabs .ui-tabs-nav .ui-tabs-anchor {
              padding: 0 !IMPORTANT;
          }
          ul.ui-tabs-nav.ui-corner-all.ui-helper-reset.ui-helper-clearfix.ui-widget-header {
              padding-top: 25px;
              border-bottom: 1px solid #C3C4C7;
              border-radius: 0 !important;
              background: transparent;
              border-width: 0 0 1px 0 !important;
          }
          .exhibitor-profile-wrap {
              display: block !important;
          }
          .exhibitor-profile-wrap .assign-booth-products, .exhibitor-profile-wrap .assign-booth-number-current-year, .exhibitor-profile-wrap .booth-number-container {
              width: 45% !important;
              display: inline-block;
              vertical-align: top;
          }
          .assign-booth-products p {
              padding: 0px !important;
              margin: 11px 0px !important;
          }
          input[type="number"] {}
          .assign-booth-products {
              border: saddlebrown;
          }
          input[type="number"] {}
          #assign-booth-product-exhibitor input[type="number"] {
              padding: 8px;
              width: 100px;
              margin-top: 10px;
              font-size: 15px;
          }
          input[type="hidden"] {
              font-size: 16px !important;
          }
      </style>
      <?php
    }
    
    public function exhibitor_management_scripts()
    {
      ?>
      <script>
        jQuery(document).ready(function($){
          let boothProducts = document.getElementById('booth-products');
          if(typeof boothProducts !== "undefined")
          {
            $('#booth-products').select2({
              placeholder: 'Select an booth', // Placeholder text
              allowClear: true
            });
          }
          //calculate price
          $('#calculatePrice').on('change', function() {
            var quantity = parseInt($(this).val());
            var price = quantity * parseInt($(this).data('price'));
            $('#totalValue').text(price);
          });
          $('#send-invoice-assign-booth').on('click', function(event){
            event.preventDefault();
            var productsValues = jQuery('#calculatePrice').val();
            
            if (productsValues > 0) {
              $.ajax({
                  url: ajax_object.ajax_url,
                  method: 'POST',
                  data: {
                    action: 'assign_booth_products',
                    products_ids: jQuery('#calculatePrice').data('product_id'),
                    qty: productsValues,
                    customer_id: $('input[name="customer_id"]').val()
                  },
                  beforeSend: function(){
                      $('body').find(`.assign-booth-products`).prepend('<div id="assistant-spinner"></div>');
                  },
                  success: function(response) {
                      if(response.success){
                          $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                          alert('Sent Invoice successfully!');
                      }
                  },
                  error: function(xhr, status, error) {
                      $('body').find(`.assign-booth-products`).find('#assistant-spinner').remove();
                  }
              });
            }else{
              alert('Please select one product at least');
            }            
          });
          //booth_number
          // $('#add_booth_number').on('click', function(event){
          $("#booth_number_current_year").on('submit', function(event){
            event.preventDefault();
            var boothNumberValues = $('input[id*="booth_number_"]').map(function() {
              return { [$(this).attr('name')]: $(this).val() };
            }).get();
            console.log(boothNumberValues);
            if (boothNumberValues.length > 0) {
              $.ajax({
                  url: ajax_object.ajax_url,
                  method: 'POST',
                  data: {
                    action: 'booth_number_current_year',
                    booth_numbers: boothNumberValues,
                    customer_id: $('input[name="customer_id"]').val()
                  },
                  beforeSend: function(){
                      $('body').find(`.assign-booth-number-current-year`).prepend('<div id="assistant-spinner"></div>');
                  },
                  success: function(response) {
                      if(response.success){
                          $('#booth_number_current_year')[0].reset();
                          $('body').find(`.assign-booth-number-current-year`).find('#assistant-spinner').remove();
                          alert('Added Booth counts successfully!');
                          $('body').find('.booth-number-log').html(response.data.data);
                      }
                  },
                  error: function(xhr, status, error) {
                      $('body').find(`.assign-booth-number-current-year`).find('#assistant-spinner').remove();
                  }
              });
            }else{
              alert('Please add one booth count at least');
            }            
          });
          //Add new assistant 
          $('#add-new-assistant').on('click', function(){
              $('.add-assistant-form').slideDown();
              $('#hide-new-assistant').show();
          });
          $('#hide-new-assistant').on('click', function(){
              $('.add-assistant-form').slideUp();
              $(this).hide();
          });
          
          //Tabs
          $( "#tabs" ).tabs();
          $( "#booth-admin-tabs" ).tabs();//
        });
      </script>
      <?php
    }
    public function gform_user_registration_update_user_id( $user_id, $entry, $form, $feed )
    {
      $user_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : $user_id;
      $key = 'ID';
      $query_arg = 'exhibitor_id';
      $field_id = '31';
      $value = rgar( $entry, $field_id, rgget( $query_arg ) );
      if ( empty( $value ) ) {
          return $user_id;
      }
      $user = get_user_by( $key, $value );
      if ( $user ) {
          return $user->ID;
      }
      return $user_id;
    }
    // Callback function to add the Exhibitor Management page
    public function add_exhibitor_management_page() {
      add_menu_page(
        'All ASGMT Exhibits', //$page_title
        'ASGMT Exhibits', //$menu_title 
        'manage_options', //$capability
        'exhibitor-management', //$menu_slug 
        array( $this, 'exhibitor_management_page_content' ),//$callback 
        'dashicons-groups',//$icon_url
        10 //$position
      );
      add_submenu_page(
        'exhibitor-management', // Parent menu slug (assumed 'exhibitor' is the custom post type)
        'Add New Exhibitor',        // Page title
        'Add New Exhibitor',            // Menu title
        'manage_options',               // Capability required to access the page
        'add-new-exhibitor',            // Menu slug
        array( $this, 'display_add_exhibitor_page' )    // Callback function to display the page content
      );
      add_submenu_page(
        'exhibitor-management', // Parent menu slug (assumed 'exhibitor' is the custom post type)
        'Invite Exhibitor',        // Page title
        'Invite Exhibitor\'s',            // Menu title
        'manage_options',               // Capability required to access the page
        'invite-exhibit',            // Menu slug
        array( $this, 'send_invitation_exhibitor' )    // Callback function to display the page content
      );
      add_submenu_page(
        'exhibitor-management', // Parent menu slug (assumed 'exhibitor' is the custom post type)
        'Exhibitor Profiles',        // Page title
        'Exhibitor Profiles',            // Menu title
        'manage_options',               // Capability required to access the page
        'edit-exhibitor-profile',            // Menu slug
        array( $this, 'exhibitor_profiles_admin_page_content' )    // Callback function to display the page content
      );
    }
  
    // Callback function to display the Exhibitor Management page content
    public function exhibitor_management_page_content() {
      // ob_start();
      // Enqueue DataTables scripts and styles
      wp_enqueue_script('jquery');
      wp_enqueue_style('datatables-management', '//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), '1.13.4');
      wp_enqueue_style('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css', array(), '2.3.6');
      wp_enqueue_style('datatables-responsive', '//cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css', array(), '2.4.1');
      
      wp_enqueue_script('datatables-management', '//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true);
      wp_enqueue_script('datatables-responsive', '//cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js', array(), '2.4.1', true);
      
      wp_localize_script('datatables-management', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'action'   => 'get_exhibitor_members',
      ));
      wp_enqueue_script('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js', array('jquery'), '2.3.6', true);
      wp_enqueue_script('datatables-management-pdfmake', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('jquery'), '0.1.53', true);
      wp_enqueue_script('datatables-management-vfs_fonts', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('jquery'), '0.1.53', true);
      wp_enqueue_script('datatables-management-jszip', '//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('jquery'), '3.1.3', true);
      wp_enqueue_script('datatables-management-buttons-html5', '//cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js', array('jquery'), '2.3.6', true);

      // Get all years of user registration
      $user_years = array();
      $exhibitor_members = get_users(array('role' => 'exhibitsmember'));

      foreach ($exhibitor_members as $exhibitor_member) {
          $registered_year = date('Y', strtotime($exhibitor_member->user_registered));
          $user_years[] = $registered_year;
      }

      $unique_years = array_unique($user_years);
      rsort($unique_years);
      // Exhibitor Members List
      echo '<h2>Exhibitor List</h2>  <a href="'.admin_url( 'admin.php?page=add-new-exhibitor' ).'" class="btn button">Add New Exhibitor</a>';
      // Year filter dropdown
      echo '<div class="year-filter">';
      echo '<label for="year">Select Year : </label>';
      echo '<select id="year-filter" name="year">';
      echo '<option value="">All</option>';

      foreach ($unique_years as $year) {
          echo '<option value="' . $year . '">' . $year . '</option>';
      }

      echo '</select>';
      echo '</div>';
      
      echo '<table id="exhibitor-members-list" style="width:100%" class="display responsive nowrap">';
      echo '<thead>
              <tr>
                <th>No.</th>
                <th>Status</th>
                <th>Company name</th>
                <th>Plan to exhibit</th>
                <th>First name</th>
                <th>Last name</th>
                <th>Email</th>
                <th>Booth counts</th>
                <th>Booth number(s)</th>
                <th>Exhibitor rep. first name</th>
                <th>Exhibitor rep. last name</th>
                <th>Participating Year</th>
                <th>Exhibitor id</th>
                <th>Date of registration</th>
              </tr>
            </thead>';
      echo '<tbody>';
      echo '</tbody>';
      echo '</table>';
      ?>
      <script>
        // Initialize DataTables
        jQuery(document).ready(function($) {
          $(document).on('change', '.status-select', function() {
            var selectedValue = $(this);
            console.log(selectedValue.data('row'));
            jQuery.ajax({
              url: ajaxurl,
              type: 'POST',
              dataType: 'json',
              data: {
                  action: 'update_user_status',
                  user_id: selectedValue.data('row'),
                  new_status: $(this).val()
              },
              success: function(response) {
                  // Handle successful response
                  console.log(response.data); // Display success message or perform any other actions
              },
              error: function(xhr, status, error) {
                  // Handle error response
                  console.log(xhr.responseText); // Display error message or perform any other actions
              }
            });
          });
          let exhibitor_profile = '<?php echo admin_url( 'admin.php?page=edit-exhibitor-profile' );?>';
          let exhibitorMembersList = document.getElementById('exhibitor-members-list');
          if(typeof exhibitorMembersList !== "undefined")
          {
            var t = $("#exhibitor-members-list").DataTable({
                  "ajax": {
                    "url": ajax_object.ajax_url,
                    "type": "POST",
                    "data": {
                        "action": "get_exhibitor_members"
                    }
                  },
                  "columns": [
                      { "data": "no" },
                      { 
                        "data": "status",
                        render: function(data, type, row, meta) {
                          // Array with key-value pairs
                          var keyValueArray = [
                            { key: '-', value: 'Select'},
                            { key: 'confirm_contact', value: 'Confirm Contact'},
                            { key: 'account_pending', value: 'Account Pending'},
                            { key: 'account_activated', value: 'Account Activated'},
                            { key: 'booth_pending', value: 'Booth Pending'},
                            { key: 'pending_payment', value: 'Pending Payment'},
                            { key: 'payment_complete', value: 'Payment Complete'},
                            { key: 'complete', value: 'Complete'}
                          ];
  
                          // Selected key
                          var selectedKey = data; // Replace with the key you want to pre-select
                          var dropdownHtml = `<select data-row="${row.id}" class="status-select">`;
  
                          keyValueArray.forEach(function(item, index) {
                            var selected = (item.key === selectedKey) ? 'selected' : '';
                            var disabled = (index > 0) ? 'disabled' : '';
                            dropdownHtml += '<option value="' + item.key + '" ' + selected + '>' + item.value + '</option>';
                          });
  
                          dropdownHtml += '</select>';
  
                          return dropdownHtml;
                        }
                      },
                      { 
                        "data": "company_name",
                        render: function(data, type, row, meta) {
                          return `<a href="${exhibitor_profile}&exhibitor_id=${row.id}">${data}</a>`;
                        }
                      },
                      { 
                        "data": "plan_to_exhibit",
                        render: function(data, type, row, meta) {
                          // Array with key-value pairs
                          var keyValueArray = [
                              { key: '-', value: 'Select'},
                              { key: 'yes', value: 'Yes'},
                              { key: 'no', value: 'No'},
                              { key: 'no_reply', value: 'No Reply'},
                              { key: 'maybe', value: 'maybe'},
                          ];
  
                          // Selected key
                          var selectedKey = data; // Replace with the key you want to pre-select
                          var plan_to_exhibit = `<select data-row="${row.id}" class="status-select">`;
  
                          keyValueArray.forEach(function(item, index) {
                            var selected = (item.key === selectedKey) ? 'selected' : '';
                            var disabled = (index > 0) ? 'disabled' : '';
                            plan_to_exhibit += '<option value="' + item.key + '" ' + selected + '>' + item.value + '</option>';
                          });
  
                          plan_to_exhibit += '</select>';
  
                          return plan_to_exhibit;
                        }
                      },
                      { "data": "first_name" },
                      { "data": "last_name" },
                      { "data": "email" },
                      { "data": "booth_count" },
                      { "data": "exhibit_booth_number" },
                      { "data": "exhibit_rep_first_name" },
                      { "data": "exhibit_rep_last_name" },
                      { "data": "particepating_year" },
                      { "data": "id" },
                      { "data": "date_of_registration" }

                      // Add more columns if needed
                  ],
                  pageLength: 25,
                  aLengthMenu: [
                      [25, 50, 100, 200, -1],
                      [25, 50, 100, 200, "All"]
                  ],
                  dom: 'Blfrtip',
                  buttons: [
                    // {extend: 'pdf'},
                    {
                      extend: 'csv',
                      split: [ 'csv', 'pdf', 'excel'],
                      text: 'Export to CSV',
                      filename: 'exhibitor-members-', // Rename the downloaded CSV file
                      exportOptions: {
                          columns: ':not(:last-child)',
                          modifier: {
                              search: 'applied'
                          }
                      }
                    },
                    // {extend: 'excel'} 
                  ],
                  columnDefs: [
                    {
                        searchable: false,
                        orderable: false,
                        targets: 0,
                    },
                  ],
                  order: [[13, 'desc']],
                  "processing": true,
                  responsive: true
              });
              $(".dt-buttons").prepend($(".year-filter"));
              t.on('order.dt search.dt', function () {
                  let i = 1;
                  t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
                      this.data(i++);
                  });
              }).draw();
              var categoryIndex = 0;
              $("#exhibitor-members-list th").each(function (i) {
                if ($(this).html() == "Year") {
                  categoryIndex = i; return false;
                }
              });
              $.fn.dataTable.ext.search.push(
                function (settings, data, dataIndex) {
                  var selectedItem = $('#year-filter').val();
                  var category = data[categoryIndex];
                  if (selectedItem === "" || category.includes(selectedItem)) {
                    return true;
                  }
                  return false;
                }
              );
  
              $("#year-filter").change(function (e) {
                t.draw();
              });
  
              t.draw();
          }
        });
      </script>
      <?php
    }
  
    public function display_add_exhibitor_page()
    {
      ?>
        <div class="wrap">
        
        <h1>Add New Exhibitor</h1>

        <div id="exhibitor-container">
          <div id="gravity-form-container">
            <?php
              gravity_form(15, true, false, false, null, false, '', true ); // Replace 1 with the ID of your Gravity Form
            ?>
          </div>
        </div>
        </div>
      <?php
    }

    public function send_invitation_exhibitor()
    {
      ?>
      <div class="wrap">
        <h1>Send Exhibitor Invitation</h1>
        <div id="exhibitor-send-invitation" style="width: 40%;">
          <?php
            gravity_form(17, true, false, false, null, false, '', true ); // Replace 1 with the ID of your Gravity Form
          ?>
        </div>
      </div>
      <?php
    }

    public function exhibitor_profiles_admin_page_content()
    {
      ?>
      <div class="wrap">
        <h1>Exhibitor Profile</h1>
        <div class="exhibitor-profile-wrap">
            <?php
              if(isset($_REQUEST['exhibitor_id']))
              {
                $exhibitor_id = $_REQUEST['exhibitor_id'];
                if(get_users( [ 'include' => $exhibitor_id, 'fields' => 'ID' ] ))
                {
                    wp_enqueue_style('select2', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0');
                    wp_enqueue_script('select2', '//cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true);
                  ?>
                  <div id="booth-admin-tabs">
                    <ul>
                      <li><a href="#booth-admin-tabs-profile">Profile</a></li>
                      <li><a href="#booth-admin-tabs-assign-booth">Assign Booth(s)</a></li>
                      <li><a href="#booth-admin-tabs-assistant">Assistant(s)</a></li>
                    </ul>
                    <div id="booth-admin-tabs-profile">
                      <div id="exhibitor-profile" style="width: 60%;">
                        <?php gravity_form(16, true, false, false, null, false, '', true ); ?>
                      </div>
                    </div>
                    <div id="booth-admin-tabs-assign-booth">
                      <div class="assign-booth-products">
                        <h1>Assign Booths</h1>
                        <form action="" id="assign-booth-product-exhibitor">
                          <input type="hidden" name="customer_id" value="<?php echo $exhibitor_id;?>" />
                          <!-- <select id="booth-products" multiple="multiple"> -->
                            <?php 
                              $args = array(
                                  'include' => array(18792),
                              );                        
                              $products = wc_get_products( $args );                        
                              foreach ( $products as $product ) {
                                  // Access product properties
                                  $product_id     = $product->get_id();
                                  $product_name   = $product->get_name();
                                  $product_price  = $product->get_price();
                              
                                  // Do something with the product information
                                  echo "<h3>".$product_name."</h3>";
                                  echo "Booth Count : <input type='number' value='1' id='calculatePrice' min='1' step='1' max='99' data-product_id='". $product_id ."' data-price='". $product_price ."' />";
                                  echo '<p>Total Value: $<span id="totalValue">'. $product_price .'</span></p>';
                              }
                              
                            ?>
                          <!-- </select> -->
                          <input type="submit" class="button" value="Send Invoice" id="send-invoice-assign-booth" />
                        </form>
                      </div>
                      <div class="assign-booth-number-current-year">
                        <h1>Assigned Booth Numbers</h1>
                        <div id="tabs">
                          <ul>
                            <li><a href="#tabs-1">Current Year</a></li>
                            <li><a href="#tabs-2">Previous Year</a></li>
                          </ul>
                          <div id="tabs-1">
                                <?php
                                  // $PurchasedQty = $this->getTotalQuantityPurchased(18792);
                                  // echo "<pre>";
                                  // print_r($PurchasedQty);
                                  acf_form_head();
                                  $current_year = date('Y');
                                  acf_form(array(
                                    'post_id'             => 'user_' . $exhibitor_id,
                                    'field_groups'        => array('group_63c15781ab918'),
                                    'fields'              => array('field_6477148a02f19'),
                                    'form'                => true,
                                    'return'              => add_query_arg('updated', 'true', site_url('wp-admin/admin.php?page=edit-exhibitor-profile&exhibitor_id='.$exhibitor_id.'#booth-admin-tabs-assign-booth')),
                                    'html_before_fields'  => '',
                                    'html_after_fields'   => '',
                                    'submit_value'        => 'Assigned Booth Number',
                                    'html_updated_message' => sprintf('Booth number successfully assigned for year %d', $current_year),
                                ));

                                
                                  // echo "</pre>";
                                ?>                            
                          </div>
                          <div id="tabs-2">
                            <div class="booth-number-container">
                              <h1>Booth Numbers History</h1>
                              <div class="booth-number-log">
                                <?php 
                                  echo "<pre>";
                                  $variable = get_field('booth_numbers', 'user_'.$exhibitor_id.'_2022');
                                  print_r($variable);
                                  echo "</pre>";
                                
                                ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div id="booth-admin-tabs-assistant">
                      <div id="exhibitor-assistant-container" style="width:40%;">';
                        <h1>Booth Admin Assistant</h1>
                        <div class="add-assistant-wrap">
                          <button id="add-new-assistant">Add Assistant</button>
                          <button id="hide-new-assistant" style="display:none;">Hide</button>
                          <div class="add-assistant-form" style="display:none;">
                              <?php gravity_form(14, false, false, false, null, true, '', true ); ?>
                          </div>
                        </div>
                        <?php echo do_shortcode( '[exhibit_assistant_list exhibitor_id="'.$exhibitor_id.'"]' ); ?>
                      </div>
                    </div>
                  </div>
                  <?php
                }else{
                  echo "<div class='notice notice-error'><p><b> Exhibitor doesn't exists with the ID ".$exhibitor_id." </b></p></div>";
                }
              }else{
                wp_redirect( admin_url( 'admin.php?page=exhibitor-management' ) );
                exit;
              }
            ?>
        </div>
      </div>
      <?php
    }
    
    // Callback function to handle AJAX request for retrieving exhibitor members
    public function get_exhibitor_members() {
      $year = isset($_POST['year']) ? sanitize_text_field($_POST['year']) : '';

      $args = array(
        'role__in'  => array('exhibitsmember','exhibitpending'),
        'orderby'   => 'ID',
        'order'     => 'ASC'
      );

      if (!empty($year)) {
          $args['meta_query'] = array(
              array(
                  'key'     => 'exhibitor_registration_year',
                  'value'   => $year,
                  'compare' => '=',
              ),
          );
      }
      $exhibitor_members = get_users($args);
      $data = array();
      foreach ($exhibitor_members as $exhibitor_member) {
        $username = $exhibitor_member->first_name.' '.$exhibitor_member->last_name;
        $company_name = get_user_meta($exhibitor_member->ID, 'user_employer', true) ? get_user_meta($exhibitor_member->ID, 'user_employer', true) : get_field('billing_company', $exhibitor_member->ID);
        $exhibit_booth_number = get_user_meta($exhibitor_member->ID, 'exhibit_booth_number', true);
        $get_status   = get_user_meta($exhibitor_member->ID, '_exhibitor_status', true );    
        $data[] = array(
            'no'                    => '',
            'status'                => $get_status ? $get_status : 'confirm_contact',
            'company_name'          => $company_name,
            'plan_to_exhibit'       => get_user_meta($exhibitor_member->ID, '_plan_to_exhibit', true) ? get_user_meta($exhibitor_member->ID, '_plan_to_exhibit', true) : '',
            'first_name'            => $exhibitor_member->first_name,
            'last_name'             => $exhibitor_member->last_name,
            'email'                 => $exhibitor_member->user_email,
            'booth_count'           => '',
            'exhibit_booth_number'  => $exhibit_booth_number ? $exhibit_booth_number : 0,
            'exhibit_rep_first_name'=> get_user_meta($exhibitor_member->ID, 'first_name__manager_information', true) ? get_user_meta($exhibitor_member->ID, 'first_name__manager_information', true) : '-',
            'exhibit_rep_last_name' => get_user_meta($exhibitor_member->ID, 'last_name__manager_information', true) ? get_user_meta($exhibitor_member->ID, 'last_name__manager_information', true) : '-',
            'particepating_year'    => get_user_meta($exhibitor_member->ID, 'particepating_year', true) ? get_user_meta($exhibitor_member->ID, 'particepating_year', true) : '',
            'id'                    => $exhibitor_member->ID,
            'date_of_registration'  => $exhibitor_member->user_registered,
        );
      }
      wp_send_json( array( 'data' => $data ) );
    }

    public function exhibitor_members_admin_confirmation( $confirmation, $form, $entry, $ajax )
    {
      if(is_admin()){
        $confirmation = array( 'redirect' => admin_url( 'admin.php?page=exhibitor-management' ) );
        return $confirmation;
      }
    }

    public function remove_form_entry( $entry ) {
        GFAPI::delete_entry( $entry['id'] );
    }

    public function exhibitor_members_payment_complete( $order_id )
    {
      $order = wc_get_order($order_id);
      $items = $order->get_items();
      $main_user_id = get_post_meta($order_id, '_customer_user', true);

      //Check Order Items Related To Booth Products
      if($this->order_has_product_category($order_id, 'booth-products'))
      {
        update_user_meta($main_user_id, '_exhibitor_status', 'payment_complete');
      }
      foreach ($items as $item) {
        if ($item->get_product_id() == 18792) {
          $main_user = get_user_by('id', $main_user_id);
          $main_user->add_role('exhibitsmember');  
          $main_user->remove_role('exhibitpending');  
        }
      }
        //===============>
      $gravity_form_entry_id = get_post_meta($order_id, '_gravity_form_entry_id', true);
      if (!empty($gravity_form_entry_id)) {
          $entry = GFAPI::get_entry($gravity_form_entry_id);
          if (!empty($entry) && $entry['form_id'] == 15 ) {
              //2023 EXHIBITOR REGISTRATION
              $main_user_id = get_post_meta($order_id, '_customer_user', true);
              $main_user = get_user_by('id', $main_user_id);
              $main_user->add_role('exhibitsmember');                
              update_user_meta($main_user_id, 'special_role', array($entry['29.1'],$entry['29.2'],$entry['29.3']));
          }
      }
        
    }
    public function order_has_product_category($order_id, $category_slug) {
      $order = wc_get_order($order_id); // Get the order object
      $items = $order->get_items(); // Get the order items
      
      foreach ($items as $item) {
          $product_id = $item->get_product_id(); // Get the product ID
          $product = wc_get_product($product_id); // Get the product object
          $categories = $product->get_category_ids(); // Get the product categories
          
          if (in_array($category_slug, $categories)) {
              return true; // Category found in the order
          }
      }
      
      return false; // Category not found in the order
  }
  
    // Callback function to handle CSV export
    public function export_exhibitors_csv() {
      $selected_year = isset($_GET['year']) ? $_GET['year'] : '';
      $exhibitor_members = get_users(array(
          'role'       => 'exhibitsmember',
          'date_query' => array(
              array(
                  'year' => $selected_year,
              ),
          ),
      ));
  
      // Generate CSV content
      $csv_data = '';
      if (!empty($exhibitor_members)) {

        $csv_data .= 'Username,Email,Company Name,Booth Number,Year' . "\n";
  
        // Add user data
        foreach ($exhibitor_members as $exhibitor_member) {
          $username = $exhibitor_member->first_name.' '.$exhibitor_member->last_name;
          $email = $exhibitor_member->user_email;
          $company_name = get_user_meta($exhibitor_member->ID, 'billing_company', true) ? get_user_meta($exhibitor_member->ID, 'billing_company', true) : get_field('user_employer', $exhibitor_member->ID);
          $exhibit_booth_number = get_field('exhibit_booth_number', $exhibitor_member->ID);
          $role = 'exhibitsmember';
          $year = date('Y', strtotime($exhibitor_member->user_registered));
            $csv_data .= '"' . 
            $username . '","' . 
            $email . '","' . 
            $company_name .'","' . 
            $exhibit_booth_number .'","' . 
            $year . '"' . "\n";
        }
      }
      // Set CSV headers
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename=exhibitors-'.$year.'.csv');
      // Output CSV data
      echo $csv_data;
      exit;
    }

    public function get_exhibitor_status_by_id( $exhibitor_id )
    {
      $exhibitor = get_user_by( 'id', $exhibitor_id );
      $status = array();
      if($exhibitor){

      }
    }

    public function has_user_purchased_product( $user_id, $product_id )
    {
      // Retrieve customer orders
      $customer_orders = get_posts(array(
        'numberposts' => -1,
        'meta_key'    => '_customer_user',
        'meta_value'  => $user_id,
        'post_type'   => 'shop_order',
        'post_status' => 'wc-completed',
      ));

      // Loop through customer orders
      foreach ($customer_orders as $customer_order) {
          // Get order ID
          $order_id = $customer_order->ID;

          // Check if the product is in the order
          $order = wc_get_order($order_id);
          $items = $order->get_items();

          foreach ($items as $item) {
              if ($item->get_product_id() == $product_id) {
                  return true;
              }
          }
      }
      return false;
    }

    public function update_user_status_callback()
    {
      if(isset($_POST['user_id']) && get_user_by( 'id', $_POST['user_id'] )){
        // Get the user ID and new status value from the AJAX request
        $user_id = $_POST['user_id'];
        $new_status = $_POST['new_status'];

        // Update the user meta field with the new status value
        update_user_meta($user_id, '_exhibitor_status', $new_status);

        // Return a response
        wp_send_json_success('User status updated successfully');
      }
    }

    public function assign_booth_products()
    {
        if(!empty($_POST['products_ids']))
        {
          try {
            $order = wc_create_order();
            $customer_id = $_POST['customer_id'];
            $customer = new WC_Customer( $customer_id );
            $billing_address = array(
              'first_name' => $customer->get_billing_first_name(),
              'last_name'  => $customer->get_billing_last_name(),
              'company'    => $customer->get_billing_company(),
              'address_1'  => $customer->get_billing_address_1(),
              'address_2'  => $customer->get_billing_address_2(),
              'city'       => $customer->get_billing_city(),
              'state'      => $customer->get_billing_state(),
              'postcode'   => $customer->get_billing_postcode(),
              'country'    => $customer->get_billing_country()
            );
            $order->set_customer_id($customer_id);
            $order->set_status('pending');
            
            // foreach ( $_POST['products_ids'] as $product_id => $qty ) {
              $quantity = $_POST['qty'] == 0 ? 1 : $_POST['qty'];
              $product = wc_get_product($_POST['products_ids']); 
              $order->add_product($product, $quantity); 
            // }
            $order->set_address($billing_address, 'billing');
            // $order->set_address($billing_address, 'billing'); 
            $order->calculate_totals();
            $order->save();
            // Send order invoice
            add_action('send_order_details', array($this, 'custom_send_order_invoice'), 10, 4);            
            // Send the payment request and order details
            do_action('send_order_details', $order, false, false, '');
            remove_action('send_order_details', array($this, 'custom_send_order_invoice'), 10);
            update_user_meta($customer_id, '_exhibitor_status', 'pending_payment');
            wp_send_json_success( array('order_id' => $order->get_id()), 201 );
            
          } catch (\Throwable $th) {
            // $th->get_mess
            wp_send_json_error();
          }
        }else{
          wp_send_json_error();
        }
    }

    public function getTotalQuantityPurchased( $product_id, $current_year= 0 ) {
        $current_year = date('Y');
        $total_quantity = 0;

        // Get all completed orders for the current year
        $orders = wc_get_orders([
            'limit'      => -1,
            'status'     => 'completed',
            'date_after' => $current_year . '-01-01',
        ]);

        // Loop through the orders
        foreach ($orders as $order) {
            // Check if the order contains the product
            if ($order->has_product($product_id)) {
                // Get the order items
                $order_items = $order->get_items();

                // Loop through the order items
                foreach ($order_items as $item) {
                    if ($item->get_product_id() === $product_id) {
                        // Get the quantity and add to the total
                        $quantity = $item->get_quantity();
                        $total_quantity += $quantity;
                    }
                }
            }
        }

        return $total_quantity;
    }
    public function custom_send_order_invoice($order, $sent_to_admin, $plain_text, $email) {
      // if (!$sent_to_admin && $order->has_status('pending')) {
          $mailer = WC()->mailer();
          $mailer->emails['WC_Email_Customer_Invoice']->trigger($order->get_id());//customer_invoice
      // }
    }

    public function booth_number_current_year()
    {
        // Validate and sanitize inputs
        $customer_id = isset($_POST['customer_id']) ? absint($_POST['customer_id']) : 0;
        $booth_numbers = isset($_POST['booth_numbers']) ? $_POST['booth_numbers'] : '';
        // update_user_meta($customer_id, '_booth_numbers', '');
        
        // Check if required inputs are not empty
        if (!empty($booth_numbers) && $customer_id) {
            // Retrieve existing booth numbers
            $existing_booth_numbers = get_user_meta($customer_id, '_booth_numbers', true);

            if (is_array($existing_booth_numbers)) {
                // Format the existing booth numbers by adding the current year as a key
                $formatted_booth_numbers = $existing_booth_numbers;
            } else {
                $formatted_booth_numbers = [];
            }

            // Add the new booth numbers with the current year as a key
            $formatted_booth_numbers[date('Y')] = $booth_numbers;

            // Update the user meta data with the formatted booth numbers
            $update_booth = update_user_meta($customer_id, '_booth_numbers', $formatted_booth_numbers);

            $get_existing_booth_numbers = get_user_meta($customer_id, '_booth_numbers', true);
            $get_existing_data = '';
            if(!empty($get_existing_booth_numbers) && is_array($get_existing_booth_numbers))
            {
              foreach ($get_existing_booth_numbers as $booth_year => $booth_numbers) {
                $get_existing_data .= "<h2 class='booth-years'>".$booth_year."</h2>";
                $get_existing_data .= "<ul>";
                foreach ($booth_numbers as $key => $value) {
                  $get_existing_data .= sprintf('<li>Booth #%s</li>', ($key + 1).' : '.$value['booth_number_'.$key + 1]);
                }
                $get_existing_data .= "</ul>";
              }
            }
            if (is_wp_error($update_booth)) {
                wp_send_json_error($update_booth->get_error_message());
            } else {
                wp_send_json_success(array('data' => $get_existing_data), 201);
            }
        } else {
            wp_send_json_error();
        }
    }

    public function restrict_repeater_rows( $valid, $value, $field, $input )
    {
      $max_rows = 5; // Specify the maximum number of rows you want to allow
      if (isset($value) && is_array($value)) {
          $total_rows = count($value);
          if ($total_rows > $max_rows) {
              $valid = false;
              return 'You have reached the maximum number of rows allowed: '. $max_rows;
          }
      }
      return $valid;
    }
    
    public function save_exhibitor_booth_data_yearly( $post_id ) {
      $current_year = date('Y');  
      $repeater_data = get_field('field_6477148a02f19', $post_id);
      update_user_meta($post_id, 'save_exhibitor_booth_data_yearly_'.$current_year, $repeater_data);
      update_field('field_6477148a02f19', $repeater_data, $post_id . '_' . 2021);
    }

    public function filter_repeater_data_yearly($value, $post_id, $field) {
      $current_year = date('Y');
      $user_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : get_current_user_id(); // Get the current user ID
      error_log(print_r('filter_repeater_data_yearly', true));
      // error_log(print_r(get_field('booth_numbers', 'user_'.$user_id.'_2022'), true));
      // Check if the query is for displaying user data
      // error_log(print_r($query->is_main_query(), true));
      error_log(print_r($user_id, true));
      // error_log(print_r($field, true));
      // if ($user_id) {
        // $settings_values = get_field('booth_numbers', 'user_' . $user_id );//. '_' . $current_year
        // error_log(print_r(get_user_meta($user_id), true));
        // error_log(print_r('=======================', true));
        // $value = array(
        //   array(
        //     'field_6477148a02f19' => 'Production',
        //   ),
        //   array(
        //     'field_6477148a02f19' => 'Director',
        //   ),
        //   array(
        //     'field_6477148a02f19' => 'Author',
        //   ),
        //   array(
        //     'field_6477148a02f19' => 'Artist',
        //   ),
        //   array(
        //     'field_6477148a02f19' => 'Etc'
        //   )
        // );
        return $value;
        // $i = 0;
    
        // foreach( $settings_values as $settings_value ){
    
        //   $value[$i]['field_6477148a02f19'] = $settings_value['type'];
    
        //   $i++;
    
        // }
      //   //     // Get the repeater data for the user for the current year
          // $value = get_field('field_6477148a02f19', 'user_' . $user_id . '_' . $current_year);//booth_numbers
  
      //     if ($repeater_data) {
      //         // Store the repeater data in a query variable to use in the template
      //         $query->set('repeater_data', $repeater_data);
      //     }
      // }
      return $value;
    }
  

}
// Instantiate the ExhibitorManagement class
function initialize_exhibitor_management() {
  global $exhibitor_management;
  $exhibitor_management = new ExhibitorManagement();
}

// Hook into the 'plugins_loaded' action to initialize the Exhibitor Management functionality
add_action('init', 'initialize_exhibitor_management');
