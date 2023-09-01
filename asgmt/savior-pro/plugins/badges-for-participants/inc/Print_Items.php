<?php

class Print_Items
{
    public $print_items_page;

    public function __construct()
    {
        $this->check_user_permissions();

        add_action('admin_menu', array($this, 'add_menu_print_items_page'));

        add_action('wp_ajax_print_qr_code_items', array($this, 'print_qr_code_items'));
    }

    public function check_user_permissions()
    {
        if (!is_admin()) return;
    }

    public function add_menu_print_items_page()
    {
        $this->print_items_page = add_menu_page('Items QR Codes', 'Items QR Codes', 'view_badges', 'print_items', array($this, 'display_print_items_page'));
    }

    public function get_sessions_by_ids($ids_arr)
    {
        global $wpdb;
        $out = array();

        if (!empty($ids_arr)) {

            $ids = implode(',', array_map('absint', $ids_arr));
            $sql = " SELECT * FROM {$wpdb->prefix}cong_panel_sessions ps WHERE ps.id IN($ids)";

            $out = $wpdb->get_results($sql, 'ARRAY_A');
        }

        return $out;
    }

    public function get_socials_by_ids($ids_arr)
    {
        global $wpdb;
        $out = array();

        if (!empty($ids_arr)) {

            $ids = implode(',', array_map('absint', $ids_arr));
            $sql = " SELECT * FROM {$wpdb->prefix}cong_social_events ps WHERE ps.id IN($ids)";

            $out = $wpdb->get_results($sql, 'ARRAY_A');
        }

        return $out;
    }

    public function print_qr_code_items() {
        if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'print_qr_code_items' && !empty($_POST['items'])) {
            $ids = explode(',', $_POST['items']);
            $data = array();
            $type = $_POST['type'];


            if ($type == 'session') {
                $sessions = $this->get_sessions_by_ids($ids);
            } else {
                $sessions = $this->get_socials_by_ids($ids);
            }


            if (!empty($sessions)) {
                foreach ($sessions as $key => $session) {
                    $src = BADGES_PLUGIN_URL . 'inc/generate_qr_item.php?' . http_build_query(
                            array(
                                'name' => $session['name'],
                                'id' => $session['id'],
                                'type' => $type
                            ));

                    $data[$key]['qr_code'] = '<img src="'.$src.'"/>';
                    $data[$key]['name'] = $session['name'];
                    $data[$key]['src'] = $src;
                    $data[$key]['id'] = $session['id'];
                }
            }

            echo json_encode($data);
            wp_die();
        }
        wp_die();
    }

    public function display_print_items_page()
    {
        if (!isset($_GET['ids']) || empty($_GET['ids'])) return;

        $ids = explode(',', $_GET['ids']);
        $type = $_GET['type'];

        if ($type == 'session') {
            $items = $this->get_sessions_by_ids($ids);
        } else {
            $items = $this->get_socials_by_ids($ids);
        }



        if (!empty($items)) {
            ?>
            <div class="wrap attendance-data" id="badges">
                <button class="button download-pdf-qr-codes">Download PDF</button>
                <input type="hidden" name="print_pdf_ids" value="<?php echo sanitize_text_field($_GET['ids']); ?>">
                <input type="hidden" name="type" value="<?php echo sanitize_text_field($_GET['type']); ?>">
                <div class="qr-code-items">
                    <?php
                    foreach ($items as $item) {

                        $src = BADGES_PLUGIN_URL . 'inc/generate_qr_item.php?' . http_build_query(
                                array(
                                    'name' => $item['name'],
                                    'id' => $item['id'],
                                    'type' => $type
                                ));
                        ?>
                        <div class="item">
                            <div class="session-id">ID: <?php echo $item['id']; ?></div>
                            <div class="session-name">Name: <?php echo $item['name']; ?></div>
                            <div class="session-qr-code">
                                <img src="<?php echo esc_url($src); ?>">
                            </div>
                        </div>
                        <?php
                    } ?>
                </div>
            </div>
            <?php
        }
    }
}