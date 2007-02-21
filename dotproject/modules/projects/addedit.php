<?php /* PROJECTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

$project_id = intval( dPgetParam( $_GET, 'project_id', 0 ) );
$company_id = intval( dPgetParam( $_GET, 'company_id', 0 ) );
$contact_id = intval( dPgetParam( $_GET, 'contact_id', 0 ) );

$perms =& $AppUI->acl();
// check permissions for this record
$canEdit = $perms->checkModuleItem( $m, 'edit', $project_id );
$canAuthor = $perms->checkModuleItem( $m, 'add' );
if ((!$canEdit && $project_id > 0) || (!$canAuthor && $project_id == 0))
	$AppUI->redirect( 'm=public&a=access_denied' );

// get a list of permitted companies
require_once( $AppUI->getModuleClass ('companies' ) );

$row = new CCompany();
$companies = $row->getEdittableRecords( $AppUI->user_id, 'company_id,company_name', 'company_name' );
$companies = arrayMerge( array( '0'=>'&nbsp;' ), $companies );

// pull users
$q  = new DBQuery;
$q->addTable('users','u');
$q->addTable('contacts','con');
$q->addQuery('user_id');
$contact_full_name = $q->concat('contact_last_name', "', '" , 'contact_first_name');
$q->addQuery($contact_full_name);
$q->addOrder('contact_last_name');
$q->addWhere('u.user_contact = con.contact_id');
$users = $q->loadHashList();

// load the record data
$row = new CProject();

if (!$row->load( $project_id, false ) && $project_id > 0) {
$AppUI->setMsg( 'Project' );
$AppUI->setMsg( 'invalidID', UI_MSG_ERROR, true );
$AppUI->redirect();
} else if (count( $companies ) < 2 && $project_id == 0) {
$AppUI->setMsg( 'noCompanies', UI_MSG_ERROR, true );
$AppUI->redirect();
}

if ($project_id == 0 && $company_id > 0) {
	$row->project_company = $company_id;
}

// add in the existing company if for some reason it is dis-allowed
if ($project_id && !array_key_exists( $row->project_company, $companies )) {
	$q  = new DBQuery;
	$q->addTable('companies');
	$q->addQuery('company_name');
	$q->addWhere('companies.company_id = '.$row->project_company);
	$sql = $q->prepare();
	$q->clear();
	$companies[$row->project_company] = db_loadResult($sql);
}

// get critical tasks (criteria: task_end_date)
$criticalTasks = ($project_id > 0) ? $row->getCriticalTasks() : NULL;

// get ProjectPriority from sysvals
$projectPriority = dPgetSysVal( 'ProjectPriority' );

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

$start_date = new CDate( $row->project_start_date );

$end_date = intval( $row->project_end_date ) ? new CDate( $row->project_end_date ) : null;
$actual_end_date = intval( $criticalTasks[0]['task_end_date'] ) ? new CDate( $criticalTasks[0]['task_end_date'] ) : null;
$style = (( $actual_end_date > $end_date) && !empty($end_date)) ? 'style="color:red; font-weight:bold"' : '';

// setup the title block
$ttl = $project_id > 0 ? 'Edit Project' : 'New Project';
$titleBlock = new CTitleBlock( $ttl, 'applet3-48.png', $m, "$m.$a" );
$titleBlock->addCrumb( '?m=projects', 'projects list' );
if ($project_id != 0)
$titleBlock->addCrumb( '?m=projects&amp;a=view&amp;project_id='.$project_id, 'view this project' );
$titleBlock->show();

//Build display list for departments
$company_id = $row->project_company;
$selected_departments = array();
if ($project_id) {
	$q =& new DBQuery;
	$q->addTable('project_departments');
	$q->addQuery('department_id');
	$q->addWhere('project_id = ' . $project_id);
	$res =& $q->exec();
	for ( $res; ! $res->EOF; $res->MoveNext())
		$selected_departments[] = $res->fields['department_id'];
	$q->clear();
}
$departments_count = 0;
$department_selection_list = getDepartmentSelectionList($company_id, $selected_departments);
if($department_selection_list != ''){
  $department_selection_list = ($AppUI->_('Departments').'<br />'."\n"
								.'<select name="dept_ids[]"  class="text">'."\n"
								.'<option value="0"></option>'."\n"
								.$department_selection_list."\n"
								.'</select>');
} else {
  $department_selection_list = '<input type="button" class="button" value="'.$AppUI->_('Select department...').'" onclick="javascript:popDepartment();" /><input type="hidden" name="project_departments" />';
}

// Get contacts list
$selected_contacts = array();
if ($project_id) {
	$q =& new DBQuery;
	$q->addTable('project_contacts');
	$q->addQuery('contact_id');
	$q->addWhere('project_id = ' . $project_id);
	$res =& $q->exec();
	for ( $res; ! $res->EOF; $res->MoveNext())
		$selected_contacts[] = $res->fields['contact_id'];
	$q->clear();
}
if ($project_id == 0 && $contact_id > 0){
	$selected_contacts[] = "$contact_id";
}
?>
<!-- import the calendar script -->
<script type="text/javascript" src="<?php echo DP_BASE_URL;?>/lib/calendar/calendar.js"></script>
<!-- import the language module -->
<script type="text/javascript" src="<?php echo DP_BASE_URL;?>/lib/calendar/lang/calendar-<?php echo $AppUI->user_locale; ?>.js"></script>

<script type="text/javascript" language="javascript">
<!--
function setColor(color) {
var f = document.editFrm;
if (color) {
	f.project_color_identifier.value = color;
}
//test.style.background = f.project_color_identifier.value;
document.getElementById('test').style.background = '#' + f.project_color_identifier.value; 		//fix for mozilla: does this work with ie? opera ok.
}

function setShort() {
var f = document.editFrm;
var x = 10;
if (f.project_name.value.length < 11) {
	x = f.project_name.value.length;
}
if (f.project_short_name.value.length == 0) {
	f.project_short_name.value = f.project_name.value.substr(0,x);
}
}

var calendarField = '';
var calWin = null;

function popCalendar( field ){
calendarField = field;
idate = eval( 'document.editFrm.project_' + field + '.value' );
window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=280, height=250, scrollbars=no' );
}

/**
*	@param string Input date in the format YYYYMMDD
*	@param string Formatted date
*/
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.project_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;

	// set end date automatically with start date if start date is after end date
	if (calendarField == 'start_date') {
		if( document.editFrm.end_date.value < idate) {
			document.editFrm.project_end_date.value = idate;
			document.editFrm.end_date.value = fdate;
		}
	}
}

