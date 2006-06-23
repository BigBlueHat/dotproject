<?php /* ADMIN $Id$ */
GLOBAL $AppUI, $user_id, $canEdit, $canDelete, $tab, $tpl;

$perms =& $AppUI->acl();
$module_list = $perms->getModuleList();
$pgos = array();
$q  = new DBQuery;
$q->addTable('modules', 'm');
$q->addQuery('mod_id, mod_name, permissions_item_table');
$q->addWhere('permissions_item_table is not null');
$q->addWhere("permissions_item_table <> ''");
$pgo_list = $q->loadHashList('mod_name');
$q->clear();

// Build an intersection array for the modules and their listing
$modules = array();
$offset = 0;
foreach ($module_list as $module) {
  $modules[ $module['type'] . "," . $module['id']] = $module['name'];
  if ($module['type'] = 'mod' && isset($pgo_list[$module['name']]))
    $pgos[$offset] = $pgo_list[$module['name']]['permissions_item_table'];
  $offset++;
}
$count = 0;

//Pull User perms
$user_acls = $perms->getUserACLs($user_id);
if (! is_array($user_acls))
  $user_acls = array(); // Stops foreach complaining.
$perm_list = $perms->getPermissionList();

foreach ($user_acls as $acl){
	$permission = $perms->get_acl($acl);

	if (is_array($permission)) {
		$modlist = array();
		$itemlist = array();
		if (is_array($permission['axo_groups'])) {
			foreach ($permission['axo_groups'] as $group_id) {
				$group_data = $perms->get_group_data($group_id, 'axo');
				$modlist[] = $AppUI->_($group_data[3]);
			}
		}
		if (is_array($permission['axo'])) {
			foreach ($permission['axo'] as $key => $section) {
				foreach ($section as $id) {
					$mod_data = $perms->get_object_full($id, $key, 1, 'axo');
					if ($mod_data['section_value'] != 'app')
						$modlist[] = '<i>' . ($mod_data['section_value']) . '</i>: ';
					$modlist[] = $mod_data['name'];
				}
			}
		}
	
		$perm_type = array();
		if (is_array($permission['aco'])) {
			foreach ($permission['aco'] as $key => $section) {
				foreach ($section as $value) {
					$perm = $perms->get_object_full($value, $key, 1, 'aco');
					$perm_type[] = $AppUI->_($perm['name']);
				}
			}
		}
		
		$ml[$acl] = implode("<br />", $modlist);
		$pt[$acl] = implode("<br />", $perm_type);
		$perm_ad[$acl] .= $permission['allow'] ? 'allow' : 'deny';
	}
}	

$tpl->assign('aro', $perms->get_object_id("user", $user_id, "aro"));
$tpl->assign('canDelete', $canDelete);
$tpl->assign('canEdit', $canEdit);
$tpl->assign('ml', $ml);
$tpl->assign('modules', $modules);
$tpl->assign('perm', $perm_ad);
$tpl->assign('perm_id', $perm_id);
$tpl->assign('perm_list', $perm_list);
$tpl->assign('pt', $pt);
$tpl->assign('user_acls', $user_acls);
$tpl->assign('user_id', $user_id);
$tpl->displayFile('usr_perms', $users);
?>

<script type="text/javascript" language="javascript">
<!--
<?php
// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// else users would be able to arbitrarily run 'bad' functions
if ($canEdit) {
?>
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
		if (f.permission_module.options[i].value == gon) {
			f.permission_module.selectedIndex = i;
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
	foreach ($pgos as $key => $value){
		// Find the module id in the modules array
		echo "tables['$key'] = '$value';\n";
	}
?>

function popPermItem() {
	var f = document.frmPerms;
	var pgo = f.permission_module.selectedIndex;

	if (!(pgo in tables)) {
		alert( '<?php echo $AppUI->_('No list associated with this Module.', UI_OUTPUT_JS); ?>' );
		return;
	}
	f.permission_table.value = tables[pgo];
	window.open('./index.php?m=public&a=selector&dialog=1&callback=setPermItem&table=' + tables[pgo], 'selector', 'left=50,top=50,height=250,width=400,resizable')
}

// Callback function for the generic selector
function setPermItem( key, val ) {
	var f = document.frmPerms;
	if (val != '') {
		f.permission_item.value = key;
		f.permission_item_name.value = val;
		f.permission_name.value = val;
	} else {
		f.permission_item.value = '0';
		f.permission_item_name.value = 'all';
		f.permission_table.value = '';
	}
}
<?php } ?>
-->
</script>