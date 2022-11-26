<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxClass
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxClass
**/ 

class angemombox extends ModuleObject
{
	/**
	 * constructor
	 *
	 * @return void
	 */
	function angemombox()
	{
	}

	function installTriggers()
	{
		// $oModuleModel = &getModel('module');
		//  $oModuleController = &getController('module');
		// display menu in sitemap, custom menu add
		// if(!$oModuleModel->getTrigger('menu.getModuleListInSitemap', 'angemombox', 'model', 'triggerModuleListInSitemap', 'after'))
		// 	$oModuleController->insertTrigger('menu.getModuleListInSitemap', 'angemombox', 'model', 'triggerModuleListInSitemap', 'after');
	}

	/**
	 * @brief install the module
	 **/
	function moduleInstall()
	{
		$this->installTriggers();
	}

	/**
	 * @brief chgeck module method
	 **/
	function checkUpdate()
	{
		$this->installTriggers();
	}

	/**
	 * @brief update module
	 **/
	function moduleUpdate()
	{
		$this->installTriggers();
	}

	function moduleUninstall()
	{
		return FALSE;
	}
}
