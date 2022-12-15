<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubView
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubView
**/ 
class angeclubView extends angeclub
{
/**
 * @brief Initialization
 */
	public function init()
	{
        if(is_null(getClass('angemombox')))  // check module dependency
            return $this->stop("msg_error_angemombox_module_required");

		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$oModuleModel = getModel('module');
		$oGrant = $oModuleModel->getGrant($oModuleModel->getModuleInfoByModuleSrl($this->module_info->module_srl), $oLoggedInfo);
		unset($oModuleModel);
		if(!$oGrant->access)
			return new BaseObject(-1, 'msg_invalid_approach');

		$oAngeclubModel = &getModel('angeclub');
		$aEffectiveUserList = $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl);
		unset($oAngeclubModel);
		if($oLoggedInfo->is_admin != 'Y' && !$aEffectiveUserList[$oLoggedInfo->user_id])
		{
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: /');
			exit;
		}

		$template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
		if(!is_dir($template_path)||!$this->module_info->skin)
		{
			$this->module_info->skin = 'default';
			$template_path = sprintf("%sskins/%s/",$this->module_path, $this->module_info->skin);
		}
		$this->setTemplatePath($template_path);
	}
/**
 * @brief 담당자별 지역별 성과 대쉬보드 화면
 */
	public function dispAngeclubIndex()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		Context::set('oLoggedInfo', $oLoggedInfo);
		$this->setTemplateFile('default');
	}
/**
 * @brief 간호사 업무일지 일반 스탭 화면
 */
	public function dispAngeclubWorkDiary()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
	
		// $oInParams = Context::getRequestVars();
		// var_dump($oInParams);
		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));

		// $oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		// Context::set('aCity', $oRst->get('aCity'));
		// Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		$oRst = $oAngeclubModel->getCenterListByStaffIdJsonStringfiedForWorkDiary();
		Context::set('aArea', $oRst->get('aArea'));
		Context::set('aJsonStringfyCenterByStaff', $oRst->get('aJsonStringfyCenterByStaff'));
		unset($oRst);

		$oRst = $oAngeclubModel->getWorkDiaryListPagination(TRUE);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('workdiary_list', $oRst->data );
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		unset($oRst);
		unset($oAngeclubModel);

		$this->setTemplateFile('workdiary_staff');
	}
/**
 * @brief 간호사 업무일지 총괄 관리자 화면
 */
	public function dispAngeclubWorkDiaryManager()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		
		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));

		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oRst);

		$oRst = $oAngeclubModel->getWorkDiaryListPagination();
		if(!$oRst->toBool())
			return $oRst;
		Context::set('workdiary_list', $oRst->data );
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		unset($oRst);
		unset($oAngeclubModel);

		$this->setTemplateFile('workdiary_manager');
	}	
/**
* @brief 간호사 업무 일지 추가 팝업
*/
	public function dispAngeclubWorkDiaryPopupAdd()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));

		$this->module_info->layout_srl = 0;  // 팝업을 위해 layout 제거

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_workdiary.xml');
		Context::set('oLoggedInfo', $oLoggedInfo);

		$oAngeclubModel = &getModel('angeclub');
		$oRst = $oAngeclubModel->getCenterListByStaffIdJsonStringfiedForWorkDiary();
		Context::set('aArea', $oRst->get('aArea'));
		Context::set('aJsonStringfyCenterByStaff', $oRst->get('aJsonStringfyCenterByStaff'));
		unset($oRst);
		unset($oAngeclubModel);

		$this->setTemplateFile('workdiary_popup_add');
	}
/**
 * @brief 간호사 업무 일지 변경 팝업
 */
	public function dispAngeclubWorkDiaryPopupUpdate()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$nClIdx = Context::get('cl_idx');
		if(!$nClIdx)
			return new BaseObject(-1, 'msg_invalid_approach');

		$this->module_info->layout_srl = 0;  // 팝업을 위해 layout 제거

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_workdiary.xml');
		Context::set('oLoggedInfo', $oLoggedInfo);

		$oAngeclubModel = &getModel('angeclub');
		// Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));
		$oRst = $oAngeclubModel->getCenterListByStaffIdJsonStringfiedForWorkDiary();
		Context::set('aArea', $oRst->get('aArea'));
		Context::set('aJsonStringfyCenterByStaff', $oRst->get('aJsonStringfyCenterByStaff'));
		unset($oRst);
		
		$oRst = $oAngeclubModel->getWorkDiaryByIdx($nClIdx);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('oWorkDiaryInfo', $oRst->data);
		unset($oRst);
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));
		Context::set('aSessionType', $this->_g_aSessionType);
		unset($oAngeclubModel);

		$this->setTemplateFile('workdiary_popup_add');
	}
