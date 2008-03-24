<?php
/**
 * Subject interface of the standard observer pattern.
 * 
 * Used by model objects to make sure that the views are in sync with their state.
 * Also used by the observers to make changes to the state of the subject.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 */

interface DP_Observable_Interface {
	
	/**
	 * Attach an observer object.
	 * 
	 * @param object $observer The object implementing DP_Observer_Interface
	 */
	public function attach(DP_Observer_Interface $observer);
	
	/**
	 * Detach an observer object.
	 * 
	 * @param object $observer The object implementing DP_Observer_Interface
	 */
	public function detach(DP_Observer_Interface $observer);
	
	
	/**
	 * Notify observers of a state change.
	 * 
	 * Can be called explicitly to avoid re-run of queries multiple times.
	 * 
	 */
	public function notify();
}
?>