<?
//pull users;
$usql="select user_first_name, user_last_name, user_id from users order by user_last_name";
$urc = mysql_query($usql);

$csql="select company_name, company_id from companies order by company_name";
$crc = mysql_query($csql);

if(empty($task_id))$task_id =0;
$psql = "Select task_name, project_name, project_color_identifier,task_precent_complete, project_id, 
task_owner,task_id, task_priority,task_project,task_hours_worked,task_start_date,
task_duration,task_end_date,u.user_id as uuid, task_description, task_milestone,task_target_budget,task_priority,
task_related_url,users.user_username as username,u.user_first_name as ufname,  u.user_last_name as ulname
from projects, 
tasks left join users on  users.user_id = task_owner
left join users u on u.user_id = task_creator 
where task_id = $task_id
and task_project = project_id";
$prc = mysql_query($psql);
echo mysql_error();
$prow = mysql_fetch_array($prc);

$project_id = $prow["project_id"];
$sql2 = "select 
task_name, 
user_username, 
comment_title, 
comment_body, 
comment_date
from tasks, task_comments 
left join users on users.user_id = task_comments.comment_user 
where task_id = $task_id and
comment_task = task_id  
order by comment_date";
$crc = mysql_query($sql2);

echo mysql_error();


//Pull users on this task
$tsql = "
select 
u.user_id, u.user_username, u.user_first_name,u.user_last_name, u.user_email
from users u, 
user_tasks t 
where 
t.task_id =$task_id and
t.user_id = u.user_id";

$tsql = mysql_query($tsql);
echo mysql_error();


//Pull users on this task
$fsql = "Select file_id, file_name, file_size,file_type from files where file_task = $task_id and file_task <> 0";

$fsql = mysql_query($fsql);
echo mysql_error();










?>
<script>
function updateTask(){
	var form=document.update;
	if(form.comments.value.length < 1){
		alert("Please enter a worthwile commment");
		form.comments.focus();
	}
	else if(isNaN(parseInt(form.complete.value + 0))){
		alert("The percent complete must be a integer");
		form.complete.focus();		
	}
	else if(form.complete.value  <0 || form.complete.value > 100){
		alert("The percent complete must be a value between 0 and 100");
		form.complete.focus();		
	}
	else{
	form.submit();
	}

}


</script>
<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>

	<TR>
	<TD><img src="./images/icons/tasks.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Manage Task</span></td>
		<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
		<TD valign="top" align="right" width="100%">

		</td>
	</tr>
</TABLE>

<table border="0" cellpadding="0" cellspacing="2" width="95%">
	<TR>
		<TD nowrap><A href="./index.php?m=projects&a=view&project_id=<?echo $prow["project_id"];?>"><?echo $prow["project_name"];?></A><b> : </b></TD>
		<TD nowrap><A href="./index.php?m=tasks">Task List</a><b> : </b></TD>
		<TD nowrap><A href="./index.php?m=tasks&a=addedit&task_id=<?echo $prow["task_id"];?>">Edit this task</TD>
		<TD width="100%" align="right"><?include ("./includes/create_new_menu.php");?>
</TD>
	</TR>
</TABLE>	
	
	
<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
	
		<TD style="border: outset #eeeeee 2px;" width="50%" bgcolor="<?echo $prow["project_color_identifier"];?>">
							<?
					$r = hexdec(substr($prow["project_color_identifier"], 0, 2)); 
					$g = hexdec(substr($prow["project_color_identifier"], 2, 2)); 
					$b = hexdec(substr($prow["project_color_identifier"], 4, 2)); 
					
					if($r < 128 && $g < 128 || $r < 128 && $b < 128 || $b < 128 && $g < 128) 
					{
					echo "<font color='white'>";
					};
					?><b>TASK: <?echo @$prow["task_name"];?></b>
		</TD>

	</tr>
</TABLE>

