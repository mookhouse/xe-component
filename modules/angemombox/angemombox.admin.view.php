<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angemomboxAdminView
 * @author singleview(root@singleview.co.kr)
 * @brief  angemomboxAdminView
**/ 
class angemomboxAdminView extends angemombox 
{
/**
 * @brief initialization
 **/
	public function init()
	{
		// Pre-check if module_srl exists. Set module_info if exists
		$module_srl = Context::get('module_srl');
		// Create module model object
		$oModuleModel = getModel('module');
		// module_srl two come over to save the module, putting the information in advance
		if($module_srl)
		{
			$module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl);
			if(!$module_info)
			{
				Context::set('module_srl','');
				$this->act = 'list';
			}
			else
			{
				ModuleModel::syncModuleToSite($module_info);
				$this->module_info = $module_info;
				Context::set('module_info',$module_info);
			}
		}
		// Get a list of module categories
		$module_category = $oModuleModel->getModuleCategories();
		Context::set('module_category', $module_category);
		//Security
		$security = new Security();
		$security->encodeHTML('module_category..title');

		// Get a template path (page in the administrative template tpl putting together)
		$this->setTemplatePath($this->module_path.'tpl');

	}
/**
 * @brief display applicants list per each doc
 **/
	public function dispAngemomboxAdminApplicantsList() 
	{
		$oArgs = new stdClass();
		$oArgs->module_srl = Context::get('module_srl');
		$oArgs->page = Context::get('page');

		$aMemberSearchTarget = ['user_name', 'user_id', 'mobile'];
		$search_target = Context::get('search_target');
		$search_keyword = Context::get('search_keyword');
		if(in_array($search_target,$aMemberSearchTarget) && $search_keyword) 
		{
			Context::set('search_target', $search_target);
			Context::set('search_keyword', $search_keyword);
			$oMemberAdminModel = getAdminModel('member');
			$oRst = $oMemberAdminModel->getMemberList();
			$aMemberSrl = [];
			foreach($oRst->data as $oSingleMember)
				$aMemberSrl[] = $oSingleMember->member_srl;
			unset($oRst);
			if(!count($aMemberSrl))
				$aMemberSrl[] = -1;  // search never exists
			$oArgs->member_srls = $aMemberSrl;
			$oRst = executeQueryArray('angemombox.getAdminDocByModuleMemberSrls', $oArgs);
			unset($oArgs);
		}
		else
		{
			$oArgs->{$search_target} = $search_keyword;
			$oRst = executeQueryArray('angemombox.getAdminDocByModule', $oArgs);
			unset($oArgs);
		}
		
		$oMemberModel = &getModel('member');
		foreach($oRst->data as $key => $val)
		{
			$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($val->member_srl, 0);
			$oRst->data[$key]->member_srl = $oMemberInfo->member_srl;
			$oRst->data[$key]->user_id = $oMemberInfo->user_id;
			$oRst->data[$key]->user_name = $oMemberInfo->user_name;
			$oRst->data[$key]->mobile = $oMemberInfo->mobile;
		}
		unset($aColumnList);

		Context::set('angemombox_list', $oRst->data );
		Context::set('total_count', $oRst->total_count);
		Context::set('total_page', $oRst->total_page);
		Context::set('page', $oRst->page);
		Context::set('page_navigation', $oRst->page_navigation);
		$this->setTemplateFile('applicant_list');
	}
/**
 * @brief Delete angemombox output
 */
	public function dispAngemomboxAdminDelete()
	{
		$module_srl = Context::get('module_srl');
		if(!$module_srl) 
			return $this->dispContent();

		$oModuleModel = getModel('module');
		$columnList = array('module_srl', 'module', 'mid');
		$module_info = $oModuleModel->getModuleInfoByModuleSrl($module_srl, $columnList);
		Context::set('module_info',$module_info);
		// Set a template file
		$this->setTemplateFile('angemombox_delete');

		$security = new Security();
		$security->encodeHTML('module_info.');
	}
/**
 * @brief display applicants doc detail
 **/
	public function dispAngemomboxAdminDocDetail() 
	{
		$nModuleSrl = Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');

		$nDocSrl = Context::get('doc_srl');
		if(!$nDocSrl) 
			return new BaseObject(-1, 'msg_invalid_doc_srl');

		$oArgs = new stdClass();
		$oArgs->module_srl = $nModuleSrl;
		$oArgs->doc_srl = $nDocSrl;
		$oRst = executeQuery('angemombox.getAdminDocDetail', $oArgs);
		unset($oArgs);

		$oMemberModel = &getModel('member');
		$oMemberInfo = $oMemberModel->getMemberInfoByMemberSrl($oRst->data->member_srl, 0);

		$oMemberInfo->user_id = $oMemberInfo->user_id ? $oMemberInfo->user_id : '탈퇴회원';
		$oMemberInfo->user_name = $oMemberInfo->user_name ? $oMemberInfo->user_name : '탈퇴회원';
		$oMemberInfo->mobile = $oMemberInfo->mobile ? $oMemberInfo->mobile : '탈퇴회원';

		Context::set('oMemberInfo', $oMemberInfo);
		unset($oMemberInfo);
		
		$oFileModel = getModel('file');
		$aFile = $oFileModel->getFiles($oRst->data->upload_target_srl);
		unset($oFileModel);

		if(count((array)$aFile))
			$oRst->data->file_srl = $aFile[0]->file_srl;
		
		$oRst->data->privacy_collection = $oRst->data->privacy_collection ? 'agree' : 'disagree';
		$oRst->data->privacy_sharing = $oRst->data->privacy_sharing ? 'agree' : 'disagree';
		Context::set('angemombox_detail', $oRst->data);
		
		$this->setTemplateFile('applicant_detail');
	}
