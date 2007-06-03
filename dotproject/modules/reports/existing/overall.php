<?php /* PROJECTS $Id$ */
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

/**
* Generates a report of the task logs for given dates
*/
$do_report 			= dPgetParam( $_POST, 'do_report', 0 );
$log_pdf 				= dPgetParam( $_POST, 'log_pdf', 0 );

$log_start_date = dPgetParam( $_POST, 'log_start_date', 0 );
$log_end_date 	= dPgetParam( $_POST, 'log_end_date', 0 );
$log_all 				= dPgetParam( $_POST, 'log_all', 0 );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date) {
	$start_date->addDays(-14);
}
$end_date->setTime( 23, 59, 59 );

$fullaccess = ($canEdit);
?>
<script type="text/javascript" language="javascript">
<!--
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'top=250,left=250,width=250, height=220, scrollbars=no' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.log_' + calendarField );
	fld_fdate = eval( 'document.editFrm.' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}
-->
</script>

<form name="editFrm" action="index.php?m=reports" method="post">
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="report_category" value="<?php echo $report_category;?>" />
	<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('For period');?>:</td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_start_date" value="<?php echo $start_date->format( FMT_TIMESTAMP_DATE );?>" />
		<input type="text" name="start_date" value="<?php echo $start_date->format( $df );?>" class="text" disabled="disabled" style="width: 80px" />
		<a href="#" onclick="popCalendar('start_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_('to');?></td>
	<td nowrap="nowrap">
		<input type="hidden" name="log_end_date" value="<?php echo $end_date ? $end_date->format( FMT_TIMESTAMP_DATE ) : '';?>" />
		<input type="text" name="end_date" value="<?php echo $end_date ? $end_date->format( $df ) : '';?>" class="text" disabled="disabled" style="width: 80px"/>
		<a href="#" onclick="popCalendar('end_date')">
			<img src="./images/calendar.gif" width="24" height="12" alt="<?php echo $AppUI->_('Calendar');?>" border="0" />
		</a>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" <?php if ($log_all) echo "checked" ?> />
		<?php echo $AppUI->_( 'Log All' );?>
	</td>
	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

	<td align="right" width="50%" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
</tr>
</table>
</form>

<?php
$allpdfdata = array();
function showcompany($company, $restricted = false)
{
	global $AppUI, $allpdfdata, $log_start_date, $log_end_date, $log_all;
	$obj = new CProject();
	$q = new DBQuery;
	$q->addTable('projects');
	$q->addQuery('project_id, project_name');                     
	$q->addwhere("project_company='$company'");
	$obj->setAllowedSQL($AppUI->user_id, $q);	
	$sql = $q->prepare();
	$projects = db_loadHashList($sql);	

	$obj = new CCompany();
	$q = new DBQuery;
	$q->addTable('companies');
	$q->addQuery('company_name');                     
	$q->addwhere("company_id='$company'");
	$obj->setAllowedSQL($AppUI->user_id, $q);	
	$sql = $q->prepare();
	$company_name = db_loadResult($sql);
		
        $table = '<h2>Company: ' . $company_name . '</h2>
        <table cellspacing="1" cellpadding="4" border="0" class="tbl">';
	$project_row = '
        <tr>
                <th>' . $AppUI->_('Project') . '</th>';
                
		$pdfth[] = $AppUI->_('Project');
        $project_row .= '<th>' . $AppUI->_('Total') . '</th></tr>';
	$pdfth[] = $AppUI->_('Total');
	$pdfdata[] = $pdfth;
        
        $hours = 0.0;
	$table .= $project_row;

        foreach ($projects as $project => $name)
        {
		$pdfproject = array();
		$pdfproject[] = $name;
		$project_hours = 0;
		$project_row = "<tr><td>$name</td>";
		
		$obj = new CTaskLog();
		$q = new DBQuery;
		$q->addTable('projects');
		$q->addTable('tasks');
		$q->addTable('task_log');
		$q->addQuery('task_log_costcode, sum(task_log_hours) as hours');                     
		$q->addwhere("project_id='$project'");
		
		if ($log_start_date != 0 && !$log_all) {
			$q->addwhere("task_log_date >= '$log_start_date'");
		}
		if ($log_end_date != 0 && !$log_all) {
			$q->addwhere("task_log_date <= '$log_end_date'");
		}
		if ($restricted) {
			$q->addwhere("task_log_creator = '$AppUI->user_id'");
		}
		$q->addwhere("project_id = task_project");					
		$q->addwhere("task_id = task_log_task");								
		$q->addgroup('project_id');
		
		$obj->setAllowedSQL($AppUI->user_id, $q);			
		$sql = $q->prepare();
		
		$task_logs = db_loadHashList($sql);

		foreach($task_logs as $task_log) {
			$project_hours += $task_log;
		}
		$project_row .= '<td>' . round($project_hours, 2) . '</td></tr>';
		$pdfproject[]=round($project_hours, 2);
		$hours += $project_hours;
		if ($project_hours > 0) {
			$table .= $project_row;
			$pdfdata[] = $pdfproject;
		}
        }
	if ($hours > 0) {
		$allpdfdata[$company_name] = $pdfdata;
	
		echo $table;
		echo '<tr><td>Total</td><td>' . round($hours, 2) . '</td></tr></table>';
	}


	return $hours;
}

if ($do_report) {
	$total = 0;

	if ($fullaccess) {
		$sql = "SELECT company_id FROM companies";
	} else {
		$sql = "SELECT company_id FROM companies WHERE company_owner='" . $AppUI->user_id . "'";
	}
	$companies = db_loadColumn($sql);
	
	if (!empty($companies)) {	
		foreach ($companies as $company) {
			$total += showcompany($company);
		}
	} else {
		$sql = "SELECT company_id FROM companies";
		foreach(db_loadColumn($sql) as $company) {
			$total += showcompany($company, true);
		}
	}
	
	echo '<h2>' . $AppUI->_('Total Hours') . ":"; 
	printf( "%.2f", $total );
	echo '</h2>';


	if ($log_pdf) {
		// make the PDF file
		$font_dir = DP_BASE_DIR.'/lib/ezpdf/fonts';

		require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );
	
		$pdf =& new Cezpdf();
		$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
		$pdf->selectFont( $font_dir.'/Helvetica.afm' );
	
		$pdf->ezText( dPgetConfig( 'company_name' ), 12 );
	
		if ($log_all) {
			$date = new CDate();
			$pdf->ezText( "\nAll hours as of " . $date->format( $df ) , 8 );
		}	else {
			$sdate = new CDate($log_start_date);
			$edate = new CDate($log_end_date);
			$pdf->ezText( "\nHours from " . $sdate->format( $df ) .  " to " . $edate->format( $df ), 8);
		}

		$pdf->ezText( "\n" . $AppUI->_('Overall Report'), 12 );
	
		foreach($allpdfdata as $company => $data) {
			$title = $company;
			$options = array(
				'showLines' => 1,
				'showHeadings' => 0,
				'fontSize' => 8,
				'rowGap' => 2,
				'colGap' => 5,
				'xPos' => 50,
				'xOrientation' => 'right',
				'width'=>'500'
			);
	
			$pdf->ezTable( $data, NULL, $title, $options );
		}

		require_once $AppUI->getModuleClass('reports');	
		$Report = new dPReport();
		$Report->initializePDF();
		$Report->write('temp'.$AppUI->user_id.'.pdf', $pdf->ezOutput());
	}
}
?>