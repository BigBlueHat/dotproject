<?php
// Copyright 2005, Adam Donnison <adam@saki.com.au>
// Released under GPL version 2 or later.

// Take an XML file and restore it to the database overwriting
// all the data in the database.

$perms =& $AppUI->acl();
if (! $perms->checkModule('backup', 'edit'))
  $AppUI->redirect('m=public&a=access_denied');
$AppUI->savePlace();

// Make sure the user realises that this is a drastic operation!
$titleBlock = new CTitleBlock('Restore Database', 'companies.gif', $m, $m.'.'.$a);
$titleBlock->show();
?>
<form name="frmRestore" enctype="multipart/form-data" action="<?php echo "$baseUrl/index.php?m=backup&a=do_restore"; ?>" method="post">
<div align="center">
<table cellspacing="0" cellpadding="4" border="0" width="80%" class="std">
  <tr>
    <th align="center" colspan="2"><?php echo $AppUI->_('Restore From XML File'); ?></th>
  </tr>
  <tr>
    <td align="center" colspan="2"><?php echo $AppUI->_('xmlRestoreWarning'); ?></td>
  </tr>
  <tr>
    <td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('XML File to Restore'); ?></td>
    <td align="left"><input type="file" class="button" name="xmlfile" /></td>
  </tr>
  <tr>
    <td align="right" valign="top" nowrap="nowrap"><?php echo $AppUI->_('Continue On Error?'); ?></td>
    <td align="left"><input type="checkbox" class="button" value="1" name="continue" /></td>
  </tr>
  <tr>
    <td align="right">&nbsp;</td>
    <td align="right"><input type="submit" class="button" name="restore" value="<?php echo $AppUI->_('upload'); ?>"</td>
  </tr>
</table>
</div>
</form>
