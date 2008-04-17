<?php 
/**
 *	@package dotProject
 *	@subpackage companies
 *	@version 3.0 alpha
*/

/**
 *	Companies Class
 *	@todo Move the 'address' fields to a generic table
 */
class DP_Module_Companies implements DP_Module_Configuration_Interface, DP_Module_Installation_Interface {
	

 // From DP_Module_Configuration_Interface

	public function getConfigDefault($section, $name) {
		
	}

	public function getConfigDefaults() {
		
	}
	
	public function upgradeConfig($from_version = 0) {
		
	}
	
 // From DP_Module_Installation_Interface
 
	public function upgrade($version_major = 0, $version_minor = 0) {
		
	}
	
	public function install() {
		
	}

	public function remove() {
		
	}
}
?>
