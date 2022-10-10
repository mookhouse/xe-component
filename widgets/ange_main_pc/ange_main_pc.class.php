<?php
/**
 * @class ange_main_pc
 * @author singleview.co.kr
 * @brief 메인 페이지 컨텐츠 섹션 추출
 * @version 0.0.1
 **/

class ange_main_pc extends WidgetHandler 
{
	function proc($args) 
	{
//var_dump($args);
		// 정렬 대상
		$order_target = $args->order_target;
		if(!in_array($order_target, array('list_order','update_order','rand()'))) $order_target = 'list_order';
		// 정렬 순서
		$order_type = $args->order_type;
		if(!in_array($order_type, array('asc','desc'))) $order_type = 'asc';
		 // 보기 옵션
		$args->option_view_arr = explode(',',$args->option_view);            
		// 목록 수
		$list_count = (int)$args->list_count;
		if(!$list_count) $list_count = 6;
		
		// 갤러리 가로 출력 수
		$widget_info->col_count = $args->col_count;
		if(!$widget_info->col_count) $widget_info->col_count = 3; 
		
		// 스크롤 갤러리 세로 출력 수
		$widget_info->scroll_per_column = $args->scroll_per_column;
		if(!$widget_info->scroll_per_column) $widget_info->scroll_per_column = 2; 
		// 스크롤 갤러리 페이지네이션
		$widget_info->scroll_pagination = $args->scroll_pagination;
		if(!$widget_info->scroll_pagination) $widget_info->scroll_pagination = 'fraction'; 
	 
		// 글자 제목 길이
		$widget_info->subject_cut_size = (int)$args->subject_cut_size;
		// 내용 길이
		$widget_info->content_cut_size = (int)$args->content_cut_size ;
		if(!$widget_info->content_cut_size) $widget_info->content_cut_size= 300;
		// 닉네임 길이 자르기
		$widget_info->nickname_cut_size = (int)$args->nickname_cut_size;
		 // 최근 글 표시 시간
		$widget_info->duration_new = (int)$args->duration_new * 60 * 60;
		if(!$widget_info->duration_new) $widget_info->duration_new = 24 * 60 * 60;            
		// 썸네일 생성 방법
		$widget_info->thumbnail_type = $args->thumbnail_type;
		if(!$widget_info->thumbnail_type) $widget_info->thumbnail_type = 'crop';
		// 썸네일 가로 크기
		$widget_info->thumbnail_width = (int)$args->thumbnail_width;
		if(!$widget_info->thumbnail_width) $widget_info->thumbnail_width = 320;
		// 썸네일 세로 크기
		$widget_info->thumbnail_height = (int)$args->thumbnail_height;
		if(!$widget_info->thumbnail_height) $widget_info->thumbnail_height = 320;            
		 // new window
		$widget_info->new_window = $args->new_window;
		// 비밀글 표시 방법
		if (!$args->view_secret_document) $args->view_secret_document = 'all_user';

		// 대상 모듈
		$mid_list = explode(",",$args->mid_list);
		// module_srl 대신 mid가 넘어왔을 경우는 직접 module_srl을 구해줌
		if($mid_list) {
			$oModuleModel = &getModel('module');
			$module_srl = $oModuleModel->getModuleSrlByMid($mid_list);
		}
		// 대상 모듈 (mid_list는 기존 위젯의 호환을 위해서 처리하는 루틴을 유지. module_srl로 위젯에서 변경)
		if($args->mid_list) {
			$mid_list = explode(",",$args->mid_list);
			$oModuleModel = &getModel('module');
			if(count($mid_list)) {
				$module_srl = $oModuleModel->getModuleSrlByMid($mid_list);
			} else {
				$site_module_info = Context::get('site_module_info');
				if($site_module_info) {
					$margs->site_srl = $site_module_info->site_srl;
					$oModuleModel = &getModel('module');
					$output = $oModuleModel->getMidList($margs);
					if(count($output)) $mid_list = array_keys($output);
					$module_srl = $oModuleModel->getModuleSrlByMid($mid_list);
				}
			}
		} elseif($args->module_srls) $obj->module_srl = $args->module_srls;

		$obj->sort_index = $order_target;
		$obj->order_type = $order_type=="desc"?"asc":"desc";
		$obj->list_count = $list_count;
		$obj->statusList = array('PUBLIC', 'SECRET'); // 임시저장글은 출력 제외
		
		if($args->content_type == "document") {
			$obj->is_notice = "N"; // 공지제외 게시물만 출력			
		}
		$output = executeQueryArray('widgets.xet_plus_content.getNewestDocuments', $obj);
		if(!$output->toBool() || !$output->data) return;

		// document 모듈의 model 객체를 받아서 결과를 객체화 시킴
		$oDocumentModel = &getModel('document');

		// 카테고리 리스트 출력
		$obj->module_srl = explode(',',$args->module_srls);
		for($i=0;$obj->module_srl[$i];$i++) {
			$category_lists[] = $oDocumentModel->getCategoryList($obj->module_srl[$i]);
		}
		Context::set('category_list', $category_lists);   
	
		Context::set('mid_list', $mid_list);     

		// 오류가 생기면 그냥 무시
		if(!$output->toBool()) return;

		// 결과가 있으면 각 문서 객체화를 시킴
		if(count($output->data)) {
			foreach($output->data as $key => $attribute) {
				$document_srl = $attribute->document_srl;
				$oDocument = null;
				$oDocument = new documentItem();
				$oDocument->setAttribute($attribute);                
				$oModuleInfo = $oModuleModel->getModuleInfoByModuleSrl($attribute->module_srl);
				$oDocument->mid = $oModuleInfo->mid;
				$oDocument->category = $oDocumentModel->getCategory($attribute->category_srl); //문서에 parent category srl 값 구함              
				
				// 해당 문서가 비밀글일 경우 권한이 있는지 확인, 없으면 continue
				if ($args->view_secret_document == 'use_permission' && $oDocument->isSecret() && !$oDocument->isGranted()) continue;
				else if ($args->view_secret_document == 'not_show' && $oDocument->isSecret()) continue;       
			 
				// 브라우저명 출력
				$oDocument->browser_name = $oModuleInfo->browser_title; 
				// 유투브 확장변수 출력
				if($args->exvar_name) $oDocument->extra_var_name = $oDocument->getExtraEidValue($args->exvar_name); 
				
				$document_list[$key] = $oDocument;
			}
		} else {
			$document_list = array();
		}

		// 템플릿 파일에서 사용할 변수들을 세팅
		if(count($mid_list)==1) $widget_info->module_name = $mid_list[0];  
		$widget_info->list_count = $list_count;
		$widget_info->category_list = $category_list;          
		$widget_info->document_list = $document_list;
		$widget_info->regdate = $args->regdate=='N'?'N':'Y';
		$widget_info->show_thumbnail = $args->show_thumbnail;
		$widget_info->show_category_title = $args->show_category_title;
		$widget_info->show_title = $args->show_title;
		$widget_info->show_content = $args->show_content;
		$widget_info->show_regdate = $args->show_regdate;
		$widget_info->show_nickname = $args->show_nickname;
		$widget_info->show_readed_count = $args->show_readed_count;
		$widget_info->show_comment_count = $args->show_comment_count;
		$widget_info->show_icon = $args->show_icon;
		$widget_info->option_view_arr = $args->option_view_arr;
		$widget_info->list_type = $args->list_type;
		$widget_info->category = $args->category;
		$widget_info->category_range = $args->category_range;
		
		$widget_info->col_webzine = $args->col_webzine;
		
		// gallery
		$widget_info->col_count = $args->col_count;
		//$widget_info->col_margin = $args->col_margin;
		$widget_info->lightbox = $args->lightbox;
		$widget_info->caption_align = $args->caption_align;
		
		// scroll gallery
		$widget_info->scroll_lightbox = $args->scroll_lightbox;
		$widget_info->scroll_play_time = $args->scroll_play_time;
		$widget_info->scroll_col_count = $args->scroll_col_count;
		$widget_info->scroll_caption_align = $args->scroll_caption_align;
		$widget_info->scroll_nav_arrow = $args->scroll_nav_arrow;
		
		// more btn
		$widget_info->more_link = $args->more_link;
		$widget_info->more_text = $args->more_text;

		unset($args->option_view_arr);
		
		Context::set('widget_info', $widget_info);
		

		// 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
		$tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
//echo __FILE__.':'.__LINE__.'<BR>';
//echo $tpl_path;
		Context::set('colorset', $args->colorset);

		// 템플릿 파일을 지정
		$tpl_file = 'content';

		// 템플릿 컴파일
		$oTemplate = &TemplateHandler::getInstance();
		$output = $oTemplate->compile($tpl_path, $tpl_file);
		return $output;
	}

	// 첨부이미지 경로 추출
	function getUploadedFiles() 
	{
		if(!$this->get('uploaded_count')) return;
		$oFileModel = &getModel('file');
		$file_list = $oFileModel->getFiles($this->get('document_srl'), array(), 'file_srl', true);
		return $file_list;
	} 
}
?>