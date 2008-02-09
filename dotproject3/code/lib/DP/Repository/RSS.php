<?php
/**
 * The RSSRepository class provides listing and download functionality for RSS feeds (such as sourceforge)
 * 
 * @author mosen
 * @package dotProject
 * @subpackage lib
 */

/**
 * The DP_Repository_RSS class provides listing and download functionality for RSS feeds (such as sourceforge)
 *
 * The RSS feed is processed into a set of titles and URI's which can then be passed to DP_Fetcher
 * to get the packages from the specified HTTP server. This is primarily intended for use with sourceforge's
 * Project file listing in RSS format. Requires Zend_Feed.
 * 
 * @var $rss_title RSS feed title
 * @var $rss_link RSS feed link
 * @var $rss_description RSS feed description
 */
class DP_Repository_RSS extends DP_Repository_Abstract {
	
	public $rss_title;
	public $rss_link;
	public $rss_description;
	
	/**
	 * Constructor for DP_Repository_RSS, sets up variables.
	 *
	 * @param string $URI
	 * @return DP_Repository_RSS
	 */
	public function __construct($URI) {
		$this->package_list = null;
		$this->URI = $URI;
	}
	
	/**
	 * List packages available via this RSS feed.
	 * 
	 * Example: dotproject file releases http://sourceforge.net/export/rss2_projfiles.php?group_id=21656
	 * At the moment is specific to sourceforge
	 */
	public function listPackages() {
		
		// Import feed
		try {
    		$sourceforgeRss = Zend_Feed::import($this->URI);
		} catch (Zend_Feed_Exception $e) {
    		// feed import failed
    		//echo "Exception caught importing feed: {$e->getMessage()}\n";
    		//exit;
		}

		// Process items
		$this->package_list = Array();
		
		foreach ($sourceforgeRss as $item) {
			$real_link = $this->extractDownloadLink($item['description']);
			$this->package_list[] = Array('title'=>$item['title'],
											'description'=>$item['description'],
											'link'=>$real_link
									);
		}
		
		return $this->package_list;
		// return array	
	}
	
	/*
	 * Extract download link from a sourceforge RSS description.
	 * 
	 * The link element from the sourceforge RSS feed points to release notes, not a direct download link.
	 * This method extracts the actual link from the description element.
	 * 
	 * example sf.net string: 
	 * &#34;http://sourceforge.net/project/showfiles.php?group_id=21656&#38;release_id=554265&#34;&#62;[Download]
	 * 
	 * @param string $sf_description
	 */
	protected function extractDownloadLink($sf_description)
	{
		preg_match('http://.*release_id=[0-9]*',$sf_description, $matches);
		return $matches[0]; 
	}	
}
?>
