<?php /* PROJECTS $Id$ */
require_once( $AppUI->getModuleClass( 'projects' ) );

$project_id 			= intval( dPgetParam( $_REQUEST, 'project_id', 0 ) );
$report_category 	= dPgetParam($_REQUEST, 'report_category', null);
$report_type 			= dPgetParam( $_REQUEST, 'report_type', '' );

$do_report = dPgetParam( $_REQUEST, 'do_report', 0 );
$log_all = dPgetParam( $_REQUEST, 'log_all', 0 );
$log_pdf = dPgetParam( $_REQUEST, 'log_pdf', 0 );
$log_csv = dPgetParam( $_REQUEST, 'log_csv', 0 );
$log_userfilter = dPgetParam( $_REQUEST, 'log_userfilter', '0' );

$log_start_date = dPgetParam( $_REQUEST, 'log_start_date', 0 );
$log_end_date = dPgetParam( $_REQUEST, 'log_end_date', 0 );

// create Date objects from the datetime fields
$start_date = intval( $log_start_date ) ? new CDate( $log_start_date ) : new CDate();
$end_date = intval( $log_end_date ) ? new CDate( $log_end_date ) : new CDate();

//if (!$log_start_date)
//	$start_date->subtractSpan( new Date_Span( "14,0,0,0" ) );

$end_date->setTime( 23, 59, 59 );

// check permissions for this record
$perms =& $AppUI->acl();

$canRead = $perms->checkModuleItem( $m, 'view', $project_id );
if (!$canRead) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$obj = new CProject();
                                                                                
$q = new DBQuery;
$q->addQuery('project_id, project_status, project_name, project_description, project_short_name');
$q->addTable('projects');
$obj->setAllowedSQL($AppUI->user_id, $q);

$q->addGroup('project_id');
$q->addOrder('project_short_name');
$projects = $q->loadList();                                                                                
$project_list=array('0'=> $AppUI->_('All', UI_OUTPUT_RAW) );
foreach ($projects as $row)
	$project_list[$row['project_id']] = '('.$row['project_short_name'].') '.$row['project_name'];

$display_project_name=$project_list[$project_id]; 

if (!$suppressHeaders)
{
?>
<script type="text/javascript" language="javascript">
<!--
function changeIt()
{
	var f=document.changeMe;
	f.submit();
}
-->
</script>

<?php
}
// get the prefered date format
$df = $AppUI->getPref('SHDATEFORMAT');

if (!isset($report_category))
{
	$reports = $AppUI->readFiles( dPgetConfig( 'root_dir' ).'/modules/reports' ); //, "\.php$"
	$ignore = array('index.php', 'setup.php', 'CVS');
	$report_categories = array_diff($reports, $ignore);
}
else
	$reports = $AppUI->readFiles( dPgetConfig( 'root_dir' ).'/modules/reports/'.$report_category, "\.php$" );


// setup the title block
if (! $suppressHeaders) {
	$titleBlock = new CTitleBlock( 'Reports', 'applet3-48.png', $m, "$m.$a" );
	$titleBlock->addCrumb( '?m=projects', 'projects list' );
	if ($project_id != 0)
		$titleBlock->addCrumb( '?m=projects&amp;a=view&amp;project_id='.$project_id, 'view this project' );
	if ($report_category)
		$titleBlock->addCrumb( '?m=reports&amp;project_id='.$project_id, 'reports index' );
	if ($report_type)
		$titleBlock->addCrumb( '?m=reports&amp;project_id='.$project_id.'&amp;report_category='.$report_category, 'category index' );

	$titleBlock->show();


if (!isset($display_project_name))
	$display_project_name = $AppUI->_('All Projects'); 

echo $AppUI->_('Selected Project') . ': <b>'.$display_project_name.'</b>'; 
$report_type_var = dPgetParam($_GET, 'report_type', '');
if (!empty($report_type_var))
	$report_type_var = "&amp;report_category=$report_category&amp;report_type=$report_type";
?>
<form name="changeMe" action="./index.php?m=reports<?php echo $report_type_var; ?>" method="post">
	<input type="hidden" name="do_report" value="<?php echo $do_report; ?>" />
	<input type="hidden" name="log_all" value="<?php echo $log_all; ?>" />
	<input type="hidden" name="log_pdf" value="<?php echo $log_pdf; ?>" />
	<input type="hidden" name="log_csv" value="<?php echo $log_csv; ?>" />
	<input type="hidden" name="log_userfilter" value="<?php echo $log_userfilter; ?>" />
	<input type="hidden" name="log_start_date" value="<?php echo $log_start_date; ?>" />
	<input type="hidden" name="log_end_date" value="<?php echo $log_end_date; ?>" />
<?php echo $AppUI->_('Projects') . ':';?>
<?php echo arraySelect( $project_list, 'project_id', 'size="1" class="text" onchange="changeIt();"', $project_id, false );?>
</form>

<?php
}
if ($report_type) {
	$report_type = $AppUI->checkFileName( $report_type );
	$report_type = str_replace( ' ', '_', $report_type );
	$report_title = @file( dPgetConfig('root_dir')."/modules/reports/$report_category/$report_type.$AppUI->user_locale.txt");
	$report_title = $report_title[0];
	require( dPgetConfig( 'root_dir' )."/modules/reports/$report_category/$report_type.php" );
} else if ($report_category) {
	echo '
<table>
<tr>
	<td><h2>' . $AppUI->_( 'Reports Available' ) . '</h2></td>
</tr>';
	foreach ($reports as $v) {
		$type = str_replace( '.php', '', $v );
		$desc_file = str_replace( '.php', '.'.$AppUI->user_locale.'.txt', $v );
		$desc = @file( dPgetConfig( 'root_dir' )."/modules/reports/$report_category/$desc_file" );

		echo "
<tr>
	<td>
		<a href=\"index.php?m=reports&amp;project_id=$project_id&amp;report_category=$report_category&amp;report_type=$type";
		if (isset($desc[2]))
			echo '&amp;' . trim($desc[2]);
		echo '">';
		echo @$desc[0] ? $desc[0] : $v;
		echo '</a>
	</td>
	<td>' . (@$desc[1] ? "- $desc[1]" : '') . '</td>
</tr>';
	}
	echo '</table>';
} else {
	echo '
<table>
<tr>
	<td><h2>' . $AppUI->_( 'Reports Categories' ) . '</h2></td>
</tr>';
	foreach ($report_categories as $v) {
		$type = $v;
		$desc_file = "$v.$AppUI->user_locale.txt";
		$desc = @file( dPgetConfig( 'root_dir' ).'/modules/reports/'.$desc_file );

		echo "
<tr>
	<td><a href=\"index.php?m=reports&amp;project_id=$project_id&amp;report_category=$v";
		if (isset($desc[2]))
			echo "&" . $desc[2];
		echo '">';
		echo @$desc[0] ? $desc[0] : $v;
		echo '</a>';
		echo '
	</td>
	<td>' . (@$desc[1] ? "- $desc[1]" : '') . '</td>
</tr>';
	}
	echo '</table>';
}
?>
