<?php
$project_id = defValArr ( $_GET, "project_id", 0 );

// check permissions
$denyEdit = getDenyEdit( $m, $project_id );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

// pull companies
$sql = "SELECT company_id, company_name FROM companies ORDER BY company_name";
$companies = arrayMerge( array( 0 => '' ), db_loadHashList( $sql ) );

/*
if (count( $companies ) < 0) {
	$AppUI->setMsg( 'noCompanies', ID_MSG_ALERT );
	$AppUI->redirect( 'm=companies' );
}
*/

// pull users
$sql = "SELECT user_id, CONCAT( user_last_name, ', ', user_first_name) FROM users ORDER BY user_last_name";
$users = db_loadHashList( $sql );

// pull the project
$sql = "SELECT * FROM projects WHERE project_id = $project_id";
db_loadHash( $sql, $project );

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = $project["project_start_date"] ? CDate::fromDateTime( $project["project_start_date"] ) : new CDate();
$start_date->setFormat( $df );

if ($project["project_end_date"]) {
	$end_date = CDate::fromDateTime( $project["project_end_date"] );
	$end_date->setFormat( $df );
} else {
	$end_date = null;
}

if ($project["project_actual_end_date"]) {
	$actual_end_date = CDate::fromDateTime( $project["project_actual_end_date"] );
	$actual_end_date->setFormat( $df );
} else {
	$actual_end_date = null;
}

$crumbs = array();
$crumbs["?m=projects"] = "projects list";
$crumbs["?m=projects&a=view&project_id=$project_id"] = "view this project";
?>
<script language="javascript">
function setColor() {
	color = document.AddEdit.project_color_identifier.value;
	test.style.background = color;
}

var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	uts = eval( 'document.AddEdit.project_' + field + '.value' );
	window.open( './calendar.php?callback=setCalendar&uts=' + uts, 'calwin', 'top=250,left=250,width=250, height=220, scollbars=false' );
}

function setCalendar( uts, fdate ) {
	fld_uts = eval( 'document.AddEdit.project_' + calendarField );
	fld_fdate = eval( 'document.AddEdit.' + calendarField );
	fld_uts.value = uts;
	fld_fdate.value = fdate;
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
		alert("<?php echo $AppUI->_('projectsValidName');?>");
		form.project_name.focus();
	} else if (form.project_color_identifier.value.length < 3) {
		alert( "<?php echo $AppUI->_('projectsColor');?>");
		form.project_color_identifier.focus();
	} else {
		form.submit();
	}
}

function delIt() {
	if (confirm( "<?php echo $AppUI->_('projectsDelete');?>" )) {
		var form = document.AddEdit;
		form.del.value=1;
		form.submit();
	}
}
</script>

<table cellspacing="0" cellpadding="0" border="0" width="98%">
<form name="AddEdit" action="./index.php?m=projects&a=dosql" method="post">
<input type="hidden" name="del" value="0" />
<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
<input type="hidden" name="project_creator" value="<?php echo $AppUI->user_id;?>" />

<tr>
	<td><img src="./images/icons/projects.gif" alt="" border="0" /></td>
	<td nowrap>
		<h1><?php echo $AppUI->_(($project_id > 0) ? "Edit Project" : "New Project" ); ?></h1>
	</td>
	<td align="right" width="100%">&nbsp;</td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', 'ID_HELP_PROJ_EDIT' );?></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" nowrap><?php echo breadCrumbs( $crumbs );?></td>
	<td width="50%" align="right">
		<a href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="" border="0" /><?php echo $AppUI->_('delete project');?></a>
	</td>
</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="98%" class="std">
<tr>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Name');?></td>
			<td width="100%">
				<input type="text" name="project_name" value="<?php echo @$project["project_name"];?>" size="25" maxlength="50" onBlur="setShort();" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Project Owner');?></td>
			<td>
<?php echo arraySelect( $users, 'project_owner', 'size="1" style="width:200px;" class="text"', defValArr( $project, "project_owner", $AppUI->user_id ) ) ?>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Company');?></td>
			<td width="100%" nowrap="nowrap">
<?php
	echo arraySelect( $companies, 'project_company', 'class="text" size="1"', $project["project_company"] );
