<?php /* TASKS $Id$ */
GLOBAL $m, $a, $project_id, $f, $task_status, $min_view, $query_string, $durnTypes;
GLOBAL $task_sort_item1, $task_sort_type1, $task_sort_order1;
GLOBAL $task_sort_item2, $task_sort_type2, $task_sort_order2;
GLOBAL $user_id, $dPconfig, $currentTabId, $currentTabName, $canEdit, $showEditCheckbox;
/*
        tasks.php

        This file contains common task list rendering code used by
        modules/tasks/index.php and modules/projects/vw_tasks.php

        in

        External used variables:

        * $min_view: hide some elements when active (used in the vw_tasks.php)
        * $project_id
        * $f
        * $query_string
*/

if (empty($query_string)) {
        $query_string = "?m=$m&a=$a";
}

// Number of columns (used to calculate how many columns to span things through)
$cols = 13;

/****
// Let's figure out which tasks are selected
*/

global $tasks_opened;
global $tasks_closed;

$tasks_closed = array();
$tasks_opened = $AppUI->getState("tasks_opened");
if(!$tasks_opened){
    $tasks_opened = array();
}

$task_id = intval( dPgetParam( $_GET, "task_id", 0 ) );
$q = new DBQuery;
$pinned_only = intval( dPgetParam( $_GET, 'pinned', 0) );
if (isset($_GET['pin']))
{
        $pin = intval( dPgetParam( $_GET, "pin", 0 ) );
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
                $AppUI->setMsg( "ins/del err", UI_MSG_ERROR, true );

        $AppUI->redirect('', -1);
}
else if($task_id > 0)
        $tasks_opened[] = $task_id;


$AppUI->savePlace();

if( ($open_task_id = dPGetParam($_GET, 'open_task_id', 0)) > 0
        && !in_array($_GET['open_task_id'], $tasks_opened)) {
    $tasks_opened[] = $_GET["open_task_id"];
}

// Closing tasks needs also to be within tasks iteration in order to
// close down all child tasks
if(($close_task_id = dPGetParam($_GET, 'close_task_id', 0)) > 0) {
    closeOpenedTask($close_task_id);
}

// We need to save tasks_opened until the end because some tasks are closed within tasks iteration
//echo "<pre>"; print_r($tasks_opened); echo "</pre>";
/// End of tasks_opened routine


$durnTypes = dPgetSysVal( 'TaskDurationType' );
$taskPriority = dPgetSysVal( 'TaskPriority' );

$task_project = intval( dPgetParam( $_GET, 'task_project', null ) );
//$task_id = intval( dPgetParam( $_GET, 'task_id', null ) );

$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', '' );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', '' );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', '' );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', '' );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', 0 ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', 0 ) );
if (isset($_POST['show_task_options'])) {
        $AppUI->setState('TaskListShowIncomplete', dPgetParam($_POST, 'show_incomplete', 0));
}
$showIncomplete = $AppUI->getState('TaskListShowIncomplete', 0);

$where = '';
require_once $AppUI->getModuleClass('projects');
$project =& new CProject;
// $allowedProjects = $project->getAllowedRecords($AppUI->user_id, 'project_id, project_name');
$allowedProjects = $project->getAllowedSQL($AppUI->user_id);
$where = "";
if ( count($allowedProjects))
  $q->addWhere($allowedProjects);

$q->clear();
$q->addQuery('project_id, project_color_identifier, project_name');
$q->addQuery('COUNT(t1.task_id) as total_tasks');
$q->addQuery('SUM(t1.task_duration*t1.task_percent_complete)/SUM(t1.task_duration) as project_percent_complete');
$q->addQuery('company_name');
$q->addTable('projects');
$q->leftJoin('tasks', 't1', 'projects.project_id = t1.task_project');
$q->leftJoin('companies', 'c', 'company_id = project_company');
$q->addWhere($allowedProjects);
$q->addGroup('project_id');
$q->addOrder('project_name');

//echo "<pre>$psql</pre>";

