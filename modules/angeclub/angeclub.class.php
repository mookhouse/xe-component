<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubClass
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubClass
**/ 

class angeclub extends ModuleObject
{
	var $_g_aClubUserState = [1=>'활동', 0=>'대기', 99=>'탈퇴'];
	var $_g_aClubUserType = [1=>'스탭', 99=>'관리자'];
	var $_g_aCenterState = [1=>'사용', 9=>'교육중지', 2=>'리모델링', 3=>'기타', 0=>'폐업'];  // 화면 표시 순서

	/**
	 * constructor
	 *
	 * @return void
	 */
	function angeclub()
	{
	}

	function installTriggers()
	{
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
