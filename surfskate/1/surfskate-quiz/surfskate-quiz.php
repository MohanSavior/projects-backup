<?php
/**
 * Plugin Name:       Surfskate Selector
 * Plugin URI:        https://surfskate.love/
 * Description:       Surfskate Selector
 * Version:           1.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Savior Marketing LLC
 * Author URI:        https://savior.im/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Update URI:        https://example.com/my-plugin/
 * Text Domain:       surfsket-quiz
 */

class SurfSkateSelector
{

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * The array of templates that this plugin tracks.
     */
    protected $templates;

    /**
     * Plugin URL.
     */
    public $plugin_url = '';
    /**
     * Plugin path.
     */
    public $plugin_path = '';

    /**
     * $_COOKIE.
     */
    public $user_email = '';

    /**
     * Returns an instance of this class.
     */
    public static function get_instance()
    {

        if (null == self::$instance) {
            self::$instance = new SurfSkateSelector();
        }

        return self::$instance;

    }

    /**
     * Initializes the plugin by setting filters and administration functions.
     */
    private function __construct()
    {
        $this->plugin_url = plugins_url('/', __FILE__);
        $this->plugin_path = plugin_dir_path(__FILE__);
        $this->templates = array();
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 20);
        add_action('wp_ajax_filter_surfsket', array($this, 'filter_surfsket'));
        add_action('wp_ajax_nopriv_filter_surfsket', array($this, 'filter_surfsket'));
        //Information send gravity form
        add_action('wp_ajax_information', array($this, 'information'));
        add_action('wp_ajax_nopriv_information', array($this, 'information'));

        // Add a filter to the attributes metabox to inject template into the cache.
        if (version_compare(floatval(get_bloginfo('version')), '4.7', '<')) {
            // 4.6 and older
            add_filter(
                'page_attributes_dropdown_pages_args',
                array($this, 'register_project_templates')
            );
        } else {
            // Add a filter to the wp 4.7 version attributes metabox
            add_filter(
                'theme_page_templates', array($this, 'add_new_template')
            );
        }

        // Add a filter to the save post to inject out template into the page cache
        add_filter(
            'wp_insert_post_data',
            array($this, 'register_project_templates')
        );

        // Add a filter to the template include to determine if the page has our
        // template assigned and return it's path
        add_filter(
            'template_include',
            array($this, 'view_project_template')
        );

        // Add your templates to this array.
        $this->templates = array(
            'surfsketquiz-template.php' => 'SurfSket Quiz',
            'surfsketquiz-template-v2.php' => 'SurfSket Variation 2',
            'surfsketquiz-template-v1.php' => 'SurfSket Variation 1',
        );

