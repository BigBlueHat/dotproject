<?php
/**
 * View containing tools which modify a selection (collection of checkboxes)
 *
 */
class DP_View_ObjectSelectTools extends DP_View_Stateful {
	/**
	 * @var $checkbox_id View ID of associated checkbox instance.
	 */
	protected $checkbox_id;
	/**
	 * @var $checkbox_name HTML name attribute of associated checkbox instance.
	 */
	protected $checkbox_name;
	/**
	 * @var $tbl_id HTML ID attribute of table containing list items to be selected.
	 */
	protected $tbl_id;
	
	/**
	 * DP_View_ObjectSelectTools constructor
	 */
	public function __construct($id, $tbl_id) {
		parent::__construct($id);
		
		$this->tbl_id = $tbl_id;
	}
	
	/**
	 * Render the interface for changing the current selection.
	 * 
	 * @return HTML Output.
	 */
	private function _renderSelectionChanger() {
		$chk_name = "";
		
		$output = "Selection:&nbsp;&nbsp;";
		$output .= '<a href="javascript:dpselection.selectAll(\''.$this->tbl_id.'\');">All</a>&nbsp;&nbsp;';
		$output .= '<a href="javascript:dpselection.selectNone(\''.$this->tbl_id.'\');">None</a>&nbsp;&nbsp;';
		$output .= '<a href="javascript:dpselection.selectInvert(\''.$this->tbl_id.'\');">Invert</a>&nbsp;&nbsp;';
		
		return $output;
	}
	
	/**
	 * Render the interface for deleting the current selection
	 * 
	 * @return HTML Output.
	 */
	private function _renderSelectionDelete() {
		$chk_name = "";
		
		$output = "";
		$output .= '<a href="javascript:deleteSelected(\''.$this->tbl_id.'\');">Delete selected</a>';
		
		return $output;
	}
	
	/**
	 * Stub: render the interface for performing some update action on the current selection.
	 * 
	 * Intended for use in companies to update the company_type of the current selection.
	 */
	private function _renderSelectionAction() {
		// stub	
	}
	
	/**
	 * Instantiate and return a checkbox cell seeded with configuration from this ObjectSelectTools view.
	 * 
	 * The cell view details are stored in this object so that it can generate JS calls which select the correct
	 * checkbox elements.
	 * 
	 * @param string $id_key ID to use for the cell view constructor.
	 * @return Instance of DP_View_Cell_ObjectSelected
	 */
	public function makeSelectCellView($id_key) {
		$this->checkbox_id = $id_key;
		$this->checkbox_name = 'chk-'.$id_key;
		
		return new DP_View_Cell_ObjectSelect($id_key);
	}
	
	/**
	 * Render the selection tools.
	 * 
	 * @return HTML Output.
	 */
	public function render() {
		// construct table
		$output = "";
		$output .= $this->_renderSelectionChanger();
		$output .= $this->_renderSelectionDelete();

		return $output;
	}
	
}
?>