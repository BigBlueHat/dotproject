<?php //$Id$

$perms =& $AppUI->acl();
if (! $perms->checkModule('tasks', 'view'))
	$AppUI->redirect("m=public&a=access_denied");

$taskfield = dPgetParam( $_REQUEST, 'taskfield', 'new_task');
$form = dPgetParam($_REQUEST, 'form', 'form');
	
$proj = $_GET['project'];
$q = new DBQuery;
$q->addTable('tasks');
$q->addQuery('task_id, task_name');
if ($proj != 0)
	$q->addQuery('task_project = ' . $proj);
$tasks = $q->loadList();
?>

<script language="JavaScript">
function loadTasks()
{
  var tasks = new Array();
  var sel = parent.document.forms['<?php echo $form; ?>'].<?php echo $taskfield; ?>;
  while ( sel.options.length )
    sel.options[0] = null;
    
  sel.options[0] = new Option('[top task]', 0);
  <?php
    $i = 0;
    foreach($tasks as $task)
    {
      ++$i;
    ?>
  sel.options[<?php echo $i; ?>] = new Option('<?php echo $task['task_name']; ?>', <?php echo $task['task_id']; ?>);
    <?php
    }
    ?>
  }
  
  loadTasks();
</script>