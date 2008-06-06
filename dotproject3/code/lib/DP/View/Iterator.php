<?php
/**
 * Base class for iteration of view objects.
 * 
 * View objects supplied to this class of objects are normally to be used in conjunction with
 * repeating data such as a row set or list. The view iterator takes an iterable data source and 
 * its internal array of views and iterates through both simultaneously.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_View_Iterator implements DP_View_Notification_Interface {
	/**
	 * @var array $views Collection of view objects.
	 */
	protected $views;
	/**
	 * @var DP_View_List_DataSource The data source.
	 */
	protected $src;	
	/**
	 * @var Integer index of the view iterator.
	 */
	protected $index;
	/**
	 * @var String container element to wrap each view in.
	 */
	protected $container_element;
	
	//protected $outer_element;
	
	public function __construct() {
		$this->views = Array();
		$this->index = 0;
		$this->container_element = 'LI';
	}

	/**
	 * Add a view to the iterator
	 * 
	 * The view objects will be rendered in the order that they are given.
	 * The view must be DP_View_Cell because this is currently the only class
	 * that can accept a table row passed to render().
	 * 
	 * @param DP_View_Cell $view A view object.
	 * 
	 */
	public function add($view) {
		$this->views[] = $view;
	}
	
	/**
	 * Get the current number of views in the view iterator.
	 * 
	 * This is unrelated to the data source.
	 * 
	 * @return Integer Total number of views.
	 */
	public function rowCount() {
		return count($this->views);
	}
	
	public function count() {
		return $this->src->pageItemCount();
	}
	
	/**
	 * Set the data source to be used for this row iterator.
	 * 
	 * The source must implement the DP_View_List_Source_Interface interface.
	 * 
	 * @param DP_View_List_Source_Interface $src The data source
	 */
	public function setDataSource(DP_View_List_DataSource $src) {
		$this->src = $src;
	}

	/**
	 * Move the iterator forward one item.
	 * 
	 * The view iterator loops infinitely through its view collection. It only stops
	 * when the source iterator is no longer valid.
	 */
	public function next() {
		$this->row_index++;
		if ($this->row_index >= $this->rowCount()) {
			$this->row_index = 0;
			$this->src->next();
		}
	}
	
	/**
	 * Get the iterator status.
	 * 
	 * @return bool true if the data source is done, false if the data source is not beyond the last item.
	 */
	public function isDone() {
		if (!$this->src->valid() || $this->rowCount() == 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Set the container element for each item
	 * 
	 * @param string $el The container element, without greater than or less than signs.
	 */
	public function setContainerElement($el) {
		$this->container_element = $el;
	}
	
	/**
	 * Get the container element for each item.
	 * 
	 * @return string The container element, without greater than or less than signs.
	 */
	public function containerElement() {
		return $this->container_element;
	}
	
	/**
	 * Render the current view.
	 * 
	 * @todo possibly use Zend_Form decorators instead of containerElement convention.
	 * @return string HTML Output.
	 */
	public function currentItem() {
		$view = $this->views[$this->index];
		//$this->src->clientWillRender();
		//$output = '<'.$this->containerElement().'>';
		$output .= $view->render($this->src->current());
		//$output .= '</'.$this->containerElement().'>';
		
		return $output;
	}
	
	// From DP_View_Notification_Interface
	
	public function viewWillRender(Zend_View $view) {
		foreach ($this->views as $v) {
			if ($v instanceof DP_View_Notification_Interface) {
				$v->viewWillRender($view);
			}
		}
	}
}
?>