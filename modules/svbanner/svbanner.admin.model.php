<?php
/**
 * @class  svbannerAdminModel
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerAdminModel
 */ 
class svbannerAdminModel extends svbanner
{
/**
 * @brief Contructor
 **/
	public function init() 
	{
		$logged_info = Context::get('logged_info');
		if($logged_info->is_admin != 'Y')
			return new BaseObject(-1, 'msg_login_required');
	}
/**
 * @brief get module instance list
 **/
	public function getMidList($oArgs) 
	{
		$oRst = executeQueryArray('svbanner.getAdminModInstList', $oArgs);
		if(count($oRst->data))
		{
			$oSvbannerModel = &getModel('svbanner');
			foreach($oRst->data as $nIdx=>$oMid)
			{
				if($oMid->module == 'svbanner')
				{
					$oMidConfig = $oSvbannerModel->getMidLevelConfig($oMid->module_srl);
					$oMid->mid_description = $oMidConfig->mid_description;
					unset($oMidConfig);
				}
			}
			unset($oSvbannerModel);
		}
		return $oRst;
	}
/**
 * @brief get client list from db
 **/
	public function getClientListFull()
	{
		$oRst = executeQueryArray('svbanner.getAdminClientListFull');
		return $oRst;
	}
/**
 * @brief get client list for ui
 **/
	public function getClientInfo4Ui($isActiveOnly=false)
	{
		$oArg = new stdClass();
		if($isActiveOnly)
			$oArg->is_active = 'Y';
		$oRst = executeQueryArray('svbanner.getAdminClientList4Ui', $oArg);
		unset($oArg);
		$aClientInfo = [];
		foreach($oRst->data as $nIdx=>$oClient)
			$aClientInfo[$oClient->client_srl] = $oClient->client_name;
		unset($oRst);
		return $aClientInfo;
	}
/**
 * @brief get single client info
 **/
	public function getClientInfoBySrl($nClientSrl)
	{
		$oArg = new stdClass();
		$oArg->client_srl = $nClientSrl;
		$oRst = executeQuery('svbanner.getAdminClientBySrl', $oArg);
		unset($oArg);
		if(isset($oRst->data->member_srl))
		{
			$oMemberModel = &getModel('member');
			$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oRst->data->member_srl);
			$oRst->data->user_id = $oMemberInfo->user_id;
			unset($oMemberInfo);
			unset($oMemberModel);
		}
		return $oRst;
	}
/**
 * @brief get package info 
 **/
	public function getPackageInfo($nPackageSrl)
	{
		$oArg = new stdClass();
		$oArg->package_srl = $nPackageSrl;
		$oPkgRst = executeQuery('svbanner.getAdminPackageInfoBySrl', $oArg);
		$oBannerRst = executeQueryArray('svbanner.getAdminBannerListByPkgSrl', $oArg);
		unset($oArg);
		$aClientInfo = $this->getClientInfo4Ui();
		$oPkgRst->data->sClientName = $aClientInfo[$oPkgRst->data->client_srl];
		unset($aClientInfo);
		$aBanner = [];
		if(count($oBannerRst->data))
		{
            $aClientInfo = $this->getClientInfo4Ui();
			foreach($oBannerRst->data as $nIdx=>$oBanner)
			{
                $aBanner[$oBanner->img_width.'x'.$oBanner->img_height] = new stdClass();
				$aBanner[$oBanner->img_width.'x'.$oBanner->img_height]->nBannerSrl = $oBanner->banner_srl;
				$aBanner[$oBanner->img_width.'x'.$oBanner->img_height]->nFileSrl = $oBanner->img_file_srl;
				$aBanner[$oBanner->img_width.'x'.$oBanner->img_height]->sRegdate = $oBanner->regdate;
				$oPkgRst->sClientName = $aClientInfo[$oPkg->client_srl];
			}
            $oPkgRst->data->aBannerList = $aBanner;
		}
		unset($oBannerRst);
		return $oPkgRst->data;
	}
/**
 * @brief get single contract info 
 **/
	public function getContractSingle($nContractSrl)
	{
		$oArg = new stdClass();
		$oArg->contract_srl = $nContractSrl;
		$oContractRst = executeQuery('svbanner.getAdminContractInfoBySrl', $oArg);
		if(isset($oContractRst->data->member_srl))
		{
			$oMemberModel = &getModel('member');
			$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oContractRst->data->member_srl);
			$oContractRst->data->user_id = $oMemberInfo->user_id;
			unset($oMemberInfo);
			unset($oMemberModel);
		}
		unset($oArg);
		return $oContractRst->data;
	}
