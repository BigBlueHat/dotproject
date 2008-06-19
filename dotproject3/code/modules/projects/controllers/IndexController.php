<?php

/**
 * IndexController
 * 
 * @package dotproject
 * @subpackage projects
 * @version 3.0 alpha
 */


class Projects_IndexController extends DP_Controller_Action {

	public function indexAction() {

		$index = new Projects_Index();
		
		$list_view_id = 'dp-projects-list-view';
		$projects_list_view = DP_View_Factory::getListView($list_view_id);
		//$projects_list_view->setUrlPrefix($this_url);
		
		// Create selection tools
		$select_tools = new DP_View_ObjectSelectTools('dp-projects-selection', $list_view_id, 'project_id');
		
		// Add buttons to horizontal box
		$btn_hbox = new DP_View_Hbox('dp-projects-hbox1');
		//$btn_hbox->add($new_btn);
		$btn_hbox->add($select_tools);
		
		$projects_list_view->add($btn_hbox, DP_View::APPEND);
		
		$projects_list_view->row_iterator->addRow(
								Array(
									  $select_tools->makeSelectCellView(),
									  new DP_View_Cell_ObjectLink('c.company_id','c.company_name', '/companies/view/object/id/'),
									  new DP_View_Cell_ObjectLink('project_id','project_name', '/projects/view/object/id/')
											)
												);
		
		$projects_tab_view = DP_View_Factory::getTabBoxView('projects_list_tabbox');
		$projects_tab_view->add($projects_list_view, 'All Projects');
		
		$projects_list_view->setDataSource($index);
		$projects_list_view->setColumnHeaders(Array('project_id'=>'X',
													 'c.company_name'=>'Company Name',
													 'project_name'=>'Project Name'));
		
		$this->view->main = $projects_tab_view;
	}

}

?>
