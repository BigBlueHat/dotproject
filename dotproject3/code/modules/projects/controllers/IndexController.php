<?php

/**
 * IndexController
 * 
 * @package dotproject
 * @subpackage projects
 * @version 3.0 alpha
 */


class Projects_IndexController extends Zend_Controller_Action {

	public function indexAction() {

		$index = new Projects_Index();
		
		$status = DP_Config::getSysVal( 'ProjectStatus' );
		$status_keys = array_keys($status);
		
		$list_view_id = 'dp-projects-list-view';
		$projects_list_view = DP_View_Factory::getListView($list_view_id);
		//$projects_list_view->setUrlPrefix($this_url);
		
		// Create selection tools
		$select_tools = new DP_View_ObjectSelectTools('dp-projects-selection', $list_view_id, 'project_id');
		

		
		$project_search_view = DP_View_Factory::getSearchFilterView('dp-projects-list-searchfilter');
		$project_search_view->setSearchFieldTitle('Project name'); // TODO - better detection of available search fields through interface.
		$project_search_view->setSearchField('project_name');
		
		$projects_list_pager = DP_View_Factory::getPagerView('dp-projects-list-pager');
		$projects_list_pager->setItemsPerPage(30);
		$projects_list_pager->setPersistent(false);
		$projects_list_pager->align = 'center';
		
		$new_btn = new DP_View_Button('dp-projects-new','new-project');
		$new_btn->button->setLabel('+ New Project');
		$new_btn->button->onClick = "location = '/projects/edit/new'";

		// Add buttons to horizontal box
		$btn_hbox = new DP_View_Hbox('dp-projects-hbox1');
		$btn_hbox->add($new_btn);
		$btn_hbox->add($select_tools);
				
		$projects_list_view->width = '100%';
		$projects_list_view->add($btn_hbox, DP_View::APPEND);
		$projects_list_view->add($project_search_view, DP_View::PREPEND);
		$projects_list_view->add($projects_list_pager, DP_View::APPEND);
		
		$projects_list_view->row_iterator->addRow(
								Array(
									  $select_tools->makeSelectCellView(),
									  new DP_View_Cell_Progress('project_percent_complete','Progress'),
									  new DP_View_Cell_ObjectLink('project_id','project_name', '/projects/view/object/id/','Project Name'),
									  new DP_View_Cell_ObjectLink('company_id','company_name', '/companies/view/object/id/','Company Name'),
									  new DP_View_Cell_Date('project_start_date','Start'),
									  new DP_View_Cell_Date('project_end_date','End'),
									  new DP_View_Cell_Date('project_actual_end_date','Actual'),
									  new DP_View_Cell_ArrayItem(DP_Config::getSysVal('ProjectPriority'),'project_priority','Priority'),
									  new DP_View_Cell('user_username','Owner'),
									  new DP_View_Cell_ArrayItem(DP_Config::getSysVal('ProjectStatus'),'project_status','Status')
											)
												);
		
		$projects_tab_view = DP_View_Factory::getTabBoxView('projects_list_tabbox');
		$projects_tab_view->add($projects_list_view, 'All Projects');
		
		
		
		foreach ($status as $project_status) {
			$projects_tab_view->add($projects_list_view, $project_status);
		}
		
		$projects_list_view->setDataSource($index);

		$index->addModifier($projects_list_view->getSort());
		$index->addModifier($project_search_view->getFilter());
		$index->addModifier($projects_list_pager->getPager());
		
		$projects_tab_view->updateStateFromServer($this->getRequest());
		
		
		$projects_tab_filter = new DP_Filter('dp-projects-tab');
		if ($projects_tab_view->selectedTab() > 0) {
			// Add project_status filter. Deduct 1 due to the All tab
			$projects_tab_filter->fieldEquals('project_status', $status_keys[$projects_tab_view->selectedTab() - 1]);
		}
		
		$index->addModifier($projects_tab_filter);
		
		$this->view->main = $projects_tab_view;
	}

}

?>
