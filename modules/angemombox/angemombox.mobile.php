<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxMobile
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxMobile class
 */
require_once(_XE_PATH_.'modules/angemombox/angemombox.view.php');
class angemomboxMobile extends angemomboxView
{
	function init()
	{
		$template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
		if(!is_dir($template_path)||!$this->module_info->mskin) 
		{
			$this->module_info->mskin = 'default';
			$template_path = sprintf("%sm.skins/%s/",$this->module_path, $this->module_info->mskin);
		}
		$this->setTemplatePath($template_path);
	}

	/**
	 * @brief General request output
	 */
	function dispAngemomboxIndex()
	{
		$oAngemomboxModel = &getModel('angemombox');
		$oMemberConnectionRst = $oAngemomboxModel->getMemberFieldConnection();
		if(!$oMemberConnectionRst->toBool())
			return $oMemberConnectionRst;
		$sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		$sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		unset($oMemberConnectionRst);

		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');
		
		$oLoggedInfo->address = $oLoggedInfo->$sMemberAddrFieldName;
		Context::set('oLoggedInfo', $oLoggedInfo);

		$bAllowSubmit = true;
		$aDenyMsg = [];
		if(!$oLoggedInfo->mobile)  // 회원 정보에 핸드폰 번호가 없으면 등록 거부
		{
			$aDenyMsg[] = '핸드폰 정보를 입력하세요.';
			$bAllowSubmit = false;
		}
		if(!$oLoggedInfo->address[0])  // 회원 정보에 배송 주소 없으면 등록 거부
		{
			$aDenyMsg[] = '배송 주소를 입력하세요.';
			$bAllowSubmit = false;
		}
		if($oLoggedInfo->$sMemberSmspushFieldName != 'Y')  // 회원 정보에 SMS 수신 동의 없으면 등록 거부
		{
			$aDenyMsg[] = 'SMS 수신 동의하세요.';
			$bAllowSubmit = false;
		}
		$aBabyList = $oAngemomboxModel->getBabyList($oLoggedInfo->member_srl);
		if(!count($aBabyList))  // 회원 정보에 아기 정보 없으면 등록 거부
		{
			$aDenyMsg[] = '아기 정보를 입력하세요.';
			$bAllowSubmit = false;
		}
		Context::set('bAllowSubmit', $bAllowSubmit);
		Context::set('sDenyMsg', implode('<BR>', $aDenyMsg));
		unset($aDenyMsg);

		$oOpenRst = $oAngemomboxModel->checkOpenDay($this->module_srl);
		if(!$oOpenRst->toBool())
			return $oOpenRst;
		unset($oOpenRst);
			
		$nYrMo = date('Ym');
		$oWinnerRst = $oAngemomboxModel->checkDuplicatedApply($this->module_srl, $oLoggedInfo->member_srl, $nYrMo);
		if(!$oWinnerRst->toBool())
			return $oWinnerRst;

		//Context::set('privacy_usage_term', $oAngemomboxModel->getPrivacyTerm($this->module_srl, 'privacy_usage_term'));
		//Context::set('privacy_shr_term', $oAngemomboxModel->getPrivacyTerm($this->module_srl, 'privacy_shr_term'));
		unset($oAngemomboxModel);

		if($this->module_srl) 
			Context::set('module_srl', $this->module_srl);

		Context::addJsFilter($this->module_path.'tpl/filter', 'insert.xml');

		$nFileSrl = getNextSequence();
		Context::set('nFileSrl', $nFileSrl);
		// gallery image editor
		$oEditorModel = &getModel('editor');
		$oOption = new stdClass();
		//$oOption->disable_html = true;
		//$oOption->enable_default_component = true;
		//$oOption->enable_component = true;
		//$oOption->module_type = 'document';
		//$oOption->skin = 'ckeditor';
		//$oOption->content_style = 'ckeditor_light';
		$oOption->content_key_name = 'null';
		$oOption->height = 1;
		$oOption->allow_fileupload = true;
		$oOption->primary_key_name = 'document_srl';
		//Context::set('gallery_editor', $oEditorModel->getEditor($nFileSrl , $oOption)); // 갤러리 첨부파일 로드하기 위한 hidden 객체
		$oEditorModel->getEditor($nFileSrl , $oOption);
		unset($oOption);
		unset($oEditorModel);
        
        $oAngeclubModel = &getModel('angeclub');
        $aBabyGenderList = $oAngeclubModel->getBabyGender();
        Context::set('aBabyGender', $aBabyGenderList);

		$this->setTemplateFile('add');
	}
}
/* End of file Angemombox.mobile.php */
/* Location: ./modules/Angemombox/Angemombox.mobile.php */