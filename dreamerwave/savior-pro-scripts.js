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

jQuery(window).load(function() { 
	jQuery('.profile-field .wpuf-attachment-upload-filelist+*').next().remove();
	
	// Reset pass page btn 	
	if (jQuery('body').find(".login-sec-cls form.pp-login-form").hasClass("pp-login-form--reset-pass") || jQuery('body').find('.login-sec-cls form.pp-login-form').hasClass('pp-login-form--lost-pass')) {
		jQuery('body').find('.hide-login-btn').addClass('show-login-btn');
	}
	
});

jQuery(document).ready(function() {
	
	jQuery(".elementor-element.user-pg-list>.elementor-widget-container ul.elementor-icon-list-items>li.elementor-icon-list-item:last-child a").click(function(e){
		e.preventDefault();
		if(!e.detail || e.detail == 1){
			window.location.href= admin_ajax_url.logout_url;	
		}
	});
	
	jQuery('.account-setting-sec.elementor-widget ul.wpuf-form li.profile-field').remove();
		
	// Header user toggle js
	jQuery(".user-info-cls .elementor-image-box-wrapper").click(function(){
		jQuery(".elementor-element.user-pg-list>.elementor-widget-container").slideToggle();
	});
	
	// Login Label toggle js 	
	jQuery(".login-sec-cls form .elementor-form-fields-wrapper .elementor-field-type-text:nth-child(2)").append('<i aria-hidden="true" class="far fa-eye-slash"></i>');
	
	jQuery('.login-sec-cls form .elementor-form-fields-wrapper .elementor-field-type-text input#password+i.far').on('click', function() {
		jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
		var x = jQuery('.login-sec-cls form .elementor-form-fields-wrapper .elementor-field-type-text input#password').attr('type');
		if (x === "password") {
			jQuery('.login-sec-cls form .elementor-form-fields-wrapper .elementor-field-type-text input#password').attr('type', 'text');
		} else {
			jQuery('.login-sec-cls form .elementor-form-fields-wrapper .elementor-field-type-text input#password').attr('type', 'password');
		}
	});
	
	jQuery(".login-sec-cls form.pp-login-form.pp-login-form--reset-pass .pp-login-form-fields .pp-login-form-field.pp-field-group.pp-field-type-text").append('<i aria-hidden="true" class="far fa-eye-slash"></i>');

	jQuery('.login-sec-cls form.pp-login-form.pp-login-form--reset-pass input#password_1+i.far').on('click', function() {
		jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
		var x = jQuery('input#password_1').attr('type');
		if (x === "password") {
			jQuery('input#password_1').attr('type', 'text');
		} else {
			jQuery('input#password_1').attr('type', 'password');
		}
	});

	jQuery('.login-sec-cls form.pp-login-form.pp-login-form--reset-pass input#password_2+i.far').on('click', function() {
		jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
		var x = jQuery('input#password_2').attr('type');
		if (x === "password") {
			jQuery('input#password_2').attr('type', 'text');
		} else {
			jQuery('input#password_2').attr('type', 'password');
		}
	});
	
	// Sign Up Label password toggle js 
	jQuery(".signup-form-sec form ul li.wpuf-el.signup-password-field .wpuf-fields").append('<i aria-hidden="true" class="far fa-eye-slash"></i>');
	
	jQuery('.signup-form-sec form ul li.wpuf-el.signup-password-field  .wpuf-fields input#password_388_1+span+i.far').on('click', function() {
		jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
		var x = jQuery('input#password_388_1').attr('type');
		if (x === "password") {
			jQuery('input#password_388_1').attr('type', 'text');
		} else {
			jQuery('input#password_388_1').attr('type', 'password');
		}
	});
	
	jQuery('.signup-form-sec form ul li.wpuf-el.signup-password-field  .wpuf-fields input#password_388_2+span+i.far').on('click', function() {
		jQuery(this).toggleClass('fa-eye').toggleClass('fa-eye-slash'); // toggle our classes for the eye icon
		var x = jQuery('input#password_388_2').attr('type');
		if (x === "password") {
			jQuery('input#password_388_2').attr('type', 'text');
		} else {
			jQuery('input#password_388_2').attr('type', 'password');
		}
	});
	
	// Create New Post Button trigger 	
	jQuery("a.post_published_btn").click(function(e){
		e.preventDefault();
		jQuery(".post-publish-form-sec input.wpuf-submit-button").trigger("click");
	});
	
	jQuery(".post-update-form-sec a.post_published_btn").text("Update Post");
	
	if(jQuery(".post-publish-form-sec .elementor-shortcode>div").hasClass('wpuf-success')) {
        jQuery(".elementor-element.list-breadcumb-sec.elementor-widget").addClass("info-add");
    } 
	
	jQuery('.elementor-element.account-setting-sec.elementor-widget li .wpuf-label').click(function(e) { 
		jQuery(this).siblings('.wpuf-fields').slideToggle();
		jQuery(this).parents('li').toggleClass('hide-border');
	});
	
	// pricing pg string remove
	jQuery('.membership-sec .pmpro_level .pmpro_level-price').each(function(e){
		let textEl = jQuery(this).text().trim();
		jQuery(this).text(textEl.slice(0,-1));
	});
	
	// Draft Post js 	
	jQuery(".draft-post-hide-cls").find("table.items-table").find("tr").each(function()	{
		var txt = jQuery(this).find("td:eq(2)").find("span").text();
		if(txt == "Offline" || txt == "offline") {
			jQuery(this).remove();
		}
	});
	
	jQuery(".hide-live-post-cls").find("table.items-table").find("tr").each(function()	{
		var txt = jQuery(this).find("td:eq(2)").find("span").text();
		if(txt == "Live" || txt == "live") {
			jQuery(this).remove();
		}
	});
		
	
});



