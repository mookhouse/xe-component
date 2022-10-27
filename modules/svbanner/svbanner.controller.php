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
		$oImpArgs->imp_srl = $oArgs->imp_srl;
		$oImpArgs->member_srl = $oLoggedInfo->member_srl;
		$oImpArgs->ua = $oArgs->ua;
		$oImpArgs->uuid = $oArgs->uuid;
		$oImpArgs->page_url = $oArgs->page_url;
		$oImpArgs->is_viewed = 1;
		executeQuery('svbanner.updateImpLog', $oImpArgs);
		unset($oImpArgs);
		unset($oArgs);
		unset($oLoggedInfo);
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