<?php
/**
 * Exception for Policy errors
 *
 */
class DP_Policy_Exception extends Exception
{
	protected $op = null;

	public function __construct($message, $op)
	{
		$this->op = $op;
		parent::__construct($message);
	}

	public function getOperation()
	{
		return $this->op;
	}
}
?>
