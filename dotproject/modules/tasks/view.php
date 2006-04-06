<?php /* $Id$ */
$task_id = intval( dPgetParam( $_GET, 'task_id', 0 ) );
$task_log_id = intval( dPgetParam( $_GET, 'task_log_id', 0 ) );
$reminded = intval( dPgetParam( $_GET, 'reminded', 0) );

// check permissions for this record
$canRead = !getDenyRead( $m, $task_id );
$canEdit = !getDenyEdit( $m, $task_id );
// check permissions for this record
$canReadModule = !getDenyRead( $m );


if (!$canRead) {
	$AppUI->redirect( 'm=public&a=access_denied' );
}
$q =& new DBQuery;
$perms =& $AppUI->acl();

// Process pin/unpin of a task.
if (isset($_GET['pin']))
{
        $pin = intval( dPgetParam( $_GET, 'pin', 0 ) );
        $msg = '';

        // load the record data
        if($pin) {
                $q->addTable('user_task_pin');
                $q->addInsert('user_id', $AppUI->user_id);
                $q->addInsert('task_id', $task_id);
        } else {
                $q->setDelete('user_task_pin');
                $q->addWhere('user_id = ' . $AppUI->user_id);
                $q->addWhere('task_id = ' . $task_id);
        }

        if ( !$q->exec() )
                $AppUI->setMsg( 'ins/del err', UI_MSG_ERROR, true );

        $AppUI->redirect('', -1);
}


$q->addTable('tasks');
$q->leftJoin('users', 'u1', 'u1.user_id = task_owner');
$q->leftJoin('projects', 'p', 'p.project_id = task_project');
$q->leftJoin('task_log', 'tl', 'tl.task_log_task = tasks.task_id');
$q->leftJoin('user_task_pin', 'utp', 'utp.task_id = tasks.task_id AND utp.user_id = ' . $AppUI->user_id);
$q->addWhere('tasks.task_id = ' . $task_id);
$q->addQuery('tasks.*');
$q->addQuery('task_pinned');
$q->addQuery('project_name, project_color_identifier');
$q->addQuery('u1.user_username as username');
$q->addQuery('ROUND(SUM(task_log_hours),2) as log_hours_worked');
$q->addGroup('tasks.task_id');

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CTask();
$canDelete = $obj->canDelete( $msg, $task_id );

//$obj = null;
$sql = $q->prepare();
$q->clear();

if (!db_loadObject( $sql, $obj, true, false )) {
	$AppUI->setMsg( 'Task' );
	$AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}

if (!$obj->canAccess( $AppUI->user_id )) {
	$AppUI->redirect( 'm=public&a=access_denied' );
}

// Clear any reminders
if ($reminded)
	$obj->clearReminder();

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'TaskLogVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'TaskLogVwTab' ) !== NULL ? $AppUI->getState( 'TaskLogVwTab' ) : 0;

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');
//Also view the time
$df .= ' ' . $AppUI->getPref('TIMEFORMAT');

$start_date = intval( $obj->task_start_date ) ? new CDate( $obj->task_start_date ) : null;
$end_date = intval( $obj->task_end_date ) ? new CDate( $obj->task_end_date ) : null;

//check permissions for the associated project
$canReadProject = !getDenyRead( 'projects', $obj->task_project);

// get the users on this task
$q->addTable('users', 'u');
$q->addTable('user_tasks', 't');
$q->leftJoin('contacts', 'c' , 'u.user_contact = c.contact_id');
$q->addQuery('u.user_id, u.user_username, c.contact_email');
$q->addWhere('t.task_id = ' . $task_id);
$q->addWhere('t.user_id = u.user_id');
$q->addOrder('u.user_username');

$sql = $q->prepare();
$q->clear();
$users = db_loadList( $sql );

$durnTypes = dPgetSysVal( 'TaskDurationType' );
$taskPriority = dPgetSysVal( 'TaskPriority' );

// setup the title block
$titleBlock = new CTitleBlock( 'View Task', 'applet-48.png', $m, "$m.$a" );
$titleBlock->addCell(
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new task').'">', '',
		'<form action="?m=tasks&a=addedit&task_project='.$obj->task_project.'&task_parent=' . $task_id . '" method="post">', '</form>'
	);
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new file').'">', '',
		'<form action="?m=files&a=addedit&project_id=' . $obj->task_project . '&file_task=' . $obj->task_id . '" method="post">', '</form>'
	);
}
$titleBlock->addCrumb( '?m=tasks', 'tasks list' );
if ($canReadProject) {
	$titleBlock->addCrumb( "?m=projects&a=view&project_id=$obj->task_project", 'view this project' );
}
if ($canEdit) {
	$titleBlock->addCrumb( "?m=tasks&a=addedit&task_id=$task_id", 'edit this task' );
}
if ($canDelete) {
	$titleBlock->addCrumbDelete( 'delete task', $canDelete, $msg );
}
$titleBlock->show();

$task_types = dPgetSysVal('TaskType');

// Pull tasks dependencies
$q->addQuery('td.dependencies_req_task_id, t.task_name');
$q->addTable('tasks', 't');
$q->addTable('task_dependencies', 'td');
$q->addWhere('td.dependencies_req_task_id = t.task_id');
$q->addWhere('td.dependencies_task_id = ' . $task_id);

$taskDep = $q->loadHashList();

