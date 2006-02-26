<?php /* TASKS $Id$ */
GLOBAL $AppUI, $task_id, $obj, $percent, $can_edit_time_information, $tpl;

$perms =& $AppUI->acl();

// check permissions
$canEdit = $perms->checkModuleItem( 'task_log', 'edit', $task_id );
$canAdd = $perms->checkModuleItem( 'task_log', 'add', $task_id );

$task_log_id = intval( dPgetParam( $_GET, 'task_log_id', 0 ) );
$log = new CTaskLog();
if ($task_log_id) {
	if (! $canEdit)
		$AppUI->redirect("m=public&a=access_denied");
	$log->load( $task_log_id );
} else {
	if (! $canAdd)
		$AppUI->redirect("m=public&a=access_denied");
	$log->task_log_creator = $AppUI->user_id;
	$log->task_log_task = $task_id;
	$log->task_log_name = $obj->task_name;
}

// Check that the user is at least assigned to a task
$task = new CTask;
$task->load($task_id);
if (! $task->canAccess($AppUI->user_id))
	$AppUI->redirect('m=public&a=access_denied');

// Lets check which cost codes have been used before
/*$sql = "select distinct task_log_costcode
        from task_log
        where task_log_costcode != ''
        order by task_log_costcode";
$task_log_costcodes = array(""); // Let's add a blank default option
$task_log_costcodes = array_merge($task_log_costcodes, db_loadColumn($sql));
*/

$proj = &new CProject();
$proj->load($obj->task_project);

$q = new DBQuery;
$q->addTable('billingcode');
$q->addQuery('billingcode_id, billingcode_name');
$q->addWhere('billingcode_status=0');
$q->addWhere("(company_id='$proj->project_company' OR company_id='0')");
$q->addOrder('billingcode_name');

$task_log_costcodes[0]="None";
$ptrc = $q->exec();
echo db_error();
$nums = 0;
if ($ptrc)
	$nums=db_num_rows($ptrc);
for ($x=0; $x < $nums; $x++) {
        $row = db_fetch_assoc( $ptrc );
        $task_log_costcodes[$row["billingcode_id"]] = $row["billingcode_name"];
}
$q->clear();
$taskLogReference = dPgetSysVal( 'TaskLogReference' );

// Task Update Form
	$df = $AppUI->getPref( 'SHDATEFORMAT' );
	$log_date = new CDate( $log->task_log_date );
	
		$task_email_title = array();
				$q = new DBQuery;
				$q->addTable('task_contacts', 'tc');
				$q->leftJoin('contacts', 'c', 'c.contact_id = tc.contact_id');
				$q->addWhere("tc.task_id = '$task_id'");
				$q->addQuery('tc.contact_id');
				$q->addQuery('c.contact_first_name, c.contact_last_name');
				$req =& $q->exec();
				$cid = array();
				for ($req; ! $req->EOF; $req->MoveNext()) {
					$cid[] = $req->fields['contact_id'];
					$task_email_title[] = $req->fields['contact_first_name']
					. ' ' . $req->fields['contact_last_name'];
				}
				$task_contacts = implode(',', $cid);
				
					$q->clear();
				$q->addTable('project_contacts', 'pc');
				$q->leftJoin('contacts', 'c', 'c.contact_id = pc.contact_id');
				$q->addWhere("pc.project_id = '$obj->task_project'");
				$q->addQuery('pc.contact_id');
				$q->addQuery('c.contact_first_name, c.contact_last_name');
				$req =& $q->exec();
				$cid = array();
				$proj_email_title = array();
				for ($req; ! $req->EOF; $req->MoveNext()) {
					if (! in_array($req->fields['contact_id'], $cid)) {
					  $cid[] = $req->fields['contact_id'];
					  $proj_email_title[] = $req->fields['contact_first_name']
					  . ' ' . $req->fields['contact_last_name'];
					}
				}
				$project_contacts = implode(',', $cid);
				$q->clear();
				
		$tl = $AppUI->getPref('TASKLOGEMAIL');
		$ta = $tl & 1;
		$tt = $tl & 2;
		$tp = $tl & 4;
		
		$tpl->assign('ta', $ta);
		$tpl->assign('tt', $tt);
		$tpl->assign('tp', $tp);
		
		$tpl->assign('task_email_title', addslashes(implode(',',$task_email_title)));
		$tpl->assign('proj_email_title', addslashes(implode(',',$proj_email_title)));
				
				$tpl->assign('task_id', $task_id);
				$tpl->assign('user_id', $AppUI->user_id);
				$tpl->assign('obj', $obj);
				$tpl->assign('log', $log);
				$tpl->assign('viewContacts', ($AppUI->isActiveModule('contacts') && $perms->checkModule('contacts', 'view')) );
				$tpl->assign('task_contacts', $task_contacts);
				$tpl->assign('project_contacts', $project_contacts);
				$tpl->assign('percent', $percent);
				$tpl->assign('task_log_costcodes', $task_log_costcodes);
				$tpl->assign('taskLogReference', $taskLogReference);
				$tpl->assign('log_date', $log_date);
				
				$tpl->assign('uniqid', uniqid(""));
				$tpl->displayFile('tasklog.addedit');
?>

<!-- TIMER RELATED SCRIPTS -->
<script language="JavaScript">
	// please keep these lines on when you copy the source
	// made by: Nicolas - http://www.javascript-page.com
	// adapted by: Juan Carlos Gonzalez jcgonz@users.sourceforge.net
	
	var timerID       = 0;
	var tStart        = null;
    var total_minutes = -1;
	
	function UpdateTimer() {
	   if(timerID) {
	      clearTimeout(timerID);
	      clockID  = 0;
	   }
	
       // One minute has passed
       total_minutes = total_minutes+1;
	   
	   document.getElementById("timerStatus").innerHTML = "<br />("+total_minutes+" <?php echo $AppUI->_('minutes elapsed'); ?>)";

	   // Lets round hours to two decimals
	   var total_hours   = Math.round( (total_minutes / 60) * 100) / 100;
	   document.editFrm.task_log_hours.value = total_hours;
	   
	   timerID = setTimeout("UpdateTimer()", 60000);
	}
	
	function timerStart() {
		if(!timerID){ // this means that it needs to be started
			timerSet();
			document.editFrm.timerStartStopButton.value = "<?php echo $AppUI->_('Stop');?>";
            UpdateTimer();
		} else { // timer must be stoped
			document.editFrm.timerStartStopButton.value = "<?php echo $AppUI->_('Start');?>";
			document.getElementById("timerStatus").innerHTML = "";
			timerStop();
		}
	}
	
	function timerStop() {
	   if(timerID) {
	      clearTimeout(timerID);
	      timerID  = 0;
          total_minutes = total_minutes-1;
	   }
	}
	
	function timerReset() {
		document.editFrm.task_log_hours.value = "0.00";
        total_minutes = -1;
	}

	function timerSet() {
		if ((dot = document.editFrm.task_log_hours.value.indexOf(':')) > 0)
			total_minutes = parseInt(document.editFrm.task_log_hours.value.substring(0, dot)) * 60 + parseInt(document.editFrm.task_log_hours.value.substring(dot + 1));
		else
			total_minutes = Math.round(document.editFrm.task_log_hours.value * 60) -1;
	}
	
	function popCalendar( field )
	{
		calendarField = field;
		idate = eval( 'document.editFrm.task_' + field + '.value' );
		window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=251, height=220, scollbars=false' );
	}
</script>
<!-- END OF TIMER RELATED SCRIPTS -->