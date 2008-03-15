<?php
class DP_List implements DP_View_List_Source_Interface {
	protected $list;
	
	public function __construct($list = Array()) {
		if (is_array($list)) {
			$this->list = $list;
		} else {
			$this->list = Array();
		}
	}
	
	public function rowCount() {
		return count($this->list);
	}
	/*
	 * 
	 * @see DP_List_Source_Interface::fetchRow()
	 */
	public function fetchRow($index) {
		$item = $this->list[$index];
		return $item;
	}
	
	/*
	public function getEnumerator() {
		return new DP_List_Enumerator(self);
	}
	*/
}
?>