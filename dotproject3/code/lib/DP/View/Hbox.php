<?php
/**
 * A horizontal layout organiser.
 * 
 * Creates a table and adds its child elements to cells which run left to right.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 */
class DP_View_Hbox extends DP_View_Stateful {
	
	
	public function __construct($id) {
		parent::__construct($id);
	}
	
	/**
	 * Render the DP_View_Hbox
	 */
	public function render() {
		$output = '<table><tr>';
		$output .= $this->renderChildren();
		$output .= '</tr></table>';
		
		return $output;
	}
	
	public function renderChildren($location = DP_View::PREPEND) {
		$output = "";
		
		foreach ($this->child_views as $child) {
			if ($child['location'] == $location) {
				if ($this->getTranslator()) {
					$child['view']->setTranslator($this->getTranslator());
				}
				$output .= "<td>";
				$output .= $child['view']->render();
				$output .= "</td>";
			}
		}
		
		return $output;
	}
}
?>