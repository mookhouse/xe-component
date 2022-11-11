<?php
/**
 * @class ange_main_section
 * @author singleview.co.kr
 * @brief 메인 페이지 컨텐츠 섹션 추출
 * @version 0.0.1
 **/

class ange_main_section extends WidgetHandler 
{
	// cookie sets via /modules/page/skins/ange_main/js/ange_delivery_expect_due.js
	private $_g_sDeliveryDueCookieName = 'delivery_expected_due';
	private $_g_sPreparationCookieName = 'pregnant_preparation';	
/**
 * @brief widget init
 */	
	public function proc($oArgs) 
	{
		// 정렬 대상
		$sOrderTarget = $oArgs->order_target;
		if(!in_array($sOrderTarget, ['list_order','update_order','rand()'])) 
			$sOrderTarget = 'list_order';
		// 정렬 순서
		$sOrderType = $oArgs->order_type;
		if(!in_array($sOrderType, ['asc','desc'])) 
			$sOrderType = 'asc';
		// 목록 수
		$nListCount = (int)$oArgs->list_count ? (int)$oArgs->list_count : 6;
		// 큐레이션 쿠키 사용
		$bCurationCookieUse = in_array($oArgs->curation_cookie_use, ['Y','N']) ? $oArgs->curation_cookie_use : 'N';

		$oWidgetInfo = new stdClass();
		// 글자 제목 길이
		$oWidgetInfo->subject_cut_size = (int)$oArgs->subject_cut_size;
		 // 최근 글 표시 시간
		$oWidgetInfo->duration_new = (int)$oArgs->duration_new * 60 * 60;
		if(!$oWidgetInfo->duration_new) 
			$oWidgetInfo->duration_new = 24 * 60 * 60;            
		// 썸네일 생성 방법
		$oWidgetInfo->thumbnail_type = $oArgs->thumbnail_type;
		if(!$oWidgetInfo->thumbnail_type) 
			$oWidgetInfo->thumbnail_type = 'crop';
		// 썸네일 가로 크기
		$oWidgetInfo->thumbnail_width = (int)$oArgs->thumbnail_width;
		if(!$oWidgetInfo->thumbnail_width) 
			$oWidgetInfo->thumbnail_width = 320;
		// 썸네일 세로 크기
		$oWidgetInfo->thumbnail_height = (int)$oArgs->thumbnail_height;
		if(!$oWidgetInfo->thumbnail_height) 
			$oWidgetInfo->thumbnail_height = 320;            

		$oParam = new stdClass();
		// 대상 모듈
		if($oArgs->module_srls) 
			$oParam->module_srl = $oArgs->module_srls;

		$oParam->sort_index = $sOrderTarget;
		$oParam->order_type = $sOrderType=="desc"?"asc":"desc";
		$oParam->list_count = $nListCount;
		$oParam->statusList = ['PUBLIC']; // 임시저장, 비밀글은 출력 제외
		
		if($oArgs->content_type == "document")  // 공지제외 게시물만 출력
			$oParam->is_notice = "N"; 

		if($bCurationCookieUse == 'Y' && (isset($_COOKIE[$this->_g_sDeliveryDueCookieName]) || $_COOKIE[$this->_g_sPreparationCookieName] == 'in_preparation'))
			$oRst = $this->_getTaggedDocs($oParam);
		else
			$oRst = $this->_getGeneralDocs($oParam);
		if(!$oRst->toBool() || !$oRst->data) 
			return;

		// 결과가 있으면 각 문서 객체화를 시킴
		$aDocList = [];
		if(count($oRst->data)) 
		{
			foreach($oRst->data as $nIdx => $oSingleDoc) 
			{
				$document_srl = $oSingleDoc->document_srl;
				$oDocument = null;
				$oDocument = new documentItem();
				$oDocument->setAttribute($oSingleDoc);                
				// 해당 문서가 비밀글일 경우 권한이 있는지 확인, 없으면 continue
				//if ($oArgs->view_secret_document == 'use_permission' && $oDocument->isSecret() && !$oDocument->isGranted()) continue;
				//else if ($oArgs->view_secret_document == 'not_show' && $oDocument->isSecret()) continue;
				$aDocList[$nIdx] = $oDocument;
			}
		} 

		// 템플릿 파일에서 사용할 변수들을 세팅
		$oWidgetInfo->document_list = $aDocList;
		$oWidgetInfo->list_type = $oArgs->list_type;
		Context::set('widget_info', $oWidgetInfo);

		// 템플릿의 스킨 경로를 지정 (skin, colorset에 따른 값을 설정)
		$sTplPath = sprintf('%sskins/%s', $this->widget_path, $oArgs->skin);

		// 템플릿 파일을 지정
		$sTplFile = 'content';

		// 템플릿 컴파일
		$oTemplate = &TemplateHandler::getInstance();
		$oRst = $oTemplate->compile($sTplPath, $sTplFile);
		return $oRst;
	}
/**
 * @brief 태그 검색
 */	
	private function _getTaggedDocs($oArgs)
	{
		if($_COOKIE[$this->_g_sDeliveryDueCookieName])
		{
			// calculate martinity week
			$nFullMonth = 10;
			$dtToday = date_create(date('Ymd'));	
			$dtDeliveryDue = date_create($_COOKIE[$this->_g_sDeliveryDueCookieName]);
			$dtDays = date_diff($dtToday, $dtDeliveryDue);
			$nMonthsCnt = (int)($dtDays->days / 28);
			if($nMonthsCnt > 10)
				$oArgs->s_tags = '임신준비';
			else
			{
				$nElapasedMonth = $nFullMonth - $nMonthsCnt;
				$oArgs->s_tags = '임신'.$nElapasedMonth.'개월';
			}
		}
		elseif($_COOKIE[$this->_g_sPreparationCookieName])
			$oArgs->s_tags = '임신준비';

		$oTagRst = executeQueryArray('document.getDocumentListWithinTag', $oArgs);
		unset($oArgs);
		if(!$oTagRst->toBool()||!count($oTagRst->data)) 
			return $oTagRst;
		$aTargetSrls = [];
		foreach($oTagRst->data as $nIdx => $oRec)
		{
			if($oRec->document_srl) 
				$aTargetSrls[] = $oRec->document_srl;
		}
		$oTgtArgs = new stdClass();
		$oTgtArgs->document_srls = implode(',', $aTargetSrls);
		$oArgs->sort_index = 'documents.'.$oArgs->sort_index;
		$oTgtArgs->order_type = $oArgs->order_type;
		$oTgtArgs->list_count = $oArgs->nListCount;
		$oTgtArgs->page = 1;
		return executeQueryArray('document.getDocuments', $oTgtArgs);
	}
/**
 * @brief 일반 검색
 */	
	private function _getGeneralDocs($oArgs)
	{
		return executeQueryArray('widgets.ange_main_section.getNewestDocuments', $oArgs);
	}
}
?>