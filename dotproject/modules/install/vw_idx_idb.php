<?php  // $Id$
global $AppUI, $db, $Installer, $dPrunLevel, $tab;

echo '<form name="instFrm" action="index.php?m=install&tab='.$tab.'" method="post">';
?>
<input type="hidden" name="dosql" value="do_install_db" />
<input type="hidden" name="various[dummy]" value="NULL" />
        <table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="center">
<?php if ($Installer->dbCreated) { ?>
         <tr>
            <td class="ok" colspan="2"><b><?php echo $AppUI->_('dbAlreadyInstalled'); ?></b></td>
        </tr>
        <tr>
                <td class="title" colspan="2">&nbsp;</td>
        </tr>
<?php } ?>
        <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('Database Settings'); ?></td>
        </tr>
         <tr>
            <td class="item"><?php echo $AppUI->_('Database Server Type'); ?></td>
            <td align="left"><?php echo arraySelect( $Installer->dbDrivers, 'pd[dbtype]', 'size="1" style="width:200px;" class="text"',  $Installer->cfg['dbtype'] );?></td>
  	 </tr>
         <tr>
            <td class="item"><?php echo $AppUI->_('Database Host Name'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[dbhost]" value="<?php echo $Installer->cfg['dbhost']; ?>" title="The Name of the Host the Database Server is installed on" /></td>
          </tr>
           <tr>
            <td class="item"><?php echo $AppUI->_('Database Name'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[dbname]" value="<?php echo $Installer->cfg['dbname']; ?>" title="The Name of the Database dotProject will use and/or install" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Database User Name'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[dbuser]" value="<?php echo $Installer->cfg['dbuser']; ?>" title="The Database User that dotProject uses for Database Connection" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Database User Password'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[dbpass]" value="<?php echo $Installer->cfg['dbpass']; ?>" title="The Password according to the above User." /></td>
          </tr>

          <tr>
            <td class="item"><?php echo $AppUI->_('Database Port'); ?></td>
            <td align="left"><input class="button" type="text" name="pd[dbport]" value="<?php echo $Installer->cfg['dbport']; ?>" title="The Port the Database Server is listening to. If empty a standard value of 3306 is used." /></td>
          </tr>
           <tr>
            <td class="item"><?php echo $AppUI->_('Use Persistent Connection'); ?>?</td>
            <td align="left"><input type="checkbox" name="pd[dbpersist]" value="true" <?php echo ($Installer->cfg['dbpersist']==true) ? 'checked="checked"' : ''; ?> title="Use a persistent Connection to your Database Server." /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Drop Existing Database'); ?>?</td>
            <td align="left"><input type="checkbox" name="various[dbdrop]" value="true" <?php echo ($Installer->various['dbdrop']==true) ? 'checked="checked"' : ''; ?> title="Deletes an existing Database before installing a new one. This deletes all data in the given database. Data cannot be restored." /><span class="item"> If checked, existing Data will be lost!</span></td>
        </tr>
        </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
<?php /* ?>
          <tr>
            <td class="title" colspan="2"><?php echo $AppUI->_('Populate Database'); ?></td>
        </tr>
        <tr>
            <td class="item" colspan="2">Fill the Database with Structure and/or Content. While filling the database with Structure will be
            necessary for Installation from Scratch (Install - Add Structure), it is recommended/handy for Upgrades avoiding it and apply
            the database upgrade scripts distributed with dotProject automatically (Upgrade) or by hand (Manual Installation - Do Nothing).
            For now,  an automatic Upgrade is only possible from one release step to another (not more than one steps in a time!), otherwise
            it is very likely that you will experience errors running dotProject. In Case of once Upgrading more than one Release Versions,
            the only way to go is a manual Application of all necessary upgrade scripts.
            Furthermore dotProject needs an initial company created for running properly.
            Fill in an appropriate name or leave empty if you do want to create one</td>
        </tr>
         <tr>
            <td class="item"><?php echo $AppUI->_('Database Installation Mode'); ?></td>
            <td align="left"><select class="button" size="1" name="db_install_mode" title="Title">
            <option value="install" <?php echo ($db_install_mode == 'install') ? 'selected="selected"' : '';?>><?php echo $AppUI->_('Install - Add Structure'); ?></option>
            <option value="upgrade" <?php echo ($db_install_mode == 'upgrade') ? 'selected="selected"' : '';?>><?php echo $AppUI->_('Upgrade'); ?></option>
            <option value="manual" <?php echo ($db_install_mode == 'manual') ? 'selected="selected"' : '';?>><?php echo $AppUI->_('Manual Installation - Do Nothing'); ?></option></select></td>
        </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
<?php */ ?>
          <tr>
            <td class="title" colspan="2">Backup existing Database (Recommended)</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Receive a Backup SQL File containing all Tables for the database entered above
            by clicking on the Button labeled 'Backup' down below. Depending on database size and system environment this process can take some time.</td>
        </tr>
        <tr>
            <td class="item">Add 'Drop Tables'-Command in SQL-Script?</td>
            <td align="left"><input type="checkbox" name="backupdrop" value="false" <?php echo ($backupdrop==true) ? 'checked="checked"' : ''; ?> title="If this command is added, existing data will be deleted by running the backup script. This can be handy not needing to manually delete existing database tables." /></td>
        </tr>
        <tr>
            <td class="item">Receive SQL File</td>
            <td align="left"><input class="button" type="submit" name="dobackup" value="Backup" title="Click here to retrieve a database backup file that can be stored on your local system." /></td>
        </tr>
          <tr>
            <td colspan="3" align="right"><br /><input class="button" type="submit" name="do_install_db" value="<?php echo $AppUI->_('install');?>" title="Save Settings and try to install the database with the given information." /></td>
          </tr>
        </table>
</form>