/**
 ** Capability To Customers Delete their own account
 **/ 

(function($){
	$('.delete_your_account a').on('click',function(e){
		e.preventDefault();
		var ajax_url  = admin_ajax_url.ajaxurl;
		var login_url = admin_ajax_url.siteurl;
		if(confirm('Are you sure you want to delete your account?')){
			$.ajax({
				type : "POST",
				url : ajax_url,
				data: {action : 'dreamerwave_delete_customers_account_account_setting'},
				success: function (res) {
					window.location.replace(login_url);
				}

			});
		}
	});
})(jQuery);

(function($){
	$('.logout_current_session').on('click',function(e){
		e.preventDefault();
		var ajax_url  = admin_ajax_url.ajaxurl;
		var login_url = admin_ajax_url.siteurl;
		if(confirm('Are you sure you want to logout your account?')){
			$.ajax({
				type : "POST",
				url : ajax_url,
				data: {action : 'wp_ajax_destroy_sessions'},
				success: function (res) {
					if(res.data){
						window.location.replace(login_url);
					}
				}

			});
		}
	});
})(jQuery);

// My Account Page Helpdesk post and Single js

jQuery(".profile-sec-cls span.account-text-cls").text("Update Profile");

jQuery('.helpdesk-post-caption .helpdesk-post-taxo-main-sec .taxonony-post-content').click(function(e) { 
    jQuery(this).siblings('.taxonomy-post-cap').slideToggle();
});

jQuery('body.single-helpdesk .breadcrumb-sec-cls ul.pp-breadcrumbs li.pp-breadcrumbs-item-custom-post-type-helpdesk a.pp-breadcrumbs-crumb').removeAttr("href");
jQuery('.elementor-element.post-comment-sec.elementor-widget .elementor-widget-container .comments-area .comment-form p.logged-in-as a:first-child, .reply-single-feature-img .post-feature-img a').removeAttr("href");

// Feed Comment Form
if (jQuery("body").hasClass("single-post")) {
	let input1 = document.getElementById('author');
	let input2 = document.getElementById('email');
	let textarea1 = document.getElementById('comment');
	document.getElementById("ast-commentform").addEventListener("submit", function(e){
		if ( !input1.value || !input2.value || !textarea1.value) {	// You should check other inputs if you have.
			e.preventDefault();
			alert('Please fill fields first');
			return false;		
		}
	});
}
/*
 * Trush post by frontend user
 */
