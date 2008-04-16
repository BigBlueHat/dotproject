<?php
/**
 * Provides a select list to choose from which will generate a filter rule.
 *
 * Renders a select list for a specified field. Picking an item will add a filter rule.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 * @todo Consider design for generating the valid options list.
 */
class DP_View_SelectFilter extends DP_View implements DP_Observer_Interface {
	/**
	 * @var array $optionhash Hash of available options.
	 */
	protected $optionhash;
	/**
	 * @var string $label Label to be displayed next to the select list.
	 */
	protected $label;
	/**
	 * Constructor method
	 * 
	 * @param string $id Unique identifier string for this view.
	 * @param array $optionhash Hash of available options.
	 * @param string $label Label to add beside the select element.
	 * @return DP_View_SelectFilter Instance of DP_View_SelectFilter 
	 */
	public function __construct($id, $optionhash, $label) {
		parent::__construct($id);
		
		$this->optionhash = $optionhash;
		$this->label = $label;
		
		$this->width = "100%";
	}
	
	/**
	 * Render this view to HTML
	 * 
	 * @return string HTML output
	 */
	public function render() {
		$output = '<div id="'.$this->id().'">';
		
		$output .= $this->label.':&nbsp;';
		
		$output .= '<select>';
		foreach ($this->optionhash as $value => $text) {
			$output .= '<option value="'.$value.'">'.$text.'</option>\n';
		}
		
		$output .= '</select>';
		$output .= '</div>';
		return $output;
	}

	// From DP_Observer_Interface
	
	/**
	 * Update the state of the observer with a given subject reference.
	 * 
	 * @param DP_Observable_Interface $subject The subject which has changed its state.
	 */
	public function updateState($subject) {
		
	}
}
?>