#!c:\programme\perl\bin\perl.exe -w
# You may have to edit the above line to reflect your system
# E.g. the typical UNIX/Linux system will require #!/usr/bin/perl

# $Id$ #

# send email report upon receipt (1 = yes, 0 = no)
$send_email_report = 1;

# Send aknowlegment back to lodger (1 = yes, 0 = no)
$send_acknowledge = 1;

# Save attachments as files in project 0 (1 = yes, 0 = no, just mark them as removed)
$save_attachments = 0;

# Skip non-MIME component of MIME emails (usually a warning about non-MIME compliant readers)
$skip_mime_preface = 1;

# NOTE:  Email addresses should escape the @ symbol as it is
# a PERL array identifier and will cause this script to break.
# Alternatively change the double quotes to single quotes, which
# also escapes the string.

# NOTE 2: If your dotProject PHP environment is correctly set up
# you don't need to add the @ and domain, it will get it from
# dPconfig[site_domain] key.

# address to send report to
$report_to_address = "admin";

# report from address
$report_from_address = "support";

# location of sendmail
$mailprog = "/usr/sbin/sendmail";

######################## </CONFIGURATION SECTION> ##############################

## First phase, check to see we can configure ourselves based upon
## the PHP environment.
die ("Gateway.pl requires the full path to the dotproject config.php file as its only argument") if ($#ARGV != 0);
%config = ();
&check_config($ARGV[0]);

# Shortcuts for the email code
$app_root = $config{'base_url'};
$dp_root = $config{'root_dir'};

# If no domain portion, add the domain from the configuration file.
if ( $report_to_address !~ /\@/ ) {
  $report_to_address .= '@' . $config{'site_domain'};
}
if ( $report_from_address !~ /\@/ ) {
  $report_from_address .= '@' . $config{'site_domain'};
}

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
&insert_attachments() if ($save_attachments);
&mail_report() if ($send_email_report);
&mail_acknowledgement() if ($send_acknowledge);

exit();

################################################################################

sub check_config() {
  $dp_conf = $_[0];
  open (PHPCONFIG, "<$dp_conf")
    or die ("Cannot find dotProject configuration file!");
  while (<PHPCONFIG>) {
    if (/^\s*\$dpconfig\[/i) {
      s/\s*;.*$//;
      # Now split the conf line up.
      @confs = split /\s*=\s*/;
      # First part is the name
      $confs[0] =~ s/^.*\[['"](.*)['"]\]/$1/;
      $confs[1] =~ s/['"\r\n]//g;
      # add to the config array
      $config{$confs[0]} = $confs[1];
    }
  }
}

sub get_headers {

    # read in headers
	# First pass, fix up split headers.
    $first_message_line = 0;
    foreach (@message) {
        last if (/^\s$/ || /^$/);
		if (/^[\s\t]+/) {
			$last_hdr = pop @headers;
			$last_hdr =~ s/[\s\t]*$//;
			s/[\s\t]*//;
			$last_hdr .= $_;
			push @headers, $last_hdr;
		} else {
			push @headers, $_;
		}
		$first_message_line++;
	}
	# Second pass, split out the required headers
	$attachment = 0;
	foreach (@headers) {
        if (/oundary=/) {
	        $attachment_info = $_;
            if ($save_attachments) {
			    $attachment = 2;
			} else {
				$attachment = 1;
			}
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

    # Allow the use of Reply-To to insert tickets on behalf of another
    if ($header{'Reply-To'}) {
	$header{'From'} = $header{'Reply-To'};
    }

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
	($i, $boundary) = split(/"/, $attachment_info);
	return if (!$boundary);

	if ($attachment_info =~ /multipart\/alternative/i) {
		$mime_alternative = 1;
	} else {
		$mime_alternative = 0;
	}
    # pull out attachments
	$in_attach_hdrs = 0;
	$attach_count = 0;
	for ($i = $first_message_line; $i <= $#message; $i++) {
        if ($message[$i] =~ /$boundary/) {
	    $in_attach_hdrs = 1;
            push @boundary_lines, $i;
	    push @attach_disposition, "";
	    push @attach_type, "text/plain";
	    push @attach_encoding, "7bit";
	    push @attach_realname, "";
	    $attach_count += 1;
	} else {
	    if ($in_attach_hdrs) {
		if ($message[$i] =~ /^\s*$/) {
		    $last = pop @boundary_lines;
		    push @boundary_lines, $i;
		    push @boundary_end, $last;
		    $in_attach_hdrs = 0;
		} else {
		    @attach_hdr = split(/[:;]/, $message[$i]);
		    if ($attach_hdr[0] =~ m/content-disposition/i) {
			    $last = pop @attach_disposition;
			    push @attach_disposition, $attach_hdr[1];
		    }
		    if ($attach_hdr[0] =~ m/content-type/i) {
			    pop @attach_type;
			    push @attach_type, $attach_hdr[1];
		    }
		    if ($attach_hdr[0] =~ m/content-transfer-encoding/i) {
			    pop @attach_encoding;
			    push @attach_encoding, $attach_hdr[1];
		    }
		    if ($message[$i] =~ m/name=/i) {
			    ($x, $f) = split(/"/, $message[$i]);
			    $x = "";
			    pop @attach_realname;
			    push @attach_realname, $f;
		    }
		}
	    }
	}
    }
    push @boundary_end, $#message;
}

################################################################################

sub get_body {

    # read in message body
	if (!$attachment_info) {
		for ($i = $first_message_line + 1; $i <= $#message; $i++) {
            $body .= $message[$i];
		}
	}
    else {
		# Look for the attachment that doesn't have a disposition
		if ($skip_mime_preface) {
			$i = 1;
		} else {
		    $i = 0;
		}
		for (; $i < $#attach_disposition; $i++) {
			if ( ($mime_alternative == 1 && $attach_type[$i] =~ /text\/plain/i) || ($mime_alternative == 0 && $attach_disposition[$i] =~ /^$/ )) {
				for ($j = $boundary_lines[$i] + 1; $j < $boundary_end[$i+1]; $j++) {
					$body .= $message[$j];
				}
			}
		}
	}
    $body =~ s/^\n//;
    $body =~ s/\r\n$/\n/;

}

################################################################################

sub insert_message {

    # connect to database
    $dbh = DBI->connect("DBI:mysql:$config{'dbname'}:$config{'dbhost'}", $config{'dbuser'}, $config{'dbpass'});

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
    $db_parent = $dbh->quote($parent);
    $attachment = $dbh->quote($attachment);
    $author = $dbh->quote($header{'From'});
    $subject = $dbh->quote($header{'Subject'});
    $body = $dbh->quote($body);
    $type = $dbh->quote($type);
    $cc = $dbh->quote($header{'Cc'});
    $assignment = $dbh->quote($assignment);

    # do insertion
    $insert_query = "INSERT INTO tickets (parent, attachment, timestamp, author, subject, body, type, cc, assignment) ";
    $insert_query .= "VALUES ($db_parent, $attachment, UNIX_TIMESTAMP(), $author, $subject, $body, $type, $cc, $assignment)";
    $sth = $dbh->prepare($insert_query);
    $sth->execute();
    $ticket = $sth->{'mysql_insertid'};
    $sth->finish();
    $dbh->disconnect();

}

sub insert_attachments {
	return if (!$attachment_info);

    $dbh = DBI->connect("DBI:mysql:$config{'dbname'}:$config{'dbhost'}", $config{'dbuser'}, $config{'dbpass'});
	if ($skip_mime_preface) {
		$i = 1;
	} else {
		$i = 0;
	}
	for ($i = 0; $i < $#attach_disposition; $i++) {
		if ( ( $mime_alternative == 0 && $attach_disposition[$i] !~ /^$/) || ($mime_alternative == 1 && $attach_type[$i] !~ /text\/plain/) ) {
			insert_attachment($i, $dbh);
		}
	}
	$dbh->disconnect();
}

sub insert_attachment($) {

	$att = $_[0];
	$dbh = $_[1];

	# Check that we can write to the required directory and that we know who the
	# web owner is.
	$files_dir = $dp_root . "/files";
	$file_repository = $files_dir . "/0";

	@st = stat $files_dir
		or	die ("Cannot find file repository");
	$web_owner = $st[4];

	# If the repository doesn't exist, create it.
	stat $file_repository
		or mkdir $file_repository, 0777;

	# Extract the file using mimencode if necessary.
	$fid = sprintf("%x_%d", time(), $att);
	# If content encoding is not 7bit, try and determine what it is
	$fname = $file_repository . "/" . $fid;
	$freal = ">";
	$freal = "| mimencode -u -o " if ($attach_encoding[$att] =~ m/base64/i);
	$freal = "| mimencode -u -q -o " if ($attach_encoding[$att] =~ m/quoted/i);
	$fout = $freal . $fname;
	open(FH, $fout);
	for ($j = $boundary_lines[$att] + 1; $j < $boundary_end[$att+1]; $j++) {
		print FH $message[$j];
	}
	close(FH);

	# Determine the files size
	open(FH, $fname);
	seek FH, 0, 2;
	$filesize = tell FH;
	close(FH);

	# Change ownership to the web server owner - assumes the files directory is correctly owned
	chown  $fname, $web_owner 
	 or chmod 0666, $fname;

	# insert the file as user Admin (id=1), Project = 0
	$sql_stmt = "INSERT into files (file_real_filename, file_name, file_type, file_size, file_date, file_description, file_task)  values (";
	$sql_stmt .= " '" . $fid . "',";
	$sql_stmt .= " '" . $attach_realname[$att] . "',";
	$sql_stmt .= " '" . $attach_type[$att] . "', ";
	$sql_stmt .= sprintf("%d", $filesize);
	$sql_stmt .= ", NOW() , ";
	$desc = "File attachment from: " . $header{'From'} . "\nTicket #" . $ticket . "\nSubject: " . $header{'Subject'};
	$sql_stmt .= $dbh->quote($desc);
	$sql_stmt .= ", ";
	$sql_stmt .= $ticket;
	$sql_stmt .= " )";
    $sth = $dbh->prepare($sql_stmt);
    $sth->execute();
    $sth->finish();
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
    $boundary = "_lkqwkASDHASK89271893712893"; 

    # mail the report
    open(MAIL, "|$mailprog -t");
	print MAIL "To: $report_to_address\n";
	print MAIL "From: $report_from_address\n";
	if ($parent) {
	    print MAIL "Subject: Client followup to trouble ticket #$parent\n";
	} else {
	    print MAIL "Subject: New support ticket #$ticket\n";
	}
	print MAIL "Content-type: multipart/alternative; boundary=\"$boundary\"\n";
	print MAIL "Mime-Version: 1.0\n\n";
	print MAIL "--$boundary\n";
	print MAIL "Content-disposition: inline\n";
	print MAIL "Content-type: text/plain\n\n";
	if ($parent) {
	  print MAIL "Followup Trouble ticket to ticket #$parent\n\n";
	} else {
	  print MAIL "New Trouble Ticket\n\n";
	}
	print MAIL "Ticket ID: $ticket\n";
	print MAIL "Author   : $author\n";
	print MAIL "Subject  : $subject\n";
	print MAIL "View     : $app_root/index.php?m=ticketsmith&a=view&ticket=$ticket\n";
	print MAIL "\n--$boundary\n";
	print MAIL "Content-disposition: inline\n";
	print MAIL "Content-type: text/html\n\n";
	print MAIL "<html>";
	print MAIL "<head>";
	print MAIL "<style>";
	print MAIL ".title {";
	print MAIL "	FONT-SIZE: 18pt; SIZE: 18pt;";
	print MAIL "}";
	print MAIL "</style>";
	if ($parent) {
	    print MAIL "<title>Followup Trouble ticket to ticket #$parent</title>";
	} else {
	    print MAIL "<title>New Trouble ticket</title>";
	}
	print MAIL "</head>";
	print MAIL "<body>";
	print MAIL "";
	print MAIL "<TABLE border=0 cellpadding=4 cellspacing=1>";
	print MAIL "	<TR>";
	print MAIL "	<TD valign=top><img src=$app_root/images/icons/ticketsmith.gif alt= border=0 width=42 height=42></td>";
 
	print MAIL "		<TD nowrap><span class=title>Trouble Ticket Management</span></td>";
	print MAIL "		<TD valign=top align=right width=100%>&nbsp;</td>";
	print MAIL "	</tr>";
	print MAIL "</TABLE>";
	print MAIL "<TABLE width=600 border=0 cellpadding=4 cellspacing=1 bgcolor=#878676>";
	print MAIL "	<TR>";
	if ($parent) {
	    print MAIL "		<TD colspan=2><font face=arial,san-serif size=2 color=white>Followup Ticket Entered</font></TD>";
	} else {
	    print MAIL "		<TD colspan=2><font face=arial,san-serif size=2 color=white>New Ticket Entered</font></TD>";
	}
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
	print MAIL "		<TD bgcolor=white nowrap><a href=$app_root/index.php?m=ticketsmith&a=view&ticket=$ticket><font face=arial,san-serif size=2>$app_root/index.php?m=ticketsmith&a=view&ticket=$ticket</font></a></TD>";
	print MAIL "	</tr>";
	print MAIL "</TABLE>";
	print MAIL "</body>";
	print MAIL "</html>";
	print MAIL "\n--$boundary--\n";
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
    $boundary = "_lkqwkASDHASK89271893712893"; 

    # mail the report
    open(MAIL, "|$mailprog -t");
	print MAIL "To: $author\n";
	print MAIL "From: $report_from_address\n";
	print MAIL "Subject: [#$ticket] Your Support Request\n";
	print MAIL "Content-type: multipart/alternative; boundary=\"$boundary\"\n";
	print MAIL "Mime-Version: 1.0\n\n";
	print MAIL "--$boundary\n";
	print MAIL "Content-disposition: inline\n";
	print MAIL "Content-type: text/plain\n\n";
	print MAIL "This is an acknowledgement that your support request has been logged\n";
	print MAIL "by an automated support tracking system. It will be assigned to a\n";
	print MAIL "support representative who will be in touch in due course.\n\n";
	print MAIL "Details of support request:\n";
	print MAIL "Ticket ID: $ticket\n";
	print MAIL "Author   : $author\n";
	print MAIL "Subject  : $subject\n";
	print MAIL "\n--$boundary\n";
	print MAIL "Content-disposition: inline\n";
	print MAIL "Content-type: text/html\n\n";
	print MAIL "<html>";
	print MAIL "<head>";
	print MAIL "<style>";
	print MAIL ".title {";
	print MAIL "	FONT-SIZE: 18pt; SIZE: 18pt;";
	print MAIL "}";
	print MAIL "</style>";
	print MAIL "<title>Your Support Request</title>";
	print MAIL "</head>";
	print MAIL "<body>";
	print MAIL "";
	print MAIL "<TABLE border=0 cellpadding=4 cellspacing=1>";
	print MAIL "	<TR>";
	print MAIL "	<TD valign=top><img src=$app_root/images/icons/ticketsmith.gif alt= border=0 width=42 height=42></td>";
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
	print MAIL "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>&nbsp;</font></TD>";
	print MAIL "		<TD bgcolor=white nowrap><font face=arial,san-serif size=2>";
	print MAIL "This is an acknowledgement that your support request has been logged<br />";
	print MAIL "by an automated support tracking system. It will be assigned to a<br />";
	print MAIL "support representative who will be in touch in due course.";
        print MAIL "            </font></TD>";
	print MAIL "	</tr>";
	print MAIL "</TABLE>";
	print MAIL "</body>";
	print MAIL "</html>";
	print MAIL "\n--$boundary--\n";
	close(MAIL);

}


