<?php

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	echo '<script language="javascript">
	window.location="./index.php?m=help&a=access_denied";
	</script>
';
}

if (empty( $sqlaction )) {
	$sqlaction = 0;
}
if (empty( $message )) {
	$message = "Viewing Permissions";
}
if (empty( $permission_id )) {
	$permission_id = 0;
}

//Insert, Update and delete first
if ($sqlaction == 1 && $permission_id == 0) {
	$apsql = "
	INSERT INTO permissions (permission_user, permission_grant_on, permission_item, permission_value)
	VALUES
	('$user_id', '$permission_grant_on', '$permission_item', '$permission_value')";
	mysql_query( $apsql );

	$message = "Permission Created ";
} else if ($sqlaction == 1 && $permission_id <> 0) {
	$upsql ="UPDATE permissions
	SET
	permission_grant_on = '$permission_grant_on',
	permission_item = '$permission_item',
	permission_value = '$permission_value'
	WHERE permission_id = $permission_id";
	mysql_query( $upsql );
	$message = "Permission Updated ";
} else if ($sqlaction == -1 && $permission_id <> 0) {
	$dpsql = "delete from permissions where permission_id =" . $permission_id;
	mysql_query( $dpsql );
	$message = "Permission Deleted ";
}
$e  =mysql_error();
if (strlen( $e ) > 0) {
	$message = $e;
}
//Pull User perms
$usql = "
SELECT u.user_id, u.user_username, p.permission_item, p.permission_id,
p.permission_grant_on, p.permission_value,
c.company_id, c.company_name, pj.project_id,
pj.project_name, f.file_id, f.file_name, u2.user_id, u2.user_username
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
WHERE u.user_id = p.permission_user
AND u.user_id = $user_id
ORDER BY permission_grant_on, permission_item
";

$urc = mysql_query( $usql );

//get username
$unsql = "
SELECT user_id, user_username
FROM users
WHERE
user_id = $user_id";
$uname = mysql_query( $unsql );
$uname = mysql_fetch_array( $uname, MYSQL_ASSOC );

//Pull all companies
$csql = "SELECT company_id AS id, company_name AS name FROM companies ORDER BY company_name";
$crc = mysql_query( $csql );

//Pull all users
$u2sql = "SELECT user_id AS id, user_username AS name FROM users ORDER BY user_username";
$u2rc = mysql_query( $u2sql );

//Pull all projects
$psql = "SELECT project_id AS id, project_name AS name FROM projects ORDER BY project_name";
$prc = mysql_query( $psql );

$modules = array(
	"all",
	"admin",
	"calendar",
	"companies",
	"contacts",
	"files",
	"forums",
	"projects",
	"tasks",
	"ticketsmith",
	"webmail"
);
$nmod = count( $modules );

//---------------------------------Begin Page -------------------------------//
?>

<script>
function editPerm( w, x, y, z ) {
	//w =Permission_id
	//x =permission_grant_on
	//y =permission_item
	//z =permission_value

	var form = document.perms;

	form.sqlaction2.value="edit";
	form.permission_id.value = w;
	x = x.toLowerCase();
	if (x == '<?php echo $modules[0]; ?>') {
		form.permission_grant_on.selectedIndex = 0;
	}
<?php
	for ($i=1; $i < $nmod; $i++) {
		echo "else if(x == '$modules[$i]')form.permission_grant_on.selectedIndex = $i;\n";
	}
?>
	if (z == 1) {
		form.permission_value.selectedIndex = 1;
	} else if (z == -1) {
		form.permission_value.selectedIndex = 2;
	} else {
		form.permission_value.selectedIndex = 0;
	}
	setPItem( y );
}

