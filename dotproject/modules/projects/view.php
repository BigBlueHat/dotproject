<?php /* PROJECTS $Id$ */
$project_id = intval( dPgetParam( $_GET, 'project_id', 0 ) );

// check permissions for this record
$perms =& $AppUI->acl();
$canRead = $perms->checkModuleItem( $m, 'view', $project_id );
$canEdit = $perms->checkModuleItem( $m, 'edit', $project_id );
$canEditT = $perms->checkModule( 'tasks', 'add');

if (!$canRead) {
	$AppUI->redirect( 'm=public&a=access_denied' );
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ProjVwTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ProjVwTab' ) !== NULL ? $AppUI->getState( 'ProjVwTab' ) : 0;

// check if this record has dependencies to prevent deletion
$msg = '';
$obj = new CProject();
// Now check if the proect is editable/viewable.
$denied = $obj->getDeniedRecords($AppUI->user_id);
if (in_array($project_id, $denied)) {
	$AppUI->redirect( 'm=public&a=access_denied' );
}

$canDelete = $obj->canDelete( $msg, $project_id );

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $obj->getCriticalTasks($project_id) : NULL;

// get ProjectPriority from sysvals
$projectPriority = dPgetSysVal( 'ProjectPriority' );
$projectPriorityColor = dPgetSysVal( 'ProjectPriorityColor' );

$working_hours = ($dPconfig['daily_working_hours']?$dPconfig['daily_working_hours']:8);

// load the record data
// GJB: Note that we have to special case duration type 24 and this refers to the hours in a day, NOT 24 hours
$q  = new DBQuery;
$q->addTable('projects');
$q->addQuery('company_name');
$contact_full_name = $q->concat('contact_last_name', "', '" , 'contact_first_name');
$q->addQuery($contact_full_name.' user_name');
$q->addQuery('projects.*');
$q->addQuery('SUM(t1.task_duration * t1.task_percent_complete * IF(t1.task_duration_type = 24, '.$working_hours
             .', t1.task_duration_type)) / SUM(t1.task_duration * IF(t1.task_duration_type = 24, '.$working_hours
             .', t1.task_duration_type)) AS project_percent_complete');
$q->addJoin('companies', 'com', 'company_id = project_company');
$q->addJoin('users', 'u', 'user_id = project_owner');
$q->addJoin('contacts', 'con', 'contact_id = user_contact');
$q->addJoin('tasks', 't1', 'projects.project_id = t1.task_project');
$q->addWhere('project_id = '.$project_id);
$q->addGroup('project_id');
$sql = $q->prepare();
echo $sql;
$q->clear();

$obj = null;
if (!db_loadObject( $sql, $obj )) {
	$AppUI->setMsg( 'Project' );
	$AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
	$AppUI->redirect();
} else {
	$AppUI->savePlace();
}
$obj->project_type_name = $ptype[$obj->project_type];
$obj->project_status_name = $pstatus[$obj->project_status];
$obj->project_priority_name = $projectPriority[$obj->project_priority];
$obj->project_priority_color = $projectPriorityColor[$obj->project_priority];

// worked hours
// by definition milestones don't have duration so even if they specified, they shouldn't add up
// the sums have to be rounded to prevent the sum form having many (unwanted) decimals because of the mysql floating point issue
// more info on http://www.mysql.com/doc/en/Problems_with_float.html
$q->addTable('task_log');
$q->addTable('tasks');
$q->addQuery('ROUND(SUM(task_log_hours),2)');
$q->addWhere("task_log_task = task_id AND task_project = $project_id AND task_milestone ='0'");
$worked_hours = $q->loadResult();
$worked_hours = rtrim($worked_hours, '.');
$q->clear();

$q->addTable('tasks');
$q->addQuery('SUM(task_duration * (100 - task_percent_complete) * IF(task_duration_type = 24, '.$working_hours
             .', task_duration_type))');
$q->addWhere('task_project = ' . $project_id);
$q->addWhere('task_milestone = 0');
$q->addWhere('task_dynamic != 1');
$remaining_hours = $q->loadResult() / 100;

// total hours
// same milestone comment as above, also applies to dynamic tasks
$q->addTable('tasks');
$q->addQuery('ROUND(SUM(task_duration),2)');
$q->addWhere("task_project = $project_id AND task_duration_type = 24 AND task_milestone ='0' AND task_dynamic != 1");
$days = $q->loadResult();
$q->clear();

$q->addTable('tasks');
$q->addQuery('ROUND(SUM(task_duration),2)');
$q->addWhere("task_project = $project_id AND task_duration_type = 1 AND task_milestone  ='0' AND task_dynamic != 1");
$hours = $q->loadResult();
$q->clear();
$total_hours = $days * $dPconfig['daily_working_hours'] + $hours;

$total_project_hours = 0;

$q->addTable('tasks', 't');
$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
$q->addWhere("t.task_project = $project_id AND t.task_duration_type = 24 AND t.task_milestone ='0' AND t.task_dynamic != 1");
$total_project_days_sql = $q->loadResult();
$q->clear();

$q->addTable('tasks', 't');
$q->addQuery('ROUND(SUM(t.task_duration*u.perc_assignment/100),2)');
$q->addJoin('user_tasks', 'u', 't.task_id = u.task_id');
$q->addWhere("t.task_project = $project_id AND t.task_duration_type = 1 AND t.task_milestone  ='0' AND t.task_dynamic != 1");
$total_project_hours_sql = $q->loadResult();
$q->clear();

$q  = new DBQuery;
$q->addTable('contacts', 'a');
$q->addTable('project_contacts', 'b');
$q->addJoin('departments', 'c', 'a.contact_department = c.dept_id', 'left outer');			
$q->addQuery('a.contact_id, a.contact_first_name, a.contact_last_name, a.contact_email, a.contact_phone, c.dept_name');
$q->addWhere('a.contact_id = b.contact_id');
$q->addWhere('b.project_id = ' . $project_id);
$q->addWhere("(contact_owner = '{$AppUI->user_id}' or contact_private='0')");

$contacts = $q->loadHashList("contact_id");
foreach($contacts as $contact_id => $contact)
{
	if (!$perms->checkModuleItem('contacts', 'edit', $contact_id))
		unset($contacts[$contact_id]);
}

$q  = new DBQuery;
$q->addTable('departments', 'a');
$q->addTable('project_departments', 'b');
$q->addQuery('a.dept_id, a.dept_name, a.dept_phone');
$q->addWhere('a.dept_id = b.department_id');
$q->addWhere('b.project_id = ' . $project_id);
$depts = $q->loadHashList('dept_id');
		
		

$total_project_hours = $total_project_days_sql * $dPconfig['daily_working_hours'] + $total_project_hours_sql;
//due to the round above, we don't want to print decimals unless they really exist
//$total_project_hours = rtrim($total_project_hours, "0");

// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

// create Date objects from the datetime fields
//$start_date = intval( $obj->project_start_date ) ? new CDate( $obj->project_start_date ) : null;
//$end_date = intval( $obj->project_end_date ) ? new CDate( $obj->project_end_date ) : null;
$obj->actual_end_date = intval( $criticalTasks[0]['task_end_date'] ) ? new CDate( $criticalTasks[0]['task_end_date'] ) : null;

$style = (( $actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$titleBlock = new CTitleBlock( 'View Project', 'applet3-48.png', $m, "$m.$a" );

// patch 2.12.04 text to search entry box
if (isset( $_POST['searchtext'] )) {
	$AppUI->setState( 'searchtext', $_POST['searchtext']);
}

$search_text = $AppUI->getState('searchtext') ? $AppUI->getState('searchtext'):'';
$titleBlock->addCell( '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . $AppUI->_('Search') . ':' );
$titleBlock->addCell(
	'
<form action="?m=projects&amp;a=view&amp;project_id='.$project_id.'" method="post" id="searchfilter">
	<input type="text" class="text" size="10" name="searchtext" onchange="document.searchfilter.submit();" value="'.$search_text.'" title="'. $AppUI->_('Search in name and description fields') . '" />
</form>', '', '', '');

if ($canEditT) {
	$titleBlock->addCell();
	$titleBlock->addCell('<form action="?m=tasks&amp;a=addedit&amp;task_project='.$project_id
                         .'" method="post"><input type="submit" class="button" value="'.$AppUI->_('new task')
                         .'" /></form>', '',	'', '');
}
if ($canEdit) {
	$titleBlock->addCell();
	$titleBlock->addCell('<form action="?m=calendar&amp;a=addedit&amp;event_project='.$project_id
                         .'" method="post"><input type="submit" class="button" value="'.$AppUI->_('new event')
                         .'" /></form>', '', '', '');
	$titleBlock->addCell();
	$titleBlock->addCell('<form action="?m=files&amp;a=addedit&amp;project_id='.$project_id
                         .'" method="post"><input type="submit" class="button" value="'.$AppUI->_('new file')
                         .'" /></form>', '',	'', '');
}

$titleBlock->addCrumb( '?m=projects', 'projects list' );
if ($canEdit) {
	$titleBlock->addCrumb( '?m=projects&amp;a=addedit&amp;project_id='.$project_id, 'edit this project' );
	if ($canDelete) {
		$titleBlock->addCrumbDelete( 'delete project', $canDelete, $msg );
	}
	$titleBlock->addCrumb('?m=tasks&amp;a=organize&amp;project_id='.$project_id, 'organize tasks');
}
$titleBlock->addCrumb( '?m=reports&amp;project_id='.$project_id, 'reports' );
$titleBlock->show();

require_once($baseDir . '/classes/CustomFields.class.php');
$custom_fields = New CustomFields( $m, $a, $obj->project_id, 'view' );

$tpl->assign('custom_fields', $custom_fields->getHTML());
$tpl->assign('style', $style);

$tpl->assign('project_id', $project_id);
$tpl->assign('viewCompany', $perms->checkModuleItem('companies', 'access', $obj->project_company));

$tpl->assign('critical_task', $criticalTasks[0]['task_id']);

$tpl->assign('total_project_hours', $total_project_hours);
$tpl->assign('total_hours', $total_hours);
$tpl->assign('worked_hours', $worked_hours);
$tpl->assign('remaining_hours', $remaining_hours);

$tpl->assign('departments', $depts);
$tpl->assign('contacts', $contacts);

$tpl->displayView($obj);
?>

<script type="text/javascript" language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt() {
	if (confirm( "<?php echo $AppUI->_('doDelete', UI_OUTPUT_JS).' '.$AppUI->_('Project', UI_OUTPUT_JS).'?';?>" )) {
		document.frmDelete.submit();
	}
 }
<?php } ?>
</script>

<?php
$tabBox = new CTabBox( "?m=projects&amp;a=view&amp;project_id=$project_id", "", $tab );
$query_string = "?m=projects&amp;a=view&amp;project_id=$project_id";
// tabbed information boxes
// Note that we now control these based upon module requirements.

$canViewTask = $perms->checkModule('tasks', 'view');
/*
if ($canViewTask) {
	$taskStatus = dPgetSysVal('TaskStatus');
	foreach ($taskStatus as $ts) {
		$tabBox->add( dPgetConfig('root_dir')."/modules/tasks/vw_tasks", 'Tasks ('.$ts.')' );
	}
}
*/
$tabBox->loadExtras($m, 'view');
if ($perms->checkModule('forums', 'view'))
	$tabBox->add( dPgetConfig('root_dir')."/modules/projects/vw_forums", 'Forums' );
//if ($perms->checkModule('files', 'view'))
//	$tabBox->add( dPgetConfig('root_dir')."/modules/projects/vw_files", 'Files' );
if ($canViewTask) {
	$tabBox->add( dPgetConfig('root_dir')."/modules/tasks/viewgantt", 'Gantt Chart' );
//	$tabBox->add( dPgetConfig('root_dir')."/modules/projects/vw_logs", 'Task Logs' );
}

// deprecated:
$tabBox->loadExtras($m);
$f = 'all';
$min_view = true;

$tabBox->show();
?>
