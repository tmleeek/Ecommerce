jQuery(function() {

  jQuery('.home-slider').bxSlider();
          jQuery('select.catsearch').on('change',function() {
                window.location.href = jQuery(this).val();
          });
  jQuery(".youtube").colorbox({iframe:true, innerWidth:640, innerHeight:390});

    jQuery('.welcome-tabs').css("background-image","url("+jQuery('[data-tab].active').data('image')+")");
    jQuery('[data-tab]').click(function(e){
        e.preventDefault();
        jQuery(this).addClass('active').siblings().removeClass('active').parents('.welcome-tabs').css("background-image","url("+jQuery(this).data('image')+")");
        jQuery('#tab'+jQuery(this).data('tab')).addClass('active').siblings().removeClass('active');
    });

	jQuery(".bottom-footer > div h2.heading").click(function () {
		if (jQuery(this).hasClass('closed')) {
			jQuery(this).removeClass('closed').addClass('open');
			jQuery(this).next().toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('closed');
			jQuery(this).next().toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});

	jQuery(".top-section .shop-brand a").click(function () {
		if (jQuery(this).hasClass('closed')) {
			jQuery(this).removeClass('closed').addClass('open');
			jQuery(".top-brand").toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('closed');
			jQuery(".top-brand").toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});


	jQuery(".navbar.navbar-default .nav-width-fixed > ul > li").children('ul').parent().addClass('parents');
	jQuery(".navbar.navbar-default .nav-width-fixed > ul > li.parents").append("<span class='arrow'></span>")
	jQuery(".navbar.navbar-default .nav-width-fixed > ul > li.parents span.arrow").click(function() {
		if (jQuery(this).prev().is(":hidden")) {
         	jQuery(this).addClass('active');
			jQuery(this).prev().prev().addClass('active');
			jQuery(this).prev().slideDown();
        } else {
			jQuery(this).removeClass('active');
			jQuery(this).prev().prev().removeClass('active');
         	jQuery(this).prev().slideUp();
        }
	});

	jQuery(".navbar.navbar-default .nav-width-fixed .tablet-menu-hide a").click(function () {
		jQuery(".navbar.navbar-default").hide();
		
	});

	jQuery(".mobile-search a").click(function() {
		if (jQuery(".login-section").is(":hidden")) {
         	jQuery(this).addClass('active');
			jQuery(".login-section").slideDown();
        } else {
			jQuery(this).removeClass('active');
         	jQuery(".login-section").slideUp();
        }
	});

	jQuery(".header-bg-section").after(jQuery('.main-category-image'));
	jQuery(".header-bg-section").after(jQuery('.verhical-search-sec'));
	jQuery(".product-view .product-essential").before(jQuery('.catalog-product-view .breadcrumb'));
	jQuery(".layout").before(jQuery('.all-account-pages .breadcrumb'));
});




jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".header-right .login-section .custom-login").after(jQuery('#search_mini_form'));	
	}
	
	else {
		jQuery(".navbar.navbar-default").after(jQuery('#search_mini_form'));	
	}
});


jQuery(window).scroll(function() {
    if (jQuery(this).scrollTop() > 1){  
        jQuery('.header-bg-section').addClass("sticky");
		jQuery('.top-section').hide();
    }
    else{
        jQuery('.header-bg-section').removeClass("sticky");
		jQuery('.top-section').show();
    }
});




jQuery(document).ready(function() {
	var owl = jQuery(".blog-slider");
	owl.owlCarousel({
		itemsCustom : [
		[0, 1],
		[320, 1],
	    [450, 1],
    	[600, 1],
		[640, 1],
	    [768, 1],
	    [992, 2],
    	[1200, 2],
	    [1400, 2],
    	[1600, 2]
		],
	navigation : true
	});
});

jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".main-new-section").before(jQuery('.top-slider-section'));
	}
	else {
		jQuery(".main-vehicle-section").after(jQuery('.top-slider-section'));
	}
});

