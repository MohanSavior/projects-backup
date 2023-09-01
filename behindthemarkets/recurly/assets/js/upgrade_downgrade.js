jQuery(document).ready(function($) {
	/*Admin side*/
    const $this = jQuery('[id^=upgrade_downgrade_]');
    const change_plan_btn = jQuery('#change-plan');
// 	console.log($this.val());
    ($this.val() != null) ? change_plan_btn.show(): change_plan_btn.hide();
    $(document).on('change', '[id^=upgrade_downgrade_]', function() {
        change_plan_btn.slideDown('slow');
    });
    $(document).on('click', '#change-plan', function() { //[id^=upgrade_downgrade_]
        const plan_code = $this.val();
        const recurly_sub_id = $this.find(':selected').data('recurly_sub_id');
        const recurly_sub_nonce = $this.find(':selected').data('recurly_sub_nonce');
        const user_id = $this.find(':selected').data('user_id');
        const order_id = $this.find(':selected').data('order_id');
        const subscription_id = $this.find(':selected').data('subscription_id');
		const product_id = $this.find(':selected').data('product_id');
        // console.log(`${plan_code}: ${recurly_sub_id}: ${recurly_sub_nonce}: ${user_id}: ${order_id}: ${subscription_id}: `);
        // return false;
        if (confirm('Sure do you want to change plan?')) {
            $.ajax({
                type: 'post',
                dataType: "json",
                url: updownAjax.ajaxurl,
                data: {
                    action: 'recurly_switch_plan_or_subscription_create',
                    plan_code: plan_code,
                    recurly_sub_nonce: recurly_sub_nonce,
                    recurly_sub_id: recurly_sub_id,
                    order_id: order_id,
                    subscription_id: subscription_id,
                    product_id: product_id,
                    user_id: user_id
                },
                beforeSend: function() {
                    change_plan_btn.attr('disabled', true).find('div').addClass('lds-dual-ring');
                },
                success: function(result) {
                    change_plan_btn.attr('disabled', false).find('div').removeClass('lds-dual-ring');
                    console.log(result);
                    if (result.type == 'success') {
                        console.log(result.type.url);
                        window.location.href = result.url;
                    } else {
                        alert(result.message);
                    }
                },
                error: function(error) {
                    change_plan_btn.attr('disabled', false).find('div').removeClass('lds-dual-ring');
                    alert(error);
					console.log(error);
                }
            })
        } else {
            alert('Why did you press cancel? You should have confirmed.');
        }
    });
	/*User side*/
	jQuery('.woocommerce-MyAccount-content a.wcs-switch-link').attr('href','javascript:void(0)');
	$( "#request_up_down" ).hide();
	jQuery('.woocommerce-MyAccount-content a.wcs-switch-link, #request_up_down .close-form').on('click',function(){
// 		$( "#request_up_down" ).toggleClass( "request-form-active",1000 );
		$( "#request_up_down" ).fadeToggle( "slow", "linear" );
	});
});