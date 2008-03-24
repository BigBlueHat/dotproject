<?php
class DP_List {
	protected $list;
	
	public function __construct($list = Array()) {
		if (is_array($list)) {
			$this->list = $list;
		} else {
			$this->list = Array();
		}
	}
}
?>