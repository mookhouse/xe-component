<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxController
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxController
**/ 
class angemomboxController extends angemombox
{
/**
 * @brief initialization
 **/
	public function init()
	{
	}
/**
 * @brief 회원 추가 후 SMS 발송 대상 추출을 위한 extra tbl에 저장
 **/
	public function triggerInsertMemberAfter(&$oMemberObj) 
	{
		$oArgs = $this->_constructMemberExtraInfo($oMemberObj);
		$oRst = executeQuery('angemombox.insertMemberExtra', $oArgs);
		unset($oArgs);
		return $oRst;
	}
/**
 * @brief 회원 수정 후 SMS 발송 대상 추출을 위한 extra tbl에 저장
 **/
	public function triggerUpdateMemberAfter(&$oMemberObj) 
	{
		$oArgs = $this->_constructMemberExtraInfo($oMemberObj);
		$oRst = executeQuery('angemombox.updateMemberExtra', $oArgs);
		unset($oArgs);
		return $oRst;
	}
/**
 *
 */
	private function _constructMemberExtraInfo($oMemberObj)
	{
		$oAngemomboxModel = &getModel('angemombox');
		$oMemberConnectionRst = $oAngemomboxModel->getMemberFieldConnection();
		unset($oAngemomboxModel);
		if(!$oMemberConnectionRst->toBool())
			return $oMemberConnectionRst;
		$sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		$sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		$sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		$sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		$sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		$sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		unset($oMemberConnectionRst);

		$oArgs = new stdClass();
		$oArgs->member_srl = $oMemberObj->member_srl;
		$oArgs->user_name = $oMemberObj->user_name;
		$oArgs->mobile = $oMemberObj->mobile;
		
		$oMemberExtra = unserialize($oMemberObj->extra_vars);
		$oArgs->postcode = $oMemberExtra->$sMemberAddrFieldName[0];
		$oArgs->addr = $oMemberExtra->$sMemberAddrFieldName[1];
		$oArgs->addr_detail = $oMemberExtra->$sMemberAddrFieldName[2];
		$oArgs->addr_extra = $oMemberExtra->$sMemberAddrFieldName[3];
		$oArgs->sms_push = $oMemberExtra->sms_push;
		$oArgs->email_push = $oMemberExtra->email_push;
		$oArgs->post_push = $oMemberExtra->post_push;
		$oArgs->sponsor_push = $oMemberExtra->sponsor_push;
		if($this->_g_aGender[$oMemberExtra->$sMemberGenderFieldName])
			$oArgs->gender = $this->_g_aGender[$oMemberExtra->$sMemberGenderFieldName];
		unset($oMemberExtra);
		return $oArgs;
	}
/**
 * @brief 신규 응모 추가 메소드
 **/
	public function procAngemomboxRegistration()
	{
		$oAngemomboxModel = &getModel('angemombox');
		
		$oMemberConnectionRst = $oAngemomboxModel->getMemberFieldConnection();
		if(!$oMemberConnectionRst->toBool())
			return $oMemberConnectionRst;
		$sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		$sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		$sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		$sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		$sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		$sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		unset($oMemberConnectionRst);
// var_dump($sMemberSmspushFieldName);
// 		exit;
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, '로그인 하세요.');

		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl)
			return new BaseObject(-1, '잘못된 접근입니다.');
					
		$oAngemomboxModel = &getModel('angemombox');
		$oOpenRst = $oAngemomboxModel->checkOpenDay($nModuleSrl);
		if(!$oOpenRst->toBool())
			return $oOpenRst;
		unset($oOpenRst);

		$nYrMo = date('Ym');
		$oWinnerRst = $oAngemomboxModel->checkDuplicatedApply($this->module_srl, $oLoggedInfo->member_srl, $nYrMo);
		if(!$oWinnerRst->toBool())
			return $oWinnerRst;
		unset($oWinnerRst);
		// begin - retrieve configuration for related modules
		$oDocInfo = $oAngemomboxModel->getDocInfo($nModuleSrl);
		unset($oAngemomboxModel);

		// $sMemberAddrFieldName = $oDocInfo->member_addr_field_name;
		// $sMemberGiveBirthDateFieldName = $oDocInfo->member_give_birth_date_field_name;
		// $sMemberSnsFieldName = $oDocInfo->member_sns_field_name;

		$nConnectedBoardModuleSrl = $oDocInfo->connected_board_srl;
		$sTitlePrefix = $oDocInfo->title_prefix;
		$nTitleCutSize = $oDocInfo->title_cut_size;
		$nConnectedBoardModuleSrl = $oDocInfo->connected_board_srl;
		$sMomboxModuleSrlFieldTtl = $oDocInfo->mombox_module_srl_field;
		$sMomboxApplicationSrlFieldTtl = $oDocInfo->mombox_application_srl_field;
		unset($oDocInfo);
		// end - retrieve configuration for related modules

		// Get user_id information
		$oMemberModel = &getModel('member');
		$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oLoggedInfo->member_srl);
