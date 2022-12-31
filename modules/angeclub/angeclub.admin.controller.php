<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubAdminController
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubAdminController
**/ 
class angeclubAdminController extends angeclub
{
	const PREGNANT = 0;  // for data migration, to be deleted
	const POSTNATAL = 1;    // for data migration, to be deleted

	/**
	 * @brief initialization
	 **/
	function init() 
	{
	}
/**
 * @brief migrate old table
 */
	public function procAngeclubAdminMigrate()
	{
		$sCommand = strip_tags(Context::get('command'));
		switch($sCommand)
		{
			case 'register_retired_into_xe_member':
				echo 'register_retired_into_xe_member has been started<BR>';
				$this->_registerRetiredIntoXeMember();
				break;
			case 'retrieve_duplicated_mobile':
				echo 'retrieve_duplicated_mobile has been started<BR>';
				$this->_retreiveDuplicatedMobileInfo();
				break;
			case 'cleanup_com_user_by_mobile':
				echo 'retrieve_duplicated_mobile has been started<BR>';
				$this->_buildupCleanupComUserList();
				break;
			case 'migrate_com_user_into_xe_member':
				echo 'migrate_com_user_into_xe_member has been started<BR>';
				$this->_migrateComUserIntoXeMember();
				break;
			case 'migrate_com_user_into_xe_modules':
				echo 'migrate_com_user_into_xe_modules has been started<BR>';
				$this->_migrateComUserIntoXeModules();
				break;
			case 'translate_cu_id':
				echo 'translate_cu_id has been started<BR>';
				$this->_translateCuId();
				break;
			case 'parse_hompy_mombox_apply':
				echo 'parse_hompy_mombox_apply has been started<BR>';
				$this->_parseHompyMomboxApply();
				break;
			case 'translate_hompy_mombox_apply':
				echo 'translate_hompy_mombox_apply has been started<BR>';
				$this->_translatetHompyMomboxApply();
				break;
			case 'translate_baby_list':
				echo 'translate_baby_list has been started<BR>';
				$this->_translatetBabyList();
				break;
		}
		exit;
	}
/**
 * @brief 구홈피 ange_user_baby tbl을 angemombox_baby_list tbl로 이전
 */
	private function _translatetBabyList()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(720);  // sec
		
		$sSeqLogFilePath = './files/angeclub/9xe_translate_baby_list_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;

		$oArgs = new stdClass();
		$oArgs->page = $nCurPage;
		// $oArgs->NO = 753763;
		
		$oArgs->list_count = 40000;
		$oRst = executeQueryArray('angeclub.getTmpAdminComUserCleanedPaginationDesc', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';

		$oMemberModel = &getModel('member');
		$oAngeclubModel = &getModel('angeclub');
		foreach($oRst->data as $nIdx=>$oComUser)
		{
			echo 'search NO: '.$oComUser->NO.' USER_ID: '.$oComUser->USER_ID.'<BR>';
			$oBabyArgs = new stdClass();
			$oBabyArgs->USER_ID = $oComUser->USER_ID;
			$oBabyRst = executeQueryArray('angeclub.getTmpAdminAngeUserBabyByUserId', $oBabyArgs);
			if(count($oBabyRst->data)==0)  // 변형 전 아이디로 아이 정보 기록한 경우
			{
				echo 'alias querying<BR>';
				// exit;
				$oAliasArgs = new stdClass();
				$oAliasArgs->USER_ID_DEST = $oComUser->USER_ID;
				$oAliasRst = executeQueryArray('angeclub.getTmpAdminComUserTransferredByUserIdDest', $oAliasArgs);
				unset($oAliasArgs);
				if(count($oAliasRst->data))  // 아이디 변경 내역을 찾으면
				{
				// 	var_dump($oAliasRst->data[0]->USER_ID_SOURCE);
				// echo '<BR>';
					$oBabyArgs->USER_ID = $oAliasRst->data[0]->USER_ID_SOURCE;
					$oBabyRst = executeQueryArray('angeclub.getTmpAdminAngeUserBabyByUserId', $oBabyArgs);
				}
			}
			// 구홈피 ange_user_baby tbl의 아이디가 뭐던 간에 회원 정보는 변형 후 아이디로 조회해야 함
			$oXeMemberInfo = $oMemberModel->getMemberInfoByUserID($oComUser->USER_ID);  

			unset($oBabyArgs);
			if(count($oBabyRst->data))  // 아이 정보 최종 발견하면
			{
				foreach($oBabyRst->data as $_ => $oSingleBaby)
				{
					$oBabyInsertArgs = new stdClass();
					$oBabyInsertArgs->member_srl = $oXeMemberInfo->member_srl;
					$oBabyInsertArgs->name = $oSingleBaby->BABY_NM;
					$oBabyInsertArgs->birthday = $oSingleBaby->BABY_BIRTH;
					$oBabyInsertArgs->gender = $oSingleBaby->BABY_SEX_GB;
					if($oSingleBaby->CARE_CENTER)
					{
						$oBabyCenter = new stdClass();
						$oBabyCenter->CARE_CENTER = $oSingleBaby->CARE_CENTER;
						$oBabyCenter->CARE_AREA = $oComUser->CARE_AREA;
						$oBabyInsertArgs->cc_idx = $this->_cleanupCenterName($oAngeclubModel, $oBabyCenter);
						unset($oBabyCenter);
					}
					// var_Dump($oBabyInsertArgs);
					// echo '<BR>';
					$oBabyInsertRst = executeQueryArray('angemombox.insertBaby', $oBabyInsertArgs);
					// var_Dump($oBabyInsertRst);
					// echo '<BR>';
					if(!$oBabyInsertRst->toBool())
					{
						var_Dump($oBabyInsertRst);
						echo '<BR>';
						var_Dump($oBabyInsertArgs);
						exit;
					}
				}
			}
		}
		unset($oMemberModel);
		unset($oAngeclubModel);
		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!';
	}
/**
 * @brief 구홈피 맘박스 신청 내역을 club_log, club_center tbl로 이전
 */
	private function _translatetHompyMomboxApply()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(720);  // sec
		
		$sSeqLogFilePath = './files/angeclub/8xe_translate_hompy_mombox_apply_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;

		$oArgs = new stdClass();
		$oArgs->page = $nCurPage;
		// $oArgs->NO = 753763;

		// begin - adm_history_join tbl 구성
		$sSerializedFilePath = './files/angeclub/serialize_hompy_mombox_apply.txt';
		$sMomboxRegistration = FileHandler::readFile($sSerializedFilePath);
		$aMomboxRegistration = unserialize($sMomboxRegistration);
		// var_dump($aMomboxRegistration);
		// exit;
		// end - adm_history_join tbl 구성

		// begin - angemombox_data_lake에 입력할 module_srl 설정
		$oModuleModel = &getModel('module');
		$oMomboxBeforeModuleInfo = $oModuleModel->getModuleInfoByMid('mombox_before_apply');
		$oMomboxAfterModuleInfo = $oModuleModel->getModuleInfoByMid('mombox_after_apply');
		$nMomboxBeforeModuleSrl = $oMomboxBeforeModuleInfo->module_srl;
		$nMomboxAfterModuleSrl = $oMomboxAfterModuleInfo->module_srl;
		unset($oMomboxBeforeModuleInfo);
		unset($oMomboxAfterModuleInfo);
		unset($oModuleModel);
		// end - angemombox_data_lake에 입력할 module_srl 설정

		if(!$nMomboxBeforeModuleSrl)
		{
			echo 'invalid nMomboxBeforeModuleSrl<BR>';
			exit;
		}
		if(!$nMomboxAfterModuleSrl)
		{
			echo 'invalid nMomboxAfterModuleSrl<BR>';
			exit;
		}

		$oAngeclubModel = &getModel('angeclub');
		$oMemberConnectionRst = $oAngeclubModel->getMemberFieldConnection();
		if(!$oMemberConnectionRst->toBool())
			return $oMemberConnectionRst;
		$sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		$sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		$sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		$sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		$sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		$sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		unset($oMemberConnectionRst);
		
