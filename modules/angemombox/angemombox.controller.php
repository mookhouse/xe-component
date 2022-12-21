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
	function init()
	{
	}
/**
 * @brief 신규 응모 추가 메소드
 **/
	function procAngemomboxRegistration()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, '로그인 하세요.');

		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl)
			return new BaseObject(-1, '잘못된 접근입니다.');
		
		$sSmsPush = Context::get('sms_push');
		if($sSmsPush != 'Y')
			return new BaseObject(-1, 'SMS 정보 수신에 동의해 주세요.');
			
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
		$sMemberAddrFieldName = $oDocInfo->member_addr_field_name;
		$sMemberGiveBirthDateFieldName = $oDocInfo->member_give_birth_date_field_name;
		$sMemberSnsFieldName = $oDocInfo->member_sns_field_name;

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
		unset($oMemberModel);

        // replace %mom_name% to member's name
        $sTitlePrefix = str_replace('%mom_name%' , $oMemberInfo->user_name, $sTitlePrefix);

		// never touch unrelated member info
		unset($oMemberInfo->password);
		unset($oMemberInfo->find_account_question);
		unset($oMemberInfo->find_account_answer);
		unset($oMemberInfo->user_name);
		unset($oMemberInfo->user_id);
		unset($oMemberInfo->nick_name);
		unset($oMemberInfo->description);
		unset($oMemberInfo->birthday);
		unset($oMemberInfo->group_srl_list);
		// never touch unrelated member info

		$oInParams = Context::getRequestVars();
// var_dump($oInParams);
// exit;		        
        // begin - check mandatory field - addr
        if(!$oInParams->postcode && !$oInParams->address && !$oInParams->detailaddress && !$oInParams->extraaddress)
        {
            if(count((array)$oLoggedInfo->address) > 3)  // reuse member info if registered
            {
                $sStrippedPostcode = $oLoggedInfo->address[0];
                $sStrippedAddr = $oLoggedInfo->address[1];
                $sStrippedAddrDetail = $oLoggedInfo->address[2];
                $sStrippedAddrExtra = $oLoggedInfo->address[3];
            }
            else
                return new BaseObject(-1, '배송 주소를 입력하세요.');
        }
        else
        {
            $sStrippedPostcode = strip_tags($oInParams->postcode);
            $sStrippedAddr = strip_tags($oInParams->address);
            $sStrippedAddrDetail = strip_tags($oInParams->detailaddress);
            $sStrippedAddrExtra = strip_tags($oInParams->extraaddress);
        }
        // end - check mandatory field - addr

        // begin - add mom's birthday into member tbl extra_vars
        if(!$oInParams->parent_birth_yr && !$oInParams->parent_birth_mo && !$oInParams->parent_birth_day)
        {
            if(strlen($oLoggedInfo->birthday))  // reuse member info if registered
                $sParentBirthday = $oLoggedInfo->birthday[0];
        }
        else
            $sParentBirthday = strip_tags($oInParams->parent_birth_yr).strip_tags($oInParams->parent_birth_mo).strip_tags($oInParams->parent_birth_day);

        // $sParentBirthday = strip_tags($oInParams->parent_birth_yr.$oInParams->parent_birth_mo.$oInParams->parent_birth_day);
        $oMemberInfo->birthday_yyyymmdd = $sParentBirthday;
        // end - add mom's birthday into member tbl extra_vars
        
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		$sAddrTitle = $sMemberAddrFieldName; // 'address';
		$oMemberInfo->$sAddrTitle[0] = $sStrippedPostcode;
		$oMemberInfo->$sAddrTitle[1] = $sStrippedAddr;
		$oMemberInfo->$sAddrTitle[2] = $sStrippedAddrDetail;
		$oMemberInfo->$sAddrTitle[3] = $sStrippedAddrExtra;

		// $sGiveBirthDate = $sMemberGiveBirthDateFieldName; //'give_birth_date';
		// $oMemberInfo->$sGiveBirthDate = $oInParams->baby_birth_yr.$oInParams->baby_birth_mo.$oInParams->baby_birth_day;
		// $sSnsId = $sMemberSnsFieldName; //'sns';
		// $oMemberInfo->$sSnsId = strip_tags($oInParams->sns);
		$this->_modifyMemberInfo($oMemberInfo);
        // end - update addr, mom's birthday into member tbl
		$sParentMobileNo = $oMemberInfo->mobile;
		unset($oMemberInfo);
		
		$oArgs = new stdClass();
		$oArgs->module_srl = Context::get('module_srl');
		$oArgs->yr_mo = $nYrMo;
		$oArgs->parent_member_srl = $oLoggedInfo->member_srl;  // mom's member srl
		$oArgs->parent_birthday = $sParentBirthday;
		$oArgs->mobile = $sParentMobileNo;
		$oArgs->parent_gender = $oInParams->parent_gender;
		$oArgs->parent_pregnant = $oInParams->parent_pregnant;

        // construct mom's info
        $oArgs->postcode = $sStrippedPostcode;
		$oArgs->addr = $sStrippedAddr;
		$oArgs->addr_detail = $sStrippedAddrDetail;
		$oArgs->addr_extra = $sStrippedAddrExtra;
        $oArgs->sns_id = strip_tags($oInParams->sns);  // mom's sns id
        // construct push agreement
        // $oArgs->email_push = 'Y';
		$oArgs->sms_push = $oInParams->sms_push;  // 항상 'Y'
		// $oArgs->post_push = 'Y';
		// $oArgs->sponsor_push = 'Y';

        // construct baby info
        $sStrippedBabyBirthname = strip_tags($oInParams->birth_name);
        $oArgs->baby_birth_name = $sStrippedBabyBirthname;
        $oArgs->baby_gender = $oInParams->baby_gender;
        $oArgs->baby_birthday = $oInParams->baby_birth_yr.$oInParams->baby_birth_mo.$oInParams->baby_birth_day;

		$oArgs->upload_target_srl = Context::get('upload_target_srl');
		$sContent = nl2br(strip_tags(Context::get('reason'), ['<a>', '<br>']));
		$oArgs->content = $sContent;

		$oArgs->is_mobile = Mobile::isMobileCheckByAgent() ? 'Y' : 'N';
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

		// begin - insert application into data lake
        $oDocRst = $this->insertDataLake($oArgs);
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
* update mombox data lake
*/  
    public function updateDataLake($oArgs)
	{
        if(!$oArgs->datalake_doc_srl)
            return new BaseObject(-1, 'msg_error_invalid_datalake_doc_srl');
        $oArgs->email_push = $oArgs->email_push ? $oArgs->email_push : 'N';
        $oArgs->sms_push = $oArgs->sms_push ? $oArgs->sms_push : 'N';
        $oArgs->post_push = $oArgs->post_push ? $oArgs->post_push : 'N';
        $oArgs->sponsor_push = $oArgs->sponsor_push ? $oArgs->sponsor_push : 'N';
		return executeQuery('angemombox.updateDataLake', $oArgs);
    }
