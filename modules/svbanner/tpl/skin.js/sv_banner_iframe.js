var _g_aViewdBanner = [];
var _g_aUa = [];
var _g_sUuid = '';
var _g_sCookieName = 'svbanner_uuid';
var _g_sParentUrl = $(window.parent.location).attr('href');
var _g_sFilter = "win16|win32|win64|mac|macintel";

if(navigator.platform) {
	if(_g_sFilter.indexOf(navigator.platform.toLowerCase()) < 0) { //mobile
		_g_aUa = 'm';
	} else { //pc
		_g_aUa = 'p';
	}
}

function getUuid() {
	// https://dullyshin.github.io/2019/09/10/WEB-CookiesMakeDel/
	// https://stackoverflow.com/questions/33232270/jquery-cookie-expiry-time
	// $.removeCookie(_g_sCookieName);
	_g_sUuid = $.cookie(_g_sCookieName);
	if(!_g_sUuid){
		$.cookie(_g_sCookieName, uuidv4(), {expires: 30, path: '/'});
		_g_sUuid = $.cookie(_g_sCookieName);
	}
	console.log(_g_sUuid);
}

function uuidv4() {
	// https://stackoverflow.com/questions/105034/how-do-i-create-a-guid-uuid
	return ([1e2]+-1e5).replace(/[018]/g, c =>
	  (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
	);
}

function checkImpression(elm, eval) {
	eval = eval || 'visible';
	var vpH = jQuery(window.parent).height(); // Viewport Height
	var st = jQuery(window.parent).scrollTop(); // Scroll Top
	var y = jQuery(elm).offset().top;
	var elementHeight = jQuery(elm).height();
	if(eval == 'visible')
		return ((y < (vpH + st)) && (y > (st - elementHeight)))  // mark an object is on viewport
	if(eval == 'above')
		return ((y < (vpH + st)));
}

function checkDisplayed(sObjId, nImpSrl) {
    var bChecked = false;
    if(_g_aViewdBanner.length > 0) {
        for(var i in _g_aViewdBanner) {
            if(_g_aViewdBanner[i] == sObjId) {
                bChecked = true;
                break;
            }
        }
    }
    if(!bChecked) {
        console.log('banner impressed');
		var params = new Array();
		params['imp_srl'] = nImpSrl;
		params['ua'] = _g_aUa;
		params['uuid'] = _g_sUuid;
		params['page_url'] = _g_sParentUrl;
		console.log(params);
		var respons = [];
		exec_xml('svbanner', 'procSvbannerPatchImp', params, function(ret_obj) {
			console.log('impression patched');
			// do nothing
		},respons);
        _g_aViewdBanner[_g_aViewdBanner.length] = sObjId;
    }
    // console.log(_g_aViewdBanner);
}

function checkClicked(nImpSrl, nBannerSrl, fDuplicatedClickLimitDay) {
	var sBannerClkId = nBannerSrl + '_clked';
	// $.removeCookie(sBannerClkId);
	sBannerClicked = $.cookie(sBannerClkId);
	console.log(sBannerClicked + ' banner clicked');
	fDuplicatedClickLimitDay = parseFloat(fDuplicatedClickLimitDay);
	if (isNaN(fDuplicatedClickLimitDay)) { // 값이 없어서 NaN값이 나올 경우
		fDuplicatedClickLimitDay = 0.0;
	}
	if(!sBannerClicked){
		console.log('click won\'t be reported until ' + fDuplicatedClickLimitDay + ' days');
		$.cookie(sBannerClkId, 1, {expires: fDuplicatedClickLimitDay, path: '/'});  // 0.0104=15/1440 means 15 min, 2/24 means expires in 2 hrs
		sBannerClicked = $.cookie(sBannerClkId);
		var bClicked = false;
	}
	else
		var bClicked = true;
	
    if(!bClicked) {
        console.log('banner click patch');
		var params = new Array();
		params['imp_srl'] = nImpSrl;
		var respons = [];
		exec_xml('svbanner', 'procSvbannerPatchClk', params, function(ret_obj) {
			console.log('click patched');
			// do nothing
		},respons);
    }
}

// var script = parent.document.createElement("script");
// script.src = "https://securepubads.g.doubleclick.net/tag/js/gpt.js";
// script.async = true;
// parent.document.getElementsByTagName("head")[0].appendChild(script);

// (function($){
// 	$(document).ready(function(){  
// 		$(".scroll").click(function(event){  
// 			//prevent the default action for the click event  
// 			event.preventDefault();  
// 			//get the full url - like mysitecom/index.htm#home  
// 			var full_url = this.href;  
// 		});  
// 	}); 
// })(jQuery);

// function addBanner(sBannerUrl){
// 	var aIframe = parent.document.createElement("iframe");
// 	aIframe.setAttribute("id","if_banner");
// 	aIframe.setAttribute("name","name값");
// 	aIframe.style.width = "100%";
// 	aIframe.style.height = "1200px";
// 	aIframe.style.border = 'none';
// 	aIframe.style.overflow = 'hidden';
// 	// aIframe.style.cursor = "pointer";
// 	aIframe.src = sBannerUrl;
// 	// aIframe.contents ="<img id='banner_sUniqId' src='http://127.0.0.1/files/attach/images/133/001/e3f4781511c1f1a717c0d6317da17a1c.png' style='cursor: pointer;'></img>";;
// 	parent.document.getElementById("banner").appendChild(aIframe);
// }