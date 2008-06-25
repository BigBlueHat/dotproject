<?php
/**
 * Plain text table cell.
 * 
 * Renders a totally unformatted text string from the column value.
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 *
 */
class DP_View_Cell extends DP_View {
	protected $value_key;
	protected $column_title;
	protected $column_name;
	
	public function __construct($value_key, $column_title = '(Untitled)', $attribs = Array()) {
		// TODO - proper generation of parent id
		parent::__construct('dp-cell-'.$value_key);
		$this->value_key = $value_key;
		$this->setHTMLAttribs($attribs);
		
		$this->column_title = $column_title;
		$this->column_name = $value_key;
	}
	
	/**
	 * Render a cell with the supplied hash of row data.
	 * 
	 * @param Array $rowhash Hash containing row data.
	 * @return HTML Output
	 */
	public function render($rowhash) {
		$output = $rowhash[$this->value_key];
		return $output;
	}
	
	public function getColumnTitle() {
		return $this->column_title;
	}
	
	public function setColumnTitle($title) {
		$this->column_title = $title;
	}
	
	public function getColumnName() {
		return $this->column_name;
	}
}
?>