$perms =& $AppUI->acl();
$projects = array();
$canViewTask = $perms->checkModule('tasks', 'view');
if ($canViewTask) {
        $prc = db_exec( $q->prepare() );
        echo db_error();
        while ($row = db_fetch_assoc( $prc )) {
                $projects[$row["project_id"]] = $row;
        }
}

$q->clear();

$q->addQuery('distinct tasks.task_id, task_parent, task_name');
$q->addQuery('task_start_date, task_end_date, task_dynamic');
$q->addQuery('task_pinned, pin.user_id as pin_user');
$q->addQuery('task_priority, task_percent_complete');
$q->addQuery('task_duration, task_duration_type');
$q->addQuery('task_project');
$q->addQuery('task_description, task_owner, task_status');
$q->addQuery('usernames.user_username, usernames.user_id');
$q->addQuery('assignees.user_username as assignee_username');
$q->addQuery('count(distinct assignees.user_id) as assignee_count');
$q->addQuery('co.contact_first_name, co.contact_last_name');
$q->addQuery('task_milestone');
$q->addQuery('count(distinct f.file_task) as file_count');
$q->addQuery('tlog.task_log_problem');

$q->addTable('tasks');
$mods = $AppUI->getActiveModules();
if (!empty($mods['history']) && !getDenyRead('history'))
{
        $q->addQuery('history_date as last_update');
        $q->leftJoin('history', 'h', 'history_item = tasks.task_id AND history_table=\'tasks\'');
}
$q->leftJoin('projects', 'p', 'p.project_id = task_project');
$q->leftJoin('users', 'usernames', 'task_owner = usernames.user_id');
$q->leftJoin('user_tasks', 'ut', 'ut.task_id = tasks.task_id');
$q->leftJoin('users', 'assignees', 'assignees.user_id = ut.user_id');
$q->leftJoin('contacts', 'co', 'co.contact_id = usernames.user_contact');
$q->leftJoin('task_log', 'tlog', 'tlog.task_log_task = tasks.task_id AND tlog.task_log_problem > 0');
$q->leftJoin('files', 'f', 'tasks.task_id = f.file_task');
$q->leftJoin('user_task_pin', 'pin', 'tasks.task_id = pin.task_id AND pin.user_id = ' . $AppUI->user_id);
//$user_id = $user_id ? $user_id : $AppUI->user_id;

if ($project_id)
        $q->addWhere('task_project = ' . $project_id);

if ($pinned_only)
        $q->addWhere('task_pinned = 1');

switch ($f) {
        case 'all':
                break;
        case 'myfinished7days':
                $q->addWhere('ut.user_id = ' . $user_id);
        case 'allfinished7days':        // patch 2.12.04 tasks finished in the last 7 days
//                $q->addTable('user_tasks');
    $q->addTable('user_tasks');
    $q->addWhere('user_tasks.user_id = ' . $user_id);
    $q->addWhere('user_tasks.task_id = tasks.task_id');

                $q->addWhere('task_percent_complete = 100');
                //TODO: use date class to construct date.
                $q->addWhere('task_end_date >= \'' . date('Y-m-d 00:00:00', mktime(0, 0, 0, date('m'), date('d')-7, date('Y'))) . "'");
                break;
        case 'children':
        // patch 2.13.04 2, fixed ambigious task_id
                $q->addWhere('task_parent = ' . $task_id);
                $q->addWhere('tasks.task_id != ' . $task_id);
                break;
        case 'myproj':
                $q->addWhere('project_owner = ' . $user_id);
                break;
        case 'mycomp':
            if(!$AppUI->user_company){
                $AppUI->user_company = 0;
            }
                $q->addWhere('project_company = ' . $AppUI->user_company);
                break;
        case 'myunfinished':
                $q->addTable('user_tasks');
                $q->addWhere('user_tasks.user_id = ' . $user_id);
                $q->addWhere('user_tasks.task_id = tasks.task_id');
//                $q->addWhere('task_project = p.project_id');

                $q->addWhere("(task_percent_complete < '100' OR task_end_date = '')");
                $q->addWhere('p.project_status != 7');
                $q->addWhere('p.project_status != 4');
                $q->addWhere('p.project_status != 5');
                break;
        case 'allunfinished':
                //                        AND task_project             = projects.project_id
                $q->addWhere("(task_percent_complete < '100' OR task_end_date = '')");
                $q->addWhere('p.project_status != 7');
                $q->addWhere('p.project_status != 4');
                $q->addWhere('p.project_status != 5');
                break;
        case 'unassigned':
                $q->leftJoin('user_tasks', 'ut_empty', 'tasks.task_id = ut_empty.task_id');
                $q->addWhere('ut_empty.task_id IS NULL');
                break;
        case 'taskcreated':
                $q->addWhere('task_owner = ' . $user_id);
                break;
        default:
    $q->addTable('user_tasks');
    $q->addWhere('user_tasks.user_id = ' . $user_id);
    $q->addWhere('user_tasks.task_id = tasks.task_id');
//                $from .= ", user_tasks";
//                $where .= "
//        AND task_project = projects.project_id
//        AND user_tasks.user_id = $user_id
//        AND user_tasks.task_id = tasks.task_id";
                break;
}

