<?php
$project_id = isset($HTTP_GET_VARS['project_id']) ? $HTTP_GET_VARS['project_id'] : 0;

// check permissions
$denyEdit = getDenyEdit( $m, $project_id );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// pull users;
$usql = "select user_first_name, user_last_name, user_id from users order by user_last_name";
$urc = mysql_query( $usql );

// Pull companies
$csql = "select company_name, company_id from companies order by company_name";
$crc = mysql_query( $csql );
$cexists = mysql_num_rows( $crc );

//pull projects
$psql = "SELECT * FROM projects WHERE project_id = $project_id";
$prc = mysql_query( $psql );
echo mysql_error();
$prow = mysql_fetch_array( $prc, MYSQL_ASSOC );

if (strlen( $prow["project_start_date"] ) == 0) {
	$start_date = date( time() );
} else {
	$start_date = mktime( 0, 0, 0, substr($prow["project_start_date"],5,2),
		substr($prow["project_start_date"],8,2), 
		substr($prow["project_start_date"],0,4)
	);
}

if (strlen( $prow["project_end_date"] ) == 0) {
	$end_date = date(time()+(3600*24));
} else {
	$end_date = mktime( 0, 0, 0, substr($prow["project_end_date"],5,2),
		substr($prow["project_end_date"],8,2),
		substr($prow["project_end_date"],0,4) 
	);
	//$end_date = $prow["project_end_date"];
}

if (strlen( $prow["project_actual_end_date"] ) ==0) {
	$actual_end_date = 0;
} else {
	$actual_end_date = mktime( 0, 0, 0, substr($prow["project_actual_end_date"],5,2),
		substr($prow["project_actual_end_date"],8,2),
		substr($prow["project_actual_end_date"],0,4) );
}

// some constants
$days = array('',1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31);
$months = array( '','Jan','Feb','Mar','Apr','Mar','Jun','Jul','Aug','Sep','Oct','Nov','Dec' );
$years = array('',2000=>2000,2001,2002,2003,2004,2005,2006,2007,2008,2009);
?>
<SCRIPT language="javascript">
function setColor() {
	color = document.AddEdit.project_color_identifier.value;
	test.style.background = color;
}

function popCalendar(x){
	var form = document.AddEdit;
	mm = eval( "document.AddEdit." + x + "MM_int.value" );
	dd = eval( "document.AddEdit." + x + "DD_int.value" );
	yy = eval( "document.AddEdit." + x + "YYYY_int.value" );

	newwin = window.open( './calendar.php?form=AddEdit&field=' + x + '&thisYear=' + yy + '&thisMonth=' + mm + '&thisDay=' + dd, 'calwin', 'width=250, height=220, scollbars=false' );
}

function setShort() {
	var form = document.AddEdit;
	var x = 10;
	if (form.project_name.value.length < 11) {
		x = form.project_name.value.length;
	}
	if (form.project_short_name.value.length == 0) {
		form.project_short_name.value = form.project_name.value.substr(0,x);
	}
}

function submitIt() {
	var form = document.AddEdit;

	if (form.project_name.value.length < 3) {
		alert( "Please enter a valid Project Name" );
		form.project_name.focus();
	} else if (form.project_color_identifier.value.length < 3) {
		alert( "Please select a color to identify this project" );
		form.project_color_identifier.focus();
	} else {
		form.submit();
	}
}

function delIt() {
	if (confirm( "Are you sure that you would like to delete this project?\n" )) {
		var form = document.AddEdit;
		form.del.value=1;
		form.submit();
	}
}

