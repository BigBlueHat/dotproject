<?
//contact index
if(empty($orderby))$orderby = "last_name";
$carr[] = array();
$orderby = "contact_order_by";
if(empty($where))$where = "%";
$perm =mysql_query($psql);
$pullperm = mysql_fetch_array($perm);


//Pull First Letters
$let = ":";
$sql = "select contact_order_by from contacts";
$rc= mysql_query($sql);
while($row = mysql_fetch_array($rc)){
			$let.= strtolower(substr($row["contact_order_by"], 0, 1) . ":");
}




$showfields = array("concat(contact_first_name, ' ' ,  contact_last_name) as test", "contact_company",  "contact_phone","contact_email");


$sql = "select contact_id, contact_order_by,";
while(list($key,$val) = each($showfields)){
	$sql.="$val,";
}
$sql.= "contact_first_name, contact_last_name,
contact_phone 
from contacts 
where contact_order_by like '$where%'
order by $orderby	";
$carrWidth=4;
$carrHeight=4;







$rc =mysql_query($sql);
$rn = mysql_num_rows($rc);

$t = floor($rn / $carrWidth);
$r = ($rn % $carrWidth);

if($rn < ($carrWidth * $carrHeight)){

	for($y =0; $y<$carrWidth;$y++){
		$x = 0;
	
		//if($y<$r)	$x = -1;
		while(($x<$carrHeight)&&($row = mysql_fetch_array($rc))){
			$carr[$y][] = $row;
			$x++;
		}
	
	}
}
else{
	for($y =0; $y<$carrWidth;$y++){
		$x = 0;
	
		if($y<$r)	$x = -1;
		while(($x<$t)&&($row = mysql_fetch_array($rc))){
			$carr[$y][] = $row;
			$x++;
		}
	
	}
}

$tdw = floor(100 / $carrWidth);

$usql = "Select user_first_name, user_last_name 
from users 
where users.user_id = $user_cookie ";
$urc = mysql_query($usql);
$urow = mysql_fetch_array($urc);
echo mysql_error();
?>
<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
		<TD><img src="./images/icons/contacts.gif" alt="" border="0"></td>
		<TD nowrap><span class="title">Contacts</span></td>
		<TD align="right" width="100%"><input type="button"  class=button value="Add New contact" onClick="javascript:window.location='./index.php?m=contacts&a=addedit'"></td>
	</tr>
	<TR>


	</tr>
</TABLE>
<TABLE width="95%" border=0 cellpadding="2" cellspacing=1>
	<TR>
	<TD valign="bottom" nowrap><span id=""><b>Welcome <?echo $urow[0];?>.</b>  This page show you a list of current contacts.</span></td>
	<TD WIDTH="100%" ALIGN=RIGHT>SHOW:</td>
	<TD align="center" bgcolor="silver"><a href="./index.php?m=contacts">All</A></TD>
	<TD align="center" <?if(strpos($let, "a:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=A";?>>A</A></TD>
	<TD align="center" <?if(strpos($let, "b:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=B";?>>B</A></TD>
	<TD align="center" <?if(strpos($let, "c:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=C";?>>C</A></TD>
	<TD align="center" <?if(strpos($let, "d:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=D";?>>D</A></TD>
	<TD align="center" <?if(strpos($let, "e:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=E";?>>E</A></TD>
	<TD align="center" <?if(strpos($let, "f:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=F";?>>F</A></TD>
	<TD align="center" <?if(strpos($let, "g:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=G";?>>G</A></TD>
	<TD align="center" <?if(strpos($let, "h:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=H";?>>H</A></TD>
	<TD align="center" <?if(strpos($let, "i:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=I";?>>I</A></TD>
	<TD align="center" <?if(strpos($let, "j:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=J";?>>J</A></TD>
	<TD align="center" <?if(strpos($let, "k:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=K";?>>K</A></TD>
	<TD align="center" <?if(strpos($let, "l:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=L";?>>L</A></TD>
	<TD align="center" <?if(strpos($let, "m:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=M";?>>M</A></TD>
	<TD align="center" <?if(strpos($let, "n:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=N";?>>N</A></TD>
	<TD align="center" <?if(strpos($let, "o:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=O";?>>O</A></TD>
	<TD align="center" <?if(strpos($let, "p:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=P";?>>P</A></TD>
	<TD align="center" <?if(strpos($let, "q:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=Q";?>>Q</A></TD>
	<TD align="center" <?if(strpos($let, "r:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=R";?>>R</A></TD>
	<TD align="center" <?if(strpos($let, "s:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=S";?>>S</A></TD>
	<TD align="center" <?if(strpos($let, "t:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=T";?>>T</A></TD>
	<TD align="center" <?if(strpos($let, "u:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=U";?>>U</A></TD>
	<TD align="center" <?if(strpos($let, "v:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=V";?>>V</A></TD>
	<TD align="center" <?if(strpos($let, "w:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=W";?>>W</A></TD>
	<TD align="center" <?if(strpos($let, "x:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=X";?>>X</A></TD>
	<TD align="center" <?if(strpos($let, "y:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=Y";?>>Y</A></TD>
	<TD align="center" <?if(strpos($let, "z:")>0)echo " bgcolor=silver ><a href=./index.php?m=contacts&where=Z";?>>Z</A></TD>
	</tr>
</TABLE>
<TABLE width="95%" border=0 bgcolor="silver" cellpadding="1" cellspacing=2 height="400">
	<TR>
		<?for($z=0;$z<$carrWidth;$z++){?>
		<TD valign="top" align="left" bgcolor="#f4efe3" width="<?echo $tdw;?>%">
			<?for($x=0;$x<@count($carr[$z]);$x++){?>
			<table width="95%" cellspacing=1 cellpadding=1>
				<TR bgcolor="silver"><TD><B><a href="./index.php?m=contacts&a=addedit&contact_id=<?echo $carr[$z][$x]["contact_id"];?>"><?echo $carr[$z][$x]["contact_order_by"];?></a></b></td></tr>
				<TR><TD>
				<?	reset($showfields);
				while(list($key,$val) = each($showfields)){				
					if(strlen($carr[$z][$x][($key+2)]) > 0){
						echo$carr[$z][$x][($key+2)] . "<BR>";
						}
				}?>
				</td></tr>
			</table><br>&nbsp;<br>
			<?}?>
		</TD>
		<?}?>
	</TR>
</Table>
