<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

require_once( $AppUI->getModuleClass( 'tasks' ) );
$log_task_task = dPgetParam($_POST, 'log_task_task', 0);
$log_task_parent = dPgetParam($_POST, 'log_task_parent', 0);
$order = dPgetParam($_POST, 'order', 'task_log_date');
$perms =& $AppUI->acl();
if (! $perms->checkModule('tasks', 'view'))
	redirect('m=public&a=access_denied');

?>
<script type="text/javascript" language="javascript">
<!--
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.log_' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scrollbars=no' );
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

function reorder( order )
{
	document.editFrm.order.value=order;
	document.editFrm.do_report.click();
	document.editFrm.submit();
}
-->
</script>

<h2><?php echo $report_title; ?></h2>
<form name="editFrm" action="" method="post">
	<input type="hidden" name="m" value="reports" />
	<input type="hidden" name="order" value="<?php echo $order; ?>" />
	<input type="hidden" name="project_id" value="<?php echo $project_id;?>" />
	<input type="hidden" name="report_category" value="<?php echo $report_category;?>" />
	<input type="hidden" name="report_type" value="<?php echo $report_type;?>" />

<table cellspacing="0" cellpadding="4" border="0" width="100%" class="std">
<tr>
	<td align="right" nowrap="nowrap">
		<input class="button" type="submit" name="do_report" value="<?php echo $AppUI->_('submit');?>" />
	</td>
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


<!--
	<td nowrap="nowrap">
		<input type="checkbox" name="log_allprojects" <?php if ($log_allprojects) echo "checked" ?> />
		<?php echo $AppUI->_( 'All Projects' );?>
	</td>
-->

	<td nowrap="nowrap">
		<input type="checkbox" name="log_all" <?php if ($log_all) echo 'checked="checked"'; ?> />
		<?php echo $AppUI->_( 'Log All' );?>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_csv" <?php if ($log_pdf) echo 'checked="checked"'; ?> />
		<?php echo $AppUI->_( 'Make CSV' );?>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo 'checked="checked"'; ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

</tr>
<tr>
	<td>&nbsp;</td>
	<td align="right" nowrap>
		<?php echo $AppUI->_('User');?>:
	</td>
	<td>
		<select name="log_userfilter" class="text" style="width: 80px" onchange="reorder('<?php echo $order; ?>')">

	<?php
		$q = new DBQuery;
		$q->addQuery('user_id, user_username');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addTable('users');
		$q->addJoin('contacts', 'c', 'user_contact = contact_id');

		echo '<option value="0" '.(($log_userfilter == 0)?' selected="selected"':'').'>'.$AppUI->_('All users' ).'</option>';

		if (($rows = db_loadList( $q->prepare(), NULL )))
			foreach ($rows as $row)
				echo '<option value="'.$row['user_id'].'"'.(($log_userfilter == $row['user_id'])?' selected':'').'>'.$row['user_username'].'</option>';
	?>
		</select>
	</td>

	<td align="right" nowrap>
		<?php echo $AppUI->_('Task Parent');?>:
	</td>
	<td>
		<select name="log_task_parent" class="text" style="width: 80px" onchange="reorder('<?php echo $order; ?>')">

	<?php
		$q = new DBQuery;
		$q->addQuery('pt.task_id, pt.task_name');
		$q->addTable('tasks', 'pt');
//		$q->addJoin('tasks', 't', 't.task_parent = pt.task_id AND t.task_id != pt.task_id');
//		$q->addWhere('t.task_id != t.task_parent');
		$q->addWhere('pt.task_id = pt.task_parent');
		if ($project_id != 0)
			$q->addWhere('pt.task_project = ' . $project_id);
		$q->addGroup('pt.task_id');
		$q->addOrder('pt.task_project, pt.task_name');

		echo '<option value="0"'.(($log_task_parent == 0)?' selected="selected"':'').'>'.$AppUI->_('All Task Parent' ) . '</option>';

		if (($rows = db_loadList( $q->prepare(), NULL )))
			foreach ($rows as $row)
				echo '<option value="'.$row['task_id'].'"'.(($log_task_parent == $row['task_id'])?' selected="selected"':'').'>'.$row['task_name'];
	?>

		</select>
	</td>

	<td nowrap>
		<?php echo $AppUI->_('Task');?>:
		<select name="log_task_task" class="text" style="width: 80px" onchange="reorder('<?php echo $order; ?>')">

	<?php
		$q = new DBQuery;
		$q->addQuery('t.task_id, t.task_name');
		$q->addTable('tasks', 't');
		$q->addJoin('tasks', 'pt', 't.task_parent = pt.task_id AND t.task_id != pt.task_id');
		$q->addWhere('t.task_id != t.task_parent');
