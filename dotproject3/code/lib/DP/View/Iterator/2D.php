<?php
/**
 * 2 dimensional view iterator.
 * 
 * Iterates through a 2 dimensional array of view objects. Normally used to construct a grid or table.
 * Can also be used to group a set of views by changing the grouping element.
 *
 * @package dotproject
 * @subpackge system
 * @version 3.0 alpha
 */
class DP_View_Iterator_2D extends DP_View_Iterator implements DP_View_Notification_Interface {
	/**
	 * @var string $grouping_element The element to group arrays of views with.
	 */
	protected $grouping_element;
	protected $required_js;
	
	/**
	 * Initialise the view array.
	 */
	public function __construct() {
		parent::__construct();
		$this->grouping_element = 'TR';
		$this->setContainerElement('TD');
		$this->required_js = Array();
	}
	
	/**
	 * Add an array of views.
	 * 
	 * Views will be rendered inside a grouping element (such as <TR>) and each within their own container element (such as <TD>).
	 * 
	 * @param DP_Cell_View $views Array of views, indexed in the order of display
	 */
	public function addRow($views) {
		$this->views[] = $views;
	}
	
	/**
	 * Get the grouping element.
	 * 
	 * @return String HTML element used to group arrays of views. Without less than or greater than signs.
	 */
	public function groupingElement() {
		return $this->grouping_element;
	}
	
	/**
	 * Set the grouping element.
	 * 
	 * @param String $el element used to group arrays of views, without less than or greater than signs.
	 */
	public function setGroupingElement($el) {
		$this->grouping_element = $el;
	}
	
	/**
	 * Notify this view that the specified Zend_View will render.
	 * 
	 * @param $view Zend_View
	 */
	public function viewWillRender(Zend_View $view) {
		// iterate through rows and cells, collecting required javascript.
		foreach($this->views as $viewrow) {
			foreach ($viewrow as $v) {
				$v->viewWillRender($view); // Get the javascript required to support the cell.
			}
		}
	}
	
	/**
	 * Render the current item.
	 * 
	 * @return string HTML Output.
	 * @todo container element attributes should be done differently.
	 * @todo consider adding zend decorator support to dp_view
	 */
	public function currentItem() {
		$cells = $this->views[$this->index];
		
		$output = '<'.$this->groupingElement().'>';
		
		foreach ($cells as $cell) {
			
			$output .= '<'.$this->containerElement();
			$output .= ($cell->align()) ? ' align="'.$cell->align().'"' : '';
			$output .= ($cell->width()) ? ' width="'.$cell->width().'"' : '';
			$output .= '>';
			$output .= $cell->render($this->src->current());
			$output .= '</'.$this->containerElement().'>';
		}
		
		$output .= '</'.$this->groupingElement().'>';
		return $output;		
	}
}
?>