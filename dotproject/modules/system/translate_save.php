<?php /* SYSTEM $Id$ */
/**
* Processes the entries in the translation form.
* @version $Revision$
* @author Andrew Eddie <users.sourceforge.net>
*/

$module = isset( $HTTP_POST_VARS['module'] ) ? $HTTP_POST_VARS['module'] : 0;
$lang = isset( $HTTP_POST_VARS['lang'] ) ? $HTTP_POST_VARS['lang'] : 'es';

$trans = isset( $HTTP_POST_VARS['trans'] ) ? $HTTP_POST_VARS['trans'] : 0;
//echo '<pre>';print_r( $trans );echo '</pre>';die;

// save to core locales if a translation exists there, otherwise save
// into the module's local locale area

if ( file_exists( "{$AppUI->cfg['root_dir']}/locales/$lang/$module.inc" ) ) {
	$filename = "{$AppUI->cfg['root_dir']}/locales/$lang/$module.inc";
} else {
	$filename = "{$AppUI->cfg['root_dir']}/modules/$module/locales/$lang.inc";
}

$fp = fopen ($filename, "wt");

if (!$fp) {
	$AppUI->setMsg( "Could not open locales file ($filename) to save.", UI_MSG_ERROR );
	$AppUI->redirect( "m=system" );
}

$txt = "##\n## DO NOT MODIFY THIS FILE BY HAND!\n##\n";

if ($lang == 'en') {
// editing the english file
	foreach ($trans as $langs) {
		if ( (@$langs['abbrev'] || $langs['english']) && empty($langs['del']) ) {
			$langs['abbrev'] = addslashes( stripslashes( @$langs['abbrev'] ) );
			$langs['english'] = addslashes( stripslashes( $langs['english'] ) );
			if (!empty($langs['abbrev'])) {
				$txt .= "\"{$langs['abbrev']}\"=>";
			}
			$txt .= "\"{$langs['english']}\",\n";
		}
	}
} else {
// editing the translation
	foreach ($trans as $langs) {
		if ( empty($langs['del']) ) {
			$langs['english'] = addslashes( stripslashes( $langs['english'] ) );
			$langs['lang'] = addslashes( stripslashes( $langs['lang'] ) );
			//fwrite( $fp, "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n" );
			$txt .= "\"{$langs['english']}\"=>\"{$langs['lang']}\",\n";
		}
	}
}
//echo "<pre>$txt</pre>";
fwrite( $fp, $txt );
fclose( $fp );

$AppUI->setMsg( "Locales file saved", UI_MSG_OK );
$AppUI->redirect();
?>
