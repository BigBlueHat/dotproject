<?php
// check permissions
$denyRead = getDenyRead( $m );
$denyEdit = getDenyEdit( $m );

if ($denyRead) {
	$AppUI->redirect( "m=help&a=access_denied" );
}
$AppUI->savePlace();

if (isset( $_GET['where'] )) {
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}
$where = $AppUI->getState( 'ContIdxWhere' ) ? $AppUI->getState( 'ContIdxWhere' ) : '%';

$orderby = 'contact_order_by';

// Pull First Letters
$let = ":";
$sql = "SELECT DISTINCT LOWER(SUBSTRING($orderby,1,1)) as L FROM contacts";
$arr = db_loadList( $sql );
foreach( $arr as $L ) {
	$let .= $L['L'];
}

// optional fields shown in the list (could be modified to allow breif and verbose, etc)
$showfields = array(
	// "test" => "concat(contact_first_name,' ',contact_last_name) as test",    why do we want the name repeated?
	"contact_company" => "contact_company",
	"contact_phone" => "contact_phone",
	"contact_email" => "contact_email"
);

// assemble the sql statement
$sql = "SELECT contact_id, contact_order_by, ";
foreach ($showfields as $val) {
	$sql.="$val,";
}
$sql.= "contact_first_name, contact_last_name, contact_phone
FROM contacts
WHERE contact_order_by LIKE '$where%'
ORDER BY $orderby
";

$carr[] = array();
$carrWidth = 4;
$carrHeight = 4;

$res = db_exec( $sql );
$rn = db_num_rows( $res );

$t = floor( $rn / $carrWidth );
$r = ($rn % $carrWidth);

if ($rn < ($carrWidth * $carrHeight)) {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		//if($y<$r)	$x = -1;
		while (($x<$carrHeight) && ($row = db_fetch_assoc( $res ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
} else {
	for ($y=0; $y < $carrWidth; $y++) {
		$x = 0;
		if($y<$r)	$x = -1;
		while(($x<$t) && ($row = db_fetch_assoc( $res ))){
			$carr[$y][] = $row;
			$x++;
		}
	}
}

$tdw = floor( 100 / $carrWidth );

?>

<table width="98%" border="0" cellpadding="0" cellspacing="1">
<tr>
	<td><img src="./images/icons/contacts.gif" alt="" border="0"></td>
	<td nowrap><h1>*</h1></td>
	<td align="right" width="100%">
	<?php if (!$denyEdit) { ?>
		<input type="button"  class=button value="<?php echo $AppUI->_('new contact');?>" onClick="javascript:window.location='./index.php?m=contacts&a=addedit'"></td>
	<?php } ?>
	<td nowrap="nowrap" width="20" align="right"><?php echo contextHelp( '<img src="./images/obj/help.gif" width="14" height="16" border="0" alt="'.$AppUI->_( 'Help' ).'">', 'ID_HELP_CONT_IDX' );?></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="2" cellspacing="1">
<tr>
	<td width="100%" align="right"><?php echo $AppUI->_('Show');?>:</td>
	<td align="center" bgcolor="silver"><a href="./index.php?m=contacts&where=0"><?php echo $AppUI->_('All');?></a></td>
<?php
	for ($a=65; $a < 91; $a++) {
		$cu = chr( $a );
		$cl = chr( $a+32 );
		$bg = strpos($let, "$cl") > 0 ? "bgcolor=silver><a href=./index.php?m=contacts&where=$cu" : '';
		echo "<td align=center $bg>$cu</a></td>\n";
	}
?>
</tr>
<tr>
	<td height="3"><img src="./images/shim.gif" width="1" height="1" border="0" alt=""></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="1" cellspacing="1" height="400" class="contacts">
<tr>
<?php 
	for ($z=0; $z < $carrWidth; $z++) {
?>
	<td valign="top" align="left" bgcolor="#f4efe3" width="<?php echo $tdw;?>%">
	<?php
		for ($x=0; $x < @count($carr[$z]); $x++) {
	?>
		<table width="100%" cellspacing="1" cellpadding="1">
		<tr>
			<td width="100%">
				<a href="./index.php?m=contacts&a=addedit&contact_id=<?php echo $carr[$z][$x]["contact_id"];?>"><strong><?php echo $carr[$z][$x]["contact_order_by"];?></strong></a>
			</td>
		</tr>
		<tr>
			<td class="hilite">
			<?php
				reset( $showfields );
				while (list( $key, $val ) = each( $showfields )) {
					if (strlen( $carr[$z][$x][$key] ) > 0) {
						if($val == "contact_email") {
						  echo "<A HREF='mailto:{$carr[$z][$x][$key]}' class='mailto'>{$carr[$z][$x][$key]}</a>\n";
						} else {
						  echo  $carr[$z][$x][$key]. "<br />";
						}
					}
				}
			?>
			</td>
		</tr>
		</table>
		<br />&nbsp;<br />
	<?php }?>
	</td>
<?php }?>
</tr>
</table>
