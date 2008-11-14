<?php
/**
 * Provides an abstract list which is modifiable by filters such as keywords, paging, etc.
 * 
 * Replaces DP_List_Dynamic
 *
 */
class DP_DataSource
{
	/**
	 * @var Zend_Db_Table $tbl Table gateway to underlying SQL table.
	 */
	protected $tbl;
	
	/**
	 * @var Zend_Db_Rowset $result_cache Cache of returned rows.
	 */
	protected $result_cache;
	
	/**
	 * DP_DataSource constructor.
	 */
	public function __construct()
	{
		
	}
	
	/**
	 * Make column definitions using the table metadata extracted by Zend_Db_Table.
	 */
	public function makeColsFromMetadata()
	{
		return new DP_YUI_ColumnDefs($this->tbl);
	}
}
?>