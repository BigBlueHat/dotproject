<?php

global $resourceTab, $AppUI;
$obj =& new CResource;

$query =& new DBQuery;
$obj->setAllowedSQL($AppUI->user_id, $query);
$query->addTable($obj->_tbl);
if ($resourceTab)
  $query->addWhere('resource_type = ' . $_SESSION['resource_type_list'][$resourceTab]['resource_type_id']);
$res =& $query->exec();
?>
<table width='100%' border='0' cellpadding='2' cellspacing='1' class='tbl'>
<tr>
	<th nowrap='nowrap' width='20%'>
    <?php echo $AppUI->_('ID'); ?>
	</th>
  <th nowrap='nowrap' width='70%'>
    <?php echo $AppUI->_('Resource Name'); ?>
  </th>
  <th nowrap='nowrap' width='10%'>
    <?php echo $AppUI->_('Max Alloc %'); ?>
  </th>
</tr>
<?php
  while ($row = db_fetch_assoc($res)) {
?>
<tr>
  <td>
    <a href="index.php?m=resources&a=view&resource_id=<?php echo $row['resource_id'];?>">
    <?php echo $row['resource_key']; ?>
    </a>
  </td>
  <td>
    <a href="index.php?m=resources&a=view&resource_id=<?php echo $row['resource_id'];?>">
    <?php echo $row['resource_name']; ?>
		</a>
  </td>
  <td>
    <?php echo $row['resource_max_allocation']; ?>
  </td>
</tr>
<?php
  }
?>
</table>
