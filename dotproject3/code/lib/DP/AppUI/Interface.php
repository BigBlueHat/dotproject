<?php

interface DP_AppUI_Interface
{
	public function isTabbed();
	public function &tabBox(&$tpl);
	public function &titleBlock(&$tpl);
}
?>
