<?php
global $AppUI, $do_store_locale, $pref_locale, $tab;
$AppUI->user_locale = isset($pref_locale) ? $pref_locale : $AppUI->user_locale;
$AppUI->setUserLocale( $AppUI->user_locale );
if ($do_store_locale) {
	$AppUI->setMsg("Language changed", UI_MSG_OK);
	$AppUI->redirect( "m=install&tab=$tab");
}

echo '<form name="locFrm" action="index.php?m=install&tab='.$tab.'" method="post">';

	// read the installed languages
	$locales = $AppUI->readDirs( 'locales' );
	$temp = $AppUI->setWarning( false );
	echo arraySelect( $locales, 'pref_locale', 'class=text size=1', @$AppUI->user_locale, true );
	$AppUI->setWarning( $temp );


?>
<input class="button" type="submit" name="do_store_locale" value="<?php echo $AppUI->_('save');?>" />
</form>
