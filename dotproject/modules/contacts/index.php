<?php /* $Id$ */
$AppUI->savePlace();

if (isset( $_GET['where'] )) {
	$AppUI->setState( 'ContIdxWhere', $_GET['where'] );
}
$where = $AppUI->getState( 'ContIdxWhere' ) ? $AppUI->getState( 'ContIdxWhere' ) : '%';

$orderby = 'contact_order_by';

// Pull First Letters
$let = ":";
$sql = "
SELECT DISTINCT LOWER(SUBSTRING($orderby,1,1)) as L
FROM contacts
WHERE contact_private=0
	OR (contact_private=1 AND contact_owner=$AppUI->user_id)
	OR contact_owner IS NULL
";
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
	AND (contact_private=0
		OR (contact_private=1 AND contact_owner=$AppUI->user_id)
		OR contact_owner IS NULL
	)
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

$a2z = $CR . '<table cellspacing="2" cellpadding="1" border="0"><tr>'
	. $CR . '<td>' . $AppUI->_('Show').':</td>'
	. $CR . '<td bgcolor="silver"><a href="./index.php?m=contacts&where=0">' . $AppUI->_('All').'</a></td>';
for ($a=65; $a < 91; $a++) {
	$cu = chr( $a );
	$cl = chr( $a+32 );
	$bg = strpos($let, "$cl") > 0 ? "bgcolor=\"silver\"><a href=\"./index.php?m=contacts&where=$cu\"" : '';
	$a2z .= "\n\t<td width=\"10\" align=\"center\" $bg>$cu</a></td>";
}
$a2z .= "\n</tr>\n</table>";

// setup the title block
$titleBlock = new CTitleBlock( 'Contacts', 'monkeychat-48.png', $m, "$m.$a" );
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new contact').'">', '',
		'<form action="?m=contacts&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->addCrumbRight( $a2z );
$titleBlock->show();
?>

<table width="100%" border="0" cellpadding="1" cellspacing="1" height="400" class="contacts">
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
