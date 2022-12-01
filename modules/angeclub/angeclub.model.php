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
 * @brief return club list
 **/
	function getClubEffectiveUser()
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
 * @brief return club list
 **/
	function getClubListPagination()
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
    function getCenterAreaJsonStringfied()
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
	function getCenterInfoByIdx($nCcIdx)
	{
		$oArgs = new stdClass();
		$oArgs->cc_idx = $nCcIdx;
		$oRst = executeQuery('angeclub.getCenterByIdx', $oArgs);
// var_Dump($oRst->data);
		unset($oArgs);
		return $oRst;
	}
/**
 * @brief 모듈 default setting 불러오기
 */
	function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		return $oModuleModel->getModuleConfig('angeclub');
	}
}