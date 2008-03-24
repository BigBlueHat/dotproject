<?php
/**
 * Class for iterating query results.
 * 
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 */
class DP_Iterator_DPQuery extends DP_Iterator {

	public function __construct(DP_Query $q) {
		parent::__construct($q);
	}
}
?>