//		$q->addWhere('pt.task_id = pt.task_parent');
		if ($project_id != 0)
			$q->addWhere('pt.task_project = ' . $project_id);
		if ($log_task_parent)
			$q->addWhere('t.task_parent = ' . $log_task_parent);
		$q->addGroup('t.task_id');
		$q->addOrder('t.task_project, t.task_name');

		echo '<option value="0"'.(($log_task_task == 0)?' selected="selected"':'').'>'.$AppUI->_('All Tasks' ) . '</option>';

		if (($rows = db_loadList( $q->prepare(), NULL )))
			foreach ($rows as $row)
				echo '<option value="'.$row['task_id'].'"'.(($log_task_task == $row['task_id'])?' selected="selected"':'').'>'.$row['task_name'];
	?>

		</select>
	</td>
</tr>
</table>
</form>

<?php
if ($do_report) {
	$q = new DBQuery;
	$q->addQuery('t.*');
	$q->addQuery('tt.task_id, tt.task_name');
	$q->addQuery('ct.task_id as child_task_id');
	$q->addQuery('ct.task_name as child_task_name');
	$q->addQuery('pt.task_id as parent_id');
	$q->addQuery('pt.task_name as parent');
	$q->addQuery('p.project_id, p.project_name');
	$q->addQuery('billingcode_name as costcode');
	$q->addQuery('ct.task_percent_complete as completion');
	$contact_full_name = $q->concat('contact_last_name', "', '" , 'contact_first_name');
	$q->addQuery($contact_full_name." AS creator");

	$q->addTable('task_log', 't');
	$q->addTable('tasks', 'tt');

	$q->addJoin('tasks', 'ct', 'ct.task_parent = tt.task_id');
	$q->addJoin('tasks', 'pt', 'tt.task_parent = pt.task_id');
	$q->addJoin('users', 'u', 'user_id = task_log_creator');
	$q->addJoin('contacts', 'c', 'user_contact = contact_id');
	$q->addJoin('projects', 'p', 'project_id = tt.task_project');
	$q->addJoin('billingcode', 'b', 'task_log_costcode = billingcode_id');

	$q->addWhere('task_log_task = ct.task_id');
	if ($project_id != 0)
		$q->addWhere('tt.task_project = ' . $project_id);
	if ($log_task_task != 0)
		$q->addWhere('tt.task_id = ' . $log_task_task);
	if ($log_task_parent != 0 && $log_task_task == 0)
	{
		$q->addWhere('tt.task_parent = ' . $log_task_parent);
/*		$task_parent = new CTask;
		$task_parent->load($log_task_parent);
		$p_tasks = $task_parent->getDeepChildren();
		if (!empty($p_tasks))
			$q->addWhere('ct.task_id IN (\'' . implode('\', \'', $p_tasks) . '\')');
*/
	}
	if (!$log_all) 
	{
		$q->addWhere("task_log_date >= '".$start_date->format( FMT_DATETIME_MYSQL )."'");
		$q->addWhere("task_log_date <= '".$end_date->format( FMT_DATETIME_MYSQL )."'");
	}
	if ($log_userfilter)
		$q->addWhere("task_log_creator = $log_userfilter");

	$proj =& new CProject;
	$allowedProjects = $proj->getAllowedSQL($AppUI->user_id, 'tt.task_project');
	if (count($allowedProjects))
		$q->addWhere(implode(" AND ", $allowedProjects));

	$q->addOrder('creator, ' . $order);

	$logs = db_loadList( $q->prepare() );
?>
	<table cellspacing="1" cellpadding="4" border="0" class="tbl" width="100%">
	<tr>
		<th width="100" nowrap="nowrap"><?php echo $AppUI->_('User');?></th>
		<th><a href="javascript:void(0)" style="color: white" onclick="reorder('project_name');"><?php echo $AppUI->_('Project');?></a></th>
		<th><a href="javascript:void(0)" style="color: white" onclick="reorder('pt.task_name');"><?php echo $AppUI->_('Task Parent');?></a></th>
		<th><a href="javascript:void(0)" style="color: white" onclick="reorder('tt.task_name');"><?php echo $AppUI->_('Task');?></a></th>
		<th><a href="javascript:void(0)" style="color: white" onclick="reorder('ct.task_name');"><?php echo $AppUI->_('Child Task');?></a></th>
		<th width="20"><a href="javascript:void(0)" style="color: white" onclick="reorder('task_log_hours');"><?php echo $AppUI->_('Hours');?></a></th>
		<th width="20"><a href="javascript:void(0)" style="color: white" onclick="reorder('costcode');"><?php echo $AppUI->_('Cost Code');?></a></th>
		<th width="20"><a href="javascript:void(0)" style="color: white" onclick="reorder('completion');"><?php echo $AppUI->_('Completion	');?></a></th>
		<th width="40"><a href="javascript:void(0)" style="color: white" onclick="reorder('task_log_date');"><?php echo $AppUI->_('Date');?></a></th>
	</tr>
<?php
	$hours = 0.0;
	$pdfdata = array();
	$csvdata = array();
	$altcolor = '#d6ebff';
	$bgcolor = $altcolor;
	$cur_user = '';
	$cur_total = 0;

	foreach ($logs as $log) 
	{
		if ($log['creator'] != $cur_user)
		{
			if ($cur_user != '')
			{
				echo '
	<tr>
		<td style="background:lightgray" colspan="4">' . $cur_user . '</td>
		<td style="background:lightgray" align="right">' . $AppUI->_('SubTotal') . ':</td>
		<td style="background:lightgray" align="right">' . sprintf( "%.2f", $cur_total ) . '</td>
		<td style="background:lightgray" colspan="3">&nbsp;</td>		
	</tr>
				';
				$csvdata[] = array('','','','',$cur_user,sprintf( "%.2f", $cur_total),'','','','');
			}
			$cur_user = $log['creator'];
			$cur_total = 0;
		}
		$bgcolor = ($bgcolor == $altcolor)?'white':$altcolor;
		$date = new CDate( $log['task_log_date'] );
		$hours += $log['task_log_hours'];
		$cur_total += $log['task_log_hours'];

		$csvdata[] = array(
			$log['creator'],
			$log['project_name'],
			$log['parent'],
			$log['task_name'],
			$log['child_task_name'],
			sprintf( "%.2f", $log['task_log_hours'] ),
			$log['costcode'],
			$log['completion'],
			$date->format( $df ),		
			$log['task_log_description']);
?>
	<tr>
		<td style="background:<?php echo $bgcolor;?>">
			<?php echo $log['creator'];?>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<a href="index.php?m=projects&amp;a=view&amp;project_id=<?php echo $log['project_id'];?>"><?php echo $log['project_name'];?></a>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<a href="index.php?m=tasks&amp;a=view&amp;task_id=<?php echo $log['parent_id'];?>"><?php echo $log['parent'];?></a>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<a href="index.php?m=tasks&amp;a=view&amp;task_id=<?php echo $log['task_id'];?>"><?php echo $log['task_name']; ?></a>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<a href="index.php?m=tasks&amp;a=view&amp;task_id=<?php echo $log['child_task_id'];?>"><?php echo $log['child_task_name']; ?></a>
		</td>
		<td style="background:<?php echo $bgcolor;?>" align="right">
			<?php printf( "%.2f", $log['task_log_hours'] );?>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<?php echo $log['costcode'];?>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<?php echo $log['completion'];?>
		</td>
		<td style="background:<?php echo $bgcolor;?>">
			<?php echo $date->format( $df );?>
		</td>
	</tr>
	<tr>
		<td style="background:<?php echo $bgcolor;?>" colspan="9">
			<?php echo $log['task_log_description']; ?>
		</td>
	</tr>
<?php
	}
	$csvdata[] = array('','','','',$cur_user,$cur_total,'','','','');
	$csvdata[] = array(
		'',
		'',
		'',
		'',
		$AppUI->_('Total Hours').':',
		sprintf( "%.2f", $hours ),
		'',
		'',
		'',
		''
	);
	$pdfdata = $csvdata;
?>
	<tr>
		<td style="background:lightgray" colspan="4"><?php echo $cur_user; ?></td>
		<td style="background:lightgray" align="right"><?php echo $AppUI->_('SubTotal');?>:</td>
		<td style="background:lightgray" align="right" ><?php printf( "%.2f", $cur_total );?></td>
		<td style="background:lightgray" colspan="3">&nbsp;</td>
	</tr>
	<tr>
		<td align="right" colspan="5"><?php echo $AppUI->_('Total Hours');?>:</td>
		<td align="right"><?php printf( "%.2f", $hours );?></td>
		<td colspan="3">&nbsp;</td>
	</tr>
	</table>
<?php
	if ($log_csv)
	{
		$temp_dir = DP_BASE_DIR . '/files/temp';
		$csvfile = '"User","Project","Task Parent","Task","Child Task","Hours","Cost Code","Completion","Date","Task Log"' . "\n"; 
		foreach($csvdata as $row)
		{	
			foreach($row as $value)
				$csvfile .= '"' . stripslashes($value) . '",';
			$csvfile = substr($csvfile, 0, -1) . "\n";
		}
			//$csvfile .= '"' . implode('","',$row) . "\"\n"; 

		if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.csv", 'wb' )) {
			fwrite( $fp, $csvfile );
			fclose( $fp );
			echo '<a href="' . DP_BASE_URL . "/files/temp/temp$AppUI->user_id.csv\">";
			echo $AppUI->_('View CSV File');
			echo '</a>';
		} else {
			echo 'Could not open file to save CSV.';
			if (!is_writable( $temp_dir ))
				'The files/temp directory is not writable.  Check your file system permissions.';
		}
	}
	if ($log_pdf) 
	{
	// make the PDF file
		if ($project_id != 0)
		{
			$q = new DBQuery;
			$q->addQuery('project_name');
			$q->addTable('projects');
			$q->addWhere('project_id = ' . $project_id);
			$pname = db_loadResult( $q->prepare() );
		}
		else
			$pname = "All Projects";
		echo db_error();

		$font_dir = DP_BASE_DIR.'/lib/ezpdf/fonts';
		require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

		$pdf =& new Cezpdf();
		$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
		$pdf->selectFont( "$font_dir/Helvetica.afm" );

		$pdf->ezText( dPgetConfig( 'company_name' ), 12 );
		// $pdf->ezText( dPgetConfig( 'company_name' ).' :: '.dPgetConfig( 'page_title' ), 12 );

		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont($font_dir . '/Helvetica-Bold.afm');
		$pdf->ezText( "\n" . $report_title, 12 );
		$pdf->ezText($pname, 15);
		if ($log_all)
			$pdf->ezText('All task log entries', 9);
		else
			$pdf->ezText('Task log entries from '.$start_date->format( $df ).' to '.$end_date->format( $df ), 9 );

		$pdf->ezText( "\n\n" );

        $pdfheaders = array(
						$AppUI->_('User'),
						$AppUI->_('Project'),
        		$AppUI->_('Task Parent'),
        		$AppUI->_('Task'),
        		$AppUI->_('Child Task'),
        		$AppUI->_('Hours'),
	        	$AppUI->_('Cost Code'),
	        	$AppUI->_('Completion'),
        		$AppUI->_('Date'),
						$AppUI->_('Task Log')
        	);

		$options = array(
			'showLines' => 1,
			'fontSize' => 8,
			'rowGap' => 2,
			'colGap' => 5,
			'xPos' => 50,
			'xOrientation' => 'right',
			'width'=>'500'
		);

		$pdf->ezTable( $pdfdata, $pdfheaders, '', $options );

		require_once $AppUI->getModuleClass('reports');	
		$Report = new dPReport();
		$Report->initializePDF();
		$Report->write('temp'.$AppUI->user_id.'.pdf', $pdf->ezOutput());
	}
}
?>