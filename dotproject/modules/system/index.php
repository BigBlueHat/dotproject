<?php /* SYSTEM $Id$ */
$AppUI->savePlace();

$titleBlock = new CTitleBlock( 'System Administration', 'system.gif', $m, "$m.$a" );
$titleBlock->show();
?>
<p>
<table width="50%" border="0" cellpadding="0" cellspacing="5" align="left">
<tr>
	<td width="34">
		<img src="./images/icons/world.gif" width="34" height="34" border="0" alt="">
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_( 'Language Support' );?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=translate"><?php echo $AppUI->_( 'Translation Management' );?></a>
	</td>
</tr>

<tr>
	<td>
		<img src="./images/icons/preference.gif" width="32" height="32" border="0" alt="">
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Preferences');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=addeditpref"><?php echo $AppUI->_('Default User Preferences');?></a>
		<br /><a href="?m=system&u=syskeys&a=keys"><?php echo $AppUI->_( 'System Lookup Keys' );?></a>
		<br /><a href="?m=system&u=syskeys"><?php echo $AppUI->_( 'System Lookup Values' );?></a>
	</td>
</tr>

<tr>
	<td>
		<img src="<?php echo dPfindImage( 'modules.gif', $m );?>" width="32" height="32" border=0 alt="">
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Modules');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&a=viewmods"><?php echo $AppUI->_('View Modules');?></a>
	</td>
</tr>

<tr>
	<td>
		<img src="<?php echo dPfindImage( 'users.gif', $m );?>" width="32" height="32" border=0 alt="">
	</td>
	<td align="left" class="subtitle">
		<?php echo $AppUI->_('Administration');?>
	</td>
</tr>

<tr>
	<td>&nbsp;</td>
	<td align="left">
		<a href="?m=system&u=roles"><?php echo $AppUI->_('User Roles');?></a>
	</td>
</tr>

</table>
</p>
