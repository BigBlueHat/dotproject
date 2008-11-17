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
	 * @var mixed $id Datasource identifier
	 */
	protected $id;
	
	/**
	 * @var Zend_Db_Table $tbl Table gateway to underlying SQL table.
	 */
	protected $tbl;
	
	/**
	 * @var Zend_Db_Rowset $result_cache Cache of returned rows.
	 */
	protected $result_cache;
	
	/**
	 * @var DP_Presentation $default_presentation The default presentation layer to use.
	 */
	protected $default_presentation;
	
	/**
	 * DP_DataSource constructor.
	 */
	public function __construct($id)
	{
		$this->id = $id;
	}
	
	/**
	 * Make column definitions using the table metadata extracted by Zend_Db_Table.
	 * 
	 * @return DP_Datasource_Columns instance of columns object.
	 */
	public function makeColsFromMetadata()
	{
		return new DP_Datasource_Columns($this->tbl);
	}
	
	// TEMP TODO: use an array presentation to fetch rows as array.
	public function fetchAll()
	{
		return $this->tbl->fetchAll();
	}
	
	public function setDefaultPresentation(DP_Presentation $pres)
	{
		$this->default_presentation = $pres;
	}
	
	/**
	 * Get the default presentation for this datasource.
	 * 
	 * The default presentation is set by the datasource factory, and usually determined on a per-module basis.
	 * 
	 * @return DP_Presentation Instance of a DP_Presentation Object
	 */
	public function getDefaultPresentation()
	{
		return $this->default_presentation;
	}
	/**
	 * Fetch all rows using the given presentation object.
	 * 
	 * @param DP_Presentation $pres Instance of a DP_Presentation object.
	 */
	public function fetch($pres = null)
	{
		if ($pres == null) {	
			if ($pres == null && $this->default_presentation != null) {
				// Fetch using default layer
				$fields = $this->default_presentation->getDefaultFields();
				
				$select = $this->tbl->select();
				$select->from($this->tbl, $fields);
				
				$rows = $this->tbl->fetchAll($select);
				
				return $this->default_presentation->translate($rows);
			} else {
				// No layer available.
			}		
		} else {
			// Fech using specified layer
		}
	}
	
	// Static Methods
	
	/**
	 * Retrieve an instance of a DP_DataSource by its module-local identifier
	 * 
	 * @param mixed $id Identifier of the requested datasource.
	 * @return DP_DataSource Instance of a DP_DataSource object.
	 */
	public static function factory($id)
	{
		return new DP_DataSource($id);	
	}
}
?>