?> *</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Start Date');?></td>
			<td>
				<input type="hidden" name="project_start_date" value="<?php echo $start_date->getTimestamp();?>" />
				<input type="text" name="start_date" value="<?php echo $start_date->toString();?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('start_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Finish Date');?></td>
			<td>
				<input type="hidden" name="project_end_date" value="<?php echo $end_date ? $end_date->getTimestamp() : '-1';?>" />
				<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->toString() : '';?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('end_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Target Budget');?> $</td>
			<td>
				<input type="Text" name="project_target_budget" value="<?php echo @$project["project_target_budget"];?>" maxlength="10" class="text" />
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Finish Date');?></td>
			<td>
				<input type="hidden" name="project_actual_end_date" value="<?php echo $actual_end_date ? $actual_end_date->getTimestamp() : '-1';?>" />
				<input type="text" name="actual_end_date" value="<?php echo $actual_end_date ? $actual_end_date->toString() : '';?>" class="text" disabled="disabled" />
				<a href="#" onClick="popCalendar('project_actual_end_date','actual_end_date')">
					<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
				</a>
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Actual Budget');?> $</td>
			<td>
				<input type="text" name="project_actual_budget" value="<?php echo @$project["project_actual_budget"];?>" size="10" maxlength="10" class="text"/>
			</td>
		</tr>
		<tr>
			<td colspan="2"><hr noshade="noshade" size="1"></td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('URL');?></td>
			<td>
				<input type="text" name="project_url" value="<?php echo @$project["project_url"];?>" size="40" maxlength="255" class="text" />
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Staging URL');?></td>
			<td>
				<input type="Text" name="project_demo_url" value="<?php echo @$project["project_demo_url"];?>" size="40" maxlength="255" class="text" />
			</td>
		</tr>
		</table>
	</td>
	<td width="50%" valign="top">
		<table cellspacing="0" cellpadding="2" border="0">
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Short Name');?></td>
			<td colspan="3">
				<input type="text" name="project_short_name" value="<?php echo @$project["project_short_name"];?>" size="10" maxlength="10" class="text" /> *
			</td>
		</tr>
		<tr>
			<td align="right" nowrap="nowrap"><?php echo $AppUI->_('Color Identifier');?></td>
			<td nowrap="nowrap">
				<input type="text" name="project_color_identifier" value="<?php echo @$project["project_color_identifier"];?>" size="10" maxlength="6" onBlur="setColor();" class="text" /> *
			</td>
			<td nowrap="nowrap">
				<a href="#" onClick="newwin=window.open('./color_selector.php', 'calwin', 'width=320, height=300, scollbars=false');"><?php echo $AppUI->_('change color');?></a>
			</td>
			<td nowrap="nowrap">
				<span id="test" title="test" style="background:#<?php echo @$project["project_color_identifier"];?>;"><a href="#" onClick="newwin=window.open('./color_selector.php', 'calwin', 'width=320, height=300, scollbars=false');"><img src="./images/shim.gif" border="1" width="40" height="20" /></a></span>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<table width="100%" bgcolor="#cccccc">
				<tr>
					<td><?php echo $AppUI->_('Status');?> *</td>
					<td nowrap="nowrap"><?php echo $AppUI->_('Progress');?></td>
					<td><?php echo $AppUI->_('Active');?>?</td>
				</tr>
				<tr>
					<td>
						<?php echo arraySelect( $pstatus, 'project_status', 'size="1" class="text"', $project["project_status"], true ); ?> 
					</td>
					<td><strong><?php echo intval(@$project["project_precent_complete"]);?> %</strong></td>
					<?php 
					/* CHANGE so default for ADDING/EDITTING Projects is ACTIVE 
					// ORIGINAL CODE [modified by kobudo 14 Feb 2003]
					<td><input type=checkbox value=1 name=project_active <?php if($project["project_active"]){?>checked<?php }?>></td>
					*/
					?>
					<td><input type=checkbox value=1 name=project_active checked /></td>
				</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td colspan="4">
				<?php echo $AppUI->_('Description');?><br />
				<textarea name="project_description" cols="50" rows="10" wrap="virtual" class="textarea"><?php echo @$project["project_description"];?></textarea>
			</td>
		</tr>
		</table>
	</td>
</tr>
<tr>
	<td>
		<input class=button type="button" name="<?php echo $AppUI->_('cancel');?>" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = './index.php?m=projects';}" />
	</td>
	<td align="right" colspan="2">
		<input class=button type="Button" name="btnFuseAction" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt();" />
	</td>
</tr>
</form>
</table>
* <?php echo $AppUI->_('requiredField');?>
