<?php 

// Project status from sysval, defined as a constant
$perms =& $AppUI->acl();

$project_id = intval( dPgetParam( $_GET, 'project_id', 0 ) );
$date       = intval( dPgetParam( $_GET, 'date', '' ) );
$user_id    = $AppUI->user_id;
$no_modify	= false;

$sort = dPgetParam($_REQUEST, 'sort', 'task_end_date');

if($perms->checkModule("admin","view")){ // let's see if the user has sysadmin access
	$other_users = true;
	if(($show_uid = dPgetParam($_REQUEST, "show_user_todo", 0)) != 0){ // lets see if the user wants to see anothers user mytodo
		$user_id = $show_uid;
		$no_modify = true;
		$AppUI->setState("user_id", $user_id);
	} else {
//		$user_id = $AppUI->getState("user_id");
	}
}

// check permissions
$canEdit = $perms->checkModule( $m, 'edit' );

// if task priority set and items selected, do some work
$action = dPgetParam( $_POST, 'action', 99 );
$selected = dPgetParam( $_POST, 'selected', 0 );

//TODO: create getDeepChildren function in the Tasks class.
function getChildren( $task_id, $deep = 'true' )
{
	$children = db_loadColumn( "SELECT task_id FROM tasks WHERE task_parent = $task_id" );
	if (!$deep)
		if (!$children)
			return array();
		else
			return $children;
	if ($children)
	{
		$deep_children = array();
		foreach ($children as $child)
			$deep_children = array_merge($deep_children, getChildren( $child ));
			
		return array_merge($children, $deep_children);
	}
	return array();
}

if ($selected && count( $selected )) {
	$new_task = dPgetParam( $_POST, 'new_task', '0' );
	$new_project = dPgetParam( $_POST, 'new_project', '' );

	foreach ($selected as $key => $val)
	{
		if ($new_task == '0')
			$new_task = $val;
		if ( isset($_POST['include_children']) && $_POST['include_children'])
			$children = getChildren($val);
		else
			$children = array();
		if ( $action == 'f') { 										// Mark FINISHED
			// mark task as completed
			$childlist = false;
			if (count($children))
				$childlist = implode(', ', $children);
			$sql = "UPDATE tasks SET task_percent_complete=100 WHERE task_id " . ($childlist)?"IN ($childlist, $val)":"=$val";
		} else if ( $action == 'd' ) { 						// DELETE
			// delete task
      $t = &new CTask();
      if (count($children))
				foreach($children as $child)
				{
					$t->load($child);
					$t->delete();
				}
				//db_loadList( "DELETE FROM tasks WHERE task_id IN ($children)" );
      $t->load($val);
			$t->delete();
			$sql = ''; //"DELETE FROM tasks WHERE task_id=$val";
		} else if ( $action == 'm' ) { 						// MOVE
			if (count($children))
				db_exec( "UPDATE tasks SET task_project=$new_project WHERE task_id IN (" . implode(', ', $children) . " )");
			$sql = "UPDATE tasks SET task_parent='$new_task', task_project='$new_project' WHERE task_id=$val";
		} else if ( $action == 'c' ) { 						// COPY
			$t = &new CTask();
			$t->load($val);
			$old_parent = $t->task_id;
			$t = $t->copy($new_project);
			if ($new_task != $old_parent)
				$t->task_parent = $new_task;
			else // necesary? depends how copy works
				$t->task_parent = $t->task_id;
			$t->store();
			$new_id = $t->task_id;
			if (count($children)) {
				foreach ($children as $child) {
					$t->load($child);
					$t = $t->copy($new_project);
					// update parent only on top tasks, others stay same
					if ($t->task_parent == $old_parent) 
						$t->task_parent = $new_id;
					$t->store();
				}
			}
			$sql = false;
		} else if ( $action > -2 && $action < 2 ) { // Set PRIORITY
			// set priority
      $sql = "UPDATE tasks SET task_priority=$action WHERE task_id";
      if ($children)
				$sql .= " IN (" . implode(',',$children) . ", $val)";
			else
				$sql .= "=$val";
		}
		if ($sql) {
			db_exec( $sql );
		}
	}
}

$AppUI->savePlace();

$proj =& new CProject;
$tobj =& new CTask;

$allowedProjects = $proj->getAllowedSQL($AppUI->user_id);
$allowedTasks = $tobj->getAllowedSQL($AppUI->user_id, 'a.task_id');