// Pull the tasks depending on this Task 
$q->addQuery('td.dependencies_task_id, t.task_name');
$q->addTable('tasks', 't');
$q->addTable('task_dependencies', 'td');
$q->addWhere('td.dependencies_task_id = t.task_id');
$q->addWhere('td.dependencies_req_task_id = ' . $task_id);
$dependingTasks = $q->loadHashList();

$q->addTable('departments', 'd');
$q->addTable('task_departments', 't');
$q->addWhere('t.department_id = d.dept_id');
$q->addWhere('t.task_id = ' . $task_id);
$q->addQuery('dept_id, dept_name, dept_phone');
$depts = $q->loadHashList('dept_id');

if ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) 
{
	$q->addTable('contacts', 'c');
	$q->leftJoin('task_contacts', 'tc', 'tc.contact_id = c.contact_id');
	$q->leftJoin('departments', 'd', 'dept_id = contact_department');
	$q->addWhere('tc.task_id = ' . $obj->task_id);
	$q->addQuery('c.contact_id, contact_first_name, contact_last_name, contact_email');
	$q->addQuery('contact_phone, dept_name');
	$q->addWhere("( contact_owner = '$AppUI->user_id' or contact_private = '0')");
	$task_contacts = $q->loadHashList('contact_id');

	$q->addTable('contacts', 'c');
	$q->leftJoin('project_contacts', 'pc', 'pc.contact_id = c.contact_id');
	$q->leftJoin('departments', 'd', 'd.dept_id = c.contact_department');
	$q->addWhere('pc.project_id = ' . $obj->task_project);
	$q->addQuery('c.contact_id, contact_first_name, contact_last_name, contact_email');
	$q->addQuery('contact_phone, dept_name');
	$q->addWhere("( contact_owner = '$AppUI->user_id' or contact_private = '0')");
	$project_contacts = $q->loadHashList('contact_id');
}

require_once  $AppUI->getSystemClass( 'CustomFields' );
$custom_fields = new CustomFields( $m, $a, $obj->task_id, 'view' );

$tpl->assign('custom_fields', $custom_fields->getHTML());
$tpl->assign('users', $users);
$tpl->assign('project_contacts', $project_contacts);
$tpl->assign('task_contacts', $task_contacts);
$tpl->assign('depts', $depts);
$tpl->assign('dependingTasks', $dependingTasks);
$tpl->assign('taskDep', $taskDep);

$tpl->assign('project_id', $project_id);
$tpl->assign('task_id', $task_id);

if ($obj->task_parent != $obj->task_id)
{
	$obj_parent = new CTask();
	$obj_parent->load($obj->task_parent);
	$tpl->assign('obj_parent', $obj_parent);
}


$obj->task_type_display = $task_types[$obj->task_type];
$obj->task_priority_display = $taskPriority[$obj->task_priority];
$obj->task_duration_type_display = $durnTypes[$obj->task_duration_type];
$obj->task_hours_worked_display = $obj->task_hours_worked + @rtrim($obj->log_hours_worked, "0");
$tpl->displayView($obj);
?>

<script language="JavaScript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.task_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.task_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>

function updateTask() {
	var f = document.editFrm;
	if (f.task_log_description.value.length < 1) {
		alert( "<?php echo $AppUI->_('tasksComment', UI_OUTPUT_JS);?>" );
		f.task_log_description.focus();
	} else if (isNaN( parseInt( f.task_percent_complete.value+0 ) )) {
		alert( "<?php echo $AppUI->_('tasksPercent', UI_OUTPUT_JS);?>" );
		f.task_percent_complete.focus();
	} else if(f.task_percent_complete.value  < 0 || f.task_percent_complete.value > 100) {
		alert( "<?php echo $AppUI->_('tasksPercentValue', UI_OUTPUT_JS);?>" );
		f.task_percent_complete.focus();
	} else {
		f.submit();
	}
}
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Task', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete.submit();
	}
}
<?php } ?>
</script>



<?php
$query_string = '?m=tasks&a=view&task_id='.$task_id;
$tabBox = new CTabBox( $query_string, '', $tab );

$tabBox_show = 0;
if ( $obj->task_dynamic != 1 ) {
	// tabbed information boxes
	if ($perms->checkModuleItem('task_log', 'view', $task_id)) {
		$tabBox_show = 1;
		$tabBox->add( $dPconfig['root_dir'].'/modules/tasks/vw_logs', 'Task Logs' );
		// fixed bug that dP automatically jumped to access denied if user does not
		// have read-write permissions on task_id and this tab is opened by default (session_vars)
		// only if user has r-w perms on this task, new or edit log is beign showed
        if ($task_log_id == 0) {
            if ($perms->checkModuleItem('task_log', 'add', $task_id))
                $tabBox->add( $dPconfig['root_dir'].'/modules/tasks/vw_log_update', 'New Log' );
        }
        else if ($perms->checkModuleItem('task_log', 'edit', $task_id)) {
            $tabBox->add( $dPconfig['root_dir'].'/modules/tasks/vw_log_update', 'Edit Log' );
        }
	}
}

if ( count($obj->getChildren()) > 0 ) {
	// Has children
	// settings for tasks
	$f = 'children';
	$min_view = true;
	$tabBox_show = 1;
	// in the tasks file there is an if that checks
	// $_GET[task_status]; this patch is to be able to see
	// child tasks withing an inactive task
	$_GET['task_status'] = $obj->task_status;
	$tabBox->add( $dPconfig['root_dir'].'/modules/tasks/tasks', 'Child Tasks' );
}

if ($tabBox->loadExtras($m, $a))
  $tabBox_show = 1;

if ( $tabBox_show == 1)	$tabBox->show();
?>
