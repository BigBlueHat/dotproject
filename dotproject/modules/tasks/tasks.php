<?php /* TASKS $Id$ */
GLOBAL $m, $a, $project_id, $f, $min_view, $query_string, $durnTypes;
/*
	tasks.php

	This file contains common task list rendering code used by
	modules/tasks/index.php and modules/projects/vw_tasks.php

	External used variables:

	* $min_view: hide some elements when active (used in the vw_tasks.php)
	* $project_id
	* $f
	* $query_string
*/

if (empty($query_string)) {
	$query_string = "?m=$m&a=$a";
}

$durnTypes = dPgetSysVal( 'TaskDurationType' );

$task_project = intval( dPgetParam( $_GET, 'task_project', null ) );
$task_id = intval( dPgetParam( $_GET, 'task_id', null ) );

$task_sort_item1 = dPgetParam( $_GET, 'task_sort_item1', '' );
$task_sort_type1 = dPgetParam( $_GET, 'task_sort_type1', '' );
$task_sort_item2 = dPgetParam( $_GET, 'task_sort_item2', '' );
$task_sort_type2 = dPgetParam( $_GET, 'task_sort_type2', '' );
$task_sort_order1 = intval( dPgetParam( $_GET, 'task_sort_order1', 0 ) );
$task_sort_order2 = intval( dPgetParam( $_GET, 'task_sort_order2', 0 ) );

$where = '';
$join = winnow( 'projects', 'project_id', $where );

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	SUM(t1.task_duration*t1.task_percent_complete)/SUM(t1.task_duration) as project_percent_complete
FROM projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project" .
$join .
"WHERE $where GROUP BY project_id
ORDER BY project_name
";

// echo "<pre>$psql</pre>";

$prc = db_exec( $psql );
echo db_error();

$projects = array();
while ($row = db_fetch_assoc( $prc )) {
	$projects[$row["project_id"]] = $row;
}

// pull tasks
$select = "
tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
task_priority, task_percent_complete, task_duration, task_duration_type, task_project,
task_description, task_owner, user_username, task_milestone
";

$from = "tasks";
$join = "LEFT JOIN projects ON project_id = task_project";
$join .= " LEFT JOIN users as usernames ON task_owner = usernames.user_id";
$where = $project_id ? "\ntask_project = $project_id" : 'project_active != 0';

switch ($f) {
	case 'all':
		break;
	case 'children':
		$where .= "\n	AND task_parent = $task_id AND task_id != $task_id";	
		break;
	case 'myproj':
		$where .= "\n	AND project_owner = $AppUI->user_id";
		break;
	case 'mycomp':
		$where .= "\n	AND project_company = $AppUI->user_company";
		break;
	case 'myinact':
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id";
		$_GET['inactive'] = -1;
		break;
	case 'myunfinished':
		$from .= ", user_tasks";
		// This filter checks all tasks that are not already in 100% 
		// and the project is not on hold nor completed
		$where .= "
					AND task_project             = projects.project_id
					AND user_tasks.user_id       = $AppUI->user_id
					AND user_tasks.task_id       = tasks.task_id
					AND task_percent_complete    < '100'
					AND projects.project_active  = '1'
					AND projects.project_status != '4'
					AND projects.project_status != '5'";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $AppUI->user_id
	AND user_tasks.task_id = tasks.task_id";
		break;
}

$task_status = intval( dPgetParam( $_GET, 'task_status', null ) );
if ($f != 'myinact') {		//separate active from inactive tasks
	if ($task_status === null) {
		$where .= "\n	AND task_status > -1";
	} else {
		$where .= "\n	AND task_status = '$task_status'";
	}
}

// filter tasks considering task and project permissions
$projects_filter = '';
$tasks_filter = '';

// TODO: Enable tasks filtering

$join .= winnow( 'projects', 'tasks.task_project', $projects_filter, 'perm1' );
$join .= winnow( 'tasks', 'tasks.task_id', $tasks_filter, 'perm2' );
$where .= " AND ( ($projects_filter) )";
// echo "<pre>$where</pre>";

$tsql = "SELECT $select FROM $from $join WHERE $where" .
  "\nORDER BY project_id, task_start_date";

//echo "<pre>$tsql</pre>";

$ptrc = db_exec( $tsql );
$nums = db_num_rows( $ptrc );
echo db_error();

//pull the tasks into an array

for ($x=0; $x < $nums; $x++) {
	$row = db_fetch_assoc( $ptrc );
	$projects[$row['task_project']]['tasks'][] = $row;
}

//This kludgy function echos children tasks as threads

