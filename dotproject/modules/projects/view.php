<?
//pull users;
$usql="select user_first_name, user_last_name, user_id from users order by user_last_name";
$urc = mysql_query($usql);

$csql="select company_name, company_id from companies order by company_name";
$crc = mysql_query($csql);


if(empty($project_id))$project_id =0;
$psql = "Select * from projects, companies, users 
where project_id = $project_id
and project_company = company_id 
and user_id = project_owner";
$prc = mysql_query($psql);
$prow = mysql_fetch_array($prc);
if(strlen($prow["project_start_date"]) == 0){
		$start_date = date(time());
	}
	else{
		$start_date = mktime(	0,	0,	0,	substr($prow["project_start_date"],5,2),	 substr($prow["project_start_date"],8,2), 	 substr($prow["project_start_date"],0,4) );
	}
	
if(strlen($prow["project_end_date"]) == 0){
		$end_date = date(time()+(3600*24));
	}
	else{
		$end_date = mktime(	0,	0,	0,	substr($prow["project_end_date"],5,2),	 substr($prow["project_end_date"],8,2), 	 substr($prow["project_end_date"],0,4) );
		//$end_date = $prow["project_end_date"];
	}
	
if(strlen($prow["project_actual_end_date"]) ==0){
		$actual_end_date = 0;
	}
	else{
		$actual_end_date = mktime(	0,	0,	0,	substr($prow["project_actual_end_date"],5,2),	 substr($prow["project_actual_end_date"],8,2), 	 substr($prow["project_actual_end_date"],0,4) );
	}

?>

<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>
	<TR>
		<TD><img src="./images/icons/projects.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Manage Project</span></td>
		<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>	
		<TD valign="top" align="right" width="100%">
			<TABLE width="400" bgcolor="#000000" cellspacing=1 cellpadding=2>
				<TR>
					<TD bgcolor="#eeeeee"><b>status</b></span> </TD>
					<TD nowrap bgcolor="#eeeeee"><b>Percent Complete</b></TD>
					<TD bgcolor="#eeeeee"><b>Active?</b></TD>
				</TR>
				<TR>
					<TD bgcolor="#eeeeee">
						<?if($prow["project_status"] ==0){?>Not Defined<?}?>
						<?if($prow["project_status"] ==1){?>Proposed<?}?>
						<?if($prow["project_status"] ==2){?>In planning<?}?>
						<?if($prow["project_status"] ==3){?>In progress<?}?>
						<?if($prow["project_status"] ==4){?>On hold<?}?>
						<?if($prow["project_status"] ==5){?>Complete<?}?>
					</TD>
					<TD bgcolor="#eeeeee"><?echo $prow["project_precent_complete"];?>%</TD>
					<TD bgcolor="#eeeeee"><?if($prow["project_active"]){?>Yes<?}else{?>No<?}?></TD>
				</TR>
			</TABLE>
		</td>
	</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD width="50%" nowrap>
		<a href="./index.php?m=projects">Project List</a> <b>:</b> 
		<a href="./index.php?m=projects&a=addedit&project_id=<?echo $prow["project_id"];?>">Edit this Project</a> 
		</td>
		<TD width="50%" align="right"><?include ("./includes/create_new_menu.php");?></td>
	</TR>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD style="border: outset #eeeeee 2px;" bgcolor="<?echo $prow["project_color_identifier"];?>">
					<?
					$r = hexdec(substr($prow["project_color_identifier"], 0, 2)); 
					$g = hexdec(substr($prow["project_color_identifier"], 2, 2)); 
					$b = hexdec(substr($prow["project_color_identifier"], 4, 2)); 
					if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128) {
						echo "<font color='white'>";
					};
					?><b><?echo $prow["project_name"];?></b>
		</TD>
	</tr>
</TABLE>

<table border="0" cellpadding="6" cellspacing="0" width="95%" bgcolor="#eeeeee">
	<tr valign="top">
		<td width="50%">
			<TABLE width="100%">
				<TR>
					<TD><b>Company:</b></TD>
					<td><?echo $prow["company_name"];?></td>
				</TR>
				<tr>
					<td><b>Short Name:</b></td>
					<td><?echo @$prow["project_short_name"];?></td>
				</tr>
				<tr>
					<td><b>Start date:</b></td> 
					<td><?echo fromDate(substr($prow["project_start_date"], 0,10));?></td>
				</tr>
				<tr>
					<td><b>Target End Date:</b></td> 
					<td><?echo fromDate(substr($prow["project_end_date"], 0, 10));?></td>
				</tr>
				<tr>
					<td><b>Actual End Date:</b></td> 
					<td><?echo fromDate(SUBSTR($prow["project_actual_end_date"], 0, 10));?></td>
				</tr>
				<tr>
					<td><b>Target Budget:</b></td>
					<td>$<?echo @$prow["project_target_budget"];?></td>
				</tr>
				<tr>
					<td><b>Project Owner:</b></td>
					<td>
						<?
							while($row = mysql_fetch_array($urc)){
								if($prow["project_owner"] == $row["user_id"]){
									echo $row["user_first_name"];?> <?echo $row["user_last_name"];
								}
							}?>
					</td>
				</tr>
				<tr>
					<td><b>URL:</b></td>
					<td><A href="<?echo @$prow["project_url"];?>" target="_new"><?echo @$prow["project_url"];?></A></td>
				</tr>
				<tr>
					<td><b>Staging URL:</b></td>
					<TD><A href="<?echo @$prow["project_demo_url"];?>" target="_new"><?echo @$prow["project_demo_url"];?></A></TD>
				</tr>
			</TABLE>
		
		</TD>
		<td width="50%">
			<b>Full Description</b><br>
			<?
			$newstr = str_replace( chr(10), "<BR>", $prow["project_description"]);
			echo $newstr;
			?>
		</td>		
	</TR>
