<?php
class infodata_smsAdminController extends infodata_sms
{
/**
 * @brief 관리자 - 기본설정 저장
 **/
	public function procInfodata_smsAdminConfig() 
	{
		$oArgs = Context::getRequestVars();
		$oRst = $this->_saveModuleConfig($oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			$this->setMessage('error_occured');
		else
			$this->setMessage('success_updated');
		unset($oRst);
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispInfodata_smsAdminConfig'));
	}
/**
* @brief arrange and save module level config
**/
	private function _saveModuleConfig($oArgs)
	{
		$oInfodata_smsModel = &getModel('infodata_sms');
		$oConfig = $oInfodata_smsModel->getModuleConfig();
		unset($oInfodata_smsModel);
		foreach($oArgs as $key=>$val)
			$oConfig->{$key} = $val;

		// remove unneccesary args
		unset($oConfig->error_return_url);
		unset($oConfig->module);
		unset($oConfig->act);
		unset($oConfig->success_return_url);
		$oModuleControll = getController('module');
		return $oModuleControll->insertModuleConfig('infodata_sms', $oConfig);
	}
}
/* !End of file */