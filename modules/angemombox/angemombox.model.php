<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxModel
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxModel
**/ 
class angemomboxModel extends module
{
/**
 * @brief initialization
 **/
	function init()
	{
	}
/**
 * @brief return module name in sitemap
 **/
    function triggerModuleListInSitemap(&$obj)
    {
        array_push($obj, 'angemombox');
    }
/**
 * @brief 모듈 default setting 불러오기
 */
	function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		return $oModuleModel->getModuleConfig('angemombox');
	}
/**
 * @brief 
 */
	public function checkOpenDay($nModuleSrl)
	{
		$oDocInfo = $this->getDocInfo($nModuleSrl);
		if(date('d') > $oDocInfo->monthly_due_day)  // 매월 xx일 후는 접수 중단
			return new BaseObject(1, sprintf(Context::getLang('msg_not_opened_yet'), date('Y-m', strtotime('+1 month')), date('Y-m'), date('Y-m').'-'.$oDocInfo->monthly_due_day)); 
		return new BaseObject();
	}
/**
 * @brief 
 */
	public function checkDuplicatedApply($nModuleSrl, $nMemberSrl, $nYrMo)
	{
		return new BaseObject();

		$oArgs = new stdClass();
		$oArgs->parent_member_srl = $nMemberSrl;
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->yr_mo = $nYrMo;

		$oWinnerRst = executeQueryArray('angemombox.getWinnerByMemberSrl', $oArgs);
		$nWinnedCnt = count($oWinnerRst->data);
		unset($oWinnerRst);
		if($nWinnedCnt)
			return new BaseObject(-1, 'msg_error_already_winned');
		
		$oAppliedRst = executeQueryArray('angemombox.getApplyByMemberSrl', $oArgs);
		$nAppliedCnt = count($oAppliedRst->data);
		unset($oAppliedRst);
		if($nAppliedCnt)
			return new BaseObject(-1, 'msg_error_already_applied');
		unset($oArgs);
		unset($oRst);
		return new BaseObject();
	}
/**
 * @brief 
 */
	public function getDocInfo($nModuleSrl)
	{
		//$nCnt = $this->getDocsCount($nModuleSrl);
		$oModuleModel = &getModel('module');
		$oModuleInfo = $oModuleModel->getModuleInfoByModuleSrl($nModuleSrl);
		$oDocInfo = new stdClass();
		$oDocInfo->mid = $oModuleInfo->mid;
		$oDocInfo->monthly_due_day = $oModuleInfo->monthly_due_day ? (int)$oModuleInfo->monthly_due_day : 25;  // 매월 25일까지 접수가 기본
		
		$aParam = ['angeclub_exclusive',  // 앙쥬클럽에서 산모 정보 수집 전용 모듈
					'member_addr_field_name', 'member_give_birth_date_field_name', 'member_sns_field_name',  // 회원 연동 정보
					'connected_board_srl', 'title_prefix', 'title_cut_size', 'mombox_module_srl_field', 'mombox_application_srl_field'  // 게시판 연동 정보
				  ];
		foreach($oModuleInfo as $sTitle=>$sVal)
		{
			if(in_array($sTitle, $aParam))
				$oDocInfo->{$sTitle} = $oModuleInfo->{$sTitle};
		}
		/*$oDocInfo->member_addr_field_name = $oModuleInfo->member_addr_field_name;
		$oDocInfo->member_give_birth_date_field_name = $oModuleInfo->member_give_birth_date_field_name;
		$oDocInfo->member_sns_field_name = $oModuleInfo->member_sns_field_name;

		$oDocInfo->connected_board_srl = $oModuleInfo->connected_board_srl;
		$oDocInfo->title_prefix = $oModuleInfo->title_prefix;
		$oDocInfo->title_cut_size = $oModuleInfo->title_cut_size;*/

		unset($oModuleInfo);
		unset($oModuleModel);
		return $oDocInfo;
	}
/**
 * @brief must be alinged with angemombox.admin.model.php::getPrivacyTerm()
 */
	function getPrivacyTerm($nModuleSrl, $sTermType)
	{
		if( !(int)$nModuleSrl )
			return 'invalid_module_srl';

		switch($sTermType)
		{
			case 'privacy_usage_term':
			case 'privacy_shr_term':
				break;
			default:
				return null;
		}
		$agreement_file = _XE_PATH_.'files/Angemombox/'.$nModuleSrl.'_'.$sTermType.'_' . Context::get('lang_type') . '.txt';
		if(is_readable($agreement_file))
			return nl2br(FileHandler::readFile($agreement_file));

		$db_info = Context::getDBInfo();
		$agreement_file = _XE_PATH_.'files/angemombox/'.$nModuleSrl.'_'.$sTermType.'_' . $db_info->lang_type . '.txt';
		if(is_readable($agreement_file))
			return nl2br(FileHandler::readFile($agreement_file));

		$lang_selected = Context::loadLangSelected();
		foreach($lang_selected as $key => $val)
		{
			$agreement_file = _XE_PATH_.'files/angemombox/'.$nModuleSrl.'_'.$sTermType.'_' . $key . '.txt';
			if(is_readable($agreement_file))
				return nl2br(FileHandler::readFile($agreement_file));
		}
		// member module의 약관 가져오기
		$oMemberAdminModel = &getAdminModel('member');
		if(method_exists($oMemberAdminModel, 'getPrivacyTerm'))  // means core is later than v1.13.2
		{
			$sMemberTermType = str_replace('_term', '', $sTermType);
			return $oMemberAdminModel->getPrivacyTerm($sMemberTermType);
		}
		unset($oMemberAdminModel);
		
		// 최종 실패할 경우 기본 약관 출력
		$agreement_file = _XE_PATH_.'modules/angemombox/tpl/'.$sTermType.'_template.txt';
		if(is_readable($agreement_file))
			return nl2br(FileHandler::readFile($agreement_file));

		return null;
	}
/**
 * @brief 
 */
	public function getAngemomboxUpdatePermission()
	{
		$nModuleSrl = (int)Context::get('module_srl');
		$oDocInfo = $this->getDocInfo( $nModuleSrl );
		
		if( $oDocInfo->is_allow_update == 'Y' )
		{
			$this->add('is_update_allowed', 1 );
			$this->add('target_mid', $oDocInfo->mid );
		}
		else
			$this->add('is_update_allowed', 0 );
		return new BaseObject();
	}
/**
 * @brief 
 */
	public function getDocsCount($nModuleSrl)
	{
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oRst = executeQuery('angemombox.getDocsCount', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');
		else
			return $oRst->total_count;
	}
/**
 * @brief 
 */
	public function getDocList( $nModuleSrl )
	{
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oRst = executeQueryArray('angemombox.getApplicantList', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');
		else
			return $oRst;
	}
}