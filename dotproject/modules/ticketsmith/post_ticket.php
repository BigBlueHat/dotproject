<?php /* TICKETSMITH $Id$ */
##
##	Ticketsmith Post Ticket
##

// setup the title block
$titleBlock = new CTitleBlock( 'Submit Trouble Ticket', 'ticketsmith.gif', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=ticketsmith", "tickets list" );
$titleBlock->show();

?>

<SCRIPT language="javascript">
function submitIt() {
	var f = document.ticket;
	var msg = '';
	if (f.name.value.length < 3) {
		msg += "\n- a valid name";
	}
	if (f.email.value.length < 3) {
		msg += "\n- a valid email";
	}
	if (f.subject.value.length < 3) {
		msg += "\n- a valid subject";
	}
	if (f.description.value.length < 3) {
		msg += "\n- a valid desciption";
	}
	
	if (msg.length < 1) {
		f.submit();
	} else {
		alert( "Please provide the following detail before submitting:" + msg );
	}
}
</script>

<TABLE width="100%" border=0 cellpadding="0" cellspacing=1 class="std">
<form name="ticket" action="?m=ticketsmith" method="post">
<input type="hidden" name="dosql" value="do_ticket_aed">

<TR height="20">
	<Th colspan=2>
		&nbsp;<font face="verdana,helveitica,arial,sans-serif" color=#ffffff><strong>Trouble Details</strong></font>
	</th>
</tr>
<tr>
	<TD align="right">Name:</td>
	<TD><input type="text" class="text" name="name" value="<?php echo @$crow["name"];?>" size=50 maxlength="255"> <span class="smallNorm">(required)</span></td>
</tr>
<tr>
	<TD align="right">E-Mail:</td>
	<TD><input type="text" class="text" name="email" value="" size=50 maxlength="50"> <span class="smallNorm">(required)</span></td>
</tr>
<tr>
	<TD align="right">Subject:</td>
	<TD><input type="text" class="text" name="subject" value="" size=50 maxlength="50"> <span class="smallNorm">(required)</span></td>
</tr>
<tr>
	<TD align="right">Priority:</td>
	<TD>
		<select name="priority" class="text">
			<option value="0">Low
			<option value="1" selected>Normal
			<option value="2">High
			<option value="3">Highest
			<option value="4"><strong>911 (Showstopper)</strong>
		</select>
	</td>
</tr>
<TR>
	<TD align="right">Description of Problem: </td>
	<td><span class="smallNorm">(required)</span></td>
</tr>
<TR>
	<TD colspan=2 align="center">
		<textarea cols="70" rows="10" class="textarea" name="description"><?php echo @$crow["description"];?></textarea>
	</td>
</tr>
<TR>
	<TD><input type="button" value="back" class="button" onClick="javascript:history.back(-1);"></td>
	<TD align="right"><input type="button" value="submit" class="button" onClick="submitIt()"></td>
</tr>
</form>
</TABLE>
&nbsp;<br />&nbsp;<br />&nbsp;
