<?		$sql = "select 
	message_id, 
	message_parent, 
	message_author, 
	message_title, 
	message_date, 
	message_body, 
	user_first_name,
	user_last_name,
	user_email,
	message_published, 
	user_username 
	from forum_messages 
	left join users on message_author = users.user_id 
	where 
	message_id = $message_id or
	message_parent = $message_id

	";
	$prc= mysql_query($sql);
	while($x = mysql_fetch_array($prc)){
		$parr[] = $x;
	}
	?>
	<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" >
		<TR>
		<TD><A href="./index.php?m=forums">All forums</a>::<A href="./index.php?m=forums&a=viewer&forum_id=<?echo $forum_id;?>"><?echo $forum_name;?></a></td>
			<TD align="right">
			<input type="button" class=button value="Post Reply" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?echo $forum_id;?>&message_parent=<?echo $message_id;?>&post_message=1';">
			<input type="button" class=button value="New Topic" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?echo $forum_id;?>&message_id=0&post_message=1';"></td>
			</TR>
		</TABLE>
		<TABLE border=0 cellpadding=4 cellspacing=1 width="95%" bgcolor="#ffffff">
			<TR>
				<TD width="175" class="mboxhdr" nowrap>Author:</TD>
				<TD width="100%" class="mboxhdr">Message:</td> 
			</tr>
		
	<?for($x=0;$x<count($parr);$x++){?>
		<?if(($x % 2) ==0){$color="#eeeeee";}else{$color="#dddddd";}?>
			<TR bgcolor="<?echo $color;?>">

					<TD valign="top">
	
					<font size="2"><?echo $parr[$x]["user_first_name"];?> <?echo $parr[$x]["user_last_name"];?></font>					(<?echo $parr[$x]["user_username"];?>) <br>
					<?echo $parr[$x]["user_email"];?>
				</td>
				<TD bgcolor="<?echo $color;?>" valign="top"><font size="2">
				<?if($x==0){?>
					<img src="./images/icons/post.gif" width="15" height="15" alt="" border="0">
					<B><?echo $parr[$x]["message_title"];?></b><hr size=1>
				<?}?>
					<?echo str_replace(chr(13), "&nbsp;<BR>", $parr[$x]["message_body"]);?></font>&nbsp;<br>&nbsp;<br>
				</td>
			</tr>
			<TR>
					<TD bgcolor="<?echo $color;?>" nowrap valign="top">	<img src="./images/icons/posticon.gif" alt="date posted" border="0" width="14" height="11"><?echo $parr[$x]["message_date"];?></td>
					<TD bgcolor="<?echo $color;?>" nowrap valign="top"></td>
			</tr>
	<?}?>
	</table>
	<TABLE border=0 cellpadding=2 cellspacing=1 width="95%" >
		<TR>
		<TD><A href="./index.php?m=forums">All forums</a>::<A href="./index.php?m=forums&a=viewer&forum_id=<?echo $forum_id;?>"><?echo $forum_name;?></a></td>
			<TD align="right">
			<input type="button" class=button value="Post Reply" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?echo $forum_id;?>&message_parent=<?echo $message_id;?>&post_message=1';">
			<input type="button" class=button value="New Topic" onClick="javascript:window.location='./index.php?m=forums&a=viewer&forum_id=<?echo $forum_id;?>&message_id=0';"></td>
			</TR>
		</TABLE>
