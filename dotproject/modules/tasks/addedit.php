<?
if(empty($project_id))$project_id =0;
//pull users;
if(empty($task_id))$task_id =0;

//Pull all users
$usql="select user_first_name, user_last_name, user_id from users order by user_last_name";
$urc = mysql_query($usql);

//Pull users on this task
$tsql = "
select 
t.task_id, u.user_id, u.user_username, u.user_first_name,u.user_last_name
from users u, 
user_tasks t 
where 
t.task_id =$task_id and 
t.task_id <> 0 and
t.user_id = u.user_id
";

$tsql = mysql_query($tsql);


$psql = "Select * from tasks where task_id = $task_id";
$prc = mysql_query($psql);
if($prow = mysql_fetch_array($prc)){
	//Pull specific project information
	$pisql="select project_name, project_id from projects where project_id =". $prow["task_project"];
	$pirc = mysql_query($pisql);
	$pirow = mysql_fetch_array($pirc);
}
else
{
	//Pull project information
	$pisql="select project_name, project_id from projects where project_id = $project_id";
	$pirc = mysql_query($pisql);
	$pirow = mysql_fetch_array($pirc);
}
if(isset($pirow["project_id"]))$project_id = $pirow["project_id"];

//Pull tasks for the parent list
$atsql="select task_name, task_id, task_project from 
tasks
where 
task_project = $project_id and task_id <> $task_id
order by task_project";
$atrc = mysql_query($atsql);




//------------------------------------ Start Page ----------------------------------------------//
?>

<SCRIPT>


function popCalendar(x){
var form = document.AddEdit;

	mm = <?echo strftime("%m", time());?>;
	dd = <?echo strftime("%d", time());?>;
	yy = <?echo strftime("%Y", time());?>;

<? JScalendarDate("AddEdit"); ?>
	
	newwin=window.open('./calendar.php?form=AddEdit&page=tasks&field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false');
}


function submitIt(){
var form = document.AddEdit;

	var fl = form.assigned.length -1;

	if(form.task_name.value.length < 3){
		alert("Please enter a valid task Name");
		form.task_name.focus();	
	}
	else if(form.task_start_date.value.length < 9){
		alert("Please enter a valid start date");
		form.task_start_date.focus();	
	}
	else if(form.duration.value.length < 1){
		alert("Please enter the duration of this task");
		form.duration.focus();	
	}
	else{
		form.hassign.value = "";
		for (fl; fl > -1; fl--){   
			form.hassign.value = "," + form.hassign.value +","+ form.assigned.options[fl].value
		}
		form.submit();
	}
}


function addUser(){
	var form = document.AddEdit;
	var fl = form.resources.length -1;
	var au = form.assigned.length -1;
	var users = "x";
	
	//build array of assiged users
	for (au; au > -1; au--){   
		users = users + "," + form.assigned.options[au].value + ","
	}
	
	//Pull selected resources and add them to list
	for (fl; fl > -1; fl--){   
		if(form.resources.options[fl].selected && users.indexOf("," + form.resources.options[fl].value + ",") == -1){
			t = form.assigned.length
			opt = new Option(form.resources.options[fl].text, form.resources.options[fl].value);
			form.assigned.options[t] = opt
		}
	}

}


function removeUser(){
	var form = document.AddEdit;
	fl = form.assigned.length -1;
	
	for (fl; fl > -1; fl--) 
	{   
		if(form.assigned.options[fl].selected) 
		{
			form.assigned.options[fl] = null;
		}
	}

}

function delIt(){
	
	if(confirm("Are you sure that you would like to delete this task?\n")){
		var form = document.AddEdit;
		form.del.value=1;
		form.submit();
	}
}

</script>


