<?php
if ( isset($_POST['dept_company']) ) {
	$company_id = $_POST['dept_company'];
} else {
	echo '<script language="javascript">window.location="./index.php?m=companies&message=Could not process department without a valid company_id."</script>';
}

$dept_id = isset($_POST['dept_id']) ? $_POST['dept_id'] : 0;
$dept_parent = isset($_POST['dept_parent']) ? $_POST['dept_parent'] : 0;
$dept_company = isset($_POST['dept_company']) ? $_POST['dept_company'] : $company_id;
$dept_name = isset($_POST['dept_name']) ? $_POST['dept_name'] : '';
$dept_phone = isset($_POST['dept_phone']) ? $_POST['dept_phone'] : '';
$dept_fax = isset($_POST['dept_fax']) ? $_POST['dept_fax'] : '';
$dept_address1 = isset($_POST['dept_address1']) ? $_POST['dept_address1'] : '';
$dept_address2 = isset($_POST['dept_address2']) ? $_POST['dept_address2'] : '';
$dept_city = isset($_POST['dept_city']) ? $_POST['dept_city'] : '';
$dept_state = isset($_POST['dept_state']) ? $_POST['dept_state'] : '';
$dept_zip = isset($_POST['dept_zip']) ? $_POST['dept_zip'] : '';
$dept_url = isset($_POST['dept_url']) ? $_POST['dept_url'] : '';
$dept_owner = isset($_POST['dept_owner']) ? $_POST['dept_owner'] : 0;
$dept_desc = isset($_POST['dept_desc']) ? 
	htmlspecialchars( stripslashes( $_POST['dept_desc'] ), ENT_QUOTES ) : '';

if ($del) {
##
##	Delete department
##
/*
	$test = "select project_id from projects where project_company = $company_id";

	$testrc = mysql_query($test);
	if(mysql_num_rows($testrc)) {
		$message = "You cannot delete a company that has projects associated with it";
	} else {
		$sql = "delete from companies where company_id = $company_id";
		$rsql = mysql_query($sql);
		$message = mysql_error();
	}
*/
} else if (($dept_id) == 0) {
##
##	New department
##
	$sql =
	"INSERT INTO departments (
	dept_parent,
	dept_company,
	dept_name,
	dept_phone,
	dept_fax,
	dept_address1,
	dept_address2,
	dept_city,
	dept_state,
	dept_zip,
	dept_url,
	dept_owner,
	dept_desc
	) values (
	$dept_parent,
	$dept_company,
	'$dept_name',
	'$dept_phone',
	'$dept_fax',
	'$dept_address1',
	'$dept_address2',
	'$dept_city',
	'$dept_state',
	'$dept_zip',
	'$dept_url',
	'$dept_owner',
	'$dept_desc'
	)";
	mysql_query( $sql );
	$message = mysql_error();
} else {
##
##	Edit department
##
	$sql = "UPDATE departments SET
	dept_parent=$dept_parent,
	dept_name='$dept_name',
	dept_phone='$dept_phone',
	dept_fax='$dept_fax',
	dept_address1='$dept_address1',
	dept_address2='$dept_address2',
	dept_city='$dept_city',
	dept_state='$dept_state',
	dept_zip='$dept_zip',
	dept_url='$dept_url',
	dept_owner='$dept_owner',
	dept_desc='$dept_desc'
	WHERE
	dept_id = $dept_id";

	mysql_query($sql);
	$message = mysql_error();
}

?>
<script language="javascript">
	window.location="./index.php?m=companies&a=view&company_id=<?php echo $dept_company;?>&message=<?php echo $message;?>";
</script>
