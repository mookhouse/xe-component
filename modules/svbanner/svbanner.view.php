<?php
/**
 * @class  svbannerView
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerView
 */
require_once(_XE_PATH_.'modules/svbanner/CircularLinkedList.class.php');
class svbannerView extends svbanner
{
	public function init()
	{
		// 템플릿 경로 설정
		$this->module_info->skin = 'default';
		$this->setTemplatePath($this->module_path.'skins/'.$this->module_info->skin);
	}
/**
 * @brief
 * @return 
 **/
	public function dispSvbannerIndex() 
	{
		$sScriptPath = $this->module_path.'tpl/skin.js/';
		Context::addJsFile($sScriptPath.'sv_banner.js');
		Context::addJsFile($sScriptPath.'jquery.cookie.js');
		$oRst = $this->_getCurrentBanner();
		Context::set('nImpSrl', $oRst->nImpLogSrl);
		Context::set('nSelectedBannerSrl', $oRst->nBannerSrl);
		Context::set('sBannerImgUrl', $oRst->sBannerImgUrl);
		Context::set('sBannerLandingUrl', $oRst->sLandingUrl);
		unset($oRst);
		Context::set('sUniqId', uniqid());
		$this->setTemplateFile('index');
	}
/**
 * @brief get current banner info based on serialized circular linked list
 * @return 
 **/
	private function _getCurrentBanner() 
	{
		$oArg = new stdClass();
		$oArg->module_srl = $this->module_info->module_srl;
		$oArg->is_active = 'Y';
		$oContractRst = executeQueryArray('svbanner.getContractListByModuleSrl', $oArg);
		$aCorrespondingPkg = [];
		$sTodayYyyymmdd = Date('Ymd');
		foreach($oContractRst->data as $nIdx => $oContract)
		{
			if((int)$oContract->begin_date <= (int)($sTodayYyyymmdd.'000000') && (int)$oContract->end_date >= (int)($sTodayYyyymmdd.'235959'))
				$aCorrespondingPkg[] = $oContract->package_srl;
		}
		unset($oArg);
		if(!$oContractRst->toBool())
			return $oContractRst;
		unset($oContractRst);

		$aBannerDim = explode('x', trim(Context::get('dim')));
		$oArg = new stdClass();
		$oArg->package_srl = implode(',', $aCorrespondingPkg);
		unset($aCorrespondingPkg);
		$oArg->img_width = $aBannerDim[0];
		$oArg->img_height = $aBannerDim[1];
		$oBannerRst = executeQueryArray('svbanner.getBannerListByPackageSrl', $oArg);
		unset($oArg);
		if(!$oBannerRst->toBool())
			return $oBannerRst;
		
		$oPkgArg = new stdClass();
		$oCll = new CircularLinkedList();
		foreach($oBannerRst->data as $nIdx=>$oBanner)  // construct banner display schedule
		{
			$oPkgArg->package_srl = $oBanner->package_srl;
			$oPackageRst = executeQuery('svbanner.getPackageByPackageSrl', $oPkgArg);
			if(!$oPackageRst->toBool())
				return $oPackageRst;
			$oBanner->landing_url = $oPackageRst->data->landing_url;
			unset($oPackageRst);
			$oCll->push($oBanner);
		}
		unset($oPkgArg);
		unset($oBanner);

		$nBannerCnt = $oCll->getNodeCnt();
		$oCll->reset();
		$aBannerScheduleByBannerSrl = [];
		// construct serialzed circular linked list
		for($j = 1; $j <= $nBannerCnt; $j++) 
		{		
			$oCurSingleBanner = $oCll->getCurrent();
			$aBannerScheduleByBannerSrl[$oCurSingleBanner->nBannerSrl] = new stdClass();
			$aBannerScheduleByBannerSrl[$oCurSingleBanner->nBannerSrl]->banner_srl = $oCurSingleBanner->nBannerSrl;
			$aBannerScheduleByBannerSrl[$oCurSingleBanner->nBannerSrl]->package_srl = $oCurSingleBanner->nPackageSrl;
			$aBannerScheduleByBannerSrl[$oCurSingleBanner->nBannerSrl]->img_file_srl = $oCurSingleBanner->nImgFileSrl;
			$aBannerScheduleByBannerSrl[$oCurSingleBanner->nBannerSrl]->landing_url = $oCurSingleBanner->sLandingUrl;
			$oNextSingleBanner = $oCll->getNext();
			$aBannerScheduleByBannerSrl[$oCurSingleBanner->nBannerSrl]->next_banner_srl = $oNextSingleBanner->nBannerSrl;
			$oCll->moveNext();
			unset($oCurSingleBanner);
			unset($oNextSingleBanner);
		}
		unset($oCll);
		$oImpArg = new stdClass();
		$oImpArg->img_width = $aBannerDim[0];
		$oImpArg->img_height = $aBannerDim[1];
		$oImpressionRst = executeQuery('svbanner.getImpLogByBannerSpecSrl', $oImpArg);
		unset($oImpArg);
		if(!$oImpressionRst->toBool())
			return $oImpressionRst;
		// retrieve banner
		if(!count((array)$oImpressionRst->data))  // get head of displaying schedule
			$oCurBanner = reset($aBannerScheduleByBannerSrl);
		else  // get next displaying schedule
		{
			$nBannerSrl = $oImpressionRst->data->banner_srl;
			$nNextBannerSrl = $aBannerScheduleByBannerSrl[$nBannerSrl]->next_banner_srl;
			$oCurBanner = $aBannerScheduleByBannerSrl[$nNextBannerSrl];
		}

		if(!$oCurBanner)  // if daily schedule has been changed
			$oCurBanner = reset($aBannerScheduleByBannerSrl);

		$oNewImpArg = new stdClass();
		$oNewImpArg->img_width = $aBannerDim[0];
		$oNewImpArg->img_height = $aBannerDim[1];
		unset($aBannerDim);
		$oNewImpArg->banner_srl = $oCurBanner->banner_srl;
		$oNewImpArg->package_srl = $oCurBanner->package_srl;
		$oImpressionAddRst = executeQueryArray('svbanner.insertImpLog', $oNewImpArg);
		unset($oNewImpArg);
		if(!$oImpressionAddRst->toBool())
			return $oImpressionAddRst;
		
		$oDB = DB::getInstance();
		$nImpLogSrl = $oDB->db_insert_id();
		unset($oDB);

		$oFileModel = &getModel('file');
		$oBannerImgFile = $oFileModel->getFile($oCurBanner->img_file_srl);
		$sServerUrl = Context::getRequestUri();
		$sBannerImgUrl = null;
		if($oBannerImgFile)
		{
			$sBannerImgUrl = $sServerUrl.$oBannerImgFile->uploaded_filename;
			unset($oBannerImgFile);
		}
		unset($oFileModel);

		$oRst = new stdClass();
		$oRst->nImpLogSrl = $nImpLogSrl;
		$oRst->sBannerImgUrl = $sBannerImgUrl;
		$oRst->nBannerSrl = $oCurBanner->banner_srl;
		$oRst->sLandingUrl = $oCurBanner->landing_url;
		return $oRst;
	}
}
/* End of file svbanner.view.php */
/* Location: ./modules/svbanner/svbanner.view.php */