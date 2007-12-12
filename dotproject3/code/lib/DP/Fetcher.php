<?php
/**
 * The Fetcher class deals with retrieval of files given any URI
 * 
 * @author mosen
 * @package dotProject
 * @subpackage lib
 */

/**
 * The Fetcher class deals with retrieval of files given any URI.
 *
 * Requests for HTTP downloads will be passed on to Zend_HTTP_Client, and
 * URI's will be validated by Zend_URI. Supported fetch methods will be initially
 * restricted to HTTP and FTP. A special method fetchRepositoryFile is given for fetching a file
 * from a repository given by reference.
 */
class DP_Fetcher {
	
	/**
	 * Fetch the specified file from the passed repository reference.
	 * 
	 * This method uses the repository class type to determine the fetch method. i.e 
	 * a HTTPRepository will pass the request on to the http fetch method.
	 *
	 * @param string $filename
	 * @param unknown_type $repository
	 */
	function fetchRepositoryFile($filename, $repository) {
		
	}
	
	/**
	 * Fetch a file given the URI
	 * 
	 * Method will determine the protocol to use based on the URI
	 *
	 * @param string $URI
	 */
	function fetchURI($URI) {
		
	}
	
	/**
	 * Fetch a file using the HTTP protocol
	 *
	 * This method should pass the request to Zend_HTTP_Client
	 * 
	 * @param string $URI
	 */
	function fetchHTTP($URI) {
		
	}
	
	
	/**
	 * Fetch a file using the FTP protocol
	 *
	 * @param string $URI
	 */
	function fetchFTP($URI) {
		
	}
}
?>