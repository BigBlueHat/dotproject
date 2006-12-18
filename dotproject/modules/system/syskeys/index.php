<?php /* SYSKEYS $Id$ */
$AppUI->savePlace();

$q = new DBQuery;
$q->addTable('sysvals');
$q->addQuery('sysval_id, sysval_title, sysval_value_id, sysval_value');
$rs = $q->Exec();

$sysval_rows = Array();

while($r = $rs->fetchRow())
{
	if (!array_key_exists($r['sysval_title'], $sysval_rows))
	{
		$sysval_rows[$r['sysval_title']] = Array();
	}
	$sysval_rows[$r['sysval_title']][] = $r;
}

$sysval_id = isset( $_GET['sysval_id'] ) ? $_GET['sysval_id'] : 0;
$sysval_add_group = isset( $_GET['sysval_add_group'] ) ? $_GET['sysval_add_group'] : NULL;

$titleBlock = new CTitleBlock( 'System Lookup Values', 'myevo-weather.png', $m, "$m.$u.$a" );
$titleBlock->addCrumb('?m=system', 'System Admin');
$titleBlock->show();

$tpl->assign('canEdit', $canEdit);
$tpl->assign('sysval_rows', $sysval_rows);
$tpl->assign('sysval_id', $sysval_id);
$tpl->assign('sysval_add_group', $sysval_add_group);
$tpl->displayFile('syskeys/index');

/*
function showRow($id=0, $key=0, $title='', $value='') {
	GLOBAL $canEdit, $sysval_id, $CR, $AppUI, $keys;
	$s = '<tr>'.$CR;
	if ($sysval_id == $id && $canEdit) {
	// edit form
		$s .= '
	<td>&nbsp;</td>
	<td valign="top">'.arraySelect( $keys, 'sysval_key_id', 'size="1" class="text"', $key).'</td>
	<td valign="top">
		<input type="text" name="sysval_title" value="'.dPformSafe($title).'" class="text" />
	</td>
	<td valign="top">
		<textarea name="sysval_value" class="small" rows="5" cols="40">'.$value.'</textarea>
	</td>
	<td>
		<input type="submit" value="'.$AppUI->_($id ? 'edit' : 'add').'" class="button" />
	</td>
	<td>&nbsp;</td>';
	} else {
		$s .= '
	<td width="12" valign="top">';
		if ($canEdit) {
			$s .= '<a href="?m=system&amp;u=syskeys&amp;sysval_id='.$id.'" title="'.$AppUI->_('save').'">'
				. dPshowImage( './images/icons/stock_edit-16.png', 16, 16, '' )
				. '</a>';
			$s .= '</td>'.$CR;
		}
		$s .= '
	<!--
	<td valign="top">'.$keys[$key].'</td>
	-->
	<td valign="top">'.dPformSafe($title).'</td>
	<td valign="top" colspan="2">'.dPformSafe($value).'</td>
	<td valign="top" width="16">';
		if ($canEdit) {
			$s .= '<a href="javascript:delIt('.$id.')" title="'.$AppUI->_('delete').'">'
				. dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
				. '</a>';
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
if ($sysval_id == 0)
	echo showRow();
*/
?>
