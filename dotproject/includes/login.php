<?php
$return = '';
setcookie("user_cookie", "");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Pragma" content="no-cache">
	<link href="./style/main.css" rel="STYLESHEET" type="text/css">
</head>

<body bgcolor="white" onload="document.loginform.username.focus();">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table align="center" border="0" width="250" cellpadding="4" cellspacing="0" bgcolor="#cccccc" class="bordertable">
<form action="./logincheck.php" method="post" name="loginform">
<input type="hidden" name="login" value="<?php echo time();?>">
<input type="hidden" name="return" value="<?php echo $return;?>">

<tr>
	<td colspan="2" class="headerfontWhite" bgcolor="gray">
		<b><?php echo $company_name;?></b>
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
	</td>
	<td bgcolor="#eeeeee" align=left class="menufontlight" nowrap>
		<input type="password" size="25" name="password" class="text">
		<p><input type="submit" value="Login" class=button></p>
	</td>
</tr>
</table>

<p align="center"><?php echo @$message?></p>

<table align="center" border="0" width="250" cellpadding="4" cellspacing="0">
<tr>
	<td>
		<br>
		<ul type="square">
			<li>
				<A href="mailto:<?php echo 'admin@' . $site_domain;?>">Help! I've forgotten my username and password!</a>
			</li>
		</ul>
	</td>
</tr>
<tr>
	<td align=center>
		<img src="./images/icons/dp.gif" width="42" height="42" border=0 alt="dotproject">
		<p>dotproject</p>
		<p>an open-source
		<br />project management
		<br />application</p>
	</td>
</tr>
</form>
</table>

</body>
</html>