if (($project_id  || $task_id) && $showIncomplete) {
        $q->addWhere('( task_percent_complete < 100 or task_percent_complete is null )');
}

// reverse lookup the status integer from Tab name
$status = dPgetSysVal( 'TaskStatus' );
$sutats = array_flip($status);
$ctn = substr($currentTabName, 7, -1);

// we are in a tabbed view
if ( isset($currentTabName) )
        $task_status = $sutats[$ctn];

if ($task_status)
    $q->addWhere('task_status = ' . $task_status);

// patch 2.12.04 text search
if ( $search_text = $AppUI->getState('searchtext') )
        $q->addWhere("( task_name LIKE ('%$search_text%') OR task_description LIKE ('%$search_text%') )");

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering

$allowedProjects = $project->getAllowedSQL($AppUI->user_id, 'task_project');
if (count($allowedProjects))
        $q->addWhere($allowedProjects);

//
$obj =& new CTask;
$allowedTasks = $obj->getAllowedSQL($AppUI->user_id);
if ( count($allowedTasks))
        $q->addWhere($allowedTasks);

// echo "<pre>$where</pre>";

// Filter by company
if ( ! $min_view && $f2 != 'all' ) {
                $q->leftJoin('companies', 'c', 'c.company_id = p.project_company');
                $q->addWhere('company_id = ' . intval($f2) );
}

$q->addGroup('task_id');
$q->addOrder('project_id, task_start_date');
//$tsql = "SELECT $select FROM $from $join WHERE $where" .
//  "\nGROUP BY task_id" .
//  "\nORDER BY project_id, task_start_date";

// echo "<pre>$tsql</pre>";

if ($canViewTask) {
        $ptrc = db_exec( $q->prepare() );
        $nums = db_num_rows( $ptrc );
        echo db_error();
} else {
        $nums = 0;
}

//pull the tasks into an array
/*
for ($x=0; $x < $nums; $x++) {
        $row = db_fetch_assoc( $ptrc );
        $projects[$row['task_project']]['tasks'][] = $row;
}
*/
for ($x=0; $x < $nums; $x++) {
        $row = db_fetch_assoc( $ptrc );

        //add information about assigned users into the page output
        $q->clear();
        $q->addQuery('ut.user_id,
        u.user_username, contact_email, ut.perc_assignment, SUM(ut.perc_assignment) AS assign_extent, contact_first_name, contact_last_name');
        $q->addTable('user_tasks', 'ut');
        $q->leftJoin('users', 'u', 'u.user_id = ut.user_id');
        $q->leftJoin('contacts', 'c', 'u.user_contact = c.contact_id');
        $q->addWhere('ut.task_id = ' . $row['task_id']);
        $q->addGroup('ut.user_id');

        $assigned_users = array ();
        $row['task_assigned_users'] = $q->loadList();
        //pull the final task row into array
        $projects[$row['task_project']]['tasks'][] = $row;
}

$showEditCheckbox = isset($canEdit)
                                                                && $canEdit
                                                                && $dPconfig['direct_edit_assignment'];
?>

<script type="text/JavaScript">
function toggle_users(id){
  var element = document.getElementById(id);
  element.style.display = (element.style.display == '' || element.style.display == "none") ? "inline" : "none";
}

<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($showEditCheckbox) {
?>
function checkAll(project_id) {
        var f = eval( 'document.assFrm' + project_id );
        var cFlag = f.master.checked ? false : true;

        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == cFlag && e.name != 'master')
                {
                         e.checked = !e.checked;
                }
        }

}

