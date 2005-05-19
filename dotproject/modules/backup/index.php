<?php
// backup database module for dotProject
// (c)2003 Daniel Vijge
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.

// Changes by Adam Donnison <ajdonnison@dotproject.net>
// Changes include:
//   Upgraded to work with dotProject 2.0
//   Added permissions check to ensure user is allowed to backup
//   Added XML backup option
//   Completely reworked the backup system to use ADODB primitives.
//   Added localisation code so that it can be translated.

$perms =& $AppUI->acl();
if (! $perms->checkModule('backup', 'view'))	// Should we have an exec permission?
	$AppUI->redirect("m=public&a=access_denied");

$title =& new CTitleBlock('Backup Database', 'companies.gif', $m, $m .'.'.$a);
$title->addCrumb('index.php?m=backup&a=restore', 'restore xml file');
$title->show();
?>
<script>
	function check_backup_options()
	{
		var f = document.frmBackup;
		if(f.export_what.options[f.export_what.selectedIndex].value == 'data')
		{
			f.droptable.enabled=false;
			f.droptable.checked=false;
		}
		else
		{
			f.droptable.enabled=true;
		}
	}
</script>

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
	<form onclick="check_backup_options()" name="frmBackup" action="<?php echo "$baseUrl/index.php?m=backup&a=do_backup&suppressHeaders=1"; ?>" method="post">
	<tr>
		<td align="right" valign="top" nowrap="nowrap">
			<?php echo $AppUI->_('Export'); ?>
		</td>
		<td width="100%" nowrap="nowrap">
			<select name="export_what" class="text" >
				<option value="all" checked="checked"><?php echo $AppUI->_('Table structure and data'); ?></option>
				<option value="table"><?php echo $AppUI->_('Only table structure'); ?></option>
				<option value="data"><?php echo $AppUI->_('Only data'); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap"><?php echo $AppUI->_('Options'); ?></td>
		<td width="100%" nowrap="nowrap">
			<input type="checkbox" name="droptable" value="1" checked="checked" /><?php echo $AppUI->_("Add 'DROP TABLE' to output-script"); ?><br />
		</td>
	</tr>
	<tr>
		<td align="right" valign="top"  nowrap="nowrap"><?php echo $AppUI->_('Save as'); ?></td>
		<td width="100%" nowrap="nowrap">
			<select name="output_format" class="text" >
				<option value="zip" checked="checked"><?php echo $AppUI->_('Compressed ZIP SQL file', UI_OUTPUT_RAW); ?></option>
				<option value="sql"><?php echo $AppUI->_('Plain text SQL file', UI_OUTPUT_RAW); ?></option>
				<option value="xml"><?php echo $AppUI->_('XML file', UI_OUTPUT_RAW); ?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			&nbsp;
		</td>
		<td align="right">
			<input type="submit" value="<?php echo $AppUI->_('Download backup'); ?>" class="button"/>
		</td>
	</tr>
	</form>
</table>
