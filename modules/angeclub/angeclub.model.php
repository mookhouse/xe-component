<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubModel
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubModel
**/ 
class angeclubModel extends module
{
/**
 * @brief initialization
 **/
	function init()
	{
	}
/**
 * @brief return baby gender list
 **/
	public function getMomList($oInParams)
	{
		unset($oInParams->error_return_url);
		unset($oInParams->mid);
		unset($oInParams->act);
		$aCcIdx = $this->getCenterInfoByName($oInParams->search_center);
		
		unset($oInParams->search_center);
		if(count($aCcIdx))
			$oInParams->a_cc_idx = $aCcIdx;
		$oRst = executeQueryArray('angeclub.getMomList', $oInParams);
		$oMemberModel = &getModel('member');
		$aMemberInfo = [];
		foreach($oRst->data as $_=>$oSingleMom)
		{
			$aEmailInfo = explode('@', $oSingleMom->email_address);
			$oSingleMom->email_address = $this->_maskMbString($aEmailInfo[0],2, 2).'@'.$aEmailInfo[1];  // 이메일 아이디 마스킹
			$oSingleMom->mobile = $this->_maskMbString($oSingleMom->mobile,5, 3);  // 핸폰 번호 마스킹
			unset($aEmailInfo);
			if($oSingleMom->member_srl_staff)
			{
				if(!$aMemberInfo[$oSingleMom->member_srl_staff])
				{
					$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oSingleMom->member_srl_staff);
					$aMemberInfo[$oMemberInfo->member_srl] = $oMemberInfo;
				}
				$oSingleMom->staff_name = $aMemberInfo[$oMemberInfo->member_srl]->user_name;
			}
			// else
			// 	$oSingleMom->staff_name = '담당자 없음';
			unset($oMemberInfo);
		}
		unset($aMemberInfo);
		unset($oMemberModel);
		return $oRst;
	}
/**
 * @brief return baby gender list
 **/
    public function getBabyGender()
    {
        $oAngeclub = &getClass('angeclub');
        $aBabyGenderList = $oAngeclub->_g_aBabyGender;
        unset($oAngeclub);
        return $aBabyGenderList;
    }
/**
 * @brief return club staff list for manager
 **/
	public function getClubEffectiveUserFullInfo($nModuleSrl)
	{
		$oArgs = new stdClass;
		$oArgs->module_srl = $nModuleSrl;
		$oGrantRst = executeQueryArray('module.getModuleGrants', $oArgs);
		unset($oArgs);
		$aGrantedGrpSrl = [];
		foreach($oGrantRst->data as $_=>$oSingleGrant)
			$aGrantedGrpSrl[$oSingleGrant->group_srl] = $oSingleGrant->group_srl;
		if(count($aGrantedGrpSrl))
		{
			$oArgs = new stdClass;
			$oArgs->selected_group_srl = $aGrantedGrpSrl;
			$oArgs->list_count = 100;  // 현실적으로 클럽 간호사는 100명 이하임
			$oMemberWithinGrpRst = executeQuery('member.getMemberListWithinGroup', $oArgs);
			unset($oArgs);

			$oMemberModel = getModel('member');
			foreach($oMemberWithinGrpRst->data as $_=>$oClubMember)
			{
				$aMemberGrp = $oMemberModel->getMemberGroups($oClubMember->member_srl);
				unset($aMemberGrp[1], $aMemberGrp[2], $aMemberGrp[3]);  // 기본 생성 그룹 제거
				$aGrpBelonged = [];
				foreach($aMemberGrp as $nGrpSrl => $sGrpLabel)
				{
					if($aGrantedGrpSrl[$nGrpSrl])
						$aGrpBelonged[] = $sGrpLabel;
				}
				$oClubMember->grant_label = implode(',', $aGrpBelonged);
			}
			unset($aGrantedGrpSrl);
			unset($oMemberModel);
		}
		else
			$oMemberWithinGrpRst = new BaseObject();  // return none
	
		return $oMemberWithinGrpRst;
	}
