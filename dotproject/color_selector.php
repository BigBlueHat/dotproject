<html>
<head>
<title>Color Selector</title>

<script language="JavaScript">

// this function is used for color changes (when a colored square is clicked)
function SendColor(color) {
	if (color == 0) {
		return;
	}
	window.opener.document.AddEdit.project_color_identifier.value = color;
	window.opener.test.style.background = color;
	window.close();

} // end of SendColor

</script>

</head>

<body bgcolor="#FFFFFF">
<center>
<table border=0 cellpadding=1 cellspacing=0 width=292>
	<tr>
		<td valign=top><font style="font-family:trebuchetms,verdana,helvetica,arial,sans-serif;font-size:18px;"><strong>Color Selector</strong></font></td>
		<form>
		<td align=right valign=bottom>
		<!-- CUSTOMISE THE FOLLOWING COLOURS AND PRESET NAMES FOR YOUR ORGANISATIONAL NEEDS -->
			<select name="" onChange="javascript:SendColor(this.options[this.selectedIndex].value)" style="font-family:trebuchetms,verdana,helvetica,arial,sans-serif;font-size:10px">
				<option value="0">- - Preset - -
				<option value="FFCC00">Administration
				<option value="333300">Development
				<option value="FF6600">Investigation
				<option value="0000FF">Maintenance
				<option value="FF0000">Research
				<option value="33FF00">Testing
			</select>
		</td>
		</form>
    </tr>
    <tr>
   		<td colspan=2>
				<a href="webpal.map"><img src="./images/colorchart.gif" width=292 height=196 border=0 alt="" usemap="#map_webpal" ismap></a>
			</td>
    </tr>
    <tr>
		<td colspan=2 align="left"><font size=1 face="trebuchetms,verdana,arial">
			Choose a color to uniquely identify your selection and close the Color Selector.</p>
		</td>
    </tr>
