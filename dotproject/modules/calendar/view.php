<?php /* CALENDAR $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$event_id = intval(dPgetParam($_GET, 'event_id', 0));

// check permissions for this record
$perms =& $AppUI->acl();
$canEdit = $perms->checkModuleItem($m, 'edit', $event_id);

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CEvent();
$canDelete = $obj->canDelete( $msg, $event_id );

// load the record data
if (!$obj->load( $event_id )) {
	$AppUI->setMsg('Event');
	$AppUI->setMsg('invalidID', UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

// load the event types
$types = dPgetSysVal('EventType');

// load the event recurs types
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

$assigned = $obj->getAssigned();
// Add contacts to the assigned list.
$assigned = array_merge($assigned, $obj->getAssignedContacts());

if (($obj->event_owner != $AppUI->user_id) && !($perms->checkModule("admin", "view"))) {
 $canEdit = false;
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$start_date = $obj->event_start_date ? new CDate( $obj->event_start_date ) : null;
$end_date = $obj->event_end_date ? new CDate( $obj->event_end_date ) : null;
$q = new DBQuery();
$q->addQuery('project_name');
$q->addTable('projects');
$q->addWhere('project_id = ' . $obj->event_project);
$event_project = $q->loadResult();

$q->addQuery('task_name');
$q->addTable('tasks');
$q->addWhere('task_id = ' . $obj->event_task);
$event_task = $q->loadResult();

// setup the title block
$titleBlock = new CTitleBlock( 'View Event', 'myevo-appointments.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell('
<form action="?m=calendar&amp;a=addedit" method="post">
	<input type="submit" class="button" value="'.$AppUI->_('new event').'" />
</form>', '', '', '');
}
$titleBlock->addCrumb( '?m=calendar&amp;date='.$start_date->format( FMT_TIMESTAMP_DATE ), 'month view' );
$titleBlock->addCrumb( '?m=calendar&amp;a=day_view&amp;date='.$start_date->format( FMT_TIMESTAMP_DATE ), 'day view' );
if ($canEdit) {
	$titleBlock->addCrumb( '?m=calendar&amp;a=addedit&amp;event_id='.$event_id, 'edit this event' );
	if ($canDelete) {
		$titleBlock->addCrumbDelete( 'delete event', $canDelete, $msg );
	}
}
$titleBlock->show();

require_once $AppUI->getSystemClass("CustomFields");
$custom_fields = New CustomFields( $m, $a, $obj->event_id, "view" );
$tpl->assign('custom_fields', $custom_fields->getHTML());
$tpl->assign('recurs', $recurs[$obj->event_recurs]);
$tpl->assign('type', $types[$obj->event_type]);
$tpl->assign('assigned', $assigned);
$tpl->assign('event_project', $event_project);
$tpl->assign('event_task', $event_task);
$tpl->assign('event_id', $event_id);
$tpl->displayView($obj);
?>
<script type="text/javascript" language="javascript">
<!--
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canDelete) {
?>
function delIt() {
	if (confirm( "<?php echo $AppUI->_('eventDelete', UI_OUTPUT_JS);?>" )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
-->
</script>