/**
 * @brief display angemombox list
 **/
	public function dispAngemomboxAdminIndex() 
	{
		$oArgs = new stdClass();
		$oArgs->sort_index = "module_srl";
		$oArgs->page = Context::get('page');
		$oArgs->list_count = 40;
		$oArgs->page_count = 10;
		$oArgs->s_module_category_srl = Context::get('module_category_srl');

		$search_target_list = array('s_mid','s_browser_title');
		$search_target = Context::get('search_target');
		$search_keyword = Context::get('search_keyword');
		if(in_array($search_target,$search_target_list) && $search_keyword) $oArgs->{$search_target} = $search_keyword;
		$output = executeQuery('angemombox.getAdminModuleList', $oArgs);
		unset($oArgs);

		$oModuleModel = getModel('module');
		$page_list = $oModuleModel->addModuleExtraVars($output->data);
		moduleModel::syncModuleToSite($page_list);

		$oModuleAdminModel = getAdminModel('module'); /* @var $oModuleAdminModel moduleAdminModel */

		$tabChoice = array('tab1'=>1, 'tab3'=>1);
		$selected_manage_content = $oModuleAdminModel->getSelectedManageHTML($this->xml_info->grant, $tabChoice, $this->module_path);
		Context::set('selected_manage_content', $selected_manage_content);

		// To write to a template context:: set
		Context::set('total_count', $output->total_count);
		Context::set('total_page', $output->total_page);
		Context::set('page', $output->page);
		Context::set('page_list', $output->data);
		Context::set('page_navigation', $output->page_navigation);

		// Set a template file
		$this->setTemplateFile('index');
	}
/**
* @brief display the selected promotion admin information
**/
	public function dispAngemomboxAdminInsertModInst() 
	{
		$this->dispAngemomboxAdminInfo();
	}
/**
 * @brief Information output of the selected page
 */
	public function dispAngemomboxAdminInfo()
	{
		$nModuleSrl = Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');

		$oAngemomboxAdminModel = &getAdminModel('angemombox');
		$module_info = $oAngemomboxAdminModel->getModuleConfig($nModuleSrl);
		
		// If the layout is destined to add layout information haejum (layout_title, layout)
		if($module_info->layout_srl)
		{
			$oLayoutModel = getModel('layout');
			$layout_info = $oLayoutModel->getLayout($module_info->layout_srl);
			$module_info->layout = $layout_info->layout;
			$module_info->layout_title = $layout_info->layout_title;
		}
		// Get a layout list
		$oLayoutModel = getModel('layout');
		$layout_list = $oLayoutModel->getLayoutList();
		Context::set('layout_list', $layout_list);

		$mobile_layout_list = $oLayoutModel->getLayoutList(0,"M");
		Context::set('mlayout_list', $mobile_layout_list);

		// Set a template file
		$oModuleModel = getModel('module');
		$skin_list = $oModuleModel->getSkins($this->module_path);
		Context::set('skin_list',$skin_list);

		$mskin_list = $oModuleModel->getSkins($this->module_path, "m.skins");
		Context::set('mskin_list', $mskin_list);

		//Security
		$security = new Security();
		$security->encodeHTML('layout_list..layout');
		$security->encodeHTML('layout_list..title');
		$security->encodeHTML('mlayout_list..layout');
		$security->encodeHTML('mlayout_list..title');
		$security->encodeHTML('module_info.');
		Context::set('module_info', $module_info);
		$this->setTemplateFile('angemombox_info');
	}
/**
 * @brief Information output of the selected page
 */
	public function dispAngemomboxAdminWinnerList()
	{
		$nModuleSrl = Context::get('module_srl');
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');
			
		$oAngemomboxAdminModel = &getAdminModel('angemombox');
		$aYrMoRange = $oAngemomboxAdminModel->getYrMoRangeByModuleSrl($nModuleSrl);
		Context::set('aYrMoRange', $aYrMoRange);
		$sYrMo = Context::get('winner_yr_mo');
		if($sYrMo)
		{
			$oRst = $oAngemomboxAdminModel->getWinnerInfoByModuleSrl($nModuleSrl, $sYrMo);
			Context::set('nWinner', $oRst->get('nWinner'));
			Context::set('nDropout', $oRst->get('nDropout'));
			Context::set('nTotal', $oRst->get('nWinner') + $oRst->get('nDropout'));
			//var_dump($oRst);
		}
		unset($oAngemomboxAdminModel);
		$this->setTemplateFile('winner_list');
	}
