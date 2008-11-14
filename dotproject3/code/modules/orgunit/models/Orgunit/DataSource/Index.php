<?php
class Orgunit_DataSource_Index extends DP_DataSource 
{
	public function __construct()
	{
		
		parent::__construct();
		$this->tbl = new Db_Table_Orgunits();
	}
	
}
?>