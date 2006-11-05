<?php /* FORUMS $Id$ */
$AppUI->savePlace();

require_once($AppUI->getModuleClass('companies'));
require_once($AppUI->getModuleClass('projects'));
require_once($AppUI->getSystemClass('event_queue'));

$ee = new EventQueue;
$ee->scan();

// retrieve any state parameters
if (isset($_REQUEST['company_id']))
	$AppUI->setState('calIdxCompany', intval($_REQUEST['company_id']));

$company_id = $AppUI->getState('calIdxCompany', $AppUI->contact_company);

if (isset($_REQUEST['project_id']))
	$AppUI->setState('calIdxProject', intval($_REQUEST['project_id']));

$project_id = $AppUI->getState('calIdxProject', 0);

if (isset($_GET['tab']))
	$AppUI->setState('calIdxTab', $_GET['tab']);

$tab = $AppUI->getState('calIdxTab') !== NULL ? $AppUI->getState('calIdxTab') : 0;
$active = intval(!$AppUI->getState('calIdxTab'));

$comp = new CCompany();

$q = new DBQuery;
$q->addTable('companies');
$q->addQuery('company_id, company_name');
$comp->setAllowedSQL($AppUI->user_id, $q);
$companies = $q->loadHashList();
$companies = arrayMerge(array('0'=>$AppUI->_('All')), $companies);

$proj = new CProject();
$q->addTable('projects');
$q->addQuery('project_id, project_name');
$proj->setAllowedSQL($AppUI->user_id, $q);
$projects = $q->loadHashList();
$projects = arrayMerge(array('0'=>$AppUI->_('All')), $projects);

$titleBlock = new CTitleBlock( 'Event and Calendar Management', 'vcalendar.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=calendar", "monthly calendar" );
$titleBlock->addCell( $AppUI->_('Company').':' );
$titleBlock->addCell(
	'<form action="' . urlencode($_SERVER['REQUEST_URI']) . '" method="post" name="pickCompany">' .
	arraySelect( $companies, 'company_id', 'onChange="document.pickCompany.submit()" class="text"', $company_id ) . 
'</form>', '', '', '');
$titleBlock->addCell( $AppUI->_('Project').':' );
$titleBlock->addCell(
'<form action="' . urlencode($_SERVER['REQUEST_URI']) . '" method="post" name="pickProject">' .
	arraySelect( $projects, 'project_id', 'onChange="document.pickProject.submit()" class="text"', $project_id ) . 
'</form>', '', '', '');
$titleBlock->show();

$perms =& $AppUI->acl();

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$tabBox = new CTabBox('?m=calendar&amp;a=calmgt', $dPconfig['root_dir'].'/modules/calendar/', $tab);

$tabBox->add('webcal_mgt', 'WebCal Management', true);
$min_view = true;
$tabBox->show();
?>