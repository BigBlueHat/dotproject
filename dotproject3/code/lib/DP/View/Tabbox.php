<?php
/**
 * Tab box view.
 * 
 * Presents the user with multiple tabs. Should be used with a child view for
 * filtering or multiple children for displaying/hiding.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 * @todo Tab box state handler
 *
 */
class DP_View_TabBox extends DP_View_Stateful {
	protected $tabs;
	
	public function __construct($id) {
		parent::__construct($id);
		$this->tabs = Array();
	}
	
	public function render() {
		$output = '<div>';
	}
}
?>