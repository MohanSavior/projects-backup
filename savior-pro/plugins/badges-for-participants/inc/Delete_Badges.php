<?php

class Delete_Badges
{
    
    public $delete_badges_page;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_delete_badges_page'));

    }

    public function add_menu_delete_badges_page()
    {
        $this->delete_badges_page = add_menu_page('Delete Badges', 'Delete Badges', 'export', 'delete_badges', array($this, 'delete_badges'));
    }


    public function delete_badges()
    {
        
        global $wpdb;

        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (is_admin() && isset($_GET['delid']) && !empty($_GET['delid']) && current_user_can('administrator')) {

            $order_id = $_GET['delid'];

            // delete all data from Woo table
            $del1 = $wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id IN (SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id =".$order_id.")");
            $del2 = $wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id=".$order_id);

            // delete data from WP tables related to order
            $del3 = $wpdb->query("DELETE FROM {$wpdb->prefix}comments WHERE comment_type='order_note' AND comment_post_ID=".$order_id);
            $del4 = $wpdb->query("DELETE FROM {$wpdb->prefix}postmeta WHERE post_id=".$order_id);
            $del5 = $wpdb->query("DELETE FROM {$wpdb->prefix}posts WHERE ID=".$order_id);

            // delete badge record
            $del6 = $wpdb->query("DELETE FROM {$wpdb->prefix}qr_bage_data WHERE order_id=".$order_id);

            $_SESSION['del_badge_status'] = 'success';
            wp_redirect(admin_url('admin.php?page=member_listing'));
            exit;

        }else{
            $_SESSION['del_badge_status'] = 'error';
            wp_redirect(admin_url('admin.php?page=member_listing'));
            exit;
        }

    }
    

}