<?php
/**
 * The Fetcher class deals with retrieval of files given any URI
 * 
 * @author mosen
 * @package dotProject
 * @subpackage lib
 */
require_once 'Zend/Uri.php';
require_once 'Zend/Http/Client.php';

/**
 * The Fetcher class deals with retrieval of files given any URI.
 *
 * Requests for HTTP downloads will be passed on to Zend_HTTP_Client, and
 * URI's will be validated by Zend_URI. Supported fetch methods will be initially
 * restricted to HTTP and FTP. A special method fetchRepositoryFile is given for fetching a file
 * from a repository given by reference.
 */
class DP_Fetcher {
	
	var $target_path;
	
	/**
	 * Fetch the specified file from the passed repository reference.
	 * 
	 * This method uses the repository class type to determine the fetch method. i.e 
	 * a HTTPRepository will pass the request on to the http fetch method.
	 *
	 * @param string $filename
	 * @param DP_Repository $repository
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
		
		$uri = Zend_Uri::factory($URI);
		
		if ($uri instanceof Zend_Uri)
		{
			$scheme = $uri->getScheme();
		
			switch($scheme) {
				case 'http':
					$this->fetchHTTP($URI->getUri());
					break;
				case 'ftp':
					$this->fetchFTP($URI->getUri);
					break;
			}
		}
		else
		{
			// TODO - throw invalid uri exception
		}
	}
	
	/**
	 * Fetch a file using the HTTP protocol
	 *
	 * HTTP protocol is handled through the Zend_Http_Client class.
	 * 
	 * @param string $URI The HTTP file location
	 * @param array $GET Associative array of GET variables
	 * @param array $POST Associative array of POST variables
	 */
	function fetchHTTP($URI, $GET = Array(), $POST = Array()) {
		$client = new Zend_Http_Client();
		$client->setUri($URI);
		$client->setConfig(array(
    		'maxredirects' => 0,
    		'timeout'      => 30));
		
		$response = $client->request();
		
		if ($response->isError()) {
			// TODO - throw http error exception
		}
		else {
			$responsedata = $response->getBody();
			return $responsedata;			
		}
	}
	
	
	/**
	 * Fetch a file using the FTP protocol
	 *
	 * @param Zend_Uri $URI
	 */
	function fetchFTP($URI) {
		
	}
}
?>