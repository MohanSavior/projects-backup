<?php
class RegistrationReports
{
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_menu', array($this, 'add_report_menu_item'));
            add_action('acf/init', array($this, 'registration_report_settings'), 10);
            add_action('admin_enqueue_scripts', array($this, 'load_reports_admin_style'));
            add_action( 'wp_ajax_analytics_registration_report', array($this, 'analytics_registration_report_callback') );
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
                    <form action="" id="registration-reports">
                        <input type="hidden" name="action" value="analytics_registration_report">
                        <div class="registration-report-type">
                            <select name="registration_type" id="registration_type">
                                <option value="">Registration Type</option>
                                <?php
                                $registration_type = get_field('products', 'option');
                                if (!empty($registration_type)) {
                                    foreach ($registration_type as $products_obj) :
                                        printf('<option value="%s">%s</option>', $products_obj->ID, $products_obj->post_title);
                                    endforeach;
                                }
                                ?>
                            </select>
                        </div>
                        <div class="registration-report-year">
                            <select name="registration_year" id="registration_year">
                                <option value="">Year</option>
                                <?php foreach ($years as $year) : ?>
                                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="registration-custom-date">
                            <label for="custom-date-from">Custom</label>
                            <input type="text" id="custom_date_from" name="custom_date_from" placeholder="yyyy-mm-dd">
                            <label for="custom-date-to">-</label>
                            <input type="text" id="custom_date_to" name="custom_date_to" placeholder="yyyy-mm-dd">
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
            </div>
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

    public function analytics_overview( $type )
    {
        $current_year = date('Y');
        $order_args = array(
            'status'    => array('wc-completed'),
            'limit'     => -1,
            'type'      => 'shop_order',
            'order'     => 'DESC',
            'return'    => 'ids',
        );
        
        // Orders today
        if( $type == 'orders_today_count')
        {
            $order_args['date_created'] = '>='.date('Y-m-d');
            $orders_today = wc_get_orders($order_args);
            return count($orders_today) ? count($orders_today) : 0;
        }

        // Orders last 7 days
        if( $type == 'orders_last_7_days_count')
        {
            $order_args['date_created'] = '>' . strtotime('midnight', strtotime('-7 days', current_time('timestamp')));
            $orders_last_7_days = wc_get_orders($order_args);
            return count($orders_last_7_days) ? count($orders_last_7_days) : 0;
        }
        // Orders this month
        if( $type == 'orders_this_month_count')
        {
            $order_args['date_created'] = '>' . strtotime('midnight', strtotime('first day of this month', current_time('timestamp')));
            $orders_this_month = wc_get_orders($order_args);
            return count($orders_this_month) ? count($orders_this_month) : 0;
        }
        // Orders this year
        if( $type == 'orders_this_year_count')
        {
            $order_args['date_created'] = '>' . strtotime('midnight', strtotime('first day of January ' . $current_year));
            $orders_this_year = wc_get_orders($order_args);
            return count($orders_this_year) ? count($orders_this_year) : 0;
        }
        return 0;
    }

    public function analytics_registration_report_callback()
    {
        global $wpdb;

        $product_id         = isset($_POST['registration_type']) ? $_POST['registration_type'] : false;
        $year               = isset($_POST['registration_year']) ? $_POST['registration_year'] : false;
        $start_date         = isset($_POST['custom_date_from']) ? $_POST['custom_date_from'] : false;
        $end_date           = isset($_POST['custom_date_to']) ? $_POST['custom_date_to'] : false;
        $csv_file_name = 'School_registration_report_';
        $order_status = ['wc-completed'];
    
        $query = "
            SELECT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            AND order_items.order_item_type = 'line_item'";
        if($product_id)
        {
            $query .= " AND order_item_meta.meta_key = '_product_id' AND order_item_meta.meta_value = '".$product_id."'";
            $csv_file_name .= get_post_field( "post_name" , $product_id ) ;
        }
        if($year)
        {
            $start_date = $year . '-01-01'; // Start of the current year
            $end_date   = $year . '-12-31'; // End of the current year
            $csv_file_name .= '_'.$year;
        }
        if ($start_date && $end_date) {
            // Add date range filter to the query
            $query .= " AND DATE(posts.post_date) BETWEEN '".$start_date."' AND '".$end_date."'";
            $csv_file_name .= '_'.$start_date."_to_".$end_date;
        } elseif ($start_date) {
            // Add start date filter to the query
            $query .= " AND DATE(posts.post_date) >= '".$start_date."'";
            $csv_file_name .= '_'.$start_date;
        } elseif ($end_date) {
            // Add end date filter to the query
            $query .= " AND DATE(posts.post_date) <= '".$end_date."'";
            $csv_file_name .= '_'.$end_date;
        }
        $csv_file_name .= '.csv';
        $query .= " ORDER BY order_items.order_id DESC";
    
        $order_ids = $wpdb->get_col($query);
    
        if( !empty($order_ids) && count($order_ids) > 0 )
        {
            $order_data     = array();
            $customer_ids   = array();
            foreach (array_unique($order_ids) as $key => $order_id) {
                $order                  = wc_get_order( $order_id );
                // Get the Customer ID (User ID)
                $customer_ids[$order->get_id()][] = $order->get_customer_id(); 
                
                $item_quantity=0;
                
                foreach ( $order->get_items() as $item_id => $item ) {
                    $item_quantity += $item->get_quantity();
                }
                $order_data[] = $this->get_customer_details_by_id( $order );
                $_attendees_order_meta  = $order->get_meta('_attendees_order_meta');
                if( $item_quantity > 1 && !empty( $_attendees_order_meta ) && is_array( $_attendees_order_meta ) )
                {
                    foreach ($_attendees_order_meta as $_attendees) {                        
                        $order_data[] = $this->get_customer_details_by_id( $order, (int)$_attendees['product_id'], (int)$_attendees['user_id'] );
                    }
                }
            }
            wp_send_json_success(array('data' => $order_data, 'filename' => $csv_file_name));
        }else{
            wp_send_json_error();
        }
    }

