<?php
$history_id = defVal( @$_GET["history_id"], 0);

/*
// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
*/

$action = @$_REQUEST["action"];
if($action) {
	$history_description = $_POST["history_description"];
	$history_project = $_POST["history_project"];
	$userid = $AppUI->user_id;
	
	if( $action == "add" ) {
		$sql = "INSERT INTO history (history_date, history_description, history_user, history_project) " .
		  "VALUES (now(), '$history_description', $userid, $history_project)";
		$okMsg = "Historia agregada";
	} else if ( $action == "update" ) {
		$sql = "UPDATE history SET history_description = '$history_description', history_project = '$history_project' WHERE history_id = $history_id";
		$okMsg = "Historia actualizada";
	} else if ( $action == "del" ) {
		$sql = "DELETE FROM history WHERE history_id = $history_id";
		$okMsg = "Historia eliminada";				
	}
	if(!db_exec($sql)) {
		$AppUI->setMsg( db_error() );
	} else {	
		$AppUI->setMsg( $okMsg );
	}
	$AppUI->redirect();
}

// pull the history
$sql = "SELECT * FROM history WHERE history_id = $history_id";
db_loadHash( $sql, $history );

?>

<form name="AddEdit" method="post">				
<table width="100%" border="0" cellpadding="0" cellspacing="1">
<input name="action" type="hidden" value="<?php echo $history_id ? "update" : "add"  ?>">
<tr>
	<td><img src="./images/icons/tasks.gif" alt="" border="0"></td>
	<td align="left" nowrap="nowrap" width="100%"><h1><?php echo $AppUI->_( $history_id ? 'Edit history' : 'New history' );?></h1></td>
</tr>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td width="50%" align="right">
		<a href="javascript:delIt()"><img align="absmiddle" src="./images/icons/trash.gif" width="16" height="16" alt="" border="0"><?php echo $AppUI->_('delete history');?></a>
	</td>
</tr>
</table>

<table border="1" cellpadding="4" cellspacing="0" width="98%" class="std">
	
<script>
	function delIt() {
		AddEdit.action.value = "del";
		AddEdit.submit();
	}	
</script>
	
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Project' );?>:</td>
	<td width="60%">
<?php
// pull the projects list
$sql = "SELECT project_id,project_name FROM projects ORDER BY project_name";
$projects = arrayMerge( array( 0 => '(any)' ), db_loadHashList( $sql ) );
echo arraySelect( $projects, 'history_project', 'class="text"', $history["history_project"] );
?>
	</td>
</tr>
	
<tr>
	<td align="right" nowrap="nowrap"><?php echo $AppUI->_( 'Description' );?>:</td>
	<td width="60%">
		<textarea name="history_description" class="textarea" cols="60" rows="5" wrap="virtual"><?php echo $history["history_description"];?></textarea>
	</td>
</tr>	
		
<table border="0" cellspacing="0" cellpadding="3" width="98%">
<tr>
	<td height="40" width="30%">&nbsp;</td>
	<td  height="40" width="35%" align="right">
		<table>
		<tr>
			<td>
				<input class="button" type="button" name="cancel" value="cancel" onClick="javascript:if(confirm('Are you sure you want to cancel.')){location.href = '?<?php echo $AppUI->getPlace();?>';}">
			</td>
			<td>
				<input class="button" type="button" name="btnFuseAction" value="save" onClick="submit()">
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>	
	
</table>
</form>		
</body>
</html>
