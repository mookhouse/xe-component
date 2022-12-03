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
	
	function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		return $oModuleModel->getModuleConfig('angeclub');
	}
}
/* End of file angemombox.admin.model.php */
/* Location: ./modules/angemombox/angemombox.admin.model.php */