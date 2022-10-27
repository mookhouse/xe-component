<?php
/**
 * @class  svbannerModel
 * @author singleview(root@singleview.co.kr)
 * @brief  svbannerModel
 */
class svbannerModel extends svbanner
{
	private $_g_aCatalogDefaultDisplay = array('checkbox', 'image', 'title', 'quantity', 'amount', 'cart_buttons', 'sales_count' );
	private $_g_aDetailDefaultDisplay = array( );
/**
 * @brief
 * @return 
 **/
	public function init() 
	{
		if (!$this->module_info->thumbnail_width)
			$this->module_info->thumbnail_width = 150;
		if (!$this->module_info->thumbnail_height)
			$this->module_info->thumbnail_height = 150;
	}
/**
 * @brief
 * @return 
 **/
	public function getModuleConfig()
	{
		$oModuleModel = &getModel('module');
		$oConfig = $oModuleModel->getModuleConfig('svbanner');
		if(is_null($oConfig))
			$oConfig = new stdClass();
		if(!$oConfig->cart_thumbnail_width) $oConfig->cart_thumbnail_width = 100;
		if(!$oConfig->cart_thumbnail_height) $oConfig->cart_thumbnail_height = 100;
		if(!$oConfig->favorite_thumbnail_width) $oConfig->favorite_thumbnail_width = 100;
		if(!$oConfig->favorite_thumbnail_height) $oConfig->favorite_thumbnail_height = 100;
		if(!$oConfig->order_thumbnail_width) $oConfig->order_thumbnail_width = 100;
		if(!$oConfig->order_thumbnail_height) $oConfig->order_thumbnail_height = 100;
		if(!$oConfig->address_input) $oConfig->address_input = 'krzip';
		
		$oConfig->currency = 'KRW';
		$oConfig->as_sign = 'Y';
		$oConfig->decimals = 0;
		return $oConfig;
	}
/**
 * @brief get mid level config
 * @return 
 **/
	public function getMidLevelConfig($nModuleSrl)
	{
		if(!$nModuleSrl)
			return new BaseObject(-1, 'msg_invalid_module_srl');
		$oModuleModel = &getModel('module');
		return $oModuleModel->getModuleInfoByModuleSrl($nModuleSrl);
	}
/**
 * @brief extract information about catalog belonged item from DB
 **/
	private function _compileTagItemListCache($nModuleSrl, $aSearchTag, $oArgs)
	{
		if( !$oArgs )
			return new BaseObject(-1, 'msg_invalid_request');
		$aTaggedItem = [];
		foreach($aSearchTag as $nIdx=>$sTag)
		{
			if(strlen($sTag))
			{
				$oArgs->sv_tags = $sTag;
				//iconv( "UTF-8", "EUC-KR", $sTag );
				$oRst = executeQueryArray('svbanner.getTaggedItemList', $oArgs);
				if (!$oRst->toBool()) 
					return $oRst;
				foreach($oRst->data as $nIdx=>$oRec)
					$aTaggedItem[(int)$oRec->item_srl] = $oRec;
			}
		}
		$aRst = [];
		if(count($aTaggedItem))
		{
			foreach($aTaggedItem as $nItemSrl=>$oRec)
				$aRst[] = $oRec;
		}
		unset($oRst->data);
		$oRst->data = $aRst;
		return $oRst;
	}
/**
 * @brief will be deprecated  
 * getItemInfoByItemSrl()의 종속 메소드로 변경해야 하나?
 * item class로 구현해야 함
 * @return 
 **/
	public function getItemStock($nItemSrl)
	{
		$config = $this->getModuleConfig();
        $oArgs = new stdClass();
		$oArgs->item_srl = $nItemSrl;
		$oItem = $this->_getItemInfo($oArgs);
		if(is_null($oItem))
			return 0;

		$nDefaultSafeStock = (int)$config->default_safe_stock;
		$nSafeStockByItem = (int)$oItem->safe_stock;
		$nSafeStock = $nDefaultSafeStock;

		if($nSafeStockByItem > 0)
			$nSafeStock = $nSafeStockByItem;

		if($config->external_server_type == 'ecaso')
		{
			require_once(_XE_PATH_.'modules/svbanner/ext_class/ecaso.class.php');
			$oExtServer = new ecaso($oItem->item_code);
			$nCurrentStock = $oExtServer->getStock();
		}
		else
			$nCurrentStock = (int)$oItem->current_stock;

		if($nSafeStock >= $nCurrentStock)
			return 0;
		else 
			return $nCurrentStock;
	}
/**
 * @brief Get a list of review
 * @return
 **/
	public function getQnas( &$item_info )
	{
		$logged_info = Context::get('logged_info');
		$oConfig = $this->getModuleConfig();
		if( !$oConfig->connected_qna_board_srl )
			return null;
		
		$nCurrentQna = $this->getQnaCnt($item_info->item_srl);
		$oModuleModel = &getModel('module');
		$oQnaBoardConfig = $oModuleModel->getModuleInfoByModuleSrl($oConfig->connected_qna_board_srl);
		$oDocumentModel = getModel('document');
		$category_content = $oDocumentModel->getCategoryPhpFile($oConfig->connected_qna_board_srl);
		require($category_content );
		$nLoadQnaCount = 0;
		$aQnas = new stdClass();
		foreach( $oConfig->qna_for_item[$item_info->item_srl] as $key=>$val)
		{
			if( $val == 'match' )
			{
				$bExceptNotice = true;
				$bLoadExtraVars = false;
				$args->category_srl = $menu->list[$key]['category_srl'];
				$args->sort_index = $oConfig->order_target?$oConfig->order_target:'list_order';
				$args->order_type = $oConfig->order_type?$oConfig->order_type:'asc';
				$args->list_count = $oConfig->max_qnas_cnt ? $oConfig->max_qnas_cnt+1 : 2+1;
				$output = $oDocumentModel->getDocumentList($args, $bExceptNotice, $bLoadExtraVars);
				foreach($output->data as $revkey=>$revval)
				{
					if( $revval->variables['status'] == 'TEMP' ) // 임시저장 후기를 숨김
						continue;

					$aQnas->data[$revval->document_srl]->title = $revval->variables['title'];
					$aQnas->data[$revval->document_srl]->member_srl = $revval->variables['member_srl'];
					if($aQnas->data[$revval->document_srl]->member_srl!=0 && $logged_info->member_srl == $aQnas->data[$revval->document_srl]->member_srl)
						$aQnas->data[$revval->document_srl]->isGranted = true;
					else
						$aQnas->data[$revval->document_srl]->isGranted = false;

					$aQnas->data[$revval->document_srl]->nick_name = $revval->variables['nick_name'];
					$aQnas->data[$revval->document_srl]->regdate = $revval->variables['regdate'];
					$aQnas->data[$revval->document_srl]->readed_count = $revval->variables['readed_count'];
					$aQnas->data[$revval->document_srl]->voted_count = $revval->variables['voted_count'];
					$aQnas->data[$revval->document_srl]->blamed_count = $revval->variables['blamed_count'];
					$aQnas->data[$revval->document_srl]->comment_count = $revval->variables['comment_count'];
					$nLoadQnaCount++;
				}
			}
		}
		$aQnas->mid = $oQnaBoardConfig->mid;
		if(count($aQnas->data))
		{
			$aQnas->qnas_per_page = $oConfig->qnas_per_page ? $oConfig->qnas_per_page : 2;
			$aQnas->remaining_qnas = $nCurrentQna - $nLoadQnaCount;
			$aQnas->category_srl = $args->category_srl;
		}
		return $aQnas;
	}
/**
 * @brief
 */
	public function getQnaCnt($nItemSrl)
	{
		$oConfig = $this->getModuleConfig();
		if( $oConfig->connected_qna_board_srl > 0 )
		{
			$oDocumentModel = getModel('document');
			$category_content = $oDocumentModel->getCategoryPhpFile($oConfig->connected_qna_board_srl);

			require($category_content );
			$nQnaCnt = 0;
			foreach( $oConfig->qna_for_item[$nItemSrl] as $key=>$val)
			{
				if( $val == 'match' )
					$nQnaCnt += (int)$menu->list[$key]['document_count'];
			}
		}
		else
			$nQnaCnt = 0;
		return $nQnaCnt;
	}
/**
 * @brief 
 */
	public function getReviewCnt($nItemSrl)
	{
		$oConfig = $this->getModuleConfig();
		if( $oConfig->connected_review_board_srl > 0 )
		{
			$oDocumentModel = getModel('document');
			$category_content = $oDocumentModel->getCategoryPhpFile($oConfig->connected_review_board_srl);

			require($category_content );
			$nReviewCnt = 0;
			foreach( $oConfig->review_for_item[$nItemSrl] as $key=>$val)
			{
				if( $val == 'match' )
					$nReviewCnt += (int)$menu->list[$key]['document_count'];
			}
		}
		else
			$nReviewCnt = 0;
		return $nReviewCnt;
	}
/**
 * @brief Get a list of review
 * @return
 **/
	public function getReviews( &$item_info )
	{
		$logged_info = Context::get('logged_info');
		$oConfig = $this->getModuleConfig();
		if( !$oConfig->connected_review_board_srl )
			return null;

		$nCurrentReview = $this->getReviewCnt($item_info->item_srl);
		$oModuleModel = &getModel('module');
		$oReviewBoardConfig = $oModuleModel->getModuleInfoByModuleSrl($oConfig->connected_review_board_srl);
		$oDocumentModel = getModel('document');
		$category_content = $oDocumentModel->getCategoryPhpFile($oConfig->connected_review_board_srl);
		require($category_content );
		$nLoadReviewCount = 0;
		$aReviews = new stdClass();
		foreach( $oConfig->review_for_item[$item_info->item_srl] as $key=>$val)
		{
			if( $val == 'match' )
			{
				$bExceptNotice = true;
				$bLoadExtraVars = false;
				$args->category_srl = $menu->list[$key]['category_srl'];
				$args->sort_index = $oConfig->order_target?$oConfig->order_target:'list_order';
				$args->order_type = $oConfig->order_type?$oConfig->order_type:'asc';
				//$args->list_count = $oConfig->reviews_per_item;
				$output = $oDocumentModel->getDocumentList($args, $bExceptNotice, $bLoadExtraVars);
				foreach($output->data as $revkey=>$revval)
				{
					if( $revval->variables['status'] == 'TEMP' ) // 임시저장 후기를 숨김
						continue;

					$aReviews->data[$revval->document_srl]->title = $revval->variables['title'];
					$aReviews->data[$revval->document_srl]->member_srl = $revval->variables['member_srl'];
					if($aReviews->data[$revval->document_srl]->member_srl!=0 && $logged_info->member_srl == $aReviews->data[$revval->document_srl]->member_srl)
						$aReviews->data[$revval->document_srl]->isGranted = true;
					else
						$aReviews->data[$revval->document_srl]->isGranted = false;

					$aReviews->data[$revval->document_srl]->nick_name = $revval->variables['nick_name'];
					$aReviews->data[$revval->document_srl]->regdate = $revval->variables['regdate'];
					$aReviews->data[$revval->document_srl]->readed_count = $revval->variables['readed_count'];
					$aReviews->data[$revval->document_srl]->voted_count = $revval->variables['voted_count'];
					$aReviews->data[$revval->document_srl]->blamed_count = $revval->variables['blamed_count'];
					$aReviews->data[$revval->document_srl]->comment_count = $revval->variables['comment_count'];
					$nLoadReviewCount++;
				}
			}
		}
		if(!count($aReviews->data))
			return null;

		$aReviews->mid = $oReviewBoardConfig->mid;
		$aReviews->reviews_per_page = $oConfig->reviews_per_page ? $oConfig->reviews_per_page : 2;
		$aReviews->remaining_reviews = $nCurrentReview - $nLoadReviewCount;
		$aReviews->category_srl = $args->category_srl;

		return $aReviews;
	}
}
/* End of file svitem.model.php */
/* Location: ./modules/svitem/svitem.model.php */