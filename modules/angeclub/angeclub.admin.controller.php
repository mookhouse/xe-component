<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubAdminController
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubAdminController
**/ 

class angeclubAdminController extends angeclub
{
	/**
	 * @brief initialization
	 **/
	function init() 
	{
	}
/**
 * @brief 
 */
	public function procAngeclubAdminDelete()
	{
		$module_srl = Context::get('module_srl');
		// delete docs belongint to the module
		$output = $this->_deleteAllDocsByModule( $module_srl );
		if( !$output->toBool() )
			return $output;

		// delete designated module
		
		// Get an original
		$oModuleController = getController('module');
		$output = $oModuleController->deleteModule($module_srl);
		if(!$output->toBool()) 
			return $output;

		$this->add('module','page');
		$this->add('page',Context::get('page'));
		$this->setMessage('success_deleted');

		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAngemomboxAdminIndex');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief 
 **/
	public function procAngeclubAdminConfig()
	{
        $oArgs = new stdClass();
		$sMemberAddrFieldName = Context::get('member_addr_field_name');
		if(strlen($sMemberAddrFieldName))
			$oArgs->member_addr_field_name = $sMemberAddrFieldName;
        $sConnectedMomboxMid = Context::get('connected_mombox_mid');
        if(strlen($sConnectedMomboxMid))
            $oArgs->connected_mombox_mid = $sConnectedMomboxMid;
		$oRst = $this->_saveModuleConfig($oArgs);
		if(!$oRst->toBool())
			$this->setMessage('error_occured');
		else
			$this->setMessage('success_updated');
		$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispAngeclubAdminConfig'));
	}
/**
 * @brief arrange and save module config
 **/
	private function _saveModuleConfig($oArgs)
	{
		//$oAngemomboxAdminModel = &getAdminModel('angemombox');
		//$oConfig = $oSvdocsAdminModel->getModuleConfig();
		//foreach( $oArgs as $key=>$val)
		//	$oConfig->{$key} = $val;
		$oModuleControll = getController('module');
		return $oModuleControll->insertModuleConfig('angeclub', $oArgs);
	}
/**
 * @brief module module
 */
	public function procAngeclubAdminUpdate()
	{
		$this->procAngeclubAdminInsert();
	}
/**
 * @brief add module
 */
	public function procAngeclubAdminInsert()
	{
		$oArgs = Context::getRequestVars();
		$oArgs->module = 'angeclub';
		$oArgs->mid = $oArgs->page_name;	//because if mid is empty in context, set start page mid
		unset($oArgs->page_name);
		if($oArgs->use_mobile != 'Y') 
			$oArgs->use_mobile = '';
		$oRst = $this->_saveConfigByMid($oArgs);
		unset($oArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		$this->add("page", Context::get('page'));
		$this->add('module_srl', $oRst->get('module_srl'));
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'module_srl', $oRst->get('module_srl'), 'act', 'dispAngeclubAdminModDetail');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief add module
 */
	private function _saveConfigByMid($oArgs)
	{
		$args = $oArgs;
		if($args->module_srl)
		{
			$oModuleModel = getModel('module');
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($args->module_srl);
			unset($module_info->is_skin_fix); // 기본 스킨 고정을 해제함
			unset($module_info->is_mskin_fix); // 기본 스킨 고정을 해제함
			if($module_info->module_srl != $args->module_srl)
				unset($args->module_srl);
			else
			{
				foreach($args as $key=>$val)
					$module_info->{$key} = $val;
				$args = $module_info;
			}
		}
		$oModuleController = getController('module');
		// Insert/update depending on module_srl
		if(!$args->module_srl)
			$output = $oModuleController->insertModule($args);
		else
			$output = $oModuleController->updateModule($args);
		return $output;
	}
/**
 * @brief mask multibyte string
 * param 원본문자열, 마스킹하지 않는 전단부 글자수, 마스킹하지 않는 후단부 글자수, 마스킹 마크 최대 표시수, 마스킹마크
 * echo _maskMbString('abc12234pro', 3, 2); => abc******ro
 */	
	private function _maskMbString($str, $len1, $len2=0, $limit=0, $mark='*')
	{
		$arr_str = preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
		$str_len = count($arr_str);

		$len1 = abs($len1);
		$len2 = abs($len2);
		if($str_len <= ($len1 + $len2))
			return $str;

		$str_head = '';
		$str_body = '';
		$str_tail = '';

		$str_head = join('', array_slice($arr_str, 0, $len1));
		if($len2 > 0)
			$str_tail = join('', array_slice($arr_str, $len2 * -1));

		$arr_body = array_slice($arr_str, $len1, ($str_len - $len1 - $len2));

		if(!empty($arr_body)) 
		{
			$len_body = count($arr_body);
			$limit = abs($limit);

			if($limit > 0 && $len_body > $limit)
				$len_body = $limit;

			$str_body = str_pad('', $len_body, $mark);
		}

		return $str_head.$str_body.$str_tail;
	}
}