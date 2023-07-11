<?php
class RegistrationReports
{
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_report_menu_item'));
            add_action('acf/init', array($this, 'registration_report_settings'), 10);
            add_action('admin_enqueue_scripts', array($this, 'load_reports_admin_style'));
            add_action('wp_ajax_analytics_registration_report', array($this, 'analytics_registration_report_callback'));
        }
    }
    public function load_reports_admin_style()
    {
        wp_enqueue_style('reports_admin_css', get_theme_file_uri('/inc/assets/css/reports-admin.css'), false, time());
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', false, '1.13.2');
        wp_enqueue_script('jquery-ui', '//code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), '1.13.2', true);
        wp_enqueue_style('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.min.css', array(), '11.7.10');
        wp_enqueue_script('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.all.min.js', array(), '11.7.10');
        wp_enqueue_script('registration-reports', get_theme_file_uri('/inc/assets/js/registration-reports.js'), array('jquery'), time(), true);
        wp_localize_script('registration-reports', 'reports_obj', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    public function add_report_menu_item()
    {
        add_menu_page('Registration Reports', 'Registration Reports', 'manage_options', 'reports', array($this, 'report_page'), 'dashicons-clipboard', 6);
    }

    public function report_page()
    {
        $years = $this->get_years_for_export_data();

        if (empty($years)) return;
        ?>
        <div class="registration-reports-wrap">
            <div class="registration-reports-container">
                <h2>Registration Reports</h2>
                <div class="report-filters-cls">
                    <form action="<?php echo admin_url('admin.php?page=reports'); ?>" method="post" id="registration-reports">
                        <input type="hidden" name="action" value="analytics_registration_report">
                        <div id="product-container" class="multiselect">
                            <div id="select-product" class="selectBox" onclick="toggleCheckboxArea()">
                                <select class="form-select">
                                    <option>Registration Type</option>
                                </select>
                                <div class="overSelect"></div>
                            </div>
                            <div id="product-select-options">
                                <label for="selectAll"><input type="checkbox" id="selectAll" onchange="toggleSelectAllCheckboxes(this)" value="0" />Select All</label>
                                <?php
                                    $registration_type = get_field('products', 'option');
                                    if (!empty($registration_type)) {
                                        foreach ($registration_type as $products_obj) :
                                            printf('<label for="%1$s"><input type="checkbox" id="%1$s" name="registration_type[]" onchange="checkboxStatusChange()" value="%1$s" />%2$s</label>', $products_obj->ID, $products_obj->post_title);
                                        endforeach;
                                    }
                                ?>
                            </div>
                        </div>
                        <div class="registration-report-year">
                            <select name="registration_year" id="registration_year">
                                <option value="">Year</option>
                                <?php foreach ($years as $year) : ?>
                                    <option value="<?php echo $year; ?>" <?php selected(date('Y'), $year); ?>><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="registration-custom-date">
                            <label for="custom-date-from">Custom</label>
                            <input type="text" id="custom_date_from" name="custom_date_from" placeholder="mm-dd-yyyy">
                            <label for="custom-date-to">-</label>
                            <input type="text" id="custom_date_to" name="custom_date_to" placeholder="mm-dd-yyyy">
                        </div>
                        <div class="action-btn">
                            <input type="submit" class="button" value="Export Report" id="export-report" />
                        </div>
                    </form>
                </div>
                <div class="analytics-overview-heading">
                    <h3>Analytics Overview</h3>
                </div>
                <div class="analytic-views-container">
                    <div class="analytic-views-today">
                        <h4>Today</h4>
                        <div id="analytic-views-today"><?php echo $this->analytics_overview('orders_today_count'); ?></div>
                    </div>
                    <div class="analytic-views-last-seven-days">
                        <h4>Last 7 Days</h4>
                        <div id="analytic-views-last-seven-days"><?php echo $this->analytics_overview('orders_last_7_days_count'); ?></div>
                    </div>
                    <div class="analytic-views-this-month">
                        <h4>This Month</h4>
                        <div id="analytic-views-this-month"><?php echo $this->analytics_overview('orders_this_month_count'); ?></div>
                    </div>
                    <div class="analytic-views-this-year">
                        <h4>This Year</h4>
                        <div id="analytic-views-this-year"><?php echo $this->analytics_overview('orders_this_year_count'); ?></div>
                    </div>
                </div>
                <div class="registration-reports-result">
                    <table id="registration-reports-result-table">
                        <thead>
                            <tr id="reports-result-headerRow">
                                <th scope="col">No.</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">Company</th>
                                <th scope="col">Transaction Date</th>
                                <th scope="col">Status</th>
                                <th scope="col">Registration Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <?php
                                    $i =1;
                                    foreach ($this->analytics_registration_report_callback() as $products_obj) : //%1$s
                                        if (
                                            $products_obj['first_name'] || 
                                            $products_obj['last_name'] || 
                                            $products_obj['company'] || 
                                            $products_obj['status'] || 
                                            $products_obj['product_name']
                                        ) {
                                            printf('<tr><td>%1$s</td><td>%2$s</td><td>%3$s</td><td>%4$s</td><td>%5$s</td><td>%6$s</td><td>%7$s</td></tr>', $i, $products_obj['first_name'], $products_obj['last_name'], $products_obj['company'], date('m-d-Y', strtotime($products_obj['order_date'])), $products_obj['status'], $products_obj['product_name']);
                                            // print_r($products_obj);
                                        }
                                        $i++;
                                    endforeach;
                                ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <script>
                function checkboxStatusChange() {
                    var values = [];
                    var checkboxes = document.getElementById("product-select-options");
                    var checkedCheckboxes = checkboxes.querySelectorAll('input[type="checkbox"]:checked:not(#selectAll)');

                    document.getElementById("selectAll").checked = (checkedCheckboxes.length === checkboxes.querySelectorAll('input[type="checkbox"]:not(#selectAll)').length);

                    for (const item of checkedCheckboxes) {
                        values.push(item.value);
                    }
                }

                function toggleCheckboxArea(onlyHide = false) {
                    var checkboxes = document.getElementById("product-select-options");
                    var displayValue = checkboxes.style.display;

                    if (displayValue != "block") {
                        if (onlyHide == false) {
                            checkboxes.style.display = "block";
                        }
                    } else {
                        checkboxes.style.display = "none";
                    }
                }

                document.addEventListener('click', function(event) {
                    var container = document.querySelector("#product-container");
                    if (!container.contains(event.target)) {
                        toggleCheckboxArea(true);
                    }
                });

                function toggleSelectAllCheckboxes(e) {
                    var checkboxes = document.querySelectorAll("input[type='checkbox']:not(#selectAll)");
                    var allChecked = [...checkboxes].every(checkbox => checkbox.checked);

                    checkboxes.forEach(function(checkbox) {
                        checkbox.checked = !allChecked;
                    });
                }
            </script>
        <?php
    }

    public function registration_report_settings()
    {
        if (function_exists('acf_add_options_page')) {

            acf_add_options_sub_page(array(
                'page_title'    => 'Report Settings',
                'menu_title'    => 'Report Settings',
                'parent_slug'   => 'reports',
                'menu_slug'     => 'report-settings',
                // 'capability'    => 'edit_posts',
            ));
        }
    }

    public function analytics_overview($type)
    {
        $current_year = date('Y');
        $order_args = array(
            'status'    => array('wc-completed'),
            'limit'     => -1,
            'type'      => 'shop_order',
            'order'     => 'DESC',
            'return'    => 'ids',
        );
        $remove_products_from_analytics_overview = get_field('remove_products_from_analytics_overview', 'option');
        if (!empty($remove_products_from_analytics_overview)) {
            $exclude_analytics = array();
            foreach ($remove_products_from_analytics_overview as $product_id)
            {
                $exclude_analytics = $exclude_analytics + $this->get_orders_ids_by_product_id( $product_id );
            }
            $order_args['exclude'] = $exclude_analytics;
        }
        // Orders today
        if ($type == 'orders_today_count') {
            $order_args['date_created'] = '>=' . date('Y-m-d');
            $orders_today = wc_get_orders($order_args);
            if(!empty($orders_today))
            {
                $qty = 0;
                foreach ($orders_today as $order_id)
                {
                    $qty += self::getTotalQuantityByOrderId($order_id);
                }
                return $qty;
            }
            return count($orders_today) ? count($orders_today) : 0;
        }

        // Orders last 7 days
        if ($type == 'orders_last_7_days_count') {
            $order_args['date_created'] = '>' . strtotime('midnight', strtotime('-7 days', current_time('timestamp')));
            $orders_last_7_days = wc_get_orders($order_args);
            if(!empty($orders_last_7_days))
            {
                $qty = 0;
                foreach ($orders_last_7_days as $order_id)
                {
                    $qty += self::getTotalQuantityByOrderId($order_id);
                }
                return $qty;
            }
            return count($orders_last_7_days) ? count($orders_last_7_days) : 0;
        }
        // Orders this month
        if ($type == 'orders_this_month_count') {
            $order_args['date_created'] = '>' . strtotime('midnight', strtotime('first day of this month', current_time('timestamp')));
            $orders_this_month = wc_get_orders($order_args);
            if(!empty($orders_this_month))
            {
                $qty = 0;
                foreach ($orders_this_month as $order_id)
                {
                    $qty += self::getTotalQuantityByOrderId($order_id);
                }
                return $qty;
            }
            return count($orders_this_month) ? count($orders_this_month) : 0;
        }
        // Orders this year
        if ($type == 'orders_this_year_count') {
            $order_args['date_created'] = '>' . strtotime('midnight', strtotime('first day of January ' . $current_year));
            $orders_this_year = wc_get_orders($order_args);
            if(!empty($orders_this_year))
            {
                $qty = 0;
                foreach ($orders_this_year as $order_id)
                {
                    $qty += self::getTotalQuantityByOrderId($order_id);
                }
                return $qty;
            }
            return count($orders_this_year) ? count($orders_this_year) : 0;
        }
        return 0;
    }

    public function get_orders_ids_by_product_id( $product_id ) {
        global $wpdb;
        
        // Define HERE the orders status to include in  <==  <==  <==  <==  <==  <==  <==
        $orders_statuses = "'wc-completed', 'wc-processing', 'wc-on-hold'";
    
        # Get All defined statuses Orders IDs for a defined product ID (or variation ID)
        return $wpdb->get_col( "
            SELECT DISTINCT woi.order_id
            FROM {$wpdb->prefix}woocommerce_order_itemmeta as woim, 
                 {$wpdb->prefix}woocommerce_order_items as woi, 
                 {$wpdb->prefix}posts as p
            WHERE  woi.order_item_id = woim.order_item_id
            AND woi.order_id = p.ID
            AND p.post_status IN ( $orders_statuses )
            AND woim.meta_key IN ( '_product_id', '_variation_id' )
            AND woim.meta_value LIKE '$product_id'
            ORDER BY woi.order_item_id DESC"
        );
    }

    public static function getTotalQuantityByOrderId($order_id) 
    {
        $order = wc_get_order($order_id);

        if ($order) {
            $item_count = 0;
            $items = $order->get_items();

            foreach ($items as $item) {
                $item_count += $item->get_quantity();
            }

            return $item_count;
        } else {
            return 0;
        }
    }

    public function analytics_registration_report_callback()
    {
        global $wpdb;

        $product_ids        = isset($_POST['registration_type']) ? $_POST['registration_type'] : false;
        $year               = isset($_POST['registration_year']) ? $_POST['registration_year'] : false;
        $start_date         = isset($_POST['custom_date_from']) ? $_POST['custom_date_from']  : false;
        $end_date           = isset($_POST['custom_date_to']) ? $_POST['custom_date_to']  : false;
        $csv_file_name      = 'All-Student-Registrations';
        $order_status       = ['wc-completed'];

        $query = "
            SELECT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode("','", $order_status) . "' )
            AND order_items.order_item_type = 'line_item'";
        
        if ($product_ids) {
            $p_ids = array();
            foreach ($product_ids as $product_id) {
                $p_ids[] = " (order_item_meta.meta_key = '_product_id' AND order_item_meta.meta_value = " . $product_id.")";
            }
            $query .= " AND (".implode(' OR', $p_ids).")";
        }
        if(!wp_doing_ajax())
        {
            $query .= " AND (order_item_meta.meta_key = '_product_id' AND order_item_meta.meta_value != 18792)";
        }

        if ($start_date && $end_date) {
            $start_date = explode('-', $start_date); 
            $start_date = $start_date[2].'-'.$start_date[0].'-'.$start_date[1];
            $end_date = explode('-', $end_date); 
            $end_date = $end_date[2].'-'.$end_date[0].'-'.$end_date[1];
            $query .= " AND DATE(posts.post_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
            $csv_file_name .= '-' . $start_date . "-to-" . $end_date;
        } elseif ($start_date) {
            $start_date = explode('-', $start_date); 
            $start_date = $start_date[2].'-'.$start_date[0].'-'.$start_date[1];
            $query .= " AND DATE(posts.post_date) >= '" . $start_date . "'";
            $csv_file_name .= '-' . $start_date;
        } elseif ($end_date) {
            $end_date = explode('-', $end_date); 
            $end_date = $end_date[2].'-'.$end_date[0].'-'.$end_date[1];
            $query .= " AND DATE(posts.post_date) <= '" . $end_date . "'";
            $csv_file_name .= '-' . $end_date;
        }else{
            $year = $year !== false ? $year : date('Y');
            $start_date = $year . '-01-01'; // Start of the current year
            $end_date   = $year . '-12-31'; // End of the current year
            $csv_file_name .= '-' . $year;
            $query .= " AND DATE(posts.post_date) BETWEEN '" . $start_date . "' AND '" . $end_date . "'";
        }
        // $csv_file_name .= '.csv';
        $query .= " ORDER BY order_items.order_id DESC";
        $order_ids = $wpdb->get_col($query);

        if (!empty($order_ids) && count($order_ids) > 0) {
            $order_data     = array();
            $customer_ids   = array();
            foreach (array_unique($order_ids) as $key => $order_id) {
                $order                  = wc_get_order($order_id);
                // Get the Customer ID (User ID)
                $customer_ids[$order->get_id()][] = $order->get_customer_id();

                $item_quantity = 0;

                foreach ($order->get_items() as $item_id => $item) {
                    $item_quantity += $item->get_quantity();
                }
                $_gravity_entry_data  = $order->get_meta('_gravity_entry_data');
                if (!empty($_gravity_entry_data)) {
                    $get_gravity_form_data = json_decode($_gravity_entry_data);
                    if ($get_gravity_form_data->form_id == 11)
                        $order_data[]           = $this->get_customer_details_by_id($order);
                }else{
                    $order_data[]           = $this->get_customer_details_by_id($order);
                }
                $_attendees_order_meta  = $order->get_meta('_attendees_order_meta');
                if ($item_quantity > 1 && !empty($_attendees_order_meta) && is_array($_attendees_order_meta)) {
                    foreach ($_attendees_order_meta as $_attendees) {
                        $order_data[] = $this->get_customer_details_by_id($order, (int)$_attendees['product_id'], (int)$_attendees['user_id']);
                    }
                }
            }
            
            if(!empty($order_data) && wp_doing_ajax()) {
                wp_send_json_success(array('data' => $order_data, 'filename' => $csv_file_name));
            }elseif(!empty($order_data) && !wp_doing_ajax()){
                return $order_data;
            }else {
                wp_send_json_error();
            }
        } else {
            wp_send_json_error();
        }
    }

    public function get_customer_details_by_id($order, $product_id = null, $customer_id = null)
    {
        $product_data = array();
        foreach ($order->get_items() as $item_id => $item) {
            $product_data[$item->get_product_id()] = array(
                'product_name'  => $item->get_name(),
                // 'quantity'      => $item->get_quantity(),
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
            'order_date'            => date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
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

    public function get_years_for_export_data()
    {
        global $wpdb;

        $res = array();
        $prefix = $wpdb->prefix;
        $q = "
            SELECT DISTINCT YEAR(posts.post_date) as YEAR
            FROM {$prefix}woocommerce_order_items as order_items
            LEFT JOIN {$prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$prefix}posts AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            AND YEAR(posts.post_date) > 2022
            GROUP BY YEAR(posts.post_date)
            ORDER BY YEAR DESC
        ";

        $results = $wpdb->get_results($q, 'ARRAY_A');

        if (!empty($results)) {
            foreach ($results as $year) {
                $res[] = $year['YEAR'];
            }
        }

        return $res;
    }
}
if (is_admin()) {
    global $registrationreports;
    $registrationreports = new RegistrationReports();
}
