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
		
		$sBody = "안녕하세요. ".$oArgs->scomapnyname."의 문의 내용입니다.".PHP_EOL;
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
		unset($oArgs->_filter);
		unset($oArgs->error_return_url);
		unset($oArgs->act);
		unset($oArgs->mid);
		unset($oArgs->module);
		// 무의미한 변수 제거
		unset($oArgs->_sex_gb);// ["_sex_gb"]=>string(1) "F"
		unset($oArgs->_contact_nm);// ["_contact_nm"]=>string(12) "웹관리자"
		unset($oArgs->_care_area);// ["_care_area"]=>string(6) "강서"
		unset($oArgs->email_id);// ["email_id"]=>string(3) "dfg"
		unset($oArgs->email_server);// ["email_server"]=>string(11) "hanmail.net"

		$oAngemomboxController = &getController ('angemombox');
		$oInArgs = new stdClass();
		
		$nDatalakeDocSrl = (int)Context::get('datalake_doc_srl');
        if(!$nDatalakeDocSrl)  // append new member
        {
			if(!$oArgs->_email)
				return new BaseObject(-1, '이메일을 입력하세요.');
			if(!$oArgs->_user_id)
				return new BaseObject(-1, '아이디를 입력하세요.');
			if(!$oArgs->_phone_2)
				return new BaseObject(-1, '핸드폰 번호를 입력하세요.');
			
			$oAngeclubModel = &getModel('angeclub');
			$oModuleInfo = $oAngeclubModel->getModuleConfig();
			unset($oAngeclubModel);
			
            // ["_user_nm"]=>string(12) "엄마이름"
            // ["_birth"]=>string(6) "19801212"
            $oRst = $this->_addMemberInfo($oModuleInfo->member_addr_field_name);
            if(!$oRst->toBool()) 
                return $oRst;
            $nMemberSrl = $oRst->get('member_srl');
			unset($oModuleInfo);
			unset($oRst);
			unset($oArgs->_email);  // ["_email"]=> string(15) "dfg@hanmail.net"
			unset($oArgs->_user_id);  // ["_user_id"]=>string(9) "아이디"
			// end - member info registration

			// begin - nurse performance registration
			// $oInArgs = new stdClass();
			$oInArgs->cc_idx = $oArgs->_care_center;  // ["_care_center"]=>string(30) "637"  // replace cc_name to cc_idx
			// $oInArgs->cu_id = $oArgs->_contact_id;  // ["_contact_id"]=>string(5) "hya1021"
			$oInArgs->member_srl_staff = $oLoggedInfo->member_srl;
			$oInArgs->member_srl_parent = $nMemberSrl;
			$oInArgs->center_visit_cnt = $oArgs->_center_cnt;  // ["_center_cnt"]=>string(1) "1"  // 해당 조리원 방문 횟수
			$oInArgs->education_cnt = 1;
			if($oArgs->_center_visit_ymd)
				$oInArgs->regdate = $oArgs->_center_visit_ymd.'000001';  // ["_center_visit_ymd"]=>string(6) "20221203"
			if($nExistingMemberSrl)  // 기존 회원 수정이면 표시함
				$oInArgs->is_existing_parent_member = 'Y';
				
			$oRst = executeQuery('angeclub.insertClubRegistration', $oInArgs);
			unset($oInArgs);
			if(!$oRst->toBool()) 
				return $oRst;
			unset($oRst);
			$oDB = DB::getInstance();
			$nAngeclubRegistrationLogSrl = $oDB->db_insert_id();
			unset($oDB);
			unset($oArgs->_contact_id);
			unset($oArgs->_care_center);
			unset($oArgs->_center_cnt);
			// end - nurse performance registration

			// begin - push into angemombox data lake
			$oInArgs = new stdClass();
			$oAngeclubModel = &getModel ('angeclub');
			$oConfig = $oAngeclubModel->getModuleConfig();
			unset($oAngeclubModel);
			$oModuleModel = &getModel ('module');
			$oAngemomboxDataLakeMidInfo = $oModuleModel->getModuleInfoByMid($oConfig->connected_mombox_mid);
			unset($oConfig);
			$oInArgs->module_srl = $oAngemomboxDataLakeMidInfo->module_srl;
			unset($oAngemomboxDataLakeMidInfo);
			$oInArgs->yr_mo = substr($oArgs->_center_visit_ymd, 0, 6);  //date('Ym');
			$oInArgs->angeclub_registration_log_srl = $nAngeclubRegistrationLogSrl;

			// construct mom's info
			$oInArgs->parent_member_srl = $nMemberSrl;  // mom's member srl
			$oInArgs->parent_birthday = $oArgs->_birth;
			$oInArgs->parent_gender = 'F';  // 산후 조리원이므로 반드시 여성
			$oInArgs->parent_pregnant = 'N';  // 산후 조리원이므로 반드시 출산 후
			$oInArgs->mobile = $oArgs->_phone_2;
			
			$oInArgs->postcode = strip_tags($oArgs->_zone_code);  // ["_zone_code"]=>string(5) "04078"
			$oInArgs->addr = strip_tags($oArgs->_addr);  // ["_addr"]=>string(44) "서울 마포구 독막로 126-1 (창전동)"
			$oInArgs->addr_detail = strip_tags($oArgs->_addr_detail);  // ["_addr_detail"]=>string(13) "상세 주소"
			$oInArgs->addr_extra = '';

			// construct push agreement
			$oInArgs->email_push = $oArgs->email_send == 'Y' ? 'Y' : 'N';  // ["email_send"]=>string(1) "Y"
			$oInArgs->sms_push = $oArgs->sms_send == 'Y' ? 'Y' : 'N';  // ["sms_send"]=>string(1) "Y"
			$oInArgs->post_push = $oArgs->addr_send == 'Y' ? 'Y' : 'N';  // ["addr_send"]=>string(1) "Y"
			$oInArgs->sponsor_push = $oArgs->sponsor == 'Y' ? 'Y' : 'N';  // ["sponsor"]=>string(1) "Y"

			// construct baby info
			$oInArgs->baby_birth_name = strip_tags($oArgs->i_baby_nm);  // ["i_baby_nm"]=>string(12) "아이이름"
			$oInArgs->baby_gender = $oArgs->i_baby_sex_gb;  // ["i_baby_sex_gb"]=>string(1) "T"
			$oInArgs->baby_birthday = $oArgs->i_baby_birth;   // ["i_baby_birth"]=>string(6) "221215"

			$oInArgs->is_mobile = Mobile::isMobileCheckByAgent() ? 'Y' : 'N';
			$oInArgs->user_agent = $_SERVER['HTTP_USER_AGENT'];
			$oRst = $oAngemomboxController->insertDataLake($oInArgs);
// var_dump($oInArgs);
			if(!$oRst->toBool())
				return $oRst;
			unset($oRst);
			// end - push into angemombox data lake
        }
        else  // update existing datalake log
        {
			// construct push agreement
			$oInArgs->email_push = $oArgs->email_send == 'Y' ? 'Y' : 'N';  // ["email_send"]=>string(1) "Y"
			$oInArgs->sms_push = $oArgs->sms_send == 'Y' ? 'Y' : 'N';  // ["sms_send"]=>string(1) "Y"
			$oInArgs->post_push = $oArgs->addr_send == 'Y' ? 'Y' : 'N';  // ["addr_send"]=>string(1) "Y"
			$oInArgs->sponsor_push = $oArgs->sponsor == 'Y' ? 'Y' : 'N';  // ["sponsor"]=>string(1) "Y"

			$oInArgs->datalake_doc_srl = $nDatalakeDocSrl;
			$oRst = $oAngemomboxController->updateDataLake($oInArgs);
			if(!$oRst->toBool())
				return $oRst;
			unset($oRst);
        } 
		unset($oInArgs);
		unset($oAngemomboxController);  
		$this->add('bRst', 1);
	}
