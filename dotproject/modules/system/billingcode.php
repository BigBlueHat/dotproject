<?php /* SYSTEM $Id$ */
##
## add or edit a user preferences
##
$company_id=0;
$company_id = isset($_REQUEST['company_id']) ? $_REQUEST['company_id'] : 0;
// Check permissions
if (!$canEdit) {
  $AppUI->redirect('m=public&a=access_denied' );
}

$q  = new DBQuery;
$q->addTable('billingcode','bc');
$q->addQuery('billingcode_id, billingcode_name, billingcode_value, billingcode_desc');
$q->addOrder('billingcode_name ASC');
$q->addWhere('bc.billingcode_status = 0');
$q->addWhere('company_id = ' . $company_id);
$billingcodes = $q->loadList();
$q->clear();

$q  = new DBQuery;
$q->addTable('companies','c');
$q->addQuery('company_id, company_name');
$q->addOrder('company_name ASC');
$company_list = $q->loadHashList();
$company_list[0] = $AppUI->_('Select Company');
$q->clear();

$company_name = $company_list[$company_id];

function showcodes(&$a) {
        global $AppUI;

        $s = "\n<tr height=20>";
        $s .= "<td width=40><a href=\"javascript:delIt2({$a['billingcode_id']});\" title=\"".$AppUI->_('delete')."\"><img src=\"./images/icons/stock_delete-16.png\" border=\"0\" alt=\"Delete\"></a></td>";
        $alt = htmlspecialchars( $a["billingcode_desc"] );
        $s .= '<td align=left>&nbsp;<a href="./index.php?m=tasks&a=view&task_id=' . $a["billingcode_id"] . '" title="' . $alt . '">' . $a["billingcode_name"] . '</a></td>';
        $s .= '<td nowrap="nowrap" align=center>'.$a["billingcode_value"].'</td>';
        $s .= '<td nowrap="nowrap">'.$a["billingcode_desc"].'</td>';
        $s .= "</tr>\n";
        echo $s;
}

$titleBlock = new CTitleBlock( 'Edit Billing Codes', 'myevo-weather.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
?>
<script language="javascript">
function submitIt(){
        var form = document.changeuser;
        form.submit();
}

function changeIt() {
        var f=document.changeMe;
        var msg = '';
        f.submit();
}


function delIt2(id) {
        document.frmDel.billingcode_id.value = id;
        document.frmDel.submit();
}
</script>

<form name="changeMe" action="./index.php?m=system&a=billingcode" method="post">
        <?php echo arraySelect( $company_list, 'company_id', 'size="1" class="text" onchange="changeIt();"', $company_id, false );?>
</form>

<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">
<form name="frmDel" action="./index.php?m=system" method="post">
        <input type="hidden" name="dosql" value="do_billingcode_aed" />
        <input type="hidden" name="del" value="1" />
        <input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
        <input type="hidden" name="billingcode_id" value="" />
</form>

<form name="changeuser" action="./index.php?m=system" method="post">
        <input type="hidden" name="dosql" value="do_billingcode_aed" />
        <input type="hidden" name="del" value="0" />
        <input type="hidden" name="company_id" value="<?php echo $company_id; ?>" />
        <input type="hidden" name="billingcode_status" value="0" />

<tr height="20">
        <th width="40">&nbsp;</th>
        <th><?php echo $AppUI->_('Billing Code');?></th>
        <th><?php echo $AppUI->_('Value');?></th>
        <th><?php echo $AppUI->_('Description');?></th>
</tr>

<?php
        foreach($billingcodes as $code) {
                showcodes( $code);
        }
?>

<tr>
        <td>&nbsp;</td>
        <td><input type="text" name="billingcode_name" value=""></td>
        <td><input type="text" name="billingcode_value" value=""></td>
        <td><input type="text" name="billingcode_desc" value=""</td>
</tr>

<tr>
        <td align="left"><input class="button"  type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" /></td>
        <td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" /></td>
</tr>
</table>
