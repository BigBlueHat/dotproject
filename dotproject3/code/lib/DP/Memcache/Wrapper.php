<?php
/**
 * Class to interface to the Memcached php library.
 *
 * Provides a simple interface to allow data to be created/deleted
 * by using standard PHP calls to the object.
 *
 * @author Adam Donnison <adonnison@dotproject.net>
 */

class DP_Memcache_Wrapper
{
	private $_memcache_available = false;
	private $_namespace = '';
	private $_max_age = 0;
	private $_memcache_object = null;
	private $_flags = 0;

	/**
	 * Construct the object.
	 *
	 * We check on two situations, if the memcache extension is loaded
	 * and if we've chosen to use memcache in our config.
	 *
	 * @author Adam Donnison <ajdonnison@dotproject.net>
	 */
	public function __construct($namespace = '', $max_age = 0, $use_compression = null)
	{
		$this->_namespace = $namespace;
		$this->_max_age = $max_age;
		if (null === $use_compression) {
			$use_compression = DP_Config::getBaseConfig()->memcached->compress;
		}
		$this->_flags = $use_compression ? MEMCACHE_COMPRESSED : 0;

		$this->_memcache_available = DP_Config::getBaseConfig()->memcached->available;
		if ($this->_memcache_available) {
			$this->_memcache_available = extension_loaded('memcache');
		}
		if ($this->_memcache_available) {
			$this->_memcache_object = new Memcache;
			$pool = explode(';', DP_Config::getBaseConfig()->memcached->pool);
			foreach ($pool as $server) {
				$this->_memcache_object->addServer($server);
			}
		}
	}

	/**
	 * Utility function to set compression
	 */
	public function compress()
	{
		$this->_flags = MEMCACHE_COMPRESSED;
	}

	/**
	 * Utility function to set age
	 */
	public function expire($max_age = 0)
	{
		$this->_max_age = $max_age;
	}

	/**
	 * Utility function to set namespace
	 */
	public function namespace($namespace)
	{
		$this->_namespace = $namespace;
	}

	/**
	 * Utility function to get availability status
	 */
	public function available()
	{
		return $this->_memcache_available;
	}

	/**
	 * Get a value by referencing its key in the current object.
	 *
	 * This allows code to do something like $x = $memcache->x
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($this->_memcache_available) {
			return $this->_memcache_object->get($this->_namespace . $key);
		} else {
			return null;
		}
	}

	/**
	 * Set a value in the memcache
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set($key, $value)
	{
		if ($this->_memcache_available) {
			$this->_memcache_object->set($this->_namespace.$key, $data,$this->_flags, $this->_max_age);
		}
	}

	/**
	 * Return true if the value is set, false otherwise
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function __isset($key)
	{
		if ($this->_memcache_available) {
			return $this->_memcache_object->get($this->_namespace.$key) !== false;
		} else {
			return false;
		}
	}

	/**
	 * Unset (delete) a value from the memcache
	 *
	 * @param string $key
	 */
	public function __unset($key)
	{
		if ($this->_memcache_available) {
			$this->_memcache_object->delete($this->_namespace.$key);
		}
	}
}
?>
