<?php /* ADMIN $Id$ */
GLOBAL $AppUI, $user_id, $canEdit, $tab;

$pvs = array(
'-1' => 'read-write',
'0' => 'deny',
'1' => 'read only'
);

//Go through all the currently installed modules, and pull out the permissions field/table information, build the sql query and build the modules drop down list..
$sql = "
SELECT 
	mod_name, 
	mod_directory, 
	permissions_item_label, 
	permissions_item_field, 
	permissions_item_table 
FROM 
	modules 
WHERE 
	mod_active=1 
";

$modules_list = db_loadList( $sql);
$pgos = array();
$select_list = array();
$join_list = array();
$count = 0;
foreach ($modules_list as $module){
	if(isset($module['permissions_item_field']) 
	&& $module['permissions_item_field']
	&& isset($module['permissions_item_table']) 
	&& $module['permissions_item_table']
	&& isset($module['permissions_item_label'])
	&& $module['permissions_item_label'] ){
		$label = "t$count";
		//associates mod dirs with tables;
		$pgos[$module['mod_directory']] = array('table'=>$module['permissions_item_table'], 'field' => $module['permissions_item_label'], 'label' => $label);
		//sql selects
		$select_list[] = "\t$label.".$module['permissions_item_field']." as $label".$module['permissions_item_field'].", $label.".$module['permissions_item_label']." as $label".$module['permissions_item_label']."";
		//sql joins
		$join_list[] = "\tLEFT JOIN ".$module['permissions_item_table']." $label ON $label.".$module['permissions_item_field']." = p.permission_item and p.permission_grant_on = '".$module['mod_directory']."'";
		$count++;
	}
}

$selects = implode(",\n", $select_list);
$joins = implode("\n", $join_list);

//Pull User perms
$sql = "
SELECT u.user_id, u.user_username,
	p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
$selects
FROM users u, permissions p
$joins
WHERE u.user_id = p.permission_user
	AND u.user_id = $user_id
";
$res = db_exec( $sql );

//pull the projects into an temp array
$tarr = array();
if ($res) {
  while ($row = db_fetch_assoc( $res )) {
	$item = @$row[@$pgos[$row['permission_grant_on']]['label'].@$pgos[$row['permission_grant_on']]['field']];
	if (!$item) {
		$item = $row['permission_item'];
	}
	if ($item == -1) {
		$item = 'all';
	}
	$tarr[] = array_merge( $row, array( 'grant_item'=>$item ) );
  }
}

// pull list of users for permission duplication from template user
// prevent from copying from users with no permissions
$sql = "SELECT DISTINCT(user_id), user_username FROM users, permissions
	WHERE user_id != $user_id AND permission_user = user_id ORDER BY user_username";
$res = db_loadList( $sql );

//create temp array of users
$tUsers = array();
foreach ( $res as $row ) {
	$tUsers = array_merge( $tUsers, array( $row['user_username']=>$row['user_username'] ) );
}

// read the installed modules
$modules = arrayMerge( array( 'all'=>'all' ), $AppUI->getActiveModules( 'modules' ));
?>

<script language="javascript">
function editPerm( id, gon, it, vl, nm ) {
/*
	id = Permission_id
	gon =permission_grant_on
	it =permission_item
	vl =permission_value
	nm = text representation of permission_value
*/
//alert( 'id='+id+'\ngon='+gon+'\nit='+it+'\nvalue='+vl+'\nnm='+nm);
	var f = document.frmPerms;

	f.sqlaction2.value = "<?php echo $AppUI->_('edit'); ?>";
	
	f.permission_id.value = id;
	f.permission_item.value = it;
	f.permission_item_name.value = nm;
	for(var i=0, n=f.permission_grant_on.options.length; i < n; i++) {
		if (f.permission_grant_on.options[i].value == gon) {
			f.permission_grant_on.selectedIndex = i;
			break;
		}
	}
	f.permission_value.selectedIndex = vl+1;
	f.permission_item_name.value = nm;
}

function clearIt(){
	var f = document.frmPerms;
	f.sqlaction2.value = "<?php echo $AppUI->_('add'); ?>";
	f.permission_id.value = 0;
	f.permission_grant_on.selectedIndex = 0;
}

function delIt(id) {
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var f = document.frmPerms;
		f.del.value = 1;
		f.permission_id.value = id;
		f.submit();
	}
}

var tables = new Array;
<?php
	foreach ($pgos as $key=>$value){
		echo "tables['$key'] = '".$value['table']."';\n";
	}
?>

function popPermItem() {
	var f = document.frmPerms;
	var pgo = f.permission_grant_on.options[f.permission_grant_on.selectedIndex].value;

	if (!(pgo in tables)) {
		alert( 'No list associated with this Module.' );
		return;
	}
	window.open('./index.php?m=public&a=selector&dialog=1&callback=setPermItem&table=' + tables[pgo], 'selector', 'left=50,top=50,height=250,width=400,resizable')
}

// Callback function for the generic selector
function setPermItem( key, val ) {
	var f = document.frmPerms;
	if (val != '') {
		f.permission_item.value = key;
		f.permission_item_name.value = val;
	} else {
		f.permission_item.value = '-1';
		f.permission_item_name.value = 'all';
	}
}
</script>

