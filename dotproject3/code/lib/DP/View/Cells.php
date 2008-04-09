<?php
/**
 * Cell view based on a multi-column arrangement of individual boxes in a non-grid format.
 * 
 * Not yet implemented - see contact list in dp2 for an example of the intended implementation
 */

class DP_View_Cells extends DP_View_Stateful {
	protected $columncount;
	
	public function __construct($id) {
		parent::__construct($id);
		
		$this->columncount = 4;
	}
	
	public function render() {
		
	}
}
?>