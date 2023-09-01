/** Scroll Bar **/ 
(function($){
	$(window).on("load",function(){
		$(".elementor-element.elementor-element-b60598e .pp-info-box-description, .services-parent-box .pp-info-box-description").mCustomScrollbar({
			scrollButtons:{enable:false},
			theme:"light-thin",
			autoDraggerLength: true,
		});
		if($(window).width() > 767) {
			$(".elementor-element.elementor-element-dc517aa > .elementor-widget-container").mCustomScrollbar({
				scrollButtons:{enable:false},
				theme:"light-thin",
				autoDraggerLength: true,
			});
		}
	});
})(jQuery);

/** Gform after submission **/
jQuery(document).on('gform_post_render', function(event, form_id, current_page){    
	if ((form_id == 1) || (form_id == 3) || (form_id == 4) || (form_id == 5)) {
		if(typeof current_page  === "undefined") {
			jQuery(".form-top-heading, .form-top-content, .pp-gravity-form-description").css('display', 'none');
		}
	}
});