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
class DP_Repository {
	
	var $URI;
	var $package_list;
	
	/**
	 * Fetch the package called $filename from the repository
	 * 
	 * Passes the request on to DP_Fetcher to download the file
	 *
	 * @param string $filename
	 */
	function fetchPackage($filename) {
		
	}
	
	/**
	 * Stub method for searching repository resuults.
	 *
	 * @param string $keyword
	 */
	function search($keyword) {
		
	}
}
?>