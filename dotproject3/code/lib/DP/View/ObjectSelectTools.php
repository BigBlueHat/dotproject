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
	 * @var string $obj_key Primary key of the object to be selected
	 */
	protected $obj_key;
	/**
	 * @var string $list_view_id ID of the list view containing the selection objects.
	 */
	protected $list_view_id;
	
	protected $update_objects;
	protected $update_action;
	
    static $ACTION_DELETE = 1;
	
	/**
	 * DP_View_ObjectSelectTools constructor
	 */
	public function __construct($id, $list_view_id, $obj_key) {
		parent::__construct($id);

		$this->obj_key = $obj_key;
		$this->list_view_id = $list_view_id;
		$this->update_objects = Array();
		$this->update_action = 0;
	}
	
	/**
	 * Render the interface for changing the current selection.
	 * 
	 * @return HTML Output.
	 */
	private function _renderSelectionChanger() {
		$chk_name = "";
		
		$output = "";
		$output .= '<input type="hidden" name="view_id" value="'.$this->id().'" />';
		$output .= '<button type="button" onClick="javascript:dpselection.selectAll(\''.$this->list_view_id.'\');">Select All</button>';
		$output .= '<button type="button" onClick="javascript:dpselection.selectNone(\''.$this->list_view_id.'\');">Select None</button>';
		$output .= '<button type="button" onClick="javascript:dpselection.selectInvert(\''.$this->list_view_id.'\');">Invert Selection</button>';
		
		return $output;
	}
	
	/**
	 * Render the interface for deleting the current selection
	 * 
	 * @return HTML Output.
	 */
	private function _renderSelectionDelete() {
		$chk_name = "";
		
		// TODO internationalisation
		$output = "";
		$output .= '<input type="hidden" id="select-delete" name="'.$this->id().'-delete" value="0" />';
		$output .= '<button type="button" onClick="javascript:dpselection.delete(\''.$this->list_view_id.'\');">Delete selected</button>';
		
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
	public function makeSelectCellView() {
		$this->checkbox_id = $this->obj_key;
		$this->checkbox_name = 'chk-'.$this->obj_key;
		
		return new DP_View_Cell_ObjectSelect($this->checkbox_id, 'ID', Array('width'=>'20px', 'align'=>'center'));
	}
	
	/**
	 * Check to see whether selected objects need to be updated/changed.
	 * 
	 * @return bool true if objects need to be updated
	 */
	public function objectsChanged() {
		if (count($this->update_objects) > 0) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Make the changes to the selected objects.
	 *
	 * @param Zend_Db_Table $tbl Table class of the objects to delete
	 */
	public function updateObjects(Zend_Db_Table $tbl) {
		if ($this->update_action == DP_View_ObjectSelectTools::$ACTION_DELETE) {
			foreach ($this->update_objects as $obj) {
				$where = $tbl->getAdapter()->quoteInto($this->obj_key.' = ?', $obj);
				$tbl->delete($where);
			}
		}
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
	
	/**
	 * Update the state of this view from server request variables
	 * 
	 * @param mixed $request Server request object.
	 */
	public function updateStateFromServer($request) {
		if ($request->view_id == $this->id()) {
			//Zend_Debug::dump($request->getParam($this->id().'-delete'));
			if ($request->getParam($this->id().'-delete') == '1') {
				// Delete selection
				$this->update_objects = $request->getParam($this->checkbox_name);
				$this->update_action = DP_View_ObjectSelectTools::$ACTION_DELETE;
			}
		}
		
		$this->updateChildrenFromServer($request);
	}
	
}
?>