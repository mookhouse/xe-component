<?php
/**
 * @class  svbannerAdminView
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerAdminView
 */ 
class svbannerAdminView extends svbanner
{
/**
 * @brief Contructor
 **/
	public function init() 
	{
		// module이 svshopmaster일때 관리자 레이아웃으로
		if(Context::get('module') == 'svshopmaster')
		{
			$sClassPath = _XE_PATH_.'modules/svshopmaster/svshopmaster.class.php';
			if(file_exists($sClassPath))
			{
				require_once($sClassPath);
				$oSvshopmaster = new svshopmaster;
				$oSvshopmaster->init($this);
			}
		}

		$logged_info = Context::get('logged_info');
		if($logged_info->is_admin!='Y')
			return new BaseObject(-1, 'msg_login_required');

		// module_srl이 있으면 미리 체크하여 존재하는 모듈이면 module_info 세팅
		$nModuleSrl = Context::get('module_srl');
		if(!$nModuleSrl && $this->module_srl)
		{
			$nModuleSrl = $this->module_srl;
			Context::set('module_srl', $nModuleSrl);
		}
		$oModuleModel = &getModel('module');
		// module_srl이 넘어오면 해당 모듈의 정보를 미리 구해 놓음
		if($nModuleSrl) 
		{
			$oModuleInfo = $oModuleModel->getModuleInfoByModuleSrl($nModuleSrl);
			if(!$oModuleInfo)
			{
				Context::set('module_srl','');
				$this->act = 'list';
			}
			else
			{
				ModuleModel::syncModuleToSite($oModuleInfo);
				$this->module_info = $oModuleInfo;
				Context::set('module_info',$oModuleInfo);
			}
		}
		if($oModuleInfo && !in_array($oModuleInfo->module, array('svbanner')))
			return $this->stop("msg_invalid_request");

		//if(Context::get('module')=='svshopmaster')
		//{
		//	$this->setLayoutPath('');
		//	$this->setLayoutFile('common_layout');
		//}
		// set template file
		$sTplPath = $this->module_path.'tpl';
		$this->setTemplatePath($sTplPath);
		$this->setTemplateFile('index');
		Context::set('tpl_path', $sTplPath);
	}
/**
 * @brief default admin view
 */
	public function dispSvbannerAdminListMid() 
	{
		$oArgs = new stdClass();
		$oArgs->sort_index = "module_srl";
		$oArgs->page = Context::get('page');
		$oArgs->list_count = 20;
		$oArgs->page_count = 10;
		$oArgs->s_module_category_srl = Context::get('module_category_srl');
		$oSvbannerModel = &getAdminModel('svbanner');
		$oRst = $oSvbannerModel->getMidList($oArgs);
		if(!$oRst->toBool())
			return $oRst;
		unset($oArgs);
		unset($oSvbannerModel);
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		Context::set('list', $oRst->data);
		unset($oRst);
		$oModuleModel = &getModel('module');
		$module_category = $oModuleModel->getModuleCategories();
		Context::set('module_category', $module_category);
		unset($oModuleModel);
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminInsertMid() 
	{
		$oModuleModel = &getModel('module');
		$module_category = $oModuleModel->getModuleCategories();
		Context::set('module_category', $module_category);
		unset($oModuleModel);
	}
/**
* @brief 
*/
	public function dispSvbannerAdminUpdateMid() 
	{
		$oModuleModel = &getModel('module');
		$module_category = $oModuleModel->getModuleCategories();
		Context::set('module_category', $module_category);
		unset($oModuleModel);
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminListClient() 
	{
		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$oRst = $oSvbannerAdminModel->getClientListFull();
		if(!$oRst->toBool())
			return $oRst;
		unset($oSvbannerModel);
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		Context::set('client_list', $oRst->data);
		unset($oRst);
		// 스킨은 $this->init()와 index.html에서 처리
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminInsertClient() 
	{
		$nClientSrl = (int)Context::get('client_srl');
		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$oRst = $oSvbannerAdminModel->getClientInfoBySrl($nClientSrl);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('oClientInfo', $oRst->data);
		
		if($nClientSrl)
		{
			$aContractList = $oSvbannerAdminModel->getContractListByClientSrl($nClientSrl);
			Context::set('aContractList', $aContractList);
			unset($aContractList);
		}
		unset($oSvbannerAdminModel);
		// 스킨은 $this->init()와 index.html에서 처리
	}
/**
 * @brief admin view for baner package list
 */
	public function dispSvbannerAdminListPkgByMid() 
	{
		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl)
			return new BaseObject(-1, 'msg_invalid_request');

		$nListCount = Context::get('disp_numb');
		$sSortIdx = Context::get('sort_index');
		$sOrderType = Context::get('order_type');
		if(!$nListCount) 
			$nListCount = 30;
		if(!$sSortIdx) 
			$sSortIdx = "list_order";
		if(!$sOrderType) 
			$sOrderType = 'asc';
		
		$oArgs = new stdClass();
		$sSearchPkgName = Context::get('search_pkg_name');
		if(strlen($sSearchPkgName))
			$oArgs->package_name = $sSearchPkgName;

		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->page = Context::get('page');
		$oArgs->list_count = $nListCount;
		$oArgs->sort_index = $sSortIdx;
		$oArgs->order_type = $sOrderType;
		$oRst = $oSvbannerAdminModel->getSvbannerAdminPkgList($oArgs);
		foreach($oRst->data as $nIdx=>$oSinglePkg)
		{
			$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($oSinglePkg->package_srl);
			// get representative banner thumbnail
			if(isset($oPackageInfo->aBannerList))
			{
				$oFirstBannerImg = array_pop($oPackageInfo->aBannerList);
				$oSinglePkg->nImgFileSrl = $oFirstBannerImg->nFileSrl;
				unset($oFirstBannerImg);
			}
			// get display period
			$aContractList = $oSvbannerAdminModel->getContractListByPkgSrl($oSinglePkg->package_srl);
			if(isset($aContractList))
			{
				foreach($aContractList as $nIdx=>$oContract)
				{
					if($oContract->is_active) // retrieve single final active contract only
					{
						$oSinglePkg->sBeginDate = $oContract->begin_date;
						$oSinglePkg->sEndDate = $oContract->end_date;
						$oContractDailyLog = $oSvbannerAdminModel->getContractPerformance($oContract->contract_srl, $oContract->package_srl, 
																						$oContract->begin_date, $oContract->end_date);
						$oSinglePkg->nGrossImp = $oContractDailyLog->nGrossImp;
						$oSinglePkg->nGrossClk = $oContractDailyLog->nGrossClk;
						$oSinglePkg->nGrossCtr = $oContractDailyLog->nGrossCtr;
						unset($oContractDailyLog);
						break;
					}
				}
			}
			unset($aContractList);
			unset($oPackageInfo);
		}
		unset($oArgs);
		unset($oSvbannerAdminModel);
		if(!$oRst->toBool())
			return $oRst;
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		Context::set('list', $oRst->data);
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminInsertPkg() 
	{
		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl)
			return new BaseObject(-1, 'msg_invalid_request');
		$oSvbannerModel = &getModel('svbanner');
		$oMidConfig = $oSvbannerModel->getMidLevelConfig($nModuleSrl);
		Context::set('oMidConfig', $oMidConfig);
		unset($oMidConfig);
		unset($oSvbannerModel);

		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$aClientInfo = $oSvbannerAdminModel->getClientInfo4Ui($isActiveOnly=true);
		Context::set('aClientInfo', $aClientInfo);
		unset($aClientInfo);
		$nPackageSrl = (int)Context::get('package_srl');
		if($nPackageSrl)
		{
			$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($nPackageSrl);
			Context::set('oPackageInfo', $oPackageInfo);
			unset($oPackageInfo);
			$aContractList = $oSvbannerAdminModel->getContractListByPkgSrl($nPackageSrl);
			// retrieve imp, clk, ctr per a contract
			foreach($aContractList as $nIdx=>$oContract)
			{
				if($oContract->is_active)
				{
					$oContractDailyLog = $oSvbannerAdminModel->getContractPerformance($oContract->contract_srl, $oContract->package_srl, 
																					$oContract->begin_date, $oContract->end_date);
					$oContract->nGrossImp = $oContractDailyLog->nGrossImp;
					$oContract->nGrossClk = $oContractDailyLog->nGrossClk;
					$oContract->nGrossCtr = $oContractDailyLog->nGrossCtr;
					unset($oContractDailyLog);
				}
			}
			Context::set('aContractList', $aContractList);			
			unset($aContractList);
		}
		unset($oSvbannerAdminModel);
		// 스킨은 $this->init()와 index.html에서 처리
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminUpdatePkg() 
	{
		$this->dispSvbannerAdminInsertPkg();
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminInsertContract() 
	{
		$nContractSrl = (int)Context::get('contract_srl');
		$oSvbannerAdminModel = &getAdminModel('svbanner');
		if($nContractSrl)
		{
			$oContractInfo = $oSvbannerAdminModel->getContractSingle($nContractSrl);
			$oContractInfo->end_date = $oContractInfo->end_date - 10000;  // zdate() converts yyyymmdd235959 to next day
			Context::set('oContractInfo', $oContractInfo);
			$nPackageSrl = (int)$oContractInfo->package_srl;
			unset($oContrctInfo);
		}
		if(!$nPackageSrl)
			$nPackageSrl = (int)Context::get('package_srl');

		$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($nPackageSrl);
		Context::set('oPackageInfo', $oPackageInfo);
		unset($oPackageInfo);
		unset($aClientInfo);
		unset($oSvbannerAdminModel);
		// 스킨은 $this->init()와 index.html에서 처리
	}
/**
 * @brief 
 */
	public function dispSvbannerAdminContractOutcome() 
	{
		$nContractSrl = (int)Context::get('contract_srl');
		if(!$nContractSrl)
			return new BaseObject(-1, 'msg_invalid_request');

		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$oContractInfo = $oSvbannerAdminModel->getContractSingle($nContractSrl);
		Context::set('oContractInfo', $oContractInfo);
		$nPackageSrl = (int)$oContractInfo->package_srl;
		unset($oContrctInfo);
		if(!$oContractInfo->package_srl || !$oContractInfo->begin_date || !$oContractInfo->end_date)
			return new BaseObject(-1, 'msg_invalid_contract');
	
		$oDailyLog = $oSvbannerAdminModel->getContractPerformance($oContractInfo->contract_srl, $oContractInfo->package_srl, 
																$oContractInfo->begin_date, $oContractInfo->end_date);
		Context::set('nGrossImp', $oDailyLog->nGrossImp);
		Context::set('nGrossClk', $oDailyLog->nGrossClk);
		Context::set('nGrossCtr', $oDailyLog->nGrossCtr);
		Context::set('aDailyLog', $oDailyLog->aCalcDailyLog);

		$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($nPackageSrl);
		Context::set('oPackageInfo', $oPackageInfo);
		unset($oPackageInfo);
		unset($aClientInfo);
		unset($oSvbannerAdminModel);
		// 스킨은 $this->init()와 index.html에서 처리
	}
/**
 * @brief svitem 스킨에서 호출하는 메쏘드
 */	
	public static function dispBannerImgUrl($nThumbFileSrl, $nWidth = 80, $nHeight = 0, $sThumbnailType = 'crop')
	{
		$sNoimgUrl = Context::getRequestUri().'/modules/svbanner/tpl/img/no_img_80x80.jpg';
		if(!$nThumbFileSrl) // 기본 이미지 반환
			return $sNoimgUrl;
		if(!$nHeight)
			$nHeight = $nWidth;
		
		// Define thumbnail information
		$sThumbnailPath = 'files/cache/thumbnails/'.getNumberingPath($nThumbFileSrl, 3);
		$sThumbnailFile = $sThumbnailPath.$nWidth.'x'.$nHeight.'.'.$sThumbnailType.'.jpg';
		$sThumbnailUrl = Context::getRequestUri().$sThumbnailFile; //http://127.0.0.1/files/cache/thumbnails/840/80x80.crop.jpg"
		// Return false if thumbnail file exists and its size is 0. Otherwise, return its path
		if(file_exists($sThumbnailFile) && filesize($sThumbnailFile) > 1) 
			return $sThumbnailUrl;

		// Target File
		$oFileModel = &getModel('file');
		$sSourceFile = NULL;
		$sFile = $oFileModel->getFile($nThumbFileSrl);
		if($sFile) 
			$sSourceFile = $sFile->uploaded_filename;

		if($sSourceFile)
			$oOutput = FileHandler::createImageFile($sSourceFile, $sThumbnailFile, $nWidth, $nHeight, 'jpg', $sThumbnailType);

		// Return its path if a thumbnail is successfully genetated
		if($oOutput) 
			return $sThumbnailUrl;
		else
			FileHandler::writeFile($sThumbnailFile, '','w'); // Create an empty file not to re-generate the thumbnail
		return $sNoimgUrl;
	}
/**
 * @brief admin view [기본설정]
 */
	public function dispSvbannerAdminConfig() 
	{
		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$oConfig = $oSvbannerAdminModel->getModuleConfig();
		Context::set('oConfig',$oConfig);
		unset($oConfig);
		Context::set('fImpGarbageRatio', $oSvbannerAdminModel->getImpressionLogGarbageRatio());
		unset($oSvbannerAdminModel);
		$this->setTemplateFile('config');
	}
/**
 * @brief display the grant information
 **/
	public function dispSvbannerAdminGrantInfo() 
	{
		// get the grant infotmation from admin module
		$oModuleAdminModel = &getAdminModel('module');
		$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('grant_content', $grant_content);
		$this->setTemplateFile('grantinfo');
	}
}
/* End of file svbanner.admin.view.php */
/* Location: ./modules/svbanner/svbanner.admin.view.php */