<?php /* CALENDAR $Id$ */
$event_id = intval( dPgetParam( $_GET, "event_id", 0 ) );

// check permissions
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// get the passed timestamp (today if none)
$date = dPgetParam( $_GET, 'date', null );

// load the record data
$obj = new CEvent();

if (!$obj->load( $event_id ) && $event_id) {
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}
// check only owner can edit
if ($obj->event_owner != $AppUI->user_id && $event_id != 0) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the event types
$types = dPgetSysVal( 'EventType' );

// setup the title block
$titleBlock = new CTitleBlock( ($event_id ? "Edit Event" : "Add Event") , 'myevo-appointments.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar", "month view" );
if ($event_id) {
	$titleBlock->addCrumb( "?m=calendar&a=view&event_id=$event_id", "view this event" );
}
$titleBlock->show();

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

// pull projects
$sql = "SELECT project_id, project_name FROM projects ORDER BY project_name";
$all_projects = '(' . $AppUI->_('All') . ')';
$projects = arrayMerge( array( 0 => $all_projects ), db_loadHashList( $sql ) );

if ($event_id) {
	$start_date = intval( $obj->event_start_date ) ? new CDate( $obj->event_start_date ) : null;
	$end_date = intval( $obj->event_end_date ) ? new CDate( $obj->event_end_date ) : $start_date;
} else {
	$start_date = new CDate( $date );
	$start_date->setTime( 8,0,0 );
	$end_date = new CDate( $date );
	$end_date->setTime( 17,0,0 );
}

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

// build array of times in 30 minute increments
$times = array();
$t = new CDate();
$t->setTime( 0,0,0 );
if (!defined('LOCALE_TIME_FORMAT'))
  define('LOCALE_TIME_FORMAT', '%I:%M %p');
for ($m=0; $m < 60; $m++) {
	$times[$t->format( "%H%M%S" )] = $t->format( LOCALE_TIME_FORMAT );
	$t->addSeconds( 1800 );
}
?>

<script language="javascript">
function submitIt(){
	var form = document.editFrm;
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
	idate = eval( 'document.editFrm.event_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.event_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}
</script>

<table cellspacing="1" cellpadding="2" border="0" width="100%" class="std">
<form name="editFrm" action="?m=calendar" method="post">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="event_id" value="<?php echo $event_id;?>" />
	<input type="hidden" name="event_project" value="0" />

<tr>
	<td width="33%" align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Event Title' );?>:</td>
	<td width="20%">
		<input type="text" class="text" size="25" name="event_title" value="<?php echo @$obj->event_title;?>" maxlength="255">
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Type');?>:</td>
	<td>
<?php
	echo arraySelect( $types, 'event_type', 'size="1" class="text"', @$obj->event_type, true );
?>
	</td>
</tr>
	
<tr>
	<td align="right"><?php echo $AppUI->_('Project');?>:</td>
	<td>
<?php
	echo arraySelect( $projects, 'event_project', 'size="1" class="text"', @$obj->event_project );
?>
	</td>
</tr>	


<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Private Entry' );?>:</td>
	<td>
		<input type="checkbox" value="1" name="event_private" <?php echo (@$obj->event_private ? 'checked' : '');?>>
	</td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Start Date' );?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="event_start_date" value="<?php echo $start_date ? $start_date->format( FMT_TIMESTAMP_DATE ) : '';?>">
		<input type="text" name="start_date" value="<?php echo $start_date ? $start_date->format( $df ) : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Time' );?>:</td>
	<td><?php echo arraySelect( $times, 'start_time', 'size="1" class="text"', $start_date->format( "%H%M%S" ) ); ?></td>
</tr>

<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'End Date' );?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="event_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>">
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled">
		<a href="#" onClick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Time' );?>:</td>
	<td><?php echo arraySelect( $times, 'end_time', 'size="1" class="text"', $end_date->format( "%H%M%S" ) ); ?></td>
</tr>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Recurs' );?>:</td>
	<td><?php echo arraySelect( $recurs, 'event_recurs', 'size="1" class="text"', $obj->event_recurs, true ); ?></td>
	<td align="right">x</td>
	<td>
		<input type="text"  name="event_times_recuring" value="<?php echo @$obj->event_times_recuring;?>" maxlength="2" size=3> <?php echo $AppUI->_( 'times' );?>
	</td>
</tr>
<?php /* FUNCTIONALITY NOT YET ENABLED ?>
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Remind Me' );?>:</td>
	<td><?php echo arraySelect( $remind, 'event_remind', 'size="1" class="text"', $obj['event_remind'] ); ?> <?php echo $AppUI->_( 'in advance' );?></td>
</tr>
<?php */ ?>
<tr>
	<td valign="top" align="right"><?php echo $AppUI->_( 'Description' );?></td>
	<td align="left" colspan="3">
		<textarea class="textarea" name="event_description" rows="5" cols="45"><?php echo @$obj->event_description;?></textarea></td>
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
