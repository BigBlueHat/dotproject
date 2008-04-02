<?php
require_once 'DP/Controller/Action.php';

/**
 * This is the file containing the definition of the login controller
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 */

/**
 * The LoginController handles the login page and its acceptance
 * @package dotproject
 * @subpackage default
 */
class LoginController extends DP_Controller_Action
{
	/**
	 * Displays the login page.
	 * 
	 * Stores the URL requested before the login page was displayed, so that the user can be
	 * redirected to their originally requested location after they log in.
	 */
	public function indexAction()
	{
		$view = $this->_helper->viewRenderer->view;
		
		$layout = $this->_helper->layout();
		$layout->dialog = 1;
		
		$AppUI = DP_AppUI::getInstance();
		$view->assign('version', $AppUI->getVersion());

		$view->assign('config',DP_Config::getConfig());
		
		$frm = $this->getLoginForm();
		$view->assign('form', $frm);
	}
	
	/**
	 * Construct and return the login form
	 */
	public function getLoginForm()
	{
		$front = Zend_Controller_Front::getInstance();
		$baseurl = $front->getBaseUrl();
		
		$frm = new Zend_Form();
		$frm->setAction($baseurl.'/login/save/')
			->setMethod('post');
		$frm->setAttrib('name', 'loginform');
		//echo time();{/php}{*dPdateFormat date=$now format='time'*}
		
		$login_time = time();
		$frm_login = new Zend_Form_Element_Hidden(Array('name'=>'alogin', 'value'=>$login_time));
		$frm_lostpass = new Zend_Form_Element_Hidden(Array('name'=>'lostpass', 'value'=>0));
		
		$redirect = ($this->getRequest()->getParam('from')) ? $this->getRequest()->getParam('from') : $this->getRequest()->getParam('redirect');
		$frm_redir = new Zend_Form_Element_Hidden(Array('name'=>'redirect', 'value'=>$redirect));
		
		$frm_username = new Zend_Form_Element_Text(Array('name'=>'username', 'label'=>'Username :' ));
		$frm_password = new Zend_Form_Element_Password(Array('name'=>'password', 'label'=>'Password :' ));
		$frm_submit = new Zend_Form_Element_Submit(Array('name'=>'login', 'value'=>'login'));
		$frm_img = new Zend_Form_Element_Image(Array('name'=>'dotproject', 
													'src'=>$baseurl.'/img/default/dp_icon.gif',
													'alt'=>'dotProject logo'));
		$frm_img->addDecorator(new Zend_Form_Decorator_HtmlTag(array('tag'=>'a', 'href'=>'http://www.dotproject.net/')));
		
		$frm->addElements(Array($frm_login, 
								$frm_lostpass, 
								$frm_redir,
								$frm_submit,
								$frm_img));
								
		$frm->addElement($frm_username, 'username', array(
											'validators'=>array(
												'NotEmpty',
												array('StringLength', null, 255)
											),
											'required'=>true
											));
									
		$frm->addElement($frm_password, 'password', array(
											'validators'=>array(
												'NotEmpty',
												array('StringLength', null, 32)
											),
											'required'=>true
											));
					
		$frm->addDisplayGroup(array('username', 'password'), 'logingrp');
		$frm->addDisplayGroup(array('login','dotproject'), 'submitgrp');
		return $frm;
	}

	/**
	 * Save and process the submitted login details.
	 * 
	 * If the user authenticates successfully then redirect to their originally requested URL,
	 * otherwise display 'failed' message.
	 */
	public function saveAction()
	{
		$req = $this->getRequest();
		$form = $this->getLoginForm();
		if ($form->isValid($_POST) && DP_AppUI::getInstance()->login($this->getRequest()->getParam('username'), $this->getRequest()->getParam('password'))) { 
		//if (DP_AppUI::getInstance()->login($this->getRequest()->getParam('username'), $this->getRequest()->getParam('password'))) {
			$redirect = urldecode($this->getRequest()->getParam('redirect'));
			$this->getResponse()->setRedirect($redirect);
			$this->getResponse()->sendHeaders();
			error_log('Redirecting to '.$redirect);
			exit;
		} else {
			DP_AppUI::getInstance()->setMsg('Login Failed');
			// Display the login page again.
			$this->_forward('index');
		}
	}
}
?>
