<?php

$module = isset( $HTTP_POST_VARS['module'] ) ? $HTTP_POST_VARS['module'] : 0;
$lang = isset( $HTTP_POST_VARS['lang'] ) ? $HTTP_POST_VARS['lang'] : 'es';

$trans = isset( $HTTP_POST_VARS['trans'] ) ? $HTTP_POST_VARS['trans'] : 0;
//print_r($trans); die;

if (!($fp = fopen ("{$AppUI->cfg['root_dir']}/locales/$lang/$module.inc", "wt"))) {
	$AppUI->setMsg( "Could not open locales file to save.", UI_MSG_ERROR );
	$AppUI->redirect( "m=system" );
}

fwrite( $fp, "##\n## DO NOT MODIFY THIS FILE BY HAND!\n##\n" );

if ($lang == 'en') {
// editing the english file
	foreach ($trans as $langs) {
		if ( ($langs['abbrev'] || $langs['english']) && empty($langs['del']) ) {
			$langs['abbrev'] = stripslashes( $langs['abbrev'] );
			$langs['english'] = stripslashes( $langs['english'] );
			if (!empty($langs['abbrev'])) {
				fwrite( $fp, "\"{$langs['abbrev']}\"=>" );
			}
			fwrite( $fp, "\"{$langs['english']}\",\n" );
		}
	}
} else {
// editing the translation
	foreach ($trans as $langs) {
		if ( empty($langs['del']) ) {
			$langs['english'] = stripslashes( $langs['english'] );
			fwrite( $fp, "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n" );
		}
	}
}

fclose($fp);

$AppUI->setMsg( "Locales file saved", UI_MSG_OK );
$AppUI->redirect();

?>