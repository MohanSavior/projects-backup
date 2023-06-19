<?php
class CreateAttendees
{
    public function __construct()
    {
        add_filter('woocommerce_checkout_update_order_meta', array($this, 'save_gravity_form_entry_id'), 10, 2);
        // add_filter('woocommerce_checkout_fields', array($this, 'add_gravity_form_entry_id_field'));
        add_filter('woocommerce_payment_complete', array($this, 'payment_complete'));
        add_action( 'woocommerce_order_status_completed', array( $this, 'payment_complete' ) );//order_status_completed
        add_action('woocommerce_email_order_meta', array($this, 'add_attendees_information_in_email_order_meta'), 10, 3);
        //Add Attendees via Dashboard
        add_filter( 'gform_form_post_get_meta_13', array($this, 'show_attendees_repeater_fields') );
        // Remove the field before the form is saved. Adjust your form ID
        add_filter( 'gform_form_update_meta_13', array($this, 'remove_attendees_repeater_fields'), 10, 3 );
        // After Submit attendees form
        add_action( 'gform_after_submission_13', array($this, 'attendees_after_submission'), 100, 2 );
        add_shortcode( 'add_gravity_form_entry_id_field', array($this, 'add_gravity_form_entry_id_field'));
    }

    public function save_gravity_form_entry_id($order_id, $posted)
    {
        if (isset($_POST['_gravity_form_entry_id'])) {
            $entry_id = $_POST['_gravity_form_entry_id'];
            update_post_meta($order_id, '_gravity_form_entry_id', $entry_id);
        }
    }

    public function add_gravity_form_entry_id_field()
    {
        $entry_id = isset($_SESSION['entry_id']) ? $_SESSION['entry_id'] : (isset($_REQUEST['entry_id']) ? $_REQUEST['entry_id'] : '');
        return $entry_id;
    }

