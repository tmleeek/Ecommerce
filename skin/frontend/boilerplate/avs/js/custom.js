jQuery( document ).ready(function() {
	jQuery('.home-slider').bxSlider({
	  infiniteLoop: true,
	  hideControlOnEnd: false
	});
});
jQuery(document).ready(function() {
    function tabmd(){
		if (jQuery(window).width() > 767) {
			jQuery(".pro-info-tab-sec .tab-sec .tab-content").hide();
			jQuery(".pro-info-tab-sec .tab-sec .tab-content:nth-child(2)").show();
			jQuery(".pro-info-tab-sec .tab-sec .tab:nth-child(1)").addClass("active");
			
			var height = jQuery(".pro-info-tab-sec .tab-sec .tab-content:visible").height();
			var fheight = height + 41;
			
			jQuery(".pro-info-tab-sec .tab-sec").css('height', fheight);
			
			jQuery('.pro-info-tab-sec .tab-sec div.tab').click(function() {
					
				if (jQuery(this).next().is(":hidden")) {
					
					jQuery(".pro-info-tab-sec .tab-sec .tab-content").hide();
					jQuery(".pro-info-tab-sec .tab-sec .tab").removeClass("active");
					jQuery(this).next().show();
					jQuery(this).addClass("active");
					
					var height = jQuery(".pro-info-tab-sec .tab-sec .tab-content:visible").height();
					var fheight = height + 41;
					jQuery(".pro-info-tab-sec .tab-sec").css('height', fheight);
					
				} else {
					
				}
			});
		}
		
		if (jQuery(window).width() < 768) {
			jQuery(".pro-info-tab-sec .tab-sec .tab-content").hide();
			jQuery(".pro-info-tab-sec .tab-sec .tab-content:nth-child(2)").show();
			jQuery(".pro-info-tab-sec .tab-sec .tab:nth-child(1)").addClass("active");
			
			var height = jQuery(".pro-info-tab-sec .tab-sec .tab-content:visible").height();
			var fheight = height + 118;
			
			jQuery(".pro-info-tab-sec .tab-sec").css('height', fheight);
			
			jQuery('.pro-info-tab-sec .tab-sec div.tab').click(function() {
					
				if (jQuery(this).next().is(":hidden")) {
					
					jQuery(".pro-info-tab-sec .tab-sec .tab-content").hide();
					jQuery(".pro-info-tab-sec .tab-sec .tab").removeClass("active");
					jQuery(this).next().show();
					jQuery(this).addClass("active");
					
					var height = jQuery(".pro-info-tab-sec .tab-sec .tab-content:visible").height();
					var fheight = height + 118;
					jQuery(".pro-info-tab-sec .tab-sec").css('height', fheight);
					
				} else {
					
				}
			});
		}
	}
	
	tabmd();
	
	jQuery( window ).resize(function() {
		tabmd();
	});
});


jQuery(document).ready(function() {
	var owl = jQuery(".product-categories .right-section ul");
	owl.owlCarousel({
		itemsCustom : [
		[0, 1],
		[320, 1],
	    [450, 1],
    	[600, 2],
		[640, 2],
	    [768, 2],
	    [992, 3],
    	[1200, 3]
		],
	navigation : true
	});
});


jQuery(window).on("load resize",function(e){
	var max = 0;
	jQuery('.product-categories .right-section ul li .list-devider a').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
});


jQuery(document).ready(function() {
	(function () { 
		var node = jQuery(".page-title h1").not('.category-title h1').contents().filter(function () { return this.nodeType == 3 }).first(),
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



jQuery(document).ready(function() {
	jQuery(".bottom-footer > div h2.heading").click(function () {
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
	jQuery(".top-section .shop-brand a").click(function () {
		if (jQuery(this).hasClass('closed')) {
			jQuery(this).removeClass('closed').addClass('open');
			jQuery(".top-brand").toggle().removeClass('show-sub').addClass('hide-sub');
		} else {
			jQuery(this).removeClass('open').addClass('closed');
			jQuery(".top-brand").toggle().removeClass('hide-sub').addClass('show-sub');
		}
	});	
});


jQuery( document ).ready(function() {
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
});

jQuery(document).ready(function() {
	jQuery(".navbar.navbar-default .nav-width-fixed .tablet-menu-hide a").click(function () {
		jQuery(".navbar.navbar-default").hide();
		
	});
});
jQuery(document).ready(function() {
	jQuery(".mobile-search a").click(function() {
		if (jQuery(".login-section").is(":hidden")) {
         	jQuery(this).addClass('active');
			jQuery(".login-section").slideDown();
        } else {
			jQuery(this).removeClass('active');
         	jQuery(".login-section").slideUp();
        }
	});
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

jQuery( document ).ready(function() {
	jQuery(".top-min-banner").after(jQuery('.main-category-image'));
	jQuery(".top-min-banner").after(jQuery('.verhical-search-sec'));
	jQuery(".product-view .product-essential").before(jQuery('.catalog-product-view .breadcrumb'));
	jQuery(".layout").before(jQuery('.all-account-pages .breadcrumb'));
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


jQuery(document).ready(function(){
	jQuery(".category-products .sort-by select.custom-style").stylize();
});

jQuery(document).ready(function(){
	jQuery(".category-products .limiter select.custom-style2").stylize();	
});


jQuery(window).on("load resize",function(e){
	if ( jQuery(window).width() <= 767 ) {
		jQuery(".product-view .product-img-box").before(jQuery('.product-view .product-name'));
	}
	else {
		jQuery(".product-view .part-code").before(jQuery('.product-view .product-name'));
	}
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
	var max = 0;
	jQuery('.mini-products-list .item').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
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


jQuery(window).on("load resize",function(e){
	var max = 0;
	jQuery('#blog .post').each(function() {
		jQuery(this).height('auto');
		var h = jQuery(this).height();
		max = Math.max(max, h);
		}).height(max);
});