<?php /* HISTORY $Id$ */
##
## History module
## (c) Copyright
## J. Christopher Pereira (kripper@imatronix.cl)
## IMATRONIX
## 

$AppUI->savePlace();
?>
<table width="100%" border="0" cellpadding="0" cellspacing=1>
<tr>
	<td><img src="./images/icons/tasks.gif" alt="Tasks" border="0" width="44" height="38"></td>
	<td nowrap width="100%"><h1><?php echo $AppUI->_('History');?></h1></td>
</tr>
</table>
	
<table width="100%" cellspacing="1" cellpadding="0" border="0">
<tr>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('Add history');?>" onclick="window.location='?m=history&a=addedit'"></td>
</table>
	

<?php

function show_history($history)
{
//        return $history;
        $limit = strpos($history, '_');
        $module = substr($history, 0, $limit);
        $history = substr($history, $limit + 1);
        $limit = strpos($history, '(');
        $action = substr($history, 0, $limit);
        $history = substr($history, $limit + 1);
        $id = substr($history, 0, -1);
        $history = substr($history, 0, -1);
        
        if ($action == 'add')
                $msg = 'Added new ';
        else if ($action == 'update')
                $msg = 'Modified ';
        else if ($action == 'delete')
                return 'Deleted (' . $history . ') from ' . $module;
        
        if ($module == 'files')
                $link = '&a=addedit&file_id=';
        else if ($module == 'tasks')
                $link = '&a=view&task_id=';
        else if ($module == 'forum')
                $link = '&a=viewer&forum_id=';
        else if ($module == 'projects')
                $link = '&a=view&project_id=';
        else if ($module == 'companies')
                $link = '&a=view&company_id=';
        else if ($module == 'contacts')
                $link = '&a=view&contact_id=';

        $msg .= '<a href="?m=' . $module . $link . $id . '">item</a> in ';

        $msg .= $module . ' module.'; // . $history;

        return $msg;
}

$psql = 
"SELECT * from history, users WHERE history_user = user_id ORDER BY history_date DESC";
$prc = db_exec( $psql );
echo db_error();

$history = array();

?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">&nbsp;</th>
	<th width="200"><?php echo $AppUI->_('Date');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Description');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('User');?>&nbsp;&nbsp;</th>
</tr>
<?php
while ($row = db_fetch_assoc( $prc )) {
?>
<tr>	
	<td><a href='<?php echo "?m=history&a=addedit&history_id=" . $row["history_id"] ?>'><img src="./images/icons/pencil.gif" alt="<?php echo $AppUI->_( 'Edit History' ) ?>" border="0" width="12" height="12"></a></td>
	<td><?php echo $row["history_date"]?></td>
	<td><?php echo show_history($row["history_description"]) ?></td>	
	<td><?php echo $row["user_username"]?></td>
</tr>	
<?php
}
?>
</table>
