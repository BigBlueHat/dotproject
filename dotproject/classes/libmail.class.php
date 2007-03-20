<?php /* CLASSES $Id$ */
if (!defined('DP_BASE_DIR')){
  die('You should not access this file directly');
}

/**
 *	This class encapsulates the PHP mail() function.
 *
 *	Implements CC, Bcc, Priority headers
 *	@version	1.3
 *	<ul>
 *	<li>added ReplyTo( $address ) method
 *	<li>added Receipt() method - to add a mail receipt
 *	<li>added optionnal charset parameter to Body() method. this should fix charset problem on some mail clients
 *	</ul>
 *  Example
 *	
 *	@code
 *	include "libmail.php";
 *
 *	$m= new Mail; // create the mail
 *	$m->From( "leo@isp.com" );
 *	$m->To( "destination@somewhere.fr" );
 *	$m->Subject( "the subject of the mail" );
 *
 *	$message= "Hello world!\nthis is a test of the Mail class\nplease ignore\nThanks.";
 *	$m->Body( $message);	// set the body
 *	$m->Cc( "someone@somewhere.fr");
 *	$m->Bcc( "someoneelse@somewhere.fr");
 *	$m->Priority(4) ;	// set the priority to Low
 *	$m->Attach( "/home/leo/toto.gif", "image/gif" ) ;	// attach a file of type image/gif
 *	$m->Send();	// send the mail
 *	echo "the mail below has been sent:<br><pre>", $m->Get(), "</pre>";
 *	@endcode

LASTMOD
	Fri Oct  6 15:46:12 UTC 2000

 *	@author	Leo West - lwest@free.fr
 */
class Mail
{
/** list of To addresses */
	var $sendto = array();
/** list of CC addresses */
	var $acc = array();
/** list of BCC addresses */
	var $abcc = array();
/** paths of attached files */
	var $aattach = array();
/** type of attached files : file (false) or text string (string) */
	var $aString = array();
/** list of message headers */
	var $xheaders = array();
/** message priorities referential */
	var $priorities = array( '1 (Highest)', '2 (High)', '3 (Normal)', '4 (Low)', '5 (Lowest)' );
/** character set of message */
	var $charset = "us-ascii";
/** character set encoding */
	var $ctencoding = "7bit";
/** message contains a return receipt */
	var $receipt = 0;

