<TABLE width="95%" border=0 cellpadding="0" cellspacing=1>
	<TR>
	<TD valign="top"><img src="./images/icons/ticketsmith.gif" alt="" border="0" width="42" height="42" /></td>
		<TD nowrap><h1>Trouble Ticket Management</h1></td>
		<TD valign="top" align="right" width="100%">&nbsp;</td>
	</tr>
</TABLE>
<?php

/* $Id$ */

require("modules/ticketsmith/config.inc.php");
require("modules/ticketsmith/common.inc.php");

/* set title */
$title = "Post Followup";

/* setup fields */
$fields = array("headings" => array("Subject", "Cc", "<br />"),
                "columns"  => array("subject", "cc", "body"),
                "types"    => array("subject", "cc", "followup"));

/* prepare ticket parent */
if (!$ticket_parent) {
    $ticket_parent = $ticket;
}
    
if (@$followup) {
    
    /* prepare fields */
    $timestamp = time();
    list($from_name, $from_email) = query2array("SELECT concat(user_first_name, ' ', user_last_name) as name, user_email as email FROM users WHERE user_id = '$AppUI->user_id'");
    $author = "$from_name <$from_email>";
    if (!$recipient) {
        $recipient = query2result("SELECT author FROM tickets WHERE ticket = '$ticket_parent'");
    }
    
    /* prepare posted stuff */
    $recipient = stripslashes($recipient);
    $subject = stripslashes($subject);
    $followup = stripslashes($followup);
    $cc = stripslashes($cc);
    
    /* fix subject */
    chop($subject);
    $subject = "[#$ticket_parent] " . $subject;
    
    /* prepare extra headers */
    $headers = "From: $author\n";
    $headers .= "Reply-To: " . $CONFIG["reply_to"];
    $headers .= $cc ? "\nCc: $cc" : "";
    $headers .= "\nX-Mailer: $xmailer";
    
    /* mail the followup */
    @mail($recipient, $subject, $followup, $headers) || fatal_error("Unable to mail followup.  Quit without recording followup to database.");
    
    /* escape special characters */
    $author = addslashes($author);
    $recipient = addslashes($recipient);
    $subject = addslashes($subject);
    $followup = addslashes($followup);
    $cc = addslashes($cc);
    
    /* do database insert */
    $query = "INSERT INTO tickets (author, subject, recipient, body, cc, timestamp, type, assignment, parent) ";
    $query .= "VALUES ('$author','$subject','$recipient','$followup','$cc','$timestamp','Staff Followup','9999','$ticket_parent')";
    do_query($query);
    
    /* update parent activity */
    do_query("UPDATE tickets SET activity = '$timestamp' WHERE ticket = '$ticket_parent'");
    
    /* redirect to parent */
    echo("<META HTTP-EQUIV=\"Refresh\" CONTENT=\"0;URL=index.php?m=ticketsmith&a=view&ticket=$ticket_parent\">");

    exit();

}

else {

    /* start page */
    common_header($title);

    /* start table */
	print("<table class=maintable bgcolor=#eeeeee width=95%>\n");
    print("<tr>\n");
    print("<td colspan=2 align=center bgcolor=#878676>\n");
    print("<div class=heading>$title</div>\n");
    print("</td>\n");
    print("</tr>\n");

    /* start form */
    print("<form action=index.php?m=ticketsmith&a=followup method=post>\n");
    print("<input type=hidden name=ticket value=$ticket>\n");

    /* get ticket */
    $ticket_info = query2hash("SELECT * FROM tickets WHERE ticket = $ticket");

    /* output From: line */
    print("<tr>\n");
    print("<td align=left><strong>From</strong></td>");
    list($from_name, $from_email) = query2array("SELECT concat(user_first_name, ' ', user_last_name) as name, user_email as email FROM users WHERE user_id = '$AppUI->user_id'");
    print("<td align=left>" . $from_name . " &lt;" . $from_email . "&gt;</td>\n");
    print("</tr>\n");

    /* output To: line */
    print("<tr>\n");
    print("<td align=left><strong>To</strong></td>");
    $recipient = query2result("SELECT author FROM tickets WHERE ticket = '$ticket_parent'");
    print("<td align=left>" . format_field($recipient, "recipient") . "</td>\n");
    print("</tr>\n");
    
    /* output ticket */
    for ($loop = 0; $loop < count($fields["headings"]); $loop++) {
        print("<tr>\n");
        print("<td align=left><strong>" . $fields["headings"][$loop] . "</strong></td>");
        print("<td align=left>" . format_field($ticket_info[$fields["columns"][$loop]], $fields["types"][$loop]) . "</td>\n");
        print("</tr>\n");
    }
    
    /* output submit button */
    print("<tr><td><br /></td><td><font size=-1><input class=button type=submit value=Post Followup></font></td></tr>\n");

    /* output actions */
    print("<tr>\n");
    print("<td align=left valign=top><br /></td>");
    print("<td align=left valign=top>\n");
    print("<a href=index.php?m=ticketsmith&a=view&ticket=$ticket");
    print(">Return to ticket</a> | <a href=index.php?m=ticketsmith>Return to ticket list</a></td></td>\n");
    print("</tr>\n");

    /* end table */
    print("</table>\n");

    /* end form */
    print("</form>\n");

    /* end page */
    common_footer();
    
}

?>