    public function payment_complete($order_id)
    {
        if (isset($_SESSION['entry_id'])) {
            unset($_SESSION['entry_id']);
        }
        $product_id_with_role = array(
            '18783' => 'student',
            '22100' => 'student',
            '22101' => 'virtuallmfstudent',
            '22102' => 'virtualgmfstudent',
            '18784' => 'daypass'
        );
        $main_user_id = get_post_meta($order_id, '_customer_user', true);
        $main_user = new WP_User($main_user_id); 
        $gravity_form_entry_id = get_post_meta($order_id, '_gravity_form_entry_id', true);
        if (!empty($gravity_form_entry_id)) {
            $entry = GFAPI::get_entry($gravity_form_entry_id);

            if (!empty($entry)) {
                //Add attendees with school registration
                if($entry['form_id']  == 11 ){
                    update_post_meta($order_id, '_gravity_entry_data', json_encode($entry));
                    if ($main_user) {
                        if (isset($entry['1027'])) {
                            $main_user_role = $product_id_with_role[$entry['1027']];
                                    
                            if($entry['1027'] !== 22101 || $entry['1027'] !== 22102){
                                $main_user->add_role($main_user_role);
                            }
                            if ( $entry['1027'] == 22101) {
                                if($entry['1006'])
                                {
                                    $course_type_role = $entry['1006'] == 'in_person' ? 'lmf_in_person' : 'lmf_virtual';
                                    $main_user->add_role($course_type_role);
                                }
                            }
                            if ($entry['1027'] == 22102 ) {
                                if($entry['1006'])
                                {
                                    $course_type_role = $entry['1006'] == 'in_person' ? 'gmf_in_person' : 'gmf_virtual';
                                    $main_user->add_role($course_type_role);
                                }
                            }
                            update_user_meta($main_user_id, 'special_role', array($entry['14.2'],$entry['14.1']));
                        }
                    }
                    if (isset($entry['1034']) && $entry['1034'] == 'yes') {
                        $repeater_field_attendees = $entry['1000'];
                        $_attendees_order_meta = array();
                        $attendees_users= array();
                        $random_password = wp_generate_password(12, false);
                        foreach ($repeater_field_attendees as $attendees_data) {
                            if (!empty($attendees_data['1001'])) {
                                $attendee_user_id = $this->get_or_create_user_by_email( $attendees_data['1001'], $random_password );
                                if (is_wp_error($attendee_user_id)) {
                                } else {
                                    $role = $product_id_with_role[$attendees_data['1005']]; // replace with the role you want to assign
                                    $user = new WP_User($attendee_user_id); 
                                    if ($user) {
                                        if($attendees_data['1005'] !== 22101 || $attendees_data['1005'] !== 22102){
                                            $user->add_role($role);
                                        }
                                        if ( $attendees_data['1005'] == 22101) {
                                            if($attendees_data['1028'])
                                            {
                                                $course_type_role = $attendees_data['1028'] == 'in_person' ? 'lmf_in_person' : 'lmf_virtual';
                                                $user->add_role($course_type_role);
                                            }
                                        }
                                        if ($attendees_data['1005'] == 22102 ) {
                                            if($attendees_data['1028'])
                                            {
                                                $course_type_role = $attendees_data['1028'] == 'in_person' ? 'gmf_in_person' : 'gmf_virtual';
                                                $user->add_role($course_type_role);
                                            }
                                        }
                                        update_user_meta($main_user_id, 'special_role', array($attendees_data['1029.2'],$attendees_data['1029.1']));
                                        update_user_meta($attendee_user_id, 'first_name', $attendees_data['1002']);
                                        update_user_meta($attendee_user_id, 'last_name', $attendees_data['1003']);
                                    }
                                    $_attendees_order_meta[] = array('user_id' => $attendee_user_id, 'product_id' => $attendees_data['1005'], 'roles' => $user->roles);
                                    $attendees_users[] = array('email' => $attendees_data['1001'], 'password' => $random_password);
                                    $this->send_email_to_attendees(array('email' => $attendees_data['1001'], 'password' => $random_password), $main_user->user_email);
                                }
                            }
                        }
                        if (!empty($_attendees_order_meta)) {
                            update_post_meta($order_id, '_attendees_order_meta', $_attendees_order_meta);
                            $this->send_attendees_users_email($attendees_users, $main_user->user_email);
                        }
                    }
                }

                if($entry['form_id'] == 13)
                {
                    $repeater_field_attendees = $entry['1000'];
                    $_attendees_order_meta = array();
                    $attendees_users= array();
                    $main_user = get_user_by('id', $entry['created_by']);//
                    $random_password = wp_generate_password(12, false);
                    foreach ($repeater_field_attendees as $attendees_data) {
                        if (!empty($attendees_data['1001'])) {
                            $email_exists_attendee = email_exists($attendees_data['1001']);
                            $attendee_user_id = $this->get_or_create_user_by_email( $attendees_data['1001'], $random_password );
                            if (is_wp_error($attendee_user_id)) {
                            } else {
                                $role = $product_id_with_role[$attendees_data['1005']]; // replace with the role you want to assign
                                // $user = get_user_by('id', $attendee_user_id);
                                $user = new WP_User($attendee_user_id); 
                                if ($user) {
                                    if($attendees_data['1005'] !== 22101 || $attendees_data['1005'] !== 22102){
                                        $user->add_role($role);
                                    }
                                    if ( $attendees_data['1005'] == 22101 ) {
                                        if($attendees_data['1028'])
                                        {
                                            $course_type_role = $attendees_data['1028'] == 'in_person' ? 'lmf_in_person' : 'lmf_virtual';
                                            $user->add_role($course_type_role);
                                        }
                                    }
                                    if ( $attendees_data['1005'] == 22102 ) {
                                        if($attendees_data['1028'])
                                        {
                                            $course_type_role = $attendees_data['1028'] == 'in_person' ? 'gmf_in_person' : 'gmf_virtual';
                                            $user->add_role($course_type_role);
                                        }
                                    }
                                    update_user_meta($main_user_id, 'special_role', array($attendees_data['1029.2'],$attendees_data['1029.1']));
                                    update_user_meta($attendee_user_id, 'first_name', $attendees_data['1002']);
                                    update_user_meta($attendee_user_id, 'last_name', $attendees_data['1003']);
                                }
                                $_attendees_order_meta[] = array('user_id' => $attendee_user_id, 'product_id' => $attendees_data['1005'], 'roles' => $user->roles);
                                $attendees_users[] = array('email' => $attendees_data['1001'], 'password' => $random_password);
                                if(!$email_exists_attendee){
                                    $this->send_email_to_attendees(array('email' => $attendees_data['1001'], 'password' => $random_password), $main_user->user_email);                                
                                }
                            }
                        }
                    }
                    if (!empty($_attendees_order_meta)) {
                        update_post_meta($order_id, '_attendees_order_meta', $_attendees_order_meta);
                        $this->send_attendees_users_email($attendees_users, $main_user->user_email);
                    }
                }
            }
        }
    }

    public function get_or_create_user_by_email( $email, $random_password = 0 )
    {
        $user_id = email_exists($email);

        if ($user_id) {
            // User already exists, return user ID
            return $user_id;
        } else {
            $random_password = $random_password ? $random_password : wp_generate_password(12, false);
            // User doesn't exist, create new user and return user ID
            $user_id = wp_create_user($email, $random_password, $email);
            return $user_id;
        }
    }

