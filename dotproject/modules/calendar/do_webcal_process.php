<?php /* FORUMS $Id$ */

// collect and convert checkbox info
$_POST['webcal_auto_import'] = dPgetParam( $_POST, 'webcal_auto_import', 0 );
$_POST['webcal_auto_publish'] =  isset($_POST['webcal_auto_publish']) ? 1 : 0;
$_POST['webcal_private_events'] = isset($_POST['webcal_private_events']) ? 1 : 0;
$_POST['webcal_purge_events'] = isset($_POST['webcal_purge_events']) ? 1 : 0;
$_POST['webcal_preserve_id'] = isset($_POST['webcal_preserve_id']) ? 1 : 0;

$obj = new CWebCalresource();

if (!$obj->bind( $_POST )) {
	$AppUI->setMsg( $obj->getError(), UI_MSG_ERROR );
	$AppUI->redirect();
}

// prepare (and translate) the module name ready for the suffix
$AppUI->setMsg( 'Webcal-Resource' );

if ($_POST['proc_method'] == 'publish' || $_POST['proc_method'] == 'import') {
	require_once( $AppUI->getSystemClass( 'webdav_client' ) );
	$obj->load($_POST['webcal_id']);
	
	// establish webDAV client connection
	$wdc = new WebDAVclient();
	$target_path = $wdc->pathInfo( 'http://'.$obj->webcal_path );
	$wdc->setPath( $target_path );
	$wdc->setServer( $wdc->hostInfo( 'http://'.$obj->webcal_path ) );
	$wdc->setPort($obj->webcal_port);
	$wdc->setUser($obj->webcal_user);
	$wdc->setPass($obj->webcal_pass);

	if (!$wdc->openConnection()) {
		$AppUI->setMsg( 'WebDAVClient: Could not open server connection', UI_MSG_ERROR );
		$AppUI->redirect();
	}

	// check if server supports webdav rfc 2518
	if (!$wdc->checkConnection()) {
		$AppUI->setMsg( 'WebDAVClient: Server does not support WebDAV or user/password may be wrong', UI_MSG_ERROR );
		$AppUI->redirect();
	}
}


switch ($_POST['proc_method']) {

case 'del':
	if (($msg = $obj->delete())) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
		$AppUI->redirect();
	} else {
		$AppUI->setMsg( "deleted", UI_MSG_ALERT, true );
		$AppUI->redirect( );
	}
	break;
case 'store':
	if (($msg = $obj->store($_POST['calendars']))) {
		$AppUI->setMsg( $msg, UI_MSG_ERROR );
	} else {
		$isNotNew = @$_POST['webcal_id'];
		$AppUI->setMsg( $isNotNew ? 'updated' : 'added', UI_MSG_OK, true );
	}
	$AppUI->redirect();
	break;
case 'publish':
		// establish the filter info based on the selected calendars
		$w = $obj->getEventFilter();

	// instantiate new multiple vEvent object with filter info
		$v = new vCalendar();
		$v->addEventsByFilter($w, $obj->webcal_private_events);
		$ics = $v->genString();

	// put the ical content string to webdav collection as file
	$http_status = $wdc->put($target_path, $ics);
	$msgtype = ($http_status == '201' || $http_status == '204') ? UI_MSG_OK : UI_MSG_ERROR;
	$AppUI->setMsg( 'WebDAVClient: Server Status '.$http_status, $msgtype );
	$wdc->closeConnection();
	flush();

	break;
case 'import':
	

	$ics = null;
	$http_status = $wdc->get($target_path, $ics);
	if ($http_status == '200') { 
		$AppUI->setMsg( 'WebDAVClient: Server Status '.$http_status, UI_MSG_OK );

		require_once( $AppUI->getLibraryClass( 'PEAR/File/IMC/Parse' ) );

		// instantiate a parser object
		$parse = new File_IMC_Parse();

		// parse a iCal file and store the data
		// in $calinfo
		$calinfo = $parse->fromText($ics);
		$calendars = $obj->getCalendars();

		// store the ical info array
		$v = new vCalendar;
		foreach ($calendars as $c) {
			$msg = $v->icsParsedArrayToDpEvents($calinfo, $c, $obj->webcal_purge_events, $obj->webcal_preserve_id);
		}

	} else {
		$AppUI->setMsg( 'WebDAVClient: Server Status '.$http_status, UI_MSG_ERROR );
	}
	$wdc->closeConnection();
	flush();
	break;
}
?>
