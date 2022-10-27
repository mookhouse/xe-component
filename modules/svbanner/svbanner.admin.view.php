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
		$aClientInfo = $oSvbannerAdminModel->getClientInfo();
		Context::set('aClientInfo', $aClientInfo);
		unset($aClientInfo);

		$nPackageSrl = (int)Context::get('package_srl');
		if($nPackageSrl)
		{
			$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($nPackageSrl);
			Context::set('oPackageInfo', $oPackageInfo);
			unset($oPackageInfo);

			$aContractList = $oSvbannerAdminModel->getContractListByPkgSrl($nPackageSrl);
			// var_dump($aContractList);
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
		$nModuleSrl = (int)Context::get('module_srl');
		$nPackageSrl = (int)Context::get('package_srl');
		if(!$nModuleSrl || !$nPackageSrl)
			return new BaseObject(-1, 'msg_invalid_request');

		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$aClientInfo = $oSvbannerAdminModel->getClientInfo();
		$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($nPackageSrl);
		$oPackageInfo->sClientName = $aClientInfo[$oPackageInfo->client_srl];
		Context::set('oPackageInfo', $oPackageInfo);
		unset($oPackageInfo);
		unset($aClientInfo);

		$nContractSrl = (int)Context::get('contract_srl');
		// var_dump($nContractSrl);
		if($nContractSrl)
		{
			$oContractInfo = $oSvbannerAdminModel->getContractSingle($nContractSrl);
			if($nPackageSrl != $oContractInfo->package_srl)
				return new BaseObject(-1, 'msg_invalid_request');

			$oMemberModel = &getModel('member');
			$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oContractInfo->member_srl);
			$oContractInfo->user_id = $oMemberInfo->user_id;
			unset($oMemberInfo);
			unset($oMemboMemberModelerInfo);
			// var_dump($oMemberInfo->user_id);
			Context::set('oContractInfo', $oContractInfo);
			// var_dump($oContractInfo->member_srl);
			unset($oContrctInfo);
		}
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
		$config = $oSvbannerAdminModel->getModuleConfig();
		
		Context::set('config',$config);	
		$oSvbannerModules = array();
		$oModuleModel = &getModel('module');
		$oModules = $oModuleModel->getMidList();
		foreach($oModules as $key=>$val)
		{
			if($val->module == 'svbanner')
			{
                if(is_null($oSvbannerModules[$nIdx]))
                    $oSvbannerModules[$nIdx] = new stdClass();
				$oSvbannerModules[$nIdx]->module_srl = $val->module_srl;
				$oSvbannerModules[$nIdx++]->mid = $val->mid;
			}
		}
		Context::set('svbanner_mod_list', $oSvbannerModules);
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