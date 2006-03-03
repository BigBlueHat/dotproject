<?php /* TASKS $Id$gantt.php,v 1.30 2004/08/06 22:56:54 gregorerhardt Exp $ */
GLOBAL  $AppUI, $company_id, $dept_ids, $department, $min_view, $m, $a, $user_id, $tab;
$min_view = defVal( @$min_view, false);
$project_id = defVal( @$_GET['project_id'], 0);

// sdate and edate passed as unix time stamps
$sdate = dPgetParam( $_POST, 'sdate', 0 );
$edate = dPgetParam( $_POST, 'edate', 0 );
$showInactive = dPgetParam( $_POST, 'showInactive', '0' );
$showLabels = dPgetParam( $_POST, 'showLabels', '0' );

$showAllGantt = dPgetParam( $_POST, 'showAllGantt', '0' );
$showTaskGantt = dPgetParam( $_POST, 'showTaskGantt', '0' );

//if set GantChart includes user labels as captions of every GantBar
if ($showLabels!='0') {
    $showLabels='1';
}
if ($showInactive!='0') {
    $showInactive='1';
}

if ($showAllGantt!='0')
     $showAllGantt='1';

$projectStatus = dPgetSysVal( 'ProjectStatus' );

if (isset(  $_POST['proFilter'] )) {
	$AppUI->setState( 'ProjectIdxFilter',  $_POST['proFilter'] );
}
$proFilter = $AppUI->getState( 'ProjectIdxFilter' ) !== NULL ? $AppUI->getState( 'ProjectIdxFilter' ) : '-1';

$projFilter = arrayMerge( array('-1' => 'All Projects'), $projectStatus);
$projFilter = arrayMerge( array( '-2' => 'All w/o in progress'), $projFilter);
natsort($projFilter);


// months to scroll
$scroll_date = 1;

$display_option = dPgetParam( $_POST, 'display_option', 'this_month' );

// format dates
$df = $AppUI->getPref('SHDATEFORMAT');

if ($display_option == 'custom') {
	// custom dates
	$start_date = intval( $sdate ) ? new CDate( $sdate ) : new CDate();
	$end_date = intval( $edate ) ? new CDate( $edate ) : new CDate();
} else {
	// month
	$start_date = new CDate();
	$start_date->day = 1;
   	$end_date = new CDate($start_date);
    	$end_date->addMonths( $scroll_date );
}

// setup the title block
if (!@$min_view) {
	$titleBlock = new CTitleBlock( 'Gantt Chart', 'applet-48.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=$m", "projects list" );
	$titleBlock->show();
}
?>
<script language="javascript">
var calendarField = '';

function popCalendar( field ){
	calendarField = field;
	idate = eval( 'document.editFrm.' + field + '.value' );
	window.open( 'index.php?m=public&a=calendar&dialog=1&callback=setCalendar&date=' + idate, 'calwin', 'width=250, height=220, scollbars=false' );
}

/**
 *	@param string Input date in the format YYYYMMDD
 *	@param string Formatted date
 */
function setCalendar( idate, fdate ) {
	fld_date = eval( 'document.editFrm.' + calendarField );
	fld_fdate = eval( 'document.editFrm.show_' + calendarField );
	fld_date.value = idate;
	fld_fdate.value = fdate;
}

function scrollPrev() {
	f = document.editFrm;
<?php
	$new_start = $start_date;	
	$new_start->day = 1;
	$new_end = $end_date;
	$new_start->addMonths( -$scroll_date );
	$new_end->addMonths( -$scroll_date );
	
	echo "f.sdate.value='".$new_start->format( FMT_TIMESTAMP_DATE )."';";
	echo "f.edate.value='".$new_end->format( FMT_TIMESTAMP_DATE )."';";
?>
	document.editFrm.display_option.value = 'custom';
	f.submit()
}

function scrollNext() {
	f = document.editFrm;
<?php
	$new_start = $start_date;
	$new_start->day = 1;
	$new_end = $end_date;	
	$new_start->addMonths( $scroll_date );
	$new_end->addMonths( $scroll_date );
	echo "f.sdate.value='" . $new_start->format( FMT_TIMESTAMP_DATE ) . "';";
	echo "f.edate.value='" . $new_end->format( FMT_TIMESTAMP_DATE ) . "';";
?>
	document.editFrm.display_option.value = 'custom';
	f.submit()
}

function showThisMonth() {
	document.editFrm.display_option.value = "this_month";
	document.editFrm.submit();
}

function showFullProject() {
	document.editFrm.display_option.value = "all";
	document.editFrm.submit();
}

</script>
<?php
global $tpl;

$src = "?m=projects&a=gantt&suppressHeaders=1" .
($display_option == 'all' ? '' : 
	'&start_date=' . $start_date->format( "%Y-%m-%d" ) . 
	'&end_date=' . $end_date->format( "%Y-%m-%d" ) ) .
	"&width=' + ((navigator.appName=='Netscape'?window.innerWidth:document.body.offsetWidth)*0.95) + '&showLabels=$showLabels&proFilter=$proFilter&showInactive=$showInactive&company_id=$company_id&department=$department&dept_ids=$dept_ids&showAllGantt=$showAllGantt";
$tpl->assign('script', "<script>document.write('<img src=\"$src\">')</script>");

$tpl->assign('projFilter', $projFilter);
$tpl->assign('proFilter', $proFilter);

$tpl->assign('showLabels', $showLabels);
$tpl->assign('showInactive', $showInactive);
$tpl->assign('showAllGantt', $showAllGantt);

$tpl->assign('start_date', $start_date);
$tpl->assign('end_date', $end_date);

$tpl->assign('display_option', $display_option);

$tpl->assign('user_id', $user_id);
$tpl->assign('tab', $tab);

$tpl->displayFile('gantt', 'projects');
?>
