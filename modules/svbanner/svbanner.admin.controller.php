<?php
/**
 * @class  svbannerAdminController
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerAdminController
 */
class svbannerAdminController extends svbanner
{
/**
 * @brief Contructor
 **/
	public function init() 
	{
		$oLoggedInfo = Context::get('logged_info');
		if($oLoggedInfo->is_admin!='Y')
		{
			unset($oLoggedInfo);
			return new BaseObject(-1, 'msg_login_required');
		}
		unset($oLoggedInfo);
	}
/**
 * @brief mid 생성하거나 변경
 **/
	public function procSvbannerAdminInsertMid()
	{
		$oArgs = Context::getRequestVars();
		$oArgs->module = 'svbanner';
		// module_srl의 값에 따라 insert
		if(!$oArgs->module_srl) 
		{
			$oModuleController = &getController('module');
			$oRst = $oModuleController->insertModule($oArgs);
			$nModuleSrl = $oRst->get('module_srl');
			$sMsgCode = 'success_registed';
		}
		else //update
		{
			$oRst = $this->_updateMidLevelConfig($oArgs);
			$nModuleSrl = $oArgs->module_srl;
			$sMsgCode = 'success_updated';
		}
		if(!$oRst->toBool())
			return $oRst;
		
		unset($oRst);
		unset($oArgs);
		$this->add('module_srl', $nModuleSrl);
		$this->setMessage($sMsgCode);
		$sReturnUrl = getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispSvbannerAdminInsertMid','module_srl',$nModuleSrl);
		$this->setRedirectUrl($sReturnUrl);
	}
/**
 * @brief 
 **/
	public function procSvbannerAdminInsertPkg() 
	{		
		$nReqModuleSrl = (int)Context::get('module_srl');
		if(!$nReqModuleSrl)
			return new BaseObject(-1, 'msg_invalid_request');
		
		// before
		//$oTriggerRst = ModuleHandler::triggerCall('svitem.insertItem', 'before', $oArgs);
		//if(!$oTriggerRst->toBool())
		//	return $oTriggerRst;
		//unset($oTriggerRst);
		$oSvbannerModel = &getModel('svbanner');
		$oMidConfig = $oSvbannerModel->getMidLevelConfig($nReqModuleSrl);
		unset($oSvbannerModel);
		
		$aBannerPolicy = [];
		foreach($oMidConfig as $sName=>$oVal)
		{
			if(substr($sName, 0, 17) === "banner_dimension_") 
			{
				$aBannerDim = explode('x', $oVal);
				$nPolicyIdx = (int)substr($sName, 17, 1);
                $aBannerPolicy[$nPolicyIdx] = new stdClass();
				$aBannerPolicy[$nPolicyIdx]->nWidth = $aBannerDim[0];
				$aBannerPolicy[$nPolicyIdx]->nHeight = $aBannerDim[1];
				$aBannerPolicy[$nPolicyIdx]->bPassed = false;
			}
		}
		// package_srl + img_width + img_height uniqueness
		$nReqPackageSrl = (int)Context::get('package_srl');
		if($nReqPackageSrl)
			$oRst = $this->_updatePkg($aBannerPolicy);
		else
        {
			$oRst = $this->_insertPkg($aBannerPolicy);
            $nReqPackageSrl = $oRst->get('nPkgSrl');
        }
		if(!$oRst->toBool())
			return $oRst;
		
		// after
		//$output = ModuleHandler::triggerCall('svitem.insertItem', 'after', $oArgs);
		//if (!$output->toBool())
		//	return $output;
		//unset($output);
		//$this->_resetCache();
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
		{
			$sReturnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module',Context::get('module'),'act','dispSvbannerAdminUpdatePkg','module_srl',Context::get('module_srl'),'package_srl',$nReqPackageSrl);
			$this->setRedirectUrl($sReturnUrl);
			return;
		}
	}
/**
 * @brief 
 **/
	public function procSvbannerAdminInsertContract() 
	{
		$nReqModuleSrl = (int)Context::get('module_srl');
		$nReqPackageSrl = (int)Context::get('package_srl');
		if(!$nReqModuleSrl || !$nReqPackageSrl)
			return new BaseObject(-1, 'msg_invalid_request');
		
		$sBeginDate = trim(Context::get('begin_date'));
		$sEndDate = trim(Context::get('end_date'));
		
		if(!$this->_validateYyyymmdd($sBeginDate) || !$this->_validateYyyymmdd($sEndDate))
			return new BaseObject(-1, 'msg_invalid_contract_date');
		$aBeginDate = explode('-', $sBeginDate);
		$aEndDate = explode('-', $sEndDate);
		
		if(!checkdate($aBeginDate[1], $aBeginDate[2], $aBeginDate[0]) || !checkdate($aEndDate[1], $aEndDate[2], $aEndDate[0]))
			return new BaseObject(-1, 'msg_invalid_contract_date');
		
		if((int)($aBeginDate[0].$aBeginDate[1].$aBeginDate[2]) > (int)($aEndDate[0].$aEndDate[1].$aEndDate[2]))
			return new BaseObject(-1, 'msg_invalid_contract_period');

		Context::set('begin_date', $aBeginDate[0].$aBeginDate[1].$aBeginDate[2].'000000');
		Context::set('end_date', $aEndDate[0].$aEndDate[1].$aEndDate[2].'235959');

		$nReqContractSrl = (int)Context::get('contract_srl');
		if($nReqContractSrl)
			$oRst = $this->_updateContract();
		else
		{
			$oRst = $this->_insertContract();
			$nReqContractSrl = $oRst->get('nContractSrl');
		}
		if(!$oRst->toBool())
			return $oRst;
		if(!in_array(Context::getRequestMethod(),array('XMLRPC','JSON')))
		{
			$sReturnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module',Context::get('module'),'act','dispSvbannerAdminInsertContract','module_srl',$nReqModuleSrl,'package_srl',$nReqPackageSrl,'contract_srl',$nReqContractSrl);
			$this->setRedirectUrl($sReturnUrl);
			return;
		}
	}
/**
 * @brief 
 **/
	private function _updateContract()
	{
		$oArgs = Context::getRequestVars();
		$oContractArgs = new stdClass();
		$oContractArgs->contract_srl = (int)Context::get('contract_srl');
		$oContractArgs->budget_amnt = $oArgs->budget_amnt;
		$oContractArgs->is_active = $oArgs->release;
		$oContractArgs->memo = $oArgs->memo;
		$oContractArgs->begin_date = $oArgs->begin_date;
		$oContractArgs->end_date = $oArgs->end_date;
		unset($oArgs);
		$oContractRst = executeQuery('svbanner.updateAdminContract', $oContractArgs);
		if(!$oContractRst->toBool()) 
		{
			unset($oContractRst);
			return $oContractRst;
		}
		unset($oContractRst);
		unset($oContractArgs);			
		return new BaseObject(0, 'success_registed');
	}	
/**
 * @brief 
 **/
	private function _insertContract()
	{
		$oArgs = Context::getRequestVars();
		
		$oSvbannerAdminModel = &getAdminModel('svbanner');
		$oPackageInfo = $oSvbannerAdminModel->getPackageInfo($oArgs->package_srl);
		unset($oSvbannerModel);

		$oContractArgs = new stdClass();
		$oLoggedInfo = Context::get('logged_info');
		$oContractArgs->member_srl = $oLoggedInfo->member_srl;
		unset($oLoggedInfo);
		$oContractArgs->module_srl = $oPackageInfo->module_srl;
		$oContractArgs->package_srl = $oArgs->package_srl;
		$oContractArgs->client_srl = $oPackageInfo->client_srl;
		$oContractArgs->budget_amnt = $oArgs->budget_amnt;
		$oContractArgs->is_active = $oArgs->release;
		$oContractArgs->memo = $oArgs->memo;
		$oContractArgs->begin_date = $oArgs->begin_date;
		$oContractArgs->end_date = $oArgs->end_date;
		unset($oArgs);
		$oContractRst = executeQuery('svbanner.insertAdminContract', $oContractArgs);
		if(!$oContractRst->toBool()) 
		{
			unset($oContractRst);
			return $oContractRst;
		}
		unset($oContractRst);
		unset($oContractArgs);	
		// set $nContractSrl
		$oDB = DB::getInstance();
		$nContractSrl = $oDB->db_insert_id();
		$oRst = new BaseObject(0, 'success_registed');
		$oRst->add('nContractSrl', $nContractSrl);
		return $oRst;
	}
/**
 * @brief 
 **/
	private function _updatePkg($aBannerPolicy) 
	{
		$oArgs = Context::getRequestVars();
		$oPkgArgs = new stdClass();
		$oPkgArgs->package_srl = $oArgs->package_srl;
		$oPkgArgs->client_srl = $oArgs->client_srl;
		$oPkgArgs->landing_url = $oArgs->landing_url;
		$oPkgArgs->description = $oArgs->pkg_description;
		$oPkgRst = executeQuery('svbanner.updateAdminPackage', $oPkgArgs);
		unset($oPkgArgs);
		if(!$oPkgRst->toBool()) 
			return $oPkgRst;
		unset($oPkgRst);
		// save banners
		foreach($aBannerPolicy as $nIdx=>$oBannerPolicy)
		{
			$sBannerImgIdx = 'banner_img_'.$nIdx;
			if($oArgs->$sBannerImgIdx['tmp_name']) 
			{
				if(is_uploaded_file($oArgs->$sBannerImgIdx['tmp_name']))
				{
					$aSizeInfo = getimagesize($oArgs->$sBannerImgIdx['tmp_name']);
					$nWidth = (int)$aSizeInfo[0];
					$nHeight = (int)$aSizeInfo[1];
					if($oBannerPolicy->nWidth==$nWidth && $oBannerPolicy->nHeight==$nHeight)
						$oBannerPolicy->bPassed = true;
					else
					{
						$oBannerPolicy->nActWidth = $nWidth;
						$oBannerPolicy->nActHeight = $nHeight;
					}
				}
			}
			else
				$oBannerPolicy->bPassed = true;
		}
		$sErrMsg = '';
		foreach($aBannerPolicy as $nIdx=>$oBannerPolicy)
		{
			if(!$oBannerPolicy->bPassed)
				$sErrMsg .= '배너 '.$nIdx.'의 규격은 '.$oBannerPolicy->nWidth.'x'.$oBannerPolicy->nHeight.'인데 업로드한 배너 규격은 '.$oBannerPolicy->nActWidth.'x'.$oBannerPolicy->nActHeight.'입니다.<BR>';
		}
		if($sErrMsg)
			return new BaseObject(-1, $sErrMsg);
		$nModuleSrl = $oArgs->module_srl;
		$nPackageSrl = $oArgs->package_srl;
		$oFileController = &getController('file');
		foreach($aBannerPolicy as $nIdx=>$oBannerPolicy)
		{
			$sBannerImgIdx = 'banner_img_'.$nIdx;
			if($oArgs->$sBannerImgIdx['tmp_name']) 
			{
				if(is_uploaded_file($oArgs->$sBannerImgIdx['tmp_name']))
				{
					$oFileRst = $oFileController->insertFile($oArgs->$sBannerImgIdx, $oArgs->module_srl, $nPackageSrl);
					if(!$oFileRst || !$oFileRst->toBool())
						return $oFileRst;
					$oFileController->setFilesValid($nPackageSrl);
					$nBannerFileSrl = $oFileRst->get('file_srl');
					unset($oFileRst);

					$oBannerArgs = new stdClass();
					$oBannerArgs->module_srl = $nModuleSrl;
					$oBannerArgs->package_srl = $nPackageSrl;
					$oBannerArgs->img_file_srl = $nBannerFileSrl;
					$oBannerArgs->img_width = $oBannerPolicy->nWidth;
					$oBannerArgs->img_height = $oBannerPolicy->nHeight;
					$oBannerRst = executeQuery('svbanner.insertAdminBanner', $oBannerArgs);
					if(!$oBannerRst->toBool()) 
						return $oBannerRst;
					unset($oBannerArgs);
					unset($oBannerRst);
				}
			}
		}
		unset($oFileController);
		unset($oArgs);
		return new BaseObject(0, 'success_updated');
	}
/**
 * @brief 
 **/
	private function _insertPkg($aBannerPolicy) 
	{
		$oArgs = Context::getRequestVars();
		// save banners
		foreach($aBannerPolicy as $nIdx=>$oBannerPolicy)
		{
			$sBannerImgIdx = 'banner_img_'.$nIdx;
			if($oArgs->$sBannerImgIdx['tmp_name']) 
			{
				if(is_uploaded_file($oArgs->$sBannerImgIdx['tmp_name']))
				{
					$aSizeInfo = getimagesize($oArgs->$sBannerImgIdx['tmp_name']);
					$nWidth = (int)$aSizeInfo[0];
					$nHeight = (int)$aSizeInfo[1];
					if($oBannerPolicy->nWidth==$nWidth && $oBannerPolicy->nHeight==$nHeight)
						$oBannerPolicy->bPassed = true;
					else
					{
						$oBannerPolicy->nActWidth = $nWidth;
						$oBannerPolicy->nActHeight = $nHeight;
					}
				}
			}
			else
				$oBannerPolicy->bPassed = true;
		}
		$sErrMsg = '';
		foreach($aBannerPolicy as $nIdx=>$oBannerPolicy)
		{
			if(!$oBannerPolicy->bPassed)
				$sErrMsg .= '배너 '.$nIdx.'의 규격은 '.$oBannerPolicy->nWidth.'x'.$oBannerPolicy->nHeight.'인데 업로드한 배너 규격은 '.$oBannerPolicy->nActWidth.'x'.$oBannerPolicy->nActHeight.'입니다.<BR>';
		}
		if($sErrMsg)
			return new BaseObject(-1, $sErrMsg);
		
		$oPkgArgs = new stdClass();
		$oPkgArgs->client_srl = $oArgs->client_srl;
		$oPkgArgs->module_srl = $oArgs->module_srl;
        $oPkgArgs->landing_url = $oArgs->landing_url;
		$oPkgArgs->description = $oArgs->pkg_description;
		$oPkgRst = executeQuery('svbanner.insertAdminPackage', $oPkgArgs);
		unset($oPkgArgs);
		if(!$oPkgRst->toBool()) 
			return $oPkgRst;
		unset($oPkgRst);
		$nModuleSrl = $oArgs->module_srl;
		// set $nPackageSrl
		$oDB = DB::getInstance();
		$nPackageSrl = $oDB->db_insert_id();
		
		$oFileController = &getController('file');
		foreach($aBannerPolicy as $nIdx=>$oBannerPolicy)
		{
			$sBannerImgIdx = 'banner_img_'.$nIdx;
			if($oArgs->$sBannerImgIdx['tmp_name']) 
			{
				if(is_uploaded_file($oArgs->$sBannerImgIdx['tmp_name']))
				{
					$oFileRst = $oFileController->insertFile($oArgs->$sBannerImgIdx, $oArgs->module_srl, $nPackageSrl);
					if(!$oFileRst || !$oFileRst->toBool())
						return $oFileRst;
					$oFileController->setFilesValid($nPackageSrl);
					$nBannerFileSrl = $oFileRst->get('file_srl');
					unset($oFileRst);

					$oBannerArgs = new stdClass();
					$oBannerArgs->module_srl = $nModuleSrl;
					$oBannerArgs->package_srl = $nPackageSrl;
					$oBannerArgs->img_file_srl = $nBannerFileSrl;
					$oBannerArgs->img_width = $oBannerPolicy->nWidth;
					$oBannerArgs->img_height = $oBannerPolicy->nHeight;
					$oBannerRst = executeQuery('svbanner.insertAdminBanner', $oBannerArgs);
					if(!$oBannerRst->toBool()) 
						return $oBannerRst;
					unset($oBannerArgs);
					unset($oBannerRst);
				}
			}
		}
		unset($oFileController);
		unset($oArgs);
        // set $nContractSrl
		$oRst = new BaseObject(0, 'success_registed');
		$oRst->add('nPkgSrl', $nPackageSrl);
		return $oRst;
	}
/**
* @brief MID 삭제
**/
	public function procSvbannerAdminDeleteModInst()
	{
		$nModuleSrl = Context::get('module_srl');
		$oModuleController = &getController('module');
		$oRst = $oModuleController->deleteModule($nModuleSrl);
		if(!$oRst->toBool())
			return $oRst;
		unset($oRst);
		unset($oModuleController);
		$this->setMessage('success_deleted');
		$sReturnUrl = getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispSvbannerAdminModInstList', 'page', Context::get('page'));
		$this->setRedirectUrl($sReturnUrl);
	}
/**
 * @brief 관리자 - 기본설정 저장
 **/
	public function procSvbannerAdminConfig() 
	{
		$oArgs = Context::getRequestVars();
		if(is_null($oArgs->external_server_type))
			$oArgs->external_server_type = '';
		
		$output = $this->_saveModuleConfig($oArgs);
		if(!$output->toBool())
			$this->setMessage('error_occured');
		else
			$this->setMessage('success_updated');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispSvbannerAdminConfig','module_srl',Context::get('module_srl')));
	}
/**
 * @brief validate yyyy-mm-dd string
 **/	
    private function _validateYyyymmdd($sYyyymmdd) 
    {
        if(preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$sYyyymmdd)) 
            return true;
        else 
            return false;
    }
