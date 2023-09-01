<?php

class Attendance_Data
{
    public $attendance_data;
    public $common;

    public function __construct()
    {
        // $this->common = new Common();

        add_action('admin_menu', array($this, 'add_import_sessions_data_page'));

//        add_action('admin_init', array($this, 'upload_attendance_data_from_form'));

        add_action('wp_ajax_export_attendance_data', array($this, 'export_attendance_data'));

        add_action('wp_ajax_export_attendance_data_by_date', array($this, 'export_attendance_data_by_date'));
    }

    public function add_import_sessions_data_page()
    {
        add_menu_page('Attendance Data', 'Attendance Data', 'view_badges', 'attendance_data', array($this, 'display_attendance_data'), '', '1.7');
    }

    public function get_visited_from_person_by_date($id, $person = 0, $data_check_in = '')
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cong_attendance_data';

        $sql = " SELECT DISTINCT cad.session_id, type FROM {$table_name} cad ";

        $sql .= " LEFT JOIN wp_cong_registrations cr ON cad.reg_id=cr.id AND cad.person_id='0' ";

        $sql .= " LEFT JOIN wp_cong_regs_persons rp ON cad.reg_id=rp.cong_reg_id AND rp.id=cad.person_id ";

        $sql .= " WHERE ";

        if ($person > 0) {
            $sql .= " cad.person_id={$person} AND rp.check_in_date='{$data_check_in}' ";
        } else {
            $sql .= " cad.person_id=0 AND cr.check_in_date='{$data_check_in}' ";
        }

        $res = $wpdb->get_results($sql, 'ARRAY_A');

