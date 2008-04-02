<?php
/**
 * Class for constructing a table row or set of rows using a set of data.
 *
 * The row iterator can construct a simple table row given a hash of information and
 * a set of DP_View_Cell objects. The row iterator can iterate through multiple row definitions.
 * i.e First displaying a row containing the object name and on the next iteration displaying
 * links to other objects.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0alpha
 * 
 */
class DP_View_RowIterator {
	/**
	 * The data source to use when rendering rows.
	 * @var Object $src
	 */
	private $src;
	/**
	 * Reference to the iterator class of the data source
	 * @var Object $srciter
	 */
	private $srciter;
	/**
	 * The index of the current row to render.
	 * @var integer $row_index
	 */
	private $row_index;
	private $rows;
	
	public function __construct($id) {
		//parent::__construct($id);
		$this->row_index = 0;
		$this->rows = Array();
	}
	
	/**
	 * Add a row to the iterator using an array of DP_View_Cell classes
	 * 
	 * The DP_View_Cell objects will be rendered in the order that they are given.
	 * 
	 * @param array $cells Array of DP_View_Cell objects
	 * 
	 */
	public function addRow($cells) {
		$this->rows[] = $cells;
	}
	
	/**
	 * Get the current number of rows in the rowiterator.
	 * 
	 * This is unrelated to the number of rows in the data source.
	 */
	public function rowCount() {
		return count($this->rows);
	}
	
	/**
	 * Set the data source to be used for this row iterator.
	 * 
	 * The source must implement the DP_View_List_Source_Interface interface.
	 * 
	 * @param DP_View_List_Source_Interface $src The data source
	 */
	public function setDataSource(DP_View_List_Source_Interface $src) {
		$this->src = $src;
		$this->srciter = $this->src->getIterator();
	}

	public function next() {
		$this->row_index++;
		if ($this->row_index >= $this->rowCount()) {
			$this->row_index = 0;
			$this->srciter->next();
		}

	}
	
	public function isDone() {
		if ($this->srciter->isDone() || $this->rowCount() == 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Render the next row
	 */
	public function currentItem() {
		$cells = $this->rows[$this->row_index];
		
		$output = '<tr>';
		
		foreach ($cells as $cell) {
			
			$output .= '<td';
			$output .= ($cell->align()) ? ' align="'.$cell->align().'"' : '';
			$output .= ($cell->width()) ? ' width="'.$cell->width().'"' : '';
			$output .= '>';
			$output .= $cell->render($this->srciter->currentItem());
			$output .= '</td>';
		}
		
		$output .= '</tr>';
		return $output;		
	}
}
?>