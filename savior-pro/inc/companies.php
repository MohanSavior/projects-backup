<?php
class Companies
{

    public function __construct()
    {
        if (is_admin()) {
            add_filter('acf/load_field/name=company_country', array($this, 'acf_load_company_country_field_choices'));
            add_action('gform_advancedpostcreation_post_after_creation', array($this, 'create_company_information'), 10, 4);
            if (isset($_REQUEST['company_id'])) {
                add_filter('gform_pre_render_19', array($this, 'prepare_company_information'), 100, 1);
                add_action('gform_after_submission_19', array($this, 'update_company_information'), 10, 2);
            }
        }
    }

    public function acf_load_company_country_field_choices($field)
    {
        $countries = GF_Fields::get('address')->get_default_countries();
        asort($countries);
        $new_countries = array();
        foreach ($countries as $country) {
            $code                   = GF_Fields::get('address')->get_country_code($country);
            $new_countries[$code] = $country;
        }
        $field['choices'] = $new_countries;
        return $field;
    }

    public function create_company_information($post_id, $feed, $entry, $form)
    {
        if (!empty($entry) && $entry['form_id'] == 18) {
            $primary_booth_admin_id = $this->check_user_by_email($entry[13]);
            $payment_history = array();
            if ($primary_booth_admin_id) {
                $payment_history[] = $primary_booth_admin_id;
                update_user_meta($primary_booth_admin_id, 'first_name', $entry['10.3']);
                update_user_meta($primary_booth_admin_id, 'last_name', $entry['10.6']);
                update_user_meta($primary_booth_admin_id, 'billing_phone', $entry[15]);
                $u = new WP_User($primary_booth_admin_id);
                // Add role
                $u->add_role('exhibitpending');
                //Post Update
                update_post_meta($post_id, 'primary_booth_admin', $primary_booth_admin_id);
            }
            $alternate_booth_admin_id = $this->check_user_by_email($entry[27]);
            if ($alternate_booth_admin_id) {
                $payment_history[] = $alternate_booth_admin_id;
                update_user_meta($alternate_booth_admin_id, 'first_name', $entry['25.3']);
                update_user_meta($alternate_booth_admin_id, 'last_name', $entry['25.6']);
                update_user_meta($alternate_booth_admin_id, 'billing_phone', $entry[32]);
                //Post Update
                update_post_meta($post_id, 'alternate_booth_admin', $alternate_booth_admin_id);
            }
            if (!empty($payment_history)) {
                update_post_meta($post_id, 'payment_history', $payment_history);
            }
        }
    }

    public function check_user_by_email($email)
    {
        $user_id = email_exists($email);
        if ($user_id) {
            return $user_id;
        } else {
            $random_password = wp_generate_password(12, false);
            $user_id = wp_create_user($email, $random_password, $email);
            if (!is_wp_error($user_id)) {
                return $user_id;
            } else {
                return false;
            }
        }
    }

    public function prepare_company_information($form)
    {
        if ($form['id'] != 19) {
            return $form;
        }
        $post_id = $_REQUEST['company_id'];
        if ($this->is_custom_post_id($post_id)) {
            $primary_booth_admin    = get_user_by('id', get_post_meta($post_id, 'primary_booth_admin', true));
            $alternate_booth_admin  = get_user_by('id', get_post_meta($post_id, 'alternate_booth_admin', true));
            foreach ($form['fields'] as &$field) {
                //Company name:
                if ($field->id == 7) {
                    $field->defaultValue = get_post_meta($post_id, 'user_employer', true);
                }
                //Company Type:
                if ($field['id'] == 45) {
                    foreach ($field->choices as &$choice) {
                        if ($choice['value'] == get_post_meta($post_id, 'type_of_the_company', true)) {
                            $choice['isSelected'] = true;
                        } else {
                            $choice['isSelected'] = false;
                        }
                    }
                }
                //Date Approved on Exhibitors List:
                if ($field->id == 35) {
                    $field->defaultValue = !empty(get_post_meta($post_id, 'date_approved_on_exhibitors_list', true)) ? date("m-d-Y", strtotime(get_post_meta($post_id, 'date_approved_on_exhibitors_list', true))) : '';
                }
                //Company Address:
                if ($field->id == 4) {
                    $pre_render_values = array(
                        '4.1' => get_post_meta($post_id, 'company_address', true),
                        '4.3' => get_post_meta($post_id, 'company_city', true),
                        '4.4' => get_post_meta($post_id, 'company_state', true),
                        '4.5' => get_post_meta($post_id, 'company_postcode', true),
                        '4.6' => get_post_meta($post_id, 'company_country', true)
                    );
                    foreach ($field->inputs as &$input) {
                        if (isset($pre_render_values[$input['id']])) {
                            $input['defaultValue'] = $pre_render_values[$input['id']];
                        }
                    }
                }
                //Primary Booth Admin first name and last name
                if ($field->id == 10) {
                    if (!empty($primary_booth_admin)) {
                        foreach ($field->inputs as &$input) {
                            if ($input['id'] == $field['id'] . '.3') {
                                $input['defaultValue'] = $primary_booth_admin->first_name;
                            } elseif ($input['id'] == $field['id'] . '.6') {
                                $input['defaultValue'] = $primary_booth_admin->last_name;
                            }
                        }
                    }
                }
                //Primary Booth Admin email address
                if ($field->id == 13) {
                    $field->defaultValue = $primary_booth_admin->user_email;
                }
                //Primary Booth Admin contact
                if ($field->id == 15) {
                    //primary_booth_admin_contact
                    $primary_contact = !empty(get_user_meta(get_post_meta($post_id, 'primary_booth_admin', true), 'billing_phone', true)) ? get_user_meta(get_post_meta($post_id, 'primary_booth_admin', true), 'billing_phone', true) : get_user_meta(get_post_meta($post_id, 'primary_booth_admin', true), 'primary_booth_admin_contact', true);
                    $field->defaultValue = $primary_contact;
                }

                //Alternate Booth Admin first name and last name
                if ($field->id == 39) {
                    if (!empty($alternate_booth_admin)) {
                        foreach ($field->inputs as &$input) {
                            if ($input['id'] == $field['id'] . '.3') {
                                $input['defaultValue'] = $alternate_booth_admin->first_name;
                            } elseif ($input['id'] == $field['id'] . '.6') {
                                $input['defaultValue'] = $alternate_booth_admin->last_name;
                            }
                        }
                    }
                }
                //Alternate Booth Admin email address
                if ($field->id == 40) {
                    $field->defaultValue = isset($alternate_booth_admin) && !empty($alternate_booth_admin) ? $alternate_booth_admin->user_email : '';
                }
                //Alternate Booth Admin contact
                if ($field->id == 41) {
                    $field->defaultValue = get_user_meta(get_post_meta($post_id, 'alternate_booth_admin', true), 'billing_phone', true);
                }
            }
        }
        return $form;
    }

