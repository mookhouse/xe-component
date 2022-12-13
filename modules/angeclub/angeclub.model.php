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
 * @brief return club list
 **/
	public function getClubEffectiveUser()
	{
		$oArgs = new stdClass();
		$oArgs->cu_id = 'admin';  // 웹관리자 제외

		$oAngeclub = &getClass('angeclub');
		$aFlipped = array_flip($oAngeclub->_g_aClubUserState);
		unset($oAngeclub);
		$oArgs->cu_state = $aFlipped['탈퇴'];  // 퇴사자 제외
		$oRst = executeQueryArray('angeclub.getActiveClubUser', $oArgs);
		$aUserInfo = [];
		foreach($oRst->data as $nIdx=>$oUser)
			$aUserInfo[$oUser->cu_id] = $oUser->cu_name;
		return $aUserInfo;
	}
/**
 * @brief return json stringfied center list by staff user id
 **/
	public function getCenterListByStaffIdJsonStringfied()
	{
		$oLoggedInfo = Context::get('logged_info');
		if(!$oLoggedInfo)
			return new BaseObject(-1, 'msg_not_loggedin');

		$oArgs = new stdClass();
		$oArgs->cu_id = $oLoggedInfo->user_id;
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
 * @brief return center list
 **/
	public function getCenterListPagination()
	{
		$oInParams = Context::getRequestVars();
		$sCenterState = $oInParams->category;
		$oArgs = new stdClass();
		$oArgs->page = Context::get('page');

		if($oInParams->cu_id)
		{
			$oArgs->cu_id = $oInParams->cu_id;  // 담당자 검색
			Context::set('cu_id', $oInParams->cu_id);  // for ux on screen
		}
		else
			$oArgs->cu_id = '0';  // 담당자 없는 센터 제외
				
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
			if($oArgs->cu_id == '0')
				$oRst = executeQueryArray('angeclub.getEffeciveCenter', $oArgs);
			else
				$oRst = executeQueryArray('angeclub.getEffeciveCenterByUserId', $oArgs);
		}
		else
		{
			$oArgs->cc_state = $sCenterState;
			if($oArgs->cu_id == '0')
				$oRst = executeQueryArray('angeclub.getCenterByState', $oArgs);
			else
				$oRst = executeQueryArray('angeclub.getCenterByStateByUserId', $oArgs);
		}
		unset($oArgs);
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
}