/**
 * @brief return period performance by city
 **/
	public function getPeriodPerfByCity($nModuleSrl, $sBeginDate, $sEndDate)
	{
		$oRst = $this->getCenterAreaJsonStringfied();
		$aCity = $oRst->get('aCity');
		// Context::set('sJsonAreaStringfy', $oRst->get('aJsonStringfyArea'));

		// city 별 조리원 idx 가져오기
		$oArg = new stdClass();
		$aTmpCenterByCity = [];
		foreach($aCity as $sCityName=>$_)
		{
			$oArgs->cc_city = $sCityName;
			$oRst = executeQuery('angeclub.getCenterByCityAreaName', $oArgs);
			if(!$oRst->toBool())
				return $oRst;

			$aCenterIdxList = [];
			foreach($oRst->data as $_=>$oCenter)
				$aCenterIdxList[] = $oCenter->cc_idx;
			$aTmpCenterByCity[$sCityName] = $aCenterIdxList;
			unset($oRst);
				// exit;
			
		}
		unset($oArg);

		// city 별 조리원의 성과 요약하기
		$oArgs = new stdClass;
		$oArgs->begin_date = $sBeginDate;
		$oArgs->end_date = $sEndDate;
		$aTmpStatisticsByCity = [];
		$aSort = [];
		foreach($aTmpCenterByCity as $sCityName=>$aCenterList)
		{
			$oArgs->a_cc_idx = $aCenterList;
			$oRst = executeQuery('angeclub.getWorkDiaryLogPerformanceByCity', $oArgs);
			if(!$oRst->toBool())
				return $oRst;

			if($oRst->data->gross_new_member || $oRst->data->gross_update_member || $oRst->data->gross_new_center)
			{
				$oSingleCityStat = new stdClass();
				$oSingleCityStat->city_name = $sCityName;
				$oSingleCityStat->gross_new_member = $oRst->data->gross_new_member;
				$oSingleCityStat->gross_update_member = $oRst->data->gross_update_member;
				$oSingleCityStat->gross_new_center = $oRst->data->gross_new_center;
				$nGross = $oRst->data->gross_new_member + $oRst->data->gross_update_member + $oRst->data->gross_new_center;
				$aSort[$sCityName] = $nGross;
				$aTmpStatisticsByCity[$sCityName] = $oSingleCityStat;
			}
			unset($oRst);
		}
		unset($oArg);
		ksort($aSort);

		$aStatisticsByCity = [];
		foreach($aSort as $sCityName=>$nGross)
			$aStatisticsByCity[] = $aTmpStatisticsByCity[$sCityName];
		unset($aTmpStatisticsByCity);
		$oRet = new BaseObject();
		$oRet->add('aStatisticsByCity', $aStatisticsByCity);
		return $oRet;
	}
/**
 * @brief return period performance by staff
 **/
	public function getPeriodPerfByStaff($nModuleSrl, $sBeginDate, $sEndDate)
	{
		$aUserInfo = $this->getClubEffectiveUser($nModuleSrl);
		
		$oArgs = new stdClass;
		$oArgs->begin_date = $sBeginDate;
		$oArgs->end_date = $sEndDate;

		$aTmpStatisticsByStaffMemberSrl = [];
		$aSort = [];
		foreach($aUserInfo as $nStaffMemberSrl=>$sStaffName)
		{
			$oArgs->member_srl_staff = $nStaffMemberSrl;
			$oRst = executeQuery('angeclub.getWorkDiaryLogPerformanceByMemberSrl', $oArgs);
			if(!$oRst->toBool())
				return $oRst;
			$oSingleStat = new stdClass();
			$oSingleStat->member_srl = $nStaffMemberSrl;
			$oSingleStat->staff_name = $sStaffName;
			$oSingleStat->gross_new_member = $oRst->data->gross_new_member;
			$oSingleStat->gross_update_member = $oRst->data->gross_update_member;
			$oSingleStat->gross_new_center = $oRst->data->gross_new_center;

			$nGross = $oRst->data->gross_new_member + $oRst->data->gross_update_member + $oRst->data->gross_new_center;
			$aSort[$nStaffMemberSrl] = $nGross;
			unset($oRst);
			$aTmpStatisticsByStaffMemberSrl[$nStaffMemberSrl] = $oSingleStat;
		}
		unset($oArgs);
		ksort($aSort);

		$aStatisticsByStaffMemberSrl = [];
		foreach($aSort as $nStaffMemberSrl=>$nGross)
			$aStatisticsByStaffMemberSrl[] = $aTmpStatisticsByStaffMemberSrl[$nStaffMemberSrl];
		unset($aTmpStatisticsByStaffMemberSrl);
		$oRet = new BaseObject();
		$oRet->add('aStatisticsByStaffMemberSrl', $aStatisticsByStaffMemberSrl);
		return $oRet;
	}
