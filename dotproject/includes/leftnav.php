<?php
//Left Nav Bar
if(@$basilix)$m = "mail";
$usql = "select user_username, user_password from users where user_id = $user_cookie";
$rcu = mysql_query($usql);
$rcu = mysql_fetch_row($rcu);
?>
<TABLE width="50" cellspacing=0 cellpadding=0 border=0 height="600">
<form name=loginForm method='POST' action='/dotproject/basilix.php'>
<input type=hidden name=RequestID value=LOGIN>
<input type=hidden name=username value="<?php echo $rcu[0];?>">
<input type=hidden name=domain value="dotmarketing.com">
<input type=hidden name=password value="<?php echo $rcu[1];?>">
<input type=hidden name=doLogin value=" Login ">
<TR>
	<TD><img src=images/shim.gif width=7 height=1></TD>
	<TD rowspan="100"><img src=images/shim.gif width=15 height=100></TD>
</TR>
<?php
if (isset( $perms['all'] ) || isset( $perms['companies'] )) { ?>
<TR  <?php if($m == "companie4s"){?> bgcolor="#ffffff" <?php }?>>
	<TD id="clients" align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=companies"><img src="./images/icons/money.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?php echo ptranslate("Companies");?>" border="0" width="30" height="30"></a></td></tr></table>
		Clients &amp; Companies</TD>
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['projects'] )) { ?>
<TR  <?php if($m == "proje4cts"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
	<A href="./index.php?m=projects"><img src="./images/icons/projects.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?php echo ptranslate("Projects");?>" border="0" width="30" height="30"></a>
	</td></tr></table>
	Projects
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['tasks'] )) { ?>
<TR  <?php if($m == "ta5sks"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=tasks"><img src="./images/icons/tasks.gif" alt="<?php echo ptranslate("Tasks");?>"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" border="0" width="30" height="30"></a></td></tr></table>
	Tasks
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['calendar'] )) { ?>
<TR>
	<TD id="clients" align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=calendar"><img src="./images/icons/calendar.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?php echo ptranslate("Calendar");?>" border="0" width="30" height="30"></a></td></tr></table>
		Calendar</TD>
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['files'] )) { ?>
<TR  <?php if($m == "file5s"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=files"><img src="./images/icons/folder.gif" alt="<?php echo ptranslate("Files");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	Files
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['contacts'] )) { ?>
<TR  <?php if($m == "conta5cts"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=contacts"><img src="./images/icons/contacts.gif" alt="<?php echo ptranslate("Contacts");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	Contacts
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['forums'] )) { ?>
<TR  <?php if($m == "use5rs"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=forums"><img src="./images/icons/communicate.gif" alt="<?php echo ptranslate("Users and Communication");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	Forums
	</TD>
</TR>
<?php }
if (isset( $perms['all'] ) || isset( $perms['ticketsmith'] )) { ?>
<TR  <?php if($m == "proje4cts"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
	<A href="./index.php?m=ticketsmith"><img src="./images/icons/ticketsmith.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?php echo ptranslate("Tickets");?>" border="0" width="42" height="42"></a>
	</td></tr></table>
	Tickets
	</TD>
</TR>

<?php }
if (isset( $perms['all'] ) || isset( $perms['admin'] )) { ?>
<TR  <?php if($m == "adm5in"){?> bgcolor="#ffffff" <?php }?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=admin"><img src="./images/icons/admin.gif" alt="<?php echo ptranslate("Admin");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	User Admin
	</TD>
</TR>
<?php }?>

<TR height="100%"><TD>&nbsp;<img src=images/shim.gif width=7 height=10> </TD></TR>

</form>
</TABLE>
