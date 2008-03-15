<?php
/**
 * View that will receive changes in state.
 * 
 * Views that inherit this class can interpret POST or GET variables
 * and change their internal representation accordingly. Saving the state
 * of the view is also supported through the DP_View_Stateful::saveState() method.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 */
class DP_View_Stateful extends DP_View {
	
	function __construct($id) {
		parent::__construct($id);
	}
	
	/**
	 * Save the state of the view to a session variable.
	 * 
	 * The state of this object will be saved to a session variable with the same ID.
	 * @todo Implement this stub method
	 */
	public function saveState() {
		
	}
	
	/**
	 * Update child views with server request object.
	 * 
	 * If the child objects are capable of handling request variables (Instances of DP_View_Stateful).
	 * The server request variables will be passed on.
	 * 
	 * @param mixed $request Server request object
	 */
	protected function updateChildrenFromServer($request) {
		foreach ($this->child_views as $child) {
			if ($child instanceof DP_View_Stateful) {
				$child->updateStateFromServer($request); 
			}
		}
	}
	
	/**
	 * Handle any POST or GET requests.
	 * 
	 * This method tries to access the object's variables in the server request object.
	 * If it finds relevant variables, it will update this object and the changes will be reflected
	 * when the view is rendered. The updates can be saved in the session by calling DP_View_Stateful::saveState()
	 * 
	 * @param mixed $request Server request object.
	 */
	public function updateStateFromServer($request) {
		$this->updateChildrenFromServer($request);
	}
}
?>