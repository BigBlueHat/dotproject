<?
//Left Nav Bar
if(@$basilix)$m = "mail";
$usql = "select user_username, user_password from users where user_id = $user_cookie";
$rcu = mysql_query($usql);
$rcu = mysql_fetch_row($rcu);
?>
<TABLE width="50" cellspacing=0 cellpadding=0 border=0 height="600">
<form name=loginForm method='POST' action='/dotproject/basilix.php'>
<input type=hidden name=RequestID value=LOGIN>
<input type=hidden name=username value="<?echo $rcu[0];?>">
<input type=hidden name=domain value="dotmarketing.com">
<input type=hidden name=password value="<?echo $rcu[1];?>">
<input type=hidden name=doLogin value=" Login ">
<TR>
	<TD><img src=images/shim.gif width=7 height=1></TD>
	<TD rowspan="100"><img src=images/shim.gif width=15 height=100></TD>
</TR>
<?
if(strpos($perms,"companies")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "companie4s"){?> bgcolor="#ffffff" <?}?>>
	<TD id="clients" align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=companies"><img src="./images/icons/money.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?echo ptranslate("Companies");?>" border="0" width="30" height="30"></a></td></tr></table>
		Clients &amp; Companies</TD>
	</TD>
</TR>
<?}
if(strpos($perms,"projects")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "proje4cts"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
	<A href="./index.php?m=projects"><img src="./images/icons/projects.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?echo ptranslate("Projects");?>" border="0" width="30" height="30"></a>
	</td></tr></table>
	Projects
	</TD>
</TR>
<?}
if(strpos($perms,"tasks")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "ta5sks"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=tasks"><img src="./images/icons/tasks.gif" alt="<?echo ptranslate("Tasks");?>"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" border="0" width="30" height="30"></a></td></tr></table>
	Tasks
	</TD>
</TR>
<?}
if(strpos($perms,"calendar")>0 || strpos($perms,"all")>0){?>
<TR>
	<TD id="clients" align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=calendar"><img src="./images/icons/calendar.gif"   onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?echo ptranslate("Calendar");?>" border="0" width="30" height="30"></a></td></tr></table>
		Calendar</TD>
	</TD>
</TR>
<?}
if(strpos($perms,"files")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "file5s"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=files"><img src="./images/icons/folder.gif" alt="<?echo ptranslate("Files");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	Files
	</TD>
</TR>
<?}
if(strpos($perms,"contacts")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "conta5cts"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=contacts"><img src="./images/icons/contacts.gif" alt="<?echo ptranslate("Contacts");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	Contacts
	</TD>
</TR>
<?}
if(strpos($perms,"users")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "use5rs"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=forums"><img src="./images/icons/communicate.gif" alt="<?echo ptranslate("Users and Communication");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	Forums
	</TD>
</TR>
<?}
if(strpos($perms,"ticketsmith")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "proje4cts"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff">
	<A href="./index.php?m=ticketsmith"><img src="./images/icons/ticketsmith.gif"  onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" alt="<?echo ptranslate("Tickets");?>" border="0" width="42" height="42"></a>
	</td></tr></table>
	Tickets
	</TD>
</TR>

<?}
if(strpos($perms,"admin")>0 || strpos($perms,"all")>0){?>
<TR  <?if($m == "adm5in"){?> bgcolor="#ffffff" <?}?>>
	<TD align="center" valign=middle class="clsBtnOff">
	<table cellspacing=0 cellpadding=0 border=0><tr><td class="clsBtnOff"><A href="./index.php?m=admin"><img src="./images/icons/admin.gif" alt="<?echo ptranslate("Admin");?>" border="0" width="30" height="30" onmouseover="doBtn();" onmouseout="doBtn();" onmousedown="doBtn();" onmouseup="doBtn();" ></a></td></tr></table>
	User Admin
	</TD>
</TR>
<?}?>

<TR height="100%"><TD>&nbsp;<img src=images/shim.gif width=7 height=10> </TD></TR>

</form>
</TABLE>