    public function order_status_completed($order_id)
    {
        // Do something when order status is completed
    }

    public function add_attendees_information_in_email_order_meta($order, $sent_to_admin, $plain_text)
    {
        // this order meta checks if order is marked as a entry
        $_gravity_form_entry_id = $order->get_meta('_gravity_form_entry_id');

        // we won't display anything if it is not a entry id
        if (empty($_gravity_form_entry_id)) {
            return;
        }

        $entry = GFAPI::get_entry($_gravity_form_entry_id);

        if($entry['1034'] == 'yes' || $entry['form_id'] == 13){
            echo"<style>
            .discount-info table{width: 100%; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif;
                color: #737373; border: 1px solid #e4e4e4; margin-bottom:8px;}
            .discount-info table th, table.tracking-info td{text-align: left; border-top-width: 4px;
                color: #737373; border: 1px solid #e4e4e4; padding: 12px; width:58%;}
            .discount-info table td{text-align: left; border-top-width: 4px; color: #737373; border: 1px solid #e4e4e4; padding: 12px;}
            </style><h2>Attendees Information</h2><div class='discount-info'>";
            // print_r($entry['1000']);
            echo"<table><thead><tr><th>Attendee name</th><th>Attendee email</th><th>Attendee product</th></tr></thead><tbody>";
            foreach ($entry['1000'] as $attendee) {
                printf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", $attendee['1002'].' '.$attendee['1003'], $attendee['1001'], get_the_title( $attendee['1005'] ));
            }
            echo"</tbody></table></div>";
        }
        
    }

    function send_attendees_users_email($sub_users, $admin_email) {
        $subject = 'New Attendees Added';
        $message = 'Hello,<br><br>
    
        New attendees have been added to your account:<br><br>
        
        ';
        
            foreach ($sub_users as $sub_user) {
                $message .= '<b>Email:</b> ' . $sub_user['email'] . ', <b>Password:</b> ' . $sub_user['password']."<br>";
            }
        
            $message .= '<h6>Note: If the email is already registered on this site, provided the password will not work.</h6><br>
        
        <br>Regards,<br>
        Your Website: <a href="'.site_url().'">'.site_url().'</a>';
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($admin_email, $subject, $message, $headers);
    }

    public function send_email_to_attendees($attendees_data, $admin_email)
    {
        $subject = 'You have been successfully registered on ASGMT school';
        $message = 'Hello,<br><br>
    
        You have been successfully registered for this years ASGMT school by:'.$admin_email.'<br><br>
        
        ';           
            $message .= sprintf(__('Username: %s'), $attendees_data['email']) . "<br>";
            $message .= __('To set your password, visit the following address:') . "<br>";
            $message .= '<a href="' . site_url('forgot-password') . '">'.site_url('forgot-password').'</a><br>';
            // $message .= '<b>Email:</b> ' . $attendees_data['email'] . ', <b>Password:</b> ' . $attendees_data['password']."<br>";
            $message .= '<h6>Note: If you have already registerd on the ASGMT site, please use your current credentials.</h6><br>
        Regards,<br>
        Your Website: <a href="'.site_url().'">'.site_url().'</a>';;
        $headers = array('Content-Type: text/html; charset=UTF-8');
        wp_mail($attendees_data['email'], $subject, $message, $headers);
    }

    public function show_attendees_repeater_fields( $form )
    {
        $email = GF_Fields::create( array(
            'type'   => 'email',
            'id'     => 1001, 
            'formId' => $form['id'],
            'label'  => 'Email',
            'pageNumber'  => 1, 
            'isRequired'  => true, // Make the field required
        ) );
        $first_name = GF_Fields::create( array(
            'type'   => 'text',
            'id'     => 1002, 
            'formId' => $form['id'],
            'label'  => 'First name',
            'pageNumber'  => 1, 
            'isRequired'  => true, // Make the field required
        ) );
    
        $last_name = GF_Fields::create( array(
            'type'   => 'text',
            'id'     => 1003, 
            'formId' => $form['id'],
            'label'  => 'Last name',
            'pageNumber'  => 1, 
            'isRequired'  => true, // Make the field required
        ) );
    
        $product_ids = array( 18783, 22100, 22101, 22102, 18784 );
        $args = array(
            'post_type' => 'product',
            'post__in' 	=> $product_ids,
            'orderby' 	=> 'post__in'
        );
        $products = get_posts( $args );
        $dynamic_choices = [];
        foreach( $products as $key => $product ) {
            $itag = '';
            if($product->ID == 22100)
            {
                $itag = "<span class='ceu-infor'>(i)</span> <span class='ceu-tooltip'><b>What is a CEU credit?</b> The Continuing Education Unit or CEU Provides a standard unit of measurement for continuing education and training, Quantify continuing education and training (CE/T) activities, and Accommodate for the diversity of providers, activities, and purposes in adult education.</span>";
            }
    
            $sale_price 	= get_post_meta( $product->ID, '_sale_price', true );
            $regular_price 	= get_post_meta( $product->ID, '_regular_price', true );
    
            $product_price ='';	
            if ( $sale_price ) {
                $product_price = $sale_price;
            } else {
                $product_price = $regular_price;
            }
            if($key == 0){
                $dynamic_choices[] = array( 'text' => $product->post_title." ".$itag. " $".$product_price, 'value' => $product->ID, 'isSelected' => true );
            }else{
                $dynamic_choices[] = array( 'text' => $product->post_title." ".$itag. " $".$product_price, 'value' => $product->ID, 'isSelected' => false );
            }
        }
        $product_choices = GF_Fields::create( array(
            'type' => 'radio',
            'id' => 1005, 
            'formId' => $form['id'],
            'label' => 'Choose Registration',
            'pageNumber' => 1, 
            'choices' => $dynamic_choices,
            'isRequired'  => true,
            'setDefaultValues' => '18783'
        ) );
        // Set the default selected option
        $product_choices['choices'][1]['isSelected'] = true;
        // create the radio field
        $course_type = GF_Fields::create( array(
            'type' => 'radio',
            'id' => 1028, 
            'formId' => $form['id'],
            'label' => 'Select Course Type',
            'pageNumber' => 1, 
            'setDefaultValues' => 'in_person',
            'choices' => array(
                array(
                    'text' => 'In-person',
                    'value' => 'in_person',
                    'isSelected' => true,
                ),
                array(
                    'text' => 'Virtual',
                    'value' => 'virtual'
                ),
            ) 
        ) );
        $course_type['choices'][1]['isSelected'] = true;
        $special_role = GF_Fields::create( 
            array(
                'type' => 'checkbox',
                'id' => 1029, // replace with a unique ID for this field
                'formId' => $form['id'],
                'label' => 'SPECIAL ROLE? (CHECK ALL THAT APPLY)',
                'pageNumber' => 1,
                'defaultChoice' => 'instructormember',
                'choices' => array(
                    array(
                        'text' => 'Is the attendee a speaker at ASGMT?',
                        'value' => 'instructormember'
                    ),
                    array(
                        'text' => 'Is the attendee an Exhibitor?',
                        'value' => 'exhibitsmember'
                    )
                ),
                'inputs' => array(
                    array(
                        'label' => 'Is the attendee a speaker at ASGMT?',
                        'id' => '1029.1',
                    ),
                    array(
                        'label' => 'Is the attendee an Exhibitor?',
                        'id' => '1029.2'
                    )
                )
            ) 
        );
        $repeater = GF_Fields::create( array(
            'type'             => 'repeater',
            'description'      => '',
            'id'               => 1000, // The Field ID must be unique on the form
            'formId'           => $form['id'],
            'label'            => '',
            'addButtonText'    => '+ Add Attendees', // Optional
            'removeButtonText' => '- Remove Attendee', // Optional
            'pageNumber'       => 1, // Ensure this is correct
            'fields'           => array( $first_name, $last_name, $email, $product_choices, $course_type, $special_role ), // Add the fields here.
        ) );
        // $form['fields'][] = $repeater;
        array_splice( $form['fields'], 13, 0, array( $repeater ) );
        return $form;
    }

    public function remove_attendees_repeater_fields( $form_meta, $form_id, $meta_name )
    {
        if ( $meta_name == 'display_meta' ) {
            $form_meta['fields'] = wp_list_filter( $form_meta['fields'], array( 'id' => 1000 ), 'NOT' );
        }
        return $form_meta;
    }

    public function attendees_after_submission( $entry, $form )
    {
		$get_all_attendees = rgar( $entry, '1000' );
		foreach ($get_all_attendees as $attendees) {
			$products_addto_cart[] = array( 'id' => $attendees['1005'], 'quantity' => 1 );
		}
        // Empty the cart
        WC()->cart->empty_cart();
        foreach ( $products_addto_cart as $product ) {
            WC()->cart->add_to_cart( $product['id'], $product['quantity'] );
        }	

        $entry_id = $entry['id'];
        $_SESSION['entry_id'] = $entry_id;
        $checkout_url = wc_get_checkout_url();
        wp_redirect( $checkout_url );
        exit;
    }
}
function init_create_attendees()
{
    global $create_attendees;
    $create_attendees = new CreateAttendees();
}

add_action('init', 'init_create_attendees');
