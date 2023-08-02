jQuery(document).ready(function(){
setTimeout(function(){
     //var tag_new = jQuery("template").eq(38).attr("id");
     var tag_new = jQuery("template").last().attr("id");
     //alert(tag_new);
     jQuery("#" + tag_new).css("display", "none");
     jQuery("#" + tag_new).next().next().css("display", "none");
}, 100);
});

jQuery(document).ready(function ($) {
	jQuery('#label_11_1027_1 .ceu-infor').hover(function(){
		$('.ceu-tooltip').show();
	},function(){
		$('.ceu-tooltip').hide();
	});

	/** On Click Show/Hide Social Icons **/
	jQuery('body').on('click', '.social-share-btn .elementor-icon', function () {
		jQuery(this).parents('.social-share-btn').next(".social-share-icons").toggle();
	});
	jQuery("body").on("click", ".paper-by-year-drop", function (e) {
		e.preventDefault();
		jQuery(".drop-hide-cls").slideToggle('fast');
	});
	if ($("input[name='paper_by_year[]']").is(':checked')) {
		$("#paper_by_year_filter").slideDown('fast');
	}
	jQuery(".drop-hide-cls").hide();
	jQuery("input[name='paper_by_year[]']").on("click", function () {
		if ($("input[name='paper_by_year[]']").is(':checked')) {
			$("#paper_by_year_filter").slideDown('fast');
		} else {
			$("#paper_by_year_filter").slideUp('fast');
		}
	});
	$("#paper_by_year_filter button").on("click", function () {
		$(this).append('<span class="asgmt-loader"></span>');
		$(this).attr('disabled', 'disabled');
	});
	jQuery('.sign-in-cls .tp-lost-password').on('click', function () {
		//jQuery(this).closest('.sign-in-sec-cls-top').css({ 'background-image': 'url("https://asgmt.mysites.io/wp-content/uploads/2023/04/forgot-bg.jpg")' });
		jQuery('.form-change-heading-cls h2').text('Forgot Your Password');
		jQuery('.forgot-heading-cls').show();
		jQuery('.form-hide-heading-cls').hide();
	});
	jQuery('.sign-in-cls .tp-lpu-back').on('click', function () {
		//jQuery(this).closest('.sign-in-sec-cls-top').css({ 'background-image': 'url("https://asgmt.mysites.io/wp-content/uploads/2023/03/sign-in-bg.png")' });
		jQuery('.form-change-heading-cls h2').text('Sign In Your Account');
		jQuery('.forgot-heading-cls').hide();
		jQuery('.form-hide-heading-cls').show();
	});

	jQuery('.elementor-element.elementor-element-bb7dd38 .elementor-image-box-content, .elementor-element.elementor-element-565d4cf li:last-child').hover(function () {
		jQuery('.dashboard-dropdown').slideDown('fast');
	}
	);
	jQuery('body').on('mouseleave', '.dashboard-dropdown', function (e) {
		if (!jQuery(this).is(e.target)) {
			jQuery(this).fadeOut();
		}
	});

	if (jQuery('body').hasClass('page-id-18338')) {
		jQuery('.custom-order-table .my_account_orders').DataTable({
			info: false,
			searching: false,
			"lengthChange": true,
			"bPaginate": false,
			dom: 'Bfrtip',
			buttons: [
				{
					extend: 'pdfHtml5',
					title: 'ASGMT Invoices',
					text: '<i class="elementor-icons-manager__tab__item__icon fa fa-file-pdf-o"></i>',
					titleAttr: 'PDF',
					customize: function (doc) {
						doc.content[1].table.widths =
							Array(doc.content[1].table.body[0].length + 1).join('*').split('');
						doc.styles.tableHeader.fontSize = 14;
						doc.defaultStyle.fontSize = 12;
						doc.defaultStyle.alignment = 'center';
					}

				}
			]
		});
	}

	let page_url = window.location.href;
	jQuery('.menu-hover-box').each(function () {
		let sidebar_link_href = jQuery(this).find('a').attr('href');
		if (sidebar_link_href == page_url) {
			jQuery(this).addClass('active');
		}

	});
	/**********************************/
	/** MAIN MENU DROPDOWN SECTIONS TOGGLE **/

	if (jQuery(window).width() > 1024) {
		jQuery("#menu-main-menu li a").hover(function (e) {
			jQuery(this).toggleClass("highlighted");
			jQuery(`.about-submenu, .school-submenu, .papers-submenu`).hide();
			if (jQuery(this).hasClass('has-submenu')) {
				jQuery(`.${jQuery(this).text().toLowerCase()}-submenu`).show();
			}
		});

		jQuery('body').on('mouseleave', '.about-submenu, .school-submenu, .papers-submenu', function (e) {
			if (!jQuery(this).is(e.target)) {
				jQuery(this).fadeOut();
			}
		});
	}
	
	if (window.location.href.indexOf("gfur_activation") > -1) {
		jQuery('.lead-in').prev().addClass('custom-email-confirmation-heading');
		jQuery('#signup-welcome').prev().addClass('custom-email-confirmation-heading');
		jQuery('.custom-email-confirmation-heading, .lead-in, .view, #signup-welcome').wrapAll('<div class="custom-email-confirmation"></div>');
		jQuery('.ast-container').css('justify-content', 'center');
		jQuery('.lead-in, .view').html('<span>Please <a href="https://asgmt.com/sign-in">login</a> with your account or go back to <a href="https://asgmt.com/">homepage</a>.</span>')
	}

});
/** mCustom Scrollbar Init **/
jQuery(window).on("load", function () {
	jQuery(".drop-hide-cls form ul").mCustomScrollbar({
		scrollButtons: { enable: false },
		theme: "dark-thin",
		autoHideScrollbar: false,

	});
	if (jQuery(window).width() > 767){
		jQuery(".list-span-text").mCustomScrollbar({
			scrollButtons: { enable: false },
			theme: "dark-thin",
			autoHideScrollbar: false,

		});
	}
});

