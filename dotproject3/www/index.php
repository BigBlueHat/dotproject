<?php
// first pass, make sure we have the right include path.
$dir = dirname(dirname(__FILE__));
// These defines are for the transition period.
define('DP_BASE_DIR', $dir);
define('DP_BASE_CODE', $dir.'/code');
define('DP_BASE_WWW', $dir.'/www');
set_include_path(get_include_path() . PATH_SEPARATOR . DP_BASE_CODE .'/lib');

//ini_set('display_errors', true);
//error_reporting(E_ALL);

//var_dump($_SESSION);

// Get AppUI and set up preferences/uistyle/localisation
DP_AppUI::getInstance()->init();

$dP = Zend_Controller_Front::getInstance();
$dP->addModuleDirectory(DP_BASE_CODE . '/modules');
$dP->addControllerDirectory(DP_BASE_CODE . '/modules/default/controllers');

Zend_Layout::startMvc(DP_BASE_CODE . '/style/default/layouts');

Zend_Controller_Action_HelperBroker::addPrefix('DP_Controller_Action_Helper');
Zend_Controller_Action_HelperBroker::getStaticHelper('ModelIncluder');
Zend_Controller_Action_HelperBroker::getStaticHelper('LoginRedirector');
$dP->dispatch();
?>
