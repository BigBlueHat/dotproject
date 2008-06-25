<?php
/**
 * Object link with a shaded background determined by a value key.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 * @author ebrosnan
 *
 */
class DP_View_Cell_ObjectLink_Shaded extends DP_View_Cell_ObjectLink
{
	protected $color_key;
	
	public function __construct($id_key, $name_key, $hrefprefix, $color_key = null, $column_title = '(Untitled)') {
		parent::__construct($id_key, $name_key, $hrefprefix, $column_title);
		$this->color_key = $color_key;
	}
	
	private function _rgbToLuminance($r, $g, $b)
	{
		$hsl = Array();
		$hsl['r'] = $r / 255;
		$hsl['g'] = $g / 255;
		$hsl['b'] = $b / 255;
		
		sort($hsl);
		$max_color = $hsl[count($hsl) - 1];
		$min_color = $hsl[0];
		
		$l = ($max_color + $min_color) / 2;
		
		return $l;
	}
	
	public function render($rowhash) {
		$shade_color = $rowhash[$this->color_key];
		$r = hexdec(substr($shade_color, 0, 2));
		$g = hexdec(substr($shade_color, 2, 2));
		$b = hexdec(substr($shade_color, 4, 2));
		
		$l = $this->_rgbToLuminance($r, $g, $b);
		
		$style = 'background-color: #'.$shade_color.';';
		$this->style = $style;
		
		$fore = ($l < 0.5) ? '#ffffff' : '#000000';
		$a_style .= 'color: '.$fore.';';		
		
		$output = '';
		
		//$output = '<a>';
		$output .= '<a href="'.$this->hrefprefix.$rowhash[$this->id_key].'" style="'.$a_style.'">';
		// 
		$output .= $rowhash[$this->name_key];
		$output .= '</a>';
		//$output .= $shade_color;
		return $output;		
	}
}
?>