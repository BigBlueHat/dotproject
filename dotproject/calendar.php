<?php /* $Id$ */
require_once( "./includes/config.php" );
require_once( "$root_dir/classdefs/calendar.php" );
require_once( "$root_dir/classdefs/ui.php" );

session_name( 'dotproject' );
session_start();
$AppUI =& $_SESSION['AppUI'];

setlocale( LC_TIME, $AppUI->user_locale );

// by RK
@include_once( "$root_dir/locales/$AppUI->user_locale/locales.php" );
header("Content-type: text/html;charset=$locale_char_set");
// end

$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$uts = isset( $_GET['uts'] ) ? $_GET['uts'] : 0;

$this_month =  new CDate( $uts && $uts > 0 ? $uts : null );
$this_month->setTime( 0,0,0 );

$uistyle = $AppUI->getPref( 'UISTYLE' );
?>
<html>
<head>
<script language="javascript">
	function clickDay( uts, fdate ) {
		window.opener.<?php echo $callback;?>(uts,fdate);
		window.close();
	}
</script>

<title>Calendar</title>
<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css">
</head>

<body onload="this.focus();" class="popcal" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<?php

$cal = new CMonthCalendar( $this_month );
$cal->setStyles( 'poptitle', 'popcal' );
$cal->showWeek = false;
$cal->callback = $callback;
$cal->setLinkFunctions( 'clickDay' );

echo $cal->show();
?>

<table border="0" cellspacing="0" cellpadding="3" width="100%" class="">
<tr>
<?php
$this_month->setFormat( "%b" );
for ($i=0; $i < 12; $i++) {
	$this_month->setMonth( $i+1 );
	echo '<td width="8%">'
		."<a href=\"{$_SERVER['SCRIPT_NAME']}?callback=$callback&uts=".$this_month->getTimestamp().'" class="">'.substr( $this_month->toString(), 0, 1)."</a>"
		.'</td>';
}
?>
</tr>
<tr>
	<td colspan="6" align="left">
		<?php echo "<a href=\"{$_SERVER['$SCRIPT_NAME']}?callback=$callback&uts=".$cal->prev_year->getTimestamp().'" class="">'.$cal->prev_year->getYear()."</a>";?>
	</td>
	<td colspan="6" align="right">
		<?php echo "<a href=\"{$_SERVER['$SCRIPT_NAME']}?callback=$callback&uts=".$cal->next_year->getTimestamp().'" class="">'.$cal->next_year->getYear()."</a>";?>
	</td>
</table>

</body>
</html>
