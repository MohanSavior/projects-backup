<?php
class ExhibitAssistantRegistration
{
    public function __construct()
    {
        //Show Exhibit Assistant Registration Form 
        add_filter('gform_form_post_get_meta_14', array( $this, 'exhibit_assistant_registration' ));
        // Remove the field before the form is saved. Adjust your form ID
        add_filter('gform_form_update_meta_14', array( $this, 'remove_exhibit_assistant_registration' ), 10, 3);
        // Hook into Gravity Forms pre-submission
        add_action('gform_validation_14', array( $this, 'check_email_and_role'));
        add_action('gform_after_submission_14', array( $this, 'crete_new_exhibit_assistants' ), 10, 2);
        add_filter( 'gform_confirmation_14', array($this, 'exhibit_assistant_registration_confirmation'), 10, 4 );
        // Show already registerd user for assistant
        add_shortcode( 'exhibit_assistant_list', array( $this, 'exhibit_assistant_list_ajax' ) );
        add_action('admin_footer', array($this, 'exhibitor_assistant_scripts'));
        add_action('wp_footer', array($this, 'exhibitor_assistant_scripts'));
        add_action('wp_ajax_update_exhibitor_assistant', array($this, 'update_exhibitor_assistant'));
    }

    public function exhibitor_assistant_scripts()
    {
        $screen = get_current_screen();
        if($screen->id == 'asgmt-exhibits_page_edit-exhibitor-profile' || is_page(19755)){

            ?>
            <script type="text/javascript">
                jQuery( function() {
                    jQuery( "#accordion" ).accordion(
                        {
                            collapsible: true,
                            active: false,
                            autoHeight: true,
                            icons: {
                                header: "ui-icon-circle-arrow-e",
                                activeHeader: "ui-icon-circle-arrow-s"
                            }
                        }
                    );
                } );
                jQuery(document).ready(function($) {
                    jQuery('#accordion').on('click', '.show-assistants-billing-address a', function(){
                        let assistantID = jQuery(this).attr('data-id');
                        jQuery(`.assistant-addres-${assistantID}`).toggle();
                        jQuery(`.assistant-addres-form-${assistantID}`).toggle();           
                        if(jQuery(`.assistant-addres-form-${assistantID}`).is(":visible"))
                        {
                            $(`#tab-${assistantID}`).css('height','auto')
                        }
                    });
                    jQuery('.assistants-billing-form-btn').click(function(event) {
                        event.preventDefault(); // Prevent default form submission behavior
                        // Serialize form data
                        let formID = $(this).attr('data-id');
                        var formData = $(`#assistant-addres-form-${formID}`).serialize();
                        
                        // Send AJAX request
                        $.ajax({
                            url: ajax_object.ajax_url,
                            method: 'POST',
                            data: formData,
                            beforeSend: function(){
                                $('body').find(`#tab-${formID}`).prepend('<div id="assistant-spinner"></div>');
                            },
                            success: function(response) {
                                if(response.success){
                                    $(`.assistants-billing-address assistant-addres-${formID}`).html(response.data);
                                    $(`.show-assistants-billing-address a[data-id="${formID}"]`).trigger('click');
                                    $('body').find(`#tab-${formID}`).find('#assistant-spinner').remove();
                                }
                            },
                            error: function(xhr, status, error) {
                                $('body').find(`#tab-${formID}`).find('#assistant-spinner').remove();
                            }
                        });
                    });
                });
            </script>
            <?php
        }
    }
    public function exhibit_assistant_registration($form)
    {
        // Create the First Name field
        $first_name = GF_Fields::create(array(
            'type'        => 'text',
            'id'          => 1001,
            'formId'      => $form['id'],
            'label'       => 'First Name',
            'isRequired'  => true,
            'pageNumber'  => 1
        ));

        // Create the Last Name field
        $last_name = GF_Fields::create(array(
            'type'        => 'text',
            'id'          => 1002,
            'formId'      => $form['id'],
            'label'       => 'Last Name',
            'isRequired'  => true,
            'pageNumber'  => 1
        ));

        // Create the Address field
        $address = new GF_Field_Address();
        $address->id = 1003;
        $address->formId = $form['id'];
        // $address->label = 'Address';
        $address->pageNumber = 1;
        $address->isRequired = false;
        $address->labelPlacement = 'top_label';
        $address->cssClass     = 'input-before-label';

        // Set required property for each subfield of the address field
        $address->inputs = array(
            array(
                'id' => "{$address->id}.1",
                'label' => 'Street Address 1:',
                'isRequired' => true
            ),
            array(
                'id' => "{$address->id}.2",
                'label' => 'Street Address 2:',
                'isRequired' => true
            ),
            array(
                'id' => "{$address->id}.3",
                'label' => 'City:',
                'isRequired' => true
            ),
            array(
                'id' => "{$address->id}.4",
                'label' => 'State:',
                'isRequired' => true
            ),
            array(
                'id' => "{$address->id}.5",
                'label' => 'ZIP / Postal Code:',
                'isRequired' => true
            ),
            array(
                'id' => "{$address->id}.6",
                'label' => 'Country:',
                'isRequired' => true
            ),
        );

        // Create the Email field
        $email = GF_Fields::create(array(
            'type'        => 'email',
            'id'          => 1004,
            'formId'      => $form['id'],
            'label'       => 'Email',
            'isRequired'  => true,
            'pageNumber'  => 1
        ));

        // Create the Radio field
        $first_year = GF_Fields::create(array(
            'type'        => 'radio',
            'id'          => 1005,
            'formId'      => $form['id'],
            'label'       => 'Is this your first year?',
            'pageNumber'  => 1,
            'isRequired'  => true,
            'choices'     => array(
                array(
                    'text'       => 'Yes',
                    'value'      => 'yes',
                ),
                array(
                    'text'       => 'No',
                    'value'      => 'no',
                )
            )
        ));

        // Create the Checkbox field
        $special_role = GF_Fields::create(array(
            'type'        => 'checkbox',
            'id'          => 1006,
            'formId'      => $form['id'],
            'label'       => 'SPECIAL ROLE? (Check all that apply)',
            'pageNumber'  => 1,
            'choices'     => array(
                array(
                    'text'       => 'Are you a speaker at ASGMT?',
                    'value'      => 'speaker',
                ),
                array(
                    'text'       => 'Are you an ASGMT Committee Member?',
                    'value'      => 'committeemember',
                ),
                array(
                    'text'       => 'Are you a Board Member?',
                    'value'      => 'bodmember',
                )
            ),
            'inputs' => array(
                array(
                    'label' => 'Are you a speaker at ASGMT?',
                    'id' => '1006.1',
                    'isSelected' => true,
                ),
                array(
                    'label' => 'Are you an ASGMT Committee Member?',
                    'id' => '1006.2'
                ),
                array(
                    'label' => 'Are you a Board Member?',
                    'id' => '1006.3'
                )
            )
        ));
        $repeater = GF_Fields::create(array(
            'type'             => 'repeater',
            'description'      => '',
            'id'               => 1000, // The Field ID must be unique on the form
            'formId'           => $form['id'],
            'label'            => '',
            'addButtonText'    => '+ Add Booth Attendee', // Optional
            'removeButtonText' => '- Remove Booth Attendee', // Optional
            'pageNumber'       => 1, // Ensure this is correct
            'fields'           => array($first_name, $last_name, $email, )//$first_year, $special_role, $address, // $first_name, $last_name, $address, $email, $radio, $checkbox
        ));
        $form['fields'][] = $repeater;
        return $form;
    }

