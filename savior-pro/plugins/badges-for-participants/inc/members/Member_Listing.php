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
        add_action('wp_ajax_get_member_list', array($this, 'get_member_list'));
        add_action('wp_ajax_print_badges', array($this, 'print_badges'));
        add_action('admin_init', array($this, 'filter_some_values'));
        add_action('admin_init', array($this, 'show_original_name'));
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
        wp_register_script('member-listing', plugins_url('badges-for-participants/assets/js/member-listing.js'), array('jquery'), time(), true);
        wp_localize_script('member-listing', 'member_object', array('ajax_url'=> admin_url('admin-ajax.php')));
    }

    public function add_menu_member_listing_page()
    {
        $this->members_page = add_menu_page('Badges', 'Badges', 'export', 'member_listing', array($this, 'member_listing_page'), '', '1.6');
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
        wp_enqueue_script('member-listing');
        ?>
        <div class="wrap" id="badges">
            <h2>Badges</h2>
            <div class="member-list" >             
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
                        </tr>
                    </thead>      
                    <tbody>
                    <?php
                        global $registrationreports;
                        $registration_reports = $registrationreports->analytics_registration_report_callback();
                        $registration_reports_boj = json_decode($registration_reports);
                        if(!empty($registration_reports_boj))
                        {
                            $get_user_ids_printed = get_option( 'badge_print_ids' );
                            foreach($registration_reports_boj->data as $key => $members_list)
                            {
                                // $members_list->customer_id,
                                // $members_list->order_id,
                                ++$key;
                                printf(
                                    '
                                    <tr>
                                        <td>%s</td><td data-printed="%s" id="user_badge_print_%s">%s</td><td><button type="button" data-customer_id="%s" data-order_id="%s" data-product_id="%s" id="member_print_%s">Print</button></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td>
                                    </tr>
                                    ',
                                    $key,
                                    in_array($members_list->customer_id, $get_user_ids_printed) ? '1' : '0',
                                    $members_list->customer_id,
                                    in_array($members_list->customer_id, $get_user_ids_printed) ? '<b style="color:green;">Printed</b>' : '<b style="color:red;">Not printed</b>',
                                    $members_list->customer_id,
                                    $members_list->order_id,
                                    $members_list->product_id,
                                    $members_list->order_id.'_'.$members_list->customer_id,
                                    $members_list->first_name,
                                    $members_list->last_name,
                                    $members_list->customer_email,
                                    $members_list->company,
                                    $members_list->product_name,
                                    $this->user_has_role($members_list->customer_id, 'bodmember') ? 'Y' : 'N',
                                    $this->user_has_role($members_list->customer_id, 'exhibits_committee_member') ? 'Y' : 'N',
                                    $this->user_has_role($members_list->customer_id, 'instructormember') ? 'Y' : 'N',
                                    $this->user_has_role($members_list->customer_id, 'exhibitsmember') ? 'Y' : 'N'
                                );
                                // if($key == 10) break;
                            }
                        }else{
                            echo "<tr></tr>";
                        }
                    ?>

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
        ob_start();
        $user_ids       = isset($_POST['customer_id']) && is_array($_POST['customer_id']) ? $_POST['customer_id'] : array($_POST['customer_id']);
        $order_id       = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $product_id     = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $format_type    = intval($_POST['format_type']);
        
        if(empty($user_ids))
        {
            return;
        }
        $out = '';
        $counter = 0;

        $default_role_labels = array(
            'bodmember'                 => 'BM',
            'exhibits_committee_member' => 'CM', 
            'instructormember'          => 'SP',    
            'exhibitsmember'            => 'EX',
        );
        foreach ( array_unique( $user_ids ) as $k => $user_id) {                                
            // Get the WP_User instance Object
            $user = new WP_User( $user_id );

            $username           = $user->username; // Get username
            $user_email         = $user->email; // Get account email
            $first_name         = $user->first_name;
            $last_name          = $user->last_name;
            $display_name       = $user->display_name;
            $nickname           = $user->nickname;

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

            $role_labels    = array();
            foreach ($user->roles as $role_label)
            {
                if(isset($default_role_labels[$role_label]))
                    $role_labels[] = $default_role_labels[$role_label];
            }
            $print_qr = 'Yes';

            $daypass = true;
            $rec = array();
            //$rec = json_decode($badge['data'], true);    
            $rec = array(
                "first_name"        => $first_name ? $first_name : $billing_first_name,
                "last_name"         => $last_name ? $last_name : $billing_last_name,
                "friendly_name"     => $nickname ? $nickname : '',
                "company"           => $billing_company,
                "email"             => $user_email,
                "job"               => get_user_meta($user_id, 'user_title', true),
                "phone_daytime"     => $billing_phone,
                "country"           => $billing_country,
                "state"             => $billing_state,
                "city"              => $billing_city,
                "addr_country"      => $billing_country,
                "addr_state"        => $billing_state,
                "addr_city"         => $billing_city,
                "addr_zip"          => $billing_postcode,
                "addr_country"      => $billing_country,
                "addr_state"        => $billing_state,
                "addr_addr_1"       => $billing_address_1,
                "addr_addr_2"       => $billing_address_2,
            );
            ?>
            <div class="badge_1 <?php echo 'tpl-' . $format_type; ?> daypass <?php echo $format_type == 2 ? 'ready' : ''; ?>"
                data-id="<?php echo $k; ?>"
                data-reg-id="<?php echo $user_id; ?>">
                <?php if ($format_type == 2): ?>
                    <div class="top">
                        <div class="user-friendly-name"><span class="full-width"><?= $user->nickname ?></span></div>
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
                                            <?php //= $rec['job'] ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="user-company-address">
                                    <div class="user-company"><span class="full-width"><?php echo $billing_company; ?></span>
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
                                <div class="b-date">SEPTEMBER 11-14, 2023</div>
                            </div>
                        </div>
                    </div>
                    <div class="bottom">
                        <img src="<?= BADGES_PLUGIN_URL ?>assets/images/logo.jpg"/>
                    </div>
                <?php else: ?>
                    <?php if ($daypass == true): ?>
                    <div class="daypass-tpl print-qr">
                        <div class="tp">
                            <div class="left-block">
                                <div class="user-friendly-name-alt">
                                    <span class="full-width"><?= $nickname ?></span>
                                </div>
                                <div class="user-name-alt">
                                    <span class="full-width"><?php echo $first_name . ' ' . $last_name ?></span>
                                </div>
                                <div class="user-title-alt">
                                    <span class="full-width"><?php //job ?></span>
                                </div>
                            </div>
                            <div class="qr-code">                                                    
                                <?= $this->print_qr_code($rec) ?>
                            </div>
                        </div>
                        <div class="bt">
                            <div class="user-company-alt">
                                <span class="full-width">
                                    <?php echo $billing_company; ?>
                                </span>
                            </div>
                            <div class="b-date">SEPTEMBER 11-14, 2024</div>
                            <?php //if(!empty($role_labels) && is_array($role_labels)){echo implode(', ', $role_labels);} ?>
                        </div>
                    </div>
                    <?php else: ?>
                        <div class="top">
                            <div class="left">
                                <div class="user-friendly-name"><span
                                            class="full-width"><?= $rec['friendly_name'] ?></span>
                                </div>
                                <div class="flex-block">
                                    <div class="top">
                                        <div class="user-name">
                                            <span class="full-width">
                                                <?php echo $first_name . ' ' . $last_name ?>
                                            </span>
                                        </div>
                                        <div class="user-job"><span
                                                    class="full-width"><?php //= $rec['job'] ?></span>
                                        </div>
                                    </div>
                                    <div class="user-company-address">
                                        <div class="user-company">
                                            <span class="full-width"><?= $rec['company'] ?>
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
                                    <div class="b-date">SEPTEMBER 11-14, 2023</div>
                                </div>
                            </div>
                        </div>
                        <div class="bottom">
                            <img src="<?= BADGES_PLUGIN_URL ?>assets/images/logo.jpg"/>
                        </div>
                    <?php endif; ?>
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
    public function user_has_role($user_id, $role_name)
    {
        $user_meta = get_userdata($user_id);
        $user_roles = $user_meta->roles;
        return in_array($role_name, $user_roles);
    }
}