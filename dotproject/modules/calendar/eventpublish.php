<?php
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

// get GETPARAMETER for event_id
$event_id = intval( $_GET['event_id']);
$types = dPgetSysVal('EventType');

$canRead = !getDenyRead( 'events' );
if (!$canRead) {
	$AppUI->redirect('m=public&a=access_denied');
}

if ( isset($_GET['event_id']) && !($_GET['event_id']=='') ) {
	$events = NULL;
	//pull data for this event
	$q  = new DBQuery;
	$q->addTable('events');
	$q->addQuery('*');
	$q->addWhere("event_id = $event_id");
	$q->loadObject($event);	
	$q->clear();
	
	$q  = new DBQuery;
	$q->addTable('user_events');
	$q->addJoin('users', 'u', 'u.user_id = user_events.user_id');
	$q->addJoin('contacts', 'c', 'u.user_contact = c.contact_id');
	$q->addWhere("event_id = $event_id");
	$users = $q->loadList();	
	$q->clear();
	
	$q  = new DBQuery;
	$q->addTable('users');
	$q->addJoin('contacts', 'c', 'users.user_contact = c.contact_id');
	$q->addWhere('user_id ='.$event->event_owner );
	$q->loadObject($owner);	
	$q->clear();

	$start_date =& new CDate($event->event_start_date);
	$end_date =& new CDate($event->event_end_date);	
	
	// create vEvent String	
	$v = new vCalendar;
	$v->addSum($event->event_title);
	$v->addDesc($event->event_description);
	$v->addCat($types[(int) $this->event_type]);
	$v->addOrg($owner->contact_first_name.' '.$owner->contact_last_name, $owner->contact_email);
	$v->addStart($start_date);
	$v->addEnd($end_date);
	if ($event->event_recurs > 0 ) {
		$v->addRec($event->event_recurs, $event->event_times_recuring, $end_date);
	}
	
	foreach ($users as $user) {
		$v->addAttendee($user['contact_first_name'] .' '. $user['contact_last_name'], $user['contact_email']);
	}
	$v->addUrl(DP_BASE_URL . '/index.php?m=calendar&a=view&event_id=' . $event->event_id );
	$v->addRel($event->event_parent, 'PARENT');
	$v->addCreated();
	$v->addUid();
	$v->addSeq();
	if ($this->event_private == TRUE || $this->event_private == 1) {
		$v->addClass('PRIVATE');
	} else {
		$v->addClass('PUBLIC');
	}

	$text = $v->genString();

require_once( $AppUI->getLibraryClass( 'webDAV/class_webdav_client' ) );

$wdc = new webdav_client();
$wdc->_path='/~gregor/webdav/';
$wdc->set_server('127.0.0.1');
$wdc->set_port(80);
$wdc->set_user('gregor');
$wdc->set_pass('gregi');
// use HTTP/1.1
$wdc->set_protocol(1);
// enable debugging
$wdc->set_debug(true);

if (!$wdc->open()) {
  print 'Error: could not open server connection';
  exit;
}

// check if server supports webdav rfc 2518
if (!$wdc->check_webdav()) {
  print 'Error: server does not support webdav or user/password may be wrong';
  exit;
}

$dir = $wdc->ls($wdc->_path);
?>
<h1>class_webdav_client Test-Suite:</h1><p>
Using method webdav_client::ls to get a listing of dir /:<br>
<table summary="ls" border="1">
<th>Filename</th><th>Size</th><th>Creationdate</th><th>Resource Type</th><th>Content Type</th><th>Activelock Depth</th><th>Activelock Owner</th><th>Activelock Token</th><th>Activelock Type</th>
<?php 
foreach($dir as $e) {
  $ts = $wdc->iso8601totime($e['creationdate']);
  $line = sprintf('<tr><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td><td>%s&nbsp;</td></tr>',
          $e['href'], 
          $e['getcontentlength'], 
          date('d.m.Y H:i:s',$ts),
          $e['resourcetype'],
          $e['getcontenttype'],
          $e['activelock_depth'],
          $e['activelock_owner'],
          $e['activelock_token'],
          $e['activelock_type']
          );
  print urldecode($line);
}
?>
</table>
<p>
<?php
$test_folder = '/~gregor/webdav/folder1';
print '<br>creating collection ' . $test_folder .' ...<br>';
#$http_status  = $wdc->mkcol($test_folder);
#print 'webdav server returns ' . $http_status . '<br>';

print 'removing collection just created using method webdav_client::delete ...<br>';
$http_status_array = $wdc->delete($test_folder);
print 'webdav server returns ' . $http_status_array['status'] . '<br>';

// put a file to webdav collection
$target_path = '/~gregor/webdav/pubcal.ics';
$http_status = $wdc->put($target_path, $text);
print 'webdav server returns ' . $http_status .'<br>';

$http_status_array = $wdc->delete($target_path);
print 'webdav server returns ' . $http_status_array['status'] . '<br>';


$wdc->close();
flush();

} else {
	$AppUI->setMsg( "eventIdError", UI_MSG_ERROR );
	$AppUI->redirect();
}
?>