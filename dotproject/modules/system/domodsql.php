<?php /* $Id$ */
##
## Activate or move a module entry
##
$cmd = isset( $_GET['cmd'] )? $_GET['cmd'] : '0';

$module = new CModule();
if (($msg = $module->bind( $_GET ))) {
	$AppUI->setMsg( $msg, UI_MSG_ERROR );
	$AppUI->redirect();
}

if ($module->mod_id) {
	$module->load( $module->mod_id );
}
$ok = include_once( "{$AppUI->cfg['root_dir']}/modules/$module->mod_directory/setup.php" );
if (!$ok) {
	if ($module->mod_type != 'core') {
		$AppUI->setMsg( 'Module setup file could not be found', UI_MSG_ERROR );
		$AppUI->redirect();
	}
}
eval( "\$setup = new {$config['mod_setup_class']}();" );

switch ($cmd) {
	case 'moveup':
	case 'movedn':
		$module->move( $cmd );
		$AppUI->setMsg( 'Module re-ordered', UI_MSG_OK );
		break;
	case 'toggle':
	// just toggle the active state of the table entry
		$module->mod_active = 1 - $module->mod_active;
		$module->store();
		$AppUI->setMsg( 'Module state changed', UI_MSG_OK );
		break;
	case 'toggleMenu':
	// just toggle the active state of the table entry
		$module->mod_ui_active = 1 - $module->mod_ui_active;
		$module->store();
		$AppUI->setMsg( 'Module menu state changed', UI_MSG_OK );
		break;
	case 'install':
	// do the module specific stuff
		$AppUI->setMsg( $setup->install() );
		$module->bind( $config );
	// add to the installed modules table
		$module->install();
		$AppUI->setMsg( 'Module installed', UI_MSG_OK );
		break;
	case 'remove':
	// do the module specific stuff
		$AppUI->setMsg( $setup->remove() );
	// remove from the installed modules table
		$module->remove();
		$AppUI->setMsg( 'Module removed', UI_MSG_ALERT );
		break;
	case 'upgrade':
		$AppUI->setMsg( $setup->upgrade() );
		break;
	default:
		$AppUI->setMsg( 'Unknown Command', UI_MSG_ERROR );
		break;
}
$AppUI->redirect();
?>