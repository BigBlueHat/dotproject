<?php
//        task index

$project_id = isset( $HTTP_GET_VARS['project_id'] ) ? $HTTP_GET_VARS['project_id'] : 0;
$project_id = isset( $HTTP_COOKIE_VARS['cookie_project'] ) ? $HTTP_COOKIE_VARS['cookie_project'] : $project_id;
$project_id = isset( $HTTP_GET_VARS['cookie_project'] ) ? $HTTP_GET_VARS['cookie_project'] : $project_id;
$project_id = isset( $HTTP_POST_VARS['cookie_project'] ) ? $HTTP_POST_VARS['cookie_project'] : $project_id;

// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
        echo '<script language="javascript">
        window.location="./index.php?m=help&a=access_denied";
        </script>
';
}

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

// pull valid projects and their percent complete information
$psql = "
SELECT project_id, project_color_identifier, project_name,
	COUNT(t1.task_id) as total_tasks,
	SUM(t1.task_duration*t1.task_precent_complete)/SUM(t1.task_duration) as project_precent_complete
FROM permissions, projects
LEFT JOIN tasks t1 ON projects.project_id = t1.task_project
WHERE project_active <> 0
	AND permission_user = $thisuser_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		)
GROUP BY project_id
ORDER BY project_name
";
//echo "<pre>$psql</pre>";
$prc = mysql_query( $psql );
echo mysql_error();
$pnums = mysql_num_rows( $prc );

$projects = array();
for ($x=0; $x < $pnums; $x++) {
	$z = mysql_fetch_array( $prc, MYSQL_ASSOC );
	$projects[$z["project_id"]] = $z;
}

// get any specifically denied tasks
$dsql = "
SELECT task_id
FROM tasks, permissions
WHERE permission_user = $thisuser_id
	AND permission_grant_on = 'tasks'
	AND permission_item = task_id
	AND permission_value = 0
";
$drc = mysql_query( $dsql );
echo mysql_error();
$deny = array();
while ($row = mysql_fetch_array( $drc, MYSQL_NUM )) {
        $deny[] = $row[0];
}

// pull tasks

$select = "
tasks.task_id, task_parent, task_name, task_start_date, task_end_date,
task_priority, task_precent_complete, task_duration, task_order, task_project,
project_name
";

$from = "tasks";
$join = "LEFT JOIN projects ON project_id = task_project";
$where = "project_active <> 0".($project_id ? "\nAND task_project = $project_id" : '');

switch ($f) {
	case 'all':
		$where .= "\nAND task_status > -1";
		break;
	case 'myproj':
		$where .= "\nAND task_status > -1\n	AND project_owner = $thisuser_id";
		break;
	case 'mycomp':
		$where .= "\nAND task_status > -1\n	AND project_company = $thisuser_company";
		break;
	case 'myinact':
		$from .= ", user_tasks";
		$where .= "
	AND task_project = projects.project_id
	AND user_tasks.user_id = $thisuser_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
	default:
		$from .= ", user_tasks";
		$where .= "
	AND task_status > -1
	AND task_project = projects.project_id
	AND user_tasks.user_id = $thisuser_id
	AND user_tasks.task_id = tasks.task_id
";
		break;
}

$tsql = "SELECT $select FROM $from $join WHERE $where ORDER BY project_id, task_order";
##echo "<pre>$tsql</pre>".mysql_error();##

$ptrc = mysql_query( $tsql );
$nums = mysql_num_rows( $ptrc );
echo mysql_error();
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
for ($x=0; $x < $nums; $x++) {
	$row = mysql_fetch_array( $ptrc, MYSQL_ASSOC );
	
        // set blank task_end_date if unset
        if($row["task_end_date"] == "0000-00-00 00:00:00") {
	        $row["task_end_date"] = "";
        }
	
	$projects[$row['task_project']]['tasks'][] = $row;
}

