<?php /* SYSTEM $Id$ */
if (!$canEdit) {
    $AppUI->redirect( "m=public&a=access_denied" );
}

$obj = new CConfig();

// set all checkboxes to false
// overwrite the true/enabled/checked checkboxes later

$q  = new DBQuery;
$q->addTable('config');
$q->addUpdate('config_value', 'false');
$q->addWhere("config_type='checkbox'");
$rs = $q->exec();

foreach ($_POST['dPcfg'] as $name => $value) {
	$obj->config_name = $name;
    $obj->config_value = (get_magic_quotes_gpc() ? $value : addslashes( $value ));
    
    // get the group and type from hidden fields to preserver their values
    // previous setup caused group and type to be lost.
    $obj->config_group = $_POST["{$name}___group"];
    $obj->config_type = $_POST["{$name}___type"];
    
	// grab the appropriate id for the object in order to ensure
	// that the db is updated well (config_name must be unique)
	$obj->config_id = $_POST['dPcfgId'][$name];
    
	// prepare (and translate) the module name ready for the suffix
	$AppUI->setMsg( 'System Configuration' );
	if (($msg = $obj->store())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "updated", UI_MSG_OK, true );
	}
}
$AppUI->redirect();
?>