/**
* @brief get contract list by client_srl 
**/
public function getContractListByClientSrl($nClientSrl)
{
	$oArg = new stdClass();
	$oArg->client_srl = $nClientSrl;
	$oContractRst = executeQueryArray('svbanner.getAdminContractList', $oArg);
	unset($oArg);
	return $oContractRst->data;
}
/**
* @brief get contract list by package_srl 
**/
	public function getContractListByPkgSrl($nPkgSrl)
	{
		$oArg = new stdClass();
		$oArg->package_srl = $nPkgSrl;
		$oContractRst = executeQueryArray('svbanner.getAdminContractList', $oArg);
		unset($oArg);
		return $oContractRst->data;
	}
/**
* @brief get imp click daily log by contract_srl 
**/
	public function getContractPerformance($nContractSrl, $nPackageSrl, $sBeginDate, $sEndDate)
	{
		date_default_timezone_set('Asia/Seoul');
		$dtToday = date_create(date('Ymd'));	
		$dtStart = date_create(substr($sBeginDate, 0, 8));
		$dtEnd = date_create(substr($sEndDate, 0, 8));
		$dtDays = date_diff($dtStart, $dtEnd);
		$nDaysCnt = $dtDays->days;
		unset($dtEnd);
		unset($dtEnd);
		// construct loggin schedule
		$aDaysToLog = [];
		for($i = 0; $i <= $nDaysCnt; $i++)
		{
			$dtDaysToToday = date_diff($dtStart, $dtToday);
			if($dtDaysToToday->d == 0)  // means today
				break;
			if($dtDaysToToday->invert == 1)  // means future
				break;
			$aDaysToLog[$dtStart->format('Ymd')] = 1;  // request to retrieve
			$nDaysToToday = $dtDaysToToday->days;
			if($nDaysToToday==1)
				break;
			$dtStart->modify('+1 day');
		}
		unset($dtToday);
		unset($dtStart);
		$nGrossImp = 0;
		$nGrossClk = 0;
		$nGrossCtr = 0.0;
		// load log cache if exists
		$sLogCacheFilePathAbs = $this->_g_DailyLogCachePath.'/'.$nContractSrl.'.cache.php';
		$sContractDailyLogCacheFile = FileHandler::readFile($sLogCacheFilePathAbs);
		if($sContractDailyLogCacheFile)
		{
			$oCacheRst = unserialize($sContractDailyLogCacheFile);
			$nGrossImp = $oCacheRst->nGrossImp;
			$nGrossClk = $oCacheRst->nGrossClk;
		}
		// toggle already-cached date to minimize DB workload
		foreach($oCacheRst->aCalcDailyLog as $nDateIdx=>$oDailyLog)
		{
			$aDaysToLog[$nDateIdx] = 0;
		}
		// retrieve new daily log of period from DB
		$aCalcDailyLog = [];
		$oParams = new stdClass();
		$oParams->package_srl = $nPackageSrl;
		foreach($aDaysToLog as $sDateIdx=>$bToggle)
		{
			if(!$bToggle)
				continue;
			$dtStart = date_create($sDateIdx);
// echo $sDateIdx.' has been proced<BR>';
			$oParams->begin_date = $sDateIdx.'000000';
			$dtStart->modify('+1 day');
			$oParams->end_date = $dtStart->format('Ymd000000');
			$oContractPerfRst = executeQuery('svbanner.getAdminContractPerformance', $oParams);
			$nGrossImp += (int)$oContractPerfRst->data->gross_impression;
			$nGrossClk += (int)$oContractPerfRst->data->gross_click;
			$aCalcDailyLog[$sDateIdx] = new stdClass();
			$aCalcDailyLog[$sDateIdx]->nImp = (int)($oContractPerfRst->data->gross_impression ? $oContractPerfRst->data->gross_impression : 0);
			$aCalcDailyLog[$sDateIdx]->nClk = (int)($oContractPerfRst->data->gross_click ? $oContractPerfRst->data->gross_click : 0);
			$aCalcDailyLog[$sDateIdx]->nCtr = $oContractPerfRst->data->gross_impression ? $oContractPerfRst->data->gross_click / $oContractPerfRst->data->gross_impression : 0.0;
			unset($oContractPerfRst);
		}
		unset($dtStart);
		unset($oParams);
		// calculated array merge with cache array
		$aMergedDailyLog = [];
		if(count((array)$oCacheRst->aCalcDailyLog))
		{
			foreach($oCacheRst->aCalcDailyLog as $sDateIdx => $oLog)
				$aMergedDailyLog[$sDateIdx] = $oLog;
		}
		foreach($aCalcDailyLog as $sDateIdx => $oLog)
			$aMergedDailyLog[$sDateIdx] = $oLog;

		unset($oCacheRst);
		unset($aCalcDailyLog);
		$oRst = new stdClass();
		$oRst->nGrossImp = $nGrossImp;
		$oRst->nGrossClk = $nGrossClk;
		$oRst->nGrossCtr = $nGrossImp ? $nGrossClk / $nGrossImp : 0.0;
		$oRst->aCalcDailyLog = $aMergedDailyLog;
		// foreach([20221025,20221026,20221027,20221028] as $_=>$nDateIdx)
		// {
		// 	unset($oRst->aCalcDailyLog[$nDateIdx]);
		// }
		// exit;
		$sSerializedRst = serialize($oRst);
		FileHandler::writeFile($sLogCacheFilePathAbs, $sSerializedRst);
		// exit;
		return $oRst;
	}