function chAssignment(project_id, rmUser, del) {
        var f = eval( 'document.assFrm' + project_id );
        var fl = f.add_users.length-1;
        var c = 0;
        var a = 0;

        f.hassign.value = "";
        f.htasks.value = "";

        // harvest all checked checkboxes (tasks to process)
        for (var i=0;i< f.elements.length;i++){
                var e = f.elements[i];
                // only if it's a checkbox.
                if(e.type == "checkbox" && e.checked == true && e.name != 'master')
                {
                         c++;
                         f.htasks.value = f.htasks.value +","+ e.value;
                }
        }

        // harvest all selected possible User Assignees
        for (fl; fl > -1; fl--){
                if (f.add_users.options[fl].selected) {
                        a++;
                        f.hassign.value = "," + f.hassign.value +","+ f.add_users.options[fl].value;
                }
        }

        if (del == true) {
                        if (c == 0) {
                                 alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                        } else if (a == 0 && rmUser == 1){
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                if (confirm( '<?php echo $AppUI->_('Are you sure you want to unassign the User from Task(s)?', UI_OUTPUT_JS); ?>' )) {
                                        f.del.value = 1;
                                        f.rm.value = rmUser;
                                        f.project_id.value = project_id;
                                        f.submit();
                                }
                        }
        } else {

                if (c == 0) {
                        alert ('<?php echo $AppUI->_('Please select at least one Task!', UI_OUTPUT_JS); ?>');
                } else {

                        if (a == 0) {
                                alert ('<?php echo $AppUI->_('Please select at least one Assignee!', UI_OUTPUT_JS); ?>');
                        } else {
                                f.rm.value = rmUser;
                                f.del.value = del;
                                f.project_id.value = project_id;
                                f.submit();

                        }
                }
        }


}
<?php } ?>
</script>
<script language="JavaScript" src="modules/tasks/tree.js?<?php echo time(); ?>"></script >


<?php if ($project_id || $task_id) { ?>
<table width='100%' border='0' cellpadding='1' cellspacing='0'>
<form name='task_list_options' method='POST' action='<?php echo $query_string; ?>'>
<input type='hidden' name='show_task_options' value='1'>
<tr>
        <td align='right'>
                <table>
                        <tr>
                                <td><?php echo $AppUI->_('Show');?>:</td>
                                <td>
                                        <input type='checkbox' name='show_incomplete' onclick='document.task_list_options.submit();' <?php echo $showIncomplete ? 'checked="checked"' : '';?> />
                                </td>
                                <td>
                                        <?php echo $AppUI->_('Incomplete Tasks Only'); ?></td>
                                </td>
                        </tr>
                </table>
        </td>
</tr>
</form>
</table>
<?php } ?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
        <th width="10">&nbsp;</th>
        <th width="10"><?php echo $AppUI->_('Pin'); ?></th>
        <th width="10"><?php echo $AppUI->_('New Log'); ?></th>
        <th width="20"><?php sort_by_item_title( 'Work', 'task_percent_complete', SORT_NUMERIC);?></th>
        <th align="center"><?php sort_by_item_title( 'P', 'task_priority', SORT_NUMERIC ); ?></th>
        <th width="200"><?php sort_by_item_title( 'Task Name', 'task_name', SORT_STRING );?></th>
        <th nowrap="nowrap"><?php sort_by_item_title( 'Task Creator', 'user_username', SORT_STRING );?></th>
        <th nowrap="nowrap"><?php echo $AppUI->_('Assigned Users')?></th>
        <th nowrap="nowrap"><?php sort_by_item_title( 'Start Date', 'task_start_date', SORT_NUMERIC );?></th>
        <th nowrap="nowrap"><?php sort_by_item_title( 'Duration', 'task_duration', SORT_NUMERIC );?>&nbsp;&nbsp;</th>
        <th nowrap="nowrap"><?php sort_by_item_title( 'Finish Date', 'task_end_date', SORT_NUMERIC );?></th>
        <th nowrap="nowrap"><?php sort_by_item_title( 'Last Update', 'last_update', SORT_NUMERIC );?></th>
        <?php if ($showEditCheckbox) { echo '<th width="1">&nbsp;</th>'; }?>
