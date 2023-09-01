<?php

class Member_Listing
{
    public $members_list;
    public $common;
    public $members_page;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', array($this, 'member_listing_enqueue_scripts'));
        add_action('admin_menu', array($this, 'add_menu_member_listing_page'));
        add_filter('set-screen-option', array($this, 'save_members_per_page_option'));
        add_action('wp_ajax_change_display_name', array($this, 'change_display_name'));
        add_action('wp_ajax_change_color', array($this, 'change_color'));
        add_action('wp_ajax_change_printed_count', array($this, 'change_printed_count'));
        add_action('wp_ajax_get_member_list', array($this, 'get_member_list_callback'));
        add_action('wp_ajax_print_badges', array($this, 'print_badges'));
        add_action('wp_ajax_update_speaker_role', array($this, 'update_speaker_role'));
        add_action('admin_init', array($this, 'filter_some_values'));
        add_action('admin_init', array($this, 'show_original_name'));
        add_action('acf/init', array($this, 'badge_settings'));
        add_action('wp_ajax_reset_print_status', array($this, 'reset_print_status_callback'));
        add_action('wp_ajax_action_printed_in_ids', array($this, 'action_printed_in_ids'));
        
    }
    public function member_listing_enqueue_scripts()
    {
        //SweetAlert
        wp_register_style('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.min.css', array(), '11.7.10');
        wp_register_script('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.all.min.js', array(), '11.7.10');

        //DataTable
        wp_register_style('datatables-management', '//cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css', array(), '1.13.4');
        wp_register_script('datatables-management', '//cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js', array('jquery'), '1.13.4', true);
        
        //DataTable Fix Header
        wp_register_style('datatables-fixedHeader', '//cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css', array(), '2.4.1');
        wp_register_script('datatables-fixedHeader', '//cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js', array(), '3.4.0', true);
        
        //DataTable Buttons
        wp_register_style('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css', array(), '2.3.6');
        wp_register_script('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js', array('jquery'), '2.3.6', true);
        wp_register_script('datatables-management-buttons-html5', '//cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js', array('jquery'), '2.3.6', true);
        wp_register_script('datatables-management-pdfmake', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('jquery'), '0.1.53', true);
        wp_register_script('datatables-management-vfs_fonts', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('jquery'), '0.1.53', true);
        wp_register_script('datatables-management-jszip', '//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('jquery'), '3.1.3', true);

        //Custom Script
        wp_register_style('member-listing', plugins_url('badges-for-participants/assets/css/member-listing.css'), array(), time());
        wp_register_script('member-listing', plugins_url('badges-for-participants/assets/js/member-listing.js'), array('jquery'), time(), true);
        wp_localize_script('member-listing', 'member_object', array('ajax_url'=> admin_url('admin-ajax.php'), 'ajax_nonce' => wp_create_nonce('reset_print')));
    }

    public function add_menu_member_listing_page()
    {
        $this->members_page = add_menu_page('Badges', 'Badges', 'export', 'member_listing', array($this, 'member_listing_page'), 'dashicons-welcome-widgets-menus', '9');
        add_action("load-$this->members_page", array($this, 'add_members_page_screen_options'));
    }
    
    public function filter_some_values() {
        if (!empty($_REQUEST['action']) && $_REQUEST['action'] === 'filter_some_values') {

            $y = isset($_REQUEST['y']) ? $_REQUEST['y'] : 'all';
            $s = isset($_REQUEST['s']) ? $_REQUEST['s'] : '';

            $link = add_query_arg(
                    array(
                        'page' => 'member_listing',
                        'y' => $y,
                        's' => urlencode($s)
                    ),
                esc_url( admin_url('admin.php') )
            );

            wp_redirect($link);
            die();
        }

    }

    public function show_original_name() {
        if (!empty($_POST['action']) && $_POST['action'] === 'show_original_name_action') {
            $show_original_name = (!empty($_POST['show_original_name']) ? true : false);
            update_option('show_original_name', $show_original_name);
        }
    }

    public function save_members_per_page_option($status, $option, $value)
    {
        return ($option == 'members_per_page') ? (int)$value : $status;
    }

    public function add_members_page_screen_options()
    {
        $screen = get_current_screen();
        if (!is_object($screen) || $screen->id != $this->members_page)
            return;
        $option = 'per_page';
        $args = [
            'label' => 'Participants per page',
            'default' => BADGES_MEMBERS_PER_PAGE,
            'option' => 'members_per_page'
        ];
        add_screen_option($option, $args);
    }

    public function change_display_name()
    {
        if (isset($_POST) && isset($_POST['form_action']) && $_POST['form_action'] === 'change_display_name' && isset($_POST['display_name']) && isset($_POST['from_user_id'])) {
            global $wpdb;
            $display_name = sanitize_text_field($_POST['display_name']);
            $item_id = (int)$_POST['wp_item_id'];
            $cong_reg_id = (int)$_POST['cong_reg_id'];
            if (!empty($item_id) && !empty($display_name)) {
                $res = $wpdb->update((empty($cong_reg_id) ? "{$wpdb->prefix}cong_registrations" : "{$wpdb->prefix}cong_regs_persons"),
                    array(
                        'badge_title' => $display_name
                    ),
                    array(
                        'id' => $item_id,
                    ));
                $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');
                echo $resText;
            }
        }
        wp_die();
    }

    public function change_color()
    {
        if (isset($_POST) && isset($_POST['cc_form_action']) && $_POST['cc_form_action'] === 'change_color' && isset($_POST['cc_color'])) {
            global $wpdb;
            $color = sanitize_text_field($_POST['cc_color']);
            $item_id = (int)$_POST['cc_wp_item_id'];
            $cong_reg_id = (int)$_POST['cc_cong_reg_id'];
            if (!empty($item_id) && !empty($color)) {
                $res = $wpdb->update((empty($cong_reg_id) ? "{$wpdb->prefix}cong_registrations" : "{$wpdb->prefix}cong_regs_persons"),
                    array(
                        'badge_color' => $color
                    ),
                    array(
                        'id' => $item_id
                    ));
                $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');
                echo $resText;
            }
        }
        wp_die();
    }

    public function change_printed_count()
    {
        if (isset($_POST) && isset($_POST['prtd_form_action']) && $_POST['prtd_form_action'] === 'change_printed_count' && isset($_POST['prtd_printed_count'])) {
            global $wpdb;
            $printed_count = (int)$_POST['prtd_printed_count'];
            $item_id = (int)$_POST['prtd_wp_item_id'];
            $cong_reg_id = (int)$_POST['prtd_cong_reg_id'];
            if (!empty($item_id) && isset($printed_count)) {
                $res = $wpdb->update((empty($cong_reg_id) ? "{$wpdb->prefix}cong_registrations" : "{$wpdb->prefix}cong_regs_persons"),
                    array(
                        'printed_count' => $printed_count
                    ),
                    array(
                        'id' => $item_id
                    ));
                $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');
                echo $resText;
            }
        }
        wp_die();
    }

    public function member_listing_page()
    {
        wp_enqueue_style('sweetalert2');
        wp_enqueue_script('sweetalert2');
        wp_enqueue_style('datatables-management');
        wp_enqueue_style('datatables-fixedHeader');
        wp_enqueue_script('datatables-management');      
        wp_enqueue_script('datatables-fixedHeader');
        wp_enqueue_style('datatables-management-buttons');
        wp_enqueue_script('datatables-management-buttons');
        wp_enqueue_script('datatables-management-buttons-html5');
        wp_enqueue_script('datatables-management-pdfmake');
        wp_enqueue_script('datatables-management-vfs_fonts');
        wp_enqueue_script('datatables-management-jszip');
        wp_enqueue_style('member-listing');
        wp_enqueue_script('member-listing');
        ?>
        <div class="wrap" id="badges">
            <h2>Badges</h2>
            <div class="member-list" >      
                <div class="filter-wrapper">
                    <div class="filter-checkbox">
                        <input type="checkbox" id="filter-checkbox" name="filter" value="Printed" />
                        <label for="filter-checkbox" class="filter-label">Hide Printed Badges</label>
                    </div>
                    <div class="print-action-btn">
                        <ul class="share-icons">
                            <li class="share-icons__item buttons-pdf" data-id="pdf" id="reports-pdf"><i class="fas fa-file-pdf" title="PDF Export"></i></li>
                            <li class="share-icons__item buttons-csv" data-id="csv"  id="reports-csv"><i class="fas fa-file-excel" title="CSV Export"></i></li>
                            <li class="share-icons__item buttons-excel" data-id="excel"  id="reports-excel"><i class="far fa-file-excel" title="EXCEL Export"></i></li>
                            <li class="share-icons__block">
                                <div class="share-icons__block-left"><i class="fas fa-file-export"></i></div>
                                <div class="share-icons__block-right"><i class="fas fa-file-export"></i></div>
                            </li>
                        </ul>       
                    </div>
                </div>
                <!-- Table -->
                <table id='member-listing' class='display nowrap'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Status</th>
                            <th>Print</th>
                            <th>First name</th>
                            <th>Last name</th>
                            <th>Customer email</th>
                            <th>Company</th>
                            <th>Product name</th>
                            <th>Bod member</th>
                            <th>Committee member</th>
                            <th>Speaker</th>
                            <th>Exhibitor</th>
                            <th>CEU</th>
                            <th>Customer ID</th>
                        </tr>
                    </thead>      
                    <tbody>                    
                    </tbody>              
                </table>
            </div>      
            <!-- Badges Print Section -->
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <div id="templates" template=""></div>
                    </div>
                </div>
            </div>
            <!--End Badges Print Section -->
        </div>
        <?php
    }

    public function print_badges( $atts )
    {
        global $wpdb;
        ob_start();
        $format_type    = intval($_POST['format_type']);
        $user_ids       = array();
        $day_pass       = array();
        $user_product   = array();
        // print_r($_POST);
        if($format_type == 2 && $_POST['multi_user'])
        {
            // $ceu_product_ids = get_field('ceu_keyword_display_via_product_purchased', 'option');
            $attendee_badge_orders = $wpdb->prefix . 'attendee_badge_orders';
            $results = $wpdb->get_results("SELECT product_id, customer_id FROM $attendee_badge_orders WHERE print_status = 0 LIMIT 60");// LIMIT 25
            foreach ($results as $result)
            {
                $user_ids[] = $result->customer_id;
                $day_pass['user_day_pass_'.$result->customer_id] = $result->product_id == 18784 ? true : false;
                $user_product['user_product_'.$result->customer_id] = $result->product_id;
            }
        }else{
            $user_ids       = isset($_POST['customer_ids']) && !empty($_POST['customer_ids']) ? (is_array($_POST['customer_ids']) ? $_POST['customer_ids'] : array($_POST['customer_ids'])) : false;
            $users_flags    = $_POST['user_flag'];
        }
        if(empty($user_ids))
        {
            wp_send_json_error(array('message' => 'No Printable Badges Found'));
        }

        $out = '';
        $counter = 0;

        foreach ( array_unique( $user_ids ) as $k => $user_id) {                                
            // Get the WP_User instance Object
            $user = new WP_User( $user_id );

            $username           = $user->username; // Get username
            $user_email         = $user->email; // Get account email
            $first_name         = $user->first_name;
            $last_name          = $user->last_name;
            $display_name       = $user->display_name;
            $nickname           = $user->display_name;

            // Customer billing information details (from account)
            $billing_first_name = $user->billing_first_name;
            $billing_last_name  = $user->billing_last_name;
            $billing_company    = $user->billing_company;
            $billing_address_1  = $user->billing_address_1;
            $billing_address_2  = $user->billing_address_2;
            $billing_city       = $user->billing_city;
            $billing_state      = $user->billing_state;
            $billing_postcode   = $user->billing_postcode;
            $billing_country    = $user->billing_country;
            $billing_phone      = $user->billing_phone;

            $role_labels = array();
            
            if($format_type == 2 && $_POST['multi_user'])
            {
                $member_roles = get_field('member_roles', 'option');
                if(!empty($member_roles) && is_array($member_roles))
                {
                    foreach($member_roles as $roles_with_keyword)
                    {
                        if(array_intersect($roles_with_keyword['roles'], $user->roles))
                        {
                            $role_labels[]=$roles_with_keyword['keyword_display_on_the_badge'];
                        }
                    }
                }                
            }
            
            $user_flags = $users_flags['user_flags_'.$user_id];
            foreach ($user_flags as $key => $flags) {
                if ($flags == 'Y') {
                    $role_labels[] = $key;
                }
            }
            $primary_booth_admin_contact = get_user_meta($user_id, 'primary_booth_admin_contact', true);
            $user_phone = get_user_meta($user_id, 'user_phone', true);
            $primary_contact = !empty($billing_phone) ? $billing_phone : ($primary_booth_admin_contact ? $primary_booth_admin_contact : $user_phone);

            $rec = array(
                "first_name"        => $first_name ? $first_name : $billing_first_name,
                "last_name"         => $last_name ? $last_name : $billing_last_name,
                "friendly_name"     => $nickname ? $nickname : '',
                "company"           => get_user_meta($user_id, 'user_employer', true) ? get_user_meta($user_id, 'user_employer', true) : $billing_company,
                "email"             => $user_email ?? $user->user_login,
                "job"               => get_user_meta($user_id, 'user_title', true),
                "phone_daytime"     => $primary_contact,
                "country"           => $billing_country,
                "state"             => $billing_state,
                "city"              => $billing_city,
                "addr_addr_1"       => $billing_address_1,
                "addr_addr_2"       => $billing_address_2,
                "addr_city"         => $billing_city,
                "addr_state"        => $billing_state,
                "addr_zip"          => $billing_postcode,
                "addr_country"      => $billing_country,
            );
            
            unset($rec['addr_addr_1']);
            unset($rec['addr_addr_2']);
            unset($rec['addr_city']);
            unset($rec['addr_state']);
            unset($rec['addr_zip']);
            unset($rec['addr_country']);
            ?>
            <div class="badge_1 <?php echo 'tpl-' . $format_type; ?> daypass <?php echo $format_type == 2 ? 'ready' : ''; ?>"
                data-id="<?php echo $k; ?>"
                data-reg-id="<?php echo $user_id; ?>">
                <?php if ($format_type == 2): ?>
                    <div class="top">
                        <?php $full = strlen($first_name) > 20 ? 18 : 30; ?>
                        <div class="user-friendly-name"><span class="full-width" style="font-size: <?=$full?>px;"><?= $first_name ?></span></div>
                        <div class="left">
                            <div class="flex-block">
                                <div class="top">
                                    <div class="user-name">
                                        <span class="full-width">
                                            <?php echo $first_name . ' ' . $last_name ?>
                                        </span>
                                    </div>
                                    <div class="user-job">
                                        <span class="full-width">
                                            <?php echo $this->word_font_size_reduce( $rec['job'], 22 ); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="user-company-address">
                                    <div class="user-company">
                                        <span class="full-width"><?php echo $this->word_font_size_reduce( $rec['company'], 19 ); ?></span>
                                    </div>
                                    <!-- <div class="user-email" style="font-size: 11px !important;"><?php //echo $user->user_login;?></div> -->
                                    <!-- <div class="user-contact" style="font-size: 12px !important;"><?php //echo $primary_contact;?></div> -->
                                    <div class="user-address">
                                        <span class="full-width">
                                            <?php
                                                echo !empty($billing_city)? $billing_city.", ":"";
                                                echo !empty($billing_state)? $billing_state.", ":"";
                                                echo !empty($billing_country)? $billing_country :"";
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="tmp_string"></div>
                        </div>
                        <div class="right">
                            <div class="qr-code">
                                <?= $this->print_qr_code($rec) ?>
                            </div>
                            <div class="bottom-qr">
                                <div class="b-label"><?php if(!empty($role_labels) && is_array($role_labels)){echo implode(', ', $role_labels);} ?></div>
                            </div>
                            <div class="b-date latter-print">SEPT. 11-14, 2023</div>
                        </div>
                    </div>
                    <div class="bottom">
                        <?php $file = file_get_contents(BADGES_PLUGIN_URL."assets/images/logo-base64.txt");?>
                        <img src="<?php echo $file; ?>"/>
                    </div>
                <?php else: ?>
                        <div class="top">
                            <div class="left">
                                <?php $full_size = strlen($first_name) > 10 ? 18 : 30; ?>
                                <div class="user-friendly-name"><span
                                            class="full-width" style="font-size: <?=$full_size?>px;"><?= $first_name ?></span>
                                </div>
                                <div class="flex-block">
                                    <div class="top">
                                        <div class="user-name">
                                            <span class="full-width">
                                                <?php echo $first_name . ' ' . $last_name ?>
                                            </span>
                                        </div>
                                        <div class="user-job">
                                            <span class="full-width"><?php echo $this->word_font_size_reduce( $rec['job'], 24 ); ?></span>
                                        </div>
                                    </div>
                                    <div class="user-company-address">
                                        <div class="user-company">
                                            <span class="full-width"><?php echo $this->word_font_size_reduce( $rec['company'], 18 ); ?>
                                        </span>
                                        </div>
                                        <div class="user-address">
                                            <span class="full-width">
                                                <?php
                                                    echo !empty($billing_city)? $billing_city.", ":"";
                                                    echo !empty($billing_state)? $billing_state.", ":"";
                                                    echo !empty($billing_country)? $billing_country :"";
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="tmp_string"></div>
                            </div>
                            <div class="right">
                                <div class="qr-code">
                                    <?= $this->print_qr_code($rec) ?>
                                </div>
                                <div class="bottom-qr">
                                    <div class="b-label"><?php if(!empty($role_labels) && is_array($role_labels)){echo implode(', ', $role_labels);} ?></div>
                                    <div class="b-date">SEPT. 11-14, 2023</div>
                                </div>
                            </div>
                        </div>
                        <div class="daypass-footer">
                            <?php 
                                $day_pass = isset($_POST['day_pass']) || $day_pass['user_day_pass_'.$user_id] ? $_POST['day_pass'] : false;
                                if($day_pass['user_day_pass_'.$user_id] == 'true')
                                {
                                    printf('<h2>DAY PASS - %s</h2><h4>EXHIBITS ONLY</h4>',date('l'));
                                }
                            ?>
                        </div>
                <?php endif; ?>
            </div>
            <?php
            $counter++;
        }
        $out = ob_get_contents();
        ob_get_clean();
        wp_send_json_success( $out );
    }
    public function print_qr_code($user_info)
    {
        $img = GenerateQRCode::render($user_info);

        return '<img src="' . $img . '"/>';
    }

    public function word_font_size_reduce( $string, $default_size )
    {
        return strlen($string) > $default_size ? substr($string,0,$default_size)."..." : $string;
    }
    public function user_has_role($user_id, $role_name)
    {
        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        return in_array($role_name, $user_roles);
    }

    public function update_speaker_role()
    {
        if(isset($_POST['action']) && !empty($_POST['member_id']) && $_POST['action'] == 'update_speaker_role')
        {
            $user = get_userdata($_POST['member_id']);
            if(!$user)
                wp_send_json_error();

            if($_POST['status'])
            {
                $user->add_role('speaker');
            }else{
                $user->remove_role('speaker');
            }
            wp_send_json_success();
        }else{
            wp_send_json_error();
        }
    }

    public function hasPurchasedProducts($user_id, $product_ids) {
        $current_year = date('Y');
        if ($user_id) {
            $customer_orders = wc_get_orders(array(
                'numberposts' => -1,
                'meta_key'    => '_customer_user',
                'meta_value'  => $user_id,
                'post_type'   => 'shop_order',
                'post_status' => array('wc-completed'),
            ));

            foreach ($customer_orders as $order) {
                $order_year = date('Y', strtotime($order->get_date_created()));
                
                if ($order_year == $current_year) {
                    $items = $order->get_items();
                    
                    foreach ($items as $item) {
                        $product_id_in_order = $item->get_product_id();
                        
                        if (in_array($product_id_in_order, $product_ids)) {
                            $purchased_products[] = $product_id_in_order;
                        }
                    }
                }
            }
            
            if (!empty($purchased_products)) {
                sort($purchased_products);
                return $purchased_products;
            }
        }
        return false;
    }

    public function badge_settings() 
    {
        if (function_exists('acf_add_options_sub_page')) {
            $parent_slug = 'member_listing'; // Replace this with the actual parent page slug
            $child = acf_add_options_sub_page(array(
                'page_title'  => __('Badge Settings'),
                'menu_title'  => __('Badge Settings'),
                'parent_slug' => $parent_slug,
            ));
        }
    }

    public function get_member_list_callback()
    {
        global $wpdb;
        $attendee_badge_orders = $wpdb->prefix . 'attendee_badge_orders';
        $custom_filter = sanitize_text_field($_POST['custom_filter']);
        $length = intval($_POST['length']);
        $start = intval($_POST['start']);

        $search_term = sanitize_text_field($_POST['search']['value']);
        $where_condition = $custom_filter ? 'print_status=0' : '1=1';

        if (!empty($search_term)) {
            $where_condition .= " AND (`first_name` LIKE '%$search_term%' OR `last_name` LIKE '%$search_term%' OR `customer_email` LIKE '%$search_term%' OR `company` LIKE '%$search_term%' OR `product_name` LIKE '%$search_term%' OR `print_status` LIKE '%$search_term%') AND product_id NOT IN (27011, 27010, 18792)";
        }
        $query = "SELECT * FROM $attendee_badge_orders WHERE $where_condition AND product_id NOT IN (27011, 27010, 18792)";
        if($length != -1)
        {
            $query .= " LIMIT $start, $length";
        }
        $orders = $wpdb->get_results($query);
        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $attendee_badge_orders WHERE $where_condition AND product_id NOT IN (27011, 27010, 18792)");
        $data = [];
        $ceu_product_ids = get_field('ceu_keyword_display_via_product_purchased', 'option');

        $member_roles = get_field('member_roles', 'option');
        foreach ($orders as $order) {
            $member_user = new WP_User( $order->customer_id );
            $role_flag = array();
            if(!empty($member_roles) && is_array($member_roles))
            {
                foreach($member_roles as $roles_with_keyword)
                {
                    if(array_intersect($roles_with_keyword['roles'], $member_user->roles))
                    {
                        $role_flag[]=$roles_with_keyword['keyword_display_on_the_badge'];
                    }
                }
            }
            $printStatus = $order->print_status ? 'Printed' : 'Print';
            $data[] = array(
                'sn'            =>'',
                'print_status'  => $order->print_status ? '<b style="color:green;">Printed</b>' : '<b style="color:red;">Not printed</b>',
                'print_btn'     => "<button type='button'data-customer_id='$order->customer_id' data-order_id='$order->order_id' data-product_id='$order->product_id' id='member_print_$order->customer_id'>$printStatus</button>",
                'first_name'    =>$order->first_name,
                'last_name'     =>$order->last_name,
                'customer_email'=>$order->customer_email,
                'company'       =>$order->company,
                'product_name'  =>$order->product_name,
                'member_bm'     =>in_array('BM', $role_flag) ? '<b style="color:green;">Y</b>' : '<b style="color:red;">N</b>',
                'member_cm'     =>in_array('CM', $role_flag) ? '<b style="color:green;">Y</b>' : '<b style="color:red;">N</b>',
                'member_sp'     =>in_array('SP', $role_flag) ?'<b style="color:green;">Y</b>' : '<b style="color:red;">N</b>',
                'member_ex'     =>in_array('EX', $role_flag) ? '<b style="color:green;">Y</b>' : '<b style="color:red;">N</b>',
                'member_ceu'    =>in_array($order->product_id, $ceu_product_ids) ? '<b style="color:green;">Y</b>' : '<b style="color:red;">N</b>',
                'customer_id'   =>$order->customer_id,
            );
        }
        $response = [
            'draw' => intval($_POST['draw']),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_records,
            'data' => $data,
        ];
        wp_send_json( $response );
    }

    public function action_printed_in_ids()
    {
        global $wpdb;
        if (isset($_POST) && isset($_POST['action']) && $_POST['action'] === 'action_printed_in_ids' && isset($_POST['ids'])) {
            $ids = (array)$_POST['ids'];
            // $get_user_ids_printed = get_option( 'badge_print_ids' );
            // // update_option( 'badge_print_ids',array());
            // $get_user_ids_printed = isset($get_user_ids_printed) && is_array($get_user_ids_printed) ? $get_user_ids_printed : array($get_user_ids_printed);
            // $user_ids_printed = update_option( 'badge_print_ids', array_filter(array_unique(array_merge($ids,$get_user_ids_printed))) );
            

            $attendee_badge_orders = $wpdb->prefix . 'attendee_badge_orders';
            try {
                foreach ($ids as $customer_id) {
                    $wpdb->update(
                        $attendee_badge_orders,
                        array('print_status' => 1), // Column updates
                        array('customer_id'  => $customer_id) // WHERE condition
                    );
                }
                wp_send_json_success();
            } catch (\Throwable $th) {
                wp_send_json_error( $th->getMessage() );
            }
        }
        wp_send_json_error();
    }

    public function reset_print_status_callback()
    {
        global $wpdb;
        // Verify nonce
        if ( !isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'reset_print') ) {
            wp_send_json_error('Invalid nonce.');
        }
        $attendee_badge_orders = $wpdb->prefix . 'attendee_badge_orders';
        try {
            $print_status = 0;
            $query = "UPDATE $attendee_badge_orders SET print_status = %s WHERE print_status !=0"; // Query to update all rows
            $update_result = $wpdb->query($wpdb->prepare($query, $print_status));
            wp_send_json_success($update_result);
        } catch (\Throwable $th) {
            wp_send_json_error( $th->getMessage() );
        }
    }
}