/**
 * @brief return club staff list for UX
 **/
	public function getClubEffectiveUser($nModuleSrl)
	{
		$oArgs = new stdClass;
		$oArgs->module_srl = $nModuleSrl;
		$oGrantRst = executeQueryArray('module.getModuleGrants', $oArgs);
		unset($oArgs);
		$aGrantedGrpSrl = [];
		foreach($oGrantRst->data as $_=>$oSingleGrant)
			$aGrantedGrpSrl[$oSingleGrant->group_srl] = $oSingleGrant->group_srl;
		unset($oGrantRst);
		$oArgs = new stdClass;
		$oArgs->selected_group_srl = $aGrantedGrpSrl;
		unset($aGrantedGrpSrl);
		$oArgs->list_count = 100;  // 현실적으로 클럽 간호사는 100명 이하임
		$oMemberWithinGrpRst = executeQuery('member.getMemberListWithinGroup', $oArgs);
		unset($oArgs);
		$aUserInfo = [];
		foreach($oMemberWithinGrpRst->data as $_=>$oClubMember)
			$aUserInfo[$oClubMember->member_srl] = $oClubMember->user_name;
		unset($oMemberWithinGrpRst);
		return $aUserInfo;
	}
/**
 * @brief return json stringfied center list by staff user id
 * for member add UI
 **/
	public function getCenterListByStaffIdJsonStringfiedForWorkDiary()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$oArgs = new stdClass();
		$oArgs->member_srl_staff = $oLoggedInfo->member_srl;
		$oArgs->cc_state = 1;
		$oRst = executeQueryArray('angeclub.getCenterByNurse', $oArgs);
		if(!$oRst->toBool())
			return $oRst;
		unset($oArgs);
		$aJsonStringfyCenterByStaffId = [];   // "1042":{ "area":"강남", "name":"강남 SK" }, 
		$aArea = [];
		foreach($oRst->data as $_=>$oCenter)
		{
			$aJsonStringfyCenterByStaffId[] = '"'.$oCenter->cc_idx.'":{"area":"'.$oCenter->cc_area.'", "name":"'.$oCenter->cc_name.'"}';
			$aArea[$oCenter->cc_area] = 1;
		}
		unset($oRst);
		$oRst = new BaseObject();
		$oRst->add('aArea', $aArea);
		$oRst->add('aJsonStringfyCenterByStaff', implode( ',', $aJsonStringfyCenterByStaffId));
		return $oRst;
	}
/**
 * @brief return work diary detail
 **/
	public function getWorkDiaryByIdx($nClIdx)
	{
		$oArgs = new stdClass();
		$oArgs->cl_idx = $nClIdx;
		$oRst = executeQuery('angeclub.getWorkDiaryByIdx', $oArgs);
		unset($oArgs);

		$oMemberModel = &getModel('member');
		$oStaffMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oRst->data->member_srl_staff);
		$oRst->data->user_name = $oStaffMemberInfo->user_name;
		unset($oMemberModel);
		unset($oStaffMemberInfo);
		return $oRst;
	}
