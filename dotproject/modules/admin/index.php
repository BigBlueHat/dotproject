<?php /* $Id$ */
$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'UserIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserIdxTab' ) !== NULL ? $AppUI->getState( 'UserIdxTab' ) : 0;


if (isset( $_GET['stub'] )) {
    $AppUI->setState( 'UserIdxStub', $_GET['stub'] );
    $AppUI->setState( 'UserIdxWhere', '' );
} else if (isset( $_POST['where'] )) { 
    $AppUI->setState( 'UserIdxWhere', $_POST['where'] );
    $AppUI->setState( 'UserIdxStub', '' );
}
$stub = $AppUI->getState( 'UserIdxStub' );
$where = $AppUI->getState( 'UserIdxWhere' );

if (isset( $_GET['orderby'] )) {
    $AppUI->setState( 'UserIdxOrderby', $_GET['orderby'] );
}
$orderby = $AppUI->getState( 'UserIdxOrderby' ) ? $AppUI->getState( 'UserIdxOrderby' ) : 'user_username';

// Pull First Letters
$let = ":";
$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_username, 1, 1)) AS L FROM users";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    $let .= $L['L'];
}
$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_first_name, 1, 1)) AS L FROM users";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    if ($L['L'])
	$let .= strpos($let, $L['L']) ? '' : $L['L'];
}

$sql = "SELECT DISTINCT UPPER(SUBSTRING(user_last_name, 1, 1)) AS L FROM users";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
    if ($L['L'])
	$let .= strpos($let, $L['L']) ? '' : $L['L'];
}

$a2z = "\n<table cellpadding=\"2\" cellspacing=\"1\" border=\"0\">";
$a2z .= "\n<tr>";
$a2z .= '<td width="100%" align="right">' . $AppUI->_('Show'). ': </td>';
$a2z .= '<td><a href="./index.php?m=admin&stub=0">' . $AppUI->_('All') . '</a></td>';
for ($c=65; $c < 91; $c++) {
	$cu = chr( $c );
	$cell = strpos($let, "$cu") > 0 ?
		"<a href=\"?m=admin&stub=$cu\">$cu</a>" :
		"<font color=\"#999999\">$cu</font>";
	$a2z .= "\n\t<td>$cell</td>";
}
$a2z .= "\n</tr>\n</table>";

// setup the title block
$titleBlock = new CTitleBlock( 'User Management', 'helix-setup-users.png', $m, "$m.$a" );

$where = dPformSafe( $where, true );

$titleBlock->addCell(
	'<input type="text" name="where" class="text" size="10" value="'.$where.'" />'
	. ' <input type="submit" value="'.$AppUI->_( 'search' ).'" class="button" />',
	'',
	'<form action="index.php?m=admin" method="post">', '</form>'
);

$titleBlock->addCell( $a2z );
$titleBlock->show();
?>
<script language="javascript">
function delMe( x, y ) {
	if (confirm( "<?php echo $AppUI->_('doDelete').' '.$AppUI->_('User');?> " + y + "?" )) {
		document.frmDelete.user_id.value = x;
		document.frmDelete.submit();
	}
}
</script>

<?php
$extra = '<td align="right" width="100%"><input type="button" class=button value="'.$AppUI->_('add user').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>';

// tabbed information boxes
$tabBox = new CTabBox( "?m=admin", "{$AppUI->cfg['root_dir']}/modules/admin/", $tab );
$tabBox->add( 'vw_active_usr', 'Active Users' );
$tabBox->add( 'vw_inactive_usr', 'Inactive Users' );
$tabBox->show( $extra );
?>

<form name="frmDelete" action="./index.php?m=admin" method="post">
	<input type="hidden" name="dosql" value="do_user_aed" />
	<input type="hidden" name="del" value="1" />
	<input type="hidden" name="user_id" value="0" />
</form>