/**
* add mombox into data lake
*/   
    public function insertDataLake($oArgs)
	{
        if(!$oArgs->module_srl)
            return new BaseObject(-1, 'msg_error_invalid_module_srl');
		if(!$oArgs->yr_mo)
            return new BaseObject(-1, 'msg_error_invalid_yr_mo');
        if(!$oArgs->parent_member_srl)
            return new BaseObject(-1, 'msg_error_invalid_member_srl');
		if(!$oArgs->parent_gender)
            return new BaseObject(-1, 'msg_error_invalid_parent_gender');
        if(!$oArgs->is_mobile)
            return new BaseObject(-1, 'msg_error_invalid_is_mobile');
        if(!$oArgs->addr)
            return new BaseObject(-1, 'msg_error_invalid_addr');
        if(!$oArgs->addr_detail)
            return new BaseObject(-1, 'msg_error_invalid_addr_detail');
        if(!$oArgs->baby_birthday)
            return new BaseObject(-1, 'msg_error_invalid_baby_birthday');
		$oArgs->ipaddress = $_SERVER['REMOTE_ADDR'];
		return executeQuery('angemombox.insertDataLake', $oArgs);
    }
/**
 * Edit member profile
 */
	private function _modifyMemberInfo($oMemberInfo)
	{
		unset($_SESSION['rechecked_password_step']);
		// Extract the necessary information in advance
		$oMemberModel = getModel('member');
		$config = $oMemberModel->getMemberConfig ();
		$getVars = array('find_account_answer','allow_mailing','allow_message');
		if($config->signupForm)
		{
			foreach($config->signupForm as $formInfo)
			{
				if($formInfo->isDefaultForm && ($formInfo->isUse || $formInfo->required || $formInfo->mustRequired))
				{
					$getVars[] = $formInfo->name;
				}
			}
		}
		$args = new stdClass;
		foreach($getVars as $val)
		{
			$args->{$val} = $oMemberInfo->$val;
			if($val == 'birthday') $args->birthday_ui = Context::get('birthday_ui');
			if($val == 'find_account_answer' && !Context::get($val)) {
				unset($args->{$val});
			}
		}
		// Login Information
		$logged_info = Context::get('logged_info');
		$args->member_srl = $logged_info->member_srl;
        $args->birthday = $oMemberInfo->birthday_yyyymmdd;

		// Remove some unnecessary variables from all the vars
		$all_args = $oMemberInfo; //Context::getRequestVars();
		unset($all_args->module);
		unset($all_args->act);
		unset($all_args->member_srl);
		unset($all_args->is_admin);
		unset($all_args->description);
		unset($all_args->group_srl_list);
		unset($all_args->body);
		unset($all_args->accept_agreement);
		unset($all_args->signature);
		unset($all_args->_filter);
		unset($all_args->mid);
		unset($all_args->error_return_url);
		unset($all_args->ruleset);
		unset($all_args->password);

		// Add extra vars after excluding necessary information from all the requested arguments
		$extra_vars = delObjectVars($all_args, $args);
		$args->extra_vars = serialize($extra_vars);

		// remove whitespace
		$checkInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
		foreach($checkInfos as $val)
		{
			if(isset($args->{$val}))
			{
				$args->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($args->{$val}));
			}
		}
		// Execute insert or update depending on the value of member_srl
		$oMemberController = &getController('member');
		$output = $oMemberController->updateMember($args);
		if(!$output->toBool()) return $output;
		unset($oMemberController);
		// Return result
		return new BaseObject();
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
}