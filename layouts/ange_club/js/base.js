(function($) {
	// 메뉴 관련
	$(document).on('mouseenter focus', '#gnb-nav .gnb-list > li', function() {
		$(this).siblings().removeClass('active');
		$(this).addClass('active');
	});
		$(document).on('mouseleave', '#gnb-nav .gnb-list > li', function() {
		$(this).removeClass('active');
	});
	// 오름차순 내림차순 관련
	$(document).on("click", ".table-wrap.list .table th a", function(e) {
		if($(this).parent().hasClass("none-order")){ // 정렬버튼을 한번도 누르지 않은상태
			$(this).parent().addClass("ascending-order").removeClass("none-order"); //최초 클릭시 ascending
		} else {
			if($(this).parent().hasClass("ascending-order")){
				$(this).parent().addClass("descending-order").removeClass("ascending-order");
			} else if($(this).parent().hasClass("descending-order")){
				$(this).parent().addClass("ascending-order").removeClass("descending-order");
			}
		}
		e.preventDefault();
	});
})(jQuery);

//팝업 상자 -->
function LoadPopup(p_url, w, h){
	w = !fnIsNull(w) ? w : 1024;
	h = !fnIsNull(h) ? h : 850;

	var px = (screen.availWidth  - w) / 2;
	var py = (screen.availHeight - h) / 2;

	var win = window.open(p_url , 'new window', 'scrollbars=yes, resizable=yes, toolbar=no, width='+ w +', height='+ h +',left='+px+',top='+py);

	//팝업페이지로 focus 이동
	if (win.focus) 	  {	win.focus();	 }
	// if (!win.closed)  {	win.focus();	 }
}

function fnIsNull(str){
	str = $.trim(str);
	if(str == null || str == 'undefined' || str.length == 0) { 
		return true; 
	}
	return false;
}

function numberCheck(obj) { 
    var val = obj.value; 
    var re = /[^0-9|.]/gi; obj.value = val.replace(re, ''); 
};

// function removeComma() {
//     var item = $(event.srcElement);
//     var val = $(item).val();

//     if (parseInt(val) == 0) {
//         $(item).val("");
//     }
// }

// function onlyNumber(event) {
// 	var key = window.event ? event.keyCode : event.which;
// 	console.log(key);
//     if ((event.shiftKey == false) && ((key  > 47 && key  < 58) || (key  > 95 && key  < 106)
//     || key  == 35 || key  == 36 || key  == 37 || key  == 39  // 방향키 좌우,home,end
//     || key  == 8  || key  == 46  || key == 9) // back space,del,tab
//     ) {
//         return true;
//     }else {
//         return false;
//     }
// };

//  $( document ).ready(function() {

// 	$('.datetimepicker').datetimepicker({
// 	  timepicker:false,
// 	  format:'Y-m-d',
// 	  style:'z-index: 101000;',
// 	  closeOnDateSelect:true,
// 	  lang: 'ko'
// 	});

// });

/* 파일 사이즈 형식을 반환합니다. */
// function FormatFilesize(p_value){
// 	var t_value = Number(p_value);

// 	if (t_value<1024) { return p_value+' B'; }
// 	if (t_value<1024*1024) { return (Math.round(t_value/1024))+' KB'; }
// 	if (t_value<1024*1024*1024) { return (Math.round(t_value/1024/1024))+' MB'; }
// }

/* 전화번호 형식을 반환합니다. */
// function FormatPhone(p_value){
// }

// /* ,가 포함된 숫자 형식을 반환합니다. */
// function FormatPhone(p_value){
// }

/* 폼을 전송합니다. */
// function SendForm(p_obj,p_gotourl,p_checkvalue,popGbn){

// 	//if(!confirm("저장 하시겠습니까")){
// 	//	return false;
// 	//}

// 	p_checkvalue = typeof p_checkvalue !== 'undefined' ? p_checkvalue : false;

// 	event.preventDefault();

// 	// 폼검사
// 	var t_pattern = { 'id':/^[a-zA-Z0-9_]+$/,'email':/^[a-zA-Z0-9_@.]+$/,'number':/^[0-9]+$/ };


// 	if (typeof(CKEDITOR_NAME)!='undefined' && CKEDITOR_NAME!=''){
// 		CKEDITOR.instances[CKEDITOR_NAME].updateElement();
// 	}

// 	if ($('#queform_0').length>0){
// 		SetQuestion();
// 	}

// 	if (p_checkvalue){

// 		for(var t_cnt=0; t_cnt< p_obj.length; t_cnt++){

// 			t_obj = p_obj.elements[t_cnt];

// 			// 유효한 항목인지 확인
// 			if (typeof t_obj!='undefined' && t_obj.name!=''){

// 				// 검사 대상인지 확인
// 				if (t_obj.title!='' && t_obj.type!='radio' && t_obj.type!='button' && t_obj.type!='submit' )
// 				{
					
// 					console.log(t_obj.name);
// 					t_item = t_obj.name.split(':');

// 					if (!t_obj.value){
// 						alert(t_obj.title+'란은 꼭 입력하셔야만 합니다.');
// 						t_obj.focus();
// 						return false;
// 					}

