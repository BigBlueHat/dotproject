<?php /* RESOURCES $Id$ */

$del = dPgetParam($_POST, 'del', 0);
$obj =& new CResource;
$msg = '';

if (! $obj->bind($_POST)) {
  $AppUI->setMsg($obj->getError(), UI_MSG_ERROR);
  $AppUI->redirect();
}

require_once("./classes/CustomFields.class.php");

$AppUI->setMsg('Resource');
if ($del) {
  if (! $obj->canDelete($msg)) {
    $AppUI->setMsg($msg, UI_MSG_ERROR);
    $AppUI->redirect();
  }
  if (($msg = $obj->delete())) {
    $AppUI->setMsg($msg, UI_MSG_ERROR);
    $AppUI->redirect();
  } else {
    $AppUI->setMsg('deleted', UI_MSG_ALERT, true);
    $AppUI->redirect('', -1);
  }
} else {
  if (($msg = $obj->store())) {
    $AppUI->setMsg($msg, UI_MSG_ERROR);
  } else {
  	
  	 	$custom_fields = New CustomFields( $m, 'addedit', $obj->resource_id, "edit" );
 		$custom_fields->bind( $_POST );
 		$sql = $custom_fields->store( $obj->resource_id ); // Store Custom Fields
 		
    $AppUI->setMsg($_POST['resource_id'] ? 'updated' : 'added', UI_MSG_OK, true);
  }
  $AppUI->redirect();
}
?>
