<?php /* PUBLIC $Id$ */

function selPermWhere( $obj, $idfld, $namefield ) {
	global $AppUI;

	$allowed  = $obj->getAllowedRecords($AppUI->user_id, "$idfld, $namefield");
	if (count($allowed))
		return " $idfld IN (" . implode(",", array_keys($allowed)) . ") ";
	else
		return "";
}

$debug = false;
$callback = dPgetParam( $_GET, 'callback', 0 );
$table = dPgetParam( $_GET, 'table', 0 );
$user_id = dPgetParam( $_GET, 'user_id', 0 );

$ok = $callback & $table;

$title = "Generic Selector";
$select = '';
$from = $table;
$where = '';
$order = '';

$modclass = $AppUI->getModuleClass($table);
if ($modclass && file_exists ($modclass))
	require_once $modclass;

switch ($table) {
case 'companies':
	$obj =& new CCompany;
	$title = 'Company';
	$select = 'company_id,company_name';
	$order = 'company_name';
	$where = selPermWhere( $obj, 'company_id', 'company_name' );
	break;
case 'departments':
// known issue: does not filter out denied companies
	$title = 'Department';
	$company_id = dPgetParam( $_GET, 'company_id', 0 );
	//$ok &= $company_id;  // Is it safe to delete this line ??? [kobudo 13 Feb 2003]
	//$where = selPermWhere( 'companies', 'company_id' );
	$obj =& new CDepartment;
	$where = selPermWhere( $obj, 'dept_id', 'dept_name' );
	if ($where)
		$where .= "\nAND ";
	$where .= "dept_company = company_id ";

	$table .= ", companies";
	$hide_company = dPgetParam( $_GET, 'hide_company', 0 );
	if ( $hide_company == 1 ){
		$select = "dept_id, dept_name";
	}else{
		$select = "dept_id,CONCAT_WS(': ',company_name,dept_name) AS dept_name";
	}
	if ($company_id) {
		$where .= "\nAND dept_company = $company_id";
		$order = 'dept_name';
	} else {
		$order = 'company_name,dept_name';
	}
	break;
case 'forums':
	$title = 'Forum';
	$select = 'forum_id,forum_name';
	$order = 'forum_name';
	break;
case 'projects':
	$project_company = dPgetParam( $_GET, 'project_company', 0 );

	$title = 'Project';
	$obj =& new CProject;
	$select = 'project_id,project_name';
	$order = 'project_name';
	$where_clause = array();
	if ($user_id > 0) {
		$where_clause[] = " project_contacts regex \",*{$user_id},*\" ";
	}
	$pwhere = selPermWhere( $obj, 'project_id', 'project_name' );
	if ($pwhere) {
		$where_clause[] = $pwhere;
	}
	if ($project_company) {
		$where_clause[] = "AND project_company = $project_company";
	}
	$where = implode("\nAND ", $where_clause);
	break;
	
case "tasks":
	$task_project = dPgetParam( $_GET, 'task_project', 0 );

	$title = 'Task';
	$select = 'task_id,task_name';
	$order = 'task_name';
	$where = $task_project ? "task_project = $task_project" : '';
	break;
case 'users':
	$title = 'User';
	$select = "user_id,CONCAT_WS(' ',contact_first_name,contact_last_name)";
	$order = 'contact_first_name';
	$from .= ", contacts";
	$where .= "user_contact = contact_id";
	break;
case 'SGD':
	$title = 'Document';
	$select = 'SGD_id, SGD_name';
	$order = 'SGD_name';
	break;
default:
	$ok = false;
	break;
}

if (!$ok) {
	echo "Incorrect parameters passed\n";
	if ($debug) {
		echo "<br />callback = $callback \n";
		echo "<br />table = $table \n";
		echo "<br />ok = $ok \n";
	}
} else {
	$sql = "SELECT $select FROM $table";
	$sql .= $where ? " WHERE $where" : '';
	$sql .= $order ? " ORDER BY $order" : '';
	//echo "<pre>$sql</pre>";

	$list = arrayMerge( array( 0=>$AppUI->_( '[none]' )), db_loadHashList( $sql ) );
	echo db_error();
?>
<script language="javascript">
	function setClose(key, val){
		window.opener.<?php echo $callback;?>(key,val);
		window.close();
	}

	window.onresize = window.onload = function setHeight(){

		if (document.compatMode && document.compatMode != "BackCompat" && document.documentElement.clientHeight)
			var wh = document.documentElement.clientHeight;
		else
			var wh = document.all ? document.body.clientHeight : window.innerHeight;
   
		var selector = document.getElementById("selector");
		var count = 0;
		obj = selector;
		while(obj!=null){
			count += obj.offsetTop;
			obj = obj.offsetParent;
		}
		selector.style.height = (wh - count - 5) + "px";

	}

</script>
<form name="frmSelector">
<b><?=$AppUI->_( 'Select' ).' '.$AppUI->_( $title ).':'?></b>
<table width="100%">
<tr>
	<td>
		<div style="white-space:normal; overflow:auto; "  id="selector">
		<ul style="padding-left:0px">
		<?php
			if (count( $list ) > 1) {
		//		echo arraySelect( $list, 'list', ' size="8"', 0 );
				foreach ($list as $key => $val) {
					echo "<li><a href=\"javascript:setClose('$key','".addslashes($val)."');\">$val</a></li>\n";
				}
			} else {
				echo $AppUI->_( "no$table" );
			}
		?>
		</ul>
		</div>
	</td>
	<td valign="bottom">
				<input type="button" class="button" value="<?php echo $AppUI->_( 'cancel' );?>" onclick="window.close()" />
	</td>
</tr>
</table>
</form>

<?php } ?>

