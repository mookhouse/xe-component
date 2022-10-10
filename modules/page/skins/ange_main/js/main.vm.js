function fn_mainLogin(){

	if(fnIsNull($("#main_loginId").val())){
		alert("아이디를 입력하여 주시기 바랍니다");
		$("#main_loginId").focus();
		return;
	}

	if(fnIsNull($("#main_loginPwd").val())){
		alert("패스워드를 입력하여 주시기 바랍니다");
		$("#main_loginPwd").focus();
		return;
	}

	fn_loginSubmit($("#main_loginId"), $("#main_loginPwd"), '');
}

/*$scope.storyStep = 1;
$scope.onStoryStep = function(step){
	$scope.storyStep = step;
	
	$("#subject_tab1").removeClass("active");
	$("#subject_tab2").removeClass("active");
	$("#subject_tab3").removeClass("active");
	$("#subject_tab4").removeClass("active");
	$("#subject_tab5").removeClass("active");

	$("#subject_list_tab1").hide();
	$("#subject_list_tab2").hide();
	$("#subject_list_tab3").hide();
	$("#subject_list_tab4").hide();
	$("#subject_list_tab5").hide();

	$("#subject_tab"+step).addClass("active");
	$("#subject_list_tab"+step).show();
}

$scope.onStoryNextStep = function(idx){
	var step = $scope.storyStep + parseInt(idx);

	if(step == 0){
		step = 1;
	}

	if(step == 6){
		step = 5;
	}

	$scope.storyStep = step;
	$scope.onStoryStep($scope.storyStep);
}
*/

//상세페이지 이동
function latest(mainmenu,submenu,no,comm_no){
	if(submenu){
		location.href = "?menu="+mainmenu+"&submenu="+submenu+"&NO="+no+"&COMM_NO="+comm_no;
	} else {
		location.href = "?menu=main";
	}
}

function getCookie(name){
	var nameOfCookie = name + "=";
	var x = 0;
	while( x <= document.cookie.length){
		var y = (x + nameOfCookie.length);
		if(document.cookie.substring(x,y) == nameOfCookie){
			if((endOfCookie = document.cookie.indexOf(";",y)) == -1){
				endOfCookie = document.cookie.length;
			}
			return unescape(document.cookie.substring(y, endOfCookie));
		}
		x = document.cookie.indexOf(" ",x) + 1;
		if(x == 0) break;
	}
	return "";
}

function setCookie(name,value,expirehours,domain){
	var todayDate = new Date();
	todayDate.setHours(todayDate.getHours() + expirehours);
	document.cookie = name + "=" + escape(value) + "; path=/; expires=" + todayDate.toGMTString() + ";";
	if(domain){
		document.cookie += "domain=" + domain + ";";
	}
}

function close_pop(){
	var f = document.getElementById('view_yn1');
	if(f.checked == true){
		setCookie("popclose1","SET",1);
	}
	$("#popup-content3").find(".close").trigger("click");
}

function view_pop(){
	var popcookie = getCookie("popclose1");
	if(popcookie != "SET"){
		emergency_popup("#popup-content3");
	}
}

function emergency_popup(el){

		var $el = $(el);        //레이어의 id를 $el 변수에 저장
		var isDim = $el.prev().hasClass('popup-bg');   //dimmed 레이어를 감지하기 위한 boolean 변수

		isDim ? $('.renew-popup').fadeIn() : $el.fadeIn();

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
			isDim ? $('.renew-popup').fadeOut() : $el.fadeOut(); // 닫기 버튼을 클릭하면 레이어가 닫힌다.
			return false;
		});

		$('.renew-popup .popup-bg').click(function(){
			$('.renew-popup').fadeOut();
			return false;
		});
	}

$(window).load(function(){
	//view_pop();

	var now_qna_ul = 0;
	var max_qna_ul = $(".qna-ul").length - 1;
	$(document).on("click", ".qna-btn", function(){
		var types = $(this).attr("data-type");
		switch(types){
			case "prev":
				if(now_qna_ul == 0) return;
				now_qna_ul--;
				$(".qna-ul").hide().eq(now_qna_ul).show();
				break;
			case "next":
				if(now_qna_ul == max_qna_ul) return;
				now_qna_ul++;
				$(".qna-ul").hide().eq(now_qna_ul).show();
				break;
		}
	});

	var now_movie_ul = 0;
	var max_movie_ul = $(".movie-ul").length - 1;
	$(document).on("click", ".movie-btn", function(){
		var types = $(this).attr("data-type");
		switch(types){
			case "prev":
				if(now_movie_ul == 0){
					now_movie_ul = max_movie_ul;
				} else {
					now_movie_ul--;
				}
				$(".movie-ul").hide().eq(now_movie_ul).show();
				break;
			case "next":
				if(now_movie_ul == max_movie_ul){
					now_movie_ul = 0;
				} else {
					now_movie_ul++;
				}
				$(".movie-ul").hide().eq(now_movie_ul).show();
				break;
		}
	});

	var now_subject_tab = 1;
	var max_subject_tab = 5;
	$(document).on("click", ".subject-btn", function(){
		var types = $(this).attr("data-type");
		switch(types){
			case "prev":
				if(now_subject_tab == 1){
					now_subject_tab = max_subject_tab;
				} else {
					now_subject_tab--;
				}
				// 탭 버튼 활성화
				$(".stab").removeClass("active");
				$("#subject_tab" + now_subject_tab).addClass("active");
				// 탭 내용 show/hide
				$(".scon").hide();
				$("#subject_list_tab" + now_subject_tab).show();
				break;
			case "next":
				if(now_subject_tab == max_subject_tab){
					now_subject_tab = 1;
				} else {
					now_subject_tab++;
				}
				// 탭 버튼 활성화
				$(".stab").removeClass("active");
				$("#subject_tab" + now_subject_tab).addClass("active");
				// 탭 내용 show/hide
				$(".scon").hide();
				$("#subject_list_tab" + now_subject_tab).show();
				break;
		}
	});
	$(document).on("click", ".stab_btn", function(){
		var tab_num = $(this).attr("data-tab");
		now_subject_tab = tab_num;
		$(".stab").removeClass("active");
		$("#subject_tab" + now_subject_tab).addClass("active");
		$(".scon").hide();
		$("#subject_list_tab" + now_subject_tab).show();
	});

	var now_community_ul = 0;
	var max_community_ul = $(".community-list").length - 1;
	$(document).on("click", ".community-btn", function(){
		var types = $(this).attr("data-type");
		switch(types){
			case "prev":
				if(now_community_ul == 0) return;
				now_community_ul--;
				$(".community-list").hide().eq(now_community_ul).show();
				break;
			case "next":
				if(now_community_ul == max_community_ul) return;
				now_community_ul++;
				$(".community-list").hide().eq(now_community_ul).show();
				break;
		}
	});
	
	var now_brand_ul = 0;
	var max_brand_ul = $(".brand-ul").length - 1;
	$(document).on("click", ".brand-btn", function(){
		var types = $(this).attr("data-type");
		switch(types){
			case "prev":
				if(now_brand_ul == 0){
					now_brand_ul = max_brand_ul;
				} else {
					now_brand_ul--;
				}
				$(".brand-ul").hide().eq(now_brand_ul).show();
				break;
			case "next":
				if(now_brand_ul == max_brand_ul){
					now_brand_ul = 0;
				} else {
					now_brand_ul++;
				}
				$(".brand-ul").hide().eq(now_brand_ul).show();
				break;
		}
	});
});