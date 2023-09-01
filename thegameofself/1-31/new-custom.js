/* This is your custom Javascript */
jQuery(document).ready(function($) {
	let logo_white 	= `https://assets.thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-White-512.svg`;
	let logo_purple = 'https://thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-Purple-2-512.svg';
	let retrievedColorObject = document.cookie.split('; ').find((row) => row.startsWith('darkModeEnabled='))?.split('=')[1];
	if( ( (typeof retrievedColorObject !== "undefined" && typeof retrievedColorObject !== "" && retrievedColorObject == 'true') || jQuery('html').hasClass('dark-mode-active') ) && (!$('body').hasClass('single-sfwd-topic') || !$('body').hasClass('single-sfwd-lessons') || !$('body').hasClass('single-sfwd-courses')) ){
		jQuery('.toggle-dark i').addClass('bb-icon-sun').removeClass('bb-icon-moon');
		jQuery('#site-logo img').attr('src', logo_white);
	}else{
		jQuery('.toggle-dark i').addClass('bb-icon-moon').removeClass('bb-icon-sun');
		jQuery('#site-logo img').attr('src', logo_purple);
	}
	let darkModeEnabled;

	jQuery('.toggle-dark').on('click', function(event) {
		event.preventDefault();		
		if(!$('body').hasClass('single-sfwd-topic') || !$('body').hasClass('single-sfwd-lessons') || !$('body').hasClass('single-sfwd-courses')){
			jQuery('html').toggleClass('dark-mode-active');
		}
		jQuery('body').toggleClass('bb-dark-theme');

		if( jQuery('html').hasClass('dark-mode-active') ){
			darkModeEnabled=true;	
			jQuery('#site-logo img').attr('src', logo_white);
		}else{
			darkModeEnabled=false;
			jQuery('#site-logo img').attr('src', logo_purple);
		}
		jQuery(this).find('i').toggleClass('bb-icon-sun bb-icon-moon');
		var expiration_date = new Date();		
		expiration_date.setFullYear(expiration_date.getFullYear() + 1);
		var cookie_string = "darkModeEnabled=" + darkModeEnabled + "; path=/; expires=''; domain=thegameofself.com";
		let mode = darkModeEnabled == true ? 'dark' : 'light';
		document.cookie = "bbtheme=" + mode + "; path=/; expires=''; domain=thegameofself.com";
		document.cookie = cookie_string;	
		return false;
	});
	jQuery('#bb-toggle-theme').on('click', function(){
		let mode 		= jQuery(this).find('span:visible').hasClass('sfwd-light-mode') ? 'light' : 'dark';
		let cookie_mode = mode == 'light' ? logo_purple : logo_white;
		if( jQuery('html').hasClass('dark-mode-active') ){
			jQuery('html').removeClass('dark-mode-active');
		}	
		let darkModeEnabled = mode == 'light' ? false : true;
		jQuery('#site-logo img').attr('src', cookie_mode);
		document.cookie = "bbtheme=" + mode + "; path=/; expires=''; domain=thegameofself.com";
		document.cookie = "darkModeEnabled=" + darkModeEnabled + "; path=/; expires=''; domain=thegameofself.com";
	});
});	