/**
 * @brief 회원 검색 및 추가 화면
 */
	public function dispAngeclubMember()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		Context::set('oLoggedInfo', $oLoggedInfo);
		$oAngeclubModel = &getModel('angeclub');
		$oRst = $oAngeclubModel->getMomList(Context::getRequestVars());
		unset($oAngeclubModel);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('aMomList', $oRst->data);
		Context::set('page_navigation', $oRst->page_navigation);
		unset($oRst);
		$this->setTemplateFile('mom_manager');
	}
/**
* @brief 산모 수기 추가 팝업
*/
	public function dispAngeclubMemberPopupAdd()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$this->module_info->layout_srl = 0;  // 팝업을 위해 layout 제거

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_mom.xml');
		Context::set('oLoggedInfo', $oLoggedInfo);

		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));
		Context::set('aBabyGender', $this->_g_aBabyGender);
		
		$oRst = $oAngeclubModel->getCenterListByStaffIdJsonStringfiedForMemberAdd();
		Context::set('aArea', $oRst->get('aArea'));
		Context::set('aJsonStringfyCenterByStaff', $oRst->get('aJsonStringfyCenterByStaff'));
		unset($oRst);
		unset($oAngeclubModel);

		// 핸폰 번호로 자동 생성되는 암호의 전치사 설정 가져오기
		$oAngeclubModel = &getModel('angeclub');
		$oConfig = $oAngeclubModel->getModuleConfig();
		Context::set('sPasswordPrefix', $oConfig->password_prefix);
		unset($oAngeclubModel);

		$this->setTemplateFile('mom_popup_add');
	}
/**
 * @brief 조리원 센터 메인 화면
 */
	public function dispAngeclubCenter()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		
		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));
		Context::set('aCenterState', $this->_g_aCenterState);
		
		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oRst);

		$oRst = $oAngeclubModel->getCenterListPagination();
		if(!$oRst->toBool())
			return $oRst;
		Context::set('angeclub_list', $oRst->data );
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		unset($oRst);
		unset($oAngeclubModel);
	
		$this->setTemplateFile('center_manager');
	}
/**
 * @brief 조리원 센터 추가 팝업
 */
	public function dispAngeclubCenterPopupAdd()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		// 팝업을 위해 layout 제거
		$this->module_info->layout_srl = 0;
		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_center.xml');
	
		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));
		Context::set('aCenterState', $this->_g_aCenterState);

		$oCenterInfo = new stdClass();
		$oCenterInfo->cc_city = '서울';  // for UX
		$oCenterInfo->cc_state = 1;  // for UX
		Context::set('oCenterInfo', $oCenterInfo);
		
		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oAngeclubModel);

		$this->setTemplateFile('center_popup_add');
	}
/**
 * @brief 조리원 센터 변경 팝업
 */
	public function dispAngeclubCenterPopupUpdate()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		$nCcIdx = Context::get('cc_idx');
		if(!$nCcIdx)
			return new BaseObject(-1, 'msg_invalid_center_idx');

		$this->module_info->layout_srl = 0;  // 팝업을 위해 layout 제거
		Context::addJsFilter($this->module_path.'tpl/filter', 'insert_center.xml');

		$oAngeclubModel = &getModel('angeclub');
		$oRst = $oAngeclubModel->getCenterInfoByIdx($nCcIdx);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('oCenterInfo', $oRst->data);
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser($this->module_info->module_srl));
		Context::set('aCenterState', $this->_g_aCenterState);
		
		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oAngeclubModel);

		$this->setTemplateFile('center_popup_add');
	}
/**
 * @brief 클럽 스탭 명단 화면
 */
	public function dispAngeclubStaffManager()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		
		$oAngeclubModel = &getModel('angeclub');
		$oRst = $oAngeclubModel->getClubEffectiveUserFullInfo($this->module_info->module_srl);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('staff_list', $oRst->data );
		// Context::set('total_count', $oRst->total_count);
		// Context::set('total_page', $oRst->total_page);
		// Context::set('page', $oRst->page);
		// Context::set('page_navigation', $oRst->page_navigation);
		unset($oRst);
		unset($oAngeclubModel);

		$this->setTemplateFile('staff_manager');
	}
}
/* End of file angeclub.view.php */
/* Location: ./modules/angeclub/angeclub.view.php */