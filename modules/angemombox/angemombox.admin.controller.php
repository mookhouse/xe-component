<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxAdminController
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxAdminController
**/ 

class angemomboxAdminController extends angemombox
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
	public function procAngemomboxAdminAddWinner()
	{
		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');
		$sYrMo = (int)Context::get('yr_mo');
		if(!$sYrMo) 
			return new BaseObject(-1, 'msg_invalid_yr_mo');

		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->yr_mo = $sYrMo;
		$oRst = executeQueryArray('angemombox.getAdminApplicantListByModuleSrlYrMo', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool())
			return new BaseObject(-1, 'msg_error_angemombox_db_query');

		$aDropouts = [];
		if(count($oRst->data))
		{
			foreach($oRst->data as $nIdx=>$oRec)
			{
				if($oRec->is_accepted != 'Y')
					$aDropouts[$oRec->doc_srl] = 1;
			}
		}
		$nAdditionalWinnerCnt = (int)Context::get('additional_winner_cnt');
		if($nAdditionalWinnerCnt)
		{
			$nDropouts = count($aDropouts);
			$nAdditionalWinnerCnt = $nAdditionalWinnerCnt > $nDropouts ? $nDropouts : $nAdditionalWinnerCnt;
// var_dump($nAdditionalWinnerCnt );
// var_dump($aDropouts );
			
			$aRandWinner = array_rand($aDropouts, $nAdditionalWinnerCnt);
			$sTypeName = gettype($aRandWinner);
			$oArgs = new stdClass();
			if($sTypeName == 'integer')
			{
				$oArgs->module_srl = $nModuleSrl;
				$oArgs->doc_srl = $aRandWinner;
				$oArgs->is_accepted = 'Y';
				executeQuery('angemombox.updateAdminApproval', $oArgs);
			}
			elseif($sTypeName == 'array')
			{
				foreach($aRandWinner as $nIdx=>$nDocSrl)
				{
					$oArgs->module_srl = $nModuleSrl;
					$oArgs->doc_srl = $nDocSrl;
					$oArgs->is_accepted = 'Y';
					executeQuery('angemombox.updateAdminApproval', $oArgs);
				}
			}
			unset($oArgs);
		}
		$this->add('nRst', 1);
	}