/**
 * @brief return json stringfied center list by staff user id
 * for member add UI
 **/
	public function getCenterListByStaffIdJsonStringfiedForMemberAdd()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$oArgs = new stdClass();
		$oArgs->member_srl_staff = $oLoggedInfo->member_srl;
		$oArgs->cc_state = 1;
		$oRst = executeQueryArray('angeclub.getCenterByNurse', $oArgs);
		if(!$oRst->toBool())
			return $oRst;
		unset($oArgs);
		$aJsonStringfyCenterByStaffId = [];   // "강남 SK":{ "area":"강남", "name":"강남 SK" },
		$aArea = [];
		foreach($oRst->data as $_=>$oCenter)
		{
			$aJsonStringfyCenterByStaffId[] = '"'.$oCenter->cc_name.'":{"idx":"'.$oCenter->cc_idx.'", "area":"'.$oCenter->cc_area.'", "name":"'.$oCenter->cc_name.'"}';
			$aArea[$oCenter->cc_area] = 1;
		}
		unset($oRst);
		$oRst = new BaseObject();
		$oRst->add('aArea', $aArea);
		$oRst->add('aJsonStringfyCenterByStaff', implode( ',', $aJsonStringfyCenterByStaffId));
		return $oRst;
	}
/**
 * @brief check duplicated user via ajax
 **/
	public function getDuplicatedUserAjax()
	{
		$sCheckAttr = Context::get('check_attr');
		if(!$sCheckAttr)
			return new BaseObject(-1, '잘못된 회원 속성입니다.');
			
		$oMemberModel = &getModel('member');
		$bDuplicated = 0;
		switch($sCheckAttr)
		{
			case 'mobile':
				$oMemberInfo = $oMemberModel->getMemberInfoByMobile(Context::get('mobile'));
				if($oMemberInfo->member_srl)
					$bDuplicated = 1;
				break;
			case 'user_id':
				$oMemberInfo = $oMemberModel->getMemberInfoByUserID(Context::get('user_id'));
				if($oMemberInfo->member_srl)
					$bDuplicated = 1;
				break;
			case 'email':
				$oMemberInfo = $oMemberModel->getMemberInfoByEmailAddress(Context::get('email'));
				if($oMemberInfo->member_srl)
					$bDuplicated = 1;
				break;
		}
        unset($oMemberInfo->allow_mailing);
        unset($oMemberInfo->allow_message);
        unset($oMemberInfo->blog);
        unset($oMemberInfo->change_password_date);
        unset($oMemberInfo->email_address);
        unset($oMemberInfo->denied);
        unset($oMemberInfo->description);
        unset($oMemberInfo->profile_image);
        unset($oMemberInfo->referral);
        unset($oMemberInfo->find_account_answer);
        unset($oMemberInfo->find_account_question);
        unset($oMemberInfo->group_list);
        unset($oMemberInfo->homepage);
        unset($oMemberInfo->image_mark);
        unset($oMemberInfo->image_name);
        unset($oMemberInfo->is_admin);
        unset($oMemberInfo->limit_date);
        unset($oMemberInfo->list_order);
        unset($oMemberInfo->password);
        unset($oMemberInfo->signature);

		$oModuleInfo = $this->getModuleConfig();
		$sMemberAddrFieldName = $oModuleInfo->member_addr_field_name;
		if($sMemberAddrFieldName != 'address')
		{
			$oMemberInfo->address = $oMemberInfo->$sMemberAddrFieldName;
			unset($oMemberInfo->$sMemberAddrFieldName);
		}
		unset($oModuleInfo);

		$this->add('isDuplicated', $bDuplicated);
        $this->add('sJsonMemberInfo', json_encode($oMemberInfo));
		unset($oMemberInfo);
		unset($oMemberModel);
		return new BaseObject();
	}
