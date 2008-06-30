<?php
/**
 * Class to provide session save interface to Zend_Session.
 * - If memcached is available, will use it instead of the database
 * Please note that these functions assume that the database
 * is accessible and that a table called 'sessions' (with a prefix
 * if necessary) exists.  It also assumes MySQL date and time
 * functions, which may make it less than easy to port to
 * other databases.  You may need to use less efficient techniques
 * to make it more generic.
 */

class DP_Session_SaveHandler implements Zend_Session_SaveHandler_Interface
{
	private $db;
	private $memcache = null;
	private $memcache_only = false;
	private $max_lifetime;
	private $max_idle;
	private $updated;
	private $start;

	public function __construct()
	{
	}

	function open($save_path, $session_name)
	{
		$this->memcache = new DP_Memcache_Wrapper('sess.');
		if ($this->memcache->available()) {
			$this->memcache_only = DP_Config::getBaseConfig()->memcached->sessions;
			if (!isset($this->memcache->session_list)) {
				$this->memcache->session_list = array();
			}
		}
		if (! $this->memcache_only) {
			$this->db = DP_Config::getDB(true);
		}
		$this->max_lifetime = $this->convertTime('max_lifetime');
		$this->max_idle = $this->convertTime('idle_time');
		return true;
	}

	function close()
	{
		return true;
	}

	/**
	 * Read from either the database (if memcache isn't available)
	 * or from memcache.
	 */
	function read($id)
	{
		$now = time();
		if ($ret = $this->memcache->$id) {
			$max = $now - $ret['start'];
			$idle = $now - $ret['updated'];
			if ($max < $this->max_lifetime && $idle < $this->max_idle) {
				return $ret['data'];
			} else {
				unset($this->memcache->session_list[$id]);
			}
		} else {
			unset($this->memcache->session_list[$id]);
		}
		if ($this->memcache_only) {
			return '';
		}
		try {
			$q = $this->db->select()
				->from('sessions', 
				array( 'session_data', 
					'session_start' => new Zend_Db_Expr('UNIX_TIMESTAMP(session_created)'),
					'session_lifespan' => new Zend_Db_Expr('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created)'),
					'session_idle' => new Zend_Db_Expr('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated)')))
				->where('session_id = ?' , $id);
			$row = $this->db->fetchRow($q);
			$q->reset();
			$this->db->closeConnection();
			// If the idle time or the max lifetime is exceeded, trash the
			// session.
			if ($this->max_lifetime < $row['session_lifespan']
			 || $this->max_idle < $row['session_idle']) {
				$this->destroy($id);
				$data = '';
			} else {
				$data = $row['session_data'];
				$this->memcache->$id = array(
					'start' => $row['session_start'],
					'updated' => $now,
					'data' => $data
				);
				@ $this->memcache->session_list[$id] = true;
			}
		}
		catch (Exception $e) {
			$data = '';
		}

		return $data;
	}

	function write($id, $data)
	{
		$now = time();
		if ($ret = $this->memcache->$id) {
			$ret['updated'] = $now;
			$ret['data'] = $data;
		} else {
			$ret = array(
				'start' => $now,
				'updated' => $now,
				'data' => $data
			);
		}
		$this->memcache->$id = $ret;
		@$this->memcache->session_list[$id] = true;
		if ($this->memcache_only) {
			return true;
		}
		
		$sql = $this->db->select()
			->from('sessions', array('row_count' => new Zend_Db_Expr('count(*)')))
			->where('session_id = ?' , $id);

		$qid = $this->db->fetchOne($sql);
		$this->db->closeConnection();
		//clear the database connection, for some reason an open connection doesn't work with another call to the database logic.
		if ( $qid > 0 ) {
			$sess = array(
				'session_data' => $data,
				'session_user' => 0,
				'session_id' => $id
			);
			$this->db->update('sessions', $sess, 'session_id = \'' . $id .'\'');
		} else {
			$sess = array(
				'session_id' => $id,
				'session_data' => $data,
				'session_created' => date('Y-m-d H:i:s')
			);
			$this->db->insert('sessions', $sess);
		}
		$this->db->closeConnection();
		return true;
	}

	function destroy($id)
	{
		$q = new DP_Query(true);

		if (($user_access_log_id = $last_insert_id))
		{
			$q->addTable('user_access_log');
			$q->addUpdate('date_time_out', date("Y-m-d H:i:s"));
			$q->addWhere('user_access_log_id = ' . $user_access_log_id);
			$q->exec();
			$q->clear();
		}

		unset($this->memcache->$id);
		unset($this->memcache->session_list[$id]);
		if ($this->memcache_only) {
			return true;
		}

		$q->setDelete('sessions');
		$q->addWhere("session_id = '$id'");
		$q->exec();
		$q->clear();
		return true;
	}

	function gc($maxlifetime)
	{
		$now = time();
		// Find all the session
		if (isset($this->memcache->session_list)) {
			foreach($this->memcache->session_list as $k => $v) {
				if ($sess = $this->memcache->$k) {
					$max = $now - $sess['start'];
					$idle = $now - $sess['updated'];
					if ($max > $this->max_lifetime || $idle > $this->max_idle) {
						unset($this->memcache->$k);
						unset($this->memcache->session_list[$k]);
						// Do the logout stuff.
					}
				} else {
					unset($this->memcache->session_list[$k]);
				}
			}
		}
		if ($this->memcache_only) {
			return true;
		}
		$q = new DP_Query(true);
		$q->addQuery('session_id, session_user');
		$q->addTable('sessions');
		$q->addWhere("UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) > {$this->max_idle} OR UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) > {$this->max_lifetime}");
		$sessions = $q->loadList();
		$q->clear();

		$session_ids = '';
		$users = '';
		if (is_array($sessions))
		{
			foreach($sessions as $session)
			{
				$session_ids .= $session['session_id'] . ',';
				$users .= $session['session_user'] . ',';
			}
		}

		if (!empty($users))
		{
			$users = substr($users, 0, -1);
		
			$q->clear();
			$q->addTable('user_access_log');
			$q->addUpdate('date_time_out', date("Y-m-d H:i:s"));
			$q->addWhere('user_access_log_id IN (' . $users . ')');
			$q->exec();
			$q->clear();
		}

		if (!empty($session_ids))
		{
			$session_ids = substr($session_ids, 0, -1);
			$q->setDelete('sessions');
			$q->addWhere('session_id in (\'' . $session_ids . '\')');
			$q->exec();
			$q->clear();
		}
		if (DP_Config::getConfig('session_gc_scan_queue')) {
			DP_EventQueue::scan();
		}
		return true;
	}

	function convertTime($key)
	{
		$key = 'session_' . $key;

		// If the value isn't set, then default to 1 day.
		$config = DP_Config::getInstance();
		if (! $config->$key ) {
			return 86400;
		}

		$numpart = (int) $config->$key;
		$modifier = substr($config->$key, -1);
		if (! is_numeric($modifier)) {
			switch ($modifier) {
				case 'h':
					$numpart *= 3600;
					break;
				case 'd':
					$numpart *= 86400;
					break;
				case 'm':
					$numpart *= (86400 * 30);
					break;
				case 'y':
					$numpart *= (86400 * 365);
					break;
			}
		}
		return $numpart;
	}

}
?>
