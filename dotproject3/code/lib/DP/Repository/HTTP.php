<?php
/**
 * The HTTPRepository class provides listing and download functionality for HTTP based repositories.
 * 
 * @author mosen
 * @package dotProject
 * @subpackage lib
 */

require_once('DP/Repository/Abstract.php');



/**
 * The HTTPRepository class provides listing and download functionality for HTTP based repositories.
 *
 * The HTTP repository class uses a package index to determine the filename and title of each module.
 */
class DP_Repository_HTTP extends DP_Repository_Abstract {
	
	/**
	 * List the packages available in this repository
	 * 
	 * Uses DP_Fetcher to retrieve the package listing which is then converted into an associative
	 * array and returned.
	 * 
	 * @return
	 */
	function listRepository() {
		
	}
}
?>