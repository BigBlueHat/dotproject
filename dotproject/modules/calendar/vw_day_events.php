<?php /* CALENDAR $Id$ */
global $this_day, $first_time, $last_time, $company_id;

// load the event types
$types = dPgetSysVal( 'EventType' );
$links = array();

// assemble the links for the events
$events = CEvent::getEventsForPeriod( $first_time, $last_time );
$events2 = array();

foreach ($events as $row) {
	$start = new Date( $row['event_start_date'] );

	$events2[$start->format( "%H%M%S" )][] = $row;
// the link
/*
	$link['href'] = "?m=calendar&a=view&event_id=".$row['event_id'];
	$link['alt'] = $row['event_description'];
	$link['text'] = '<img src="./images/obj/event.gif" width="16" height="16" border="0" alt="" />'
		.'<span class="event">'.$row['event_title'].'</span>';
	$links[$start->format( DATE_FORMAT_TIMESTAMP_DATE )][] = $link;
*/
}

$tf = $AppUI->getPref('TIMEFORMAT');

$dayStamp = $this_day->format( DATE_FORMAT_TIMESTAMP_DATE );

$start = 8;
$end = 17;
$inc = 15;

$this_day->setTime( $start, 0, 0 );

$html = '<table cellspacing="1" cellpadding="2" border="0" width="100%" class="tbl">';
for ($i=0, $n=($end-$start)*60/$inc; $i < $n; $i++) {
	$html .= "\n<tr>";
	
	$tm = $this_day->format( $tf );
	$html .= "\n\t<td width=\"1%\" align=\"right\" nowrap=\"nowrap\">".($this_day->getMinute() ? $tm : "<b>$tm</b>")."</td>";

	$timeStamp = $this_day->format( "%H%M%S" );
	if( @$events2[$timeStamp] ) {
		$row = $events2[$timeStamp][0];

		$et = new Date( $row['event_end_date'] );
		$rows = (($et->getHour()*60 + $et->getMinute()) - ($this_day->getHour()*60 + $this_day->getMinute()))/$inc;

		$href = "?m=calendar&a=view&event_id=".$row['event_id'];
		$alt = $row['event_description'];

		$html .= "\n\t<td class=\"event\" rowspan=\"$rows\" valign=\"top\">";

		$html .= "\n<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\"><tr>";
		$html .= "\n<td>" . dPshowImage( dPfindImage( 'event'.$row['event_type'].'.png', 'calendar' ), 16, 16, '' );
		$html .= "</td>\n<td>&nbsp;<b>" . $types[$row['event_type']] . "</b></td></tr></table>";


		$html .= $href ? "\n\t\t<a href=\"$href\" class=\"event\" title=\"$alt\">" : '';
		$html .= "\n\t\t{$row['event_title']}";
		$html .= $href ? "\n\t\t</a>" : '';
		$html .= "\n\t</td>";
	} else {
		$html .= "\n\t<td></td>";
	}

	$html .= "\n</tr>";

	$this_day->addSeconds( 60*$inc );
}


$html .= '</table>';
echo $html;
?>