<?php
class ExpiredSubscriptions
{
    private static $instance = null;

    public function __construct()
    {
        $screen = get_current_screen();
        // error_log(print_r('get_current_screen', true));
        // error_log(print_r($screen, true));
        add_action('admin_menu', array($this, 'add_menu_expired_subscriptions_page'));
        add_action('admin_enqueue_scripts', array($this, 'expired_subscriptions_enqueue_scripts'));
        add_action('admin_footer', array($this, 'expired_subscriptions_datatable_scripts'));
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
        wp_localize_script('datatables-management', 'wp_admin_obj', array(
            'admin_url' => admin_url(),
            'ajaxurl'   => admin_url('admin-ajax.php')
        ));
        //DataTable Fix Header
        // wp_register_style('datatables-fixedHeader', '//cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css', array(), '2.4.1');
        // wp_register_script('datatables-fixedHeader', '//cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js', array(), '3.4.0', true);
        
        //DataTable Buttons
        // wp_register_style('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css', array(), '2.3.6');
        // wp_register_script('datatables-management-buttons', '//cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js', array('jquery'), '2.3.6', true);
        // wp_register_script('datatables-management-buttons-html5', '//cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js', array('jquery'), '2.3.6', true);
        // wp_register_script('datatables-management-pdfmake', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array('jquery'), '0.1.53', true);
        // wp_register_script('datatables-management-vfs_fonts', '//cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array('jquery'), '0.1.53', true);
        // wp_register_script('datatables-management-jszip', '//cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js', array('jquery'), '3.1.3', true);
    }

    public function expired_subscription_fn()
    {
        wp_enqueue_style('datatables-management');
        // wp_enqueue_style('datatables-fixedHeader');
        // wp_enqueue_style('datatables-management-buttons');

        wp_enqueue_script('datatables-management');      
        // wp_enqueue_script('datatables-fixedHeader');
        // wp_enqueue_script('datatables-management-buttons');
        // wp_enqueue_script('datatables-management-buttons-html5');
        // wp_enqueue_script('datatables-management-pdfmake');
        // wp_enqueue_script('datatables-management-vfs_fonts');
        // wp_enqueue_script('datatables-management-jszip');

        
        ?>
        <div class="wrap" >
            <h2>Expired Subscription</h2>
            <div class="expired-subscriptions" style="width: 97%;background-color: #FFFFFF;padding: 18px;border-radius: 10px;margin-top: 20px;box-shadow: 0 3px 10px rgb(0 0 0 / 0.2);">      
                <!-- Table -->
                <table id='expired-subscriptions' class='display nowrap' width="100%">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Subscription ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Order ID</th>
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
                        // fixedHeader: true,
                        dom: 'Blfrtip',
                        pageLength: 25,
                        processing: true,
                        serverSide: true,
                        ajax: {
                            url: wp_admin_obj.ajaxurl, // Provide the URL to the server-side script
                            type: 'POST',
                            data: function (data) {
                                data.action = "expired_subscriptions";
                            },
                        },
                        columns: [
                            { 
                                data: 'sn',
                                render: function (data, type, row, meta) {
                                    return meta.row + meta.settings._iDisplayStart + 1; // Calculate the serial number
                                }
                            },
                            { data: '_subscription_id' },
                            { data: '_billing_first_name' },
                            { data: '_billing_last_name' },
                            { data: '_billing_email' },
                            { 
                                data: '_order_id',
                                render: function (data, type, row, meta) 
                                {
                                    return '<a href="'+wp_admin_obj.admin_url+'post.php?post='+data+'&action=edit">'+data+'</a>';
                                }
                            },
                        ],
                        aLengthMenu: [
                            [10, 25, 50, 100, 200, -1],
                            [10, 25, 50, 100, 200, "All"]
                        ],
                        // scrollX: true,
                        
                    });

                });
            </script>
        JS;
    }
    public function get_expired_subscriptions_callback()
    {
        global $wpdb;
        $expiredTable  = $wpdb->prefix . 'shop_subscription_expired';
        // $custom_filter              = sanitize_text_field($_POST['custom_filter']);
        $length                     = intval($_POST['length']);
        $start                      = intval($_POST['start']);
        $search_term                = sanitize_text_field($_POST['search']['value']);

        $where_condition = '1=1';

        if (!empty($search_term)) {
            $where_condition .= " AND (`post_content` LIKE '%$search_term%' OR `ID` LIKE '%$search_term%' OR `post_parent` LIKE '%$search_term%')";
        }
        $sql_que = "SELECT `ID`, `post_content`, `post_parent` FROM $expiredTable WHERE $where_condition";
        $query = $wpdb->prepare($sql_que);
        if($length != -1)
        {
            $query .= " LIMIT $start, $length";
        }
        $subscriptionMetadatas = $wpdb->get_results($query);

        $total_records = $wpdb->get_var("SELECT COUNT(*) FROM $expiredTable WHERE $where_condition");

        $keysToFind = ['_billing_first_name', '_billing_last_name', '_billing_email'];

        $subscriptionExpired = [];
        foreach ($subscriptionMetadatas as $subscriptionMetadata) {
            $postContent = json_decode($subscriptionMetadata->post_content, true);
            
            $userMeta = array_filter($postContent, function ($item) use ($keysToFind) {
                return in_array($item['meta_key'], $keysToFind);
            });

            $postContentData = array_combine(
                array_column($userMeta, 'meta_key'),
                array_column($userMeta, 'meta_value')
            );

            $subscriptionExpired[] = [
                'sn'                    => '',
                '_subscription_id'      => $subscriptionMetadata->ID,
                '_billing_first_name'   => $postContentData['_billing_first_name'] ?? null,
                '_billing_last_name'    => $postContentData['_billing_last_name'] ?? null,
                '_billing_email'        => $postContentData['_billing_email'] ?? null,
                '_order_id'             => $subscriptionMetadata->post_parent,
            ];
        }
        $response = [
            'draw' => intval($_POST['draw']),
            'recordsTotal' => $total_records,
            'recordsFiltered' => $total_records,
            'data' => $subscriptionExpired,
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