<table border="0" cellpadding="0" cellspacing="6" width="95%" bgcolor="#cccccc">
		<tr bgcolor="#cccccc" valign="top">
			<td width="50%">
				<b>Details</b>
				<TABLE width="430" cellspacing=1>
				<TR>
					<TD align=right>Project:</td>
					<TD bgcolor="#eeeeee"><?echo @$prow["project_name"];?></td>
				</tr>
				<TR>
					<TD align=right>Task:</td>
					<TD bgcolor="#eeeeee"><?echo @$prow["task_name"];?></td>
				</tr>
				<TR>
					<TD align=right>Owner:</td>
					<TD bgcolor="#eeeeee"> <?echo @$prow["username"];?></td>
				</tr>				<TR>
					<TD align=right>Priority:</td>
					<TD bgcolor="#eeeeee"><?
					if($prow["task_priority"] == 0){
						echo "Normal";
					}
						elseif($prow["task_priority"] < 0){
						echo "Low";
					}
					else{
						echo "High";
					}
					?></td>
				</tr>
				<TR>
					<TD align=right>Web Address:</td>
					<TD bgcolor="#eeeeee" width="300"><?echo @$prow["task_related_url"];?></td>
				</tr>		
				<TR>
					<TD align=right>Milestone:</td>
					<TD bgcolor="#eeeeee" width="300"><?if($prow["task_milestone"]){echo "Yes";}else{echo "No";}?></td>
				</tr>
				<TR>
					<TD align=right>Percent Complete:</td>
					<TD bgcolor="#eeeeee" width="300"><?echo @$prow["task_precent_complete"];?>%</td>
				</tr>				
					<TR>
					<TD align=right>Time worked:</td>
					<TD bgcolor="#eeeeee" width="300"><?echo @$prow["task_hours_worked"];?></td>
				</tr>				
				</table>
			</td>

			
			
			
			<td width="50%" align="right">
				<table cellspacing=0 cellpadding=2 width="100%">
				<form name="update" action="./index.php?m=tasks&a=view" method="post">
				<input type="hidden" value="<?echo uniqid("");?>" name="uniqueid">
				<input type="hidden" value="updatetask" name="dosql">
				<input type="hidden" value="<?echo @$prow["task_id"];?>" name="task_id">
				<input type="hidden" value="<?echo $user_cookie;?>" name="user_id">
				<input type="hidden" value="Update :<?echo $$prow["task_name"];?>" name="comment_title">
				<input type="hidden" value="<?echo @$prow["task_hours_worked"];?>" name="already_worked">
					<TR>
						<TD colspan=2><b>Update Task</b></TD>
						<TD colspan=2>comments:</TD>
					</TR>
					<TR bgcolor="#eeeeee">
						<TD align="right" nowrap>hours worked<br>since last update
						 </td>
						<TD bgcolor="#eeeeee"><input type="text" name="worked" maxlength=3 size=4></td>
						<TD rowspan=2><textarea name="comments" cols=25 rows=4></textarea></td>
					</tr>
					<TR bgcolor="#eeeeee">
						<TD bgcolor="#eeeeee" align="right">percent<br>
						complete</td>
						<TD bgcolor="#eeeeee">						
						<select name="complete">
						<option value="0" <?if($prow["task_precent_complete"] ==0){?>selected<?}?>>0
						<option value="5" <?if($prow["task_precent_complete"]==5){?>selected<?}?>>5
						<option value="10" <?if($prow["task_precent_complete"] ==10){?>selected<?}?>>10
						<option value="15" <?if($prow["task_precent_complete"]==15){?>selected<?}?>>15
						<option value="20" <?if($prow["task_precent_complete"] ==20){?>selected<?}?>>20
						<option value="25 <?if($prow["task_precent_complete"] ==25){?>selected<?}?>">25
						<option value="30" <?if($prow["task_precent_complete"] ==30){?>selected<?}?>>30
						<option value="35" <?if($prow["task_precent_complete"]==35){?>selected<?}?>>35
						<option value="40" <?if($prow["task_precent_complete"] ==40){?>selected<?}?>>40
						<option value="45" <?if($prow["task_precent_complete"] ==45){?>selected<?}?>>45
						<option value="50" <?if($prow["task_precent_complete"] ==50){?>selected<?}?>>50
						<option value="55" <?if($prow["task_precent_complete"] ==55){?>selected<?}?>>55
						<option value="60" <?if($prow["task_precent_complete"] ==60){?>selected<?}?>>60
						<option value="65" <?if($prow["task_precent_complete"] ==65){?>selected<?}?>>65
						<option value="70" <?if($prow["task_precent_complete"] ==70){?>selected<?}?>>70
						<option value="75" <?if($prow["task_precent_complete"] ==75){?>selected<?}?>>75
						<option value="80" <?if($prow["task_precent_complete"] ==80){?>selected<?}?>>80
						<option value="85" <?if($prow["task_precent_complete"] ==85){?>selected<?}?>>85
						<option value="90" <?if($prow["task_precent_complete"] ==90){?>selected<?}?>>90
						<option value="95" <?if($prow["task_precent_complete"] ==95){?>selected<?}?>>95
						<option value="100" <?if($prow["task_precent_complete"] ==100){?>selected<?}?>>100
					</select> %</td>
					</tr>
					<TR>
						<TD colspan=3 ALIGN="CENTER"><input type="button" value="update task" onClick="updateTask()"></td>
					
					</tr>
					</form>
				</table>
			
			
			</td>
		</tr>
		<tr bgcolor="#cccccc">
			<td valign="top">
				<b>Dates and Targets</b>
				<TABLE width="100%" cellspacing=1>
					<TR>
						<TD align=right>Start Date:</TD>
						<TD bgcolor="#eeeeee" width="300"><?echo fromDate(substr($prow["task_start_date"], 0, 10));?></td>
					</TR>
					<TR>
						<TD align=right>End Date:</TD>
						<TD bgcolor="#eeeeee" width="300"><?if(intval($prow["task_end_date"]) == 0){echo "n/a";}else{echo fromDate(substr($prow["task_end_date"], 0, 10));}?></td>
					</tr>
					<TR>
						<TD align=right>Expected Duration:</td>
						<TD bgcolor="#eeeeee" width="300"><? 
						$dur = returnDur($prow["task_duration"]);
						echo $dur["value"] . " " . $dur["type"];
						
						?></td>		
					</tr>				
					<TR>
						<TD align=right>Target Budget:</td>
						<TD bgcolor="#eeeeee" width="300"><?echo $prow["task_target_budget"];?></td>		
					</tr>
				</TABLE>
			</TD>
			<td valign="top" rowspan=2>
				<b>Assigned Users</b>
				<TABLE width="100%" cellspacing=1 bgcolor="black">
				<?while($row = mysql_fetch_array($tsql)){?>
				<TR><TD bgcolor="#f4efe3"><?echo $row["user_username"];?></td><TD bgcolor="#f4efe3"><?echo $row["user_email"];?></td></tr>
				<?};?>
			</TABLE>

			<TABLE width="100%" cellspacing=0 cellpadding=0>
			<TR><TD><B>Attached Files</b></td><TD align=right><A href="./index.php?m=files&a=addedit&project_id=<?echo $project_id;?>&task_id=<?echo $task_id;?>">Attach a file<img src="./images/icons/minifile.gif" align=absmiddle width=20 height=28 alt="attach a file to this task" border=0></a></td></tr>
			</TABLE>
			 
			<TABLE width="100%" cellspacing=1 bgcolor="black">
				<?if(mysql_num_rows($fsql)==0)echo "<TR><TD bgcolor=#ffffff>none</td></tr>";
				while($row = mysql_fetch_array($fsql)){?>
				<TR><TD bgcolor="#eeeeee"><A href="./fileviewer.php?file_id=<?echo $row["file_id"];?>"><?echo $row["file_name"];?></a></td><TD bgcolor="#ffffff"><?echo $row["file_type"];?></td><TD bgcolor="#eeeeee"><?echo $row["file_size"];?></td></tr>
				<?};?>
			</TABLE>
		</td>
	</tr>
	<TR>
		<TD>
			<b>full description</b>
			<TABLE WIDTH="100%" height="66">
				<TR>
					<TD bgcolor="#eeeeee"><?$newstr = str_replace( chr(10), "<BR>", $prow["task_description"]);echo $newstr;?></td>
				</tr>
			</table>
		</td>
	</tr>
</table>


<table border="0" cellpadding="0" cellspacing="4" width="95%" bgcolor="#eeeeee">
	<TR>
		<TD><B>Task Log and Comments</b></td>
	</tr>
<TR><TD>
<table border="0" cellpadding="3" cellspacing="1" width="100%" bgcolor="#cccccc">
	<TR style="border: outset #eeeeee 2px;">
		<TD width="100" class="mboxhdr">Action</td>
		<TD width="100" class="mboxhdr">User</td>
		<TD class="mboxhdr">Comments</td>
		<TD width="150" class="mboxhdr">Date</td>
	
	</tr>	
	<?while($row = mysql_fetch_array($crc)){?>
	<TR bgcolor="white">
		<TD width="100"><?echo $row["comment_title"];?></td>
		<TD width="100"><?echo $row["user_username"];?></td>
		<TD><?$newstr = str_replace(chr(10), "<BR>",$row["comment_body"]);echo $newstr;?></td>
		<TD width="150"><?echo fromDate($row["comment_date"]);?></td>
	
	</tr>
	
	<?}?>
</TABLE>
</td></tr>
</TABLE>
	</center>

</body>
</html>

