<?php

/**
 * Class based approach to overriding various components in the
 * class.  This MUST be supplied by each style.
 */

require_once 'DP/AppUI/Interface.php';
require_once dirname(__FILE__).'/tabBox.php';
require_once dirname(__FILE__).'/titleBlock.php';

class DP_AppUI_Default implements DP_AppUI_Interface
{
	public function isTabbed()
	{
		return true;
	}

	public function &tabBox(&$tpl)
	{
		return new Style_Default_TabBox($tpl);
	}

	public function &titleBlock(&$tpl)
	{
		return new Style_Default_TitleBlock($tpl);
	}

}
?>
