// Share Btn
jQuery(document).ready(function ($) {

	// ADD Comma JS

	jQuery(".price-sec-cls span.pp-second-text").each(function () {
		var post_price_int = jQuery(this).text().replace(/[^\d.]/g, '');
		post_price_int = parseInt(post_price_int);
		jQuery(this).text(`$${post_price_int.toLocaleString()}`);
	});

	jQuery(".share-icon-cls").click(function (e) {
		e.preventDefault();
		jQuery(".post-share-btn").slideToggle();
	});

	// Current Page URL Copy 	
	var $temp = jQuery("<input>");
	var $url = jQuery(location).attr('href');

	jQuery('.copy-link-cls a').on('click', function (e) {
		e.preventDefault();
		jQuery("body").append($temp);
		$temp.val($url).select();
		document.execCommand("copy");
		$temp.remove();
	});

	// Header user toggle js
	jQuery(".user-info-cls .elementor-image-box-wrapper").click(function (e) {
		e.preventDefault();
		jQuery(".elementor-element.user-pg-list>.elementor-widget-container").slideToggle();
	});

	// Marketplace Filter Result js
	jQuery(".all-filter-btn-cls").click(function (e) {
		e.preventDefault();
		jQuery(".elementor-element.filter-result-main-sec.elementor-widget").slideDown();
	});

	jQuery(".filter-result-box-main-cls a.filter-close-btn").click(function (e) {
		e.preventDefault();
		jQuery(".elementor-element.filter-result-main-sec.elementor-widget").slideUp();
	});

	// Forgot Form text 	
	jQuery(".login-form-cls a.tp-lost-password").on("click", function () {
		jQuery('.elementor-773 .elementor-element.elementor-element-5978d54 .pp-first-text').text("Reset your password");
	});

	jQuery(".login-form-cls a.tp-lpu-back").on("click", function () {
		jQuery('.elementor-773 .elementor-element.elementor-element-5978d54 .pp-first-text').text("Welcome back to S&D");
	});
	//Watchlists slideToggle 
	$(".post-like-btn-cls .wp_ulike_btn").on("click", function (e) {
		$(".watchlists-tags-main-sec").slideToggle("fast");
	});
	$(".add-post-tag-cls a").on("click", function (e) {
		e.preventDefault();
		$(".post-single-like-btn-cls .watchlists-tags-main-sec").slideToggle("fast");
	});

	$("#watchlists-post-other").prop('checked', false);
	/*CLICK ON OTHER CHECK BOX*/
	$("#watchlists-post-other").on("click", function (e) {
		$(".watchlists-tags-main-sec ul li:last-child").slideToggle('fast');
		$(".watchlists-tags-main-sec").mCustomScrollbar("scrollTo", "bottom");
	});

	if (jQuery('body').filter('.single-startup.logged-in').length > 0) {
		$(".watchlists-tags-main-sec").mCustomScrollbar();
	}
	/**
	 * Hart Icon 
	 */
	const hartIcon = Array.prototype.slice.call(document.getElementsByName("watchlists-post-tag")).filter(ch => ch.checked == true);
	hartIcon.length > 0 ? jQuery('.add-post-tag-cls i').css('color', '#a71c44') : jQuery('.add-post-tag-cls i').css('color', '#90939E');
	
	/*ADD NEW TAG BUTTON CLICK*/
	jQuery("#watchlists-add-tags").on("click", function (e) {
		e.preventDefault();
		let inputVal = $('#watchlists-post-other-val');
		if (inputVal.val() == '') return;
		if (checkDuplicatTagName()) return;

		let addButton = $("#watchlists-add-tags");
		let post_id = $('#watchlists-post-id').val();

		jQuery.ajax({
			type: "post",
			dataType: "json",
			url: savior_ajax_obj.ajaxurl,
			data: {
				action: "add-startup-tags",
				post_id: post_id,
				newstartup_tags: inputVal.val(),
				nonce: savior_ajax_obj.nonce
			},
			beforeSend: function () {
				addButton.find(':last-child').show();
				addButton.prop('disabled', true);
			},
			success: function (response) {
				if (response.success) {
					$('body').find(".watchlists-tags-main-sec ul li:nth-last-child(3)").after(`<li class="added-new-tag"><label for="watchlists-post-tag-${response.data.post_id}"><input type="checkbox" id="watchlists-post-tag-${response.data.post_id}" value="${response.data.post_id}" name="watchlists-post-tag" checked="checked" data-name="${inputVal.val()}"> ${inputVal.val()} </label></li>`);
					inputVal.val('');
					setTimeout(() => {
						$('body').find('.watchlists-tags-main-sec ul li.added-new-tag').css('background-color', '#fafbfc');
					}, 500);
				}
				addButton.find(':last-child').hide();
				addButton.prop('disabled', false);
			}
		});
	});
});