    public function remove_exhibit_assistant_registration($form_meta, $form_id, $meta_name)
    {
        if ($meta_name == 'display_meta') {
            $form_meta['fields'] = wp_list_filter($form_meta['fields'], array('id' => 1000), 'NOT');
        }
        return $form_meta;
    }

    public function check_email_and_role( $validation_result )
    {
        // Replace 1 with the actual field ID of the repeater field
        $repeater_field_id = 1004;
        // error_log(print_r($validation_result, true));
        // Get the form object and the field value
        $form = $validation_result['form'];
        $field_value = rgpost( 'input_' . $repeater_field_id );

        // Perform your validation logic here
        // You can access individual repeater fields using the $field_value array

        // Example validation: check if any of the emails already exist in the database
        // foreach ( $field_value as $row_index => $row ) {
        //     error_log(print_r('$row', true));
        //     error_log(print_r($row, true));
        //     error_log(print_r($form, true));
            // foreach ( $row as $field_key => $field_value ) {
            //     if ( strpos( $field_key, 'email' ) === 0 ) {
            //         $email = rgar( $row, $field_key );

            //         // Check if email exists in database
            //         if ( email_exists( $email ) ) {
            //             $field_id = $repeater_field_id . '.' . $row_index . '.' . $field_key;
            //             $form['fields'][$field_id]->failed_validation = true;
            //             $form['fields'][$field_id]->validation_message = 'This email already exists in the database.';
            //             $validation_result['is_valid'] = false;
            //         }
            //     }
            // }
        // }
        $form = $validation_result['form'];

        // Replace "2" with the field ID of your repeater field
        $repeater_field_id = 1000;
    
        // Replace "3" with the field ID of the email validation field
        $email_validation_field_id = 1004;
    
        // Get the submitted form values
        $field_values = $_POST['input_' . $repeater_field_id];
    
        $invalid_emails = array();
    
        // Perform the email validation
        foreach ($field_values as $field_value) {
            $email = $field_value['input_' . $email_validation_field_id];
            error_log(print_r('$email', true));
            error_log(print_r($email, true));
            error_log(print_r($field_value, true));
            // Implement your email existence check here
            // You can query a database or use an API to validate the email existence
    
            // For example, assume you have a function named "check_email_existence" that performs the check
            $email_exists = email_exists( $email );
    
            if (!$email_exists) {
                $invalid_emails[] = $email;
            }
        }
    
        // If there are invalid emails, set the validation result to false and add an error message
        if (!empty($invalid_emails)) {
            $validation_result['is_valid'] = false;
    
            // Customize the error message as needed
            $error_message = 'The following emails are invalid: ' . implode(', ', $invalid_emails);
    
            // Replace "3" with the field ID of the email validation field
            $form['fields'][$email_validation_field_id]['failed_validation'] = true;
            $form['fields'][$email_validation_field_id]['validation_message'] = $error_message;
        }
    
        $validation_result['form'] = $form;
        return $validation_result;
    }

