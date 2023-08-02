<?php
// Define the ExhibitorManagement class
class ExhibitorManagement {
    public function __construct() {

      add_filter('woocommerce_payment_complete', array($this, 'exhibitor_members_payment_complete'));
      add_action( 'woocommerce_order_status_completed', array( $this, 'exhibitor_members_payment_complete' ) );
      if(is_admin())
      {
        // Hook into WordPress admin_menu to add the Exhibitor Management page
        add_action('admin_menu', array($this, 'add_exhibitor_management_page'));
        add_action( 'admin_enqueue_scripts', array($this, 'load_exhibitor_admin_style') );
    
        // Hook into admin_post action to handle CSV export
        add_action('wp_ajax_get_exhibitor_members', array($this, 'get_exhibitor_members'));
        add_filter( 'gform_confirmation_19', array($this, 'exhibitor_members_admin_confirmation'), 10, 4 );
  
        add_filter( 'gform_confirmation_18', array($this, 'exhibitor_members_admin_confirmation'), 10, 4 );
        // Hook into the 'gform_user_registration_validation' filter
        add_filter( 'gform_user_registration_validation', array($this, 'bypass_existing_user_registration'), 5, 3 );
  
        add_action('wp_ajax_update_company_status', array($this, 'update_company_status_callback'));
        add_action('wp_ajax_update_plane_to_exhibitor_status', array($this, 'update_plane_to_exhibitor_status'));
        add_action('wp_ajax_assign_booth_products', array($this, 'assign_booth_products'));
        
        add_filter('acf/prepare_field/name=booth_numbers', array($this, 'restrict_repeater_rows'), 10);
        
        add_filter('acf/load_value/name=booth_numbers', array($this, 'filter_repeater_field_value'), 10, 3);
        add_filter('acf/update_value/name=booth_numbers', array($this, 'update_repeater_field_value'), 10, 4);
        
        
        add_action( 'wp_ajax_add_representative', array($this, 'add_representative_callback') );
        add_action( 'wp_ajax_notes', array($this, 'notes_callback') );
  
        
        add_shortcode('get_booth_product_status', array($this, 'get_booth_product_status'));
        add_shortcode('payment_history', array($this, 'view_payment_history_exhibitor'));
        
        add_action( 'gform_after_submission_20', array($this, 'reset_password_exhibitor'), 10, 2 );
        add_action( 'wp_ajax_add_booth_count', array($this, 'add_booth_count_callback'));
        
        add_action( 'wp_ajax_check_email_exist', array($this, 'check_email_exist_callback'));
        
        add_action( 'admin_enqueue_scripts', array($this, 'jquery_tab_ui_admin_scripts') );
        add_action( 'wp_enqueue_scripts', array($this, 'jquery_tab_ui_admin_scripts') );
        // if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'exhibitor-management')
        add_action( 'wp_ajax_get_total_booth_count_by_companies_status', array($this, 'get_total_booth_count_by_companies_status_callback'));
      }
      
      add_shortcode('my_booth_status', array($this, 'my_booth_status'));
      add_shortcode('activate_user_account', array($this, 'activate_user_account'));
    }

