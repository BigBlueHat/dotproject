<?php
##
##	Ticketsmith sql handler
##

$name = dPgetParam($_POST, 'name', '');
$email = dPgetParam($_POST, 'email', '');
$subject = dPgetParam($_POST, 'subject', '');
$priority = dPgetParam($_POST, 'priority', '');
$description = dPgetParam($_POST, 'description', '');
//$description = db_escape($description);

$author = $name . ' <' . $email . '>';
$q = new DBQuery;
$q->addTable('tickets');
$q->addInsert('author', $author);
$q->addInsert('subject', $subject);
$q->addInsert('priority', $priority);
$q->addInsert('body', $description);
$q->addInsert('timestamp', 'UNIX_TIMESTAMP()', false, true);
$q->addInsert('type', 'Open');

if (!$q->exec()) 
	$AppUI->setMsg( mysql_error() );
else 
{
	$AppUI->setMsg( 'Ticket added' );

	if ($priority == 4) // TODO
	{
		include ('classes/libmail.class.php');
		$mail = new Mail;
		$notification_email = dPgetConfig('notification_email');
		if ($mail->ValidEmail($notification_email))
			$mail->To($notification_email);

		$q->clear();	
		$q->addQuery('contact_email');
		$q->addTable('users', 'u');
		$q->leftJoin('contacts', 'c', 'c.contact_id = u.user_contact');
		$q->addWhere('u.user_id = ' . $AppUI->user_id);
		$owner_email = $q->loadResult();
			
		$mail->From($owner_email);
		$mail->Subject('Ticket: ' . $subject, $locale_char_set);
		
		$body = $subject . "\n\n";
		$body .= 'From: ' . $author . "\n";
		$body .= 'Priority: ' . $priority;
		$body .= 'Description: ' . "\n" . $description;
		$mail->Body($body, $locale_char_set);
		$mail->Send();
	}
}

$AppUI->redirect( 'm=ticketsmith' );
?>