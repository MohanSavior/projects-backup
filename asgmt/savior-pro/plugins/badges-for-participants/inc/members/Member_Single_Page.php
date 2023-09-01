<?php

class Member_Single_Page
{
    public $member_single_page;

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_menu_member_single_page'));
        add_action('wp_ajax_action_check_in', array($this, 'action_check_in'));
        add_action('wp_ajax_action_printed_in', array($this, 'action_printed_in'));
        add_action('wp_ajax_action_printed_in_ids', array($this, 'action_printed_in_ids'));
        add_action('admin_init', array($this, 'get_print_data_and_print'));
    }

    public function add_menu_member_single_page()
    {
        $this->member_single_page = add_menu_page('Member Page', 'Member Page', 'export', 'member_page', array($this, 'display_member_single_page'));
    }

    public function get_acc_persons($cong_reg_id, $limit)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}cong_regs_persons ";
        $sql .= " WHERE cong_reg_id={$cong_reg_id} LIMIT {$limit}";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        return stripslashes_deep($result);
    }

    public function get_max_acc_persons_from_all($paid = 'all')
    {
        global $wpdb;
        $sql = "SELECT COUNT(cong_reg_id) AS max_accs FROM {$wpdb->prefix}cong_regs_persons ";
        //$sql .= " WHERE cong_reg_id={$congress_reg_id} ";
        if ($paid == 'paid') {
            $sql .= " AND paid = 1 ";
        } else if ($paid == 'unpaid') {
            $sql .= " AND paid = 0 ";
        }
        $sql .= " GROUP BY cong_reg_id ORDER BY COUNT( cong_reg_id ) DESC  LIMIT 1";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        if (count($result)) {
            $max_accs = $result[0]['max_accs'];
        } else {
            $max_accs = 0;
        }
        return stripslashes_deep($max_accs);
    }

    public function returnEditPage($reg)
    {
        global $congressRegForm;
        if ($reg['has_day_pass']) {
            $admin_url = $congressRegForm->get_admin_product_link_day_pass($reg['congress_year']);
        } else {
            $admin_url = $congressRegForm->get_admin_product_link($reg['congress_year']);
        }
        return sprintf('<a target="_blank" class="button" href="%s/?user_id=%s">Edit</a>', $admin_url, esc_attr(absint($reg['user_id'])));
    }

    public function invalid_message()
    {
        $notice = get_option('invalid_message');

        if (!empty($notice)) {
            printf('<div class="notice notice-error">%s</div>', $notice);
            delete_option('invalid_message');
        }
    }

    private function update_participant($id, $user)
    {
        global $wpdb;
        $set = array();
        foreach ($user as $key => $val) {
            if (in_array($key, ['first_name', 'last_name', 'company', 'email', 'is_attendee', 'is_gas_flow', 'is_ceu', 'is_liquid', 'visitor_type', 'is_qr_print'])) {

                if (in_array($key, ['first_name']) || in_array($key, ['last_name']) || in_array($key, ['company'])) {
                    $val = trim($val);

                    $user[$key] = $val;
                }

                if (in_array($key, ['email'])) {

                    $sanitized_email = sanitize_email($val);
                    $trimmed_email = trim($val);

                    if ($sanitized_email !== $val || $trimmed_email !== $val) {
                        update_option('invalid_message', 'Invalid Email Field');
                        header('Location: '.$_SERVER['REQUEST_URI']);
                        die();
                    }

                    $val = trim($val);

                    $user[$key] = $val;
                }


                $set[] = $key . '=\'' . $val . '\'';
            }

            if (in_array($key, ['friendly_name', 'company', 'job', 'country', 'state'])) {
                $user[$key] = $val;
            }
        }

        $set[] = 'data=\'' . json_encode($user, JSON_UNESCAPED_UNICODE) . '\'';
        $sql = 'UPDATE ' . $wpdb->prefix . 'qr_bage_data SET ' . implode(',', $set) . ' WHERE id =' . $id;
        $result = $wpdb->get_results($sql, 'ARRAY_A');
    }

    public function display_member_single_page()
    {
        global $wpdb;
        $congress_year = (int)$_GET['congress_year'];
        $user_id = (int)$_GET['user_id'];
        $back_url = (isset($_GET['back_url']) ? $_GET['back_url'] : '');
        $data = (isset($_POST['root']) ? $_POST['root'] : null);
        if ($data !== null) {
            $data['name'] = $data['last_name'] . ' ' . $data['first_name'];
            $this->update_participant($user_id, $data);
            if (mb_strlen($back_url) > 0) {
                wp_redirect($back_url);
                exit;
            }
        }
        $sql = 'SELECT
*
FROM ' . $wpdb->prefix . 'qr_bage_data WHERE id=' . $user_id;
        $user = $wpdb->get_row($sql, 'ARRAY_A');
        ?>
        <div class="member-profile">

        <?php $this->invalid_message(); ?>


        <input type="hidden" name="reg_id" value="<?php echo $user['id']; ?>">
        <input type="hidden" name="congress_year" value="<?php echo $user['congress_year']; ?>">
        <div class="member-row">
            <div>
                <?php
                if (mb_strlen($back_url) > 0) {
                    ?>
                    <a href="<?= $back_url ?>" class="button">Back</a>
                    <?php
                }
                ?>
            </div>
            <h3>Member Info:</h3>
            <div class="inner-container">
                <div class="member-info" style="width: 100%">
                    <form method="post" class="submit-badges">
                        <table style="width: 100%">
                            <tr>
                                <td><b>Reg ID: </b></td>
                                <td><?php echo $user['id']; ?></td>
                            </tr>
                            <tr>
                                <td><b>Name: </b></td>
                                <td><?php echo $user['first_name']; ?> <?php echo $user['last_name']; ?></td>
                            </tr>
                            <tr>
                                <td><b>Company: </b></td>
                                <td><?php echo $user['company']; ?></td>
                            </tr>
                            <tr>
                                <td><b>Email: </b></td>
                                <td><?php echo $user['email']; ?></td>
                            </tr>
                            <?php if (!empty($user['country'])) { ?>
                                <tr>
                                    <td><b>Country: </b></td>
                                    <td><?php echo $user['country']; ?></td>
                                </tr>
                            <?php } ?>
                            <?php if (!empty($user['city'])) { ?>
                                <tr>
                                    <td><b>City: </b></td>
                                    <td><?php echo $user['city']; ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <td colspan="2">
                                    <div id="json_editor">

                                    </div>
                                    <script>
                                        var starting_value = <?php echo $user['data'] ?>;
                                    </script>
                                    <button class="button" type="submit">Update Changes</button>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <a class="button <?php echo($user['is_additional'] == true ? 'print-badge-show-error-message' : ''); ?>"
                                        <?php echo($user['is_additional'] == true ? 'data-parent-id="' . $user['parent_order_id'] . '"' : ''); ?>
                                       href="<?php echo admin_url('admin.php?page=print_badges&ids=' . $user['id']) ?>">Print
                                        Badge</a>
                                </td>
                            </tr>
                            <?php if ($user['parent_order_id'] == true): ?>
                                <tr class="hide-print-badge-show-error-message">
                                    <td colspan="2">
                                        <span class="styled-message-red-print-with-parent">Please print badge for this attendee from parent order with number <?php echo $user['parent_order_id']; ?></span>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </form>
                </div>
            </div>
        </div>
        <br/>
        <?php
        if (false) {
            ?>
            <div class="member-row">
                <h3>Actions:</h3>
                <div class="checkin">
                    <table>
                        <tr>
                            <td><?php echo $reg['first_name'] . ' ' . $reg['last_name']; ?></td>
                            <td><b>Check In: </b></td>
                            <td>
                                <div class="result-check-in single"><?php echo(isset($reg['check_in']) && !empty($reg['check_in']) && $reg['check_in'] == 1 ? 'yes' : 'no'); ?></div>
                            </td>
                            <td>
                                <div class="button action_check_in single">Checked</div>
                            </td>
                            <td>
                                <b> Check in date:</b> <span
                                        class="check-in-date-single"><?php echo(isset($reg['check_in_date']) && !empty($reg['check_in_date']) ? $reg['check_in_date'] : 'not checked'); ?></span>
                            </td>
                            <td>
                                <b>Check in time:</b> <span
                                        class="check-in-time-single"><?php echo(isset($reg['check_in_time']) && !empty($reg['check_in_time']) ? $reg['check_in_time'] : 'not checked'); ?></span>
                            </td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <?php if (count($acc_persons)): ?>
                                <td>
                                    <div class="button action_check_in all">Check All</div>
                                </td>
                            <?php endif; ?>
                            <td>
                                <form method="post">
                                    <?php echo $this->print_badge($reg['id'], $congress_year); ?>
                                    <input type="submit" class="button print_button_action"
                                           name="print_button_action"
                                           style="margin-left: 2px;" value="Print Badge"/>
                                </form>
                            </td>
                            <?php if (count($acc_persons)): ?>
                                <td>
                                    <form method="post">
                                        <?php echo $this->print_badge($reg['id'], $congress_year, true); ?>
                                        <input type="submit" class="button print_button_action"
                                               name="print_button_action"
                                               style="margin-left: 2px;" value="Print All Badges"/>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                        <?php if (in_array('administrator', (empty(get_userdata(get_current_user_id())) ? array() : get_userdata(get_current_user_id())->roles))): ?>
                            <tr>
                                <td>
                                    <?php echo $this->returnEditPage($reg); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
            </div>
            <?php
        }
    }

    public function get_sessions_by_reg_id($reg_id)
    {
        global $wpdb;
        $sorted = array();
        $all_cats = $this->get_all_cats();
        $sql = "";
        $sql .= " SELECT * FROM {$wpdb->prefix}cong_panel_sessions ps ";
        $sql .= " JOIN {$wpdb->prefix}cong_regs_panels rp ON ps.id = rp.panel_id ";
        $sql .= " WHERE rp.cong_reg_id = {$reg_id} ";
        $sql .= " ORDER BY ps.start_date_time ";
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        if (!empty($result)) {
            foreach ($result as $key => $item) {
                $unic_key = date('dmY', strtotime($item['start_date_time']));
                $item['cat_info'] = $all_cats[$item['cat_id']];
                $sorted[$unic_key]['save_data'] = $item['start_date_time'];
                $sorted[$unic_key][$item['cat_id']][] = $item;
            }
            return $sorted;
        }
    }

    public function get_print_data_and_print()
    {
        if (isset($_POST['bulk-print']) && !empty($_POST['bulk-print']) && isset($_POST['print_button_action']) && is_admin()) {
            $items = $_POST['bulk-print'];
            if (!empty($items)) {
                $_SESSION['print_badges'] = $items;
                wp_redirect(admin_url('admin.php?page=print_badges'));
            }
        }
    }

    public function action_check_in()
    {
        if (isset($_POST) && isset($_POST['action']) && $_POST['action'] === 'action_check_in' && isset($_POST['id']) && isset($_POST['congress_year'])) {
            global $wpdb;
            $val = ($_POST['type'] == 'false' ? 0 : 1);
            $id = (int)$_POST['id'];
            $congress_year = (int)$_POST['congress_year'];
            if (!empty($id) && !empty($congress_year)) {
                $res = $wpdb->update("{$wpdb->prefix}qr_bage_data",
                    array(
                        'is_checked' => (int)$val,
                    ),
                    array(
                        'id' => $id
                    ));
                $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');
                echo $resText;
            }
        }
        wp_die();
    }

    public function action_printed_in()
    {
        if (isset($_POST) && isset($_POST['action']) && $_POST['action'] === 'action_printed_in' && isset($_POST['id']) && isset($_POST['congress_year'])) {
            global $wpdb;
            $val = ($_POST['type'] == 'false' ? 0 : 1);
            $id = (int)$_POST['id'];
            $congress_year = (int)$_POST['congress_year'];
            if (!empty($id) && !empty($congress_year)) {
                $res = $wpdb->update("{$wpdb->prefix}qr_bage_data",
                    array(
                        'is_printed' => (int)$val,
                    ),
                    array(
                        'id' => $id
                    ));
                $resText = (isset($res) && $res !== false && (int)$res > 0 ? 'success' : 'error');
                echo $resText;
            }
        }
        wp_die();
    }

    public function action_printed_in_ids()
    {
        if (isset($_POST) && isset($_POST['action']) && $_POST['action'] === 'action_printed_in_ids' && isset($_POST['ids'])) {
            $ids = (array)$_POST['ids'];
            $get_user_ids_printed = get_option( 'badge_print_ids' );
            // update_option( 'badge_print_ids',array());
            $get_user_ids_printed = isset($get_user_ids_printed) && is_array($get_user_ids_printed) ? $get_user_ids_printed : array($get_user_ids_printed);
            $user_ids_printed = update_option( 'badge_print_ids', array_filter(array_unique(array_merge($ids,$get_user_ids_printed))) );
            if(is_wp_error($user_ids_printed))
            {
                wp_send_json_error( $user_ids_printed->get_error_message() );
            }else{
                wp_send_json_success();
            }
        }
        wp_send_json_error();
    }

    public function wpdb_update_in($table, $data, $where, $format = NULL, $where_format = NULL)
    {

        global $wpdb;

        $table = esc_sql($table);

        if (!is_string($table) || !isset($wpdb->$table)) {
            return FALSE;
        }

        $i = 0;
        $q = "UPDATE " . $wpdb->$table . " SET ";
        $format = array_values((array)$format);
        $escaped = array();

        foreach ((array)$data as $key => $value) {
            $f = isset($format[$i]) && in_array($format[$i], array('%s', '%d'), TRUE) ? $format[$i] : '%s';
            $escaped[] = esc_sql($key) . " = " . $wpdb->prepare($f, $value);
            $i++;
        }

        $q .= implode($escaped, ', ');
        $where = (array)$where;
        $where_keys = array_keys($where);
        $where_val = (array)array_shift($where);
        $q .= " WHERE " . esc_sql(array_shift($where_keys)) . ' IN (';

        if (!in_array($where_format, array('%s', '%d'), TRUE)) {
            $where_format = '%s';
        }

        $escaped = array();

        foreach ($where_val as $val) {
            $escaped[] = $wpdb->prepare($where_format, $val);
        }

        $q .= implode($escaped, ', ') . ')';

        return $wpdb->query($q);
    }
}