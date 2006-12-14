<?php

if (!defined('LOADED_AS_MODULE')) {
	die ('You cannot access this file directly');
}
if (! pnLocalReferer()) {
	die('You cannot access this file from an external site');
}
if (! $url) {
	die('You must use the {} calling method in your menu, not []');
}

$home = pnGetBaseURL();
$home .= 'user.php?op=loginscreen&module=NS-User';
if (!pnUserLoggedIn()) {
	pnRedirect($home);
}

// Concatination present to clear warnings in eclipse (cannot find which header.php file the include is referring to).
include(''.'header.php');
echo '<iframe name="dplink" src="'.$url.'" width="100%" height="1600"
marginwidth="0" marginheight="0" frameborder="0"></iframe>';
include(''.'footer.php');
?>