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

	if (jQuery('body').filter('.single-startup.logged-in').length > 0 || jQuery('body').filter('.page-id-1796.logged-in').length > 0) {
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
				action: "add_startup_tags",
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
	if (jQuery('.watchlists-tags-main-sec:visible') && jQuery('body').hasClass('page-id-1796')) {
		if (!jQuery('.watchlists-tags-main-sec:visible').has(event.target).length && !jQuery(event.target).hasClass('watchlists-edit-tag') && !jQuery(event.target).hasClass('watchlists-tags-main-sec')) {
			jQuery('.watchlists-tags-main-sec:visible').slideUp('fast');
		}
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

	jQuery(document).on('click', 'input[name="watchlists-post-tag"]', function () {
		startupAddRemove(watchlistsCheckboxes);
	});

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
				},
				success: function (response) {
					watchlists.removeClass('add-remove-tag-cls');
				}
			});
		}, 2000);
	}
}
let delay = (() => {
	let timer = 0;
	return function (callback, ms) {
		clearTimeout(timer);
		timer = setTimeout(callback, ms);
	};
})();
/**
 * WatchList Page
 */
jQuery(document).ready(function ($) {
	$(".watchlists-edit-tag").on("click", function (e) {
		e.preventDefault();
		$(".watchlists-edit-tag").parents('.watchlist-post-tag-rep').siblings().find('.watchlists-tags-main-sec:visible').not(`#watchlists-post-${$(this).data('startup_tag_id')}`).slideUp('fast');
		$(`#watchlists-post-${$(this).data('startup_tag_id')}`).slideToggle("fast");
	});

});

const watchListsPostWithTag = document.getElementsByName("watchlists-post-with-tag");
if (typeof watchListsPostWithTag !== 'undefined') {
	watchListsPostWithTag.forEach(item => {
		item.addEventListener('change', (event) => {
			let tag_id = event.target.getAttribute('data-tag_id');
			let post_id = event.target.value;
			startUpPostRemove(post_id, tag_id);

			item.parentElement.parentElement.remove();
			document.getElementById(`watchlist-post-${post_id}-tag-${tag_id}`).remove();

			const watchlistsBoxes = Array.prototype.slice.call(document.querySelectorAll(`#watchlists-post-${tag_id} input`)).filter(ch => ch.checked == true);
			let deleteEl = document.querySelector(`#watchlist-tag-wrapper-${tag_id} .watchlists-delete-tag`);
			let getPostIds = deleteEl.getAttribute('data-ids').split(',');
			getPostIds.splice(getPostIds.indexOf(post_id), 1);
			deleteEl.setAttribute('data-ids', getPostIds)

			if (watchlistsBoxes.length === 0) document.getElementById(`watchlist-tag-wrapper-${tag_id}`).remove();

			if (document.querySelectorAll('.watchlist-post-tag-rep').length === 0) {
				jQuery('.watch-list-page-wrapper').append('<div class="no-watchlists-record"><p>No Watchlist Record Found!</p></div>')
			}

		});
	})

	//Mouse Hove On Edit List
	// const test = document.getElementsByClassName('test');//watchlists-post-with-tag-li
	document.getElementsById
	Array.from(document.querySelectorAll('.watchlists-post-with-tag-li')).forEach(item => {
		item.addEventListener('mouseover', (event) => {
			let inputEl = event.target.querySelector('input');
			if (typeof inputEl !== null) {
				let postEl = document.getElementById(`watchlist-post-${inputEl.value}-tag-${inputEl.getAttribute('data-tag_id')}`);
				if (event.target.tagName == "LI" || event.target.tagName == "LABEL") {
					postEl.style.border = '1px solid #A71C44';
					postEl.style.boxShadow = '2px 1px 20px 0px #a71c441f';
				}
			}
		})
		item.addEventListener('mouseout', (event) => {
			let inputEl = event.target.querySelector('input');
			let postEl = document.getElementById(`watchlist-post-${inputEl.value}-tag-${inputEl.getAttribute('data-tag_id')}`);
			if (event.target.tagName == "LI" || event.target.tagName == "LABEL") {
				postEl.style.border = '1px solid rgba(139, 139, 139, 0.3)';
				postEl.style.boxShadow = '';
			}
		})

	})
}
const startUpPostRemove = (post_id, tag_id) => {
	let PostEl = document.getElementById(`watchlist-tag-wrapper-${tag_id}`);
	jQuery.ajax({
		type: "post",
		dataType: "json",
		url: savior_ajax_obj.ajaxurl,
		data: {
			action: "remove_watchlists_tags",
			post_id: post_id,
			tag_id: tag_id,
			nonce: savior_ajax_obj.nonce
		},
		beforeSend: function () {
			if (Array.isArray(post_id)) {
				PostEl.classList.add("watchlist-loading");
				jQuery(`#watchlist-tag-wrapper-${tag_id}`).append('<div class="watch-list-page-wrapper-loading"></div>');
			}
		},
		success: function (response) {
			if (Array.isArray(post_id) && response.success) {
				PostEl.remove();
			} else if (response.success) {

			} else {
				jQuery('body').find('.watch-list-page-wrapper-loading').remove();
				PostEl.classList.remove("watchlist-loading");
			}
		},
		error: function (xhr, status, error) {
			if (Array.isArray(post_id)) {
				jQuery('body').find('.watch-list-page-wrapper-loading').remove();
				PostEl.classList.remove("watchlist-loading");
			}
		}
	});
}
/**
 * Delete WatchList 
 */
const deleteWatchLists = document.querySelectorAll(".watchlists-delete-tag");
if (typeof deleteWatchLists !== 'undefined') {
	deleteWatchLists.forEach(item => {
		item.addEventListener('mouseover', (event) => {
			let boxShadowEl = document.querySelectorAll(`#watchlist-tag-wrapper-${event.target.getAttribute('data-startup_tag_id')} .startup-post-sec-cls`)[0];
			boxShadowEl.style.boxShadow = 'rgba(167, 28, 68, 0.12) 2px 1px 20px 0px';
			// boxShadowEl.style.padding = '5px';
			boxShadowEl.style.borderRadius = '4px';
		})
		item.addEventListener('mouseout', (event) => {
			let boxShadowEl = document.querySelectorAll(`#watchlist-tag-wrapper-${event.target.getAttribute('data-startup_tag_id')} .startup-post-sec-cls`)[0];
			boxShadowEl.style.boxShadow = '';
			// boxShadowEl.style.padding = '';
			boxShadowEl.style.borderRadius = '';
		})
		item.addEventListener('click', (event) => {
			if (window.confirm("Do you really want to remove?")) {
				let tag_id = event.target.getAttribute('data-startup_tag_id');
				let post_ids = event.target.getAttribute('data-ids');
				startUpPostRemove(post_ids.split(','), tag_id);
			}
		})
	})
}
/**
 * WatchLists Sort by Rate
 */
const sortByRate = document.querySelectorAll(".watchlists-sort-by-rate");
if (typeof sortByRate !== 'undefined') {
	sortByRate.forEach(item => {
		item.addEventListener('change', async (event) => {
			await Array.from(document.querySelectorAll(`#watchlist-tag-wrapper-${event.target.getAttribute('data-startup_tag_id')} [data-${event.target.value}]`)).sort( (a, b) => a.getAttribute(`data-${event.target.value}`) - b.getAttribute(`data-${event.target.value}`) )
			.forEach(e => document.querySelector(`#watchlist-tag-wrapper-${event.target.getAttribute('data-startup_tag_id')} .startup-post-sec-cls`).appendChild(e) );
		})
	})
}