<?php
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

if (!$log_start_date)
{
	$start_date->addDays(-8);
	while($start_date->format('%w') != 1)
		$start_date->addDays( -1 );
}
if (!$log_end_date)
{
	$end_date->addDays( -8 );
	while($end_date->format('%w') != 1)
		$end_date->addDays( -1 );
	$end_date->addDays( 6 );
}
$end_date->setTime( 23, 59, 59 );

//error_reporting( E_ALL );
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
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
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
<h2><?php echo $report_title; ?></h2>
<form name="editFrm" action="" method="post">
	<input type="hidden" name="m" value="reports" />
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

	<td nowrap>
		<?php echo $AppUI->_('User');?>:
		<select name="log_userfilter" class="text" style="width: 80px">

	<?php
		$q = new DBQuery;
		$q->addQuery('user_id, user_username');
		$q->addQuery('contact_first_name, contact_last_name');
		$q->addTable('users');
		$q->addJoin('contacts', 'c', 'user_contact = contact_id');

		echo '<option value="0" '.(($log_userfilter == 0)?' selected':'').'>'.$AppUI->_('All users' ).'</option>';

		if (($rows = db_loadList( $q->prepare(), NULL )))
			foreach ($rows as $row)
				echo '<option value="'.$row["user_id"].'"'.(($log_userfilter == $row["user_id"])?' selected':'').'>'.$row["user_username"].'</option>';
	?>
		</select>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_csv" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make CSV' );?>
	</td>

	<td nowrap="nowrap">
		<input type="checkbox" name="log_pdf" <?php if ($log_pdf) echo "checked" ?> />
		<?php echo $AppUI->_( 'Make PDF' );?>
	</td>

</tr>
</table>
</form>

