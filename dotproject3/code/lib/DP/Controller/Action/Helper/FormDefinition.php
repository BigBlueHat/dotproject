<?php
/**
 * Convenience Helper for retrieving form definitions.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_Controller_Action_Helper_FormDefinition extends Zend_Controller_Action_Helper_Abstract {
	
	/**
	 * Direct method to get a form definition by name
	 * 
	 * The helper assumes that the .ini definitions reside in a subdirectory called
	 * forms underneath the views directory.
	 * 
	 * @param String $name Name of the .ini file to get, without extension
	 * @return Zend_Config_Ini Configuration object
	 */
	public function direct($name, $section = null) {
		$request = $this->getRequest();
		$module = $request->getModuleName();
		$moduleDir = $this->getFrontController()->getControllerDirectory($module);
		
		$definition_file = dirname($moduleDir).'/views/forms/'.$name.'.ini';
		// TODO - could use an inflector to automatically grab the right .ini for the action.
		
		$config = new Zend_Config_Ini($definition_file, $section);
		return $config;
	}
}
?>