const checkDuplicatTagName = () => {
	const checkboxes = document.getElementsByName("watchlists-post-tag");
	const selectedCboxes = Array.prototype.slice.call(checkboxes).map(ch => ch.getAttribute("data-name").toLowerCase());
	const otherEl = document.getElementById("watchlists-post-other-val");
	if (selectedCboxes.includes(otherEl.value.toLowerCase())) {
		otherEl.style.border = "1px solid red";
		setTimeout(() => {
			otherEl.style.border = "";
		}, 3000);
		return true;
	} else {
		return false;
	}
};
jQuery(document).on("click", function (event) {
	var $trigger = jQuery(".user-info-cls");
	if ($trigger !== event.target && !$trigger.has(event.target).length) {
		jQuery(".user-pg-list .elementor-widget-container").slideUp("fast");
	}
});

// FAQs Search JS

jQuery('.faq-search-form-cls form.elementor-search-form').on('submit', function (e) {
	e.preventDefault(e);
	var texto = jQuery(this).find("input.elementor-search-form__input").val();
	filtro(texto);
});

function filtro(texto) {
	var lista = jQuery(".faq-main-cls .pp-faq-item-wrap").hide()
		.filter(function () {
			var item = jQuery(this).find('.pp-faq-question').text();
			var padrao = new RegExp(texto, "i");
			return padrao.test(item);
		}).closest(".pp-faq-item-wrap").show();
}

// Range Slider Js

const rangeInput = document.querySelectorAll(".range-input input"),
	priceInput = document.querySelectorAll(".price-input input"),
	range = document.querySelector(".slider .progress");
let priceGap = 1000;

priceInput.forEach((input) => {
	input.addEventListener("input", (e) => {
		let minPrice = parseInt(priceInput[0].value),
			maxPrice = parseInt(priceInput[1].value);

		if (maxPrice - minPrice >= priceGap && maxPrice <= rangeInput[1].max) {
			if (e.target.className === "input-min") {
				rangeInput[0].value = minPrice;
				range.style.left = (minPrice / rangeInput[0].max) * 100 + "%";
			} else {
				rangeInput[1].value = maxPrice;
				range.style.right = 100 - (maxPrice / rangeInput[1].max) * 100 + "%";
			}
		}
	});
});

rangeInput.forEach((input) => {
	input.addEventListener("input", (e) => {
		let minVal = parseInt(rangeInput[0].value),
			maxVal = parseInt(rangeInput[1].value);

		if (maxVal - minVal < priceGap) {
			if (e.target.className === "range-min") {
				rangeInput[0].value = maxVal - priceGap;
			} else {
				rangeInput[1].value = minVal + priceGap;
			}
		} else {
			priceInput[0].value = minVal;
			priceInput[1].value = maxVal;
			range.style.left = (minVal / rangeInput[0].max) * 100 + "%";
			range.style.right = 100 - (maxVal / rangeInput[1].max) * 100 + "%";
		}
	});
});

/**
 * Tag add or remove
 */
const watchlistsCheckboxes = document.getElementsByName("watchlists-post-tag");
if (typeof watchlistsCheckboxes !== 'undefined') {
	watchlistsCheckboxes.forEach(item => {
		item.addEventListener('change', function () {
			startupAddRemove(watchlistsCheckboxes);
		});
	})

	jQuery(document).on('click', 'input[name="watchlists-post-tag"]', function() {
		startupAddRemove(watchlistsCheckboxes);
	});

	let delay = (() => {
		let timer = 0;
		return function (callback, ms) {
			clearTimeout(timer);
			timer = setTimeout(callback, ms);
		};
	})();

	const startupAddRemove = (watchlistsCheckboxes) => {
		const Cboxes = Array.prototype.slice.call(watchlistsCheckboxes).filter(ch => ch.checked == true);
		const CboxesVal = Cboxes.map(i => i.getAttribute('value'));
		CboxesVal.length > 0 ? jQuery('.add-post-tag-cls i').css('color', '#a71c44') : jQuery('.add-post-tag-cls i').css('color', '#90939E');
		delay(() => {
			let watchlists = jQuery(".watchlists-tags-main-sec");
			jQuery.ajax({
				type: "post",
				dataType: "json",
				url: savior_ajax_obj.ajaxurl,
				data: {
					action: "add_remove_startup_tags",
					post_id: jQuery("#watchlists-post-id").val(),
					newstartup_tags: CboxesVal.length > 0 ? CboxesVal : '',
					nonce: savior_ajax_obj.nonce
				},
				beforeSend: function () {
					watchlists.addClass('add-remove-tag-cls');
					// addButton.prop('disabled', true);
				},
				success: function (response) {
					if (response.success) {

					}

					watchlists.removeClass('add-remove-tag-cls');
				}
			});
		}, 2000);
	}
}