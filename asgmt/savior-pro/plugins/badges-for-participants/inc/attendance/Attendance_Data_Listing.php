<?php

class Attendance_Data_Listing extends WP_List_Table
{
    public $common;
    public $table_name;

    public function __construct()
    {
        if (!isset($_GET['session_id'])) return;

        parent::__construct([
            'singular' => __('Attendance Data', 'cong_reg'),
            'plural' => __('Attendances Data', 'cong_reg'),
            'ajax' => false
        ]);
        // $this->common = new Common();
        $this->set_table_name();
    }

    public function set_table_name()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'cong_attendance_data';
    }

    public function get_columns()
    {
        $columns = [
            'cb' => '<input type="checkbox" />',
            'first_name' => __('First Name', 'first_name'),
            'last_name' => __('Last Name', 'last_name'),
            'reg_id' => __('Reg ID', 'reg_id'),
            'member_id' => __('Member ID', 'member_id'),
            'phone' => __('Phone', 'phone'),
            'email' => __('Email', 'email'),
            'date' => __('Check in date', 'date'),
            'time' => __('Check in time', 'time'),
            'visited' => __('Visited', 'visited')
        ];

        return $columns;
    }

    public function get_sortable_columns()
    {
        $sortable_columns = array(
            'first_name' => array('first_name', true),
            'last_name' => array('last_name', true),
            'reg_id' => array('reg_id', true),
            'phone' => array('phone', true),
            'email' => array('email', true),
            'member_id' => array('member_id', true),
            'date' => array('scan_date', true),
            'time' => array('scan_time', true)
        );

        return $sortable_columns;
    }


    public function column_cb($item)
    {
        $out = '';

        $out .= sprintf(
            '<input type="checkbox" name="bulk-export-attendance[]" value="%s" />', $item['reg_id']
        );

        $out .= sprintf(
            '<input type="hidden" name="bulk-delete[]" value="%s" />', $item['id']
        );

        return $out;
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

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'reg_id':
                return $item['reg_id'];
            case 'member_id':
                return $item['member_id'];
            case 'date':
            case 'time':
            case 'first_name':
            case 'last_name':
            case 'visited':
            case 'phone':
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    public function column_date($item) {
        if (!empty($item['acc_check_in_date'])) {
            $check_in_date = $item['acc_check_in_date'];
        } else {
            $check_in_date = $item['scan_date'];
        }

        if (empty($check_in_date)) {
            $check_in_date = 'not checked';
        }

        return $check_in_date;
    }

    public function column_time($item) {
        if (!empty($item['acc_check_in_time'])) {
            $check_in_time = $item['acc_check_in_time'];
        } else {
            $check_in_time = $item['scan_time'];
        }

        if (empty($check_in_time)) {
            $check_in_time = 'not checked';
        }

        return $check_in_time;
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

    public function column_visited($item)
    {
        $sessions_and_social_events = $this->get_visited_from_person($item['reg_id'], $item['person_id']);
        $out = '';

        if (!empty($sessions_and_social_events)) {
            foreach ($sessions_and_social_events as $key => $sessions_and_social_event) {
                $out .= '<div class="item-type-ev">';
                $out .= '<h3 style="padding:0;">' . ($key == 'social_event' ? 'Social Events:<br/>' : 'Sesssions:<br/>') . '</h3>';
                if (!empty($sessions_and_social_event)) {
                    foreach ($sessions_and_social_event as $s_key => $item_ev) {
                        $ev_name = '';
                        if ($key == 'social_event') {
                            $ev_name = $this->get_social_event_by_year_and_id(date('Y'), $item_ev['session_id']);
                        } else {
                            $ev_name = $this->get_session_by_year_and_id(date('Y'), $item_ev['session_id']);

                        }

                        $order = $s_key + 1 . ') ';

                        $out .= '<div class="item">' . $order . $ev_name . '</div>';
                    }
                }
                $out .= '</div>';
            }
        }
        return $out;
    }

    public function get_reg_id_info($reg_id)
    {
        global $congressRegForm;

        $selected_user = $congressRegForm->get_user_id_for_reg_id($reg_id, false);
        $congress_year = $congressRegForm->get_congress_year_by_reg($reg_id, false);

        $reg = $congressRegForm->get_registration_by_user($selected_user, $congress_year);

        return $reg;
    }

    public function get_info_about_guest_by_id($id)
    {
        global $wpdb;

        $sql = " SELECT * FROM {$wpdb->prefix}cong_regs_persons WHERE id={$id} ";

        $res = $wpdb->get_row($sql);

        return $res;
    }

    public function column_first_name($item)
    {

        if (isset($item['acc_first_name'])) {
            $first_name = $item['acc_first_name'];
        } else {
            $first_name = $item['first_name'];
        }

        return $first_name;
    }

    public function column_last_name($item)
    {
        if (isset($item['acc_last_name'])) {
            $last_name = $item['acc_last_name'];
        } else {
            $last_name = $item['last_name'];
        }
        return $last_name;
    }

    public function column_phone($item)
    {
        return $item['phone'];
    }

    public function column_email($item)
    {
        return $item['email'];
    }

    public function no_items()
    {
        _e('No found Attendance Data.', 'cong_reg');
    }

    public function record_count()
    {
        global $wpdb;
        $sql = "";

        $table_name = $wpdb->prefix . 'cong_attendance_data';

        $sql = " SELECT COUNT(*) FROM( SELECT DISTINCT cad1.id as id, cr.first_name, cr.last_name, um.meta_value AS phone, u.user_email AS email, cad.reg_id, cad.member_id, cad.person_id, cr.check_in_date as scan_date, cr.check_in_time as scan_time, GROUP_CONCAT(ps.name SEPARATOR '[|]') AS sessions, GROUP_CONCAT(soc.name SEPARATOR '[|]') AS social_events, ";

        $sql .= " rp.first_name as acc_first_name, rp.last_name as acc_last_name, rp.check_in_date as acc_check_in_date, rp.check_in_time as acc_check_in_time ";

        $sql .= " FROM wp_cong_attendance_data cad ";

        $sql .= " LEFT JOIN wp_cong_registrations cr ON cr.id = cad.reg_id ";

        $sql .= " LEFT JOIN wp_usermeta um ON cr.user_id = um.user_id AND um.meta_key = 'phone' ";

        $sql .= " LEFT JOIN wp_users u ON cr.user_id = u.ID ";

        $sql .= " LEFT JOIN wp_cong_panel_sessions ps ON ps.id = cad.session_id AND cad.type!='social_event' ";

        $sql .= " LEFT JOIN wp_cong_social_events soc ON soc.id = cad.session_id AND cad.type='social_event' ";

        $sql .= " LEFT JOIN wp_cong_attendance_data cad1 ON cad1.reg_id = cad.reg_id ";

        $sql .= " LEFT JOIN wp_cong_regs_persons rp ON cad.person_id = rp.id ";

        $sql .= " WHERE 1 ";


        if (isset($_REQUEST['s']) && $_REQUEST['s'] != NULL) {

            $search = $_REQUEST['s'];

            $search = trim($search);


            $sql .= " AND ( cr.first_name LIKE '%{$search}%' OR cr.last_name LIKE '%{$search}%' OR um.meta_value LIKE '%{$search}%' OR u.user_email LIKE '%{$search}%' OR cad.reg_id LIKE '%{$search}%' OR cad.member_id LIKE '%{$search}%' OR cr.check_in_date LIKE '%{$search}%' OR cr.check_in_time LIKE '%{$search}%' OR ps.name LIKE '%{$search}%' OR ps.name LIKE '%{$search}%') ";
        }

        $sql .= " GROUP BY cr.id, cr.first_name, cr.last_name, phone, email, cad.reg_id, cad.member_id, cad.person_id, cr.check_in_date, cr.check_in_time ";

        if (!empty($_REQUEST['orderby'])) {

            $order_by = $_REQUEST['orderby'];

            $sql .= ' ORDER BY ' . esc_sql($order_by);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " ) as s";


        return $wpdb->get_var($sql);
    }

    public function get_attendances($per_page = BADGES_MEMBERS_PER_PAGE, $page_number = 1)
    {
        global $wpdb;

        $sql = " SELECT DISTINCT cad1.id as id, cr.first_name, cr.last_name, um.meta_value AS phone, u.user_email AS email, cad.reg_id, cad.member_id, cad.person_id, cr.check_in_date as scan_date, cr.check_in_time as scan_time, GROUP_CONCAT(ps.name SEPARATOR '[|]') AS sessions, GROUP_CONCAT(soc.name SEPARATOR '[|]') AS social_events, ";

        $sql .= " rp.first_name as acc_first_name, rp.last_name as acc_last_name, rp.check_in_date as acc_check_in_date, rp.check_in_time as acc_check_in_time ";

        $sql .= " FROM wp_cong_attendance_data cad ";

        $sql .= " LEFT JOIN wp_cong_registrations cr ON cr.id = cad.reg_id ";

        $sql .= " LEFT JOIN wp_usermeta um ON cr.user_id = um.user_id AND um.meta_key = 'phone' ";

        $sql .= " LEFT JOIN wp_users u ON cr.user_id = u.ID ";

        $sql .= " LEFT JOIN wp_cong_panel_sessions ps ON ps.id = cad.session_id AND cad.type!='social_event' ";

        $sql .= " LEFT JOIN wp_cong_social_events soc ON soc.id = cad.session_id AND cad.type='social_event' ";

        $sql .= " LEFT JOIN wp_cong_attendance_data cad1 ON cad1.reg_id = cad.reg_id ";

        $sql .= " LEFT JOIN wp_cong_regs_persons rp ON cad.person_id = rp.id ";

        $sql .= " WHERE 1 ";


        if (isset($_REQUEST['s']) && $_REQUEST['s'] != NULL) {

            $search = $_REQUEST['s'];

            $search = trim($search);


            $sql .= " AND ( cr.first_name LIKE '%{$search}%' OR cr.last_name LIKE '%{$search}%' OR um.meta_value LIKE '%{$search}%' OR u.user_email LIKE '%{$search}%' OR cad.reg_id LIKE '%{$search}%' OR cad.member_id LIKE '%{$search}%' OR cr.check_in_date LIKE '%{$search}%' OR cr.check_in_time LIKE '%{$search}%' OR ps.name LIKE '%{$search}%' OR ps.name LIKE '%{$search}%') ";
        }

        $sql .= " GROUP BY cr.id, cr.first_name, cr.last_name, phone, email, cad.reg_id, cad.member_id, cad.person_id, cr.check_in_date, cr.check_in_time ";

        if (!empty($_REQUEST['orderby'])) {

            $order_by = $_REQUEST['orderby'];

            $sql .= ' ORDER BY ' . esc_sql($order_by);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return stripslashes_deep($result);
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function prepare_items()
    {

        $columns = $this->get_columns();
        $hidden = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);

        $this->process_bulk_action();

        $per_page = $this->get_items_per_page('members_per_page', BADGES_MEMBERS_PER_PAGE);
        $current_page = $this->get_pagenum();

        $total_items = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page
        ]);


        $this->items = $this->get_attendances($per_page, $current_page);
    }

    public function process_bulk_action()
    {

        if (isset($_POST['_wpnonce']) && !empty($_POST['_wpnonce'])) {

            $nonce = filter_input(INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING);
            $action = 'bulk-' . $this->_args['plural'];

            if (!wp_verify_nonce($nonce, $action))
                wp_die('Nope! Security check failed!');

        }

        if ('bulk-delete' === $this->current_action()) {

            if (isset($_POST['bulk-delete']) && !empty($_POST['bulk-delete'])) {
                global $wpdb;

                $ids = implode(',', array_map('absint', $_POST['bulk-delete']));

                $table_name = $wpdb->prefix . 'cong_attendance_data';

                $sql = "DELETE FROM {$table_name} WHERE id IN($ids)";

                $wpdb->query($sql);
            }

        }
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => 'Delete',
            'bulk-export-attendance' => 'Export to PDF'
        ];

        return $actions;
    }

    protected function display_tablenav($which)
    {
        if ('top' == $which)
            wp_nonce_field('bulk-' . $this->_args['plural']);
        ?>
        <div class="tablenav <?php echo esc_attr($which); ?>">

            <div class="alignleft actions bulkactions">
                <?php $this->bulk_actions($which); ?>
                <div class="progress-wrap progress" data-progress-percent="25">
                    <div class="progress-bar progress"></div>
                </div>
            </div>
            <?php
            $this->extra_tablenav($which);
            $this->pagination($which);
            ?>

            <br class="clear"/>
        </div>
        <?php
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
}