// var_dump($oMemberInfo); 		
		unset($oMemberModel);

		if(strlen($oMemberInfo->mobile) == 0)
			return new BaseObject(-1, '회원 정보에서 핸드폰 번호를 입력해 주세요.');
		if($oMemberInfo->$sMemberSmspushFieldName != 'Y')
			return new BaseObject(-1, '회원 정보에서 SMS 수신 동의해 주세요.');

		// begin - check mandatory field - addr
		if(strlen($oMemberInfo->$sMemberAddrFieldName[0]))  // reuse member info if registered
		{
			$sStrippedPostcode = $oMemberInfo->$sMemberAddrFieldName[0];
			$sStrippedAddr = $oMemberInfo->$sMemberAddrFieldName[1];
			$sStrippedAddrDetail = $oMemberInfo->$sMemberAddrFieldName[2];
			$sStrippedAddrExtra = $oMemberInfo->$sMemberAddrFieldName[3];
		}
		else
			return new BaseObject(-1, '회원 정보에 배송 주소를 입력하세요.');
        // end - check mandatory field - addr

        // replace %mom_name% to member's name
        $sTitlePrefix = str_replace('%mom_name%' , $oMemberInfo->user_name, $sTitlePrefix);

		// never touch unrelated member info
		// unset($oMemberInfo->password);
		// unset($oMemberInfo->find_account_question);
		// unset($oMemberInfo->find_account_answer);
		// unset($oMemberInfo->user_name);
		// unset($oMemberInfo->user_id);
		// unset($oMemberInfo->nick_name);
		// unset($oMemberInfo->description);
		// unset($oMemberInfo->birthday);
		// unset($oMemberInfo->group_srl_list);
		// never touch unrelated member info
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		// $sAddrTitle = $sMemberAddrFieldName; // 'address';
		// $oMemberInfo->$sAddrTitle[0] = $sStrippedPostcode;
		// $oMemberInfo->$sAddrTitle[1] = $sStrippedAddr;
		// $oMemberInfo->$sAddrTitle[2] = $sStrippedAddrDetail;
		// $oMemberInfo->$sAddrTitle[3] = $sStrippedAddrExtra;
		// $this->_modifyMemberInfo($oMemberInfo);
        // end - update addr, mom's birthday into member tbl
		$sParentMobileNo = $oMemberInfo->mobile;

		$oArgs = new stdClass();
		$oArgs->module_srl = Context::get('module_srl');
		$oArgs->yr_mo = $nYrMo;
		$oArgs->member_srl = $oLoggedInfo->member_srl;  // mom's member srl
		// $oArgs->parent_birthday = $sParentBirthday;
		$oArgs->mobile = $sParentMobileNo;

		if($this->_g_aGender[$oMemberInfo->$sMemberGenderFieldName])
			$oArgs->parent_gender = $this->_g_aGender[$oMemberInfo->$sMemberGenderFieldName];
		$oArgs->parent_pregnant = Context::get('parent_pregnant');

        // construct mom's info
        $oArgs->postcode = $sStrippedPostcode;
		$oArgs->addr = $sStrippedAddr;
		$oArgs->addr_detail = $sStrippedAddrDetail;
		$oArgs->addr_extra = $sStrippedAddrExtra;
        // $oArgs->sns_id = strip_tags($oInParams->sns);  // mom's sns id

        // construct push agreement
        $oArgs->sms_push = $oMemberInfo->$sMemberSmspushFieldName ;//? $oMemberInfo->$sMemberSmspushFieldName : 'N';
		// $oArgs->email_push = $oMemberInfo->$sMemberEmailpushFieldName;
		// $oArgs->post_push = $oMemberInfo->$sMemberPostpushFieldName;
		// $oArgs->sponsor_push = $oMemberInfo->$sMemberSponsorpushFieldName;
		unset($oMemberInfo);

        // construct baby info
        // $sStrippedBabyBirthname = strip_tags($oInParams->birth_name);
        // $oArgs->baby_birth_name = $sStrippedBabyBirthname;
        // $oArgs->baby_gender = $oInParams->baby_gender;
        $oArgs->baby_birthday = Context::get('baby_birth_yr').Context::get('baby_birth_mo').Context::get('baby_birth_day') ;

		$oArgs->upload_target_srl = Context::get('upload_target_srl');
		$sContent = nl2br(strip_tags(Context::get('reason'), ['<a>', '<br>']));
		$oArgs->content = $sContent;

		// begin - insert application into data lake
        $oDocRst = $this->insertApplication($oArgs);
		unset($oArgs);
		if(!$oDocRst->toBool())
			return $oDocRst;
		unset($oDocRst);
		$oDB = DB::getInstance();
		$nAngemomboxSrl = $oDB->db_insert_id();
		unset($oDB);
		// end - insert application
		
		// begin - copy application to connected board
		$oDocumentModel = getModel('document');
		$aExtraKeys = $oDocumentModel->getExtraKeys($nConnectedBoardModuleSrl);
		unset($oDocumentModel);

		$oDocObj = new stdClass();
        // replace %baby_name% to baby's name
        $sTitlePrefix = str_replace('%baby_name%' , $sStrippedBabyBirthname, $sTitlePrefix);
		$oDocObj->title = $sTitlePrefix.' '.cut_str(strip_tags(Context::get('reason')), $nTitleCutSize, '...');
		$oDocObj->content = $sContent;
		$oDocObj->module_srl = $nConnectedBoardModuleSrl;
		if(count($aExtraKeys))
		{
			foreach($aExtraKeys as $nIdx => $oExtraItem)
			{
				if($oExtraItem->name == $sMomboxModuleSrlFieldTtl)
					$oDocObj->{'extra_vars'.$nIdx} = $nModuleSrl;
				if($oExtraItem->name == $sMomboxApplicationSrlFieldTtl)
					$oDocObj->{'extra_vars'.$nIdx} = $nAngemomboxSrl;
			}
		}
		unset($aExtraKeys);
		$oDocumentController = getController('document');
		$oRst = $oDocumentController->insertDocument($oDocObj);
		$nNewDocSrl = $oRst->get('document_srl');
		unset($oDocumentController);
		unset($oDocObj);
		unset($oRst);
		// end - copy application to connected board

		$oModuleModel = &getModel('module');
		$oConfig = $oModuleModel->getModuleInfoByModuleSrl($nConnectedBoardModuleSrl);
		$sConnectedBoardMid = $oConfig->module;
		unset($oModuleModel);
		unset($oConfig);

		// begin - register board_srl and document_srl into application
		$oDocArgs = new stdClass();
		$oDocArgs->connected_doc_srl = $nNewDocSrl;
		$oDocArgs->doc_srl = $nAngemomboxSrl;
		$oDocRst = executeQuery('angemombox.updateAdminConnectedDocSrl', $oDocArgs);
		unset($oDocArgs);
		if(!$oDocRst->toBool())
			return new BaseObject(-1, '연결된 게시판에 등록 실패입니다.');
		unset($oDocRst);
