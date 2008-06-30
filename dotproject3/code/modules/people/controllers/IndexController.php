<?php

/**
 * IndexController
 * 
 * @author ebrosnan
 * @version 3.0 alpha
 */

require_once 'Zend/Controller/Action.php';

class People_IndexController extends Zend_Controller_Action {
	/**
	 * The default action - show the home page
	 */
	public function indexAction() {		
		$people_index = new People_Index();
		$list_view_id = 'dp-people-index';
		$this_url = $this->_helper->Url('index','index','people');
		
		$title_block = $this->_helper->TitleBlock('');
		$title_block->addCrumb('/people', 'People');	
		$this->view->heading = "People"; // @todo translate		
		
		$people_search_view = DP_View_Factory::getSearchFilterView('dp-people-list-searchfilter');
		$people_search_view->setSearchFieldTitle('Display name'); // TODO - better detection of available search fields through interface.
		$people_search_view->setSearchField('displayname');

		$pager = DP_View_Factory::getPagerView('dp-people-list-pager');
		$pager->setItemsPerPage(40);
		$pager->setUrlPrefix($this_url);
		$pager->setPersistent(false);
		$pager->align = 'center';
		
		$new_btn = new DP_View_Button('dp-people-new','new-person');
		$new_btn->button->setLabel('+ New Person');
		$new_btn->button->onClick = "location = '/people/edit/new'";

		$select_tools = new DP_View_ObjectSelectTools('dp-people-selection', $list_view_id, 'id');
		
		// Add buttons to horizontal box
		$btn_hbox = new DP_View_Hbox('dp-people-hbox');
		$btn_hbox->add($new_btn);
		$btn_hbox->add($select_tools);
		
		// rolodex view
		$col_view = DP_View_Factory::getColumnView($list_view_id);

		// Add utilities
		
		$col_view->add($people_search_view, DP_View::PREPEND);
		$col_view->add($btn_hbox, DP_View::APPEND);
		$col_view->add($pager, DP_View::APPEND);
		
		$card_iterator = new DP_View_Iterator();
		
		$card_view = new People_View_Card('dp-people-view-card');
		$card_view->setDisplayKeys(Array('uid', 'mail','sn'));
		$card_view->setPrimaryDisplayKey('displayname');
		$card_view->setIdKey('id');
		
		$card_iterator->add($card_view);
		$card_iterator->setDataSource($people_index);
		
		$col_view->setIterator($card_iterator);
		
		// Add modifiers
		
		$people_index->addModifier($people_search_view->getFilter());
		$people_index->addModifier($pager->getPager());
		
		// Update state from request vars
		$col_view->updateStateFromServer($this->getRequest());
		
		$people_index->clientWillRender();
		
		// Assign contacts view
		$this->view->main = $col_view;
		$this->view->titleblock = $title_block;	
	
	}
}
?>