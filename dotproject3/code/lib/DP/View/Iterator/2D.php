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
class DP_View_Iterator_2D extends DP_View_Iterator {
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
	 * Add a required javascript file.
	 * 
	 * @param DP_View_Cell $view Cell view that requested the javascript.
	 * @param string $js Relative URL of the javascript to be included.
	 */
	private function addRequiredJS($view, $js) {
		$this->required_js[] = Array('view'=>$view, 'js'=>$js);
	}
	
	/**
	 * Get an array of required javascript files.
	 * 
	 * @return Array indexed array of relative URLs to javascript files.
	 */
	private function getRequiredJSFiles() {
		$required_files = Array();
		foreach ($this->required_js as $req) {
			$required_file = $req['js'];
			$required_files[] = $required_file;
		}
		
		return $required_files;
	}
	
	/**
	 * Add an array of views.
	 * 
	 * Views will be rendered inside a grouping element (such as <TR>) and each within their own container element (such as <TD>).
	 * 
	 * @param DP_Cell_View $views Array of views.
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
	public function viewWillRender($view) {
		// iterate through rows and cells, collecting required javascript.
		Zend_Debug::dump("Iterator notified of view render.");
		foreach($this->views as $viewrow) {
			foreach ($viewrow as $v) {
				$js = $v->getRequiredJS(); // Get the javascript required to support the cell.
				
				if ($js != null) {
					Zend_Debug::dump("Javascript required: ".$js);
					$view->headScript()->appendFile($js);
				}
			}
		}
		// add javascript to Zend_View
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