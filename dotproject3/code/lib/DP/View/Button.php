<?php
/**
 * Button view containing a button.
 * 
 * Use this when the button is not grouped with any other functionality
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_View_Button extends DP_View_Stateful {
	public $button;
	
	public function __construct($id, $title) {
		parent::__construct($id);
		$this->button = new Zend_Form_Element_Button($title);
		$this->button->removeDecorator('DtDdWrapper');
	}
	
	public function render() {
		$output = '<div class="View_Button">';
		$output .= $this->button->render();
		$output .= '</div>';
		return $output;
	}
}
?>