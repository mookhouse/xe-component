/**
 * Author : Sung-hwan Kim
 * Email  : hacker9100@marveltree.com
 * Date   : 2014-09-23
 * Description : dataService 선언
 */
var CONSTANT =  {
	SYSTEM_GB			: g_system_gb,
	DASHBOARD_PAGE_SIZE	: 5,
	PAGE_SIZE			: 20,
	BASE_URL			: g_base_url,
	BASE_URL2			: g_base_url,
	UPLOAD_INDEX		: "/service/upload/",
	COMM_NO_NOTICE		: "51,52",
	COMM_NO_FAQ			: "53",
	COMM_NO_ONLINETALK	: "61",
	COMM_NO_QNA			: "",
	VM_PATH				: g_vm_path,
    AD_LOG_URL			: g_base_url + "/adm/io/log.php",
    AD_FILE_URL			: g_base_url + "/adm/upload/",
    AD_SERVER_URL		: g_base_url
};

var SERVER  =   {
    SERVER_URL			: "/service/ajax/"
};

var UPLOAD	=   {
    UPLOAD_INDEX		: "/service/upload/",
    BASE_URL			: g_base_url
};

var helpers = {
	uri			: SERVER.SERVER_URL,
	serviceUri	: 'cms',
	getParam	: function(){
		return {
			  db        : 'ange'
			,_method    : ''        // CRUD 정보 (GET : 조회, POST : 등록, PUT : 수정, DELETE : 삭제)
			,_key       : ''        // 기본키
			,_type      : ''        // CRUD 타입 (list : 목록, item : 모델, )
			,_page      : {}        // 리스트 페이징 정보
			,_search    : {}        // 검색 조건 및 정렬
			,_phase     : ''        // CMS 컨텐츠 단계 (0 : 태스크등록, 10 : 원고작성, 11 : 원고승인대기, 12 : 원고반려, 13 : 원고승인완료, 20 : 편집작성, 21 : 편집승인대기, 22 : 편집반려, 23 : 편집승인완료, 30 : 출판대기, 40 : 완료)
			,_model     : {}        // 모델 정보 (json 형태에서 object 형태로 그대로 넘기는 방법으로 변경)
			,_category  : {}        // 카테고리 정보 (CMS에서는 사용안함)
		};
	}
};

var $location = {
	url : function(link, type, param){
		
		if(fnIsNull(param)){
			param = "";
		}

		if(fnIsNull(type)){
			//alert(g_w_root + link + param);
			location.href = g_w_root + link + param;
		}else if(type == "1"){
			location.href = g_w_root + "/?submenu=" + link + param;
		}
		
	}
};

var dialogs = {
	error : function(type, msg, size){
		if(type == "오류"){
			alert(msg);
		}else{
			alert(msg);
		}
	},
	notify : function(type, msg, size){
		if(type == "알림"){
			alert(msg);
		}else{
			alert(msg);
		}
	},
	confirm : function(type, msg, size){
		if(confirm(msg)){
			return true;
		}else{
			return false;
		}
	}
};