<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form name="AddEdit" action="./index.php?m=tasks" method="post">
<input name="dosql" type="hidden" value="addeditTask">
<input name="del" type="hidden" value="0">
<input name="task_id" type="hidden" value="<?echo $task_id;?>">
<input name="task_project" type="hidden" value="<?echo $project_id;?>">
	<TR>
	<TD><img src="./images/icons/tasks.gif" alt="" border="0"></td>
		<TD  nowrap><span class="title">
		<?if($task_id > 0)
		{
			echo "Edit Existing task for ". $pirow['project_name'];
		}
		else
		{
			echo "Create New task for ". $pirow['project_name'];
		}?>
		</span></td>
		<TD align="right" width="100%" valign="bottom"><?if($task_id){?><A href="javascript:delIt()">delete task<img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this task" border="0"></a><?}?></td>
	</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%" bgcolor="#dddddd">
	<tr>
		<td class="allFormsTitleHeader" valign="middle">
			<img src="./images/icons/minitask.gif" alt="" border="0" align="absmiddle">
			<b>
			<?if($task_id > 0){
					echo "Edit the task using the form below";
				}
				else{
					echo "To create a new task complete the form below";
				}?>
				</b>
    	</td>
	</tr>
</table>


<table border="1" cellpadding="6" cellspacing="0" width="95%" bgcolor="#eeeeee">
		<tr class="basic" valign="top" width="50%">
			<td><span id="ccstasknamestr"><span class="FormLabel">task name</span> <span class="FormElementRequired">*</span></span><br><input type="text" name="task_name" value="<?echo @$prow["task_name"];?>" size="25" maxlength="50"></td>
			<td>
				<TABLE width="100%" bgcolor="#dddddd">
					<TR>
						<TD><span class="FormLabel">priority</span> <span class="FormElementRequired">*</span></TD>
						<TD nowrap>Percent Complete</TD>
						<TD>Milestone?</TD>
					</TR>
					<TR>
						<TD nowrap><input type="radio" name="task_priority" value="-1" <?if($prow["task_priority"] ==-1){?>checked<?}?>>low 
								<input type="radio" name="task_priority" value="0" <?if(intval($prow["task_priority"]) ==0){?>checked<?}?>>normal 
								<input type="radio" name="task_priority" value="1" <?if($prow["task_priority"] ==1){?>checked<?}?>>high

			
						</TD>
						<TD>	
						<select name="task_precent_complete">
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
					</select> %
					</TD>
					<TD>
					<input type=checkbox value=1 name="task_milestone" <?if($prow["task_milestone"]){?>checked<?}?>>
					</TD>
				</TR>
			</TABLE>
			</td>
		</tr>
		<tr class="basic" valign="top">
		<TD width="50%">
		task owner<br>
		<select name="task_owner" style="width:200px;">
		
		<?while($row = mysql_fetch_array($urc)){?>
		<option value="<?echo $row["user_id"];?>" 
		<?
		if($task_id == 0 && $row["user_id"] == $user_cookie){
			echo "selected";
		}
		else if($prow["task_owner"] == $row["user_id"])
			{
			echo "selected";
			}?>><?echo $row["user_first_name"];?> <?echo $row["user_last_name"];?> 
		<?}?>
		</select><br>
		Related URL<br>		
		<input type="Text" name="task_related_url" value="<?echo @$prow["task_related_url"];?>" size="50" maxlength="255""><br>
		<table>
			<tr>
				<TD>Task budget</td>
				<TD><img src="./images/shim.gif" width=30 height=1></td>
				<TD>Task Parent:</td>
			</tr>
			<tr>
				<TD>$<input type="Text" name="task_target_budget" value="<?echo @$prow["task_target_budget"];?>" size="10" maxlength="10"></td>
				<TD><img src="./images/shim.gif" width=30 height=1></td>
				<TD><select name="task_parent" style="width:150px;"><option value="<?echo $prow["task_id"];?>">None
						<?
						while($row = mysql_fetch_array($atrc)){
							if($row["task_id"] == $prow["task_parent"]){
							echo "<option selected value=\"".$row["task_id"]."\">" . $row["task_name"];
							}
							else{
								echo "<option value=\"" . $row["task_id"]."\">".$row["task_name"];
							}
						}?></select></td>
			</tr>
		</table>
		</td>
					<td  align="center" width="50%">
						<TABLE width="300">
							<TR>
								<TD><span id="startmmint"><span class="FormLabel">start date<br>
								(<?echo dateFormat()?>)</span></span></TD>
								<TD><span id="targetmmint"><span class="FormLabel">finish date<br>
								(<?echo dateFormat()?>)</span></TD>
							</TR>
							<TR>
								<TD nowrap><input type="text" name="task_start_date" value="<?if(intval($prow["task_start_date"]) > 0){
								echo fromDate(substr($prow["task_start_date"], 0, 10));
								}
								else{
								echo fromDate(date("Y", time()) ."-" . date("m", time()) ."-" . date("d", time()));
								};?>" size="10" maxlength="10"><a href="#" onClick="popCalendar('task_start_date');"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="popCalendar('task_start_date');">calendar</A> &nbsp; &nbsp; &nbsp;</td>
								<TD nowrap><input type="text" name="task_end_date" value="<?if(intval($prow["task_end_date"]) > 0)echo fromDate(substr($prow["task_end_date"], 0, 10));?>" size="10" maxlength="10"><a href="#" onClick="popCalendar('task_end_date')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="popCalendar('task_end_date')">calendar</A></td>
							</tr>
							<TR><TD colspan=2>Expected duration:</td></tr>
							<TR><TD colspan=2>
							<? if(($prow["task_duration"]) > 24 ){
								$newdir = ($prow["task_duration"] / 24);
								$dir = 24;
								}
								else{
								$newdir = ($prow["task_duration"]);
								$dir = 1;
								}
								if($newdir ==0)$newdir ="";
							?>
								
															
							<input type="text" name="duration" maxlength =4 size=5 value="<?echo $newdir;?>">
							<select name="dayhour">
								<option value="1" <? if($dir ==1)echo "selected";?>>hour(s)
								<option value="24" <? if($dir ==24)echo "selected";?>>day(s)
							</select>
							</td></tr>
						</table>
						
						
			</td>
		</tr>
		<tr class="basic">
					<td valign="middle">
					<span id="fulldesctext"><span class="formlabel">Instructions:</span></span><br>
				<textarea name="task_description" cols="38" rows="10" wrap="virtual"><?echo @$prow["task_description"];?></textarea>
			</td>
			<td valign="middle">
			<TABLE>
				<TR>
					<TD>Resources</td>
					<TD></td>
					<TD>Assigned to Task</td>
				</TR>
				<TR>
					<TD><Select multiple name="resources" style="width:150px" size="10" style="font-size:9pt;">
						<?
						mysql_data_seek($urc,0);
						while($row = mysql_fetch_array($urc)){
						echo "<option value=\"".$row["user_id"]."\">". $row["user_first_name"] ." " . $row["user_last_name"];
						
						
						}?>
					
					</select></td>
					<TD nowrap>
					<input type="button" value=" << " onClick="removeUser()"> 
					<input type="button" value=" >> " onClick="addUser()"> </td>
					<TD><Select multiple name="assigned" style="width:150px" size="10" style="font-size:9pt;">
						<?
						mysql_data_seek($urc,0);
						while($row = mysql_fetch_array($tsql)){
						echo "<option value=\"".$row["user_id"]."\">" . $row["user_first_name"] ." " .$row["user_last_name"];
						
						
						}?>
					
					</select></td>
				</tr>
				<TR>
				<TD colspan=3 align="center"><input type="checkbox" name="notify" value="1"> Notify Assignees of Task by Email</td></tr>
			</table>
			</td>

		</tr>

		</table>
	<table border="0" cellspacing="0" cellpadding="3" width="95%">
		<tr class="basic">
			<td height="40" width="35%">
				<span class="FormElementRequired">*</span> <span class="FormInstruction">indicates required field</span>
			</td>
			<td height="40" width="30%">
					&nbsp;
			</td>
			<td  height="40" width="35%" align="right">
				<table><tr>
<td><input class=button type="Button" name="Cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=tasks';}"></td>
<td><input class=button type="Button" name="btnFuseAction" value="save" onClick="submitIt();"></td>

				</tr></table>
			</td>
		</tr>
	</table>
	</center>
<input type="hidden" name="hassign">
</form>
</body>
</html>

