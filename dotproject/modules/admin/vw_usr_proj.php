<?php
GLOBAL $user_id;

$psql = "
SELECT projects.*
FROM projects
WHERE project_owner = $user_id
ORDER BY project_name
";
$prc = mysql_query($psql);
$nums = mysql_num_rows($prc);

//pull the projects into an temp array
$tarr = array();
for($x=0;$x<$nums;$x++){
	$tarr[$x] = mysql_fetch_array($prc);
}

$pstatus = array(
	'Not Defined',
	'Proposed',
	'In planning',
	'In progress',
	'On hold',
	'Complete'
);
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th>Name</th>
	<th>Status</th>
</tr>

<?php
for ($x =0; $x < $nums; $x++){
	if ($tarr[$x]["project_active"] <> 0) {
	?>
<tr>
	<td>
		<A href="./index.php?m=projects&a=view&project_id=<?php echo $tarr[$x]["project_id"];?>">
			<?php echo $tarr[$x]["project_name"];?>
		</a>
	<td><?php echo $pstatus[$tarr[$x]["project_status"]]; ?></td>
</tr>
<?php
	}
}
?>
</table>
