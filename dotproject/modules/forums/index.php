<?php /* FORUMS $Id$ */
$AppUI->savePlace();

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

$f = dPgetParam( $_POST, 'f', 0 );


// get read denied projects
$deny = array();
$sql = "
SELECT project_id
FROM projects, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'projects'
	AND permission_item = project_id
	AND permission_value = 0
";
$deny1 = db_loadColumn( $sql );

// get read denied forums
$deny = array();
$sql = "
SELECT forum_id
FROM forums, permissions
WHERE permission_user = $AppUI->user_id
	AND permission_grant_on = 'forums'
	AND permission_item = forum_id
	AND permission_value = 0
";
$deny2 = db_loadColumn( $sql );

$max_msg_length = 30;
$sql = "
SELECT forum_id, forum_project, forum_description, forum_owner, forum_name, forum_moderated,
	forum_create_date, forum_last_date,
	COUNT(distinct t.message_id) forum_topics, COUNT(distinct r.message_id) forum_replies,
	user_username,
	project_name, project_color_identifier,
	SUBSTRING(l.message_body,1,$max_msg_length) message_body,
	LENGTH(l.message_body) message_length,
	watch_user,
	l.message_parent
FROM forums, users, projects, permissions
LEFT JOIN forum_messages t ON t.message_forum = forum_id AND t.message_parent = -1
LEFT JOIN forum_messages r ON r.message_forum = forum_id AND r.message_parent > -1
LEFT JOIN forum_messages l ON l.message_id = forum_last_id
LEFT JOIN forum_watch ON watch_user = $AppUI->user_id AND watch_forum = forum_id
WHERE user_id = forum_owner
	AND project_id = forum_project
# filter projects permissions
	AND permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'projects' AND permission_item = -1)
		OR (permission_grant_on = 'projects' AND permission_item = project_id)
		OR (permission_grant_on = 'forums' AND permission_item = -1)
		OR (permission_grant_on = 'forums' AND permission_item = forum_id)
		)"
.(count($deny1) > 0 ? "\nAND forum_project NOT IN (" . implode( ',', $deny1 ) . ')' : '')
.(count($deny2) > 0 ? "\nAND forum_id NOT IN (" . implode( ',', $deny2 ) . ')' : '')
;

//if (isset($project_id) && $project_id) {
//	$sql.= "\nAND forum_project = $project_id";
//}
switch ($f) {
	case 1:
		$sql .= "\nAND project_active=1 AND forum_owner = $AppUI->user_id";
		break;
	case 2:
		$sql .= "\nAND project_active=1 AND watch_user IS NOT NULL";
		break;
	case 3:
		$sql .= "\nAND project_active=1 AND project_owner = $AppUI->user_id";
		break;
	case 4:
		$sql .= "\nAND project_active=1 AND project_company = $AppUI->user_company";
		break;
	case 5:
		$sql .= "\nAND project_active=0";
		break;
	default:
		$sql .= "\nAND project_active=1";
		break;
}
$sql .= "\nGROUP BY forum_id\nORDER BY forum_project, forum_name";

$forums = db_loadList( $sql );
##echo "<pre>$sql</pre>".db_error();##

// setup the title block
$titleBlock = new CTitleBlock( 'Forums', 'support.png', $m, "$m.$a" );
$titleBlock->addCell(
	arraySelect( $filters, 'f', 'size="1" class="text" onChange="document.forum_filter.submit();"', $f , true ), '',
	'<form name="forum_filter" action="?m=forums" method="post">', '</form>'
);
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new forum').'">', '',
		'<form action="?m=forums&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();
?>

