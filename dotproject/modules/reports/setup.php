<?php

$config = array();
$config['mod_name'] = 'Reports';
$config['mod_version'] = '0.1';
$config['mod_directory'] = 'reports';
$config['mod_setup_class'] = 'CSetupReports';
$config['mod_type'] = 'user';
$config['mod_ui_name'] = 'Reports';
$config['mod_ui_icon'] = '';
$config['mod_description'] = 'A module for reports';

if (@$a == 'setup') {
  echo dPshowModuleConfig( $config );
}

class CSetupReports {

  function install() {
		return null;
	}

  function remove() {
    return null;
  }

  function upgrade() {
    return null;
  }
}

?>
