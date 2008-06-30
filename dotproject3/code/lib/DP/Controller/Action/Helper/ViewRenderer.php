<?php
/**
 * Extension of Zend_Controller_Action_Helper_ViewRenderer
 * to provide a stable helper path for DP specific helpers.
 *
 * @project  dotproject
 * @subproject system
 */
class DP_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_ViewRenderer
{

	/**
	 * Initialisation function.
	 */
	public function init()
	{
		parent::init();
		$this->view->addHelperPath(DP_BASE_CODE . '/lib/DP/View/Helper', 'DP_View_Helper_');
	}
}

?>
