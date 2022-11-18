<?php
class infodata_smsAdminView extends infodata_sms
{
	function init()
	{
		$this->setTemplatePath($this->module_path . 'tpl');
		$this->setTemplateFile(str_replace('dispInfodata_sms', '', $this->act));
	}

	public function dispInfodata_smsAdminConfig()
	{
		$oModuleModel = getModel('module');
		$oConfig = $oModuleModel->getModuleConfig('infodata_sms');
		unset($oModuleModel);
		Context::set('oConfig', $oConfig);
	}

	public function dispInfodata_smsAdminListLog()
	{
		$oRst = executeQueryArray('infodata_sms.getAdminSmsLog');
		if(!$oRst->toBool())
			return $oRst;
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		Context::set('aSmsList', $oRst->data);
	}	
}
/* !End of file */
