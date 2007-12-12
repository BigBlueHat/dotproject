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
	 * DP_Fetcher constructor
	 * 
	 * Set up initial download path.
	 *
	 * @return DP_Fetcher
	 */
	function DP_Fetcher()
	{
		$this->target_path = DP_BASE_DIR . '/temp';
		// TODO - make this a real default
	}
	
	/**
	 * Save a HTTP response to a file.
	 *
	 * @param string $data The HTTP response body
	 */
	function _saveToFile($data)
	{
		$file_name = 'test.tar.gz';
		$path = $this->target_path.'/'.$file_name;
		
		$fd = fopen($path, 'w');
		fwrite($fd, $data);
		return $path;
	}
	
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
	function fetchURI($uri) {
		
		$uriobj = Zend_Uri::factory($uri);
		
		if ($uriobj instanceof Zend_Uri)
		{
			$scheme = $uriobj->getScheme();
			
			switch($scheme) {
				case 'http':
					return $this->fetchHTTP($uriobj->getUri());
				case 'ftp':
					return $this->fetchFTP($uriobj->getUri());
				default:
					// TODO - scheme not recognised
			}
		}
		else
		{
			// TODO - throw invalid uri exception
			throw new Exception('Invalid URI Error');
		}
	}
	
	/**
	 * Fetch a file using the HTTP protocol
	 *
	 * HTTP protocol is handled through the Zend_Http_Client class.
	 * Downloads a file from HTTP and saves it to $target_path
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
			throw new Exception('Error while Fetching via HTTP:'.$response->getStatus().' '.$response->getMessage.'\n');
		}
		else {
			$responsedata = $response->getBody();
			$savedlocation = $this->_saveToFile($responsedata);

			return $savedlocation;
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