/**
 * @brief delete designated module
 */
	public function procAngemomboxAdminDelete()
	{
		$nModuleSrl = Context::get('module_srl');
		$oModuleController = getController('module');
		$oRst = $oModuleController->deleteModule($nModuleSrl);
		if(!$oRst->toBool()) 
			return $oRst;
        unset($oRst);
		$this->add('module','page');
		$this->add('page',Context::get('page'));
		$this->setMessage('success_deleted');
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAngemomboxAdminIndex');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief 
 */
	public function procAngemomboxAdminDeleteDoc()
	{
		$nModuleSrl = Context::get('module_srl');
		$nDocSrls = Context::get('doc_srls');
		// delete docs belongint to the module
		$oRst = $this->_deleteSingleDocsByModule($nModuleSrl, $nDocSrls);
		if(!$oRst->toBool())
			return $oRst;
		unset($oRst);
		$this->add('module','page');
		$this->add('page',Context::get('page'));
		$this->setMessage('success_deleted');
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'act', 'dispAngemomboxAdminApplicantsList', 'module_srl', $module_srl, 'page',Context::get('page'));
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief
 */
	private function _deleteSingleDocsByModule($nModuleSrl, $aDocSrl)
	{
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		foreach($aDocSrl as $key => $val)
		{
			$oArgs->doc_srl = $val;
			$oRst = executeQuery('angemombox.updateSingleDocDeleted', $oArgs ); // 삭제 표시
			if(!$oRst->toBool())
				return new BaseObject(-1, 'msg_error_angemombox_db_query');
		}
		return new BaseObject(0);
	}
/**
 * @brief module module
 */
	public function procAngemomboxAdminUpdate()
	{
		$this->procAngemomboxAdminInsert();
	}
/**
 * @brief add module
 */
	public function procAngemomboxAdminInsert()
	{
		$oArgs = Context::getRequestVars();
		$oArgs->module = 'angemombox';
		$oArgs->mid = $oArgs->page_name;	//because if mid is empty in context, set start page mid
		unset($oArgs->page_name);
		if($oArgs->use_mobile != 'Y') 
			$oArgs->use_mobile = '';
		if($oArgs->angeclub_exclusive != 'Y') 
			$oArgs->angeclub_exclusive = '';
		$oRst = $this->_saveConfigByMid($oArgs);
		unset($oArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		$this->add("page", Context::get('page'));
		$this->add('module_srl', $oRst->get('module_srl'));
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'module_srl', $oRst->get('module_srl'), 'act', 'dispAngemomboxAdminInfo');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief 
 */
	public function procAngemomboxAdminApproveApplicant()
	{
		$nModuleSrl = (int)Context::get('module_srl');
		if(!$nModuleSrl)
			return new BaseObject( -1, 'msg_error_invalid_module_srl');
		$nDocSrl = (int)Context::get('doc_srl');
		if(!$nDocSrl)
			return new BaseObject( -1, 'msg_error_invalid_doc_srl');
		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->doc_srl = $nDocSrl;
		$oArgs->is_accepted = Context::get('approve');
		$oRst = executeQuery('angemombox.updateAdminApproval', $oArgs);
		unset($oArgs);
		if(!$oRst->toBool()) 
			return $oRst;
		$returnUrl = Context::get('success_return_url') ? Context::get('success_return_url') : getNotEncodedUrl('', 'module', 'admin', 'module_srl', $nModuleSrl,'doc_srl', $nDocSrl, 'act', 'dispAngemomboxAdminDocDetail');
		$this->setRedirectUrl($returnUrl);
	}
/**
 * @brief add module
 */
	private function _saveConfigByMid($oArgs)
	{
		$oInArgs = $oArgs;
		if($oInArgs->module_srl)
		{
			$oModuleModel = getModel('module');
			$oModuleInfo = $oModuleModel->getModuleInfoByModuleSrl($oInArgs->module_srl);
			unset($oModuleInfo->is_skin_fix); // 기본 스킨 고정을 해제함
			unset($oModuleInfo->is_mskin_fix); // 기본 스킨 고정을 해제함
			if($oModuleInfo->module_srl != $oInArgs->module_srl)
				unset($oInArgs->module_srl);
			else
			{
				foreach($oInArgs as $key=>$val)
					$oModuleInfo->{$key} = $val;
				$oInArgs = $oModuleInfo;
			}
		}
		$oModuleController = getController('module');
		if(!$oInArgs->module_srl)
			$oRst = $oModuleController->insertModule($oInArgs);  // Insert
		else
			$oRst = $oModuleController->updateModule($oInArgs);  // update
		unset($oInArgs);
        return $oRst;
	}
/**
 * @brief 
 **/
public function procAngemomboxAdminConfig()
{
// 	$oArgs = Context::getRequestVars();
// var_dump($oArgs);
// 	exit;

	$oArgs = new stdClass();
	$sMemberAddrFieldName = Context::get('member_addr_field_name');
	if(strlen($sMemberAddrFieldName))
		$oArgs->member_addr_field_name = $sMemberAddrFieldName;
	$sMemberGenderFieldName = Context::get('member_gender_field_name');
	if(strlen($sMemberGenderFieldName))
		$oArgs->member_gender_field_name = $sMemberGenderFieldName;
	$sMemberSmspushFieldName = Context::get('member_sms_push_field_name');
	if(strlen($sMemberSmspushFieldName))
		$oArgs->member_sms_push_field_name = $sMemberSmspushFieldName;
	$sMemberEmailpushFieldName = Context::get('member_email_push_field_name');
	if(strlen($sMemberEmailpushFieldName))
		$oArgs->member_email_push_field_name = $sMemberEmailpushFieldName;
	$sMemberPostpushFieldName = Context::get('member_post_push_field_name');
	if(strlen($sMemberPostpushFieldName))
		$oArgs->member_post_push_field_name = $sMemberPostpushFieldName;
	$sMemberSponsorpushFieldName = Context::get('member_sponsor_push_field_name');
	if(strlen($sMemberSponsorpushFieldName))
		$oArgs->member_sponsor_push_field_name = $sMemberSponsorpushFieldName;

	$oRst = $this->_saveModuleConfig($oArgs);
	if(!$oRst->toBool())
		$this->setMessage('error_occured');
	else
		$this->setMessage('success_updated');
	$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispAngemomboxAdminConfig'));
}
/**
 * @brief arrange and save module config
 **/
	private function _saveModuleConfig($oArgs)
	{
		$oModuleControll = getController('module');
		return $oModuleControll->insertModuleConfig('angemombox', $oArgs);
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
/**
 * @brief 
 */	
	function procAngemomboxAdminCSVDownloadByModule() 
	{
		$nModuleSrl = Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');
		
		//header( 'Content-Type: text/html; charset=UTF-8' );
		header( 'Content-Type: Application/octet-stream; charset=UTF-8' );
		header( "Content-Disposition: attachment; filename=\"angemombox_raw-".date('Ymd').".csv\"");
		echo chr( hexdec( 'EF' ) );
		echo chr( hexdec( 'BB' ) );
		echo chr( hexdec( 'BF' ) );
		
		// 기본 컬럼 제목 설정 시작
		$oDataConfig = Array( 'doc_srl','module_srl','member_srl','applicant_name','applicant_name_secured','applicant_phone','applicant_phone_secured','ipaddress','privacy_collection','privacy_sharing','user_agent','datetimestamp_entry','datetimestamp_final','duration_sec','init_referral','utm_source', 'utm_medium', 'utm_campaign', 'utm_term','is_mobile','is_accepted','regdate','member_id' );

		foreach( $oDataConfig as $key => $val )
			echo "\"".$val."\",";
		// 기본 컬럼 제목 설정 끝
		
		// extra_vars의 컬럼 제목 설정 시작
		$oAngemomboxModel = getModel('angemombox');
		$extra_keys = $oAngemomboxModel->getExtraKeys($nModuleSrl);
		
		$aAddrEid = array();
		$aCheckBoxEid = array();
		$aCheckBoxAnswers = array();
		$aTempAddrValue = array();
		$nPhoneNumberLegnth = 0;
		foreach( $extra_keys as $key=>$val)
		{
			switch( $val->type )
			{
				case 'kr_zip':
					echo "\"post_code\",\"address\",";
					$aAddrEid[] = $val->eid;
					break;
				case 'checkbox':
					$aMultipleAnswer = explode( ',', $val->default);
					foreach( $aMultipleAnswer as $key1=>$val1)
						echo "\"".$val->name.'_'.$val1."\",";

					$aCheckBoxAnswers[$val->eid] = $aMultipleAnswer;
					$aCheckBoxEid[] = $val->eid;
					break;
				default:
					echo "\"".$val->name."\",";
					break;
			}
		}
		// extra_vars의 컬럼 제목 설정 끝
		// svauth 모듈이 있다면 실명인증 정보 추출
		if( getClass('svauth') )
		{
			$oSvauthAdminModel = getAdminModel('svauth');
			$oSvauthDataConfig = Array( '인증일시', '인증실명','인증생일','인증성별','인증국적','인증통신사','인증핸드폰' );
			if( getClass('svcrm') )
			{
				$oSvcrmAdminModel = &getAdminModel('svcrm');
				$oSvcrmConfig = $oSvcrmAdminModel->getModuleConfig();
				$aPrivacyAccessPolicy = $oSvcrmConfig->privacy_access_policy;
				unset($oSvcrmConfig);
			}
		}
		// svauth 컬렘 제목 설정 시작
		foreach( $oSvauthDataConfig as $key => $val )
			echo "\"".$val."\",";
		// svauth 컬렘 제목 설정 끝

		echo "\r\n";
		$oMemberModel = &getModel('member');
		$oAngemomboxAdminModel = getAdminModel('angemombox');

		$args->module_srl = $nModuleSrl;
		$args->is_deleted_doc = 0;
		$args->is_accepted = 'Y';
		$args->list_count = 99999;
		$output = executeQueryArray('angemombox.getAdminAngemomboxByModule', $args);
		if( !$output->toBool() )
			return $output;

		$data = $output->data;

		// extra_vars의 컬럼 데이터 설정
		if( count( $data ) )
		{
			$to_time = 0;
			$from_time = 0;

			foreach( $data as $key1 => $val1 )
			{
				$nMemberSrl = (int)$val1->member_srl;
				$nDocSrl = (int)$val1->doc_srl;
				foreach( $val1 as $key2 => $val2 )
				{
					if( $key2 == 'is_deleted_doc' )
						continue;

					if( $key2 == 'datetimestamp_entry' )
						$to_time = strtotime( $val2 );
						
					if( $key2 == 'datetimestamp_final' )
					{
						echo "\"".$val2."\",";
						$from_time = strtotime( $val2 );
						$duration_sec = round(abs($to_time - $from_time), 2 );
						echo "\"".$duration_sec."\",";
						continue;
					}

					if( $key2 == 'applicant_name' )
						echo "\"".$val2."\",\"".$this->_maskMbString($val2,1,1)."\",";
					else if( $key2 == 'applicant_phone' )
					{
						$nPhoneNumberLegnth = strlen( $val2 );
						switch( $nPhoneNumberLegnth )
						{
							case 10:
								echo "\"".substr($val2, 0, 3).'-'.substr($val2, 3, 3).'-'.substr($val2, 6, 4)."\","; // original number
								echo "\"".$this->_maskMbString(substr($val2, 0, 3),2,0).'-'.$this->_maskMbString(substr($val2, 3, 3),0,0).'-'.substr($val2, 6, 4)."\","; // masked number
								break;
							case 11:
								echo "\"".substr($val2, 0, 3).'-'.substr($val2, 3, 4).'-'.substr($val2, 7, 4)."\","; // original number
								echo "\"".$this->_maskMbString(substr($val2, 0, 3),2,0).'-'.$this->_maskMbString(substr($val2, 3, 4),0,0).'-'.substr($val2, 7, 4)."\","; // masked number
								break;
							default:
								echo "\"".$val2."\","; // original number
								echo "\"".$this->_maskMbString($val2,2,4)."\","; // masked number
						}
					}
					else if( $key2 == 'regdate' )
					{
						$dtTemp = strtotime($val2);
						echo "\"". date("Y-m-d H:i:s",$dtTemp)."\",";
					}
					else
						echo "\"".$val2."\",";
				}

				// member_id
				$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($nMemberSrl, 0, $columnList);
				if($oMemberInfo)
					echo "\"".$oMemberInfo->user_id."\",";
				else
					echo "\"withdraw\",";
				// extra_vars
				$aExtraVars = $oAngemomboxAdminModel->getDocExtraVars($nModuleSrl, $nDocSrl);
				
				foreach( $aExtraVars as $key=>$val)
				{
					if (in_array($val->eid, $aAddrEid)) 
					{
						$aTempAddrValue = explode( "|@|", $val->value );
						echo "\"".$aTempAddrValue[0]."\",\"".$aTempAddrValue[1]." ".$aTempAddrValue[2]." ".$aTempAddrValue[3]."\",";
					}
					else if (in_array($val->eid, $aCheckBoxEid)) 
					{
						$sMultipleChoices = null;
						$aTempCbValue = explode( "|@|", $val->value );
						$naTempCbValue = count( $aTempCbValue );
						foreach( $aCheckBoxAnswers[$val->eid] as $key1 => $val1 )
						{
							if (in_array($val1, $aTempCbValue))
								$sMultipleChoices .= "1,";
							else
							{
								$sLastElem = $aTempCbValue[$naTempCbValue -1];
								if( $val1 == '기타' && strlen($sLastElem) && !in_array($sLastElem, $aCheckBoxAnswers[$val->eid]) )
									$sMultipleChoices .= $sLastElem.",";
								else
									$sMultipleChoices .= "0,";
							}
						}
						echo $sMultipleChoices;
					}
					else
						echo "\"".$val->value."\",";
				}

				if( $oSvauthAdminModel )
				{
					$oSvauthData = $oSvauthAdminModel->getMemberAuthInfo($nMemberSrl,$aPrivacyAccessPolicy);
					foreach( $oSvauthDataConfig as $authkey=>$authval)
					{
						if( $authval == '인증일시' )
						{
							if( $oSvauthData->{$authval} == NULL )
								echo "\"\",";
							else
							{
								$dtTemp = strtotime($oSvauthData->{$authval});
								echo "\"". date("Y-m-d H:i:s",$dtTemp)."\",";
							}
						}
						else if( $authval == '인증생일' )
						{
							if( $oSvauthData->{$authval} == NULL )
								echo "\"\",";
							else if( $oSvauthData->{$authval} == '열람권한없음' )
								echo "\"".$oSvauthData->{$authval}."\",";
							else
							{
								$dtTemp = strtotime($oSvauthData->{$authval});
								echo "\"". date("Y-m-d",$dtTemp)."\",";
							}
						}
						else if(  $authval == '인증핸드폰' )
						{
							$sAuthPhoneNumber = $oSvauthData->{$authval};
							$nPhoneNumberLegnth = strlen( $sAuthPhoneNumber );
							switch( $nPhoneNumberLegnth )
							{
								case 10:
									echo "\"".substr($sAuthPhoneNumber, 0, 3).'-'.substr($sAuthPhoneNumber, 3, 3).'-'.substr($sAuthPhoneNumber, 6, 4)."\",";
									break;
								case 11:
									echo "\"".substr($sAuthPhoneNumber, 0, 3).'-'.substr($sAuthPhoneNumber, 3, 4).'-'.substr($sAuthPhoneNumber, 7, 4)."\",";
									break;
								default:
									echo "\"".$sAuthPhoneNumber."\",";
							}
						}
						else
							echo "\"".$oSvauthData->{$authval}."\",";
					}
				}
				echo "\r\n";
			}
		}
		exit(0);
	}
/**
 * @brief 
 **/
	// public function procAngemomboxAdminInsertConfig()
	// {
	// 	$sGaV3TrackingId = Context::get('ga_v3_tracking_id');
	// 	if(strlen($sGaV3TrackingId))
	// 		$oArgs->ga_v3_tracking_id = $sGaV3TrackingId;
	// 	$oRst = $this->_saveModuleConfig($oArgs);
	// 	if(!$oRst->toBool())
	// 		$this->setMessage('error_occured');
	// 	else
	// 		$this->setMessage('success_updated');
	// 	$this->setRedirectUrl(getNotEncodedUrl('', 'module', Context::get('module'), 'act', 'dispAngemomboxAdminConfig'));
	// }
/**
 * @brief arrange and save module config
 **/
	// private function _saveModuleConfig($oArgs)
	// {
	// 	//$oAngemomboxAdminModel = &getAdminModel('angemombox');
	// 	//$oConfig = $oSvdocsAdminModel->getModuleConfig();
	// 	//foreach( $oArgs as $key=>$val)
	// 	//	$oConfig->{$key} = $val;
	// 	$oModuleControll = getController('module');
	// 	$oRst = $oModuleControll->insertModuleConfig('angemombox', $oArgs);
	// 	return $oRst;
	// }
}