(function($){
	$('.delete_btn_shortcode a').on('click', function( event ){
		event.preventDefault();
		if (confirm('Are you sure you want to delete this?')) {
			var url = new URL( $(this).attr('href') ),
				nonce = url.searchParams.get('_wpnonce'), // MUST for security checks
				postID = url.searchParams.get('post'),
				parent_post_ID = url.searchParams.get('parent_post_ID');
			$.ajax({
				method:'POST',
				url: admin_ajax_url.ajaxurl,
				data: {
					'action' : 'moverepliestotrash',
					'post_id' : postID,
					'parent_post_ID': parent_post_ID,
					'_wpnonce' : nonce
				},
				beforeSend : function(){
					$('.delete_btn_shortcode .elementor-shortcode').append('<i class="fas fa-sync fa-spin"></i>');
				},
				success: function (res) {
					if(res.success){
						window.location.href = res.data;
					}
				},
				error: function(){
					$('delete_btn_shortcode elementor-shortcode').find('i').remove();
				}
			});
		}
	});
	/*
	 * User name update
	 */
// 	$('.elementor-element.edit-profile-sec.elementor-widget li.username-field-cls .wpuf-label').addClass('change-user-name');
	$('.elementor-element.edit-profile-sec.elementor-widget li.username-field-cls .wpuf-label').click(function(e) { 
// 		$(this).toggleClass( 'change-user-name');
		if($(this).siblings().find('input').prop('disabled')){
			$(this).siblings().find('input').prop('disabled',false);
			$(this).siblings().find('input').focus();
			$(this).addClass('change-user-name').removeClass('update-user-name');
		}else{
			$(this).siblings().find('input').prop('disabled',true);
			$(this).addClass('update-user-name').removeClass('change-user-name');
		}		
	});
	$('body').on('click', '.wpuf-label.update-user-name', function( event ){
		$.ajax({
			method:'POST',
			url: admin_ajax_url.ajaxurl,
			data: {
				'action' : 'update_username',
				'user_login': $('input[name="user_login"]').val(),
				'_wpnonce' : admin_ajax_url.sitenonce
			},
			success: function (res) {
				if(res.success){
					window.location = admin_ajax_url.my_account;
				}
			},
			error: function(error){
				console.log(error)
			}
		});
	});
	
	setHeight($('.responded-article-sec-cls .pp-posts .pp-post-wrap.pp-grid-item-wrap>.pp-post.pp-grid-item, .replies-all-post-list .pp-post.pp-grid-item'));
	function setHeight(col) {
		var $col = $(col);
		var $maxHeight = 0;
		$col.each(function () {
			var $thisHeight = $(this).outerHeight();
			if ($thisHeight > $maxHeight) {
				$maxHeight = $thisHeight;
			}
		});
		$col.height($maxHeight);
	}
	
})(jQuery);


// Author Single Load More JS

function AddReadMore() {
	//This limit you can set after how much characters you want to show Read More.
	var carLmt = 180;
	// Text to show when text is collapsed
	var readMoreTxt = " ... Load More";
	// Text to show when text is expanded
	var readLessTxt = " Load Less";


	//Traverse all selectors with this class and manupulate HTML part to show Read More
	jQuery(".author-bio-desc-cls p").each(function() {
		if (jQuery(this).find(".firstSec").length)
			return;

		var allstr = jQuery(this).text();
		if (allstr.length > carLmt) {
			var firstSet = allstr.substring(0, carLmt);
			var secdHalf = allstr.substring(carLmt, allstr.length);
			var strtoadd = firstSet + "<span class='SecSec'>" + secdHalf + "</span><span class='readMore'  title='Click to Show More'>" + readMoreTxt + "</span><span class='readLess' title='Click to Show Less'>" + readLessTxt + "</span>";
			jQuery(this).html(strtoadd);
		}

	});
	//Read More and Read Less Click Event binding
	jQuery(document).on("click", ".readMore,.readLess", function() {
		jQuery(this).closest(".author-bio-desc-cls p").toggleClass("showlesscontent showmorecontent");
	});
}
jQuery(function() {
	//Calling function after Page Load
	AddReadMore();
});
