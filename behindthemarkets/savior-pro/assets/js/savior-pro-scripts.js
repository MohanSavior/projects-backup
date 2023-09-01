(function($){
	$(window).on("load",function(){
		$(".scroll-on-content, .up-down-plans").mCustomScrollbar({
			scrollButtons:{enable:false},
			theme:"dark-thin",
			autoHideScrollbar: false,
		});
		$(".hm-product-main-desc-cls .elementor-widget-container").mCustomScrollbar({
			scrollButtons:{enable:false},
			theme:"dark-thin",
			autoHideScrollbar: false,
		});
	});
	$(document).ready(function() {
		$('.remove-on-load').remove();
		$('.tablepress').each(function(index, value){
			var tablepress = $(this);			
			if(tablepress.find('tr td[colspan]')){
				 tablepress.find('tr td[colspan]').each(function(i, val){
					 var colspan_val = $(this).attr('colspan');
					for(let i = 1; i < colspan_val; i++ ){
						$('<td style="display: none;"></td>').insertAfter($(this));
					}
				 });
			}
		});
		
		
		
		/*if ( $.fn.DataTable.isDataTable('.article-content .tablepress') ) {
			$('.article-content .tablepress').DataTable().destroy();
		}*/
		/*$('.article-content .tablepress').dataTable({
			"stripeClasses":["even","odd"],
			"ordering":true,
			"paging":false,
			"info":false,
			"scrollX":true,
			"initComplete": function(settings, json) {
				//console.log('initComplete');
				var th_count = 0;
				var th_empty_count = 0;
				$('.article-content .tablepress').each(function(index, value){
					var tablepress = $(this);
					tablepress.find('tr th').each(function(i, val){
						if ($(this).html() == '&nbsp;') {
							th_empty_count++;
							var colspan = $(this).prev().attr('colspan');
							$(this).prev().attr('colspan', parseInt(colspan) + 1);
							$(this).remove();
						}
						th_count++;
						//var col_number = th_count - th_empty_count.toString();
						//tablepress.find('tr th.column-'+ col_number).attr('colspan', th_empty_count+1);
					});
				});
			}
		});*/
		$('body').on('click', '.print-table-popup', function(){
			var prtContent = document.getElementById("table_full_view_main_cls");
			var WinPrint = window.open('', '', 'left=0,top=0,width=1200px,height=900,toolbar=0,scrollbars=0,status=0');
			WinPrint.document.write(prtContent.innerHTML);
			WinPrint.document.close();
			WinPrint.focus();
			WinPrint.print();
			WinPrint.close();
		});
	} );
	
})(jQuery);


/**
 ** 404 Page Breadcrum Text
 **/

jQuery(document).ready(function() {
	jQuery("body.error404 li.pp-breadcrumbs-item.pp-breadcrumbs-item-current").text("Page not found");
});

/**
 ** Faq Page Js Start
 **/

jQuery('.faq-search-sec form.elementor-search-form').on('submit', function(e) {
	e.preventDefault(e);
	var texto = jQuery(this).find("input.elementor-search-form__input").val();
	filtro(texto);
});
	
function filtro(texto) {
	var lista = jQuery(".faq-main-sec-cls .pp-accordion-item").hide()
	.filter(function(){
	
		var item = jQuery(this).find('.pp-accordion-title-text').text();
		var padrao = new RegExp(texto, "i");

		   return padrao.test(item);
		  
// 		return padrao.test(item) == true ? padrao.test(item) :'Not Found Anything';
	}).closest(".pp-accordion-item").show();

}

