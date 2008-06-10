<?php
interface DP_View_Notification_Interface {
	
	/**
	 * Notify the DP_View object that the Zend_View $view is about to render.
	 * 
	 * This gives all objects a last chance to inject javascript and CSS into the head section.
	 *
	 * @param Zend_View $view
	 */
	public function viewWillRender(Zend_View $view);
	
}
?>