<?
if(empty($return))$return = $REQUEST_URI;
setcookie("user_cookie", "");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Pragma" content="no-cache">
<link href="./style/main.css" rel="STYLESHEET" type="text/css">


</head>
<body bgcolor="white" onload="document.loginform.username.focus();">

<center>
<br>
<br>
<br>
<br>
<CENTER><?echo @$message?>&nbsp;</center>
<table align="center" border="0" width="250" cellpadding="4" cellspacing="0" bgcolor="#cccccc" class=bordertable>
<form action="./logincheck.php" method="post" name="loginform">
<input type="hidden" name="login" value="<? echo time();?>">
<input type="hidden" name="return" value="<? echo $return;?>">
<TR><TD colspan=2 class="headerfontWhite" bgcolor="gray"><b><?echo $company_name;?></b></td></tr>



</td>
</tr>
<tr>
<td bgcolor="#eeeeee" valign="top" align="right" nowrap width="80">
Username:
</td>
<td bgcolor="#eeeeee" align=left class="menufontlight" nowrap>
<input type="text" size="25" name="username"class=text>
</td>
</tr>
<tr>
<td bgcolor="#eeeeee" valign="top" align="right"  nowrap>
Password:
<td bgcolor="#eeeeee" align=left class="menufontlight" nowrap>
<input type="password" size="25" name="password"class=text><br>
<br>

<input type="submit" value="Login" class=button>
</font>
</td>
</tr>
</table>
<table align="center" border="0" width="250" cellpadding="2" cellspacing="1">
<TR><TD>

<br><UL type="square">
<LI>
<A href="mailto:support@mysite.com">Help! I've forgotten my username and password!</a>

</ul>
</td></tr>
</TABLE>
</form>


</center>
</body>
</html>