<?php
/**
 * Convenience Helper for retrieving form definitions.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @author ebrosnan
 */
class DP_Controller_Action_Helper_FormDefinition extends Zend_Controller_Action_Helper_Abstract {
	
	/**
	 * Direct method to get a form definition by name
	 * 
	 * The helper assumes that the .ini definitions reside in a subdirectory called
	 * forms underneath the views directory.
	 * 
	 * @param String $name Name of the .ini file to get, without extension. Defaults to controller-action.ini
	 * @param String $section Section of the configuration file to retrieve. Defaults to entire file.
	 * @return Zend_Config_Ini Configuration object
	 */
	public function direct($name = null, $section = null) {
		
		$controller_dir = $this->getFrontController()->getControllerDirectory($this->getRequest()->getModuleName());
		$module_dir = dirname($controller_dir);
		$controller_name = $this->getRequest()->getControllerName();
		$action_name = $this->getRequest()->getActionName();

		if ($name == null) {
			$name = $controller_name.'-'.$action_name;
		}
		
		$definition_file = $module_dir.'/views/forms/'.$name.'.ini';
		
		if (file_exists($definition_file)) {
			$config = new Zend_Config_Ini($definition_file, $section);
			return $config;
		} else {
			throw new Exception('Could not find the specified form definition: '.$definition_file);
		}
	}
}
?>