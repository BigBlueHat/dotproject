<?php  /* FORUMS $Id$ */
$AppUI->savePlace();
$sort = dPgetParam($_REQUEST, 'sort', 'asc');
$viewtype = dPgetParam($_REQUEST, 'viewtype', 'normal');
$hideEmail = dPgetConfig('hide_email_addresses', false );

$sql = "
SELECT forum_messages.*,
	contact_first_name, contact_last_name, contact_email, user_username,
	forum_moderated, visit_user
FROM forum_messages, forums
LEFT JOIN users ON message_author = users.user_id
LEFT JOIN contacts ON contact_id = user_contact
LEFT JOIN forum_visits ON visit_user = '{$AppUI->user_id}' AND visit_forum = '$forum_id'
AND visit_message = forum_messages.message_id
WHERE forum_id = message_forum
	AND (message_id = $message_id OR message_parent = $message_id)" .
  ( (@$dPconfig['forum_descendent_order'] || dPgetParam($_REQUEST,'sort',0)) ? " ORDER BY message_date $sort" : "" );

//echo "<pre>$sql</pre>";
$messages = db_loadList( $sql );

$crumbs = array();
$crumbs["?m=forums"] = "forums list";
$crumbs["?m=forums&a=viewer&forum_id=$forum_id"] = "topics for this forum";
?>
<script language="javascript">
<?php
if ($viewtype != 'normal')
{
?>
        function toggle(id)
        {
<?php
if ($viewtype == 'single')
{
?>
                var elems = document.getElementsByTagName("div");
                for (var i=0; i<elems.length; i++)
                  if (elems[i].className == 'message')
                        elems[i].style.display = 'none';
                document.getElementById(id).style.display = 'block';

<?php 
}
else if ($viewtype=='short')
{
?>
                vista = (document.getElementById(id).style.display == 'none') ? 'block' : 'none';
                document.getElementById(id).style.display = vista;
<?php
}
?>
        }
<?php 
}
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id){
	var form = document.messageForm;
	if (confirm( "<?php echo $AppUI->_('forumsDelete');?>" )) {
		form.del.value = 1;
		form.message_id.value = id;
		form.submit();
	}
}
<?php } ?>
</script>
<?php
$thispage = "?m=$m&a=viewer&forum_id=$forum_id&message_id=$message_id&sort=$sort";
// $thispage = $_PHP['self'];
?>

<table width="98%" cellspacing="1" cellpadding="2" border="0" align="center">
<tr>
	<td><?php echo breadCrumbs( $crumbs );?></td>
        <td>
<form action="<?php echo $thispage; ?>" method="post">
        View: 
        <input type="radio" name="viewtype" value="normal" <?php echo ($viewtype == 'normal')?'checked':'';?> onClick="this.form.submit();" />Normal
        <input type="radio" name="viewtype" value="short" <?php echo ($viewtype == 'short')?'checked':'';?> onClick="this.form.submit();" />Collapsed
        <input type="radio" name="viewtype" value="single" <?php echo ($viewtype == 'single')?'checked':'';?> onClick="this.form.submit();" />Single Message at a time
</form>
        </td>
	<td align="right">
        <?php $sort = ($sort == 'asc')?'desc':'asc'; ?>
		<input type="button" class=button value="<?php echo $AppUI->_('Sort By Date') . ' (' . $sort . ')'; ?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=<?php echo $message_id;?>&sort=<?php echo $sort; ?>'" />
	<?php if ($canEdit) { ?>
		<input type="button" class=button value="<?php echo $AppUI->_('Post Reply');?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_parent=<?php echo $message_id;?>&post_message=1';" />
		<input type="button" class=button value="<?php echo $AppUI->_('New Topic');?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=0&post_message=1';" />
	<?php } ?>
	</td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="1" width="98%" class="tbl" align="center">
<form name="messageForm" method="POST" action="?m=forums&forum_id=<?php echo $forum_id;?>">
	<input type="hidden" name="dosql" value="do_post_aed" />
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="message_id" value="0" />
</form>
<tr>
<?php 
if ($viewtype != 'short')
	echo '<th nowrap>' .$AppUI->_('Author') .':</th>';
echo '
	<th width="'.(($viewtype=='single')?'60':'100').'%">'. $AppUI->_('Message').':</th>';
?>
</tr>

<?php 
$x = false;

$date = new CDate();
$pdfdata = array();
$pdfhead = array('Date', 'User', 'Message');

if ($viewtype == 'single')
{
	$s = '';
        $first = true;
}

$new_messages = array();

