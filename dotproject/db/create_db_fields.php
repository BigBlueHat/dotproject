<?php
if (file_exists('../includes/config.php'))
{
        include_once('../includes/config.php');
        require_once( $dPconfig['root_dir']."/includes/db_adodb.php" );
        include_once('../includes/db_connect.php');
        include_once('../includes/main_functions.php');
}
if (function_exists('db_exec'))
{
        global $dPconfig;
        $lines = file($dPconfig['root_dir'] . '/db/upgrade_latest.sql');
        $sql = '';
        dprint(__FILE__, __LINE__, 7, 'Attempting to correct the problem...');

        foreach($lines as $line)
        {
                if (!strstr($line, '#') )
                        $sql .= $line;
                if (strstr($line, ';'))
                {
                        $sql = str_replace(';', '', $sql);
                        dprint(__FILE__, __LINE__, 7, "Executing: <pre>$sql</pre>");
                        db_exec($sql);
                        //TODO: remove the following line after testing is done.
                        dprint(__FILE__, __LINE__, 7, db_error());
                        $sql = '';
                }
        }
        dprint(__FILE__, __LINE__, 7, 'Upgrades completed.');
}
?>