    public function crete_new_exhibit_assistants($entry, $form)
    {

        $repeater_field_assistants = $entry['1000'];
        if (!empty($repeater_field_assistants) && !empty($entry['created_by'])) {
            $_assistants_ids = array();
            foreach ($repeater_field_assistants as $assistants_data) {
                $attendee_user_id = $this->get_or_create_assistant_user_by_email($assistants_data['1004']);
                if (is_wp_error($attendee_user_id)) {
                } else {
                    $assistant_metas = array(
                        'first_name'                => isset($assistants_data['1001']) ? $assistants_data['1001'] : '',
                        'last_name'                 => isset($assistants_data['1002']) ? $assistants_data['1002'] : '',
                        'billing_first_name'        => isset($assistants_data['1001']) ? $assistants_data['1001'] : '',
                        'billing_last_name'         => isset($assistants_data['1002']) ? $assistants_data['1002'] : '',
                        'billing_address_1'         => isset($assistants_data['1003.1']) ? $assistants_data['1003.1'] : '',
                        'billing_address_2'         => isset($assistants_data['1003.2']) ? $assistants_data['1003.2'] : '',
                        'billing_city'              => isset($assistants_data['1003.3']) ? $assistants_data['1003.3'] : '',
                        'billing_state'             => isset($assistants_data['1003.4']) ? $assistants_data['1003.4'] : '',
                        'billing_postcode'          => isset($assistants_data['1003.5']) ? $assistants_data['1003.5'] : '',
                        'billing_country'           => isset($assistants_data['1003.6']) ? $assistants_data['1003.6'] : '',
                        'billing_email'             => isset($assistants_data['1004']) ? $assistants_data['1004'] : '',
                        'is_this_your_first_year'   => isset($assistants_data['1005']) ? $assistants_data['1005'] : ''
                    );
                    foreach ($assistant_metas as $key => $value) {
                        if (empty(get_user_meta($attendee_user_id, $key, true))) {
                            update_user_meta($attendee_user_id, $key, $value);
                        }
                    }
                    $special_role = array();
                    if(!empty($assistants_data['1006.1']))
                    {
                        $special_role[0] = $assistants_data['1006.1'];
                    }
                    if(!empty($assistants_data['1006.2']))
                    {
                        $special_role[1] = $assistants_data['1006.2'];
                    }
                    if(!empty($assistants_data['1006.3']))
                    {
                        $special_role[2] = $assistants_data['1006.3'];
                    }
                    update_user_meta($attendee_user_id, 'special_role', $special_role);
                    $user = get_user_by('id', $attendee_user_id);
                    if ($user) {
                        $user->add_role('exhibitassistant');
                    }
                    $_assistants_ids[] = $attendee_user_id;
                }
            }
            $exhibitor_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : $entry['created_by'];
            $get_assistants_ids = get_user_meta($exhibitor_id, '_assistants_ids', true);
            $update_assistants_ids = is_array($get_assistants_ids) ? array_unique(array_merge($get_assistants_ids, $_assistants_ids)) : $_assistants_ids;
            update_user_meta($exhibitor_id, '_assistants_ids', $update_assistants_ids);
        }
    }

