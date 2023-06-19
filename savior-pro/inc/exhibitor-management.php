<?php
// Define the ExhibitorManagement class
class ExhibitorManagement {
    public function __construct() {
      // Hook into WordPress admin_menu to add the Exhibitor Management page
      add_action('admin_menu', array($this, 'add_exhibitor_management_page'));
  
      // Hook into admin_post action to handle CSV export
      add_action('wp_ajax_get_exhibitor_members', array($this, 'get_exhibitor_members'));
      add_filter( 'gform_confirmation_19', array($this, 'exhibitor_members_admin_confirmation'), 10, 4 );
      add_filter( 'gform_confirmation_18', array($this, 'exhibitor_members_admin_confirmation'), 10, 4 );
      // Hook into the 'gform_user_registration_validation' filter
      add_filter( 'gform_user_registration_validation', array($this, 'bypass_existing_user_registration'), 5, 3 );

      add_filter( 'gform_user_registration_update_user_id', array($this, 'gform_user_registration_update_user_id'), 10, 4 );
      add_filter('woocommerce_payment_complete', array($this, 'exhibitor_members_payment_complete'));

      // add_action('admin_head', array($this, 'exhibitor_members_style'));
      add_action( 'admin_enqueue_scripts', array($this, 'load_exhibitor_admin_style') );
      add_action('wp_ajax_update_user_status', array($this, 'update_user_status_callback'));
      add_action('wp_ajax_update_plane_to_exhibitor_status', array($this, 'update_plane_to_exhibitor_status'));
      // add_action( 'woocommerce_order_status_completed', array( $this, 'exhibitor_members_payment_complete' ) );//order_status_completed
      add_action('wp_ajax_assign_booth_products', array($this, 'assign_booth_products'));
      
      add_filter('acf/prepare_field/name=booth_numbers', array($this, 'restrict_repeater_rows'), 10);
      
      add_filter('acf/load_value/name=booth_numbers', array($this, 'filter_repeater_field_value'), 10, 3);
      add_filter('acf/update_value/name=booth_numbers', array($this, 'update_repeater_field_value'), 10, 4);
      
      // add_action('template_redirect', array($this, 'handle_account_activation'));
      
      add_action( 'wp_ajax_add_representative', array($this, 'add_representative_callback') );
      add_action( 'wp_ajax_notes', array($this, 'notes_callback') );
      add_shortcode('my_booth_status', array($this, 'my_booth_status'));
      add_shortcode('get_booth_product_status', array($this, 'get_booth_product_status'));
      
      add_action( 'gform_after_submission_20', array($this, 'reset_password_exhibitor'), 10, 2 );
      add_action( 'wp_ajax_add_booth_count', array($this, 'add_booth_count_callback'));
      
      add_shortcode('activate_user_account', array($this, 'activate_user_account'));
      add_action( 'wp_ajax_check_email_exist', array($this, 'check_email_exist_callback'));
      add_shortcode('my_orders', array($this, 'shortcode_my_orders'));
    }

