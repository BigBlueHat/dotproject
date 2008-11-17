<?php
class Orgunit_DataSource_Index extends DP_DataSource 
{
	public function __construct($id)
	{
		
		parent::__construct($id);
		$this->tbl = new Db_Table_Orgunits();
	}
	
}
?>