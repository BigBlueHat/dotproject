<?php
/**
 * The SearchFilter view displays a text box and a button for text searching
 * 
 * DP_View_SearchFilter supports searching by substring and accepts a default field to search
 * (usually an object name).
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 */
class DP_View_SearchFilter extends DP_View_Stateful {
	/**
	 * @var DP_Filter $filter Filter object containing rules.
	 */
	protected $filter;
	/**
	 * @var string $form_reset_var Name of the form variable that indicates this filter should be reset.
	 */
	protected $form_reset_var;
	/**
	 * @var string $form_input_text Name of the form variable containing the search text.
	 */
	protected $form_input_text;
	/**
	 * @var string $field_title Human readable title of the field being searched.
	 */
	protected $field_title;
	/**
	 * @var string $field_name Name of the field to apply filter rules to.
	 */
	protected $field_name;
	
	/**
	 * DP_View_SearchFilter constructor
	 * 
	 * @param string $id identifier for this SearchFilter view.
	 * @param DP_Filter $filter current state of this filter.
	 * @return Instance of DP_View_SearchFilter
	 */
	public function __construct($id) {
		parent::__construct($id);
		
		// Manage DP_Filter state
		$AppUI = DP_AppUI::getInstance();
		$this->filter = new DP_Filter($id.'-filter');

		$filter_state = $AppUI->getState($id.'-filter');
		if ($filter_state != null) {
			$this->filter->setMemento($filter_state);
		}
		
		//$this->width = "100%";
		$this->form_reset_var = $this->id().'-reset';
		$this->form_input_text = $this->id().'-input';
		// TODO - this shouldnt be hardcoded
		$this->field_name = 'c.company_name';
	}
	
	/**
	 * Access the internal filter object.
	 * 
	 * @return DP_Filter the filter produced by this searchfilter view
	 */
	public function getFilter() {
		return $this->filter;
	}
	
	public function getSearchFieldTitle() {
		return $this->field_title;
	}
	
	public function setSearchFieldTitle($field_title) {
		$this->field_title = $field_title;
	}
	
	/**
	 * Render this view to HTML
	 * 
	 * @return string HTML output
	 * @todo Use a small template for this output.
	 */
	public function render() {
		$search_filter = $this->filter->getFilter($this->field_name);
		$search_text = $search_filter['field_value'];
		$output = '<div class="View_SearchFilter">';
		$output .= '<form method="POST" action="'.$this->getActionUrl().'">';
		
		$output .= '<b>'.$this->getSearchFieldTitle().'</b> contains: ';
		
		$existing_filters = $this->filter->getFilters($this->field_name);
		
		if (count($existing_filters) > 0) {
			foreach ($existing_filters as $i => $rule) {
				$output .= $rule['field_value'].' <a href="?view_id='.$this->id().'&delete_filter='.$rule['filter_id'].'">(del)</a> AND ';	
			}
		}
		
		$output .= '<input type="text" name="'.$this->form_input_text.'" size="25" value="" ';
		//$output .= ($search_text != '') ? 'disabled' : '';
		$output .= '/>';

		$output .= '<input type="hidden" name="'.$this->id().'" value="companieslistview">';
		$output .= '<input type="hidden" name="'.$this->form_reset_var.'" id="'.$this->form_reset_var.'" value="0">';
		$output .= '<input type="button" value="reset" onClick="document.getElementById(\''.$this->form_reset_var.'\').value = 1;this.form.submit();" />';
		$output .= '<input type="submit" value="search" />';
		$output .= '</form>';
		$output .= '</div>';
		
		return $output;
	}
	/**
	 * Reset the state of the SearchFilter
	 */
	public function reset() {
	
	}
	
	// Methods inherited from DP_View_Stateful
	public function saveState() {
		
	}
	
	/**
	 * Update the filter from server request variables.
	 */
	public function updateStateFromServer($request) {
		// Clear filters if requested
		$reset_this = $this->form_reset_var;
		$filter_state = null; 
		
		if ($request->view_id == $this->id()) {
			if ($request->delete_filter) {
				$this->filter->deleteFilterById($request->delete_filter);
				$filter_state = $this->filter->createMemento();
			}
		}
		
		if ($request->$reset_this) {
			$this->filter->deleteAllRules();
			$filter_state = $this->filter->createMemento();
		}
		
		// Add search string if requested
		$input_text = $this->form_input_text;
		
		if ($request->$input_text != '') {
			$this->filter->fieldSubstring('c.company_name', $request->$input_text);
			$filter_state = $this->filter->createMemento();
		}
		
		// Save state changes
		// TODO - instead of calling createMemento multiple times just call if changed.
		if ($filter_state != null) {
			$AppUI = DP_AppUI::getInstance();
			$AppUI->setState($this->id().'-filter', $filter_state);
		}
	}
	

}
?>