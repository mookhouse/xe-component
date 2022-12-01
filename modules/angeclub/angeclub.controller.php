<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubController
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubController
**/ 
class angeclubController extends angeclub
{
/**
 * @brief initialization
 **/
	function init()
	{
	}
/**
 * @brief 센터 추가 / 변경 메소드
 **/
	function procAngeclubAddCenter()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, '로그인 하세요.');

		$oArgs = Context::getRequestVars();
		unset($oArgs->_filter);
		unset($oArgs->error_return_url);
		unset($oArgs->act);
		unset($oArgs->mid);
		unset($oArgs->module);
		if(!$oArgs->_cu_id)
			return new BaseObject(-1, '담당자를 선택하세요.');
		if(!$oArgs->_cc_city)
			return new BaseObject(-1, '도시를 선택하세요.');
		if(!$oArgs->_cc_area)
			return new BaseObject(-1, '지역을 선택하세요.');
		if(!$oArgs->_zone_code)
			return new BaseObject(-1, '우편 번호를 입력하세요.');
		if(!$oArgs->_addr)
			return new BaseObject(-1, '주소를 입력하세요.');
		if(!$oArgs->_addr_detail)
			return new BaseObject(-1, '상세주소를 입력하세요.');
		if(!$oArgs->_cc_name)
			return new BaseObject(-1, '교육장명을 입력하세요.');
		if(!$oArgs->_cc_state)
			return new BaseObject(-1, '교육장 상태를 선택하세요.');
		
		if((int)$oArgs->cc_idx > 0) // update
			$oRst = executeQuery('angeclub.updateCenter', $oArgs);
		else  // insert
		{
			$oArgs->cc_address = '';
			$oRst = executeQuery('angeclub.insertCenter', $oArgs);
		}
		if(!$oRst->toBool())
			return $oRst;
		$this->add('bRst', 1);
	}
/**
 * Edit member profile
 *
 * @return void|BaseObject (void : success, BaseObject : fail)
 */
	private function _modifyMemberInfo($oMemberInfo)
	{
		if(!Context::get('is_logged'))
			return $this->stop('msg_not_logged');

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
}