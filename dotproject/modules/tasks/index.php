<?

if(empty($project_id))$project_id =0;
if(isset($HTTP_COOKIE_VARS["cookie_project"]))$project_id = $HTTP_COOKIE_VARS["cookie_project"];
if(isset($HTTP_GET_VARS["cookie_project"]))$project_id = $HTTP_GET_VARS["cookie_project"];
if(isset($HTTP_POST_VARS["cookie_project"]))$project_id = $HTTP_POST_VARS["cookie_project"];

$pluarr = array();
$tarr = array();
//task index
$pull_tasks = "Select 
tasks.task_id, 
project_color_identifier, 
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
if(intval($project_id > 0)){
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
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array($ptrc);
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

for($x=0;$x<$pnums;$x++){
	$z = mysql_fetch_array($pprc);
	$newper = @intval($z["project_precent_complete"]);
	$pluarr[$z["project_id"]] = array("project_color_identifier"=>$z["project_color_identifier"],
	"project_name"=>$z["project_name"],
	"countt"=>$z["countt"],
	"project_precent_complete"=>$newper, 
	"project_id"=>$z["project_id"]
	);
	
	}


//This kludgy function echos children tasks as threads

function findchild($parent, $level =0){

	GLOBAL $tarr, $nums;
	reset($tarr);
	$level = $level+1;
	$str ="";
	for($x=0;$x<$nums;$x++){
		reset($tarr);
		if($tarr[$x]["task_parent"] == $parent && $tarr[$x]["task_parent"] != $tarr[$x]["task_id"]){
		
		?>
		<TR bgcolor="#f4efe3">
		<TD><A href="./index.php?m=tasks&a=addedit&task_id=<?echo $tarr[$x]["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
		<TD align="right"><?echo intval($tarr[$x]["task_precent_complete"]);?>%</td>
		<TD>
		<?if($tarr[$x]["task_priority"] <0){
			echo "<img src='./images/icons/low.gif' width=13 height=16>";
		}else if($tarr[$x]["task_priority"] >0){
			echo "<img src='./images/icons/" . $tarr[$x]["task_priority"] .".gif' width=13 height=16>";
		}?>
		</td>
		<TD width=90%>
		<?for($y=0;$y<$level;$y++){
			if($y + 1==$level)	{
				echo "<img src=./images/corner-dots.gif width=16 height=12  border=0>";
			}
			else{
				echo "<img src=./images/shim.gif width=16 height=12  border=0>";
			}
		}?>
		
		<A href="./index.php?m=tasks&a=view&task_id=<?echo $tarr[$x]["task_id"];?>"><?echo $tarr[$x]["task_name"];?></a></td>		
		<TD nowrap><?echo fromDate(substr($tarr[$x]["task_start_date"], 0, 10));?></td>
				<TD><?
			if($tarr[$x]["task_duration"] > 24 ){
				$dt = "day";
				$dur = $tarr[$x]["task_duration"] / 24;
			}
			else{
				$dt = "hour";
				$dur = $tarr[$x]["task_duration"];
			}
			if($dur > 1)$dt.="s";
				
		
		echo $dur . " " . $dt ;?></td>
		<TD nowrap><?echo fromDate(substr($tarr[$x]["task_end_date"], 0, 10));?></td>
		</tr>
		<?

		$str=findchild($tarr[$x]["task_id"], $level);
		}
	}
	return $str;
}

?>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form action="<?echo $REQUEST_URI;?>" method="post" name="pickProject">
	<TR>
	<TD><img src="./images/icons/tasks.gif" alt="<?echo ptranslate("Tasks");?>" border="0" width="44" height="38"></td>
		<TD nowrap><span class="title">Tasks: <?if($project_id == 0){echo "All";} else {echo @$tarr[0]["project_name"];} ;?></span></td>
		<TD align="right" width="100%">Project: <select name="cookie_project" onChange="document.pickProject.submit()" style="font-size:8pt;font-family:verdana;">
		<option value="0" <?if($project_id == 0)echo " selected" ;?> >all
		<?
		reset($pluarr);
		while ( list($key, $val) = each($pluarr) ) { 
			if($val["project_id"] == $project_id){
				echo "<option selected value=" . $val["project_id"] . ">" . $val["project_name"] ;
			}
			else{
				echo "<option value=" . $val["project_id"] . ">" . $val["project_name"] ;
			}
		}?>
		</select><br>
		<?include ("./includes/create_new_menu.php");?>
		</td>
	</tr>
</TABLE>

<?if(isset($message))echo $message;?>
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
	
	
<?
$isproj = 0;

for($x =0;$x < $nums;$x++){
	if($tarr[$x]["task_project"] != $isproj){
	
		$r = hexdec(substr($tarr[$x]["project_color_identifier"], 0, 2)); 
		$g = hexdec(substr($tarr[$x]["project_color_identifier"], 2, 2)); 
		$b = hexdec(substr($tarr[$x]["project_color_identifier"], 4, 2)); 
		
		if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128){
			$font = "<span style='color:#ffffff;text-decoration:none;' >";
		}
		else{
			$font = "<span style='color:#000000;text-decoration:none;' >";
		}

	if($isproj > 0)echo "<TR bgcolor="#f4efe3"><TD colspan=9>&nbsp;</TD></TR>";

?>
	<TR>
		<TD colspan=9  bgcolor="#f4efe3">
		
			<table width="100%" border=0>
				<tr>
					<TD nowrap style="border: outset #eeeeee 2px;" bgcolor="<?echo $pluarr[$tarr[$x]["task_project"]]["project_color_identifier"];?>"><A href="./index.php?m=projects&a=view&project_id=<?echo $tarr[$x]["task_project"];?>"><?echo $font;?><B><?echo $tarr[$x]["project_name"];?></b></span></a></td>
					<TD width="<?echo (101 - intval($pluarr[$tarr[$x]["task_project"]]["project_precent_complete"]));?>%"> <?echo (intval($pluarr[$tarr[$x]["task_project"]]["project_precent_complete"]));?>%</td>

				</tr>
			</table>
				
		</td>

	</tr>
<?}

$isproj = $tarr[$x]["task_project"];


	if($tarr[$x]["task_parent"] == $tarr[$x]["task_id"]){?>
		<TR  bgcolor="#f4efe3">
		<TD><A href="./index.php?m=tasks&a=addedit&task_id=<?echo $tarr[$x]["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
		<TD align="right"><?echo intval($tarr[$x]["task_precent_complete"]);?>%</td>
		<TD>
		<?if($tarr[$x]["task_priority"] <>0){echo "<img src='./images/icons/" . $tarr[$x]["task_priority"] .".gif' width=13 height=16>";}?>
		
		</td>
		<TD valign="middle"><img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow<?echo $tarr[$x]["task_id"];?>">
		<map name="arrow<?echo $tarr[$x]["task_id"];?>"><area coords="0,0,10,7" href=<?echo "./index.php?m=tasks&a=reorder&task_project=" . $tarr[$x]["task_project"] . "&task_id=" . $tarr[$x]["task_id"] . "&order=" . $tarr[$x]["task_order"] . "&w=u";?>>
		<area coords="0,8,10,14" href=<?echo "./index.php?m=tasks&a=reorder&task_project=" . $tarr[$x]["task_project"] . "&task_id=" . $tarr[$x]["task_id"] . "&order=" . $tarr[$x]["task_order"] . "&w=d";?>></map> <A href="./index.php?m=tasks&a=view&task_id=<?echo $tarr[$x]["task_id"];?>"><?echo $tarr[$x]["task_name"];?></a></td>		
		<TD><?echo fromDate(substr($tarr[$x]["task_start_date"], 0, 10));?></td>
		<TD><?
			if($tarr[$x]["task_duration"] > 24 ){
				$dt = "day";
				$dur = $tarr[$x]["task_duration"] / 24;
			}
			else{
				$dt = "hour";
				$dur = $tarr[$x]["task_duration"];
			}
			if($dur > 1)$dt.="s";
			echo $dur . " " . $dt ;?></td>
		<TD><?if(intval($tarr[$x]["task_end_date"]) >0)echo fromDate(substr($tarr[$x]["task_end_date"], 0, 10));?></td>
		</tr>

	<?
	$order = $tarr[$x]["task_order"];
	echo findchild($tarr[$x]["task_id"]);
	}
}?>
</TABLE>
<TABLE height="100%">
	<TR><TD>&nbsp; </TD></TR>
</TABLE>
