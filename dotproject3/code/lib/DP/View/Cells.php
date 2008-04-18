<?php
/**
 * Cell view based on a multi-column arrangement of individual boxes in a non-grid format.
 * 
 * Not yet implemented - see contact list in dp2 for an example of the intended implementation
 */

class DP_View_Cells extends DP_View_Stateful {
	protected $columncount;

	/**
	 * @var DP_List_Source_interface $src The data source for this object
	 */
	protected $src;
	/**
	 * @var DP_View_Iterator Iterator used when generating markup.
	 */
	protected $view_iterator;
	
	public function __construct($id) {
		parent::__construct($id);
		$this->view_iterator = new DP_View_Iterator();
		//$this->columncount = 4;
	}
	
	/**
	 * Set the data source to use for the list view.
	 * 
	 * The data source must implement the DP_View_List_Source_Interface interface.
	 * 
	 * @param DP_View_List_Source_Interface $listsource The list view source.
	 */
	public function setDataSource(DP_View_List_DataSource $listsource) {
		$this->src = $listsource;
	}
	
	/**
	 * Get the data source being used for this list view.
	 * 
	 * @return DP_List_View_Source_Interface The source object
	 */
	public function dataSource() {
		return $this->$src;
	}
	
	/**
	 * Get the current instance of the view iterator.
	 * 
	 * @return DP_View_Iterator view iterator.
	 */
	public function getViewIterator() {
		return $this->view_iterator;
	}
	
	/**
	 * Set the view row iterator to use
	 */
	public function setIterator(DP_View_Iterator $iter) {
		$this->view_iterator = $iter;
	}
	
	public function render() {
		// Notify datasource of our intention to render
		$this->src->clientWillRender();

		$output = "";
		$output .= $this->renderChildren(DP_View::PREPEND);
		

		$this->view_iterator->setDataSource($this->src);
		if ($this->view_iterator->isDone()) {
			$output .= '<p>No records were found matching your query.</p>';
		}
		
		while (!$this->view_iterator->isDone()) {
			$row = $this->view_iterator->currentItem();
			$output .= $row;
			$this->view_iterator->next();
		}
		
		$output .= $this->renderChildren(DP_View::APPEND);		
		return $output;
	}
	

}
?>