function setPItem( y ) {
	var form = document.perms;
	x = form.permission_grant_on[form.permission_grant_on.selectedIndex].value;
	x = x.toLowerCase();

	// Clear the select list
	var n = form.permission_item.length + 1;
	for (var i=0; i < n; i++) {
		eval( "form.permission_item.options[i]=null" )
	}

	//Set option 0 to all
	var option0 = new Option( "All", "-1" );
	form.permission_item.options[0] = option0;

	if (x == "companies") {
		<?php
		$i=1;
		while ($crow = mysql_fetch_array( $crc, MYSQL_ASSOC )) {
			echo "var option$i = new Option(\"$crow[name]\", \"$crow[id]\");\n";
			echo "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		?>
	} else if (x == "projects") {
		<?php
		$i=1;
		while ($crow = mysql_fetch_array( $prc, MYSQL_ASSOC )) {
			echo "var option$i = new Option(\"$crow[name]\", \"$crow[id]\");\n";
			echo "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		?>
	} else if (x == "users") {
		<?php
		$i=1;
		while($crow = mysql_fetch_array( $u2rc, MYSQL_ASSOC )) {
			echo "var option$i = new Option(\"$crow[name]\", \"$crow[id]\");\n";
			echo "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		?>
	}
// select the item
	var n = form.permission_item.length;
	for (var i=0; i < n; i++) {
		//alert( i+','+form.permission_item.options[i].value+','+ form.permission_item.options[i].text );
		if ( form.permission_item.options[i].value == y ) {
			form.permission_item.selectedIndex = i;
		}
	}
}

function clearIt(){
	var form = document.perms;
	form.sqlaction2.value = "add";
	form.permission_id.value = 0;
	form.permission_grant_on.selectedIndex = 3;
	setPItem();
}

function delIt( user_id, perm_id ){
	if (confirm( 'Are you sure you want to delete this permission?' )) {
		var form = document.topform;
		window.location = './index.php?a=permissions&m=admin&sqlaction=-1&user_id='+user_id+'&permission_id='+perm_id;
	}
}

function changeUser(){
	var form = document.topform;
	window.location = "./index.php?m=admin&a=permissions&user_id=" + form.change_user[form.change_user.selectedIndex].value;
}
</script>


<TABLE width="75%" border=0 cellpadding="0" cellspacing=1>
<form name="topform">
<TR>
	<TD><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
	<TD nowrap><span class="title">Permissions</span></td>
	<TD width="100%" align="right">
		Select user:
		<select name="change_user" onchange="changeUser()" style="width: 100px;">
	<?php
	mysql_data_seek( $u2rc, 0 );
	while($crow = mysql_fetch_array( $u2rc )) {
		if ($crow['id'] == $user_id) {
			echo "<option value=$crow[id] selected>$crow[name]";
		} else {
			echo "<option value=$crow[id]>$crow[name]";
		}
	}
	?>
		</select>
	</TD>
	<TD align="right" width="99%" nowrap>
		<input type="button" class=button value="back" onClick="javascript:window.location='./index.php?m=admin';">
	</td>
</tr>
</form>
</TABLE>

<?php echo $message;?> for <b><?php echo $uname['user_username'];?></b>
<TABLE width="75%" border=0 bgcolor="#f4efe3" cellpadding="3" cellspacing=1>
<TR>
	<TD width="60"> &nbsp;</td>
	<TD class="mboxhdr"><font color="white">Module</font></td>
	<TD class="mboxhdr"><font color="white">Item</font></td>
	<TD class="mboxhdr"><font color="white">Permission Type</font></td>
</tr>
<?php
if(mysql_num_rows($urc) == 0) {
	echo '<TR><TD colspan=4 align=center><B>No permissions for this User</b></td></tr>';
};

$i = 0;
while ($row = mysql_fetch_array( $urc )) {
	echo "<TR>";
	echo "<TD>"
		."<a href=# onClick=\"editPerm({$row['permission_id']},'{$row['permission_grant_on']}',{$row['permission_item']},{$row['permission_value']});\">edit</a> | "
		."<a href=# onClick=\"delIt({$user_id},{$row['permission_id']})\">del</A></td>";

	if($row['permission_grant_on'] == "all" && $row['permission_item'] == -1 && $row['permission_value'] == -1) {
		echo "<TD bgcolor=#ffc235>";
	} else if($row['permission_item'] == -1 && $row['permission_value'] == -1) {
		echo "<TD bgcolor=#ffff99>";
	} else {
		echo "<TD>";
	}

	echo $row['permission_grant_on'] . "</td>";

	if($row['permission_grant_on'] =="files" && $row['permission_item'] >0) {
		$item = $row['file_name'];
	} else if($row['permission_grant_on'] =="users" && $row['permission_item'] >0) {
		$item = $row['user_username'];
	} else if($row['permission_grant_on'] =="projects" && $row['permission_item'] >0) {
		$item = $row['project_name'];
	} else if($row['permission_grant_on'] =="companies" && $row['permission_item'] >0) {
		$item = $row['company_name'];
	} else {
		$item = $row['permission_item'];
	}

	if($item == "-1") {
		$item = "all";
	}

	if($row['permission_value'] ==-1) {
		$value = "read-write";
	} else if($row['permission_value'] ==1) {
		$value = "read-only";
	} else {
		$value = "deny";
	}

	echo "<TD>" . $item . "</td>";
	echo "<TD>" . $value . "</td>";
	echo "</TR>";
}
?>
</TABLE>

<TABLE>
<TR>
	<TD>Key:</td>
	<TD>&nbsp; &nbsp;</td>
	<TD bgcolor="#ffc235">&nbsp; &nbsp;</td>
	<TD>=SuperUser</td>
	<TD>&nbsp; &nbsp;</td>
	<TD bgcolor="#ffff99">&nbsp; &nbsp;</td>
	<TD>=full access to module</td>
</tr>
</table>

<br>&nbsp;<br>
<TABLE width="75%" cellpadding=0 bgcolor="black" border=0 cellspacing=1>
<tr>
	<td bgcolor="#f4efe3">
		<TABLE width="100%" border=0 cellpadding="2" cellspacing=0 align="center">
		<TR>
			<TD colspan=3 class="mboxhdr">Add or modify permissions</td>
		</tr>
		<form method="post" name="perms">

		<input type="hidden" name="user_id" value="<?php echo $user_id;?>">
		<input type="hidden" name="permission_id" value="0">
		<input type="hidden" name="action" value="permissions">
		<input type="hidden" name="module" value="admin">
		<input type="hidden" name="sqlaction" value="1">
		<TR>
			<TD>Module</td>
			<TD>Item</td>
			<TD>Level</td>
		</tr>
		<TR>
			<TD>
				<select name="permission_grant_on" onChange="setPItem()" style="font-size:9px">
				<option value="<?php echo $modules[0]; ?>" selected><?php echo $modules[0]; ?>
			<?php
				for ($i=1; $i < $nmod; $i++) {
					echo '<option value="' . $modules[$i] . '">' . $modules[$i];
				}
			?>
				</select>
			</td>
			<TD>
				<select name="permission_item" style="font-size:9px">
				<option value="-1" selected>All

				</select>
			</td>
			<TD>
				<select name="permission_value" style="font-size:9px">
				<option value="0">deny
				<option value="1">read-only
				<option value="-1" selected>read-write
				</select>
			</td>
		</tr>
		<TR>
			<TD colspan=2>
				<input type="reset" value="clear" style="font-size:9px;width:100px;" name="sqlaction" onClick="clearIt();">
			</td>
			<TD colspan=2 align="right">
				<input type="submit" value="add" style="font-size:9px;width:100px;" name="sqlaction2">
			</td>
		</tr>
		</form>
		</TABLE>
	</td>
</tr>
</table>
