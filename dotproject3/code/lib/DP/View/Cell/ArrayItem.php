<?php
/**
 * Cell based on an array, which prints the item according to the key it is supplied.
 * 
 * The item_key is used to pluck the data from the table row.
 * The data returned will also be a valid key for the items array. The Cell then prints
 * the item with the key from the table row.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * 
 */
class DP_View_Cell_ArrayItem extends DP_View_Cell {
	/**
	 * @var Array $items collection of items
	 */
	private $items;
	
	/**
	 * @var mixed $item_key key to table row data
	 */
	private $item_key;
	
	public function __construct($items, $item_key, $attribs = array()) {
		parent::__construct('cell');

		$this->items = $items;
		$this->item_key = $item_key;
		$this->setHTMLAttribs($attribs);
	}
	
	public function render($rowhash) {
		$output = $this->items[$rowhash[$this->item_key]];
		return $output;
	}
}
?>