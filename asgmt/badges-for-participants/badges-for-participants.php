<?php
/**
 * Plugin Name: Badges for participants
 * Description: Additional functionality
 * Version: 1.0
 */
if (!defined('ABSPATH')) {
    exit;
}
// options
define('BADGES_MEMBERS_PER_PAGE', 75);
define('BADGES_PLUGIN_URL', plugin_dir_url(__FILE__));

class Badges_For_Participants
{
    public $member_listing;
    public $print_badges;
    public $delete_badges;
    public $member_single_page;
    public $attendance_data;
    public $attendance_data_sessions;
    public $attendance_data_session;
    public $attendance_data_socials;
    public $attendance_data_social;
    public $print_items;

    public function __construct()
    {
        global $wpdb;
        $this->includes();
        $this->load();
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdmin'));
        add_action('admin_head', array($this, 'badges_custom_styles'));
        add_action('admin_init', array($this, 'allow_access_to_badges_for_staff'));
        add_action('init', array($this, 'redirect_to_admin'));
        // add_filter( 'wp_nav_menu_items', array($this, 'filter_function_name_4792'), 10, 2 );
        add_filter('wp_nav_menu_top-menu_items', array($this, 'filter_function_name_4792'), 10, 2);
        $sql = 'SHOW TABLES LIKE \'' . $wpdb->prefix . 'qr_bage_data\'';
        $result = $wpdb->get_results($sql, 'ARRAY_A');
        if (count($result) == 0) {
            $sql = 'CREATE TABLE `' . $wpdb->prefix . 'qr_bage_data` (
`id`  bigint NOT NULL AUTO_INCREMENT ,
`order_id`  bigint NOT NULL ,
`parent_order_id`  bigint NULL ,
`is_additional`  int NOT NULL DEFAULT 0 ,
`date_added`  timestamp NOT NULL ON UPDATE CURRENT_TIMESTAMP ,
`name`  varchar(255) NOT NULL ,
`company`  varchar(255) NOT NULL ,
`email`  varchar(255) NOT NULL ,
`data`  longtext NOT NULL ,
`is_qr_print`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'Yes\' ,
`is_attendee`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`is_gas_flow`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`is_ceu`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`is_liquid`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`role_speaker`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`role_committee`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`role_board`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`role_exhibitor`  enum(\'Yes\',\'No\') NOT NULL DEFAULT \'No\' ,
`visitor_type`  enum(\'Student\',\'Vendor\',\'Day Pass\') NOT NULL DEFAULT \'Student\' ,
`is_printed`  int NOT NULL DEFAULT 0 ,
`is_checked`  int NOT NULL DEFAULT 0 ,
PRIMARY KEY (`id`),
INDEX (`order_id`) ,
INDEX (`parent_order_id`) ,
INDEX (`date_added`) ,
INDEX (`name`) ,
INDEX (`is_qr_print`) ,
INDEX (`is_attendee`) ,
INDEX (`is_gas_flow`) ,
INDEX (`is_ceu`) ,
INDEX (`role_speaker`) ,
INDEX (`role_committee`) ,
INDEX (`role_board`) ,
INDEX (`role_exhibitor`) ,
INDEX (`is_printed`) ,
INDEX (`is_checked`) ,
INDEX (`is_additional`) ,
INDEX (`visitor_type`) 
)
DEFAULT CHARACTER SET=utf8 COLLATE=utf8_general_ci
;';
            $wpdb->get_results($sql, 'ARRAY_A');
        }
    }

    public function filter_function_name_4792($items, $args)
    {
        $user = wp_get_current_user();
        if ($args->theme_location = 'top-menu' && is_user_logged_in() && in_array('staff', (array)$user->roles)) {
            $items .= '<li><a href="' . admin_url('/') . '">Admin Dashboard</a></li>';
        }
        return $items;
    }

    public function redirect_to_admin()
    {
        if (isset($_GET['c']) && !empty($_GET['c'])) {
            $decrypt = Encryption::decrypt_from_url_param($_GET['c']);
            $arr = explode(',', $decrypt);
            if (!empty($arr) && isset($arr[0]) && isset($arr[1])) {
                wp_redirect(home_url('/wp-admin/admin.php?page=member_page&user_id=' . (int)$arr[0] . '&congress_year=' . (int)$arr[1]));
            }
        }
    }

    public function get_member_listing()
    {
        return $this->member_listing;
    }

    public function includes()
    {
        require_once('inc/Encryption.php');
        require_once('inc/members/Member_Listing.php');
        require_once('inc/members/Member_Listing_Table.php');
        require_once('inc/Print_Badges.php');
        require_once('inc/Delete_Badges.php');
        require_once('inc/members/Member_Single_Page.php');
        // attendance data
        require_once('inc/attendance/Attendance_Data.php');
        require_once('inc/attendance/Attendance_Data_Listing.php');
        // print qr codes for sessions and social events
        require_once('inc/Generate_Qr.php');
        require_once('inc/Print_Items.php');
    }

    public function badges_custom_styles()
    {
        echo '<style> #toplevel_page_print_badges, #toplevel_page_member_page, #toplevel_page_session_single_page, #toplevel_page_social_single_page {display:none!important;}</style>';
    }

    public function enqueueAdmin()
    {
        $screen = get_current_screen();
        if ($screen->id !== 'toplevel_page_member_listing' &&
            $screen->id !== 'toplevel_page_print_badges' &&
            $screen->id !== 'toplevel_page_member_page' &&
            $screen->id !== 'badges_page_attendance_data' &&
            $screen->id !== 'toplevel_page_attendance_data' &&
            $screen->id !== 'attendance-data_page_attendance_data_sessions' &&
            $screen->id !== 'toplevel_page_session_single_page' &&
            $screen->id !== 'toplevel_page_social_single_page' &&
            $screen->id !== 'attendance-data_page_attendance_data_socials' &&
            $screen->id !== 'toplevel_page_print_items') {
            return;
        }
        // scripts
        // wp_enqueue_script('sumoselect', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.0.2/jquery.sumoselect.min.js', array('jquery'), time(), true);
        // wp_enqueue_script('ofi', plugins_url('assets/js/ofi.min.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('jspdf', plugins_url('assets/js/jsPDF/jspdf.min.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('jspdf-autotable', plugins_url('assets/js/jsPDF/jspdf-autotable.min.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('dom-to-image', plugins_url('assets/js/dom-to-image.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('custom-select', plugins_url('assets/js/custom-select.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('participants-jsoneditor', plugins_url('assets/js/jsoneditor.min.js', __FILE__), array('jquery'), time(), true);
        wp_enqueue_script('participants-app', plugins_url('assets/js/app.js', __FILE__), array('jquery'), time(), true);
        // styles
        // wp_enqueue_style('sumoselect', 'https://cdnjs.cloudflare.com/ajax/libs/jquery.sumoselect/3.0.2/sumoselect.min.css', null, time());
        wp_enqueue_style('custom-select', plugins_url('assets/css/custom-select.css', __FILE__), null, time());
        wp_enqueue_style('participants-styles', plugins_url('assets/css/styles.css', __FILE__), null, null);
        // wp_enqueue_style('participants-jsoneditor-styles', plugins_url('assets/css/bootstrap-combined.min.css', __FILE__), null, time());
    }

    public function load()
    {
        $this->member_listing = new Member_Listing();
        $this->print_badges = new Print_Badges();
        $this->delete_badges = new Delete_Badges();
        $this->member_single_page = new Member_Single_Page();
        // load attendance data
        $this->attendance_data = new Attendance_Data();
        // load print qr codes for sessions and social events
        $this->print_items = new Print_Items();
    }

    public function allow_access_to_badges_for_staff()
    {
        $custom_cap = 'view_badges';
        $min_cap = 'read'; // Check "Roles and objects table in codex!
        $grant = true;
        $role = 'staff';
        $admin_role = 'administrator';
        $admin_min_role = 'manage_options';
        foreach ($GLOBALS['wp_roles'] as $role_obj) {
            if (isset($role_obj[$role]) && is_object($role_obj[$role])) {
                if (!$role_obj[$role]->has_cap($custom_cap) && $role_obj[$role]->has_cap($min_cap)) {
                    $role_obj[$role]->add_cap($custom_cap, $grant);
                } else if (!$role_obj[$admin_role]->has_cap($custom_cap) && $role_obj[$admin_role]->has_cap($admin_min_role)) {
                    $role_obj[$admin_role]->add_cap($custom_cap, $grant);
                }
            }
        }
    }

    public function check_if_user_has_cap($cap, $capabilities)
    {
        return (array_key_exists($cap, $capabilities) && $capabilities[$cap] === 1);
    }

    public static function add_is_liquid_column()
    {
        global $wpdb;
        $table_qr_badge_data = "{$wpdb->prefix}qr_bage_data";
        $sql = "SHOW COLUMNS FROM $table_qr_badge_data LIKE 'is_liquid'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $sql = "ALTER TABLE $table_qr_badge_data ADD is_liquid ENUM('Yes', 'No') NOT NULL DEFAULT 'No'";
            $wpdb->query($sql);
        }
    }

