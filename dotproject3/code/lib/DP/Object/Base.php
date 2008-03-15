<?php
/**
 * Base object representing a single object in dotProject
 * 
 * Subclasses of this object should represent a single database row. The object class allows these
 * objects to implement standard methods for access control and search indexing. Additionally the object should
 * provide a hash for the templating system to use.
 * @package dotproject
 * @subpackage system
 * @version not.even.alpha
 * @todo Interface for templating system. 
 */
class DP_Object_Base {
	
	/**
	 * Stub method for fetching the Zend_ACL_Resource.
	 * @todo Implement stub method for Zend_ACL.
	 */
	function getAclResource() {
		
	}
	
	/**
	 * Stub method for fetching the Lucene Document.
	 * @todo Implement stub method for Zend_Lucene_Document.
	 */
	function getLuceneDocument() {
		
	}
}
?>