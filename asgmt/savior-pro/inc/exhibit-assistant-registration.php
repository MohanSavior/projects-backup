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
        // add_action('admin_footer', array($this, 'exhibitor_assistant_scripts'));
        add_action('wp_footer', array($this, 'exhibitor_assistant_scripts'));
        add_action('wp_ajax_delete_exhibitor_assistant', array($this, 'delete_exhibitor_assistant'));
    }

    public function exhibitor_assistant_scripts()
    {
        $screen = get_current_screen();
        if(isset($screen->id) && $screen->id == 'asgmt-exhibits_page_edit-exhibitor-profile' || is_page(19755)){
            // wp_enqueue_style('sweetalert2', '//cdn.jsdelivr.net/npm/sweetalert2@11.7.10/dist/sweetalert2.min.css', array(), '11.7.10');
            wp_enqueue_script('sweetalert2', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', array(), '');
            ?>
            <style type="text/css">
                #assistant-spinner {
                    position: absolute;
                    width: 100%;
                    height: 100%;
                    background: #898989a6;
                    z-index: 9;
                    border-radius: 10px;
                    top: 0;
                    left: 0;
                }

                #assistant-spinner:before {
                    content: "";
                    position: absolute;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    border: 2px solid #333;
                    border-top-color: transparent;
                    top: 45%;
                    left: 45%;
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    0% {
                        transform: rotate(0);
                    }
                    100% {
                        transform: rotate(360deg);
                    }
                }
                .elementor-element.elementor-element-1992996 h3.ui-accordion-header span {
                    margin-right: 5px;
                }
                .elementor-element.elementor-element-1992996 h3.ui-accordion-header {
                    color: #080E41;
                    background-color: #F7C338;
                    border: 0;
                    padding: 15px 20px;
                    font-family: "Roboto", Sans-serif;
                    font-size: 15px;
                    font-weight: 500;
                }
                .elementor-element.elementor-element-1992996 .accordion-content {
                    padding: 20px 20px;
                }
                .elementor-element.elementor-element-1992996 .accordion-content .assistant-actions a {
                    color: 5a5a5a;
                }
                .elementor-element.elementor-element-1992996 .accordion-content .assistant-actions a:hover {
                    color: red;
                }
                .elementor-element.elementor-element-1992996 .accordion-content .assistants-billing-address p {
                    color: #5a5a5a;
                    font-family: montserrat,Sans-serif;
                    font-size: 16px;
                    font-weight: 600;
                    line-height: 28px;
                    margin-bottom: 5px;
                }
                .elementor-element.elementor-element-1992996 .accordion-content .assistants-billing-address {
                    margin-top: 10px;
                    text-align: left;
                }
                .elementor-element.elementor-element-1992996 .accordion-content .delete-assistants-billing-address {
                    text-align: left;
                }

                .elementor-element.elementor-element-1992996 .accordion-content .assistants-billing-address span {
                    color: #252f86;
                }
            </style>
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
                    
                    //Delete Assistant
                    jQuery('#accordion').on('click', '.delete-assistants-billing-address a', function(event){
                        event.preventDefault();
                        let formID = $(this).attr('data-id');  
                        let fomData = $(this).data();  
                        fomData.nonce = ajax_object.security;  
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
                                    data: fomData,
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
                                        if(!response.data.result)
                                            $('body').find(`#assistant-container`).html('<p>No assistants added yet.</p>');
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
                if($user_id = email_exists($assistants_data['1004']))
                {
                    $_assistants_ids[] = $user_id;
                }
            }
            $company_id = $this->get_company_id_by_primary_or_alternate_admin();
            $get_assistants_ids = get_post_meta($company_id, 'assistants', true);
            $update_assistants_ids = is_array($get_assistants_ids) && !empty($get_assistants_ids) ? array_unique(array_merge($get_assistants_ids, $_assistants_ids)) : $_assistants_ids;
            update_post_meta($company_id, 'assistants', $update_assistants_ids);
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
        ob_start();
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
        $company_id =  $this->get_company_id_by_primary_or_alternate_admin(); // Get the company id   
        echo "<div id='assistant-container'>";
        if(isset($company_id) && $get_assistants_ids = get_post_meta($company_id, 'assistants', true))
        {
            $assistants = get_users( array('include' => $get_assistants_ids) );
            ?>
                <div id="accordion">
                    <?php foreach ($assistants as $assistant_user) : 
                        $user_email = $assistant_user->user_email;
                    ?>
                        <h3><?php echo $assistant_user->first_name.' '.$assistant_user->last_name; ?></h3>
                        <div id="tab-<?php echo $assistant_user->ID ;?>" class="accordion-content">
                            <div class="assistant-actions">
                                <div class="delete-assistants-billing-address">
                                    <a  href="javascript:void(0)" 
                                        class="dashicons dashicons-trash" 
                                        data-id="<?php echo $assistant_user->ID ;?>" 
                                        data-assistant_id="<?php echo $assistant_user->ID ;?>" 
                                        data-company_id="<?php echo $company_id ;?>" 
                                        data-action="delete_exhibitor_assistant" 
                                    ></a>
                                </div> 
                            </div>
                            <div class="assistants-billing-address assistant-addres-view-<?php echo $assistant_user->ID; ?>">
                                <?php                                                                 
                                    echo '<p><span>First Name: </span>' . $assistant_user->first_name . '</p>';
                                    echo '<p><span>Last Name: </span>' . $assistant_user->last_name . '</p>';
                                    echo '<p><span>Email: </span>' . $user_email . '</p>';
                                ?>                                
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php
        }else{
            echo "<p>No assistants added yet.</p>";
        }
        echo "</div>";
        $out = ob_get_contents();
        ob_get_clean();
        return $out;
    }
    
    public function get_company_id_by_primary_or_alternate_admin()
    {
        $args = array(
            'posts_per_page'    => 1,
            'post_type'         => 'companies',
            'fields'            => 'ids',
            'meta_query' => array(
                'relation' => 'OR',
                array(
                    'key'     => 'primary_booth_admin',
                    'value'   => get_current_user_id(),
                    'compare' => 'LIKE',
                ),
                array(
                    'key'     => 'alternate_booth_admin',
                    'value'   => get_current_user_id(),
                    'compare' => 'LIKE',
                )
            )
        );
        $company_query = new WP_Query( $args );
        $company_id = (!empty($company_query->posts) && is_array($company_query->posts)) ? $company_query->posts[0] : false; 
        return $company_id;
    }

    public function delete_exhibitor_assistant()
    {
        $nonce = $_POST['nonce'];
        if ( ! wp_verify_nonce( $nonce, 'exhibitor_assistant' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
        }
        try {
            $get_assistants_ids = get_post_meta($_REQUEST['company_id'], 'assistants', true);
            if (is_array($get_assistants_ids) && in_array($_POST['assistant_id'], $get_assistants_ids)) {
                $updated_assistants_ids = array_diff($get_assistants_ids, array($_POST['assistant_id']));
                $assistants = update_post_meta($_REQUEST['company_id'], 'assistants', $updated_assistants_ids);
                if(!is_wp_error( $assistants ))
                    wp_send_json_success(array('result' => count(get_post_meta($_REQUEST['company_id'], 'assistants', true))));
            }
        } catch (\Throwable $th) {
            wp_send_json_error();
        }
        wp_send_json_error();
    }
}

function init_create_ssistant()
{
    global $create_attendees;
    $create_attendees = new ExhibitAssistantRegistration();
}

add_action('init', 'init_create_ssistant');
