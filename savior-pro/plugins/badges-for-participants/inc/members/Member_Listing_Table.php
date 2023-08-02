<?php

if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/screen.php' );
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Member_Listing_Table extends WP_List_Table
{
    public $common;

    /** Class constructor */
    public function __construct()
    {
        parent::__construct([
            'singular' => __('Member Registration', 'cong_reg'), //singular name of the listed records
            'plural' => __('Member Registrations', 'cong_reg'), //plural name of the listed records
            'ajax' => false //does this table support ajax?
        ]);
    }

    private function check_yes_no($in)
    {
        $keys = ['is_attendee', 'is_gas_flow', 'is_ceu', 'is_liquid', 'role_speaker', 'role_committee', 'role_board', 'role_exhibitor'];
        foreach ($keys as $val) {
            if (isset($in[$val])) {
                if (!in_array($in[$val], ['Yes', 'No'])) {
                    $in[$val] = 'No';
                }
            }
        }
        return $in;
    }

    private function translate_array($in)
    {
        $translation = [
            'Company Name' => 'Company',
            'Enter Email' => 'Email',
            'State / Province' => 'State'
        ];

        $out = [];
        foreach ($in as $key => $val) {
            if (is_array($val)) {
                $val = $this->translate_array($val);
            }
            if (isset($translation[$key])) {
                $out[$translation[$key]] = $val;
            } else {
                $out[$key] = $val;
            }
        }
        return $out;
    }

    private function save_participant($user)
    {


        $user = array_map('esc_sql', $user);

        global $wpdb;
        $sql = 'INSERT INTO ' . $wpdb->prefix . 'qr_bage_data(' . implode(',', array_keys($user)) . ') VALUES (\'' . implode('\',\'', array_values($user)) . '\')';
        $result = $wpdb->get_results($sql, 'ARRAY_A');
    }

    /**
     * Retrieve members data from the database
     *
     * @param int $per_page
     * @param int $page_number
     *
     * @return mixed
     */
    public function get_member_registartions($per_page = BADGES_MEMBERS_PER_PAGE, $page_number = 1, $year = 0)
    {
        global $wpdb;

        $sql = 'SELECT t_ps.id,t_psm.meta_value FROM ' . $wpdb->prefix . 'posts as t_ps 
        INNER JOIN ' . $wpdb->prefix . 'postmeta as t_psm ON t_ps.id=t_psm.post_id AND t_psm.meta_key=\'woocommerce_order_data\'
        WHERE t_ps.post_type=\'shop_order\' AND t_ps.id NOT IN (SELECT order_id FROM ' . $wpdb->prefix . 'qr_bage_data) AND (t_ps.post_status <> \'wc-failed\' AND t_ps.post_status <> \'wc-pending\')';

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        foreach ($result as $rec) {

            $parse = parse_woocommerce_order_data($rec['meta_value']);
            if (is_array($parse) && !empty($parse)) {

                foreach ($parse as $key => $item) {
                    $item = $this->translate_array($item);

                    if (is_array($item['Email'])) {
                        $item['Email'] = $item['Email']['Email'];
                        unset($item['email']);
                    }

                    if ($key === 'additional') {
                        $order_id = (!isset($item['Order number']) ? $this->checkEmailForAdditionalProduct($item['Email']) : $item['Order number']);
                        if (!empty($order_id)) {
                            $update_data = array();
                            $need_visitor_type = 'Student';

                            $order_sql = 'SELECT * FROM ' . $wpdb->prefix . 'qr_bage_data WHERE order_id=' . $order_id . ' AND email="' . $item['Email'] . '" AND visitor_type="' . $need_visitor_type . '"';
                            $order_object = $wpdb->get_row($order_sql, 'ARRAY_A');

                            if ($order_object) {
                                $order_object_data = json_decode($order_object['data']);
                                $item['Order number'] = $order_id;

								$has_ceu = isset($item[ASGMT_PRODUCT_SKU_CEU_LABEL]);
								$has_gas = isset($item[ASGMT_PRODUCT_SKU_GMF_PERSON_LABEL]) || isset($item[ASGMT_PRODUCT_SKU_GMF_VIRTUAL_LABEL]);
	                            $has_liquid = isset($item[ASGMT_PRODUCT_SKU_LMF_PERSON_LABEL]) || isset($item[ASGMT_PRODUCT_SKU_LMF_VIRTUAL_LABEL]);


//                                if (isset($item['Mailing Address for GFMF']) or isset($item['Gas Flow Measurement Fundamentals (CEU Included +$38)'])) {
//                                    $update_data['is_gas_flow'] = 'Yes';
//                                    $order_object_data->is_gas_flow = 'Yes';
//                                }
//
//                                if (isset($item['Mailing Address for CEU']) or isset($item['CEU'])) {
//                                    $update_data['is_ceu'] = 'Yes';
//                                    $order_object_data->is_ceu = 'Yes';
//                                }
//
//                                if (isset($item['Mailing Address for Liquid Course']) or isset($item['Liquid Course  (CEU Included +$38)'])) {
//                                    $update_data['is_liquid'] = 'Yes';
//                                    $order_object_data->is_liquid = 'Yes';
//                                }

	                            if ($has_ceu) {
                                    $update_data['is_ceu'] = 'Yes';
                                    $order_object_data->is_ceu = 'Yes';
	                            }

	                            if ($has_gas) {
		                            $update_data['is_gas_flow'] = 'Yes';
		                            $order_object_data->is_gas_flow = 'Yes';
	                            }

	                            if ($has_liquid) {
		                            $update_data['is_liquid'] = 'Yes';
		                            $order_object_data->is_liquid = 'Yes';
	                            }


                                $update_data['data'] = json_encode($order_object_data);

                                if (!empty($update_data)) {
                                    $wpdb->update($wpdb->prefix . 'qr_bage_data', $update_data, array(
                                        'order_id' => $order_id,
                                        'email' => $item['Email'],
                                        'visitor_type' => $need_visitor_type
                                    ));
                                }
                            }
                        }
                    }

                    $user = [
                        'order_id' => $rec['id'],
                        'parent_order_id' => !empty($item['Order number']) ? (int)$item['Order number'] : 0,
                        'is_additional' => !empty($item['Order number']) ? 1 : 0,
                        'date_added' => date('Y-m-d H:s'),
                        'name' => $item['Name']['Last'] . ' ' . $item['Name']['First'],
                        'visitor_type' => ($item['ASGMT Day Pass Registration 2021']['Name'] === 'ASGMT Day Pass Registration 2021' ? 'Day Pass' : ($key === 'data' ? 'Vendor' : 'Student')),
                        'company' => isset($item['Company']) ? $item['Company'] : '',
                        'email' => isset($item['Email']) ? strtolower($item['Email']) : '',
                        'is_attendee' => (isset($item['Personal Mailing Address']) && 'additional' !== $key) ? 'Yes' : 'No',
                        'is_gas_flow' => $has_gas ? 'Yes' : 'No',
                        'is_ceu' => $has_ceu ? 'Yes' : 'No',
                        'is_liquid' => $has_liquid ? 'Yes' : 'No',
                        'is_qr_print' => !empty($item['Order number']) ? 'No' : 'Yes',
                        'role_speaker' => isset($item['Special Role']['Are you a speaker at ASGMT?']) ? mb_convert_case($item['Special Role']['Are you a speaker at ASGMT?'], MB_CASE_TITLE) : 'No',
                        'role_committee' => isset($item['Special Role']['Are you a Committee Member?']) ? mb_convert_case($item['Special Role']['Are you a Committee Member?'], MB_CASE_TITLE) : 'No',
                        'role_board' => isset($item['Special Role']['Are you a Board Member?']) ? mb_convert_case($item['Special Role']['Are you a Board Member?'], MB_CASE_TITLE) : 'No',
                        'role_exhibitor' => isset($item['Special Role']['Are you an Exhibitor?']) ? mb_convert_case($item['Special Role']['Are you an Exhibitor?'], MB_CASE_TITLE) : 'No',
                        'first_name' => (isset($item['Name']) ? $item['Name']['First'] : ''),
                        'last_name' => (isset($item['Name']) ? $item['Name']['Last'] : ''),
                        'original_first_name' => (isset($item['Name']) ? $item['Name']['First'] : ''),
                        'original_last_name' => (isset($item['Name']) ? $item['Name']['Last'] : ''),
                    ];

                    $user = $this->check_yes_no($user);
                    $user['data'] = json_encode($user + [
                            'first_name' => $item['Name']['First'],
                            'last_name' => $item['Name']['Last'],
                            'friendly_name' => $item['Friendly Name'],
                            'job' => isset($item['Title']) ? $item['Title'] : '',
                            'state' => !empty($item['State/Province/Region']) ? $item['State/Province/Region'] : (isset($item['State']) ? $item['State'] : ''),
                            'city' => isset($item['City']) ? $item['City'] : '',
                            'country' => isset($item['Country']) ? $item['Country'] : '',
                            'phone_daytime' => isset($item['Daytime Phone']) ? $item['Daytime Phone'] : '',
                            'phone_cell' => isset($item['Cell Phone']) ? $item['Cell Phone'] : '',
                            'addr_zip' => isset($item['Personal Mailing Address']['ZIP / Postal Code']) ? $item['Personal Mailing Address']['ZIP / Postal Code'] : '',
                            'addr_country' => isset($item['Personal Mailing Address']['Country']) ? $item['Personal Mailing Address']['Country'] : '',
                            'addr_state' => isset($item['Personal Mailing Address']['State']) ? $item['Personal Mailing Address']['State'] : '',
                            'addr_city' => isset($item['Personal Mailing Address']['City']) ? $item['Personal Mailing Address']['City'] : '',
                            'addr_addr_1' => isset($item['Personal Mailing Address']['Street Address']) ? $item['Personal Mailing Address']['Street Address'] : '',
                            'addr_addr_2' => isset($item['Personal Mailing Address']['Address Line 2']) ? $item['Personal Mailing Address']['Address Line 2'] : '',
                        ], JSON_UNESCAPED_UNICODE);

                    $check_day_pass = $item['ASGMT Day Pass Registration 2021']['Name'] === 'ASGMT Day Pass Registration 2021';

//                    $authorize_payment_ok = get_post_meta($rec['id'], 'authorize_payment_ok', true);

                    $order = wc_get_order( $rec['id'] );

//                    if ($authorize_payment_ok == 'yes' || $check_day_pass || sizeof($order->get_used_coupons()) > 0) {
//                        $this->save_participant($user);
//                    }

                    $this->save_participant($user);



                }
            }
        }

        $sql = 'SELECT * FROM ' . $wpdb->prefix . 'qr_bage_data bd WHERE 1=1';
        if (isset($_REQUEST['s']) && $_REQUEST['s'] != NULL) {
            $search = trim($_REQUEST['s']);
            $search_int = (int)$search;
            $sql .= " AND (name LIKE '%{$search}%' OR visitor_type LIKE '%{$search}%' OR email LIKE '%{$search}%' OR company LIKE '%{$search}%' OR id = {$search_int}  OR order_id = {$search_int}) ";
        }
        if (isset($_REQUEST['y'])) {
            $y = (int)$_REQUEST['y'];
        } else {
            $y = date('Y');
        }
        $sql .= ' AND (date_added>=\'' . $y . '-01-01 00:00:00\' AND date_added<\'' . ($y + 1) . '-01-01 00:00:00\')';
        if (!empty($_REQUEST['orderby'])) {
            $order_by = $_REQUEST['orderby'];
            switch ($order_by) {
                case 'name':
                    $order_by = 'name';
                    break;
                case 'first_name':
                    $order_by = 'first_name';
                    break;
                case 'last_name':
                    $order_by = 'last_name';
                    break;
                case 'visitor_type':
                    $order_by = 'visitor_type';
                    break;
                case 'order_id':
                    $order_by = 'order_id';
                    break;
            }
            $sql .= ' ORDER BY ' . esc_sql($order_by);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        } else {
            $sql .= ' ORDER BY id DESC';
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return stripslashes_deep($result);
    }

    /**
     * Returns the count of records in the database.
     *
     * @return null|string
     */
    public function record_count($year = 0)
    {
        global $wpdb;

        $sql = 'SELECT count(*) as total FROM ' . $wpdb->prefix . 'qr_bage_data';

        $y = isset($_REQUEST['y']) ?  (int)$_REQUEST['y'] : date('Y');

        $sql .= " WHERE (date_added >= '".$y."-01-01 00:00:00' AND date_added<'" . ($y + 1) . "-01-01 00:00:00') ";

        if (isset($_REQUEST['s']) && $_REQUEST['s'] != NULL) {
            $search = trim($_REQUEST['s']);
            $search_int = (int)$search;
            $sql .= " AND (name LIKE '%{$search}%') OR visitor_type LIKE '%{$search}%' OR id = {$search_int}  OR order_id = {$search_int}) ";
        }

        return $wpdb->get_var($sql);
    }

    /**
     * Text displayed when no panel sessions categories data is available
     */
    public function no_items()
    {
        _e('No member registration found.', 'cong_reg');
    }

    /**
     * Render a column when no column specific method exist.
     *
     * @param array $item
     * @param string $column_name
     *
     * @return mixed
     */
    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'id':
                return '<span class="reg-id" reg_id="' . $item['id'] . '">' . $item['id'] . '</span>';
            case 'order_id':
                return $item['order_id'];
            case 'parent_order_id':
                return $item['parent_order_id'];
//            case 'name':
//                return $item['name'];
            case 'first_name':
                return $item['first_name'];
            case 'last_name':
                return $item['last_name'];
            case 'company':
                return $item['company'];
            case 'email':
                return $item['email'];
            case 'is_attendee':
                return $item['is_attendee'];
            case 'is_gas_flow':
                return $item['is_gas_flow'];
            case 'is_ceu':
                return $item['is_ceu'];
            case 'is_liquid':
                return $item['is_liquid'];
            case 'is_printed':
                return $item['is_printed'];
            case 'is_checked':
                return $item['is_checked'];
            case 'visitor_type':
                return $item['visitor_type'];
            case 'is_qr_print':
                return $item['is_qr_print'];
            case 'is_additional':
                return $item['is_additional'];
            case 'action':
                return $item['action'];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    function column_first_name($item)
    {
        global $wpdb;

        if (!isset($item['first_name']) || !isset($item['last_name'])) {
            if (isset($item['name'])) {
                $data = json_decode($item['data']);

                $first_name = (!empty($data->first_name) ? $data->first_name : '');
                $last_name = (!empty($data->last_name) ? $data->last_name : '');
                $id = (int)$item['id'];

                $sql = $wpdb->prepare("UPDATE {$wpdb->prefix}qr_bage_data SET first_name='%s', last_name='%s' WHERE id=%d", $first_name, $last_name, $id);

                $res = $wpdb->query($sql);
            }
        } else {
            $first_name = $item['first_name'];
        }

        $show_original_name = get_option('show_original_name');

        if ($show_original_name == true && !empty($item['original_first_name'])) {
            $html = '<div class="original-field-name"><span>Original:</span><span>'.$item['original_first_name'].'</span></div>';

            $first_name .=  $html;
        }


        return $first_name;
    }

    function column_last_name($item)
    {
        if (!isset($item['last_name'])) {
            $data = json_decode($item['data']);
            $last_name = (!empty($data->last_name) ? $data->last_name : '');
        } else {
            $last_name = $item['last_name'];
        }

        $show_original_name = get_option('show_original_name');

        if ($show_original_name == true && !empty($item['original_last_name'])) {
            $html = '<div class="original-field-name"><span>Original:</span><span>'.$item['original_last_name'].'</span></div>';

            $last_name .=  $html;
        }


        return $last_name;
    }

    function column_is_attendee($item)
    {
        $status = $item['is_attendee'] == 'Yes' ? '<span class="color_blue">Yes</span>' : '<span class="color_yellow">No</span>';
        return $status;
    }

    function column_is_qr_print($item)
    {
        $status = $item['is_qr_print'] == 'Yes' ? '<span class="color_blue">Yes</span>' : '<span class="color_yellow">No</span>';
        return $status;
    }

    function column_is_gas_flow($item)
    {
        $status = $item['is_gas_flow'] == 'Yes' ? '<span class="color_blue">Yes</span>' : '<span class="color_yellow">No</span>';
        return $status;
    }

    function column_is_ceu($item)
    {
        $status = $item['is_ceu'] == 'Yes' ? '<span class="color_blue">Yes</span>' : '<span class="color_yellow">No</span>';
        return $status;
    }

    function column_is_liquid($item)
    {
        $status = $item['is_liquid'] == 'Yes' ? '<span class="color_blue">Yes</span>' : '<span class="color_yellow">No</span>';
        return $status;
    }

    function column_is_additional($item)
    {
        $status = $item['is_additional'] == 1 ? '<span class="color_blue">Yes</span>' : '<span class="color_yellow">No</span>';
        return $status;
    }

    function column_is_checked($item)
    {
        $status = (int)$item['is_checked'] > 0 ? '<span>Yes</span>' : '<span>No</span>';
        $res = $status . '<div class="button action_check_in">Checked</div>';
        return $res;
    }

    function column_is_printed($item)
    {
        $status = $item['is_printed'] > 0 ? '<span>Yes</span>' : '<span>No</span>';
        $res = $status . '<div class="button action_check_in">Printed</div>';
        return $res;
    }

    function column_cb($item)
    {
        if ($item['is_printed'] == '1') {
            $out = '<div class="red_box" data-id="' . $item['id'] . '" title="Is not student"></div>';
        } else if ($item['visitor_type'] == 'Student' && $item['is_additional'] != 1) {
            $out = sprintf(
                '<input id="bulk-print-' . $item['id'] . '" type="checkbox" class="id_input" name="bulk-print[' . $item['id'] . ']" value="%s" />', $item['id']
            );
        } else if ($item['visitor_type'] == 'Vendor') {
            $out = '<div class="orange_box" title="Is not student"></div>';
        } else {
            $out = '<div class="red_box" title="Is not student"></div>';
        }
        return $out;
    }

    /*
        public function column_printed($item)
        {
            $form = '';
            $form .= '<div class="printed disabled-edit-field">';
            $form .= '<input class="prtd_count disabled-enabled-field" type="number" min="0" value="' . (isset($item['printed_count']) && !empty($item['printed_count']) ? $item['printed_count'] : '0') . '" />';
            $form .= '<input class="prtd_from_user_id" type="hidden" value="' . $item['user_id'] . '" />';
            $form .= '<input class="prtd_congress_year" type="hidden" value="' . $item['congress_year'] . '" />';
            $form .= '<input class="prtd_wp_item_id" type="hidden" value="' . $item['id'] . '" />';
            $form .= '<input class="prtd_cong_reg_id" type="hidden" value="' . (isset($item['cong_reg_id']) ? $item['cong_reg_id'] : '') . '" />';
            $form .= '<a class="button action go_change_printed_count icon-container toggle-action-save-edit" ><span class="mini-icon save"></span></a>';
            $form .= '</div>';
            return $form;
        }*/
    public function column_action($item)
    {
        $form = '';
        if ($item['is_attendee'] == 'Yes' || $item['visitor_type'] == 'Vendor') {
            $form .= '<a class="button" href="' . admin_url('admin.php?page=print_badges&ids=' . $item['id']) . '">Print</a>';
        }
        $form .= '<a class="button" href="' . admin_url('admin.php?page=member_page&user_id=' . $item['id']) . '&congress_year=' . $item['congress_year'] . '&back_url=' . urlencode($_SERVER['REQUEST_URI'] . '#bulk-print-' . $item['id']) . '">Details</a>';

        if (current_user_can('administrator')) {
            $form .= '<a class="button button-primary button-link-delete" onClick="return confirm(\'Are you sure you want to delete badges related to order #' . $item['order_id'] . '? This action cannot be undone.\')" href="' . admin_url('admin.php?page=delete_badges&delid=' . $item['order_id']) . '">Delete</a>';
        }

        return $form;
    }

    /**
     * Render status column
     *
     * @param array $item
     *
     * @return string
     */
    /*  function column_total($item)
      {
          $return = $this->common->format_currency($item['total'], $this->common->currency_by_year[$item['congress_year']]);
          if (count($item['orders'])) {
              foreach ($item['orders'] as $order) {
                  $return .= '<br /><a href="/wp-admin/post.php?post=' . $order['order_id'] . '&action=edit" target="_blank">Order#' . $order['order_id'] . '</a>';
              }
          }
          return $return;
      }*/

    public function get_country_by_id($id)
    {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}countries WHERE country_code = '" . esc_sql($id) . "'";
        $result = $wpdb->get_results($sql, 'ARRAY_A'); // ARRAY_A -> return an associative array
        return stripslashes_deep($result[0]['country']);
    }

    /**
     *  Associative array of columns
     *
     * @return array
     */
    function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'id' => __('Reg ID', 'id'),
            'order_id' => __('Order ID', 'order_id'),
            'parent_order_id' => __('Parent Order ID', 'parent_order_id'),
            'is_additional' => __('Additional', 'is_additional'),
//            'name' => __('Name', 'name'),
            'first_name' => __('First Name', 'first_name'),
            'last_name' => __('Last Name', 'last_name'),
            'company' => __('Company', 'company'),
            'email' => __('Email', 'email'),
            'is_attendee' => __('Attendee', 'is_attendee'),
            'is_gas_flow' => __('GasFlow', 'is_gas_flow'),
            'is_ceu' => __('CEU', 'is_ceu'),
            'is_liquid' => __('Liquid', 'is_liquid'),
            'visitor_type' => __('Visitor Type', 'visitor_type'),
            'is_qr_print' => __('Print QR', 'is_qr_print'),
            'is_printed' => __('Printed', 'is_printed'),
            'is_checked' => __('Checked', 'is_checked'),
            'action' => __('Action', 'action'),
        ];
        return $columns;
    }

    /**
     * Columns to make sortable.
     *
     * @return array
     */
    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'id' => array('id', true),
            'order_id' => array('order_id', true),
            'name' => array('name', true),
            'first_name' => array('first_name', true),
            'last_name' => array('last_name', true),
            'visitor_type' => array('visitor_type', true)
        );
        return $sortable_columns;
    }

    public function resend_reg_notif($reg_id)
    {
        if (empty($reg_id)) return;
        global $congressRegForm;
        $reg = $congressRegForm->get_registration_by_id($reg_id);
        if (!$reg) {
            echo json_encode(array('success' => false, 'message' => 'The registration ID is not valid.'));
            wp_die();
        }
        $congressRegForm->user_id = $reg['user_id'];
        $congressRegForm->send_conf_email(array('congress_year' => $reg['congress_year']), array(), true);
        return true;
    }

    /**
     * Returns an associative array containing the bulk action
     *
     * @return array
     */
    public function get_bulk_actions()
    {
        $actions = [
            'bulk-print' => 'Print Badges'
        ];
        return $actions;
    }

    /**
     * Handles data query and filter, sorting, and pagination.
     */
    public function prepare_items()
    {
        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        /** Process bulk action */
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page('members_per_page', BADGES_MEMBERS_PER_PAGE);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count(date('Y'));
        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page' => $per_page //WE have to determine how many items to show on a page
        ]);
        $this->items = $this->get_member_registartions($per_page, $current_page, date('Y'));
    }

    public function print_column_headers($with_id = true)
    {
        list($columns, $hidden, $sortable) = $this->get_column_info();
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg('paged', $current_url);
        if (isset($_GET['orderby']))
            $current_orderby = $_GET['orderby'];
        else
            $current_orderby = '';
        if (isset($_GET['order']) && 'desc' == $_GET['order'])
            $current_order = 'desc';
        else
            $current_order = 'asc';
        if (!empty($columns['cb'])) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __('Select All') . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
            $cb_counter++;
        }
        foreach ($columns as $column_key => $column_display_name) {
            $class = array('manage-column', "column-$column_key");
            $style = '';
            if (in_array($column_key, $hidden))
                $style = 'display:none;';
            $style = ' style="' . $style . '"';
            if ('cb' == $column_key)
                $class[] = 'check-column';
            elseif (in_array($column_key, array('posts', 'comments', 'links')))
                $class[] = 'num';
            if (isset($sortable[$column_key])) {
                list($orderby, $desc_first) = $sortable[$column_key];
                if ($current_orderby == $orderby) {
                    $order = 'asc' == $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'asc' : 'desc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'desc' : 'asc';
                }
                $column_display_name = '<a href="' . esc_url(add_query_arg(compact('orderby', 'order'), $current_url)) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }
            $id = $with_id ? "id='$column_key'" : '';
            if (!empty($class))
                $class = "class='" . join(' ', $class) . "'";
            echo "<th scope='col' $id $class $style>$column_display_name</th>";
        }
    }

    public function process_bulk_action()
    {
        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {
            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];
            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');
        }

        if ('bulk-print' === $this->current_action()) {
            $items = array();
            if (isset($_POST['bulk-print']) && !empty($_POST['bulk-print'])) {
                if (is_array($_POST['bulk-print'])) {
                    foreach ($_POST['bulk-print'] as $val) {
                        $items[] = $val;
                    }
                }
            }

            if (count($items) > 0) {
                $url = 'admin.php?page=print_badges&ids=' . implode(',', $items);

                if (isset($_REQUEST['orderby'])) {
                    $order_by = $_REQUEST['orderby'];
                    $url .= '&orderby=' . $order_by;
                }

                if (isset($_REQUEST['order'])) {
                    $order = $_REQUEST['order'];
                    $url .= '&order=' . $order;
                }

                wp_redirect(admin_url($url));
            }
        }
    }

    protected function pagination($which)
    {
        if (empty($this->_pagination_args)) {
            return;
        }
        $s = '';
        $total_items = $this->_pagination_args['total_items'];
        $total_pages = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if (isset($this->_pagination_args['infinite_scroll'])) {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }
        $output = '<span class="displaying-num">' . sprintf(_n('1 item', '%s items', $total_items), number_format_i18n($total_items)) . '</span>';
        $current = $this->get_pagenum();
        $current_url = set_url_scheme('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        $current_url = remove_query_arg(array('hotkeys_highlight_last', 'hotkeys_highlight_first'), $current_url);
        if (!empty($_REQUEST['s'])) {
            $s = $_REQUEST['s'];
            $current_url = add_query_arg('s', $s, $current_url);
        }
        $page_links = array();
        $disable_first = $disable_last = '';
        if ($current == 1) {
            $disable_first = ' disabled';
        }
        if ($current == $total_pages) {
            $disable_last = ' disabled';
        }
        $page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
            'first-page' . $disable_first,
            esc_attr__('Go to the first page'),
            esc_url(remove_query_arg('paged', $current_url)),
            '&laquo;'
        );
        $page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
            'prev-page' . $disable_first,
            esc_attr__('Go to the previous page'),
            esc_url(add_query_arg('paged', max(1, $current - 1), $current_url)),
            '&lsaquo;'
        );
        if ('bottom' == $which) {
            $html_current_page = $current;
        } else {
            $html_current_page = sprintf("%s<input class='current-page' id='current-page-selector' title='%s' type='text' name='paged' value='%s' size='%d' />",
                '<label for="current-page-selector" class="screen-reader-text">' . __('Select Page') . '</label>',
                esc_attr__('Current page'),
                $current,
                strlen($total_pages)
            );
        }
        $html_total_pages = sprintf("<span class='total-pages'>%s</span>", number_format_i18n($total_pages));
        $page_links[] = '<span class="paging-input">' . sprintf(_x('%1$s of %2$s', 'paging'), $html_current_page, $html_total_pages) . '</span>';
        $page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
            'next-page' . $disable_last,
            esc_attr__('Go to the next page'),
            esc_url(add_query_arg('paged', min($total_pages, $current + 1), $current_url)),
            '&rsaquo;'
        );
        $page_links[] = sprintf("<a class='%s' title='%s' href='%s'>%s</a>",
            'last-page' . $disable_last,
            esc_attr__('Go to the last page'),
            esc_url(add_query_arg('paged', $total_pages, $current_url)),
            '&raquo;'
        );
        $pagination_links_class = 'pagination-links';
        if (!empty($infinite_scroll)) {
            $pagination_links_class = ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join("\n", $page_links) . '</span>';
        if ($total_pages) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";
        echo $this->_pagination;
    }

    public function checkEmailForAdditionalProduct($email)
    {
        global $wpdb;
        $order_status = array('wc-completed', 'wc-processing', 'wc-on-hold');
        $year = date('Y');
        $valid_email = array();

        $q = "
              SELECT order_items.order_id, pm1.meta_value as woocommerce_order_data
              FROM {$wpdb->prefix}woocommerce_order_items as order_items
              LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
              LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
              LEFT OUTER JOIN {$wpdb->postmeta} AS pm1 ON (pm1.post_id = order_items.order_id AND pm1.meta_key = 'woocommerce_order_data')
              WHERE posts.post_type = 'shop_order'
              AND posts.post_status IN ( '" . implode("','", $order_status) . "' )
              AND YEAR(posts.post_date) = '$year'
              AND order_items.order_item_type = 'line_item'
              AND order_item_meta.meta_key = '_product_id'
              GROUP BY order_items.order_id, pm1.meta_value
              ORDER BY order_items.order_id DESC
          ";

        $results = $wpdb->get_results($q, 'ARRAY_A');

        $orders = array();

        if (!empty($results)) {
            foreach ($results as $k => $reg) {
                $orders[$k]['woocommerce_order_data'] = parse_woocommerce_order_data($reg['woocommerce_order_data']);
                if (!empty($orders[$k]['woocommerce_order_data'])) {
                    foreach ($orders[$k]['woocommerce_order_data'] as $key => $order_data) {
                        if ($key !== 'data' && $key !== 'additional') {
                            if ($order_data['Enter Email'] == $email) {
                                $result = $reg['order_id'];
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function get_hidden_columns()
    {
        return array();
    }
}
