<?php
/**
 * A person. Can be a user of the system or only a contact.
 * 
 * 
 *
 */
class DP_Person {
	protected $person_attributes;
	protected $person_membership;
	
	public function __construct()
	{
		
	}
	
	public function __get($name)
	{	
		if (array_key_exists($name, $this->person_attributes)) {
			return $this->person_attributes[$name];
		} else {
			return null;
		}
	}
	
	/**
	 * Set the attributes of this person.
	 *
	 * @param array $person_attributes
	 */
	public function setAttributes($person_attributes)
	{
		$this->person_attributes = $person_attributes;
	}
	
	
	/**
	 * Factory method to instantiate a DP_Person.
	 * 
	 * The factory method should load the DP_Person data from the relevant
	 * data source (LDAP or DbTable or other).
	 *
	 * @param unknown_type $identity
	 */
	static function factory($identity)
	{
		$p = new DP_Person();
		$db = DP_Config::getDB();
		Zend_Db_Table::setDefaultAdapter($db);
		
		$tbl = new DP_Db_Table_People();
		
		$select = $tbl->select()->where('uid = ?', $identity);
		$result = $tbl->fetchRow($select);
		
		$p->setAttributes($result->toArray());
		
		return $p;
	}
}
?>