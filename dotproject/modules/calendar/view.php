<?php /* CALENDAR $Id$ */
$event_id = intval( dPgetParam( $_GET, "event_id", 0 ) );

// check permissions for this record
$canEdit = !getDenyEdit( $m, $event_id );

// check if this record has dependancies to prevent deletion
$msg = '';
$obj = new CEvent();
$canDelete = $obj->canDelete( $msg, $event_id );

// load the record data
if (!$obj->load( $event_id )) {
	$AppUI->setMsg( 'Event' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// load the event types
$types = dPgetSysVal( 'EventType' );

if ($obj->event_owner != $AppUI->user_id) {
	$canEdit = false;
}
$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$start_date = $obj->event_start_date ? new CDate( $obj->event_start_date ) : null;
$end_date = $obj->event_end_date ? new CDate( $obj->event_end_date ) : null;

// setup the title block
$titleBlock = new CTitleBlock( 'View Event', 'myevo-appointments.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
		'<form action="?m=calendar&a=addedit&event_id=' . $event_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( "?m=calendar&date=".$start_date->format( FMT_TIMESTAMP_DATE ), "month view" );
if ($canEdit) {
	$titleBlock->addCrumb( "?m=calendar&a=day_view&date=".$start_date->format( FMT_TIMESTAMP_DATE ), "day view" );
	$titleBlock->addCrumb( "?m=calendar&a=addedit&event_id=$event_id", "edit this event" );
	if ($canEdit) {
		$titleBlock->addCrumbDelete( 'delete event', $canDelete, $msg );
	}
}
$titleBlock->show();
?>
<script language="javascript">
function delIt() {
	if (confirm( "<?php echo $AppUI->_('eventDelete');?>" )) {
		document.frmDelete.submit();
	}
}
</script>

<table border="0" cellpadding="4" cellspacing="0" width="100%" class="std">

<form name="frmDelete" action="./index.php?m=calendar" method="post">
	<input type="hidden" name="dosql" value="do_event_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="event_id" value="<?php echo $event_id;?>" />
</form>

<tr>
	<td valign="top" width="50%">
		<strong><?php echo $AppUI->_('Details');?></strong>
		<table cellspacing="1" cellpadding="2" width="100%">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Event Title');?>:</td>
			<td class="hilite" width="100%"><?php echo $obj->event_title;?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Type');?>:</td>
			<td class="hilite" width="100%"><?php echo $types[$obj->event_type];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Starts');?>:</td>
			<td class="hilite"><?php echo $start_date ? $start_date->format( "$df $tf" ) : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Ends');?>:</td>
			<td class="hilite"><?php echo $end_date ? $end_date->format( "$df $tf" ) : '-';?></td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Description');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $obj->event_description);?>&nbsp;
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>