var dataService = {

        param		: helpers.getParam(),

		getFormData : function ($form){
			var unindexed_array = $form.serializeArray();
			var indexed_array = {};
			$.map(unindexed_array, function(n, i){
				indexed_array[n['name']] = n['value'];
			});
			return indexed_array;
		},

		login : function(key, model, callback){

			var param = dataService.param;
                param._method	= 'GET';
                param._type		= '';
                param._key		= key;
                param._model	= model;

                $.ajax({
                     url		 : helpers.uri+'login.php'
                    ,type		 : 'POST'
                    ,data		 : param
                    ,contentType : 'application/x-www-form-urlencoded'
					,success	 : 
						function(data){ 
							if(!!callback){
								if (!fnIsNull(data)) {
									var res = $.parseJSON(data);
									callback(res,200);
								}else{
									callback('',200);
								}
							}
						}
					,error		:
						function(data,status,error){ 
							if(!!data){
								callback(data,status);
							}
						}
				});
         },

		logout : function(key, callback){

			var param = dataService.param;
				param._method	= 'DELETE';
				param._type		= '';
				param._key		= key;
				param._model	= {};
                
				$.ajax({
                     url		 : helpers.uri+'login.php'
                    ,type		 : 'POST'
                    ,data		 : param
                    ,contentType : 'application/x-www-form-urlencoded'
					,success	 : 
						function(data){ 
							if(!!callback){
								if (!fnIsNull(data)) {
									var res = $.parseJSON(data);
									callback(res,200);
								}else{
									callback('',200);
								}
							}
						}
					,error		:
						function(data,status,error){ 
							if(!!data){
								callback(data,status);
							}
						}
				});

		}, 
			
		getSession : function(callback){

			var param = dataService.param;
				param._method	= 'GET';
				param._type		= '';
				param._key		= '';
				param._model	= {};
               
				$.ajax({
                     url		 : helpers.uri+'login.php'
                    ,type		 : 'POST'
                    ,data		 : param
                    ,contentType : 'application/x-www-form-urlencoded'
					,success	 : 
						function(data){ 
							if(!!callback){
								if (!fnIsNull(data)) {
									var res = $.parseJSON(data);
									callback(res,200);
								}else{
									callback('',200);
								}
							}
						}
					,error		:
						function(data,status,error){ 
							if(!!data){
								callback(data,status);
							}
						}
				});

		}, 
			
		updateStatus : function(uri,type,key,phase,callback){

			var param			= dataService.param;
				param._method	= 'PUT';
				param._type		= type;
				param._key		= key;
				param._phase	= phase;
               
				$.ajax({
                     url		 : helpers.uri+'login.php'
                    ,type		 : 'POST'
                    ,data		 : param
                    ,contentType : 'application/x-www-form-urlencoded'
					,success	 : 
						function(data){ 
							if(!!callback){
								if (!fnIsNull(data)) {
									var res = $.parseJSON(data);
									callback(res,200);
								}else{
									callback('',200);
								}
							}
						}
					,error		:
						function(data,status,error){ 
							if(!!data){
								callback(data,status);
							}
						}
				});

		}, 
			
		db : (function(uri,dbname) {
			var param = {
				 db         : dbname
				,_method    : ''
				,_type      : ''
				,_key       : ''
				,_page      : {}
				,_search    : {}
				,_model     : {}
				,_category  : {}
			};

			return ( function(uri) {
				return {
					find : function(){
						var callback;

						param._method	= 'GET';
						param._type		= arguments[0];
						param._key		= '';
						param._page		= arguments[1];
						param._search	= arguments[2];
						param._model	= {};
						callback		= arguments[3];

						$.ajax({
							 url		 : helpers.uri+uri+'.php'
							,type		 : 'POST'
							,data		 : param
							,contentType : 'application/x-www-form-urlencoded'
							,success	 : 
								function(data,status){ 
									if(!!callback){
										if (!fnIsNull(data)) {
											var res = $.parseJSON(data);
											callback(res,200);
										}else{
											callback('',200);
										}
									}
								}
							,error		:
								function(data,status,error){ 
									if(!!data){
										callback(data,status,error);
									}
								}
						});
						return true;
					},
					findOne : function(){
						var callback ;

						param._method	= 'GET';
						param._type		= arguments[0];
						param._key		= arguments[1];
						param._page		= {};
						param._search	= arguments[2];
						param._model	= {};
						callback		= arguments[3];

						$.ajax({
							 url		 : helpers.uri+uri+'.php'
							,type		 : 'POST'
							,data		 : param
							,contentType : 'application/x-www-form-urlencoded'
							,success	 : 
								function(data){ 
									if(!!callback){
										if (!fnIsNull(data)) {
											var res = $.parseJSON(data);
											callback(res,200);
										}else{
											callback('',200);
										}
									}
								}
							,error		:
								function(data,status,error){ 
									if(!!data){
										callback(data,status,error);
									}
								}
						});

						return true;

					},
					insert : function(){
						param._method	= 'POST';
						param._type		= arguments[0];
						param._key		= "";
						param._page		= {};
						param._search	= {};
						param._model	= arguments[1];
						var callback	= arguments[2];

						$.ajax({
							 url		 : helpers.uri+uri+'.php'
							,type		 : 'POST'
							,data		 : param
							,contentType : 'application/x-www-form-urlencoded'
							,success	 : 
								function(data){ 
									if(!!callback){
										if (!fnIsNull(data)) {
											var res = $.parseJSON(data);
											callback(res,200);
										}else{
											callback('',200);
										}
									}
								}
							,error		:
								function(data,status,error){ 
								//	console.log("_ip/js/service/data-service.js - 355 : data = \r\n");
								//	console.log(data);
								//	console.log("_ip/js/service/data-service.js - 355 : status = "+status+"\r\n");
								//	console.log("_ip/js/service/data-service.js - 355 : error = "+error+"\r\n");
									callback(data,status,error);
								}
						});

						return true;
					},
					update : function(){
						param._method	= 'PUT';
						param._type		= arguments[0];
						param._key		= arguments[1];
						param._page		= {};
						param._search	= {};
						param._model	= arguments[2];
						var callback	= arguments[3];

						$.ajax({
							 url		 : helpers.uri+uri+'.php'
							,type		 : 'POST'
							,data		 : param
							,contentType : 'application/x-www-form-urlencoded'
							,success	 : 
								function(data){ 
									if(!!callback){
										if (!fnIsNull(data)) {
											var res = $.parseJSON(data);
											callback(res,200);
										}else{
											callback('',200);
										}
									}
								}
							,error		:
								function(data,status,error){ 
									callback(data,status,error);
								}
						});

						return true;
					},
					remove : function(){
						param._method	= 'DELETE';
						param._type		= arguments[0];
						param._key		= arguments[1];
						param._page		= {};
						param._search	= {};
						param._model	= {};
						var callback	= arguments[2];

						$.ajax({
							 url		 : helpers.uri+uri+'.php'
							,type		 : 'POST'
							,data		 : param
							,contentType : 'application/x-www-form-urlencoded'
							,success	 : 
								function(data){ 
									if(!!callback){
										if (!fnIsNull(data)) {
											var res = $.parseJSON(data);
											callback(res,200);
										}else{
											callback('',200);
										}
									}
								}
							,error		:
								function(data,status,error){ 
									callback(data,status,error);
								}
						});

						return true;
					}
				}
			})
		})(helpers.serviceUri,'ange')

};