/**
* @brief calculate impression log garbage ratio
**/
	public function getImpressionLogGarbageRatio()
	{
		$oImpRst = executeQuery('svbanner.getAdminImpressionCnt');
		$nTotalLogCnt = (int)$oImpRst->data->total_count;
		unset($oImpRst);
		$oImpRst = executeQuery('svbanner.getAdminGarbageImpressionCnt');
		$nGarbageLogCnt = (int)$oImpRst->data->total_count;
		unset($oImpRst);
		return $nGarbageLogCnt / $nTotalLogCnt;
	}
/**
 * @brief get module level config
 * @return 
 **/
	public function getModuleConfig()
	{
		$oSvbannerModel = &getModel('svbanner');
		$oRst = $oSvbannerModel->getModuleConfig();
		unset($oSvbannerModel);
		return $oRst;
	}
/**
 * @brief 
 **/
	public function getSvbannerAdminDeleteModInst() 
	{
		$oModuleModel = &getModel('module');
		$module_srl = Context::get('module_srl');
		$module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
		Context::set('module_info', $module_info);
		$oTemplate = &TemplateHandler::getInstance();
		$tpl = $oTemplate->compile($this->module_path.'tpl', 'form_delete_modinst');
		$this->add('tpl', str_replace("\n"," ",$tpl));
	}
/**
 * @brief 
 **/
	public function getSvbannerAdminDeleteItem() 
	{
		$oSvbannerModel = &getModel('svbanner');
		$item_srl = Context::get('item_srl');
		$item_info = $oSvbannerModel->getItemInfoByItemSrl($item_srl);
		Context::set('item_info', $item_info);
		$oTemplate = &TemplateHandler::getInstance();
		$tpl = $oTemplate->compile($this->module_path.'tpl', 'form_delete_item');
		$this->add('tpl', str_replace("\n"," ",$tpl));
	}
/**
 * @brief 
 **/
	public function getSvbannerAdminItemInfo($nModuleSrl, $nItemSrl) 
	{
		if(!$nModuleSrl || !$nItemSrl)
			return new BaseObject(-1, 'msg_invalid_request');
		$oArg->module_srl = $nModuleSrl;
		$oArg->item_srl = $nItemSrl;
		$oRst = executeQuery('svbanner.getAdminItem', $oArg);
        unset($oArg);
		return $oRst;
	}
/**
 * @brief
 **/
	public function getSvbannerAdminPkgList($oParam)
	{
		$oArg = new stdClass();
		if(!is_null($oParam->module_srl) && $oParam->module_srl != 0)
			$oArg->module_srl = $oParam->module_srl;
		if(!is_null($oParam->page) && $oParam->page != 0)
			$oArg->page = $oParam->page;
		if(!is_null($oParam->list_count) && $oParam->list_count != 0)
			$oArg->list_count = $oParam->list_count;
		if(!is_null($oParam->package_name) && strlen($oParam->package_name) > 0)
			$oArg->pkg_name = $oParam->pkg_name;
		$oRst = executeQueryArray('svbanner.getAdminPkgList', $oArg);
		unset($oArg);
		if(count((Array)$oRst->data))
		{
			$aClientInfo = $this->getClientInfo4Ui();
			foreach($oRst->data as $nIdx=>$oPkg)
				$oPkg->sClientName = $aClientInfo[$oPkg->client_srl];
		}
		return $oRst;
	}
}
/* End of file svbanner.admin.model.php */
/* Location: ./modules/svbanner/svbanner.admin.model.php */