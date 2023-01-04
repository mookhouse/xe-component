<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxAdminController
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxAdminController
**/ 

class angemomboxAdminController extends angemombox
{
	/**
	 * @brief initialization
	 **/
	function init() 
	{
	}
/**
 * @brief 
 */
	public function procAngemomboxAdminAddWinner()
	{
		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');
		$sYrMo = (int)Context::get('yr_mo');
		if(!$sYrMo) 
			return new BaseObject(-1, 'msg_invalid_yr_mo');

		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->yr_mo = $sYrMo;
		$oRst = executeQueryArray('angemombox.getAdminApplicantListByModuleSrlYrMo', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');

		$aDropouts = [];
		if(count($oRst->data))
		{
			foreach($oRst->data as $nIdx=>$oRec)
			{
				if($oRec->is_accepted != 'Y')
					$aDropouts[$oRec->doc_srl] = 1;
			}
		}
		$nAdditionalWinnerCnt = (int)Context::get('additional_winner_cnt');
		if($nAdditionalWinnerCnt)
		{
			$nDropouts = count($aDropouts);
			$nAdditionalWinnerCnt = $nAdditionalWinnerCnt > $nDropouts ? $nDropouts : $nAdditionalWinnerCnt;
// var_dump($nAdditionalWinnerCnt );
// var_dump($aDropouts );
			
			$aRandWinner = array_rand($aDropouts, $nAdditionalWinnerCnt);
			$sTypeName = gettype($aRandWinner);
			$oArgs = new stdClass();
			if($sTypeName == 'integer')
			{
				$oArgs->module_srl = $nModuleSrl;
				$oArgs->doc_srl = $aRandWinner;
				$oArgs->is_accepted = 'Y';
				executeQuery('angemombox.updateAdminApproval', $oArgs);
			}
			elseif($sTypeName == 'array')
			{
				foreach($aRandWinner as $nIdx=>$nDocSrl)
				{
					$oArgs->module_srl = $nModuleSrl;
					$oArgs->doc_srl = $nDocSrl;
					$oArgs->is_accepted = 'Y';
					executeQuery('angemombox.updateAdminApproval', $oArgs);
				}
			}
			unset($oArgs);
		}
		$this->add('nRst', 1);
	}
/**
 * @brief delete designated module
 */
	public function procAngemomboxAdminDelete()
	{
		$nModuleSrl = Context::get('module_srl');
		$oModuleController = getController('module');
		$oRst = $oModuleController->deleteModule($nModuleSrl);
		if(!$oRst->toBool()) 
			return $oRst;
        unset($oRst);
		$this->add('module','page');
		$this->add('page',Context::get('page'));
		$this->setMessage('success_deleted');
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAngemomboxAdminIndex');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief 
 */
	public function procAngemomboxAdminDeleteDoc()
	{
		$nModuleSrl = Context::get('module_srl');
		$nDocSrls = Context::get('doc_srls');
		// delete docs belongint to the module
		$oRst = $this->_deleteSingleDocsByModule($nModuleSrl, $nDocSrls);
		if(!$oRst->toBool())
			return $oRst;
		unset($oRst);
		$this->add('module','page');
		$this->add('page',Context::get('page'));
		$this->setMessage('success_deleted');
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAngemomboxAdminApplicantsList', 'module_srl', $module_srl, 'page',Context::get('page'));
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief
 */
	private function _deleteSingleDocsByModule($nModuleSrl, $aDocSrl)
	{
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		foreach($aDocSrl as $key => $val)
		{
			$oArgs->doc_srl = $val;
			$oRst = executeQuery('angemombox.updateSingleDocDeleted', $oArgs ); // 삭제 표시
			if(!$oRst->toBool())
				return new BaseObject(-1, 'msg_error_angemombox_db_query');
		}
		return new BaseObject(0);
	}
/**
 * @brief module module
 */
	public function procAngemomboxAdminUpdate()
	{
		$this->procAngemomboxAdminInsert();
	}
/**
 * @brief add module
 */
	public function procAngemomboxAdminInsert()
	{
		$oArgs = Context::getRequestVars();
		$oArgs->module = 'angemombox';
		$oArgs->mid = $oArgs->page_name;	//because if mid is empty in context, set start page mid
		unset($oArgs->page_name);
		if($oArgs->use_mobile != 'Y') 
			$oArgs->use_mobile = '';
		$oRst = $this->_saveConfigByMid($oArgs);
		unset($oArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		$this->add("page", Context::get('page'));
		$this->add('module_srl', $oRst->get('module_srl'));
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'module_srl', $oRst->get('module_srl'), 'act', 'dispAngemomboxAdminInfo');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief 
 */
	public function procAngemomboxAdminApproveApplicant()
	{
		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl)
			return new BaseObject( -1, 'msg_error_invalid_module_srl');
		$nDocSrl = (int)Context::get('doc_srl');
		if(!$nDocSrl)
			return new BaseObject( -1, 'msg_error_invalid_doc_srl');
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->doc_srl = $nDocSrl;
		$oArgs->is_accepted = Context::get('approve');
		$oRst = executeQuery('angemombox.updateAdminApproval', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'module_srl', $nModuleSrl,'doc_srl', $nDocSrl, 'act', 'dispAngemomboxAdminDocDetail');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief add module
 */
	private function _saveConfigByMid($oArgs)
	{
		$oInArgs = $oArgs;
		if($oInArgs->module_srl)
		{
			$oModuleModel = getModel('module');
			$oModuleInfo = $oModuleModel->getModuleInfoByModuleSrl($oInArgs->module_srl);
			unset($oModuleInfo->is_skin_fix); // 기본 스킨 고정을 해제함
			unset($oModuleInfo->is_mskin_fix); // 기본 스킨 고정을 해제함
			if($oModuleInfo->module_srl != $oInArgs->module_srl)
				unset($oInArgs->module_srl);
			else
			{
				foreach($oInArgs as $key=>$val)
					$oModuleInfo->{$key} = $val;
				$oInArgs = $oModuleInfo;
			}
		}
		$oModuleController = getController('module');
		if(!$oInArgs->module_srl)
			$oRst = $oModuleController->insertModule($oInArgs);  // Insert
		else
			$oRst = $oModuleController->updateModule($oInArgs);  // update
		unset($oInArgs);
        return $oRst;
	}
/**
 * @brief 
 **/
public function procAngemomboxAdminConfig()
{
// 	$oArgs = Context::getRequestVars();
// var_dump($oArgs);
// 	exit;

	$oArgs = new stdClass();
	$sMemberAddrFieldName = Context::get('member_addr_field_name');
	if(strlen($sMemberAddrFieldName))
		$oArgs->member_addr_field_name = $sMemberAddrFieldName;
	$sMemberGenderFieldName = Context::get('member_gender_field_name');
	if(strlen($sMemberGenderFieldName))
		$oArgs->member_gender_field_name = $sMemberGenderFieldName;
	$sMemberSmspushFieldName = Context::get('member_sms_push_field_name');
	if(strlen($sMemberSmspushFieldName))
		$oArgs->member_sms_push_field_name = $sMemberSmspushFieldName;
	$sMemberEmailpushFieldName = Context::get('member_email_push_field_name');
	if(strlen($sMemberEmailpushFieldName))
		$oArgs->member_email_push_field_name = $sMemberEmailpushFieldName;
	$sMemberPostpushFieldName = Context::get('member_post_push_field_name');
	if(strlen($sMemberPostpushFieldName))
		$oArgs->member_post_push_field_name = $sMemberPostpushFieldName;
	$sMemberSponsorpushFieldName = Context::get('member_sponsor_push_field_name');
	if(strlen($sMemberSponsorpushFieldName))
		$oArgs->member_sponsor_push_field_name = $sMemberSponsorpushFieldName;

	$oRst = $this->_saveModuleConfig($oArgs);
	if(!$oRst->toBool())
		$this->setMessage('error_occured');
	else
		$this->setMessage('success_updated');
	$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispAngemomboxAdminConfig'));
}
/**
 * @brief arrange and save module config
 **/
	private function _saveModuleConfig($oArgs)
	{
		$oModuleControll = getController('module');
		return $oModuleControll->insertModuleConfig('angemombox', $oArgs);
	}
/**
 * @brief mask multibyte string
 * param 원본문자열, 마스킹하지 않는 전단부 글자수, 마스킹하지 않는 후단부 글자수, 마스킹 마크 최대 표시수, 마스킹마크
 * echo _maskMbString('abc12234pro', 3, 2); => abc******ro
 */	
	private function _maskMbString($str, $len1, $len2=0, $limit=0, $mark='*')
	{
		$arr_str = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
		$str_len = count($arr_str);

		$len1 = abs($len1);
		$len2 = abs($len2);
		if($str_len <= ($len1 + $len2))
			return $str;

		$str_head = '';
		$str_body = '';
		$str_tail = '';

		$str_head = join('', array_slice($arr_str, 0, $len1));
		if($len2 > 0)
			$str_tail = join('', array_slice($arr_str, $len2 * -1));

		$arr_body = array_slice($arr_str, $len1, ($str_len - $len1 - $len2));

		if(!empty($arr_body)) 
		{
			$len_body = count($arr_body);
			$limit = abs($limit);

			if($limit > 0 && $len_body > $limit)
				$len_body = $limit;

			$str_body = str_pad('', $len_body, $mark);
		}

		return $str_head.$str_body.$str_tail;
	}
/**
 * @brief 
 */	
	function procAngemomboxAdminCSVDownloadSms() 
	{
		/*
		미즈톡톡(가입일) - 오늘날짜 기준으로, 임산부이고 새로 회원가입한 사람

		SELECT c.USER_NM, c.PHONE_2, c.ADDR, c.ADDR_DETAIL, c.BABY_BIRTH_DT, a.BABY_BIRTH, c.REG_DT
		FROM COM_USER c LEFT JOIN ANGE_USER_BABY a ON c.USER_ID = a.USER_ID
		WHERE (c.BABY_BIRTH_DT > 20221128 AND c.BABY_BIRTH_DT < 20241231 OR a.BABY_BIRTH > 20221128 AND a.BABY_BIRTH < 20241231) and (REG_DT between '2022-11-14' AND '2022-11-21')
		GROUP BY c.USER_ID

		화성 / 동탄지역 4~12개월 

		SELECT c.USER_NM, c.PHONE_2, c.ADDR, c.ADDR_DETAIL, c.BABY_BIRTH_DT, a.BABY_BIRTH, c.REG_DT
		FROM COM_USER c LEFT JOIN ANGE_USER_BABY a ON c.USER_ID = a.USER_ID
		WHERE (c.ADDR LIKE '%화성%' OR c.ADDR LIKE '%수원%') AND (c.BABY_BIRTH_DT > 20211101 AND c.BABY_BIRTH_DT < 20220810 OR a.BABY_BIRTH > 20211101 AND a.BABY_BIRTH < 20220810)
		GROUP BY c.USER_ID
		*/
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(720);  // sec

		$sBabyBirthBeginYyyymmdd = Context::get('babybirth_begin_yyyymmdd');
		if(!$sBabyBirthBeginYyyymmdd) 
			return new BaseObject(-1, 'msg_invalid_babybirth_begin_yyyymmdd');
		$sBabyBirthEndYyyymmdd = Context::get('babybirth_end_yyyymmdd');
		if(!$sBabyBirthEndYyyymmdd) 
			return new BaseObject(-1, 'msg_invalid_babybirth_end_yyyymmdd');

		$sRegistBeginYyyymmdd = Context::get('regist_begin_yyyymmdd');
		$sRegistEndYyyymmdd = Context::get('regist_end_yyyymmdd');
		$sCsvCityName = Context::get('csv_city_name');
		
		$aUniqMemberList = [];
		$oArgs = new stdClass();
		$oArgs->babybirth_begin_date = $sBabyBirthBeginYyyymmdd;
		$oArgs->babybirth_end_date = $sBabyBirthEndYyyymmdd;
		
		// 회원 가입일 기준으로 고유 배열 작성
		if($sRegistBeginYyyymmdd && $sRegistEndYyyymmdd)
		{
			$oArgs->regist_begin_date = $sRegistBeginYyyymmdd.'000000';
			$oArgs->regist_end_date = $sRegistEndYyyymmdd.'235959';
			$oRst = executeQueryArray('angemombox.getAdminDocSMSCsvDownloadByRegdate', $oArgs);
			if(!$oRst->toBool())
				return $oRst;
			foreach($oRst->data as $_ => $oSingleSms)
			{
				$oRecepientInfo = new stdClass();
				$oRecepientInfo->user_name = $oSingleSms->user_name;
				$oRecepientInfo->mobile = $oSingleSms->mobile;
				$oRecepientInfo->gender = $oSingleSms->gender;
				$oRecepientInfo->baby_birthday = $oSingleSms->baby_birthday;
				$oRecepientInfo->addr = $oSingleSms->addr;
				$oRecepientInfo->sms_push = $oSingleSms->sms_push;
				$oRecepientInfo->regdate = $oSingleSms->regdate;
				$aUniqMemberList[$oSingleSms->member_srl] = $oRecepientInfo;
			}
			unset($oSingleSms);
			unset($oRst);
			unset($oArgs->regist_begin_date);
			unset($oArgs->regist_end_date);
		}

		// 회원 가입일 기준으로 고유 배열 작성
		if($sCsvCityName)
		{
			$aCity = explode(',', $sCsvCityName);
			foreach($aCity as $_=>$sCity)
			{
				if(strlen($sCity) < 2)
					continue;

				$oArgs->city_name = '%'.$sCity.'%';
				$oRst = executeQueryArray('angemombox.getAdminDocSMSCsvDownloadByCity', $oArgs);
				if(!$oRst->toBool())
					return $oRst;
				foreach($oRst->data as $_ => $oSingleSms)
				{
					$oRecepientInfo = new stdClass();
					$oRecepientInfo->user_name = $oSingleSms->user_name;
					$oRecepientInfo->mobile = $oSingleSms->mobile;
					$oRecepientInfo->gender = $oSingleSms->gender;
					$oRecepientInfo->baby_birthday = $oSingleSms->baby_birthday;
					$oRecepientInfo->addr = $oSingleSms->addr;
					$oRecepientInfo->sms_push = $oSingleSms->sms_push;
					$oRecepientInfo->regdate = $oSingleSms->regdate;
					$aUniqMemberList[$oSingleSms->member_srl] = $oRecepientInfo;
				}
			}
			unset($oSingleSms);
			unset($oRst);
			//unset($oArgs->city_name);
		}
		unset($oArgs);		

		//header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'Content-Type: Application/octet-stream; charset=UTF-8' );
		header( "Content-Disposition: attachment; filename=\"".date('Ymd')."_SMS-아기생일(".$sBeginYyyymmdd."-".$sEndYyyymmdd.")-회원가입(".$sRegistBeginYyyymmdd."-".$sRegistEndYyyymmdd.")-도시(".$sCsvCityName.").csv\"");
		echo chr( hexdec( 'EF' ) );
		echo chr( hexdec( 'BB' ) );
		echo chr( hexdec( 'BF' ) );
		
		// 기본 컬럼 제목 설정 시작
		$oDataConfig = Array('parent_name','parent_phone','parent_gender','baby_birthday','addr','sms_push','regdate');

		foreach( $oDataConfig as $key => $val )
			echo "\"".$val."\",";
		// 기본 컬럼 제목 설정 끝	
		
		echo "\r\n";
		//echo '<BR>';

		foreach($aUniqMemberList as $nMemberSrl => $oSingleRecepient)
		{
			echo "\"".$oSingleRecepient->user_name."\",";
			echo "=\"".$oSingleRecepient->mobile."\",";
			echo "\"".$oSingleRecepient->gender."\",";
			echo "\"".zdate($oSingleRecepient->baby_birthday, 'Y-m-d')."\",";
			echo "\"".$oSingleRecepient->addr."\",";
			echo "\"".$oSingleRecepient->sms_push."\",";
			echo "\"".zdate($oSingleRecepient->regdate, 'Y-m-d H:i:s')."\",";
			echo "\r\n";
			//echo '<BR>';
		}
		exit(0);
	}
/**
 * @brief 
 */	
	function procAngemomboxAdminCSVDownloadByModule() 
	{
		$nModuleSrl = Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');
		$oModuleModel = &getModel('module');
		$oModuleInfo = $oModuleModel->getModuleInfoByModuleSrl($nModuleSrl);
		if(gettype($oModuleInfo)!='object')
			return new BaseObject(-1, 'msg_invalid_module_srl');
		$sModuleTitle =  mb_substr($oModuleInfo->browser_title, 0, 5);
		unset($oModuleInfo);

		$sBeginYyyymmdd = Context::get('period_begin');
		if(!$sBeginYyyymmdd) 
			return new BaseObject(-1, 'msg_invalid_begin_yyyymmdd');
		$sEndYyyymmdd = Context::get('period_end');
		if(!$sEndYyyymmdd) 
			return new BaseObject(-1, 'msg_invalid_end_yyyymmdd');
		
		//header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'Content-Type: Application/octet-stream; charset=UTF-8' );
		header( "Content-Disposition: attachment; filename=\"".date('Ymd')."_".$sModuleTitle."-".$sBeginYyyymmdd."-".$sEndYyyymmdd.".csv\"");
		echo chr( hexdec( 'EF' ) );
		echo chr( hexdec( 'BB' ) );
		echo chr( hexdec( 'BF' ) );
		
		// 기본 컬럼 제목 설정 시작
		$oDataConfig = Array( 'doc_srl','module_title','yr_mo','member_id','applicant_name','applicant_phone','parent_gender','parent_pregnant','baby_birthday',
								'postcode','addr','content', 'email_push', 'sms_push', 'post_push', 'sponsor_push', 'init_referral','utm_source', 'utm_medium', 'utm_campaign', 'utm_term','is_mobile','is_accepted','regdate');

		foreach( $oDataConfig as $key => $val )
			echo "\"".$val."\",";
		// 기본 컬럼 제목 설정 끝	
		
		echo "\r\n";
		//echo '<BR>';
		
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->begin_date = $sBeginYyyymmdd.'000000';
		$oArgs->end_date = $sEndYyyymmdd.'235959';
		$oArgs->is_deleted_doc = 0;
		//$args->is_accepted = 'Y';
		$oArgs->list_count = 99999;
		$oRst = executeQueryArray('angemombox.getAdminDocByModuleCsvDownload', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return $oRst;

		$oMemberModel = &getModel('member');
		foreach($oRst->data as $_ => $oSingleApplicant)
		{
			echo "\"".$oSingleApplicant->doc_srl."\",";
			echo "\"".$sModuleTitle."\",";
			echo "\"".$oSingleApplicant->yr_mo."\",";
			// member info
			$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl((int)$oSingleApplicant->member_srl);
			if($oMemberInfo)
			{
				echo "\"".$oMemberInfo->user_id."\",";
				echo "\"".$oMemberInfo->user_name."\",";
			}
			else
			{
				echo "\"탈퇴\",";
				echo "\"탈퇴\",";
			}
			unset($oMemberInfo);
			echo "=\"".$oSingleApplicant->mobile."\",";
			echo "\"".$oSingleApplicant->parent_gender."\",";
			echo "\"".$oSingleApplicant->parent_pregnant."\",";
			echo "\"".zdate($oSingleApplicant->baby_birthday, 'Y-m-d')."\",";
			echo "\"".$oSingleApplicant->postcode."\",";
			echo "\"".$oSingleApplicant->addr.' '.$oSingleApplicant->addr_detail.' '.$oSingleApplicant->addr_extra."\",";

			$sStrippedContent = str_replace('"', '', $oSingleApplicant->content);
			$sStrippedContent = str_replace("'", '', $sStrippedContent);
			echo "\"".$sStrippedContent."\",";
			
			echo "\"".$oSingleApplicant->email_push."\",";
			echo "\"".$oSingleApplicant->sms_push."\",";
			echo "\"".$oSingleApplicant->post_push."\",";
			echo "\"".$oSingleApplicant->sponsor_push."\",";
			echo "\"".$oSingleApplicant->init_referral."\",";
			echo "\"".$oSingleApplicant->utm_source."\",";
			echo "\"".$oSingleApplicant->utm_medium."\",";
			echo "\"".$oSingleApplicant->utm_campaign."\",";
			echo "\"".$oSingleApplicant->utm_term."\",";
			echo "\"".$oSingleApplicant->is_mobile."\",";
			echo "\"".$oSingleApplicant->is_accepted."\",";
			echo "\"".zdate($oSingleApplicant->regdate, 'Y-m-d H:i:s')."\",";
			
			echo "\r\n";
			//echo '<BR>';
		}
		unset($oMemberModel);
		exit(0);
	}
/**
 * @brief 
 **/
	// public function procAngemomboxAdminInsertConfig()
	// {
	// 	$sGaV3TrackingId = Context::get('ga_v3_tracking_id');
	// 	if(strlen($sGaV3TrackingId))
	// 		$oArgs->ga_v3_tracking_id = $sGaV3TrackingId;
	// 	$oRst = $this->_saveModuleConfig($oArgs);
	// 	if(!$oRst->toBool())
	// 		$this->setMessage('error_occured');
	// 	else
	// 		$this->setMessage('success_updated');
	// 	$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispAngemomboxAdminConfig'));
	// }
/**
 * @brief arrange and save module config
 **/
	// private function _saveModuleConfig($oArgs)
	// {
	// 	//$oAngemomboxAdminModel = &getAdminModel('angemombox');
	// 	//$oConfig = $oSvdocsAdminModel->getModuleConfig();
	// 	//foreach( $oArgs as $key=>$val)
	// 	//	$oConfig->{$key} = $val;
	// 	$oModuleControll = getController('module');
	// 	$oRst = $oModuleControll->insertModuleConfig('angemombox', $oArgs);
	// 	return $oRst;
	// }
}