    public function get_customer_details_by_id( $order, $product_id = null, $customer_id = null )
    {
        $product_data = array();
        foreach ( $order->get_items() as $item_id => $item ) {
            $product        = $item->get_product();
            $product_data[$item->get_product_id()] = array(
                'product_name'  => $item->get_name(),
                'get_quantity'  => $item->get_quantity(),
                'get_total'     => ($item->get_total() / $item->get_quantity())
            );
        }
        $first_value    = reset($product_data);
        $first_key      = key($product_data);

        $defaults_order_data = array(
            'order_id'              => $order->get_id(),
            'customer_id'           => $order->get_user_id(),
            'company'               => wp_slash(get_user_meta($order->get_user_id(), 'user_employer', true)),
            'customer_email'        => ($a = get_userdata($order->get_user_id() )) ? $a->user_email : '',
            'billing_first_name'    => $order->get_billing_first_name(),
            'billing_last_name'     => $order->get_billing_last_name(),
            'billing_company'       => wp_slash($order->get_billing_company()),
            'billing_email'         => $order->get_billing_email(),
            'billing_phone'         => $order->get_billing_phone(),
            'billing_address_1'     => wp_slash($order->get_billing_address_1()),
            'billing_address_2'     => $order->get_billing_address_2(),
            'billing_postcode'      => $order->get_billing_postcode(),
            'billing_city'          => wp_slash($order->get_billing_city()),
            'billing_state'         => wp_slash($order->get_billing_state()),
            'billing_country'       => $order->get_billing_country(),
            'order_number'          => $order->get_order_number(),
            'order_date'            => date('Y-m-d H:i:s', strtotime(get_post($order->get_id())->post_date)),
            'status'                => $order->get_status(),
            'shipping_total'        => $order->get_total_shipping(),
            'shipping_tax_total'    => wc_format_decimal($order->get_shipping_tax(), 2),
            'fee_total'             => wc_format_decimal($fee_total, 2),
            'fee_tax_total'         => wc_format_decimal($fee_tax_total, 2),
            'tax_total'             => wc_format_decimal($order->get_total_tax(), 2),
            'cart_discount'         => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_cart_discount(), 2),
            'order_discount'        => (defined('WC_VERSION') && (WC_VERSION >= 2.3)) ? wc_format_decimal($order->get_total_discount(), 2) : wc_format_decimal($order->get_order_discount(), 2),
            'discount_total'        => wc_format_decimal($order->get_total_discount(), 2),
            'order_total'           => wc_format_decimal($order->get_total(), 2),
            'order_currency'        => $order->get_currency(),
            'payment_method'        => $order->get_payment_method(),
            'shipping_method'       => $order->get_shipping_method(),
            'customer_note'         => wp_slash($order->get_customer_note()),
        );
        $defaults_order_data = $defaults_order_data  + $first_value;
        if( $customer_id )
        {
            $customer = new WC_Customer( $customer_id );
            $args = array(
                'customer_id'           => $customer_id,
                'company'               => wp_slash(get_user_meta($customer_id, 'user_employer', true)),
                'customer_email'        => ($a = get_userdata($customer_id )) ? $a->user_email : '',
                'billing_first_name'    => $customer->get_billing_first_name(),
                'billing_last_name'     => $customer->get_billing_last_name(),
                'billing_company'       => wp_slash($customer->get_billing_company()),
                'billing_email'         => $customer->get_billing_email(),
                'billing_phone'         => $customer->get_billing_phone(),
                'billing_address_1'     => wp_slash($customer->get_billing_address_1()),
                'billing_address_2'     => $customer->get_billing_address_2(),
                'billing_postcode'      => $customer->get_billing_postcode(),
                'billing_city'          => wp_slash($customer->get_billing_city()),
                'billing_state'         => wp_slash($customer->get_billing_state()),
                'billing_country'       => $customer->get_billing_country()
            );
            $order_data_new = wp_parse_args( $args, $defaults_order_data );
            return wp_parse_args( $product_data[$product_id], $order_data_new );
        }else{
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
