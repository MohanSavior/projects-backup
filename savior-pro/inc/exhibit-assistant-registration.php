<?php
class ExhibitAssistantRegistration
{
    public function __construct()
    {
        //Show Exhibit Assistant Registration Form 
        add_filter('gform_form_post_get_meta_14', array( $this, 'exhibit_assistant_registration' ));
        add_filter('gform_form_update_meta_14', array( $this, 'remove_exhibit_assistant_registration' ), 10, 3);
        add_action('gform_field_validation_14', array( $this, 'check_email_and_role'), 10, 4);
        add_action('gform_after_submission_14', array( $this, 'crete_new_exhibit_assistants' ), 10, 2);
        add_filter( 'gform_confirmation_14', array($this, 'exhibit_assistant_registration_confirmation'), 10, 4 );
        add_shortcode( 'exhibit_assistant_list', array( $this, 'exhibit_assistant_list_ajax' ) );
        add_action('admin_footer', array($this, 'exhibitor_assistant_scripts'));
        add_action('wp_footer', array($this, 'exhibitor_assistant_scripts'));
        add_action('wp_ajax_update_exhibitor_assistant', array($this, 'update_exhibitor_assistant'));
        add_action('wp_ajax_delete_exhibitor_assistant', array($this, 'delete_exhibitor_assistant'));
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
                            heightStyle: "content" ,
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
                        jQuery(`.assistant-addres-view-${assistantID}`).toggle();
                        jQuery(`.assistant-addres-form-${assistantID}`).toggle();           
                        if(jQuery(`.assistant-addres-form-${assistantID}`).is(":visible"))
                        {
                            $(`#tab-${assistantID}`).css('height','auto')
                        }
                    });
                    jQuery('.assistants-billing-form-btn').click(function(event) {
                        event.preventDefault();
                        let formID = $(this).attr('data-id');
                        var formData = $(`#assistant-addres-form-${formID}`).serialize();
                        $.ajax({
                            url: ajax_object.ajax_url,
                            method: 'POST',
                            data: formData,
                            beforeSend: function(){
                                $('body').find(`#tab-${formID}`).prepend('<div id="assistant-spinner"></div>');
                            },
                            success: function(response) {
                                if(response.success){
                                    $(`.show-assistants-billing-address a[data-id="${formID}"]`).trigger('click');
                                    setTimeout(() => {
                                        $(`.assistant-addres-view-${formID}`).html(response.data);
                                        $('body').find(`#tab-${formID}`).find('#assistant-spinner').remove();
                                    }, 200);
                                    Swal.fire({
                                        icon: 'success',
                                        text: 'Successfully updated!',
                                        showConfirmButton: true,
                                        timer: 1500
                                    })
                                }else{
                                    Swal.fire({
                                        icon: 'error',
                                        text: 'Something went wrong please try again!',
                                        showConfirmButton: true,
                                        timer: 1500
                                    })
                                }
                            },
                            error: function(xhr, status, error) {
                                $('body').find(`#tab-${formID}`).find('#assistant-spinner').remove();
                                Swal.fire({
                                    icon: 'error',
                                    text: 'Something went wrong please try again!',
                                    showConfirmButton: true,
                                    timer: 1500
                                })
                            }
                        });
                    });
                    //Delete Assistant
                    jQuery('#accordion').on('click', '.delete-assistants-billing-address a', function(event){
                        event.preventDefault();
                        let formID = $(this).attr('data-id');                        
                        var formData = $(`#assistant-addres-form-${formID}`).serialize();
                        var formData = formData.replace("action=update_exhibitor_assistant", "action=delete_exhibitor_assistant");
                        Swal.fire({
                            title: 'Are you sure?',
                            text: "You won't be able to revert this!",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, delete it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: ajax_object.ajax_url,
                                    method: 'POST',
                                    data: formData,
                                    beforeSend: function(){
                                        $('body').find(`#tab-${formID}`).prepend('<div id="assistant-spinner"></div>');
                                    },
                                    success: function(response) {
                                        if(response.success){
                                            Swal.fire(
                                                'Deleted!',
                                                'Assistant deleted.',
                                                'success'
                                            );
                                        }else{
                                            Swal.fire(
                                                'Error!',
                                                'An error occurred while deleting the assistant.',
                                                'error'
                                            );
                                        }
                                        $(`#tab-${formID}`).remove();
                                        $(`[aria-controls="tab-${formID}"]`).remove();  
                                        $('body').find(`#tab-${formID}`).find('#assistant-spinner').remove();
                                    },
                                    error: function(xhr, status, error) {
                                        Swal.fire(
                                            'Error!',
                                            'An error occurred while deleting the assistant.',
                                            'error'
                                        );
                                        $(`#tab-${formID}`).remove();
                                        $(`[aria-controls="tab-${formID}"]`).remove();  
                                        $('body').find(`#tab-${formID}`).find('#assistant-spinner').remove();
                                    }
                                });
                            }
                        })
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

        // Create the Email field
        $email = GF_Fields::create(array(
            'type'        => 'email',
            'id'          => 1004,
            'formId'      => $form['id'],
            'label'       => 'Email',
            'isRequired'  => true,
            'pageNumber'  => 1
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
            'fields'           => array($first_name, $last_name, $email, )
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

    public function check_email_and_role(  $result, $value, $form, $field  )
    {
        if( $field->id === 1004 )
        {
            $exists = email_exists($value);
            if ( $exists )
            {
                $user = get_userdata( $exists );
                $roles = $user->roles;
                if( !in_array('student', $roles) )
                {
                    $result['is_valid'] = false;
                    $field->validation_message = 'This email is not registered as a student';
                }
            }
            else
            {
                $result['is_valid'] = false;
                $field->validation_message = 'This email is not registered';
            }
        }
        return $result;
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
                        'email'                     => isset($assistants_data['1004']) ? $assistants_data['1004'] : '',
                    );
                    foreach ($assistant_metas as $key => $value) {
                        if (empty(get_user_meta($attendee_user_id, $key, true))) {
                            update_user_meta($attendee_user_id, $key, $value);
                        }
                    }
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
            $confirmation = array( 'redirect' => $entry['source_url'].'#booth-admin-tabs-assistant' );
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
              $assistants = get_users( array('include' => $get_assistants_ids) );
              ?>
                <div id="accordion">
                    <?php foreach ($assistants as $assistant) : 
                        $user_info = get_userdata($assistant->ID);
                        $user_email = $user_info->user_email;
                        ?>
                        <h3><?php echo $assistant->first_name.' '.$assistant->last_name; ?></h3>
                        <div id="tab-<?php echo $assistant->ID ;?>" class="accordion-content">
                            <?php
                                if(is_admin())
                                {
                                    ?>
                                    <div class="assistant-actions">
                                        <div class="show-assistants-billing-address">
                                            <a data-id="<?php echo $assistant->ID ;?>" class="dashicons dashicons-edit-large" href="javascript:void(0)"></a>
                                        </div> 
                                        <div class="delete-assistants-billing-address">
                                            <a data-id="<?php echo $assistant->ID ;?>" class="dashicons dashicons-trash" href="javascript:void(0)"></a>
                                        </div> 
                                    </div>
                                    <?php 
                                }    
                            ?>
                            <div class="assistants-billing-address assistant-addres-view-<?php echo $assistant->ID; ?>">
                                <?php                                                                 
                                    echo '<p><span>First Name: </span>' . $user_info->first_name . '</p>';
                                    echo '<p><span>Last Name: </span>' . $user_info->last_name . '</p>';
                                    echo '<p><span>Email: </span>' . $user_email . '</p>';
                                ?>                                
                            </div>
                            <?php
                                if(is_admin())
                                {
                                    $exhibitor_id = isset($_REQUEST['exhibitor_id']) ? $_REQUEST['exhibitor_id'] : 0;
                                    ?>                            
                                        <div class="assistants-billing-address-update assistant-addres-form-<?php echo $assistant->ID; ?>" style="display:none;">   
                                            <form id="assistant-addres-form-<?php echo $assistant->ID; ?>">
                                                <input type="hidden" name="action" value="update_exhibitor_assistant" />
                                                <input type="hidden" name="assistants-user-id" value="<?php echo $assistant->ID; ?>" />
                                                <input type="hidden" name="security" value="<?php echo wp_create_nonce( 'exhibitor_assistant' ); ?>" />
                                                <input type="hidden" name="booth_admin" value="<?php echo $_REQUEST['exhibitor_id'];?>">
                                                <div class="assistant-form">
                                                    <div class="assitant-col-100">
                                                        <div class="assitant-form-col-50">
                                                            <label for="<?php echo $assistant->ID; ?>-first_name">First Name:</label><br>
                                                            <input type="text" id="<?php echo $assistant->ID; ?>-first_name" name="first_name" value="<?php echo $user_info->first_name; ?>"><br><br>
                                                        </div>

                                                        <div class="assitant-form-col-50">
                                                            <label for="<?php echo $assistant->ID; ?>-last_name">Last Name:</label><br>
                                                            <input type="text" id="<?php echo $assistant->ID; ?>-last_name" name="last_name" value="<?php echo $user_info->last_name; ?>"><br><br>
                                                        </div>
                                                        <div class="assitant-form-col-100">
                                                            <label for="<?php echo $assistant->ID; ?>-email">Email:</label><br>
                                                            <input type="email" id="<?php echo $assistant->ID; ?>-email" name="email" value="<?php echo $user_email; ?>"><br><br>
                                                        </div>
                                                    </div>
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
        $nonce = $_POST['security'];
        if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
        }
        try {
            $user_data = get_userdata($_POST['assistants-user-id']);
            if($user_data->first_name !== $_POST['first_name']){
                $user_data->first_name = $_POST['first_name'];
            }
            if($user_data->last_name !== $_POST['last_name']){
                $user_data->last_name = $_POST['last_name'];
            }
            if($user_data->user_email !== $_POST['email']){
                $user_data->user_email = $_POST['email'];
            }

            $assistant_id = wp_update_user( $user_data );
            if(is_wp_error( $assistant_id ))
            {
                wp_send_json_error( $assistant_id->get_error_message(), 400 );
            }else{
                $html = '<p><span>First Name: </span>' . $_POST['first_name'] . '</p>
                        <p><span>Last Name: </span>' . $_POST['last_name'] . '</p>
                        <p><span>Email: </span>' . $_POST['email'] . '</p>';
                wp_send_json_success($html, 202);
            }
        }catch (Exception $e) {  
            wp_send_json_error( 'Exception Message: ' .$e->getMessage() );
        }  
    }

    public function delete_exhibitor_assistant()
    {
        $nonce = $_POST['security'];
        if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
        }
        $get_assistants_ids = get_user_meta($_REQUEST['booth_admin'], '_assistants_ids', true);
        if (is_array($get_assistants_ids) && in_array($_POST['assistants-user-id'], $get_assistants_ids)) {
            $updated_assistants_ids = array_diff($get_assistants_ids, array($_POST['assistants-user-id']));
            update_user_meta($_REQUEST['booth_admin'], '_assistants_ids', $updated_assistants_ids);
            wp_send_json_success();
        }else{
            wp_send_json_error();
        }
    }
}

function init_create_ssistant()
{
    global $create_attendees;
    $create_attendees = new ExhibitAssistantRegistration();
}

add_action('init', 'init_create_ssistant');
