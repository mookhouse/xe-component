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
//var_dump($oInParams);
//exit;
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		$sAddrTitle = $sMemberAddrFieldName; // 'address';
		$oMemberInfo->$sAddrTitle[0] = $oInParams->postcode;
		$oMemberInfo->$sAddrTitle[1] = $oInParams->address;
		$oMemberInfo->$sAddrTitle[2] = $oInParams->detailaddress;
		$oMemberInfo->$sAddrTitle[3] = $oInParams->extraaddress;

		$sGiveBirthDate = $sMemberGiveBirthDateFieldName; //'give_birth_date';
		$oMemberInfo->$sGiveBirthDate = $oInParams->year.$oInParams->month.$oInParams->day;

		$sSnsId = $sMemberSnsFieldName; //'sns';
		$oMemberInfo->$sSnsId = strip_tags($oInParams->sns);

		// 회원 정보에 주소, 출산일, SNS ID 등록
		$this->_modifyMemberInfo($oMemberInfo);
		
		$oArgs = new stdClass();
		$oArgs->module_srl = Context::get('module_srl');
		$oArgs->member_srl = $oLoggedInfo->member_srl;
		$oArgs->privacy_collection = Context::get('privacy_collection') == 'Y' ? 1 : 0;
		$oArgs->privacy_sharing = Context::get('privacy_sharing') == 'Y' ? 1 : 0;

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

		$sTitle = $sTitlePrefix.' '.cut_str(strip_tags(Context::get('reason')), $nTitleCutSize, '...');
		$sContent = nl2br(strip_tags(Context::get('reason'), ['<a>', '<br>']));

		$oArgs->yr_mo = $nYrMo;
		$oArgs->upload_target_srl = Context::get('upload_target_srl');
		$oArgs->content = $sContent;

		// begin - insert application
		$oDocRst = executeQuery('angemombox.insertAngemombox', $oArgs);
		unset($oArgs);
		if(!$oDocRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');

		$oDB = DB::getInstance();
		$nAngemomboxSrl = $oDB->db_insert_id();
		unset($oDocRst);
		unset($oDB);
		// end - insert application
		
		// begin - copy application to connected board
		$oDocumentModel = getModel('document');
		$aExtraKeys = $oDocumentModel->getExtraKeys($nConnectedBoardModuleSrl);
		unset($oDocumentModel);

		$oDocObj = new stdClass();
		$oDocObj->title = $sTitle;
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
		// end - register board_srl and document_srl into application
		if(!in_array(Context::getRequestMethod(),['XMLRPC','JSON']))
		{
			$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', $sConnectedBoardMid, 'document_srl', $nNewDocSrl);
			$this->setRedirectUrl($returnUrl);
			return;
		}
		else
		{
			$this->add('module', $sConnectedBoardMid);
			$this->add('document_srl', $nNewDocSrl);
		}
	}
/**
 * Edit member profile
 *
 * @return void|BaseObject (void : success, BaseObject : fail)
 */
	private function _modifyMemberInfo($oMemberInfo)
	{
		if(!Context::get('is_logged'))
		{
			return $this->stop('msg_not_logged');
		}
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
		$args->birthday = intval(strtr($args->birthday, array('-'=>'', '/'=>'', '.'=>'', ' '=>'')));
		if(!$args->birthday && $args->birthday_ui) $args->birthday = intval(strtr($args->birthday_ui, array('-'=>'', '/'=>'', '.'=>'', ' '=>'')));

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
		
		//$profile_image = Context::get('profile_image');
		//if(is_uploaded_file($profile_image['tmp_name']))
		//{
		//	$this->insertProfileImage($args->member_srl, $profile_image['tmp_name']);
		//}

		//$image_mark = Context::get('image_mark');
		//if(is_uploaded_file($image_mark['tmp_name']))
		//{
		//	$this->insertImageMark($args->member_srl, $image_mark['tmp_name']);
		//}

		//$image_name = Context::get('image_name');
		//if(is_uploaded_file($image_name['tmp_name']))
		//{
		//	$this->insertImageName($args->member_srl, $image_name['tmp_name']);
		//}

		// Save Signature
		//$signature = Context::get('signature');
		//$this->putSignature($args->member_srl, $signature);

		// Call a trigger after successfully log-in (after)
		//$trigger_output = ModuleHandler::triggerCall('member.procMemberModifyInfo', 'after', $this->memberInfo);
		//if(!$trigger_output->toBool()) return $trigger_output;

		//$oMemberController->setSessionInfo();
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