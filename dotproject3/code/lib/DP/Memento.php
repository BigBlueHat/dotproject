<?php
/**
 * Memento class, which implements part of the Memento pattern
 * 
 * See http://en.wikipedia.org/wiki/Memento_pattern
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 */
class DP_Memento {
	protected $state;
	
	public function __construct($state) {
		$this->state = $state;
	}
	
	public function getState() {
		return $this->state;	
	}
	
	public function setState($state) {
		$this->state = $state;
	}
}
?>