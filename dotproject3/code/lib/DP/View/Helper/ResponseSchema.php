<?php
/**
 *
 * @author ebrosnan
 * @version 3.0 alpha
 */
require_once 'Zend/View/Interface.php';

/**
 * ResponseSchema helper
 *
 * @uses viewHelper Zend_View_Helper
 */
class Zend_View_Helper_ResponseSchema {
	
	/**
	 * @var Zend_View_Interface 
	 */
	public $view;
	
	/**
	 * Generate a response schema definition as JS object.
	 * 
	 * For use with YUI DataTable.
	 * 
	 * @param array $schema Array containing schema information. Should be generated using the json presentation object.
	 * @return string JS object notation of response schema without variable.
	 */
	public function responseSchema(Array $schema) {
		$js = '{
				resultsList: "'.$schema['resultsList'].'",
				fields: [';
		foreach ($schema['fields'] as $f) {
			$js .= '{ key: "'.$f.'" }';
			if ($f != $schema['fields'][count($schema['fields']) - 1]) {
				$js .= ', ';
			}
		}
		
		$js .= ']
		};';

		return $js;
	}
	
	/**
	 * Sets the view field 
	 * @param $view Zend_View_Interface
	 */
	public function setView(Zend_View_Interface $view) {
		$this->view = $view;
	}
}
