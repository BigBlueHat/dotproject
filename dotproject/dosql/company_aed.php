<?php
if (empty( $company_id )) {
	$company_id = 0;
}
if ($company_owner =="") {
	$co = $user_cookie;
} else {
	$co =$company_owner;
}

if ($del) {
	$test = "select project_id from projects where project_company = $company_id";

	$testrc = mysql_query($test);
	if(mysql_num_rows($testrc)) {
		$message = "You cannot delete a company that has projects associated with it";
	} else {
		$sql = "delete from companies where company_id = $company_id";
		$rsql = mysql_query($sql);
		$message = mysql_error();
	}
} else if (($company_id) == 0) {
	$company_name = $HTTP_POST_VARS["company_name"];
	$sql =
	"insert into companies (
	company_name,
	company_phone1,
	company_phone2,
	company_fax,
	company_address1,
	company_address2,
	company_city,
	company_state,
	company_zip,
	company_primary_url,
	company_owner,
	company_description )
	values
	(
	'$company_name',
	'$company_phone1',
	'$company_phone2',
	'$company_fax',
	'$company_address1',
	'$company_address2',
	'$company_city',
	'$company_state',
	'$company_zip',
	'$company_primary_url',
	$user_cookie,
	'$company_description')";

	$rsql = mysql_query($sql);
	$message = mysql_error();

} else {
	$company_name = $HTTP_POST_VARS["company_name"];
	$sql = "update companies set
	company_name='$company_name',
	company_phone1='$company_phone1',
	company_phone2='$company_phone2',
	company_fax='$company_fax',
	company_address1='$company_address1',
	company_address2='$company_address2',
	company_city='$company_city',
	company_state='$company_state',
	company_zip='$company_zip',
	company_primary_url='$company_primary_url',
	company_owner=$co,
	company_description= '$company_description'
	where
	company_id = $company_id";

	$rsql = mysql_query($sql);
	$message = mysql_error();
}

?>
<script language="javascript">
	window.location="./index.php?m=companies&message=<?php echo $message;?>";
</script>