<table width="100%" cellspacing="1" cellpadding="2" border="0" class="tbl">
<form name="watcher" action="./index.php?m=forums&f=<?php echo $f;?>" method="post">
<tr>
	<th nowrap="nowrap">&nbsp;</th>
	<th nowrap="nowrap" width="25"><?php echo $AppUI->_( 'Watch' );?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_( 'Forum Name' );?></th>
	<th nowrap="nowrap" width="50" align="center"><?php echo $AppUI->_( 'Topics' );?></th>
	<th nowrap="nowrap" width="50" align="center"><?php echo $AppUI->_( 'Replies' );?></th>
	<th nowrap="nowrap" width="200"><?php echo $AppUI->_( 'Last Post Info' );?></th>
</tr>
<?php
$p ="";
foreach ($forums as $row) {
	$ts = db_dateTime2unix( $row['forum_last_date'] );
	if ($ts < 0) {
		$message_date = null;
	} else {
		$message_date = new CDate( $ts, "$df $tf" );
		$message_since = abs( $message_date->compareTo( new CDate() ) );
	}

	if($p != $row["forum_project"]) {
		$ts = db_dateTime2unix( $row["forum_create_date"], "$df" );
		$create_date = $ts < 0 ? null : new CDate( $ts );
?>
<tr>
	<td colspan="6" style="background-color:#<?php echo $row["project_color_identifier"];?>">
		<a href="?m=projects&a=view&project_id=<?php echo $row["forum_project"];?>">
			<font color=<?php echo bestColor( $row["project_color_identifier"] );?>>
			<strong><?php echo $row["project_name"];?></strong>
			</font>
		</a>
	</td>
</tr>
	<?php
		$p = $row["forum_project"];
	}?>
<tr>
	<td nowrap="nowrap" align="center">
	<?php if ($row["forum_owner"] == $AppUI->user_id) { ?>
		<a href="?m=forums&a=addedit&forum_id=<?php echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width="12" height="12"></a>
	<?php } ?>
	</td>

	<td nowrap="nowrap" align="center">
		<input type="checkbox" name="forum_<?php echo $row['forum_id'];?>" <?php echo $row['watch_user'] ? 'checked' : '';?> />
	</td>

	<td>
		<span style="font-size:10pt;font-weight:bold">
			<a href="?m=forums&a=viewer&forum_id=<?php echo $row["forum_id"];?>"><?php echo $row["forum_name"];?></a>
		</span>
		<br /><?php echo $row["forum_description"];?>
		<br /><font color=#777777><?php echo $AppUI->_( 'Owner' ).' '.$row["user_username"];?>,
		<?php echo $AppUI->_( 'Started' ).' '.$create_date->toString();?>
		</font>
	</td>
	<td nowrap="nowrap" align="center"><?php echo $row["forum_topics"];?></td>
	<td nowrap="nowrap" align="center"><?php echo $row["forum_replies"];?></td>
	<td width="200">
<?php
	if ($message_date !== null) {
		echo $message_date->toString().'<br /><font color=#999966>(';
		if ($message_since < 3600) {
			$str = sprintf( "%d ".$AppUI->_( 'minutes' ), $message_since/60 );
		} else if ($message_since < 48*3600) {
			$str = sprintf( "%d ".$AppUI->_( 'hours' ), $message_since/3600 );
		} else {
			$str = sprintf( "%d ".$AppUI->_( 'days' ), $message_since/(24*3600) );
		}
		printf( $AppUI->_('%s ago'), $str );
		echo ') </font><br />&gt;&nbsp;<a href="?m=forums&a=viewer&forum_id='.$row['forum_id'].'&message_id='.$row['message_parent'].'"><font color=#777777>'.$row['message_body'];
		echo $row['message_length'] > $max_msg_length ? '...' : '';
		echo '</font></a>';
	} else {
		echo "No posts";
	}
?>
	</td>
</tr>

<?php } ?>
</table>

<table width="100%" cellspacing="1" cellpadding="0" border="0">
	<input type="hidden" name="dosql" value="do_watch_forum" />
	<input type="hidden" name="watch" value="forum" />
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align="left">
		<input type="submit" class=button value="<?php echo $AppUI->_( 'update watches' );?>" />
	</td>
</tr>
</form>
</table>
