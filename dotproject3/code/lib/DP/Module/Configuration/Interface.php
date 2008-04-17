<?php
/**
 * Interface for modules which are configurable.
 * 
 * When DP_Config performs a config reset action it deletes all configuration items associated with
 * a particular module name and then re-inserts them using the hash from DP_Module_Configuration_Interface::getConfigDefaults()
 * 
 * @package dotproject
 * @subpackage system
 * @version 3.0 alpha
 */
interface DP_Module_Configuration_Interface {
	
	/**
	 * Get the default value for one configuration item.
	 * 
	 * @param string $section Name of the configuration section.
	 * @param string $name Name of the configuration item.
	 * @return string Default value.
	 */
	public function getConfigDefault($section, $name);
	
	/**
	 * Get all of the configuration items with their default values.
	 * 
	 * @return Array hash in the format array('section_name'=>array('config_column'=>'column_value'))
	 */
	public function getConfigDefaults();
	
	/**
	 * Upgrade existing configuration from a specified config version.
	 * 
	 * Configuration version is stored inside the config with the reserved key of '__config_version'
	 * 
	 * @param float $from_version Version to upgrade from.
	 * @return float Version upgraded to or null with exception if not upgraded.
	 */
	public function upgradeConfig($from_version = 0);
}
?>