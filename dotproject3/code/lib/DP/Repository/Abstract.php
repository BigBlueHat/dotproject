<?php
/**
 * The Repository class is an abstract of the basic functionality required by all
 * repository types
 * 
 * @author mosen
 * @package dotProject
 * @subpackage lib
 */


/**
 * The Repository class is an abstract of the basic functionality required by all
 * repository types
 */
class DP_Repository_Abstract {
	
	public $URI;
	public $package_list;
	
	/**
	 * Fetch the package called $filename from the repository
	 * 
	 * Passes the request on to DP_Fetcher to download the file
	 *
	 * @param string $filename
	 */
	public function fetchPackage($filename) {
		
	}
	
	/**
	 * Stub method for searching repository resuults.
	 *
	 * @param string $keyword
	 */
	public function search($keyword) {
		
	}
}
?>