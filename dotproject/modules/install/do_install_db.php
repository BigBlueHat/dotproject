<?php // $Id$
$Installer->bindPost($pd);
$Installer->bindToVarious($various);
$Installer->updateDPcfgFromPost($pd);

include_once("./includes/db_connect.php");

$dbi = $Installer->ADODBconnect();
$dbci = $Installer->DBconnect();

if ($do_install_db) {
        if ($various[dbdrop]) { db_exec("DROP DATABASE IF EXISTS ".$Installer->cfg['dbname']); }
        $Installer->createDB();
        $dbError = db_errno();

        if ($dbError <> 0 && $dbError <> 1007) {
                //provide some error info
                $AppUI->setMsg( "A Database Error occurred. Database has not been created! The provided database details are probably not correct.\n".db_error(), UI_MSG_ERROR);
                $AppUI->redirect("m=install&tab=$tab");
        }

        $Installer->dbCreated = true;

        $Installer->populateDB();
        if ($dbError <> 0 && $dbError <> 1007) {
                //provide some error info
                $AppUI->setMsg( "A Database Error occurred. Database has probably not been populated completely!\n".db_error(), UI_MSG_ERROR);
                $AppUI->redirect("m=install&tab=$tab");
        }

        $Installer->dbPopulated = true;
        $AppUI->setMsg( "Database successfully created and populated with structure!", UI_MSG_OK);
} else {

        echo $Installer->generateBackupSQL();

}

$AppUI->redirect("m=install&tab=$tab");

?>