jQuery(document).ready(function() {
	jQuery(".browser-dropdown .browser-select-main").click(function(){
		jQuery("select#browserSelect").trigger("change");
	});
	
	jQuery('#browserSelect').on('change', function() {
		if(jQuery(this).val() == 'yahoo'){
			jQuery("section#yahoo-section").css("display", "block");
			jQuery("section#gmail-section").css("display", "none");
			jQuery("section#mac-section").css("display", "none");
			jQuery("section#outlook-section").css("display", "none");
		} else if(jQuery(this).val() == 'mac'){
			jQuery("section#yahoo-section").css("display", "none");
			jQuery("section#gmail-section").css("display", "none");
			jQuery("section#mac-section").css("display", "block");
			jQuery("section#outlook-section").css("display", "none");
		} else if (jQuery(this).val() == 'outlook') {
			jQuery("section#yahoo-section").css("display", "none");
			jQuery("section#gmail-section").css("display", "none");
			jQuery("section#mac-section").css("display", "none");
			jQuery("section#outlook-section").css("display", "block");
		} else {
			jQuery("section#yahoo-section").css("display", "none");
			jQuery("section#gmail-section").css("display", "block");
			jQuery("section#mac-section").css("display", "none");
			jQuery("section#outlook-section").css("display", "none");
		}
	});
	
	jQuery("#browserSelect").select2({
		templateResult: function (idioma) {
			var $span = jQuery("<span><img src='http://behindthemarkets.saviormarketing.com/wp-content/uploads/2021/08/" + idioma.id + ".png'/> " + idioma.text + "</span>");
			return $span;
		},
		templateResult: function (idioma) {
			var $span = jQuery("<span><img src='http://behindthemarkets.saviormarketing.com/wp-content/uploads/2021/08/" + idioma.id + ".png'/> " + idioma.text + "</span>");
			return $span;
		},
		templateResult: function (idioma) {
			var $span = jQuery("<span><img src='http://behindthemarkets.saviormarketing.com/wp-content/uploads/2021/08/" + idioma.id + ".png'/> " + idioma.text + "</span>");
			return $span;
		},
		templateSelection: function (idioma) {
			var $span = jQuery("<span><img src='http://behindthemarkets.saviormarketing.com/wp-content/uploads/2021/08/" + idioma.id + ".png'/> " + idioma.text + "</span>");
			return $span;
		}
	});

});

/**
 ** Faq Page Js End
 **/

/**
 ** Equal Height Section js
 **/
	if (jQuery(window).width() >= 0) {
		jQuery.fn.equalHeights = function(){
			var max_height = 0;
			jQuery(this).each(function(){
				max_height = Math.max(jQuery(this).height(), max_height);
			});
			jQuery(this).each(function(){
				jQuery(this).height(max_height);
			});
		};
		jQuery('.first_row_column').equalHeights();
		jQuery('.second_row_column').equalHeights();
		jQuery('.third_row_column').equalHeights();
		jQuery('.fourth_row_column').equalHeights();
		jQuery('.fifth_row_column').equalHeights();
		jQuery('.sixth_row_column').equalHeights();
		jQuery('.seventh_row_column').equalHeights();
		jQuery('.eight_row_column').equalHeights();
		jQuery('.nine_row_column').equalHeights();
	}

/**
 ** Equal Height Section js
 **/

/**
 ** You Account js
 **/

jQuery(".address-heading-cls").click(function(){
	jQuery(".address-form-cls").slideToggle();
	jQuery(".account-form-cls").slideToggle();
});

jQuery(".account-heading-cls").click(function(){
	jQuery(".address-form-cls").slideToggle();
	jQuery(".account-form-cls").slideToggle();
});

jQuery(".your-account-header-menu ul.pp-list-items.pp-inline-items").click(function(){
	jQuery(".your-account-menu").slideToggle();
});

jQuery('p.no_subscriptions a.woocommerce-Button.button').each(function(){
	this.href = this.href.replace("/shop", "");
});

jQuery('p.return-to-shop>a.button.wc-backward').each(function(){
	this.href = this.href.replace("/shop", "");
});

/**
 ** You Account js
 **/


/**
 ** Product Dashboard Page Link js
 **/

jQuery(document).ready(function($){
	if($('body').hasClass('page-id-13') || $('body').hasClass("page-id-2730"))
	{
		jQuery.ajax({
			type : "post",
			dataType : "json",
			url : ajax_obj.admin_url,
			data : {action: "getProductTitle"},
			success: function(response) {
				if(response.success) {
					jQuery('.additional_products .pp-grid-item-wrap').each(function(){
						const resObject = response.data.data.find(item => item.id == jQuery(this).find('section').attr('id'));
						jQuery(this).find('a.elementor-button-link').attr('href', resObject.link.replace('/product',''));
						jQuery(this).find('.hm-product-title-cls .elementor-heading-title a').attr('href', resObject.link.replace('/product',''));
					});
				}
			}
		})
	}
// 	var prod = ajax_obj.getProductTitle;
// 	jQuery('.additional_products .pp-grid-item-wrap').each(function(){
// 		const resObject = prod.find(item => item.id == jQuery(this).find('section').attr('id'));
// 		jQuery(this).find('a.elementor-button-link').attr('href', resObject.link.replace('/product',''));
// 		jQuery(this).find('.hm-product-title-cls .elementor-heading-title a').attr('href', resObject.link.replace('/product',''));
		
// 	});
	jQuery('.additional_products .pp-grid-item-wrap a.elementor-button-link span.elementor-button-text').text('Learn More');
});

