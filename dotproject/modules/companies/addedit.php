<?php /* COMPANIES $Id$ */
$company_id = intval( dPgetParam( $_GET, "company_id", 0 ) );

// check permissions for this company
$perms =& $AppUI->acl();
// If the company exists we need edit permission,
// If it is a new company we need add permission on the module.
if ($company_id)
  $canEdit = $perms->checkModuleItem($m, "edit", $company_id);
else
  $canEdit = $perms->checkModule($m, "add");

if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

// load the company types
$types = dPgetSysVal( 'CompanyType' );

// load the record data
$sql = "
SELECT companies.*, contacts.contact_first_name, contacts.contact_last_name
FROM companies
LEFT JOIN users ON users.user_id = companies.company_owner
LEFT JOIN contacts ON user_contact = contact_id
WHERE companies.company_id = $company_id
";

$obj = null;
if (!db_loadObject( $sql, $obj ) && $company_id > 0) {
	$AppUI->setMsg( 'Company' );
	$AppUI->setMsg( "invalidID", UI_MSG_ERROR, true );
	$AppUI->redirect();
}

// collect all the users for the company owner list
$owners = array( '0'=>'' );
$sql = "SELECT user_id,CONCAT_WS(' ',contact_first_name,contact_last_name) 
         FROM users, contacts
         WHERE user_contact = contact_id
         ORDER BY contact_first_name";
$owners = db_loadHashList( $sql );

