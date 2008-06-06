<?php
/**
 * Cell containing the name of an object which is also a link to that object.
 *
 */
class DP_View_Cell_ObjectSelect extends DP_View_Cell implements DP_View_Notification_Interface {
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
		//$output = '<input type="checkbox" name="chk-'.$this->id_key.'[]" value="'.$rowhash[$this->id_key].'" onChange="dpselection.toggle(this, this.parentNode.parentNode);" />';
		$output = '<input 
					type="checkbox"
					id="chk-'.$this->id_key.'-'.$rowhash[$this->id_key].'"
					name="chk-'.$this->id_key.'[]" 
					value="'.$rowhash[$this->id_key].'" 
					onClick="dpselection.toggle(this, this.parentNode.parentNode);"
					/>';
		
		return $output;
	}
	
	/**
	 * Notify this view that it is about to be rendered.
	 * 
	 * @param Zend_View $view the view that is about to render this object.
	 */
	public function viewWillRender(Zend_View $view) {
		$view->HeadScript()->appendScript('dojo.require("dijit.form.CheckBox");');
		$view->HeadScript()->appendFile('/js/DP/View/Cell-ObjectSelect.js');
		
		$this->notifyChildrenWillRender($view);
	}
}
?>