<?php /* CALENDAR $Id$ */
$event_id = dPgetParam( $_GET, "event_id", 0 );

// check permissions for this event
$canEdit = !getDenyEdit( $m, $event_id );
$AppUI->savePlace();

// pull data
$sql = "SELECT * FROM events WHERE event_id = $event_id";
db_loadHash( $sql, $event );

// setup the title block
if (!db_loadHash( $sql, $event )) {
	$titleBlock = new CTitleBlock( 'Invalid Event ID', 'myevo-appointments.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=calendar", "month view" );
	$titleBlock->show();
} else {
	if ($event['event_owner'] != $AppUI->user_id) {
		$canEdit = false;
	}
	$df = $AppUI->getPref('SHDATEFORMAT');
	$tf = $AppUI->getPref('TIMEFORMAT');

	$start_date = $event["event_start_date"] ? new CDate( $event["event_start_date"], "$df $tf" ) : null;
	$end_date = $event["event_end_date"] ? new CDate( $event["event_end_date"], "$df $tf" ) : null;

// setup the title block
	$titleBlock = new CTitleBlock( 'View Event', 'myevo-appointments.png', $m, "$m.$a" );
	if ($canEdit) {
		$titleBlock->addCell();
		$titleBlock->addCell(
			'<input type="submit" class="button" value="'.$AppUI->_('new event').'">', '',
			'<form action="?m=calendar&a=addedit&event_id=' . $event_id . '" method="post">', '</form>'
		);
	}
	$titleBlock->addCrumb( "?m=calendar", "month view" );
	if ($canEdit) {
		$titleBlock->addCrumb( "?m=calendar&a=addedit&event_id=$event_id", "edit this event" );
		if ($canDelete) {
			$titleBlock->addCrumbRight(
				'<a href="javascript:delIt()">'
					. '<img align="absmiddle" src="' . dPfindImage( 'trash.gif', $m ) . '" width="16" height="16" alt="" border="0" />&nbsp;'
					. $AppUI->_('delete event') . '</a>'
			);
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
			<td class="hilite" width="100%"><?php echo $event["event_title"];?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Starts');?>:</td>
			<td class="hilite"><?php echo $start_date ? $start_date->toString() : '-';?></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Ends');?>:</td>
			<td class="hilite"><?php echo $end_date ? $end_date->toString() : '-';?></td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<strong><?php echo $AppUI->_('Description');?></strong>
		<table cellspacing="0" cellpadding="2" border="0" width="100%">
		<tr>
			<td class="hilite">
				<?php echo str_replace( chr(10), "<br />", $event["event_description"]);?>&nbsp;
			</td>
		</tr>
		</table>

	</td>
</tr>
</table>
<?php } ?>