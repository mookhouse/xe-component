 $( document ).ready(function() {

	//$('.datetimepicker').datetimepicker({
	//  timepicker:false,
	//  format:'Y-m-d',
	//  style:'z-index: 101000;',
	//  closeOnDateSelect:true,
	//  lang: 'ko'
	//});

    // 메뉴 관련
    //$(document).on('mouseenter focus', '#gnb-nav .gnb-list > li', function() {
    //    $(this).siblings().removeClass('active');
    //    $(this).addClass('active');
    //});
    
	//$(document).on('mouseleave', '#gnb-nav .gnb-list > li', function() {
    //    $(this).removeClass('active');
    //});

});

function setCookie(cookieName, value, exdays){
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + exdays);
    var cookieValue = escape(value) + ((exdays==null) ? "" : "; expires=" + exdate.toGMTString());
    document.cookie = cookieName + "=" + cookieValue;
}

function deleteCookie(cookieName){
    var expireDate = new Date();
    expireDate.setDate(expireDate.getDate() - 1);
    document.cookie = cookieName + "= " + "; expires=" + expireDate.toGMTString();
}

function getCookie(cookieName) {
    cookieName = cookieName + '=';
    var cookieData = document.cookie;
    var start = cookieData.indexOf(cookieName);
    var cookieValue = '';
    if(start != -1){
        start += cookieName.length;
        var end = cookieData.indexOf(';', start);
        if(end == -1)end = cookieData.length;
        cookieValue = cookieData.substring(start, end);
    }
    return unescape(cookieValue);
}

/* 파일 사이즈 형식을 반환합니다. */
function FormatFilesize(p_value){
	var t_value = Number(p_value);

	if (t_value<1024) { return p_value+' B'; }
	if (t_value<1024*1024) { return (Math.round(t_value/1024))+' KB'; }
	if (t_value<1024*1024*1024) { return (Math.round(t_value/1024/1024))+' MB'; }
}

/* 전화번호 형식을 반환합니다. */
function FormatPhone(p_value){
}

/* ,가 포함된 숫자 형식을 반환합니다. */
function FormatPhone(p_value){
}

/* 폼을 전송합니다. */
function SendForm(p_obj,p_gotourl,p_checkvalue,popGbn){

	//if(!confirm("저장 하시겠습니까")){
	//	return false;
	//}

	p_checkvalue = typeof p_checkvalue !== 'undefined' ? p_checkvalue : false;

	event.preventDefault();

	// 폼검사
	var t_pattern = { 'id':/^[a-zA-Z0-9_]+$/,'email':/^[a-zA-Z0-9_@.]+$/,'number':/^[0-9]+$/ };


	if (typeof(CKEDITOR_NAME)!='undefined' && CKEDITOR_NAME!=''){
		CKEDITOR.instances[CKEDITOR_NAME].updateElement();
	}

	if ($('#queform_0').length>0){
		SetQuestion();
	}

	if (p_checkvalue){

		for(var t_cnt=0; t_cnt< p_obj.length; t_cnt++){

			t_obj = p_obj.elements[t_cnt];

			// 유효한 항목인지 확인
			if (typeof t_obj!='undefined' && t_obj.name!=''){

				// 검사 대상인지 확인
				if (t_obj.title!='' && t_obj.type!='radio' && t_obj.type!='button' && t_obj.type!='submit' )
				{
					
					//console.log(t_obj.name);
					t_item = t_obj.name.split(':');

					if (!t_obj.value){
						alert(t_obj.title+'란은 꼭 입력하셔야만 합니다.');
						t_obj.focus();
						return false;
					}

					if (t_obj.alt!='' && Number(t_obj.alt)>t_obj.value.length ){
						alert(t_obj.title+'\n최소길이를 확인하여 주십시오.');
						t_obj.focus();
						return false;
					}

					if (t_item.length>1) {
						if (t_pattern[t_item[1]].test(t_obj.value)==false) {
							alert('적절하지 않은 값이 입력되었습니다.');
							t_obj.focus();
							return false;
						}
					}
				}


			}

		}
	}

	$.ajax({
			method : 'post',
			url : p_obj.action,
			data: $(p_obj).serializeArray(),
			success : function(data) {
				t_result = data;
				if (t_result.substr(0,4)=='true'){
					p_gotourl = p_gotourl.replace('#insert_id#',t_result.substr(5));
					//location.href=p_gotourl;
					//ShowAlert('저장하였습니다.');
					if(!fnIsNull(popGbn) && popGbn == "P"){
						opener.location.href = p_gotourl;
						window.close();
					}else{
						location.href = p_gotourl;
					}
				}else{
					data = data.replace('false#','');
					ShowAlert('완료하지 못했습니다.\r\n'+data);
				}
			},
			error : function(data, status, err) {
				//console.log(data);
				alert('error');
			}
		});


}