    public function load_exhibitor_admin_style()
    {
      wp_enqueue_style( 'exhibitor_admin_css', get_theme_file_uri('/inc/assets/css/exhibitor-admin.css'), false, time() );
      // Enqueue DataTables scripts and styles
      $current_screen = get_current_screen();
      if($current_screen->id == 'toplevel_page_exhibitor-management')
      {
        wp_enqueue_script('jquery');
        wp_enqueue_style('datatables-management', '//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), '1.13.4');
        wp_enqueue_style('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css', array(), '2.3.6');
        wp_enqueue_style('datatables-fixedHeader', '//cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css', array(), '2.4.1');
        
        wp_enqueue_script('datatables-management', '//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true);
        wp_enqueue_script('datatables-fixedHeader', '//cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js', array(), '3.4.0', true);
        
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
        'current_screen'  => $current_screen->id,
        'assigned_booth_numbers' => $this->get_all_assigned_booth_numbers()
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

    public function jquery_tab_ui_admin_scripts() {
      wp_enqueue_style('jquery-tab-ui', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', array(), '1.13.2');
      wp_enqueue_script('jquery-tab-ui', '//code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), '1.13.2', true);
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
        <span id="total-booth-count-by-companies-status"></span>
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
          <option value="confirm_contact">Confirm Contact (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'confirm_contact')); ?>)</option>
          <option value="account_pending">Account Pending (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'account_pending')); ?>)</option>
          <option value="account_activated">Account Activated (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'account_activated')); ?>)</option>
          <option value="booth_pending">Booth Pending (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'booth_pending')); ?>)</option>
          <option value="pending_payment">Pending Payment (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'pending_payment')); ?>)</option>
          <option value="payment_complete">Payment Complete (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'payment_complete')); ?>)</option>
          <option value="complete">Complete (<?php echo $this->count_post_by_meta_key_value(array('meta_value' => 'complete')); ?>)</option>        
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
      <table id="exhibitor-members-list" style="width:100%" class="display nowrap">
        <thead>
          <tr>
            <th>No.</th>
            <th>Status</th>
            <th>Company name</th>
            <th>Plan to exhibit</th>
            <th>Booth counts</th>
            <th>Booth number(s)</th>
            <th>Primary First name</th>
            <th>Primary Last name</th>
            <th>Primary Email</th>
            <th>Alternate First name</th>
            <th>Alternate Last name</th>
            <th>Alternate Email</th>
            <th>Exhibitor rep. first name</th>
            <th>Exhibitor rep. last name</th>
            <!-- <th>Dated created</th>
            <th>Date paid </th>
            <th>Payment status</th> -->
            <th>Company id</th>
            <th>Date Approved Exhibitor List</th>
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
      $company_title = get_post( $_REQUEST['company_id'] ) ? get_post_meta($_REQUEST['company_id'], 'user_employer', true) : '-';
      ?>
      <div class="wrap">
        <h1>Exhibitor Profile | <span><?php echo $company_title; ?></span></h1>
        <div class="exhibitor-profile-wrap">
            <?php
              if(isset($_REQUEST['company_id']))
              {
                $company_post_id = $_REQUEST['company_id'];
                if(get_post( $company_post_id ))
                {
                  $company_post_meta_data = (object)$this->get_field_values_post_id( $company_post_id );
                  $company_post_status = get_post_meta($company_post_id, '_exhibitor_status', true);
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
                          <select data-row="<?php echo $company_post_id;?>" data-assign_booth_number="<?php echo $this->get_booth_numbers_by_company_id( $company_post_id );?>" class="status-select" style="width:50%">
                              <option value="-">Select</option>
                              <option <?php selected( $company_post_status, 'confirm_contact' ); ?> value="confirm_contact" selected="">Confirm Contact</option>
                              <option <?php selected( $company_post_status, 'account_pending' ); ?> value="account_pending">Account Pending</option>
                              <option <?php selected( $company_post_status, 'account_activated' ); ?> value="account_activated">Account Activated</option>
                              <option <?php selected( $company_post_status, 'booth_pending' ); ?> value="booth_pending">Booth Pending</option>
                              <option <?php selected( $company_post_status, 'pending_payment' ); ?> value="pending_payment">Pending Payment</option>
                              <option <?php selected( $company_post_status, 'payment_complete' ); ?> value="payment_complete">Payment Complete</option>
                              <option <?php selected( $company_post_status, 'complete' ); ?> value="complete">Complete</option>
                          </select>
                        </div>
                        <?php 
                          gravity_form(19, true, false, false, '', false, '', true ); 
                        ?>
                      </div>
                    </div>
                    <div id="booth-admin-tabs-assign-booth">
                      <div class="assign-booth-products">
                        <h1>Assign Booths</h1>
                        <form action="" id="assign-booth-product-exhibitor">
                          <input type="hidden" name="company_id" value="<?php echo $company_post_id;?>" />
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
                                  $boothCount = is_numeric(get_post_meta($company_post_id, 'booth_count', true)) ? get_post_meta($company_post_id, 'booth_count', true) : 0;
                                  $boothCountPrice = $boothCount === 0 ? 0 : $boothCount * $product_price;
                                  echo "Booth Count : <input type='number' value='".$boothCount."' id='calculatePrice' min='1' step='1' max='99' data-product_id='". $product_id ."' data-price='". $product_price ."' />";
                                  echo '<p>Total Value: $<span id="totalValue">'. $boothCountPrice .'</span></p>';
                              }

                              $primary_booth_admin_id = get_post_meta($company_post_id, 'primary_booth_admin', true);
                              $alternate_booth_admin_id = get_post_meta($company_post_id, 'alternate_booth_admin', true);

                              $order_status = array();

                              $booth_admin_ids = array_filter([$primary_booth_admin_id, $alternate_booth_admin_id], 'strlen');

                              if (!empty($booth_admin_ids)) {
                                  foreach ($booth_admin_ids as $customer_id) {
                                      $last_order_id = $this->get_last_order_id_by_product_and_customer(18792, $customer_id);
                                      if (!empty($last_order_id)) {
                                          $order_status[] = $last_order_id;
                                      }
                                  }
                              }

                              $send_invoice_flag = 1; // Default value

                              if (!empty($order_status)) {
                                  if (array_intersect(array('processing', 'on-hold', 'completed', 'cancelled' , 'refunded', 'failed'), $order_status)) {
                                      $send_invoice_flag = 1;
                                  } elseif (count($order_status) === 1) {
                                      $send_invoice_flag = $order_status[0] === 'pending' ? 0 : 1;
                                  }else{
                                    $send_invoice_flag = 0;
                                  }
                              }
                            ?>
                          <!-- </select> -->
                          <div class="action-btn">
                            <input type="submit" class="button" value="Save Count" id="save-count-booth" />  
                            <input type="submit" class="button" data-status="<?php echo $send_invoice_flag; ?>" value="Send Invoice" id="send-invoice-assign-booth" />
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
                              <h4><?php echo $this->getTotalQuantityPurchased($company_post_id, 18792); ?> Booths Purchased</h4>
                              <?php
                                  if($this->getTotalQuantityPurchased($company_post_id, 18792) > 0 )
                                  {
                                    acf_form_head();
                                    $current_year = date('Y');
                                    acf_form(array(
                                      'post_id'             => $company_post_id,
                                      'field_groups'        => array('group_649950635643a'),
                                      'fields'              => array('booth_numbers'),
                                      'form'                => true,
                                      'return'              => add_query_arg('updated', 'true', site_url('wp-admin/admin.php?page=edit-exhibitor-profile&company_id='.$company_post_id.'#booth-admin-tabs-assign-booth')),
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
                                  $Booth_numbers_history = get_post_meta($company_post_id, '_repeater_assigned_booth_numbers_'.$current_year -1, true);
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
                              <?php 
                                // gravity_form(14, false, false, false, null, true, '', true );
                                acf_form_head();
                                acf_form(array(
                                  'post_id'             => $company_post_id,
                                  'field_groups'        => array('group_64994a6feb53a'),
                                  'fields'              => array('assistants'),
                                  'form'                => true,
                                  'return'              => add_query_arg('updated', 'true', site_url('wp-admin/admin.php?page=edit-exhibitor-profile&company_id='.$company_post_id.'#booth-admin-tabs-assistant')),
                                  'submit_value'        => 'Register Assistant Now',
                                  'html_updated_message' => __('Assistant successfully updated', 'savior-pro'),
                                )); 
                              ?>
                          </div>
                        </div>
                        <?php echo $this->list_company_assistants( $company_post_id ); ?>
                        <?php //echo do_shortcode( '[exhibit_assistant_list exhibitor_id="'.$company_post_id.'"]' ); ?>
                      </div>
                    </div>
                    <div id="booth-admin-tabs-representative">
                      <h1>Representative</h1>
                      <form id="representative-form">
                        <?php 
                          $representative_first_name = $company_post_meta_data->representative_first_name;
                          $representative_last_name = $company_post_meta_data->representative_last_name;
                          $representative_email = $company_post_meta_data->representative_email;
                          $representative_contact = $company_post_meta_data->representative_contact;
                        ?>
                        <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'exhibitor_assistant' ); ?>" />
                        <input type="hidden" name="booth_admin" value="<?php echo $company_post_id;?>">
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
                        <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'exhibitor_assistant' ); ?>" />
                        <input type="hidden" name="booth_admin" value="<?php echo $company_post_id;?>">
                        <input type="hidden" name="action" value="notes" />
                        <div class="form-group">
                          <textarea name="notes"class="form-control" id="notes" rows="15" cols="100"><?php echo $company_post_meta_data->notes; ?></textarea>
                        </div>
                        <button type="submit" id="notes-save" class="btn btn-primary">Submit</button>
                      </form>
                    </div>
                    <div id="booth-admin-tabs-payment">
                      <h1>Payment History </h1>
                        <?php
                          if(is_admin())
                            echo do_shortcode('[payment_history]' );
                        ?>
                    </div>
                  </div>
                  <?php
                }else{
                  echo "<div class='notice notice-error'><p><b> Exhibitor doesn't exists with the ID ".$company_post_id." </b></p></div>";
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
    
    public function get_field_values_post_id( $post_id )
    {
      $post_meta = array();
      if($post_data = get_post_meta( $post_id ))
      {
        foreach($post_data as $key => $post)
        {
          $post_meta[$key] = is_serialized($post[0]) ? maybe_unserialize($post[0]) : $post[0];
        }
      }
      return $post_meta;
    }

    public function list_company_assistants( $post_id )
    {
      ob_start();
      $get_assistants_ids = get_post_meta( $post_id, 'assistants', true );
      if(!empty($get_assistants_ids))
      {
        wp_enqueue_script( 'jquery-tab-ui' );
        wp_enqueue_style( 'jquery-tab-ui' );
        ?>
        <div id="accordion">
            <?php foreach ($get_assistants_ids as $assistant_id) : 
                $assistant = get_userdata($assistant_id);
                ?>
                <h3><?php echo $assistant->first_name.' '.$assistant->last_name; ?></h3>
                <div id="tab-<?php echo $assistant_id;?>" class="accordion-content">                        
                    <div class="assistants-billing-address assistant-addres-view-<?php echo $assistant_id; ?>">
                        <?php                                                                 
                            echo '<p><span>First Name: </span>' . $assistant->first_name . '</p>';
                            echo '<p><span>Last Name: </span>' . $assistant->last_name . '</p>';
                            echo '<p><span>Email: </span>' . $assistant->user_email . '</p>';
                        ?>                                
                    </div>                        
                </div>
            <?php endforeach; ?>
        </div>

        <?php
      }else{
          echo "<p>No assistants added yet.</p>";
      }
      $out = ob_get_contents();
      ob_get_clean();
      return $out;
    }

    public function view_payment_history_exhibitor( $atts ) {
      if(isset($_REQUEST['company_id']))
      {
        $my_orders_columns = apply_filters(
          'woocommerce_my_account_my_orders_columns',
          array(
            'order-number'  => esc_html__( 'Order', 'woocommerce' ),
            'order-items'   => esc_html__( 'Items', 'woocommerce' ),
            'date-created'  => esc_html__( 'Date Created', 'woocommerce' ),
            'date-paid'     => esc_html__( 'Date Paid', 'woocommerce' ),
            'order-status'  => esc_html__( 'Status', 'woocommerce' ),
            'order-total'   => esc_html__( 'Total', 'woocommerce' ),
            // 'order-actions' => esc_html__( 'Action', 'woocommerce' ),
          )
        );
        $payment_history = get_post_meta($_REQUEST['company_id'], 'payment_history', true);
        if(!empty($payment_history) && is_array($payment_history)) {
          $payment_history = array_filter($payment_history);
          $customer_orders = get_posts(
            array(
              'numberposts' => -1,
              'post_status' => 'publish',
              'post_type'   => 'shop_order',
              'post_status' => 'all',
              'meta_query' => array(
                array(
                    'key' => '_customer_user',
                    'value' => $payment_history,
                    'compare' => 'IN'
                )
              )
            )
          );
        }else{
          $customer_orders = false;
        }
        
        if ( $customer_orders ) : ?>
        
          <h2><?php echo apply_filters( 'woocommerce_my_account_my_orders_title', esc_html__( 'Recent orders', 'woocommerce' ) ); ?></h2>
        
          <table class="shop_table shop_table_responsive my_account_orders">
        
            <thead>
              <tr>
                <?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
                  <th class="<?php echo esc_attr( $column_id ); ?>"><span class="nobr"><?php echo esc_html( $column_name ); ?></span></th>
                <?php endforeach; ?>
              </tr>
            </thead>
        
            <tbody>
              <?php
              foreach ( $customer_orders as $customer_order ) :
                $order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
                $item_count = $order->get_item_count();
                ?>
                <tr class="order">
                  <?php foreach ( $my_orders_columns as $column_id => $column_name ) : ?>
                    <td class="<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
                      <?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
                        <?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>
        
                      <?php elseif ( 'order-number' === $column_id ) : ?>
                      <?php echo _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number(); ?>

                      <?php elseif ( 'order-items' === $column_id ) : ?>
                      <?php 
                          $product_name_by_order = [];
                          foreach( $order->get_items() as $order_item ) {
                            $product = $order_item ->get_product();
                            $product_name_by_order[] = $product->get_name();
                          }
                          echo implode(', ', $product_name_by_order);
                      ?>
        
                      <?php elseif ( 'date-created' === $column_id ) : ?>
                        <time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?></time>

                      <?php elseif ( 'date-paid' === $column_id ) : 
                        if( $order->get_status() == 'completed')
                        {

                          ?>
                          <time datetime="<?php echo esc_attr( $order->get_date_paid()->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $order->get_date_paid() ) ); ?></time>
                          <?php 
                        }else{
                          echo '-';
                        }
                        ?>
                      <?php elseif ( 'order-status' === $column_id ) : ?>
                        <?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
        
                      <?php elseif ( 'order-total' === $column_id ) : ?>
                        <?php
                        /* translators: 1: formatted order total 2: total order items */                        
                        printf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        ?>
                      <?php endif; ?>
                    </td>
                  <?php endforeach; ?>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php 
          else:
            echo '<p>No record found.</p>';
        endif; 
      }
    
    }

    public function get_exhibitor_members() {
      if(is_admin())
      {
        $args = array(
          'post_type'      => 'companies',
          'post_status'    => 'publish',
          'posts_per_page' => -1,
          'no_found_rows'  => true,
          'fields'         => 'ids',
        );
        
        $query = new WP_Query($args);        
        $post_ids = $query->posts;        
        if(!empty($post_ids))
        {
          $companies_data = array();
          foreach ($post_ids as $post_id) {
            $primary_booth_admin_id = get_post_meta($post_id, 'primary_booth_admin', true);
            $primary_user           = !empty($primary_booth_admin_id) ? new WP_User( $primary_booth_admin_id ) : false;
            
            $alternate_booth_admin_id = get_post_meta($post_id, 'alternate_booth_admin', true);
            $alternate_user         = !empty($alternate_booth_admin_id) ? new WP_User( $alternate_booth_admin_id ) : false;

            if($primary_user)
            {
              $exhibit_booth_number = $this->get_booth_numbers_by_company_id( $post_id ) !== 0 ? $this->get_booth_numbers_by_company_id( $post_id ) : ( ($this->has_user_purchased_product( $primary_booth_admin_id, 18792 ) && $this->get_booth_numbers_by_company_id( $post_id ) == 0 ) ? 'Not Assigned' : 0);
            }else{
              $exhibit_booth_number = $this->get_booth_numbers_by_company_id( $post_id ) !== 0 ? $this->get_booth_numbers_by_company_id( $post_id ) : ( ($this->has_user_purchased_product( $alternate_booth_admin_id, 18792 ) && $this->get_booth_numbers_by_company_id( $post_id ) == 0 ) ? 'Not Assigned' : 0);
            }
            $companies_data[] = array(
                'no'                    => '',
                'status'                => get_post_meta( $post_id, '_exhibitor_status', true ),
                'company_name'          => get_post_meta( $post_id, 'user_employer', true ),
                'plan_to_exhibit'       => get_post_meta( $post_id, '_plan_to_exhibit', true ),
                'booth_count'           => get_post_meta( $post_id, 'booth_count', true ),
                'exhibit_booth_number'  => $exhibit_booth_number,
                'primary_first_name'    => $primary_user->first_name,
                'primary_last_name'     => $primary_user->last_name,
                'primary_email'         => $primary_user->user_email,
                'alternate_first_name'  => isset($alternate_user) && !empty($alternate_user) ? $alternate_user->first_name : '-',
                'alternate_last_name'   => isset($alternate_user) && !empty($alternate_user) ? $alternate_user->last_name : '-',
                'alternate_email'       => isset($alternate_user) && !empty($alternate_user) ? $alternate_user->user_email : '-',
                'exhibit_rep_first_name'=> get_post_meta( $post_id, 'representative_first_name', true ),
                'exhibit_rep_last_name' => get_post_meta( $post_id, 'representative_last_name', true ),
                'date_created'          => '',
                'date_paid'             => '',
                'payment_status'        => '',
                'id'                    => $post_id,
                'date_of_registration'  => get_post_meta( $post_id, 'date_approved_on_exhibitors_list', true ) ? date('Y-m-d', strtotime(get_post_meta( $post_id, 'date_approved_on_exhibitors_list', true ))) : '',
                'active_status'         => get_post_meta( $post_id, '_exhibitor_status', true ),
                'active_plan_to_exhibit'=> get_post_meta( $post_id, '_plan_to_exhibit', true )
            );
          }
          wp_reset_postdata();
          wp_send_json( array( 'data' => $companies_data ) );
        }else{
          wp_send_json_error( array( 'data' => [] ) );
        }      
      }else{
          wp_send_json_error( array( 'data' => [] ) );
      }
    }



    public function get_booth_numbers_by_company_id( $company_id )
    {
      $booth_numbers = get_post_meta($company_id, '_repeater_assigned_booth_numbers_'.date('Y'), true);
      $booth_num = array();
      if(!empty($booth_numbers))
      {
        foreach($booth_numbers as $key => $booth_number)
        {
          $booth_num[] = $booth_number['field_64995177d5025'];
        }
        $exhibit_booth_number = implode(',', $booth_num);
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
          $confirmation = array( 'redirect' => admin_url( 'admin.php?page=edit-exhibitor-profile&company_id='.rgar( $entry, '31' ) ) );
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
              'first_name'                        => isset($_POST['input_10_3']) ? $_POST['input_10_3'] : '',
              'last_name'                         => isset($_POST['input_10_6']) ? $_POST['input_10_6'] : '',
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
          $_company_id = get_post_meta($order_id, '_company_id', true );
          $primary_booth_admin    = get_user_by('id', get_post_meta($_company_id, 'primary_booth_admin', true));
          $alternate_booth_admin  = get_user_by('id', get_post_meta($_company_id, 'alternate_booth_admin', true));
          if($primary_booth_admin && $main_user_id !== $primary_booth_admin->ID)
          {
            $primary_booth_admin->add_role('exhibitsmember');  
            $primary_booth_admin->remove_role('exhibitpending'); 
          }
          if($alternate_booth_admin && $main_user_id !== $alternate_booth_admin->ID)
          {
            $alternate_booth_admin->add_role('exhibitsmember');  
            $alternate_booth_admin->remove_role('exhibitpending'); 
          }
          update_post_meta($_company_id, '_exhibitor_status', 'payment_complete');
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
      //Insert attendee order data
      $this->create_table_order_data();
      $attendees_order_data = $this->insert_order_data_with_order_items( $order_id );
      if(!empty($attendees_order_data))
      {
        $this->insert_attendees_order_data_payment_complete( $attendees_order_data );
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
      foreach ($customer_orders as $customer_order) {
          $order_id = $customer_order->ID;
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

    public function update_company_status_callback()
    {
      if(isset($_POST['company_id']) && get_post_type($_POST['company_id']) == 'companies' ){
        // Get the user ID and new status value from the AJAX request
        $company_id    = $_POST['company_id'];
        $new_status = $_POST['new_status'];
        // Update the user meta field with the new status value
        $old_status = update_post_meta($company_id, '_exhibitor_status', true);
        $update_status = update_post_meta($company_id, '_exhibitor_status', $new_status);
        if(is_wp_error( $update_status ))
        {
          wp_send_json_error('Something error please try again!!!');
        }else{
          $all_status = array(
            'confirm_contact'   => 'Contact information confirmed successfully!',
            'account_pending'   => 'Account activation link sent to exhibitor successfully!',
            'account_activated' => 'Account Activated Successfully. Please assign booth to the exhibitor!',
            'booth_pending'     => 'Booth payment request sent successfully, Please change status to pending payment!',
            'pending_payment'   => 'Status changed pending payment!',
            'payment_complete'  => 'Payment done by exhibitor!',
            'complete'          => 'Successfully completed!'
          );
          $primary_booth_admin_id = get_post_meta($company_id, 'primary_booth_admin', true);
          $alternate_booth_admin_id = get_post_meta($company_id, 'alternate_booth_admin', true);
          if($new_status == 'account_pending')
          {
            if(!empty($primary_booth_admin_id))
              $this->send_activation_email($primary_booth_admin_id);
              
            if(!empty($alternate_booth_admin_id))
              $this->send_activation_email($alternate_booth_admin_id);            
          }
          if(get_post_meta($company_id, '_exhibitor_status', true) == 'payment_complete')
          {
            $primary_user = get_userdata( $primary_booth_admin_id );
            $primary_user->add_role( 'exhibitsmember' );

            if($alternate_user = get_userdata( $alternate_booth_admin_id ))
              $alternate_user->add_role( 'exhibitsmember' );
          }
          wp_send_json_success(array('status' => $new_status, 'old_status' => $old_status, 'message' => $all_status[$new_status]));
        }
      }
    }

    public function update_plane_to_exhibitor_status()
    {
      if( isset($_POST['company_id']) ){
        $company_id = $_POST['company_id'];
        $new_status = $_POST['new_status'];

        // Update the user meta field with the new status value
        $new_status = update_post_meta($company_id, '_plan_to_exhibit', $new_status);
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
        $order_ids = array();
        $primary_booth_admin_id = get_post_meta( $_POST['company_id'], 'primary_booth_admin', true );
        $user_employer = get_post_meta( $_POST['company_id'], 'user_employer', true );
        $relation_between_orders = $this->generateRandomString();
        if(!empty($primary_booth_admin_id))
        {
          $order_ids[] = $this->create_order($_POST['products_ids'], $primary_booth_admin_id, $_POST['qty'], $_POST['company_id'], $user_employer, $relation_between_orders );
        }
        $alternate_booth_admin_id = get_post_meta( $_POST['company_id'], 'alternate_booth_admin', true );
        if(!empty($alternate_booth_admin_id))
        {
          $order_ids[] = $this->create_order($_POST['products_ids'], $alternate_booth_admin_id, $_POST['qty'], $_POST['company_id'], $user_employer, $relation_between_orders );
        }
        if(!empty($order_ids) && is_array($order_ids)){
          wp_send_json_success( $order_ids );
        }else{
          wp_send_json_error($order_ids);
        }
      }else{
        wp_send_json_error();
      }
    }

    public function create_order( $product_id, $customer_id, $qty = 0, $company_id, $user_employer = '', $relation_between_orders )
    {
      try {
        WC()->cart->empty_cart(); 
        $order = wc_create_order();
        $customer = new WC_Customer( $customer_id );
        $billing_address = array(
          'first_name' => $customer->get_billing_first_name() ? $customer->get_billing_first_name() : $customer->get_first_name(),
          'last_name'  => $customer->get_billing_last_name() ? $customer->get_billing_last_name() : $customer->get_last_name(),
          'company'    => $customer->get_billing_company() ? $customer->get_billing_company() : $user_employer,
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
          $quantity = $qty == 0 ? 1 : $qty;
          $product = wc_get_product($product_id); 
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
        update_post_meta($company_id, '_exhibitor_status', 'pending_payment');
        update_post_meta($order->get_id(), '_company_id', $company_id );
        update_post_meta($order->get_id(), '_relation_between_orders', $relation_between_orders );
        return $order->get_id();
        // wp_send_json_success( array('order_id' => $order->get_id()), 201 );
        
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }

    public function generateRandomString($length = 10) {
      // Generate random bytes and base64 encode them
      $randomBytes = random_bytes($length);
      $base64String = base64_encode($randomBytes);
  
      // Remove special characters from the base64 string
      $alphanumericString = strtr($base64String, '+/', '-_');
  
      // Generate a unique ID
      $uniqueID = uniqid();
  
      // Concatenate the base64 string and the unique ID
      $randomAlphanumericString = substr($alphanumericString, 0, $length - strlen($uniqueID)) . $uniqueID;
  
      return $randomAlphanumericString;
    }

    public function getTotalQuantityPurchased($company_post_id, $product_id, $current_year= 0 ) {
        $current_year = date('Y');
        $total_quantity = 0;
        $payment_history = get_post_meta( $company_post_id, 'payment_history', true );

        if(empty($payment_history)) return 0;

        $payment_history = array_filter($payment_history);
        // Get all completed orders for the current year
        $orders = wc_get_orders([
          'limit'      => -1,
          'customer'   => $payment_history,
          'status'     => array('wc-completed'),//'wc-processing','wc-pending'
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
    public function custom_send_order_invoice($order, $sent_to_admin, $plain_text, $email) 
    {
      // if (!$sent_to_admin && $order->has_status('pending')) {
          $mailer = WC()->mailer();
          $mailer->emails['WC_Email_Customer_Invoice']->trigger($order->get_id());//customer_invoice
      // }
    }

    public function restrict_repeater_rows( $field  )
    {
      $exhibitor_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : '';
      $max_rows = $this->getTotalQuantityPurchased($exhibitor_id, 18792) ? $this->getTotalQuantityPurchased($exhibitor_id, 18792) : 0;
      $field ['max'] = $max_rows;
      return $field ;
    }

    public function filter_repeater_field_value($value, $post_id, $field) {
      $current_year = date('Y');
      $user_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : $post_id;
      $meta_key = '_repeater_assigned_booth_numbers_' . $current_year;
      $repeater_data = get_post_meta($user_id, $meta_key, true);
      if ($repeater_data) {
          $value = $repeater_data;
      }else{
        $value ='';
      }
      return $value;
    }
  
    public function update_repeater_field_value( $value, $post_id, $field, $original ) {
        $current_year = date('Y');
        $user_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : $post_id;
        $meta_key = '_repeater_assigned_booth_numbers_' . $current_year;
        update_post_meta($user_id, $meta_key, $original);    
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
      $sign_in = '<a href="' . home_url('sign-in').'">Login</a>';
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
      $post_type = get_post_type($_POST['booth_admin']);

      if ($post_type === 'companies') {
          update_post_meta( $_POST['booth_admin'], 'representative_first_name', $_POST['representative_first_name'] );
          update_post_meta( $_POST['booth_admin'], 'representative_last_name', $_POST['representative_last_name'] );
          update_post_meta( $_POST['booth_admin'], 'representative_email', $_POST['representative_email'] );
          update_post_meta( $_POST['booth_admin'], 'representative_contact', $_POST['representative_contact'] );
          wp_send_json_success();
      }else{
        wp_send_json_error();
      }
    }

    public function notes_callback()
    {
      $nonce = $_POST['security'];
      if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
          wp_send_json_error( 'Invalid nonce.' );
      }
      $update = update_post_meta( $_POST['booth_admin'], 'notes', $_POST['notes'] );
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
      $atts = shortcode_atts(
        array(
          'booth_product' => false,
          'booth_numbers' => false,
          'company_name' => false,
          'booth_managers' => false,
          'booth_representative' => false,
        ), $atts, 'my_booth_status' );
        if ( is_user_logged_in() ) :
          $user_id = get_current_user_id(); // Get the current user ID
          $args = array(
            'posts_per_page' => 1,
            'post_type'   => 'companies',
            'fields' => 'ids',
            'meta_query' => array(
              array(
                'key'     => 'payment_history',
                'value'   => $user_id,
                'compare' => 'LIKE',
                // 'type'    => 'NUMERIC'
              )
            )
          );
          $company_query = new WP_Query( $args );
          $company_id =  (!empty($company_query->posts) && is_array($company_query->posts)) ? $company_query->posts[0] : false;      

          if($atts['booth_product'] == 'true')
          {
            $current_year = date('Y'); // Get the current year
            $product_id = 18792; // Replace with the desired product ID
            $args = array(
              'status'        => array( 'wc-completed', 'wc-pending' ), // Retrieve orders with any status
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
          if($company_id)       
          {
            echo get_post_meta($company_id , 'user_employer', true);
          }
        }
        if($atts['booth_numbers'] && !empty($this->get_booth_numbers_by_company_id( $company_id ))) // 
        {
          echo $this->get_booth_numbers_by_company_id( $company_id );
        }
        if($atts['booth_managers'])
        {
          $primary_booth_admin_id = get_post_meta($company_id, 'primary_booth_admin', true);
          $alternate_booth_admin_id = get_post_meta($company_id, 'alternate_booth_admin', true);
          if(get_user_meta( $primary_booth_admin_id, 'first_name', true ) || get_user_meta( $primary_booth_admin_id, 'last_name', true ))
          {
            echo get_user_meta( $primary_booth_admin_id, 'first_name', true ) .' '. get_user_meta( $primary_booth_admin_id, 'last_name', true );
            if(!empty(get_user_meta($alternate_booth_admin_id, 'first_name', true)))
            {
              echo ', ';
            }
            echo get_user_meta($alternate_booth_admin_id, 'first_name', true) . ' ' . get_user_meta($alternate_booth_admin_id, 'last_name', true);
          }
          
        }
        //ASGMT Booth Representative
        if($atts['booth_representative'])
        {//alternate_booth_admin_first_name
          $representative_first_name  = get_post_meta( $company_id, 'representative_first_name', true );
          $representative_last_name   = get_post_meta( $company_id, 'representative_last_name', true );
          $representative_email       = get_post_meta( $company_id, 'representative_email', true );
          $representative_contact     = get_post_meta( $company_id, 'representative_contact', true );
          if(!empty($representative_first_name) || !empty($representative_last_name) || !empty($representative_email) ||!empty($representative_contact) )
          {
            $representative_first_name = isset($representative_first_name) ? $representative_first_name : '';
            $representative_last_name = isset($representative_last_name) ? $representative_last_name : '';
            $representative_email = isset($representative_email) ? $representative_email : '';
            $representative_contact = isset($representative_contact) ? $representative_contact : '';
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
        'numberposts' => -1,
        'post_status' => 'publish',
        'post_type'   => 'companies',
        'fields'      => 'ids',
      );
      $user_query = get_posts($args);
      if(!empty($user_query)) {
        foreach ($user_query as $company_post_id) {
          $total_qty += is_numeric( get_post_meta($company_post_id, 'booth_count', true)) ? get_post_meta($company_post_id, 'booth_count', true) : 0;
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
        $company_id = $_POST['company_id'];          
        $update_booth_count = update_post_meta($company_id, 'booth_count', $_POST['count']);    
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

    public function get_all_assigned_booth_numbers()
    {
      $company_posts_ids = get_posts(
        array(
          'numberposts' => -1,
          'post_status' => 'publish',
          'post_type'   => 'companies',
          'fields'      => 'ids',
          'meta_query' => array(
            array(
                'key' => 'booth_numbers',
                'value' => '',
                'compare' => '!='
            )
          )
        )
      );
      $data = [];
      if(!empty($company_posts_ids))
      {
        foreach ($company_posts_ids as $company_post_id) {
          if($assigned_booth_numbers = $this->get_booth_numbers_by_company_id( $company_post_id ))
          {
            $data[] = $assigned_booth_numbers;
          }
        }
      }
      if(!empty($data) && is_array($data)) 
      {
        return implode(',',$data);
      }else{
        return '';
      }
    }

    public function count_post_by_meta_key_value( $args = array() )
    {
      $defaults = array(
        'post_type'       => 'companies',
        'meta_key'        => '_exhibitor_status',
        'meta_value'      => 'complete',
        'posts_per_page'  => -1,
      );
      $args = wp_parse_args( $args, $defaults );
      $query = new WP_Query($args);
      $count = $query->post_count;
      wp_reset_postdata();
      return $count;
    }

    public function get_total_booth_count_by_companies_status_callback()
    {
      if(isset($_POST['page']) && $_POST['page'] !== 'exhibitor-management')
      {
        return;
      }
      global $wpdb;
      $status = array(
        'confirm_contact',
        'account_pending',
        'account_activated',
        'booth_pending',
        'pending_payment',
        'payment_complete',
        'complete'
      );
      $booth_count = array();
      foreach ( $status as $state )
      {
        // $filter_meta_value = isset($_POST['status']) ? $_POST['status'] : 'complete';
        $filter_meta_key = '_exhibitor_status';
        $total_meta_key = 'booth_count';
        $companies_args = array(
          'post_type'      => 'companies',
          'post_status'    => 'publish',
          'meta_key'       => $filter_meta_key,
          'meta_value'     => $state,
          'posts_per_page' => -1,
          'fields'         => 'ids',
        );
        
        $query = new WP_Query($companies_args);        
        $post_ids = $query->posts;  
        if(!empty($post_ids))
        {
          $total_query = $wpdb->prepare("
              SELECT SUM(meta_value)
              FROM {$wpdb->postmeta}
              WHERE meta_key = %s
              AND post_id IN (" . implode(',', $post_ids) . ")
          ", $total_meta_key);
          $total_count = $wpdb->get_var($total_query);
          wp_reset_postdata();
          $booth_count[$state] = $total_count > 0 ? (int)$total_count : 0;
        }else{
          $booth_count[$state] = 0;
        }
      }
      return wp_send_json_success( $booth_count );
    }

    public function get_last_order_id_by_product_and_customer($product_id, $customer_id) 
    {
      global $wpdb;
      $current_year = date('Y');
      $last_order_id = $wpdb->get_var( $wpdb->prepare("
        SELECT MAX(p.ID)
        FROM {$wpdb->prefix}posts AS p
        INNER JOIN {$wpdb->prefix}postmeta AS pm ON p.ID = pm.post_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_items AS woi ON p.ID = woi.order_id
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS woim ON woi.order_item_id = woim.order_item_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status IN ('wc-pending', 'wc-processing', 'wc-on-hold', 'wc-completed', 'wc-cancelled' , 'wc-refunded', 'wc-failed')
        AND pm.meta_key = '_customer_user'
        AND pm.meta_value = %d
        AND woim.meta_key = '_product_id'
        AND woim.meta_value = %d
        AND YEAR(p.post_date) = %d
        ", $customer_id, $product_id, $current_year )
      );
      if(!empty($last_order_id))
      {
        $order = wc_get_order( $last_order_id );
        return $order->get_status();
      }
      return false;
    }

    public function insert_attendees_order_data_payment_complete( $attendees_order_data )
    {
      global $wpdb;
      $attendees_order_data['printed'] = 'Not Printed';
      // Insert data into the custom table
      $table_name = $wpdb->prefix . 'attendees_order_data';
      $wpdb->insert(
          $table_name,
          $attendees_order_data,
          array(
              '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s',
              '%s', '%s', '%d', '%s', '%d', '%f', '%d' // Adjust the data types accordingly
          )
      );
    }
  
    public function create_table_order_data()
    {
      global $wpdb;
      $table_name = $wpdb->prefix . 'attendees_order_data';
      if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        // Define the table name
    
        // Create the SQL query to create the custom table
        $sql = "CREATE TABLE $table_name (
            id INT NOT NULL AUTO_INCREMENT,
            order_id INT NOT NULL,
            customer_id INT NOT NULL,
            first_name VARCHAR(255) NOT NULL,
            last_name VARCHAR(255) NOT NULL,
            customer_email VARCHAR(255) NOT NULL,
            company VARCHAR(255),
            date_created DATETIME NOT NULL,
            order_date DATE NOT NULL,
            status VARCHAR(100) NOT NULL,
            cart_discount DECIMAL(10,2),
            order_discount DECIMAL(10,2),
            discount_total DECIMAL(10,2),
            order_currency CHAR(3) NOT NULL,
            payment_method VARCHAR(100) NOT NULL,
            product_id INT NOT NULL,
            product_name VARCHAR(255) NOT NULL,
            item_total DECIMAL(10,2) NOT NULL,
            order_total DECIMAL(10,2) NOT NULL,
            printed BOOLEAN NOT NULL DEFAULT 0,
            PRIMARY KEY (id)
        ) $wpdb->charset_collate;";
        // Include the necessary upgrade file
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        // Create the table
        dbDelta($sql);
      }
    }

    public function insert_order_data_with_order_items( $order_id )
    {
        $order_data     = array();
        $customer_ids   = array();
            $order                  = wc_get_order( $order_id );    
            $item_quantity = 0;
            foreach ($order->get_items() as $item_id => $item) {
                $item_quantity += $item->get_quantity();
            }
            $_gravity_form_entry_id = $order->get_meta('_gravity_form_entry_id');
            if( isset($_gravity_form_entry_id) && GFAPI::entry_exists($_gravity_form_entry_id) )
            {
                $entry = GFAPI::get_entry($_gravity_form_entry_id);
                if($entry['form_id'] == 11)
                {
                    $order_data[] = $this->get_order_customer_details_by_id($order);
                }
                $_attendees_order_meta  = $order->get_meta('_attendees_order_meta');
                if (($item_quantity > 1 && !empty($_attendees_order_meta) || $entry['form_id'] == 13)) {
                    foreach ($_attendees_order_meta as $_attendees) {
                        $order_data[] = $this->get_order_customer_details_by_id($order, (int)$_attendees['product_id'], (int)$_attendees['user_id']);
                    }
                }
            }else{
                $order_data[] = $this->get_order_customer_details_by_id($order);
            }        
        return $order_data;
    }

    public function get_order_customer_details_by_id($order, $product_id = null, $customer_id = null)
    {
        $product_data = array();
        foreach ($order->get_items() as $item_id => $item) {
            $product_data[$item->get_product_id()] = array(
                'product_id'    => $item->get_product_id(),
                'product_name'  => $item->get_name(),
                'item_total'    => ($item->get_total() / $item->get_quantity())
            );
        }
        $first_value    = reset($product_data);
        $user_data = get_userdata($order->get_user_id());
        $defaults_order_data = array(
            'order_id'              => $order->get_id(),
            'customer_id'           => $order->get_user_id(),
            'first_name'            => $user_data->first_name,
            'last_name'             => $user_data->last_name,
            'customer_email'        => ($a = get_userdata($order->get_user_id())) ? $a->user_email : '',
            'company'               => wp_slash(get_user_meta($order->get_user_id(), 'user_employer', true)),
            'date_created'          => $order->get_date_paid()->date("Y-m-d"),
            'order_date'            => $order->get_date_created()->date("Y-m-d"),//date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
            'status'                => $order->get_status(),
            'cart_discount'         => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
            'order_discount'        => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
            'discount_total'        => wc_format_decimal($order->get_total_discount(), 2),
            'order_currency'        => $order->get_currency(),
            'payment_method'        => $order->get_payment_method()            
        );
        $order_total = array('order_total' => wc_format_decimal($order->get_total(), 2));

        $defaults_order_data = $defaults_order_data  + $first_value + $order_total;
        if ($customer_id && $product_id) {
            $user_data = get_userdata($customer_id);
            $args = array(
                'first_name'            => $user_data->first_name,
                'last_name'             => $user_data->last_name,
                'customer_id'           => $customer_id,
                'customer_email'        => ($user_data) ? $user_data->user_email : '',
                'company'               => wp_slash(get_user_meta($customer_id, 'user_employer', true))                
            );
            $order_data_new = wp_parse_args($args, $defaults_order_data);
            return wp_parse_args($product_data[$product_id], $order_data_new);
        } else {
            return $defaults_order_data;
        }
    }
}

// if (is_admin()) {
global $ExhibitorManagement;
$ExhibitorManagement = new ExhibitorManagement();
// }