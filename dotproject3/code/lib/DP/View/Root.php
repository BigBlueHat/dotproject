<?php
/**
 * Root container for DP_View objects. Is used by viewRenderer helper to render the view.
 * 
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 */
class DP_View_Root implements Zend_View_Interface {
	protected $view_objects;
	
	
	public function __construct() {
		$this->view_objects = Array();
	}
	
	/**
	 * Add a view object to the root view
	 */
	public function add(DP_View $view) {
		$this->view_objects[] = $view;
	}
	
	
	// From Zend_View_Interface
	public function __call($name, $args) {
		
	}
	
	
	/**
	 * @see Zend_View_Interface::__isset()
	 */
	public function __isset($key) {
		return isset($this->view_objects[$key]);
	}

	/**
	 * @see Zend_View_Interface::__set()
	 */
	public function __set($key, $val) {
		$this->view_objects[$key] = $val;
	}
	
	public function __unset($key) {
		unset($this->view_objects[$key]);
	}
	
	public function addBasePath($path, $classPrefix = 'Zend_View') {
		
	}
	
	public function assign($spec, $value = null) {
		if (is_array($spec)) {
			foreach ($spec as $k => $v) {
				$this->$k = $v;
			}
		} else {
			$this->$spec = $value;
		}
	}
	
	public function clearVars() {
		$this->view_objects = Array();
	}
	
	public function getEngine() {

	}
	
	public function getScriptPaths() {
		
	}
	
	public function render($name) {
		$output = "";
		
		foreach ($this->view_objects as $obj) {
			if (!is_object($obj)) {
				print_r($obj);
			} else {
				$rendered_view = $obj->render();
				$template_id = $obj->id();
				$output .= $rendered_view;
			}
		}
		return $output;	
	}
	
	public function setBasePath($path, $classPrefix = 'Zend_View') {
		
	}
	
	public function setScriptPath($path) {
		
	}
	
	

}
?>