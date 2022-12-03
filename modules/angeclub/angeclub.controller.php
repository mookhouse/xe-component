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
	public function init()
	{
	}
/**
 * @brief 산후조리원 엄마 추가 / 변경 메소드
 **/
	public function procAngeclubAddMom()
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
		if(!$oArgs->_email)
			return new BaseObject(-1, '이메일을 입력하세요.');
		if(!$oArgs->_user_id)
			return new BaseObject(-1, '아이디를 입력하세요.');
		if(!$oArgs->_phone_2)
			return new BaseObject(-1, '핸드폰 번호를 입력하세요.');
		
		// begin - member registration
		// ["_email"]=> string(15) "dfg@hanmail.net"
		// ["_user_id"]=>string(9) "아이디"
		// ["_user_nm"]=>string(12) "엄마이름"
		// ["_birth"]=>string(6) "801212"
		// ["_phone_2"]=>string(9) "010312645"
		// ["_zone_code"]=>string(5) "04078"
		// ["_addr"]=>string(44) "서울 마포구 독막로 126-1 (창전동)"
		// ["_addr_detail"]=>string(13) "상세 주소"
		$oRst = $this->_addMemberInfo();
		if(!$oRst->toBool()) 
			return $oRst;
		$nMemberSrl = $oRst->get('member_srl');
		unset($oRst);
		unset($oArgs->_email);
		unset($oArgs->_user_id);
		unset($oArgs->_phone_2);
		// end - member registration

		// begin - nurse performance registration
		$oInArgs = new stdClass();
		$oInArgs->cc_idx = $oArgs->_care_center;
		$oInArgs->cu_id = $oArgs->_contact_id;
		// $oInArgs->member_srl_staff = null;
		$oInArgs->member_srl_mom = $nMemberSrl;
		$oInArgs->center_visit_cnt = $oArgs->_center_cnt;
		$oInArgs->education_cnt = 1;
		$oInArgs->email_push = $oArgs->email_send == 'Y' ? 'Y' : 'N';
		$oInArgs->sms_push = $oArgs->sms_send == 'Y' ? 'Y' : 'N';
		$oInArgs->post_push = $oArgs->addr_send == 'Y' ? 'Y' : 'N';
		$oInArgs->sponsor_push = $oArgs->sponsor == 'Y' ? 'Y' : 'N';
		if($oArgs->_center_visit_ymd)
			$oInArgs->regdate = '20'.$oArgs->_center_visit_ymd.'000001';
		$oRst = executeQuery('angeclub.insertClubRegistration', $oInArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		unset($oArgs->_contact_id);
		unset($oArgs->_care_center);
		unset($oArgs->_center_visit_ymd);
		unset($oArgs->_center_cnt);
		unset($oArgs->email_send);
		unset($oArgs->sms_send);
		unset($oArgs->sponsor);
		unset($oArgs->addr_send);
		// ["_contact_id"]=>string(5) "admin"
		// ["_care_center"]=>string(30) "637"  // replace cc_name to cc_idx
		// ["_center_visit_ymd"]=>string(6) "221203"  // regdate
		// ["_center_cnt"]=>string(1) "1"
		// ["email_send"]=>string(1) "Y"
		// ["sms_send"]=>string(1) "Y"
		// ["sponsor"]=>string(1) "Y"
		// ["addr_send"]=>string(1) "Y"
		// end - nurse performance registration

		// begin - push msg list registration
		// end - push msg list registration
		
exit;
		// ["i_baby_nm"]=>string(12) "아이이름"
		// ["i_baby_birth"]=>string(6) "221215"
		// ["i_baby_sex_gb"]=>string(1) "T"

		// 무의미
		// ["_sex_gb"]=>string(1) "F"
		// ["_contact_nm"]=>string(12) "웹관리자"
		// ["_care_area"]=>string(6) "강서"
		// ["email_id"]=>string(3) "dfg"
		// ["email_server"]=>string(11) "hanmail.net"
		
		// if(!$oArgs->_cc_city)
		// 	return new BaseObject(-1, '도시를 선택하세요.');
		
		// if((int)$oArgs->cc_idx > 0) // update
		// 	$oRst = executeQuery('angeclub.updateCenter', $oArgs);
		// else  // insert
		// {
		// 	$oArgs->cc_address = '';  // 기존 스키마에 맞춰주는 쓸데없는 짓
		// 	$oRst = executeQuery('angeclub.insertCenter', $oArgs);
		// }
		// if(!$oRst->toBool())
		// 	return $oRst;
		$this->add('bRst', 1);
	}