// 					if (t_obj.alt!='' && Number(t_obj.alt)>t_obj.value.length ){
// 						alert(t_obj.title+'\n최소길이를 확인하여 주십시오.');
// 						t_obj.focus();
// 						return false;
// 					}

// 					if (t_item.length>1) {
// 						if (t_pattern[t_item[1]].test(t_obj.value)==false) {
// 							alert('적절하지 않은 값이 입력되었습니다.');
// 							t_obj.focus();
// 							return false;
// 						}
// 					}
// 				}


// 			}

// 		}
// 	}

// 	$.ajax({
// 			method : 'post',
// 			url : p_obj.action,
// 			data: $(p_obj).serializeArray(),
// 			success : function(data) {
// 				t_result = data;
// 				if (t_result.substr(0,4)=='true'){
// 					p_gotourl = p_gotourl.replace('#insert_id#',t_result.substr(5));
// 					//location.href=p_gotourl;
// 					//ShowAlert('저장하였습니다.');
// 					if(!fnIsNull(popGbn) && popGbn == "P"){
// 						opener.location.href = p_gotourl;
// 						window.close();
// 					}else{
// 						location.href = p_gotourl;
// 					}
// 				}else{
// 					data = data.replace('false#','');
// 					ShowAlert('완료하지 못했습니다.\r\n'+data);
// 				}
// 			},
// 			error : function(data, status, err) {
// 				console.log(data);
// 				alert('error');
// 			}
// 		});


// }

/* 숫자에 콤마 넣기 */
// function AddComma(str) {
//     str = String(str);
//     return str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
// }

/* 확인 후 전송 합니다. */
// function SendStringWithConfirm(p_message,p_url,p_data,p_gotourl,popGbn){

// 	//ShowConfirm(p_message);
	
// 	var Confirm_callback = function(){
// 		$.ajax({
// 		  type: "POST",
// 		  url: p_url,
// 		  data: p_data,
// 		  success : function(data) {

// 					if (data=='true'){
// 						if(!fnIsNull(popGbn) && popGbn == "P"){
// 							opener.location.href = p_gotourl;
// 							window.close();
// 						}else{
// 							location.href = p_gotourl;
// 						}
						
// 					}else{
// 						alert(data);
// 					}
// 				},
// 				error : function(data, status, err) {
// 					alert('error');
// 				}
// 		});
// 	}

// 	if(confirm(p_message)){
// 		Confirm_callback();
// 	}

// }

/* post로 전송 합니다. */
// function SendString(p_url,p_data,p_gotourl){

// 	$.ajax({
// 	  type: "POST",
// 	  url: p_url,
// 	  data: p_data,
// 	  success : function(data) {

// 				if (data=='true'){
// 					location.href=p_gotourl;
// 				}else{
// 					alert(data);
// 				}
// 			},
// 			error : function(data, status, err) {
// 				alert('error');
// 			}
// 	});
// }


// Date.prototype.format = function(f) {
//     if (!this.valueOf()) return " ";

//     var weekName = ["일요일", "월요일", "화요일", "수요일", "목요일", "금요일", "토요일"];
//     var d = this;

//     return f.replace(/(yyyy|yy|MM|dd|E|hh|mm|ss|a\/p)/gi, function($1) {
//         switch ($1) {
//             case "yyyy": return d.getFullYear();
//             case "yy": return (d.getFullYear() % 1000).zf(2);
//             case "MM": return (d.getMonth() + 1).zf(2);
//             case "dd": return d.getDate().zf(2);
//             case "E": return weekName[d.getDay()];
//             case "HH": return d.getHours().zf(2);
//             case "hh": return ((h = d.getHours() % 12) ? h : 12).zf(2);
//             case "mm": return d.getMinutes().zf(2);
//             case "ss": return d.getSeconds().zf(2);
//             case "a/p": return d.getHours() < 12 ? "오전" : "오후";
//             default: return $1;
//         }
//     });
// };

// String.prototype.string = function(len){var s = '', i = 0; while (i++ < len) { s += this; } return s;};
// String.prototype.zf = function(len){return "0".string(len - this.length) + this;};
// Number.prototype.zf = function(len){return this.toString().zf(len);};

// function onlyNumberEnter(event) {
// 	var key = window.event ? event.keyCode : event.which;

//     if ((event.shiftKey == false) && ((key  > 47 && key  < 58) || (key  > 95 && key  < 106)
//     || key  == 35 || key  == 36 || key  == 37 || key  == 39  // 방향키 좌우,home,end
//     || key  == 8  || key  == 46  || key == 9 || key == 13) // back space,del,tab,enter
//     ) {
//         return true;
//     }else {
//         return false;
//     }
// };

// function HidePopup(){

// }
//메세지 상자
// function ShowAlert(p_message){
// 	alert(p_message);
// }
// function HideAlert(){
	
// }

// function ShowConfirm(p_message){
// 	return confirm(p_message);
// }

// function HideConfirm(p_value){
	
// }
//var Confirm_callback = function(){  }