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
 * @brief 모듈 default setting 불러오기
 */
	function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		return $oModuleModel->getModuleConfig('angemombox');
	}
/**
 * @brief member 사용자 정의 필드 연동 설정
 */
	function getMemberFieldConnection()
	{
		$oConfig = $this->getModuleConfig();
		$sMemberAddrFieldName = $oConfig->member_addr_field_name;
		if(strlen($sMemberAddrFieldName) == 0) // 스킨의 주소 입력 UX 처리
			return new BaseObject(-1, 'invalid_config_member_addr_field_name');

		$sMemberGenderFieldName = $oConfig->member_gender_field_name;
		if(strlen($sMemberGenderFieldName) == 0) // 스킨의 주소 입력 UX 처리
			return new BaseObject(-1, 'invalid_config_member_gender_field_name');
		
		$sMemberSmspushFieldName = $oConfig->member_sms_push_field_name;
		if(strlen($sMemberSmspushFieldName) == 0) // 스킨의 주소 입력 UX 처리
			return new BaseObject(-1, 'invalid_config_member_sms_push_field_name');

		$sMemberEmailpushFieldName = $oConfig->member_email_push_field_name;
		if(strlen($sMemberEmailpushFieldName) == 0) // 스킨의 주소 입력 UX 처리
			return new BaseObject(-1, 'invalid_config_member_email_push_field_name');
		
		$sMemberPostpushFieldName = $oConfig->member_post_push_field_name;
		if(strlen($sMemberPostpushFieldName) == 0) // 스킨의 주소 입력 UX 처리
			return new BaseObject(-1, 'invalid_config_member_post_push_field_name');

		$sMemberSponsorpushFieldName = $oConfig->member_sponsor_push_field_name;
		if(strlen($sMemberSponsorpushFieldName) == 0) // 스킨의 주소 입력 UX 처리
			return new BaseObject(-1, 'invalid_config_member_sponsor_push_field_name');

		$oRst = new BaseObject();
		$oRst->add('sMemberAddrFieldName', $sMemberAddrFieldName);
		$oRst->add('sMemberGenderFieldName', $sMemberGenderFieldName);
		$oRst->add('sMemberSmspushFieldName', $sMemberSmspushFieldName);
		$oRst->add('sMemberEmailpushFieldName', $sMemberEmailpushFieldName);
		$oRst->add('sMemberPostpushFieldName', $sMemberPostpushFieldName);
		$oRst->add('sMemberSponsorpushFieldName', $sMemberSponsorpushFieldName);
		return $oRst;
	}
/**
 * @brief UX for index.php?mid=index&act=dispMemberInfo
 */
    public function getBabyList($nMemberSrl)
    {
        $oArgs = new stdClass();
		$oArgs->member_srl = $nMemberSrl;
		$oRst = executeQueryArray('angemombox.getBabyList', $oArgs);
		unset($oArgs);
        if(count($oRst->data))
        {
            $oAngeclubModel = getModel('angeclub');
            $aBabyGender = $oAngeclubModel->getBabyGender();
            unset($oAngeclubModel);
            foreach($oRst->data as $nIdx => $oBaby)
            {
                $oBaby->gender_ui = $aBabyGender[$oBaby->gender];
                $oBaby->birthday_yr = substr($oBaby->birthday, 0,4);
                $oBaby->birthday_mo = substr($oBaby->birthday, 4,2);
                $oBaby->birthday_day = substr($oBaby->birthday, 6,2);
            }
        }
        return $oRst->data;
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