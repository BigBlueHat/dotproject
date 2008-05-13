<?php
class DP_Controller_Action_Helper extends Zend_Controller_Action_Helper_Abstract {
	
	public function preDispatch()
	{
		// Placeholder adapter here
		$adapter = new Zend_Translate('array', array('simple' => 'einfach'), 'de');
		Zend_Registry::set('Zend_Translate', $adapter);
	}
}
?>