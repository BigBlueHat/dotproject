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
		$AppUI = DP_AppUI::getInstance();
		$AppUI->savePlace();
		$perms =& $AppUI->acl();
			
		// Get the full URL without parameters.
		$req = $this->getRequest();
		$this_url = $req->getBaseUrl().'/'.$req->getModuleName().'/'.$req->getControllerName().'/'.$req->getActionName();
		// setup the title block
		$m = $req->getModuleName();
		$a = $req->getActionName();
		
		$titleBlock = DP_View_Factory::getTitleBlockView( 'Contacts', 'monkeychat-48.png', $m, "$m.$a" );
		$titleBlock->addCell( '[alphabetical index]' );
		
		$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('new contact').'">', '',
			'<form action="?m=contacts&a=addedit" method="post">', '</form>'
		);
		
		$titleBlock->addCrumbRight(
			'<a href="./index.php?m=contacts&a=csvexport&suppressHeaders=true">' . $AppUI->_('CSV Download'). "</a> | " .
			'<a href="./index.php?m=contacts&a=vcardimport&dialog=0">' . $AppUI->_('Import vCard') . '</a>'
		);

		$this->view->titleblock = $titleBlock;
		
		
		// Construct the view hierarchy
		$contact_search_view = DP_View_Factory::getSearchFilterView('dp-contacts-list-searchfilter');
	}
}
?>