jQuery(document).ready(function() {
	jQuery(".block-layered-nav dt").click(function () {
		if (jQuery(this).hasClass('active')) {
			jQuery(this).removeClass('active').addClass('open');
			jQuery(this).next().toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('active');
			jQuery(this).next().toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});	
});

jQuery(document).ready(function(){
	jQuery(".category-products .sort-by select.custom-style").stylize();
});

jQuery(document).ready(function(){
	jQuery(".category-products .limiter select.custom-style2").stylize();	
});

(function($){
	$.fn.stylize=function(){
		this.each(function(){
		$(this).wrap("<div class='stylize'></div>");
		var str='';
			$(this).children().each(function(){
				if($(this).prop("tagName")=="optgroup" || $(this).prop("tagName")=="OPTGROUP" || $(this).prop("tagName")=="option" || $(this).prop("tagName")=="OPTION"){
					var attr = $(this).attr('value');
					var vals='';
					if (typeof attr !== 'undefined' && attr !== false) {
						vals=$(this).attr("value");
					}
					isSelected="";
					if($(this).closest(".stylize").find("select  option:selected").text() ==$(this).text()){
					isSelected=' class="selected" ';
					}
 
					if($(this).prop("tagName")=="optgroup" || $(this).prop("tagName")=="OPTGROUP"){
					str+='<li class="optgroup"><span>'+$(this).text()+'</span></li>';
					}
					else{
					str+='<li class="option"><a rel="'+vals+'" href="javascript:;"'+isSelected+' rel="nofollow">'+$(this).text()+'</a></li>';
					}
				}
			});
 
			str="<p class='selVal'>"+$(this).parent().find("select  option:selected").text()+"</p><ul class='stylizeul'>"+str+"</ul>";
			$(this).parent().append(str);
 
			$(this).closest(".stylize").find("a").click(function(){
				$(this).closest(".stylize").find("a").removeClass("selected")
				$(this).closest("div.stylize").find("select").children("option").removeAttr("selected");
				var idx=$(this).parent().index();
				$(this).addClass("selected");
				$(this).closest("div.stylize").find("select").children("option:eq("+idx+")").attr("selected","selected");
				$(this).closest("div.stylize").find("select").trigger("change");
				$(this).closest("div.stylize").find("select").trigger("click");
				$(this).closest("div.stylize").find("p.selVal").html($(this).html())
				$(this).closest("ul").slideUp();
			});
			$(this).closest("div.stylize").find("p.selVal").click(function(){
				$(this).closest("div.stylize").find("ul").slideToggle();
			});
			$(this).hide();
		});
	}
})(jQuery)

jQuery(document).ready(function() {
(function () { 
    var node = jQuery(".page-title h1").contents().filter(function () { return this.nodeType == 3 }).first(),
        text = node.text(),
        first = text.slice(0, text.indexOf(" "));

    if (!node.length)
        return;

    node[0].nodeValue = text.slice(first.length);
    node.before('<span>' + first + '</span>');
})(); 

});


jQuery(window).on("load resize",function(e){
	var max = 0;
	jQuery('.products-grid .item').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
});



jQuery(document).ready(function() {
	var owl = jQuery(".product-img-box .more-views ul");
	owl.owlCarousel({
		itemsCustom : [
		[0, 1],
		[320, 2],
	    [450, 2],
    	[600, 3],
		[640, 3],
	    [768, 3],
	    [992, 5],
    	[1200, 5]
		],
	navigation : true,
	pagination : false,
	});
});

jQuery(window).on("load resize",function(e){
	var max = 0;
	jQuery('.mini-products-list .item').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
});


jQuery(document).ready(function() {
	jQuery(".pro-head-over> h2.mobi-drop-icon").click(function () {
		if (jQuery(this).hasClass('closed')) {
			jQuery(this).removeClass('closed').addClass('open');
			jQuery(this).next().toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('closed');
			jQuery(this).next().toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});	
});

jQuery(document).ready(function() {
	jQuery(".pro-head-over-information> h2.mobi-drop-icon").click(function () {
		if (jQuery(this).hasClass('closed')) {
			jQuery(this).removeClass('closed').addClass('open');
			jQuery(this).next().toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('closed');
			jQuery(this).next().toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});	
});

jQuery(document).ready(function() {
	jQuery(".Product-qa-main> h2.mobi-drop-icon").click(function () {
		if (jQuery(this).hasClass('closed')) {
			jQuery(this).removeClass('closed').addClass('open');
			jQuery(this).next().toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('closed');
			jQuery(this).next().toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});	
});


jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".aw-pq2-list.box-content").after(jQuery('.mobile-content-bottom-form'));
	}
	else {
		jQuery(".aw-pq2-question-sort.pager").before(jQuery('.mobile-content-bottom-form'));
	}
});

jQuery(window).on("load resize",function(e){
	var max = 0;
	jQuery('.brand-logo-main .brand-our-main').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
});

jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".layout.layout-2-cols").before(jQuery('.catalog-category-view .breadcrumb'));
	}
	else {
		jQuery(".layout-2-cols .page-title.category-title").before(jQuery('.catalog-category-view .breadcrumb'));
	}
});

jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".layout.layout-2-cols").before(jQuery('.ulvehicle-results-for .breadcrumb'));
	}
	else {
		jQuery(".layout-2-cols .page-title.category-title").before(jQuery('.ulvehicle-results-for .breadcrumb'));
	}
});

jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".layout.layout-2-cols").before(jQuery('.catalogsearch-result-index .breadcrumb'));
	}
	else {
		jQuery(".layout-2-cols .page-title.category-title").before(jQuery('.catalogsearch-result-index .breadcrumb'));
	}
});


