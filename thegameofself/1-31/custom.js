/* This is your custom Javascript */
jQuery(document).ready(function($) {
	const retrievedColorObject = document.cookie.split('; ').find((row) => row.startsWith('darkModeEnabled='))?.split('=')[1];
	if( (typeof retrievedColorObject !== "undefined" && typeof retrievedColorObject !== "" && retrievedColorObject == 'true') || jQuery('html').hasClass('dark-mode-active') ){
		jQuery('.toggle-dark i').addClass('bb-icon-sun').removeClass('bb-icon-moon');
		jQuery('#site-logo img').attr('src', 'https://assets.thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-White-512.svg');
	}else{
		jQuery('.toggle-dark i').addClass('bb-icon-moon').removeClass('bb-icon-sun');
		jQuery('#site-logo img').attr('src', 'https://thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-Purple-2-512.svg');
	}
	let darkModeEnabled;
// 	if( jQuery('html').hasClass('dark-mode-active') ){
// 		jQuery('.toggle-dark').find('i').addClass('bb-icon-sun');
// 	}
	jQuery('.toggle-dark').on('click', function(event) {
		event.preventDefault();		
		if(!$('body').hasClass('single-sfwd-topic') || !$('body').hasClass('single-sfwd-lessons') || !$('body').hasClass('single-sfwd-courses')){
			jQuery('html').toggleClass('dark-mode-active');
		}
		if( jQuery(this).attr('id') !== 'bb-toggle-theme' ){
// 			jQuery(this).find('i').toggleClass('bb-icon-moon');
		}else{
			jQuery('body').toggleClass('bb-dark-theme');
		}
		if( jQuery('html').hasClass('dark-mode-active') ){
			darkModeEnabled=true;	
			jQuery('#site-logo img').attr('src', 'https://assets.thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-White-512.svg');
		}else{
			darkModeEnabled=false;
			jQuery('#site-logo img').attr('src', 'https://thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-Purple-2-512.svg');
		}
// 		console.log(darkModeEnabled)
		jQuery(this).find('i').toggleClass('bb-icon-sun bb-icon-moon');
		var expiration_date = new Date();		
		expiration_date.setFullYear(expiration_date.getFullYear() + 1);
		var cookie_string = "darkModeEnabled=" + darkModeEnabled + "; path=/; expires=''; domain=thegameofself.com";

		document.cookie = cookie_string;	
		return false;
	});
	jQuery('#bb-toggle-theme').on('click', function(){
		if( jQuery('html').hasClass('dark-mode-active') ){
			jQuery('html').removeClass('dark-mode-active');
			jQuery('#site-logo img').attr('src', 'https://thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-Purple-2-512.svg');
		}else{
			jQuery('#site-logo img').attr('src', 'https://assets.thegameofself.com/wp-content/uploads/2022/02/Landon-Joystick-White-512.svg');
		}
	});
});	