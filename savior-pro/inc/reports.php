<?php
class Reports
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_report_menu_item'));
        add_action('admin_init', array($this, 'generate_reports'));
        add_action( 'admin_enqueue_scripts', array($this, 'load_reports_admin_style') );
    }
    public function load_reports_admin_style()
    {
        wp_enqueue_style( 'reports_admin_css', get_theme_file_uri('/inc/assets/css/reports-admin.css'), false, '1.0.0' );
    }
    public function dump()
    {
        echo (php_sapi_name() !== 'cli') ? '<pre>' : '';
        foreach (func_get_args() as $arg) {
            echo preg_replace('#\n{2,}#', "\n", print_r($arg, true));
        }
        echo (php_sapi_name() !== 'cli') ? '</pre>' : '';
    }

    public function add_report_menu_item()
    {
        add_menu_page('Registration Reports', 'Registration Reports', 'edit_others_posts', 'reports', array($this, 'report_page'), 'dashicons-clipboard', 6);
    }

    public function report_page()
    {
        $years = $this->get_years_for_export_data();

        if (empty($years)) return;
        ?>
        <div class="wrap">
            <h2>Registration Reports</h2>
            <div class="reports-container">
                <div class="row">
                    <form method="post">
                        <label for="school_registration_year">School
                            Registrations: </label>
                        <select id="school_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="school_registration">
                        <input type="submit" name="school_registration" value="Download Export File" class="download-reports">
                    </form>
                </div>
                <div class="row">
                    <form method="post">
                        <label for="gfmf_registration_year">Gas Flow
                            Measurement Fundamentals: </label>
                        <select id="gfmf_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="gfmf">
                        <input type="submit" name="gfmf" value="Download Export File"
                               class="download-reports">
                    </form>
                </div>
                <div class="row">
                    <form method="post">
                        <label for="ceu_registration_year">Continuing
                            Education Unit (CEU) </label>
                        <select id="ceu_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="ceu">
                        <input type="submit" name="ceu" value="Download Export File"
                               class="download-reports">
                    </form>
                </div>
                <div class="row">
                    <form method="post">
                        <label for="liquid_registration_year">Liquid Onsite / Virtual </label>
                        <select id="liquid_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="liquid">
                        <input type="submit" name="liquid" value="Download Export File"
                               class="download-reports">
                    </form>
                </div>
                <div class="row">
                    <form method="post">
                        <label for="vendors_registration_year"
                            >Vendors </label>
                        <select id="vendors_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="vendors">
                        <input type="submit" name="vendors" value="Download Export File"
                               class="download-reports">
                    </form>
                </div>
                <div class="row">
                    <form method="post">
                        <label for="vendors_registration_year"
                            >Attendees List</label>
                        <select id="vendors_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="attendees">
                        <input type="submit" name="vendors" value="Download Export File"
                               class="download-reports">
                    </form>
                </div>

                <div class="row">
                    <form method="post">
                        <label for="vendors_registration_year"
                            >Exhibitor List</label>
                        <select id="vendors_registration_year" name="year">
                            <?php foreach ($years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="export_type" value="exhibitors">
                        <input type="submit" name="vendors" value="Download Export File"
                               class="download-reports">
                    </form>
                </div>
            </div>
            <pre>
                <?php
                        $year = 2023;
                        $timestamp_start = $year.'-01-01 00:00:00.000'; // example
                        $timestamp_end   = $year.'-12-31 23:59:59.000'; // example
                        // Get orders by year
                        $args = array(
                            'limit' => -1,
                            'type'=> 'shop_order',
                            'status'=> array( 'wc-completed' ),
                            'date_created' => strtotime( $timestamp_start ) .'...'. strtotime( $timestamp_end ),
                        );
                        $orders = wc_get_orders($args);

                        // Process the orders
                        foreach ($orders as $order) {
                            // $order_id = $order->get_id();
                            // $order_total = $order->get_total();
                            // ... process other order details as needed
                            print_r($order->get_id());
                            echo "<br>";
                        }
                                    
                ?>
            </pre>
        </div>
        <?php
    }

    public function generate_reports()
    {
        if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'reports') return;
        if (isset($_POST) && !empty($_POST)) {
            if (!empty($_POST['export_type'])) {

                if ($_POST['export_type'] == 'school_registration') {
                    $this->generate_school_registration_report();
                }

                if ($_POST['export_type'] == 'gfmf') {
                    $this->generate_gfmf_report();
                }

                if ($_POST['export_type'] == 'ceu') {
                    $this->generate_ceu_report();
                }

                if ($_POST['export_type'] == 'liquid') {
                    $this->generate_liquid_report();
                }

                if ($_POST['export_type'] == 'vendors') {
                    $this->generate_vendors_report();
                }

                if ($_POST['export_type'] == 'attendees') {
                    $this->generate_atteendees_report();
                }
                if ($_POST['export_type'] == 'exhibitors') {
                    $this->generate_exhibitors_report();
                }
                
            }
        }
    }

    public function generate_exhibitors_reports_year($year)
    {
        global $wpdb;
        $server_name = $_SERVER['SERVER_NAME'];
        $rows = array();

        if($year < 2022) {

            if ($server_name === 'asgmt.local') {
                $vendor_db = new wpdb('root', '', 'vendor', 'localhost');
                $prefix = 'wp_';
            } else {
                $vendor_db = new wpdb('asgmt_wp', 'tiIdFbYExID3', 'asgmt_wp', 'localhost');
                $prefix = 'wp2021_';
            }

            //$year = $_POST['year'];

            $product_id = '24';

            $query = "select
    p.ID as order_id,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
    max( CASE WHEN pm.meta_key = '_billing_phone' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_phone,
    max( CASE WHEN pm.meta_key = '_billing_company' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_company,
    max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
    max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
    max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
    max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
    max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
    max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
   ( select oim2.order_item_name from {$prefix}woocommerce_order_items oim2 where oim2.order_id = p.ID and oim2.order_item_name LIKE '%Vendor Booth Registration%' ) as order_items

from
    {$prefix}posts p 
    JOIN {$prefix}postmeta pm ON p.ID = pm.post_id
    JOIN {$prefix}woocommerce_order_items oi on p.ID = oi.order_id
where
    post_type = 'shop_order' AND
    post_date BETWEEN '$year-01-01' AND '$year-12-31' AND
        post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
group by
    p.ID";

            $res = $vendor_db->get_results($query, ARRAY_A);

            if (!empty($res)) {
                foreach ($res as $key => $item) {
                    $rows[] = [
                        'Company' => $item['_billing_company'],
                        'Booth Number' => '',
                        'Contact Name' => $item['_billing_first_name']. ' '. $item['_billing_last_name'],
                        'Contact Email' => $item['billing_email'],
                        'Company Description' => '',
                        'Website' => '',
                        'Address' => $item['_billing_address_1'] .' ' .$item['_billing_postcode'] . ' '.$item['_billing_city']. ' '.$item['_billing_state'],
                        'Phone' => $item['_billing_phone'],
                        'Tier' => '',
                    ];
                }
            }
        } else {

        
        // old reports by year;
        $prefix_asgmt_com = $wpdb->prefix;
        $query = "select
    p.ID as order_id,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
    max( CASE WHEN pm.meta_key = '_billing_phone' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_phone,
    max( CASE WHEN pm.meta_key = '_billing_company' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_company,
    max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
    max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
    max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
    max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
    max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
    max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
    ( select GROUP_CONCAT(oim2.order_item_name, ' (', oim3.meta_value, ')') as qty
    from {$prefix_asgmt_com}woocommerce_order_items oim2
    LEFT JOIN {$prefix_asgmt_com}woocommerce_order_itemmeta oim3 ON oim2.order_item_id = oim3.order_item_id AND oim3.meta_key = '_qty'
    where oim2.order_id = p.ID and 
          oim2.order_item_name LIKE '%Vendor Booth Registration%' ) 
          as order_items
from
    {$prefix_asgmt_com}posts p 
    JOIN {$prefix_asgmt_com}postmeta pm ON p.ID = pm.post_id
    JOIN {$prefix_asgmt_com}woocommerce_order_items oi on p.ID = oi.order_id AND oi.order_item_name LIKE '%Vendor Booth Registration%'
where
    post_type = 'shop_order' AND
    post_date BETWEEN '$year-01-01' AND '$year-12-31' AND
    post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
group by
    p.ID";

        $res = $wpdb->get_results($query, ARRAY_A);
        }
        if (!empty($res)) {
            foreach ($res as $key => $item) {
                $rows[] = [
                    'Company' => $item['_billing_company'],
                    'Booth Number' => '',
                    'Contact Name' => $item['_billing_first_name']. ' '. $item['_billing_last_name'],
                    'Contact Email' => $item['billing_email'],
                    'Company Description' => '',
                    'Website' => '',
                    'Address' => $item['_billing_address_1'] .' ' .$item['_billing_postcode'] . ' '.$item['_billing_city']. ' '.$item['_billing_state'],
                    'Phone' => $item['_billing_phone'],
                    'Tier' => '',
                ];
            }
        }

        if (!empty($rows)) {
            $this->download_send_headers("Exhibitor_list_year_".$year."_report_".date("Y-m-d").".csv");
            echo $this->array2csv($rows);
            die();
        }
    }
    public function generate_exhibitors_report()
    {
        $rows = array();
        $year = isset($_POST['year']) ? $_POST['year'] : date("Y");
        $product_id = '24';
        switch ($year) {
            case '2026':
            case '2025':
            case '2024':
            case '2023':
            case '2022':
            case '2021':
                $this->generate_exhibitors_reports_year($year);
            break;
            default:
                $result = $this->get_orders_ids_by_product_id($product_id, $year);

                if (!empty($result)) {
                    foreach ($result as $order_id => $rec) {

                        $parse = self::get_item_from_meta_value($rec['meta_value']);

                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);

                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }

                                $visitor_type = self::get_visitor_type($item);

                                if ($visitor_type === 'vendor' && $key !== 'additional') {
                                    $rows[] = [
                                        'Company' => isset($item['Company']) ? $item['Company'] : '',
                                        'Booth Number' => '',
                                        'Contact Name' => (isset($item['Contact Responsible for Exhibit']['First']) ? $item['Contact Responsible for Exhibit']['First'] : ''). ' '. 
                                            (isset($item['Contact Responsible for Exhibit']['Last']) ? $item['Contact Responsible for Exhibit']['Last'] : ''),
                                        'Contact Email' =>  $item['Email'],
                                        'Company Description' => '',
                                        'Website' => '',
                                        'Address' => '',
                                        'Phone' =>  '',
                                        'Tier' => '',
                                    ];
                                }
                            }
                        }
                    }
                }

                if (!empty($rows)) {
                    $this->download_send_headers("Exhibitor_list_year_".$year."_report_" . date("Y-m-d") . ".csv");
                    echo $this->array2csv($rows);
                    die();
                }
            break;
        }

    }
    public function generate_atteendees_report()
    {
        $rows = array();
        $year = $_POST['year'];
        $product_id = '24';

        $result = $this->get_orders_ids_by_product_id($product_id, $year);

        if (!empty($result)) {
            switch($year) {
                case '2024':
                case '2023':
                case '2022':
                    $rrows = array();
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        // echo "<br>parse array";
                        // $this->dump($parse);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);
                                
                                //$this->dump($item);
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);

                                if ($visitor_type === 'student' && $key !== 'additional') {

                                    $rrows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Full Name' => '',//(isset($item['Name']['First']) ? $item['Name']['First'] : '') . ' '. (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Email' => isset($item['Email']) ? $item['Email'] : '',
                                        'Company' => isset($item['Company']) ? $item['Company'] : '',
                                        'Position' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Location' => (isset($item['City']) ? $item['City'] : ''). !empty($item['State/Province/Region']) ? ', '.$item['State/Province/Region'] : (isset($item['State']) ? ', '.$item['State'] : ''),
                                        'Attendee Category' => '',
                                        'Bio' => '',
                                        'Ticket Type' => 'Attendee',
                                    ];
                                }
                            }
                        }
                    }
                    // $rows = array_merge([[
                    //     'First Name' => '',
                    //     'Last Name' => '',
                    //     //'Full Name' => '',
                    //     'Email' => '',
                    //     'Company' => '',
                    //     'Position' => '',
                    //     'Location' => '',
                    //     'Attendee Category' => '',
                    //     'Bio' => '',
                    //     'Ticket Type' => '',
                    // ]],$rrows);
                    $rows = $rrows;
                break;
                default:
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);
        
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);
        
                                if ($visitor_type === 'student' && $key !== 'additional') {
            
                                    $rows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : ( isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Full Name' => '',//(isset($item['Name']['First']) ? $item['Name']['First'] : '') . ' '. (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Email' => isset($item['Email']) ? $item['Email'] : '',
                                        'Company' => isset($item['Company']) ? $item['Company'] : '',
                                        'Position' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Location' => (isset($item['City']) ? $item['City'] : ''). !empty($item['State/Province/Region']) ? ', '.$item['State/Province/Region'] : (isset($item['State']) ? ', '.$item['State'] : ''),
                                        'Attendee Category' => '',
                                        'Bio' => '',
                                        'Ticket Type' => 'Attendee'
                                       ];
                                    }
                                }
                            }
                        }
                    break;
                }
        }
        // echo "whats the issue?";
        //  $this->dump($rows);
        // die;
        if (!empty($rows)) {
            $this->download_send_headers("Attendees_report_year_".$year."_report_" . date("Y-m-d") . ".csv");
            echo $this->array2csv($rows);
            die();
        }

    }

    public function generate_vendors_report()
    {
        $rows = array();
        $year = isset($_POST['year']) ? $_POST['year'] : date("Y");
        $product_id = '24';
        switch ($year) {
            case '2024':
            case '2023':
            case '2022':
            case '2021':
                $this->generate_vendor_reports_new($year);
            break;
            default:
                $result = $this->get_orders_ids_by_product_id($product_id, $year);

                if (!empty($result)) {
                    foreach ($result as $order_id => $rec) {

                        $parse = self::get_item_from_meta_value($rec['meta_value']);

                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);

                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);

                                if ($visitor_type === 'vendor' && $key !== 'additional') {
                                    $rows[] = [
                                        'Name (First Name)' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Name (Last Name)' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Company' => isset($item['Company']) ? $item['Company'] : '',
                                        'Contact Responsible for Exhibit (First)' => (isset($item['Contact Responsible for Exhibit']['First']) ? $item['Contact Responsible for Exhibit']['First'] : ''),
                                        'Contact Responsible for Exhibit (Last)' => (isset($item['Contact Responsible for Exhibit']['Last']) ? $item['Contact Responsible for Exhibit']['Last'] : ''),
                                        'Friendly Name' => (isset($item['Friendly Name']) ? $item['Friendly Name'] : ''),
                                        'Order Number' => $rec['id']
                                    ];
                                }
                            }
                        }
                    }
                }

                if (!empty($rows)) {
                    $this->download_send_headers("data_export_".$year."_" . date("Y-m-d") . ".csv");
                    echo $this->array2csv($rows);
                    die();
                }
            break;
        }

    }

    public function generate_ceu_report()
    {
        $rows = array();
        $rrows = array();
        $year = $_POST['year'];
        $product_id = '24';

        $result = $this->get_orders_ids_by_product_id($product_id, $year);

        if (!empty($result)) {
            switch($year) {
                case '2024':
                case '2023':
                case '2022':
                    $count_gas_online = 0;
                    $count_gas_in_person = 0;
                    $count_liquid_online = 0;
                    $count_liquid_in_person= 0;
                    $count_ceu = 0;
                    foreach ($result as $order_id => $rec) {
                        
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        // echo "is array ";
                        // $this->dump($rec);
                        // echo "parsed ";
                        // $this->dump($parse);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);
                                // echo "item ";
                                // $this->dump($item);
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);

                                $is_ceu = ((isset($item['Mailing Address for CEU']) || 
                                    isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ||
                                    isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ||
                                    isset($item['Liquid Course (CEU Included +$38) Virtual Class Online']) ||
                                    isset($item['Liquid Course (CEU Included +$38) In-Person']) ||
                                    isset($item['CEU'])
                                ) ? true : false);

                                $count_gas_online       += isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ? 1 : 0;
                                $count_gas_in_person    += isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ? 1 : 0;
                                $count_liquid_online    += isset($item['Liquid Course (CEU Included +$38) Virtual Class Online']) ? 1 : 0;
                                $count_liquid_in_person += isset($item['Liquid Course (CEU Included +$38) In-Person']) ? 1 : 0;
                                $count_ceu              += isset($item['CEU']) ? 1 : 0;

                                if ($visitor_type !== 'vendor' && $key !== 'additional' && $is_ceu) {
                                    $rrows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => (isset($item['Company']) ? $item['Company'] : ''),
                                        'Street Address' => (isset($item['Mailing Address']['Street Address']) && $item['Mailing Address']['Street Address']!='') ? 
                                            $item['Mailing Address']['Street Address'] : 
                                            (isset($item['Mailing Address for CEU']['Street Address']) ? $item['Mailing Address for CEU']['Street Address'] : ''),
                                        'Address Line 2' => (isset($item['Mailing Address']['Address Line 2']) && $item['Mailing Address']['Address Line 2']!='') ? 
                                            $item['Mailing Address']['Address Line 2'] : 
                                            (isset($item['Mailing Address for CEU']['Address Line 2']) ? $item['Mailing Address for CEU']['Address Line 2'] : ''),
                                        'City' => (isset($item['Mailing Address']['City']) && $item['Mailing Address']['City']!='') ? 
                                            $item['Mailing Address']['City'] : 
                                            (isset($item['Mailing Address for CEU']['City']) ? $item['Mailing Address for CEU']['City'] : ''),
                                        'State' => (isset($item['Mailing Address']['State']) && $item['Mailing Address']['State']!='') ?
                                            $item['Mailing Address']['State'] : 
                                            (isset($item['Mailing Address for CEU']['State']) ? $item['Mailing Address for CEU']['State'] : ''),
                                        'Zip' => (isset($item['Mailing Address']['ZIP / Postal Code']) && $item['Mailing Address']['ZIP / Postal Code']!='') ? 
                                            $item['Mailing Address']['ZIP / Postal Code'] : 
                                            (isset($item['Mailing Address for CEU']['ZIP / Postal Code']) ? $item['Mailing Address for CEU']['ZIP / Postal Code'] : ''),
                                        'Phone' => (isset($item['Daytime Phone']) ? $item['Daytime Phone'] : ''),
                                        'Email' => (isset($item['Email']) ? $item['Email'] : ''),
                                        'Order Number' => $rec['id'],
                                        'Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online' => isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ? 1 : 0,
                                        'Gas Measurement Fundamentals (CEU Included +$38) In-Person' => isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ? 1 : 0,
                                        'Liquid Course (CEU Included +$38) Virtual Class Online' => isset($item['Liquid Course (CEU Included +$38) Virtual Class Online']) ? 1 : 0,
                                        'Liquid Course (CEU Included +$38) In-Person' => isset($item['Liquid Course (CEU Included +$38) In-Person']) ? 1 : 0,
                                        'CEU' => isset($item['CEU']) ? 1 : 0,

                                    ];
                                }
                            }
                        }
                    }
                    $rows = array_merge([[
                        'First Name' => '',
                        'Last Name' => '',
                        'Title' => '',
                        'Company' => '',
                        'Street Address' => '',
                        'Address Line 2' => '',
                        'City' => '',
                        'State' => '',
                        'Zip' => '',
                        'Phone' => '',
                        'Email' => '',
                        'Order Number' => '',
                        'Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online' => $count_gas_online,
                        'Gas Measurement Fundamentals (CEU Included +$38) In-Person'            => $count_gas_in_person,
                        'Liquid Course (CEU Included +$38) Virtual Class Online'                => $count_liquid_online,
                        'Liquid Course (CEU Included +$38) In-Person'                           => $count_liquid_in_person,
                        'CEU'                                                                   => $count_ceu,
                    ]],$rrows);

                break;
                default: //previous years logic
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);

                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);

                                $is_ceu = ((isset($item['Mailing Address for CEU']) or isset($item['Continuing Education Unit (CEU)'])) ? true : false);
                               

                                if ($visitor_type !== 'vendor' && $key !== 'additional' && $is_ceu) {
                                    
                                    $rows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => (isset($item['Company']) ? $item['Company'] : ''),
                                        'Street Address' => (isset($item['Mailing Address for CEU']['Street Address']) ? $item['Mailing Address for CEU']['Street Address'] : ''),
                                        'Address Line 2' => (isset($item['Mailing Address for CEU']['Address Line 2']) ? $item['Mailing Address for CEU']['Address Line 2'] : ''),
                                        'City' => (isset($item['Mailing Address for CEU']['City']) ? $item['Mailing Address for CEU']['City'] : ''),
                                        'State' => (isset($item['Mailing Address for CEU']['State / Province']) ? $item['Mailing Address for CEU']['State / Province'] : ''),
                                        'Zip' => (isset($item['Mailing Address for CEU']['ZIP / Postal Code']) ? $item['Mailing Address for CEU']['ZIP / Postal Code'] : ''),
                                        'Phone' => (isset($item['Daytime Phone']) ? $item['Daytime Phone'] : ''),
                                        'Email' => (isset($item['Email']) ? $item['Email'] : ''),
                                        'Order Number' => $rec['id']
                                    ];
                                }
                            }
                        }
                    }
                break;
            }
        }
        //$this->dump($rows);die;
        if (!empty($rows)) {
            $this->download_send_headers("CEU_report_year_".$year."_data_export_on_" . date("Y-m-d") . ".csv");
            echo $this->array2csv($rows);
            die();
        }

    }

    public function generate_liquid_report()
    {
        $rows = array();
        $year = $_POST['year'];
        $product_id = '24';

        $result = $this->get_orders_ids_by_product_id($product_id, $year);

        if (!empty($result)) {
            switch($year) {
                case '2024':
                case '2023':
                case '2022':
                    $count_virtual = 0;
                    $count_in_person = 0;
                    $rrows = [];
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        if (is_array($parse)) {
                            // echo "is array ";
                            // $this->dump($rec);
                            // echo "parsed ";
                            // $this->dump($parse);
                            foreach ($parse as $key => $item) {

                                //echo "$key - item";
                                $item = $this->translate_array($item);
    
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                // echo "item";
                                // $this->dump($item);
                                $visitor_type = self::get_visitor_type($item);
                                
                                $is_liquid_virtual = isset($item['Liquid Course (CEU Included +$38) Virtual Class Online']) ? true : false;
                                $is_liquid_in_person = isset($item['Liquid Course (CEU Included +$38) In-Person']) ? true : false;
                                $count_virtual += $is_liquid_virtual ? 1 : 0;
                                $count_in_person += $is_liquid_virtual ? 1 : 0;
                                //echo "(".var_export($is_liquid_virtual, true)." || ".var_export($is_liquid_in_person, true) ." ) && ".var_export($visitor_type, true)." !== 'vendor' && $key !== 'additional') <br><br>";

                                if (($is_liquid_virtual || $is_liquid_in_person) && $visitor_type !== 'vendor' && $key !== 'additional') {
                                    $rrows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => (isset($item['Company']) ? $item['Company'] : ''),
                                        'Street Address' => (isset($item['Mailing Address']['Street Address']) ? $item['Mailing Address']['Street Address'] : ''),
                                        'Address Line 2' => (isset($item['Mailing Address']['Address Line 2']) ? $item['Mailing Address']['Address Line 2'] : ''),
                                        'City' => (isset($item['Mailing Address']['City']) ? $item['Mailing Address']['City'] : ''),
                                        'State' => (isset($item['Mailing Address']['State']) ? 
                                            $item['Mailing Address']['State'] : 
                                            (isset($item['Mailing Address']['State / Province']) ? $item['Mailing Address']['State / Province'] : '')),
                                        'Zip' => (isset($item['Mailing Address']['ZIP / Postal Code']) ? $item['Mailing Address']['ZIP / Postal Code'] : ''),
                                        'Phone' => (isset($item['Daytime Phone']) ? $item['Daytime Phone'] : ''),
                                        'Email' => (isset($item['Email']) ? $item['Email'] : ''),
                                        'Order Number' => $rec['id'],
                                        'In Person' => $is_liquid_in_person ? 'Yes' : 'No',
                                        'Virtual' =>$is_liquid_virtual ? 'Yes' : 'No',
                                    ];
                                }
                            }
                        }
                    }
                    $rows = array_merge([[
                        'First Name' => '',
                        'Last Name' => '',
                        'Title' => '',
                        'Company' => '',
                        'Street Address' => '',
                        'Address Line 2' => '',
                        'City' => '',
                        'State' => '',
                        'Zip' => '',
                        'Phone' => '',
                        'Email' => '',
                        'Order Number' => '',
                        'In Person' => $count_in_person,
                        'Virtual' => $count_virtual,
                    ]],$rrows);
                    //$this->dump($rrows);die;
                    if (!empty($rows)) {
                        $this->download_send_headers("data_export_liquid_inperson_virtual_for_".$year."_on_" . date("Y-m-d") . ".csv");
                        echo $this->array2csv($rows);
                        die();
                    }
                break;
                default:
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);

                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);

                                $is_liquid = ((isset($item['Mailing Address for Liquid Course'])
                                    || isset($item['Liquid Course  (CEU Included +$38)']) ) ? true : false);

                                if ($visitor_type !== 'vendor' && $key !== 'additional' && $is_liquid) {
                                    $rows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => (isset($item['Company']) ? $item['Company'] : ''),
                                        'Street Address' => (isset($item['Mailing Address for Liquid Course']['Street Address']) ? $item['Mailing Address for Liquid Course']['Street Address'] : ''),
                                        'Address Line 2' => (isset($item['Mailing Address for Liquid Course']['Address Line 2']) ? $item['MMailing Address for Liquid Course']['Address Line 2'] : ''),
                                        'City' => (isset($item['Mailing Address for Liquid Course']['City']) ? $item['Mailing Address for Liquid Course']['City'] : ''),
                                        'State' => (isset($item['Mailing Address for Liquid Course']['State / Province']) ? $item['Mailing Address for Liquid Course']['State / Province'] : ''),
                                        'Zip' => (isset($item['Mailing Address for Liquid Course']['ZIP / Postal Code']) ? $item['Mailing Address for Liquid Course']['ZIP / Postal Code'] : ''),
                                        'Phone' => (isset($item['Daytime Phone']) ? $item['Daytime Phone'] : ''),
                                        'Email' => (isset($item['Email']) ? $item['Email'] : ''),
                                        'Order Number' => $rec['id']
                                    ];
                                }
                            }
                        }
                    }
                    if (!empty($rows)) {
                        $this->download_send_headers("data_export_liquid_for_".$year."_on_" . date("Y-m-d") . ".csv");
                        echo $this->array2csv($rows);
                        die();
                    }
                break;
            }
        }
        if (!empty($rows)) {
            $this->download_send_headers("data_export_for_".$year."_on_" . date("Y-m-d") . ".csv");
            echo $this->array2csv($rows);
            die();
        }

    }

    public function generate_gfmf_report()
    {
        $rows = array();
        $year = $_POST['year'];
        $product_id = '24';

        $result = $this->get_orders_ids_by_product_id($product_id, $year);

        if (!empty($result)) {
            switch($year) {
                case '2024':
                case '2023':
                case '2022':
                    $count_gas_online = 0;
                    $count_gas_in_person = 0;
                    $rrows = [];
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        // echo "is array " . $order_id;
                        // $this->dump($rec);
                        // echo "parsed ";
                        // $this->dump($parse);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);

                               
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);
                                $count_gas_online       += isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ? 1 : 0;
                                $count_gas_in_person    += isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ? 1 : 0;
                                
                                $is_gas = ((isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) 
                                || isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person'])) ? true : false);

                                if ($visitor_type !== 'vendor' && $key !== 'additional' && $is_gas) {
                                    // echo "item ";
                                    // $this->dump($item);
                                    $rrows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => (isset($item['Company']) ? $item['Company'] : ''),
                                        'Street Address' => (isset($item['Mailing Address']['Street Address']) && $item['Mailing Address']['Street Address']!='' ? $item['Mailing Address']['Street Address'] :
                                            (isset($item['Mailing Address for CEU']['Street Address']) ? $item['Mailing Address for CEU']['Street Address'] : '')),
                                        'Address Line 2' => (isset($item['Mailing Address']['Address Line 2']) && $item['Mailing Address']['Address Line 2']!='' ? $item['Mailing Address']['Address Line 2'] : 
                                            (isset($item['Mailing Address for CEU']['Address Line 2']) ? $item['Mailing Address for CEU']['Address Line 2'] : '')),
                                        'City' => (isset($item['Mailing Address']['City']) && $item['Mailing Address']['City']!='' ? $item['Mailing Address']['City'] :
                                            (isset($item['Mailing Address for CEU']['City']) && isset($item['Mailing Address for CEU']['City'])!=''  ? $item['Mailing Address for CEU']['City'] : 
                                                (isset($item['City']) ? $item['City'] : ''))),
                                        'State' => (isset($item['Mailing Address']['State']) && $item['Mailing Address']['State'])!=''  ? $item['Mailing Address']['State'] : 
                                            (isset($item['Mailing Address for CEU']['State']) && isset($item['Mailing Address for CEU']['State'])!='' ? $item['Mailing Address for CEU']['State'] : 
                                                (isset($item['State']) ? $item['State'] : '')),
                                        'Zip' => (isset($item['Mailing Address']['ZIP / Postal Code']) && isset($item['Mailing Address']['ZIP / Postal Code'])!='' && isset($item['Mailing Address']['State'])!='' ? $item['Mailing Address']['ZIP / Postal Code'] : 
                                            (isset($item['Mailing Address for CEU']['ZIP / Postal Code']) ? $item['Mailing Address for CEU']['ZIP / Postal Code'] : '')),
                                        'Phone' => (isset($item['Daytime Phone']) ? $item['Daytime Phone'] : ''),
                                        'Email' => (isset($item['Email']) ? $item['Email'] : ''),
                                        'Order Number' => $rec['id'],
                                        'In Person' => isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ? 1 : 0,
                                        'Virtual' => isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ? 1 : 0,
                                    ];
                                }
                            }
                        }
                    }
                    $rows = array_merge([[
                        'First Name' => '',
                        'Last Name' => '',
                        'Title' => '',
                        'Company' => '',
                        'Street Address' => '',
                        'Address Line 2' => '',
                        'City' => '',
                        'State' => '',
                        'Zip' => '',
                        'Phone' => '',
                        'Email' => '',
                        'Order Number' => '',
                        'In Person' => $count_gas_in_person,
                        'Virtual' => $count_gas_online,
                    ]],$rrows);
                    //$this->dump($rrows);die;
                break;
                default:
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        // echo "is array " . $order_id;
                        // $this->dump($rec);
                        // echo "parsed ";
                        // $this->dump($parse);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);
                                // echo "item ".$key;
                                // $this->dump($item);
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $visitor_type = self::get_visitor_type($item);
        
                                $is_gas = ((isset($item['Mailing Address for GFMF']) or isset($item['Gas Flow Measurement Fundamentals'])) ? true : false);
        
                                if ($visitor_type !== 'vendor' && $key !== 'additional' && $is_gas) {
                                    $rows[] = [
                                        'First Name' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Last Name' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => (isset($item['Company']) ? $item['Company'] : ''),
                                        'Street Address' => (isset($item['Mailing Address for CEU']['Street Address']) ? $item['Mailing Address for CEU']['Street Address'] : ''),
                                        'Address Line 2' => (isset($item['Mailing Address for CEU']['Address Line 2']) ? $item['Mailing Address for CEU']['Address Line 2'] : ''),
                                        'City' => (isset($item['Mailing Address for CEU']['City']) ? $item['Mailing Address for CEU']['City'] : ''),
                                        'State' => (isset($item['Mailing Address for CEU']['State / Province']) ? $item['Mailing Address for CEU']['State / Province'] : ''),
                                        'Zip' => (isset($item['Mailing Address for CEU']['ZIP / Postal Code']) ? $item['Mailing Address for CEU']['ZIP / Postal Code'] : ''),
                                        'Phone' => (isset($item['Daytime Phone']) ? $item['Daytime Phone'] : ''),
                                        'Email' => (isset($item['Email']) ? $item['Email'] : ''),
                                        'Order Number' => $rec['id']
                                    ];
                                }
                            }
                        }
                    }
                break;
            }
        }   
        //$this->dump($rows);die;
        if (!empty($rows)) {
            $this->download_send_headers("GAS_report_year_".$year."_data_export_" . date("Y-m-d") . ".csv");
            echo $this->array2csv($rows);
            die();
        }

    }

    public function generate_vendor_reports_new($year)
    {
        global $wpdb;
        $server_name = $_SERVER['SERVER_NAME'];
        $rows = array();

        if($year < 2022) {

            if ($server_name === 'asgmt.local') {
                $vendor_db = new wpdb('root', '', 'vendor', 'localhost');
                $prefix = 'wp_';
            } else {
                $vendor_db = new wpdb('asgmt_wp', 'tiIdFbYExID3', 'asgmt_wp', 'localhost');
                $prefix = 'wp2021_';
            }

            //$year = $_POST['year'];

            $product_id = '24';

            $query = "select
    p.ID as order_id,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
    max( CASE WHEN pm.meta_key = '_billing_phone' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_phone,
    max( CASE WHEN pm.meta_key = '_billing_company' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_company,
    max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
    max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
    max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
    max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
    max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
    max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
   ( select oim2.order_item_name from {$prefix}woocommerce_order_items oim2 where oim2.order_id = p.ID and oim2.order_item_name LIKE '%Vendor Booth Registration%' ) as order_items

from
    {$prefix}posts p 
    JOIN {$prefix}postmeta pm ON p.ID = pm.post_id
    JOIN {$prefix}woocommerce_order_items oi on p.ID = oi.order_id
where
    post_type = 'shop_order' AND
    post_date BETWEEN '$year-01-01' AND '$year-12-31' AND
        post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
group by
    p.ID";

            $res = $vendor_db->get_results($query, ARRAY_A);

            if (!empty($res)) {
                foreach ($res as $key => $item) {
                    $rows[] = [
                        'Order ID' => $item['order_id'],
                        'First Name' => $item['_billing_first_name'],
                        'Last Name' => $item['_billing_last_name'],
                        'Email' => $item['billing_email'],
                        'Phone' => $item['_billing_phone'],
                        'Company' => $item['_billing_company'],
                        'Adress' => $item['_billing_address_1'],
                        'City' => $item['_billing_city'],
                        'State' => $item['_billing_state'],
                        'Postcode' => $item['_billing_postcode'],
                        'Order Total' => $item['order_total'],
                        'Paid Date' => $item['paid_date'],
                        'Order Items' => $item['order_items']
                    ];
                }
            }
        } else {

        
        // old reports by year;
        $prefix_asgmt_com = $wpdb->prefix;
        $query = "select
    p.ID as order_id,
    max( CASE WHEN pm.meta_key = '_billing_email' and p.ID = pm.post_id THEN pm.meta_value END ) as billing_email,
    max( CASE WHEN pm.meta_key = '_billing_first_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_first_name,
    max( CASE WHEN pm.meta_key = '_billing_last_name' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_last_name,
    max( CASE WHEN pm.meta_key = '_billing_phone' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_phone,
    max( CASE WHEN pm.meta_key = '_billing_company' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_company,
    max( CASE WHEN pm.meta_key = '_billing_address_1' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_1,
    max( CASE WHEN pm.meta_key = '_billing_address_2' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_address_2,
    max( CASE WHEN pm.meta_key = '_billing_city' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_city,
    max( CASE WHEN pm.meta_key = '_billing_state' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_state,
    max( CASE WHEN pm.meta_key = '_billing_postcode' and p.ID = pm.post_id THEN pm.meta_value END ) as _billing_postcode,
    max( CASE WHEN pm.meta_key = '_order_total' and p.ID = pm.post_id THEN pm.meta_value END ) as order_total,
    max( CASE WHEN pm.meta_key = '_paid_date' and p.ID = pm.post_id THEN pm.meta_value END ) as paid_date,
    ( select GROUP_CONCAT(oim2.order_item_name, ' (', oim3.meta_value, ')') as qty
    from {$prefix_asgmt_com}woocommerce_order_items oim2
    LEFT JOIN {$prefix_asgmt_com}woocommerce_order_itemmeta oim3 ON oim2.order_item_id = oim3.order_item_id AND oim3.meta_key = '_qty'
    where oim2.order_id = p.ID and 
          oim2.order_item_name LIKE '%Vendor Booth Registration%' ) 
          as order_items
from
    {$prefix_asgmt_com}posts p 
    JOIN {$prefix_asgmt_com}postmeta pm ON p.ID = pm.post_id
    JOIN {$prefix_asgmt_com}woocommerce_order_items oi on p.ID = oi.order_id AND oi.order_item_name LIKE '%Vendor Booth Registration%'
where
    post_type = 'shop_order' AND
    post_date BETWEEN '$year-01-01' AND '$year-12-31' AND
    post_status IN ( 'wc-completed', 'wc-processing', 'wc-on-hold' )
group by
    p.ID";

        $res = $wpdb->get_results($query, ARRAY_A);
        }
        if (!empty($res)) {
            foreach ($res as $key => $item) {
                $rows[] = [
                    'Order ID' => $item['order_id'],
                    'First Name' => $item['_billing_first_name'],
                    'Last Name' => $item['_billing_last_name'],
                    'Email' => $item['billing_email'],
                    'Phone' => $item['_billing_phone'],
                    'Company' => $item['_billing_company'],
                    'Adress' => $item['_billing_address_1'],
                    'City' => $item['_billing_city'],
                    'State' => $item['_billing_state'],
                    'Postcode' => $item['_billing_postcode'],
                    'Order Total' => $item['order_total'],
                    'Paid Date' => $item['paid_date'],
                    'Order Items' => $item['order_items']
                ];
            }
        }

        if (!empty($rows)) {
            $this->download_send_headers("data_export_vendors_report_" . $year . ".csv");
            echo $this->array2csv($rows);
            die();
        }

    }

    public function generate_school_registration_report()
    {
        $rows = array();
        $year = $_POST['year'];
        $product_id = '24';

        $result = $this->get_orders_ids_by_product_id($product_id, $year);

        if (!empty($result)) {
            switch($year) {
                case '2024':
                case '2023':
                case '2022':
                    $rrows = array();
                    $count_gas_online = 0;
                    $count_gas_in_person = 0;
                    $count_liquid_online = 0;
                    $count_liquid_in_person= 0;
                    $count_ceu = 0;
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);

                                //$this->dump($item);
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }
                                if(isset($rec['badge_email']) && isset($item['Email'])){
                                    if($rec['badge_email'] != $item['Email']){
                                        continue;
                                    }
                                }
                                $count_gas_online       += isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ? 1 : 0;
                                $count_gas_in_person    += isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ? 1 : 0;
                                $count_liquid_online    += isset($item['Liquid Course (CEU Included +$38) Virtual Class Online']) ? 1 : 0;
                                $count_liquid_in_person += isset($item['Liquid Course (CEU Included +$38) In-Person']) ? 1 : 0;
                                $count_ceu              += isset($item['CEU']) ? 1 : 0;

                                $visitor_type = self::get_visitor_type($item);

                                if ($visitor_type === 'student' && $key !== 'additional') {

                                    $rrows[] = [
                                        'Friendly Name' => (isset($item['Friendly Name']) ? $item['Friendly Name'] : ''),
                                        'Name (First Name)' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Name (Last Name)' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Email' => isset($item['Email']) ? $item['Email'] : '',
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => isset($item['Company']) ? $item['Company'] : '',
                                        'City' => isset($item['City']) ? $item['City'] : '',
                                        'state' => !empty($item['State/Province/Region']) ? $item['State/Province/Region'] : (isset($item['State']) ? $item['State'] : ''),
                                        'Order Number' => $rec['id'],
                                        'Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online' => isset($item['Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online']) ? 1 : 0,
                                        'Gas Measurement Fundamentals (CEU Included +$38) In-Person' => isset($item['Gas Measurement Fundamentals (CEU Included +$38) In-Person']) ? 1 : 0,
                                        'Liquid Course (CEU Included +$38) Virtual Class Online' => isset($item['Liquid Course (CEU Included +$38) Virtual Class Online']) ? 1 : 0,
                                        'Liquid Course (CEU Included +$38) In-Person' => isset($item['Liquid Course (CEU Included +$38) In-Person']) ? 1 : 0,
                                        'CEU' => isset($item['CEU']) ? 1 : 0
                                    ];
                                }
                            }
                        }
                    }
                    $rows = array_merge([[
                        'Friendly Name' => '',
                        'Name (First Name)' => '',
                        'Name (Last Name)' => '',
                        'Email' => '',
                        'Title' => '',
                        'Company' => '',
                        'City' => '',
                        'state' => '',
                        'Order Number' => '',
                        'Gas Measurement Fundamentals (CEU Included +$38) Virtual Class Online' => $count_gas_online,
                        'Gas Measurement Fundamentals (CEU Included +$38) In-Person'            => $count_gas_in_person,
                        'Liquid Course (CEU Included +$38) Virtual Class Online'                => $count_liquid_online,
                        'Liquid Course (CEU Included +$38) In-Person'                           => $count_liquid_in_person,
                        'CEU'                                                                   => $count_ceu,
                    ]],$rrows);
                break;
                default:
                    foreach ($result as $order_id => $rec) {
                        $parse = self::get_item_from_meta_value($rec['meta_value']);
                        if (is_array($parse)) {
                            foreach ($parse as $key => $item) {
                                $item = $this->translate_array($item);
        
                                if (is_array($item['Email'])) {
                                    $item['Email'] = $item['Email']['Email'];
                                    unset($item['email']);
                                }

                                $visitor_type = self::get_visitor_type($item);
        
                                if ($visitor_type === 'student' && $key !== 'additional') {
            
                                    $rows[] = [
                                        'Friendly Name' => (isset($item['Friendly Name']) ? $item['Friendly Name'] : ''),
                                        'Name (First Name)' => isset($rec['badge_fname']) ? $rec['badge_fname'] : (isset($item['Name']['First']) ? $item['Name']['First'] : ''),
                                        'Name (Last Name)' => isset($rec['badge_lname']) ? $rec['badge_lname'] : (isset($item['Name']['Last']) ? $item['Name']['Last'] : ''),
                                        'Email' => isset($item['Email']) ? $item['Email'] : '',
                                        'Title' => (isset($item['Title']) ? $item['Title'] : ''),
                                        'Company' => isset($item['Company']) ? $item['Company'] : '',
                                        'City' => isset($item['City']) ? $item['City'] : '',
                                        'state' => !empty($item['State/Province/Region']) ? $item['State/Province/Region'] : (isset($item['State']) ? $item['State'] : ''),
                                        'Gas Flow Measurement Fundamentals' => (isset($item['Mailing Address for GFMF']) or isset($item['Gas Flow Measurement Fundamentals'])) ? 'Yes' : 'No',
                                        'Continuing Education Unit (CEU)' => (isset($item['Mailing Address for CEU']) or isset($item['Continuing Education Unit (CEU)'])) ? 'Yes' : 'No',
                                        'Order Number' =>  $rec['id']                                        ];
                                    }
                                }
                            }
                        }
                    break;
                }
        }
        // echo "whats the issue?";
        // $this->dump($rows);
        // die;
        if (!empty($rows)) {
            $this->download_send_headers("School_registration_report_year_".$year."_report_" . date("Y-m-d") . ".csv");
            echo $this->array2csv($rows);
            die();
        }

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

    private function check_yes_no($in)
    {
        $keys = ['is_attendee', 'is_gas_flow', 'is_ceu', 'role_speaker', 'role_committee', 'role_exhibitor'];
        foreach ($keys as $val) {
            if (isset($in[$val])) {
                if (!in_array($in[$val], ['Yes', 'No'])) {
                    $in[$val] = 'No';
                }
            }
        }
        return $in;
    }

    public function get_orders_ids_by_product_id($product_id, $year, $order_status = array('wc-completed', 'wc-processing', 'wc-on-hold'))
    {
        global $wpdb;
        $arr = [];

        $q = "
            SELECT t_ps.id,t_psm.meta_value,t_qr.first_name as badge_fname, t_qr.last_name as badge_lname, t_qr.email as badge_email FROM {$wpdb->prefix}posts as t_ps 
            INNER JOIN {$wpdb->prefix}qr_bage_data as t_qr ON t_ps.id=t_qr.order_id
            LEFT OUTER JOIN {$wpdb->prefix}postmeta as t_psm ON t_ps.id=t_psm.post_id AND t_psm.meta_key='woocommerce_order_data'
            WHERE t_ps.post_type='shop_order' 
            AND YEAR(t_qr.date_added) = '$year'
            AND t_ps.post_status IN ( '" . implode("','", $order_status) . "' )
            GROUP BY t_qr.email, t_ps.id
            ORDER BY t_ps.id ASC
        ";

        $results = $wpdb->get_results($q, 'ARRAY_A');
        // echo "results" ;
        // $this->dump( $results );
        if (!empty($results)) {
            foreach ($results as $k => $res) {
                $arr[$k] = $res;
            }
        }

        return $arr;
    }

    public function get_years_for_export_data()
    {
        global $wpdb;

        $res = array();
        $prefix = $wpdb->prefix;
        $q = "
            SELECT DISTINCT YEAR(posts.post_date) as YEAR
            FROM {$prefix}woocommerce_order_items as order_items
            LEFT JOIN {$prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$prefix}posts AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            AND YEAR(posts.post_date) > 2022
            GROUP BY YEAR(posts.post_date)
            ORDER BY YEAR DESC
        ";

        $results = $wpdb->get_results($q, 'ARRAY_A');

        if (!empty($results)) {
            foreach ($results as $year) {
                $res[] = $year['YEAR'];
            }
        }

        return $res;
    }

    public static function is_serial($string)
    {
        return (@unserialize($string) !== false);
    }

    public static function is_json($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public static function get_item_from_meta_value($data)
    {
        if (is_array($data)) {
            $parsed = $data;
        } else if (self::is_serial($data)) {
            $parsed = unserialize(unserialize($data));
        } else if (self::is_json($data)) {
            $parsed = json_decode($data, true);
        }

        return $parsed;
    }

    public static function get_visitor_type($item)
    {

        $visitor_type = ($item['ASGMT Day Pass Registration 2019']['Name'] === 'ASGMT Day Pass Registration 2019' ? 'day_pass' : ((isset($item['Vendor Booth Space']) && $item['Vendor Booth Space']['Name'] === 'Vendor Booth Space') ? 'vendor' : 'student'));

        return $visitor_type;
    }

    public function array2csv(array &$array)
    {
        if (count($array) == 0) {
            return null;
        }
        ob_start();
        $df = fopen("php://output", 'w');

        fputs($df, "\xEF\xBB\xBF");

        fputcsv($df, array_keys(reset($array)));
        foreach ($array as $row) {
            fputcsv($df, $row);
        }
        fclose($df);
        return ob_get_clean();
    }

    public function download_send_headers($filename)
    {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header('Content-Encoding: UTF-8');
        header('Content-Type: text/csv; charset=utf-8');
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, post-check=0, pre-check=0");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }
}


// Instantiate the ExhibitorManagement class
function initialize_reports() {
    global $reports;
    $reports = new Reports();
  }
add_action('init', 'initialize_reports');