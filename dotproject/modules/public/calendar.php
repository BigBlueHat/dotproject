<?php /* PUBLIC $Id$ */
require_once( "./classes/ui.class.php" );
require_once( "./modules/calendar/calendar.class.php" );

$callback = isset( $_GET['callback'] ) ? $_GET['callback'] : 0;
$date = dpGetParam( $_GET, 'date', null );

$this_month = new Date( $date ? "{$date}000000" : null );

$uistyle = $AppUI->getPref( 'UISTYLE' ) ? $AppUI->getPref( 'UISTYLE' ) : $AppUI->cfg['host_style'];

$cal = new CMonthCalendar( $this_month );
$cal->setStyles( 'poptitle', 'popcal' );
$cal->showWeek = false;
$cal->callback = $callback;
$cal->setLinkFunctions( 'clickDay' );

echo $cal->show();
?>
<script language="javascript">
/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
	function clickDay( idate, fdate ) {
		window.opener.<?php echo $callback;?>(idate,fdate);
		window.close();
	}
</script>

<table border="0" cellspacing="0" cellpadding="3" width="100%">
	<tr>
<?php
	for ($i=0; $i < 12; $i++) {
		$this_month->setMonth( $i+1 );
		echo "\n\t<td width=\"8%\">"
			."<a href=\"index.php?m=public&a=calendar&dialog=1&callback=$callback&date=".$this_month->format( DATE_FORMAT_TIMESTAMP_DATE ).'" class="">'.substr( $this_month->format( "%b" ), 0, 1)."</a>"
			."</td>";
	}
?>
	</tr>
	<tr>
<?php
	echo "\n\t<td colspan=\"6\" align=\"left\">";
	echo "<a href=\"index.php?m=public&a=calendar&dialog=1&callback=$callback&date=".$cal->prev_year->format( DATE_FORMAT_TIMESTAMP_DATE ).'" class="">'.$cal->prev_year->getYear()."</a>";
	echo "</td>";
	echo "\n\t<td colspan=\"6\" align=\"right\">";
	echo "<a href=\"index.php?m=public&a=calendar&dialog=1&callback=$callback&date=".$cal->next_year->format( DATE_FORMAT_TIMESTAMP_DATE ).'" class="">'.$cal->next_year->getYear()."</a>";
	echo "</td>";
?>
	</tr>
</table>