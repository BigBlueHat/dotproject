<?php
/**
 * TitleBlock construction helper
 * 
 * This helper helps to construct a DP_View_TitleBlock object and provide some reasonable defaults.
 * Defaults can be changed via set methods.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @todo Implement set methods
 */
class DP_Controller_Action_Helper_TitleBlock extends Zend_Controller_Action_Helper_Abstract {
	/**
	 * @var DP_View_TitleBlock $title_block An instance of the title block.
	 */
	protected $title_block;
	
	/**
	 * Default function: construct a new TitleBlock
	 * 
	 * Constructs and returns the TitleBlock. Also assigns the TitleBlock to the callers view property
	 * under the key 'titleblock'.
	 * 
	 * @param string $title The title to use
	 * @param string $icon Path to an image to use as the module icon.
	 * @return DP_View_TitleBlock Instance of TitleBlock.
	 */
	public function direct($title, $icon = '') {
		$request = $this->getRequest();
		$module = $request->getModuleName();
		
		$this->title_block = DP_View_Factory::getTitleBlockView($module.'-titleblock', $title, $icon, null, null );
		$this->getActionController()->view->titleblock = $this->title_block;
		
		return $this->title_block;
	}
}
?>