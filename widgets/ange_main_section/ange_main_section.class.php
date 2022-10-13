<?php
/**
 * @class ange_main_section
 * @author singleview.co.kr
 * @brief 메인 페이지 컨텐츠 섹션 추출
 * @version 0.0.1
 **/

class ange_main_section extends WidgetHandler 
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

		// 목록 수
		$list_count = (int)$args->list_count;
		if(!$list_count) $list_count = 6;
		
		$widget_info = new stdClass();
		// 글자 제목 길이
		$widget_info->subject_cut_size = (int)$args->subject_cut_size;
		
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
		$obj = new stdClass();
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
		} 
		elseif($args->module_srls) 
			$obj->module_srl = $args->module_srls;

		$obj->sort_index = $order_target;
		$obj->order_type = $order_type=="desc"?"asc":"desc";
		$obj->list_count = $list_count;
		$obj->statusList = array('PUBLIC', 'SECRET'); // 임시저장글은 출력 제외
		
		if($args->content_type == "document") {
			$obj->is_notice = "N"; // 공지제외 게시물만 출력			
		}
		$output = executeQueryArray('widgets.ange_main_section.getNewestDocuments', $obj);
		if(!$output->toBool() || !$output->data) 
			return;
		// 결과가 있으면 각 문서 객체화를 시킴
		if(count($output->data)) 
		{
			foreach($output->data as $key => $attribute) 
			{
				$document_srl = $attribute->document_srl;
				$oDocument = null;
				$oDocument = new documentItem();
				$oDocument->setAttribute($attribute);                
				// 해당 문서가 비밀글일 경우 권한이 있는지 확인, 없으면 continue
				if ($args->view_secret_document == 'use_permission' && $oDocument->isSecret() && !$oDocument->isGranted()) continue;
				else if ($args->view_secret_document == 'not_show' && $oDocument->isSecret()) continue;       
				$document_list[$key] = $oDocument;
			}
		} 
		else 
		{
			$document_list = array();
		}

		// 템플릿 파일에서 사용할 변수들을 세팅
		if(count($mid_list)==1) $widget_info->module_name = $mid_list[0];  
		$widget_info->document_list = $document_list;
		$widget_info->list_type = $args->list_type;
		unset($args->option_view_arr);
		Context::set('widget_info', $widget_info);

		// 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
		$tpl_path = sprintf('%sskins/%s', $this->widget_path, $args->skin);
		Context::set('colorset', $args->colorset);

		// 템플릿 파일을 지정
		$tpl_file = 'content';

		// 템플릿 컴파일
		$oTemplate = &TemplateHandler::getInstance();
		$output = $oTemplate->compile($tpl_path, $tpl_file);
		return $output;
	}
}
?>