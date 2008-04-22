<?php
/**
 * Column view which behaves like a set of newspaper columns would.
 * 
 * The column view renders until it has reached a maximum height and then
 * wraps on to the next column. Each column is an equal percentage of the total size of this
 * element by default.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0
 *
 */
class DP_View_Columns extends DP_View_Stateful {
	/**
	 * @var integer $num_columns The number of columns to display.
	 */
	protected $num_columns;	
	/**
	 * @var integer $max_height The maximum height before rolling over to the next column.
	 */
	protected $max_height; // Is this really needed if we limit the number of returned rows? cant have pager and height limit.
	/**
	 * @var DP_View_Iterator $view_iterator The view iterator used for rendering column content.
	 */
	protected $view_iterator;
	/**
	 * @var integer $page_items The number of items on one page.
	 */
	protected $page_items;
	
	public function __construct($id) {
		parent::__construct($id);
		
		$this->num_columns = 4;
		$this->max_height = 16;
		$this->view_iterator = null;
	}
	
	// Height functions - may be deprecated
	
	/**
	 * Set the maximum height of a column.
	 * 
	 * The units are arbitrary at the moment, they are relative to the view iterators height() method.
	 * Which returns an arbitrary integer representing the height of one view iteration (in the case of contacts
	 * the height unit is one row of information).
	 * 
	 * @param integer $max_height The maximum column height
	 */
	public function setMaxHeight($max_height) {
		$this->max_height = $max_height;
	}
	
	/**
	 * Get the maximum column height.
	 * 
	 * @return Integer maximum column height in arbitrary units.
	 */
	public function maxHeight() {
		return $this->max_height;
	}
	
	/**
	 * Set the number of columns to display.
	 * 
	 * @param integer $num_columns The number of columns to display.
	 */
	public function setColumnCount($num_columns) {
		$this->num_columns = $num_columns;
	}
	
	/**
	 * Get the number of display columns currently set.
	 * 
	 * @return integer Number of columns to display.
	 */
	public function columnCount() {
		return $this->num_columns;
	}
	
	/**
	 * Set the view iterator used for rendering the columns.
	 * 
	 * @param DP_View_Iterator $iter instance of view iterator.
	 */
	public function setIterator(DP_View_Iterator $iter) {
		$this->view_iterator = $iter;
	}
	
	/**
	 * Get the instance of view iterator used for rendering the columns.
	 * 
	 * @return DP_View_Iterator instance of the view iterator.
	 */
	public function getIterator() {
		return $this->view_iterator;
	}
	
	/**
	 * Set the number of items on a page.
	 * 
	 * Used to calculate the number of items in a column.
	 * 
	 * @todo Somehow automatically determine this from the pager, or have the data source callback this method.
	 * 
	 * @param int $page_items Number of items per page.
	 */
	public function setItemsPerPage($page_items) {
		$this->page_items = $page_items;
	}
	
	public function itemsPerPage() {
		return $this->page_items;
	}
	
	/**
	 * Render this view.
	 * 
	 * @see DP_View::render()
	 */
	public function render() {
		if ($this->view_iterator != null) {

			$output = "";
			$output .= $this->renderChildren(DP_View::PREPEND);
			
			
			$output .= '<table class="View_Columns" width="100%">';
		
			
			
			if ($this->view_iterator->isDone()) {
				$output .= '<tr><td width="100%">';
				$output .= '<p>No records were found matching your query.</p>';
			} else {

				$max_items_per_col = 0;
				$page_items = $this->getIterator()->count();
				
				// Maximum items in each column, determined by number of rows in data source divided by set number of columns.
				$max_items_per_col = $page_items / $this->columnCount();
	
				// If items dont divide evenly we need another item per column.
				if (($page_items % $this->columnCount()) > 0) {
					$max_items_per_col++;
				}	
	
				$column_height = 0;
				$column_width = floor(100 / $this->columnCount());
				
				$output .= '<tr><td width="'.$column_width.'%">';
				$column_count = 1;		
				
				while (!$this->view_iterator->isDone()) {
					$row = $this->view_iterator->currentItem();
					
					$column_height++;
					
					if ($column_height > $max_items_per_col) {
						$output .= '</td><td width="'.$column_width.'%">';
						$column_height = 1;
						$column_count++;
					}
	
					$output .= $row;
					$this->view_iterator->next();
	
				}
			}

			$output .= '</td></tr>';
			$output .= '</table>';
			$output .= $this->renderChildren(DP_View::APPEND);
			
			return $output;
		}
	}
	
}
?>