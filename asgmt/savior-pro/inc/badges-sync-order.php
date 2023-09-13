<?php
class Badges_Sync_Order
{
    public function __construct()
    {
        add_filter('manage_edit-shop_order_columns', array($this, 'add_badges_sync_header'));
        add_action('manage_shop_order_posts_custom_column', array($this, 'add_badges_sync_data'), 10, 2);
        add_action('admin_footer', array($this, 'badges_sync_footer_script'), 99); // For back-end
        add_action('wp_ajax_badge_ajax_sync_order', array($this, 'prepare_customer_details_by_order_id'));
    }


    public function add_badges_sync_header($columns)
    {
        $columns['badges_sync'] = __('Badges Sync', 'savior-pro');
        return $columns;
    }

    public function add_badges_sync_data($column_name, $post_id)
    {
        if ($column_name === 'badges_sync') {

            $order = wc_get_order($post_id);
            if ($order) {
                $items = $order->get_items();
                $product_ids = array();
                foreach ($items as $item) {
                    $product_id = $item->get_product_id();
                    if ($product_id) {
                        $product_ids[] = $product_id;
                    }
                }
            }
            if (!array_intersect($product_ids, array(27011, 27010, 18792))) {
                $_attendees_order_meta = $order->get_meta('_attendees_order_meta');
                $badges_customer_data = $this->prepare_customer_details_by_order_id($post_id, $_attendees_order_meta);
                // echo '<pre>';
                print_r($badges_customer_data);
                // echo '</pre>';
            }
        }
    }

    public function prepare_customer_details_by_order_id($order_id)
    {
        global $wpdb;
        $order_id = $order_id ?: (isset($_POST['order_id']) ? $_POST['order_id'] : null);

        if (!$order_id) {
            return;
        }

        $attendee_badge_orders = $wpdb->prefix . 'attendee_badge_orders';
        $order = wc_get_order($order_id);

        // Check if the order is valid and completed
        if ($order && $order->has_status('completed')) {
            $order_data = [];
            $_gravity_form_entry_id = $order->get_meta('_gravity_form_entry_id');

            if (isset($_gravity_form_entry_id) && GFAPI::entry_exists($_gravity_form_entry_id)) {
                $entry = GFAPI::get_entry($_gravity_form_entry_id);

                if ($entry['form_id'] == 11) {
                    $order_data[] = $this->get_customer_details_by_order($order);
                }

                $_attendees_order_meta = $order->get_meta('_attendees_order_meta');

                if (!empty($_attendees_order_meta) && is_array($_attendees_order_meta)) {
                    foreach ($_attendees_order_meta as $_attendees) {
                        $order_data[] = $this->get_customer_details_by_order(
                            $order,
                            (int)$_attendees['product_id'],
                            (int)$_attendees['user_id']
                        );
                    }
                }
            } elseif ($_gravity_form_entry_id) {
                $order_data[] = $this->get_customer_details_by_order($order);
            }

            if (!empty($order_data)) {
                $sync_return_data = '<i class="fas fa-check fa-2x" style="color: lightgreen;" aria-hidden="true"></i>';

                // Check for duplicate records in the database
                foreach ($order_data as $sync_data) {
                    $order_id = $sync_data['order_id'];
                    $customer_id = $sync_data['customer_id'];
                    $customer_email = $sync_data['customer_email'];
                    $date_created = $sync_data['date_created'];
                    $product_id = $sync_data['product_id'];

                    $check_order_query = $wpdb->prepare(
                        "SELECT COUNT(*) as count FROM $attendee_badge_orders
                        WHERE `order_id` = %d
                        AND `customer_id` = %d
                        AND `customer_email` = %s
                        AND DATE(date_created) = %s
                        AND `product_id` = %d",
                            $order_id,
                            $customer_id,
                            $customer_email,
                            $date_created,
                            $product_id
                    );

                    $count_badge = $wpdb->get_var($check_order_query);

                    if ($count_badge == null || $count_badge == 0) {
                        $sync_return_data = '<a href="javascript:void(0);" class="btn btn-info badge_sync_order" data-id="' . $order_id . '"><i class="fas fa-sync fa-2x"></i></a>';
                        break;
                    }
                }

                if (!wp_doing_ajax()) {
                    return $sync_return_data;
                } else {
                    try {
                        $ajax = array();
                        foreach ($order_data as $attendee_order_data) {
                            $check_order_query = $wpdb->prepare(
                                "SELECT COUNT(*) as count FROM $attendee_badge_orders
                                WHERE `order_id` = %d
                                AND `customer_id` = %d
                                AND `customer_email` = %s
                                AND DATE(date_created) = %s
                                AND `product_id` = %d",
                                $attendee_order_data['order_id'],
                                $attendee_order_data['customer_id'],
                                $attendee_order_data['customer_email'],
                                $attendee_order_data['date_created'],
                                $attendee_order_data['product_id']
                            );
            
                            $count_badge = $wpdb->get_var($check_order_query);
                            
                            if ($count_badge == null || $count_badge == 0) {
                                $ajax[]= $attendee_order_data;
                                // $wpdb->insert($attendee_badge_orders, $attendee_order_data);
                            }
                        }
                        wp_send_json_success($ajax);
                    } catch (\Throwable $th) {
                        wp_send_json_error($th->getMessage());
                    }
                }
            }
        }
    }


    public function get_customer_details_by_order($order, $product_id = null, $customer_id = null)
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
            'order_date'            => $order->get_date_created()->date("Y-m-d"),
            'order_status'          => $order->get_status(),
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

    public function badges_sync_footer_script()
    {
        ?>
            <script>
                (function($) {
                    $('a.badge_sync_order').on('click', function() {
                        let $this = $(this);
                        jQuery('a:focus').css('box-shadow', 'unset');
                        jQuery.ajax({
                            url: theplus_ajax_url,
                            method: 'POST',
                            data: {
                                action: 'badge_ajax_sync_order',
                                order_id: $this.data('id'),
                                nonce: theplus_nonce
                            },
                            beforeSend: function() {
                                $this.find('i').addClass('fa-spin');
                                $this.parent('td').css({
                                    'pointer-events': 'none',
                                    'cursor': 'default',
                                    'opacity': '0.6'
                                });
                                $this.css({
                                    'pointer-events': 'none',
                                    'cursor': 'no-drop'
                                });
                            },
                            success: function(response) {
                                if (response.success) {
                                    $this.parent('td').html('<i class="fa fa-check fa-2x" style="color: lightgreen;" aria-hidden="true"></i>');
                                }
                            },
                            error: function(errorThrown) {
                                alert('An error occurred, please try again');
                            },
                            complete: function() {
                                $this.find('i').removeClass('fa-spin');
                                $this.parent('td').attr("style", "");
                                $this.attr("style", "");
                            }
                        });
                    });
                })(jQuery);
            </script>
        <?php
    }
}

function check_if_woocommerce_orders_page()
{
    new Badges_Sync_Order();
}

add_action('admin_init', 'check_if_woocommerce_orders_page');
