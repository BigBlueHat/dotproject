<?php
/**
 * Adds model subdirectory for inclusion.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Controller_Action_Helper_ModelIncluder extends Zend_Controller_Action_Helper_Abstract {	

	public function preDispatch() {
		
		$request = $this->getRequest();
		$module = $request->getModuleName();
		$moduleDir = $this->getFrontController()->getControllerDirectory($module);
		
		$model_dir = dirname($moduleDir).'/models';
		set_include_path(get_include_path() . PATH_SEPARATOR . $model_dir);
	}
}
?>