<?php
/**
 * Interface for modules which are installable.
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 *
 */
interface DP_Module_Installation_Interface 
{
	/**
	 * Upgrade this module from a previous version.
	 * 
	 * @param integer $version_major The major revision of this module to upgrade from.
	 * @param integer $version_minor The minor revision of this module to upgrade from.
	 */
	public function upgrade($version_major = 0, $version_minor = 0);
	
	/**
	 * Install this module.
	 * 
	 * Calls upgradeFrom(0,0)
	 * 
	 */
	public function install();
	
	/**
	 * Remove this module.
	 */
	public function remove();
	
	/**
	 * Get the module version.
	 * 
	 * @return float Module version.
	 */
	public function version();
}
?>