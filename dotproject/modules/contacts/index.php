<?php
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// contact index
if (empty( $orderby )) {
	$orderby = "last_name";
}
if (empty( $where )) {
	$where = "%";
}
$carr[] = array();
$orderby = "contact_order_by";

// Pull First Letters
$let = ":";
$sql = "select contact_order_by from contacts";
$rc = mysql_query( $sql );
while ($row = mysql_fetch_array( $rc, MYSQL_ASSOC )) {
	$let .= strtolower( substr( $row["contact_order_by"], 0, 1 ) );
}

$showfields = array(
	// "test" => "concat(contact_first_name,' ',contact_last_name) as test",    why do we want the name repeated?
	"contact_company" => "contact_company",
	"contact_phone" => "contact_phone",
	"contact_email" => "contact_email"
);

$sql = "select contact_id, contact_order_by,";
while (list( $key, $val ) = each( $showfields )) {
	$sql.="$val,";
}
$sql.= "contact_first_name, contact_last_name,
contact_phone
from contacts
where contact_order_by like '$where%'
order by $orderby	";

$carrWidth=4;
$carrHeight=4;

$rc = mysql_query( $sql );
$rn = mysql_num_rows( $rc );

$t = floor( $rn / $carrWidth );
$r = ($rn % $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		//if($y<$r)	$x = -1;
		while (($x<$carrHeight) && ($row = mysql_fetch_array( $rc, MYSQL_ASSOC ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
} else {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		if($y<$r)	$x = -1;
		while(($x<$t) && ($row = mysql_fetch_array( $rc, MYSQL_ASSOC ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
}

$tdw = floor( 100 / $carrWidth );

$usql = "Select user_first_name, user_last_name
from users
where users.user_id = $user_cookie ";
$urc = mysql_query( $usql );
$urow = mysql_fetch_array( $urc, MYSQL_ASSOC );
echo mysql_error();
?>

<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
<TR>
	<TD><img src="./images/icons/contacts.gif" alt="" border="0"></td>
	<TD nowrap><span class="title">Contacts</span></td>
	<TD align="right" width="100%">
	<?php if (!$denyEdit) { ?>
		<input type="button"  class=button value="Add New contact" onClick="javascript:window.location='./index.php?m=contacts&a=addedit'"></td>
	<?php } ?>
</tr>
</TABLE>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
<TR>
	<TD valign="bottom" nowrap><span id=""><b>Welcome <?php echo $urow['user_first_name'];?>.</b>  This page show you a list of current contacts.</span></td>
	<TD WIDTH="100%" ALIGN=RIGHT>SHOW:</td>
	<TD align="center" bgcolor="silver"><a href="./index.php?m=contacts">All</A></TD>
<?php
	for ($a=65; $a < 91; $a++) {
		$cu = chr( $a );
		$cl = chr( $a+32 );
		$bg = strpos($let, "$cl") > 0 ? "bgcolor=silver><a href=./index.php?m=contacts&where=$cu" : '';
		echo "<TD align=center $bg>$cu</A></TD>\n";
	}
?>
</tr>
</TABLE>

<TABLE width="95%" border=0 bgcolor="silver" cellpadding="1" cellspacing=2 height="400">
<TR>
<?php 
	for ($z=0; $z < $carrWidth; $z++) {
?>
	<TD valign="top" align="left" bgcolor="#f4efe3" width="<?php echo $tdw;?>%">
	<?php
		for ($x=0; $x < @count($carr[$z]); $x++) {
	?>
		<table width="95%" cellspacing=1 cellpadding=1>
		<TR bgcolor="silver">
			<TD>
				<a href="./index.php?m=contacts&a=addedit&contact_id=<?php echo $carr[$z][$x]["contact_id"];?>"><B><?php echo $carr[$z][$x]["contact_order_by"];?></b></a>
			</td>
		</tr>
		<TR>
			<TD>
			<?php
				reset( $showfields );
				while (list( $key, $val ) = each( $showfields )) {
					if (strlen( $carr[$z][$x][$key] ) > 0) {
						if($val == "contact_email") {
						  echo "<A HREF='mailto:{$carr[$z][$x][$key]}' class='mailto'>{$carr[$z][$x][$key]}</A>\n";
						} else {
						  echo  $carr[$z][$x][$key]. "<BR>";
						}
					}
				}
			?>
			</td>
		</tr>
		</table>
		<br>&nbsp;<br>
	<?php }?>
	</TD>
<?php }?>
</TR>
</Table>
