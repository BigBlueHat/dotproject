<?php
// Copyright 2005, Adam Donnison <adam@saki.com.au>
// Released under GPL version 2 or later

// Restores an XML file.
$perms =& $AppUI->acl();
if (! $perms->checkModule('backup', 'edit'))
  $AppUI->redirect('m=public&a=access_denied');

// Try restoring the XML file.

if (! isset($_FILES['xmlfile'])) {
  $AppUI->setMsg('No upload file', UI_MSG_ERR);
  $AppUI->redirect();
}

$upload_tmp_file = $_FILES['xmlfile']['tmp_name'];
$continue = dPgetParam($_POST, 'continue', false);

require_once $baseDir . '/lib/adodb/adodb-xmlschema.inc.php';
$schema = new adoSchema($GLOBALS['db']);
$schema->setUpgradeMethod('REPLACE');
$schema->ContinueOnError(true);
if (($sql = $schema->ParseSchemaFile($upload_tmp_file)) == false) {
  $AppUI->setMsg('Error in parsing XML file', UI_MSG_ERR);
  $AppUI->redirect();
}

$result = 0;
$AppUI->setMsg('');
$errs = array();
echo '<pre>' . "\n";
foreach ($sql as $query) {
  if ( $db->Execute($query)) {
    if (! $result)
      $result = 2;
  } else {
    echo 'Error in Query: ' . $query . "\n";
    echo 'Error: ' . $db->ErrorMsg() . "\n";
    if (! $continue) {
      $result = 0;
      break;
    } else {
      $result = 1;
    }
  }
}
echo "</pre>\n";

switch ($result) {
  case 0:
    echo "<B>" . $AppUI->_('Failed to restore backup') . "</b>";
    break;
  case 1:
    echo "<B>" . $AppUI->_('Backup restored, but with errors') . "</b>";
    break;
  case 2:
    echo "<B>" . $AppUI->_('Backup Restored OK') . "</b>";
    break;
}
echo '<br/><b>' . $AppUI->_('xmlLoginMsg') . '</b>';
exit;
?>
