<?php  /* $Id$ */
$nav = array(
    array( 'companies', 'money.gif', 'Companies' ),
    array( 'projects', 'projects.gif', 'Projects' ),
    array( 'tasks', 'tasks.gif', 'Tasks' ),
    array( 'calendar', 'calendar.gif', 'Calendar' ),
    array( 'files', 'folder.gif', 'Files' ),
    array( 'contacts', 'contacts.gif', 'Contacts' ),
    array( 'forums', 'communicate.gif', 'Forums' ),
    array( 'ticketsmith', 'ticketsmith.gif', 'Tickets' ),
    array( 'admin', 'admin.gif', 'User Admin' ),
    array( 'system', 'system.gif', 'System Admin' )
);
?>
<!doctype html public "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<script language="JavaScript">
function doBtn() {
    var oEl = event.srcElement;
    var doit = event.type;

    while (-1 == oEl.className.indexOf( "Btn" )) {
        oEl = oEl.parentElement;
        if (!oEl) {
            return;
        }
    }
    if (doit == "mouseover" || doit == "mouseup") {
        oEl.className = "clsBtnOn";
    } else if (doit == "mousedown") {
        oEl.className = "clsBtnDown";
    } else {
        oEl.className = "clsBtnOff";
    }
}
function tboff(){
    var oEl = event.srcElement;
    var doit = event.type;
    oEl.className = "topBtnOff";
}
</script>
<title><?php echo $page_title;?></title>
<link rel="stylesheet" type="text/css" href="./style/<?php echo $uistyle ?>/main.css">
</head>
<body bgcolor="#f0f0f0" topmargin="0" leftmargin="0" marginheight="0" marginwidth="0">

<table width="100%" cellpadding="3" cellspacing="0" border="0">
<tr>
    <td background="style/<?php echo $uistyle ?>/images/titlegrad.jpg" style="background-color:#a5cbf7;color:#ffffff"><b>dotproject</b></a>
</tr>
<tr>
    <td style="border: #848284 1px outset;background-color:#d5d3ce;color:#000000">
        <table width="100%" cellpadding="1" cellspacing="0" width="100%">
        <tr>
            <td>
<?php
$links = array();
foreach ($nav as $module) {
    if (isset( $perms['all'] ) || isset( $perms[$module[0]] )) {
        $links[] = '<a href="?m='.$module[0].'">'.$AppUI->_($module[2]).'</a>';
    }
}
echo implode( ' | ', $links );
?>
            </td>
            <form name="frm_new" method=GET action="./index.php">
<?php
    echo '<td nowrap="nowrap" align="right">';
    $newItem = array( ""=>'- New Item -' );

    if ($AppUI->getProject()) {
        $newItem["tasks"] = "Task";
    } else if (!empty( $task_id ) && $task_id > 0) {
        $sql = "SELECT task_project FROM tasks WHERE task_id = $task_id";
        if ($rc = db_exec( $sql )) {
            if ($row = db_fetch_row( $rc )) {
                $AppUI->setProject( $row[0] );
                $newItem["tasks"] = "Task";
            }
        }
    }

    $newItem["projects"] = "Project";
    $newItem["companies"] = "Company";
    $newItem["files"] = "File";
    $newItem["contacts"] = "Contact";
    $newItem["calendar"] = "Event";

    echo arraySelect( $newItem, 'm', 'style="font-size:10px" onChange="f=document.frm_new;mod=f.m.options[f.m.selectedIndex].value;if(mod) f.submit();"', '', true);

    echo '</td><input type="hidden" name="a" value="addedit">';

//build URI string
	if (isset( $company_id )) {
		echo '<input type="hidden" name="company_id" value="'.$company_id.'">';
	}
	if (isset( $task_id )) {
		echo '<input type="hidden" name="task_parent" value="'.$task_id.'">';
	}
	if (isset( $file_id )) {
		echo '<input type="hidden" name="file_id" value="'.$file_id.'">';
	}
?>
            </form>
            </td>
        </tr>
        </table>
    </td>
</tr>
<tr>
    <td>
        <table cellspacing="0" cellpadding="3" border="0" width="100%">
        <tr>
            <td width="100%">Welcome <?php echo "$AppUI->user_first_name $AppUI->user_last_name"; ?></td>
            <td nowrap="nowrap">
                <a href="?m=help"><?php echo $AppUI->_( 'Help' );?></a> |
                <a href="./index.php?m=admin&a=viewuser&user_id=<?php echo $AppUI->user_id;?>" onmouseover="doBtn();"><?php echo $AppUI->_('My Info');?></a> |
                <a href="./index.php?logout=-1" onmouseover="doBtn();"><?php echo $AppUI->_('Logout');?></a>
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="4" border="0">
<tr>
<td valign="top" align="left" width="98%">
<?php 
    echo $AppUI->getMsg();
?>
<!-- <img src="images/shim.gif" width="1" height="5" alt="" border="0"><br> -->
