<?php
if (! defined('DP_BASE_DIR')) {
	die('You have not configured your installation correctly.  You must have an auto prepend option set to include the initialisation code');
}
// Get AppUI and set up preferences/uistyle/localisation
DP_AppUI::getInstance()->init();

$dP = Zend_Controller_Front::getInstance();
$dP->addModuleDirectory(DP_BASE_CODE . '/modules');
$dP->addControllerDirectory(DP_BASE_CODE . '/modules/default/controllers');

Zend_Layout::startMvc(DP_BASE_CODE . '/style/default/layouts');

Zend_Controller_Action_HelperBroker::addPrefix('DP_Controller_Action_Helper');
Zend_Controller_Action_HelperBroker::getStaticHelper('ModelIncluder');
Zend_Controller_Action_HelperBroker::getStaticHelper('LoginRedirector');
Zend_Controller_Action_HelperBroker::addHelper(new DP_Controller_Action_Helper_ViewRenderer());
$dP->dispatch();
?>
