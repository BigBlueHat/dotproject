<?php /* ROLES $Id$ */
$AppUI->savePlace();

// pull all the key types
$sql = "SELECT *,mod_name FROM roles LEFT JOIN modules ON mod_id = role_module ORDER BY role_module, role_name";
$roles = db_loadList( $sql );

$role_id = dPgetParam( $_GET, 'role_id', 0 );

$modules = 
$sql = "SELECT mod_id, mod_name FROM modules WHERE mod_active > 0 ORDER BY mod_directory";
$modules = arrayMerge( array( '0'=>'All' ), db_loadHashList( $sql ) );

// setup the title block
$titleBlock = new CTitleBlock( 'Roles', 'main-settings.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "System Admin" );
$titleBlock->show();

$crumbs = array();
$crumbs["?m=system"] = "System Admin";
?>

<script language="javascript">
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
function delIt(id) {
	if (confirm( 'Are you sure you want to delete this?' )) {
		f = document.roleFrm;
		f.del.value = 1;
		f.role_id.value = id;
		f.submit();
	}
}
<?php } ?>
</script>

<span style="color:red"><strong>Note this function is still in development and testing phase</strong></span>

<table border="0" cellpadding="2" cellspacing="1" width="100%" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th><?php echo $AppUI->_('Role Name');?></th>
	<th><?php echo $AppUI->_('Description');?></th>
	<th><?php echo $AppUI->_('Type');?></th>
	<th><?php echo $AppUI->_('Module');?></th>
	<th>&nbsp;</th>
</tr>
<?php

function showRow( $role=null ) {
	global $canEdit, $role_id, $AppUI, $modules;
	$CR = "\n";
	$id = @$role['role_id'];
	$name = @$role['role_name'];
	$description = @$role['role_description'];
	$type = @$role['role_type'];
	$module = @$role['role_module'];

	$s = '<tr>'.$CR;
	if (($role_id == $id || $id == 0) && $canEdit) {
	// edit form
		$s .= '<form name="roleFrm" method="post" action="?m=system&u=roles">'.$CR;
		$s .= '<input type="hidden" name="dosql" value="do_role_aed" />'.$CR;
		$s .= '<input type="hidden" name="del" value="0" />'.$CR;
		$s .= '<input type="hidden" name="role_id" value="'.$id.'" />'.$CR;

		$s .= '<td>&nbsp;</td>';
		$s .= '<td valign="top"><input type="text" name="role_name" value="'.$name.'" class="text" /></td>';
		$s .= '<td valign="top"><textarea name="role_description" class="small" rows="5" cols="40">'.$description.'</textarea></td>';
		$s .= '<td valign="top">' . arraySelect( $modules, 'role_module', 'size="1" class="text"', $module ) . '</td>';
		$s .= '<td><input type="submit" value="'.$AppUI->_($id ? 'edit' : 'add').'" class="button" /></td>';
		$s .= '<td>&nbsp;</td>';
	} else {
		$s .= '<td width="12" valign="top">';
		if ($canEdit) {
			$s .= '<a href="?m=system&u=roles&role_id='.$id.'"><img src="./images/icons/pencil.gif" alt="edit" border="0" width="12" height="12"></a>';
			$s .= '</td>'.$CR;
		}
		$s .= '<td valign="top">'.$name.'</td>'.$CR;
		$s .= '<td valign="top">'.$description.'</td>'.$CR;
		$s .= '<td valign="top">'.$type.'</td>'.$CR;
		$s .= '<td valign="top">'.$modules[$module].'</td>'.$CR;
		$s .= '<td valign="top" width="16">';
		if ($canEdit) {
			$s .= '<a href="javascript:delIt('.$id.')"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="'.$AppUI->_('delete').'" border="0"></a>';
		}
		$s .= '</td>'.$CR;
	}
	$s .= '</tr>'.$CR;
	return $s;
}

// do the modules that are installed on the system
$s = '';
foreach ($roles as $row) {
	echo showRow( $row );
}
// add in the new key row:
if ($role_id == 0) {
	echo showRow();
}
?>
</table>