$crumbs = array();
$crumbs["?m=tasks&a=todo"] = "my todo";

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) {
	global $done;
	$done[] = $a['task_id']; ?>
	<tr>
	<td><A href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
	<td align="right"><?php echo intval($a["task_precent_complete"]);?>%</td>
	<td>
	<?php if ($a["task_priority"] < 0 ) {
		echo "<img src='./images/icons/low.gif' width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
	}?>
	</td>
	<td width="90%">

	<?php if ($level == 0) { ?>
	<img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow<?php echo $a["task_id"];?>">
	<map name="arrow<?php echo $a["task_id"];?>"><area coords="0,0,10,7" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=u";?>>
	<area coords="0,8,10,14" href=<?php echo "./index.php?m=tasks&a=reorder&task_project=" . $a["task_project"] . "&task_id=" . $a["task_id"] . "&order=" . $a["task_order"] . "&w=d";?>></map>

	<?php } else {
		for ($y=0; $y < $level; $y++) {
			if ($y+1 == $level) {
				echo "<img src=./images/corner-dots.gif width=16 height=12  border=0>";
			} else {
				echo "<img src=./images/shim.gif width=16 height=12  border=0>";
			}
		}
	}?>

	<A href="./index.php?m=tasks&a=view&task_id=<?php echo $a["task_id"];?>"><?php echo $a["task_name"];?></a></td>
	<td nowrap><?php echo fromDate(substr($a["task_start_date"], 0, 10));?></td>
	<td>
	<?php if ($a["task_duration"] > 24 ) {
		$dt = "day";
		$dur = $a["task_duration"] / 24;
	} else {
		$dt = "hour";
		$dur = $a["task_duration"];
	}
	if ($dur > 1) {
	       	// FIXME: this won't work for every language!		
		$dt.="s";
	}
        echo ($dur!=0)?$dur . " " . $dt:"n/a";
	?>
	</td>
	<td nowrap>
        <?php 
        	if($a["task_end_date"]) {
        		echo fromDate(substr($a["task_end_date"], 0, 10));
        	} else {
        		echo "n/a";
        	}
        ?>
	</td>
	</tr>
<?php }

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
?>

<table width="98%" border=0 cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/tasks.gif" alt="Tasks" border="0" width="44" height="38"></td>
	<td nowrap width="100%">
		<span class="title">Project Tasks</span>
	</td>
<form name="task_filter" method=GET action="./index.php">
<input type=hidden name=m value=tasks>
	<td nowrap align=right>
<?php
	echo arraySelect( $filters, 'f', 'size=1 class=text onChange="document.task_filter.submit();"', $f );
?>
	</td>
</form>

<?php /* ?>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickProject">
	<td nowrap align="right">
		Project:
		<select name="cookie_project" onChange="document.pickProject.submit()" style="font-size:8pt;font-family:verdana;">
			<option value="0" <?php if($project_id == 0)echo " selected" ;?> >all
<?php
        reset($projects);
        while ( list($key, $val) = each($projects) ) {
			echo "<option value=" . $val["project_id"];
			if ($val["project_id"] == $project_id) {
					echo " selected";
			}
			echo ">" . $val['project_name'];
} ?>
			</select>
	</td>
</form>
<?php */ ?>

</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right" width="100%"></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">id</th>
	<th width="20">work</th>
	<th width="15" align="center">p</th>
	<th width="200">task name</th>
	<th>start date</th>
	<th>duration&nbsp;&nbsp;</th>
	<th>finish date</th>
</tr>
<?php
//echo '<pre>'; print_r($projects); echo '</pre>';
reset( $projects );
while (list( $k, ) = each( $projects ) ) {
	$p = &$projects[$k];
	$tnums = count( @$p['tasks'] );
// don't show project if it has no tasks
	if ($tnums) {
//echo '<pre>'; print_r($p); echo '</pre>';
?>
<tr>
	<td>
		<a href="index.php?m=tasks&f=<?php; echo $f;?>&project_id=<?php echo $project_id ? 0 : $p["project_id"];?>">
			<img src="./images/icons/<?php echo $project_id ? 'expand.gif' : 'collapse.gif';?>" width="16" height="16" border="0" alt="<?php echo $project_id ? 'show other projects' : 'show only this project';?>">
		</a>
	</td>
	<td colspan="8">
		<table width="100%" border="0">
		<tr>
			<td nowrap style="border: outset #eeeeee 2px;background-color:<?php echo $p["project_color_identifier"];?>">
				<A href="./index.php?m=projects&a=view&project_id=<?php echo $k;?>">
				<span style='color:<?php echo bestColor( $p["project_color_identifier"] ); ?>;text-decoration:none;'><B><?php echo $p["project_name"];?></b></span></a>
			</td>
			<td width="<?php echo (101 - intval($p["project_precent_complete"]));?>%">
				<?php echo (intval($p["project_precent_complete"]));?>%
			</td>
		</tr>
		</table>
</tr>
<?php
		$done = array();
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
		
		if($tnums && ENABLE_GANTT_CHARTS) { ?>
		<tr>
			<td colspan="8" align=right>
				<input type="button" class=button value="see gant chart" onClick="javascript:window.location='index.php?m=tasks&a=viewgantt&project_id=<?php echo $p["project_id"] ?>';">
			</td>	
		</tr>
		<?php }
	}
}
?>
</table>
<table height="100%">
<tr><td>&nbsp;</td></TR>
</table>