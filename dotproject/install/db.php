<?php // $Id$
?>
<html>
<head>
	<title>dotProject Installer</title>
	<meta name="Description" content="dotProject Installer">
 	<link rel="stylesheet" type="text/css" href="../style/default/main.css">
</head>
<body>
<h1><img src="dp.png" align="middle" alt="dotProject Logo"/>&nbsp;dotProject Installer</h1>
<?php
!is_file( "../includes/config.php" )
	or die("Security Check: dotProject seems to be already configured. Communication broken for Security Reasons!");

if (is_file( "../includes/config-dist.php" )){
	// include the standard config values
	include_once( "../includes/config-dist.php" );
} else {
	echo "<h2>../includes/config-dist.php is not available. It would preset some standard config values for you.</h2>";
}
?>
<form name="instFrm" action="do_install_db.php" method="post">
<table cellspacing="0" cellpadding="3" border="0" class="tbl" width="100%" align="center">
        <tr>
            <td class="title" colspan="2">Database Settings</td>
        </tr>
         <tr>
            <td class="item">Database Server Type</td>
            <td align="left">
		<select name="dbtype" size="1" style="width:200px;" class="text">
			<option value="access">access</option>
			<option value="ado">ado</option>
			<option value="ado_access">ado_access</option>
			<option value="ado_mssql">ado_mssql</option>

			<option value="db2">db2</option>
			<option value="vfp">vfp</option>
			<option value="fbsql">fbsql</option>
			<option value="ibase">ibase</option>
			<option value="firebird">firebird</option>
			<option value="borland_ibase">borland_ibase</option>

			<option value="informix">informix</option>
			<option value="informix72">informix72</option>
			<option value="ldap">ldap</option>
			<option value="mssql">mssql</option>
			<option value="mssqlpro">mssqlpro</option>
			<option value="mysql" selected="selected">mysql</option>

			<option value="mysqlt">mysqlt</option>
			<option value="maxsql">maxsql</option>
			<option value="oci8">oci8</option>
			<option value="oci805">oci805</option>
			<option value="oci8po">oci8po</option>
			<option value="odbc">odbc</option>

			<option value="odbc_mssql">odbc_mssql</option>
			<option value="odbc_oracle">odbc_oracle</option>
			<option value="odbt">odbt</option>
			<option value="odbt_unicode">odbt_unicode</option>
			<option value="oracle">oracle</option>
			<option value="netezza">netezza</option>

			<option value="postgres">postgres</option>
			<option value="postgres64">postgres64</option>
			<option value="postgres7">postgres7</option>
			<option value="sapdb">sapdb</option>
			<option value="sqlanywhere">sqlanywhere</option>
			<option value="sqlite">sqlite</option>

			<option value="sqlitepo">sqlitepo</option>
			<option value="sybase">sybase</option>
		</select>
	   </td>
  	 </tr>
         <tr>
            <td class="item">Database Host Name</td>
            <td align="left"><input class="button" type="text" name="dbhost" value="<?php echo $dPconfig['dbhost']; ?>" title="The Name of the Host the Database Server is installed on" /></td>
          </tr>
           <tr>
            <td class="item">Database Name</td>
            <td align="left"><input class="button" type="text" name="dbname" value="<?php echo  $dPconfig['dbname']; ?>" title="The Name of the Database dotProject will use and/or install" /></td>
          </tr>
          <tr>
            <td class="item">Database User Name</td>
            <td align="left"><input class="button" type="text" name="dbuser" value="<?php echo $dPconfig['dbuser']; ?>" title="The Database User that dotProject uses for Database Connection" /></td>
          </tr>
          <tr>
            <td class="item">Database User Password</td>
            <td align="left"><input class="button" type="text" name="dbpass" value="<?php echo $dPconfig['dbpass']; ?>" title="The Password according to the above User." /></td>
          </tr>
           <tr>
            <td class="item">Use Persistent Connection?</td>
            <td align="left"><input type="checkbox" name="dbpersist" value="true" <?php echo ($dPconfig['dbpersist']==true) ? 'checked="checked"' : ''; ?> title="Use a persistent Connection to your Database Server." /></td>
          </tr>
          <tr>
            <td class="item">Drop Existing Database?</td>
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
            <td align="left"><br /><input class="button" type="submit" name="do_db" value="install db only" title="Try to set up the database with the given information." />
	    &nbsp;<input class="button" type="submit" name="do_cfg" value="write config file only" title="Write a config file with the details only." /></td>
	  <td align="right" class="item"><br />(Recommended) &nbsp;<input class="button" type="submit" name="do_db_cfg" value="install db & write cfg" title="Write config file and setup the database with the given information." />
   		</td>
          </tr>
        </table>
</form>
</body>
</html>
