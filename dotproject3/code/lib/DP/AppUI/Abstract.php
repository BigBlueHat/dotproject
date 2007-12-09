<?php

class DP_AppUI_Abstract implements DP_AppUI_Interface
{
	public function isTabbed()
	{
		return true;
	}

	public function &tabBox(&$tpl)
	{
		return new DP_AppUI_TabBox_Abstract($tpl);
	}

	public function &titleBlock(&$tpl)
	{
		return new DP_AppUI_TitleBlock_Abstract($tpl);
	}
}

?>
