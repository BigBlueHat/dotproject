<?php /* $Id: session.php 5072 2007-06-17 11:09:09Z cyberhorse $ */
require_once 'DP/Config.php';
require_once 'DP/AppUI.php';
require_once 'DP/EventQueue.php';
require_once 'DP/Query.php';
require_once 'Zend/Controller/Front.php';

/**
 * Session Handling Functions
 * Please note that these functions assume that the database
 * is accessible and that a table called 'sessions' (with a prefix
 * if necessary) exists.  It also assumes MySQL date and time
 * functions, which may make it less than easy to port to
 * other databases.  You may need to use less efficient techniques
 * to make it more generic.
 *
 * While it would be nice to use a class for this, it would appear
 * nigh-on impossible to get it to work, so back to standard functions for us!
 */

function DP_Session_Open($save_path, $session_name)
{
	return true;
}

function DP_Session_Close()
{
	return true;
}

function DP_Session_Read($id)
{
	error_log('session read: ' . $id);
	try {
		$db = DP_Config::getDB();
		$q = $db->select()
			->from('sessions', 
				array( 'session_data', 
					'session_lifespan' => new Zend_Db_Expr('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created)'),
					'session_idle' => new Zend_Db_Expr('UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated)')))
			->where('session_id = ?' , $id);
		$row = $db->fetchRow($q);
		$q->reset();
		$db->closeConnection();
		$max = DP_Session_ConvertTime('max_lifetime');
		$idle = DP_Session_ConvertTime('idle_time');
		// If the idle time or the max lifetime is exceeded, trash the
		// session.
		if ($max < $row['session_lifespan']
		 || $idle < $row['session_idle']) {
			DP_Session_Destroy($id);
			$data = '';
		} else {
			$data = $row['session_data'];
		}
	}
	catch (Exception $e) {
		error_log('Exception occurred in session read, ' . $e->getMessage());
		$data = '';
	}

	error_log('read returned ' . strlen($data) . ' bytes: '. $data);
	return $data;
}

function DP_Session_Write($id, $data)
{
	error_log('session write: ' . $id);
	// $AppUI = DP_AppUI::getInstance();
	$db = DP_Config::getDB();
	$sql = $db->select()
		->from('sessions', array('row_count' => new Zend_Db_Expr('count(*)')))
		->where('session_id = ?' , $id);

	$qid = $db->fetchOne($sql);
	$db->closeConnection();
	//clear the database connection, for some reason an open connection doesn't work with another call to the database logic.
	error_log('first pass, got count:' . $qid);
	// error_log('created new query object');
	if ( $qid > 0 ) {
		error_log('updating session');
		$sess = array(
			'session_data' => $data,
			'session_user' => 0,
			'session_id' => $id
		);
		$db->update('sessions', $sess, 'session_id = \'' . $id .'\'');
	} else {
		error_log('creating session');
		$sess = array(
			'session_id' => $id,
			'session_data' => $data,
			'session_created' => date('Y-m-d H:i:s')
		);
		$db->insert('sessions', $sess);
	}
	$db->closeConnection();
	error_log('write returned');
	return true;
}

function DP_Session_Destroy($id)
{
	error_log('session destroy');
	$q = new DP_Query;
	$q->setDelete('sessions');
	$q->addWhere("session_id = '$id'");
	$q->exec();
	$q->clear();

	if (($user_access_log_id = $last_insert_id))
	{
		$q->addTable('user_access_log');
		$q->addUpdate('date_time_out', date("Y-m-d H:i:s"));
		$q->addWhere('user_access_log_id = ' . $user_access_log_id);
		$q->exec();
		$q->clear();
	}
	error_log('detstroy returned');
	return true;
}

function DP_Session_Gc($maxlifetime)
{
	error_log('session gc');
	$now = time();
	$max = DP_Session_ConvertTime('max_lifetime');
	$idle = DP_Session_ConvertTime('idle_time');
	// Find all the session
	$q = new DP_Query;
	$q->addQuery('session_id, session_user');
	$q->addTable('sessions');
	$q->addWhere("UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_updated) > $idle OR UNIX_TIMESTAMP() - UNIX_TIMESTAMP(session_created) > $max");
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
	error_log('gc returned');
	return true;
}

function DP_Session_ConvertTime($key)
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

function DP_Session_Init($start_vars = 'AppUI')
{
	error_log('session construct');
	session_name(DP_Config::getConfig('session_name', 'dotproject'));
	
	if (ini_get('session.auto_start') > 0) {
		session_write_close();
	}
	if (strtolower(DP_Config::getConfig('session_handling')) == 'app') 
	{
		ini_set('session.save_handler', 'user');
	
		// PHP 5.2 workaround
		if (version_compare(phpversion(), '5.2.0', '>=')) {
			register_shutdown_function('session_write_close');
		}
	
		session_set_save_handler(
			'DP_Session_Open',
			'DP_Session_Close',
			'DP_Session_Read',
			'DP_Session_Write',
			'DP_Session_Destroy',
			'DP_Session_Gc');
		$max_time = DP_Session_ConvertTime('max_lifetime');
	} else {
		$max_time = 0; // Browser session only.
	}
	// Try and get the correct path to the base URL.
	preg_match('_^(https?://)([^/]+)(:0-9]+)?(/.*)?$_i', Zend_Controller_Front::getInstance()->getBaseUrl(), $url_parts);
	$cookie_dir = $url_parts[4];
	if (substr($cookie_dir, 0, 1) != '/')
		$cookie_dir = '/' . $cookie_dir;
	if (substr($cookie_dir, -1) != '/')
		$cookie_dir .= '/';
	session_set_cookie_params($max_time, $cookie_dir);
	session_start();
	if (is_array($start_vars)) {
		foreach ($start_vars as $var) {
			session_register($var);
		}
	} else if (! empty($start_vars)) {
		session_register($start_vars);
	}
}

?>
