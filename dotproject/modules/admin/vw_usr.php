<table cellpadding="2" cellspacing="1" border="0" width="100%" class="tbl">
<tr>
	<td width="60" align="right">
		&nbsp; sort by:&nbsp;
	</td>
	<th width="150">
		<a href="?m=admin&a=index&orderby=user_username&z=<?php echo $z;?>"><font color="white">Login Name</font></a>
	</th>
	<th>
		<a href="?m=admin&a=index&orderby=user_last_name&z=<?php echo $z;?>"><font color="white">Real Name</font></a>
	</th>
</tr>
<?php 
while ($row = mysql_fetch_array( $urow, MYSQL_ASSOC )) {
?>
<tr>
	<td align="right" nowrap="nowrap">
		<img src="images/shim.gif" width="1" height="1" border="0" alt="">
<?php if (!$denyEdit) { ?>
		<a href="./index.php?m=admin&a=addedituser&user_id=<?php echo $row["user_id"];?>"><img src="images/icons/pencil.gif" width="12" height="12" border="0" alt="edit information"></a>

		<a href="?m=admin&a=permissions&user_id=<?php echo $row["user_id"];?>"><img src="images/obj/lock.gif" width="16" height="16" border="0" alt="edit permissions"></a>

		<a href="javascript:delMe(<?php echo $row["user_id"];?>, '<?php echo $row["user_first_name"] . " " . $row["user_last_name"];?>')"><img src="images/icons/trash.gif" width="16" height="16" border="0" alt="delete"></a>
<?php } ?>
	</td>
	<td>
		<a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $row["user_id"];?>"><?php echo $row["user_username"];?></a>
	</td>
	<td>
		<a href="mailto:<?php echo $row["user_email"];?>"><img src="images/obj/email.gif" width="16" height="16" border="0" alt="email"></a>
		<?php echo $row["user_last_name"].', '.$row["user_first_name"];?>
	</td>
</tr>
<?php }?>

</table>