// 왼쪽에서부터 채운다는 의미
function fn_lpad(s, c, n) {    
    if (! s || ! c || s.length >= n) {
        return s;
    }

    var max = (n - s.length)/c.length;
    for (var i = 0; i < max; i++) {
        s = c + s;
    }
    return s;
}

 

// 오른쪽에서부터 채운다는 의미
function fn_rpad(s, c, n) {  
    if (! s || ! c || s.length >= n) {
        return s;
    }

    var max = (n - s.length)/c.length;
    for (var i = 0; i < max; i++) {
        s += c;
    }
    return s;
}

/* 숫자에 콤마 넣기 */
function AddComma(str) {
    str = String(str);
    return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}

/* 확인 후 전송 합니다. */
function SendStringWithConfirm(p_message,p_url,p_data,p_gotourl,popGbn){

	//ShowConfirm(p_message);
	
	var Confirm_callback = function(){
		$.ajax({
		  type	: "POST",
		  url	: p_url,
		  data	: p_data,
		  success : function(data) {

					if (data=='true'){
						if(!fnIsNull(popGbn) && popGbn == "P"){
							opener.location.href = p_gotourl;
						}else{
							location.href = p_gotourl;
						}
						
					}else{
						alert(data);
					}
				},
				error : function(data, status, err) {
					alert('error');
				}
		});
	}

	if(confirm(p_message)){
		Confirm_callback();
	}

}

/* post로 전송 합니다. */
function SendString(p_url,p_data,p_gotourl){

	$.ajax({
	  type: "POST",
	  url: p_url,
	  data: p_data,
	  success : function(data) {

				if (data=='true'){
					location.href=p_gotourl;
				}else{
					alert(data);
				}
			},
			error : function(data, status, err) {
				alert('error');
			}
	});
}

function contentPrint(divId){
	//$scope.click_printDiv(divId);
	window.print();
}

function onlyNumber(event) {
	var key = window.event ? event.keyCode : event.which;

    if ((event.shiftKey == false) && ((key  > 47 && key  < 58) || (key  > 95 && key  < 106)
    || key  == 35 || key  == 36 || key  == 37 || key  == 39  // 방향키 좌우,home,end
    || key  == 8  || key  == 46  || key == 9) // back space,del,tab
    ) {
        return true;
    }else {
        return false;
    }
};

function onlyNumberEnter(event) {
	var key = window.event ? event.keyCode : event.which;

    if ((event.shiftKey == false) && ((key  > 47 && key  < 58) || (key  > 95 && key  < 106)
    || key  == 35 || key  == 36 || key  == 37 || key  == 39  // 방향키 좌우,home,end
    || key  == 8  || key  == 46  || key == 9 || key == 13) // back space,del,tab,enter
    ) {
        return true;
    }else {
        return false;
    }
};

function isInteger(objValue)
{
	var bool = true;

	if(objValue == null || objValue == "")
		bool = false;
	else
	{
		for (var i=0; i<objValue.length; i++)

		{
			ch = objValue.charCodeAt(i);
				if(!(ch >= 0x30 && ch <= 0x39))
				{
					bool = false;
					break;
				}
		}
	}
	return bool;
};