// query my sub-tasks (ignoring task parents)

$sql = "
		 SELECT tasks.*,
		 project_name, project_id, project_color_identifier". 
"		 FROM projects, tasks ".
"		 WHERE project_id = task_project";
if ($project_id)
	$sql .= " AND project_id = $project_id";

if (count($allowedTasks))
	$sql .= " AND " . implode(" AND ", $allowedTasks);

if (count($allowedProjects))
	$sql .= " AND " . implode(" AND ", $allowedProjects);

$sql .=   " GROUP BY task_id
	ORDER BY $sort, task_priority DESC
";
// echo "<pre>$sql</pre>";
$tasks = db_loadList( $sql );

$priorities = array(
	'1' => 'high',
	'0' => 'normal',
  '-1' => 'low'
);

$durnTypes = dPgetSysVal( 'TaskDurationType' );

if (!@$min_view) {
	$titleBlock = new CTitleBlock( 'Organize Tasks', 'applet-48.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=tasks", "tasks list" );
	if ($project_id)
		$titleBlock->addCrumb("?m=projects&a=view&project_id=$project_id", "view project");
	$titleBlock->show();
}

function showchildren($id, $level=1)
{
	global $tasks;
	$t = $tasks; // otherwise, $tasks is accessed from a static context and doesn't work.
	foreach ($t as $task)
	{
		//echo $id . '==> ' . $task['task_parent'] . '==' . $id . '<br>';
		if ($task['task_parent'] == $id && $task['task_parent'] != $task['task_id'])
		{
			showtask_edit($task, $level);
			showchildren($task['task_id'], $level+1);
		}
	}
}

/** show a task - at a sublevel
 * {{{
*/
function showtask_edit($task, $level=0)
{
	global $AppUI, $canEdit, $durnTypes, $now, $df;
	
	$style = '';
	$sign = 1;
	$start = intval( @$task["task_start_date"] ) ? new CDate( $task["task_start_date"] ) : null;
	$end = intval( @$task["task_end_date"] ) ? new CDate( $task["task_end_date"] ) : null;
	
	if (!$end && $start) {
		$end = $start;
		$end->addSeconds( @$task["task_duration"]*$task["task_duration_type"]*SEC_HOUR );
	}

	if ($now->after( $start ) && $task["task_percent_complete"] == 0) {
		$style = 'background-color:#ffeebb';
	} else if ($now->after( $start )) {
		$style = 'background-color:#e6eedd';
	}

	if ($now->after( $end )) {
		$sign = -1;
		if ($end)
			$style = 'background-color:#cc6666;color:#ffffff';
		else
			$style = 'background-color: lightgray;';
	} 

	if ($start)
		$days = $now->dateDiff( $end ) * $sign;
	else
		$days = 0;
?>
<tr>
	<td>
<?php if ($canEdit) { ?>
		<a href="./index.php?m=tasks&a=addedit&task_id=<?php echo $task["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a>
<?php } ?>
	</td>
	<td align="right">
		<?php echo intval($task["task_percent_complete"]);?>%
	</td>

	<td>
<?php if ($task["task_priority"] < 0 ) {
	echo "<img src='./images/icons/low.gif' width=13 height=16>";
} else if ($task["task_priority"] > 0) {
	echo "<img src='./images/icons/" . $task["task_priority"] .".gif' width=13 height=16>";
}?>
	</td>

	<td width="50%">
	<? for ($i = 1; $i < $level; $i++)
							echo '&nbsp;&nbsp;';
			if ($level > 0)
				echo '<img src="./images/corner-dots.gif" width="16" height="12" border="0">'; ?>
			
		<a 	href="./index.php?m=tasks&a=view&task_id=<?php echo $task["task_id"];?>"
				title="<?php
					echo ( isset($task['parent_name']) ? '*** ' . $AppUI->_('Parent Task') . " ***\n" . htmlspecialchars($task['parent_name'], ENT_QUOTES) . "\n\n" : '' ) .
					'*** ' . $AppUI->_('Description') . " ***\n" . htmlspecialchars($task['task_description'], ENT_QUOTES) ?>">
					<?php echo htmlspecialchars($task["task_name"], ENT_QUOTES); ?>
		</a>
	</td>
	<td style="<?php echo $style;?>">
<?php
	echo $task['task_duration'] . ' ' . $AppUI->_( $durnTypes[$task['task_duration_type']] );
?>
	</td>

	<td nowrap align="right" style="<?php echo $style;?>">
		<?php echo $days; ?>
	</td>
	<td>
		<input type="checkbox" name="selected[]" value="<?php echo $task['task_id'] ?>">
	</td>
</tr>
<?php } // END of displaying tasks function.}}}
?>

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<form name="form" method="post" action="index.php?<?php echo "m=$m&a=$a&date=$date";?>">
<tr>
	<th width="20" colspan="2"><?php echo $AppUI->_('Progress');?></th>
	<th width="15" align="center"><?php echo $AppUI->_('P');?></th>
	<th>
		<a style="color: white;" href="index.php?m=tasks&a=organize&sort=task_name">
		<?php echo $AppUI->_('Task');?>
		</a>
	</th>
	<th nowrap>
		<a style="color: white;" href="index.php?m=tasks&a=organize&sort=task_duration">
		<?php echo $AppUI->_('Duration');?>
		</a>
	</th>
	<th nowrap>
		<a style="color: white;" href="index.php?m=tasks&a=organize&sort=task_end_date">
		<?php echo $AppUI->_('Due In');?>
		</a>
	</th>
	<th width="0">Select</th>
</tr>

<?php

/*** Tasks listing ***/
$now = new CDate();
$df = $AppUI->getPref('SHDATEFORMAT');

foreach ($tasks as $task) 
	if ($task['task_id'] == $task['task_parent'])
	{
		showtask_edit($task);
		showchildren($task['task_id']);
	}
?>
</table>

<?php
  $actions = array();
  $actions['d'] = $AppUI->_('Delete', UI_OUTPUT_JS);
  $actions['f'] = $AppUI->_('Mark as Finished', UI_OUTPUT_JS);
  $actions['m'] = $AppUI->_('Move', UI_OUTPUT_JS);
  $actions['c'] = $AppUI->_('Copy', UI_OUTPUT_JS);
	foreach($priorities as $k => $v)
		$actions[$k] = $AppUI->_('set priority to ' . $v, UI_OUTPUT_JS);

  
  $deny = $proj->getDeniedRecords( $AppUI->user_id );
  $sql = 'SELECT project_id, project_name
          FROM projects';
	if ($deny)
		$sql .= "\nWHERE project_id NOT IN (" . implode( ',', $deny ) . ')';
  $projects = db_loadHashList($sql, 'project_id');
	$p[0] = '[none]';
	foreach($projects as $proj)
		$p[$proj[0]] = $proj[1];
	if ($project_id)
		$p[$project_id] = '[same project]';
		
	$projects = $p;
	
	$ts[0] = '[top task]';
	foreach($tasks as $t)
		$ts[$t['task_id']] = $t['task_name'];
?>

<input type="checkbox" name="include_children" value='1' />Include Children<br />
<table>
  <tr>
    <th>Action: </th>
    <th>Project: </th>
    <th>Task: </th>
  </tr>
  <tr>
    <td>
      <?php echo arraySelect($actions, 'action', '', '0'); ?>
    </td>
    <td>
      <?php echo arraySelect($projects, 'new_project', ' onChange="updateTasks();"', '0'); ?>
    </td>
    <td>
      <?php echo ($ts)?arraySelect($ts, 'new_task', '', '0'):''; ?>
    </td>
		<td>
			<input type="submit" class="button" value="<?php echo $AppUI->_('update selected tasks');?>">
		</td>
  </tr>
</table>
</form>

<table>
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffffff">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Future Task');?></td>
	<td bgcolor="#e6eedd">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Started and on time');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffeebb">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Should have started');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#CC6666">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Overdue');?></td>
	<td bgcolor="lightgray">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Unknown');?></td>
</tr>
</table>

<script language="javascript">
	function updateTasks()
	{
		var proj = document.forms['form'].new_project.value;
		var tasks = new Array();
		var sel = document.forms['form'].new_task;
		while ( sel.options.length )
			sel.options[0] = null;
		sel.options[0] = new Option('loading...', -1);
		frames['thread'].location.href = './index.php?m=tasks&a=listtasks&project=' + proj;
	}
</script>

<iframe style="display: none;" name="thread" width="0" height="0" scrolling="yes" src=""></iframe>
