<?php
/**
 * The archive class deals with the compression/decompression of archives
 * 
 * @author mosen
 * @package dotproject
 * @subpackage lib
 */
require_once('Archive/Tar.php');

/**
 * The archive class deals with the compression/decompression of archives.
 * 
 * @author mosen
 */
class DP_Archive {
	
	var $bla;
	
	function DP_Archive($tarname)
	{
		
	}
	
	/**
	 * Stub method for expansion of all archives
	 *
	 * @param unknown_type $sourcepath
	 * @param unknown_type $destpath
	 */
	function expand($sourcepath = '', $destpath = ''){
		$a = new Archive_Tar();
	}
	
	
}
?>