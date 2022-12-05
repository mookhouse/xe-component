<?php
/**
 * vi:set sw=4 ts=4 noexpandtab fileencoding=utf-8:
 * @class  angeclubAdminView
 * @author singleview(root@singleview.co.kr)
 * @brief  angeclubAdminView
**/ 
class angeclubAdminView extends angeclub 
{
/**
 * @brief initialization
 **/
	public function init()
	{
        if(is_null(getClass('angemombox')))  // check module dependency
            return $this->stop("msg_error_angemombox_module_required");
            
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
 * @brief Delete angeclub output
 */
	public function dispAngeclubAdminDelete()
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
 * @brief display angeclub list
 **/
	public function dispAngeclubAdminIndex() 
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
		$output = executeQuery('angeclub.getAdminModuleList', $oArgs);
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
	public function dispAngeclubAdminInsertMod() 
	{
		$this->dispAngeclubAdminModDetail();
	}
/**
 * @brief Information output of the selected page
 */
	public function dispAngeclubAdminModDetail()
	{
		$nModuleSrl = Context::get('module_srl');

		$oAngeclubAdminModel = &getAdminModel('angeclub');
		$module_info = $oAngeclubAdminModel->getModuleConfig($nModuleSrl);
		
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
		$this->setTemplateFile('angeclub_info');
	}
/**
 * @brief display the grant information
 **/
	public function dispAngeclubAdminGrantInfo() 
	{
		// get the grant infotmation from admin module
		$oModuleAdminModel = getAdminModel('module');
		$grant_content = $oModuleAdminModel->getModuleGrantHTML($this->module_info->module_srl, $this->xml_info->grant);
		Context::set('grant_content', $grant_content);
		$this->setTemplateFile('grant_list');
	}
/**
 * @brief admin view [기본설정]
 **/
	public function dispAngeclubAdminConfig() 
	{
		$oAngeclugAdminModel = &getAdminModel('angeclub');
		Context::set('oConfig', $oAngeclugAdminModel->getModuleConfig());
		unset($oAngeclugAdminModel);
		$this->setTemplateFile('config');
	}
}