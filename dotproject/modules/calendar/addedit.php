<?php /* CALENDAR $Id$ */
// check permissions
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
$event_id = isset( $_GET['event_id'] ) ? $_GET['event_id'] : 0;

// Pull event information
$sql = "SELECT * FROM events WHERE event_id = $event_id";
if (!db_loadHash( $sql, $event ) && $event_id) {
	$titleBlock = new CTitleBlock( 'Invalid Event ID', 'calendar.gif', $m, 'ID_HELP_EVENT_EDIT' );
	$titleBlock->addCrumb( "?m=calendar", "month view" );
	$titleBlock->show();
} else {
// check only owner can edit
	if ($event['event_owner'] != $AppUI->user_id && $event_id != 0) {
		$AppUI->redirect( "m=public&a=access_denied" );
	}

// setup the title block
	$titleBlock = new CTitleBlock( $AppUI->_(($event_id > 0) ? "Edit Event" : "Add Event" ), 'calendar.gif', $m, 'ID_HELP_EVENT_EDIT' );
	if ($canEdit) {
		$titleBlock->addCell();
		$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
			'<form action="?m=calendar&a=addedit" method="post">', '</form>'
		);
	}
	$titleBlock->addCrumb( "?m=calendar", "month view" );
	if ($event_id) {
		$titleBlock->addCrumb( "?m=calendar&a=view&event_id=$event_id", "view this event" );
	}
	$titleBlock->show();

// format dates
	$df = $AppUI->getPref('SHDATEFORMAT');

	$start_date = $event["event_start_date"] ? new CDate( $event["event_start_date"], $df ) : new CDate();
	$end_date = $event["event_end_date"] ? new CDate( $event["event_end_date"], $df ) : new CDate();

	$start_time = $start_date->getHours() * 60 + $start_date->getMinutes();
	$start_date->setTime( 0, 0, 0 );
	$end_time = $end_date->getHours() * 60 + $end_date->getMinutes();
	$end_date->setTime( 0, 0, 0 );

	$recurs =  array (
		'Never',
		'Hourly',
		'Daily',
		'Weekly',
		'Bi-Weekly',
		'Every Month',
		'Quarterly',
		'Every 6 months',
		'Every Year'
	);

	$remind = array (
		"900" => '15 mins',
		"1800" => '30 mins',
		"3600" => '1 hour',
		"7200" => '2 hours',
		"14400" => '4 hours',
		"28800" => '8 hours',
		"56600" => '16 hours',
		"86400" => '1 day',
		"172800" => '2 days'
	);

	$times = array();
	$t = new CDate();
	$t->setTime( 0,0,0 );
	for ($m=0; $m < 1440; $m+=30) {
		$t->setTime( 0, $m, 0 );
		$times[$m] = $t->toString( "%I:%M %p" );
	}
?>

<script language="javascript">
function submitIt(){
	var form = document.changeevent;
	if (form.event_title.value.length < 1) {
		alert("Please enter a valid event title");
		form.event_title.focus();
	}
	if (form.event_start_date.value.length < 1){
		alert("Please enter a start date");
		form.event_start_date.focus();
	}
	if (form.event_end_date.value.length < 1){
		alert("Please enter an end date");
		form.event_end_date.focus();
	}
/* FUNCTIONALITY NOT YET ENABLED
	if (form.event_recurs.selectedIndex != 0 && isNaN(parseInt(form.event_times_recuring.value))){
		alert("Please select how often you wish this event to recur.");
		form.event_times_recuring.focus();
	} else {
*/
		form.submit();
/*
	}
*/
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.changeevent.event_' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.changeevent.event_' + calendarField );
	fld_fdate = eval( 'document.changeevent.' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
}
</script>

<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<form name="changeevent" action="?m=calendar&a=do_event_aed" method="post">
<input type="hidden" name="event_id" value="<?php echo $event_id;?>">
<input type="hidden" name="event_project" value="0">

<tr>
	<td width="33%" align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Event Title' );?>:</td>
	<td width="20%">
		<input type="text" class="text" size="25" name="event_title" value="<?php echo @$event["event_title"];?>" maxlength="255">
	</td>
	<td width="5%"></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Private Entry' );?>:</td>
	<td>
		<input type="checkbox" value="1" name="event_private" <?php echo (@$event["event_private"] ? 'checked' : '');?>>
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="event_start_date" value="<?php echo $start_date ? $start_date->getTimestamp() : '-1';?>">
		<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->toString() : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Time' );?>:</td>
	<td><?php echo arraySelect( $times, 'start_time', 'size="1" class="text"', $start_time ); ?></td>
</tr>

<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'End Date' );?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="event_end_date" value="<?php echo $end_date ? $end_date->getTimestamp() : '-1';?>">
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->toString() : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Time' );?>:</td>
	<td><?php echo arraySelect( $times, 'end_time', 'size="1" class="text"', $end_time ); ?></td>
</tr>
<?php /* FUNCTIONALITY NOT YET ENABLED ?>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Recurs' );?>:</td>
	<td><?php echo arraySelect( $recurs, 'event_recurs', 'size="1" class="text"', $event['event_recurs'] ); ?></td>
	<td align="right">x</td>
	<td>
		<input type="text"  name="event_times_recuring" value="<?php echo @$event["event_times_recuring"];?>" maxlength="2" size=3> <?php echo $AppUI->_( 'times' );?>
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Remind Me' );?>:</td>
	<td><?php echo arraySelect( $remind, 'event_remind', 'size="1" class="text"', $event['event_remind'] ); ?> <?php echo $AppUI->_( 'in advance' );?></td>
</tr>
<?php */ ?>
<tr>
	<td valign="top" align="right"><?php echo $AppUI->_( 'Description' );?></td>
	<td align="left" colspan="3">
		<textarea class="textarea" name="event_description" rows="5" cols="45"><?php echo @$event["event_description"];?></textarea></td>
	</td>
</tr>
<tr>
	<td colspan="2">
		<input type="button" value="<?php echo $AppUI->_( 'back' );?>" class="button" onclick="javascript:history.back();">
	</td>
	<td align="right" colspan="2">
		<input type="button" value="<?php echo $AppUI->_( 'submit' );?>" class="button" onClick="submitIt()">
	</td>
</tr>
</form>
</table>
<?php } ?>