    public function is_custom_post_id($post_id)
    {
        $post_type = get_post_type($post_id);
        if ($post_type === 'companies') {
            return true;
        }
        return false;
    }

    public function update_company_information($entry, $form)
    {
        $post_id = $entry[31] ? $entry[31] : $_POST['input_31']; // Replace with your post ID
        if (!$primary_booth_admin_id = email_exists($_POST['input_13'])) {
            $primary_booth_admin_id = $this->check_user_by_email($_POST['input_13']);
        }
        update_user_meta($primary_booth_admin_id, 'first_name', $entry['10.3']);
        update_user_meta($primary_booth_admin_id, 'last_name', $entry['10.6']);
        update_user_meta($primary_booth_admin_id, 'billing_phone', $entry[15]);
        if (!$alternate_booth_admin_id = email_exists($_POST['input_40'])) {
            $alternate_booth_admin_id = $this->check_user_by_email($_POST['input_40']);
        }
        update_user_meta($alternate_booth_admin_id, 'first_name', $entry['39.3']);
        update_user_meta($alternate_booth_admin_id, 'last_name', $entry['39.6']);
        update_user_meta($alternate_booth_admin_id, 'billing_phone', $entry[41]);
        $payment_history = get_post_meta($post_id, 'payment_history', true) ? get_post_meta($post_id, 'payment_history', true) : array();
        $is_primary_booth_admin = isset($_POST['is_primary']) && $_POST['is_primary'] == 'alternate' ? $alternate_booth_admin_id : $primary_booth_admin_id;
        $custom_fields = array(
            'user_employer'                     => $_POST['input_7'],
            'company_address'                   => $_POST['input_4_1'],
            'company_city'                      => $_POST['input_4_3'],
            'company_state'                     => $_POST['input_4_4'],
            'company_postcode'                  => $_POST['input_4_5'],
            'company_country'                   => $_POST['input_4_6'],
            'type_of_the_company'               => $_POST['input_45'],
            'date_approved_on_exhibitors_list'  => !empty($_POST['input_35']) ? date('Y-m-d', strtotime($_POST['input_35'])) : '',
            'is_primary_booth_admin'            => $is_primary_booth_admin,
            'payment_history'                   => array_unique(array_merge($payment_history, array($primary_booth_admin_id, $alternate_booth_admin_id))),
            'primary_booth_admin'               => $is_primary_booth_admin == $primary_booth_admin_id ? $primary_booth_admin_id : $alternate_booth_admin_id,
            'alternate_booth_admin'             => $is_primary_booth_admin == $primary_booth_admin_id ? $alternate_booth_admin_id : $primary_booth_admin_id,
        );

        // Update the post
        $post_data = array(
            'ID'           => $post_id,
            'meta_input'   => $custom_fields,
        );

        $result = wp_update_post($post_data);

        if (is_wp_error($result)) {
            error_log(print_r('Post not updated', true));
            error_log(print_r($result->get_error_message(), true));
        }
    }
}
new Companies();
