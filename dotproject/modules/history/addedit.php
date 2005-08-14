<?php /* $Id$ */
$history_id = defVal( @$_GET["history_id"], 0);

/*
// check permissions
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}
*/
$action = @$_REQUEST["action"];
$q = new DBQuery;
if($action) {
	$history_description = dPgetParam($_POST, 'history_description', '');
	$history_project = dPgetParam($_POST, 'history_project', '');
	$userid = $AppUI->user_id;
	
	if( $action == 'add' ) {
		$q->addTable('history');
		$q->addInsert('history_table', "history");
		$q->addInsert('history_action', "add");
		$q->addInsert( 'history_date', str_replace( "'", '', $db->DBTimeStamp( time() ) ) );
		$q->addInsert('history_description', $history_description);
		$q->addInsert('history_user', $userid);
		$q->addInsert('history_project', $history_project);
		$okMsg = 'History added';
	} else if ( $action == 'update' ) {
		$q->addTable('history');
		$q->addUpdate('history_description', $history_description);
		$q->addUpdate('history_project', $history_project);
		$q->addWhere('history_id ='.$history_id);
		$okMsg = 'History updated';
	} else if ( $action == 'del' ) {
		$q->setDelete('history');
		$q->addWhere('history_id ='.$history_id);
		$okMsg = 'History deleted';				
	}
	if(!$q->exec()) {
		$AppUI->setMsg( db_error() );
	} else {	
		$AppUI->setMsg( $okMsg );
                if ($action == 'add')
			$q->clear();
			$q->addTable('history');
			$q->addUpdate('history_item = history_id');
			$q->addWhere('history_table = \'history\'');
			$okMsg = 'History deleted';
	}
	$q->clear();
	$AppUI->redirect();
}

// pull the projects list
$q->addTable('projects');
$q->addQuery('project_id, project_name');
$q->addOrder('project_name');
$projects = arrayMerge( array( 0 => '('.$AppUI->_('any', UI_OUTPUT_RAW).')' ), $q->loadHashList() );

// pull the history
$q->addTable('history');
$q->addQuery('*');
$q->addWhere('history_id ='.$history_id);
$sql = $q->prepare();
$q->clear();
db_loadHash( $sql, $history );

$tpl->assign('history_id', $history_id);
$tpl->assign('projects', $projects);
$tpl->assign('current_url', $AppUI->getPlace());
$tpl->displayAddEdit($history);
?>