<?php
/**
 * A box containing multiple items of information.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
class DP_View_InfoCell extends DP_View_Cell {
	/**
	 * @var array $display_keys Keys of the values to display, in order.
	 */
	protected $display_keys;
	/**
	 * @var string $title_key Key of the title item which should be a different class.
	 */
	protected $title_key;
	
	public function __construct($id) {
		parent::__construct($id);
	}
	/**
	 * Set the keys to use when rendering the cell.
	 * 
	 * The keys will be rendered in order. Except for the title key which
	 * is always rendered first.
	 * 
	 * @param array $dkeys Keys to values to be rendered.
	 */
	public function setDisplayKeys($dkeys) {
		$this->display_keys = $dkeys;
	}
	
	/**
	 * Get the key to use for the infocell title.
	 * 
	 * @return string Key of title item.
	 */
	public function titleKey() {
		return $this->title_key;
	}
	
	/**
	 * Set the key of the value to use for the cell title.
	 * 
	 * @param string $tk key of the value to use for the cell title.
	 */
	public function setTitleKey($tk) {
		$this->title_key = $tk;
	}
	
	public function render($rowhash) {
		$output = '<div class="View_InfoCell">';
		$output .= '<ul>';
		
		if ($this->titleKey()) {
			$output .= '<li class="View_InfoCell_Title">';
			$output .= $rowhash[$this->titleKey()];
			$output .= '</li>';
		}
		
		foreach ($this->display_keys as $dk) {
			if ($dk != $this->titleKey()) {
				$output .= '<li>'.$rowhash[$dk].'</li>';	
			}
		}
		
		$output .= '<ul>';
		$output .= '</div>';
		
		return $output;
	}
}
?>