		$oArgs->list_count = 40000;
		$oRst = executeQueryArray('angeclub.getTmpAdminComUserCleanedPaginationAsc', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';

		$oMemberModel = &getModel('member');
		foreach($oRst->data as $nIdx=>$oComUser)
		{
			// 주의!! com_user_cleaned tbl이므로 $oComUser->USER_ID == $oXeMemberInfo->user_id
			echo 'search NO: '.$oComUser->NO.' USER_ID: '.$oComUser->USER_ID.'<BR>';
			// $sOriginalUserId = null;
			$oXeMemberInfo = $oMemberModel->getMemberInfoByUserID($oComUser->USER_ID);
		
			$oHompyMomboxRegist = $aMomboxRegistration[$oXeMemberInfo->user_id];
			if(!$oHompyMomboxRegist) // 변경된 아이디로 맘박스 신청
			{
				echo 'inquiry unregistered member alias<BR>';
				$oArgs = new stdClass();
				$oArgs->USER_ID_DEST = $oComUser->USER_ID;
				$oAliasRst = executeQueryArray('angeclub.getTmpAdminComUserTransferredByUserIdDest', $oArgs);
				if(count($oAliasRst->data))
				{
					echo 'alias found<BR>';
					// exit;
					$sOriginalUserId = $oAliasRst->data[0]->USER_ID_DEST;
					$oXeMemberInfo = $oMemberModel->getMemberInfoByUserID($oAliasRst->data[0]->USER_ID_SOURCE);
				}
				unset($oAliasRst);
				unset($oArgs);
				$oHompyMomboxRegist = $aMomboxRegistration[$oXeMemberInfo->user_id];
				if(!$oHompyMomboxRegist) // 변경된 아이디로 맘박스 신청
					echo 'broken mombox info<BR>';
			}

			$oAngeclubRegistArgs = new stdClass();
			if($oHompyMomboxRegist->mombox_type == self::POSTNATAL)
				$oAngeclubRegistArgs->module_srl = $nMomboxAfterModuleSrl;
			elseif($oHompyMomboxRegist->mombox_type == self::PREGNANT)
				$oAngeclubRegistArgs->module_srl = $nMomboxBeforeModuleSrl;
			else
			{
				echo 'invalid mombox_type<BR>';
				var_Dump($oHompyMomboxRegist);
				exit;
			}
			if($oHompyMomboxRegist) // 맘박스 신청 내역이 잇으면 등록함
			{
				$oAngeclubRegistArgs->yr_mo = $oHompyMomboxRegist->yr_mo;
				$oAngeclubRegistArgs->parent_pregnant = $oComUser->PREGNENT_FL;
				$oAngeclubRegistArgs->baby_birthday = $oHompyMomboxRegist->baby_birthday;
				$oAngeclubRegistArgs->content = $oHompyMomboxRegist->content;
				$oAngeclubRegistArgs->regdate = $oHompyMomboxRegist->regdate;
				
				$oAngeclubRegistArgs->member_srl = $oXeMemberInfo->member_srl;
				$oAngeclubRegistArgs->mobile = $oXeMemberInfo->mobile;
				$oAngeclubRegistArgs->parent_gender = $oXeMemberInfo->$sMemberGenderFieldName == '여' ? 'F' : 'M';
				$oAngeclubRegistArgs->postcode = $oXeMemberInfo->$sMemberAddrFieldName[0];
				$oAngeclubRegistArgs->addr = $oXeMemberInfo->$sMemberAddrFieldName[1];
				$oAngeclubRegistArgs->addr_detail = $oXeMemberInfo->$sMemberAddrFieldName[2];
				$oAngeclubRegistArgs->addr_extra = $oXeMemberInfo->$sMemberAddrFieldName[3];
				$oAngeclubRegistArgs->email_push = $oXeMemberInfo->$sMemberEmailpushFieldName;
				$oAngeclubRegistArgs->sms_push = $oXeMemberInfo->$sMemberSmspushFieldName;
				$oAngeclubRegistArgs->post_push = $oXeMemberInfo->$sMemberPostpushFieldName;
				$oAngeclubRegistArgs->sponsor_push = $oXeMemberInfo->$sMemberSponsorpushFieldName ;
				$oAngeclubRegistrationInsertRst = executeQuery('angemombox.insertApplication', $oAngeclubRegistArgs);
				unset($oAngeclubRegistArgs);
				if(!$oAngeclubRegistrationInsertRst->toBool())
				{
					var_Dump($oAngeclubRegistrationInsertRst);
					echo '<BR>';
					var_Dump($oAngeclubRegistArgs);
					exit;
				}
			}
		}
		
		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!';
	}
/**
 * @brief 아래의 쿼리로 adm_history_join에서 추출한 홈피 맘박스 신청자 php array 반환
 */
	private function _parseHompyMomboxApply()
	{
		/*
		홈피 맘박스 신청 내역 이전하기 위해 구홈페이지에서 htdocs/extrac/extrac.php 콘솔 실행
		생성된 mombox_registration_log_array.php를 
		/files/angeclub/mombox_registration_log_array.php 로 이동
		*/
		$sMomboxRegistrationArrPath = './files/angeclub/mombox_registration_log_array.php';
		include $sMomboxRegistrationArrPath;
		$aArrangedMomboxRegistration = [];
		foreach($aMomboxRegistration as $_=>$aMombox)
		{
			$sMomId = $aMombox['adu_id'];
			$aArrangedMomboxRegistration[$sMomId] = new stdClass();
			if(strpos($aMombox['ada_name'], '출산맘') !== false)  // 출산맘박스
				$aArrangedMomboxRegistration[$sMomId]->mombox_type = self::POSTNATAL;
			else  // 임신맘박스
				$aArrangedMomboxRegistration[$sMomId]->mombox_type = self::PREGNANT;
				
			$sMomboxTitle = $aMombox['ada_name'];
			// var_dump($aMombox['adhj_answers']);
			$aFirstChunk =  explode(',', $aMombox['adhj_answers']);
			$sBabybirth = str_replace('{"1":"', '', $aFirstChunk[0]);
			$sBabybirth = str_replace('"', '', $sBabybirth);
			$sBabybirth = str_replace('-', '', $sBabybirth).'000001';
			// echo $sBabybirth.'<BR>';
			$aSecondChunk = explode('""3":', $aFirstChunk[1]);
			$sContent = strip_tags(str_replace('"2":"', '', $aSecondChunk[0]));
			// echo $sContent.'<BR>';

			// ""3":""}
			//$nYrMoPos = mb_strpos($sMomboxTitle, '년');
			//if($nYrMoPos === false)  // 앙쥬 기존회원 앙쥬맘박스(구.샘플팩) 2020-07
			//{
			//	$nYrMoPos = strpos($sMomboxTitle, '20');
			//	$sInitYrMo = substr($sMomboxTitle, $nYrMoPos, 7);
			//	$aInitYrMo = explode('-', $sInitYrMo);
			//	$sFinalYrMo = $aInitYrMo[0].sprintf('%02d', $aInitYrMo[1]);
			//	$aArrangedMomboxRegistration[$sMomId]->yr_mo = $sFinalYrMo;
			//}
			//else  // 앙쥬 신규회원 앙쥬맘박스(구.샘플팩) 20년 5월_114차
			//{
			//	$sInitYrMo = mb_substr($sMomboxTitle, $nYrMoPos-2, 7);
			//	$sInitYrMo = str_replace('_' , '', $sInitYrMo);
			//	$aInitYrMo = explode('년 ', $sInitYrMo);
			//	$sFinalYrMo = '20'.$aInitYrMo[0].sprintf('%02d',str_replace('월' , '', $aInitYrMo[1]));
			//	$aArrangedMomboxRegistration[$sMomId]->yr_mo = $sFinalYrMo;
			//}
			$aArrangedMomboxRegistration[$sMomId]->regdate = preg_replace("/[ :-]/i", "", $aMombox['adhj_date_request']);  // 2020-04-26 02:04:40 수정
			$aArrangedMomboxRegistration[$sMomId]->yr_mo = substr($aArrangedMomboxRegistration[$sMomId]->regdate, 0, 6);
			$aArrangedMomboxRegistration[$sMomId]->baby_birthday = $sBabybirth;
			$aArrangedMomboxRegistration[$sMomId]->content = $sContent;
		}
		// var_dump($aArrangedMomboxRegistration);
		$sSerializedFilePath = './files/angeclub/serialize_hompy_mombox_apply.txt';
		FileHandler::writeFile($sSerializedFilePath, serialize($aArrangedMomboxRegistration));
		unset($aArrangedMomboxRegistration);
		// return $aArrangedMomboxRegistration;
	}		
/**
 * @brief club_log, club_center tbl에서 cu_id 필드를 member_srl_staff으로 변경
 */
	private function _translateCuId()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(720);  // sec

		$sSeqLogFilePath = './files/angeclub/6xe_translate_cu_id_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;
		
