<?
//pull users;
$usql="select user_first_name, user_last_name, user_id from users order by user_last_name";
$urc = mysql_query($usql);

//Pull companies
$csql="select company_name, company_id from companies order by company_name";
$crc = mysql_query($csql);
$cexists = mysql_num_rows($crc);

if(empty($project_id))$project_id =0;

//pull projects
$psql = "Select 
project_id,
project_company,
project_name,
project_short_name,
project_owner,
project_url,
project_demo_url,
project_start_date,
project_end_date,
project_actual_end_date,
project_status,
project_color_identifier,
project_description,
project_target_budget,
project_actual_budget,
project_creator,
project_active,
count(tasks.task_id)  as countt,  
avg(tasks.task_precent_complete)  as project_precent_complete 

from projects left join tasks on projects.project_id = tasks.task_project 

where 
project_id = $project_id
group by project_id 
";
$prc = mysql_query($psql);
echo mysql_error();
$prow = mysql_fetch_array($prc);
if(strlen($prow["project_start_date"]) == 0)
	{
		$start_date = date(time());
	}
	else
	{
		$start_date = mktime(	0,	0,	0,	substr($prow["project_start_date"],5,2),	 substr($prow["project_start_date"],8,2), 	 substr($prow["project_start_date"],0,4) );
	}
	
if(strlen($prow["project_end_date"]) == 0)
	{
		$end_date = date(time()+(3600*24));
	}
	else
	{
	$end_date = mktime(	0,	0,	0,	substr($prow["project_end_date"],5,2),	 substr($prow["project_end_date"],8,2), 	 substr($prow["project_end_date"],0,4) );
	
	
		//$end_date = $prow["project_end_date"];
	}
	
if(strlen($prow["project_actual_end_date"]) ==0)
	{
		$actual_end_date = 0;
	}
	else
	{
		$actual_end_date = mktime(	0,	0,	0,	substr($prow["project_actual_end_date"],5,2),	 substr($prow["project_actual_end_date"],8,2), 	 substr($prow["project_actual_end_date"],0,4) );
	}

?>















<SCRIPT language="javascript">
function setColor(){
	color = document.AddEdit.project_color_identifier.value;
	test.style.background = color;

}

function popCalendar(x){
var form = document.AddEdit;
mm = eval("document.AddEdit." + x + "MM_int.value");
dd = eval("document.AddEdit." + x + "DD_int.value");
yy = eval("document.AddEdit." + x + "YYYY_int.value");


newwin=window.open('./calendar.php?field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false');



}


function setShort(){
var form = document.AddEdit;
var x = 10;
if(form.project_name.value.length < 11) x = form.project_name.value.length;

if(form.project_short_name.value.length ==0)
	{
	form.project_short_name.value = form.project_name.value.substr(0,x);
	}
}