/**
 ** Product Dashboard Page Link js
 **/

/**
 ** Login Page Link js
 **/

jQuery(".login-sec form.pp-form .elementor-form-fields-wrapper>.elementor-field-type-text:nth-child(2)").append('<i aria-hidden="true" class="far fa-eye-slash"></i>');
jQuery(".login-sec form.pp-login-form.pp-login-form--reset-pass .pp-login-form-fields .pp-login-form-field.pp-field-group.pp-field-type-text").append('<i aria-hidden="true" class="far fa-eye-slash"></i>');

jQuery('input#password+i.far').on('click', function() {
	jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
	var x = jQuery('input#password').attr('type');
	if (x === "password") {
		jQuery('input#password').attr('type', 'text');
	} else {
		jQuery('input#password').attr('type', 'password');
	}
});

jQuery('input#password_1+i.far').on('click', function() {
	jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
	var x = jQuery('input#password_1').attr('type');
	if (x === "password") {
		jQuery('input#password_1').attr('type', 'text');
	} else {
		jQuery('input#password_1').attr('type', 'password');
	}
});

jQuery('input#password_2+i.far').on('click', function() {
	jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
	var x = jQuery('input#password_2').attr('type');
	if (x === "password") {
		jQuery('input#password_2').attr('type', 'text');
	} else {
		jQuery('input#password_2').attr('type', 'password');
	}
});

// 

jQuery(".login-form-cls.elementor-widget .tp-wp-lrcf form.tp-form-stacked .tp-field-group.tp-user-login-password a.tp-lost-password").removeAttr("href");

jQuery(".login-form-cls.elementor-widget .tp-wp-lrcf form.tp-form-stacked .tp-field-group.tp-l-lr-password .tp-form-controls, .login-form-cls.elementor-widget form.tp-form-stacked-reset .tp-ulp-input-group").append('<i aria-hidden="true" class="far fa-eye-slash"></i>');

jQuery('.login-form-cls input[name="pwd"]+i.far, .login-form-cls input[name="user_reset_pass"]+i.far').on('click', function() {
	jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
	var x = jQuery('.login-form-cls input[name="pwd"], .login-form-cls input[name="user_reset_pass"], .login-form-cls input[name="user_reset_pass_conf"]').attr('type');
	if (x === "password") {
		jQuery('.login-form-cls input[name="pwd"], .login-form-cls input[name="user_reset_pass"], .login-form-cls input[name="user_reset_pass_conf"]').attr('type', 'text');
	} else {
		jQuery('.login-form-cls input[name="pwd"], .login-form-cls input[name="user_reset_pass"], .login-form-cls input[name="user_reset_pass_conf"]').attr('type', 'password');
	}
});

if (jQuery(".login-form-cls.elementor-widget .tp-reset-pass-form form").hasClass("tp-form-stacked-reset")) {
  jQuery(".elementor-2879 .elementor-element.elementor-element-2a8a8cd").remove();
}

/**
 ** Login Page Link js
 **/

jQuery("a#more-btn").click(function(e){
	e.preventDefault(e);
	jQuery("span#more").slideToggle();
	jQuery('a#more-btn').text(jQuery('a#more-btn').text() === 'more' ? "less" : "more");
});

/**
 ** Articles Search
 **/

jQuery('.stock-portfolio-latest-sec .pp-post-wrap').each(function(){
    var link = jQuery(this).find('.elementor-heading-title a').attr('href');
    if(link == '#'){
        jQuery(this).hide();
    }
});

/**
 ** Membership My Account Redirection
 **/

