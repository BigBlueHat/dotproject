<?php

// deny all but system admins
$canEdit = !getDenyEdit( 'system' );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$AppUI->savePlace();

if (isset( $_POST['forcewatch'] ) && isset( $_POST['forcesubmit'] ) ) {		// insert row into forum_watch for forcing Watch
	$sql = "INSERT INTO forum_watch (watch_user,watch_forum,watch_topic) VALUES (0,0,0)";
	if (!db_exec($sql)) {
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
	} else {
		$AppUI->setMsg( "Watch Forced", UI_MSG_OK );
	}
	$AppUI->redirect( 'm=forums&a=configure' );

}
elseif (isset( $_POST['forcesubmit'] ) && !isset( $_POST['forcewatch'] ) ) {	// delete row from forum_watch for unorcing Watch
	$sql = "DELETE FROM forum_watch WHERE watch_user = 0 AND watch_forum = 0 AND watch_topic = 0";
	if (!db_exec($sql)) {
		$AppUI->setMsg( db_error(), UI_MSG_ERROR );
	}
	else {
	$AppUI->setMsg( "Watch Unforced", UI_MSG_OK );
	}
	$AppUI->redirect( 'm=forums&a=configure' );

}


// SQL-Query to check if the message should be delivered to all users (forced) (checkbox)
$sql = "SELECT * FROM forum_watch WHERE watch_user = 0 AND watch_forum = 0 AND watch_topic = 0";
$resAll = db_exec( $sql );

if (db_num_rows( $resAll ) >= 1)	// message has to be sent to all users
{
	$watchAll = true;
}


// setup the title block
$titleBlock = new CTitleBlock( 'Configure Forums Module', 'support.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->addCrumb( "?m=system&a=viewmods", "modules list" );
$titleBlock->show();
?>

<script language="javascript">
function submitFrm( frmName ) {

	eval('document.'+frmName+'.submit();');

}
</script>
<form name="frmForceWatch" method="post" action="?m=forums&a=configure">
<input type="hidden" name="forcesubmit" value="true" />
<input type="checkbox" name="forcewatch" value="dod" <?php echo $watchAll ? 'checked' : '';?> onclick="javascript:submitFrm('frmForceWatch');"/>
<?php echo $AppUI->_('forumForceWatch');?>
</form>