	var $useRawAddress = true;

/** SMTP host to use, default is localhost */
	var $host;
/** port to use, default is 25(smtp) */
	var $port;
/** whether to use SASL authentication, default is false */
	var $sasl;
/** username for authentication */
	var $username;
/** password for authentication */
	var $password;
/** transport method to use, default is php's mail() function */
	var $transport;
/** defer mail delivery */
	var $defer;

/**
 *	Mail constructor
*/
function Mail()
{
	$this->autoCheck( true );
	$this->boundary= "--" . md5( uniqid("myboundary") );
	// Grab the current mail handling options
	$this->transport = dPgetConfig('mail_transport', 'php');
	$this->host = dPgetConfig('mail_host', 'localhost');
	$this->port = dPgetConfig('mail_port', '25');
	$this->sasl = dPgetConfig('mail_auth', false);
	$this->username = dPgetConfig('mail_user');
	$this->password = dPgetConfig('mail_pass');
	$this->defer = dPgetConfig('mail_defer');
	$this->timeout = dPgetConfig('mail_timeout', 0);
}

/**
 *	activate or de-activate the email addresses validator
 *
 *	by default autoCheck feature is on
 *	@param $bool set to true to turn on automatic e-mail address validation.
 */
function autoCheck( $bool )
{
	if( $bool ) {
		$this->checkAddress = true;
	} else {
		$this->checkAddress = false;
	}
}

/**
 *	Define the subject line of the email
 *	@param $subject any monoline string
 *	@param $charset encoding to be used for Quoted-Printable encoding of the subject 
*/
function Subject( $subject, $charset='' )
{
	global $AppUI;
	
	if( isset($charset) && $charset != "" ) {
		$this->charset = strtolower($charset);
	}
	
	if ( ( $AppUI->user_locale != 'en' || ( $this->charset && $this->charset != 'us-ascii' && $this->charset != 'utf-8') ) && function_exists('imap_8bit')) {
		$subject = "=?".$this->charset."?Q?".
			str_replace("=\r\n","",imap_8bit($subject))."?=";		
	}
	$this->xheaders['Subject'] = dPgetConfig('email_prefix', '').' '.strtr( $subject, "\r\n" , "  " );
}

/**
 *	Set the sender of the mail
 *	@param $from should be an email address
 */
function From( $from )
{
	if( ! is_string($from) ) {
		echo "Class Mail: error, From is not a string";
		exit;
	}
	$this->xheaders['From'] = $from;
}

/**
 *	Set the Reply-to header
 *	@param $email should be an email address
*/
function ReplyTo( $address )
{
	if (!is_string($address)) {
		return false;
	}
	$this->xheaders["Reply-To"] = $address;
}

/** Add a return receipt to the e-mail
 *
 *	Ie.  a confirmation is returned to the "From" address (or "ReplyTo" if defined)
 *	when the receiver opens the message.
 *	@warning this functionality is *not* a standard, thus only some mail clients are compliant.
*/
function Receipt()
{
	$this->receipt = 1;
}

/**
 *	Set the mail recipient
 *
 *	The optional reset parameter is useful when looping through records to send individual mails.
 *	This prevents the 'to' array being continually stacked with additional addresses.
 *
 *	@param $to email address, accept both a single address or an array of addresses
 *	@param $reset resets the current array
*/
function To( $to, $reset=false )
{

	// TODO : test validité sur to
	if( is_array( $to ) ) {
		$this->sendto = $to;
	} else {
		if ($this->useRawAddress) {
		   if( preg_match( "/^(.*)\<(.+)\>$/", $to, $regs ) ) {
			  $to = $regs[2];
		   }
		}
		if ($reset) {
			unset( $this->sendto );
			$this->sendto = array();
		}
		$this->sendto[] = $to;
	}

	if( $this->checkAddress == true )
		$this->CheckAdresses( $this->sendto );

}

/** Set the CC recipients
 *	
 *	Set the carbon copy recipients using an array or a comma delimited string
 *	@param $cc Array of e-mail addresses, or string of e-mail addresses seperated by commas.
 */
function Cc( $cc )
{
	if( is_array($cc) )
		$this->acc= $cc;
	else
		$this->acc= explode(',', $cc);

	if( $this->checkAddress == true )
		$this->CheckAdresses( $this->acc );

}

/** Set the BCC recipients
 *
 *	set the blind carbon copy recipients using an array or a comma delimited string
 *	@param $bcc Array of e-mail addresses, or string of e-mail addresses seperated by commas.
 */
function Bcc( $bcc )
{
	if( is_array($bcc) ) {
		$this->abcc = $bcc;
	} else {
		$this->abcc[]= $bcc;
	}

	if( $this->checkAddress == true )
		$this->CheckAdresses( $this->abcc );
}

/** Set the body (message) of the mail
 *
 * define the charset if the message contains extended characters (accents)
 * defaults to us-ascii, 
 * Example:
 * @code
 * $mail->Body( "mél en français avec des accents", "iso-8859-1" );
 * @endcode
 * @param $body Body of the e-mail message
 * @param $charset charset to use
 */
function Body( $body, $charset="" )
{
	$this->body = $body;

	if( isset($charset) && $charset != "" ) {
		$this->charset = strtolower($charset);
		if( $this->charset != "us-ascii" )
			$this->ctencoding = "8bit";
	}
}

/** Set the organization header
 * @param $org value of the organization header
 */
function Organization( $org )
{
	if( trim( $org != "" )  )
		$this->xheaders['Organization'] = $org;
}

/** Set the mail priority
 *	@param $priority e-mail priority from 1 (highest) to 5 (lowest)
 */
function Priority( $priority )
{
	if( ! intval( $priority ) )
		return false;

	if( ! isset( $this->priorities[$priority-1]) )
		return false;

	$this->xheaders["X-Priority"] = $this->priorities[$priority-1];

	return true;
}

/** Attach a file to the mail
 *
 *	@param $filename Path of the file to attach
 *	@param $filetype MIME-type of the file. default to 'application/x-unknown-content-type'
 *	@param $disposition Instruct the Mailclient to display the file if possible ("inline") or always as a link ("attachment") possible values are "inline", "attachment"
 *	@param $isString expects $filename to be a real existing file link if FALSE; if var is a STRING $filename is expected to be a dummy and the attachment will be generated from the content of $isString (like an icalendar text block)
 */
function Attach( $filename, $filetype = "", $disposition = "inline", $isString = false )
{
	// TODO : si filetype="", alors chercher dans un tablo de MT connus / extension du fichier
	if( $filetype == "" )
		$filetype = "application/x-unknown-content-type";

	$this->aattach[] = $filename;
	$this->actype[] = $filetype;
	$this->adispo[] = $disposition;
	$this->aString[] = $isString;
}

/** Reset file attachments
*/
function clearAttachments()
{
	$this->aattach 	= array();
	$this->actype 	= array();
	$this->adispo 	= array();
	$this->aString 	= array();
}

/**
 *	Build the email message
 *	@internal
*/
function BuildMail()
{
// build the headers
	global $AppUI;

	$this->headers = "";
//	$this->xheaders['To'] = implode( ", ", $this->sendto );

	if( count($this->acc) > 0 ) {
		$this->xheaders['CC'] = implode( ", ", $this->acc );
	}
	if( count($this->abcc) > 0 ) {
		$this->xheaders['BCC'] = implode( ", ", $this->abcc );
	}

	if( $this->receipt ) {
		if( isset($this->xheaders["Reply-To"] ) ) {
			$this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
		} else {
			$this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
		}
	}

	if( $this->charset != "" ) {
		$this->xheaders["Mime-Version"] = "1.0";
		$this->xheaders["Content-Type"] = "text/plain; charset=$this->charset";
		$this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
	}

	$this->xheaders["X-Mailer"] = "dotProject v" . $AppUI->getVersion();

	// include attached files
	if( count( $this->aattach ) > 0 ) {
		$this->_build_attachement();
	} else {
		$sep = "\n";
		$arr = preg_split("/(\r?\n)|\r/", $this->body);
		$this->fullBody = implode($sep, $arr);
	}

	reset($this->xheaders);
	while( list( $hdr,$value ) = each( $this->xheaders )  ) {
		if( $hdr != "Subject" )
			$this->headers .= "$hdr: $value\n";
	}
}

/** Format and send the mail
 * @return Return status of the transport used to send the e-mail
 */
function Send()
{
	$this->BuildMail();

	$this->strTo = implode( ", ", $this->sendto );

	if ($this->defer)
		return $this->QueueMail();
	else if ($this->transport == 'smtp')
		return $this->SMTPSend( $this->sendto, $this->xheaders['Subject'], $this->fullBody, $this->xheaders );
	else
		return @mail( $this->strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers );
}

/**
 * Send email via an SMTP connection.
 * @param $to To recipients
 * @param $subject E-mail subject
 * @param $body E-mail body
 * @param &$headers Extra headers
 * @return false on error, true on success
 */
function SMTPSend($to, $subject, $body, &$headers)
{
	global $AppUI;

	// Start the connection to the server
	$error_number = 0;
	$error_message = '';
	$this->socket = fsockopen($this->host, $this->port, $error_number, $error_message, $this->timeout);
	if (! $this->socket) {
		dprint(__FILE__, __LINE__, 1, "Error on connecting to host {$this->host} at port {$this->port}: $error_message ($error_number)");
		$AppUI->setMsg("Cannot connect to SMTP Host: $error_message ($error_number)");
		return false;
	}
	// Read the opening stuff;
	$this->socketRead();
	// Send the protocol start
	$this->socketSend("HELO " . $this->getHostName());
	if ($this->sasl && $this->username) {
		$this->socketSend("AUTH LOGIN");
		$this->socketSend(base64_encode($this->username));
		$rcv = $this->socketSend(base64_encode($this->password));
		if (strpos($rcv, '235') !== 0) {
			dprint(__FILE__, __LINE__, 1, "Authentication failed on server: $rcv");
			$AppUI->setMsg("Failed to login to SMTP server: $rcv");
			fclose($this->socket);
			return false;
		}
	}
	// Determine the mail from address.
	if ( ! isset($headers['From'])) {
		$from = dPgetConfig('admin_user') . '@' . dPgetConfig('site_domain');
	} else {
		// Search for the parts of the email address
		if (preg_match('/.*<([^@]+@[a-z0-9\._-]+)>/i', $headers['From'], $matches))
			$from = $matches[1];
		else
			$from = $headers['From'];
	}
	$rcv = $this->socketSend("MAIL FROM: <$from>");
	if (substr($rcv,0,1) != '2') {
		$AppUI->setMsg("Failed to send email: $rcv", UI_MSG_ERROR);
		return false;
	}
	foreach ($to as $to_address) {
		if (strpos($to_address, '<') !== false) {
			preg_match('/^.*<([^@]+\@[a-z0-9\._-]+)>/i', $to_address, $matches);
			if (isset($matches[1]))
				$to_address = $matches[1];
		}
		$rcv = $this->socketSend("RCPT TO: <$to_address>");
		if (substr($rcv,0,1) != '2') {
			$AppUI->setMsg("Failed to send email: $rcv", UI_MSG_ERROR);
			return false;
		}
	}
	$this->socketSend("DATA");
	foreach ($headers as $hdr =>$val) {
		$this->socketSend("$hdr: $val", false);
	}
	// Now build the To Headers as well.
	$this->socketSend("To: " . implode(', ', $to), false);
	$this->socketSend("Date: " . date('r'), false);
	$this->socketSend("", false);
	$this->socketSend($body, false);
	$result = $this->socketSend(".\r\nQUIT");
	if (strpos($result, '250') === 0)
		return true;
	else {
		dprint(__FILE__, __LINE__, 1, "Failed to send email from $from to $to_address: $result");
		$AppUI->setMsg("Failed to send email: $result");
		return false;
	}
}

/** Read the connected socket buffer
 * @internal
 * @return Data from socket
 */
function socketRead()
{
	$result = fgets($this->socket, 4096);
	dprint(__FILE__, __LINE__, 12, "server said: $result");
	return $result;
}

/** Send data to the connected socket
 * @internal
 * @param $msg Message to send
 * @param $rcv Return the servers response as the result
 * @return if $rcv is true, returns the servers response. If $rcv is false returns the number of bytes sent(?)
 */
function socketSend($msg, $rcv = true)
{
	dprint(__FILE__, __LINE__, 12, "sending: $msg");
	$sent = fputs($this->socket, $msg . "\r\n");
	if ($rcv)
		return $this->socketRead();
	else
		return $sent;
}

/** Get the hostname of this server
*/
function getHostName()
{
  // Grab the server address, return a hostname for it.
  if ($host = gethostbyaddr($_SERVER['SERVER_ADDR']))
    return $host;
  else
    return '[' . $_SERVER['SERVER_ADDR'] . ']';
}

/**
 * Queue mail to allow the queue manager to trigger
 * the email transfer.
 *
 * @return Event queue ID 
 */
function QueueMail()
{
	global $AppUI;

	require_once $AppUI->getSystemClass('event_queue');
	$ec = new EventQueue;
	$vars = get_object_vars($this);
	return $ec->add(array('Mail', 'SendQueuedMail'), $vars, 'libmail', true);
}

/**
 * Dequeue the email and transfer it.  Called from the queue manager.
 * @note The first three parameters are not used in this method. Maybe deprecated? - ebrosnan
 * @param $mod Queue module
 * @param $type Queue type
 * @param $originator Originator
 * @param $owner Event owner
 * @param &$args Arguments to use with SMTPSend or mail()
 * @return The return status of the transport's method
 */
function SendQueuedMail($mod, $type, $originator, $owner, &$args)
{
	extract($args);
	// These two lines scrub the message body to take care of "no CR prepended (SMTP code 451)"
	$fullBody = str_replace("\r\n", "\n", $fullBody);
	$fullBody = str_replace("\n", "\r\n", $fullBody);

	if ($this->transport == 'smtp') {
		return $this->SMTPSend($this->sendto, $this->xheaders['Subject'], $fullBody, $this->xheaders);
	} else {
		$headers = '';
		foreach ($this->xheaders as $hdr =>$val) {
			$headers .= "$hdr: $val\r\n";
		}
		$strTo = explode(', ', $this->sendto);
		return @mail( $strTo, $this->xheaders['Subject'], $fullBody, $headers );
	}
}

/**
 *	Returns the whole e-mail , headers + message
 *
 *	can be used for displaying the message in plain text or logging it
 *
 *	@return Entire e-mail as a string.
 */
function Get()
{
	$this->BuildMail();
	$mail = "To: " . $this->strTo . "\r\n";
	$mail .= $this->headers . "\r\n";
	$mail .= $this->fullBody;
	return $mail;
}

/**
 *	Check an email address validity
 *
 *	@param $address E-mail address to check
 *	@return boolean true if e-mail address is ok
 */
function ValidEmail($address)
{
   if( preg_match( "/^(.*)\<(.+)\>$/", $address, $regs ) ) {
      $address = $regs[2];
   }
   if( preg_match( "/^[^@ ]+@([a-zA-Z0-9.\-.]+)$/",$address) ) {
      return true;
   } else {
      return false;
   }
}

/** Check validity of multiple email addresses
 * @todo Replace echo of invalid email addresses with a returned array of invalid addresses, also correct spelling of CheckAdresses()
 * @param $aad Array of e-mail addresses to check
 */
function CheckAdresses( $aad )
{
	for($i=0;$i< count( $aad); $i++ ) {
		if( ! $this->ValidEmail( $aad[$i]) ) {
			echo "Class Mail, method Mail : invalid address $aad[$i]";
			exit;
		}
	}
}

/** Check and encode attached file(s)
*/
function _build_attachement()
{
	$sep= "\r\n";
	
	$this->xheaders["Content-Type"] = 'multipart/mixed; boundary="'.$this->boundary .'"';

	$this->fullBody = 'This is a multi-part message in MIME format.' . $sep.$sep;
	$this->fullBody .= '--' . $this->boundary . $sep;
	$this->fullBody .= 'Content-Type: text/plain;'.$sep .' charset=' . $this->charset . $sep;
	$this->fullBody .= 'Content-Transfer-Encoding: ' . $this->ctencoding . $sep . $sep;

	
	$body = preg_split("/\r?\n/", $this->body);
	$this->fullBody .= implode($sep, $body) . $sep;

	$ata= array();
	$k=0;

	// for each attached file, do...
	for( $i=0; $i < count( $this->aattach); $i++ ) {
		$filename = $this->aattach[$i];
		$basename = basename($filename);
		$ctype = $this->actype[$i];	// content-type
		$disposition = $this->adispo[$i];

		$subhdr = '--' . $this->boundary . $sep;
		$subhdr .= 'Content-Type: '. $ctype . ';  name="'.$basename.'"'.$sep;
		$subhdr .= 'Content-Transfer-Encoding: base64'.$sep;
		$subhdr .= 'Content-Disposition: '.$disposition.';' . $sep;
	
		if ($this->aString[$i] == false) {	// attachment is a real file
			if( ! file_exists( $filename) ) {
				echo "Class Mail, method attach : file $filename can't be found"; exit;
			}
			$subhdr .= '  filename="'.$basename.'"' . $sep;
			$ata[$k++] = $subhdr;
			// non encoded line length
			$linesz= filesize( $filename)+1;
			$fp= fopen( $filename, 'r' );
			$ata[$k++] = chunk_split(base64_encode(fread( $fp, $linesz)));
			fclose($fp);
		} else {				// attachment is included from text string
			$subhdr .= '  filename="'.$basename.'"' . $sep . ' method="publish"'. $sep;
			$ata[$k++] = $subhdr;
			$ata[$k++] = base64_encode($this->aString[$i]);
		}
	}
	$this->fullBody .= implode($sep, $ata);
}

} // class Mail
?>