</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );

foreach ($projects as $k => $p) {
        $tnums = count( @$p['tasks'] );
// don't show project if it has no tasks
// patch 2.12.04, show project if it is the only project in view
        if ($tnums > 0 || $project_id == $p['project_id']) {
//echo '<pre>'; print_r($p); echo '</pre>';
                if (!$min_view) {

                echo "<form name=\"assFrm{$p['project_id']}\" action=\"index.php?m=$m&a=$a\" method=\"post\">
                                <input type=\"hidden\" name=\"del\" value=\"1\" />
                                <input type=\"hidden\" name=\"rm\" value=\"0\" />
                                <input type=\"hidden\" name=\"store\" value=\"0\" />
                                <input type=\"hidden\" name=\"dosql\" value=\"do_task_assign_aed\" />
                                <input type=\"hidden\" name=\"project_id\" value=\"{$p['project_id']}\" />
                                <input type=\"hidden\" name=\"hassign\" />
                                <input type=\"hidden\" name=\"htasks\" />"
?>
<tr>
        <td>
                <a href="index.php?m=tasks&f=<?php echo $f;?>&project_id=<?php echo $project_id ? 0 : $k;?>">
                        <img src="./images/icons/<?php echo $project_id ? 'expand.gif' : 'collapse.gif';?>" width="16" height="16" border="0" alt="<?php echo $project_id ? $AppUI->_('show other projects') : $AppUI->_('show only this project');?>">
                </a>
        </td>
        <td colspan="<?php echo $dPconfig['direct_edit_assignment'] ? $cols-4 : $cols-1; ?>">
                <table width="100%" border="0">
                <tr>
                        <!-- patch 2.12.04 display company name next to project name -->
                        <td nowrap style="border: outset #eeeeee 2px;background-color:#<?php echo @$p["project_color_identifier"];?>">
                                <a href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
                                <span style='color:<?php echo bestColor( @$p["project_color_identifier"] ); ?>;text-decoration:none;'><strong><?php echo @$p["company_name"].' :: '.@$p["project_name"];?></strong></span></a>
                        </td>
                        <td width="<?php echo (101 - intval(@$p["project_percent_complete"]));?>%">
                                <?php echo (intval(@$p["project_percent_complete"]));?>%
                        </td>
                </tr>
                </table>
        </td>
        <?php if ($dPconfig['direct_edit_assignment']) {
            // get Users with all Allocation info (e.g. their freeCapacity)
            $tempoTask = new CTask();
            $userAlloc = $tempoTask->getAllocation("user_id");
                ?>
         <td colspan="3" align="right" valign="middle">
                <table width="100%" border="0">
                        <tr>
                                <td align="right">
                                <select name="add_users" style="width:200px" size="2" multiple="multiple" class="text"  ondblclick="javascript:chAssignment('.$user_id.', 0, false)">
                                <?php foreach ($userAlloc as $v => $u) {
                                echo "\n\t<option value=\"".$u['user_id']."\">" . dPformSafe( $u['userFC'] ) . "</option>";
                                }?>
                                </select>
                                </td>
                                 <td align="center">
                                <?php
                                        echo "<a href='javascript:chAssignment({$p['project_id']}, 0, 0);'>".
                                        dPshowImage(dPfindImage('add.png', 'tasks'), 16, 16, 'Assign Users', 'Assign selected Users to selected Tasks')."</a>";
                                        echo  "&nbsp;<a href='javascript:chAssignment({$p['project_id']}, 1, 1);'>".
                                        dPshowImage(dPfindImage('remove.png', 'tasks'), 16, 16, 'Unassign Users', 'Unassign Users from Task')."</a>";
                                ?><br />
                                <?php
                                        echo "<select class=\"text\" name=\"percentage_assignment\" title=\"".$AppUI->_('Assign with Percentage')."\">";
                                        for ($i = 0; $i <= 100; $i+=5) {
                                                echo "<option ".(($i==30)? "selected=\"true\"" : "" )." value=\"".$i."\">".$i."%</option>";
                                        }
                                ?>
                                </select>
                                </td>
                        </tr>
                </table>
         </td>
         <?php }?>
</tr>
<?php
                }
                global $done;
                $done = array();
                if ( $task_sort_item1 != "" )
                {
                        if ( $task_sort_item2 != "" && $task_sort_item1 != $task_sort_item2 )
                                $p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1
                                                                                  , $task_sort_item2, $task_sort_order2, $task_sort_type2 );
                        else $p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1 );
                }

                for ($i=0; $i < $tnums; $i++) {
                        $t = $p['tasks'][$i];

                        if ($t["task_parent"] == $t["task_id"] || ($f == 'children')) {
                            $is_opened = in_array($t["task_id"], $tasks_opened);
                            $t['node_id'] = $t['task_id'];
                                showtask( $t, 0, $is_opened );
                                $q->clear();
                                if(($is_opened && !dPgetConfig('tasks_ajax_list')) || $t["task_dynamic"] == 0){
                                    findchild( $p['tasks'], $t["task_id"] );
                                }
                        }
                }
