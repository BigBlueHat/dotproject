<?php
require_once 'DP/Controller/Action.php';

/**
 * This is the file containing the definition of the index controller for the default module
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 * @subpackage default
 */

/**
 * Handles the default action for the default module
 * @package dotproject
 * @subpackage default
 */
class IndexController extends DP_Controller_Action
{
	public function indexAction()
	{
		
	}
	
	public function usernavAction() {
		$AppUI = DP_AppUI::getInstance();
		$this->view->user_name = $AppUI->user_first_name.' '.$AppUI->user_last_name;
	}
	
	public function modulenavigationAction()
	{
		$AppUI = DP_AppUI::getInstance();
		// top navigation menu
		if ($AppUI->user_id > 0) {
			$nav = $AppUI->getMenuModules();
			$perms =& $AppUI->acl();
			$links = array();
			
			foreach ($nav as $module) {
				if ($perms->checkModule($module['mod_directory'], 'access')) {
					$links[] = $module; //'<a href="?m='.$module['mod_directory'].'">'.$AppUI->_($module['mod_ui_name']).'</a>';
				}
			}
			$this->view->modules = $links;
			$fc = Zend_Controller_Front::getInstance();
			$req = $fc->getRequest();
			$this->view->module_selected = $req->getModuleName();
		}
		
		
		$modules_newitem = Array('companies'=>'Company',
								'contacts'=>'Contact',
								'calendar'=>'Event',
								'files'=>'File',
								'projects'=>'Project'					
		);
		$newitem_form = new Zend_Form();
		$newitem_select = new Zend_Form_Element_Select('new_item', Array('class'=>'text'));
		$newItem = Array('' => '- New Item -' );
		
		foreach($modules_newitem as $m => $txt) {
			if ($perms->checkModule( $m, 'add' )) 
				$newItem[$m] = $txt;			
		}
		$newitem_select->addMultiOptions($newItem);
		$newitem_form->addElement($newitem_select);
		
		$this->view->new_item = $newitem_form;
	}
	
	// TODO - find a better place for this code
	public function preparelayoutAction() {
		$this->_helper->viewRenderer->setNoRender(true);
		
		$adapter = new Zend_Translate('array', array('simple' => 'einfach', 'forgotPassword'=>'Help'), 'de');
		Zend_Registry::set('Zend_Translate', $adapter);
		
		$AppUI = DP_AppUI::getInstance();
		$layout = $this->_helper->layout();

		if (isset($AppUI)) {
			$layout->version = $AppUI->getVersion();
			$layout->user_id = $AppUI->user_id;
			$layout->user_name = $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
		}

		$layout->baseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
		$layout->baseDir = DP_BASE_DIR;

		$perms = $AppUI->acl();
		
		$layout->page_title = $page_title;
		$layout->charset = isset( $locale_char_set ) ? $locale_char_set : 'UTF-8';
		$layout->version = $AppUI->getVersion();

		$layout->access_calendar = $perms->checkModule('calendar', 'access');
		$layout->access_links = $perms->checkModule('links', 'access');
		$layout->msg = $AppUI->getMsg();		
		
		$layout->js = $AppUI->loadJS();
		$layout->uistyle = $AppUI->getPref('UISTYLE');
		$layout->style_extras = $style_extras;		
		
		// top navigation menu
		if ($AppUI->user_id > 0) {
			$nav = $AppUI->getMenuModules();
			$perms =& $AppUI->acl();
			$links = array();
			
			foreach ($nav as $module) {
				if ($perms->checkModule($module['mod_directory'], 'access')) {
					$links[] = $module; //'<a href="?m='.$module['mod_directory'].'">'.$AppUI->_($module['mod_ui_name']).'</a>';
				}
			}
			$layout->modules = $links;
		}
		
		$modules_newitem = Array('companies'=>'Company',
								'contacts'=>'Contact',
								'calendar'=>'Event',
								'files'=>'File',
								'projects'=>'Project'					
		);
		$newitem_form = new Zend_Form();
		$newitem_select = new Zend_Form_Element_Select('new_item', Array('class'=>'text'));
		$newItem = Array('' => '- New Item -' );
		
		foreach($modules_newitem as $m => $txt) {
			if ($perms->checkModule( $m, 'add' )) 
				$newItem[$m] = $txt;			
		}
		$newitem_select->addMultiOptions($newItem);
		$newitem_form->addElement($newitem_select);
		
		$layout->new_item = $newitem_form;
		
		
	}

}

