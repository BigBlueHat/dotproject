<?php /* FORUMS $Id$ */
$AppUI->savePlace();

require_once( $AppUI->getSystemClass( 'event_queue' ) );

$ee = new EventQueue;
$ee->scan();

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );

// retrieve any state parameters
if (isset( $_REQUEST['company_id'] )) {
	$AppUI->setState( 'calIdxCompany', intval( $_REQUEST['company_id'] ) );
}
$company_id = $AppUI->getState( 'calIdxCompany', $AppUI->contact_company);

if (isset( $_REQUEST['project_id'] )) {
	$AppUI->setState( 'calIdxProject', intval( $_REQUEST['project_id'] ) );
}
$project_id = $AppUI->getState( 'calIdxProject', 0);

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'calIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'calIdxTab' ) !== NULL ? $AppUI->getState( 'calIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'calIdxTab' ) );

$comp = new CCompany();

$r  = new DBQuery;
$r->addTable('companies');
$r->addQuery('company_id, company_name');
$comp->setAllowedSQL($AppUI->user_id, $r);
$companies = $r->loadHashList();
$r->clear();
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

$proj = new CProject();
$r  = new DBQuery;
$r->addTable('projects');
$r->addQuery('project_id, project_name');
$proj->setAllowedSQL($AppUI->user_id, $r);
$projects = $r->loadHashList();
$r->clear();
$projects = arrayMerge( array( '0'=>$AppUI->_('All') ), $projects );


$titleBlock = new CTitleBlock( 'Event and Calendar Management', 'vcalendar.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar", "monthly calendar" );
$titleBlock->addCell( $AppUI->_('Company').':' );
$titleBlock->addCell(
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickCompany">', '</form>'
);
$titleBlock->addCell( $AppUI->_('Project').':' );
$titleBlock->addCell(
	arraySelect( $projects, 'project_id', 'onChange="document.pickProject.submit()" class="text"', $project_id ), '',
	'<form action="' . $_SERVER['REQUEST_URI'] . '" method="post" name="pickProject">', '</form>'
);
$titleBlock->show();

$perms =& $AppUI->acl();

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

$tabBox = new CTabBox( "?m=calendar&a=calmgt", "{$dPconfig['root_dir']}/modules/calendar/", $tab );

$tabBox->add('webcal_mgt', 'WebCal Management', true);
$min_view = true;
$tabBox->show();
?>