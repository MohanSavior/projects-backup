<?php
class ExpiredSubscriptions
{
    private static $instance = null;

    public function __construct()
    {
        $screen = get_current_screen();
        error_log(print_r('get_current_screen', true));
        error_log(print_r($screen, true));
        add_action('admin_menu', array($this, 'add_menu_expired_subscriptions_page'));
        add_action('admin_enqueue_scripts', array($this, 'expired_subscriptions_enqueue_scripts'));
        add_action( 'admin_footer', array($this, 'expired_subscriptions_datatable_scripts'));
        add_action('wp_ajax_expired_subscriptions', array($this, 'get_expired_subscriptions_callback'));
    }
    public function add_menu_expired_subscriptions_page()
    {
        add_submenu_page(
            'woocommerce', 
            __('Expired Subscription', 'woocommerce'), 
            __('Expired Subscription', 'woocommerce'), 
            'manage_woocommerce', 
            'expired_subscription', 
            [&$this, 'expired_subscription_fn']
        );

    }

    public function expired_subscriptions_enqueue_scripts()
    {
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
    }

    public function expired_subscription_fn()
    {
        wp_enqueue_style('datatables-management');
        wp_enqueue_style('datatables-fixedHeader');
        wp_enqueue_style('datatables-management-buttons');

        wp_enqueue_script('datatables-management');      
        wp_enqueue_script('datatables-fixedHeader');
        wp_enqueue_script('datatables-management-buttons');
        wp_enqueue_script('datatables-management-buttons-html5');
        wp_enqueue_script('datatables-management-pdfmake');
        wp_enqueue_script('datatables-management-vfs_fonts');
        wp_enqueue_script('datatables-management-jszip');
        ?>
        <div class="wrap" >
            <h2>Expired Subscription</h2>
            <div class="expired-subscriptions" >      
                <!-- Table -->
                <table id='expired-subscriptions' class='display nowrap'>
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
        </div>      
        <?php
    }
    
    public function expired_subscriptions_datatable_scripts()
    {
        echo <<<JS
            <script>
                jQuery(document).ready(function ($) {
                    'use strict';
                    var t = $("#expired-subscriptions").DataTable({
                        fixedHeader: true,
                        dom: 'Blfrtip',
                        pageLength: 25,
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: member_object.ajax_url, // Provide the URL to the server-side script
                            type: 'POST',
                            data: function (data) {
                                data.action = "get_expired_subscriptions";
                            },
                        },
                        columns: [
                            { 
                                data: 'sn',
                                render: function (data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1; // Calculate the serial number
                                }
                            },
                            { data: 'print_status' },
                            { data: 'print_btn' },
                            { data: 'first_name' },
                            { data: 'last_name' },
                            { data: 'customer_email' },
                            { data: 'company' },
                            { data: 'product_name' },
                            { data: 'member_bm' },
                            { data: 'member_cm' },
                            { data: 'member_sp' },
                            { data: 'member_ex' },
                            { data: 'member_ceu' },
                            { data: 'customer_id' },
                        ],
                        aLengthMenu: [
                            [10, 25, 50, 100, 200, -1],
                            [10, 25, 50, 100, 200, "All"]
                        ],
                        scrollX: true,
                        
                    });

                });
            </script>
        JS;
    }
    public function get_expired_subscriptions_callback()
    {
        global $wpdb;
        $users                      = $wpdb->prefix . 'users';
        $shop_subscription_expired  = $wpdb->prefix . 'shop_subscription_expired';
        $custom_filter              = sanitize_text_field($_POST['custom_filter']);
        $length                     = intval($_POST['length']);
        $start                      = intval($_POST['start']);
        $search_term                = sanitize_text_field($_POST['search']['value']);

        $where_condition = '1=1';

        if (!empty($search_term)) {
            $where_condition .= " AND (`first_name` LIKE '%$search_term%' OR `last_name` LIKE '%$search_term%' OR `customer_email` LIKE '%$search_term%' OR `company` LIKE '%$search_term%' OR `product_name` LIKE '%$search_term%' OR `print_status` LIKE '%$search_term%') AND product_id NOT IN (27011, 27010, 18792)";
        }
        $query = "SELECT
                    es.ID,
                    u.ID,
                    u.user_login,
                    u.user_email,
                    u.display_name,

                FROM
                    $shop_subscription_expired es 
                JOIN
                $users u WHERE $where_condition";
        if($length != -1)
        {
            $query .= " LIMIT $start, $length";
        }
        $orders = $wpdb->get_results($query);
        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $shop_subscription_expired WHERE $where_condition AND product_id NOT IN (27011, 27010, 18792)");
        $data = [];

        foreach ($orders as $order) {
            
            $data[] = array(
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


    public static function instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
ExpiredSubscriptions::instance();