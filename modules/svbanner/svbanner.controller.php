<?php
/**
 * @class  svbannerController
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerController
 */
class svbannerController extends svbanner
{
/**
 * @brief 배너의 노출수 기록
 */	
	public function procSvbannerPatchImp()
	{
		$oArgs = Context::getRequestVars();
		$oLoggedInfo = Context::get('logged_info');

		$oImpArgs = new stdClass();
		$oImpArgs->member_srl = $oLoggedInfo->member_srl;
		unset($oLoggedInfo);
		$oImpArgs->imp_srl = $oArgs->imp_srl;
		$oImpArgs->ua = $oArgs->ua;
		$oImpArgs->uuid = $oArgs->uuid;
		$oImpArgs->is_viewed = 1;
        // 내부 배너면 도메인 주소 제거하여 DB 용량 최소화
		$sStrippedUrl = str_replace($_SERVER['REQUEST_SCHEME'].'://'. $_SERVER['SERVER_NAME'],'', $oArgs->page_url);
        // url 쿼리 제거하여 DB 용량 최소화
        $aStrippedUrl = explode('?', $sStrippedUrl);
        $oImpArgs->page_url = $aStrippedUrl[0];
        unset($aStrippedUrl);
		$oRst = executeQuery('svbanner.updateImpLog', $oImpArgs);
		unset($oImpArgs);
		unset($oArgs);
		$this->add('nRst', 0);
	}
/**
 * @brief 배너의 노출수 기록
 */	
	public function procSvbannerPatchClk()
	{
		$oArgs = Context::getRequestVars();
		$oClkArgs = new stdClass();
		$oClkArgs->imp_srl = $oArgs->imp_srl;
		$oClkArgs->is_clicked = 1;
		executeQuery('svbanner.updateClkLog', $oClkArgs);
		unset($oClkArgs);
		unset($oArgs);
		$this->add('nRst', 0);
	}
}
/* End of file svbanner.controller.php */
/* Location: ./modules/svbanner/svbanner.controller.php */