#!c:\programme\perl\bin\perl.exe -w
# You may have to edit the above line to reflect your system

# $Id$ #

%config = (database_host => 'localhost',
           database_name => 'dotproject',
           database_user => 'dotproject',
           database_pass => 'yourpassword');

# centralize some of the hard coding JBF
$app_root = "http://host.yourdomain.com/path.to/dotproject";

# send email report upon receipt (1 = yes, 0 = no)
$send_email_report = 1;

# address to send report to
$report_to_address = "you\@yourdomain.com";

# report from address
$report_from_address = "support\@yourdomain.com";

# location of sendmail
$mailprog = "/usr/sbin/sendmail";

######################## </CONFIGURATION SECTION> ##############################

# database bindings
use DBI;

# read in message
while (<STDIN>) {
	push @message, $_;
}

# main program
&get_headers();
&check_attachments();
&get_body();
&insert_message();
&mail_report() if ($send_email_report);

exit();

################################################################################

sub get_headers {

    # read in headers
    foreach (@message) {
        push @headers, $_;
        last if (/^\s$/ || /^$/);
        if (/oundary=/) {
	        $attachment_info = $_;
            $attachment = 1;
	    }
        else {
            $attachment = 0;
        }
	    $_ =~ s/:\s/:/g;
        if (/:/) {
            @vars = split(':', $_, 2);
	        if (@vars) {
                chop($header{$vars[0]} = $vars[1]);
	        }
        }
    }

    # strip out Re:'s in subject
    $header{'Subject'} =~ s/\s*Re:\s*//gi;

    # put a nice Re: back in
    $header{'Subject'} =~ s/(\[\#\d+\])(.*)/$1 Re: $2/;

    # initialize Cc: header
    $header{'Cc'} = "" if (!$header{'Cc'});

    # fix quoting in email headers
    $header{'From'} =~ s/"/\"/g;
    $header{'Cc'} =~ s/"/\"/g;

    # determine ticket number
    $parent = $header{'Subject'};
    if ($parent =~ /\[\#(\d+)\]/) {
        $parent =~ s/.*\[\#(\d+)\].*/$1/;
    }
    else {
        $parent = 0;
    }

}

################################################################################

sub check_attachments {

    # check for attachment
	return if (!$attachment_info);

    # determine attachment delimiter
	($i, $boundary) = split("\"", $attachment_info);
	return if (!$boundary);

    # pull out attachments
	for ($i = $#headers + 1; $i <= $#message; $i++) {
        if ($message[$i] =~ /$boundary/) {
            push @boundary_lines, $i;
		}
	}

}

################################################################################

sub get_body {

    # read in message body
	if (!$attachment_info) {
		for ($i = $#headers + 1; $i <= $#message; $i++) {
            $body .= $message[$i];
		}
	}
    else {
		for ($i = $boundary_lines[0] + 1; $i < $boundary_lines[1]; $i++) {
            if ($past_info) {
                $body .= $message[$i];
            }
            elsif ($message[$i] =~ /^\s+$/) {
                $past_info = 1;
            }
		}
	}
    $body =~ s/^\n//;
    $body =~ s/\r\n$/\n/;

}

################################################################################

sub insert_message {

    # connect to database
    $dbh = DBI->connect("DBI:mysql:$config{'database_name'}:$config{'database_host'}", $config{'database_user'}, $config{'database_pass'});

    # update parent activity
    if ($parent) {
        $activity_query = "UPDATE tickets SET type = 'Open', activity = UNIX_TIMESTAMP() WHERE ticket = '$parent'";
        $sth = $dbh->prepare($activity_query);
        $sth->execute();
        $sth->finish();
        $type = "Client Followup";
        $assignment = "9999";
    }
    else {
        $type = "Open";
        $assignment = "0";
    }

    # quote all fields
    $parent = $dbh->quote($parent);
    $attachment = $dbh->quote($attachment);
    $author = $dbh->quote($header{'From'});
    $subject = $dbh->quote($header{'Subject'});
    $body = $dbh->quote($body);
    $type = $dbh->quote($type);
    $cc = $dbh->quote($header{'Cc'});
    $assignment = $dbh->quote($assignment);

    # do insertion
    $insert_query = "INSERT INTO tickets (parent, attachment, timestamp, author, subject, body, type, cc, assignment) ";
    $insert_query .= "VALUES ($parent, $attachment, UNIX_TIMESTAMP(), $author, $subject, $body, $type, $cc, $assignment)";
    $sth = $dbh->prepare($insert_query);
    $sth->execute();
    $ticket = $sth->{'mysql_insertid'};
    $sth->finish();
    $dbh->disconnect();

}

################################################################################

sub mail_report {

    # unquote necessary fields
	$author =~ s/^\'(.*)\'$/$1/;
	$author =~ s/\\\'/'/g;
    $subject =~ s/^\'(.*)\'$/$1/;
	$subject =~ s/\\\'/'/g;

    # remove ticket number
    $subject =~ s/\[\#\d+\](.*)/$1/;

    # mail the report
    open(MAIL, "|$mailprog -t");
	print MAIL "To: $report_to_address\n";
	print MAIL "From: $report_from_address\n";
	print MAIL "Subject: New support ticket #$ticket\n";
	print MAIL "Content-type: text/html\n";
	print MAIL "Mime-type: 1.0\n\n";
	print MAIL "<html>";
	print MAIL "<head>";
	print MAIL "<style>";
	print MAIL ".title {";
	print MAIL "	FONT-SIZE: 18pt; SIZE: 18pt;";
	print MAIL "}";
	print MAIL "</style>";
	print MAIL "<title>New Trouble ticket</title>";
	print MAIL "</head>";
	print MAIL "<body>";
	print MAIL "";
	print MAIL "<TABLE border=0 cellpadding=4 cellspacing=1>";
	print MAIL "	<TR>";
	print MAIL "	<TD valign=top><img src=" . $app_root . "/images/icons/ticketsmith.gif alt= border=0 width=42 height=42></td>";
 
	print MAIL "		<TD nowrap><span class=title>Trouble Ticket Management</span></td>";
	print MAIL "		<TD valign=top align=right width=100%>&nbsp;</td>";
	print MAIL "	</tr>";
	print MAIL "</TABLE>";
	print MAIL "<TABLE width=600 border=0 cellpadding=4 cellspacing=1 bgcolor=#878676>";
	print MAIL "	<TR>";
	print MAIL "		<TD colspan=2><font face=arial,san-serif size=2 color=white>New Ticket Entered</font></TD>";
	print MAIL "	</tr>";
	print MAIL "	<TR>";
	print MAIL "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>Ticket ID:</font></TD>";
	print MAIL "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>$ticket</font></TD>";
	print MAIL "	</tr>";
	print MAIL "	<TR>";
	print MAIL "		<TD bgcolor=white><font face=arial,san-serif size=2>Author:</font></TD>";
	print MAIL "		<TD bgcolor=white><font face=arial,san-serif size=2>$author</font></TD>";
	print MAIL "	</tr>";
	print MAIL "	<TR>";
	print MAIL "		<TD bgcolor=white><font face=arial,san-serif size=2>Subject:</font></TD>";
	print MAIL "		<TD bgcolor=white><font face=arial,san-serif size=2>$subject</font></TD>";
	print MAIL "	</tr>";
	print MAIL "	<TR>";
	print MAIL "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>View:</font></TD>";
	print MAIL "		<TD bgcolor=white nowrap><a href=" . $app_root . "/index.php?m=ticketsmith&a=view&ticket=$ticket><font face=arial,san-serif size=2>$app_root/index.php?m=ticketsmith&a=view&ticket=$ticket</font></a></TD>";
	print MAIL "	</tr>";
	print MAIL "</TABLE>";
	print MAIL "</body>";
	print MAIL "</html>";
	close(MAIL);

}

################################################################################

sub mail_acknowledgement {

    # unquote necessary fields
	$author =~ s/^\'(.*)\'$/$1/;
	$author =~ s/\\\'/'/g;
    $subject =~ s/^\'(.*)\'$/$1/;
	$subject =~ s/\\\'/'/g;

    # remove ticket number
    $subject =~ s/\[\#\d+\](.*)/$1/;

    # mail the report
    open(MAIL, "|$mailprog -t");
	print MAIL "To: $author\n";
	print MAIL "From: $report_from_address\n";
	print MAIL "Subject: Dotmarketing Support Request Received, ticket: #$ticket\n\n";
	print MAIL "This is an acknowledgement that your support request has been received";
	print MAIL "by dotmarketing, Inc.  ";
	close(MAIL);

}


