<?php
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
    $AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

if (isset( $_GET['tab'] )) {
    $AppUI->setState( 'UserIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'UserIdxTab' ) !== NULL ? $AppUI->getState( 'UserIdxTab' ) : 0;

if (isset( $_GET['where'] )) {
    $AppUI->setState( 'UserIdxWhere', $_GET['where'] );
}
$where = $AppUI->getState( 'UserIdxWhere' ) ? $AppUI->getState( 'UserIdxWhere' ) : '%';

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
?>
<script language="javascript">
function delMe( x, y ) {
    if (confirm( "Are you sure you want\nto delete user " + y + "?" )) {
        top.location="?m=admin&a=dosql&del=1&user_id=" + x;
    }
}
</script>

<table cellpadding="0" cellspacing="1" border="0" width="98%">
<tr>
    <td valign="top"><img src="./images/icons/admin.gif" alt="" border="0" width="42" height="42" /></td>
    <td nowrap><h1><?php echo $AppUI->_('User Management');?></h1></td>
    <td align="right">
        <table cellpadding="2" cellspacing="1" border="0">
        <tr>
            <td width="100%" align="right"><?php echo $AppUI->_('Show');?>: </td>
            <td><a href="./index.php?m=admin&where=0"><?php echo $AppUI->_('All');?></a></td>
<?php
    for ($a=65; $a < 91; $a++) {
        $cu = chr( $a );
        $cell = strpos($let, "$cu") > 0 ?
            "<a href=\"?m=admin&where=$cu\">$cu</a>" :
            "<font color=\"#999999\">$cu</font>";
        echo "<td>$cell</td>";
    }
?>
        </tr>
        </table>
    </td>
    <td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'" />', 'ID_HELP_USER_IDX' );?></td>
</tr>
</table>

<?php
$extra = '<td align="right" width="100%"><input type="button" class=button value="'.$AppUI->_('add user').'" onClick="javascript:window.location=\'./index.php?m=admin&a=addedituser\';" /></td>';

// tabbed information boxes
$tabBox = new CTabBox( "?m=admin", "{$AppUI->cfg['root_dir']}/modules/admin/", $tab );
$tabBox->add( 'vw_active_usr', 'Active Users' );
$tabBox->add( 'vw_inactive_usr', 'In-Active Users' );
$tabBox->show( $extra );
?>
