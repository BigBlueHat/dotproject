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
		$this->sort_object = $AppUI->getState($this->id().'-sort', new DP_Query_Sort());
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
	public function setDataSource(DP_View_List_Source_Interface $listsource) {
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
	 * Render a table cell given the column information and the cell data.
	 * 
	 * @param array $column Array containing the specs for the column.
	 * @param array $data Hash of row data.
	 * @return string Rendered table cell.
	 */
	private function renderColumn($column, $data) {
		switch($column['type']) {
			case "objectlink":
				return '<td><a href="'.$column['link_prefix'].$data[$column['object_id']].'">'.$data[$column['name']].'</a></td>';
			case "text":
				return '<td>'.$data[$column['name']].'</td>';
		}
	}
	
	/**
	 * Render tool views to be inserted at the top of the list view.
	 * 
	 * @return string Tool view output.
	 */
	private function renderToolViews() {
		$output = "";
		
		foreach ($this->tool_views as $tv) {
			$output .= "<tr><td colspan=\"".$this->columnCount()."\">".$tv->render()."</td></tr>\n";
		}
		
		return $output;
	}
	
	protected function renderChildren() {
		$output = "";
		foreach ($this->child_views as $child) {
			$output .= '<div style="float: left; margin-left: 5px; margin-right: 5px;">';
			$output .= $child->render();
			$output .= '</div>';
			//$output .= '&nbsp;|&nbsp;';
		}
		return $output;
	}
	
	/**
	 * Render this view to HTML
	 * 
	 * @return string HTML output
	 */
	public function render() {
		$output = "";
		$output .= "<table class=\"dp-view-list\" width=\"".$this->width()."\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\" >\n";
		
		$output .= '<tr><td colspan="'.$this->columnCount().'">';
		$output .= $this->renderChildren();
		$output .= '</td></tr>';
		
		
		foreach ($this->column_headers as $fname => $hdr) {
			$output .= "\t<th><a href=\"?sort=".$fname."&view_id=".$this->id()."\" class=\"hdr\">".$hdr."</a></th>\n";
		}

		$this->row_iterator->setDataSource($this->src);
		
		while (!$this->row_iterator->isDone()) {
			$row = $this->row_iterator->currentItem();
			$output .= $row;
			$this->row_iterator->next();
		}
		
		$output .= "</table>\n";
		
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
		}
		
		$this->updateChildrenFromServer($request);
	}
	
}
?>