<?php
GLOBAL $user_id;

//Pull User perms
$usql = "
SELECT u.user_id, u.user_username,
	p.permission_item, p.permission_id, p.permission_grant_on, p.permission_value,
	c.company_id, c.company_name,
	pj.project_id, pj.project_name,
	f.file_id, f.file_name,
	u2.user_id, u2.user_username
FROM users u, permissions p
LEFT JOIN companies c ON c.company_id = p.permission_item and p.permission_grant_on = 'companies'
LEFT JOIN projects pj ON pj.project_id = p.permission_item and p.permission_grant_on = 'projects'
LEFT JOIN files f ON f.file_id = p.permission_item and p.permission_grant_on = 'files'
LEFT JOIN users u2 ON u2.user_id = p.permission_item and p.permission_grant_on = 'users'
WHERE u.user_id = p.permission_user
	AND u.user_id = $user_id
";

$urc = mysql_query($usql);
$nums = mysql_num_rows($urc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array($urc);
}
?>

<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>Module</th>
	<th>Item</th>
	<th>Permission Type</th>
</tr>

<?php
for ($x =0; $x < $nums; $x++){
	echo '<tr><td>' . $tarr[$x]['permission_grant_on'] . "</td>";

	if ($tarr[$x]['permission_grant_on'] =="files" && $tarr[$x]['permission_item'] > 0) {
		$item = $tarr[$x]['file_name'];
	} else if ($tarr[$x]['permission_grant_on'] =="users" && $tarr[$x]['permission_item'] > 0) {
		$item = $tarr[$x]['user_username'];
	} else if ($tarr[$x]['permission_grant_on'] =="projects" && $tarr[$x]['permission_item'] > 0) {
		$item = $tarr[$x]['project_name'];
	} else if ($tarr[$x]['permission_grant_on'] =="companies" && $tarr[$x]['permission_item'] > 0) {
		$item = $tarr[$x]['company_name'];
	} else {
		$item = $tarr[$x]['permission_item'];
	}

	if ($item == "-1") {
		$item = "all";
	}
	if ($tarr[$x]['permission_value'] == -1) {
		$value = "read-write";
	} else if ($tarr[$x]['permission_value'] == 1) {
		$value = "read-only";
	} else {
		$value = "deny";
	}
	echo "<td>$item</td><td>$value</td></tr>";
}
?>
</table>