</table>
<map name="map_webpal">

   <area coords="2,2,18,18"  href="javascript:SendColor('330000')">
   <area coords="18,2,34,18" href="javascript:SendColor('333300')">
   <area coords="34,2,50,18" href="javascript:SendColor('336600')">
   <area coords="50,2,66,18" href="javascript:SendColor('339900')">
   <area coords="66,2,82,18" href="javascript:SendColor('33CC00')">
   <area coords="82,2,98,18" href="javascript:SendColor('33FF00')">

   <area coords="98,2,114,18"  href="javascript:SendColor('66FF00')">
   <area coords="114,2,130,18" href="javascript:SendColor('66CC00')">
   <area coords="130,2,146,18" href="javascript:SendColor('669900')">
   <area coords="146,2,162,18" href="javascript:SendColor('666600')">
   <area coords="162,2,178,18" href="javascript:SendColor('663300')">
   <area coords="178,2,194,18" href="javascript:SendColor('660000')">

   <area coords="194,2,210,18" href="javascript:SendColor('FF0000')">
   <area coords="210,2,226,18" href="javascript:SendColor('FF3300')">
   <area coords="226,2,242,18" href="javascript:SendColor('FF6600')">
   <area coords="242,2,258,18" href="javascript:SendColor('FF9900')">
   <area coords="258,2,274,18" href="javascript:SendColor('FFCC00')">
   <area coords="274,2,290,18" href="javascript:SendColor('FFFF00')">


   <area coords="2,18,18,34"  href="javascript:SendColor('330033')">
   <area coords="18,18,34,34" href="javascript:SendColor('333333')">
   <area coords="34,18,50,34" href="javascript:SendColor('336633')">
   <area coords="50,18,66,34" href="javascript:SendColor('339933')">
   <area coords="66,18,82,34" href="javascript:SendColor('33CC33')">
   <area coords="82,18,98,34" href="javascript:SendColor('33FF33')">

   <area coords="98,18,114,34"  href="javascript:SendColor('66FF33')">
   <area coords="114,18,130,34" href="javascript:SendColor('66CC33')">
   <area coords="130,18,146,34" href="javascript:SendColor('669933')">
   <area coords="146,18,162,34" href="javascript:SendColor('666633')">
   <area coords="162,18,178,34" href="javascript:SendColor('663333')">
   <area coords="178,18,194,34" href="javascript:SendColor('660033')">

   <area coords="194,18,210,34" href="javascript:SendColor('FF0033')">
   <area coords="210,18,226,34" href="javascript:SendColor('FF3333')">
   <area coords="226,18,242,34" href="javascript:SendColor('FF6633')">
   <area coords="242,18,258,34" href="javascript:SendColor('FF9933')">
   <area coords="258,18,274,34" href="javascript:SendColor('FFCC33')">
   <area coords="274,18,290,34" href="javascript:SendColor('FFFF33')">


   <area coords="2,34,18,50"  href="javascript:SendColor('330066')">
   <area coords="18,34,34,50" href="javascript:SendColor('333366')">
   <area coords="34,34,50,50" href="javascript:SendColor('336666')">
   <area coords="50,34,66,50" href="javascript:SendColor('339966')">
   <area coords="66,34,82,50" href="javascript:SendColor('33CC66')">
   <area coords="82,34,98,50" href="javascript:SendColor('33FF66')">

   <area coords="98,34,114,50"  href="javascript:SendColor('66FF66')">
   <area coords="114,34,130,50" href="javascript:SendColor('66CC66')">
   <area coords="130,34,146,50" href="javascript:SendColor('669966')">
   <area coords="146,34,162,50" href="javascript:SendColor('666666')">
   <area coords="162,34,178,50" href="javascript:SendColor('663366')">
   <area coords="178,34,194,50" href="javascript:SendColor('660066')">

   <area coords="194,34,210,50" href="javascript:SendColor('FF0066')">
   <area coords="210,34,226,50" href="javascript:SendColor('FF3366')">
   <area coords="226,34,242,50" href="javascript:SendColor('FF6666')">
   <area coords="242,34,258,50" href="javascript:SendColor('FF9966')">
   <area coords="258,34,274,50" href="javascript:SendColor('FFCC66')">
   <area coords="274,34,290,50" href="javascript:SendColor('FFFF66')">


   <area coords="2,50,18,66"  href="javascript:SendColor('330099')">
   <area coords="18,50,34,66" href="javascript:SendColor('333399')">
   <area coords="34,50,50,66" href="javascript:SendColor('336699')">
   <area coords="50,50,66,66" href="javascript:SendColor('339999')">
   <area coords="66,50,82,66" href="javascript:SendColor('33CC99')">
   <area coords="82,50,98,66" href="javascript:SendColor('33FF99')">

   <area coords="98,50,114,66"  href="javascript:SendColor('66FF99')">
   <area coords="114,50,130,66" href="javascript:SendColor('66CC99')">
   <area coords="130,50,146,66" href="javascript:SendColor('669999')">
   <area coords="146,50,162,66" href="javascript:SendColor('666699')">
   <area coords="162,50,178,66" href="javascript:SendColor('663399')">
   <area coords="178,50,194,66" href="javascript:SendColor('660099')">

   <area coords="194,50,210,66" href="javascript:SendColor('FF0099')">
   <area coords="210,50,226,66" href="javascript:SendColor('FF3399')">
   <area coords="226,50,242,66" href="javascript:SendColor('FF6699')">
   <area coords="242,50,258,66" href="javascript:SendColor('FF9999')">
   <area coords="258,50,274,66" href="javascript:SendColor('FFCC99')">
   <area coords="274,50,290,66" href="javascript:SendColor('FFFF99')">


   <area coords="2,66,18,82"  href="javascript:SendColor('3300CC')">
   <area coords="18,66,34,82" href="javascript:SendColor('3333CC')">
   <area coords="34,66,50,82" href="javascript:SendColor('3366CC')">
   <area coords="50,66,66,82" href="javascript:SendColor('3399CC')">
   <area coords="66,66,82,82" href="javascript:SendColor('33CCCC')">
   <area coords="82,66,98,82" href="javascript:SendColor('33FFCC')">

   <area coords="98,66,114,82"  href="javascript:SendColor('66FFCC')">
   <area coords="114,66,130,82" href="javascript:SendColor('66CCCC')">
   <area coords="130,66,146,82" href="javascript:SendColor('6699CC')">
   <area coords="146,66,162,82" href="javascript:SendColor('6666CC')">
   <area coords="162,66,178,82" href="javascript:SendColor('6633CC')">
   <area coords="178,66,194,82" href="javascript:SendColor('6600CC')">

   <area coords="194,66,210,82" href="javascript:SendColor('FF00CC')">
   <area coords="210,66,226,82" href="javascript:SendColor('FF33CC')">
   <area coords="226,66,242,82" href="javascript:SendColor('FF66CC')">
   <area coords="242,66,258,82" href="javascript:SendColor('FF99CC')">
   <area coords="258,66,274,82" href="javascript:SendColor('FFCCCC')">
   <area coords="274,66,290,82" href="javascript:SendColor('FFFFCC')">


   <area coords="2,82,18,98"  href="javascript:SendColor('3300FF')">
   <area coords="18,82,34,98" href="javascript:SendColor('3333FF')">
   <area coords="34,82,50,98" href="javascript:SendColor('3366FF')">
   <area coords="50,82,66,98" href="javascript:SendColor('3399FF')">
   <area coords="66,82,82,98" href="javascript:SendColor('33CCFF')">
   <area coords="82,82,98,98" href="javascript:SendColor('33FFFF')">

   <area coords="98,82,114,98"  href="javascript:SendColor('66FFFF')">
   <area coords="114,82,130,98" href="javascript:SendColor('66CCFF')">
   <area coords="130,82,146,98" href="javascript:SendColor('6699FF')">
   <area coords="146,82,162,98" href="javascript:SendColor('6666FF')">
   <area coords="162,82,178,98" href="javascript:SendColor('6633FF')">
   <area coords="178,82,194,98" href="javascript:SendColor('6600FF')">

   <area coords="194,82,210,98" href="javascript:SendColor('FF00FF')">
   <area coords="210,82,226,98" href="javascript:SendColor('FF33FF')">
   <area coords="226,82,242,98" href="javascript:SendColor('FF66FF')">
   <area coords="242,82,258,98" href="javascript:SendColor('FF99FF')">
   <area coords="258,82,274,98" href="javascript:SendColor('FFCCFF')">
   <area coords="274,82,290,98" href="javascript:SendColor('FFFFFF')">


   <area coords="2,98,18,114"  href="javascript:SendColor('0000FF')">
   <area coords="18,98,34,114" href="javascript:SendColor('0033FF')">
   <area coords="34,98,50,114" href="javascript:SendColor('0066FF')">
   <area coords="50,98,66,114" href="javascript:SendColor('0099FF')">
   <area coords="66,98,82,114" href="javascript:SendColor('00CCFF')">
   <area coords="82,98,98,114" href="javascript:SendColor('00FFFF')">

   <area coords="98,98,114,114"  href="javascript:SendColor('99FFFF')">
   <area coords="114,98,130,114" href="javascript:SendColor('99CCFF')">
   <area coords="130,98,146,114" href="javascript:SendColor('9999FF')">
   <area coords="146,98,162,114" href="javascript:SendColor('9966FF')">
   <area coords="162,98,178,114" href="javascript:SendColor('9933FF')">
   <area coords="178,98,194,114" href="javascript:SendColor('9900FF')">

   <area coords="194,98,210,114" href="javascript:SendColor('CC00FF')">
   <area coords="210,98,226,114" href="javascript:SendColor('CC33FF')">
   <area coords="226,98,242,114" href="javascript:SendColor('CC66FF')">
   <area coords="242,98,258,114" href="javascript:SendColor('CC99FF')">
   <area coords="258,98,274,114" href="javascript:SendColor('CCCCFF')">
   <area coords="274,98,290,114" href="javascript:SendColor('CCFFFF')">


   <area coords="2,114,18,130"  href="javascript:SendColor('0000CC')">
   <area coords="18,114,34,130" href="javascript:SendColor('0033CC')">
   <area coords="34,114,50,130" href="javascript:SendColor('0066CC')">
   <area coords="50,114,66,130" href="javascript:SendColor('0099CC')">
   <area coords="66,114,82,130" href="javascript:SendColor('00CCCC')">
   <area coords="82,114,98,130" href="javascript:SendColor('00FFCC')">

   <area coords="98,114,114,130"  href="javascript:SendColor('99FFCC')">
   <area coords="114,114,130,130" href="javascript:SendColor('99CCCC')">
   <area coords="130,114,146,130" href="javascript:SendColor('9999CC')">
   <area coords="146,114,162,130" href="javascript:SendColor('9966CC')">
   <area coords="162,114,178,130" href="javascript:SendColor('9933CC')">
   <area coords="178,114,194,130" href="javascript:SendColor('9900CC')">

   <area coords="194,114,210,130" href="javascript:SendColor('CC00CC')">
   <area coords="210,114,226,130" href="javascript:SendColor('CC33CC')">
   <area coords="226,114,242,130" href="javascript:SendColor('CC66CC')">
   <area coords="242,114,258,130" href="javascript:SendColor('CC99CC')">
   <area coords="258,114,274,130" href="javascript:SendColor('CCCCCC')">
   <area coords="274,114,290,130" href="javascript:SendColor('CCFFCC')">


   <area coords="2,130,18,146"  href="javascript:SendColor('000099')">
   <area coords="18,130,34,146" href="javascript:SendColor('003399')">
   <area coords="34,130,50,146" href="javascript:SendColor('006699')">
   <area coords="50,130,66,146" href="javascript:SendColor('009999')">
   <area coords="66,130,82,146" href="javascript:SendColor('00CC99')">
   <area coords="82,130,98,146" href="javascript:SendColor('00FF99')">

   <area coords="98,130,114,146"  href="javascript:SendColor('99FF99')">
   <area coords="114,130,130,146" href="javascript:SendColor('99CC99')">
   <area coords="130,130,146,146" href="javascript:SendColor('999999')">
   <area coords="146,130,162,146" href="javascript:SendColor('996699')">
   <area coords="162,130,178,146" href="javascript:SendColor('993399')">
   <area coords="178,130,194,146" href="javascript:SendColor('990099')">

   <area coords="194,130,210,146" href="javascript:SendColor('CC0099')">
   <area coords="210,130,226,146" href="javascript:SendColor('CC3399')">
   <area coords="226,130,242,146" href="javascript:SendColor('CC6699')">
   <area coords="242,130,258,146" href="javascript:SendColor('CC9999')">
   <area coords="258,130,274,146" href="javascript:SendColor('CCCC99')">
   <area coords="274,130,290,146" href="javascript:SendColor('CCFF99')">


   <area coords="2,146,18,162"  href="javascript:SendColor('000066')">
   <area coords="18,146,34,162" href="javascript:SendColor('003366')">
   <area coords="34,146,50,162" href="javascript:SendColor('006666')">
   <area coords="50,146,66,162" href="javascript:SendColor('009966')">
   <area coords="66,146,82,162" href="javascript:SendColor('00CC66')">
   <area coords="82,146,98,162" href="javascript:SendColor('00FF66')">

   <area coords="98,146,114,162"  href="javascript:SendColor('99FF66')">
   <area coords="114,146,130,162" href="javascript:SendColor('99CC66')">
   <area coords="130,146,146,162" href="javascript:SendColor('999966')">
   <area coords="146,146,162,162" href="javascript:SendColor('996666')">
   <area coords="162,146,178,162" href="javascript:SendColor('993366')">
   <area coords="178,146,194,162" href="javascript:SendColor('990066')">

   <area coords="194,146,210,162" href="javascript:SendColor('CC0066')">
   <area coords="210,146,226,162" href="javascript:SendColor('CC3366')">
   <area coords="226,146,242,162" href="javascript:SendColor('CC6666')">
   <area coords="242,146,258,162" href="javascript:SendColor('CC9966')">
   <area coords="258,146,274,162" href="javascript:SendColor('CCCC66')">
   <area coords="274,146,290,162" href="javascript:SendColor('CCFF66')">


   <area coords="2,162,18,178"  href="javascript:SendColor('000033')">
   <area coords="18,162,34,178" href="javascript:SendColor('003333')">
   <area coords="34,162,50,178" href="javascript:SendColor('006633')">
   <area coords="50,162,66,178" href="javascript:SendColor('009933')">
   <area coords="66,162,82,178" href="javascript:SendColor('00CC33')">
   <area coords="82,162,98,178" href="javascript:SendColor('00FF33')">

   <area coords="98,162,114,178"  href="javascript:SendColor('99FF33')">
   <area coords="114,162,130,178" href="javascript:SendColor('99CC33')">
   <area coords="130,162,146,178" href="javascript:SendColor('999933')">
   <area coords="146,162,162,178" href="javascript:SendColor('996633')">
   <area coords="162,162,178,178" href="javascript:SendColor('993333')">
   <area coords="178,162,194,178" href="javascript:SendColor('990033')">

   <area coords="194,162,210,178" href="javascript:SendColor('CC0033')">
   <area coords="210,162,226,178" href="javascript:SendColor('CC3333')">
   <area coords="226,162,242,178" href="javascript:SendColor('CC6633')">
   <area coords="242,162,258,178" href="javascript:SendColor('CC9933')">
   <area coords="258,162,274,178" href="javascript:SendColor('CCCC33')">
   <area coords="274,162,290,178" href="javascript:SendColor('CCFF33')">


   <area coords="2,178,18,194"  href="javascript:SendColor('000000')">
   <area coords="18,178,34,194" href="javascript:SendColor('003300')">
   <area coords="34,178,50,194" href="javascript:SendColor('006600')">
   <area coords="50,178,66,194" href="javascript:SendColor('009900')">
   <area coords="66,178,82,194" href="javascript:SendColor('00CC00')">
   <area coords="82,178,98,194" href="javascript:SendColor('00FF00')">

   <area coords="98,178,114,194"  href="javascript:SendColor('99FF00')">
   <area coords="114,178,130,194" href="javascript:SendColor('99CC00')">
   <area coords="130,178,146,194" href="javascript:SendColor('999900')">
   <area coords="146,178,162,194" href="javascript:SendColor('996600')">
   <area coords="162,178,178,194" href="javascript:SendColor('993300')">
   <area coords="178,178,194,194" href="javascript:SendColor('990000')">

   <area coords="194,178,210,194" href="javascript:SendColor('CC0000')">
   <area coords="210,178,226,194" href="javascript:SendColor('CC3300')">
   <area coords="226,178,242,194" href="javascript:SendColor('CC6600')">
   <area coords="242,178,258,194" href="javascript:SendColor('CC9900')">
   <area coords="258,178,274,194" href="javascript:SendColor('CCCC00')">
   <area coords="274,178,290,194" href="javascript:SendColor('CCFF00')">
</map>
</center>
</body>
</html>



