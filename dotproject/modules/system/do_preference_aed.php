<?php /* SYSTEM $Id$ */
$del = isset($_POST['del']) ? $_POST['del'] : 0;

$obj = new CPreferences();
$obj->pref_user = isset($_POST['pref_user']) ? $_POST['pref_user'] : 0;

foreach ($_POST['pref_name'] as $name => $value) {
	$obj->pref_name = $name;
	$obj->pref_value = $value;

	if ($del) {
		if (($msg = $obj->delete())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		} else {
			$AppUI->setMsg( "Preferences deleted", UI_MSG_ALERT );
		}
	} else {
		if (($msg = $obj->store())) {
			$AppUI->setMsg( $msg, UI_MSG_ERROR );
		} else {
			$AppUI->setMsg( "Preferences updated", UI_MSG_OK );
			if ($obj->pref_user) {
			// if user preferences, reload them now
				$AppUI->loadPrefs( $AppUI->user_id );
				$AppUI->setUserLocale();
			}
		}
	}
}
$AppUI->redirect();
?>