/**
* @brief update mid level config
**/
	private function _updateMidLevelConfig($oArgs)
	{
		if(!$oArgs->module_srl)
			return new BaseObject(-1, 'msg_invalid_module_srl');

		unset($oArgs->module);
		unset($oArgs->error_return_url);
		unset($oArgs->success_return_url);
		unset($oArgs->act);
		unset($oArgs->ext_script);
		unset($oArgs->list);
		$oModuleModel = &getModel('module');
		$oConfig = $oModuleModel->getModuleInfoByModuleSrl($oArgs->module_srl);
		foreach($oArgs as $key=>$val)
			$oConfig->{$key} = $val;
		$oModuleController = &getController('module');
		$oRst = $oModuleController->updateModule($oConfig);
		return $oRst;
	}
/**
* @brief arrange and save module level config
**/
	private function _saveModuleConfig($oArgs)
	{
		$oSvitemAdminModel = &getAdminModel('svitem');
		$oConfig = $oSvitemAdminModel->getModuleConfig();
		foreach( $oArgs as $key=>$val)
			$oConfig->{$key} = $val;

		$oModuleControll = getController('module');
		return $oModuleControll->insertModuleConfig('svitem', $oConfig);
	}
/**
 * @brief  update event 발생하면 ./files/cache/svbanner/ 폴더 비우기
 **/
	private function _resetCache()
	{
		FileHandler::removeFilesInDir('./files/cache/svbanner/');
	}
}
/* End of file svbanner.admin.controller.php */
/* Location: ./modules/svbanner/svbanner.admin.controller.php */