</script>
<?php
if (!$cexists) {
	echo "You add a company or client before creating a new project<br>";
	echo "<a href=./index.php?m=companies&a=addedit>Click here to add a new Company/Client</A>";
} else {
?>
<TABLE width="98%" border=0 cellpadding="0" cellspacing=1>
<form name="AddEdit" action="./index.php?m=projects&a=dosql" method="post">
<input type="hidden" name="del" value="0">
<input type="hidden" name="project_id" value="<?php echo $project_id;?>">

<TR>
<TD><img src="./images/icons/projects.gif" alt="" border="0"></td>
	<TD nowrap>
		<span class="title">
		<?php echo (($project_id > 0) ? "Edit Existing" : "Create New" ) . " Project"; ?>
		</span>
	</td>
	<TD align="right" width="100%">&nbsp;</td>
</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
	<TR>
		<TD width="50%" nowrap>
		<a href="./index.php?m=projects">Projects List</a>
		<b>:</b> <a href="./index.php?m=projects&a=view&project_id=<?php echo $project_id;?>">View this Project</a>
		</td>
		<TD width="50%" align="right">
			<A href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="Delete this project" border="0">delete project</a>
		</td>
	</TR>
</table>

<table border="0" cellpadding="0" cellspacing="0" width="98%" bgcolor="silver">
<tr>
	<td nowrap class="tabBG" valign="top">
		<img height=4 src="./images/shim.gif" width="1" align="top"><br>
		<table border="0" cellpadding="4" cellspacing="0" width="100%">
		<tr>
			<td class="allFormsTitleHeader" valign="middle">
				<img src="./images/icons/icn_project.gif" width="16" height="16" alt="" border="0">
				<b>
				<?php echo ($project_id > 0) ? "Edit the project using the form below" : "To create a new project complete the form below"; ?>
				</b>
			</td>
			<td align="right" class="viewheader" valign="top">&nbsp;</td>
		</tr>
		</table>
	</td>
</tr>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="98%" bgcolor="dddddd">
<tr class="basic" valign="top">
	<td>
		<table>
		<tr>
			<td valign="bottom">
				<span id="ccsprojectnamestr"><span class="FormLabel">project name</span>
				<span class="FormElementRequired">*</span></span>
				<br><input type="text" name="project_name" value="<?php echo @$prow["project_name"];?>" size="25" maxlength="50" onBlur="setShort();">
			</td>
			<td>&nbsp;</td>
			<td valign="bottom">
				<span id="ccsprojectnamestr"><span class="FormLabel">company name</span>
				<span class="FormElementRequired">*</span></span>
				<br><select name="project_company" style="width:200px;">
			<?php
				while ($row = mysql_fetch_array( $crc, MYSQL_ASSOC )) {
					echo '<option value="' . $row["company_id"] . '"'
						. (($row["company_id"] == $prow["project_company"]) ? ' selected' : '')
						. '>' . $row["company_name"];
				}
			?>
				</select>
			</td>
		</tr>
		</table>
	</td>
	<td>
		<span id="shortnamestr"><span class="FormLabel">short name</span>
		<span class="FormElementRequired">*</span></span>
		<br><input type="text" name="project_short_name" value="<?php echo @$prow["project_short_name"];?>" size="10" maxlength="10">
	</td>
	<td>
		<span id="tempcolorstr"><span class="FormLabel">color identifier</span>
		<span class="FormElementRequired">*</span>&nbsp;&nbsp;<a href="#" onClick="newwin=window.open('./color_selector.php', 'calwin', 'width=320, height=300, scollbars=false');">change color</a></span>
		<br><input type="text" name="project_color_identifier" value="<?php echo @$prow["project_color_identifier"];?>" size="6" onBlur="setColor();"> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
		<span id="test" title="test" style="background:#<?php echo @$prow["project_color_identifier"];?>;"><a href="#" onClick="newwin=window.open('./color_selector.php', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border=1 width="40" height="20"></a></span>
	</td>
</tr>
<tr class="basic" valign="top">
	<td>
		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td valign="bottom">
				<span id="startmmint"><span class="FormLabel">start date (mm/dd/yy)</span></span><br>
				<?php echo arraySelect( $months, 'StartMM_int', 'size=1', @date("m", $start_date) ); ?> /
				<?php echo arraySelect( $days, 'StartDD_int', 'size=1', @date("d", $start_date) ); ?> /
				<?php echo arraySelect( $years, 'StartYYYY_int', 'size=1', @date("Y", $start_date) ); ?>
			</td>
			<td valign="bottom">
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
				<?php echo arraySelect( $pstatus, 'project_status', 'size=1', $prow["project_status"] ); ?> 
			</TD>
			<TD><b><?php echo intval(@$prow["project_precent_complete"]);?> %</b></TD>
			<TD><input type=checkbox value=1 name=project_active <?php if($prow["project_active"]){?>checked<?php }?>></TD>
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
				<?php echo arraySelect( $months, 'TargetMM_int', 'size=1', @date("m", $end_date) ); ?> /
				<?php echo arraySelect( $days, 'TargetDD_int', 'size=1', @date("d", $end_date) ); ?> /
				<?php echo arraySelect( $years, 'TargetYYYY_int', 'size=1', @date("Y", $end_date) ); ?>
			</td>
			<td valign="bottom">
				<a href="#" onClick="popCalendar('Target')"><img src="./images/calendar.gif" width="24" height="12" alt="" border="0"></a> <a href="#" onClick="popCalendar('Target')">pop calendar</A>
			</td>
		</tr>
		</table>
	</td>
	<td colspan="2">
		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>
				<span class="FormLabel">actual finish date (mm/dd/yy)</span><br>
				<?php echo arraySelect( $months, 'ActualMM_int', 'size=1', @date("m", $actual_end_date) ); ?> /
				<?php echo arraySelect( $days, 'ActualDD_int', 'size=1', @date("d", $actual_end_date) ); ?> /
				<?php echo arraySelect( $years, 'ActualYYYY_int', 'size=1', @date("Y", $actual_end_date) ); ?>
			</td>
			<td valign="bottom">
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
		<span class="FormLabel">$</span><input type="Text" name="project_target_budget" value="<?php echo @$prow["project_target_budget"];?>" size="10" maxlength="10">
	</td>
	<td valign="middle" colspan="2">
		<span class="FormLabel">actual budget</span><br>
		<span class="FormLabel">$</span><input type="Text" name="project_actual_budget" value="<?php echo @$prow["project_actual_budget"];?>" size="10" maxlength="10"">
	</td>
</tr>
<tr class="basic">
	<td  colspan="1">
		<span id="fulldesctext"><span class="formlabel">full description</span></span><br>
		<textarea name="project_description" cols="38" rows="5" wrap="virtual"><?php echo @$prow["project_description"];?></textarea>
	</td>
	<TD colspan=2 valign="top">
		Project owner<br>
		<select name="project_owner" style="width:200px;">
	<?php
		while ($row = mysql_fetch_array( $urc, MYSQL_ASSOC )) { ?>
		<option value="<?php echo $row["user_id"];?>" <?php if($prow["project_owner"] == $row["user_id"]){echo "selected";}?>><?php echo $row["user_first_name"];?> <?php echo $row["user_last_name"];?>
	<?php }?>
		</select>
		<br>URL
		<br><input type="Text" name="project_url" value="<?php echo @$prow["project_url"];?>" size="50" maxlength="255"">
		<br>Staging URL
		<br><input type="Text" name="project_demo_url" value="<?php echo @$prow["project_demo_url"];?>" size="50" maxlength="255"">
	</td>
</tr>
</table>

<table border="0" cellspacing="0" cellpadding="3" width="98%">
<tr class="basic">
	<td height="40" width="35%">
		<span class="FormElementRequired">*</span> <span class="FormInstruction">indicates required field</span>
	</td>
	<td height="40" width="30%">&nbsp;</td>
	<td  height="40" width="35%" align="right">
		<table>
		<tr>
			<td>
				<input class=button type="Button" name="Cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}">
			</td>
			<td><input class=button type="Button" name="btnFuseAction" value="save" onClick="submitIt();"></td>
		</tr>
		</table>
	</td>
</tr>
</table>
</center>
</form>
<?php }?>