/**
* @brief display the selected promotion admin information
**/
	public function dispAngemomboxAdminTermsPrivacyUsage() 
	{
		$nModuleSrl = (int)$this->module_info->module_srl;
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');

		$oAngemomboxAdminModel = &getAdminModel('angemombox');
		Context::set('privacy_usage_term', $oAngemomboxAdminModel->getPrivacyTerm($nModuleSrl, 'privacy_usage_term'));
		Context::set('mid', $this->module_info->mid);

		// get a privacy_usage_term editor
		$option = new stdClass();
		$option->primary_key_name = 'temp_srl';
		$option->content_key_name = 'privacy_usage_term';
		$option->allow_fileupload = false;
		$option->enable_autosave = false;
		$option->enable_default_component = true;
		$option->enable_component = true;
		$option->resizable = true;
		$option->height = 200;
		$oEditorModel = getModel('editor');
		$editor = $oEditorModel->getEditor(0, $option);
		Context::set('editor', $editor);
		$this->setTemplateFile('terms_privacy_usage');
	}
/**
* @brief display the selected promotion admin information
**/
	public function dispAngemomboxAdminTermsPrivacyShr() 
	{
		$nModuleSrl = (int)$this->module_info->module_srl;
		if(!$nModuleSrl) 
			return new BaseObject(-1, 'msg_invalid_module_srl');

		$oAngemomboxAdminModel = &getAdminModel('angemombox');
		Context::set('privacy_shr_term', $oAngemomboxAdminModel->getPrivacyTerm($nModuleSrl, 'privacy_shr_term'));
		Context::set('mid', $this->module_info->mid);

		// get a privacy_usage_term editor
		$option = new stdClass();
		$option->primary_key_name = 'temp_srl';
		$option->content_key_name = 'privacy_shr_term';
		$option->allow_fileupload = false;
		$option->enable_autosave = false;
		$option->enable_default_component = true;
		$option->enable_component = true;
		$option->resizable = true;
		$option->height = 200;
		$oEditorModel = getModel('editor');
		$editor = $oEditorModel->getEditor(0, $option);
		Context::set('editor', $editor);
		$this->setTemplateFile('terms_privacy_shr');
	}
/**
 * Display skin setting page
 */
	public function dispAngemomboxAdminSkinInfo()
	{
		$oModuleAdminModel = getAdminModel('module');
		$skin_content = $oModuleAdminModel->getModuleSkinHTML($this->module_info->module_srl);
		Context::set('skin_content', $skin_content);
		$this->setTemplateFile('skin_info');
	}
/**
 * Display mobile skin setting page
 */
	public function dispAngemomboxAdminMobileSkinInfo()
	{
		$oModuleAdminModel = getAdminModel('module');
		$skin_content = $oModuleAdminModel->getModuleMobileSkinHTML($this->module_info->module_srl);
		Context::set('skin_content', $skin_content);

		$this->setTemplateFile('skin_info');
	}
/**
 * @brief display the grant information
 **/
	public function dispAngemomboxAdminGrantInfo() 
	{
		// get the grant infotmation from admin module
		$oModuleAdminModel = getAdminModel('module');
		$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('grant_content', $grant_content);
		$this->setTemplateFile('grant_list');
	}
/**
 * @brief dispAngemomboxAdminDocDetail 의 tpl/applicant_detail.html 호출하는 메쏘드
 */	
	public static function dispImgUrl($nThumbFileSrl, $nWidth = 80, $nHeight = 0, $sThumbnailType = 'crop')
	{
		$sNoimgUrl = Context::getRequestUri().'/modules/angemombox/tpl/img/no_img_80x80.jpg';
		if(!$nThumbFileSrl) // 기본 이미지 반환
			return $sNoimgUrl;
		if(!$nHeight)
			$nHeight = $nWidth;

		// Define thumbnail information
		$sThumbnailPath = 'files/cache/thumbnails/'.getNumberingPath($nThumbFileSrl, 3);
		$sThumbnailFile = $sThumbnailPath.$nWidth.'x'.$nHeight.'.'.$sThumbnailType.'.jpg';
		$sThumbnailUrl = Context::getRequestUri().$sThumbnailFile; //http://127.0.0.1/files/cache/thumbnails/840/80x80.crop.jpg"
		// Return false if thumbnail file exists and its size is 0. Otherwise, return its path
		if(file_exists($sThumbnailFile) && filesize($sThumbnailFile) > 1) 
			return $sThumbnailUrl;
		// Target File
		$oFileModel = &getModel('file');
		$sSourceFile = NULL;
		$oFile = $oFileModel->getFile($nThumbFileSrl);
		if($oFile) 
			$sSourceFile = $oFile->uploaded_filename;

		if($sSourceFile)
			$oOutput = FileHandler::createImageFile($sSourceFile, $sThumbnailFile, $nWidth, $nHeight, 'jpg', $sThumbnailType);

		// Return its path if a thumbnail is successfully genetated
		if($oOutput) 
			return $sThumbnailUrl;
		else
			FileHandler::writeFile($sThumbnailFile, '','w'); // Create an empty file not to re-generate the thumbnail
		return $sThumbnailFile;
	}
}