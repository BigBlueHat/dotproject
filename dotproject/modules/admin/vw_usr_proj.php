<?php /* ADMIN $Id$ */
GLOBAL $AppUI, $user_id;

$sql = "
SELECT projects.*
FROM projects
WHERE project_owner = $user_id
	AND project_active <> 0
ORDER BY project_name
";
$projects = db_loadList( $sql );

$pstatus = dPgetSysVal( 'ProjectStatus' );
?>
<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th><?php echo $AppUI->_('Name');?></th>
	<th><?php echo $AppUI->_('Status');?></th>
</tr>

<?php foreach ($projects as $row) {	?>
<tr>
	<td>
		<a href="?m=projects&a=view&project_id=<?php echo $row["project_id"];?>">
			<?php echo $row["project_name"];?>
		</a>
	<td><?php echo $pstatus[$row["project_status"]]; ?></td>
</tr>
<?php } ?>
</table>