foreach ($messages as $row) {
        // Find the parent message - the topic.
        if ($row['message_id'] == $message_id)
                $topic = $row['message_title'];
	$sql = "
	SELECT DISTINCT contact_email, contact_first_name, contact_last_name, user_username
	FROM users, forum_messages
        LEFT JOIN contacts ON contact_id = user_contact
	WHERE users.user_id = ".$row["message_editor"];

	$editor = db_loadList( $sql );

	$date = intval( $row["message_date"] ) ? new CDate( $row["message_date"] ) : null;
if ($viewtype != 'single')
	$s = '';
	$style = $x ? 'background-color:#eeeeee' : '';

//!!! Different table building for the three different views
// To be cleaned up, and reuse common code at later stage.
if ($viewtype =='normal')
{
        $s .= "<tr>";

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	if ( ! $hideEmail) {
			$s .= '<a href="mailto:'.$row["contact_email"].'">';
	}
	$s .= '<font size="2">'.$row['contact_first_name'].' '.$row['contact_last_name'].'</font>';
	if ( ! $hideEmail) {
		$s .= '</a>';
	}
	if (sizeof($editor)>0) {
		$s .= '<br/>&nbsp;<br/>'.$AppUI->_('last edited by');
		$s .= ':<br/>';
		if ( !$hideEmail) {
			$s .= '<a href="mailto:'.$editor[0]["contact_email"].'">';
		}
		$s .= '<font size="1">'.$editor[0]['contact_first_name'].' '.$editor[0]['contact_last_name'].'</font>';
		if (! $hideEmail) {
			$s .= '</a>';
		}
	}
	if ($row['visit_user'] != $AppUI->user_id) {
		$s .= "<br/>&nbsp;" . dPshowImage('images/icons/stock_new_small.png');
		$new_messages[] = $row['message_id'];
	}
	$s .= '</td>';
	$s .= '<td valign="top" style="'.$style.'">';
	$s .= '<font size="2"><strong>'.$row["message_title"].'</strong><hr size=1>';
	$s .= str_replace( chr(13), "&nbsp;<br />", $row["message_body"] );
	$s .= '</font></td>';

	$s .= '</tr><tr>';

	$s .= '<td valign="top" style="'.$style.'" nowrap="nowrap">';
	$s .= '<img src="./images/icons/posticon.gif" alt="date posted" border="0" width="14" height="11">'.$date->format( "$df $tf" ).'</td>';
	$s .= '<td valign="top" align="right" style="'.$style.'">';

	//the following users are allowed to edit/delete a forum message: 1. the forum creator  2. a superuser with read-write access to 'all' 3. the message author
	if ( ($canEdit && $AppUI->user_id == $row['forum_moderated']) || (!empty($perms['all']) && !getDenyEdit('all')) || ($canEdit && $AppUI->user_id == $row['message_author'])) {
		$s .= '<table cellspacing="0" cellpadding="0" border="0"><tr>';
	// edit message
		$s .= '<td><a href="./index.php?m=forums&a=viewer&post_message=1&forum_id='.$row["message_forum"].'&message_parent='.$row["message_parent"].'&message_id='.$row["message_id"].'" title="'.$AppUI->_( 'Edit' ).' '.$AppUI->_( 'Message' ).'">';
		$s .= dPshowImage( './images/icons/stock_edit-16.png', '16', '16' );
		$s .= '</td><td>';
	// delete message
		$s .= '<a href="javascript:delIt('.$row["message_id"].')" title="'.$AppUI->_( 'delete' ).'">';
		$s .= dPshowImage( './images/icons/stock_delete-16.png', '16', '16' );
		$s .= '</a>';
		$s .= '</td></tr></table>';

	}
	$s .= '</td>';

	$s .= '</tr>';
}
else if ($viewtype == 'short')
{
        $s .= "<tr>";

        $s .= '<td valign="top" style="'.$style.'" >';
        $s .= '<a href="mailto:'.$row["contact_email"].'">';
        $s .= '<font size="2">'.$row['contact_first_name'].' '.$row['contact_last_name'].'</font></a>';
        $s .= ' (' . $date->format( "$df $tf" ) . ') ';
        if (sizeof($editor)>0) {
                $s .= '<br/>&nbsp;<br/>'.$AppUI->_('last edited by');
                $s .= ':<br/><a href="mailto:'.$editor[0]["contact_email"].'">';
                $s .= '<font size="1">'.$editor[0]['contact_first_name'].' '.$editor[0]['contact_last_name'].'</font></a>';
        }
  $s .= '<a name="'.$row['message_id'].'" href="#'.$row['message_id'].'" onClick="toggle('.$row['message_id'].')">';
        $s .= '<span size="2"><strong>'.$row["message_title"].'</strong></span></a>';
        $s .= '<div class="message" id="'.$row['message_id'].'" style="display: none">';
        $s .= str_replace( chr(13), "&nbsp;<br />", $row["message_body"] );
        $s .= '</div></td>';

        $s .= '</tr>';
}
else if ($viewtype == 'single')
{
        $s .= "<tr>";

        $s .= '<td valign="top" style="'.$style.'">';
        $s .= $date->format( "$df $tf" ).' - ';
        $s .= '<a href="mailto:'.$row["contact_email"].'">';
        $s .= '<font size="2">'.$row['contact_first_name'].' '.$row['contact_last_name'].'</font></a>';
        $s .= '<br />';
        if (sizeof($editor)>0) {
                $s .= '<br/>&nbsp;<br/>'.$AppUI->_('last edited by');
                $s .= ':<br/><a href="mailto:'.$editor[0]["contact_email"].'">';
                $s .= '<font size="1">'.$editor[0]['contact_first_name'].' '.$editor[0]['contact_last_name'].'</font></a>';
        }
  $s .= '<a href="#" onClick="toggle('.$row['message_id'].')">';
        $s .= '<span size="2"><strong>'.$row["message_title"].'</strong></span></a>';
        $side .= '<div class="message" id="'.$row['message_id'].'" style="display: none">';
        $side .= str_replace( chr(13), "&nbsp;<br />", $row["message_body"] );
        $side .= '</div>';
        $s .= '</td>';
        if ($first)
        {
                $s .= '<td rowspan="'.count($messages).'" valign="top">';
                echo $s;
                $s = '';
                $first = false;
        }

        $s .= '</tr>';
}

if ($viewtype != 'single')
	echo $s;
	$x = !$x;

        $pdfdata[] = array($row['message_date'],
$row['contact_first_name'] . ' ' . $row['contact_last_name'],
'<b>' . $row['message_title'] . '</b>
' . $row['message_body']);
}
if ($viewtype == 'single')
        echo $side . '</td>' . $s;
?>
</table>


<?php //PDF Creation
$font_dir = $dPconfig['root_dir']."/lib/ezpdf/fonts";
$temp_dir = $dPconfig['root_dir']."/files/temp";
$base_url  = $dPconfig['base_url'];
require( $AppUI->getLibraryClass( 'ezpdf/class.ezpdf' ) );

$pdf = &new Cezpdf($paper='A4',$orientation='portrait');
$pdf->ezSetCmMargins( 1, 2, 1.5, 1.5 );
$pdf->selectFont( "$font_dir/Helvetica.afm" );
$pdf->ezText('Project: ' . $forum['project_name']. '   Forum: '.$forum['forum_name'] );
$pdf->ezText('Topic: ' . $topic);
$pdf->ezText('');
                $options = array(
                        'showLines' => 1,
                        'showHeadings' => 1,
                        'fontSize' => 8,
                        'rowGap' => 2,
                        'colGap' => 5,
                        'xPos' => 50,
                        'xOrientation' => 'right',
                        'width'=>'500'
                );

$pdf->ezTable( $pdfdata, $pdfhead, NULL, $options );

if ($fp = fopen( "$temp_dir/forum_$AppUI->user_id.pdf", 'wb' )) {
                        fwrite( $fp, $pdf->ezOutput() );
                        fclose( $fp );
                        $crumbs["$base_url/files/temp/forum_$AppUI->user_id.pdf"] = "view PDF file";
                        //echo "<a href=\"$base_url/files/temp/forum_$AppUI->user_id.pdf\" target=\"pdf\">";
                        //echo $AppUI->_( "View PDF File" );
                        //echo "</a>";
                } else {
                        echo "Could not open file to save PDF.  ";
                        if (!is_writable( $temp_dir )) {
                                "The files/temp directory is not writable.  Check your file system permissions.";
                        }
                }
?>

<table border=0 cellpadding=2 cellspacing=1 width="98%" >
<tr>
	<td><?php echo breadCrumbs( $crumbs );?></td>
	<td align="right">
		<input type="button" class=button value="<?php echo $AppUI->_('Sort By Date') . ' (' . $sort . ')'; ?>" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=<?php echo $message_id;?>&sort=<?php echo $sort; ?>'" />
	<?php if ($canEdit) { ?>
		<input type="button" class="button" value="<?php echo $AppUI->_('Post Reply');?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_parent=<?php echo $message_id;?>&post_message=1';" />
		<input type="button" class="button" value="<?php echo $AppUI->_('New Topic');?>" onclick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&message_id=0&post_message=1';" />
	<?php } ?>
	</td>
</tr>
</table>
<?php
  // Now we need to update the forum visits with the new messages so they don't show again.
  foreach ($new_messages as $msg_id) {
    $sql = "insert into forum_visits (visit_user, visit_forum, visit_message)
    values ( '{$AppUI->user_id}', '$forum_id', '$msg_id')";
    db_exec($sql);
	}
?>