if (! function_exists('showtask') ) {
function showtask( &$a, $level=0 ) {
	global $AppUI, $done, $query_string, $durnTypes;
	$df = $AppUI->getPref( 'SHDATEFORMAT' );
	$done[] = $a['task_id'];

	$start_date = intval( $a["task_start_date"] ) ? new CDate( $a["task_start_date"] ) : null;
	$end_date = intval( $a["task_end_date"] ) ? new CDate( $a["task_end_date"] ) : null;

	$s = "\n<tr>";
// edit icon
	$s .= "\n\t<td>";
	$canEdit = !getDenyEdit( 'tasks', $a["task_id"] );
	if ($canEdit) {
		$s .= "\n\t\t<a href=\"?m=tasks&a=addedit&task_id={$a['task_id']}\">"
			. "\n\t\t\t".'<img src="./images/icons/pencil.gif" alt="'.$AppUI->_( 'Edit Task' ).'" border="0" width="12" height="12">'
			. "\n\t\t</a>";
	}
	$s .= "\n\t</td>";
// percent complete
	$s .= "\n\t<td align=\"right\">".intval( $a["task_percent_complete"] ).'%</td>';
// priority
	$s .= "\n\t<td>";
	if ($a["task_priority"] < 0 ) {
		$s .= "\n\t\t<img src=\"./images/icons/low.gif\" width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		$s .= "\n\t\t<img src=\"./images/icons/" . $a["task_priority"] .'.gif" width=13 height=16>';
	}
	$s .= "\n\t</td>";
// dots
	$s .= '<td width="90%">';
	for ($y=0; $y < $level; $y++) {
		if ($y+1 == $level) {
			$s .= '<img src="./images/corner-dots.gif" width="16" height="12" border="0">';
		} else {
			$s .= '<img src="./images/shim.gif" width="16" height="12"  border="0">';
		}
	}
// name link
	$alt = htmlspecialchars( $a["task_description"] );

	if ($a["task_milestone"] > 0) {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '" title="' . $alt . '"><b>' . $a["task_name"] . '</b></a></td>';
	} else {
		$s .= '&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["task_id"] . '" title="' . $alt . '">' . $a["task_name"] . '</a></td>';
	}
// task owner
	$s .= '<td nowrap="nowrap" align=center>'. $a["user_username"] .'</td>';
// start date
	$s .= '<td nowrap="nowrap">'.($start_date ? $start_date->format( $df ) : '-').'</td>';
// duration or milestone
	$s .= '<td align="right">';
	if ( $a['task_milestone'] == '0' ) {
		$s .= $a['task_duration'] . ' ' . $AppUI->_( $durnTypes[$a['task_duration_type']] );
	} else {
		$s .= $AppUI->_("Milestone");
	}
	$s .= '</td>';
// end date
	$s .= '<td nowrap="nowrap">'.($end_date ? $end_date->format( $df ) : '-').'</td>';

	$s .= '</tr>';

	echo $s;
}

}

if (! function_exists('findchild') ) {
function findchild( &$tarr, $parent, $level=0 ){
	GLOBAL $projects;
	$level = $level+1;
	$n = count( $tarr );
	for ($x=0; $x < $n; $x++) {
		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
			showtask( $tarr[$x], $level );
			findchild( $tarr, $tarr[$x]["task_id"], $level);
		}
	}
}
}

/* please throw this in an include file somewhere, its very useful */

function array_csort()   //coded by Ichier2003
{
    $args = func_get_args();
    $marray = array_shift($args);
	
	if ( empty( $marray )) return array();
	
	$i = 0;
    $msortline = "return(array_multisort(";
	$sortarr = array();
    foreach ($args as $arg) {
        $i++;
        if (is_string($arg)) {
            foreach ($marray as $row) {
                $sortarr[$i][] = $row[$arg];
            }
        } else {
            $sortarr[$i] = $arg;
        }
        $msortline .= "\$sortarr[".$i."],";
    }
    $msortline .= "\$marray));";

    eval($msortline);
    return $marray;
}

function sort_by_item_title( $title, $item_name, $item_type )
{
	global $AppUI,$project_id,$min_view;
	global $task_sort_item1,$task_sort_type1,$task_sort_order1;
	global $task_sort_item2,$task_sort_type2,$task_sort_order2;
	
	if ( $min_view )
	{
		if ( $task_sort_item2 == $item_name ) $item_order = $task_sort_order2;
		if ( $task_sort_item1 == $item_name ) $item_order = $task_sort_order1;
		
		if ( isset( $item_order ) )
		{
			if ( $item_order == SORT_ASC ) echo '<img src="./images/icons/low.gif" width=13 height=16>';
			else echo '<img src="./images/icons/1.gif" width=13 height=16>';
		}
		else $item_order = SORT_DESC;
		
	/* flip the sort order for the link */
		$item_order = ( $item_order == SORT_ASC ) ? SORT_DESC : SORT_ASC;
		
		echo '<a href="./index.php?m=projects&a=view&project_id='.$project_id;
		echo '&task_sort_item1='.$item_name;
		echo '&task_sort_type1='.$item_type;
		echo '&task_sort_order1='.$item_order;
		if ( $task_sort_item1 == $item_name )
		{
			echo '&task_sort_item2='.$task_sort_item2;
			echo '&task_sort_type2='.$task_sort_type2;
			echo '&task_sort_order2='.$task_sort_order2;
		}
		else
		{
			echo '&task_sort_item2='.$task_sort_item1;
			echo '&task_sort_type2='.$task_sort_type1;
			echo '&task_sort_order2='.$task_sort_order1;
		}
		echo '">';
	}
	
	echo $AppUI->_($title);
	
	if ( $min_view ) { echo '</a>'; }
}

