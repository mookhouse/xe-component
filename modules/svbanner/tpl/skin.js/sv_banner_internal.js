var _g_aViewdBanner = [];
var _g_sUa = '';
var _g_sUuid = '';
var _g_sCookieName = 'svbanner_uuid';
var _g_sParentUrl = $(window.location).attr('href');
var _g_sFilter = "win16|win32|win64|mac|macintel";

if(navigator.platform) {
	if(_g_sFilter.indexOf(navigator.platform.toLowerCase()) < 0) { //mobile
		_g_sUa = 'm';
	} else { //pc
		_g_sUa = 'p';
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
		params['ua'] = _g_sUa;
		params['uuid'] = _g_sUuid;
		params['page_url'] = _g_sParentUrl;
		var respons = [];
		exec_xml('svbanner', 'procSvbannerPatchImp', params, function(ret_obj) {
			console.log('impression patched');
			// do nothing
		},respons);
		_g_aViewdBanner[_g_aViewdBanner.length] = sObjId;
	}
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