<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<script language="JavaScript">
function doBtn() {
	var oEl = event.srcElement;
	var doit = event.type;
	
	while (-1 == oEl.className.indexOf( "Btn" )) {
		oEl = oEl.parentElement;
		if (!oEl)
			return;
	}
	if (doit == "mouseover" || doit == "mouseup") {
		oEl.className = "clsBtnOn";
	} else if (doit == "mousedown") {
		oEl.className = "clsBtnDown";
	} else {
		oEl.className = "clsBtnOff";
	}
}
function tboff(){
	var oEl = event.srcElement;
	var doit = event.type;
	oEl.className = "topBtnOff";
}
</script>
<title><?php echo $page_title;?></title>
<link rel="stylesheet" type="text/css" href="./style/main.css">
</head>
<body bgcolor="#ffffff" topmargin="0" leftmargin="0" marginheight=0 marginwidth=0 background="images/bground.gif">
<TABLE width="100%" cellpadding=3 cellspacing=0 bgcolor="#cccccc" style="border: outset #eeeeee 2px;">
	<TR>
		<TD nowrap width="33%"><span id="smallCompanyTitle"><?php echo $company_name;?></SPAN></TD>
		<TD nowrap width="34%"><span id="smallCompanyTitle">Current user: <?php echo "$thisuser_first_name $thisuser_last_name"; ?></SPAN></TD>
		<TD nowrap width="33%" align="right">
			<TABLE cellpadding=1 cellspacing=1 width="200">
				<TR>
					<TD class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><A href="./index.php?m=admin&a=viewuser&user_id=<?php echo $user_cookie;?>" onmouseover="doBtn();"><?php echo ptranslate("My Info");?></a></TD>
					<TD class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><A href="./index.php?logout=-1" onmouseover="doBtn();"><?php echo ptranslate("Logout");?></a></TD>
					<TD class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><?php echo ptranslate("Help");?></TD>
					<TD class="topBtnOff" nowrap bgcolor="#cccccc" align="center"  onmouseover="doBtn();" onmouseout="tboff();" onmousedown="doBtn();" onmouseup="doBtn();"><A href="./index.php?m=help&a=about" onmouseover="doBtn();"><?php echo ptranslate("About");?></a></TD>
				</TR>
			</TABLE>
	</TR>
</TABLE>
<TABLE width="100%" cellpadding=0 cellspacing=0 border=0>
<TR>
<TD valign="top"><?php require "./includes/leftnav.php";?></TD>
<TD valign="top" align="left" width="100%">
<?php echo @$message;?><img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>	
