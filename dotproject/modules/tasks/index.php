<?php

if(empty($project_id))$project_id =0;
if(isset($HTTP_COOKIE_VARS["cookie_project"]))$project_id = $HTTP_COOKIE_VARS["cookie_project"];
if(isset($HTTP_GET_VARS["cookie_project"]))$project_id = $HTTP_GET_VARS["cookie_project"];
if(isset($HTTP_POST_VARS["cookie_project"]))$project_id = $HTTP_POST_VARS["cookie_project"];

$pluarr = array();
$tarr = array();
//task index
$pull_tasks = "Select
tasks.task_id,
task_parent,
task_name,
task_start_date,
task_end_date,
task_priority,
task_precent_complete,
task_duration,
task_order,
project_name,
project_precent_complete,
task_project
from tasks, projects, user_tasks
where task_project = projects.project_id
and user_tasks.user_id = $user_cookie
and user_tasks.task_id = tasks.task_id
and project_active <> 0
";

//Conditional SQL
if (intval( $project_id > 0 )) {
	$pull_tasks.= "and task_project = " . $project_id ;
}
$pull_tasks.= " order by project_id, task_order";

//echo $pull_tasks;
$ptrc = mysql_query($pull_tasks);
$nums = mysql_num_rows($ptrc);
//echo mysql_error();
$y = 0;
$orrarr[] = array("task_id"=>0, "order_up"=>0, "order"=>"");

//pull the tasks into an array
$projects = array();
for ($x=0; $x < $nums; $x++) {
	$tarr = mysql_fetch_array($ptrc);
	$projects[$tarr['project_name']][] = $tarr;
}

//Pull projects and their percent complete information
$ppsql = "select project_id,
project_color_identifier,
project_name,
count(tasks.task_id)  as countt,
avg(tasks.task_precent_complete)  as project_precent_complete
from projects
left join tasks on projects.project_id = tasks.task_project
where project_active <> 0
group by project_id
order by project_name";

$pprc = mysql_query($ppsql);
//echo mysql_error();
$pnums = mysql_num_rows($pprc);

for ($x=0; $x < $pnums; $x++) {
	$z = mysql_fetch_array($pprc);
	$newper = @intval($z["project_precent_complete"]);
	$pluarr[$z["project_name"]] = array(
		"project_color_identifier"=>$z["project_color_identifier"],
		"countt"=>$z["countt"],
		"project_precent_complete"=>$newper,
		"project_id"=>$z["project_id"]
	);
}

//This kludgy function echos children tasks as threads

function showtask( &$a, $level=0 ) { 
	global $done;
	$done[] = $a['task_id']; ?>
	<TR bgcolor="#f4efe3">
	<TD><A href="./index.php?m=tasks&a=addedit&task_id=<?php echo $a["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
	<TD align="right"><?php echo intval($a["task_precent_complete"]);?>%</td>
	<TD>
	<?php if ($a["task_priority"] < 0 ) {
		echo "<img src='./images/icons/low.gif' width=13 height=16>";
	} else if ($a["task_priority"] > 0) {
		echo "<img src='./images/icons/" . $a["task_priority"] .".gif' width=13 height=16>";
	}?>
	</td>
	<TD width=90%>

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
	<TD nowrap><?php echo fromDate(substr($a["task_start_date"], 0, 10));?></td>
	<TD>
	<?php if ($a["task_duration"] > 24 ) {
		$dt = "day";
		$dur = $a["task_duration"] / 24;
	} else {
		$dt = "hour";
		$dur = $a["task_duration"];
	}
	if ($dur > 1) {
		$dt.="s";
	}
	echo $dur . " " . $dt ;
	?>
	</td>
	<TD nowrap><?php echo fromDate(substr($a["task_end_date"], 0, 10));?></td>
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

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form action="<?php echo $REQUEST_URI;?>" method="post" name="pickProject">
	<TR>
	<TD><img src="./images/icons/tasks.gif" alt="<?php echo ptranslate("Tasks");?>" border="0" width="44" height="38"></td>
		<TD nowrap><span class="title">Tasks: <?php if($project_id == 0){echo "All";} else {echo @$tarr[0]["project_name"];} ;?></span></td>
		<TD align="right" width="100%">Project: <select name="cookie_project" onChange="document.pickProject.submit()" style="font-size:8pt;font-family:verdana;">
		<option value="0" <?php if($project_id == 0)echo " selected" ;?> >all
		<?php
		reset($pluarr);
		while ( list($key, $val) = each($pluarr) ) {
			echo "<option value=" . $val["project_id"];
			if ($val["project_id"] == $project_id) {
				echo " selected";
			}
			echo ">" . $key ;
		}?>
		</select><br>
		<?php include ("./includes/create_new_menu.php");?>
		</td>
	</tr>
</form>
</TABLE>

<?php if(isset($message))echo $message;?>
<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
	<TR style="border: outset #eeeeee 2px;">
		<TD class="mboxhdr" width="10">id</td>
		<TD class="mboxhdr" width="20">work</td>
		<TD class="mboxhdr" width="15" align="center">p</td>
		<TD class="mboxhdr" width=200>task name</td>
		<TD class="mboxhdr">start date</td>
		<TD class="mboxhdr">duration&nbsp;&nbsp;</td>
		<TD class="mboxhdr">finish date</td>
	</tr>
<?php

while (list( $p, $tarr ) = each( $projects )) {
	$pci = $pluarr[$p]["project_color_identifier"];
	$r = hexdec(substr($pci, 0, 2));
	$g = hexdec(substr($pci, 2, 2));
	$b = hexdec(substr($pci, 4, 2));

	if ($r < 153 && $g < 153 || $r < 153 && $b < 153 || $b < 153 && $g < 153) {
		$font = "#ffffff";
	} else {
		$font = "#000000";
	}
?>
	<TR>
		<TD colspan=9  bgcolor="#f4efe3">

			<table width="100%" border=0>
				<tr>
					<TD nowrap style="border: outset #eeeeee 2px;" bgcolor="<?php echo $pluarr[$p]["project_color_identifier"];?>">
						<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[0]["task_project"];?>"><span style='color:<?php echo $font;?>;text-decoration:none;'><B><?php echo $p;?></b></span></a>
					</td>
					<TD width="<?php echo (101 - intval($pluarr[$p]["project_precent_complete"]));?>%">
						<?php echo (intval($pluarr[$p]["project_precent_complete"]));?>%
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?php
	$done = array();
	while (list( , $t ) = each( $tarr )) {
		if ($t["task_parent"] == $t["task_id"]) {
			showtask( $t );
			findchild( $tarr, $t["task_id"] );
		}
	}
	reset( $tarr );
	while (list( , $t ) = each( $tarr )) {
		if ( !in_array( $t["task_id"], $done )) {
			showtask( $t, 1 );
		}
	}
}
?>
</TABLE>
<TABLE height="100%">
	<TR><TD>&nbsp; </TD></TR>
</TABLE>
