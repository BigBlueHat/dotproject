<table cellspacing=0 cellpadding=2 border=0 height="600">
<tr>
	<td><img src=images/shim.gif width=70 height=3></td>
	<td rowspan="100"><img src=images/shim.gif width=10 height=100></td>
</tr>
<?php
if (isset( $perms['all'] ) || isset( $perms['companies'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=companies"><img src="./images/icons/money.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Companies" border="0" width="30" height="30"></a></td></tr></table>
		<?php echo $AppUI->_('Clients & Companies');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['projects'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
		<A href="./index.php?m=projects"><img src="./images/icons/projects.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Projects" border="0" width="30" height="30"></a>
		</td></tr></table>
		<?php echo $AppUI->_('Projects');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['tasks'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=tasks"><img src="./images/icons/tasks.gif" alt="Tasks"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" border="0" width="30" height="30"></a></td></tr></table>
		<?php echo $AppUI->_('Tasks');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['calendar'] )) { ?>
<tr>
	<td id="clients" align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=calendar"><img src="./images/icons/calendar.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Calendar" border="0" width="30" height="30"></a></td></tr></table>
		<?php echo $AppUI->_('Calendar');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['files'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=files"><img src="./images/icons/folder.gif" alt="Files" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		<?php echo $AppUI->_('Files');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['contacts'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=contacts"><img src="./images/icons/contacts.gif" alt="Contacts" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		<?php echo $AppUI->_('Contacts');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['forums'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=forums"><img src="./images/icons/communicate.gif" alt="Users and Communication" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		<?php echo $AppUI->_('Forums');?>
	</td>
</tr>
<?php }
if (isset( $perms['all'] ) || isset( $perms['ticketsmith'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
		<A href="./index.php?m=ticketsmith"><img src="./images/icons/ticketsmith.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Tickets" border="0" width="30" height="30"></a>
		</td></tr></table>
		<?php echo $AppUI->_('Tickets');?>
	</td>
</tr>

<?php }
if (isset( $perms['all'] ) || isset( $perms['admin'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=admin"><img src="./images/icons/admin.gif" alt="Admin" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		<?php echo $AppUI->_('User Admin');?>		
	</td>
</tr>

<?php }
if (isset( $perms['all'] ) || isset( $perms['system'] )) { ?>
<tr>
	<td align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="?m=system"><img src="./images/icons/system.gif" alt="System Administration" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		<?php echo $AppUI->_('System Admin');?>
	</td>
</tr>
<?php }?>

<tr height="100%">
	<td>&nbsp;<img src=images/shim.gif width=7 height=10></td>
</tr>
</table>