<?php
global $baseDir, $db;

if (! isset($baseDir))
	die('You must not use this file directly, please direct your browser to install/index.php instead');

dPmsg("Upgrading sysvals");

function dPgetOldSysval( $sysval_title )
{
        GLOBAL $db;

        $sql = "SELECT syskey_type, syskey_sep1, syskey_sep2, sysval_value 
                FROM sysvals 
                LEFT JOIN syskeys sk ON syskey_id = sysval_key_id
                WHERE sysval_title = '$sysval_title'
                ";

        $rs = $db->Execute($sql);
        $arr = array();

        $row = $rs->fetchRow();

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
$sql = "SELECT sysval_title FROM sysvals GROUP BY sysval_title";
$rs = $db->Execute($sql);

$sysvals = Array();

// read all the old sysvals
while ($r = $rs->fetchRow())
{
	$sv = dPgetOldSysval( $r['sysval_title'] );
	$sysvals[$r['sysval_title']] = $sv;
}

// create the new sysvals table
$sql_create_sv = "CREATE TABLE `sysvals_upgrade` (
	  `sysval_id` int(10) unsigned NOT NULL auto_increment,
	  `sysval_title` varchar(48) NOT NULL default '',
	  `sysval_value_id` varchar(32) default '0',
	  `sysval_value` text NOT NULL,
	  PRIMARY KEY  (`sysval_id`)
	) TYPE=MyISAM;";

$rs = $db->Execute($sql_create_sv);

foreach($sysvals as $k=>$v)
{
	foreach ($v as $sv_k=>$sv_v)
	{
		$sql_insert_sv = "INSERT INTO `sysvals_upgrade` (
				`sysval_title`, `sysval_value_id`, `sysval_value`) VALUES (
                                                        '".$k."', '".$sv_k."', '".$sv_v."')";
                $rs = $db->Execute($sql_insert_sv);
        }
}

$sql_rename_sv = "ALTER TABLE `sysvals` RENAME TO `sysvals_old`";
$sql_rename_sv_ug = "ALTER TABLE `sysvals_upgrade` RENAME TO `sysvals`";

$db->Execute($sql_rename_sv);
$db->Execute($sql_rename_sv_ug);

?>
