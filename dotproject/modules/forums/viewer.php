<?php
//view posts
$forum_id = isset($_GET["forum_id"]) ? $_GET["forum_id"] : 0;
$message_id = isset($_GET["message_id"]) ? $_GET["message_id"] : 0;
$post_message = isset($_GET["post_message"]) ? $_GET["post_message"] : 0;
$f = dpGetParam( $_POST, 'f', 0 );

// check permissions
$canRead = !getDenyRead( $m, $forum_id );
$canEdit = !getDenyEdit( $m, $forum_id );

if (!$canRead || ($post_message & !$canEdit)) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

$df = $AppUI->getPref('SHDATEFORMAT');
$tf = $AppUI->getPref('TIMEFORMAT');

$sql = "
SELECT forum_id, forum_project,	forum_description, forum_owner, forum_name,
	forum_create_date, forum_last_date, forum_message_count, forum_moderated,
	user_first_name, user_last_name,
	project_name, project_color_identifier
FROM forums, users, projects 
WHERE user_id = forum_owner 
	AND forum_id = $forum_id 
	AND forum_project = project_id
";
db_loadHash( $sql, $forum );
$forum_name = $forum["forum_name"];
echo db_error();

$start_date = $forum["forum_create_date"] ? new CDate( db_dateTime2unix( $forum["forum_create_date"] ) ) : 0;
if ($start_date) {
	$start_date->setFormat( $df );
}

// setup the title block
$titleBlock = new CTitleBlock( 'Forum', 'communicate.gif', $m, "$m.$a" );
$titleBlock->addCell(
	arraySelect( $filters, 'f', 'size="1" class="text" onchange="document.filterFrm.submit();"', $f , true), '',
	'<form action="?m=forums&a=viewer&forum_id='.$forum_id.'" method="post" name="filterFrm">', '</form>'
);
$titleBlock->show();
?>
<table width="100%" cellspacing="0" cellpadding="2" border="0" class="std">
<tr>
	<td height="20" colspan="3" style="border: outset #D1D1CD 1px;background-color:#<?php echo $forum["project_color_identifier"];?>">
		<font size="2" color=<?php echo bestColor( $forum["project_color_identifier"] );?>><strong><?php echo @$forum["forum_name"];?></strong></font>
	</td>
</tr>
<tr>
	<td align="left" nowrap><?php echo $AppUI->_( 'Related Project' );?>:</td>
	<td nowrap><strong><?php echo $forum["project_name"];?></strong></td>
	<td valign="top" width="50%" rowspan=99><strong><?php echo $AppUI->_( 'Description' );?>:</strong><br /><?php echo @str_replace(chr(13), "&nbsp;<br />",$forum["forum_description"]);?></td>
</tr>
<tr>
	<td align="left"><?php echo $AppUI->_( 'Owner' );?>:</td>
	<td nowrap><?php
		echo $forum["user_first_name"].' '.$forum["user_last_name"];
		if (intval( $forum["forum_id"] ) <> 0) {
			echo " (".$AppUI->_( 'moderated' ).") ";
		}?>
	</td>
</tr>
<tr>
	<td align="left"><?php echo $AppUI->_( 'Created On' );?>:</td>
	<td nowrap><?php echo $start_date ? $start_date->toString() : '-';?></td>
</tr>
</table>

<?php
if($post_message){
	include("./modules/forums/post_message.php");
} else if($message_id == 0) {
	include("./modules/forums/view_topics.php");
} else {
	include("./modules/forums/view_messages.php");
}
?>