exit;		
        // end - register board_srl and document_srl into application
        $this->add('module', $sConnectedBoardMid);
        $this->add('document_srl', $nNewDocSrl);
	}
/**
* add mombox into data lake
*/   
    public function insertApplication($oArgs)
	{
        if(!$oArgs->module_srl)
            return new BaseObject(-1, 'msg_error_invalid_module_srl');
		if(!$oArgs->yr_mo)
            return new BaseObject(-1, 'msg_error_invalid_yr_mo');
        if(!$oArgs->member_srl)
            return new BaseObject(-1, 'msg_error_invalid_member_srl');
        if(!$oArgs->addr)
            return new BaseObject(-1, 'msg_error_invalid_addr');
        if(!$oArgs->addr_detail)
            return new BaseObject(-1, 'msg_error_invalid_addr_detail');
        if(!$oArgs->baby_birthday)
            return new BaseObject(-1, 'msg_error_invalid_baby_birthday');
		
		$oArgs->user_agent = $_SERVER['HTTP_USER_AGENT'];
		// depends on /addons/svtracker
		$sValue = $this->_getSessionValue('HTTP_INIT_REFERER' );
		if(!is_null($sValue))
			$oArgs->init_referral = $sValue;
		$sValue = $this->_getSessionValue('HTTP_INIT_SOURCE' );
		if(!is_null($sValue))
			$oArgs->utm_source = $sValue;
		$sValue = $this->_getSessionValue('HTTP_INIT_MEDIUM' );
		if(!is_null($sValue))
			$oArgs->utm_medium = $sValue;
		$sValue = $this->_getSessionValue('HTTP_INIT_CAMPAIGN' );
		if(!is_null($sValue))
			$oArgs->utm_campaign = $sValue;
		$sValue = $this->_getSessionValue('HTTP_INIT_KEYWORD' );
		if(!is_null($sValue))
			$oArgs->utm_term = $sValue;
		$oArgs->ipaddress = $_SERVER['REMOTE_ADDR'];
		$oArgs->is_mobile = Mobile::isMobileCheckByAgent() ? 'Y' : 'N';
		return executeQuery('angemombox.insertApplication', $oArgs);
    }
