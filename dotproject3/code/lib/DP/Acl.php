<?php
/**
 * Stub class to allow code using dPacl to still work, basically
 * turns off all access control.
 * We should replace this with an extension of the Zend_Acl class.
 */

class DP_Acl
{
	public function __construct()
	{
	}

	// Basically return true for anything we haven't yet coded.
	public function __call($method, $args)
	{
		error_log('DP_Acl::'.$method.'()');
		switch ($method) {
			case 'getAllowedItems':
			case 'getDeniedItems':
				return array();
				break;
			default:
				return true;
				break;
		}
	}
}


?>
