<?php /* $Id */
$AppUI->savePlace();
// temporary until tranlated!
$AppUI->setWarning( false );

// pull all the key types
$sql = "SELECT syskey_id,syskey_name FROM syskeys ORDER BY syskey_name";
$keys = arrayMerge( array( 0 => '- Select Type -' ), db_loadHashList( $sql ) );

$sql = "SELECT * FROM syskeys, sysvals WHERE sysval_key_id = syskey_id ORDER BY sysval_title";
$values = db_loadList( $sql );

$sysval_id = isset( $_GET['sysval_id'] ) ? $_GET['sysval_id'] : 0;

$crumbs = array();
$crumbs["?m=system"] = "System Admin";
?>

<script language="javascript">
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.sysKeyFrm;
		f.del.value = 1;
		f.sysval_id.value = id;
		f.submit();
	}
}
</script>

<table cellspacing="1" cellpadding="1" border="0" width="98%">
<tr>
	<td><img src="<?php echo dPfindImage( 'preference.gif', $m );?>" alt="" border="0"></td>
	<td nowrap="nowrap"><h1><?php echo $AppUI->_('System Lookup Values');?></h1></td>
	<td nowrap="nowrap"><img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
	<td valign="top" align="right" width="100%"></td>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_SYSVALS_IDX' );?></td>
</tr>
</table>

<table cellspacing="0" cellpadding="4" border="0" width="98%">
<tr>
	<td width="50%" nowrap="nowrap"><?php echo breadCrumbs( $crumbs );?></td>
</tr>
</table>

<span style="color:red"><strong>Note this function is still in development and testing phase</strong></span>

<table border="0" cellpadding="2" cellspacing="1" width="98%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Key Type');?></th>
	<th><?php echo $AppUI->_('Title');?></th>
	<th colspan="2"><?php echo $AppUI->_('Values');?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow($id=0, $key=0, $title='', $value='') {
	GLOBAL $canEdit, $sysval_id, $CR, $AppUI, $keys;
	$s = '<tr>'.$CR;
	if ($sysval_id == $id && $canEdit) {
	// edit form
		$s .= '<form name="sysValFrm" method="post" action="?m=system&u=syskeys&a=do_sysval_aed">'.$CR;
		$s .= '<input type="hidden" name="del" value="0" />'.$CR;
		$s .= '<input type="hidden" name="sysval_id" value="'.$id.'" />'.$CR;

		$s .= '<td>&nbsp;</td>';
		$s .= '<td valign="top">'.arraySelect( $keys, 'sysval_key_id', 'size="1" class="text"', $key).'</td>';
		$s .= '<td valign="top"><input type="text" name="sysval_title" value="'.$title.'" class="text" /></td>';
		$s .= '<td valign="top"><textarea name="sysval_value" class="small" rows="5" cols="40">'.$value.'</textarea></td>';
		$s .= '<td><input type="submit" value="'.$AppUI->_($id ? 'edit' : 'add').'" class="button" /></td>';
		$s .= '<td>&nbsp;</td>';
	} else {
		$s .= '<td width="12" valign="top">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=syskeys&sysval_id='.$id.'"><img src="./images/icons/pencil.gif" alt="edit" border="0" width="12" height="12"></a>';
			$s .= '</td>'.$CR;
		}
		$s .= '<td valign="top">'.$keys[$key].'</td>'.$CR;
		$s .= '<td valign="top">'.$title.'</td>'.$CR;
		$s .= '<td valign="top" colspan="2">'.$value.'</td>'.$CR;
		$s .= '<td valign="top" width="16">';
		if ($canEdit) {
			$s .= '<a href="#" onclick="return delIt('.$id.')"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="'.$AppUI->_('delete').'" border="0"></a>';
		}
		$s .= '</td>'.$CR;
	}
	$s .= '</tr>'.$CR;
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($values as $row) {
	echo showRow( $row['sysval_id'], $row['sysval_key_id'], $row['sysval_title'], $row['sysval_value'] );
}
// add in the new key row:
if ($sysval_id == 0) {
	echo showRow();
}
?>
</table>
