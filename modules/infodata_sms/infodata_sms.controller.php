<?php
class infodata_smsController extends infodata_sms
{
/**
* @brief 
* params ["country_code"]=>  int(82)
  ["clue"]=>  string(11) "01012345678"
  ["authcode"]=>  string(5) "75279"
  ["ipaddress"]=>  string(12) "192.168.0.56"
  ["recipient_no"]=>  string(11) "01012345678"
  ["sender_no"]=>  string(9) "02123456"
  ["content"]=>  string(74) "[핸드폰인증] 75279 ☜ 인증번호를 정확히 입력해 주세요.
* file appending
*     curl -X POST -H "Authorization:Basic eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzbXMiOiJZIiwiYXVkIjoiYW5nZV9yZXN0IiwibW1zIjoiWSIsImV4cCI6MTY0NTY4MTY1NCwicmVwIjoiTiJ9.wMrBeXOSIgXvYLMqXPNo_plBtHxI-7BOrJky2JgkGtE" -H "Content-Type: multipart/form-data; boundary='something'" -H "Accept: application/json" -F fileData=/home/w9721066/htdocs/xe-core-sv/layouts/default/visual.sub.jpg https://file.supersms.co:7010/sms/v3/file
* simple msg transmission
*     curl -X POST -H "Authorization:Basic eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzbXMiOiJZIiwiYXVkIjoiYW5nZV9yZXN0IiwibW1zIjoiWSIsImV4cCI6MTY1NDY3NTQ4NCwicmVwIjoiTiJ9.Ks3HCAPqHAmIEYMO1gll6X1BRiBSroC2cHw9rZywGdo" -H "Accept: application/json" -H "content-type: application/json" -d '{"title":"title1","from":"023334650","text":"info data SMS test text1","fileKey":"","destinations":[{"to":"+821012345678","replaceWord1":"","replaceWord2":"","replaceWord3":"","replaceWord4":"","replaceWord5":""}],"ref":"ref1","ttl":"100","paymentCode":"1","clientSubId":"1"}' https://sms.supersms.co:7020/sms/v3/multiple-destinations 
**/
	public function sendMessage($oInArgs)
	{
		$oModuleModel = getModel('module');
		$oConfig = $oModuleModel->getModuleConfig('infodata_sms');
		unset($oModuleModel);

		$sCallbackNo = $oInArgs->sender_no;
		if(!$sCallbackNo)
			$sCallbackNo = $oConfig->callback_no;

		$sSmsBody = $oInArgs->content;
		$sDestNo = $oInArgs->recipient_no;
		
		$aRst = $this->_validateRestfulApiAccessToken($oConfig);
		if($aRst['b_err'] or strlen($aRst['s_access_token'])==0)
			return false;
		
		if(strlen($sCallbackNo)==0 || strlen($sSmsBody)==0 || strlen($sDestNo)==0)
			return false;

		if($sDestNo[0] == '0') // 핸폰 번호 맨앞의 0을 지움
			$sDestNo = mb_substr($sDestNo, 1);
		$sDestNo = '+'.$oInArgs->country_code.$sDestNo;  // +821012345678
		
		$oCh = curl_init();
		curl_setopt($oCh, CURLOPT_URL, $this->_g_sRestfulServerUrl);
		curl_setopt($oCh, CURLOPT_HTTPHEADER, array(
			'Accept: application/json',
			'content-type: application/json',
			'Authorization:Basic '.$aRst['s_access_token'],  // Basic - $aTokenRst['schema'] ?
			//'X-IB-Client-Passwd: '.$_g_sApiPw
		));

		$aPostData["title"] = $oConfig->sms_title; //  "임신출산 1등 매거진 앙쥬!";
		$aPostData["from"] = $sCallbackNo ;
		$aPostData["text"] = $sSmsBody;
		$aPostData["fileKey"] = "";
		$aPostData["destinations"][0]['to'] = $sDestNo;
		$aPostData["destinations"][0]['replaceWord1'] = "";
		$aPostData["destinations"][0]['replaceWord2'] = "";
		$aPostData["destinations"][0]['replaceWord3'] = "";
		$aPostData["destinations"][0]['replaceWord4'] = "";
		$aPostData["destinations"][0]['replaceWord5'] = "";
		$aPostData["ref"] = "ref1";
		$aPostData["ttl"] = "100";
		$aPostData["paymentCode"] = "1";
		$aPostData["clientSubId"] = "1";
		$sJsonData = json_encode($aPostData, true);

		curl_setopt($oCh, CURLOPT_POST, 1);
		curl_setopt($oCh, CURLOPT_POSTFIELDS, $sJsonData);
		curl_setopt($oCh, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($oCh, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($oCh, CURLOPT_SSL_VERIFYHOST, false);
		$sResponse = curl_exec($oCh);
		if(curl_errno($oCh)) 
		{
			//echo 'Error:' . curl_error($oCh);
			return false;
		}
		curl_close($oCh);
		$aRst = json_decode($sResponse, true);
		// array(4) { ["groupId"]=> string(28) "20221118065646853C0267896253" ["toCount"]=> string(1) "1" ["destinations"]=> array(1) { [0]=> array(4) { ["messageId"]=> string(30) "20221118065646853C0267896253-0" ["to"]=> string(13) "+821031755222" ["status"]=> string(4) "R000" ["errorText"]=> string(0) "" } } ["ref"]=> string(4) "ref1" }
		$oLogArgs = new stdClass();
		$oLogArgs->msg_id = $aRst['destinations'][0]['messageId'];
		$oLogArgs->to_cid = $aRst['destinations'][0]['to'];
		$oLogArgs->status = $aRst['destinations'][0]['status'];
		$oLogArgs->ipaddress = $oInArgs->ipaddress; // requested IP referral
		$oLogArgs->message = $sSmsBody;
		$oLogArgs->error_text = $aRst['destinations'][0]['errorText'];
		executeQuery('infodata_sms.insertSmsLog', $oLogArgs);
		unset($oLogArgs);
		
		// aligned with coolsms return
		$oRst = new BaseObject();
		if($aRst['destinations'][0]['status'] != 'R000')
		{
			$oRst->add('error_code', $aRst['destinations'][0]['status']);
			$oRst->add('failure_count', 1);
		}
		else
			$oRst->add('failure_count', 0);
		$oRst->add('success_count', $aRst['toCount']);
		$oRst->add('group_id', $aRst['groupId']);
		return $oRst;
	}
/**
* @brief 
**/	
	private function _validateRestfulApiAccessToken($oConfig)
	{
		$aRst = array('b_err'=>true, 's_access_token'=>'');
		// load access token cache if exists
		$sAccessTokenFileFullpath = $this->_g_sRestfulAccessTokenCachePath.'/restful_access_token';
		$sAccessTokenCacheFile = FileHandler::readFile($sAccessTokenFileFullpath);
		if($sAccessTokenCacheFile)
		{
			$aAccessTokenInfo = unserialize($sAccessTokenCacheFile);
			if($aAccessTokenInfo['expired'] > (int)date('YmdHis') + 10)  // allow 10 sec to expiration
			{
				$aRst['b_err'] = false;
				$aRst['s_access_token'] = $aAccessTokenInfo['accessToken'];
			}
		}
		if($aRst['b_err'] or strlen($aRst['s_access_token'])==0)
		{
			# curl -X POST -H "X-IB-Client-Id:API아이디" -H "X-IB-Client-Passwd:패스워드" -H "Accept: application/json" https://auth.supersms.co:7000/auth/v3/token
			$oCh = curl_init();
			curl_setopt($oCh, CURLOPT_URL, $this->_g_sTokenServerUrl);
			curl_setopt($oCh, CURLOPT_HTTPHEADER, array(
				//'Accept: application/json',
				'Content-Type: Application/json',
				'X-IB-Client-Id: '.$oConfig->api_id,
				'X-IB-Client-Passwd: '.$oConfig->api_pw
			));
			unset($oConfig);
			
			curl_setopt($oCh, CURLOPT_POST, true);
			curl_setopt($oCh, CURLOPT_RETURNTRANSFER, true);
			// begin - options for PHP Version 5.4.16
			// https://www.learn-codes.net/php/content-type-not-being-set-in-curl-in-php/
			// https://asang-developer.tistory.com/44
			curl_setopt($oCh, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($oCh, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($oCh, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); 
			curl_setopt($oCh, CURLOPT_POSTFIELDS, []);
			// end - options for PHP Version 5.4.16

			// begin - options for PHP Version 5.3.3
			//curl_setopt($oCh, CURLOPT_SSL_VERIFYPEER, false);
			//curl_setopt($oCh, CURLOPT_SSL_VERIFYHOST, false);
			// end - options for PHP Version 5.3.3

			$sResponse = curl_exec($oCh);
			if(curl_errno($oCh)) 
			{
				//echo 'Error:' . curl_error($oCh);
				return false;
			}
			curl_close($oCh);
			$aTokenRst = json_decode($sResponse, true);
	////// test code /////////////
			//$aTokenRst['expired'] = date("Ymdhis", time() + 300);  // allow 5 mins for token
	////// test code /////////////
			$sSerializedRst = serialize($aTokenRst);
			FileHandler::writeFile($sAccessTokenFileFullpath, $sSerializedRst);
			$aRst['b_err'] = false;
			$aRst['s_access_token'] = $aTokenRst['accessToken'];
		}
		return $aRst;
	}
}
/* !End of file */