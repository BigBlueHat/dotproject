<?php
/**
 * Cell containing the name of an object which is also a link to that object.
 *
 */
class DP_View_Cell_ObjectSelect extends DP_View_Cell {
	private $hrefprefix;
	private $id_key;
	
	public function __construct($id_key, $attribs = Array()) {
		// TODO - proper generation of parent id
		parent::__construct('Cell-Select');
		$this->id_key = $id_key;

		$this->setHTMLAttribs($attribs);
		
		$fc = Zend_Controller_Front::getInstance();
		
	}
	
	/**
	 * Render the cell with a given associative array produced from a data source.
	 * 
	 * The data source will produce an associative array of table column name to value.
	 * @param array $rowhash associative array of row data.
	 * @return HTML output
	 */
	public function render($rowhash) {
		$output = '<input type="checkbox" name="chk-'.$this->id_key.'[]" value="'.$rowhash[$this->id_key].'" onClick="toggleSelectListItem(this, this.parentNode.parentNode);" />';
		return $output;
	}
	
	/**
	 * Notify this view that it is about to be rendered.
	 * 
	 * @param Zend_View $view the view that is about to render this object.
	 */
	public function viewWillRender($view) {
		$this->notifyChildrenWillRender($view);
	}
	
	/**
	 * Get the relative URL of javascript file required to support this view.
	 * 
	 * @return string Relative url of .js file.
	 * @todo Make return type array in order to supply multiple js files per view object.
	 */
	public function getRequiredJS() {
		return '/js/DP/View/Cell-ObjectSelect.js';
	}
}
?>