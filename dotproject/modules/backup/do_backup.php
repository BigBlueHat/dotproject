<?php
// backup database module for dotProject
// (c)2003 Daniel Vijge
// Licensed under GNU/GPL v2 or later

// Based on the work of the phpMyAdmin
// (c)2001-2002 phpMyAdmin group [http://www.phpmyadmin.net]

// Completely rewritten for 2.0 by Adam Donnison <ajdonnison@dotproject.net>

$perms =& $AppUI->acl();

if (! $perms->checkModule('backup', 'view'))
  $AppUI->redirect('m=public&a=access_denied');

$export_what = dPgetParam($_POST, 'export_what');
$output_format = dPgetParam($_POST, 'output_format');
$droptable = dPgetParam($_POST, 'droptable', false);

$valid_export_options = array('all', 'table', 'data');
$valid_output_formats = array('xml', 'zip', 'sql');

if (! in_array($export_what, $valid_export_options)
|| ! in_array($output_format, $valid_output_formats)) {
  $AppUI->setMsg('Invalid Options', UI_MSG_ERR);
  $AppUI->redirect('m=public&a=access_denied');
}

require_once "$baseDir/lib/adodb/adodb-xmlschema.inc.php";


if ($output_format == 'xml') {
  $schema = new adoSchema($GLOBALS['db']);
  $output = $schema->ExtractSchema(($export_what == 'table') ? false : true);
} else {
  // Build the SQL manually.
  $db->setFetchMode(ADODB_FETCH_NUM);
  $alltables = $db->MetaTables('TABLES');
  $output  = '';
  $output .= '# Backup of database \'' . $dPconfig['dbname'] . '\'' . "\r\n";
  $output .= '# Generated on ' . date('j F Y, H:i:s') . "\r\n";
  $output .= '# OS: ' . PHP_OS . "\r\n";
  $output .= '# PHP version: ' . PHP_VERSION . "\r\n";
  if ($dPconfig['dbtype'] == 'mysql')
    $output .= '# MySQL version: ' . mysql_get_server_info() . "\r\n";
  $output .= "\r\n";
  $output .= "\r\n";

  // fetch all tables on by one
  foreach ($alltables as $table)
  {
    // introtext for this table
    $output .= '# TABLE: ' . $table . "\r\n";
    $output .= '# --------------------------' . "\r\n";
    $output .= '#' . "\r\n";
    $output .= "\r\n";
    
    if ($drop_table) 
    {
      // drop table
      $output .= 'DROP TABLE IF EXISTS `' . $table . '`;' . "\r\n";
      $output .= "\r\n";
    }
    
    if ($export_what != 'data') 
    {
      // structure of the table
      $rs = $db->Execute('SELECT * FROM ' . $table . ' WHERE -1');

      $fields = $db->MetaColumns($table);
      $indexes = $db->MetaIndexes($table);
      $output .= 'CREATE TABLE `' . $table . '` (' . "\r\n";
      $primary = array();
      $first = true;
      if (is_array($fields)) {
	foreach ($fields as $details) {
	  if ($first)
	    $first = false;
	  else
	    $output .= ",\r\n";
	  if ($details->primary_key)
	    $primary[] = $details->name;
	  $output .= '  `' . $details->name . '` ' . $details->type;
	  if ($details->max_length > -1) {
	    $output .= '(' . $details->max_length;
	    if (isset($details->scale))
	      $output .= ',' . $details->scale;
	    $output .= ')';
	  }
	  if ($details->not_null)
	    $output .= ' NOT NULL';
	  if ($details->has_default)
	    $output .= ' DEFAULT ' . "'$details->default_value'";
	  if ($details->auto_increment)
	    $output .= ' auto_increment';
	}
      }
      if (is_array($indexes)) {
	foreach ($indexes as $index => $details) {
	  if ($first)
	    $first = false;
	  else
	    $output .= ",\r\n";
	  $output .= '  ';
	  if ($details['unique'])
	    $output .= 'UNIQUE ';
	  $output .= 'KEY `' . $index . '` ( `' . implode('`, `', $details['columns'] ) . '` )';
	}
      }
      if (count($primary)) {
	$output .= "\r\n" . '  PRIMARY KEY ( `'. implode('`, `', $primary) . '` )';
      }
      $output .= "\r\n" . ');' . "\r\n\r\n";
    }
    
    if ($export_what != 'table') 
    {
      // all data from table
      $db->setFetchMode(ADODB_FETCH_ASSOC) ;

      $result = $db->Execute('SELECT * FROM '.$table);
      while($tablerow = $result->fetchRow())
      {
        $output .= 'INSERT INTO `'.$table.'` ( `' . implode('`, `', array_keys($tablerow)) . '` )' . "\r\n";
	$output .= ' VALUES (';
	$first = true;
	foreach ($tablerow as $value) {
	  if ($first)
	    $first = false;
	  else
	    $output .= ',';
          // remove all enters from the field-string. MySql stamement must be on one line
          $value = str_replace("\r\n",'\n',$value);
	  $value = str_replace("\n", '\n', $value); // Just in case there are unadorned newlines.
          // replace ' by \'
          $value = str_replace('\'',"\'",$value);
          $output .= '\''.$value.'\'';
	}
        $output .= ');' . "\r\n";
      } // while
      $output .= "\r\n";
      $output .= "\r\n";
    }
  }
}

switch ($output_format) {
  case 'xml':
    header('Content-Disposition: attachment; filename="backup.xml"');
    header('Content-Type: text/xml');
    echo $output;
    break;
  case 'zip':
    header('Content-Disposition: inline; filename="backup.zip"');
    header('Content-Type: application/x-zip');
    include_once $baseDir . '/modules/backup/zip.lib.php';
    $zip = new zipfile;
    $zip->addFile($output,'backup.sql');
    echo $zip->file();
    break;
  case 'sql':
    header('Content-Disposition: inline; filename="backup.sql"');
    header('Content-Type: text/sql');
    echo $output;
    break;
}

?>
