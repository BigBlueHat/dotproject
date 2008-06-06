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
		$this->_helper->RequireModel('View/Card');
		
		$AppUI = DP_AppUI::getInstance();
		$AppUI->savePlace();
		$perms =& $AppUI->acl();
		
		$this_url = $this->_helper->Url('index','index','contacts');
		
		$contacts_index = new Contacts_Index();
		
		$contact_search_view = DP_View_Factory::getSearchFilterView('dp-contacts-list-searchfilter');
		$contact_search_view->setSearchFieldTitle('Contact name'); // TODO - better detection of available search fields through interface.
		$contact_search_view->setSearchField('contact_order_by');
		
		$contact_list_pager = DP_View_Factory::getPagerView('dp-contacts-list-pager');
		$contact_list_pager->setItemsPerPage(40);
		$contact_list_pager->setUrlPrefix($this_url);
		$contact_list_pager->setPersistent(false);
		$contact_list_pager->align = 'center';

		// TODO - Find a better way of adding new object buttons to lists.
		$new_btn = new DP_View_Button('dp-contacts-new','new-contact');
		$new_btn->button->setLabel('+ New Contact');
		$new_btn->button->onClick = "location = '/contacts/edit/new'";
				
		// Contacts card view
		$contact_col_view = DP_View_Factory::getColumnView('dp-contacts-index');
		// Add utilities
		$contact_col_view->add($contact_search_view, DP_View::PREPEND);
		$contact_col_view->add($new_btn, DP_View::APPEND);
		$contact_col_view->add($contact_list_pager, DP_View::APPEND);
		
		$select_tools = new DP_View_ObjectSelectTools('dp-companies-selection', $contact_col_view->id(), 'contact_id');
		
		$contact_col_view->add($select_tools, DP_View::APPEND);
		// Set up data source
		$contact_iterator = new DP_View_Iterator();
		$contact_card_view = new Contacts_View_Card('dp-contacts-view-card');
		$contact_card_view->setDisplayKeys(Array('contact_phone', 'contact_phone2','contact_email'));
		$contact_iterator->add($contact_card_view);
		$contact_iterator->setDataSource($contacts_index);
		
		$contact_col_view->setIterator($contact_iterator);
				

		// Add modifiers
		$contacts_index->addModifier($contact_search_view->getFilter());
		$contacts_index->addModifier($contact_list_pager->getPager());
		// Company filter
		// First letter filter

			
		// Update state from request vars
		$contact_col_view->updateStateFromServer($this->getRequest());
		
		$contacts_index->clientWillRender();
		
		// Assign contacts view
		$this->view->main = $contact_col_view;
	}
}
?>