</table>

<?//------Begin Task Include--------?>		
<?
if(empty($project_id))$project_id =0;

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
and user_tasks.task_id = tasks.task_id "; 

//Conditional SQL
if(intval($project_id > 0)){
	$pull_tasks.= "and task_project = " . $project_id ;
}
$pull_tasks.= " order by project_id, task_order";
//echo $pull_tasks;


$ptrc = mysql_query($pull_tasks);
$nums = mysql_num_rows($ptrc);
echo mysql_error();
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
echo mysql_error();
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
		<?for($y=0;$y<$level;$y++){
			if($y + 1==$level)	{
				echo "<img src=./images/corner-dots.gif width=16 height=12  border=0>";
			}
			else{
				echo "<img src=./images/shim.gif width=16 height=12  border=0>";
			}
		}?>
		
		<A href="./index.php?m=tasks&a=view&task_id=<?echo $tarr[$x]["task_id"];?>"><?echo $tarr[$x]["task_name"];?></a></td>		
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
		</tr>
		<?

		$str=findchild($tarr[$x]["task_id"], $level);
		}
	}
	return $str;
}
?>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
	<TR>
		<TD width=50% valign=top><strong>Tasks:</strong><br>
		
			<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
				<TR style="border: outset #eeeeee 2px;">
					<TD class="mboxhdr" width="10">&nbsp;</td>
					<TD class="mboxhdr" width="20">work</td>
					<TD class="mboxhdr" width=200>task</td>		
					<TD class="mboxhdr">duration&nbsp;&nbsp;</td>
				</tr>
					
			<?
			
			
			for($x =0;$x < $nums;$x++){
			
			
			
				if($tarr[$x]["task_parent"] == $tarr[$x]["task_id"]){?>
					<TR  bgcolor="#f4efe3">
					<TD><A href="./index.php?m=tasks&a=addedit&task_id=<?echo $tarr[$x]["task_id"];?>"><img src="./images/icons/pencil.gif" alt="Edit Task" border="0" width="12" height="12"></a></td>
					<TD align="right"><?echo intval($tarr[$x]["task_precent_complete"]);?>%</td>
					<TD valign="middle" width="100%"><img src="./images/icons/updown.gif" width="10" height="15" border=0 usemap="#arrow<?echo $tarr[$x]["task_id"];?>">
					<map name="arrow<?echo $tarr[$x]["task_id"];?>"><area coords="0,0,10,7" href=<?echo "./index.php?m=tasks&a=reorder&task_project=" . $tarr[$x]["task_project"] . "&task_id=" . $tarr[$x]["task_id"] . "&order=" . $tarr[$x]["task_order"] . "&w=u";?>>
					<area coords="0,8,10,14" href=<?echo "./index.php?m=tasks&a=reorder&task_project=" . $tarr[$x]["task_project"] . "&task_id=" . $tarr[$x]["task_id"] . "&order=" . $tarr[$x]["task_order"] . "&w=d";?>></map> <A href="./index.php?m=tasks&a=view&task_id=<?echo $tarr[$x]["task_id"];?>"><?echo $tarr[$x]["task_name"];?></a></td>		
					<TD nowrap><?
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
					</tr>
			
				<?
				$order = $tarr[$x]["task_order"];
				echo findchild($tarr[$x]["task_id"]);
				}
			}?>
			</TABLE>
		<?//------End Task Include--------?>		
		</TD>
		<TD width=50% valign=top>
		<strong>Forums:</strong>
		<?//------Begin Forum include --------?>	
		<?

		//Forum index.php
		$sql = "select forum_id,forum_project,forum_description,forum_owner,user_username,forum_name,forum_create_date,forum_last_date,forum_message_count,forum_moderated, project_name, project_color_identifier, project_id
		from forums, users, projects 
		where user_id = forum_owner and ";
		if(isset($project_id))$sql.= "forum_project = $project_id and ";
		$sql.= " project_id = forum_project order by forum_project, forum_name";
		$rc= mysql_query($sql);
		?>
<TABLE width="100%" border=0 cellpadding="2" cellspacing=1 bgcolor="white">
	<TR style="border: outset #eeeeee 2px;">
		<TD nowrap class="mboxhdr">&nbsp;</td>
		<TD nowrap class="mboxhdr" width="100%"><A href="#"><font color="white">Forum Name</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Messages</font></a></td>
		<TD nowrap class="mboxhdr"><A href="#"><font color="white">Last Post</font></a></td>
	</tr>
<?
while($row = mysql_fetch_array($rc)){?>
	<TR bgcolor="#f4efe3">
		<TD nowrap align=center>
			<?if($row["forum_owner"] == $user_cookie){?>
				<A href="./index.php?m=forums&a=addedit&forum_id=<?echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width=12 height=12></a>
			<?}?>
		</td>				
		<TD nowrap><A href="./index.php?m=forums&a=viewer&forum_id=<?echo $row["forum_id"];?>"><?echo $row["forum_name"];?></a></td>
		<TD nowrap><?echo $row["forum_message_count"];?></td>
		<TD nowrap><?if(intval($row["forum_last_date"])>0 ){
				echo $row["forum_last_date"];
			}
			else{
				echo "n/a";
			}
			?></td>
	</tr>
	<TR>
		<TD></td>
		<TD colspan=3><?echo $row["forum_description"];?></td>
	</tr>
<?}?>
</TABLE>
		<?//------End Forum include --------?>	
		</TD>
	</TR>
</TABLE>

