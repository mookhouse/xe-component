<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubAdminModel
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubAdminModel
**/ 
class angeclubAdminModel extends angeclub
{
/**
 * Initialization
 * @return void
 */
	function init()
	{
	}
	
	function getModuleConfig($nModuleSrl)
	{
		$oModuleModel = &getModel('module');
		if( $nModuleSrl )
		{
			if (!$GLOBALS['__angemombox_module_config__'])
			{
				$config = $oModuleModel->getModuleInfoByModuleSrl($nModuleSrl);
				$GLOBALS['__angemombox_module_config__'] = $config;
			}
			return $GLOBALS['__angemombox_module_config__'];
		}
		else
			return $oModuleModel->getModuleConfig('angemombox');
	}
}
/* End of file angemombox.admin.model.php */
/* Location: ./modules/angemombox/angemombox.admin.model.php */