/**
 * add member profile
 */
	private function _addMemberInfo($sMemberAddrFieldName)
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
		$oArgs->user_id = strip_tags(Context::get('_user_id'));
		$oArgs->email_address = strip_tags(Context::get('_email'));
		$oArgs->mobile = strip_tags(Context::get('_phone_2'));
		$oArgs->password = strip_tags(Context::get('password'));
		$oArgs->user_name = strip_tags(Context::get('_user_nm'));

		$sMomBirth = strip_tags(Context::get('_birth'));  
		$oArgs->birthday = strip_tags(Context::get('_birth'));  // $this->_completeBirthday($sMomBirth);
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
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		$oAllArgs->$sMemberAddrFieldName[0] = strip_tags($oAllArgs->_zone_code);
		$oAllArgs->$sMemberAddrFieldName[1] = strip_tags($oAllArgs->_addr);
		$oAllArgs->$sMemberAddrFieldName[2] = strip_tags($oAllArgs->_addr_detail);
		$oAllArgs->$sMemberAddrFieldName[3] = '';
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
		$oRst = $oMemberController->insertMember($oArgs);
        return $oRst;
	}
/**
 * Edit member profile
 */
    private function _modifyMemberInfo($oMemberInfo)
    {
        unset($_SESSION['rechecked_password_step']);

        // never touch unrelated member info
        unset($oMemberInfo->referral);
        unset($oMemberInfo->password);
        unset($oMemberInfo->find_account_question);
        unset($oMemberInfo->find_account_answer);
        unset($oMemberInfo->user_name);
        unset($oMemberInfo->user_id);
        unset($oMemberInfo->nick_name);
        unset($oMemberInfo->description);
        unset($oMemberInfo->birthday);
        unset($oMemberInfo->group_srl_list);
        unset($oMemberInfo->email_id);
        unset($oMemberInfo->email_host);
        unset($oMemberInfo->denied);
        unset($oMemberInfo->limit_date);
        unset($oMemberInfo->regdate);
        unset($oMemberInfo->last_login);
        unset($oMemberInfo->change_password_date);
        unset($oMemberInfo->list_order);
        unset($oMemberInfo->profile_image);
        unset($oMemberInfo->image_name);
        unset($oMemberInfo->image_mark);
        unset($oMemberInfo->group_list);
        // never touch unrelated member info

        $oInParams = Context::getRequestVars();
        $oMemberModel = &getModel('member');
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
        $args->member_srl = $oMemberInfo->member_srl;
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
		if(!$oArgs->_cl_count_update)
			return new BaseObject(-1, '중복장 DB 인원을 입력하세요.');
		if(!$oArgs->_cl_category)
			return new BaseObject(-1, '교육 유형을 입력하세요.');
		// if(!$oArgs->_cl_count_center)
		// 	return new BaseObject(-1, '신규 DB 인원을 입력하세요.');
		// if(!$oArgs->_cl_title)
		// 	return new BaseObject(-1, '교육명을 입력하세요.');
		// if(!$oArgs->_cl_memo)
		// 	return new BaseObject(-1, '특이사항을 입력하세요.');

		$oArgs->workdate = preg_replace("/[ :-]/i", "", $oArgs->workdate);  // 2020-04-26 02:04:40 수정
		
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