<?php
if ($do_report) {
	$q = new DBQuery;
	$q->addQuery('t.*');
	$q->addQuery('DATE_FORMAT(task_log_date, \'%d/%m/%Y %W\') as f_date');
	$q->addQuery('sum(task_log_hours) as hours');
	$contact_full_name = $q->concat('contact_last_name', "', '" , 'contact_first_name');
	$q->addQuery($contact_full_name." AS creator");
	$q->addTable('task_log', 't');
	$q->addTable('tasks');
	$q->addJoin('users', 'u', 'user_id = task_log_creator');
	$q->addJoin('contacts', 'c', 'user_contact = contact_id');
	$q->addJoin('projects', 'p', 'project_id = task_project');
	$q->addWhere('task_log_task = task_id');
	if ($project_id != 0)
		 $q->addWhere("task_project = $project_id");
	if (!$log_all) 
	{
		$q->addWhere("task_log_date >= '".$start_date->format( FMT_DATETIME_MYSQL )."'");
		$q->addWhere("task_log_date <= '".$end_date->format( FMT_DATETIME_MYSQL )."'");
	}
	if ($log_userfilter)
		$q->addWhere("task_log_creator = $log_userfilter");

	$proj =& new CProject;
	$allowedProjects = $proj->getAllowedSQL($AppUI->user_id, 'task_project');
	if (count($allowedProjects))
		$q->addWhere(implode(" AND ", $allowedProjects));
	$q->addGroup('task_log_creator, DATE_FORMAT(task_log_date, \'%Y%m%d\')');
	$q->addOrder('creator, task_log_date');

	$logs = db_loadList( $q->prepare() );
?>
	<table cellspacing="1" cellpadding="4" border="0" class="tbl">
	<tr>
		<th nowrap="nowrap"><?php echo $AppUI->_('User');?></th>
		<th><?php echo $AppUI->_('Day');?></th>
		<th><?php echo $AppUI->_('Hours');?></th>
	</tr>
<?php
	$hours = 0.0;
	$subtotal = 0.0;
	$pdfdata = array();
	$csvdata = array();
	$name = '';

        foreach ($logs as $log) {
		$date = new CDate( $log['task_log_date'] );

		if ($log['creator'] == '')
			$log['creator'] = 'Unknown';
		if ($log['creator'] != $name)
		{
			if ($name != '')
			{
					$csvdata[] = array(
		'',
		$AppUI->_('Subtotal'),
		sprintf( "%.2f", $hours - $subtotal )
	);
					$csvdata[] = array( '','','' );
				echo '<tr><td style="background: lightgray" align="right" colspan="2">Subtotal:</td>
							<td style="background: lightgray" align="right">'.sprintf('%.2f', $hours - $subtotal).'</td></tr>';
//<tr><td colspan="3">'.$log['creator'].'</td></tr>';
			}
			$name = $log['creator'];
			$subtotal = $hours;
		}

		$hours += $log['hours'];

		$csvdata[] = array(
			$log['creator'],
			$log['f_date'], //$date->format( $df ),
			sprintf( "%.2f", $log['hours'] ));
?>
	<tr>
		<td><?php echo $log['creator'];?></td>
		<td><?php echo $log['f_date']; //$date->format( $df );?></td>
		<td align="right"><?php printf( "%.2f", $log['hours'] );?></td>
	</tr>
<?php
	}
	echo '<tr><td style="background: lightgray" align="right" colspan="2">Subtotal:</td>
				<td style="background: lightgray" align="right">'.sprintf('%.2f',$hours - $subtotal).'</td></tr>';

	$csvdata[] = array(
		'',
		$AppUI->_('Subtotal'),
		sprintf( "%.2f", $hours - $subtotal )
	);
	$csvdata[] = array(
		'',
		$AppUI->_('Total Hours'),
		sprintf( "%.2f", $hours )
	);
	$pdfdata = $csvdata;
?>
	<tr>
		<td align="right" colspan="2"><?php echo $AppUI->_('Total Hours');?>:</td>
		<td align="right"><?php printf( "%.2f", $hours );?></td>
	</tr>
	</table>
<?php
	if ($log_csv)
	{
		$temp_dir = dPgetConfig( 'root_dir' )."/files/temp";
		$csvfile = 'User,Date,Hours' . "\n"; 
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
			echo "<a href=\"" . dPgetConfig( 'base_url' ) . "/files/temp/temp$AppUI->user_id.csv\">";
			echo $AppUI->_( "View CSV File" );
			echo "</a>";
		} else {
			echo "Could not open file to save CSV.  ";
			if (!is_writable( $temp_dir )) {
				"The files/temp directory is not writable.  Check your file system permissions.";
			}
		}
	}
	if ($log_pdf) {
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

		$font_dir = dPgetConfig( 'root_dir' )."/lib/ezpdf/fonts";
		$temp_dir = dPgetConfig( 'root_dir' )."/files/temp";
		$base_url  = dPgetConfig( 'base_url' );
		require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

		$pdf =& new Cezpdf();
		$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
		$pdf->selectFont( "$font_dir/Helvetica.afm" );

		$pdf->ezText( dPgetConfig( 'company_name' ), 12 );
		// $pdf->ezText( dPgetConfig( 'company_name' ).' :: '.dPgetConfig( 'page_title' ), 12 );

		$date = new CDate();
		$pdf->ezText( "\n" . $date->format( $df ) , 8 );

		$pdf->selectFont( "$font_dir/Helvetica-Bold.afm" );
		$pdf->ezText( "\n" . $report_title, 12 );
		$pdf->ezText( "$pname", 15 );
		if ($log_all) {
			$pdf->ezText( "All task log entries", 9 );
		} else {
			$pdf->ezText( "Task log entries from ".$start_date->format( $df ).' to '.$end_date->format( $df ), 9 );
		}
		$pdf->ezText( "\n\n" );

        $pdfheaders = array(
		        $AppUI->_('User'),
        		$AppUI->_('Date'),
        		$AppUI->_('Hours')
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

		if ($fp = fopen( "$temp_dir/temp$AppUI->user_id.pdf", 'wb' )) {
			fwrite( $fp, $pdf->ezOutput() );
			fclose( $fp );
			echo "<a href=\"$base_url/files/temp/temp$AppUI->user_id.pdf\" target=\"pdf\">";
			echo $AppUI->_( "View PDF File" );
			echo "</a>";
		} else {
			echo "Could not open file to save PDF.  ";
			if (!is_writable( $temp_dir )) {
				"The files/temp directory is not writable.  Check your file system permissions.";
			}
		}
	}
}
?>