?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
<!--
	<th width="10" STYLE="background: #4aa">&nbsp;</th>
	<th width="20"><?php echo $AppUI->_('Work');?></th>
	<th width="15" align="center">&nbsp;</th>
	<th width="200"><?php echo $AppUI->_('Task Name');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Task Creator');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Duration');?>&nbsp;&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Finish Date');?></th>
-->
	<th width="10" STYLE="background: #4aa">&nbsp;</th>
	<th width="20" STYLE="background: #4aa"><?php echo $AppUI->_('Work');?></th>
	<th width="15" align="center" STYLE="background: #4aa">&nbsp;</th>
	<th width="200" STYLE="background: #4aa"><?php sort_by_item_title( 'Task Name', 'task_name', SORT_STRING );?></th>
	<th nowrap="nowrap" STYLE="background: #4aa"><?php sort_by_item_title( 'Task Creator', 'user_username', SORT_STRING );?></th>
	<th nowrap="nowrap" STYLE="background: #4aa"><?php sort_by_item_title( 'Start Date', 'task_start_date', SORT_NUMERIC );?></th>
	<th nowrap="nowrap" STYLE="background: #4aa"><?php sort_by_item_title( 'Duration', 'task_duration', SORT_NUMERIC );?>&nbsp;&nbsp;</th>
	<th nowrap="nowrap" STYLE="background: #4aa"><?php sort_by_item_title( 'Finish Date', 'task_end_date', SORT_NUMERIC );?></th>


</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );
foreach ($projects as $k => $p) {
	$tnums = count( @$p['tasks'] );
// don't show project if it has no tasks
	if ($tnums) {
//echo '<pre>'; print_r($p); echo '</pre>';
		if (!$min_view) {
?>
<tr>
	<td>
		<a href="index.php?m=tasks&f=<?php echo $f;?>&project_id=<?php echo $project_id ? 0 : $k;?>">
			<img src="./images/icons/<?php echo $project_id ? 'expand.gif' : 'collapse.gif';?>" width="16" height="16" border="0" alt="<?php echo $project_id ? 'show other projects' : 'show only this project';?>">
		</a>
	</td>
	<td colspan="8">
		<table width="100%" border="0">
		<tr>
			<td nowrap style="border: outset #eeeeee 2px;background-color:#<?php echo @$p["project_color_identifier"];?>">
				<a href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
				<span style='color:<?php echo bestColor( @$p["project_color_identifier"] ); ?>;text-decoration:none;'><strong><?php echo @$p["project_name"];?></strong></span></a>
			</td>
			<td width="<?php echo (101 - intval(@$p["project_percent_complete"]));?>%">
				<?php echo (intval(@$p["project_percent_complete"]));?>%
			</td>
		</tr>
		</table>
</tr>
<?php
		}
		global $done;
		$done = array();
		if ( $min_view && $task_sort_item1 != "" )
		{
			if ( $task_sort_item2 != "" && $task_sort_item1 != $task_sort_item2 )
				$p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1
										  , $task_sort_item2, $task_sort_order2, $task_sort_type2 );
			else $p['tasks'] = array_csort($p['tasks'], $task_sort_item1, $task_sort_order1, $task_sort_type1 );
		}
		
		for ($i=0; $i < $tnums; $i++) {
			$t = $p['tasks'][$i];
			if ($t["task_parent"] == $t["task_id"]) {
				showtask( $t );
				findchild( $p['tasks'], $t["task_id"] );
			}
		}
// check that any 'orphaned' user tasks are also display
		for ($i=0; $i < $tnums; $i++) {
			if ( !in_array( $p['tasks'][$i]["task_id"], $done )) {
				showtask( $p['tasks'][$i], 1 );
			}
		}

		if($tnums && $AppUI->cfg['enable_gantt_charts'] && !$min_view) { ?>
		<tr>
			<td colspan="8" align="right">
				<input type="button" class="button" value="<?php echo $AppUI->_('see gantt chart');?>" onclick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $k;?>';" />
			</td>
		</tr>
		<?php }
	}
}
?>
</table>