// setup the title block
$ttl = $company_id > 0 ? "Edit Company" : "Add Company";
$titleBlock = new CTitleBlock( $ttl, 'handshake.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=companies", "companies list" );
if ($company_id != 0)
  $titleBlock->addCrumb( "?m=companies&a=view&company_id=$company_id", "view this company" );
$titleBlock->show();
?>

<script language="javascript">
function submitIt() {
	var form = document.changeclient;
	if (form.company_name.value.length < 3) {
		alert( "<?php echo $AppUI->_('companyValidName', UI_OUTPUT_JS);?>" );
		form.company_name.focus();
	} else {
		form.submit();
	}
}

function testURL( x ) {
	var test = "document.changeclient.company_primary_url.value";
	test = eval(test);
	if (test.length > 6) {
		newwin = window.open( "http://" + test, 'newwin', '' );
	}
}
</script>

<form name="changeclient" action="?m=companies" method="post">
	<input type="hidden" name="dosql" value="do_company_aed" />
	<input type="hidden" name="company_id" value="<?php echo $company_id;?>" />
<table cellspacing="1" cellpadding="1" border="0" width='100%' class="std">


<tr>
<td>


<table>
	<tr>
		<td align="right"><?php echo $AppUI->_('Company Name');?>:</td>
		<td>
			<input type="text" class="text" name="company_name" value="<?php echo dPformSafe(@$obj->company_name);?>" size="50" maxlength="255" /> (<?php echo $AppUI->_('required');?>)
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Email');?>:</td>
		<td>
			<input type="text" class="text" name="company_email" value="<?php echo dPformSafe(@$obj->company_email);?>" size="30" maxlength="255" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Phone');?>:</td>
		<td>
			<input type="text" class="text" name="company_phone1" value="<?php echo dPformSafe(@$obj->company_phone1);?>" maxlength="30" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Phone');?>2:</td>
		<td>
			<input type="text" class="text" name="company_phone2" value="<?php echo dPformSafe(@$obj->company_phone2);?>" maxlength="50" />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Fax');?>:</td>
		<td>
			<input type="text" class="text" name="company_fax" value="<?php echo dPformSafe(@$obj->company_fax);?>" maxlength="30" />
		</td>
	</tr>
	<tr>
		<td colspan=2 align="center">
			<img src="images/shim.gif" width="50" height="1" /><?php echo $AppUI->_('Address');?><br />
			<hr width="500" align="center" size=1 />
		</td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Address');?>1:</td>
		<td><input type="text" class="text" name="company_address1" value="<?php echo dPformSafe(@$obj->company_address1);?>" size=50 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Address');?>2:</td>
		<td><input type="text" class="text" name="company_address2" value="<?php echo dPformSafe(@$obj->company_address2);?>" size=50 maxlength="255" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('City');?>:</td>
		<td><input type="text" class="text" name="company_city" value="<?php echo dPformSafe(@$obj->company_city);?>" size=50 maxlength="50" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('State');?>:</td>
		<td><input type="text" class="text" name="company_state" value="<?php echo dPformSafe(@$obj->company_state);?>" maxlength="50" /></td>
	</tr>
	<tr>
		<td align="right"><?php echo $AppUI->_('Zip');?>:</td>
		<td><input type="text" class="text" name="company_zip" value="<?php echo dPformSafe(@$obj->company_zip);?>" maxlength="15" /></td>
	</tr>
	<tr>
		<td align="right">
			URL http://<A name="x"></a></td><td><input type="text" class="text" value="<?php echo dPformSafe(@$obj->company_primary_url);?>" name="company_primary_url" size="50" maxlength="255" />
			<a href="#x" onClick="testURL('CompanyURLOne')">[<?php echo $AppUI->_('test');?>]</a>
		</td>
	</tr>
	
	<tr>
		<td align="right"><?php echo $AppUI->_('Company Owner');?>:</td>
		<td>
	<?php
		echo arraySelect( $owners, 'company_owner', 'size="1" class="text"', @$obj->company_owner );
	?>
		</td>
	</tr>
	
	<tr>
		<td align="right"><?php echo $AppUI->_('Type');?>:</td>
		<td>
	<?php
		echo arraySelect( $types, 'company_type', 'size="1" class="text" onChange="javascript:changeRecordType(this.value);"', @$obj->company_type, true );
	?>
		</td>
	</tr>
	
	<tr>
		<td align="right" valign=top><?php echo $AppUI->_('Description');?>:</td>
		<td align="left">
			<textarea cols="70" rows="10" class="textarea" name="company_description"><?php echo @$obj->company_description;?></textarea>
		</td>
	</tr>
</table>


</td>
	<td align='left'>
		<?php
			error_reporting(E_ALL);
			require_once("./classes/customfieldsparser.class.php");
			// let's create the parser
			$cfp = new CustomFieldsParser("CompanyCustomFields", $obj->company_id);
			
			// we will need the amount of record types
			$amount_custom_record_types = count($cfp->custom_record_types);
		?>
		
		<?php
			// let's parse the custom fields form table
			echo $cfp->parseTableForm(true);
		?>
		
		<script language="javascript">
		    var companies_type;
		    
		    // We need to create an array of all the names
		    // of the record types in JS so we can map the Key to the type name (used in the field filter)
		    companies_type = new Array(<?php echo $amount_custom_record_types; ?>);
		    
		    <?php
		    	foreach($cfp->custom_record_types as $key => $record_type){
		    		echo "companies_type[$key] = new String('$record_type');\n";
		    	}
		    	reset($cfp->custom_record_types);
		    	$actual_record_type = str_replace(" ", "_", $cfp->custom_record_types[$obj->company_type]);
		    	
		    	// Let's parse all the show functions
		    	echo $cfp->parseShowFunctions();
		    ?>
		    
		    
			function changeRecordType(value){
				// if the record type is changed, then hide everything
				hideAllRows();
				// and how only those fields needed for the current type
				eval("show"+companies_type[value]+"();");
			}
				
			<?php echo $cfp->showHideAllRowsFunction(); ?>
			// by default hide everything and show the actual type record
			<?php echo "\n\nhideAllRows(); show$actual_record_type();"; ?>
		</script>
	</td>
</tr>
<tr>
	<td><input type="button" value="<?php echo $AppUI->_('back');?>" class="button" onClick="javascript:history.back(-1);" /></td>
	<td align="right"><input type="button" value="<?php echo $AppUI->_('submit');?>" class="button" onClick="submitIt()" /></td>
</tr>

</table>
</form>
