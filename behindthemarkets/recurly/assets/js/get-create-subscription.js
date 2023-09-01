jQuery(document).ready(function($) { 
    $('#search_subscription_by_uuid').on('click', function(e){
        if($('#search-subscription').val() == '')
            return false;

        $.ajax({
            url: ajax_obj.ajaxurl,
            method: "POST",
            dataType: 'JSON',
            data: {
                'action': 'missing_subscription_request',
                'uuid' : $('#search-subscription').val(),
                'nonce' : ajax_obj.nonce
            },
            beforeSend: function () { 
                $('#loader').removeClass('hidden');
            },
            success:function(response) {
                $('#show-subscription').html('');
                $('#subscription-details').html('');
                if(response.success)
                {
                    $('#show-subscription').html(response.data);
                }else{
                    $('#show-subscription').html(response.message);
                }
            },
            complete: function () { 
                $('#loader').addClass('hidden');
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });  
    });   
    $('body').on('click', '#create_missing_subscription', function(e){
        
        let sub_data = $(this).data('subscription_data');
        sub_data['nonce'] = ajax_obj.nonce;
        $.ajax({
            url: ajax_obj.ajaxurl,
            method: "POST",
            dataType: 'JSON',
            data: sub_data,
            beforeSend: function () { 
                $('#loader').removeClass('hidden');
            },
            success:function(response) {
                $('#subscription-details').html('');
                $('#subscription-details').html(response.message);
            },
            complete: function () { 
                $('#loader').addClass('hidden');
            },
            error: function(errorThrown){
                console.log(errorThrown);
            }
        });  
    }); 
    $('body').on('change', '#existing_plans', function(){
        let btn = $('body').find('#create_missing_subscription');
        let subscription_data = btn.data('subscription_data');
        subscription_data['product_variation_id'] = parseInt($(this).val());
        // btn.data('subscription_data', '');
        subscription_data = JSON.stringify(subscription_data);
        btn.attr('data-subscription_data', subscription_data);
        $(btn).slideDown('slow');
    });
});