/**
 * add member profile
 *
 * @return 
 */
	private function _addMemberInfo()
	{
		if(Context::getRequestMethod () == "GET") 
			return new BaseObject(-1, "msg_invalid_request");

		$oMemberModel = &getModel ('member');
		$oConfig = $oMemberModel->getMemberConfig();
		if($oConfig->enable_join != 'Y')
			return $this->stop ('msg_signup_disabled');
		// Check if the user accept the license terms (only if terms exist)
		if($oConfig->agreement && Context::get('accept_agreement')!='Y') 
			return $this->stop('msg_accept_agreement');
		if($oConfig->privacy_usage && Context::get('accept_privacy_usage')!='Y') 
			return $this->stop('msg_accept_privacy_usage');
		if($oConfig->privacy_shr && Context::get('accept_privacy_shr')!='Y') 
			return $this->stop('msg_accept_privacy_shr');

		$oArgs = new stdClass;
		$oArgs->user_id = Context::get('_user_id');
		$oArgs->email_address = Context::get('_email');
		$oArgs->mobile = Context::get('_phone_2');
		$oArgs->password = Context::get('_phone_2');
		$oArgs->user_name = Context::get('_user_nm');
		
		$sBirth = Context::get('_birth');  // 801212
		if((int)$sBirth > 700000)
			$sBirth = '19'.$nBirth;
		elseif((int)$sBirth < 600000)  // 201212
			$sBirth = '20'.$nBirth;
		$oArgs->birthday = $sBirth;

		// check password strength
		if(!$oMemberModel->checkPasswordStrength($oArgs->password, $oConfig->password_strength))
		{
			$message = Context::getLang('about_password_strength');
			return new BaseObject(-1, $message[$oConfig->password_strength]);
		}
		unset($oMemberModel);
		unset($oConfig);

		// Remove some unnecessary variables from all the vars
		$oAllArgs = Context::getRequestVars();
		unset($oAllArgs->_filter);
		unset($oAllArgs->error_return_url);
		unset($oAllArgs->act);
		unset($oAllArgs->mid);
		unset($oAllArgs->module);
		unset($oAllArgs->_email);
		unset($oAllArgs->_phone_2);
		unset($oAllArgs->_user_id);
		unset($oAllArgs->email_id);
		unset($oAllArgs->email_server);
		unset($oAllArgs->_user_nm);
		unset($oAllArgs->_birth);
		unset($oAllArgs->_center_cnt);
		unset($oAllArgs->_clubedu_cnt);
		unset($oAllArgs->email_send);
		unset($oAllArgs->sms_send);
		unset($oAllArgs->sponsor);
		unset($oAllArgs->addr_send);
		unset($oAllArgs->i_baby_nm);
		unset($oAllArgs->i_baby_birth);
		unset($oAllArgs->i_baby_sex_gb);
		unset($oAllArgs->_contact_id);
		unset($oAllArgs->_care_area);
		unset($oAllArgs->_care_center);
		unset($oAllArgs->_center_visit_ymd);

		// 시작 - 주소를 extra var로 변경
		$oAngeclubModel = &getModel('angeclub');
		$oModuleInfo = $oAngeclubModel->getModuleConfig();
		unset($oAngeclubModel);
		$sMemberAddrFieldName = $oModuleInfo->member_addr_field_name;
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		$sAddrTitle = $sMemberAddrFieldName;
		$oAllArgs->$sAddrTitle[0] = $oAllArgs->_zone_code;
		$oAllArgs->$sAddrTitle[1] = $oAllArgs->_addr;
		$oAllArgs->$sAddrTitle[2] = $oAllArgs->_addr_detail;
		$oAllArgs->$sAddrTitle[3] = '';
		unset($oAllArgs->_zone_code);
		unset($oAllArgs->_addr);
		unset($oAllArgs->_addr_detail);
		// 끝 - 주소를 extra var로 변경

		// Add extra vars after excluding necessary information from all the requested arguments
		$oExtraVars = delObjectVars($oAllArgs, $oArgs);
		$oArgs->extra_vars = serialize($oExtraVars);
		// remove whitespace
		$aCheckInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
		foreach($aCheckInfos as $val)
		{
			if(isset($oArgs->{$val}))
				$oArgs->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($oArgs->{$val}));
		}
		$oMemberController = &getController('member');
		return $oMemberController->insertMember($oArgs);
	}
/**
 * @brief 센터 추가 / 변경 메소드
 **/
	public function procAngeclubAddCenter()
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
			$oArgs->cc_address = '';  // 기존 스키마에 맞춰주는 쓸데없는 짓
			$oRst = executeQuery('angeclub.insertCenter', $oArgs);
		}
		if(!$oRst->toBool())
			return $oRst;
		$this->add('bRst', 1);
	}
}