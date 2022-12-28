<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxClass
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxClass
**/ 

class angemombox extends ModuleObject
{
	var $_g_aGender = ['여'=>'F', '남'=>'M'];
	/**
	 * constructor
	 *
	 * @return void
	 */
	function angemombox()
	{
	}

	/**
	 * @brief install the module
	 **/
	function moduleInstall()
	{
		return new BaseObject();
	}

	/**
	 * @brief chgeck module method
	 **/
	function checkUpdate()
	{
		$oModuleModel = &getModel('module');
		if(!$oModuleModel->getTrigger('member.insertMember', 'angemombox', 'controller', 'triggerInsertMemberAfter', 'after'))
			return true;
		if(!$oModuleModel->getTrigger('member.updateMember', 'angemombox', 'controller', 'triggerUpdateMemberAfter', 'after'))
			return true;
		// if(!$oModuleModel->getTrigger('member.deleteMember', 'svauth', 'controller', 'triggerDeleteMemberBefore', 'before'))
		// 	return true;
		unset($oModuleModel);
		return false;
	}

	/**
	 * @brief update module
	 **/
	function moduleUpdate()
	{
		$oModuleController = &getController('module');
		$oModuleController->insertTrigger('member.insertMember', 'angemombox', 'controller', 'triggerInsertMemberAfter', 'after');
		$oModuleController->insertTrigger('member.updateMember', 'angemombox', 'controller', 'triggerUpdateMemberAfter', 'after');
		// $oModuleController->insertTrigger('member.deleteMember', 'svauth', 'controller', 'triggerDeleteMemberBefore', 'before');
		unset($oModuleController);
		return new BaseObject(0, 'success_updated');
	}

	function moduleUninstall()
	{
		return new BaseObject();
	}
}
