<?php /* $Id$ */
require_once( "./classdefs/ui.php" );
require_once( "./classdefs/calendar.php" );

session_name( 'dotproject' );
session_start();
$AppUI =& $_SESSION['AppUI'];

setlocale( LC_TIME, $AppUI->user_locale );

// by RK
@include_once( "{$AppUI->cfg['root_dir']}/locales/$AppUI->user_locale/locales.php" );
header("Content-type: text/html;charset=$locale_char_set");
// end

$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$uts = isset( $_GET['uts'] ) ? $_GET['uts'] : 0;

$this_month =  new CDate( $uts && $uts > 0 ? $uts : null );
$this_month->setTime( 0,0,0 );

$uistyle = $AppUI->getPref( 'UISTYLE' );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<link rel="stylesheet" href="./style/<?php echo $uistyle;?>/main.css" type="text/css" />
	<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
	<script language="javascript">
		function clickDay( uts, fdate ) {
			window.opener.<?php echo $callback;?>(uts,fdate);
			window.close();
		}
	</script>
	<title>Calendar</title>
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
<table border="0" cellspacing="0" cellpadding="3" width="100%">
	<tr>
<?php
		$this_month->setFormat( "%b" );
		for ($i=0; $i < 12; $i++) {
			$this_month->setMonth( $i+1 );
			echo "        <td width=\"8%\">"
				."<a href=\"{$_SERVER['SCRIPT_NAME']}?callback=$callback&uts=".$this_month->getTimestamp().'" class="">'.substr( $this_month->toString(), 0, 1)."</a>"
				."</td>\n";
		}
?>
	</tr>
	<tr>
<?php
		echo "        <td colspan=\"6\" align=\"left\">";
		echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?callback=$callback&uts=".$cal->prev_year->getTimestamp().'" class="">'.$cal->prev_year->getYear()."</a>";
		echo "</td>\n";
		echo "        <td colspan=\"6\" align=\"right\">";
		echo "<a href=\"{$_SERVER['SCRIPT_NAME']}?callback=$callback&uts=".$cal->next_year->getTimestamp().'" class="">'.$cal->next_year->getYear()."</a>";
		echo "</td>\n";
?>
	</tr>
</table>
</body>
</html>
