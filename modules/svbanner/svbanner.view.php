<?php
/**
 * @class  svbannerView
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerView
 */
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
		Context::addJsFile($sScriptPath.'sv_banner_iframe.js');
		Context::addJsFile($sScriptPath.'jquery.cookie.js');

		$aBannerDim = explode('x', trim(Context::get('dim')));
		$oSvbannerModel = &getModel('svbanner');
		$oRst = $oSvbannerModel->getCurrentBanner($this->module_info->module_srl, $aBannerDim);
		Context::set('nImpSrl', $oRst->nImpLogSrl);
		Context::set('nSelectedBannerSrl', $oRst->nBannerSrl);
		Context::set('sBannerImgUrl', $oRst->sBannerImgUrl);
		Context::set('sBannerLandingUrl', $oRst->sLandingUrl);
		unset($oRst);
		$oConfig = $oSvbannerModel->getModuleConfig();
		Context::set('fDuplicatedClickLimitDay', $oConfig->duplicated_click_limit_day);
		unset($oConfig);
		unset($oSvbannerModel);
		Context::set('sUniqId', uniqid());
		$this->setTemplateFile('index_iframe');
	}
}
/* End of file svbanner.view.php */
/* Location: ./modules/svbanner/svbanner.view.php */