// check that any 'orphaned' user tasks are also display
                for ($i=0; $i < $tnums; $i++) {
                        if ( !in_array( $p['tasks'][$i]["task_id"], $done ) ) {
                            if($p['tasks'][$i]["task_dynamic"] && in_array( $p['tasks'][$i]["task_parent"], $tasks_closed)) {
                                closeOpenedTask($p['tasks'][$i]["task_id"]);
                            }
                            // $f == 'children' - Child Tasks in tasks module
                            if((!dPgetConfig('tasks_ajax_list') || $f == 'children') && in_array($p['tasks'][$i]["task_parent"], $tasks_opened)){
                                    showtask( $p['tasks'][$i], 1, false);
                            }
                        }
                }

                if($tnums && $dPconfig['enable_gantt_charts'] && !$min_view) { ?>
                <tr>
                        <td colspan="<?php echo $cols; ?>" align="right">
                                <input type="button" class="button" value="<?php echo $AppUI->_('Reports');?>" onclick="javascript:window.location='index.php?m=reports&project_id=<?php echo $k;?>';" />
                                <input type="button" class="button" value="<?php echo $AppUI->_('Gantt Chart');?>" onclick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $k;?>';" />
                        </td>
                </tr>
                </form>
                <?php }
        }
}
$AppUI->setState("tasks_opened", $tasks_opened);
?>
</table>
<table>
<tr>
        <td><?php echo $AppUI->_('Key');?>:</td>
        <td>&nbsp; &nbsp;</td>
        <td class="task_future">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Future Task');?></td>
        <td>&nbsp; &nbsp;</td>
        <td class="task_started">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Started and on time');?></td>
        <td class="task_notstarted">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Should have started');?></td>
        <td>&nbsp; &nbsp;</td>
        <td class="task_overdue">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Overdue');?></td>
        <td>&nbsp; &nbsp;</td>
        <td class="task_late">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Late');?></td>
        <td>&nbsp; &nbsp;</td>
        <td class="task_done">&nbsp; &nbsp;</td>
        <td>=<?php echo $AppUI->_('Done');?></td>
</tr>
</table>