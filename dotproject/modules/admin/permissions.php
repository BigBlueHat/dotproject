<?php 
if(empty($sqlaction))$sqlaction =0;
if(empty($message))$message ="Viewing Permissions";
if(empty($permission_id))$permission_id =0;
//Insert, Update and delete first

if($sqlaction ==1 && $permission_id ==0){
	$apsql = "
	insert into permissions (permission_user, permission_grant_on, permission_item, permission_value)
	values
	('$user_id', '$permission_grant_on', '$permission_item', '$permission_value')";
	mysql_query($apsql);
	
	$message = "Permission Created ";
}
elseif($sqlaction ==1 && $permission_id <>0){
	$upsql ="update permissions 
	set 
	permission_grant_on = '$permission_grant_on',
	permission_item = '$permission_item',
	permission_value = '$permission_value'
	where permission_id = $permission_id";
	mysql_query($upsql);
	$message = "Permission Updated ";
}
elseif($sqlaction ==-1 && $permission_id <>0){
	$dpsql = "delete from permissions where permission_id =" . $permission_id;
	mysql_query($dpsql);
	$message = "Permission Deleted ";
}
$e  =mysql_error();
if(strlen($e) > 0 )$message = $e;
//Pull User perms
$usql = "
select u.user_id, u.user_username, p.permission_item, p.permission_id, 
p.permission_grant_on, p.permission_value, 
c.company_id, c.company_name, pj.project_id, 
pj.project_name, f.file_id, f.file_name, u2.user_id, u2.user_username
from users u, permissions p 
left join companies c on c.company_id = p.permission_item and p.permission_grant_on = 'companies' 
left join projects pj on pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
left join files f on f.file_id = p.permission_item and p.permission_grant_on = 'files' 
left join users u2 on u2.user_id = p.permission_item and p.permission_grant_on = 'users'  
where u.user_id = p.permission_user
and u.user_id = $user_id";

$urc = mysql_query($usql);



//get username
$unsql = "
select user_id, user_username
from users 
where 
user_id = $user_id";
$uname = mysql_query($unsql);
$uname = mysql_fetch_array($uname);


//Pull all companies
$csql = "select company_id as id, company_name as name from companies order by company_name";
$crc =mysql_query($csql);

//Pull all users 
$u2sql = "select user_id as id, user_username as name from users order by user_username";
$u2rc = mysql_query($u2sql);
 
//Pull all projects
$psql = "select project_id as id, project_name as name from projects order by project_name";
$prc = mysql_query($psql);


//---------------------------------Begin Page -------------------------------//
?>

<script>
function editPerm(w, x, y, z){
	//w =Permission_id	
	//x =permission_grant_on	
	//x =permission_item	
	//z =permission_value
	
	var form = document.perms;
	
	form.sqlaction2.value="edit";
	form.permission_id.value = w;
	x = x.toLowerCase();
	if(x == "companies")form.permission_grant_on.selectedIndex = 0;
	else if(x == "projects")form.permission_grant_on.selectedIndex = 1;
	else if(x == "admin")form.permission_grant_on.selectedIndex = 2;
	else if(x == "all")form.permission_grant_on.selectedIndex = 3;
	else if(x == "webmail")form.permission_grant_on.selectedIndex = 4;
	else if(x == "tasks")form.permission_grant_on.selectedIndex = 5;
	else if(x == "files")form.permission_grant_on.selectedIndex = 6;
	else if(x == "contacts")form.permission_grant_on.selectedIndex = 7;
	else if(x == "forum")form.permission_grant_on.selectedIndex = 8;


	if(z == 1)
	{
		form.permission_value.selectedIndex = 1;
	}
	else if(z ==-1)
	{
	form.permission_value.selectedIndex = 2;
	}
	else
	{
	form.permission_value.selectedIndex = 0;
	}
	setPItem();
}

