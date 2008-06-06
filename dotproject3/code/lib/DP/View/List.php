<?php
/** 
 * Represents a table containing a list of rows.
 * 
 * This view object must render a DP_View_List_Source into a typical listview interface.
 * A listview interface will contain column headers, a list of rows and a standardised sort function when
 * clicking on headers.
 * @author ebrosnan
 * @version not.even.alpha
 * @package dotproject
 * @subpackage system
 * @todo Minimise the amount of HTML in DP_View classes
 */
class DP_View_List extends DP_View_Stateful {

	/**
	 * @var DP_List_Source_interface $src The data source for this DP_View_List Instance
	 */
	protected $src;

	/**
	 * @var DP_View_RowIterator $rowiterator Renders the table rows.
	 */
	public $row_iterator;
	/**
	 * @var mixed $sort Sort object
	 */
	protected $sort_object;
	/**
	 * @var array $column_headers Array of column field names to column names. Used for the sorting headers
	 */
	protected $column_headers;
	
	/**
	 * Create a new DP_View_List
	 * 
	 * @param mixed $id The DP_View_List identifier. Must be exactly the same as the template variable.
	 * @param DP_View_List_Source_Interface $listobj The object implementing the data source interface.
	 * @return DP_View_List Instance of DP_View_List
	 */
	public function __construct($id) {
		parent::__construct($id);
		
		$AppUI = DP_AppUI::getInstance();
		$this->row_iterator = DP_View_Factory::getRowIterator($this->id().'-rows');
		$this->src = new DP_List();
		
		// Manage sort state
		$this->sort_object = new DP_Query_Sort($this->id().'-sort');
		
		$sort_state = $AppUI->getState($this->id().'-sort');
		if ($sort_state != null) {
			$this->sort_object->setMemento($sort_state);
		}
		
		$this->column_headers = Array();
	}

	// Access methods
	
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
	 * Set the view row iterator to use
	 */
	public function setRowIterator(DP_View_RowIterator $rowiterator) {
		$this->row_iterator = $rowiterator;
	}

	/**
	 * Get a reference to the listviews sort object.
	 * 
	 * @return DP_Query_Sort An instance of DP_Query_Sort which reflects the current sorting of the view
	 */
	public function getSort() {
		return $this->sort_object;
	}
	
	/**
	 * Set column headers.
	 * 
	 * @param array $colhdrs array of strings to use as column headers
	 */
	public function setColumnHeaders($colhdrs) {
		$this->column_headers = $colhdrs;
	}
	
	/**
	 * Get the current number of defined columns.
	 * 
	 * @return integer Number of columns.
	 */
	public function columnCount() {
		return count($this->column_headers);
	}
	
	/**
	 * Notify this view that it is about to be rendered
	 * 
	 * @param Zend_View $view The view object that is about to be rendered.
	 */
	public function viewWillRender($view) {
		$this->row_iterator->viewWillRender($view); // Allows row iterator to add javascript for cells.
		$this->notifyChildrenWillRender($view);
	}
	
	/**
	 * Render this view to HTML
	 * 
	 * @return string HTML output
	 */
	public function render() {
		// Notify datasource of our intention to render
		$this->src->clientWillRender();
		
		// TODO - remove hardcoded action
		$output = "<form id=\"".$this->id()."-form\" name=\"".$this->id()."\" action=\"?\" method=\"POST\">";
		$output .= $this->renderChildren(DP_View::PREPEND);
		
		// TODO - transfer element attributes to style sheet
		$output .= "<table class=\"dp-view-list\" id=\"".$this->id()."\" width=\"".$this->width()."\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\" >\n";
		
		foreach ($this->column_headers as $fname => $hdr) {
			$output .= '<th><a href="?view_id='.$this->id().'&sort='.$fname.'" class="hdr">'.$hdr.'</a></th>';
		}

		$this->row_iterator->setDataSource($this->src);
		
		if ($this->row_iterator->isDone()) {
			// TODO - add translation object to DP_View_List
			$output .= '<tr><td colspan="'.$this->columnCount().'">No records were found matching your query.</td></tr>';
		} else {	
			while (!$this->row_iterator->isDone()) {
				$row = $this->row_iterator->currentItem();
				$output .= $row;
				$this->row_iterator->next();
			}
		}
		$output .= "</table></form>\n";
		$output .= $this->renderChildren(DP_View::APPEND);		
		return $output;
	}

	/**
	 * Update the visual state of the list view from server request variables.
	 * 
	 * This method should update the list view to reflect the state of the sort object.
	 * 
	 * @param mixed $request Server request object.
	 */
	public function updateStateFromServer($request) {
		if (isset($request->sort) && ($request->view_id == $this->id())) {
			$this->sort_object->sort($request->sort);

			$AppUI = DP_AppUI::getInstance();
			$AppUI->setState($this->id().'-sort', $this->sort_object->createMemento());
		}
		
		$this->updateChildrenFromServer($request);
	}
	
}
?>