<?php
##
##	Ticketsmith sql handler
##

$name = isset($HTTP_POST_VARS['name']) ? $HTTP_POST_VARS['name'] : '';
$email = isset($HTTP_POST_VARS['email']) ? $HTTP_POST_VARS['email'] : '';
$subject = isset($HTTP_POST_VARS['subject']) ? $HTTP_POST_VARS['subject'] : '';
$priority = isset($HTTP_POST_VARS['priority']) ? $HTTP_POST_VARS['priority'] : '';
$problem = isset($HTTP_POST_VARS['problem']) ? $HTTP_POST_VARS['problem'] : '';

$author = $name . " <" . $email . ">";
$tsql =
"INSERT INTO tickets (author,subject,priority,body,timestamp,type) ".
"VALUES('$author','$subject','$priority','$description',UNIX_TIMESTAMP(),'Open')";

$rc = mysql_query($tsql);

if (!mysql_errno()) {
	$message = mysql_error();
	// add code to mail to ticket master
} else {
	$message = "Ticket added";
}

?>
<script language="javascript">
	window.location="./index.php?m=ticketsmith&message=<?php echo $message;?>";
</script>