		$oMemberModel = &getModel('member');
		echo 'angeclub_log tbl translation<BR>';
		$oArgs = new stdClass();
		$oArgs->page = $nCurPage;
		// $oArgs->cl_idx = 753763;
		$oArgs->list_count = 50000;
		$oRst = executeQueryArray('angeclub.getTmpAdminClubLogPagination', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';
		// var_dump($oRst);
		// exit;
		
		foreach($oRst->data as $nIdx=>$oSingleLog)
		{
			$sStaffMemberId = $this->_translateClubStaffId($oSingleLog->cu_id);

			$oUpdateArgs = new stdClass();
			$oUpdateArgs->cl_idx = $oSingleLog->cl_idx;
			$oUpdateArgs->workdate = preg_replace("/[ :-]/i", "", $oSingleLog->cl_date);  // 2020-04-26 02:04:40 수정
			$oUpdateArgs->regdate = preg_replace("/[ :-]/i", "", $oSingleLog->cl_date_regi);  // 2020-04-26 02:04:40 수정

			$oXeStaffMemberInfo = $oMemberModel->getMemberInfoByUserID($sStaffMemberId);
			if(!$oXeStaffMemberInfo)
			{
				if(!$this->_isAbandonedStaffId($oSingleLog->cu_id))
				{
					echo 'weird contact id<BR>';
					var_dump($sStaffMemberId);
					echo '<BR>';
					exit;
				}
			}
			$oUpdateArgs->member_srl_staff = $oXeStaffMemberInfo->member_srl ? $oXeStaffMemberInfo->member_srl : 0;
			$oUpdateRst = executeQueryArray('angeclub.updateTmpAdminClubLog', $oUpdateArgs);
			if(!$oUpdateRst->toBool())
			{
				var_Dump($oUpdateRst);
				echo '<BR>';
				var_Dump($oUpdateArgs);
				exit;
			}
			unset($oUpdateArgs);
		}
		unset($oArgs);
		echo count($oRst->data).' records has been resolved<BR>';

		echo 'angeclub_center tbl translation<BR>';
		$oArgs = new stdClass();
		$oArgs->page = $nCurPage;
		// $oArgs->cl_idx = 753763;
		$oArgs->list_count = 50000;
		$oRst = executeQueryArray('angeclub.getTmpAdminClubCenterPagination', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';
		
		// $oMemberModel = &getModel('member');
		foreach($oRst->data as $nIdx=>$oSingleLog)
		{
			$sStaffMemberId = $this->_translateClubStaffId($oSingleLog->cu_id);

			$oUpdateArgs = new stdClass();
			$oUpdateArgs->cc_idx = $oSingleLog->cc_idx;
			if($oSingleLog->cc_date_regi)
				$oUpdateArgs->regdate = preg_replace("/[ :-]/i", "", $oSingleLog->cc_date_regi);  // 2020-04-26 02:04:40 수정

			$oXeStaffMemberInfo = $oMemberModel->getMemberInfoByUserID($sStaffMemberId);
			if(!$oXeStaffMemberInfo)
			{
				if(!$this->_isAbandonedStaffId($oSingleLog->cu_id))
				{
					echo 'weird contact id<BR>';
					var_dump($sStaffMemberId);
					echo '<BR>';
					exit;
				}
			}
			$oUpdateArgs->member_srl_staff = $oXeStaffMemberInfo->member_srl ? $oXeStaffMemberInfo->member_srl : 0;

			$oUpdateRst = executeQueryArray('angeclub.updateTmpAdminClubCenter', $oUpdateArgs);
			// var_dump($oUpdateArgs);
			// echo '<BR>';
			if(!$oUpdateRst->toBool())
			{
				var_Dump($oUpdateRst);
				echo '<BR>';
				var_Dump($oUpdateArgs);
				exit;
			}
			unset($oUpdateRst);
			unset($oUpdateArgs);
		}
		unset($oMemberModel);
		echo count($oRst->data).' records has been resolved<BR>';

		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!';
	}
/**
 * @brief 무결성이 손상된 클럽 스탭 ID를 해석
 */
private function _isAbandonedStaffId($sClubStaffId)
{
	$aAbandonedStaffId = ['beauty79y', 'miheejilong', 'feelsk0614', 'thguskim', 'admin', 'bynwhj', 'pjsmommy',
							 'kiok1219', 'dahae1541', '0', ''];
	if(in_array($sClubStaffId, $aAbandonedStaffId))
		return true;
	return FALSE;
}
/**
 * @brief 무결성이 손상된 클럽 스탭 ID를 해석
 */
	private function _translateClubStaffId($sClubStaffId)
	{
		switch($sClubStaffId)	
		{
			case 'whzhwhzh':
				return 'whzhwzh';
			case 'juhyun0451':
				return 'ju7886';
			case 'mocha':
				return 'mocha2';
			default:
				return $sClubStaffId;
		}
	}
/**
 * @brief angeclub_registration에 신청 내역 추가
 */
	private function _cleanupCenterName($oAngeclubModel, $oComUser)
	{
		switch($oComUser->CARE_CENTER)
		{
			case '마포 아이린(망원)':
			case '마포 아이린(성산)':
				$sCenterName = '마포 아이린';
				break;
			case '강남 논현동그라미':
				$sCenterName = '강남 동그라미(논현)';
				break;
			case '송파아이숲':
				$sCenterName = '송파 아이숲';
				break;
			case '동작 카리스 1관':
				$sCenterName = '동작 카리스1관';
				break;
			case '부산 미래여성부설(신관)':
			case '부산 미래여성부설부설(신관)':
				$sCenterName = '부산 미래여성부설(별관)';
				break;
			case '용인수지미래부설':
				$sCenterName = '용인 수지미래부설';
			case '부산 일신부설(연제)':
				$sCenterName = '부산 일신부설(연제본관)';
				break;
			case '부산제일여성부설':
				$sCenterName = '부산 제일여성부설';
				break;
			case '강동 동그라미(명일)':
			case '강동명일 동그라미':
				$sCenterName = '강동 동그라미';
				break;
			case '광명 레피리움(닥터마레)':
				$sCenterName = '광명 레피리움(광명)';
				break;
			case '양천 포미즈부설':
				$sCenterName = '양천 포미즈타임';
				break;
			case '부산 순부설(신관)':
			case '부산 순부설(본관)':
				$sCenterName = '부산 순부설';
				break;
			case '중구 레피리움(용산마포)':
				$sCenterName = '중구 레피리움(마포)';
				break;
			case '영등포 리더스미래퀸':
				$sCenterName = '영등포 리더스';
				break;
			case '일산 퀸즈파크':
				$sCenterName = '일산 퀸스파크';
				break;
			case '영등포 아란테':
				$sCenterName = '영등포 아란태';
				break;
			default:
				$sCenterName = $oComUser->CARE_CENTER;
		}

		if(strpos($oComUser->CARE_CENTER, '퀸즈마리') !== false) // '양천 퀸즈마리'  table에 있는데 php는 문자열 인식 안됨
		{
			if($oComUser->CARE_AREA	== '양천')
				$sCenterName = 	'양천 queensmari';
		}

		$aCcIdx = $oAngeclubModel->getCenterInfoByName($sCenterName);
		$nLatestCenterId = array_pop($aCcIdx);
		if(!$nLatestCenterId)  // 폐업 조리원
		{
			if($oComUser->CARE_CENTER == '양천 이자르' || $oComUser->CARE_CENTER == '구리 이자르' || 
				$oComUser->CARE_CENTER == '구리 뉴이자르' || $oComUser->CARE_CENTER == '구리이자르' || 
				$oComUser->CARE_CENTER == '양천 강서레피리움' || $oComUser->CARE_CENTER == '부산창원 새한마음' ||
				$oComUser->CARE_CENTER == '인천 미추홀 W여성부설 B동' || $oComUser->CARE_CENTER == '의정부 레피리움' ||
				$oComUser->CARE_CENTER == '성남 곽생로부설' || $oComUser->CARE_CENTER == '성남 곽생로' || 
				$oComUser->CARE_CENTER == '용인 포근포근' || $oComUser->CARE_CENTER == '부산 마미캠프(좌동)' || 
				$oComUser->CARE_CENTER == 'jyujin0304')
			{
				$nLatestCenterId = -1;
			}
			else
			{
				echo 'weird center name<BR>';
				var_dump($oComUser);
				echo '<BR><BR>';
				var_dump($aCcIdx);
				echo '<BR><BR>';
				var_dump($sCenterName);
				exit;	
			}
		}
		return $nLatestCenterId;
	}
/**
 * @brief angeclub_registration에 신청 내역 추가
 */
	private function _registerAngeclubRegistration($oAngeclubModel, $oMemberModel, $oComUser, $oXeMemberParentInfo)
	{
		$sStaffMemberId = $this->_translateClubStaffId($oComUser->CONTACT_ID);
		$oXeStaffMemberInfo = $oMemberModel->getMemberInfoByUserID($sStaffMemberId);
		if(!$oXeStaffMemberInfo)
		{
			// if($oComUser->CONTACT_ID != 'dahae1541' && $oComUser->CONTACT_ID != 'admin')  // 직원 정보 상실
			if(!$this->_isAbandonedStaffId($oComUser->CONTACT_ID))
			{
				echo 'weird contact id<BR>';
				var_dump($oComUser->CONTACT_ID);
				echo '<BR>';
				exit;
			}
		}
		// switch($oComUser->CARE_CENTER)
		// {
		// 	case '마포 아이린(망원)':
		// 	case '마포 아이린(성산)':
		// 		$sCenterName = 	'마포 아이린';
		// 		break;
		// 	case '동작 카리스 1관':
		// 		$sCenterName = 	'동작 카리스1관';
		// 		break;
		// 	case '부산 미래여성부설(신관)':
		// 		$sCenterName = 	'부산 미래여성부설(별관)';
		// 		break;
		// 	case '부산 일신부설(연제)':
		// 		$sCenterName = 	'부산 일신부설(연제본관)';
		// 		break;
		// 	case '강동 동그라미(명일)':
		// 		$sCenterName = 	'강동 동그라미';
		// 		break;
		// 	case '광명 레피리움(닥터마레)':
		// 		$sCenterName = 	'광명 레피리움(광명)';
		// 		break;
		// 	case '양천 포미즈부설':
		// 		$sCenterName = 	'양천 포미즈타임';
		// 		break;
		// 	case '부산 순부설(신관)':
		// 	case '부산 순부설(본관)':
		// 		$sCenterName = 	'부산 순부설';
		// 		break;
		// 	case '중구 레피리움(용산마포)':
		// 		$sCenterName = 	'중구 레피리움(마포)';
		// 		break;
		// 	case '영등포 리더스미래퀸':
		// 		$sCenterName = 	'영등포 리더스';
		// 		break;
		// 	case '일산 퀸즈파크':
		// 		$sCenterName = 	'일산 퀸스파크';
		// 		break;
		// 	case '영등포 아란테':
		// 		$sCenterName = 	'영등포 아란태';
		// 		break;
		// 	default:
		// 		$sCenterName = $oComUser->CARE_CENTER;
		// }

		// if(strpos($oComUser->CARE_CENTER, '퀸즈마리') !== false) // '양천 퀸즈마리'  table에 있는데 php는 문자열 인식 안됨
		// {
		// 	if($oComUser->CARE_AREA	== '양천')
		// 		$sCenterName = 	'양천 queensmari';
		// }

		// $aCcIdx = $oAngeclubModel->getCenterInfoByName($sCenterName);
		// $nLatestCenterId = array_pop($aCcIdx);
		// if(!$nLatestCenterId)  // 폐업 조리원
		// {
		// 	if($oComUser->CARE_CENTER == '양천 이자르' || $oComUser->CARE_CENTER == '구리 이자르' || 
		// 		$oComUser->CARE_CENTER == '구리 뉴이자르' ||
		// 		$oComUser->CARE_CENTER == '양천 강서레피리움' || $oComUser->CARE_CENTER == '부산창원 새한마음' ||
		// 		$oComUser->CARE_CENTER == '인천 미추홀 W여성부설 B동' || $oComUser->CARE_CENTER == '의정부 레피리움' ||
		// 		$oComUser->CARE_CENTER == '성남 곽생로부설' || $oComUser->CARE_CENTER == '용인 포근포근')
		// 	{
		// 		$nLatestCenterId = -1;
		// 	}
		// 	else
		// 	{
		// 		echo 'weird center name<BR>';
		// 		var_dump($oComUser);
		// 		echo '<BR><BR>';
		// 		var_dump($aCcIdx);
		// 		echo '<BR><BR>';
		// 		var_dump($sCenterName);
		// 		exit;	
		// 	}
		// }
		$nLatestCenterId = $this->_cleanupCenterName($oAngeclubModel, $oComUser);

		$oAngeclubRegistArgs = new stdClass();
		$oAngeclubRegistArgs->cc_idx = $nLatestCenterId;
		unset($aCcIdx);
		$oAngeclubRegistArgs->member_srl_staff = $oXeStaffMemberInfo->member_srl;
		$oAngeclubRegistArgs->member_srl_parent = $oXeMemberParentInfo->member_srl;
		$oAngeclubRegistArgs->is_existing_parent_member = $oXeMemberParentInfo->is_existing_parent_member;
		$oAngeclubRegistArgs->center_visit_cnt = $oComUser->CENTER_CNT;
		$oAngeclubRegistArgs->education_cnt = $oComUser->CLUBEDU_CNT;

		//$aClubRegdate = explode(' ', $oComUser->CLUB_REG_DT);
		//$sClubRegdate = str_replace('-', '', $aClubRegdate[0]).'133030';
		//$oAngeclubRegistArgs->regdate = $sClubRegdate; //$oComUser->CLUB_REG_DT;
		$oAngeclubRegistArgs->regdate = preg_replace("/[ \:-]/i", "", $oComUser->CLUB_REG_DT);  // 2012-01-26 21:12:12 수정
		$oAngeclubRegistrationInsertRst = executeQuery('angeclub.insertClubRegistration', $oAngeclubRegistArgs);
		if(!$oAngeclubRegistrationInsertRst->toBool())
		{
			var_Dump($oAngeclubRegistrationInsertRst);
			echo '<BR>';
			var_Dump($oAngeclubRegistArgs);
			exit;
		}

		$oDB = DB::getInstance();
		$nAngeclubRegistrationLogSrl = $oDB->db_insert_id();
		// echo 'nAngeclubRegistrationLogSrl<BR>';
		// var_dump($nAngeclubRegistrationLogSrl);
		// echo '<BR>';
		unset($oDB);
		unset($oAngeclubRegistArgs);
		unset($oAngeclubRegistrationInsertRst);
		$oRst = new stdClass();
		$oRst->nAngeclubRegistrationLogSrl = $nAngeclubRegistrationLogSrl;
		$oRst->sClubRegdate = $sClubRegdate;
		return $oRst;
	}
/**
 * @brief 아이 정보 가져오기
 */
	// private function _getBabyInfoByXeUserId($sXeUserId)
	// {
	// 	$aBabyList = [];
	// 	// echo 'inquiry old USER_ID alias<BR>';
	// 	$oArgs = new stdClass();
	// 	$oArgs->USER_ID_DEST = $sXeUserId;
	// 	$oAliasRst = executeQueryArray('angeclub.getTmpAdminComUserTransferredByUserId', $oArgs);
	// 	foreach($oAliasRst->data as $_=>$oAlias)  // search baby list by OLD USER_ID
	// 	{
	// 		echo 'alias USER_ID found for baby list searching<BR>';
	// 		$oBabyArgs = new stdClass();
	// 		$oBabyArgs->USER_ID = $oAlias->USER_ID_SOURCE;
	// 		$oBabyRst = executeQueryArray('angeclub.getTmpAdminBabyList', $oBabyArgs);
	// 		foreach($oBabyRst->data as $_=>$oSingleBaby)
	// 			$aBabyList[$oSingleBaby->BABY_BIRTH.'|@|'.$oSingleBaby->BABY_SEX_GB] = $oSingleBaby->BABY_NM;
	// 		unset($oBabyRst);
	// 		unset($oBabyArgs);
	// 	}
	// 	unset($oAliasRst);
	// 	unset($oArgs);

	// 	$oBabyArgs = new stdClass();  // search baby list by XE member.user_id
	// 	$oBabyArgs->USER_ID = $sXeUserId;
	// 	$oBabyRst = executeQueryArray('angeclub.getTmpAdminBabyList', $oBabyArgs);
	// 	foreach($oBabyRst->data as $_=>$oSingleBaby)
	// 		$aBabyList[$oSingleBaby->BABY_BIRTH.'|@|'.$oSingleBaby->BABY_SEX_GB] = $oSingleBaby->BABY_NM;
	// 	unset($oBabyRst);
	// 	unset($oBabyArgs);

	// 	$aFinalBabyList = [];
	// 	foreach($aBabyList as $sUid=>$sBabyName)
	// 	{
	// 		$aInfo = explode('|@|', $sUid);
	// 		$oSingleBaby = new stdClass();
	// 		$oSingleBaby->sName = $sBabyName;
	// 		$oSingleBaby->sBirthYyyymmdd = $aInfo[0];
	// 		$oSingleBaby->sGender = $aInfo[1];
	// 		$aFinalBabyList[] = $oSingleBaby;
	// 	}
	// 	unset($aBabyList);
	// 	return $aFinalBabyList;
	// }
/**
 * @brief angemombox_data_lake에 신청 내역 추가
 */
	private function _registerAngemomboxDataLake($oDatalakeArgs)
	{
		// $oDatalakeArgs->baby_birth_name = null;
		// $oDatalakeArgs->baby_gender = null;
		// $oDatalakeArgs->baby_birthday_yyyymmdd = null;
		$oDataLakeInsertRst = executeQuery('angeclub.insertTmpAdminDataLake', $oDatalakeArgs);
		return $oDataLakeInsertRst;
	}
/**
 * @brief COM_USER_cleaned 테이블을 참조하여 mombox_member_extra와 club_registration에 입력
 */
	private function _migrateComUserIntoXeModules()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(720);  // sec
		
		$sSeqLogFilePath = './files/angeclub/5xe_user_migrate_into_module_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;

		$oArgs = new stdClass();
		$oArgs->page = $nCurPage;
		// $oArgs->NO = 753763;
		
		$oArgs->list_count = 80000;
		$oRst = executeQueryArray('angeclub.getTmpAdminComUserCleanedPaginationAsc', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';

		$oAngeclubModel = &getModel('angeclub');

		$oMemberConnectionRst = $oAngeclubModel->getMemberFieldConnection();
		if(!$oMemberConnectionRst->toBool())
			return $oMemberConnectionRst;
		$sMemberAddrFieldName = $oMemberConnectionRst->get('sMemberAddrFieldName');
		$sMemberGenderFieldName = $oMemberConnectionRst->get('sMemberGenderFieldName');
		$sMemberSmspushFieldName = $oMemberConnectionRst->get('sMemberSmspushFieldName');
		$sMemberEmailpushFieldName = $oMemberConnectionRst->get('sMemberEmailpushFieldName');
		$sMemberPostpushFieldName = $oMemberConnectionRst->get('sMemberPostpushFieldName');
		$sMemberSponsorpushFieldName = $oMemberConnectionRst->get('sMemberSponsorpushFieldName');
		unset($oMemberConnectionRst);

		$oMemberModel = &getModel('member');
		foreach($oRst->data as $nIdx=>$oComUser)
		{
			// 주의!! com_user_cleaned tbl이므로 $oComUser->USER_ID == $oXeMemberInfo->user_id
			echo 'search NO: '.$oComUser->NO.' USER_ID: '.$oComUser->USER_ID.'<BR>';
			$sOriginalUserId = null;
			$oXeMemberInfo = $oMemberModel->getMemberInfoByUserID($oComUser->USER_ID);
			
			if($oXeMemberInfo->member_srl)
			{
				$oMemberExtraArgs = new stdClass();
				if(strlen($oComUser->CONTACT_ID) && strlen($oComUser->CLUB_REG_DT) && strlen($oComUser->CARE_CENTER)) // 클럽 수기 가입
				{
					$oXeMemberParentInfo = new stdClass();
					$oXeMemberParentInfo->member_srl = $oXeMemberInfo->member_srl;
					if($oComUser->CLUB_INT == 'Y') // 홈피로 가입한 산모를 클럽에서 만남
						$oXeMemberParentInfo->is_existing_parent_member = 'Y';
					else
						$oXeMemberParentInfo->is_existing_parent_member = 'ㅜ';
					$oRst = $this->_registerAngeclubRegistration($oAngeclubModel, $oMemberModel, $oComUser, $oXeMemberParentInfo);
				}
				// $oMemberExtraArgs->parent_birthday_yyyymmdd = $oXeMemberInfo->birthday;
				$oMemberExtraArgs->angeclub_registration_log_srl = $oRst->nAngeclubRegistrationLogSrl;
				$oMemberExtraArgs->member_srl = $oXeMemberInfo->member_srl;
				$oMemberExtraArgs->user_name = $oXeMemberInfo->user_name;
				$oMemberExtraArgs->mobile = $oXeMemberInfo->mobile;
				$oMemberExtraArgs->gender = $oXeMemberInfo->$sMemberGenderFieldName == '여' ? 'F' : 'M';  //$oComUser->SEX_GB;
				$oMemberExtraArgs->postcode = $oXeMemberInfo->$sMemberAddrFieldName[0];  //$oComUser->ZONE_CODE ? $oComUser->ZONE_CODE : $oComUser->ZIP_CODE;
				$oMemberExtraArgs->addr = $oXeMemberInfo->$sMemberAddrFieldName[1];  //$oComUser->ADDR;
				$oMemberExtraArgs->addr_detail = $oXeMemberInfo->$sMemberAddrFieldName[2];  //$oComUser->ADDR_DETAIL;
				$oMemberExtraArgs->email_push = $oXeMemberInfo->$sMemberEmailpushFieldName;  //$oComUser->EN_ANGE_EMAIL_FL;
				$oMemberExtraArgs->sms_push = $oXeMemberInfo->$sMemberSmspushFieldName;  //$oComUser->EN_ANGE_SMS_FL;
				$oMemberExtraArgs->post_push = $oXeMemberInfo->$sMemberPostpushFieldName;  //$oComUser->EN_ANGE_ADDR_FL;
				$oMemberExtraArgs->sponsor_push = $oXeMemberInfo->$sMemberSponsorpushFieldName;  //$oComUser->EN_CLUB_SPONSOR_FL;
				$oMemberExtraArgs->regdate = $oXeMemberInfo->regdate;
				$oRst = executeQuery('angeclub.insertTmpAdminMemberExtra', $oMemberExtraArgs);
				unset($oMemberExtraArgs);
				if(!$oRst->toBool())
					return $oRst;
				unset($oRst);

				/*$oHompyMomboxRegist = $aMomboxRegistInfo[$oXeMemberInfo->user_id];
				if($oHompyMomboxRegist)  // 홈피에서 맘박스 신청
				{
					if($oHompyMomboxRegist->mombox_type == self::POSTNATAL)
						$oDatalakeArgs->module_srl = $nMomboxAfterModuleSrl;
					elseif($oHompyMomboxRegist->mombox_type == self::PREGNANT)
						$oDatalakeArgs->module_srl = $nMomboxBeforeModuleSrl;
					else
					{
						echo 'invalid mombox_type<BR>';
						var_Dump($oHompyMomboxRegist);
						exit;
					}
					$oDatalakeArgs->yr_mo = $oHompyMomboxRegist->yr_mo;
					$oDatalakeArgs->parent_pregnant = $oComUser->PREGNENT_FL;
					// $oDatalakeArgs->content = null; 
					$oDatalakeArgs->regdate = $oHompyMomboxRegist->regdate;
					foreach($aFinalBabyList as $_=>$oSingleBaby)
					{
						$oDatalakeArgs->baby_birth_name = $oSingleBaby->sName;
						$oDatalakeArgs->baby_gender = $oSingleBaby->sGender;
						$oDatalakeArgs->baby_birthday_yyyymmdd = $oSingleBaby->sBirthYyyymmdd;
						$oDataLakeInsertRst = $this->_registerAngemomboxDataLake($oDatalakeArgs);
						if(!$oDataLakeInsertRst->toBool())
						{
							echo 'error while register hompy an exclusive member<BR>';
							var_Dump($oDataLakeInsertRst);
							echo '<BR>';
							var_Dump($oDatalakeArgs);
							echo '<BR>';
							var_Dump($oHompyMomboxRegist);
							echo '<BR>';
							var_Dump($oXeMemberInfo->user_id);
							exit;
						}
					}

					if($oComUser->CLUB_INT == 'Y') // 홈피로 가입한 산모를 클럽에서 만났으므로 한번 더 기록
					{
						if(!$oComUser->CLUB_REG_DT || !$oComUser->CARE_CENTER) // 불완전한 기록이므로 무시
							continue;
						
						$oRst = $this->_registerAngeclubRegistration($oAngeclubModel, $oMemberModel, $oComUser, $oXeMemberInfo);
						$oDatalakeArgs->angeclub_registration_log_srl = $oRst->nAngeclubRegistrationLogSrl;
						$oDatalakeArgs->module_srl = $nAngeclubModuleSrl;
						$oDatalakeArgs->yr_mo = substr($oRst->sClubRegdate, 0, 6);
						$oDatalakeArgs->parent_pregnant = 'N';  // 조리원이므로 당연히 출산 후
						$oDatalakeArgs->regdate = $oRst->sClubRegdate;
						unset($oRst);
						foreach($aFinalBabyList as $_=>$oSingleBaby)
						{
							$oDatalakeArgs->baby_birth_name = $oSingleBaby->sName;
							$oDatalakeArgs->baby_gender = $oSingleBaby->sGender;
							$oDatalakeArgs->baby_birthday_yyyymmdd = $oSingleBaby->sBirthYyyymmdd;
							$oDataLakeInsertRst = $this->_registerAngemomboxDataLake($oDatalakeArgs);
							if(!$oDataLakeInsertRst->toBool())
							{
								echo 'error while register a hompy angeclub duplicated member<BR>';
								var_Dump($oDataLakeInsertRst);
								echo '<BR>';
								var_Dump($oDatalakeArgs);
								exit;
							}
						}
					}
				}
				else  // 앙쥬 클럽만 가입한 산모
				{
					if(!$oComUser->CLUB_REG_DT || !$oComUser->CARE_CENTER)  // 불완전한 기록이므로 무시
						continue;
					
					$oRst = $this->_registerAngeclubRegistration($oAngeclubModel, $oMemberModel, $oComUser, $oXeMemberInfo);
					$oDatalakeArgs->angeclub_registration_log_srl = $oRst->nAngeclubRegistrationLogSrl;
					$oDatalakeArgs->module_srl = $nAngeclubModuleSrl;
					$oDatalakeArgs->yr_mo = substr($oRst->sClubRegdate, 0, 6);
					$oDatalakeArgs->parent_pregnant = 'N';  // 조리원이므로 당연히 출산 후
					$oDatalakeArgs->regdate = $oRst->sClubRegdate;
					unset($oRst);
					foreach($aFinalBabyList as $_=>$oSingleBaby)
					{
						$oDatalakeArgs->baby_birth_name = $oSingleBaby->sName;
						$oDatalakeArgs->baby_gender = $oSingleBaby->sGender;
						$oDatalakeArgs->baby_birthday_yyyymmdd = $oSingleBaby->sBirthYyyymmdd;
						$oDataLakeInsertRst = $this->_registerAngemomboxDataLake($oDatalakeArgs);
						if(!$oDataLakeInsertRst->toBool())
						{
							echo 'error while register a angeclub exclusive member<BR>';
							var_Dump($oDataLakeInsertRst);
							echo '<BR>';
							var_Dump($oDatalakeArgs);
							exit;
						}
					}
				}*/
			}
			// unset($aFinalBabyList);
		}
		unset($oAngeclubModel);
		unset($oMemberModel);
		// exit;
		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!';
	}
/**
 * @brief 퇴사 간호사를 xe_member tbl에 입력함
 */
	private function _registerRetiredIntoXeMember()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		// cu_id		cu_pw	cu_name	cu_phone_1	cu_email	cu_st_date	cu_en_date
		$aStaff = [];
		$aStaff[] = ['mocha2', 'c4650', '김경희', '01047428413', 'angeclub@ange.co.kr', '20051122'];  // 19691017
		$aStaff[] = ['sugarprime', '830228', '손미연', '01071377707', 'sugarprime@naver.com', '20160208'];
		$aStaff[] = ['sooga7', '761015', '이성숙', '01065514405', 'sooga76@naver.com', '20180528'];
		$aStaff[] = ['hya1021', '821021', '신명희', '01034972002', 'hya1021@naver.com', '20190422'];
		$aStaff[] = ['myheromyth89', '891001', '남가은', '01091611247', 'myheromyth89@naver.com', '20200810'];

		$oMemberModel = &getModel('member');
		$nStaffGrpSrl = 0;
		$aMemberGrps = $oMemberModel->getGroups();
		foreach($aMemberGrps as $oGroup)
		{
			if($oGroup->title == '앙쥬클럽스탭')
			{
				$nStaffGrpSrl = $oGroup->group_srl;
				break;
			}
		}
		unset($aMemberGrps);

		$oArgs = new stdClass;
		foreach($aStaff as $aSingleStaff)
		{
			$oArgs->member_srl = getNextSequence();
			$oArgs->user_id = $aSingleStaff[0];
			$oArgs->user_name = $aSingleStaff[2];
			$oArgs->nick_name =  $this->_getRandStr(7, TRUE);
			$oArgs->mobile = $aSingleStaff[3];
			$oArgs->email_address = $aSingleStaff[4];
			$aEmailInfo = explode('@', $oArgs->email_address);
			$oArgs->email_id = $aEmailInfo[0];
			$oArgs->email_host = $aEmailInfo[1];
			unset($aEmailInfo);
			$oArgs->password = $oMemberModel->hashPassword($aSingleStaff[1]);
			if($aSingleStaff[0] == 'mocha2')
				$oArgs->birthday = '19691017';
			else
				$oArgs->birthday = '19'.$aSingleStaff[1];

			$oArgs->allow_mailing = 'N';
			$oArgs->regdate = $aSingleStaff[5];
			$oArgs->last_login = date('YmdHis');
			$dtChangePasswordDate = new \DateTime($oArgs->last_login.' +60 day');
			$oArgs->change_password_date = $dtChangePasswordDate->format('YmdHis');
		
			$oArgs->list_order = -1 * $oArgs->member_srl;
			$oMemberAddRst = executeQuery('member.insertMember', $oArgs);
			if(!$oMemberAddRst->toBool())
			{
				var_dump($oMemberAddRst);
				echo '<BR>';
				var_dump($oArgs);
				echo '<BR>';
			}
			$oGrpAddArgs = new stdClass();
			$oGrpAddArgs->member_srl = $oArgs->member_srl;
			$oGrpAddArgs->group_srl = 2;
			$oGrpAddArgs->site_srl = 0;
			$output = executeQuery('member.addMemberToGroup',$oGrpAddArgs);

			$oGrpAddArgs->member_srl = $oArgs->member_srl;
			$oGrpAddArgs->group_srl = $nStaffGrpSrl;
			$oGrpAddArgs->site_srl = 0;
			$output = executeQuery('member.addMemberToGroup',$oGrpAddArgs);
			unset($output);
			unset($oGrpAddArgs);
			unset($oArgs);
			echo $aSingleStaff[2].' has been imported<BR>';
		}
		unset($oMemberModel);
		unset($aStaff);
		echo '<BR><BR>succeed!';
	}
/**
 * @brief USER_ID, EMAIL, PHONE_2, NICK_NM을 정제한 COM_USER_cleaned 테이블을 xe_member tbl에 입력함
 */
	private function _migrateComUserIntoXeMember()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(720);  // sec
		
