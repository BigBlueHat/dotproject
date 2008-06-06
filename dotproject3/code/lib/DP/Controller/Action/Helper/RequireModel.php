<?php
/**
 * Convenience Helper for inclusion of model classes.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Controller_Action_Helper_RequireModel extends Zend_Controller_Action_Helper_Abstract {
	
	/**
	 * Direct method to require a model class by name
	 * 
	 * The root directory will be the models subdirectory of the module.
	 * 
	 * @param String $fname (optional)subdirectory and filename of the model to require, without extension
	 * @param String $module The name of the module to include the file from, or null to automatically include a file from the current module.
	 * @return null
	 */
	public function direct($fname, $module = null) {
		$request = $this->getRequest();
		
		if ($module == null) { 
			$module = $request->getModuleName(); 
		}
		
		$moduleDir = $this->getFrontController()->getControllerDirectory($module);
		
		$model_file = dirname($moduleDir).'/models/'.$fname.'.php';
		require_once($model_file);
	}
}
?>