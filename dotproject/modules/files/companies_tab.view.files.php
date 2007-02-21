<?php // check access to files module
if (!defined('DP_BASE_DIR')){
	die('You should not access this file directly');
}

global $AppUI, $m, $company_id;
if (!getDenyRead( 'files' )) {
	if (!getDenyEdit( 'files' )) { 
		echo '<a href="./index.php?m=files&a=addedit">' . $AppUI->_('Attach a file') . '</a>';
	}
	echo dPshowImage( dPfindImage( 'stock_attach-16.png', $m ), 16, 16, '' ); 
	$showProject=true;
	include(DP_BASE_DIR . '/modules/files/index_table.php');
}
?>