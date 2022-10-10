(function($) {
	
	// gnb nav
	$(document).on("click", "#header .btn-open-nav", function(e) {
		$("body").addClass("gnb-opened");
		e.preventDefault();
	});
	$(document).on("click", "#gnb-nav .nav-backdrop, #gnb-nav .btn-close", function(e) {
		$("body").removeClass("gnb-opened");
	});

	// mypage nav
	$(document).on("click", "#header .btn-open-mypage", function(e) {
		$("body").addClass("mypage-opened");
		e.preventDefault();
	});
	$(document).on("click", "#mypage-nav .nav-backdrop, #mypage-nav .btn-close", function(e) {
		$("body").removeClass("mypage-opened");
	});

	$(document).on("click", ".first_depth", function(){
		if($(this).parent().hasClass("active")){
			$(this).parent().removeClass("active");
		} else {
			$(this).parent().addClass("active").siblings().removeClass("active");
		}
	});

	// 메뉴 관련 
	$(document).on("click", ".second_depth", function() {
		if($(this).parent().hasClass("has-child")){
			if ($(this).parent().hasClass("on")) {
				$(this).parent().removeClass("on");
			} else {
				$(this).parent().siblings(".on").removeClass("on");
				$(this).parent().addClass("on");
			}
		}
	});
	
	$(document).ready(function(){
		if($(".view-content").length > 0)
			$(".view-content").html($(".view-content").html().replace(/\<iframe /gi,'<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" ').replace(/\<\/iframe\>/gi,'</iframe></div>'));
		if($(".story_content").length > 0)
			$(".story_content").html($(".story_content").html().replace(/\<iframe /gi,'<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" ').replace(/\<\/iframe\>/gi,'</iframe></div>'));
	});

	//if(!is_main){
		$(document).on('click', '.page-header .drop-open-btn', function() {
			$('body').addClass('open-dropdown');
		});
		$(document).on('click', '.dropdowns-menu .drop-close-btn', function() {
			$('body').removeClass('open-dropdown');
		});
		// 하위 메뉴 활성화 관련
		$(document).on("click", ".dropdowns-menu > .drop-container > ul > .has-child > a", function(e) {		
			if ($(this).parent().hasClass("active")) {
				$(this).parent().removeClass("active");
			} else {
				$(this).parent().siblings(".active").removeClass("active");
				$(this).parent().addClass("active");
			}
			e.preventDefault();
		});
	//}
})(jQuery);