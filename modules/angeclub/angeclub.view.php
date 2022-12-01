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
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$oAngeclubModel = &getModel('angeclub');
		$aEffectiveUserList = $oAngeclubModel->getClubEffectiveUser();
		// var_dump($aEffectiveUserList);
		unset($oAngeclubModel);
		$oLoggedInfo->user_id = 'sugarprime';
		if(!$aEffectiveUserList[$oLoggedInfo->user_id])
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
 * @brief 기본 화면
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
 * @brief 조리원 센터 메인 화면
 */
	public function dispAngeclubCenter()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		
		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser());
		Context::set('aCenterState', $this->_g_aCenterState);
		
		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oRst);

		$oRst = $oAngeclubModel->getClubListPagination();
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

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');
	
		$oAngeclubModel = &getModel('angeclub');
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser());
		Context::set('aCenterState', $this->_g_aCenterState);
		
		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oAngeclubModel);

		$this->setTemplateFile('popup_add_center');
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

		// 팝업을 위해 layout 제거
		$this->module_info->layout_srl = 0;

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');

		$oAngeclubModel = &getModel('angeclub');
		$oRst = $oAngeclubModel->getCenterInfoByIdx($nCcIdx);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('oCenterInfo', $oRst->data);
		Context::set('aUserInfo', $oAngeclubModel->getClubEffectiveUser());
		Context::set('aCenterState', $this->_g_aCenterState);
		
		$oRst = $oAngeclubModel->getCenterAreaJsonStringfied();
		Context::set('aCity', $oRst->get('aCity'));
		Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));
		unset($oAngeclubModel);

		$this->setTemplateFile('popup_add_center');
	}
}
/* End of file angeclub.view.php */
/* Location: ./modules/angeclub/angeclub.view.php */