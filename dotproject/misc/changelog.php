<?php
$email = 'core-developers@dotproject.net';
$mime_boundary = '----dotProject----'.md5(time());

exec('misc/cvs2cl/cvs2cl.pl --dp --accum');
$old_lines = exec('cat ChangeLog.bak | wc -l');
$new_lines = exec('cat ChangeLog | wc -l');
$changed_lines = $new_lines - $old_lines;

$text = 'dotProject ChangeLog
' . $changed_lines . ' new lines of CVS log' . "\n";
$html = '
<html>
<head>
	<title>dotProject ChangeLog</title>
</head>
<body>
<h1>' . $changed_lines . ' new lines of CVS log</h1>';

if ($changed_lines > 0)
{
	exec('head -n ' . $changed_lines . ' ChangeLog', $output);
	$html .= '
<table align="center" cellpadding="5">
<tr>
       <th>Date</th>
       <th>User</th>
       <th>Change</th>
</tr>' . "\n";
	$new_record = true;
	for ($i = 0; $i < $changed_lines; $i++)
	{
       if ($new_record)
       {
               list($date, $user) = explode('  ', $output[$i]);
							 $user = substr($user, 1, -1);
							 $users[$user]++;
               ++$i; // skip ---------------- line
               $new_record = false;
               continue;
       }
       elseif ($output[$i]{0} == '-')
               $changes .= substr($output[$i], 1) . '<br />';
       elseif (strlen(trim($output[$i])) == 0)
       {
					$text .= $date . "\t" . $user . "\t" . trim($changes) . "\n";
					$text .= $files . "\n\n";
					$html .= '
<tr>
	<td valign="top" nowrap="nowrap">' . $date . '</td>
	<td valign="top" nowrap="nowrap">' . $user . '</td>
	<td valign="top" width="90%">' . trim($changes) . '</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td colspan="2">' . $files . '</td>
</tr>' . "\n";
               $files = '';
               $changes = '';
               $date = '';
               $user = '';
               $new_record = true;
       }
       else
               $files .= $output[$i] . '<br />';
	}
	$html .= '</table>' . "\n";

	$html .= '<h1>User contributions:</h1>';
	$html .= '<table><tr><th>User</th><th>Commits</th></tr>';
	foreach ($users as $user => $commits)
		$html .= '<tr><td>'.$user.'</td><td>'.$commits.'</td></tr>';
	$html .= '</table>';

	exec('cvs ci -m "Updating ChangeLog on ' . date('Y-m-d') . '" ChangeLog');
	exec('cvs update ChangeLog');
	// Update ChangeLog to include latest Changelog change
	exec('misc/cvs2cl/cvs2cl.pl --dp --accum');
	copy('ChangeLog', 'ChangeLog.bak');
}
else {
	$text .= 'No changes since last time';
	$html .= '<h1>No changes since last time!</h1>';
}

$html .= '
</body>
</html>';

$dir = getcwd();
$ver = substr($dir, strrpos($dir, '/') + 1);
$headers = 'From: dotProject ChangeLog updater <cyberhorse@dotproject.net>' . "\n";
$headers .= 'Reply-To: dotProject ChangeLog updater <cyberhorse@dotproject.net>' . "\n";
$headers .= 'MIME-Version: 1.0' . "\n";
$headers .= 'Content-type: text/html; UTF-8' . "\n";

/**** multipart
$headers .= 'Content-type: multipart/alternative; boundary="'.$mime_boundary.'"' . "\n";

$message = '--'.$mime_boundary."\n";
$message .= 'Content-Type: text/plain; UTF-8'."\n";
$message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
$message .= $text."\n\n";
$message .= '--'.$mime_boundary."\n";
$message .= 'Content-Type: text/html; UTF-8'."\n";
$message .= 'Content-Transfer-Encoding: 8bit'."\n\n";
$message .= $html."\n";
$message .= '--'.$mime_boundary."\n\n";
*/
$message = $html;

$subject = '['.$ver.'] ChangeLog ' . date('Y-m-d');
mail($email, $subject, $message, $headers);
?>