function submitIt(){
var form = document.AddEdit;

if(form.project_name.value.length < 3)
	{
	alert("Please enter a valid Project Name");
	form.project_name.focus();	
	}
	else if(form.project_color_identifier.value.length < 3)
	{
	alert("Please select a color to identify this project");
	form.project_color_identifier.focus();	
	}
	else
	{
	form.submit();
	}
}
</script>
<?
if(!$cexists){
echo "You add a company or client before creating a new project<br>";
echo "<a href=./index.php?m=companies&a=addedit>Click here to add a new Company/Client</A>";
}
else{
?>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<form name="AddEdit" action="./index.php?m=projects&a=dosql" method="post">
<input type="hidden" value="<?echo $project_id;?>" name="project_id">
	<TR>
	<TD><img src="./images/icons/projects.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">
		<?if($project_id > 0)
		{
			echo "Edit Existing Project";
		}
		else
		{
			echo "Create New Project";
		}?>
		</span></td>
		<TD align="right" width="100%">&nbsp;</td>
	</tr>
</TABLE>
<table border="0" cellpadding="0" cellspacing="0" width="99%" bgcolor="silver">
		<tr><td nowrap class="tabBG" valign="top">
			<img height=4 src="./images/shim.gif" width="1" align="top"><br>
			<table border="0" cellpadding="4" cellspacing="0" width="100%">
			<tr><td class="allFormsTitleHeader" valign="middle">
				<img src="./images/icons/icn_project.gif" width="16" height="16" alt="" border="0">
				<b>
				<?if($project_id > 0)
					{
						echo "Edit the project using the form below";
					}
					else
					{
						echo "To create a new project complete the form below";
					}?>
					</b>
		    </td><td align="right" class="viewheader" valign="top">
				<img height="28" width="20" src="./images/shim.gif">
			</td></tr>
			</table>
		</td></tr>
		</table>
		<table border="0" cellpadding="6" cellspacing="0" width="99%" bgcolor="dddddd">
		<tr class="basic" valign="top">
			<td>
			<table>
				<tr>
					<td valign="bottom"><span id="ccsprojectnamestr"><span class="FormLabel">project name</span> <span class="FormElementRequired">*</span></span><br>
						<input type="text" name="project_name" value="<?echo @$prow["project_name"];?>" size="25" maxlength="50" onBlur="setShort();"></td>
					<td>&nbsp;</td>
					<td valign="bottom"><span id="ccsprojectnamestr"><span class="FormLabel">company name</span> <span class="FormElementRequired">*</span></span><br>
									<select name="project_company" style="width:200px;">					
								<?while($row = mysql_fetch_array($crc)){?>
									<option value="<?echo $row["company_id"];?>" <?if($row["company_id"] == $prow["project_company"]){echo "selected";}?>><?echo $row["company_name"];?>  
								<?}?></select></td>
				</tr>
			</table>

			</td>
			<td>
				<span id="shortnamestr"><span class="FormLabel">short name</span> <span class="FormElementRequired">*</span></span><br>
				<input type="text" name="project_short_name" value="<?echo @$prow["project_short_name"];?>" size="10" maxlength="10">
			</td>
			<td>
				<span id="tempcolorstr"><span class="FormLabel">color identifier</span> <span class="FormElementRequired">*</span>&nbsp;&nbsp;<a href="#" onClick="newwin=window.open('./color_selector.php', 'calwin', 'width=320, height=300, scollbars=false');">change color</a></span><br>
				<input type="text" name="project_color_identifier" value="<?echo @$prow["project_color_identifier"];?>" size="6" onBlur="setColor();"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
				<span id="test" title="test" style="background:#<?echo @$prow["project_color_identifier"];?>;"><a href="#" onClick="newwin=window.open('./color_selector.php', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border=1 width="40" height="20"></a></span>
			</td>
		</tr>
		<tr class="basic" valign="top">
			<td>
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td valign="bottom">						
							<span id="startmmint"><span class="FormLabel">start date (mm/dd/yy)</span></span><br>
							<select name="StartMM_int" size="1">
							<OPTION VALUE="1" <?if(@date("m", $start_date) == 1){?>selected<?}?>>Jan
							<OPTION VALUE="2" <?if(@date("m", $start_date) == 2){?>selected<?}?>>Feb
							<OPTION VALUE="3" <?if(@date("m", $start_date) == 3){?>selected<?}?>>Mar
							<OPTION VALUE="4" <?if(@date("m", $start_date) == 4){?>selected<?}?>>Apr
							<OPTION VALUE="5" <?if(@date("m", $start_date) == 5){?>selected<?}?>>May
							<OPTION VALUE="6" <?if(@date("m", $start_date) == 6){?>selected<?}?>>Jun
							<OPTION VALUE="7" <?if(@date("m", $start_date) == 7){?>selected<?}?>>Jul
							<OPTION VALUE="8" <?if(@date("m", $start_date) == 8){?>selected<?}?>>Aug
							<OPTION VALUE="9" <?if(@date("m", $start_date) == 9){?>selected<?}?>>Sep
							<OPTION VALUE="10" <?if(@date("m", $start_date) == 10){?>selected<?}?>>Oct
							<OPTION VALUE="11" <?if(@date("m", $start_date) == 11){?>selected<?}?>>Nov
							<OPTION VALUE="12" <?if(@date("m", $start_date) == 12){?>selected<?}?>>Dec 
							</select>/
<select name="StartDD_int" size="1">
<OPTION VALUE="1" <?if(@date("d", $start_date) == 1){?>selected<?}?>>1 
<OPTION VALUE="2" <?if(@date("d", $start_date) == 2){?>selected<?}?>>2 
<OPTION VALUE="3" <?if(@date("d", $start_date) == 3){?>selected<?}?>>3 
<OPTION VALUE="4" <?if(@date("d", $start_date) == 4){?>selected<?}?>>4 
<OPTION VALUE="5" <?if(@date("d", $start_date) == 5){?>selected<?}?>>5 
<OPTION VALUE="6" <?if(@date("d", $start_date) == 6){?>selected<?}?>>6 
<OPTION VALUE="7" <?if(@date("d", $start_date) == 7){?>selected<?}?>>7 
<OPTION VALUE="8" <?if(@date("d", $start_date) == 8){?>selected<?}?>>8 
<OPTION VALUE="9" <?if(@date("d", $start_date) == 9){?>selected<?}?>>9 
<OPTION VALUE="10" <?if(@date("d", $start_date) == 10){?>selected<?}?>>10 
<OPTION VALUE="11" <?if(@date("d", $start_date) == 11){?>selected<?}?>>11 
<OPTION VALUE="12" <?if(@date("d", $start_date) == 12){?>selected<?}?>>12 
<OPTION VALUE="13" <?if(@date("d", $start_date) == 13){?>selected<?}?>>13 
<OPTION VALUE="14" <?if(@date("d", $start_date) == 14){?>selected<?}?>>14 
<OPTION VALUE="15" <?if(@date("d", $start_date) == 15){?>selected<?}?>>15 
<OPTION VALUE="16" <?if(@date("d", $start_date) == 16){?>selected<?}?>>16 
<OPTION VALUE="17" <?if(@date("d", $start_date) == 17){?>selected<?}?>>17 
<OPTION VALUE="18" <?if(@date("d", $start_date) == 18){?>selected<?}?>>18 
<OPTION VALUE="19" <?if(@date("d", $start_date) == 19){?>selected<?}?>>19 
<OPTION VALUE="20" <?if(@date("d", $start_date) == 20){?>selected<?}?>>20 
<OPTION VALUE="21" <?if(@date("d", $start_date) == 21){?>selected<?}?>>21 
<OPTION VALUE="22" <?if(@date("d", $start_date) == 22){?>selected<?}?>>22 
<OPTION VALUE="23" <?if(@date("d", $start_date) == 23){?>selected<?}?>>23 
<OPTION VALUE="24" <?if(@date("d", $start_date) == 24){?>selected<?}?>>24 
<OPTION VALUE="25" <?if(@date("d", $start_date) == 25){?>selected<?}?>>25 
<OPTION VALUE="26" <?if(@date("d", $start_date) == 26){?>selected<?}?>>26 
<OPTION VALUE="27" <?if(@date("d", $start_date) == 27){?>selected<?}?>>27 
<OPTION VALUE="28" <?if(@date("d", $start_date) == 28){?>selected<?}?>>28 
<OPTION VALUE="29" <?if(@date("d", $start_date) == 29){?>selected<?}?>>29 
<OPTION VALUE="30" <?if(@date("d", $start_date) == 30){?>selected<?}?>>30 
<OPTION VALUE="31" <?if(@date("d", $start_date) == 31){?>selected<?}?>>31 </select>/
<select name="StartYYYY_int" size="1"><OPTION VALUE="1999">1999 
<OPTION VALUE="2000" <?if(@date("Y", $start_date) == 2000){?>selected<?}?>>2000 
<OPTION VALUE="2001" <?if(@date("Y", $start_date) == 2001){?>selected<?}?>>2001 
<OPTION VALUE="2002" <?if(@date("Y", $start_date) == 2002){?>selected<?}?>>2002 
<OPTION VALUE="2003" <?if(@date("Y", $start_date) == 2003){?>selected<?}?>>2003 
<OPTION VALUE="2004" <?if(@date("Y", $start_date) == 2004){?>selected<?}?>>2004 
<OPTION VALUE="2005" <?if(@date("Y", $start_date) == 2005){?>selected<?}?>>2005 
<OPTION VALUE="2006" <?if(@date("Y", $start_date) == 2006){?>selected<?}?>>2006 
<OPTION VALUE="2007" <?if(@date("Y", $start_date) == 2007){?>selected<?}?>>2007 
<OPTION VALUE="2008" <?if(@date("Y", $start_date) == 2008){?>selected<?}?>>2008 
<OPTION VALUE="2009" <?if(@date("Y", $start_date) == 2009){?>selected<?}?>>2009 </select>
						</td><td valign="bottom">
							<a href="#" onClick="popCalendar('Start')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="popCalendar('Start')">pop calendar</A>
						</td>
					</tr>
				</table>
			</td>
			<td colspan="2">
				<TABLE width="100%" bgcolor="#eeeeee">
					<TR>
						<TD><span class="FormLabel">status</span> <span class="FormElementRequired">*</span></TD>
						<TD nowrap>Percent Complete</TD>
						<TD>Active?</TD>
					</TR>
					<TR>
						<TD>
						<select name="project_status">
						<option value="0" <?if($prow["project_status"] ==0){?>selected<?}?>>Not Defined
						<option value="1" <?if($prow["project_status"] ==1){?>selected<?}?>>Proposed
						<option value="2" <?if($prow["project_status"] ==2){?>selected<?}?>>In planning
						<option value="3" <?if($prow["project_status"] ==3){?>selected<?}?>>In progress
						<option value="4" <?if($prow["project_status"] ==4){?>selected<?}?>>On hold
						<option value="5" <?if($prow["project_status"] ==5){?>selected<?}?>>Complete
						</select> 
						</TD>
						<TD>	<b><?echo intval(@$prow["project_precent_complete"]);?> %</b>
					</TD>
					<TD>
					<input type=checkbox value=1 name=project_active <?if($prow["project_active"]){?>checked<?}?>>
					</TD>
				</TR>
			</TABLE>
			</td>
		</tr>
		<tr class="basic">
			<td valign="BOTTOM" height="20" align="Left"><span class="FormInstructionMedium">targets</span></td>
			<td valign="BOTTOM" height="20" align="Left" colspan="2"><span class="FormInstructionMedium">actuals</span></td>
		</tr>
		<tr class="basic">
			<td>
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td>
							<span id="targetmmint"><span class="FormLabel">target finish date (mm/dd/yy)</span></span><br>
<select name="TargetMM_int" size="1">
							<OPTION VALUE="1" <?if(@date("m", $end_date) == 1){?>selected<?}?>>Jan
							<OPTION VALUE="2" <?if(@date("m", $end_date) == 2){?>selected<?}?>>Feb
							<OPTION VALUE="3" <?if(@date("m", $end_date) == 3){?>selected<?}?>>Mar
							<OPTION VALUE="4" <?if(@date("m", $end_date) == 4){?>selected<?}?>>Apr
							<OPTION VALUE="5" <?if(@date("m", $end_date) == 5){?>selected<?}?>>May
							<OPTION VALUE="6" <?if(@date("m", $end_date) == 6){?>selected<?}?>>Jun
							<OPTION VALUE="7" <?if(@date("m", $end_date) == 7){?>selected<?}?>>Jul
							<OPTION VALUE="8" <?if(@date("m", $end_date) == 8){?>selected<?}?>>Aug
							<OPTION VALUE="9" <?if(@date("m", $end_date) == 9){?>selected<?}?>>Sep
							<OPTION VALUE="10" <?if(@date("m", $end_date) == 10){?>selected<?}?>>Oct
							<OPTION VALUE="11" <?if(@date("m", $end_date) == 11){?>selected<?}?>>Nov
							<OPTION VALUE="12" <?if(@date("m", $end_date) == 12){?>selected<?}?>>Dec 
</select>/
<select name="TargetDD_int" size="1">
<OPTION VALUE="1" <?if(@date("d", $end_date) == 1){?>selected<?}?>>1 
<OPTION VALUE="2" <?if(@date("d", $end_date) == 2){?>selected<?}?>>2 
<OPTION VALUE="3" <?if(@date("d", $end_date) == 3){?>selected<?}?>>3 
<OPTION VALUE="4" <?if(@date("d", $end_date) == 4){?>selected<?}?>>4 
<OPTION VALUE="5" <?if(@date("d", $end_date) == 5){?>selected<?}?>>5 
<OPTION VALUE="6" <?if(@date("d", $end_date) == 6){?>selected<?}?>>6 
<OPTION VALUE="7" <?if(@date("d", $end_date) == 7){?>selected<?}?>>7 
<OPTION VALUE="8" <?if(@date("d", $end_date) == 8){?>selected<?}?>>8 
<OPTION VALUE="9" <?if(@date("d", $end_date) == 9){?>selected<?}?>>9 
<OPTION VALUE="10" <?if(@date("d", $end_date) == 10){?>selected<?}?>>10 
<OPTION VALUE="11" <?if(@date("d", $end_date) == 11){?>selected<?}?>>11 
<OPTION VALUE="12" <?if(@date("d", $end_date) == 12){?>selected<?}?>>12 
<OPTION VALUE="13" <?if(@date("d", $end_date) == 13){?>selected<?}?>>13 
<OPTION VALUE="14" <?if(@date("d", $end_date) == 14){?>selected<?}?>>14 
<OPTION VALUE="15" <?if(@date("d", $end_date) == 15){?>selected<?}?>>15 
<OPTION VALUE="16" <?if(@date("d", $end_date) == 16){?>selected<?}?>>16 
<OPTION VALUE="17" <?if(@date("d", $end_date) == 17){?>selected<?}?>>17 
<OPTION VALUE="18" <?if(@date("d", $end_date) == 18){?>selected<?}?>>18 
<OPTION VALUE="19" <?if(@date("d", $end_date) == 19){?>selected<?}?>>19 
<OPTION VALUE="20" <?if(@date("d", $end_date) == 20){?>selected<?}?>>20 
<OPTION VALUE="21" <?if(@date("d", $end_date) == 21){?>selected<?}?>>21 
<OPTION VALUE="22" <?if(@date("d", $end_date) == 22){?>selected<?}?>>22 
<OPTION VALUE="23" <?if(@date("d", $end_date) == 23){?>selected<?}?>>23 
<OPTION VALUE="24" <?if(@date("d", $end_date) == 24){?>selected<?}?>>24 
<OPTION VALUE="25" <?if(@date("d", $end_date) == 25){?>selected<?}?>>25 
<OPTION VALUE="26" <?if(@date("d", $end_date) == 26){?>selected<?}?>>26 
<OPTION VALUE="27" <?if(@date("d", $end_date) == 27){?>selected<?}?>>27 
<OPTION VALUE="28" <?if(@date("d", $end_date) == 28){?>selected<?}?>>28 
<OPTION VALUE="29" <?if(@date("d", $end_date) == 29){?>selected<?}?>>29 
<OPTION VALUE="30" <?if(@date("d", $end_date) == 30){?>selected<?}?>>30 
<OPTION VALUE="31" <?if(@date("d", $end_date) == 31){?>selected<?}?>>31</select>/
<select name="TargetYYYY_int" size="1" onFocus="if(wizardForChangeText_Exists_int == 1){wizardFormChangeText('TargetYYYY_int');}"><OPTION VALUE="1999">1999 
<OPTION VALUE="2000" <?if(@date("Y", $end_date) == 2000){?>selected<?}?>>2000 
<OPTION VALUE="2001" <?if(@date("Y", $end_date) == 2001){?>selected<?}?>>2001 
<OPTION VALUE="2002" <?if(@date("Y", $end_date) == 2002){?>selected<?}?>>2002 
<OPTION VALUE="2003" <?if(@date("Y", $end_date) == 2003){?>selected<?}?>>2003 
<OPTION VALUE="2004" <?if(@date("Y", $end_date) == 2004){?>selected<?}?>>2004 
<OPTION VALUE="2005" <?if(@date("Y", $end_date) == 2005){?>selected<?}?>>2005 
<OPTION VALUE="2006" <?if(@date("Y", $end_date) == 2006){?>selected<?}?>>2006 
<OPTION VALUE="2007" <?if(@date("Y", $end_date) == 2007){?>selected<?}?>>2007 
<OPTION VALUE="2008" <?if(@date("Y", $end_date) == 2008){?>selected<?}?>>2008 
<OPTION VALUE="2009" <?if(@date("Y", $end_date) == 2009){?>selected<?}?>>2009 </select>
						</td><td valign="bottom">
							<a href="#" onClick="popCalendar('Target')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="popCalendar('Target')">pop calendar</A>
						</td>
					</tr>
				</table>
			</td>
			<td colspan="2">
				<table border="0" cellspacing="0" cellpadding="0">
					<tr>
						<td>
							<span id="wizactualfinish"><span class="FormLabel">actual finish date (mm/dd/yy)</span></span><br>
<select name="ActualMM_int" size="1">
<OPTION VALUE="" <?if($actual_end_date == 0){?>selected<?}?>>
	<OPTION VALUE="1" <?if(@date("m", $actual_end_date) == 1 && $actual_end_date != 0){?>selected<?}?>>Jan
	<OPTION VALUE="2" <?if(@date("m", $actual_end_date) == 2 && $actual_end_date != 0){?>selected<?}?>>Feb
	<OPTION VALUE="3" <?if(@date("m", $actual_end_date) == 3 && $actual_end_date != 0){?>selected<?}?>>Mar
	<OPTION VALUE="4" <?if(@date("m", $actual_end_date) == 4 && $actual_end_date != 0){?>selected<?}?>>Apr
	<OPTION VALUE="5" <?if(@date("m", $actual_end_date) == 5 && $actual_end_date != 0){?>selected<?}?>>May
	<OPTION VALUE="6" <?if(@date("m", $actual_end_date) == 6 && $actual_end_date != 0){?>selected<?}?>>Jun
	<OPTION VALUE="7" <?if(@date("m", $actual_end_date) == 7 && $actual_end_date != 0){?>selected<?}?>>Jul
	<OPTION VALUE="8" <?if(@date("m", $actual_end_date) == 8 && $actual_end_date != 0){?>selected<?}?>>Aug
	<OPTION VALUE="9" <?if(@date("m", $actual_end_date) == 9 && $actual_end_date != 0){?>selected<?}?>>Sep
	<OPTION VALUE="10" <?if(@date("m", $actual_end_date) == 10 && $actual_end_date != 0){?>selected<?}?>>Oct
	<OPTION VALUE="11" <?if(@date("m", $actual_end_date) == 11 && $actual_end_date != 0){?>selected<?}?>>Nov
	<OPTION VALUE="12" <?if(@date("m", $actual_end_date) == 12 && $actual_end_date != 0){?>selected<?}?>>Dec 
</select>/
<select name="ActualDD_int" size="1">
<?if($actual_end_date ==0){?>
	<OPTION VALUE="">
	<?}else{?>
	<OPTION VALUE="<?echo @date("d", $actual_end_date);?>"><?echo @date("d", $actual_end_date);?>
<?}?>
<OPTION VALUE="1">1 
<OPTION VALUE="2">2 
<OPTION VALUE="3">3 
<OPTION VALUE="4">4 
<OPTION VALUE="5">5 
<OPTION VALUE="6">6 
<OPTION VALUE="7">7 
<OPTION VALUE="8">8 
<OPTION VALUE="9">9 
<OPTION VALUE="10">10 
<OPTION VALUE="11">11 
<OPTION VALUE="12">12 
<OPTION VALUE="13">13 
<OPTION VALUE="14">14 
<OPTION VALUE="15">15 
<OPTION VALUE="16">16 
<OPTION VALUE="17">17 
<OPTION VALUE="18">18 
<OPTION VALUE="19">19 
<OPTION VALUE="20">20 
<OPTION VALUE="21">21 
<OPTION VALUE="22">22 
<OPTION VALUE="23">23 
<OPTION VALUE="24">24 
<OPTION VALUE="25">25 
<OPTION VALUE="26">26 
<OPTION VALUE="27">27 
<OPTION VALUE="28">28 
<OPTION VALUE="29">29 
<OPTION VALUE="30">30 
<OPTION VALUE="31">31 </select>/
<select name="ActualYYYY_int" size="1">
<?if($actual_end_date ==0){?>
<OPTION VALUE="">
	<?}else{?>
	<OPTION VALUE="<?echo @date("Y", $actual_end_date);?>"><?echo @date("Y", $actual_end_date);?>
<?}?>
<OPTION VALUE="2000">2000 
<OPTION VALUE="2001">2001 
<OPTION VALUE="2002">2002 
<OPTION VALUE="2003">2003 
<OPTION VALUE="2004">2004 
<OPTION VALUE="2005">2005 
<OPTION VALUE="2006">2006 
<OPTION VALUE="2007">2007 
<OPTION VALUE="2008">2008 
<OPTION VALUE="2009">2009 </select>
						</td><td valign="bottom">
							<a href="#" onClick="popCalendar('Actual')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> 
							<a href="#" onClick="popCalendar('Actual')">pop calendar</A>
						</td>
					</tr>
				</table>
			</td>
		</tr>
		<tr class="basic">
			<td valign="middle">
				<span id="targetbudgetnum"><span class="FormLabel">target budget</span></span><br>
				<span class="FormLabel">$</span><input type="Text" name="project_target_budget" value="<?echo @$prow["project_target_budget"];?>" size="10" maxlength="10">
			</td>
			<td valign="middle" colspan="2">
				<span id="wizactualbudget"><span class="FormLabel">actual budget</span></span><br>
				<span class="FormLabel">$</span><input type="Text" name="project_actual_budget" value="<?echo @$prow["project_actual_budget"];?>" size="10" maxlength="10"">
			</td>
		</tr>
		<tr class="basic">
			<td  colspan="1">
				<span id="fulldesctext"><span class="formlabel">full description</span></span><br>
				<textarea name="project_description" cols="38" rows="5" wrap="virtual"><?echo @$prow["project_description"];?></textarea>
			</td>
		<TD colspan=2 valign="top">Project owner<br>
		<select name="project_owner" style="width:200px;">
		
		<?while($row = mysql_fetch_array($urc)){?>
		<option value="<?echo $row["user_id"];?>" <?if($prow["project_owner"] == $row["user_id"]){echo "selected";}?>><?echo $row["user_first_name"];?> <?echo $row["user_last_name"];?> 
		<?}?>
		</select><br>
		URL<br>		
		<input type="Text" name="project_url" value="<?echo @$prow["project_url"];?>" size="50" maxlength="255""><br>
		Staging URL<br>
		<input type="Text" name="project_demo_url" value="<?echo @$prow["project_demo_url"];?>" size="50" maxlength="255"">

		
		
		
		</td>
		</tr>
		</table>
	<table border="0" cellspacing="0" cellpadding="3" width="99%">
		<tr class="basic">
			<td height="40" width="35%">
				<span class="FormElementRequired">*</span> <span class="FormInstruction">indicates required field</span>
			</td>
			<td height="40" width="30%">
					&nbsp;
			</td>
			<td  height="40" width="35%" align="right">
				<table><tr>
<td><input class=button type="Button" name="Cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}"></td>
<td><input class=button type="Button" name="btnFuseAction" value="save" onClick="submitIt();"></td>

				</tr></table>
			</td>
		</tr>
	</table>
	</center>
</form>
<?}?>
