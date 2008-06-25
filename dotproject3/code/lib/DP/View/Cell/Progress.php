<?php
class DP_View_Cell_Progress extends DP_View_Cell 
{
	public function render($rowhash) {
		$percent_complete = $rowhash[$this->value_key];
		$project_color = $rowhash['project_color_identifier'];
		$output = '<div class="view-cell-progress" style="background-color: #'.$project_color.'; padding: 1px; width: '.$percent_complete.'%;">';
		$output .= '<span style="background-color: white">'.$percent_complete.'%</span>';
		$output .= '</div>';
		
		return $output;	
	}
}
?>