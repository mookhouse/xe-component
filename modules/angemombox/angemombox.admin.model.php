<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxAdminModel
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxAdminModel
**/ 
class angemomboxAdminModel extends angemombox
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
		return $oModuleModel->getModuleConfig('angemombox');
	}	
	function getMidConfig($nModuleSrl)
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
/**
 * 응모자 삭제 호출 callback 함수
 * @return void
 */
	function getAngemomboxAdminDeleteDoc() 
	{
		$doc_srls = Context::get('doc_srls');
		$doc_names = Context::get('doc_names');
		$doc_phones = Context::get('doc_phones');

		foreach($doc_names as $key => $val)
			$doc_name_to_be_deleted[$key] = $val;

		foreach($doc_phones as $key => $val)
			$doc_phone_to_be_deleted[$key] = $val;

		Context::set('doc_srls_to_be_deleted', $doc_srls);
		Context::set('doc_names_to_be_deleted', $doc_name_to_be_deleted);
		Context::set('doc_phones_to_be_deleted', $doc_phone_to_be_deleted);

		$oTemplate = &TemplateHandler::getInstance();
		$tpl = $oTemplate->compile($this->module_path.'tpl', 'form_delete_doc');

		$this->add('tpl', str_replace("\n"," ",$tpl));
	}
/**
 * retreive year month range for the application
 */
	public function getYrMoRangeByModuleSrl($nModuleSrl)
	{
		$sFirstYrMo = null;
		$sLastYrMo = null;

		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oRst = executeQuery('angemombox.getAdminMinYrMoByModuleSrl', $oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');
		if(count($oRst->data))
		{
			$oRec = array_shift($oRst->data);
			$sFirstYrMo = substr($oRec->yr_mo, 0, 4).'-'.substr($oRec->yr_mo, 4, 2);
		}
		unset($oRec);
		unset($oRst);
//var_dump($sFirstYrMo);
//echo '<BR>';
		$oRst = executeQuery('angemombox.getAdminMaxYrMoByModuleSrl', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');
		if(count($oRst->data))
		{
			$oRec = array_shift($oRst->data);
			$sLastYrMo = substr($oRec->yr_mo, 0, 4).'-'.substr($oRec->yr_mo, 4, 2);
		}
		unset($oRec);
		unset($oRst);
//var_dump($sLastYrMo);
//echo '<BR>';
		if($sFirstYrMo && $sLastYrMo)
			$aYrMo = $this->_getMonthRange(@strtotime($sFirstYrMo), @strtotime($sLastYrMo));
		else
			$aYrMo = [];
//var_dump($aYrMo);
//echo '<BR>';		
		return $aYrMo;
	}
/**
 * retreive applicant list
 */
	public function getWinnerInfoByModuleSrl($nModuleSrl, $sYrMo)
	{
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->yr_mo = $sYrMo;
		$oRst = executeQueryArray('angemombox.getAdminApplicantListByModuleSrlYrMo', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');
		$nWinner = 0;  // 당첨자수
		$nDropout = 0;  // 탈락자수
		if(count($oRst->data))
		{
			foreach($oRst->data as $nIdx=>$oRec)
			{
				if($oRec->is_accepted == 'Y')
					$nWinner++;
				else
					$nDropout++;
			}
		}
		$oRst = new BaseObject();
		$oRst->add('nWinner', $nWinner);
		$oRst->add('nDropout', $nDropout);
		return $oRst;
	}
/**
 * 
 */
	private function _getMonthRange($dtStart, $dtEnd)
	{
		$aRet = [];
		if($dtStart == $dtEnd)
		{
			$aRet[] = date('Ym', $dtStart);
		}
		elseif($dtStart < $dtEnd)
		{
			$dtCurrent = $dtStart;
			while($dtCurrent < $dtEnd)
			{
				$dtNext = @date('Y-M-01', $dtCurrent) . "+1 month";
				$dtCurrent = @strtotime($dtNext);
				$aRet[] = date('Ym', $dtCurrent);
			}
			$aRet = array_reverse($aRet);
		}
		return $aRet;
	}
}
/* End of file angemombox.admin.model.php */
/* Location: ./modules/angemombox/angemombox.admin.model.php */