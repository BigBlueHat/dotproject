<?php
/**
 * Originator interface for Originator objects which implement the Memento pattern
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 */
interface DP_Originator_Interface {
	
	/**
	 * Restore internal state from a memento.
	 * 
	 * @param DP_Memento $m State memento.
	 */
	public function setMemento(DP_Memento $m);
	
	/**
	 * Create a memento containing a snapshot of the current internal state.
	 * 
	 * @return DP_Memento current state memento.
	 */
	public function createMemento();
}
?>