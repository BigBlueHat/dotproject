<?php

/**
 * Standard observer interface.
 * 
 * This class should normally be used by DP_View objects to observe their underlying model or data
 * objects.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 */
interface DP_Observer_Interface {
	
	/**
	 * Update the state of the observer with a given subject reference.
	 * 
	 * @param DP_Observable_Interface $subject The subject which has changed its state.
	 */
	public function updateState($subject);
}

?>