/**
 * @brief session에 기록된 UTM 값을 가져옴
 **/
	private function _getSessionValue( $sSessionName )
	{
		$sSessionName = trim( $sSessionName );
		$sSessionValue = null;
		if( strlen( $sSessionName ) > 0 )
			$sSessionValue = $_SESSION[$sSessionName];
		return $sSessionValue;
	}
/**
 * Edit member profile
 */
	// private function _modifyMemberInfo($oMemberInfo)
	// {
	// 	unset($_SESSION['rechecked_password_step']);
	// 	// Extract the necessary information in advance
	// 	$oMemberModel = getModel('member');
	// 	$config = $oMemberModel->getMemberConfig ();
	// 	$getVars = array('find_account_answer','allow_mailing','allow_message');
	// 	if($config->signupForm)
	// 	{
	// 		foreach($config->signupForm as $formInfo)
	// 		{
	// 			if($formInfo->isDefaultForm && ($formInfo->isUse || $formInfo->required || $formInfo->mustRequired))
	// 			{
	// 				$getVars[] = $formInfo->name;
	// 			}
	// 		}
	// 	}
	// 	$args = new stdClass;
	// 	foreach($getVars as $val)
	// 	{
	// 		$args->{$val} = $oMemberInfo->$val;
	// 		if($val == 'birthday') $args->birthday_ui = Context::get('birthday_ui');
	// 		if($val == 'find_account_answer' && !Context::get($val)) {
	// 			unset($args->{$val});
	// 		}
	// 	}
	// 	// Login Information
	// 	$logged_info = Context::get('logged_info');
	// 	$args->member_srl = $logged_info->member_srl;
    //     $args->birthday = $oMemberInfo->birthday_yyyymmdd;

	// 	// Remove some unnecessary variables from all the vars
	// 	$all_args = $oMemberInfo; //Context::getRequestVars();
	// 	unset($all_args->module);
	// 	unset($all_args->act);
	// 	unset($all_args->member_srl);
	// 	unset($all_args->is_admin);
	// 	unset($all_args->description);
	// 	unset($all_args->group_srl_list);
	// 	unset($all_args->body);
	// 	unset($all_args->accept_agreement);
	// 	unset($all_args->signature);
	// 	unset($all_args->_filter);
	// 	unset($all_args->mid);
	// 	unset($all_args->error_return_url);
	// 	unset($all_args->ruleset);
	// 	unset($all_args->password);

	// 	// Add extra vars after excluding necessary information from all the requested arguments
	// 	$extra_vars = delObjectVars($all_args, $args);
	// 	$args->extra_vars = serialize($extra_vars);

	// 	// remove whitespace
	// 	$checkInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
	// 	foreach($checkInfos as $val)
	// 	{
	// 		if(isset($args->{$val}))
	// 		{
	// 			$args->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($args->{$val}));
	// 		}
	// 	}
	// 	// Execute insert or update depending on the value of member_srl
	// 	$oMemberController = &getController('member');
	// 	$output = $oMemberController->updateMember($args);
	// 	if(!$output->toBool()) return $output;
	// 	unset($oMemberController);
	// 	// Return result
	// 	return new BaseObject();
	// }
/**
* update mombox data lake
*/  
    /*public function updateDataLake($oArgs)
	{
        if(!$oArgs->doc_srl)
            return new BaseObject(-1, 'msg_error_invalid_doc_srl');
        $oArgs->email_push = $oArgs->email_push ? $oArgs->email_push : 'N';
        $oArgs->sms_push = $oArgs->sms_push ? $oArgs->sms_push : 'N';
        $oArgs->post_push = $oArgs->post_push ? $oArgs->post_push : 'N';
        $oArgs->sponsor_push = $oArgs->sponsor_push ? $oArgs->sponsor_push : 'N';
		return executeQuery('angemombox.updateApplication', $oArgs);
    }*/
}