/**
 * Repeater fields on registration page
 */
jQuery(document).on('gform_post_render', function (event, form_id, current_page) {
	let available_seats = asgmt_ajax_object?.stock_quantities;
	jQuery('body').on('change', 'input[value="22101"], input[value="22102"]', function(){
		let stock_quantity = jQuery(this).val() == 22101 ? available_seats?.stock_quantity_22101 : available_seats?.stock_quantity_22102;
		let labelColor = stock_quantity > 0 ? '#008000' : '#FF0000';
		let labelText = stock_quantity > 0 ? `In-person - <b style="color:${labelColor};">Available seats ${stock_quantity}</b>` : `In-person - <b style="color:${labelColor};">Sold Out, you can not book!</b>`;
	  	let $this = jQuery(this);
		let parentElement = $this.parents('#field_11_1027').length > 0 ? '.choose-registration-row' : '.gfield_repeater_item';
		setTimeout(() => {
			$this.parents(parentElement).find('input[value="in_person"]').next('label').html(labelText);
		}, 300);
	});
	if( form_id == 13 )
	{
		let field_13_1028 =  jQuery('.gfield_repeater_items #field_13_1028').parent('.gfield_repeater_cell');
		field_13_1028.slideUp();
		jQuery('input[name="input_1005[0]"]').on('change', function(){
			if(jQuery(this).val() == 22101 || jQuery(this).val() == 22102)
			{				
				field_13_1028.slideDown();
				setTimeout(() => {
					jQuery('#label_13_1028_0').trigger('click');
				}, 300);
			}else{
				field_13_1028.slideUp();
			}
		});
	}

	if (jQuery('input[name="input_1034"]:checked').val() === 'yes') {
		jQuery('#field_11_1000').show();
		jQuery('.gchoice_11_1034_1').show()
	} else {
		jQuery('#field_11_1000').hide();
		jQuery('.gchoice_11_1034_1').hide();
	}
	jQuery('input[name="input_1034"]').change(function () {
		if (jQuery(this).val() === 'yes') {
			jQuery('#field_11_1000').slideDown();
			jQuery('.gchoice_11_1034_1').show()
		} else {
			var confirmResult = confirm('Are you sure you want to remove this fields?');
			if (!confirmResult) {
				jQuery('#choice_11_1034_0').click();
				return;
			}
			jQuery('#field_11_1000').slideUp();
			jQuery('.gchoice_11_1034_1').hide();
			jQuery(this).parent('.gchoice_11_1034_1').hide();
		}
	});

	if (jQuery('#choice_11_1005_2-0').is(':checked') || jQuery('#choice_11_1005_3-0').is(':checked')) {
		jQuery('body').find('#field_11_1028').parent('.gfield_repeater_cell').slideDown();
	} else {
		jQuery('body').find('#field_11_1028').parent('.gfield_repeater_cell').slideUp();
		jQuery('input[name="input_1028[0]"]').prop('checked', false);
	}
	jQuery('input[name="input_1005[0]"]').on('change', function () {
		if (jQuery(this).attr('id') == 'choice_11_1005_2-0' || jQuery(this).attr('id') == 'choice_11_1005_3-0') {
			jQuery('#field_11_1028').parent('.gfield_repeater_cell').slideDown();
			jQuery('input[name="input_1028[0]"]').prop('checked', true);
		} else {
			jQuery('#field_11_1028').parent('.gfield_repeater_cell').slideUp();
			jQuery('input[name="input_1028[0]"]').prop('checked', false);
		}
	});
	jQuery('#choice_11_1005_0-0').prop('checked', true);
});
if(typeof gform !== 'undefined' && (jQuery('body').hasClass('page-id-20551') || jQuery('body').hasClass('page-id-24902'))){
	gform.addFilter('gform_repeater_item_pre_add', function (clone, item) {
		let available_seats = asgmt_ajax_object?.stock_quantities;
		// clone.find('input[value="22101"], input[value="22102"]').on('change', function(){
		// 	let seats = jQuery(this).val() == 22101 ? `In-person - Available seats ${available_seats?.stock_quantity_22101}` : `In-person - Available seats ${available_seats?.stock_quantity_22102}`;
		// 	jQuery('input[value="in_person"]').next('label').text(seats);
		// });
		let choice_11_1005_ = clone.find("[name^='input_1005']");
		clone.find('.gfield_repeater_cell:eq(4)').hide();
		if (typeof choice_11_1005_ !== undefined) {
			choice_11_1005_.each(function (field) {
				let inputFields = clone.find('input[type="radio"]').not("[name^='input_1028']");
				Array.prototype.map.call(inputFields, (inputField, index) => {
					inputField.addEventListener("change", (event) => {
						let pEl = event.target.getAttribute("id");
						let course_type = clone.find(".gfield_repeater_cell");
						let fourthChildNode = course_type[4];
						if (event.target.value == 22101 || event.target.value == 22102) {
							fourthChildNode.style.display = 'block';
							if (fourthChildNode.querySelector('input[type="radio"]')) {
								fourthChildNode.querySelector('input[type="radio"]').checked = true;
							}
						} else {
							fourthChildNode.style.display = 'none';
							if (fourthChildNode.querySelector('input[type="radio"]')) {
								fourthChildNode.querySelector('input[type="radio"]').checked = false;
							}
						}
					});
				});
			});
		}
		return clone;
	});
}

/** Gform after submission **/
jQuery(document).on('gform_post_render', function(event, form_id, current_page){    
	if ((form_id == 7)) {
		if(typeof current_page  === "undefined") {
			jQuery(".elementor-element.elementor-element-62c25d9").css('display', 'none');
		}
	}
});