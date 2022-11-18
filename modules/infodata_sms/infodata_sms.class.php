<?php
/**
 * @class  infodata_sms
 * @author singleview(root@singleview.co.kr)
 * @brief  infodata_sms
 */
class infodata_sms extends ModuleObject
{
	var $_g_sRestfulAccessTokenCachePath = './files/cache/infodata_sms';
	var $_g_sTokenServerUrl = "https://auth.supersms.co:7000/auth/v3/token";
	var $_g_sRestfulServerUrl = "https://sms.supersms.co:7020/sms/v3/multiple-destinations";
/**
 * @brief 
 **/
	function infodata_sms()
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
/* End of file infodata_sms.class.php */
/* Location: ./modules/infodata_sms/infodata_sms.class.php */