<?php
GLOBAL $denyEdit;
?>

<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">
<tr>
	<td width="60" align="right">
		&nbsp; sort by:&nbsp;
	</td>
	<th>
		<A href="./index.php?m=admin&a=index&orderby=user_username&vm=<?php echo $vm;?>"><font color="white">Login Name</font></a>
	</th>
	<th>
		<A href="./index.php?m=admin&a=index&orderby=user_last_name&vm=<?php echo $vm;?>"><font color="white">Real Name</font></a>
	</th>
	<th>
		<A href="./index.php?m=admin&a=index&orderby=user_email&vm=<?php echo $vm;?>"><font color="white">Email</font></a>
	</th>
	<th>Active?</th>
<?php if (!$denyEdit) { ?>
	<th>Permissions</td>
<?php } ?>
</tr>
<?php 
while ($row = mysql_fetch_array( $urow, MYSQL_ASSOC )) {
?>
<tr>
	<TD width="60" class="smallNorm" align="right">
<?php if (!$denyEdit) { ?>
	<span  class="smallNorm">
	<a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row["user_id"];?>">edit</a> | 
	<a href="javascript:delme(<?php echo $row["user_id"];?>, '<?php echo $row["user_first_name"] . " " . $row["user_last_name"];?>')">del</a>
	</span>
<?php } ?>
		&nbsp;
	</td>
	<td>
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>"><?php echo $row["user_username"];?></a>
	</td>
	<td>
		<?php echo $row["user_last_name"].', '.$row["user_first_name"];?>
	</td>
	<td>
		<a href="mailto:<?php echo $row["user_email"];?>"><?php echo $row["user_email"];?></a>
	</td>
	<td>
		<?php if(intval(@$row["permission_user"]) !=0){echo "Yes";} else { echo "No";}?>
	</td>
<?php if (!$denyEdit) { ?>
	<td>
		<input type="button" class=button value="permissions" onClick="javascript:window.location= './index.php?m=admin&a=permissions&user_id=<?php echo $row["user_id"];?>';">
	</td>
<?php } ?>

</tr>
<?php }?>

</table>