jQuery(document).ready(function() {
	jQuery(".navbar.navbar-default .nav-width-fixed > ul > li.our-products-menu").children('.product-navigation ').parent().addClass('parents');
		jQuery(".navbar.navbar-default .nav-width-fixed > ul > li.our-products-menu.parents").append("<span class='arrow'></span>")
		jQuery(".navbar.navbar-default .nav-width-fixed > ul > li.our-products-menu.parents span.arrow").click(function() {
			if (jQuery(this).prev().is(":hidden")) {
				jQuery(this).addClass('active');
				jQuery(this).prev().prev().addClass('active');
				jQuery(this).prev().slideDown();
			} else {
				jQuery(this).removeClass('active');
				jQuery(this).prev().prev().removeClass('active');
				jQuery(this).prev().slideUp();
			}
	});
});


/* ONLINE RETAILERS SEARCH */
jQuery(function(){

	jQuery("#online-search select.select-country").change(function(e){
		jQuery("#online-search").ajaxSubmit({
			target: ".retailers"
		});
		jQuery("#locator #online-search select.select-brand").removeAttr("disabled");
		jQuery("#locator .retailers").hide();
	});
	
	jQuery("#online-search select.select-brand").change(function(e){
		jQuery("#online-search").ajaxSubmit({
			target: ".retailers"
		});
		jQuery("#locator #online-search select.select-categories").removeAttr("disabled");
		jQuery("#locator .retailers").hide();
	});
	
	jQuery("#online-search select.select-categories").change(function(e){
		jQuery("#online-search").ajaxSubmit({
			target: ".retailers"
		});
		jQuery("#locator .retailers").show();
		jQuery("#locator .retailers2").hide();
	});
	
	
	
	jQuery("#locator-search input[type='checkbox']").change(function(){
		jQuery(this).parents('form').submit();
	});

});

jQuery(document).ready(function($) {
	setTimeout(function(){
	$('.product-shop .price-box .regular-price span.price').html(function (_, old_html) {
		return old_html.substr(0, old_html.indexOf('.')) + '<sup class="decimals">' + old_html.substr(old_html.indexOf('.')) + '</sup>';
	});
 }, 5000);
	function replaceText() {
		$(".product-shop .price-box .regular-price span.price").each(function() { 
			if($(this).children().length==0) { 
				$(this).html($(this).text().replace('$', '<sup>$</sup>'));
			} 
		});
	}
	$(document).ready(replaceText);
	$("html").ajaxStop(replaceText);
});


jQuery(document).ready(function() {
	jQuery(".product-view #customer-reviews.box-collateral > h2.mobi-drop-icon").click(function () {
		if (jQuery(this).hasClass('active')) {
			jQuery(this).removeClass('active').addClass('open');
			jQuery(this).next().toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('active');
			jQuery(this).next().toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});	
});

jQuery(document).ready(function($) {
	$( ".no-rating a" ).click(function() {
	  $( "#write-form-review a" ).click();
	});
});



// BLOG READ MORE
jQuery(function(){
	if(jQuery('.container2 .more a').length > 0){
		initMoreLink();
	}
});

function initMoreLink(){

	jQuery('.container2 .more a').click(function(e){
		e.preventDefault();

		var button = jQuery(this).parent('.more');

		jQuery.ajax({
			url: jQuery(this).attr('href'),
			type: 'get',
			cache: false,
			success: function(r){
				button.remove();
				jQuery('.container2').append(r);
				initMoreLink();
			}
		});
	});

}


jQuery(document).ready(function() {
	function close_accordion_section() {
		jQuery('#locator .results a.title').removeClass('active');
		jQuery('#locator .results .location').slideUp(300).removeClass('open');
	}
	jQuery('#locator .results a.title:nth-child(1)').addClass('active');
	jQuery('#locator .results a.title:nth-child(1)').next().show();

	jQuery('#locator .results a.title').click(function(e) {
		var currentAttrValue = jQuery(this).attr('href');

		if(jQuery(e.target).is('.active')) {
			close_accordion_section();
		}else {
			close_accordion_section();
			jQuery(this).addClass('active');
			jQuery('.results ' + currentAttrValue).slideDown(300).addClass('open'); 
		}
		e.preventDefault();
	});
});


jQuery(window).on("load resize",function(e){
	var max = 0;
	jQuery('.category-children .category-grid li.item').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
});

jQuery(document).ready(function() {
	jQuery(".navbar-default .navbar-nav > li.hidefor-mobile").hover(function(){
		jQuery('.our-product-navi').show();
	},function(){
		jQuery('.our-product-navi').hide();
	});
});
jQuery(document).ready(function() {
	jQuery(".our-product-navi").hover(function(){
		jQuery('.our-product-navi').show();
	},function(){
		jQuery('.our-product-navi').hide();
	});
});
jQuery(document).ready(function() {
	jQuery('.block-layered-nav dd ol li a.amshopby-attr-selected').each(function() {
		jQuery(this).parent().addClass("selectd");
	});
});

jQuery(document).ready(function() {
	jQuery("#locator #online-search select.select-country option[value='Country']").remove();
	jQuery("#locator #online-search select.select-brand option[value='Brand']").remove();
	jQuery("#locator #online-search select.select-categories option[value='Category']").remove();
});