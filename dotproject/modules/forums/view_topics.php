<?php
/**********************************************/
/*		If we are viewing forum headers					*/
/**********************************************/
	//Pull All Messages
	$sql = "select 
	count(fm2.message_id) as replies,
	max(fm2.message_date) as latest_reply,
	fm1.message_id, 
	fm1.message_parent, 
	fm1.message_author, 
	fm1.message_title, 
	fm1.message_date, 
	fm1.message_published, 
	user_username,
	user_first_name
	from forum_messages fm1
	left join users on fm1.message_author = users.user_id 
	left join forum_messages fm2 on fm1.message_id = fm2.message_parent
	where fm1.message_forum = $forum_id
	group by 
	fm1.message_id, 
	fm1.message_parent, 
	fm1.message_author, 
	fm1.message_title, 
	fm1.message_date, 
	fm1.message_body, 
	fm1.message_published
	";
	$prc= mysql_query($sql);
	
	while($x = mysql_fetch_array($prc)){
		$parr[] = $x;
	}
	?>
	<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" >
		<TR>
		<TD><A href="./index.php?m=forums">All forums</a></td>
			<TD align="right"><input type="button" class=button style="width:120;" value="start a new topic" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id;?>&post_message=1';"></td>
			</TR>
		</TABLE>
		<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" >
		<TR bgcolor="silver">
			<TD> </td>
			<TD>Topics</td>
			<TD>Author</td>
			<TD>Replies</td>
			<TD>Last Post</td>
		</tr>
		<?php for($x=0;$x<count($parr);$x++){
//JBF limit displayed messages to first-in-thread
			if ($parr[$x]["message_parent"]<0) {?>
			<TR bgcolor="#eeeeee">
				<TD width="25" bgcolor=white><img src="./images/icons/forum_folder.gif" alt="" border="0" width="20" height="20"></td>
				<TD><span style="font-size:10pt;"><A href="./index.php?m=forums&a=viewer&forum_id=<?php echo $forum_id . "&message_id=" . $parr[$x]["message_id"];?>"><?php echo $parr[$x]["message_title"];?></a></span></td>
				<TD bgcolor=#dddddd><?php echo $parr[$x]["user_username"];?></td>
				<TD><?php echo  $parr[$x]["replies"];?></td>
				<TD bgcolor=#dddddd><?php  if(empty($parr[$x]["latest_reply"])){echo $parr[$x]["message_date"];}else{echo $parr[$x]["latest_reply"];}?></td>
			</tr>
		<?php
//JBF
			}
		}?>
		</table>
