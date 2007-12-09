<?php

/**
 * Modules must conform to the DP_Module_Interface in order to load
 * correctly.
 *
 * This interface provides a basic set of required functions.
 */
interface DP_Module_Interface
{
	// Functions related to user interface modifications.
	public function hasTabs($module = null);
	public function getTabs($module, $controller = null, $action = null);

	// Functions required for installable modules.
	public function canInstall();
	public function setup();
	public function remove();
	public function config();

	// Functions that are supplied in DP_Module_Abstract
	public function load($object_id=null, $strip = true);
	public function store($updateNulls = true);
	public function check();
	public function access($type);
	public function delete();
	public function search($keyword);
}

?>
