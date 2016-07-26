jQuery(document).ajaxStart(function(){
    jQuery("#wait").css("display", "block");
});

jQuery(document).ajaxComplete(function(){
    jQuery("#wait").css("display", "none");
}); 


jQuery(document).ready(function() {
	jQuery('.garage-container').on('click', '.current-vehicle', function () {
		if (jQuery(".header-right .login-section .additional-vehicles").is(":hidden")) {
			jQuery(this).addClass('active');
			jQuery(".header-right .login-section .additional-vehicles").slideDown();
		} else {
			jQuery(this).removeClass('active');
			jQuery(".header-right .login-section .additional-vehicles").slideUp();
		}
	});
});


jQuery(document).ready(function() {
	jQuery('.change-vehicle').on('click', '.add-vehicle', function () {
		if (jQuery(".product-vehicle-container .additional-vehicles").is(":hidden")) {
			jQuery(this).addClass('active');
			jQuery(".product-vehicle-container .additional-vehicles").slideDown();
		} else {
			jQuery(this).removeClass('active');
			jQuery(".product-vehicle-container .additional-vehicles").slideUp();
		}
	});
});

jQuery(document).ready(function() {
	jQuery('.add-vehicle-item').on('click', '.add-vehicle', function () {
		if (jQuery(".product-vehicle-container .embedded-vehicle-selector").is(":hidden")) {
			jQuery(this).addClass('active');
			jQuery(".product-vehicle-container .embedded-vehicle-selector").slideDown();
			jQuery(".product-vehicle-container .additional-vehicles").slideUp();
		} else {
			jQuery(this).removeClass('active');
			jQuery(".product-vehicle-container .embedded-vehicle-selector").slideUp();
			jQuery(".product-vehicle-container .additional-vehicles").slideDown();
		}
	});
});