    public function load_exhibitor_admin_style()
    {
      wp_enqueue_style( 'exhibitor_admin_css', get_theme_file_uri('/inc/assets/css/exhibitor-admin.css'), false, '1.0.0' );
      // Enqueue DataTables scripts and styles
      $current_screen = get_current_screen();
      if($current_screen->id == 'toplevel_page_exhibitor-management')
      {
        wp_enqueue_script('jquery');
        wp_enqueue_style('datatables-management', '//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), '1.13.4');
        wp_enqueue_style('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css', array(), '2.3.6');
        wp_enqueue_style('datatables-responsive', '//cdn.datatables.net/responsive/2.4.1/css/responsive.dataTables.min.css', array(), '2.4.1');
        
        wp_enqueue_script('datatables-management', '//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true);
        wp_enqueue_script('datatables-responsive', '//cdn.datatables.net/responsive/2.4.1/js/dataTables.responsive.min.js', array(), '2.4.1', true);
        
        wp_enqueue_script('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js', array('jquery'), '2.3.6', true);
        wp_enqueue_script('datatables-management-pdfmake', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('jquery'), '0.1.53', true);
        wp_enqueue_script('datatables-management-vfs_fonts', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('jquery'), '0.1.53', true);
        wp_enqueue_script('datatables-management-jszip', '//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('jquery'), '3.1.3', true);
        wp_enqueue_script('datatables-management-buttons-html5', '//cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js', array('jquery'), '2.3.6', true);
        
      }
      wp_enqueue_script('exhibitor-management-datatable', get_theme_file_uri('/inc/assets/js/exhibitor-management-datatable.js'), array('jquery'), time(), true);
      wp_localize_script('exhibitor-management-datatable', 'exhibitor_object', array(
        'ajax_url'        => admin_url('admin-ajax.php'),
        'action'          => 'get_exhibitor_members',
        'booth_admin'     => admin_url( 'admin.php?page=edit-exhibitor-profile' ),
        'current_screen'  => $current_screen->id
      ));
      if( 
        $current_screen->id == 'asgmt-exhibits_page_edit-exhibitor-profile' || 
        $current_screen->id == 'toplevel_page_exhibitor-management' ||
        $current_screen->id == 'asgmt-exhibits_page_add-new-exhibitor'
      )
      {
        wp_enqueue_style('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.min.css', array(), '11.7.10');
        wp_enqueue_script('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.all.min.js', array(), '11.7.10');
      }
      
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
      ?>
      <h2>Exhibitor List</h2>  <a href="<?php echo admin_url( 'admin.php?page=add-new-exhibitor' );?>" class="btn button">Add New Exhibitor</a>
      <div class='booth-totalizer'>
        <?php print_r($this->get_booth_totalizer()); ?>
      </div>
      <!-- // Year filter dropdown -->
      <div class="year-filter">
        <label for="year">Select Year : </label>
        <select id="year-filter" name="year">
        <option value="">All</option>
          <?php
            foreach ($unique_years as $year) {
                echo '<option value="' . $year . '">' . $year . '</option>';
            }
          ?>
        </select>
      </div>
      <div class="filter-by-status">
        <label for="filter-by-status">Filter by status</label>
        <select id="filter-by-status" name="filter-by-status">
          <option value="">Select</option>
          <option value="confirm_contact">Confirm Contact</option>
          <option value="account_pending">Account Pending</option>
          <option value="account_activated">Account Activated</option>
          <option value="booth_pending">Booth Pending</option>
          <option value="pending_payment">Pending Payment</option>
          <option value="payment_complete">Payment Complete</option>
          <option value="complete">Complete</option>        
        </select>
      </div>
      <div class="filter_plan_to_exhibit">
        <label for="filter_plan_to_exhibit">Filter by plan to exhibit</label>
        <select id="filter_plan_to_exhibit" name="filter_plan_to_exhibit">
          <option value="">Select</option>
          <option value="yes">Yes</option>
          <option value="no">No</option>
          <option value="no_reply">No Reply</option>
          <option value="maybe">Maybe</option>       
        </select>
      </div>
      <div class="reset-filter">
        <button id="reset_filter">Reset Filter</button>
      </div>
      <table id="exhibitor-members-list" style="width:100%" class="display responsive nowrap">
        <thead>
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
            <th>Active status</th>
            <th>Active plan to exhibit</th>
          </tr>
        </thead>
        <tbody>
        </tbody>
      </table>
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
              gravity_form(18, true, false, false, null, true, true, true ); // Replace 1 with the ID of your Gravity Form
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
                $_exhibitor_status = get_user_meta($exhibitor_id, '_exhibitor_status', true);
                if(get_users( [ 'include' => $exhibitor_id, 'fields' => 'ID' ] ))
                {
                  ?>
                  <div id="booth-admin-tabs">
                    <ul>
                      <li><a href="#booth-admin-tabs-profile">Profile</a></li>
                      <li><a href="#booth-admin-tabs-assign-booth">Assign Booth(s)</a></li>
                      <li><a href="#booth-admin-tabs-assistant">Assistant(s)</a></li>
                      <li><a href="#booth-admin-tabs-representative">Representative</a></li>
                      <li><a href="#booth-admin-tabs-notes">Note(s)</a></li>
                      <li><a href="#booth-admin-tabs-payment">Payment(s)</a></li>
                    </ul>
                    <div id="booth-admin-tabs-profile">
                      <div id="exhibitor-profile" style="width: 60%;">
                        <div class="exhibitor-status">
                          <label for="" style="display:block;">Status</label>
                          <select data-row="<?php echo $exhibitor_id;?>" data-assign_booth_number="<?php echo $this->get_booth_numbers_by_user_id( $exhibitor_id );?>" class="status-select" style="width:50%">
                              <option value="-" disabled>Select</option>
                              <option <?php selected( $_exhibitor_status, 'confirm_contact' ); ?> value="confirm_contact" selected="">Confirm Contact</option>
                              <option <?php selected( $_exhibitor_status, 'account_pending' ); ?> value="account_pending">Account Pending</option>
                              <option <?php selected( $_exhibitor_status, 'account_activated' ); ?> value="account_activated">Account Activated</option>
                              <option <?php selected( $_exhibitor_status, 'booth_pending' ); ?> value="booth_pending">Booth Pending</option>
                              <option <?php selected( $_exhibitor_status, 'pending_payment' ); ?> value="pending_payment">Pending Payment</option>
                              <option <?php selected( $_exhibitor_status, 'payment_complete' ); ?> value="payment_complete">Payment Complete</option>
                              <option <?php selected( $_exhibitor_status, 'complete' ); ?> value="complete">Complete</option>
                          </select>
                        </div>
                        <?php gravity_form(19, true, false, false, null, false, '', true ); ?>
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
                                  $boothCount = is_numeric(get_user_meta($exhibitor_id, 'booth_count', true)) ? get_user_meta($exhibitor_id, 'booth_count', true) : 0;
                                  $boothCountPrice = $boothCount === 0 ? 0 : $boothCount * $product_price;
                                  echo "Booth Count : <input type='number' value='".$boothCount."' id='calculatePrice' min='1' step='1' max='99' data-product_id='". $product_id ."' data-price='". $product_price ."' />";
                                  echo '<p>Total Value: $<span id="totalValue">'. $boothCountPrice .'</span></p>';
                              }
                              
                            ?>
                          <!-- </select> -->
                          <div class="action-btn">
                            <input type="submit" class="button" value="Save Count" id="save-count-booth" />  
                            <input type="submit" class="button" value="Send Invoice" id="send-invoice-assign-booth" />
                          </div>
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
                              <h4><?php echo $this->getTotalQuantityPurchased($exhibitor_id, 18792); ?> Booths Purchased</h4>
                              <?php
                                  if($this->getTotalQuantityPurchased($exhibitor_id, 18792) > 0 )
                                  {
                                    // echo $this->get_booth_numbers_by_user_id( (int)$exhibitor_id );
                                    acf_form_head();
                                    $current_year = date('Y');
                                    acf_form(array(
                                      'post_id'             => 'user_' . $exhibitor_id,
                                      'field_groups'        => array('group_63c15781ab918'),
                                      'fields'              => array('booth_numbers'),
                                      'form'                => true,
                                      'return'              => add_query_arg('updated', 'true', site_url('wp-admin/admin.php?page=edit-exhibitor-profile&exhibitor_id='.$exhibitor_id.'#booth-admin-tabs-assign-booth')),
                                      'html_before_fields'  => '',
                                      'html_after_fields'   => '',
                                      'submit_value'        => 'Assigned Booth Number',
                                      'html_updated_message' => sprintf('Booth number successfully assigned for year %d', $current_year),
                                    ));
                                  }else{
                                    echo "<p>No Booth Assigned Yet</p>";
                                  }
                                  
                            ?>                            
                          </div>
                          <div id="tabs-2">
                            <div class="booth-number-container">
                              <h1>Booth Numbers History</h1>
                              <div class="booth-number-log">
                                <?php 
                                  $current_year = date('Y');
                                  $Booth_numbers_history = get_user_meta($exhibitor_id, '_repeater_assigned_booth_numbers_'.$current_year -1, true);
                                  if(!empty($Booth_numbers_history) && is_array($Booth_numbers_history))
                                  {
                                    echo "<ul>";
                                    $i = 1;
                                    foreach ($Booth_numbers_history as $key => $value) {
                                      printf('<li>Booth #%s : %s</li>', $i, $value['field_647714c602f1a']);
                                      $i++;
                                    }
                                    echo "</ul>";
                                  }else{
                                    echo __('<p>No record found!</p>');
                                  }
                                ?>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div id="booth-admin-tabs-assistant">
                      <div id="exhibitor-assistant-container" style="width:40%;">
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
                    <div id="booth-admin-tabs-representative">
                      <h1>Representative</h1>
                      <form id="representative-form">
                        <?php 
                        $exhibitor_id = get_user_meta( $_REQUEST['exhibitor_id'], '_representative_data', true );
                        $representative_first_name = isset($exhibitor_id['representative_first_name']) ? $exhibitor_id['representative_first_name'] : '';
                        $representative_last_name = isset($exhibitor_id['representative_last_name']) ? $exhibitor_id['representative_last_name'] : '';
                        $representative_email = isset($exhibitor_id['representative_email']) ? $exhibitor_id['representative_email'] : '';
                        $representative_contact = isset($exhibitor_id['representative_contact']) ? $exhibitor_id['representative_contact'] : '';
                        ?>
                        <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'exhibitor_assistant' ); ?>" />
                        <input type="hidden" name="booth_admin" value="<?php echo $_REQUEST['exhibitor_id'];?>">
                        <input type="hidden" name="action" value="add_representative" />
                        <div class="form-group">
                          <label for="representative-first-name">First Name</label>
                          <input type="text" name="representative_first_name" value="<?php echo $representative_first_name; ?>" class="form-control" id="representative-first-name" placeholder="First Name" />
                        </div>
                        <div class="form-group">
                          <label for="representative-last-name">Last Name</label>
                          <input type="text" name="representative_last_name" value="<?php echo $representative_last_name; ?>" class="form-control" id="representative-last-name" placeholder="Last Name" />
                        </div>
                        <div class="form-group">
                          <label for="representative-email">Email</label>
                          <input type="text" name="representative_email" value="<?php echo $representative_email; ?>" class="form-control" id="representative-email" placeholder="Email" />
                        </div>
                        <div class="form-group">
                          <label for="representative-contact">Contact</label>
                          <input type="text" name="representative_contact" value="<?php echo $representative_contact; ?>" class="form-control" id="representative-contact" placeholder="Contact no." />
                        </div>
                        <button type="submit" id="representative-save" class="btn btn-primary">Submit</button>
                      </form>
                    </div>
                    <div id="booth-admin-tabs-notes">
                      <h1>Notes</h1>
                      <form id="notes-form">
                        <?php 
                        $notes = get_user_meta( $_REQUEST['exhibitor_id'], '_notes', true );
                        ?>
                        <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'exhibitor_assistant' ); ?>" />
                        <input type="hidden" name="booth_admin" value="<?php echo $_REQUEST['exhibitor_id'];?>">
                        <input type="hidden" name="action" value="notes" />
                        <div class="form-group">
                          <textarea name="notes"class="form-control" id="notes" rows="15" cols="100"><?php echo $notes; ?></textarea>
                        </div>
                        <button type="submit" id="notes-save" class="btn btn-primary">Submit</button>
                      </form>
                    </div>
                    <div id="booth-admin-tabs-payment">
                      <h1>Payment History </h1>
                      <pre>
                        <?php
                          //$_REQUEST['exhibitor_id']
                          //echo do_shortcode('[my_orders]' );

                        ?>
                      </pre>
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
    
    public function shortcode_my_orders( $atts ) {

      extract( shortcode_atts( array(
   
          'order_count' => -1
   
      ), $atts ) );
   
    
   
      ob_start();
   
      wc_get_template( 'myaccount/my-orders.php', array(   
          'current_user' => get_user_by( 'id', $_REQUEST['exhibitor_id'] ),   
          'order_count'   => $order_count   
      ) );
   
      return ob_get_clean();
    
    }
    // Callback function to handle AJAX request for retrieving exhibitor members
    public function get_exhibitor_members() {
      $args = array(
        'role__in'  => array('exhibitsmember','exhibitpending'),
        'orderby'   => 'ID',
        'order'     => 'ASC',
        // 'number'  => 1
      );

      $exhibitor_members = get_users($args);
      $data = array();
      foreach ($exhibitor_members as $exhibitor_member) {
        $company_name         = get_user_meta($exhibitor_member->ID, 'user_employer', true) ? get_user_meta($exhibitor_member->ID, 'user_employer', true) : get_field('billing_company', $exhibitor_member->ID);
        $get_status           = get_user_meta($exhibitor_member->ID, '_exhibitor_status', true );    
        $representative_data  = get_user_meta( $exhibitor_member->ID, '_representative_data', true );
        $_exhibitor_status    = get_user_meta( $exhibitor_member->ID, '_exhibitor_status', true ) ? get_user_meta($exhibitor_member->ID, '_exhibitor_status', true) : '';
        $_plan_to_exhibit     = get_user_meta($exhibitor_member->ID, '_plan_to_exhibit', true) ? get_user_meta($exhibitor_member->ID, '_plan_to_exhibit', true) : '';
        $representative_fname = isset($representative_data['representative_first_name']) ? $representative_data['representative_first_name'] : '-';
        $representative_lname = isset($representative_data['representative_last_name']) ? $representative_data['representative_last_name'] : '-';
        $particepating_year   = get_user_meta($exhibitor_member->ID, 'particepating_year', true) ? get_user_meta($exhibitor_member->ID, 'particepating_year', true) : '';
        $booth_count          = is_numeric( get_user_meta($exhibitor_member->ID, 'booth_count', true)) ? get_user_meta($exhibitor_member->ID, 'booth_count', true) : 0;

        $data[] = array(
            'no'                    => '',
            'status'                => $get_status ? $get_status : '',
            'company_name'          => $company_name,
            'plan_to_exhibit'       => $_plan_to_exhibit,
            'first_name'            => $exhibitor_member->first_name,
            'last_name'             => $exhibitor_member->last_name,
            'email'                 => $exhibitor_member->user_email,
            'booth_count'           => $booth_count,
            'exhibit_booth_number'  => $this->getTotalQuantityPurchased($exhibitor_member->ID, 18792) == 0 ? $this->get_booth_numbers_by_user_id( (int)$exhibitor_member->ID ) : 'Not Assigned',
            'exhibit_rep_first_name'=> $representative_fname,
            'exhibit_rep_last_name' => $representative_lname,
            'particepating_year'    => $particepating_year,
            'id'                    => $exhibitor_member->ID,
            'date_of_registration'  => $exhibitor_member->user_registered,
            'active_status'         => $_exhibitor_status,
            'active_plan_to_exhibit'=> $_plan_to_exhibit
        );
      }
      wp_send_json( array( 'data' => $data ) );
    }

    public function get_booth_numbers_by_user_id( $user_id )
    {
      $booth_numbers = get_user_meta($user_id, '_repeater_assigned_booth_numbers_'.date('Y'), true);
      if(!empty($booth_numbers))
      {
        $imploded = array_map(function($subArray) {
          return implode(', ', $subArray);
        }, $booth_numbers);        
        $exhibit_booth_number = implode(', ', $imploded);
      }else{
        $exhibit_booth_number = 0;
      }
      return $exhibit_booth_number;
    }

    public function exhibitor_members_admin_confirmation( $confirmation, $form, $entry, $ajax )
    {
      if(is_admin()){
        if( $form['id'] == '19' )
        {
          $confirmation = array( 'redirect' => admin_url( 'admin.php?page=edit-exhibitor-profile&exhibitor_id='.rgar( $entry, '31' ) ) );
          return $confirmation;
        }
        $confirmation = array( 'redirect' => admin_url( 'admin.php?page=exhibitor-management' ) );
        return $confirmation;
      }
    }

    public function bypass_existing_user_registration( $form, $config, $pagenum )
    {
      // Make sure we only run this code on the specified form ID
      if($form['id'] != 18) {
        return $form;
      }
      // Loop through the current form fields
      foreach($form['fields'] as &$field) {

        // confirm that we are on the current field ID and that it has failed validation because the email already exists
        if($field->id == 13 && $field->validation_message == 'This email address is already registered')
        {
          $field->failed_validation = false;
          $existing_user = get_user_by( 'email', $_POST['input_13'] );
          if(isset($_POST) && $existing_user)
          {
            $user_registration = array(
              'user_employer'                     => isset($_POST['input_7']) ? $_POST['input_7'] : '',
              'type_of_the_company'               => isset($_POST['input_30']) ? $_POST['input_30'] : '',
              'date_approved_on_exhibitors_list'  => isset($_POST['input_31']) ? $_POST['input_31'] : '',
              'billing_first_name'                => isset($_POST['input_10_3']) ? $_POST['input_10_3'] : '',
              'billing_last_name'                 => isset($_POST['input_10_6']) ? $_POST['input_10_6'] : '',
              'billing_email'                     => isset($_POST['input_13']) ? $_POST['input_13'] : '',
              'billing_company'                   => isset($_POST['input_7']) ? $_POST['input_7'] : '',
              'billing_address_1'                 => isset($_POST['input_4_1']) ? $_POST['input_4_1'] : '',
              'billing_address_2'                 => isset($_POST['input_4_2']) ? $_POST['input_4_2'] : '',
              'billing_city'                      => isset($_POST['input_4_3']) ? $_POST['input_4_3'] : '',
              'billing_state'                     => isset($_POST['input_4_4']) ? $_POST['input_4_4'] : '',
              'billing_postcode'                  => isset($_POST['input_4_5']) ? $_POST['input_4_5'] : '',
              'billing_country'                   => isset($_POST['input_4_6']) ? $_POST['input_4_6'] : '',
              'billing_phone'                     => isset($_POST['input_15']) ? $_POST['input_15'] : '',
              'primary_booth_admin_contact'       => isset($_POST['input_15']) ? $_POST['input_15'] : '',
              'first_name'                        => isset($_POST['input_10_3']) ? $_POST['input_10_3'] : '',
              'last_name'                         => isset($_POST['input_10_6']) ? $_POST['input_10_6'] : '',
              'email'                             => isset($_POST['input_13']) ? $_POST['input_13'] : '',
              'tp_phone_number'                   => isset($_POST['input_15']) ? $_POST['input_15'] : '',
              'alternate_booth_admin_first_name'  => isset($_POST['input_25_3']) ? $_POST['input_25_3'] : '',
              'alternate_booth_admin_last_name'   => isset($_POST['input_25_6']) ? $_POST['input_25_6'] : '',
              'alternate_booth_admin_email'       => isset($_POST['input_27']) ? $_POST['input_27'] : '',
              'alternate_booth_admin_contact'     => isset($_POST['input_32']) ? $_POST['input_32'] : ''
            );

            foreach ($user_registration as $user_meta_key => $meta_value) {
              if(!empty($meta_value))
              {
                update_user_meta( $existing_user->ID, $user_meta_key, $meta_value );
              }
            }
            $existing_user->add_role( 'exhibitpending' );
            //status
            update_user_meta( $existing_user->ID, '_exhibitor_status', 'account_activated' );
            // wp_update_user( array( 'ID' => $existing_user->ID, 'role' => 'exhibitpending' ) );
          }
        }
      }

      return $form;
    }

    public function exhibitor_members_payment_complete( $order_id )
    {
      $order = wc_get_order($order_id);
      $items = $order->get_items();
      $main_user_id = get_post_meta($order_id, '_customer_user', true);
      $user = get_userdata( $main_user_id );

      foreach ($items as $item) {
        if ($item->get_product_id() == 18792) {
          $user->add_role('exhibitsmember');  
          $user->remove_role('exhibitpending');  
          update_user_meta($main_user_id, '_exhibitor_status', 'payment_complete');
        }
      }
        //===============>
      $gravity_form_entry_id = get_post_meta($order_id, '_gravity_form_entry_id', true);
      if (!empty($gravity_form_entry_id)) {
          $entry = GFAPI::get_entry($gravity_form_entry_id);
          if (!empty($entry) && $entry['form_id'] == 18 ) {
              //2023 EXHIBITOR REGISTRATION
              $user->add_role('exhibitsmember');          
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
        $user_id    = $_POST['user_id'];
        $new_status = $_POST['new_status'];
        // Update the user meta field with the new status value
        $update_status = update_user_meta($user_id, '_exhibitor_status', $new_status);
        if(is_wp_error( $update_status ))
        {
          wp_send_json_error('Something error please try again!!!');
        }else{
          $all_status = array(
            'confirm_contact'   => 'Contact Information Confirmed Successfully!',
            'account_pending'   => 'Account Activation Link sent to Exhibitor Successfully!',
            'account_activated' => 'Account Activated Successfully. Please Assign Booth to the Exhibitor!',
            'booth_pending'     => 'Booth Payment Request sent Successfully, Please Change Status to Pending Payment!',
            'pending_payment'   => 'Status changed pending payment!',
            'payment_complete'  => 'Payment Done by Exhibitor!',
            'complete'          => 'Successfully Completed!'
          );
          if($new_status == 'account_pending')
          {
            $this->send_activation_email($user_id);
          }
          if(get_user_meta($user_id, '_exhibitor_status', true) == 'payment_complete')
          {
            $user = get_userdata( $user_id );
            $user->add_role( 'exhibitsmember' );
          }
          wp_send_json_success($all_status[$new_status]);
        }
      }
    }

    public function update_plane_to_exhibitor_status()
    {
      if(isset($_POST['user_id']) && get_user_by( 'id', $_POST['user_id'] )){
        // Get the user ID and new status value from the AJAX request
        $user_id = $_POST['user_id'];
        $new_status = $_POST['new_status'];

        // Update the user meta field with the new status value
        $new_status = update_user_meta($user_id, '_plan_to_exhibit', $new_status);
        if(is_wp_error( $new_status ))
        {
          wp_send_json_error('Something error please try again!!!');
        }else{
          wp_send_json_success('Status has been changed successfully!');
        }
      }
    }

    public function assign_booth_products()
    {
        if(!empty($_POST['products_ids']))
        {
          try {
            WC()->cart->empty_cart(); 
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

    public function getTotalQuantityPurchased($user_id, $product_id, $current_year= 0 ) {
        $current_year = date('Y');
        $total_quantity = 0;

        // Get all completed orders for the current year
        $orders = wc_get_orders([
          'limit'      => -1,
          'customer_id'=> $user_id,
          'status'     => 'completed',
          'date_query' => [
              [
                  'after'     => $current_year . '-01-01',
                  'inclusive' => true,
              ],
          ],
        ]);
        foreach ($orders as $order) {
            $order_items = $order->get_items();
            foreach ($order_items as $item) {
                if ($item->get_product_id() === $product_id) {
                    $quantity = $item->get_quantity();
                    $total_quantity += $quantity;
                    break;
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

    public function restrict_repeater_rows( $field  )
    {
      $exhibitor_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : '';
      $max_rows = $this->getTotalQuantityPurchased($exhibitor_id, 18792) ? $this->getTotalQuantityPurchased($exhibitor_id, 18792) : 0;
      $field ['max'] = $max_rows;
      return $field ;
    }

    public function filter_repeater_field_value($value, $post_id, $field) {
      $current_year = date('Y');
      $user_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : $post_id;
      $meta_key = '_repeater_assigned_booth_numbers_' . $current_year;
      $repeater_data = get_user_meta($user_id, $meta_key, true);
      if ($repeater_data) {
          $value = $repeater_data;
      }else{
        $value ='';
      }
      return $value;
    }
  
    public function update_repeater_field_value( $value, $post_id, $field, $original ) {
        $current_year = date('Y');
        $user_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : $post_id;
        $meta_key = '_repeater_assigned_booth_numbers_' . $current_year;
        update_user_meta($user_id, $meta_key, $original);    
        return $value;
    }

    public function send_activation_email($user_id) {
      $user = get_userdata($user_id);
      $user_email = $user->user_email;
      $first_name = $user->first_name;
      $last_name = $user->last_name;
      $activation_key = wp_generate_password(20, false);
      update_user_meta($user_id, 'activation_key', $activation_key);
      // $active_link = '<a href="' . home_url().'/reset-password?user_id='.$user_id. '">Activate Account</a>';
      $active_link = $this->exhibitor_create_activation_link($user_id);
      $sign_in = '<a href="' . home_url('sign-in').'">Activate Account</a>';
      $subject = 'ASGMT Account Activation';
      $message = '<p>Dear '.$first_name.' '. $last_name .',</p>';
      $message .= '<p>You\'re one step closer to creating your new ASGMT account. Please click the link to activate your account: <a href="'.$active_link.'">Activate Account</a><p>';
      $message .= '<p>If you already have an ASGMT account you don\'t have to activate your account. Please click here to login: '.$sign_in.'<p>';
      $message .= '<p>Best Regards,<br>ASGMT Committee<p>';
      $headers = array('Content-Type: text/html; charset=UTF-8');
      $mail = wp_mail($user_email, $subject, $message, $headers);
    }

    public function exhibitor_generate_activation_key($user_id) {
      $activation_key = wp_generate_password(20, false);
      update_user_meta($user_id, 'activation_key', $activation_key);
      return $activation_key;
    }
    
    public function exhibitor_create_activation_link($user_id) {
      $activation_key = $this->exhibitor_generate_activation_key($user_id);
      $activation_url = add_query_arg(
          array(
              'action' => 'activate',
              'key' => $activation_key,
              'user_id' => $user_id
          ),
          home_url('reset-password')
      );
  
      return $activation_url;
    }

    public function activate_user_account() {
      ob_start();
      if (isset($_GET['action']) && $_GET['action'] === 'activate' && isset($_GET['key']) && isset($_GET['user_id'])) {
          $activation_key = $_GET['key'];
          $user_id = $_GET['user_id'];
          $stored_key = get_user_meta($user_id, 'activation_key', true);
          if ($activation_key === $stored_key) {
            echo "<style>.reset-password-true { display: block !important;} .reset-password-false { display: none !important;}</style>";
            delete_user_meta($user_id, 'activation_key');
          }else{
            echo "<style>.reset-password-true { display: none !important;}.reset-password-false { display: block !important;}</style>";
          }
      }
      $oput = ob_get_contents();
      return ob_get_clean();      
    }

    public function add_representative_callback()
    {
      $nonce = $_POST['security'];
      if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
          wp_send_json_error( 'Invalid nonce.' );
      }
      $representative_data = array(
        'representative_first_name' => $_POST['representative_first_name'],
        'representative_last_name'  => $_POST['representative_last_name'],
        'representative_email'      => $_POST['representative_email'],
        'representative_contact'    => $_POST['representative_contact']
      );
      $update = update_user_meta( $_POST['booth_admin'], '_representative_data', $representative_data );
      if(is_wp_error( $update ))
      {
        wp_send_json_error();
      }else{
        wp_send_json_success();
      }
    }

    public function notes_callback()
    {
      $nonce = $_POST['security'];
      if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
          wp_send_json_error( 'Invalid nonce.' );
      }
      $update = update_user_meta( $_POST['booth_admin'], '_notes', $_POST['notes'] );
      if(is_wp_error( $update ))
      {
        wp_send_json_error();
      }else{
        wp_send_json_success();
      }
    }

    public function my_booth_status( $atts, $content, $tag )
    {
      // ob_start();
        if ( is_user_logged_in() ) :
          $user_id = get_current_user_id(); // Get the current user ID
          // echo $atts['booth_product'];
          if($atts['booth_product'] == 'true')
          {
            $current_year = date('Y'); // Get the current year
            $product_id = 18792; // Replace with the desired product ID
            $args = array(
              'status'        => array( 'completed', 'pending' ), // Retrieve orders with any status
              'customer_id'   => $user_id, // Filter orders by user ID
              'orderby'       => 'date', // Order by date
              'order'         => 'DESC', // Sort in descending order
              'limit'         => 1, // Retrieve only one order
              'date_query'    => array(
                'year'      => $current_year, // Filter orders by the current year
              ),
              'meta_query'    => array(
                  array(
                      'key'       => '_product_id', // Filter orders by product ID
                      'value'     => $product_id,
                      'compare'   => '=',
                  ),
              ),
            );
          
          $query = new WC_Order_Query( $args );
          $orders = $query->get_orders();
          
          if ( ! empty( $orders ) ) {
              $latest_order = reset( $orders );
              if($latest_order->get_status() === 'pending')
              {
                printf('<a href="%s">Pay Now</a>', esc_url( $latest_order->get_checkout_payment_url() ));
              }
              if($latest_order->get_status() === 'completed')
              {
                printf('<a href="javascript:void(0);">%s</a>', 'Paid');
              }
          } else {
            $checkout_link = wc_get_checkout_url() . '?add-to-cart=18792&quantity=1';
            printf('<a href="%s">Pay Now</a>', esc_url( $checkout_link ));
          }
        }
        if($atts['company_name'])
        {
          if(get_user_meta( $user_id, 'user_employer', true ) || get_user_meta( $user_id, 'billing_company', true ))
          {
            echo get_user_meta( $user_id, 'user_employer', true ) ? get_user_meta( $user_id, 'user_employer', true ) : get_user_meta( $user_id, 'billing_company', true );
          }          
        }
        if($atts['booth_numbers'] && !empty($this->get_booth_numbers_by_user_id( $user_id )))
        {
          echo $this->get_booth_numbers_by_user_id( $user_id );
        }
        if($atts['booth_managers'])
        {//alternate_booth_admin_first_name
          if(get_user_meta( $user_id, 'alternate_booth_admin_first_name', true ) || get_user_meta( $user_id, 'alternate_booth_admin_last_name', true ))
          {
            echo get_user_meta( $user_id, 'alternate_booth_admin_first_name', true ) .' '. get_user_meta( $user_id, 'alternate_booth_admin_last_name', true );
          }
          
        }
        //ASGMT Booth Representative
        if($atts['booth_representative'])
        {//alternate_booth_admin_first_name
          $representative_data = get_user_meta( $user_id, '_representative_data', true );
          if(!empty($representative_data))
          {
            $representative_first_name = isset($representative_data['representative_first_name']) ? $representative_data['representative_first_name'] : '';
            $representative_last_name = isset($representative_data['representative_last_name']) ? $representative_data['representative_last_name'] : '';
            $representative_email = isset($representative_data['representative_email']) ? $representative_data['representative_email'] : '';
            $representative_contact = isset($representative_data['representative_contact']) ? $representative_data['representative_contact'] : '';
            echo "<p>".$representative_first_name .' '. $representative_last_name."<br />". $representative_email."<br />".$representative_contact. "</p>";
          }
          
        }
      endif;
      // $out = ob_get_contents();
      // ob_get_clean();
      // return $out;
    }
    public function get_booth_totalizer() {
      $total_qty = 0;
      $args = array(
        'role__in'  => array('exhibitsmember','exhibitpending'),
      );
      $user_query = get_users($args);
      if(!empty($user_query)) {
        foreach ($user_query as $user) {
          // $total_qty += $this->get_booth_numbers_by_user_id( (int)$user->ID );
          $total_qty += is_numeric( get_user_meta($user->ID, 'booth_count', true)) ? get_user_meta($user->ID, 'booth_count', true) : 0;
        }
      }
      echo (177 - $total_qty) .' of 177 Booths remains';
    }

    public function get_booth_product_status()
    {
      $current_year = date('Y'); // Get the current year
      $product_id = 18792; // Replace with the desired product ID
      $args = array(
        'status'        => array( 'completed' ), // Retrieve orders with any status
        'customer_id'   => get_current_user_id(), // Filter orders by user ID
        'date_query'    => array(
          'year'      => $current_year, // Filter orders by the current year
        ),
        'meta_query'    => array(
            array(
                'key'       => '_product_id', // Filter orders by product ID
                'value'     => $product_id,
                'compare'   => '=',
            ),
        ),
      );
    
      $query = new WC_Order_Query( $args );
      $orders = $query->get_orders();
      if(!empty($orders))
        return count($orders) > 0 ? 1 : 0;

      return 0;
    }

    public function reset_password_exhibitor( $entry, $form )
    {
      $user_password  = rgar( $entry, '1' );
      $user_id        = rgar( $entry, '2' );
      $pass           = wp_set_password( $user_password, $user_id );
      if(is_wp_error( $pass ))
      {

      }else{

      }
    }

    public function add_booth_count_callback()
    {
      try {
        $customer_id = $_POST['customer_id'];          
        $update_booth_count = update_user_meta($customer_id, 'booth_count', $_POST['count']);    
        if(is_wp_error( $update_booth_count ))      
        {
          wp_send_json_error();
        }else{
          wp_send_json_success();
        }
      } catch (\Throwable $th) {
        wp_send_json_error();
      }
    }

    public function exhibitor_after_user_registered( $user_id, $feed, $entry, $user_pass )//input_19_34
    {
      error_log(print_r('$entry', true));
      error_log(print_r($entry, true));
      update_user_meta( $user_id, 'type_of_the_company', rgar( $entry, '34' ) );
    }
    public function check_email_exist_callback()
    {
      if(!empty($_POST['email']))
      {
        $email = email_exists($_POST['email']);
        if($email)
        {
          wp_send_json_success( array('message' => 'Registered') );
        }
      }
      wp_send_json_error();
    }
}
// Instantiate the ExhibitorManagement class
function initialize_exhibitor_management() {
  global $exhibitor_management;
  $exhibitor_management = new ExhibitorManagement();
}

// Hook into the 'plugins_loaded' action to initialize the Exhibitor Management functionality
add_action('init', 'initialize_exhibitor_management');