/**
 * @brief return center list by center name
 **/
	public function getCenterInfoByName($sCenterName)
	{
		$aCcIdx = [];
		if(strlen($sCenterName) < 2)
			return $aCcIdx;	
		$oArgs = new stdClass();
		$oArgs->cc_name = $sCenterName;
		$oRst = executeQueryArray('angeclub.getCenterByCenterName', $oArgs);
		unset($oArgs);
		// var_dump($oRst->data);
		$aCcIdx = [];
		foreach($oRst->data as $_=>$oCenter)
			$aCcIdx[] = $oCenter->cc_idx;
		unset($oRst);
		return $aCcIdx;
	}
/**
 * @brief return work diary list
 **/
	public function getWorkDiaryListPagination($bStaffMode=FALSE)
	{
		$oInParams = Context::getRequestVars();
		unset($oInParams->error_return_url);
		unset($oInParams->mid);
		unset($oInParams->act);
		
		$oCenterArgs = new stdClass();
		if($oInParams->cc_idx)  // ["cc_idx"]=> string(6) "137" 
		{
			$oCenterArgs->cc_idx = $oInParams->cc_idx;
			Context::set('cc_idx', $oInParams->cc_idx);  // for ux on screen
		}
		if($oInParams->cc_city)  // ["cc_city"]=> string(6) "서울" 
		{
			$oCenterArgs->cc_city = $oInParams->cc_city;
			Context::set('cc_city', $oInParams->cc_city);  // for ux on screen
		}
		if($oInParams->cc_area)  // ["cc_area"]=> string(6) "강북" 
		{
			$oCenterArgs->cc_area = $oInParams->cc_area;
			Context::set('cc_area', $oInParams->cc_area);  // for ux on screen
		}
		
		$aCenterIdx = [];
		if($oCenterArgs->cc_idx || $oCenterArgs->cc_city || $oCenterArgs->cc_area)
		{
			$oCenterRst = executeQueryArray('angeclub.getCenterByCityAreaName', $oCenterArgs);
			foreach($oCenterRst->data as $_=>$oSingleCenter)
				$aCenterIdx[] = $oSingleCenter->cc_idx;
			unset($oCenterRst);
		}
		unset($oCenterArgs);
	
		$oArgs = new stdClass();
		$oArgs->page = Context::get('page');
		if($oInParams->member_srl_staff)  // 총괄 화면에서 개별 스탭 검색 버튼
		{
			$oArgs->member_srl_staff = $oInParams->member_srl_staff;  // 담당자 검색
			Context::set('member_srl_staff', $oInParams->member_srl_staff);  // for ux on screen
		}
		if($bStaffMode)  // 일반 스태프용 목록 화면
		{
			$oLoggedInfo = Context::get('logged_info');
			if(!$oLoggedInfo)
				return new BaseObject(-1, 'msg_not_loggedin');
			$oArgs->member_srl_staff = $oLoggedInfo->member_srl;
		}
		if(count($aCenterIdx))
			$oArgs->a_cc_idx = $aCenterIdx;  // 지역별 조리원 검색

		// "search_start"]=> string(8) "20221207" ["search_end"]=> string(8) "20221214"
		$oRst = executeQueryArray('angeclub.getWorkDiaryLog', $oArgs);
		unset($oArgs);
		
		$oMemberModel = &getModel('member');
		$aCenterName = [];
		foreach($oRst->data as $_=>$oSingleWorkDiary)  // 조리원 idx를 조리원명으로 변경
		{
			// 시작 - 직원 이름 변환
			$oStaffMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oSingleWorkDiary->member_srl_staff);
			$oSingleWorkDiary->user_name = $oStaffMemberInfo->user_name;
			unset($oStaffMemberInfo);
			// 끝 - 직원 이름 변환
			if($aCenterName[$oSingleWorkDiary->cc_idx])
				$oSingleWorkDiary->cc_name = $aCenterName[$oSingleWorkDiary->cc_idx];
			else
			{
				$oCenterRst = $this->getCenterInfoByIdx($oSingleWorkDiary->cc_idx);
				$oSingleWorkDiary->cc_name = $oCenterRst->data->cc_name;
				$aCenterName[$oSingleWorkDiary->cc_idx] = $oCenterRst->data->cc_name;
				unset($oCenterRst);
			}
		}
		unset($aCenterName);
		return $oRst;
	}
