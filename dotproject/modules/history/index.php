<?php /* HISTORY $Id$ */
##
## History module
## (c) Copyright
## J. Christopher Pereira (kripper@imatronix.cl)
## IMATRONIX
## 

$AppUI->savePlace();
$titleBlock = new CTitleBlock( 'History', 'stock_book_blue_48.png', $m, "$m.$a" );
$titleBlock->show();
?>
<table width="100%" cellspacing="1" cellpadding="0" border="0">
<tr>
        <td nowrap align="right">
<form name="filter" action="?m=history" method="post" onChange="document.filter.submit()">
Changes to:
        <select name="filter">
                <option value=""></option>
                <option value="">Show all</option>
                <option value="projects">Projects</option>
                <option value="files">Files</option>
                <option value="forums">Forums</option>
                <option value="login">Login/Logouts</option>
        </select>
</form>
        </td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('Add history');?>" onclick="window.location='?m=history&a=addedit'"></td>
</table>


<?php

function show_history($history)
{
//        return $history;
        $id = $history['history_item'];
        $module = $history['history_table'];        
	$table_id = (substr($module, -1) == 's'?substr($module, 0, -1):$module) . '_id';
        
        if ($module == 'login')
               return 'User \'' . $history['history_description'] . '\' ' . $history['history_action'] . '.';
        
        if ($history['history_action'] == 'add')
                $msg = 'Added new ';
        else if ($history['history_action'] == 'update')
                $msg = 'Modified ';
        else if ($history['history_action'] == 'delete')
                return 'Deleted \'' . $history['history_description'] . '\' from ' . $module . ' module.';

	$q  = new DBQuery;
	$q->addTable($module);
	$q->addQuery($table_id);
	$q->addWhere($table_id.' ='.$id);
	$sql = $q->prepare();
	$q->clear();
	if (db_loadResult($sql))
        switch ($module)
        {
        case 'history':
                $link = '&a=addedit&history_id='; break;
        case 'files':
                $link = '&a=addedit&file_id='; break;
        case 'tasks':
                $link = '&a=view&task_id='; break;
        case 'forums':
                $link = '&a=viewer&forum_id='; break;
        case 'projects':
                $link = '&a=view&project_id='; break;
        case 'companies':
                $link = '&a=view&company_id='; break;
        case 'contacts':
                $link = '&a=view&contact_id='; break;
        case 'task_log':
                $module = 'tasks';
                $link = '&a=view&task_id=170&tab=1&task_log_id=';
                break;
        }

	if (!empty($link))
		$link = '<a href="?m='.$module.$link.$id.'">'.$history['history_description'].'</a>';
	else
		$link = $history['history_description'];
        $msg .= "item '$link' in $module module."; // . $history;

        return $msg;
}

$filter = '';
if (!empty($_REQUEST['filter']))
        $filter = ' AND history_table = \'' . $_REQUEST['filter'] . '\' ';
if (!empty($_REQUEST['project_id']))
{
	$project_id = $_REQUEST['project_id'];
	
$q  = new DBQuery;
$q->addTable('tasks');
$q->addQuery('task_id');
$q->addWhere('task_project = ' . $project_id);
$sql = $q->prepare();
$q->clear();
$project_tasks = implode(',', db_loadColumn($sql));
if (!empty($project_tasks))
	$project_tasks = "OR (history_table = 'tasks' AND history_item IN ($project_tasks))";

$q->addTable('files');
$q->addQuery('file_id');
$q->addWhere('file_project = ' . $project_id);
$sql = $q->prepare();
$q->clear();
$project_files = implode(',', db_loadColumn($sql));
if (!empty($project_files))
	$project_files = "OR (history_table = 'files' AND history_item IN ($project_files))";

	$filter .= " AND (
	(history_table = 'projects' AND history_item = '$project_id')
	$project_tasks
	$project_files
	)";
}
$q = new DBQuery;
$q->addTable('history');
$q->addTable('users');
$q->addWhere('history_user = user_id'.$filter);
$q->addOrder('history_date DESC');
$history = $q->loadList();
$q->clear();
?>
<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th width="10">&nbsp;</th>
	<th width="200"><?php echo $AppUI->_('Date');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('Description');?></th>
	<th nowrap="nowrap"><?php echo $AppUI->_('User');?>&nbsp;&nbsp;</th>
</tr>
<?php
foreach($history as $row) {
  $module = $row['history_table'] == 'task_log'?'tasks':$row['history_table'];
  // Checking permissions.
  // TODO: Enable the lines below to activate new permissions.
  $perms = & $AppUI->acl();
  if ($module == 'login' || $perms->checkModuleItem($module, "access", $row['history_item']))
  {
?>
<tr>	
	<td><a href='<?php echo "?m=history&a=addedit&history_id=" . $row["history_id"] ?>'><img src="./images/icons/pencil.gif" alt="<?php echo $AppUI->_( 'Edit History' ) ?>" border="0" width="12" height="12"></a></td>
	<td><?php echo $row["history_date"]?></td>
	<td><?php echo show_history($row) ?></td>	
	<td><?php echo $row["user_username"]?></td>
</tr>	
<?php
  }
}
?>
</table>
