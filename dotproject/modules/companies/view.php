<?php
$company_id = isset($HTTP_GET_VARS['company_id']) ? $HTTP_GET_VARS['company_id'] : 0;

// check permissions
$denyRead = getDenyRead( $m, $company_id );
$denyEdit = getDenyEdit( $m, $company_id );

if ($denyRead) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

// pull data
$csql = "
select companies.*,users.user_first_name,users.user_last_name
from companies
left join users on users.user_id = companies.company_owner
where companies.company_id = $company_id
";
$crc = mysql_query( $csql );
$crow = mysql_fetch_array( $crc, MYSQL_ASSOC );

$pstatus = array(
	'Not Defined',
	'Proposed',
	'In planning',
	'In progress',
	'On hold',
	'Complete'
);
?>

<TABLE width="95%" border=0 cellpadding="1" cellspacing=1>
	<TR>
		<TD><img src="./images/icons/money.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">View Company/Client</span></td>
		<TD nowrap> <img src="./images/shim.gif" width="16" height="16" alt="" border="0"></td>
		<TD align="right" width="100%">
		<?php if (!$denyEdit) { ?>
			<input type="button" class=button value="new company" onClick="javascript:window.location='./index.php?m=companies&a=addedit';">
		<?php } ?>
		</td>
	</tr>
</TABLE>

<table border="0" cellpadding="4" cellspacing="0" width="95%">
	<TR>
		<TD width="50%" nowrap>
		<a href="./index.php?m=companies">Companies List</a>
<?php if (!$denyEdit) { ?>
		<b>:</b> <a href="./index.php?m=companies&a=addedit&company_id=<?php echo $company_id;?>">Edit this Company</a>
<?php } ?>
		</td>
		<TD width="50%" align="right"><?php include ("./includes/create_new_menu.php");?></td>
	</TR>
</table>

<table border="0" cellpadding="6" cellspacing="0" width="95%" bgcolor="#eeeeee">
	<tr valign="top">
		<td width="50%">
			<TABLE width="100%">
				<TR>
					<TD><b>Company:</b></TD>
					<td><?php echo $crow["company_name"];?></td>
				</TR>
				<tr>
					<td><b>Phone:</b></td>
					<td><?php echo @$crow["company_phone1"];?></td>
				</tr>
				<tr>
					<td><b>Phone2:</b></td>
					<td><?php echo @$crow["company_phone2"];?></td>
				</tr>
				<tr>
					<td><b>Fax:</b></td>
					<td><?php echo @$crow["company_fax"];?></td>
				</tr>
				<tr valign=top>
					<td><b>Address:</b></td>
					<td><?php
						echo @$crow["company_address1"]
							.( ($crow["company_address2"]) ? '<br>'.$crow["company_address2"] : '' )
							.'<br>'.$crow["company_city"]
							.'&nbsp;&nbsp;'.$crow["company_state"]
							.'&nbsp;&nbsp;'.$crow["company_zip"]
							;
					?></td>
				</tr>
				<tr>
					<td><b>URL:</b></td>
					<td>
						<a href="http://<?php echo @$crow["company_primary_url"];?>" target="Company"><?php echo @$crow["company_primary_url"];?></a>
					</td>
				</tr>
			</TABLE>

		</TD>
		<td width="50%">
			<b>Description</b><br>
			<?php
			$newstr = str_replace( chr(10), "<BR>", $crow["company_description"]);
			echo $newstr;
			?>
		</td>
	</TR>
</table>

<?php
$psql = "
select projects.*, users.user_first_name,users.user_last_name
from projects
left join users on users.user_id = projects.project_owner
where project_company = $company_id
order by project_name
";
$prc = mysql_query($psql);
$nums = mysql_num_rows($prc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array( $prc, MYSQL_ASSOC );
}
?>

<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
	<TR>
		<TD width=50% valign=top><strong>Active Projects:</strong><br>
<?php
$psql = "
select projects.*, users.user_first_name,users.user_last_name
from projects
left join users on users.user_id = projects.project_owner
where project_company = $company_id
order by project_name
";
$prc = mysql_query($psql);
$nums = mysql_num_rows($prc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array( $prc, MYSQL_ASSOC );
}
?>
			<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
				<TR style="border: outset #eeeeee 2px;">
					<TD class="mboxhdr">Name</td>
					<TD class="mboxhdr">Owner</td>
					<TD class="mboxhdr">Status</td>
				</tr>

		<?php
			for ($x =0; $x < $nums; $x++){
				if ($tarr[$x]["project_active"] <> 0) {
				?>
				<TR bgcolor="#f4efe3">
					<TD>
						<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[$x]["project_id"];?>">
							<?php echo $tarr[$x]["project_name"];?>
						</a>
					<td><?php echo $tarr[$x]["user_first_name"].'&nbsp;'.$tarr[$x]["user_last_name"];?></td>
					<td><?php echo $pstatus[$tarr[$x]["project_status"]]; ?></td>
				</tr>
		<?php
				}
			}
		?>
			</TABLE>

			<p><strong>Achived Projects:</strong><br>
			<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
				<TR style="border: outset #eeeeee 2px;">
					<TD class="mboxhdr">Name</td>
					<TD class="mboxhdr">Owner</td>
				</tr>

		<?php
			for ($x =0; $x < $nums; $x++){
				if ($tarr[$x]["project_active"] == 0) {
				?>
				<TR bgcolor="#f4efe3">
					<TD>
						<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[$x]["project_id"];?>">
							<?php echo $tarr[$x]["project_name"];?>
						</a>
					<td><?php echo $tarr[$x]["user_first_name"].'&nbsp;'.$tarr[$x]["user_last_name"];?></td>
				</tr>
		<?php
				}
			}
		?>
			</TABLE>

		</TD>
		<TD width=50% valign=top><strong>Users:</strong><br>
<?php
$usql = "
select user_id, user_username, user_first_name, user_last_name
from users
where user_company = $company_id
";

$urc = mysql_query($usql);
$nums = mysql_num_rows($urc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array( $urc, MYSQL_ASSOC );
}
?>
			<TABLE width="100%" border=0 cellpadding="2" cellspacing=1>
				<TR style="border: outset #eeeeee 2px;">
					<TD class="mboxhdr">Login Name</td>
					<TD class="mboxhdr">User Name</td>
				</tr>

		<?php
			for ($x =0; $x < $nums; $x++){
		?>
				<TR bgcolor="#f4efe3">
					<TD>
						<A href="./index.php?m=admin&a=viewuser&user_id=<?php echo $tarr[$x]["user_id"];?>">
							<?php echo $tarr[$x]["user_username"];?>
						</a>
					<td><?php echo $tarr[$x]["user_first_name"].'&nbsp;'.$tarr[$x]["user_last_name"];?></td>
				</tr>
		<?php
			}
		?>
			</TABLE>
		</TD>
	</TR>
</TABLE>