/**
 * @brief return center list
 **/
	public function getCenterListPagination()
	{
		$oInParams = Context::getRequestVars();
		$sCenterState = $oInParams->category;
		$oArgs = new stdClass();
		$oArgs->page = Context::get('page');

		if($oInParams->member_srl_staff)
		{
			$oArgs->member_srl_staff = $oInParams->member_srl_staff;  // 담당자 검색
			Context::set('member_srl_staff', $oInParams->member_srl_staff);  // for ux on screen
		}
		else
			$oArgs->member_srl_staff = '0';  // 담당자 없는 센터 제외
				
		if($oInParams->cc_name)
		{
			$oArgs->cc_name = $oInParams->cc_name;
			Context::set('cc_name', $oInParams->cc_name);  // for ux on screen
		}
		if($oInParams->cc_city)
		{
			$oArgs->cc_city = $oInParams->cc_city;
			Context::set('cc_city', $oInParams->cc_city);  // for ux on screen
		}
		if($oInParams->cc_area)
		{
			$oArgs->cc_area = $oInParams->cc_area;
			Context::set('cc_area', $oInParams->cc_area);  // for ux on screen
		}

		if(is_null($sCenterState))  // 기본 화면
		{
			$oArgs->cc_state = 0;  // 폐업 센터 제외
			// select count(1) from club_center cc inner join club_user cu on cc.cu_id=cu.cu_id where 1=1 and cc.cc_state<>0
			if($oArgs->member_srl_staff == '0')
				$oRst = executeQueryArray('angeclub.getEffeciveCenter', $oArgs);
			else
				$oRst = executeQueryArray('angeclub.getEffeciveCenterByUserId', $oArgs);
		}
		else
		{
			$oArgs->cc_state = $sCenterState;
			if($oArgs->member_srl_staff == '0')
				$oRst = executeQueryArray('angeclub.getCenterByState', $oArgs);
			else
				$oRst = executeQueryArray('angeclub.getCenterByStateByUserId', $oArgs);
		}
		unset($oArgs);
		$oMemberModel = &getModel('member');
		foreach($oRst->data as $_=>$oSingleCenter)  // 담당자 member_srl을 담당자명으로 변경
		{
			$oStaffMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oSingleCenter->member_srl_staff);
			$oSingleCenter->user_name = $oStaffMemberInfo->user_name;
			unset($oStaffMemberInfo);
		}
		unset($oMemberModel);
		return $oRst;
	}
/**
 * @brief return json stringfied center area list
 **/
	public function getCenterAreaJsonStringfied()
    {
        $oRst = executeQueryArray('angeclub.getCenterAreaAll');
		if(!$oRst->toBool())
			return $oRst;
		$aJsonStringfyArea = [];
		$aCity = [];
		foreach($oRst->data as $_=>$oArea)
		{
			$aJsonStringfyArea[] = '"'.$oArea->ca_idx.'":{"city":"'.$oArea->ca_city.'", "area":"'.$oArea->ca_area.'"}';
			$aCity[$oArea->ca_city] = 1;
		}
		$oRst = new BaseObject();
		$oRst->add('aCity', $aCity);
		$oRst->add('aJsonStringfyArea', implode( ',', $aJsonStringfyArea));
		return $oRst;
    }
/**
 * @brief return center detail
 **/
	public function getCenterInfoByIdx($nCcIdx)
	{
		$oArgs = new stdClass();
		$oArgs->cc_idx = $nCcIdx;
		$oRst = executeQuery('angeclub.getCenterByIdx', $oArgs);
		unset($oArgs);
		$oMemberModel = &getModel('member');
		$oStaffMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oRst->data->member_srl_staff);
		$oRst->data->user_name = $oStaffMemberInfo->user_name;
		unset($oMemberModel);
		unset($oStaffMemberInfo);
		return $oRst;
	}
/**
 * @brief 모듈 default setting 불러오기
 */
	public function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		return $oModuleModel->getModuleConfig('angeclub');
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
}