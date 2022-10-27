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
 * @brief get client list
 **/
	public function getClientInfo()
	{
		$oRst = executeQueryArray('svbanner.getAdminClientList');
		$aClientInfo = [];
		$aClientInfo[0] = '광고주를 선택하세요';
		foreach($oRst->data as $nIdx=>$oClient)
			$aClientInfo[$oClient->client_srl] = $oClient->client_name;
		unset($oRst);
		return $aClientInfo;
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
		$aBanner = [];
		if(count($oBannerRst->data))
		{
            $aClientInfo = $this->getClientInfo();
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
		$oContractRst = executeQueryArray('svbanner.getAdminContractListByPkgSrl', $oArg);
		unset($oArg);
		return $oContractRst->data;
	}
/**
 * @brief get module level config
 * @return 
 **/
	public function getModuleConfig()
	{
		$oSvbannerModel = &getModel('svbanner');
		return $oSvbannerModel->getModuleConfig();
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
			$aClientInfo = $this->getClientInfo();
			foreach($oRst->data as $nIdx=>$oPkg)
				$oPkg->sClientName = $aClientInfo[$oPkg->client_srl];
		}
		return $oRst;
	}
}
/* End of file svbanner.admin.model.php */
/* Location: ./modules/svbanner/svbanner.admin.model.php */