        $this->user_email = isset($_COOKIE['user_email']) ? $_COOKIE['user_email'] : false;
    }

    /**
     * Enqueue Scripts
     *
     */
    public function enqueue_scripts()
    {
        if (is_page(3780) || is_page(9122) || is_page(9124)) {
            wp_enqueue_script('surfsket-bootstrap-js', '//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js', array(), time(), true);
            wp_enqueue_script('surfsket-bootstrap-select-js', '//cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/js/bootstrap-select.min.js', array(), time(), true);
            wp_enqueue_style('surfsket-bootstrap-min-css', '//cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css', '', time(), 'all');
            wp_enqueue_style('surfsket-animate-min-css', '//cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', '', time(), 'all');
            wp_enqueue_style('surfsket-bootstrap-icons-css', '//cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css', '', time(), 'all');
            wp_enqueue_style('surfsket-select-min-css', '//cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta2/dist/css/bootstrap-select.min.css', '', time(), 'all');

            wp_enqueue_script('surfsket-selector-js', $this->plugin_url . 'assets/js/surfskate-selector.js', array('jquery'), time(), true);
            wp_localize_script('surfsket-selector-js', 'surfsket_ajax_object',
                array(
                    'ajax_url'      => admin_url('admin-ajax.php'),
                    'total_post'    => wp_count_posts('surfskate'),
                    'ajax_nonce'    => wp_create_nonce('ajax-nonce'),
                    'current_email' => $this->user_email,
                    'current_ip'    => $this->check_opt_in_email_exsist(),
                ));
            wp_enqueue_style('surfsket-selector-css', $this->plugin_url . 'assets/css/surfskate-selector.css', '', time(), 'all');
        }
    }

    public function check_opt_in_email_exsist()
    {        
        try {
            $response = wp_remote_get( 'http://ip-api.com/json', array(
                'headers' => array(
                    'Accept' => 'application/json',
                )
            ) );
            if ( ( !is_wp_error($response)) && (200 === wp_remote_retrieve_response_code( $response ) ) ) {
                $responseBody = json_decode($response['body']);
                if( json_last_error() === JSON_ERROR_NONE ) {
                    //Do your thing.
                }
                return $responseBody->query;
            }
        } catch( Exception $ex ) {
            //Handle Exception.
        }
    }

    public function get_term_taxonomy($taxonomy)
    {
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
        ]);
        return $terms;
    }

    /**
     * Information
     */
    public function information()
    {
        if (!wp_verify_nonce($_POST['nonce'], 'ajax-nonce')) {
            wp_send_json_error();
        }
        if (empty($_POST['fullname']) && empty($_POST['useremail'])) {
            wp_send_json_error(array('error' => 'Invalid user information'));
        }
        $form_id = 18;
        $input_values = array();
        $input_values['input_4_3'] = $_POST['firstname'];
        $input_values['input_4_6'] = $_POST['lastname'];
        $input_values['input_3'] = $_POST['useremail'];
        if (isset($_POST['entry_id']) && !empty($_POST['entry_id'])) {
            wp_send_json_success(array('entry_id' => $_POST['entry_id']));
        }
        $result = GFAPI::submit_form($form_id, $input_values);
        if (is_wp_error($result)) {
            $error_message = $result->get_error_message();
            wp_send_json_error(array('error' => '(): GFAPI Error Message => ' . $error_message));
        }

        if (!rgar($result, 'is_valid')) {
            $error_message = 'Submission is invalid.';
            $field_errors = rgar($result, 'validation_messages', array());
            wp_send_json_error($field_errors);
        }

        if (rgar($result, 'confirmation_type') === 'redirect') {
            $redirect_url = rgar($result, 'confirmation_redirect');
            // error_log(print_r('setcookie confirmation_type', true));
            // setcookie('user_email', $_POST['useremail']);
            wp_send_json_success($result);
        } else {
            // error_log(print_r('setcookie else', true));
            // setcookie('user_email', $_POST['useremail']);
            $arr_cookie_options = array(
                'expires' => time() + 60 * 60 * 24 * 365,
                'path' => '/',
                'domain' => '.surfskate.love', // leading dot for compatibility or use subdomain
                'secure' => true, // or false
                'httponly' => true, // or false
                'samesite' => 'None', // None || Lax  || Strict
            );
            setcookie('user_email', $_POST['useremail'], $arr_cookie_options);
            $confirmation_message = rgar($result, 'confirmation_message');
            wp_send_json_success($result);
            // error_log(print_r($confirmation_message,true));
        }
        // error_log(print_r($_COOKIE, true));
    }
    /**
     * Ajax filter
     */
    public function filter_surfsket()
    {
        extract($_POST);
        $surfsket_query_args = [
            'post_type' => 'surfskate', //surfskate
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                'relation' => 'AND',
                'wheelbase_asc' => [
                    'key' => 'wheelbase',
                ],
                'length_asc' => [
                    'key' => 'length',
                ],
            ],
            'orderby' => [
                'wheelbase_asc' => 'ASC',
                'length_asc' => 'ASC',
                'title' => 'ASC',
            ],
        ];
        if (isset($surfskate_to_feel)) {
            if ($surfskate_to_feel == 'novelty_longboard' && $stance_width >= 16) {
                $surfsket_query_args['meta_query'] = [
                    [
                        'key' => 'surfskate_longboard',
                        'value' => 1,
                        'compare' => 'BOOLEAN',
                    ],
                ];
            }
        }
        (int) $stance_width_low = (int) $stance_width - 1;
        (int) $stance_width_high = (int) $stance_width + 1;

        $response_data = [];
        $final_data = [];
        $surfsket_query = new WP_Query($surfsket_query_args);
        $questions_no = [];
        $max_sum = 1;
        $post_count = 0;
        $checkData = false;
        if ($surfsket_query->have_posts()) {
            $checkData = true;
            while ($surfsket_query->have_posts()) {
                $surfsket_query->the_post();
                $get_brands = get_the_terms(get_the_ID(), 'brands');

                $stanceWidthLow = (int) get_post_meta(get_the_ID(), 'stance_width_low', true);
                $stanceWidthHigh = (int) get_post_meta(get_the_ID(), 'stance_width_high', true);
                //Question NO 1 ==================================================
                $questions_no[1] = ($stanceWidthLow <= $stance_width && $stanceWidthHigh >= $stance_width) ? 1 : 0;
                //Question NO 2 ==================================================
                if (isset($required_skill_level)) {
                    $skill = get_post_meta(get_the_ID(), 'required_skill_level', true);
                    if ($required_skill_level == 'advanced') {
                        $questions_no[2] = ($skill == 'advanced' || $skill == 'intermediate' || $skill == 'beginner') ? 1 : 0;
                    } elseif ($required_skill_level == 'intermediate') {
                        $questions_no[2] = ($skill == 'intermediate' || $skill == 'beginner') ? 1 : 0;
                    } else {
                        $questions_no[2] = ($skill == 'beginner') ? 1 : 0;
                    }
                }
                //Question NO 3 ==================================================
                if (isset($price)) {
                    $questions_no[3] = ((int) get_post_meta(get_the_ID(), 'price', true) <= $price) ? 1 : 0;
                }
                //Question NO 4 ==================================================
                if (isset($purpose)) {
                    $questions_no[4] = in_array($purpose, get_post_meta(get_the_ID(), 'purpose', true)) ? 1 : 0;
                }
                //Question NO 5 ==================================================
                if (isset($riding_style)) {
                    $questions_no[5] = in_array($riding_style, get_post_meta(get_the_ID(), 'riding_style', true)) ? 1 : 0;
                }
                //Question NO 6 ==================================================
                if (isset($surfskate_to_feel)) {
                    $adjustedWheelbase = get_post_meta(get_the_ID(), 'adjusted_wheelbase', true);
                    if ($surfskate_to_feel == 'performance_sportcar') {
                        $questions_no[6] = ($adjustedWheelbase < $stance_width_low || $adjustedWheelbase > $stance_width) ? 0 : 1;
                    } elseif ($surfskate_to_feel == 'cruising_sedan') {
                        $questions_no[6] = ($adjustedWheelbase < $stance_width || $adjustedWheelbase > $stance_width_high) ? 0 : 1;
                    } else {
                        $questions_no[6] = ($adjustedWheelbase < $stance_width_high) ? 0 : 1;
                    }
                }
                //Question NO 7 ==================================================
                if (isset($truck_feel)) {
                    $truckFeel = get_post_meta(get_the_ID(), 'truck_feel', true);
                    $questions_no[7] = in_array($truck_feel, $truckFeel) ? 1 : 0;
                }
                //Question NO 8 ==================================================
                if (isset($riding_distance)) {
                    $ridingDistance = get_post_meta(get_the_ID(), 'riding_distance', true);
                    $questions_no[8] = in_array($riding_distance, $ridingDistance) ? 1 : 0;
                }

                if ((int) array_sum($questions_no) >= (int) $max_sum) {
                    $max_sum = (int) array_sum($questions_no);
                    if ($final) {
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'full');
                        $response_data[$get_brands[0]->term_id][] = [
                            'term_id' => $get_brands[0]->term_id,
                            'name' => get_the_title(),
                            'post_image' => $image[0],
                            'url' => get_permalink(),
                            'title' => get_the_title(),
                            'brand' => $get_brands[0]->name,
                            'price' => get_post_meta(get_the_ID(), 'price', true),
                            'content' => get_the_content(),
                            'length' => get_post_meta(get_the_ID(), 'length', true) . '"',
                            'wheelbase' => get_post_meta(get_the_ID(), 'wheelbase', true),
                            'stance_width_range' => $stanceWidthLow . '"-' . $stanceWidthHigh . '"',
                            'width' => get_post_meta(get_the_ID(), 'width', true) . '"',
                            'concave' => get_post_meta(get_the_ID(), 'concave', true),
                            'questions' => $questions_no,
                            'sum' => array_sum($questions_no),
                        ];

                    } else {
                        $image = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'full');
                        $response_data[$get_brands[0]->term_id][] = array(
                            'term_id' => $get_brands[0]->term_id,
                            'name' => get_the_title(),
                            'post_image' => $image[0],
                            'url' => get_permalink(),
                            'questions' => $questions_no,
                            'sum' => array_sum($questions_no),
                        );
                    }
                }
            }
        } else {
            $checkData = false;
        }
        wp_reset_postdata();
        if ($checkData) {
            $brand_objects = $this->get_term_taxonomy('brands');
            $brand_data_with_post = [];
            $data_by_count_max = [];
            if (!empty($brand_objects)) {
                foreach ($response_data as $term_id => $brand_object) {
                    foreach ($brand_object as $key => $val1) {
                        if ($val1['sum'] !== $max_sum) {
                            unset($response_data[$term_id][$key]);
                        } else {
                            $data_by_count_max[$val1['term_id']][] = $val1;
                            $post_count++;
                        }
                    }
                    if (!empty($response_data[$term_id])) {
                        $image = get_field('select_image', 'brands_' . $term_id);
                        $size = 'thumbnail'; // (thumbnail, medium, large, full or custom size)
                        $brand_data_with_post[$term_id] = [
                            'brand_name' => get_term($term_id)->name,
                            'brand_id' => $term_id,
                            'brand_image' => $image,
                            'post_data' => $data_by_count_max[$term_id],
                            'max_sum' => $max_sum,
                        ];
                    }
                }
            }

            usort($brand_data_with_post, [$this, 'compareByName']);
            wp_send_json_success(array('data' => $brand_data_with_post, 'post_count' => $post_count));
        } else {
            wp_send_json_error();
        }
    }

    public function compareByName($a, $b)
    {
        return strcmp($a["brand_name"], $b["brand_name"]);
    }
    /**
     * Adds our template to the page dropdown for v4.7+
     *
     */
    public function add_new_template($posts_templates)
    {
        $posts_templates = array_merge($posts_templates, $this->templates);
        return $posts_templates;
    }

    /**
     * Adds our template to the pages cache in order to trick WordPress
     * into thinking the template file exists where it doens't really exist.
     */
    public function register_project_templates($atts)
    {

        // Create the key used for the themes cache
        $cache_key = 'page_templates-' . md5(get_theme_root() . '/' . get_stylesheet());

        // Retrieve the cache list.
        // If it doesn't exist, or it's empty prepare an array
        $templates = wp_get_theme()->get_page_templates();
        if (empty($templates)) {
            $templates = array();
        }

        // New cache, therefore remove the old one
        wp_cache_delete($cache_key, 'themes');

        // Now add our template to the list of templates by merging our templates
        // with the existing templates array from the cache.
        $templates = array_merge($templates, $this->templates);

        // Add the modified cache to allow WordPress to pick it up for listing
        // available templates
        wp_cache_add($cache_key, $templates, 'themes', 1800);

        return $atts;

    }

    /**
     * Checks if the template is assigned to the page
     */
    public function view_project_template($template)
    {

        // Get global post
        global $post;

        // Return template if post is empty
        if (!$post) {
            return $template;
        }

        // Return default template if we don't have a custom one defined
        if (!isset($this->templates[get_post_meta(
            $post->ID, '_wp_page_template', true
        )])) {
            return $template;
        }

        $file = plugin_dir_path(__FILE__) . get_post_meta(
            $post->ID, '_wp_page_template', true
        );

        // Just to be safe, we check if the file exist first
        if (file_exists($file)) {
            return $file;
        } else {
            echo $file;
        }

        // Return template
        return $template;

    }

}
add_action('plugins_loaded', array('SurfSkateSelector', 'get_instance'));
