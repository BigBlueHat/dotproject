<?php /* SYSKEYS $Id$*/
$q = new DBQuery;
$q->addQuery('*');
$q->addTable('syskeys');
$q->addOrder('syskey_name');
$keys = $q->loadList();

$syskey_id = isset( $_GET['syskey_id'] ) ? $_GET['syskey_id'] : 0;

$titleBlock = new CTitleBlock( 'System Lookup Keys', 'myevo-weather.png', $m, "$m.$a" );
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();
?>
<script type="text/javascript" language="javascript">
<!--
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.sysKeyFrm;
		f.del.value = 1;
		f.syskey_id.value = id;
		f.submit();
	}
}
<?php } ?>
-->
</script>

<form name="sysKeyFrm" method="post" action="?m=system&amp;u=syskeys&amp;a=do_syskey_aed">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="syskey_id" value="<?php echo $syskey_id; ?>" />

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Name');?></th>
	<th colspan="2"><?php echo $AppUI->_('Label');?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow($id=0, $name='', $label='') {
	GLOBAL $canEdit, $syskey_id, $CR, $AppUI;
	$s = '<tr>'.$CR;
	if ($syskey_id == $id && $canEdit) {
		$s .= '
	<td>&nbsp;</td>
	<td>
		<input type="text" name="syskey_name" value="'.$name.'" class="text" />
	</td>
	<td>
		<textarea name="syskey_label" class="small" rows="2" cols="40">'.$label.'</textarea>
	</td>
	<td>
		<input type="submit" value="'.$AppUI->_($id ? 'edit' : 'add').'" class="button" />
	</td>
	<td>&nbsp;</td>';
	} else {
		$s .= '<td width="12">';
		if ($canEdit) {
			$s .= '<a href="?m=system&amp;u=syskeys&amp;a=keys&amp;syskey_id='.$id.'">
			<img src="./images/icons/pencil.gif" alt="edit" border="0" width="12" height="12" /></a>';
			$s .= '</td>'.$CR;
		}
		$s .= '<td>'.$name.'</td>'.$CR;
		$s .= '<td colspan="2">'.htmlentities($label).'</td>'.$CR;
		$s .= '<td width="16">';
		if ($canEdit) {
			$s .= '<a href="javascript:delIt('.$id.')">
					<img src="./images/icons/trash.gif" width="16" height="16" alt="'.$AppUI->_('delete').'" border="0" /></a>';
		}
		$s .= '</td>'.$CR;
	}
	$s .= '</tr>'.$CR;
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($keys as $row)
	echo showRow( $row['syskey_id'], $row['syskey_name'], $row['syskey_label'] );

// add in the new key row:
if ($syskey_id == 0)
	echo showRow();
?>
</table>
</form>
