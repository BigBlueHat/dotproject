<?php
/**
 * Card view for contact listing.
 * 
 * Displays name or order by field as the title. with contact details underneath.
 * 
 * @package dotproject
 * @subpackage contacts
 * @version 3.0 alpha
 *
 */
class Contacts_View_Card extends DP_View_Cell implements DP_View_Notification_Interface {
	protected $display_keys;
	
	public function __construct($id) {
		parent::__construct($id);
	}
	
	/**
	 * Set the keys to use when rendering the cell.
	 * 
	 * The keys will be rendered in order. Except for the title which
	 * is always rendered first.
	 * 
	 * @param array $keys Keys to values to be rendered.
	 */
	public function setDisplayKeys($keys) {
		$this->display_keys = $keys;	
	}

	public function render($rowhash) {		
		$output = ' <div class="Contacts_View_Card">'."\n";
		$output .= '<input id="cb_'.$rowhash['contact_id'].'" name="chk-contact_id[]"
					type="checkbox"
					value="'.$rowhash['contact_id'].'" 
					class="selectionCheck"
					onClick="dpselection.toggle(this, this.parentNode)"
					>';
		$output .= "\t".'<ul>';
		
		// Print title
		$title = ($rowhash['contact_order_by'] != null) ? $rowhash['contact_order_by'] : $rowhash['contact_last_name'].', '.$rowhash['contact_first_name'];

		$output .= "\t\t" . '<li class="Contacts_View_Card_Title">';
		$output .= "\t\t" . '<a href="/contacts/view/object/id/'.$rowhash['contact_id'].'">'.$title.'</a>';
		$output .= "\t\t" . '</li>';
		
		foreach ($this->display_keys as $dk) {
			if ($rowhash[$dk] != '') {
				$output .= '<li class="Contacts_View_Card_Info">';

				$output .= $rowhash[$dk];
				$output .= '</li>'."\n";
			}
		}
			
		$output .= "\t".'</ul>';
		$output .= '</div>';
		
		return $output;
	}
	
	public function viewWillRender(Zend_View $view) {
		$view->HeadScript()->appendFile('/js/DP/View/Cell-ObjectSelect.js');
	}
}
?>