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

function show_history($history)
{
//        return $history;
	GLOBAL $AppUI;
        $id = $history['history_item'];
        $module = $history['history_table'];        
	$table_id = (substr($module, -1) == 's'?substr($module, 0, -1):$module);
	if (substr($table_id, -2) == 'ie')
		$table_id = substr($table_id, 0, -2) . 'y';
 	$table_id .= '_id';
	$item_name = substr($table_id, 0, -2) . 'name';
        
        if ($module == 'login')
               return 'User \'' . $history['history_description'] . '\' ' . $history['history_action'] . '.';
        
        if ($history['history_action'] == 'add')
                $msg = $AppUI->_('Added new').' ';
        else if ($history['history_action'] == 'update')
                $msg = $AppUI->_('Modified').' ';
        else if ($history['history_action'] == 'delete')
                return $AppUI->_('Deleted').' \'' . $history['history_description'] . '\' '.$AppUI->_('from').' ' . $AppUI->_($module) . $AppUI->_('module');

	$q  = new DBQuery;
	$q->addTable($module);
	$q->addQuery('*');
	$q->addWhere($table_id.' ='.$id);
	list($item) = $q->loadList();
	if ($item)
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
                $module = 'Tasks';
                $link = '&a=view&task_id=170&tab=1&task_log_id=';
                break;
        }

	if (!empty($link)) 
		$link = '<a href="?m='.$module.$link.$id.'">'.($item[$item_name]?$item[$item_name]:$history['history_description']).'</a>';
	else
		$link = ($item[$item_name]?$item[$item_name]:$history['history_description']);
		$msg .= $AppUI->_('item')." '$link' ".$AppUI->_('in').' '.$AppUI->_(ucfirst($module)).' '.$AppUI->_('module'); // . $history;
	
        return $msg;
}

$filter = array();
if (!empty($_REQUEST['filter']))
        $filter[] = 'history_table = \'' . $_REQUEST['filter'] . '\' ';
if (!empty($_REQUEST['project_id']))
{
	$project_id = $_REQUEST['project_id'];
	
$q  = new DBQuery;
$q->addTable('tasks');
$q->addQuery('task_id');
$q->addWhere('task_project = ' . $project_id);
$project_tasks = implode(',', $q->loadColumn());
if (!empty($project_tasks))
	$project_tasks = "OR (history_table = 'tasks' AND history_item IN ($project_tasks))";

$q->addTable('files');
$q->addQuery('file_id');
$q->addWhere('file_project = ' . $project_id);
$project_files = implode(',', $q->loadColumn());
if (!empty($project_files))
	$project_files = "OR (history_table = 'files' AND history_item IN ($project_files))";

	$filter[] = "(
	(history_table = 'projects' AND history_item = '$project_id')
	$project_tasks
	$project_files
	)";
}

$q  = new DBQuery;
$q->addTable('history');
$q->addTable('users');
$q->addWhere('history_user = user_id');
$q->addWhere($filter);
$q->addOrder('history_date DESC');
$history = $q->loadList();

foreach ($history as $key => $row)
{
	$module = $row['history_table'] == 'task_log'?'tasks':$row['history_table'];
	$row['history_table'] = $module;
	$history[$key]['history_display'] = show_history($row);

	$perms = & $AppUI->acl();
  if ($module != 'login' && !$perms->checkModuleItem($module, "access", $row['history_item']))
  	unset($history[$key]);
}

$tpl->displayList('history', $history);
?>
