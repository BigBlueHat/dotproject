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
	 * @var DP_List_Source_interface The data source for this DP_View_List Instance
	 */
	protected $src;
	/**
	 * @var Array $columns Internal array of columns to display.
	 */
	protected $columns;
	/**
	 * @var mixed $filter Not yet implemented
	 * @todo Make DP_View_List reflect the sort state.
	 */
	protected $filter;
	/**
	 * @var mixed $sort Sort object
	 */
	protected $sort_object;
	/**
	 * @var array $tool_views DP_View objects to be inserted into the header area of the DP_View_List
	 */
	protected $tool_views;
	
	/**
	 * Create a new DP_View_List
	 * 
	 * @param mixed $id The DP_View_List identifier. Must be exactly the same as the template variable.
	 * @param DP_View_List_Source_Interface $listobj The object implementing the data source interface.
	 * @return DP_View_List Instance of DP_View_List
	 */
	public function __construct($id, DP_Query_Sort $sort) {
		parent::__construct($id);
		
		//$this->src = $listobj;
		$this->src = new DP_List();
		
		$this->columns = Array();
		$this->tool_views = Array();
		$this->sort_object = $sort;
	}

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
	 * Add a plain text column to the list view
	 * 
	 * @param string $field_name The key of the field to use in this column.
	 * @param string $field_heading The heading to use for this column.
	 */
	public function addTextColumn($field_name, $field_heading) {
		$this->columns[] = Array("type"=>"text", "name"=>$field_name, "heading"=>$field_heading); 
	}
	
	/**
	 * Add an object link column to the list view
	 * 
	 * @param string $object_id_field The key of the field containing the object identifier.
	 * @param string $object_name_field The key of the field containing the name of the object.
	 * @param string $link_prefix The link prefix containing the module and action.
	 * @param string $field_heading The heading of the column.
	 */
	public function addObjectLinkColumn($object_id_field, $object_name_field, $link_prefix, $field_heading) {
		$this->columns[] = Array("type"=>"objectlink", 
								"object_id"=>$object_id_field, 
								"name"=>$object_name_field, 
								"link_prefix"=>$link_prefix,
								"heading"=>$field_heading);
	}

	/**
	 * Get the current number of defined columns.
	 * 
	 * @return integer Number of columns.
	 */
	public function columnCount() {
		return count($this->columns);
	}
	
	/**
	 * Set the filter used to produce the data source for this object.
	 * 
	 * The filter lets the list view know what state the search or sort widgets should be in.
	 * 
	 * @param DP_Filter $filter The filter used to produce the data source for this object.
	 * @todo Make the DP_View_List display the proper state of each filter/sort object
	 */
	public function setFilterState(DP_Filter $filter) {
		$this->filter = $filter;
	}
	
	/**
	 * Insert a DP_View in the table heading section.
	 * 
	 * Insert a DP_View to be displayed in the table heading section as a toolbar.
	 * The toolbar will be rendered with the same style as the table headers.
	 * More than one view can be added. The views will be displayed sequentially.
	 * Child views will have the parent id set to the id of this DP_View_List object.
	 * 
	 * @param DP_View $view The view to insert
	 */
	public function insertToolView(DP_View $view) {
		$view->setParentViewId($this->id());
		$this->tool_views[] = $view;
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
	
	/**
	 * Render this view to HTML
	 * 
	 * @return string HTML output
	 */
	public function render() {
		// TODO - at the moment the table is dynamically generated. Use smarty templates to produce standard
		// cells or custom cells as required (html shouldnt be used inside the code).
		$output = "";
		$output .= "<table class=\"tbl\" width=\"".$this->width()."\" border=\"0\" cellpadding=\"2\" cellspacing=\"1\" >\n";

		
		//$output .= $this->renderToolViews();
		
		$output .= '<tr><td colspan="'.$this->columnCount().'">';
		$output .= $this->renderChildren();
		$output .= '</td></tr>';
		
		foreach ($this->columns as $col) {
			$output .= "\t<th><a href=\"?sort=".$col["name"]."&view_id=".$this->id()."\" class=\"hdr\">".$col['heading']."</a></th>\n";
		}
		
		for ($rn = 0; $rn <= $this->src->rowCount(); $rn++) {
			$output .= "\t<tr>\n";
			$output .= "\t\t";
			$row = $this->src->fetchRow($rn);
			foreach ($this->columns as $col) {
				$output .= $this->renderColumn($col, $row);
			}
			$output .= "\n";
			$output .= "\t</tr>\n";
		}
		$output .= "</table>\n";
		
		return $output;
	}

	/**
	 * Update the visual state of the list view from server request variables.
	 * 
	 * This method should update the list view to reflect the state of the sort object.
	 * @todo Make server updates flow on to child objects
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