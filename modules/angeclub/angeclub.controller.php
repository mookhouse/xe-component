<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubController
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubController
**/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class angeclubController extends angeclub
{
/**
 * @brief initialization
 **/
	public function init()
	{
	}
/**
 * @brief /inquiry 페이지의 광고 문의 메일 전송
 * https://seokd.tistory.com/6
 **/
	public function procAngeclubSendMail()
	{
		$oAngeclubModel = &getModel ('angeclub');
		$oConfig = $oAngeclubModel->getModuleConfig();
		unset($oAngeclubModel);

		$oArgs = Context::getRequestVars();
		unset($oArgs->module);
		unset($oArgs->act);

		foreach($oArgs as $sName => $sVal)
			$oArgs->$sName = strip_tags($sVal);

		require_once(_XE_PATH_.'libs/PHPMailer.6.7.1/src/Exception.php');
		require_once(_XE_PATH_.'libs/PHPMailer.6.7.1/src/PHPMailer.php');
		require_once(_XE_PATH_.'libs/PHPMailer.6.7.1/src/SMTP.php');

		$oMail = new PHPMailer(true);
		// allow insecure connections via the SMTPOptions
		// Connection failed. Error #2: stream_socket_client(): SSL operation failed with code 1. OpenSSL Error messages:error:14090086:SSL 발생 시 허용해야 함
		if($oConfig->allow_insecure_connections == 'Y')
		{
			$oMail->SMTPOptions = [
				'ssl' => [
					'verify_peer' => false,
					'verify_peer_name' => false,
					'allow_self_signed' => true,
				]
			];
		}
		$oMail->SMTPDebug = 0;
		$oMail->isSMTP();
		$oMail->SMTPAuth = true; //Set this to true if SMTP host requires authentication to send email
		$oMail->CharSet = "utf-8"; //이거 설정해야 한글 안깨짐
		//Provide username and password     
		$oMail->Username = $oConfig->sender_email_id; //발송 이메일
		$oMail->Password = $oConfig->sender_email_pw; // 비밀번호 STMP 비번

		// daum STMP 설정
		//$oMail->Host = "smtp.daum.net";  //Set SMTP host name
		//$oMail->SMTPSecure = "ssl";  
		//$oMail->Port = 465;  //Set TCP port to connect to
		// 네이버 STMP 설정
		// $oMail->Host = "smtp.naver.com";
		// $oMail->SMTPSecure = "tls";  //If SMTP requires TLS encryption then set it
		// $oMail->Port = 587;
		// 메일 플러그 SMTP 설정
		$oMail->Host = $oConfig->sender_email_host;
		$oMail->SMTPSecure = "ssl";
		$oMail->Port = 465;

		$oMail->From = $oConfig->sender_email_id; //발송 이메일
		$oMail->FromName = "앙쥬";
		$oMail->addAddress($oConfig->sender_email_id, '앙쥬'); //받는 사람
		$oMail->Subject = $oArgs->scomapnyname."의 ".$oArgs->scategorygb.'입니다.';
		
		$sBody = "안녕하세요. ".$oArgs->scomapnyname."의 문의 내용입니다.".PHP_EOL.PHP_EOL.PHP_EOL;
		$sBody .= "문의구분 : ".$oArgs->scategorygb.PHP_EOL;
		$sBody .= "기업명 : ".$oArgs->scomapnyname.PHP_EOL;
		$sBody .= "담당자 : ".$oArgs->schargename.PHP_EOL;
		$sBody .= "홈페이지 : ".$oArgs->surl.PHP_EOL;
		$sBody .= "이메일 : ".$oArgs->semail.PHP_EOL;
		$sBody .= "전화 : ".$oArgs->phone.PHP_EOL;
		$sBody .= "내용 : ".$oArgs->snote.PHP_EOL;
		$oMail->Body = $sBody;
		$oMail->AltBody = "";
		try 
		{
			$oMail->send();
			$this->add('isSent', 1);
		}
		catch (Exception $e) //echo "Mailer Error: " . $oMail->ErrorInfo;
		{
			$this->add('isSent', 0);
		}
		unset($oMail);
		unset($oConfig);
		unset($oArgs);
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
		$oArgs->old_member_srl = (int)$oArgs->old_member_srl;
		// 무의미한 변수 제거
		unset($oArgs->_filter);
		unset($oArgs->error_return_url);
		unset($oArgs->act);
		unset($oArgs->mid);
		unset($oArgs->module);
		unset($oArgs->_sex_gb);
		unset($oArgs->_contact_nm);// ["_contact_nm"]=>string(12) "웹관리자"
		unset($oArgs->_care_area);// ["_care_area"]=>string(6) "강서"

		if(!$oArgs->email_id || !$oArgs->email_server )
			return new BaseObject(-1, '이메일을 입력1하세요.');
		if(!$oArgs->_user_id)
			return new BaseObject(-1, '아이디를 입력하세요.');
		if(!$oArgs->_phone_2)
			return new BaseObject(-1, '핸드폰 번호를 입력하세요.');
		
        if(!$oArgs->old_member_srl)  // append new member
        {
            // $oMemberRst = $this->_addMemberInfo();  // angemombox_member_extra tbl과 baby_list도 trigger로 등록됨
			$oMemberRst = $this->_constructMemberInfo('insert');
			if(!$oMemberRst->toBool()) 
				return $oMemberRst;
			$oMemberArgs = $oMemberRst->get('oArgs');
			unset($oMemberRst);
			$oMemberController = &getController('member');
			// angemombox_member_extra tbl도 trigger로 등록됨
			$oMemberRst = $oMemberController->insertMember($oMemberArgs);
			unset($oMemberArgs);
			unset($oMemberController);
            if(!$oMemberRst->toBool()) 
                return $oMemberRst;
            $nMemberSrl = $oMemberRst->get('member_srl');
			unset($oMemberRst);
			// end - member info registration

			// begin - nurse performance registration
			$oClubRst = $this->_registerClubRegistration($oLoggedInfo->member_srl, $nMemberSrl);
			if(!$oClubRst->toBool()) 
				return $oClubRst;
			$nAngeclubRegistrationLogSrl = $oClubRst->get('nAngeclubRegistrationLogSrl');
			unset($oClubRst);
			// end - nurse performance registration

// 			$oInArgs = new stdClass();
// 			$oInArgs->cc_idx = $oArgs->_care_center;
// 			// $oInArgs->cu_id = $oArgs->_contact_id;  // ["_contact_id"]=>string(5) "hya1021"
// 			$oInArgs->member_srl_staff = $oLoggedInfo->member_srl;
// 			$oInArgs->member_srl_parent = $nMemberSrl;
// 			$oInArgs->center_visit_cnt = $oArgs->_center_cnt;
// 			$oInArgs->education_cnt = $oArgs->_clubedu_cnt;
// 			if($oArgs->_center_visit_ymd)
// 				$oInArgs->regdate = $oArgs->_center_visit_ymd.'000001';
// 			if($nExistingMemberSrl)  // 기존 회원 수정이면 표시함
// 				$oInArgs->is_existing_parent_member = 'Y';
// // var_dump($oArgs);
// 			$oRst = executeQuery('angeclub.insertClubRegistration', $oInArgs);
// 			unset($oInArgs);
// 			if(!$oRst->toBool()) 
// 				return $oRst;
// 			unset($oRst);
// 			$oDB = DB::getInstance();
// 			$nAngeclubRegistrationLogSrl = $oDB->db_insert_id();
// 			unset($oDB);
			
			// begin - update angeclub_registration_log_srl for angemombox_member_extra tbl
			$oMemberExtraArgs = new stdClass();
			$oMemberExtraArgs->member_srl = $nMemberSrl;
			$oMemberExtraArgs->angeclub_registration_log_srl = $nAngeclubRegistrationLogSrl;
			$oAngemomboxController = &getController('angemombox');
			$oRst = $oAngemomboxController->updateMemberExtraAngeclubRegistrationLogSrl($oMemberExtraArgs);
			unset($oMemberExtraArgs);
			unset($oAngemomboxController);
			if(!$oRst->toBool())
				return $oRst;
			unset($oRst);
			// end - update angeclub_registration_log_srl for angemombox_member_extra tbl
        }
        else  // update existing old member
        {
			// $oMemberRst = $this->_updateMemberInfo();  // angemombox_member_extra tbl과 baby_list도 trigger로 등록됨
			// if(!$oMemberRst->toBool()) 
			// 	return $oMemberRst;
			$oMemberRst = $this->_constructMemberInfo('update');
			if(!$oMemberRst->toBool()) 
				return $oMemberRst;
			$oMemberArgs = $oMemberRst->get('oArgs');
			unset($oMemberRst);
			$oMemberController = &getController('member');
			// angemombox_member_extra tbl도 trigger로 등록됨
			$oMemberRst = $oMemberController->updateMember($oMemberArgs, TRUE);  // angemombox_member_extra tbl과 baby_list도 trigger로 등록됨
			unset($oMemberController);
			if(!$oMemberRst->toBool()) 
				return $oMemberRst;
			unset($oMemberArgs);
			unset($oMemberRst);

			// begin - nurse performance registration
			$oClubRst = $this->_registerClubRegistration($oLoggedInfo->member_srl, $oArgs->old_member_srl);
			if(!$oClubRst->toBool()) 
				return $oClubRst;
			unset($oClubRst);
			// end - nurse performance registration
        }
echo __FILE__.':'.__LINE__;
exit;	
		unset($oInArgs);
		$this->add('bRst', 1);
	}
/**
 * register club registration
 */
	private function _registerClubRegistration($nMemberSrlStaff, $nMemberSrlParent)
	{
		$oRegistArgs = new stdClass();
		$oRegistArgs->member_srl_parent = $nMemberSrlParent;
		$oRegistRst = executeQuery('angeclub.getRegistrationByMemberSrlParent', $oRegistArgs);
		unset($oRegistArgs);
		if(!$oRegistRst->toBool())
			return $oRegistRst;
		
		$oInArgs = new stdClass();
		$oInArgs->cc_idx = Context::get('_care_center');
		// $oInArgs->cu_id = $oArgs->_contact_id;  // ["_contact_id"]=>string(5) "hya1021"
		$oInArgs->member_srl_staff = $nMemberSrlStaff;
		$oInArgs->member_srl_parent = $nMemberSrlParent;
		$oInArgs->center_visit_cnt = Context::get('_center_cnt');
		$oInArgs->education_cnt = Context::get('_clubedu_cnt');
		$oInArgs->regdate = Context::get('_center_visit_ymd').'000001';
		if($oRegistRst->data->log_srl > 0)  // update an existing registration
			$oRst = executeQuery('angeclub.updateClubRegistration', $oInArgs);
		else  // update a new registration
		{
			if(Context::get('is_existing_member_parent') == 'Y')  // 홈피 가입 회원을 조리원에서 만나면 Y
				$oInArgs->is_existing_member_parent = 'Y';
			$oRst = executeQuery('angeclub.insertClubRegistration', $oInArgs);
		}
// var_dump($oRst);
		unset($oInArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		unset($oRst);
		$oDB = DB::getInstance();
		$nAngeclubRegistrationLogSrl = $oDB->db_insert_id();
		unset($oDB);
		$oRst = new BaseObject();
		$oRst->add('nAngeclubRegistrationLogSrl', $nAngeclubRegistrationLogSrl);
		return $oRst;
	}
/**
 * pre process for member info
 */
	private function _constructMemberInfo($sMode)
	{
		if(Context::getRequestMethod () == "GET") 
			return new BaseObject(-1, "msg_invalid_request");

		$oAllArgs = Context::getRequestVars();
		// Remove unnecessary variables from all the vars
		unset($oAllArgs->_filter);
		unset($oAllArgs->error_return_url);
		unset($oAllArgs->act);
		unset($oAllArgs->mid);
		unset($oAllArgs->module);
		unset($oAllArgs->_center_cnt);
		unset($oAllArgs->_clubedu_cnt);
		unset($oAllArgs->i_baby_nm);
		unset($oAllArgs->i_baby_birth);
		unset($oAllArgs->i_baby_sex_gb);
		unset($oAllArgs->_contact_id);
		unset($oAllArgs->_care_area);
		unset($oAllArgs->_care_center);
		unset($oAllArgs->_center_visit_ymd);

		$oArgs = new stdClass;
		$oArgs->user_id = strip_tags($oAllArgs->_user_id);
		$oArgs->user_name = strip_tags($oAllArgs->_user_nm);
		$oArgs->email_address = strip_tags($oAllArgs->email_id).'@'.strip_tags($oAllArgs->email_server);
		$oArgs->mobile = strip_tags($oAllArgs->_phone_2);
		$oArgs->birthday = strip_tags($oAllArgs->_birth);
		unset($oAllArgs->_user_id);
		unset($oAllArgs->_user_nm);
		unset($oAllArgs->email_id);
		unset($oAllArgs->email_server);
		unset($oAllArgs->_phone_2);
		unset($oAllArgs->_birth);

		if($sMode == 'insert')  // add member
		{
			$oMemberModel = &getModel ('member');
			$oConfig = $oMemberModel->getMemberConfig();
			if($oConfig->enable_join != 'Y')
				return $this->stop ('msg_signup_disabled');

			$oArgs->password = strip_tags($oAllArgs->password);
			unset($oAllArgs->password);
			// check password strength
			if(!$oMemberModel->checkPasswordStrength($oArgs->password, $oConfig->password_strength))
			{
				$message = Context::getLang('about_password_strength');
				return new BaseObject(-1, $message[$oConfig->password_strength]);
			}
			unset($oMemberModel);
			unset($oConfig);
		}
		elseif($sMode == 'update')  // update member
		{
			unset($_SESSION['rechecked_password_step']);
			$oArgs->member_srl = (int)$oAllArgs->old_member_srl;
		}
		else
			return new BaseObject(-1, "msg_invalid_request");

		unset($oAllArgs->password);

		$oAngeclubModel = &getModel('angeclub');
		$oMemberConnectionRst = $oAngeclubModel->getMemberFieldConnection();
		unset($oAngeclubModel);
		if(!$oMemberConnectionRst->toBool())
			return $oMemberConnectionRst;
		$sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		$sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		$sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		$sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		$sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		$sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		unset($oMemberConnectionRst);

		// 시작 - 주소를 extra var로 변경
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		$oAllArgs->$sMemberAddrFieldName[0] = strip_tags($oAllArgs->_zone_code);
		$oAllArgs->$sMemberAddrFieldName[1] = strip_tags($oAllArgs->_addr);
		$oAllArgs->$sMemberAddrFieldName[2] = strip_tags($oAllArgs->_addr_detail);
		$oAllArgs->$sMemberAddrFieldName[3] = '';
		unset($oAllArgs->_zone_code);
		unset($oAllArgs->_addr);
		unset($oAllArgs->_addr_detail);
		// 끝 - 주소를 extra var로 변경

		$oAllArgs->$sMemberGenderFieldName = '여';  // 산후 조리원이므로 반드시 여성  성별을 extra var로 변경
		$oAllArgs->$sMemberSmspushFieldName = strip_tags($oAllArgs->sms_send);  // sms 수신 동의를 extra var로 변경
		$oAllArgs->$sMemberEmailpushFieldName = strip_tags($oAllArgs->email_send);  // 이메일 수신 동의를 extra var로 변경
		$oAllArgs->$sMemberPostpushFieldName = strip_tags($oAllArgs->addr_send);  // 우편 수신 동의를 extra var로 변경
		$oAllArgs->$sMemberSponsorpushFieldName = strip_tags($oAllArgs->sponsor);  // 후원사 정보 수신 동의를 extra var로 변경
		unset($oAllArgs->email_send);
		unset($oAllArgs->sms_send);
		unset($oAllArgs->sponsor);
		unset($oAllArgs->addr_send);

		// Add extra vars after excluding necessary information from all the requested arguments
		$oExtraVars = delObjectVars($oAllArgs, $oArgs);
		$oArgs->extra_vars = serialize($oExtraVars);
		unset($oExtraVars);
		unset($oAllArgs);

		// remove whitespace
		$aCheckInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
		foreach($aCheckInfos as $val)
		{
			if(isset($oArgs->{$val}))
				$oArgs->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($oArgs->{$val}));
		}
		$oRst = new BaseObject();
		$oRst->add('oArgs', $oArgs);
		unset($oArgs);
		return $oRst;
	}
/**
 * update member profile
 */
	private function _updateMemberInfo()
	{
		// if(Context::getRequestMethod () == "GET") 
		// 	return new BaseObject(-1, "msg_invalid_request");

		// unset($_SESSION['rechecked_password_step']);
		
		// $oAllArgs = Context::getRequestVars();
		// // Remove unnecessary variables from all the vars
		// unset($oAllArgs->_filter);
		// unset($oAllArgs->error_return_url);
		// unset($oAllArgs->act);
		// unset($oAllArgs->mid);
		// unset($oAllArgs->module);
		// unset($oAllArgs->_center_cnt);
		// unset($oAllArgs->_clubedu_cnt);
		// unset($oAllArgs->i_baby_nm);
		// unset($oAllArgs->i_baby_birth);
		// unset($oAllArgs->i_baby_sex_gb);
		// unset($oAllArgs->_contact_id);
		// unset($oAllArgs->_care_area);
		// unset($oAllArgs->_care_center);
		// unset($oAllArgs->_center_visit_ymd);

		// $oArgs = new stdClass;
		// $oArgs->member_srl = (int)$oAllArgs->old_member_srl;
		// $oArgs->user_id = strip_tags($oAllArgs->_user_id);
		// $oArgs->user_name = strip_tags($oAllArgs->_user_nm);
		// $oArgs->email_address = strip_tags($oAllArgs->email_id).'@'.strip_tags($oAllArgs->email_server);
		// $oArgs->mobile = strip_tags($oAllArgs->_phone_2);
		// $oArgs->birthday = strip_tags($oAllArgs->_birth);
		// unset($oAllArgs->old_member_srl);
		// unset($oAllArgs->_user_id);
		// unset($oAllArgs->_user_nm);
		// unset($oAllArgs->email_id);
		// unset($oAllArgs->email_server);
		// unset($oAllArgs->_phone_2);
		// unset($oAllArgs->_birth);
		// unset($oAllArgs->password);

		// $oAngeclubModel = &getModel('angeclub');
		// $oMemberConnectionRst = $oAngeclubModel->getMemberFieldConnection();
		// unset($oAngeclubModel);
		// if(!$oMemberConnectionRst->toBool())
		// 	return $oMemberConnectionRst;
		// $sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		// $sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		// $sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		// $sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		// $sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		// $sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		// unset($oMemberConnectionRst);

		// // 시작 - 주소를 extra var로 변경
		// // O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		// $oAllArgs->$sMemberAddrFieldName[0] = strip_tags($oAllArgs->_zone_code);
		// $oAllArgs->$sMemberAddrFieldName[1] = strip_tags($oAllArgs->_addr);
		// $oAllArgs->$sMemberAddrFieldName[2] = strip_tags($oAllArgs->_addr_detail);
		// $oAllArgs->$sMemberAddrFieldName[3] = '';
		// unset($oAllArgs->_zone_code);
		// unset($oAllArgs->_addr);
		// unset($oAllArgs->_addr_detail);
		// // 끝 - 주소를 extra var로 변경

		// $oAllArgs->$sMemberGenderFieldName = '여';  // 산후 조리원이므로 반드시 여성  성별을 extra var로 변경
		// $oAllArgs->$sMemberSmspushFieldName = strip_tags($oAllArgs->sms_send);  // sms 수신 동의를 extra var로 변경
		// $oAllArgs->$sMemberEmailpushFieldName = strip_tags($oAllArgs->email_send);  // 이메일 수신 동의를 extra var로 변경
		// $oAllArgs->$sMemberPostpushFieldName = strip_tags($oAllArgs->addr_send);  // 우편 수신 동의를 extra var로 변경
		// $oAllArgs->$sMemberSponsorpushFieldName = strip_tags($oAllArgs->sponsor);  // 후원사 정보 수신 동의를 extra var로 변경
		// unset($oAllArgs->email_send);
		// unset($oAllArgs->sms_send);
		// unset($oAllArgs->sponsor);
		// unset($oAllArgs->addr_send);

		// // Add extra vars after excluding necessary information from all the requested arguments
		// $oExtraVars = delObjectVars($oAllArgs, $oArgs);
		// $oArgs->extra_vars = serialize($oExtraVars);
		// unset($oExtraVars);

		// // remove whitespace
		// $aCheckInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
		// foreach($aCheckInfos as $val)
		// {
		// 	if(isset($oArgs->{$val}))
		// 		$oArgs->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($oArgs->{$val}));
		// }
		unset($_SESSION['rechecked_password_step']);
		$oMemberRst = $this->_constructMemberInfo('update');
		if(!$oMemberRst->toBool()) 
			return $oMemberRst;
		$oArgs = $oMemberRst->get('oArgs');

		$oMemberController = &getController('member');
		// angemombox_member_extra tbl도 trigger로 등록됨
		$oMemberRst = $oMemberController->updateMember($oArgs, TRUE);
		unset($oMemberController);
		unset($oArgs);
		// unset($oAllArgs);
		return $oMemberRst;
	}
/**
 * add member profile
 */
	private function _addMemberInfo()
	{
		// if(Context::getRequestMethod () == "GET") 
		// 	return new BaseObject(-1, "msg_invalid_request");

		// $oAllArgs = Context::getRequestVars();
		// // Remove unnecessary variables from all the vars
		// unset($oAllArgs->_filter);
		// unset($oAllArgs->error_return_url);
		// unset($oAllArgs->act);
		// unset($oAllArgs->mid);
		// unset($oAllArgs->module);
		// unset($oAllArgs->_center_cnt);
		// unset($oAllArgs->_clubedu_cnt);
		// unset($oAllArgs->i_baby_nm);
		// unset($oAllArgs->i_baby_birth);
		// unset($oAllArgs->i_baby_sex_gb);
		// unset($oAllArgs->_contact_id);
		// unset($oAllArgs->_care_area);
		// unset($oAllArgs->_care_center);
		// unset($oAllArgs->_center_visit_ymd);

		// $oArgs = new stdClass;
		// $oArgs->user_id = strip_tags($oAllArgs->_user_id);
		// $oArgs->user_name = strip_tags($oAllArgs->_user_nm);
		// $oArgs->email_address = strip_tags($oAllArgs->email_id).'@'.strip_tags($oAllArgs->email_server);
		// $oArgs->mobile = strip_tags($oAllArgs->_phone_2);
		// $oArgs->birthday = strip_tags($oAllArgs->_birth);
		// unset($oAllArgs->_user_id);
		// unset($oAllArgs->_user_nm);
		// unset($oAllArgs->email_id);
		// unset($oAllArgs->email_server);
		// unset($oAllArgs->_phone_2);
		// unset($oAllArgs->_birth);

		// if($oAllArgs->password && !(int)$oAllArgs->old_member_srl)  // add member
		// {
		// 	$oMemberModel = &getModel ('member');
		// 	$oConfig = $oMemberModel->getMemberConfig();
		// 	if($oConfig->enable_join != 'Y')
		// 		return $this->stop ('msg_signup_disabled');

		// 	$oArgs->password = strip_tags($oAllArgs->password);  // strip_tags(Context::get('password'));
		// 	unset($oAllArgs->password);
		// 	// check password strength
		// 	if(!$oMemberModel->checkPasswordStrength($oArgs->password, $oConfig->password_strength))
		// 	{
		// 		$message = Context::getLang('about_password_strength');
		// 		return new BaseObject(-1, $message[$oConfig->password_strength]);
		// 	}
		// 	unset($oMemberModel);
		// 	unset($oConfig);
		// }

		// $oAngeclubModel = &getModel('angeclub');
		// $oMemberConnectionRst = $oAngeclubModel->getMemberFieldConnection();
		// unset($oAngeclubModel);
		// if(!$oMemberConnectionRst->toBool())
		// 	return $oMemberConnectionRst;
		// $sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		// $sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		// $sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		// $sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		// $sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		// $sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		// unset($oMemberConnectionRst);

		// // 시작 - 주소를 extra var로 변경
		// // O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		// $oAllArgs->$sMemberAddrFieldName[0] = strip_tags($oAllArgs->_zone_code);
		// $oAllArgs->$sMemberAddrFieldName[1] = strip_tags($oAllArgs->_addr);
		// $oAllArgs->$sMemberAddrFieldName[2] = strip_tags($oAllArgs->_addr_detail);
		// $oAllArgs->$sMemberAddrFieldName[3] = '';
		// unset($oAllArgs->_zone_code);
		// unset($oAllArgs->_addr);
		// unset($oAllArgs->_addr_detail);
		// // 끝 - 주소를 extra var로 변경

		// $oAllArgs->$sMemberGenderFieldName = '여';  // 산후 조리원이므로 반드시 여성  성별을 extra var로 변경
		// $oAllArgs->$sMemberSmspushFieldName = strip_tags($oAllArgs->sms_send);  // sms 수신 동의를 extra var로 변경
		// $oAllArgs->$sMemberEmailpushFieldName = strip_tags($oAllArgs->email_send);  // 이메일 수신 동의를 extra var로 변경
		// $oAllArgs->$sMemberPostpushFieldName = strip_tags($oAllArgs->addr_send);  // 우편 수신 동의를 extra var로 변경
		// $oAllArgs->$sMemberSponsorpushFieldName = strip_tags($oAllArgs->sponsor);  // 후원사 정보 수신 동의를 extra var로 변경
		// unset($oAllArgs->email_send);
		// unset($oAllArgs->sms_send);
		// unset($oAllArgs->sponsor);
		// unset($oAllArgs->addr_send);

		// // Add extra vars after excluding necessary information from all the requested arguments
		// $oExtraVars = delObjectVars($oAllArgs, $oArgs);
		// $oArgs->extra_vars = serialize($oExtraVars);
		// unset($oExtraVars);

		// // remove whitespace
		// $aCheckInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
		// foreach($aCheckInfos as $val)
		// {
		// 	if(isset($oArgs->{$val}))
		// 		$oArgs->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($oArgs->{$val}));
		// }
		$oMemberRst = $this->_constructMemberInfo('insert');
		if(!$oMemberRst->toBool()) 
			return $oMemberRst;
		$oArgs = $oMemberRst->get('oArgs');
		$oMemberController = &getController('member');
		// angemombox_member_extra tbl도 trigger로 등록됨
		$oMemberRst = $oMemberController->insertMember($oArgs);
		unset($oArgs);
		unset($oMemberController);
		// unset($oAllArgs);
        return $oMemberRst;
	}
/**
 * @brief 업무일지 추가 / 변경 메소드
 **/
	public function procAngeclubAddWorkDiary()
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
		$oArgs->member_srl_staff = $oLoggedInfo->member_srl;

		if(!$oArgs->_cc_idx)
			return new BaseObject(-1, '조리원을 선택하세요.');
		if(!$oArgs->_cl_count_regi)
			return new BaseObject(-1, '신규 DB 인원을 입력하세요.');
		//if(!$oArgs->_cl_count_update)
		//	return new BaseObject(-1, '중복 DB 인원을 입력하세요.');
		if(!$oArgs->_cl_category)
			return new BaseObject(-1, '교육 유형을 입력하세요.');
		// if(!$oArgs->_cl_count_center)
		// 	return new BaseObject(-1, '신규 DB 인원을 입력하세요.');
		// if(!$oArgs->_cl_title)
		// 	return new BaseObject(-1, '교육명을 입력하세요.');
		// if(!$oArgs->_cl_memo)
		// 	return new BaseObject(-1, '특이사항을 입력하세요.');

		$oArgs->workdate = preg_replace("/[ :-]/i", "", $oArgs->workdate).'00';  // 2020-04-26 02:04:40 수정
//var_dump($oArgs->workdate);
//exit;		
		if((int)$oArgs->cl_idx > 0) // update
			$oRst = executeQuery('angeclub.updateWorkDiary', $oArgs);
		else  // insert
			$oRst = executeQuery('angeclub.insertWorkDiary', $oArgs);
		if(!$oRst->toBool())
			return $oRst;
		$this->add('bRst', 1);
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
		if(!$oArgs->member_srl_staff)
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