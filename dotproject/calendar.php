<?php
require "./includes/config.php";
require "$root_dir/classdefs/date.php";
require "$root_dir/classdefs/ui.php";

session_start();
$AppUI =& $_SESSION['AppUI'];

$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$uts = isset( $_GET['uts'] ) ? $_GET['uts'] : 0;

$this_month =  new CDate( $uts && $uts > 0 ? $uts : null );

// legacy support
if (!empty($thisMonth)) { $this_month->setMonth($thisMonth); }
if (!empty($thisYear)) { $this_month->setYear($thisYear); }
if (!empty($thisDay)) { $this_month->setDay($thisDay); }

$prev_month = $this_month;
$prev_month->addMonths( -1 );

$next_month = $this_month;
$next_month->addMonths( +1 );

$prev_year = $this_month;
$prev_year->addYears( -1 );

$next_year = $this_month;
$next_year->addYears( +1 );

//Short Day names
$dayNamesShort = array( "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat" );
$monthLetters = array( 'J', 'F', 'M', 'A', 'M', 'J', 'J', 'A', 'S', 'O', 'N', 'D' );

if(empty($todaysDay)){$todaysDay = date("d", time());}
if(empty($todaysMonth)){$todaysMonth = intval(date("m", time()));}
if(empty($todaysYear)){$todaysYear = date("Y", time());}
$day=0;

$uistyle = $AppUI->getPref( 'UISTYLE' );
?>
<html>
<head>
<script language="javascript">
	function setClose( uts, fdate ){
		window.opener.<?php echo $callback;?>(uts,fdate);
		window.close();
	}
</script>
<title>Calendar</title>
<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle;?>/main.css">
</head>

<body onload="this.focus();" class="popcal" leftmargin="0" topmargin="0" marginheight="0" marginwidth="0">
<table border="0" cellspacing="1" cellpadding="2" width="100%" class="popcal">
<tr>
	<td align="left" class="poparrows">
		<a href="<?php echo "$SCRIPT_NAME?callback=$callback&uts=".$prev_month->getTimestamp();?>"><img src="./images/prev.gif" width="16" height="16" alt="pre" border="0"></a>
	</td>
	<td colspan="5" align="center" class="popmonth">
		<?php echo $this_month->toString( "%B %Y" );?>
	</td>
	<td align="right" class="poparrows">
		<a href="<?php echo "$SCRIPT_NAME?callback=$callback&uts=".$next_month->getTimestamp();?>"><img src="./images/next.gif" width="16" height="16" alt="pre" border="0"></a>
	</td>
</tr>
<?php  
// print days across top
$s = '<tr>';
for ($i=0; $i < 7; $i++) {
	$s .= "<th width=\"14%\">$dayNamesShort[$i]</th>\n";
}
$s .= '</tr>';

// pre-pad the calendar
$pad = $this_month->getStartSpaces();
$p = '';
for ($i=0; $i < $pad; $i++) {
	$p .= '<td class="poppad">&nbsp;</td>';
}
$s .= $p ? "<tr>$p" : '';

// fill the calendar
$show_day = $this_month;
$show_day->setDay( 1 );
$show_day->setFormat( $AppUI->getPref( 'SHDATEFORMAT' ) );

$n = $this_month->daysInMonth();
for ($i=0; $i < $n; $i++) {
	$day = $show_day->getWeekday();
	$class = '';
	if ($show_day->D == $this_month->D) {
		$class = 'poptoday';
	} else if ($day < 1 || $day > 5) {
		$class = 'popweekend';
	}
	// start new row
	if ($day == 0) {
		$s .= '<tr>';
	}
	$href = "javascript:setClose(".$show_day->getTimestamp().",'".$show_day->toString()."')";
	$s .= "<td class=\"$class\"><a href=\"$href\" class=\"$class\">".$show_day->D.'</a></td>';

	// finish a row
	if ($day == 6) {
		$s .= '</tr>';
	}
	$show_day->addDays( 1 );
}

// post-pad the calendar
$pad = 7 - (($pad + $this_month->daysInMonth()) % 7);
if ($pad < 7) {
	for ($i=0; $i < $pad; $i++) {
		$s .= '<td class="poppad">&nbsp;</td>';
	}
	$s .= '</tr>';
}
// print it
echo $s;
?>
</table>

<table border="0" cellspacing="0" cellpadding="3" width="100%">
<tr>
<?php
$this_month->setFormat( "%b" );
for ($i=0; $i < 12; $i++) {
	$this_month->setMonth( $i+1 );
	echo '<td width="8%">'
		."<a href=\"$SCRIPT_NAME?callback=$callback&uts=".$this_month->getTimestamp().'" class="popcallinks">'.substr( $this_month->toString(), 0, 1)."</a>"
		.'</td>';
}
?>
</tr>
<tr>
	<td colspan="6" align="left">
		<?php echo "<a href=\"$SCRIPT_NAME?callback=$callback&uts=".$prev_year->getTimestamp().'" class="popcallinks">'.$prev_year->Y."</a>";?>
	</td>
	<td colspan="6" align="right">
		<?php echo "<a href=\"$SCRIPT_NAME?callback=$callback&uts=".$next_year->getTimestamp().'" class="popcallinks">'.$next_year->Y."</a>";?>
	</td>
</table>

</body>
</html>
