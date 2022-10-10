$(function($){
	// 헤더 배너  /modules/page/skins/ange_main/content.html로 이동
	//$(document).ready(function(){
	//	$('.top-banner > ul').bxSlider({
			/*
			auto: true,
			pager: false,
			prevText: '<i class="icon-left-dir-1"><span class="sr-only">이전</span></i>',
			nextText: '<i class="icon-right-dir-1"><span class="sr-only">다음</span></i>'
			*/
	//		touchEnabled:(navigator.maxTouchPoints > 0),
	//		auto: true,
	//		pager: false,
	//		controls: false
	//	});
		//setTimeout(function(){ "
	//});
	
	// GNB 메뉴 관련 이벤트
	$(document).on('click', '#gnb .gnb-nav .btn-controls .btn-open', function() {
		$('#gnb').addClass('opened');
	});
	$(document).on('click', '#gnb .nav-backdrop, #gnb .gnb-nav .btn-controls .btn-close', function() {
		$('#gnb').removeClass('opened');
	});

	// 퀵배너 및 탑 관련
	$(window).on('scroll', function() {
		//console.log("doc height : " + $(document).height() + "\r\nquick height : " + $("#quick-banner").height() + "\r\nquick top : " + $("#quick-banner").offset().top + "\r\nsum : " + parseInt($("#quick-banner").height() + $("#quick-banner").offset().top));
		if ($(this).scrollTop() >= 100) {
			$('#quick-top').show('slow');
			if(parseInt($("#quick-banner").height() + $("#quick-banner").offset().top) < $(document).height()-100){
				$('#quick-banner').stop().animate({
					top: $(this).scrollTop() + 231
				}, 'slow');
			} else {
				$('#quick-banner').stop().animate({
					top: parseInt($(document).height()-100) - parseInt($("#quick-banner").height())
				}, 'slow');
			}
			/*  adsense floating banner related
			if(parseInt($(".tdn_float").height() + $(".tdn_float").offset().top) < $(document).height()-100){
				$('.tdn_float').stop().animate({
					top: $(this).scrollTop() + 231
				}, 'slow');
			} else {
				$('.tdn_float').stop().animate({
					top: parseInt($(document).height()-100) - parseInt($(".tdn_float").height())
				}, 'slow');
			}*/
		} else {
			$('#quick-top').hide('slow');
			$('#quick-banner').stop().animate({
				top: 231
			}, 'slow');
			$('.tdn_float').stop().animate({
				top: 231
			}, 'slow');
		}
	});
	
	// 퀵 탑 관련
	$(document).on('click', '#quick-top .top', function(e) {
		$('html, body').stop().animate({
			scrollTop: 0
		});
		e.preventDefault();
	});
	
	// 로그인 관련 팝업
	$('.btn-login').click(function(){
		var $href = $(this).attr('href');
		layer_popup($href);
	});
	function layer_popup(el){

		var $el = $(el);        //레이어의 id를 $el 변수에 저장
		var isDim = $el.prev().hasClass('login-bg');   //dimmed 레이어를 감지하기 위한 boolean 변수

		isDim ? $('.login-layer').fadeIn() : $el.fadeIn();

		var $elWidth = ~~($el.outerWidth()),
			$elHeight = ~~($el.outerHeight()),
			docWidth = $(document).width(),
			docHeight = $(document).height();

		// 화면의 중앙에 레이어를 띄운다.
		if ($elHeight < docHeight || $elWidth < docWidth) {
			$el.css({
				marginTop: -$elHeight /2,
				marginLeft: -$elWidth/2
			})
		} else {
			$el.css({top: 0, left: 0});
		}

		$el.find('.close').click(function(){
			isDim ? $('.login-layer').fadeOut() : $el.fadeOut(); // 닫기 버튼을 클릭하면 레이어가 닫힌다.
			return false;
		});

		/*$('.login-layer .login-bg').click(function(){ // 레이어 밖 클릭시 종료
			$('.login-layer').fadeOut();
			return false;
		});*/
	}
}(jQuery));