    public static function add_original_first_name_column()
    {
        global $wpdb;
        $table_qr_badge_data = "{$wpdb->prefix}qr_bage_data";
        $sql = "SHOW COLUMNS FROM $table_qr_badge_data LIKE 'original_first_name'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $sql = "ALTER TABLE $table_qr_badge_data ADD original_first_name TEXT NOT NULL";
            $wpdb->query($sql);
        }
    }

    public static function add_original_last_name_column()
    {
        global $wpdb;
        $table_qr_badge_data = "{$wpdb->prefix}qr_bage_data";
        $sql = "SHOW COLUMNS FROM $table_qr_badge_data LIKE 'original_last_name'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $sql = "ALTER TABLE $table_qr_badge_data ADD original_last_name TEXT NOT NULL";
            $wpdb->query($sql);
        }
    }

    public static function add_badge_title_column()
    {
        global $wpdb;
        $table_cong_registrations = "{$wpdb->prefix}cong_registrations";
        $sql = "SHOW COLUMNS FROM $table_cong_registrations LIKE 'badge_title'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_registrations ADD badge_title TEXT");
        }
        $table_cong_regs_persons = "{$wpdb->prefix}cong_regs_persons";
        $sql = "SHOW COLUMNS FROM $table_cong_regs_persons LIKE 'badge_title'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_regs_persons ADD badge_title TEXT");
        }
    }

    public static function add_badge_color_column()
    {
        global $wpdb;
        $table_cong_registrations = "{$wpdb->prefix}cong_registrations";
        $sql = "SHOW COLUMNS FROM $table_cong_registrations LIKE 'badge_color'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_registrations ADD badge_color TEXT");
        }
        $table_cong_regs_persons = "{$wpdb->prefix}cong_regs_persons";
        $sql = "SHOW COLUMNS FROM $table_cong_regs_persons LIKE 'badge_color'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_regs_persons ADD badge_color TEXT");
        }
    }

    public static function add_printed_count_column()
    {
        global $wpdb;
        $table_cong_registrations = "{$wpdb->prefix}cong_registrations";
        $sql = "SHOW COLUMNS FROM $table_cong_registrations LIKE 'printed_count'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_registrations ADD printed_count INT");
        }
        $table_cong_regs_persons = "{$wpdb->prefix}cong_regs_persons";
        $sql = "SHOW COLUMNS FROM $table_cong_regs_persons LIKE 'printed_count'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_regs_persons ADD printed_count INT");
        }
    }

    public static function add_check_in_column()
    {
        global $wpdb;
        $table_cong_registrations = "{$wpdb->prefix}cong_registrations";
        $sql = "SHOW COLUMNS FROM $table_cong_registrations LIKE 'check_in'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_registrations ADD check_in INT");
        }
        $table_cong_regs_persons = "{$wpdb->prefix}cong_regs_persons";
        $sql = "SHOW COLUMNS FROM $table_cong_regs_persons LIKE 'check_in'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_regs_persons ADD check_in INT");
        }
    }

    public static function create_attendance_data_table()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'cong_attendance_data';
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $table_name (
              id mediumint(9) NOT NULL AUTO_INCREMENT,
              session_id INT NOT NULL,
              reg_id INT NOT NULL,
              member_id text NOT NULL,
              scan_time text NOT NULL,
              scan_date text NOT NULL,
              type text NOT NULL,
              PRIMARY KEY  (id)
            ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function add_type_column_for_attendance_data()
    {
        global $wpdb;
        $table_attendance_data = "{$wpdb->prefix}cong_attendance_data";
        $sql = "SHOW COLUMNS FROM $table_attendance_data LIKE 'type'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_attendance_data ADD type TEXT");
        }
    }

    public static function add_first_name_column_for_badge_data()
    {
        global $wpdb;
        $table_attendance_data = "{$wpdb->prefix}qr_bage_data";
        $sql = "SHOW COLUMNS FROM $table_attendance_data LIKE 'first_name'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_attendance_data ADD first_name TEXT");
        }
    }

    public static function add_last_name_column_for_badge_data()
    {
        global $wpdb;
        $table_attendance_data = "{$wpdb->prefix}qr_bage_data";
        $sql = "SHOW COLUMNS FROM $table_attendance_data LIKE 'last_name'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_attendance_data ADD last_name TEXT");
        }
    }


    public static function add_person_id_column_for_attendance_data()
    {
        global $wpdb;
        $table_attendance_data = "{$wpdb->prefix}cong_attendance_data";
        $sql = "SHOW COLUMNS FROM $table_attendance_data LIKE 'person_id'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_attendance_data ADD person_id INT");
        }
    }

    public static function add_check_in_date_column()
    {
        global $wpdb;
        $table_cong_registrations = "{$wpdb->prefix}cong_registrations";
        $sql = "SHOW COLUMNS FROM $table_cong_registrations LIKE 'check_in_date'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_registrations ADD check_in_date TEXT");
        }
        $table_cong_regs_persons = "{$wpdb->prefix}cong_regs_persons";
        $sql = "SHOW COLUMNS FROM $table_cong_regs_persons LIKE 'check_in_date'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_regs_persons ADD check_in_date TEXT");
        }
    }

    public static function add_check_in_time_column()
    {
        global $wpdb;
        $table_cong_registrations = "{$wpdb->prefix}cong_registrations";
        $sql = "SHOW COLUMNS FROM $table_cong_registrations LIKE 'check_in_time'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_registrations ADD check_in_time TEXT");
        }
        $table_cong_regs_persons = "{$wpdb->prefix}cong_regs_persons";
        $sql = "SHOW COLUMNS FROM $table_cong_regs_persons LIKE 'check_in_time'";
        $wpdb->get_results($sql);
        if ($wpdb->num_rows == 0) {
            $wpdb->query("ALTER TABLE $table_cong_regs_persons ADD check_in_time TEXT");
        }
    }
}

register_activation_hook(__FILE__, 'badges_for_participants_plugin_activated');
function badges_for_participants_plugin_activated()
{
    Badges_For_Participants::add_badge_title_column();
    Badges_For_Participants::add_badge_color_column();
    Badges_For_Participants::add_printed_count_column();
    Badges_For_Participants::add_check_in_column();
    Badges_For_Participants::create_attendance_data_table();
    Badges_For_Participants::add_type_column_for_attendance_data();
    Badges_For_Participants::add_first_name_column_for_badge_data();
    Badges_For_Participants::add_last_name_column_for_badge_data();
    Badges_For_Participants::add_person_id_column_for_attendance_data();
    Badges_For_Participants::add_check_in_date_column();
    Badges_For_Participants::add_check_in_time_column();
    Badges_For_Participants::add_is_liquid_column();
    Badges_For_Participants::add_original_first_name_column();
    Badges_For_Participants::add_original_last_name_column();
}

add_action('plugins_loaded', 'load_badges_plugin');
function load_badges_plugin()
{
    global $badges;
    $badges = new Badges_For_Participants();

    Badges_For_Participants::add_is_liquid_column();
}