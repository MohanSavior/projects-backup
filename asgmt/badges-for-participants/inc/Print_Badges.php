<?php

class Print_Badges
{
    public $print_badges_page;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_print_badges_page'));

        add_action('wp_ajax_update_printing_count', array($this, 'update_printing_count'));

        add_shortcode( 'print_badge_qr', array($this, 'print_badge_qr_code_fn') );
    }

    public function add_menu_print_badges_page()
    {
        $this->print_badges_page = add_menu_page('Print Badges', 'Print Badges', 'export', 'print_badges', array($this, 'display_print_badges'));
    }

    public function increase_printed_count($member)
    {
        $member->printed_count = (!empty($member->printed_count) ? $member->printed_count + 1 : 1);
        return $member;
    }

    public function return_compact_members_data($member)
    {
        return '(' . $member->id . ',' . $member->printed_count . ')';
    }

    public function increase_member_without_guests($ids)
    {
        global $wpdb;
        $members = $wpdb->get_results("SELECT id, printed_count FROM {$wpdb->prefix}cong_registrations WHERE id IN (" . implode(", ", $ids) . ")");

        if (!empty($members)) {
            $new_members = array_map(array($this, 'increase_printed_count'), $members);

            $res = $wpdb->query("INSERT INTO {$wpdb->prefix}cong_registrations (id,printed_count) VALUES " . implode(", ", array_map(array($this, 'return_compact_members_data'), $new_members)) . "
 ON DUPLICATE KEY UPDATE id=VALUES(id),printed_count=VALUES(printed_count);");

            $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');

            return $resText;
        }
    }

    public function return_compact_guests_data($member)
    {
        return '(' . $member->id . ',' . $member->printed_count . ')';
    }

    public function increase_members_guests($ids)
    {
        global $wpdb;
        $members = $wpdb->get_results("SELECT id, printed_count FROM {$wpdb->prefix}cong_regs_persons WHERE id IN (" . implode(", ", $ids) . ")");

        if (!empty($members)) {
            $new_members = array_map(array($this, 'increase_printed_count'), $members);

            $res = $wpdb->query("INSERT INTO {$wpdb->prefix}cong_regs_persons (id,printed_count) VALUES " . implode(", ", array_map(array($this, 'return_compact_guests_data'), $new_members)) . "
 ON DUPLICATE KEY UPDATE id=VALUES(id),printed_count=VALUES(printed_count);");

            $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');

            return $resText;
        }
    }

    public function print_badge_qr_code_fn( $atts )
    {
        $atts = shortcode_atts(
        array(
            'current_user_id' => get_current_user_id(),
            // 'format_type' => 'dyno',//letter
        ), $atts, 'print_badge_qr' );

        $user_id = $atts['current_user_id'];           
        if(is_user_logged_in())
        {
            $user = get_userdata($user_id);
            $current_user               = $user;
            $user_obj                   = array();
            $user_obj['name']           = $current_user->first_name.' '.$current_user->last_name;
            $user_obj['email']          = $current_user->user_email;
            $user_obj['first_name']     = $current_user->first_name;
            $user_obj['last_name']      = $current_user->last_name;            
            $user_obj['role']     = get_user_meta( $current_user->ID, 'user_title', true );
            $user_obj['company']  = get_user_meta( $current_user->ID, 'user_employer', true );
            $user_obj['visitor_type']   = get_user_meta( $current_user->ID, 'ure_select_other_roles', true );
            // $user_obj['company']        = get_user_meta( $current_user->ID, 'billing_company', true );

            $billing_phone = get_user_meta($current_user->ID, 'billing_phone', true);
            $primary_booth_admin_contact = get_user_meta($current_user->ID, 'primary_booth_admin_contact', true);
            $user_phone = get_user_meta($current_user->ID, 'user_phone', true);
            $primary_contact = !empty($billing_phone) ? $billing_phone : ($primary_booth_admin_contact ? $primary_booth_admin_contact : $user_phone);
            $user_obj['phone_daytime']  = $primary_contact;
            $addr_addr_1                = get_user_meta( $current_user->ID, 'billing_address_1', true );
            $addr_addr_2                = get_user_meta( $current_user->ID, 'billing_address_2', true );
            $addr_city                  = get_user_meta( $current_user->ID, 'billing_city', true );
            $addr_state                 = get_user_meta( $current_user->ID, 'billing_state', true );
            $addr_country               = get_user_meta( $current_user->ID, 'billing_country', true );
            $addr_zip                   = get_user_meta( $current_user->ID, 'billing_postcode', true );

            $ceu_product_ids = get_field('ceu_keyword_display_via_product_purchased', 'option');

            $member_roles = get_field('member_roles', 'option');

            $role_flag = array();
            if(!empty($member_roles) && is_array($member_roles))
            {
                foreach($member_roles as $roles_with_keyword)
                {
                    if(array_intersect($roles_with_keyword['roles'], $current_user->roles))
                    {
                        $role_flag[]=$roles_with_keyword['keyword_display_on_the_badge'];
                    }
                    // $member_roles_with_flage[$roles_with_keyword['keyword_display_on_the_badge']] = $roles_with_keyword['roles'];
                }
            }
            // echo "<pre>";
            // print_r($user_obj);
            // echo "</pre>";
            // print_r($this->check_user_product_matches_in_orders($current_user->ID, $ceu_product_ids));
            // $role_flag[] = $this->check_user_product_matches_in_orders($current_user->ID, $ceu_product_ids) == true ? 'CEU' : '';

            $user_flag = implode(', ', array_filter( $role_flag) );
            // ob_start();
            $out = sprintf('
                <div class="user-badge-preview">
                    <div class="badge-header">
                        <h3 class="badge-heading">%s</h3>
                    </div>
                    <div class="badge-content">
                        <div class="badge-left-content">
                            <p class="badge-username">%s</p>
                            <p class="badge-title">%s</p>
                            <p class="badge-employer">%s</p>
                            <p class="badge-address">%s</p>
                        </div>
                        <div class="badge-right-content">%s</div>
                    </div>
                    <div class="badge-footer">
                        <p class="label">%s</p>
                        <p class="badge">Sept. 11-14, 2023</p>
                        <img class="badge-footer-logo" src="%s"/>
                    </div>
                </div>
                ',
                $current_user->first_name,
                $current_user->first_name. ' ' .$current_user->last_name,
                get_user_meta( $current_user->ID, 'user_title', true ),
                get_user_meta( $current_user->ID, 'user_employer', true ),
                preg_replace('!\s+!', ' ', $addr_city. ' ' .$addr_state. ' ' .$addr_country),
                $this->generate_qr_code($user_obj),
                $user_flag,
                //ucfirst(get_user_meta( $current_user->ID, 'user_type_label', true ) ? implode(' ', get_user_meta( $current_user->ID, 'user_type_label', true )) : $user_obj['role'][0]),
                site_url(). '/wp-content/plugins/badges-for-participants/assets/images/logo.jpg'
            );
        }
        // $out = ob_get_contents();
        // ob_end_clean();
        return $out;
    }
    public function display_print_badges()
    {
        global $wpdb;

        $ids = isset($_GET['ids']) ? $_GET['ids'] : '';
        if (mb_strlen($ids) == 0) {
            wp_redirect(admin_url('admin.php?page=member_listing'));
        }

        $order = $order_by = '';

        $data = explode(',', $_GET['ids']);

        $imploded_ids = implode(',', $data);

        $sql = "SELECT * FROM " . $wpdb->prefix . "qr_bage_data bd WHERE bd.id IN (" . $imploded_ids . ")";

        if (isset($_REQUEST['orderby'])) {
            $order_by = $_REQUEST['orderby'];
            $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'ASC';

            $sql .= " ORDER BY bd." . $order_by . " " . $order . "";

        } else {
            $sql .= " ORDER BY bd.first_name ASC, bd.last_name ASC";
        }

        $data = $wpdb->get_results($sql, 'ARRAY_A');

        $path = get_home_path();
        $out = '';

        $template = (isset($_GET['template']) && !empty($_GET['template']) ? $_GET['template'] : 1);

        $templates = array(0 => '1', 1 => '2');

        $counter = 0;

        $templates_name = array(
            0 => 'Dyno format',
            1 => 'Letter format'
        );

        ?>
        <div class="wrap" id="badges">
            <div class="top-header header-badges">
                <h2>Print badges</h2>
                <div class="manual-select-template">
                    <form method="get" action="/wp-admin/admin.php">
                        <input type="hidden" name="page" value="print_badges">
                        <input type="hidden" name="ids" value="<?php echo $_GET['ids']; ?>">
                        <select name="template">
                            <?php foreach ($templates_name as $k => $tpl): ?>
                                <option
                                    <?php echo($template == $k ? 'selected="selected"' : ''); ?> value="<?php echo $k; ?>">
                                    <?php echo $tpl; ?></option>
                            <?php endforeach; ?>
                        </select>

                        <?php if (!empty($order_by)): ?>
                            <input type="hidden" name="orderby" value="<?= $order_by;?>">
                        <?php endif; ?>

                        <?php if (!empty($order)): ?>
                            <input type="hidden" name="order" value="<?= $order;?>">
                        <?php endif; ?>

                    </form>
                </div>
                <div class="button action-download-pdf">Download PDF</div>
                <div class="progress-wrap progress" data-progress-percent="25">
                    <div class="progress-bar progress"></div>
                </div>
            </div>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <div id="templates" template="<?php echo $template; ?>">
                            <?php

                            foreach ($data as $k => $badge) {
                                $print_qr = $badge['is_qr_print'] === 'Yes';

                                $daypass = $badge['visitor_type'] == 'Day Pass';
                                $rec = json_decode($badge['data'], true);
                                $rec['id'] = $badge['id'];
                                $label = [];
                                if ($rec['is_ceu'] == 'Yes') {
                                    $label[] = 'CEU';
                                }
                                if ($rec['is_gas_flow'] == 'Yes') {
                                    $label[] = 'Gt';
                                }
                                if ($rec['is_liquid'] == 'Yes') {
                                    $label[] = 'LC';
                                }
                                if ($rec['role_exhibitor'] == 'No' && $rec['role_committee'] == 'No' && !$daypass) {
                                    $label[] = 'St';
                                }
                                if ($rec['role_speaker'] == 'Yes') {
                                    $label[] = 'Sp';
                                }
                                if ($rec['role_committee'] == 'Yes') {
                                    $label[] = 'C';
                                }
                                if ($rec['role_board'] == 'Yes') {
                                    $label[] = 'BOD';
                                }
                                if ($rec['role_exhibitor'] == 'Yes') {
                                    $label[] = 'E';
                                }

                                ?>
                                <div class="badge_1 <?php echo 'tpl-' . $template; ?> <?php echo($daypass == true ? 'daypass' : ''); ?>"
                                     data-id="<?php echo $k; ?>"
                                     data-reg-id="<?php echo $rec['id']; ?>">
                                    <?php if ($template == 2): ?>
                                        <div class="top">
                                            <div class="user-friendly-name"><span
                                                        class="full-width"><?= $rec['friendly_name'] ?></span></div>
                                            <div class="left">
                                                <div class="flex-block">
                                                    <div class="top">
                                                        <div class="user-name"><span
                                                                    class="full-width"><?= $rec['first_name'] . ' ' . $rec['last_name'] ?></span>
                                                        </div>
                                                        <div class="user-job"><span
                                                                    class="full-width"><?= $rec['job'] ?></span></div>
                                                    </div>
                                                    <div class="user-company-address">
                                                        <div class="user-company"><span
                                                                    class="full-width"><?= $rec['company'] ?></span>
                                                        </div>
                                                        <div class="user-address">
                                                            <span class="full-width">
                                                                <?php
                                                                $city_f = '';
                                                                if (!empty($rec['city'])) {
                                                                    $city_f .= $rec['city'] . (!empty($rec['state']) ? ', ' : '');
                                                                }
                                                                if (!empty($rec['state'])) {
                                                                    $city_f .= $rec['state'] . (!empty($rec['country']) ? ', ' : '');
                                                                }
                                                                if (!empty($rec['country'])) {
                                                                    $city_f .= $rec['country'];
                                                                }
                                                                echo $city_f;
                                                                ?>

                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="tmp_string"></div>
                                            </div>
                                            <div class="right">
                                                <div class="qr-code">
                                                    <?php if ($print_qr === true): ?>
                                                        <?= $this->generate_qr_code($rec) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="bottom-qr">
                                                    <div class="b-label"><?= implode(' ', $label) ?></div>
                                                    <div class="b-date">SEPTEMBER 13-16, 2021</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="bottom">
                                            <img src="<?= BADGES_PLUGIN_URL ?>assets/images/logo.jpg"/>
                                        </div>
                                    <?php else: ?>
                                        <?php if ($daypass == true): ?>
                                            <div class="daypass-tpl <?= ($print_qr === true ? 'print-qr' : ''); ?>">
                                                <div class="tp">
                                                    <div class="left-block">
                                                        <div class="user-friendly-name-alt"><span
                                                                    class="full-width"><?= $rec['friendly_name'] ?></span>
                                                        </div>
                                                        <div class="user-name-alt"><span
                                                                    class="full-width"><?= $rec['first_name'] . ' ' . $rec['last_name'] ?></span>
                                                        </div>
                                                        <div class="user-title-alt"><span
                                                                    class="full-width"><?= $rec['job'] ?></span></div>
                                                    </div>


                                                    <?php if ($print_qr === true):
                                                        ?>
                                                        <div class="qr-code">
                                                            <?= $this->generate_qr_code($rec) ?>
                                                        </div>
                                                    <?php endif; ?>

                                                </div>
                                                <div class="bt">
                                                    <div class="user-company-alt"><span
                                                                class="full-width"><?= $rec['company'] ?></span></div>
                                                    <div class="b-date">SEPTEMBER 13-16, 2021</div>
                                                    <?php if (in_array('Sp', $label)): ?>
                                                        <div class="b-label">Sp</div>
                                                    <?php endif; ?>
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
                                                            <div class="user-name"><span
                                                                        class="full-width"><?= $rec['first_name'] . ' ' . $rec['last_name'] ?></span>
                                                            </div>
                                                            <div class="user-job"><span
                                                                        class="full-width"><?= $rec['job'] ?></span>
                                                            </div>
                                                        </div>
                                                        <div class="user-company-address">
                                                            <div class="user-company"><span
                                                                        class="full-width"><?= $rec['company'] ?></span>
                                                            </div>
                                                            <div class="user-address">
                                                                <span class="full-width">
                                                                    <?php
                                                                    $city_f = '';
                                                                    if (!empty($rec['city'])) {
                                                                        $city_f .= $rec['city'] . (!empty($rec['state']) ? ', ' : '');
                                                                    }
                                                                    if (!empty($rec['state'])) {
                                                                        $city_f .= $rec['state'] . (!empty($rec['country']) ? ', ' : '');
                                                                    }
                                                                    if (!empty($rec['country'])) {
                                                                        $city_f .= $rec['country'];
                                                                    }
                                                                    echo $city_f;
                                                                    ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tmp_string"></div>
                                                </div>
                                                <div class="right">
                                                    <div class="qr-code">
                                                        <?php if ($print_qr === true): ?>
                                                            <?= $this->generate_qr_code($rec) ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="bottom-qr">
                                                        <div class="b-label"><?= implode(' ', $label) ?></div>
                                                        <div class="b-date">SEPTEMBER 13-16, 2021</div>
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
                            ?>
                        </div>
                    </div>
                </div>
            </div>
            <br class="clear">
        </div>
        </div>
        <?php
    }

    public function get_avatar_url($get_avatar)
    {
        preg_match("/src='(.*?)'/i", $get_avatar, $matches);
        return $matches[1];
    }

    public function encode_array($arr)
    {
        return rtrim(strtr(base64_encode(gzdeflate(json_encode($arr), 9)), '+/', '-_'), '=');
    }

    public function generate_qr_code($user_info)
    {
        $img = GenerateQRCode::render($user_info);
        return '<img src="' . $img . '"/>';
    }

    public function update_printing_count()
    {
        $res_members = $res_guests = 'none';
        if (isset($_POST) && isset($_POST['action']) && $_POST['action'] === 'update_printing_count' && isset($_POST['ids']) && !empty($_POST['ids']) && is_array($_POST['ids'])) {
            $ids = $_POST['ids'];

            if (isset($ids['members']) && !empty($ids['members']) && is_array($ids['members'])) {
                $res_members = $this->increase_member_without_guests($ids['members']);
            }

            if (isset($ids['guests']) && !empty($ids['guests']) && is_array($ids['guests'])) {
                $res_guests = $this->increase_members_guests($ids['guests']);
            }
        }

        $res = [
            'member' => $res_members,
            'guests' => $res_guests
        ];

        print_r($res);

        wp_die();
    }

    public function check_user_product_matches_in_orders($user_id, $product_ids) {
        $current_year = date('Y');
        $args = array(
            'post_type'      => 'shop_order',
            'post_status'    => 'wc-completed', // Change to the desired order status
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'date_query'     => array(
                array(
                    'year' => $current_year,
                ),
            ),
            'meta_query'     => array(
                array(
                    'key'   => '_attendees_order_meta',
                    'compare' => 'EXISTS', // Make sure the meta key exists in the order
                ),
            ),
        );
    
        $order_ids = get_posts($args);
    
        foreach ($order_ids as $order_id) {
            $attendees_order_meta = get_post_meta($order_id, '_attendees_order_meta', true);
    
            if (!$attendees_order_meta || !is_array($attendees_order_meta)) {
                return false;
            }
            $customer_id = get_post_meta($order_id, '_customer_user', true);
            if( $user_id == $customer_id ) 
            {
                $order_items = wc_get_order($order_id)->get_items();
                if (!empty($order_items)) {
                    foreach ($order_items as $item) {
                        if(in_array($item->get_product_id(), $product_ids))
                        {
                            return true;
                        }else{
                            return false;
                        }
                        break;
                    }
                }
            }else{
                foreach ($attendees_order_meta as $attendee) {
                    if (
                        isset($attendee['user_id']) && ( $attendee['user_id'] == $user_id) &&
                        isset($attendee['product_id']) && in_array($attendee['product_id'], $product_ids)
                    ) {
                        return true;
                        break;
                    }
                }
            }
        }
        return false;
    }

    

}