<?php /* FORUMS $Id$ */
$AppUI->savePlace();

$df = $AppUI->getPref( 'SHDATEFORMAT' );
$tf = $AppUI->getPref( 'TIMEFORMAT' );

$f = isset( $_GET['f'] ) ? $_GET['f'] : 0;

//Forum index.php
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
FROM forums, users, projects
LEFT JOIN forum_messages t ON t.message_forum = forum_id AND t.message_parent = -1
LEFT JOIN forum_messages r ON r.message_forum = forum_id AND r.message_parent > -1
LEFT JOIN forum_messages l ON l.message_id = forum_last_id
LEFT JOIN forum_watch ON watch_user = $AppUI->user_id AND watch_forum = forum_id
WHERE user_id = forum_owner
	AND project_id = forum_project
";
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
?>
<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br />
<table width="98%" cellspacing="1" cellpadding="0" border="0">
<tr>
	<td><img src="./images/icons/communicate.gif" alt="" border="0" width="42" height="42" /></td>
	<td nowrap width="100%"><h1><?php echo $AppUI->_( 'Forums' );?></h1></td>
<form name="forum_filter" method=GET action="./index.php">
<input type=hidden name=m value=forums>
	<td nowrap>
<?php
	echo arraySelect( $filters, 'f', 'size=1 class=text onChange="document.forum_filter.submit();"', $f , true );
?>
	</td>
</form>
	<td><img src="images/shim.gif" width=5 height=5></td>
<form name="searcher" action="./index.php?m=files&a=search" method="post">
<input type="hidden" name="dosql" value="searchfiles">
	<td align="right"><input class="button" type="text" name="s" maxlength="30" size="20" value="<?php echo $AppUI->_('Not implemented');?>" disabled></td>
	<td><img src="images/shim.gif" width=5 height=5></td>
	<td><input class=button type="submit" value="<?php echo $AppUI->_( 'search' );?>" disabled></td>
</form>
	<td><img src="images/shim.gif" width=5 height=5></td>
	<td align="right">
	<?php if (!$denyEdit) { ?>
		<input type="button" class=button value="<?php echo $AppUI->_( 'add new forum' );?>" onClick="javascript:window.location='./index.php?m=forums&a=addedit';">
	<?php } ?>
	</td>
</tr>
</table>

<table width="98%" cellspacing="1" cellpadding="2" border="0" class="tbl">
<form name="watcher" action="./index.php?m=forums&f=<?php echo $f;?>" method="post">
<tr>
	<th nowrap>&nbsp;</th>
	<th nowrap width=25><?php echo $AppUI->_( 'Watch' );?></th>
	<th nowrap><?php echo $AppUI->_( 'Forum Name' );?></th>
	<th nowrap width=50 align=center><?php echo $AppUI->_( 'Topics' );?></th>
	<th nowrap width=50 align=center><?php echo $AppUI->_( 'Replies' );?></th>
	<th nowrap width=200><?php echo $AppUI->_( 'Last Post Info' );?></th>
</tr>
<?php
$p ="";
foreach ($forums as $row) {
	if ($row["forum_last_date"]) {
		$message_date = CDate::fromDateTime( $row["forum_last_date"] );
		$message_date->setFormat( "$df $tf" );
		$message_since = abs( $message_date->compareTo( new CDate() ) );
	}
	if($p != $row["forum_project"]) {
		$create_date = CDate::fromDateTime( $row["forum_create_date"] );
		$create_date->setFormat( "$df" );
?>
<tr>
	<td colspan=6 style="background-color:#<?php echo $row["project_color_identifier"];?>">
		<a href="./index.php?m=projects&a=view&project_id=<?php echo $row["forum_project"];?>"><font color=<?php echo bestColor( $row["project_color_identifier"] );?>><strong><?php echo $row["project_name"];?></strong></font></a>
	</td>
</tr>
	<?php
		$p = $row["forum_project"];
	}?>
<tr>
	<td nowrap align=center>
	<?php if($row["forum_owner"] == $AppUI->user_id){?>
		<a href="?m=forums&a=addedit&forum_id=<?php echo $row["forum_id"];?>"><img src="./images/icons/pencil.gif" alt="expand forum" border="0" width=12 height=12></a>
	<?php }?>
	</td>

	<td nowrap align=center>
		<input type="checkbox" name="forum_<?php echo $row['forum_id'];?>" <?php echo $row['watch_user'] ? 'checked' : '';?>>
	</td>

	<td>
		<span style="font-size:10pt;font-weight:bold"><a href="?m=forums&a=viewer&forum_id=<?php echo $row["forum_id"];?>"><?php echo $row["forum_name"];?></a></span>
		<br /><?php echo $row["forum_description"];?>
		<br /><font color=#777777><?php echo $AppUI->_( 'Owner' ).' '.$row["user_username"];?>,
		<?php echo $AppUI->_( 'Started' ).' '.$create_date->toString();?>
		</font>
	</td>
	<td nowrap align=center><?php echo $row["forum_topics"];?></td>
	<td nowrap align=center><?php echo $row["forum_replies"];?></td>
	<td width=200>
<?php if ($row["forum_last_date"]) {
		echo $message_date->toString().'<br /><font color=#999966>(';
		if ($message_since < 3600) {
			$str = sprintf( "%d ".$AppUI->_( 'minutes' ), $message_since/60 );
		} else if ($message_since < 48*3600) {
			$str = sprintf( "%d ".$AppUI->_( 'hours' ), $message_since/3600 );
		} else {
			$str = sprintf( "%d ".$AppUI->_( 'days' ), $message_since/(24*3600) );
		}
		printf($AppUI->_('%s ago'), $str);
		echo ') </font><br />&gt;&nbsp;<a href="?m=forums&a=viewer&forum_id='.$row['forum_id'].'&message_id='.$row['message_parent'].'"><font color=#777777>'.$row['message_body'];
		echo $row['message_length'] > $max_msg_length ? '...' : '';
		echo '</font></a>';
	} else {
		echo "No posts";
	}
?>
	</td>
</tr>

<?php }?>
</table>

<table width="98%" cellspacing="1" cellpadding="0" border="0">
<input type="hidden" name="dosql" value="watch_forum">
<input type="hidden" name="watch" value="forum">
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td align="left">
		<input type="submit" class=button value="<?php echo $AppUI->_( 'update watches' );?>">
	</td>
</tr>
</form>
</table>