jQuery(document).ready(function() {
	if (window.location.href.indexOf("my-membership-content") > -1) {
		window.location.href = `${ajax_obj.home_url}/your-account/`; 
	}
});
/**
 ** Search form additional input field
 **/

jQuery(document).ready(function(){
	var getCurrentPostID = ajax_obj.getCurrentPostID;
	//console.log(getCurrentPostID);
	if(typeof getCurrentPostID !== 'undefined'){
		var fieldHTML = `
			<input type="hidden" class="ee-form__field__control--search" name="current_post_id" value="${getCurrentPostID}">
		`;
		jQuery('.blog-search-form .ee-search-form').append(fieldHTML);	
	}
	
});

//welcome mail send
async function log(url,i) { 
	let result;
	try {
		result = await jQuery.ajax({
			type: 'POST',
			url: ajax_obj.admin_url,
			data : {action: "get_data",email: url,count: i},
			dataType: 'json',
		});
        console.log(result);
		return result;
	} catch (error) {
		console.error(error);
	}
}

jQuery(document).ready(function($) {
	let email_daily_count = 1;
	function welcome_email(){
		var data = {
			'action': 'welcome_email_template_mail'
		};
		if(email_daily_count <= 5000 ){
			// We can also pass the url value separately from ajaxurl for front end AJAX implementations
			jQuery.post(ajax_obj.admin_url, data, function(response) {
				//console.log('Got this from the server: ' + response);
				response = JSON.parse(response);
				if(response.success && response.username != ''){
					if(response.email_sent == true){
						jQuery('.email_response').append('<p>Welcome Credentials has been sent to: ' + response.email + '<p>');
					}else{
						jQuery('.email_response').append('<p>Welcome Credentials has not been sent to ' + response.email + ' because of no active subscription.<p>');
					}
					
					email_daily_count = email_daily_count + 1;
					setTimeout(function(){
						welcome_email();
					}, 3000);
				}
			});
		}
	}
	$("#ajax_mail").click(function(){
		//setTimeout(run(), 10000);
		welcome_email();
	}); 
});

jQuery(document).ready(function(){

setTimeout(function(){

     //var tag_new = jQuery("template").eq(38).attr("id");
     var tag_new = jQuery("template").last().attr("id");
     console.log(tag_new);

     //alert(tag_new);
     jQuery("#" + tag_new).css("display", "none");
     jQuery("#" + tag_new).next().next().css("display", "none");
}, 100);

});

jQuery(document).ready(function(){
	if (jQuery(".elementor-element.elementor-element-55c3202").hasClass("welcome-video-active")) {
		jQuery(".elementor-element.elementor-element-d0bb33f").removeClass("recent-alerts-active");
	}
	if (jQuery(".elementor-element.elementor-element-55c3202").hasClass("bonus-reports")) {
		jQuery(".elementor-element.elementor-element-d0bb33f").removeClass("recent-alerts-active");
	}
});

// 
var $j = jQuery.noConflict();
// On Document Ready and Ajax Complete
$j(document).on('ready', function($) {
	$j('.table_full_view').on('click', function(){
		var modal = document.getElementById(`table_full_view_main_cls`);
		modal.classList.toggle("modal-open");
	});
	$j('body').find('.close').on('click', function(){
		$j(this).parents('.modal').toggleClass("modal-open");
	});
});
/************ Search Tags *************/
if(jQuery('body').hasClass('single-btm_products')){
	jQuery('.tablepress').find('td a').each(function(){
		if(jQuery(this).attr('href').includes('tags')){
			var path = location.pathname;
			var getTag = jQuery(this).attr('href').split("/").slice(-2);
			jQuery(this).attr('href',`https://behindthemarkets.com${path}?search_tags=${getTag[0]}`);
		}
	});
}

// Join Wishlist jquery
jQuery(document).on('gform_confirmation_loaded', function(event, form_id, current_page){

	if(form_id == 7){
		jQuery(".join-list-bg-img-col-cls").addClass("form-submit-bg-col-cls");
		jQuery(".join-list-form-col-cls").addClass("form-submit-sucess-cls");
		jQuery(".join-waitlist-main-sec").addClass("form-submit-sucess-main-cls");
		jQuery("#elementor-popup-modal-166452>.dialog-widget-content").addClass("form-submit-sucess-popup-cls");
		
	}

});