<?php
/**
 * The RSSRepository class provides listing and download functionality for RSS feeds (such as sourceforge)
 * 
 * @author mosen
 * @package dotProject
 * @subpackage lib
 */

require_once('DP/Repository.php');



/**
 * The RSSRepository class provides listing and download functionality for RSS feeds (such as sourceforge)
 *
 * The RSS feed is processed into a set of titles and URI's which can then be passed to DP_Fetcher
 * to get the packages from the specified HTTP server. This is primarily intended for use with sourceforge's
 * Project file listing in RSS format.
 */
class DP_Repository_RSS extends DP_Repository {

}
?>