        $sort = array();
        if (!empty($res)) {
            foreach ($res as $row) {
                $sort[$row['type']][] = $row;
            }
        }
        return $sort;
    }

    public function get_visited_from_person($id, $person = 0)
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cong_attendance_data';

        $sql = " SELECT DISTINCT session_id,id,person_id,type FROM {$table_name} WHERE reg_id={$id} ";

        if ($person > 0) {
            $sql .= " AND person_id={$person}";
        } else {
            $sql .= " AND person_id=0";
        }

        $res = $wpdb->get_results($sql, 'ARRAY_A');

        $sort = array();
        if (!empty($res)) {
            foreach ($res as $row) {
                $sort[$row['type']][] = $row;
            }
        }
        return $sort;
    }

    public function get_reg_id_info($reg_id)
    {
        global $wpdb;

        $sql = " SELECT * FROM {$wpdb->prefix}cong_registrations WHERE id={$reg_id} ";

        $res = $wpdb->get_row($sql);

        return $res;
    }

    public function get_info_about_guest_by_id($id)
    {
        global $wpdb;

        $sql = " SELECT * FROM {$wpdb->prefix}cong_regs_persons WHERE id={$id} ";

        $res = $wpdb->get_row($sql);

        return $res;
    }

    public function get_session_by_year_and_id($y = 2018, $id)
    {
        global $wpdb;
        $sql = "";

        $sql .= " SELECT ps.id, ps.name, ps.congress_year FROM {$wpdb->prefix}cong_panel_sessions ps ";

        $sql .= " WHERE congress_year={$y} AND id={$id}";


        $result = $wpdb->get_results($sql, 'ARRAY_A');


        if (!empty($result)) {
            $result = $result[0]['name'];
        }

        return $result;
    }

    public function get_social_event_by_year_and_id($y = 2018, $id)
    {
        global $wpdb;
        $sql = "";
        $result = array();

        $sql .= " SELECT ps.id, ps.name, ps.congress_year FROM {$wpdb->prefix}cong_social_events ps ";

        $sql .= " WHERE congress_year={$y} AND id={$id}";


        $result = $wpdb->get_results($sql, 'ARRAY_A');

        if (!empty($result)) {
            $result = $result[0]['name'];
        }

        return $result;
    }

    public function unique_multidim_array($array, $key)
    {
        $temp_array = array();
        $i = 0;
        $key_array = array();

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[$i] = $val[$key];
                $temp_array[$i] = $val;
            }
            $i++;
        }
        return $temp_array;
    }

    public function toString($item)
    {
        return "'$item'";
    }

    public function export_attendance_data_by_date()
    {
        if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'export_attendance_data_by_date' && !empty($_POST['reg_ids'])) {
            global $wpdb;

            $ids = $_POST['reg_ids'];
            $arr = $return_arr = array();
            $out = '';

            $ids_imploded = implode(',', array_map(array($this, 'toString'), $ids));

            $table_name = $wpdb->prefix . 'cong_attendance_data';

            $sql = " SELECT DISTINCT cr.check_in_date, cad.reg_id, cad.member_id, cad.person_id, cad.scan_date, cad.scan_time, rp.check_in_date as acc_check_in_date FROM {$table_name} cad ";

            $sql .= " LEFT JOIN wp_cong_registrations cr ON cad.reg_id=cr.id AND cad.person_id='0' ";

            $sql .= " LEFT JOIN wp_cong_regs_persons rp ON cad.reg_id=rp.cong_reg_id AND rp.id=cad.person_id ";

            $sql .= " WHERE cr.check_in_date IN($ids_imploded) OR rp.check_in_date IN($ids_imploded) ";

            $sql .= " GROUP BY cr.check_in_date, cad.reg_id, cad.member_id, cad.person_id ";

            $res = $wpdb->get_results($sql);

            if (!empty($res)) {

                foreach ($res as $re) {
                    $date = (!empty($re->acc_check_in_date) ? $re->acc_check_in_date : $re->check_in_date);
                    $arr_d[$date][] = $re;
                }

                krsort($arr_d);

                if (!empty($arr_d)) {
                    foreach ($arr_d as $key => $by_day) {
                        $data_check_in = $key;
                        foreach ($by_day as $d_key => $item) {

                            $order_key = $d_key;
                            $first_name = $last_name = $reg_id = $member_id = $phone = $email = $check_in_time = '-';


                            if (isset($item->person_id) && !empty($item->person_id) && $item->person_id !== 0) {
                                $res_info = $this->get_info_about_guest_by_id($item->person_id);
                                $first_name = $res_info->first_name;
                                $last_name = $res_info->last_name;
                                $check_in_time = $res_info->check_in_time;
                            } else {
                                $res_info = $this->get_reg_id_info($item->reg_id);
                                $first_name = $res_info->first_name;
                                $last_name = $res_info->last_name;
                                $reg_id = $res_info->id;
                                $member_id = get_aippi_usermeta($res_info->user_id, 'aippi_member_id', true);
                                $email = get_userdata($res_info->user_id)->data->user_email;
                                $phone = get_metadata('user', $res_info->user_id, 'phone')[0];
                                $check_in_time = $res_info->check_in_time;
                            }


                            $arr[$order_key] = [$first_name, $last_name, $reg_id, $member_id, $phone, $email, $check_in_time];

                        }
                        $return_arr[$data_check_in]['date'] = $data_check_in;
                        $return_arr[$data_check_in]['items'] = $arr;

                        $arr = array();
                    }
                }


                echo json_encode(array('success' => true, 'data' => $return_arr));
                wp_die();
            }
            echo json_encode(array('success' => false));
            wp_die();
        }
        wp_die();
    }

    public function get_attendance_data_days()
    {
        global $wpdb;
        $result = $res_by_persons = $res_by_members = $arr = array();
        $res_by_members = $wpdb->get_results(" SELECT DISTINCT check_in_date FROM wp_cong_registrations cr JOIN wp_cong_attendance_data ad ON ad.reg_id=cr.id WHERE 1 ", 'ARRAY_A');
        $res_by_persons = $wpdb->get_results(" SELECT DISTINCT check_in_date FROM wp_cong_regs_persons rp JOIN wp_cong_attendance_data ad ON ad.reg_id=rp.cong_reg_id WHERE 1 ", 'ARRAY_A');

        $res = array_merge($res_by_members, $res_by_persons);

        foreach ($res as $key => $item) {
            if (!empty($item['check_in_date'])) {
                $arr[] = $item['check_in_date'];
            }
        }
        $arr = array_unique($arr);
        ksort($arr);


        return $arr;
    }

    public function export_attendance_data()
    {
        if (isset($_POST) && !empty($_POST) && $_POST['action'] === 'export_attendance_data' && !empty($_POST['reg_ids'])) {
            global $wpdb;
            $ids = $_POST['reg_ids'];
            $arr = array();
            $out = '';

            $ids_imploded = implode(',', array_map('absint', $ids));

            $table_name = $wpdb->prefix . 'cong_attendance_data';

            $sql = " SELECT DISTINCT cad.reg_id, cad.member_id, cad.person_id, cad.scan_date, cad.scan_time FROM {$table_name} cad WHERE cad.reg_id IN($ids_imploded)";

            $res = $wpdb->get_results($sql);

            if (!empty($res)) {

                foreach ($res as $key => $item) {
                    $order_key = $key;
                    $sessions_and_social_events = $this->get_visited_from_person($item->reg_id, $item->person_id);

                    if (!empty($sessions_and_social_events)) {
                        foreach ($sessions_and_social_events as $key => $sessions_and_social_event) {

                            $first_name = $last_name = '';

                            if (isset($item->person_id) && !empty($item->person_id) && $item->person_id !== 0) {
                                $res_info = $this->get_info_about_guest_by_id($item->person_id);
                                $first_name = $res_info->first_name;
                                $last_name = $res_info->last_name;

                            } else {
                                $res_info = $this->get_reg_id_info($item->reg_id);
                                $first_name = $res_info->first_name;
                                $last_name = $res_info->last_name;
                            }

                            if (!empty($first_name)) {
                                $arr[$order_key]['first_name'] = $first_name;
                            }

                            if (!empty($last_name)) {
                                $arr[$order_key]['last_name'] = $last_name;
                            }


                            if (!empty($sessions_and_social_event)) {
                                foreach ($sessions_and_social_event as $s_key => $item_ev) {
                                    $ev_name = '';
                                    $order = $s_key + 1 . ') ';
                                    if ($key == 'social_event') {
                                        $ev_name = $this->get_social_event_by_year_and_id(date('Y'), $item_ev['session_id']);
                                        $arr[$order_key]['social_events'][$s_key]['name'] = $order . $ev_name;
                                    } else {
                                        $ev_name = $this->get_session_by_year_and_id(date('Y'), $item_ev['session_id']);
                                        $arr[$order_key]['sessions'][$s_key]['name'] = $order . $ev_name;
                                    }
                                }
                            }
                        }
                    }


                }

                echo json_encode(array('success' => true, 'data' => $arr));
                wp_die();
            }
            echo json_encode(array('success' => false));
            wp_die();
        }
        wp_die();
    }

    public function display_attendance_data()
    {

        $this->attendance_data = new Attendance_Data_Listing();
        $data_by_day = $this->get_attendance_data_days();

        ?>
        <div class="wrap attendance-data" id="badges">
            <h2>Attendance Data</h2>

            <?php if (!empty($data_by_day)): ?>
                <form class="download-pdf-by-date">
                    <select id="download_pdf_by_date" name="download_pdf_by_date" multiple="multiple">
                        <?php
                        foreach ($data_by_day as $key => $date) {
                            echo '<option value="' . $date . '">' . $date . '</option>';
                        }
                        ?>
                    </select>
                    <input type="submit" name="download_pdf_by_date" class="button" style="height:31px;"
                           value="download pdf">
                </form>
            <?php endif; ?>
            <div id="poststuff">
                <div id="post-body" class="metabox-holder">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post" class="submit-badges">
                                <?php
                                $this->common->search_box('Filter', 'search');
                                if (isset($_POST['s'])) {
                                    $search = $_POST['s'];
                                    $this->attendance_data->prepare_items($search);
                                } else {
                                    $this->attendance_data->prepare_items();
                                }
                                $this->attendance_data->display();
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
    }
}