<table width="100%" border="0" cellpadding="2" cellspacing="0">
<tr><td width="50%" valign="top">

<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>&nbsp;</th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Module');?></th>
	<th width="100%"><?php echo $AppUI->_('Item');?></th>
	<th nowrap><?php echo $AppUI->_('Type');?></th>
	<th>&nbsp;</th>
</tr>

<?php
foreach ($tarr as $row){
	$buf = '';

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=# onClick=\"editPerm({$row['permission_id']},'{$row['permission_grant_on']}',{$row['permission_item']},{$row['permission_value']},'".addslashes($row['grant_item'])."');\" title=\"".$AppUI->_('edit')."\">"
			. dPshowImage( './images/icons/stock_edit-16.png', 16, 16, '' )
			. "</a>";
	}
	$buf .= '</td>';

	$style = '';
	if($row['permission_grant_on'] == "all" && $row['permission_item'] == -1 && $row['permission_value'] == -1) {
		$style =  'style="background-color:#ffc235"';
	} else if($row['permission_item'] == -1 && $row['permission_value'] == -1) {
		$style = 'style="background-color:#ffff99"';
	}

	$buf .= "<td $style>" . $AppUI->_(ucfirst($row['permission_grant_on'])) . "</td>";

	$buf .= "<td>" . $row['grant_item'] . "</td><td nowrap>" . $AppUI->_($pvs[$row['permission_value']]) . "</td>";

	$buf .= '<td nowrap>';
	if ($canEdit) {
		$buf .= "<a href=\"javascript:delIt({$row['permission_id']});\" title=\"".$AppUI->_('delete')."\">"
			. dPshowImage( './images/icons/stock_delete-16.png', 16, 16, '' )
			. "</a>";
	}
	$buf .= '</td>';
	
	echo "<tr>$buf</tr>";
}
?>
</table>

<table>
<tr>
	<td><?php echo $AppUI->_('Key');?>:</td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffc235">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('Super User');?></td>
	<td>&nbsp; &nbsp;</td>
	<td bgcolor="#ffff99">&nbsp; &nbsp;</td>
	<td>=<?php echo $AppUI->_('full access to module');?></td>
</tr>
</table>


</td><td width="50%" valign="top">

<?php if ($canEdit) {?>

<table cellspacing="1" cellpadding="2" border="0" class="std" width="100%">
<form name="frmPerms" method="post" action="?m=admin">
	<input type="hidden" name="del" value="0" />
	<input type="hidden" name="dosql" value="do_perms_aed" />
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="permission_user" value="<?php echo $user_id;?>" />
	<input type="hidden" name="permission_id" value="0" />
	<input type="hidden" name="permission_item" value="-1" />
<tr>
	<th colspan="2"><?php echo $AppUI->_('Add or Edit Permissions');?></th>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Module');?>:</td>
	<td width="100%"><?php echo arraySelect($modules, 'permission_grant_on', 'size="1" class="text"', 'all', true);?></td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Item');?>:</td>
	<td>
		<input type="text" name="permission_item_name" class="text" size="30" value="all" disabled>
		<input type="button" name="" class="text" value="..." onclick="popPermItem();">
	</td>
</tr>
<tr>
	<td nowrap align="right"><?php echo $AppUI->_('Level');?>:</td>
	<td><?php echo arraySelect($pvs, 'permission_value', 'size="1" class="text"', 0);?></td>
</tr>
<tr>
	<td>
		<input type="reset" value="<?php echo $AppUI->_('clear');?>" class="button" name="sqlaction" onClick="clearIt();">
	</td>
	<td align="right">
		<input type="submit" value="<?php echo $AppUI->_('add');?>" class="button" name="sqlaction2">
	</td>
</tr>
</form>
</table>
<br />
<table cellspacing="1" cellpadding="2" border="0" class="std" width="100%">
<form name="cpPerms" method="post" action="?m=admin">
	<input type="hidden" name="dosql" value="do_perms_cp" />
	<input type="hidden" name="user_id" value="<?php echo $user_id;?>" />
	<input type="hidden" name="permission_user" value="<?php echo $user_id;?>" />
<tr>
	<th colspan="2"><?php echo $AppUI->_('Copy Permissions from Template');?></th>
</tr>
<tr>
	<td nowrap align="left"><?php echo $AppUI->_('Copy Permissions from User');?>:
	<?php echo arraySelect($tUsers, 'temp_user_name', 'size="1" class="text"', 6);?></td>
</tr>
<tr>
	<td colspan="2">
		<input type="checkbox" name="delPerms" class="text" value="true" checked="checked">
		<?php echo $AppUI->_('adminDeleteTemplate');?>
	</td>
</tr>
<tr>
	<td align="center" colspan="2">
		<input type="submit" value="<?php echo $AppUI->_('Copy from Template');?>" class="button" name="cptempperms">
	</td>
</tr>
</form>
</table>
<?php } ?>

</td>

</tr>



</tr>

</table>
