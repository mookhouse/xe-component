var Paging = function(totalCnt, dataSize, pageSize, pageNo, token){

	   totalCnt = parseInt(totalCnt);		// 전체레코드수
	   dataSize = parseInt(dataSize);       // 페이지당 보여줄 데이타수
	   pageSize = parseInt(pageSize);		// 페이지 그룹 범위       1 2 3 5 6 7 8 9 10
	   pageNo   = parseInt(pageNo)+1;		// 현재페이지
		
	   var lastPageNum = Math.floor((totalCnt-1)/dataSize);
	   var html = new Array();
	   if(totalCnt == 0){
			return "";
	   }
	  
	   // 페이지 카운트
	   var pageCnt = totalCnt % dataSize;         
	   if(pageCnt == 0){
			pageCnt = parseInt(totalCnt / dataSize);
	   }else{
			pageCnt = parseInt(totalCnt / dataSize) + 1;
	   }
	  
	   

	   var pRCnt = parseInt(pageNo / pageSize);
	   if(pageNo % pageSize == 0){
				  pRCnt = parseInt(pageNo / pageSize) - 1;
	   }

	   if (totalCnt > 1) {
			if (pageNo > 1) {
				html.push(" <li class=\"first\"><a aria-label=\"Previous\" href=\"javascript:" + token + "(0);\" title=\"맨앞으로\"><i class=\"icon-angle-double-left\"></i></a></li>");
			}
	   }



	   //이전 화살표
	   if(pageNo > pageSize){
				  var s2;
				  if(pageNo % pageSize == 0){
							  s2 = pageNo - pageSize;
				  }else{
							  s2 = pageNo - pageNo % pageSize;
				  }
				  html.push("<li class=\"prev\"><a aria-label=\"Previous\" href=\"javascript:" + token + "('"+(s2-1)+"');\" class=\"pprev\" title=\"이전\"><i class=\"icon-angle-left\"></i></a></li>");
	   }else{
				  html.push("<li class=\"prev\"><a aria-label=\"Previous\" href=\"javascript:;\" class=\"pprev\" title=\"이전\"><i class=\"icon-angle-left\"></i></a></li>");
	   }
	  
	   //paging Bar
	   for(var index=pRCnt * pageSize + 1;index<(pRCnt + 1)*pageSize + 1;index++){
				  if(index == pageNo){
						html.push('<li class="active">');
						html.push('<a href="javascript:;">'+index+'</a>');
						html.push('</li>');
				  }else{
						html.push("<li><a href=\"javascript:" + token + "('"+(index-1)+"')\">"+index+"</a></li>");
				  }
				  if(index == pageCnt){
					  break;
				  }else{ 
					  //html.push('|');
				  }
	   }
		
	   //다음 화살표
	   if(pageCnt > (pRCnt + 1) * pageSize){
				  html.push("<li class=\"next\"><a aria-label=\"Next\" href=\"javascript:" + token + "('"+(((pRCnt + 1)*pageSize+1) - 1)+"');\" title=\"다음\"><i class=\"icon-angle-right\"></i></a></li>");
	   }else{
				  html.push("<li class=\"next\"><a aria-label=\"Next\" href=\"javascript:;\" title=\"다음\"><i class=\"icon-angle-right\"></i></a></li>");
	   }

	   
	   if (pageNo < lastPageNum) {
				  html.push("<li class=\"last\"><a aria-label=\"Next\" href=\"javascript:" + token + "('"+lastPageNum+"');\" title=\"맨뒤로\"><i class=\"icon-angle-double-right\"></i></a>");
	   }

	   return html.join("");
}


var Paging2 = function(totalCnt, dataSize, pageSize, pageNo, token){

	   totalCnt = parseInt(totalCnt);		// 전체레코드수
	   dataSize = parseInt(dataSize);       // 페이지당 보여줄 데이타수
	   pageSize = parseInt(pageSize);		// 페이지 그룹 범위       1 2 3 5 6 7 8 9 10
	   pageNo   = parseInt(pageNo);			// 현재페이지
		
	   var lastPageNum = Math.floor((totalCnt-1)/pageSize) + 1;
	   var html = new Array();
	   if(totalCnt == 0){
			return "";
	   }
	  
	   // 페이지 카운트
	   var pageCnt = totalCnt % dataSize;         
	   if(pageCnt == 0){
			pageCnt = parseInt(totalCnt / dataSize);
	   }else{
			pageCnt = parseInt(totalCnt / dataSize) + 1;
	   }
	  
	   var pRCnt = parseInt(pageNo / pageSize);
	   if(pageNo % pageSize == 0){
				  pRCnt = parseInt(pageNo / pageSize) - 1;
	   }

	   if (totalCnt > 1) {
			if (pageNo > 1) {
				html.push("<li class=\"first\"><a aria-label=\"Previous\" href=\"javascript:" + token + "(0);\" title=\"맨앞으로\"><i class=\"icon-angle-double-left\"></i></a></li>");
			}
	   }

	   //이전 화살표
	   if(pageNo > pageSize){
				  var s2;
				  if(pageNo % pageSize == 0){
							  s2 = pageNo - pageSize;
				  }else{
							  s2 = pageNo - pageNo % pageSize;
				  }
				  html.push("<li class=\"prev\"><a aria-label=\"Previous\" href=\"javascript:" + token + "('"+(s2)+"');\" title=\"이전\"><i class=\"icon-angle-left\"></i></a></li>");
	   }else{
				  html.push("<li class=\"prev\"><a aria-label=\"Previous\" href=\"javascript:;\" title=\"이전\"><i class=\"icon-angle-left\"></i></a></li>");
	   }
	  
	   //paging Bar
	   for(var index=pRCnt * pageSize + 1;index<(pRCnt + 1)*pageSize + 1;index++){
				  if(index == pageNo){
						html.push('<li class="active">');
						html.push('<a href="javascript:;">'+index+'</a>');
						html.push('</li>');
				  }else{
						html.push("<li><a href=\"javascript:" + token + "('"+(index)+"')\">"+index+"</a></li>");
				  }
				  if(index == pageCnt){
					  break;
				  }else{ 
					  //html.push('|');
				  }
	   }
		
	   //다음 화살표
	   if(pageCnt > (pRCnt + 1) * pageSize){
				  html.push("<li class=\"next\"><a aria-label=\"Next\" href=\"javascript:" + token + "('"+(((pRCnt + 1)*pageSize+1) )+"');\" title=\"다음\"><i class=\"icon-angle-right\"></i></a></li>");
	   }else{
				  html.push("<li class=\"next\"><a aria-label=\"Next\" href=\"javascript:;\" title=\"다음\"><i class=\"icon-angle-right\"></i></a></li>");
	   }

	   
	   if (pageNo < lastPageNum) {
				  html.push("<li class=\"last\"><a href=\"javascript:" + token + "('"+lastPageNum+"');\" title=\"맨뒤로\"><i class=\"icon-angle-double-right\"></i></a>");
	   }

	   return html.join("");
}