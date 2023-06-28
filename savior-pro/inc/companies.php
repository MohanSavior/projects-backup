<?php
class Companies{

    public function __construct() {
        if(is_admin())
        {
            add_filter('acf/load_field/name=company_country', array( $this, 'acf_load_company_country_field_choices'));
            add_action( 'gform_advancedpostcreation_post_after_creation', array( $this, 'update_company_information'), 10, 4 );

        }

    }

    public function acf_load_company_country_field_choices( $field ) 
    {
        $countries = GF_Fields::get( 'address' )->get_default_countries();
        asort( $countries );
        $new_countries = array();
        foreach ( $countries as $country ) {
            $code                   = GF_Fields::get( 'address' )->get_country_code( $country );
            $new_countries[ $code ] = $country;
        }
        $field['choices'] = $new_countries;
        return $field;
    }
    /*
     *
     */
    public function update_company_information( $post_id, $feed, $entry, $form ) 
    {
        if( !empty($entry) && $entry['form_id'] == 18 )
        {
            $primary_booth_admin_id = $this->check_user_by_email($entry[13]);
            $payment_history = array();
            if($primary_booth_admin_id)
            {
                $payment_history[] = $primary_booth_admin_id;
                update_user_meta($primary_booth_admin_id, 'first_name', $entry['10.3']);
                update_user_meta($primary_booth_admin_id, 'last_name', $entry['10.6']);
                update_user_meta($primary_booth_admin_id, 'billing_phone', $entry[15]);
                $u = new WP_User( $primary_booth_admin_id );
                // Add role
                $u->add_role( 'exhibitpending' );
                //Post Update
                update_post_meta($post_id, 'primary_booth_admin', $primary_booth_admin_id);
                
            }
            $alternate_booth_admin_id = $this->check_user_by_email($entry[27]);
            if($alternate_booth_admin_id)
            {
                $payment_history[] = $alternate_booth_admin_id;
                update_user_meta($alternate_booth_admin_id, 'first_name', $entry['10.3']);
                update_user_meta($alternate_booth_admin_id, 'last_name', $entry['10.6']);
                update_user_meta($alternate_booth_admin_id, 'billing_phone', $entry[15]);
                //Post Update
                update_post_meta($post_id, 'alternate_booth_admin', $alternate_booth_admin_id);
            }            
            if(!empty($payment_history))
            {
                update_post_meta($post_id, 'payment_history', $payment_history);
            }
        }
    }

    public function check_user_by_email( $email )
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
}
new Companies();