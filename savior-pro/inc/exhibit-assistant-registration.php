<?php
class ExhibitAssistantRegistration
{
    public function __construct()
    {
        //Show Exhibit Assistant Registration Form 
        add_filter('gform_form_post_get_meta_14', array( $this, 'exhibit_assistant_registration' ));
        // Remove the field before the form is saved. Adjust your form ID
        add_filter('gform_form_update_meta_14', array( $this, 'remove_exhibit_assistant_registration' ), 10, 3);
        add_action('gform_after_submission_14', array( $this, 'crete_new_exhibit_assistants' ), 10, 2);
        // Show already registerd user for assistant
        add_shortcode( 'exhibit_assistant_list', array( $this, 'exhibit_assistant_list_ajax' ) );
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
        $address->isRequired = true;
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
            'label'       => 'Is this your first year?*',
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
            'addButtonText'    => '+ Add Assistant', // Optional
            'removeButtonText' => '- Remove Assistant', // Optional
            'pageNumber'       => 1, // Ensure this is correct
            'fields'           => array($first_name, $last_name, $address, $email, $first_year, $special_role) // $first_name, $last_name, $address, $email, $radio, $checkbox
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
                        'first_name'            => $assistants_data['1001'],
                        'last_name'             => $assistants_data['1002'],
                        'billing_first_name'    => $assistants_data['1001'],
                        'billing_last_name'     => $assistants_data['1002'],
                        'billing_address_1'     => $assistants_data['1003.1'],
                        'billing_address_2'     => $assistants_data['1003.2'],
                        'billing_city'          => $assistants_data['1003.3'],
                        'billing_state'         => $assistants_data['1003.4'],
                        'billing_postcode'      => $assistants_data['1003.5'],
                        'billing_country'       => $assistants_data['1003.6'],
                        'billing_email'         => $assistants_data['1004']
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
            $get_assistants_ids = get_user_meta($entry['created_by'], '_assistants_ids', true);
            $update_assistants_ids = is_array($get_assistants_ids) ? array_unique(array_merge($get_assistants_ids, $_assistants_ids)) : $_assistants_ids;
            update_user_meta($entry['created_by'], '_assistants_ids', $update_assistants_ids);
        }
    }

    public function exhibit_assistant_list_ajax()
    {
        $get_assistants_ids = get_user_meta(get_current_user_id(), '_assistants_ids', true);
        if(!empty($get_assistants_ids))
        {
            $args = [
                'include' => $get_assistants_ids, // ID's of users you want to get
                'fields'  => [ 'ID', 'user_email', 'display_name', 'user_url' ],
              ];
              $users = get_users( $args );
            ?>
                <ul>
                    <?php foreach ($users as $user) : ?>
                        <li><?php echo $user->display_name; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php
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
}

function init_create_ssistant()
{
    global $create_attendees;
    $create_attendees = new ExhibitAssistantRegistration();
}

add_action('init', 'init_create_ssistant');
