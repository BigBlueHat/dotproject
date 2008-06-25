<?php
class DP_View_Cell_Date extends DP_View_Cell 
{
	public function __construct($value_key, $column_title = '(Untitled)', $attribs = array()) {
		parent::__construct($value_key, $column_title, $attribs);
	}
	/**
	 * Render a cell with the supplied hash of row data.
	 * 
	 * @param Array $rowhash Hash containing row data.
	 * @return HTML Output
	 */
	public function render($rowhash) {
		$raw_date = $rowhash[$this->value_key];
		if (!is_null($raw_date)) {
			$date = new Zend_Date($raw_date);
			$date->setLocale('en_AU'); // TODO - localisation from DP_Config
			$output = $date->get(Zend_Date::DATE_SHORT);
		} else {
			$output = '';
		}
		return $output;
	}
}
?>