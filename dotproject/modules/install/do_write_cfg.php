<?php // $Id$
$Installer->bindPost($pd);
$Installer->bindToFT($ft);
$Installer->bindToVarious($various);
if ($Installer->various["do_save_cfg"]) {
        $AppUI->setMsg( "Config data saved for this Session", UI_MSG_OK);
} else {
        $Installer->cfgFileCreated = $Installer->cfgFileStore();
        if ($Installer->cfgFileCreated) {
                $AppUI->setMsg( "Config file successfully written", UI_MSG_OK);
        } else {
                $AppUI->setMsg( "Config file could not be written", UI_MSG_ERROR);
        }
}
$AppUI->redirect("m=install&tab=$tab");
?>