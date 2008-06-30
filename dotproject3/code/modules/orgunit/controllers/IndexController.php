<?php

require_once ('Zend/Controller/Action.php');

class Orgunit_IndexController extends Zend_Controller_Action {

	public function indexAction() {		
			
		$index = new Orgunit_Index();
		$list_view_id = 'dp-ou-index';
		$this_url = $this->_helper->Url('index','index','orgunit');
		
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/orgunit', 'Organizational Units');	
		$this->view->heading = "Organizational Units"; // @todo translate		
		
		$searchview = DP_View_Factory::getSearchFilterView('dp-ou-list-searchfilter');
		$searchview->setSearchFieldTitle('Display name'); // TODO - better detection of available search fields through interface.
		$searchview->setSearchField('displayname');

		$pager = DP_View_Factory::getPagerView('dp-ou-list-pager');
		$pager->setItemsPerPage(40);
		$pager->setUrlPrefix($this_url);
		$pager->setPersistent(false);
		$pager->align = 'center';
		
		$new_btn = new DP_View_Button('dp-ou-new','new-ou');
		$new_btn->button->setLabel('+ New Organizational Unit');
		$new_btn->button->onClick = "location = '/orgunit/edit/new'";

		$select_tools = new DP_View_ObjectSelectTools('dp-ou-selection', $list_view_id, 'id');
		
		// Add buttons to horizontal box
		$btn_hbox = new DP_View_Hbox('dp-ou-hbox');
		$btn_hbox->add($new_btn);
		$btn_hbox->add($select_tools);
		
		$ou_list_view = DP_View_Factory::getListView($list_view_id);
		$ou_list_view->width = '100%';
		
		$ou_list_view->row_iterator->addRow(
								Array(
									  $select_tools->makeSelectCellView(),
									  new DP_View_Cell_ObjectLink('id','name','/orgunit/view/object/id/','Organizational Unit')
									  )
									 );
		// Add utilities
		
		$ou_list_view->add($searchview, DP_View::PREPEND);
		$ou_list_view->add($btn_hbox, DP_View::APPEND);
		$ou_list_view->add($pager, DP_View::APPEND);
		
		// Add modifiers
		
		$index->addModifier($searchview->getFilter());
		$index->addModifier($pager->getPager());
		
		
		$ou_list_view->setDataSource($index);
		// Update state from request vars
		$ou_list_view->updateStateFromServer($this->getRequest());
		
		$db = DP_Config::getDB();
		Zend_Db_Table::setDefaultAdapter($db);
		
		if ($select_tools->objectsChanged()) {
			$select_tools->updateObjects(new Db_Table_Orgunits());
		}
		
		$index->clientWillRender();
		
		// Assign contacts view
		$this->view->main = $ou_list_view;
		//$this->view->titleblock = $title_block;	
	
	}
}
?>