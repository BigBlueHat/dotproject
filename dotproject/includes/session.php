<?php /* $Id$ */
##
## Session Handling Functions
##
/*
* Please note that these functions assume that the database
* is accessible and that a table called 'sessions' (with a prefix
* if necessary) exists.  It also assumes MySQL date and time
* functions, which may make it less than easy to port to
* other databases.  You may need to use less efficient techniques
* to make it more generic.
*
* NOTE: index.php and fileviewer.php MUST call dPsessionStart
* instead of trying to set their own sessions.
*/

require_once dirname(__FILE__) . '/main_functions.php';
require_once dirname(__FILE__) . '/db_adodb.php';
require_once dirname(__FILE__) . '/db_connect.php';
require_once dirname(__FILE__) . '/../classes/query.class.php';

function dPsessionOpen($save_path, $session_name)
{
	return true;
}

function dPsessionClose()
{
	return true;
}

function dPsessionRead($id)
{
	$q  = new DBQuery;
	$q->addTable('sessions');
	$q->addQuery('session_data');
	$q->addQuery('UNIX_TIMESTAMP(now() - session_created) as session_lifespan');
	$q->addQuery('UNIX_TIMESTAMP(now() - session_updated) as session_idle');
	$q->addWhere("session_id = '$id'");
	$qid =& $q->exec();
	if (! $qid ) {
		$data =  "";
	} else {
		$max = dPsessionConvertTime('max_lifetime');
		$idle = dPsessionConvertTime('idle_time');
		// If the idle time or the max lifetime is exceeded, trash the
		// session.
		if ($max < $qid->fields['session_lifespan']
		 || $idle < $qid->fields['session_idle']) {
			dPsessionDestroy($id);
			$data = '';
		} else {
			$data = $qid->fields['session_data'];
		}
	}
	$q->clear();
	return $data;
}

function dPsessionWrite($id, $data)
{
	$q = new DBQuery;
	$q->addQuery('count(*) as row_count');
	$q->addTable('sessions');
	$q->addWhere("session_id = '$id'");

	if ( $qid =& $q->exec() && $qid->fields['row_count'] > 0) {
		$q->query = null;
		$q->addUpdate('session_data', $data);
	} else {
		$q->query = null;
		$q->where = null;
		$q->addInsert('session_id', $id);
		$q->addInsert('session_data', $data);
		$q->addInsert('session_created', date('Y-m-d H:i:s'));
	}
	$q->exec();
	$q->clear();
	return true;
}

function dPsessionDestroy($id)
{
	$q = new DBQuery;
	$q->setDelete('sessions');
	$q->addWhere("session_id = '$id'");
	$q->exec();
	$q->clear();
	return true;
}

function dPsessionGC($maxlifetime)
{
	$now = time();
	$max = dPsessionConvertTime('max_lifetime');
	$idle = dPsessionConvertTime('idle_time');
	// Find all the session
	$q = new DBQuery;
	$q->setDelete('sessions');
	$q->addWhere("now() - session_updated > FROM_UNIXTIME($idle) OR now() - session_created > FROM_UNIXTIME($max)");
	$q->exec();
	$q->clear();
	return true;
}

function dPsessionConvertTime($key)
{
	global $dPconfig;
	$key = 'session_' . $key;

	$numpart = (int) $dPconfig[$key];
	$modifier = substr($dPconfig[$key], -1);
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

function dpSessionStart($start_vars = 'AppUI')
{
	ini_set('session.save_handler', 'user');
	session_name('dotproject');
	if (ini_get('session.auto_start') > 0) {
		session_write_close();
	}
	session_set_save_handler('dPsessionOpen', 'dPsessionClose', 'dPsessionRead', 'dPsessionWrite', 'dPsessionDestroy', 'dPsessionGC');
	$max_time = dPsessionConvertTime('max_lifetime');
	$cookie_dir = dirname($_SERVER['SCRIPT_NAME']);
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

// vi:ai sw=2 ts=2:
// vim600:ai sw=2 ts=2 fdm=marker:
?>
