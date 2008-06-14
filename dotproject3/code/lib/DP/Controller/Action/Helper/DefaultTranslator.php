<?php
/**
 * Default translator helper.
 * 
 * Instantiates and sets the default translator with the appropriate language.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @author ebrosnan
 * 
 * @todo Create proper translator instance instead of placeholder.
 */
class DP_Controller_Action_Helper_DefaultTranslator extends Zend_Controller_Action_Helper_Abstract {
	
	public function preDispatch()
	{
		// Placeholder adapter here
		$adapter = new Zend_Translate('array', array('simple' => 'einfach'), 'de');
		Zend_Registry::set('Zend_Translate', $adapter);
	}
}
?>