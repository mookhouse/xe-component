var _g_sDeliveryDueYyyymmdd = '';
var _g_sDeliveryDueCookieName = 'delivery_expected_due';
var _g_sPreparationCookieName = 'pregnant_preparation';
var _g_bPreparation = false;

function getDeliveryDue() {
	// https://dullyshin.github.io/2019/09/10/WEB-CookiesMakeDel/
	// https://stackoverflow.com/questions/33232270/jquery-cookie-expiry-time
	_g_sDeliveryDueYyyymmdd = $.cookie(_g_sDeliveryDueCookieName);
	//console.log(_g_sDeliveryDueYyyymmdd);
}

// 출산 예정일 등록
function setDeliveryDue() {
	// https://dullyshin.github.io/2019/09/10/WEB-CookiesMakeDel/
	// https://stackoverflow.com/questions/33232270/jquery-cookie-expiry-time
	unsetCookie();
	_g_sDeliveryDueYyyymmdd = $('#delivery_due_yyyymmdd').val().trim().replaceAll('-', '');
	if(_validateDate() && _isComingDate())
	{
		$.cookie(_g_sDeliveryDueCookieName, _g_sDeliveryDueYyyymmdd, {expires: 30, path: '/'});
		_g_sDeliveryDueYyyymmdd = $.cookie(_g_sDeliveryDueCookieName);
		location.reload();
	}
	else
	{
		alert('유효한 날짜를 입력해 주세요.');
		return false;
	}
}

// 임신 준비 중 등록
function setPreparation() {
	// https://dullyshin.github.io/2019/09/10/WEB-CookiesMakeDel/
	// https://stackoverflow.com/questions/33232270/jquery-cookie-expiry-time
	$.cookie(_g_sPreparationCookieName, 'in_preparation', {expires: 30, path: '/'});
	_g_bPreparation = true;
	location.reload();
}

function unsetCookie() {
	$.removeCookie(_g_sDeliveryDueCookieName);
	$.removeCookie(_g_sPreparationCookieName);
	location.reload();
}

function _validateDate() {
	// check if valid date
	var y = parseInt(_g_sDeliveryDueYyyymmdd.substr(0,4), 10),
		m = parseInt(_g_sDeliveryDueYyyymmdd.substr(4,2), 10),
		d = parseInt(_g_sDeliveryDueYyyymmdd.substr(6,2), 10);
	try {
	    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
	    result = dateRegex.test(d+'-'+m+'-'+y);
	} catch (err) {
		return false;
	}
    return true;
}

function _isComingDate() {
	// check if coming date
	var y = parseInt(_g_sDeliveryDueYyyymmdd.substr(0,4), 10),
		m = parseInt(_g_sDeliveryDueYyyymmdd.substr(4,2), 10),
		d = parseInt(_g_sDeliveryDueYyyymmdd.substr(6,2), 10);
	var curDate = new Date();
	var tDate = new Date(y, m-1, d, 23, 59);
	// 입력한 시간이 현재일자 시간 이전이면 false
	if(tDate - curDate < 0) {
		return false;
	}
    return true;
}