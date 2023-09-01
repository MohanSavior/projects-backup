// Share Btn
jQuery(document).ready(function($){

	// ADD Comma JS

	jQuery( ".price-sec-cls span.pp-second-text" ).each(function(){
		var post_price_int = jQuery(this).text().replace ( /[^\d.]/g, '' );
		post_price_int = parseInt(post_price_int);
		jQuery(this).text(`$${post_price_int.toLocaleString()}` );
	});

	jQuery(".share-icon-cls").click(function(e){
		e.preventDefault();
		jQuery(".post-share-btn").slideToggle();
	});

	// Current Page URL Copy 	
	var $temp = jQuery("<input>");
	var $url = jQuery(location).attr('href');

	jQuery('.copy-link-cls a').on('click', function(e) {
		e.preventDefault();
		jQuery("body").append($temp);
		$temp.val($url).select();
		document.execCommand("copy");
		$temp.remove();
	});

	// Header user toggle js
	jQuery(".user-info-cls .elementor-image-box-wrapper").click(function(e){
		e.preventDefault();
		jQuery(".elementor-element.user-pg-list>.elementor-widget-container").slideToggle();
	});

	// Marketplace Filter Result js
	jQuery(".all-filter-btn-cls").click(function(e){
		e.preventDefault();
		jQuery(".elementor-element.filter-result-main-sec.elementor-widget").slideDown();
	});

	jQuery(".filter-result-box-main-cls a.filter-close-btn").click(function(e){
		e.preventDefault();
		jQuery(".elementor-element.filter-result-main-sec.elementor-widget").slideUp();
	});

	// Forgot Form text 	
	jQuery( ".login-form-cls a.tp-lost-password" ).on( "click", function() {
		jQuery('.elementor-773 .elementor-element.elementor-element-5978d54 .pp-first-text').text("Reset your password");
	});

	jQuery(".login-form-cls a.tp-lpu-back").on( "click", function() {
		jQuery('.elementor-773 .elementor-element.elementor-element-5978d54 .pp-first-text').text("Welcome back to S&D");
	});
	//Watchlists slideToggle 
	$(".post-like-btn-cls button").on("click", function(e){
		$(".watchlists-tags-main-sec").slideToggle("fast");
	});
	/* Add New Watchlists Tag  */
	$("#watchlists-post-other").prop('checked', false);
	$("#watchlists-post-other").on("click", function(e){
		$(".watchlists-tags-main-sec ul li:last-child").slideToggle("fast");
		if($(this).is(':checked')){
			
		}else{
			
		}
		
	});
});

// jQuery(document).on("click", function (e) {
// 	e.preventDefault();
// 	var $tgt = jQuery(e.target);
// 	if ($tgt.closest(".elementor-element.user-pg-list>.elementor-widget-container").length) {
// 		alert();
// 	} 
// });

jQuery(document).on("click", function(event){
	var $trigger = jQuery(".user-info-cls");
	if($trigger !== event.target && !$trigger.has(event.target).length){
		jQuery(".user-pg-list .elementor-widget-container").slideUp("fast");
	}
});

// FAQs Search JS

jQuery('.faq-search-form-cls form.elementor-search-form').on('submit', function(e) {
	e.preventDefault(e);
	var texto = jQuery(this).find("input.elementor-search-form__input").val();
	filtro(texto);
});

function filtro(texto) {
	var lista = jQuery(".faq-main-cls .pp-faq-item-wrap").hide()
	.filter(function(){
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