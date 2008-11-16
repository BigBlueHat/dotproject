<?php
/**
 * Stores column metadata and definitions.
 * 
 * The class can extract metadata from a Db_Table class and present the information as a javascript object for use
 * with a DataTable widget. This provides some sane defaults as to what the column headings, size defaults, validators,
 * and editors should be. The API should also provide methods to show/hide columns and change defaults.
 * 
 * This object is paired with a view helper which does the job of outputting the javascript code.
 * 
 * @author ebrosnan
 * @version 3.0 alpha
 * @package dotproject
 */
class DP_Datasource_Columns implements Iterator, Countable {
	/**
	 * @var array $table_meta Table column metadata.
	 */
	protected $table_meta; 
	/**
	 * @var array $table_meta_keys Array of keys to table column metadata.
	 */
	protected $table_meta_keys;
	/**
	 * @var integer $object_iter_idx Iterator index.
	 */
	protected $object_iter_idx;
	/**
	 * @var array $labels Column header labels.
	 */
	protected $labels;
	/**
	 * @var array $enabled_columns Column keys of columns that should be outputted.
	 */
	protected $enabled_columns;
	
	/**
	 * Datasource columns constructor.
	 * 
	 * @param Zend_Db_Table $tbl The instance of zend_db_table to extract column metadata from.
	 */
	public function __construct(Zend_Db_Table $tbl) {
		$this->table_meta = $tbl->info('metadata');
		$this->table_meta_keys = array_keys($this->table_meta);
		$this->object_iter_idx = 0;
		$this->enabled_columns = Array("id", "name", "description"); // TODO: un hard-code columns
	}
	
	/**
	 * Generate reasonable YUI column defaults for formatting and editing.
	 * 
	 * @param mixed $key Array key of a column in the table metadata.
	 * @param Array $column table column metadata
	 * 
	 * @return Array associative array representing a YUI DataTable column definition.
	 */
	private function _toYuiDef($key, array $column) {
		switch($column['DATA_TYPE'])
		{
			case 'int':
				$formatter = 'number';
				$editor = 'YAHOO.widget.DataTable.TextboxCellEditor';
				break;
			case 'varchar':
				$formatter = 'text';
				$editor = 'YAHOO.widget.DataTable.TextboxCellEditor';
				break;
			case 'text':
				$formatter = 'textarea';
				$editor = 'YAHOO.widget.DataTable.TextAreaCellEditor';
				break;
			default:
				$formatter = 'text';
				$editor = 'YAHOO.widget.DataTable.TextboxCellEditor';
		}
		
		return array(
			"key"=>$key,
			"label"=>$this->labels[$key],
			"formatter"=>$formatter,
			"editor"=>$editor,
		
		);
	}
	
	public function setLabels(Array $cols_labels)
	{
		$this->labels = $cols_labels;
	}
	
	// From Countable
	
	public function count() {
		return count($this->table_meta);
	}
	
	// From Iterator
	
	/**
	 * Return the current element.
	 */
	public function current() {
		$meta = $this->table_meta[$this->enabled_columns[$this->object_iter_idx]];
		$meta_key = $this->table_meta_keys[$this->object_iter_idx];
		
		$definition = $this->_toYuiDef($meta_key, $meta);
		
		return $definition;
	}
	
	/**
	 * Return the key of the current element.
	 */
	public function key() {
		if ($this->needs_refresh == false) {
			return $this->object_iter_idx;
		} else {
			return null;
		}
	}
	
	/**
	 * Move forward to next element.
	 */
	public function next() {
		$this->object_iter_idx++;
	}
	
	/**
	 * Rewind the Iterator to the first element.
	 */
	public function rewind() {
		$this->object_iter_idx = 0;
	}
	
	/**
	 * Check if there is a current element after calls to rewind() or next().
	 */
	public function valid() {
		if ($this->object_iter_idx < count($this->enabled_columns)) {
			return true;
		} else {
			return false;
		}
	}	
	
}
?>