<?php  // $Id$
global $AppUI, $db, $Installer, $dPrunLevel, $tab;

if ($GLOBALS["dPrunLevel"] == 0) {   // config.php is not available, nor $dPconfig

	is_file( "./includes/config-dist.php" )
		or die("./includes/config-dist.php is not available. It is needed for guessing some config values for installation procedure.
		Therefore you should never delete or modify it. Please restore this file.");

	// include the standard config values
	include_once( "./includes/config-dist.php" );
}


$dbDrivers = array ( "access" => "access", "ado"=> "ado", "ado_access"=> "ado_access", "ado_mssql"=> "ado_mssql",
                                "db2" => "db2", "vfp" => "vfp", "fbsql" => "fbsql", "ibase" =>"ibase", "firebird" => "firebird",
                                "borland_ibase" => "borland_ibase", "informix" => "informix", "informix72" => "informix72",
                                "ldap" => "ldap", "mssql" =>"mssql", "mssqlpro" =>"mssqlpro", "mysql" => "mysql", "mysqlt" => "mysqlt",
                                "maxsql" => "maxsql", "oci8" => "oci8", "oci805" => "oci805", "oci8po" => "oci8po", "odbc" => "odbc",
                                "odbc_mssql" => "odbc_mssql", "odbc_oracle" => "odbc_oracle", "odbt" => "odbt", "odbt_unicode" => "odbt_unicode",
                                "oracle" => "oracle", "netezza" => "netezza", "postgres" => "postgres", "postgres64" => "postgres64",
                                "postgres7" => "postgres7", "sapdb" => "sapdb", "sqlanywhere" => "sqlanywhere", "sqlite" => "sqlite",
                                "sqlitepo" => "sqlitepo", "sybase" => "sybase" );

echo '<form name="instFrm" action="index.php?m=install&tab='.$tab.'" method="post">';
?>
<input type="hidden" name="dosql" value="do_install_db" />
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
            <td align="left"><?php echo arraySelect( $dbDrivers, 'dbtype', 'size="1" style="width:200px;" class="text"',  $Installer->cfg['dbtype'] );?></td>
  	 </tr>
         <tr>
            <td class="item"><?php echo $AppUI->_('Database Host Name'); ?></td>
            <td align="left"><input class="button" type="text" name="dbhost" value="<?php echo $dPconfig['dbhost']; ?>" title="The Name of the Host the Database Server is installed on" /></td>
          </tr>
           <tr>
            <td class="item"><?php echo $AppUI->_('Database Name'); ?></td>
            <td align="left"><input class="button" type="text" name="dbname" value="<?php echo  $dPconfig['dbname']; ?>" title="The Name of the Database dotProject will use and/or install" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Database User Name'); ?></td>
            <td align="left"><input class="button" type="text" name="dbuser" value="<?php echo $dPconfig['dbuser']; ?>" title="The Database User that dotProject uses for Database Connection" /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Database User Password'); ?></td>
            <td align="left"><input class="button" type="text" name="dbpass" value="<?php echo $dPconfig['dbpass']; ?>" title="The Password according to the above User." /></td>
          </tr>

          <tr>
            <td class="item"><?php echo $AppUI->_('Database Port'); ?></td>
            <td align="left"><input class="button" type="text" name="dbport" value="<?php echo $dPconfig['dbport']; ?>" title="The Port the Database Server is listening to. If empty a standard value of 3306 is used." /></td>
          </tr>
           <tr>
            <td class="item"><?php echo $AppUI->_('Use Persistent Connection'); ?>?</td>
            <td align="left"><input type="checkbox" name="dbpersist" value="true" <?php echo ($dPconfig['dbpersist']==true) ? 'checked="checked"' : ''; ?> title="Use a persistent Connection to your Database Server." /></td>
          </tr>
          <tr>
            <td class="item"><?php echo $AppUI->_('Drop Existing Database'); ?>?</td>
            <td align="left"><input type="checkbox" name="dbdrop" value="true" title="Deletes an existing Database before installing a new one. This deletes all data in the given database. Data cannot be restored." /><span class="item"> If checked, existing Data will be lost!</span></td>
        </tr>
        </tr>
          <tr>
            <td class="title" colspan="2">&nbsp;</td>
        </tr>
          <tr>
            <td class="title" colspan="2">Backup existing Database (Recommended)</td>
        </tr>
        <tr>
            <td class="item" colspan="2">Receive a Backup SQL File containing all Tables for the database entered above
            by clicking on the Button labeled 'Backup' down below. Depending on database size and system environment this process can take some time.</td>
        </tr>
        <tr>
            <td class="item">Receive XML Backup Schema File</td>
            <td align="left"><input class="button" type="submit" name="dobackup" value="Backup" title="Click here to retrieve a database backup file that can be stored on your local system." /></td>
        </tr>
          <tr>
            <td align="left"><br /><input class="button" type="submit" name="do_db" value="<?php echo $AppUI->_('install db only');?>" title="Try to set up the database with the given information." />
	    &nbsp;<input class="button" type="submit" name="do_cfg" value="<?php echo $AppUI->_('write config file only');?>" title="Write a config file with the details only." /></td>
	  <td align="right" class="item"><br />(Recommended) &nbsp;<input class="button" type="submit" name="do_db_cfg" value="<?php echo $AppUI->_('install db & write cfg');?>" title="Write config file and setup the database with the given information." />
   		</td>
          </tr>
        </table>
</form>
