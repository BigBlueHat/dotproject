<?php
GLOBAL $project_id;
// Files mini-table in project view action

$sql="
SELECT file_owner,file_name,file_size,file_type,file_id,
	DATE_FORMAT(file_date, '%d-%b-%Y %H:%i') file_date,
	user_first_name,user_last_name
FROM files 
LEFT JOIN users ON users.user_id=files.file_owner 
WHERE file_project = '$project_id' 
ORDER BY file_date DESC
";
//echo "<pre>$sql</pre>";
$rc = mysql_query( $sql );
?>

<table width="100%" border=0 cellpadding="2" cellspacing="1" class="tbl">
<tr>
	<th nowrap>&nbsp;</td>
	<th nowrap width="100%">Filename</td>
	<th nowrap width="100%">File Owner</td>
	<th nowrap>File Date</td>
	<th nowrap>File Type</td>
	<th nowrap>File Size</td>
</tr>
<?php
while ($row = mysql_fetch_array( $rc )) { ?>
<tr>
	<td nowrap align=center>
<?php
	if ($row["file_owner"] == $user_cookie) { ?>
		<A href="./index.php?m=files&a=addedit&file_id=<?php echo $row["file_id"];?>"><img src="./images/icons/pencil.gif" alt="expand file" border="0" width=12 height=12></a>
<?php } ?>
	</td>
	<td nowrap><A href="./fileviewer.php?file_id=<?php echo $row["file_id"];?>"><?php echo $row["file_name"];?></a></td>
	<td nowrap><?php echo $row["user_first_name"]." ".$row["user_last_name"];?></td>
	<td nowrap><?php echo $row["file_date"];?></td>
	<td nowrap><?php echo $row["file_type"];?></td>
<?php
	$size=$row["file_size"];
	$size=($size>1024) ? sprintf("%0.2f kB",$size/1024) : $row["file_size"]." B"; 
?>
	<td nowrap align=right><?php echo $size;?></td>
	</td>
</tr>
<?php }?>
</table>