		$sSeqLogFilePath = './files/angeclub/4xe_member_migrate_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;

		$oArgs = new stdClass();
		$oArgs->page = $nCurPage;
		// $oArgs->NO = 720476;
		$oArgs->list_count = 90000;
		$oRst = executeQueryArray('angeclub.getTmpAdminComUserCleanedPagination', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';

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

		foreach($oRst->data as $nIdx=>$oComUser)
		{
			$oInsertRst = $this->_addMemberInfo($oComUser, 
												$sMemberAddrFieldName, $sMemberGenderFieldName, $sMemberSmspushFieldName,
												$sMemberEmailpushFieldName, $sMemberPostpushFieldName, $sMemberSponsorpushFieldName);
			if(!$oInsertRst->toBool())
			{
				echo 'error occured while xe_member insert!!!<BR>';
				// var_Dump($oComUser);
				// echo '<BR>';
				// var_Dump($oInsertRst);
				// echo '<BR>';
				exit;
			}
			$nXeMemberSrl = $oInsertRst->get('member_srl');
			unset($oInsertRst);
			echo 'NO: '.$oComUser->NO.' USER_ID: '.$oComUser->USER_ID.' has been migrated to xe_member_srl: '.$nXeMemberSrl.'<BR>';
			// exit;
		}
		unset($oRst);
		// exit;
		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!';
	}
/**
 * add member profile
 * $oMemberController->insertMember($oArgs, true)를 이용하면 너무 느려서 member table에 직접 기록함
 */
	private function _addMemberInfo($oComUserInfo, 
									$sMemberAddrFieldName, $sMemberGenderFieldName, $sMemberSmspushFieldName,
									$sMemberEmailpushFieldName, $sMemberPostpushFieldName, $sMemberSponsorpushFieldName)
	{
		$oArgs = new stdClass;
		$oArgs->member_srl = getNextSequence();
		$oArgs->user_id = $oComUserInfo->USER_ID;
		$oArgs->mobile = $oComUserInfo->PHONE_2;
		$oArgs->email_address = $oComUserInfo->EMAIL;
		$oArgs->password = $oComUserInfo->PASSWORD;

		$aEmailInfo = explode('@', $oComUserInfo->EMAIL);
		$oArgs->email_id = $aEmailInfo[0];
		$oArgs->email_host = $aEmailInfo[1];
		unset($aEmailInfo);

		$oArgs->user_name = $oComUserInfo->USER_NM;
		$oArgs->nick_name = $oComUserInfo->NICK_NM;
		$oArgs->birthday = $oComUserInfo->BIRTH;

		$oArgs->allow_mailing = 'N';
		//$sCleanedRegDt = preg_replace("/[ \:-]/i", "", $oComUserInfo->REG_DT);  // 2012-01-26 21: 수정
		//$oArgs->regdate = $sCleanedRegDt.'0101';
		$oArgs->regdate = preg_replace("/[ \:-]/i", "", $oComUserInfo->REG_DT);  // 2012-01-26 21:12:12 수정
		if(strlen($oComUserInfo->FINAL_LOGIN_DT))
		{
			//$sCleanedFinalLoginDt = preg_replace("/[ \:-]/i", "", $oComUserInfo->FINAL_LOGIN_DT);  // 2012-01-26 21: 수정
			//$oArgs->last_login = $sCleanedFinalLoginDt.'0101';
			$oArgs->last_login = preg_replace("/[ \:-]/i", "", $oComUserInfo->FINAL_LOGIN_DT);  // 2012-01-26 21:12:12 수정
			$dtChangePasswordDate = new \DateTime($oArgs->last_login.' +60 day');
			$oArgs->change_password_date = $dtChangePasswordDate->format('YmdHis');
		}
		else
		{
			$oArgs->last_login = $oArgs->regdate;
			$dtChangePasswordDate = new \DateTime($oArgs->regdate.' +60 day');
			$oArgs->change_password_date = $dtChangePasswordDate->format('YmdHis');
		}
		
		// 시작 - 주소를 extra var로 변경
		// O:8:"stdClass":3:{s:15:"xe_validator_id";s:20:"modules/member/tpl/1";s:7:"address";a:4:{i:0;s:5:"06307";i:1;s:30:"서울 서울구 서울로 202";i:2;s:19:"각각동 아파트";i:3;s:11:"(서울동)";}s:15:"give_birth_date";s:8:"20221115";}
		$oExtraVarsArgs = new stdClass;
		$oExtraVarsArgs->$sMemberAddrFieldName[0] = $oComUserInfo->ZONE_CODE ? $oComUserInfo->ZONE_CODE : $oComUserInfo->ZIP_CODE;
		$oExtraVarsArgs->$sMemberAddrFieldName[1] = $oComUserInfo->ADDR;
		$oExtraVarsArgs->$sMemberAddrFieldName[2] = $oComUserInfo->ADDR_DETAIL;
		$oExtraVarsArgs->$sMemberAddrFieldName[3] = '';
		// 끝 - 주소를 extra var로 변경

		$oExtraVarsArgs->$sMemberGenderFieldName = '여';  // 산후 조리원이므로 반드시 여성  성별을 extra var로 변경
		$oExtraVarsArgs->$sMemberSmspushFieldName = $oComUserInfo->EN_ANGE_SMS_FL;  // sms 수신 동의를 extra var로 변경
		$oExtraVarsArgs->$sMemberEmailpushFieldName = $oComUserInfo->EN_ANGE_EMAIL_FL;  // 이메일 수신 동의를 extra var로 변경
		$oExtraVarsArgs->$sMemberPostpushFieldName = $oComUserInfo->EN_ANGE_ADDR_FL;  // 우편 수신 동의를 extra var로 변경
		$oExtraVarsArgs->$sMemberSponsorpushFieldName = $oComUserInfo->EN_CLUB_SPONSOR_FL;  // 후원사 정보 수신 동의를 extra var로 변경
		
		// Add extra vars after excluding necessary information from all the requested arguments
		// $oExtraVars = delObjectVars($oAllArgs, $oArgs);
		$oArgs->extra_vars = serialize($oExtraVarsArgs);
		unset($oExtraVarsArgs);
		// exit;
		// remove whitespace
		// $aCheckInfos = array('user_id', 'user_name', 'nick_name', 'email_address');
		// foreach($aCheckInfos as $val)
		// {
		// 	if(isset($oArgs->{$val}))
		// 		$oArgs->{$val} = preg_replace('/[\pZ\pC]+/u', '', html_entity_decode($oArgs->{$val}));
		// }
		// $oMemberController = &getController('member');
		// $oRst = $oMemberController->insertMember($oArgs, true);
		// unset($oArgs);
		// var_dump($oRst);
		// exit;
		$oArgs->list_order = $oArgs->member_srl; //-1 * $oArgs->member_srl;
		$oMemberAddRst = executeQuery('member.insertMember', $oArgs);
		if(!$oMemberAddRst->toBool())
		{
			var_dump($oMemberAddRst);
			echo '<BR>';
			var_dump($oArgs);
			echo '<BR>';
			// exit;
		}
		$oMemberAddRst->add('member_srl', $oArgs->member_srl);
		
		$oGrpAddArgs = new stdClass();
		$oGrpAddArgs->member_srl = $oArgs->member_srl;
		$oGrpAddArgs->group_srl = 2;
		$oGrpAddArgs->site_srl = 0;
		$output = executeQuery('member.addMemberToGroup',$oGrpAddArgs);
		unset($output);
		unset($oGrpAddArgs);
		unset($oArgs);

		return $oMemberAddRst;
	}
/**
 * @brief 
 */
	private function _getRandStr($length = 6, $bAllAhphaNumeric=FALSE) 
	{
		if($bAllAhphaNumeric)
        	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		else
			$characters = 'abcdefghijklmnopqrstuvwxyz';  // 숫자를 발생시키면 핸폰 번호 아이디 postfix에서 혼란스러움, 대문자 불허함
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
/**
 * @brief 
 */
	private function _checkUserIdUniqueness($sUserId) 
	{
		$oArg = new stdClass();
		$oArg->USER_ID = $sUserId;
		$oUserIdRst = executeQueryArray('angeclub.getTmpAdminComUserByUserId', $oArg);
		if(count($oUserIdRst->data))
			$sFinalUserId = $sUserId.$this->_getRandStr(2);
		else
			$sFinalUserId = $sUserId;
		unset($oArg);
		unset($oUserIdRst);
		return $sFinalUserId;
	}
/**
 * @brief 
 * // if(preg_match("/[\xA1-\xFE][\xA1-\xFE]/", $str))  // EUC-KR인 경우
 *	// 	echo "한글포함.";
 *	// else
 *	// 	echo "한글없음";
 *	// $str = iconv("euckr", "utf8", $str);  // euc-kr -> utf-8
 */
	private function _cleanUserId($sUserId, $sEmail, $sMobile) 
	{
		if(strpos($sUserId, '@') !== false) // USER_ID가 이메일이면
		{ 
			$aUserId = explode('@', $sUserId);
			$sEmailId = str_replace(' ' , '', $aUserId[0]);
			$sEmailId = strtolower($sEmailId);
			$sEmailId = preg_replace("/[ #\&\\%@=\/\\\:;,\.\+'\"\^`~\|\!\?\*$#<>()\[\]\{\}]/i", "", $sEmailId);  // -_ 제외한 특수 문자 제거
			$sUserId = $this->_checkUserIdUniqueness($sEmailId);
			unset($aUserId);
		}
		if(preg_match("/[\xE0-\xFF][\x80-\xFF][\x80-\xFF]/", $sUserId)) // USER_ID가 UTF-8 한글이면
		{ 
			$aUserId = explode('@', $sEmail);
			$sEmailId = str_replace(' ' , '', $aUserId[0]);
			$sEmailId = strtolower($sEmailId);
			$sUserId = $this->_checkUserIdUniqueness($sEmailId);
			$sEmailId = preg_replace("/[ #\&\\%@=\/\\\:;,\.\+'\"\^`~\|\!\?\*$#<>()\[\]\{\}]/i", "", $sEmailId);  // -_ 제외한 특수 문자 제거
			// $sUserId = $this->_checkUserIdUniqueness($aUserId[0]);
			unset($aUserId);
		}
		// https://lynmp.com/ko/article/ad811c9dc5oi
		$sUserId = preg_replace("/[ #\&\\%@=\/\\\:;,\.\+'\"\^`~\|\!\?\*$#<>()\[\]\{\}]/i", "", $sUserId);  // -_ 제외한 특수 문자 제거
		if(strlen($sUserId) == 0)
			$sUserId = $this->_getRandStr(7);  // $sMobile.$this->_getRandStr(2);
		if(!preg_match('/^[0-9a-zA-Z]+([-_0-9a-zA-Z]+)*$/is', $sUserId))  // XE member ID rule
			$sUserId = $this->_getRandStr(7); //$sMobile.$this->_getRandStr(2);
		return strtolower($sUserId);
	}
/**
 * @brief XE email 규칙 위반하면 수정
 */
	private function _validateXeEmail($sEmail) 
	{
		if(strlen($sEmail)==0)  // 이메일이 공란이면
			return $this->_getRandStr(7, TRUE).'@e.c';
		
		$sEmail = str_replace(',' , '.', $sEmail); // co,kr 오류 수정
		$sEmail = str_replace(' ' , '', $sEmail); // 빈칸 오류 수정

		$aEmailInfo = explode('@', $sEmail);
		if(count($aEmailInfo)>2) // rudgml2474@naver.com@rudgml2474@naver.com
		{
			$sTmpEmailId = $aEmailInfo[0];
			$sTmpEmailHost = null;
			unset($aEmailInfo[0]);
			foreach($aEmailInfo as $_ => $sChunk)
			{
				if(strpos($sChunk, ".") !== false)  // catch host info, whatever
				{
					$sTmpEmailHost = $sChunk;
					break;
				}
			}
			$sEmail = $sTmpEmailId.'@'.$sTmpEmailHost;
		}
		// 그 이상의 이메일 주소 문법 오류는 허위 입력으로 처리함
		if(!preg_match('/^[\w-]+((?:\.|\+|\~)[\w-]+)*@[\w-]+(\.[\w-]+)+$/is', $sEmail))
			return $this->_getRandStr(7, TRUE).'@e.c';
		return $sEmail;
	}
/**
 * @brief ComUserCleaned tbl에 레코드 추가
 */
	private function _insertComUserCleaned($oMemberInfo) 
	{
		// clean addr info
		$oMemberInfo->ZIP_CODE = trim($oMemberInfo->ZIP_CODE);
		$oMemberInfo->ZONE_CODE = trim($oMemberInfo->ZONE_CODE);
		$oMemberInfo->ADDR = trim($oMemberInfo->ADDR);
		$oMemberInfo->ADDR_DETAIL = trim($oMemberInfo->ADDR_DETAIL);
		// clean addr info
	
		$sOriginNick = $oMemberInfo->NICK_NM;
		$oMemberInfo->NICK_NM = preg_replace("/\s+/","", $oMemberInfo->NICK_NM); // 닉네임의 모든 공백 제거
		if(strlen($oMemberInfo->NICK_NM)==0)
			$oMemberInfo->NICK_NM = $this->_getRandStr(7, TRUE);
		$oInsertRst = executeQuery('angeclub.insertTmpAdminComUserCleaned', $oMemberInfo);
		if(!$oInsertRst->toBool())
		{
			if(strpos($oInsertRst->message, "Duplicate entry '' for key 'uniq_NICK_NM'") !== false)
			{
				$oMemberInfo->NICK_NM = $this->_getRandStr(7, TRUE);
				$oInsertRstAgain = executeQuery('angeclub.insertTmpAdminComUserCleaned', $oMemberInfo);
				if(!$oInsertRstAgain->toBool())
				{
					echo 'duplicated user nick name error occured!!!<BR>';
					var_Dump($oMemberInfo);
					echo '<BR>';
					var_Dump($oInsertRstAgain->message);
					exit;
				}
				echo 'duplicated user nick name: '.$sOriginNick.' has been resolved to '.$oMemberInfo->NICK_NM.'<BR>';
				exit;
			}
			elseif(strpos($oInsertRst->message, "Duplicate entry '' for key 'uniq_USER_ID'") !== false)
			{
				$oMemberInfo->USER_ID = $this->_getRandStr(7, TRUE);
				$oInsertRstAgain = executeQuery('angeclub.insertTmpAdminComUserCleaned', $oMemberInfo);
				if(!$oInsertRstAgain->toBool())
				{
					echo 'duplicated user id name error occured!!!<BR>';
					var_Dump($oMemberInfo);
					echo '<BR>';
					var_Dump($oInsertRstAgain->message);
					exit;
				}
				echo 'duplicated user id: '.$sOriginNick.' has been resolved to '.$oMemberInfo->USER_ID.'<BR>';
				exit;
			}
			// exit;
		}
		unset($oInsertRst);
		return;
	}
/**
 * @brief 고유 핸폰 번호 기준으로 COM_USER 테이블 작성
 * NO와 USER_ID는 가장 마지막 회원 정보를 선택하고 중복 레코드는 mapper에 기록함
 * 아이디로 승인할 수 없는 아이디는 변형하고 mapper에 기록함
 * angeadmin4650, angestory, momsfood 아이디를 보존할 수 없는 이유: 중복되면 안되는 angeweb@ange.co.kr이라는 메일주소로 9게의 중복 회원, mook1004 가 보존됨
 */
	private function _buildupCleanupComUserList()
	{
		echo __FILE__.':'.__LINE__.'<BR>';
		ini_set('memory_limit', '2048M');  // php.ini default 512M
		set_time_limit(360);  // sec

		// 핸드폰 고유성이 확보된 COM_USER 테이블 구성
		$sSeqLogFilePath = './files/angeclub/2com_user_cleanup_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;

		$oArgs = new stdClass();
		$oArgs->NO = 682224;
		$oArgs->page = $nCurPage;
		$oArgs->list_count = 75000;
		$oRst = executeQueryArray('angeclub.getTmpAdminComUserPagination', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';

		$oArgs = new stdClass();
		$oInsertArgs = new stdClass();
		$aIgnoreMobileNo = ["00000000000", "01011112222", "01012345678", "01012341234", "123123123", 
							"01044444444", "010400000", "01023232323", "01012121212", "010000000000",
							"0110000000", "0109999999", "01100000000", "01033333333", "0103333333",
							"01099999999", "01055555555", "01022222222", "0000000000", "000-000-0000", 
							"0101231234", "0102222222", "010000000", "000000000", "000-000-000", 
							"01058482576", "0101234567", "0101111111", "01011111111", "0100000000", 
							"01075742379", "01000000000", "0107777777", '01700000000', '000-0000-0000',
							"01000000001", "01011112222",
							"01023221128", // cys1128@marveltree.com
							"01027137400" // hacker9100@gmail.com, ilsimx@nate.com
							];
		foreach($oRst->data as $nIdx=>$oUser)
		{
			if(strlen($oUser->PHONE_2) < 9)  // 0101234567
				continue;
			if(in_array($oUser->PHONE_2, $aIgnoreMobileNo))
				continue;

			// echo $oUser->NO.' - '.$oUser->PHONE_2.'<BR>';
			$oArgs->PHONE_2 = $oUser->PHONE_2;
			$oDupRst = executeQueryArray('angeclub.getTmpAdminDuplicatedMobileList', $oArgs);
			$nDuplication = count($oDupRst->data);
			if($nDuplication == 0)  // 핸폰 번호 고유성 확인된 회원 정보를 등록
			{
				// echo '핸폰 번호 고유성 확인된 회원 정보를 등록<BR>';
				$sCleanedUserId = $this->_cleanUserId($oUser->USER_ID, $oUser->EMAIL, $oUser->PHONE_2);
				if($oUser->USER_ID != $sCleanedUserId)  // 최종 처리된 ID가 원본 ID와 다르면 ALIAS에 추가함
				{
					$oTransferArg = new stdClass();
					$oTransferArg->NO_SOURCE = $oUser->NO;
					$oTransferArg->USER_ID_SOURCE = $oUser->USER_ID;
					$oTransferArg->NO_DEST = $oUser->NO;
					$oTransferArg->USER_ID_DEST = $sCleanedUserId;
					$oTransferArg->mobile = $oUser->PHONE_2;
					$oInsertTransferRst = executeQuery('angeclub.insertTmpAdminComUserTransferred', $oTransferArg);
					// echo '<BR>핸폰 번호 고유성 확인된 회원 정보의 ID 변경<BR>';
					// var_dump($oTransferArg);
					// echo '<BR><BR>';
					unset($oTransferArg);
					unset($oInsertRst);
				}
				$oUser->USER_ID = $sCleanedUserId;
				$sCleanedEmail = $this->_validateXeEmail($oUser->EMAIL);
				if($oUser->EMAIL != $sCleanedEmail)
				{
					echo 'invalid eamil: '.$oUser->EMAIL.' has been corrected to '.$sCleanedEmail.'<BR>';
					$oUser->EMAIL = $sCleanedEmail;
				}
				$this->_insertComUserCleaned($oUser);
			}
			elseif($nDuplication>1)  // 다수의 동일 핸폰 번호가 등록된 회원 정보를 정리하고 등록
			{
				if($oDupRst->data[$nDuplication-1]->NO == $oUser->NO)
				{
					// echo ': compress '.$nDuplication.'-times duplicated mobile info!<BR>';
					// echo '<BR>';
					$oLatestMemberInfo = $oDupRst->data[$nDuplication-1];
					$aTmpUserId = [];
					$nFinalChoosedNo = null;
					$sFinalChoosedUserId = null;
					foreach($oDupRst->data as $nDupIdx=>$oDupUser)
					{
						if(strpos($oDupUser->USER_ID, '@') === false)  // USER_ID가 이메일이 아니면
						{ 
							$nFinalChoosedNo = $oDupUser->NO;
							$sFinalChoosedUserId = $oDupUser->USER_ID;
						}
						$aTmpUserId[$oDupUser->NO] = $oDupUser->USER_ID;
					}

					$bVeryComplexSituation = FALSE;
					if(!$nFinalChoosedNo && !$sFinalChoosedUserId)  // 등록된 모든 ID가 이메일 일때
					{
						$nFinalChoosedNo = $oLatestMemberInfo->NO;
						$aUserId = explode('@', $oLatestMemberInfo->USER_ID);
						$sFinalChoosedUserId = $this->_checkUserIdUniqueness($aUserId[0]);
						unset($aUserId);
						$sCleanedUserId = $this->_cleanUserId($sFinalChoosedUserId, $oLatestMemberInfo->EMAIL, $oLatestMemberInfo->PHONE_2);
						// 최종 처리된 ID가 원본 ID와 달라서 ALIAS에 추가함
						$oTransferArg = new stdClass();
						$oTransferArg->NO_SOURCE = $nFinalChoosedNo;
						$oTransferArg->USER_ID_SOURCE = $oLatestMemberInfo->USER_ID;
						$oTransferArg->NO_DEST = $oLatestMemberInfo->NO;
						$oTransferArg->USER_ID_DEST = $sCleanedUserId;
						$oTransferArg->mobile = $oLatestMemberInfo->PHONE_2;
						$oInsertTransferRst = executeQuery('angeclub.insertTmpAdminComUserTransferred', $oTransferArg);
						// echo '<BR>중복 핸폰 번호이며 등록된 모든 ID가 이메일인 회원 정보의 ID 변경<BR>';
						// var_dump($oTransferArg);
						// echo '<BR><BR>';
						unset($oTransferArg);
						unset($oInsertRst);
						$bVeryComplexSituation = TRUE;
					}
					else
					{
						$sCleanedUserId = $this->_cleanUserId($sFinalChoosedUserId, $oLatestMemberInfo->EMAIL, $oLatestMemberInfo->PHONE_2);
						if($sFinalChoosedUserId != $sCleanedUserId)
						{
							// 최종 처리된 ID가 원본 ID와 달라서 ALIAS에 추가함
							$oTransferArg = new stdClass();
							$oTransferArg->NO_SOURCE = $nFinalChoosedNo;
							$oTransferArg->USER_ID_SOURCE = $sFinalChoosedUserId;
							$oTransferArg->NO_DEST = $nFinalChoosedNo;
							$oTransferArg->USER_ID_DEST = $sCleanedUserId;
							$oTransferArg->mobile = $oLatestMemberInfo->PHONE_2;
							$oInsertTransferRst = executeQuery('angeclub.insertTmpAdminComUserTransferred', $oTransferArg);
							// echo '<BR>중복 핸폰 번호이며 등록된 모든 ID가 이메일은 아니지만 ID 검사 실패한 회원 정보의 ID 변경<BR>';
							// var_dump($oTransferArg);
							// echo '<BR><BR>';
							unset($oTransferArg);
							unset($oInsertRst);
							$bVeryComplexSituation = TRUE;
						}
					}
					$sCleanedEmail = $this->_validateXeEmail($oLatestMemberInfo->EMAIL);
					if($oLatestMemberInfo->EMAIL != $sCleanedEmail)
					{
						echo 'invalid eamil: '.$oLatestMemberInfo->EMAIL.' has been corrected to '.$sCleanedEmail.'<BR>';
						$oLatestMemberInfo->EMAIL = $sCleanedEmail;
					}
					$oLatestMemberInfo->NO = $nFinalChoosedNo;
					$oLatestMemberInfo->USER_ID = $sCleanedUserId;
					$this->_insertComUserCleaned($oLatestMemberInfo);
					unset($nFinalChoosedNo, $sFinalChoosedUserId, $aFinalNoAlias,$aFinalUserIdAlias);

					// 집계된 alias NO USER_ID 등록
					unset($aTmpUserId[$oLatestMemberInfo->NO]); // transferred tbl에 sFinalChoosedUserId 중복 등록 방지
					$oTransferArg = new stdClass();
					foreach($aTmpUserId as $nNo=>$sUserId)
					{
						$oTransferArg->NO_SOURCE = $nNo;
						$oTransferArg->USER_ID_SOURCE = $sUserId;
						$oTransferArg->NO_DEST = $oLatestMemberInfo->NO;
						$oTransferArg->USER_ID_DEST = $oLatestMemberInfo->USER_ID;
						$oTransferArg->mobile = $oLatestMemberInfo->PHONE_2;
						$oInsertTransferRst = executeQuery('angeclub.insertTmpAdminComUserTransferred', $oTransferArg);
						// echo '<BR>중복 핸폰 번호의 ID ALIAS 등록<BR>';
						// var_dump($oTransferArg);
						// echo '<BR>';
						unset($oInsertRst);
					}
					unset($aTmpNo, $aTmpUserId, $oTransferArg, $oLatestMemberInfo);
					
					if($bVeryComplexSituation)
					{
						;//echo 'VeryComplexSituation<BR>';
						// exit;
					}
					// exit;
				}
				else
					; // echo ': pass '.$nDuplication.'-times duplicated mobile '.$oUser->PHONE_2.' info!<BR>';
				unset($oDupRst);
			}
		}
		unset($oArgs);
		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!<BR>wait for DB proc completion!<BR><BR>';
	}
/**
 * @brief 중복 핸폰 번호 목록을 별도 테이블로 추출
 */
	private function _retreiveDuplicatedMobileInfo()
	{
		// $oInsertArgs->NO = $oDupUser->NO;  // ["NO"]=> int(1) 
		// $oInsertArgs->USER_ID = $oDupUser->USER_ID;  // ["USER_ID"]=> string(10) "seunghee13" 
		// $oInsertArgs->USER_NM = $oDupUser->USER_NM;  // ["USER_NM"]=> string(9) "백승희" 
		// $oInsertArgs->NICK_NM = $oDupUser->NICK_NM;  // ["NICK_NM"]=> string(12) "돌핀엄마" 
		// $oInsertArgs->PASSWORD = $oDupUser->PASSWORD;  // ["PASSWORD"]=> string(77) "sha256:1000:yyE54pLeEC4FBzbBnwzbRo1lF92UigE4:RRkphOoLqQQuhYxP+ylQ807I8M7uj+QE"  
		// $oInsertArgs->BIRTH = $oDupUser->BIRTH;  // ["BIRTH"]=> string(8) "19910925" 
		// $oInsertArgs->ZONE_CODE = $oDupUser->ZONE_CODE;  // ["ZONE_CODE"]=> string(0) "" 
		// $oInsertArgs->ZIP_CODE = $oDupUser->ZIP_CODE;  // ["ZIP_CODE"]=> string(6) "365831" 
		// $oInsertArgs->ADDR = $oDupUser->ADDR;  // ["ADDR"]=> string(29) "충북 진천군 광혜원면" 
		// $oInsertArgs->ADDR_DETAIL = $oDupUser->ADDR_DETAIL;  // ["ADDR_DETAIL"]=> string(46) "광혜원리 코아루아파트 107동 1102호" 
		// $oInsertArgs->PHONE_2 = $oDupUser->PHONE_2;  // ["PHONE_2"]=> string(11) "01091797386" 
		// $oInsertArgs->EMAIL = $oDupUser->EMAIL;  // ["EMAIL"]=> string(22) "babydolphin9@naver.com" 
		// $oInsertArgs->SEX_GB = $oDupUser->SEX_GB;   // ["SEX_GB"]=> string(1) "F" 
		// $oInsertArgs->CLUB_INT = $oDupUser->CLUB_INT;  // ["CLUB_INT"]=> NULL 
		// $oInsertArgs->REG_DT = $oDupUser->REG_DT;  // ["REG_DT"]=> string(19) "2012-01-26 21:54:07" 
		// $oInsertArgs->CLUB_REG_DT = $oDupUser->CLUB_REG_DT;  // ["CLUB_REG_DT"]=> NULL 
		// $oInsertArgs->FINAL_LOGIN_DT = $oDupUser->FINAL_LOGIN_DT;  // ["FINAL_LOGIN_DT"]=> string(19) "2018-03-21 13:48:08" 
		// $oInsertArgs->PREGNENT_FL = $oDupUser->PREGNENT_FL;  // ["PREGNENT_FL"]=> string(1) "N" 
		// $oInsertArgs->CONTACT_ID = $oDupUser->CONTACT_ID;  // ["CONTACT_ID"]=> string(0) "" 
		// $oInsertArgs->CARE_AREA = $oDupUser->CARE_AREA;  // ["CARE_AREA"]=> NULL 
		// $oInsertArgs->CARE_CENTER = $oDupUser->CARE_CENTER;  // ["CARE_CENTER"]=> string(0) "" 
		// $oInsertArgs->CENTER_CNT = $oDupUser->CENTER_CNT;  // ["CENTER_CNT"]=> int(0) 
		// $oInsertArgs->CLUBEDU_CNT = $oDupUser->CLUBEDU_CNT;  // ["CLUBEDU_CNT"]=> NULL 
		// $oInsertArgs->EN_ANGE_EMAIL_FL = $oDupUser->EN_ANGE_EMAIL_FL;  // ["EN_ANGE_EMAIL_FL"]=> string(1) "Y"
		// $oInsertArgs->EN_ANGE_SMS_FL = $oDupUser->EN_ANGE_SMS_FL;  // ["EN_ANGE_SMS_FL"]=> string(1) "Y" 
		// $oInsertArgs->EN_CLUB_SPONSOR_FL = $oDupUser->EN_CLUB_SPONSOR_FL;  // ["EN_CLUB_SPONSOR_FL"]=> string(1) "Y" 
		// $oInsertArgs->EN_ANGE_ADDR_FL = $oDupUser->EN_ANGE_ADDR_FL;  // ["EN_ANGE_ADDR_FL
		ini_set('memory_limit', '1024M');  // php.ini 512M
		set_time_limit(360);  // sec

		// 회원 정보에서 중복된 핸드폰 번호 발견하면 중복 번호 테이블에 기록
		$sSeqLogFilePath = './files/angeclub/1com_user_duplicated_mobile_seq.txt';
		$sSeqLogFileContent = FileHandler::readFile($sSeqLogFilePath);
		if($sSeqLogFileContent)
			$nCurPage = (int)$sSeqLogFileContent;
		else
			$nCurPage = 1;

		$oArgs = new stdClass();
		$oArgs->NO = 682224;
		$oArgs->page = $nCurPage;
		$oRst = executeQueryArray('angeclub.getTmpAdminComUserPagination', $oArgs);
		unset($oArgs);
		echo count($oRst->data).' records has been detected<BR>';

		$oArgs = new stdClass();
		$oInsertArgs = new stdClass();
		$nCnt = 1;
		foreach($oRst->data as $nIdx=>$oUser)
		{
			if(strlen($oUser->PHONE_2) < 9)  // 0101234567
				continue;
			echo $nCnt++.' - '.$oUser->NO.' - '.$oUser->PHONE_2.'<BR>';
			$oArgs->PHONE_2 = $oUser->PHONE_2;
			$oDupRst = executeQueryArray('angeclub.getTmpAdminComUserDuplicatedMobile', $oArgs);
			$nDuplication = count($oDupRst->data);
			if($nDuplication>1)
			{
				echo ':'.$nDuplication.'-times duplicated mobile found!<BR>';
				foreach($oDupRst->data as $nDupIdx=>$oDupUser)
				{
					$oInsertRst = executeQuery('angeclub.insertTmpAdminComUserDuplicatedMobile', $oDupUser);
					if(!$oInsertRst->toBool())
					{
						if(strpos($oInsertRst->message, 'Duplicate entry') === false)
						{  
							var_Dump($oInsertRst->message);
							echo 'error occured!!!';
							exit;
						} 
					}
				}
			}
		}
		unset($oArgs);
		FileHandler::writeFile($sSeqLogFilePath, ++$nCurPage);
		echo '<BR><BR>succeed!<BR>wait for DB proc completion!<BR><BR>';
	}
/**
 * @brief 
 */
	public function procAngeclubAdminDelete()
	{
		$module_srl = Context::get('module_srl');
		// delete docs belongint to the module
		$output = $this->_deleteAllDocsByModule( $module_srl );
		if( !$output->toBool() )
			return $output;

		// delete designated module
		
		// Get an original
		$oModuleController = getController('module');
		$output = $oModuleController->deleteModule($module_srl);
		if(!$output->toBool()) 
			return $output;

		$this->add('module','page');
		$this->add('page',Context::get('page'));
		$this->setMessage('success_deleted');

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAngemomboxAdminIndex');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief 
 **/
	public function procAngeclubAdminConfig()
	{
        $oArgs = new stdClass();
		$sPasswordPrefix = Context::get('password_prefix');
        if(strlen($sPasswordPrefix))
            $oArgs->password_prefix = $sPasswordPrefix;
		
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
        
		$sAllowInsecureConnections = Context::get('allow_insecure_connections');
        if(strlen($sAllowInsecureConnections))
            $oArgs->allow_insecure_connections = $sAllowInsecureConnections;
		$sSenderEmailHost = Context::get('sender_email_host');
		if(strlen($sSenderEmailHost))
			$oArgs->sender_email_host = $sSenderEmailHost;
		$sSenderEmailId = Context::get('sender_email_id');
		if(strlen($sSenderEmailId))
			$oArgs->sender_email_id = $sSenderEmailId;
		$sSenderEmailPw= Context::get('sender_email_pw');
		if(strlen($sSenderEmailPw))
			$oArgs->sender_email_pw = $sSenderEmailPw;
	
		$oRst = $this->_saveModuleConfig($oArgs);
		if(!$oRst->toBool())
			$this->setMessage('error_occured');
		else
			$this->setMessage('success_updated');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispAngeclubAdminConfig'));
	}
/**
 * @brief arrange and save module config
 **/
	private function _saveModuleConfig($oArgs)
	{
		$oModuleControll = getController('module');
		return $oModuleControll->insertModuleConfig('angeclub', $oArgs);
	}
/**
 * @brief module module
 */
	public function procAngeclubAdminUpdate()
	{
		$this->procAngeclubAdminInsert();
	}
/**
 * @brief add module
 */
	public function procAngeclubAdminInsert()
	{
		$oArgs = Context::getRequestVars();
		$oArgs->module = 'angeclub';
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
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'module_srl', $oRst->get('module_srl'), 'act', 'dispAngeclubAdminModDetail');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief add module
 */
	private function _saveConfigByMid($oArgs)
	{
		$args = $oArgs;
		if($args->module_srl)
		{
			$oModuleModel = getModel('module');
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
			unset($module_info->is_skin_fix); // 기본 스킨 고정을 해제함
			unset($module_info->is_mskin_fix); // 기본 스킨 고정을 해제함
			if($module_info->module_srl != $args->module_srl)
				unset($args->module_srl);
			else
			{
				foreach($args as $key=>$val)
					$module_info->{$key} = $val;
				$args = $module_info;
			}
		}
		$oModuleController = getController('module');
		// Insert/update depending on module_srl
		if(!$args->module_srl)
			$output = $oModuleController->insertModule($args);
		else
			$output = $oModuleController->updateModule($args);
		return $output;
	}
}