function onlyNumberCheck(obj){
	if(fnIsNull(obj.value)) return; 
	if(!isInteger(obj.value)){
		var alertMsg  = "정수 숫자만 입력하실 수 있습니다.\n\n";
			alertMsg += "입력범위 : 0 ~ 9\n";
			alertMsg += "입력예시 : 011, 2003, 1234567890, etc.";
		alert(alertMsg);
		obj.value = "";

	}
}


function fnIsNull(str){
	
	str = $.trim(str);

	if(str == null || str == 'undefined' || str.length == 0) { 
		return true; 
	}

	return false;
}

function strCut(str, len, el){
	
	if(fnIsNull(str)){ 
		return ""; 
	}

	if(fnIsNull(el)) { el = "..."; }

	if(str.length >= len){
		return str.substr(0,len)+"...";
	}else{
		return str;
	}
}

function fn_login(){
	//레이어 팝업 띄우기
	//LoadPopup(g_w_root+'/vm/login.svm.php','&1=1');
	//var $href = $(this).attr('href');
	layer_popup("#login-popup");
}

function layer_popup(el){

	var $el = $(el);								//레이어의 id를 $el 변수에 저장
	var isDim = $el.prev().hasClass('login-bg');	//dimmed 레이어를 감지하기 위한 boolean 변수

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

function fn_loginSubmit(id, pwd, url){
	$item				= {};
	$item.SYSTEM_GB		= 'ANGE';
	$item.id			= $(id).val();
	$item.password		= $(pwd).val();
	
	if (fnIsNull($item.id)) {
		dialogs.notify('알림', '아이디를 입력하세요', {size: 'md'});
		$(id).focus();
		return;
	}

	if (fnIsNull($item.password)) {
		dialogs.notify('알림', '패스워드를 입력하세요', {size: 'md'});
		$(pwd).focus();
		return;
	}

	// 로그인
	$scope.login($item.id, $item, 
		(function(data,status){

			if (fnIsNull(data.USER_ID)) {
				dialogs.error ('오류', '아이디나 패스워드가 일치하지 않습니다.', {size: 'md'});
			}else{
				dialogs.notify('알림', '정상적으로 로그인 되었습니다.', {size: 'md'});
				$scope.addMileage('LOGIN', null);
				
				if (!fnIsNull($scope.login_reload) && $scope.login_reload == true) {
					location.reload();
				}else{

					if (fnIsNull(url)) {
						location.href = g_w_root;
					}else{
						if(url == "md"){
							HidePopup();
						}else{
							location.href = url;
						}
					}

				}

				$rootScope.login			= true;
				$rootScope.authenticated	= true;
				$rootScope.user_info		= data;
				$rootScope.uid				= data.USER_ID;
				$rootScope.name				= data.USER_NM;
				$rootScope.role				= data.ROLE_ID;
				$rootScope.mileage			= data.REMAIN_POINT;
				$rootScope.system			= data.SYSTEM_GB;
				$rootScope.menu_role		= data.MENU_ROLE;
				$rootScope.email			= data.EMAIL;
				$rootScope.nick				= data.NICK_NM;
				$rootScope.session			= data;
			}
		})
	);
}

function fn_logout(){
	
	if(dialogs.confirm('알림', '로그아웃 하시겠습니까?', {size: 'md'})){
		dataService.logout('logout',(function(data){
				alert("로그아웃 되었습니다");
				location.href = g_w_root;
			})
		);
	}

}

function fn_parseGetChoice(divId, que_data, classId){
	
	if(fnIsNull(que_data)){
		$("#"+divId).empty();
		return null;
	}
	
	$("#"+divId).empty();

	var QUE				= new Array();
	que_data			= que_data.replace(/&quot;/gi, '"'); // replace all 효과
	var parse_que_data	= JSON.parse(que_data);

	for(var x in parse_que_data){

		var choice = [];
		if(parse_que_data[x].type == 0){ // 객관식일때
			var select_answer = parse_que_data[x].choice.split(';'); // ;를 기준으로 문자열을 잘라 배열로 변환

			for(var i=0; i < select_answer.length; i++){
				choice.push(select_answer[i]); // 선택문항 값 push 하여 배열에 저장
			}
		}else if(parse_que_data[x].type == 1){ // 주관식일때
			choice = "";
		}else if(parse_que_data[x].type == 2){ // 통합형
			var select_answer = parse_que_data[x].choice.split(';'); // ;를 기준으로 문자열을 잘라 배열로 변환

			for(var i=0; i < select_answer.length; i++){
				choice.push(select_answer[i]); // 선택문항 값 push 하여 배열에 저장
			}
		}else if(parse_que_data[x].type == 3){ // 복수
			var select_answer = parse_que_data[x].choice.split(';'); // ;를 기준으로 문자열을 잘라 배열로 변환

			for(var i=0; i < select_answer.length; i++){
				choice.push(select_answer[i]); // 선택문항 값 push 하여 배열에 저장
			}
		}else if(parse_que_data[x].type == 4){ // 장문입력
			choice = "";
		}

		var index = parseInt(x)+parseInt(1);
		var type  = parse_que_data[x].type;

		var html =  "<dl>";
			html += "<input type=\"hidden\" name=\"index[]\" value=\""+index+"\" class='"+classId+"'/>";
			html += "<dt><label for=\"addition-class1\">"+parse_que_data[x].title+"</label></dt>";
			html += "<dd>";
			html += "<div class=\"form-group\">";
			if(type == '1'){
				html += "<input  type=\"text\" title=\"답변\" class=\"form-control\" name=\"answer"+index+"\" id=\"answer"+index+"\"/>";
			}

			if(type == '0'){

				if(!fnIsNull(choice)){
					for(var a=0; a < choice.length; a++){
						html += "<input type=\"radio\" title=\"문항\" name=\"answer"+index+"\" id=\"answer"+index+"\" value=\""+choice[a]+"\"/>"+choice[a];
					}
				}
			}

			if(type == '2'){

				if(!fnIsNull(choice)){
					for(var a=0; a < choice.length; a++){
						html += "<input type=\"radio\" title=\"문항\" name=\"answer"+index+"\" id=\"answer"+index+"\" value=\""+choice[a]+"\"/>"+choice[a];
					}
				}
				//html += "<input type=\"radio\" title=\"기타\"  name=\"answer"+index+"\" id=\"answer"+index+"\" value=\"etc\"/>기타";
				//html += "<input type=\"text\" name=\"answer_etc"+index+"\" id=\"answer_etc"+index+"\"/>";
			}

			if(type == '3'){

				if(!fnIsNull(choice)){
					for(var a=0; a < choice.length; a++){
						html += "<input type=\"checkbox\" title=\"문항\" name=\"answer"+index+"\" id=\"answer"+index+"\" value=\""+choice[a]+"\"/>"+choice[a];
					}
				}
				//html += "<input type=\"radio\" title=\"기타\"  name=\"answer"+index+"\" id=\"answer"+index+"\" value=\"etc\"/>기타";
				//html += "<input type=\"text\" name=\"answer_etc"+index+"\" id=\"answer_etc"+index+"\"/>";
			}

			if(type == '4'){

				html += "<textarea name=\"answer"+index+"\" class=\"form-control\" id=\"answer"+index+"\" title=\"장문\"></textarea>";
			}
		html += "</div>";
		html += "</dd>";
		html += "</dl>";
		$("#"+divId).append(html);
		
		QUE.push({"index" : index,"title" : parse_que_data[x].title, "type" : parse_que_data[x].type, "choice" :choice});
	}

	return QUE;

}

function fn_parseGetChoice2(divId, que_data, classId){
	
	if(fnIsNull(que_data)){
		$("#"+divId).empty();
		return null;
	}
	
	//$("#"+divId).empty();

	var QUE				= new Array();
	que_data			= que_data.replace(/&quot;/gi, '"'); // replace all 효과
	var parse_que_data	= JSON.parse(que_data);

	for(var x in parse_que_data){

		var choice = [];
		if(parse_que_data[x].type == 0){ // 객관식일때
			var select_answer = parse_que_data[x].choice.split(';'); // ;를 기준으로 문자열을 잘라 배열로 변환

			for(var i=0; i < select_answer.length; i++){
				choice.push(select_answer[i]); // 선택문항 값 push 하여 배열에 저장
			}
		}else if(parse_que_data[x].type == 1){ // 주관식일때
			choice = "";
		}else if(parse_que_data[x].type == 2){ // 통합형
			var select_answer = parse_que_data[x].choice.split(';'); // ;를 기준으로 문자열을 잘라 배열로 변환

			for(var i=0; i < select_answer.length; i++){
				choice.push(select_answer[i]); // 선택문항 값 push 하여 배열에 저장
			}
		}else if(parse_que_data[x].type == 3){ // 복수
			var select_answer = parse_que_data[x].choice.split(';'); // ;를 기준으로 문자열을 잘라 배열로 변환

			for(var i=0; i < select_answer.length; i++){
				choice.push(select_answer[i]); // 선택문항 값 push 하여 배열에 저장
			}
		}else if(parse_que_data[x].type == 4){ // 장문입력
			choice = "";
		}

		var index = parseInt(x)+parseInt(1);
		var type  = parse_que_data[x].type;

		var html =  "<tr>";
			html +=  "<td colspan=\"2\" class=\"space\">";
			html +=  "<dl>";
			html += "<input type=\"hidden\" name=\"index[]\" value=\""+index+"\" class='"+classId+"'/>";
			html += "<dt class=\"necessary\"><label for=\"addition-class\">"+parse_que_data[x].title+"</label></dt>";
			html += "<dd>";
			html += "<div class=\"form-group\">";
			if(type == '1'){
				html += "<input  type=\"text\" title=\"답변\" class=\"form-control\" name=\"answer"+index+"\" id=\"answer"+index+"\"/>";
			}

			if(type == '0'){

				if(!fnIsNull(choice)){
					for(var a=0; a < choice.length; a++){
						html += "<label><input type=\"radio\" title=\"문항\" name=\"answer"+index+"\" id=\"answer"+index+"\" value=\""+choice[a]+"\"/>"+choice[a]+"</label>";
					}
				}
			}

			if(type == '2'){

				if(!fnIsNull(choice)){
					for(var a=0; a < choice.length; a++){
						html += "<label><input type=\"radio\" title=\"문항\" name=\"answer"+index+"\" id=\"answer"+index+"\" value=\""+choice[a]+"\"/>"+choice[a]+"</label>";
					}
				}
				//html += "<input type=\"radio\" title=\"기타\"  name=\"answer"+index+"\" id=\"answer"+index+"\" value=\"etc\"/>기타";
				//html += "<input type=\"text\" name=\"answer_etc"+index+"\" id=\"answer_etc"+index+"\"/>";
			}

			if(type == '3'){

				if(!fnIsNull(choice)){
					for(var a=0; a < choice.length; a++){
						html += "<label><input type=\"checkbox\" title=\"문항\" name=\"answer"+index+"\" id=\"answer"+index+"\" value=\""+choice[a]+"\"/>"+choice[a]+"</label>";
					}
				}
				//html += "<input type=\"radio\" title=\"기타\"  name=\"answer"+index+"\" id=\"answer"+index+"\" value=\"etc\"/>기타";
				//html += "<input type=\"text\" name=\"answer_etc"+index+"\" id=\"answer_etc"+index+"\"/>";
			}

			if(type == '4'){

				html += "<textarea name=\"answer"+index+"\" class=\"form-control\" id=\"answer"+index+"\" title=\"장문\"></textarea>";
			}
		html += "</div>";
		html += "</dd>";
		html += "</dl>";
		html += "</td>";
		html += "</tr>";
		$("#"+divId).append(html);
		
		QUE.push({"index" : index,"title" : parse_que_data[x].title, "type" : parse_que_data[x].type, "choice" :choice});
	}

	return QUE;

}

function fn_getPollCheck1(ada_type, ada_que_type){
	
	//체험단
	if(ada_type == "exp"){
		
		if($("#credit_agreement_Y").is(":checked")){
			$scope.item.CREDIT_FL = 'Y';
		}else{
			alert('제 3자 정보제공에 동의 하셔야 상품 발송이 가능합니다.');
			return false;
		}
		if($("#marketing_agreement_Y").is(":checked")){
			$scope.item.MARKETING_FL = 'Y';
		}else{
			alert('콘텐츠 마케팅 활용에 동의 하셔야 이벤트 참여가 가능합니다. ');
			return false;
		}

		if($scope.item.BABY_YEAR == undefined){
			alert('아기 생일년도를 선택하세요');
			return false;
		}

		if($scope.item.BABY_MONTH == undefined){
			alert('아기 생년월을 선택하세요');
			return false;
		}

		if($scope.item.BABY_DAY == undefined){
			alert('아기 생년일을 선택하세요');
			return false;
		}

		if($scope.item.DELIVERY_YEAR == undefined){
			$scope.item.DELIVERY_YEAR = '';
		}else{
			$scope.item.DELIVERY_YEAR = $scope.item.DELIVERY_YEAR;
		}

		if($scope.item.DELIVERY_MONTH == undefined){
			$scope.item.DELIVERY_MONTH = '';
		}else{
			$scope.item.DELIVERY_MONTH = $scope.item.DELIVERY_MONTH;
		}

		if($scope.item.DELIVERY_DAY == undefined){
			$scope.item.DELIVERY_DAY = '';
		}else{
			$scope.item.DELIVERY_DAY = $scope.item.DELIVERY_DAY;
		}

		return true;
	

	//이벤트
	}else if(ada_type == "event"){
		
		return true;

	}

}

function fn_setPollItem1(ada_type, ada_que_type, classId, blogId){

	//체험단
	if(ada_type == "exp"){

		var babybirthday = '';
		babybirthday = $scope.item.BABY_YEAR + '-' + $scope.item.BABY_MONTH + '-' + $scope.item.BABY_DAY;

		var deliveryday = '';
		deliveryday = $scope.item.DELIVERY_YEAR + '-' + $scope.item.DELIVERY_MONTH + '-' + $scope.item.DELIVERY_DAY;

		var cnt = inputBlog = $("."+blogId).length;
		$scope.item.BLOG_URL = '';

		var flag = true;
		$("input[name='blog[]']").each(function(index, element) {
			if($(element).val() == ""){
				$(element).focus();
				flag = false;
				return;
			}

			if(index != (cnt -1)){
				$scope.item.BLOG_URL += $(element).val()+';';
			}else{
				$scope.item.BLOG_URL += $(element).val();
			}
		});

		if(flag == false){
			alert("블로그를 입력하시기 바랍니다.");
			return false;
		}

		if($scope.item.REASON != undefined){
			$scope.item.REASON = $scope.item.REASON.replace(/^\s+|\s+$/g,'');
		}

		if($scope.item.REASON == undefined || $scope.item.REASON == ""){
			alert('신청사유를 작성하세요');
			return false;
		}

		var answer = '"1":"'+babybirthday+'","2":"'+deliveryday+'","3":"'+$scope.item.BLOG_URL+'","4":"'+$scope.item.REASON+'"';

		// 문항정보
		if($scope.item.QUE != undefined ){

			$rootScope.jsontext2 = new Array();

			var poll_length = $('.'+classId).length;

			for(var i=1; i<=poll_length; i++){

				if(document.getElementById("answer"+i).type == 'radio'){
					$rootScope.jsontext2[i] = '"'+parseInt(i+4)+'":"'+$("input[name=answer"+i+"]:checked").val() +'"';
				}else if(document.getElementById("answer"+i).type == 'checkbox'){
					var checkvalue = '';
					$("input[name=answer"+i+"]:checked").each(function() {
						checkvalue += $(this).val() + ';';
					});
					$rootScope.jsontext2[i] = '"'+parseInt(i+4)+'":"'+ checkvalue+'"';
				}else if(document.getElementById("answer"+i).type == 'text'){
					$rootScope.jsontext2[i] = '"'+parseInt(i+4)+'":"'+ document.getElementById("answer"+i).value+'"';
				}else if(document.getElementById("answer"+i).type == 'textarea'){
					$rootScope.jsontext2[i] = '"'+parseInt(i+4)+'":"'+ document.getElementById("answer"+i).value+'"';
				}
			}

			$scope.item.ANSWER = '{'+answer+$rootScope.jsontext2+'}';
			//$scope.item.ANSWER = $scope.item.ANSWER.replace(/{,/ig, '{');
		}else{
			$scope.item.ANSWER = '{'+answer+'}';
		}

		return true;

	//이벤트
	}else if(ada_type == "event"){

		var poll_length = $('.'+classId).length;

		for(var i=1; i<=poll_length; i++){

			if(document.getElementById("answer"+i).type == 'radio'){
				$rootScope.jsontext2[i] = '"'+i+'":"'+$("input[name=answer"+i+"]:checked").val() +'"';
			}else if(document.getElementById("answer"+i).type == 'checkbox'){
				var checkvalue = '';
				$("input[name=answer"+i+"]:checked").each(function() {
					checkvalue += $(this).val() + ';';
				});
				$rootScope.jsontext2[i] = '"'+i+'":"'+ checkvalue+'"';
			}else if(document.getElementById("answer"+i).type == 'text'){
				$rootScope.jsontext2[i] = '"'+i+'":"'+ document.getElementById("answer"+i).value+'"';
			}else if(document.getElementById("answer"+i).type == 'textarea'){
				$rootScope.jsontext2[i] = '"'+i+'":"'+ document.getElementById("answer"+i).value+'"';
			}
		}

		$scope.item.ANSWER = '{'+$rootScope.jsontext2+'}';
		$scope.item.ANSWER = $scope.item.ANSWER.replace(/{,/ig, '{');
		
		return true;
	}

}

function autoFileUpload(url, formId, inputId){


}

function fn_menuUrlLink(menuId, link, subCnt){
	
	//로그인이 필요한 페이지인지 체크
	var auth = true;
	for(var i in $loginCheckMenu) {
		if($loginCheckMenu[i] == menuId){
			auth = false;
			break;
		}
	}

	if(!auth){
		//로그인정보 체크
		if(fnIsNull($rootScope.uid)){
			fn_login();
		}else{
			location.href = link;
		}
	}else{
		location.href = link;
	}
}

//즐겨잧기
function fn_favorite(){
   
	var bookmarkURL		= window.location.href;
    var bookmarkTitle	= document.title;
    var triggerDefault = false;

    if (window.sidebar && window.sidebar.addPanel) {
        // Firefox version &lt; 23
        window.sidebar.addPanel(bookmarkTitle, bookmarkURL, '');
    } else if ((window.sidebar && (navigator.userAgent.toLowerCase().indexOf('firefox') < -1)) || (window.opera && window.print)) {
        // Firefox version &gt;= 23 and Opera Hotlist
        var $this = $(this);
        $this.attr('href', bookmarkURL);
        $this.attr('title', bookmarkTitle);
        $this.attr('rel', 'sidebar');
        $this.off(e);
        triggerDefault = true;
    } else if (window.external && ('AddFavorite' in window.external)) {
        // IE Favorite
        window.external.AddFavorite(bookmarkURL, bookmarkTitle);
    } else {
        // WebKit - Safari/Chrome
        alert((navigator.userAgent.toLowerCase().indexOf('mac') != -1 ? 'Cmd' : 'Ctrl') + '+D 를 이용해 이 페이지를 즐겨찾기에 추가할 수 있습니다.');
    }

    //return triggerDefault;

}


function resizeImg(imgObj, width, height) {

	if(imgObj.width > width) {
		imgObj.width = width;
	}

	if(height != "") {
		if(imgObj.height > height) {
			imgObj.height = height;
		}
	}

}

function strip_tags (input, allowed) { 
	// making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
	allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); 
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi , commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
	return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';});
}


var is_init  = true;

function imgCheck(url){
	
	return true;
}