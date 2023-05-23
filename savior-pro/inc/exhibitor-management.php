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
      add_filter( 'gform_after_submission_16', array($this, 'remove_form_entry'), 10 );

      add_filter( 'gform_user_registration_update_user_id', array($this, 'gform_user_registration_update_user_id'), 10, 4 );
      add_filter('woocommerce_payment_complete', array($this, 'exhibitor_members_payment_complete'));

      add_action('admin_head', array($this, 'exhibitor_members_style'));
      add_action('wp_ajax_update_user_status', array($this, 'update_user_status_callback'));
      // add_action( 'woocommerce_order_status_completed', array( $this, 'exhibitor_members_payment_complete' ) );//order_status_completed
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
      </style>
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
      ob_start();
      // Enqueue DataTables scripts and styles
      wp_enqueue_script('jquery');
      wp_enqueue_style('datatables-management', 'https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), '1.13.4');
      wp_enqueue_style('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css', array(), '2.3.6');
      
      wp_enqueue_script('datatables-management', 'https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true);
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
      echo '<h2>Exhibitor Members List</h2>  <a href="'.admin_url( 'admin.php?page=add-new-exhibitor' ).'" class="btn button">Add New Exhibitor</a>';
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
      
      echo '<table id="exhibitor-members-list" class="display">';
      echo '<thead>
              <tr>
                <th>No.</th>
                <th>Member name</th>
                <th>Email</th>
                <th>Company name</th>
                <th>Booth number</th>
                <th>Year</th>
                <th>Member id</th>
                <th>Date of registration</th>
                <th>Status</th>
              </tr>
            </thead>';
      echo '<tbody>';

      // Fetch users with the "exhibitsmember" role
      $exhibitor_members = get_users(array(
        'role__in' => array('exhibitsmember','exhibitpending'),
        'orderby'  => 'ID',
        'order'    => 'ASC'
      ));  
      echo '</tbody>';
      echo '</table>';
      ?>
      <script>
        const update_status = (e) => {
          console.log(e)
        }
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
                    "data": "username",
                    render: function(data, type, row, meta) {
                      return `<a href="${exhibitor_profile}&exhibitor_id=${row.id}">${data}</a>`;
                    }
                  },
                  { "data": "email" },
                  { "data": "company_name" },
                  { "data": "exhibit_booth_number" },
                  { "data": "year" },
                  { "data": "id" },
                  { "data": "date_of_registration" },
                  { 
                    "data": "status",
                    render: function(data, type, row, meta) {
                      // Array with key-value pairs
                      var keyValueArray = [
                        { key: 'new_registration', value: 'New Registration' },
                        { key: 'pending_payment', value: 'Pending Payment' },
                        { key: 'invoice_paid', value: 'Invoice Paid' },
                        { key: 'completed', value: 'Completed' }
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
                  }
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
              // order: [[1, 'asc']],
              order: [[6, 'desc']],
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
        <div id="exhibitor-profile" style="width: 60%;">
          <?php
            if(isset($_REQUEST['exhibitor_id']))
            {
              if(get_users( [ 'include' => $_REQUEST['exhibitor_id'], 'fields' => 'ID' ] ))
              {
                gravity_form(16, true, false, false, null, false, '', true ); // Replace 1 with the ID of your Gravity Form
              }else{
                echo "<div class='notice notice-error'><p><b> Exhibitor doesn't exists with the ID ".$_REQUEST['exhibitor_id']." </b></p></div>";
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
        $company_name = get_user_meta($exhibitor_member->ID, 'billing_company', true) ? get_user_meta($exhibitor_member->ID, 'billing_company', true) : get_field('user_employer', $exhibitor_member->ID);
        $exhibit_booth_number = get_field('exhibit_booth_number', $exhibitor_member->ID);
        $get_status   = get_user_meta($exhibitor_member->ID, '_exhibitor_status', true );    
        $data[] = array(
            'no'                    => '',
            'username'              => $username,
            'email'                 => $exhibitor_member->user_email,
            'company_name'          => $company_name,
            'exhibit_booth_number'  => $exhibit_booth_number,
            'year'                  => date('Y', strtotime($exhibitor_member->user_registered)),
            'id'                    => $exhibitor_member->ID,
            'date_of_registration'  => $exhibitor_member->user_registered,
            'status'                => $get_status ? $get_status : 'new_registration'
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

      foreach ($items as $item) {
        if ($item->get_product_id() == 18792) {
          $main_user_id = get_post_meta($order_id, '_customer_user', true);
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

}
// Instantiate the ExhibitorManagement class
function initialize_exhibitor_management() {
  global $exhibitor_management;
  $exhibitor_management = new ExhibitorManagement();
}

// Hook into the 'plugins_loaded' action to initialize the Exhibitor Management functionality
add_action('init', 'initialize_exhibitor_management');
