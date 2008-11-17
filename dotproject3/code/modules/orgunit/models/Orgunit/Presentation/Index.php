<?php
/**
 * Organisational Unit Index Presentation Layer.
 * 
 * The presentation layer specifies a custom list of columns to display from the datasource.
 * The query and json response will only contain these fields, and the YUI.DataSource response schema will be
 * tailored to match this presentation.
 * 
 * @package dotproject
 * @version 3.0 alpha
 * @author ebrosnan
 *
 */
class Orgunit_Presentation_Index extends DP_Presentation_Json 
{
	public function __construct()
	{
		parent::__construct(Array(), 'orgunit', 'json', 'index');
		$this->default_columns = Array("id", "name", "description", "mail");
	}
	
}
?>