<?php
$return = '';
$AppUI = new CAppUI;
$AppUI->locale_warn = true;
$AppUI->user_locale = $host_locale;

@include_once( "$root_dir/locales/core.php" );
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
	<meta http-equiv="Pragma" content="no-cache">
	<link href="./style/demo1/main.css" rel="STYLESHEET" type="text/css">
</head>

<body bgcolor="#f0f0f0" onload="document.loginform.username.focus();">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<table align="center" border="0" width="250" cellpadding="4" cellspacing="0" class="std">
<form action="./logincheck.php" method="post" name="loginform">
<input type="hidden" name="login" value="<?php echo time();?>">
<input type="hidden" name="return" value="<?php echo $return;?>">
<tr>
	<th colspan="2" >
		<b><?php echo $company_name;?></b>
	</th>
</tr>
<tr>
	<td align="right" nowrap width="100">
		<?php echo $AppUI->_('Username');?>:
	</td>
	<td align=left nowrap>
		<input type="text" size="25" name="username" class=text>
	</td>
</tr>
<tr>
	<td align="right"  nowrap>
		<?php echo $AppUI->_('Password');?>:
	</td>
	<td align="left" nowrap>
		<input type="password" size="25" name="password" class="text">
	</td>
</tr>
<tr>
	<td align="center" nowrap colspan="2">
		<input type="submit" value="<?php echo $AppUI->_('login');?>" class="button"></p>
	</td>
</tr>
</table>

<p align="center"><?php 
	echo '<span class="error">'.$AppUI->getMsg().'</span>';
	echo ini_get( 'register_globals') ? '' : '<br><span class="warning">WARNING: dotproject is not fully supported with register_globals=off</span>';
?></p>

<table align="center" border="0" width="250" cellpadding="4" cellspacing="0">
<tr>
	<td>
		<br>
		<ul type="square">
			<li>
				<A href="mailto:<?php echo 'admin@' . $site_domain;?>"><?php echo $AppUI->_('forgotPassword');?></a>
			</li>
		</ul>
	</td>
</tr>
<tr>
	<td align=center>
		<img src="./images/icons/dp.gif" width="42" height="42" border=0 alt="dotproject">
		<p>dotproject</p>
		<p><?php echo $AppUI->_('openSource');?></p>
	</td>
</tr>
</form>
</table>

</body>
</html>
