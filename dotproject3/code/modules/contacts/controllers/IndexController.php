<?php
/**
 * This file contains the definition for the index controller of the contacts module
 * 
 * @author ebrosnan
 * @package dotproject
 * @subpackage contacts
 * @version 3.0 alpha
 */
class Contacts_IndexController extends DP_Controller_Action {
	
	public function indexAction() {
		$this->_helper->RequireModel('Index');
		
		$AppUI = DP_AppUI::getInstance();
		$AppUI->savePlace();
		$perms =& $AppUI->acl();
		
		$this_url = $this->_helper->Url('index','index','contacts');
		
		$contacts_index = new Contacts_Index();
		
		$contact_search_view = DP_View_Factory::getSearchFilterView('dp-contacts-list-searchfilter');
		$contact_search_view->setSearchFieldTitle('Contact name'); // TODO - better detection of available search fields through interface.
		
		$contact_list_pager = DP_View_Factory::getPagerView('dp-contacts-list-pager');
		$contact_list_pager->setItemsPerPage(30);
		$contact_list_pager->setUrlPrefix($this_url);
		$contact_list_pager->setPersistent(false);
		$contact_list_pager->align = 'center';

		// TODO - Find a better way of adding new object buttons to lists.
		$new_btn = new DP_View_Button('dp-contacts-new','new-contact');
		$new_btn->button->setLabel('+ New Contact');
		$new_btn->button->onClick = "location = '/contacts/edit/new'";
				
		$contact_cell_view = DP_View_Factory::getCellView('dp-contacts-list');
		
		$contact_cell_view->add($contact_search_view, DP_View::PREPEND);
		$contact_cell_view->add($new_btn, DP_View::APPEND);
		$contact_cell_view->add($contact_list_pager, DP_View::APPEND);
		
		// Create new cell/infocell and tell the cell view to use it.
		$contact_infocell = DP_View_Factory::getInfoCellView('dp-contact-info');
		$contact_infocell->setDisplayKeys(Array('contact_first_name', 'contact_last_name'));
		$contact_cell_view->getViewIterator()->add($contact_infocell);
		// Add modifiers
		$contacts_index->addModifier($contact_search_view->getFilter());
		$contacts_index->addModifier($contact_list_pager->getPager());
		// Company filter
		// First letter filter
		
		// Update state from request vars
		$contact_cell_view->updateStateFromServer($this->getRequest());
		
		// Set up the datasource
		$contact_cell_view->setDataSource($contacts_index);
		
		// Assign contacts view
		$this->view->main = $contact_cell_view;
	}
}
?>