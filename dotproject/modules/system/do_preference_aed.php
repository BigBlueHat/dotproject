<?php /* SYSTEM $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$del = dPgetParam($_POST, 'del', 0);

$obj = new CPreferences();
$obj->pref_user = dPgetParam($_POST, 'pref_user', 0);

// reset checkboxes
// checked checkboxes will be set to true later again
$q  = new DBQuery;
$q->addTable('user_preferences');
$q->addUpdate('pref_value', 'false');
$q->addWhere('pref_type="checkbox"');
$q->addWhere('pref_user = '.$obj->pref_user);
$rs = $q->exec();

$update = true;
foreach ($_POST['pref_name'] as $name => $value) {
	$obj->pref_name = $name;
	$obj->pref_value = $value;
	$obj->pref_group = $_POST['pref_group'][$name];
	$obj->pref_type = $_POST['pref_type'][$name];
	// prepare (and translate) the module name ready for the suffix
	$AppUI->setMsg( 'Preferences' );
	if ($del) {
		if (($msg = $obj->delete())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		} else {
			$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		}
		$update = false;
	} else {
		if (($msg = $obj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
			$update = false;
		}
	}
}

if ($update)
{
	if ($obj->pref_user == $AppUI->user_id) {
	// if user preferences, reload them now
		$AppUI->loadPrefs( $AppUI->user_id );
		$AppUI->setUserLocale();
		include_once DP_BASE_DIR . '/locales/' . $AppUI->user_locale . '/locales.php');
		include DP_BASE_DIR . '/locales/core.php';
		$AppUI->setMsg('Preferences');
	}
	
	$AppUI->setMsg('updated', UI_MSG_OK, true);
}

$AppUI->redirect();
?>