function submitIt() {
	var f = document.editFrm;
	var msg = '';

	<?php 
	/*
	** Automatic required fields generated from System Values
	*/
	$requiredFields = dPgetSysVal( 'ProjectRequiredFields' );
	echo dPrequiredFields($requiredFields);
	?>

	/*
	if (f.project_end_date.value > 0 && f.project_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate1');?>";
	}
	if (f.project_actual_end_date.value > 0 && f.project_actual_end_date.value < f.project_start_date.value) {
		msg += "\n<?php echo $AppUI->_('projectsBadEndDate2');?>";
	}
	*/
	if (msg.length < 1) {
		f.submit();
	} else {
		alert(msg);
	}
}

var selected_contacts_id = "<?php echo implode(',', $selected_contacts); ?>";

function popContacts() {
	window.open('./index.php?m=public&amp;a=contact_selector&amp;dialog=1&amp;call_back=setContacts&amp;selected_contacts_id='+selected_contacts_id, 'contacts','height=600,width=400,resizable,scrollbars=yes');
}

function setContacts(contact_id_string){
	if(!contact_id_string){
		contact_id_string = "";
	}
	document.editFrm.project_contacts.value = contact_id_string;
	selected_contacts_id = contact_id_string;
}

var selected_departments_id = "<?php echo implode(',', $selected_departments); ?>";