function setPItem(){
	var form = document.perms;
	x = form.permission_grant_on[form.permission_grant_on.selectedIndex].value;
	x = x.toLowerCase();
	
	//Clear the select list
	y = form.permission_item.length;
	for (var i=0; i < y+1 ; i++) 
	{      
		eval("form.permission_item.options[i]=null")      
	}
	
	//Set option 0 to all
	var option0 = new Option("All", "-1");
	form.permission_item.options[0]=option0;
	
	if(x == "companies")
	{
		<?php 
		$i=1;
		while($crow = mysql_fetch_array($crc))
		{
			echo "var option$i = new Option(\"$crow[name]\", \"$crow[id]\");\n";
			echo "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		?>
	}
	else if (x == "projects")
	{
		<?php 
		$i=1;
		while($crow = mysql_fetch_array($prc))
		{
			echo "var option$i = new Option(\"$crow[name]\", \"$crow[id]\");\n";
			echo "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		?>
	}
	else if (x == "users")
	{
		<?php 
		$i=1;
		while($crow = mysql_fetch_array($u2rc))
		{
			echo "var option$i = new Option(\"$crow[name]\", \"$crow[id]\");\n";
			echo "form.permission_item.options[$i]=option$i;\n";
			$i++;
		}
		?>
	}
}

function clearIt(){
	var form = document.perms;
	form.sqlaction2.value="add";
	form.permission_id.value = 0;
	form.permission_grant_on.selectedIndex = 3;
	setPItem();
}

function changeUser(){
var form = document.topform;
window.location="./index.php?m=admin&a=permissions&user_id=" + form.change_user[form.change_user.selectedIndex].value;
}

	</script>


<TABLE width="75%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<form name="topform">
	<TD><img src="./images/icons/admin.gif" alt="" border="0" width=42 height=42></td>
		<TD nowrap><span class="title">Permissions</span></td>
		<TD width="100%" align="right">
		
		Select user: <Select name="change_user" onchange="changeUser()" style="width: 100px;">
		<?php 
		mysql_data_seek($u2rc, 0);
		while($crow = mysql_fetch_array($u2rc))
		{
			if($crow[id] == $user_id)
			{
				echo "<option value=$crow[id] selected>$crow[name]";
			}
			else
			{
				echo "<option value=$crow[id]>$crow[name]";
			}
		}
		?>
		
		</select>
		</TD>
		<TD align="right" width="99%" nowrap>
		<input type="button" class=button value="back" onClick="javascript:window.location='./index.php?m=admin';"></td>
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
if(mysql_num_rows($urc) == 0)
{
echo '<TR><TD colspan=4 align=center><B>No permissions for this User</b></td></tr>';

};




$i = 0;
while($row = mysql_fetch_array($urc)){
	echo "<TR>";
	echo "<TD><a href=# onClick=\"editPerm($row[permission_id],'$row[permission_grant_on]',$row[permission_item],$row[permission_value]);\">edit</a> | <a href=\"./index.php?a=permissions&m=admin&sqlaction=-1&user_id=$user_id&permission_id=$row[permission_id]\">del</A></td>";
	if($row['permission_grant_on'] == "all" && $row['permission_item'] == -1 && $row['permission_value'] == -1)
	{
		echo "<TD bgcolor=#ffc235>";
	}
	else if($row['permission_item'] == -1 && $row['permission_value'] == -1)
	{
		echo "<TD bgcolor=#ffff99>";
	}
	else
	{
		echo "<TD>";
	}

	echo  $row['permission_grant_on'] . "</td>";
	
	if($row['permission_grant_on'] =="files" && $row['permission_item'] >0)
	{
		$item = $row['file_name'];
	}	
	else if($row['permission_grant_on'] =="users" && $row['permission_item'] >0)
	{
		$item = $row['user_username'];
	}	
	else if($row['permission_grant_on'] =="projects" && $row['permission_item'] >0)
	{
		$item = $row['project_name'];
	}	
	else if($row['permission_grant_on'] =="companies" && $row['permission_item'] >0)
	{
		$item = $row['company_name'];
	}	
	else
	{
		$item = $row['permission_item'];
	}
	
	if($item == "-1") $item = "all";
	
	if($row['permission_value'] ==-1)
	{
		$value = "read-write";
	}	
	else if($row[permission_value] ==1)
	{
		$value = "read-only";
	}	
	else
	{
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
<tr><td bgcolor="#f4efe3">
<TABLE width="100%" border=0 cellpadding="2" cellspacing=0 align="center">
<TR><TD colspan=3 class="mboxhdr">Add or modify permissions</td></tr>
<form sqlaction="http://smitty.dotmarketing.net/dotproject/index.php" method="post" name="perms">

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
	<option value="ticketsmith">Ticketsmith
	<option value="companies">Companies
	<option value="projects">Projects
	<option value="admin">Admin
	<option value="all" selected>All
	<option value="webmail">Webmail
	<option value="tasks">Tasks
	<option value="files">Files
	<option value="contacts">Contacts
	<option value="forums">Forums
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
<TD colspan=2><input type="reset" value="clear" style="font-size:9px;width:100px;" name="sqlaction" onClick="clearIt();"></td>
<TD colspan=2 align="right"><input type="submit" value="add" style="font-size:9px;width:100px;" name="sqlaction2"></td>
</tr>
</form>
</TABLE>
</td></tr>
</table>

