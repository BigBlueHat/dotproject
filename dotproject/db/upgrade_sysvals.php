<?php
global $baseDir, $db;

require_once "$baseDir/classes/query.class.php";

if (! isset($baseDir))
	die('You must not use this file directly, please direct your browser to install/index.php instead');

dPmsg("Upgrading sysvals");

$q = new DBQuery;

function dPgetOldSysval( $sysval_title )
{
	$q = new DBQuery;
	$q->addTable('sysvals');
	$q->addQuery('syskey_type');
	$q->addQuery('syskey_sep1');
	$q->addQuery('syskey_sep2');
	$q->addQuery('sysval_value');
	$q->leftJoin('syskeys', 'sk', 'syskey_id = sysval_key_id');
	$q->addWhere('sysval_title = \''.$sysval_title.'\'');

        $arr = array();

        $row = $q->loadHash();

        $sep1 = $row['syskey_sep1'];    // item separator
        $sep2 = $row['syskey_sep2'];    // alias separator

        // A bit of magic to handle newlines and returns as separators
        // Missing sep1 is treated as a newline.
        if (!isset($sep1) || empty($sep1)) {
          $sep1 = "\n";
        }
        if ($sep1 == "\\n") {
          $sep1 = "\n";
        }
        if ($sep1 == "\\r") {
          $sep1 = "\r";
        }

        $temp = explode( $sep1, $row['sysval_value'] );
        // We use trim() to make sure a numeric that has spaces
        // is properly treated as a numeric
        foreach ($temp as $item) {
        if ($item) {
                        $sep2 = empty($sep2) ? "\n" : $sep2;
                        $temp2 = explode( $sep2, $item );
                        if (isset( $temp2[1] )) {
                                $arr[trim($temp2[0])] = trim($temp2[1]);
                        } else {
                                $arr[trim($temp2[0])] = trim($temp2[0]);
                        }
                }
        }
        return $arr;
}

// upgrade the sysvals table
$q = new DBQuery;
$q->addQuery('sysval_title');
$q->addTable('sysvals');
$q->addGroup('sysval_title');

$title_list = $q->loadList();
$q->clear();

$sysvals = Array();

// read all the old sysvals
foreach ($title_list as $r)
{
	$sv = dPgetOldSysval( $r['sysval_title'] );
	$sysvals[$r['sysval_title']] = $sv;
}

// drop the old format sysvals table
$q->dropTable('sysvals');
$q->exec();

// create the new sysvals table
/*
 * MerlinYoda (2007-01-31): Increased sysval_value_id size from 32 to 128
 * because code being stored in database was overflowing and being truncated.
 * "Code" probably shouldn't be stored in this table anyway. 
 * Added line in upgrade_latest.sql to correct this for anyone that updated on
 * HEAD between then (when this file was set in place) and now (2007-01-31)
 */
$sql_create_sv = "(
	  `sysval_id` int(10) unsigned NOT NULL auto_increment,
	  `sysval_title` varchar(128) NOT NULL default '',
	  `sysval_value_id` varchar(128) default '0',
	  `sysval_value` text NOT NULL,
	  PRIMARY KEY  (`sysval_id`)
	)";

$q->createTable('sysvals');
$q->createDefinition($sql_create_sv);
$q->exec();
$q->clear();

// insert new sysvals data
foreach($sysvals as $k=>$v)
{
	foreach ($v as $sv_k=>$sv_v)
	{
		$q->addTable('sysvals');
		$q->addInsert('sysval_title', $k);
		$q->addInsert('sysval_value_id', $sv_k);
		$q->addInsert('sysval_value', $sv_v);
		$q->exec();
		$q->clear();
        }
}

?>
