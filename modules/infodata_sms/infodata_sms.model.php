<?php
/**
 * @class  infodata_smsModel
 * @author singleview(root@singleview.co.kr)
 * @brief  infodata_smsModel
 */
class infodata_smsModel extends infodata_sms
{
/**
 * @brief
 * @return 
 **/
	public function init() 
	{
	}
/**
 * @brief
 * @return 
 **/
	public function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		$oConfig = $oModuleModel->getModuleConfig('infodata_sms');
		if(is_null($oConfig))
			$oConfig = new stdClass();
		return $oConfig;
	}
}
/* End of file infodata_sms.model.php */
/* Location: ./modules/infodata_sms/infodata_sms.model.php */