function popDepartment() {
	var f = document.editFrm;
	var url = './index.php?m=public&amp;a=selector&amp;dialog=1&amp;callback=setDepartment&amp;table=departments&amp;company_id='
            + f.project_company.options[f.project_company.selectedIndex].value
            + '&amp;dept_id='
            + selected_departments_id;
        window.open(url,'dept','left=50,top=50,height=250,width=400,resizable');
}

function setDepartment(department_id_string){
	if(!department_id_string){
		department_id_string = "";
	}
	document.editFrm.project_departments.value = department_id_string;
	selected_departments_id = department_id_string;
}
-->
</script>

<?php
// Template projects related processing.
	$objProject = new CProject();
	$allowedProjects = $objProject->getAllowedRecords( $AppUI->user_id, 'project_id,project_name', 'project_name' );
	
	$q  = new DBQuery;
	$q->addTable('projects', 'p');
	$q->addTable('tasks', 't');
	$q->addQuery('p.project_id, p.project_name');
	$q->addWhere('t.task_project = p.project_id');
	if ( count($allowedProjects) > 0 ) {
		$q->addWhere('(p.project_id IN (' .
		implode (',', array_keys($allowedProjects)) . '))');
	}
	$q->addOrder('p.project_name');
		
	$importList = $q->loadHashList ();
	$importList = arrayMerge( array( '0'=> $AppUI->_('none') ), $importList);


/**************** Display *********************/
	require_once(DP_BASE_DIR . '/classes/CustomFields.class.php');
	$custom_fields = New CustomFields( $m, $a, $row->project_id, 'edit' );
	
	$tpl->assign('custom_fields', $custom_fields->getHTML());
		
	$tpl->assign('importList', $importList);
	$tpl->assign('companies', $companies);
	$tpl->assign('projectPriority', $projectPriority);
	$tpl->assign('users', $users);
	$tpl->assign('ptype', $ptype);
	$tpl->assign('pstatus', $pstatus);
	
	$row->project_owner = $row->project_owner ? $row->project_owner : $AppUI->user_id;
	
	// TODO: is the check for contacts being an active module necessary?!? 
	// isn't it always active?
	$viewContacts = ( $AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view'));
	$tpl->assign('viewContacts', $viewContacts);
	
	$tpl->assign('actual_end_date', $actual_end_date);
	$tpl->assign('critical_task_id', $criticalTasks[0]['task_id']);
	$tpl->assign('style', $style);
	$tpl->assign('department_selection_list', $department_selection_list);
	$tpl->assign('project_id', $project_id);
	$tpl->assign('project_creator', $AppUI->user_id);
	$tpl->assign('project_contacts', implode(',', $selected_contacts));
	
	$tpl->displayAddEdit($row);

/**
 * List available departments options list with formatting (tabulated).
 * used in a recursive call.
 *
 * @param int $company_id 			the id of the company for which to lookup departments
 * @param array $checked_array 	the list of already processed departments 
 * @param int $dept_parent 			the parent department of the current one
 * @param int $spaces						the size of the tabulation to be applied (in spaces)
 *
 * return string 		the list of options for all accessible departments
 */
function getDepartmentSelectionList($company_id, $checked_array = array(), $dept_parent = 0, $spaces = 0){
	global $departments_count;
	$parsed = '';

	if ($departments_count < 6)
		$departments_count++;
	
	$q  = new DBQuery;
	$q->addTable('departments');
	$q->addQuery('dept_id, dept_name');
	$q->addWhere("dept_parent = '$dept_parent' and dept_company = '$company_id'");
	$depts_list = $q->loadHashList("dept_id");

	foreach($depts_list as $dept_id => $dept_info){
		$selected = in_array($dept_id, $checked_array) ? ' selected="selected"' : '';

		$parsed .= '<option value="'.$dept_id .'"'.$selected.'>'.str_repeat('&nbsp;', $spaces).$dept_info['dept_name'].'</option>';
		$parsed .= getDepartmentSelectionList($company_id, $checked_array, $dept_id, $spaces + 5);
	}
	
	return $parsed;
}
?>