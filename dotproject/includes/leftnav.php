<TABLE cellspacing=0 cellpadding=2 border=0 height="600">
<TR>
	<TD><img src=images/shim.gif width=70 height=3></TD>
	<TD rowspan="100"><img src=images/shim.gif width=10 height=100></TD>
</TR>
<?php
if (isset( $perms['all'] ) || isset( $perms['companies'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=companies"><img src="./images/icons/money.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Companies" border="0" width="30" height="30"></a></td></tr></table>
		Clients &amp; Companies
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['projects'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
		<A href="./index.php?m=projects"><img src="./images/icons/projects.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Projects" border="0" width="30" height="30"></a>
		</td></tr></table>
		Projects
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['tasks'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=tasks"><img src="./images/icons/tasks.gif" alt="Tasks"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" border="0" width="30" height="30"></a></td></tr></table>
		Tasks
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['calendar'] )) { ?>
<TR>
	<TD id="clients" align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=calendar"><img src="./images/icons/calendar.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Calendar" border="0" width="30" height="30"></a></td></tr></table>
		Calendar
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['files'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=files"><img src="./images/icons/folder.gif" alt="Files" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		Files
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['contacts'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=contacts"><img src="./images/icons/contacts.gif" alt="Contacts" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		Contacts
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['forums'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=forums"><img src="./images/icons/communicate.gif" alt="Users and Communication" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		Forums
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['ticketsmith'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
		<A href="./index.php?m=ticketsmith"><img src="./images/icons/ticketsmith.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="Tickets" border="0" width="30" height="30"></a>
		</td></tr></table>
		Tickets
	</TD>
</TR>

<?php }
if (isset( $perms['all'] ) || isset( $perms['admin'] )) { ?>
<TR>
	<TD align="center" valign=middle class=nav>
		<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=admin"><img src="./images/icons/admin.gif" alt="Admin" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
		User Admin
	</TD>
</TR>
<?php }?>

<TR height="100%">
	<TD>&nbsp;<img src=images/shim.gif width=7 height=10></TD>
</TR>
</TABLE>