    public function exhibit_assistant_registration_confirmation( $confirmation, $form, $entry, $ajax )
    {
        if(is_admin()){
            $confirmation = array( 'redirect' => $entry['source_url'] );
            return $confirmation;
        }
    }

    public function exhibit_assistant_list_ajax( $atts )
    {
        $atts = shortcode_atts(
            array(
                'exhibitor_id' => get_current_user_id(),
            ), $atts, 'exhibit_assistant_list' 
        );
        wp_enqueue_style('jquery-ui', '//code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css', array(), '1.13.2');
        wp_enqueue_script('jquery-ui', '//code.jquery.com/ui/1.13.2/jquery-ui.js', array('jquery'), '1.13.2', true);
        wp_localize_script('jquery-ui', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'action'   => 'update_exhibitor_assistant',
            'security' => wp_create_nonce( 'exhibitor_assistant' )
        ));
        $get_assistants_ids = get_user_meta($atts['exhibitor_id'], '_assistants_ids', true);
        if(!empty($get_assistants_ids))
        {
            $args = [
                'include' => $get_assistants_ids, // ID's of users you want to get
                // 'fields'  => [ 'ID', 'user_email', 'display_name', 'user_url' ],
              ];
              $assistants = get_users( $args );
              $countries = GF_Fields::get( 'address' )->get_default_countries();
              ?>
                <div id="accordion">
                    <?php foreach ($assistants as $assistant) : 
                        $special_role = get_user_meta($assistant->ID, 'special_role', true);
                        ?>
                        <h3><?php echo $assistant->first_name.' '.$assistant->last_name; ?></h3>
                        <div id="tab-<?php echo $assistant->ID ;?>" class="accordion-content">
                            <?php
                                if(is_admin())
                                {
                                    ?>
                                        <div class="show-assistants-billing-address">
                                            <a data-id="<?php echo $assistant->ID ;?>" class="dashicons dashicons-edit-large" href="javascript:void(0)"></a>
                                        </div>  
                                    <?php 
                                }    
                            ?>
                            <div class="assistants-billing-address assistant-addres-<?php echo $assistant->ID; ?>">
                                <?php                                                                 
                                    echo '<p><span>First Name: </span>' . get_user_meta($assistant->ID, 'billing_first_name', true) . '</p>';
                                    echo '<p><span>Last Name: </span>' . get_user_meta($assistant->ID, 'billing_last_name', true) . '</p>';
                                    echo '<p><span>Email: </span>' . get_user_meta($assistant->ID, 'billing_email', true) . '</p>';
                                    // echo '<p><span>Street Address: </span>' . get_user_meta($assistant->ID, 'billing_address_1', true) . '</p>';
                                    // echo '<p><span>Address Line 2: </span>' . get_user_meta($assistant->ID, 'billing_address_2', true) . '</p>';
                                    // echo '<p><span>ZIP / Postal Code: </span>' . get_user_meta($assistant->ID, 'billing_postcode', true) . '</p>';
                                    // echo '<p><span>City: </span>' . get_user_meta($assistant->ID, 'billing_city', true) . '</p>';
                                    // echo '<p><span>State / Province / Region: </span>' . get_user_meta($assistant->ID, 'billing_state', true) . '</p>';
                                    // echo '<p><span>Country: </span>' . $countries[get_user_meta($assistant->ID, 'billing_country', true)] . '</p>';
                                    // echo '<p><span>Is this your first year?: </span>' . get_user_meta($assistant->ID, 'is_this_your_first_year', true) . '</p>';
                                ?>
                                    <!-- <input type="checkbox" id="speaker" name="special_role[]" value="speaker" <?php //checked($special_role[0], 'speaker'); ?> readonly disabled='disabled'/>
                                    <label for="speaker">Are you a speaker at ASGMT?</label><br>
                                    
                                    <input type="checkbox" id="committee_member" name="special_role[]" value="committeemember" <?php //checked($special_role[1], 'committeemember'); ?> readonly disabled='disabled' />
                                    <label for="committee_member">Are you an ASGMT Committee Member?</label><br>
                                    
                                    <input type="checkbox" id="board_member" name="special_role[]" value="bodmember" <?php //checked($special_role[2], 'bodmember'); ?> readonly disabled='disabled' />
                                    <label for="board_member">Are you a Board Member?</label><br><br> -->
                                
                            </div>
                            <?php
                                if(is_admin())
                                {
                                    
                                    ?>                            
                                        <div class="assistants-billing-address-update assistant-addres-form-<?php echo $assistant->ID; ?>" style="display:none;">   
                                            <form id="assistant-addres-form-<?php echo $assistant->ID; ?>">
                                                <input type="hidden" name="action" value="update_exhibitor_assistant" />
                                                <input type="hidden" name="assistants-user-id" value="<?php echo $assistant->ID; ?>" />
                                                <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'exhibitor_assistant' ); ?>" />
                                                <div class="assistant-form">
                                                    <div class="assitant-col-100">
                                                    <div class="assitant-form-col-50">
                                                        <label for="<?php echo $assistant->ID; ?>-first_name">First Name:</label><br>
                                                        <input type="text" id="<?php echo $assistant->ID; ?>-first_name" name="billing_first_name" value="<?php echo get_user_meta($assistant->ID, 'billing_first_name', true); ?>"><br><br>
                                                    </div>

                                                    <div class="assitant-form-col-50">
                                                        <label for="<?php echo $assistant->ID; ?>-last_name">Last Name:</label><br>
                                                        <input type="text" id="<?php echo $assistant->ID; ?>-last_name" name="billing_last_name" value="<?php echo get_user_meta($assistant->ID, 'billing_last_name', true); ?>"><br><br>
                                                    </div>
                                                    </div>
                                                    
                                                    <!-- <label for="<?php //echo $assistant->ID; ?>-address1">Street Address 1:</label><br>
                                                    <input type="text" id="<?php //echo $assistant->ID; ?>-address1" name="billing_address_1" value="<?php //echo get_user_meta($assistant->ID, 'billing_address_1', true); ?>"><br><br>
                                                    
                                                    <label for="<?php //echo $assistant->ID; ?>-address2">Street Address 2:</label><br>
                                                    <input type="text" id="<?php //echo $assistant->ID; ?>-address2" name="billing_address_2" value="<?php //echo get_user_meta($assistant->ID, 'billing_address_2', true); ?>"><br><br>
                                                    
                                                    <div class="assitant-col-100">
                                                        <div class="assitant-form-col-50">
                                                        <label for="<?php //echo $assistant->ID; ?>-city">City:</label><br>
                                                        <input type="text" id="<?php //echo $assistant->ID; ?>-city" name="billing_city" value="<?php //echo get_user_meta($assistant->ID, 'billing_city', true); ?>"><br><br>
                                                        </div>
                                                    
                                                        <div class="assitant-form-col-50">
                                                        <label for="<?php //echo $assistant->ID; ?>-state">State:</label><br>
                                                        <input type="text" id="<?php //echo $assistant->ID; ?>-state" name="billing_state" value="<?php //echo get_user_meta($assistant->ID, 'billing_state', true); ?>"><br><br>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="assitant-col-100">
                                                        <div class="assitant-form-col-50">
                                                        <label for="<?php //echo $assistant->ID; ?>-zip">Zip/Postal Code:</label><br>
                                                        <input type="text" id="<?php //echo $assistant->ID; ?>-zip" name="billing_postcode" value="<?php //echo get_user_meta($assistant->ID, 'billing_postcode', true); ?>"><br><br>
                                                        </div>
                                                    
                                                        <div class="assitant-form-col-50">
                                                        <label for="<?php //echo $assistant->ID; ?>-country">Country:</label><br>
                                                        <?php
                                                            // Get HTML options
                                                            // $html_countries = '';
                                                            // foreach ( $countries as $country_cod => $country ) {
                                                            //     $html_countries .= sprintf(
                                                            //         '<option value="%1$s" %3$s>%2$s</option>',
                                                            //         esc_attr( $country_cod ),
                                                            //         esc_html( $country ),
                                                            //         selected( get_user_meta($assistant->ID, 'billing_country', true), esc_attr( $country_cod ), false )
                                                            //     );
                                                            // }
                                                            // Display Select element or tag
                                                            //echo '<select class="country" name="billing_country">' . $html_countries . '</select>';
                                                        ?><br><br>
                                                        </div>
                                                    </div>                                                    
                                                    <label for="<?php //echo $assistant->ID; ?>-email">Email:</label><br>
                                                    <input type="email" id="<?php //echo $assistant->ID; ?>-email" name="billing_email" value="<?php //echo get_user_meta($assistant->ID, 'billing_email', true); ?>"><br><br>
                                                    <label for="<?php //echo $assistant->ID; ?>-first_year">Is this your first year?:</label><br>
                                                    <input type="radio" id="<?php //echo $assistant->ID; ?>-first_year_true" name="is_this_your_first_year" value="yes" <?php //checked(get_user_meta($assistant->ID, 'is_this_your_first_year', true) == 'yes' ); ?> />
                                                    <label for="<?php //echo $assistant->ID; ?>-first_year_true">Yes</label><br>
                                                    <input type="radio" id="<?php //echo $assistant->ID; ?>-first_year_false" name="is_this_your_first_year" value="no" <?php //checked(get_user_meta($assistant->ID, 'is_this_your_first_year', true) == 'no' ); ?> />
                                                    <label for="<?php //echo $assistant->ID; ?>-first_year_false">No</label><br><br>
                                                    <label for="<?php //echo $assistant->ID; ?>-special_role">SPECIAL ROLE? (Check all that apply):</label><br>
                                                    <input type="checkbox" id="<?php //echo $assistant->ID; ?>-speaker" name="special_role[0]" value="speaker" <?php //checked($special_role[0], 'speaker'); ?>>
                                                    <label for="<?php //echo $assistant->ID; ?>-speaker">Are you a speaker at ASGMT?</label><br>
                                                    
                                                    <input type="checkbox" id="<?php //echo $assistant->ID; ?>-committee_member" name="special_role[1]" value="committeemember" <?php //checked($special_role[1], 'committeemember'); ?>>
                                                    <label for="<?php //echo $assistant->ID; ?>-committee_member">Are you an ASGMT Committee Member?</label><br>
                                                    
                                                    <input type="checkbox" id="<?php //echo $assistant->ID; ?>-board_member" name="special_role[2]" value="bodmember" <?php //checked($special_role[2], 'bodmember'); ?>>
                                                    <label for="<?php //echo $assistant->ID; ?>-board_member">Are you a Board Member?</label><br><br> -->
                                                    
                                                    <input type="submit" class="assistants-billing-form-btn" data-id="<?php echo $assistant->ID; ?>" value="Update Assitant Information">
                                                </div>
                                            </form>
                                        </div>
                                    <?php
                                }
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php
        }else{
            echo "<p>No assistants added yet.</p>";
        }
    }
    public function get_or_create_assistant_user_by_email( $email, $random_password = 0 )
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

    public function update_exhibitor_assistant()
    {
        // Verify the nonce
        $nonce = $_POST['security'];
        if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
        }
        try {
            $assistant_metadata = array(
                'billing_first_name'        => $_POST['billing_first_name'],
                'billing_last_name'         => $_POST['billing_last_name'],
                'billing_address_1'         => $_POST['billing_address_1'],
                'billing_address_2'         => $_POST['billing_address_2'],
                'billing_city'              => $_POST['billing_city'],
                'billing_state'             => $_POST['billing_state'],
                'billing_postcode'          => $_POST['billing_postcode'],
                'billing_country'           => $_POST['billing_country'],
                'billing_email'             => $_POST['billing_email'],
                'is_this_your_first_year'   => $_POST['is_this_your_first_year']
            );
            $special_role = array(
                isset($_POST['special_role'][0]) ? 'speaker' : '',
                isset($_POST['special_role'][1]) ? 'committeemember' : '',
                isset($_POST['special_role'][2]) ? 'bodmember' : ''
            );

            foreach ($assistant_metadata as $key => $value) {
                update_user_meta($_POST['assistants-user-id'], $key, $value);
            }
            update_user_meta($_POST['assistants-user-id'], 'special_role', $special_role);

            $assistant_ID = $_POST['assistants-user-id'];
            $html = "<p><span>First Name: </span>'" . get_user_meta($assistant_ID, 'billing_first_name', true) . "'</p>'
            <p><span>Last Name: </span>'" . get_user_meta($assistant_ID, 'billing_last_name', true) . "'</p>'
            <p><span>Email: </span>'" . get_user_meta($assistant_ID, 'billing_email', true) . "'</p>'
            <p><span>Street Address: </span>'" . get_user_meta($assistant_ID, 'billing_address_1', true) . "'</p>'
            <p><span>Address Line 2: </span>'" . get_user_meta($assistant_ID, 'billing_address_2', true) . "'</p>'
            <p><span>ZIP / Postal Code: </span>'" . get_user_meta($assistant_ID, 'billing_postcode', true) . "'</p>'
            <p><span>City: </span>'" . get_user_meta($assistant_ID, 'billing_city', true) . "'</p>'
            <p><span>State / Province / Region: </span>'" . get_user_meta($assistant_ID, 'billing_state', true) . "'</p>'
            <p><span>Country: </span>'" . $countries[get_user_meta($assistant_ID, 'billing_country', true)] . "'</p>'
            <p><span>Is this your first year?: </span>'" . get_user_meta($assistant->ID, 'is_this_your_first_year', true) . "'</p>'
            <input type='checkbox' id='speaker' name='special_role[0]' value='speaker' " . checked($special_role[0], 'speaker', false). " readonly disabled='disabled' />
            <label for='speaker'>Are you a speaker at ASGMT?</label><br>
            
            <input type='checkbox' id='committee_member' name='special_role[1]' value='committeemember' " . checked($special_role[1], 'committeemember', false). " readonly disabled='disabled' />
            <label for='committee_member'>Are you an ASGMT Committee Member?</label><br>
            
            <input type='checkbox' id='board_member' name='special_role[2]' value='bodmember' " . checked($special_role[2], 'bodmember', false). " readonly disabled='disabled' />
            <label for='board_member'>Are you a Board Member?</label>";
            wp_send_json_success($html);
        }catch (Exception $e) {  
            // echo 'Exception Message: ' .$e->getMessage();  
            wp_send_json_error( 'Exception Message: ' .$e->getMessage() );
        }  
    }
}

function init_create_ssistant()
{
    global $create_attendees;
    $create_attendees = new ExhibitAssistantRegistration();
}

add_action('init', 'init_create_ssistant');
