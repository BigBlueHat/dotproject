<?php  // $Id$
//TODO db backup
//TODO secure: let only admins run installer in runMode > 1 (Do this when new perms system is implemented)
//TODO db unavailable bug (adodbconnect)
//TODO remove superfluous dirs
//TODO db version  & db upgrade functionality
//TODO md5() password for login with existing config file but unavail db
//TODO destroy session
//TODO session: crypt
//TODO enhanced texts, resumee

// reset all config values to 'default' (values loaded from config.php or config-dist.php)
if (isset( $_GET['refresh'] ) && $_GET['refresh'] == true) {
        session_unregister('Installer');
         $AppUI->setMsg( "reset all config values to default", UI_MSG_OK);
        $AppUI->redirect("m=install");
}

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'InstallerIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'InstallerIdxTab' ) !== NULL ? $AppUI->getState( 'InstallerIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'InstallerIdxTab' ) );

// define image sources for vw_idx_check.php
$failedImg = '<img src="./images/icons/stock_cancel-16.png" width="16" height="16" align="middle" alt="Failed"/>';
$okImg = '<img src="./images/icons/stock_ok-16.png" width="16" height="16" align="middle" alt="OK"/>';

$titleBlock = new CTitleBlock('Installation', 'control-center.png', $m);
$titleBlock->addCrumbRight("<a href='?m=install&refresh=true'>".$AppUI->_('reset all config values to default')."</a>");
$titleBlock->show();

echo $AppUI->_("intro1");
echo "<br />&nbsp;<br />";

$tabBox = new CTabBox( "?m=install", "{$dPconfig['root_dir']}/modules/install/", $tab );
$tabBox->add('vw_idx_locale', 'Locale');
$tabBox->add('vw_idx_check', 'Check');
$tabBox->add('vw_idx_idb', 'Install Database');
$tabBox->add('vw_idx_cfg', 'Create Config File');
$tabBox->show();
?>
