jQuery(document).ready(function($) { 
    $('#search_member_by_email').on('click', function(e){
        if($('#search-member').val() == '')
            return false;

        $.ajax({
            url: ajax_obj.ajaxurl,
            method: "POST",
            dataType: 'JSON',
            data: {
                'action': 'mailchimp_check_member',
                'id' : $('#search-member').val(),
                'nonce' : ajax_obj.nonce
            },
            beforeSend: function () { 
                $('#loader').removeClass('hidden');
            },
            success:function(response) {
                $('#show-member').html('');
                $('#member-details').html('');
                // if(response.success)
                // {
                //     $('#show-member').html(response.data);
                // }else{
                    $('#show-member').html(response.message);
                // }
            },
            complete: function () { 
                $('#loader').addClass('hidden');
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });  
    });   
    $('body').on('click', '.mailchimp-item-data input[type="radio"]', function(e){  
        $.ajax({
            url: ajax_obj.ajaxurl,
            method: "POST",
            dataType: 'JSON',
            data: {
                'action':'mailchimp_update_member',
                'id':$(this).data('mailchimp'),
                'list_id': $(this).attr('name'),
                'status': $(this).val(),
                'nonce': ajax_obj.nonce
            },
            beforeSend: function () { 
                $('#loader').removeClass('hidden');
            },
            success:function(response) {
                $('#update-response').html('');
                $('#update-response').html(response.message);
                $('#update-response').slideDown(); 
                setTimeout(function(){
                    $('#update-response').slideUp(); 
                }, 5000);
            },
            complete: function () { 
                $('#loader').addClass('hidden');
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });  
    }); 
});