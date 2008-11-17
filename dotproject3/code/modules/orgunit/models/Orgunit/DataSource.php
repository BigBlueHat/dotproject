<?php
/**
 * Organisational Units DataSource Factory Class.
 * 
 * Initialises and returns instances of DP_DataSource configured with the relevant table.
 *
 */
class Orgunit_DataSource extends DP_DataSource
{
	public static function factory(Zend_Controller_Action $controller, $id)
	{
		switch($id) {
			case "index":
				$ou = new Orgunit_DataSource_Index($id);
				//$pres = DP_Presentation_Json::factory($controller, $id);
				$pres = new Orgunit_Presentation_Index();
				$ou->setDefaultPresentation($pres);
				
				return $ou;
				break;
			default:
				return null;
		}
	}
	
}
?>