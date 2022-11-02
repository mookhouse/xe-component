<?php
/**
 * @class  svbanner
 * @author singleview(root@singleview.co.kr)
 * @brief  svbanner
 */
class svbanner extends ModuleObject
{
	var $_g_DailyLogCachePath = './files/cache/svbanner/log';
	var $_g_DailyScheduleCachePath = './files/cache/svbanner/schedule';
/**
 * @brief 
 **/
	function Svbanner()
	{
	}
/**
 * @brief 모듈 설치 실행
 **/
	function moduleInstall()
	{
		$oModuleModel = &getModel('module');
		$oModuleController = &getController('module');
		return new BaseObject();
	}
/**
 * @brief 설치가 이상없는지 체크
 **/
	function checkUpdate()
	{
		return false;
	}
/**
 * @brief 업데이트(업그레이드)
 **/
	function moduleUpdate()
	{
		return new BaseObject(0, 'success_updated');
	}
/**
 * @brief 
 **/
	function moduleUninstall()
	{
	}
/**
 * @brief 캐시파일 재생성
 **/
	function recompileCache()
	{
	}
}
/* End of file svbanner.class.php */
/* Location: ./modules/svbanner/svbanner.class.php */