$(function($){
    // jumbo slider
    $(document).ready(function(){
        $('.jumbo > .jumbo-slider').bxSlider({
			touchEnabled:(navigator.maxTouchPoints > 0),
            controls: true,
			auto: true,
			prevText: '<img src="/modules/page/skins/ange_main/imgs/btn_bannerL.png" style="height:30px;" alt="">',
            nextText: '<img src="/modules/page/skins/ange_main/imgs/btn_bannerR.png" style="height:30px;" alt="">'
        });
    });
    // 로그인 스크롤 관련
    /*$(window).load(function(){
       $("#content-scroll").mCustomScrollbar(
			{setHeight: 160}
	   );
    });*/
}(jQuery)); 