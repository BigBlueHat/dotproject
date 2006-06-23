<?php /* EVENTS $Id$ */
GLOBAL $company_id;

$canEdit = !getDenyEdit( 'events' );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// check whether iCal file should be fetched from source or parsed for iCalKeys; criteria: get parameters
if ( isset($_FILES['ics']) && isset($_GET['suppressHeaders']) && ($_GET['suppressHeaders']=='true')) {	//parse and store iCal file
	$purge_events = dPgetParam( $_POST, 'purge_events', false );
	$purge_events = ($purge_events != false) ? true : false;

	$webdav_preserve_id = dPgetParam( $_POST, 'webdav_preserve_id', false );
	$webdav_preserve_id = ($webdav_preserve_id != false) ? true : false;

	$vcf = $_FILES['ics'];
	// include PEAR IMC class
	//require_once( $AppUI->getLibraryClass( 'PEAR/File/IMC' ) );
	require_once( $AppUI->getLibraryClass( 'PEAR/File/IMC/Parse' ) );
	
	$cat = dPgetSysVal('EventType');
	$tac = array_flip($cat);
	
	//get event types
	$vdummy = new vCalendar();
	$et = array_flip($vdummy->recurs);
	
	if (is_uploaded_file($vcf['tmp_name'])) {

		// instantiate a parser object
		$parse = new File_IMC_Parse();

		// parse a iCal file and store the data
		// in $cardinfo
		$calinfo = $parse->fromFile($vcf['tmp_name']);

		// store the ical info array
		// store the ical info array
		$v = new vCalendar;
		foreach ($_POST['calendars'] as $c) {
			$msg = $v->icsParsedArrayToDpEvents($calinfo, $c, $webdav_purge_events, $webdav_preserve_id);
		}
		if ($msg == true) {
			// one or more iCal imports were successful
			$AppUI->setMsg( 'iCalendar Event(s) imported', UI_MSG_OK, true );
		} else {
			$AppUI->setMsg( $msg, UI_MSG_ERROR, true );
		}
	}
	else {	// redirect in case of file upload trouble
		$AppUI->setMsg( "iCalendarFileUploadError", UI_MSG_ERROR );
	}

	$AppUI->redirect();


}
elseif ( isset($_GET['dialog']) && ($_GET['dialog']=='0') ){	//file upload form

	require_once( $AppUI->getModuleClass( 'companies' ) );
	require_once( $AppUI->getModuleClass( 'projects' ) );
	
	$comp = new CCompany();
	$proj = new CProject();
	
	$r  = new DBQuery;
	$r->addTable('projects');
	$r->addQuery('project_id, CONCAT(c.company_name,"::", project_name) AS project_name');
	$r->addJoin('companies', 'c', 'project_company = c.company_id'); 
	if ($company_id > 0){
		$r->addWhere('project_company='.$company_id);
	}
	$proj->setAllowedSQL($AppUI->user_id, $r);
	$comp->setAllowedSQL($AppUI->user_id, $r);
	$calendar = $r->loadHashList();
	$r->clear();
	
	$calendar[0] = $AppUI->_('Unspecified Calendar');
	$calendar[-1] = $AppUI->_('Personal Calendar');
	
	$titleBlock = new CTitleBlock( 'Import iCalendar File', 'vcalendar.png', $m, "$m.$a" );
	$titleBlock->addCrumb( "?m=calendar", "monthly calendar" );
	$titleBlock->show();
	
	$tpl->assign('cal', $cal);
	$tpl->assign('calendar', $calendar);
	$tpl->displayFile('eventimport', 'calendar');

} else {	// trouble with get parameters
$AppUI->setMsg( "iCalendarImportError", UI_MSG_ERROR );
	$AppUI->redirect();
}