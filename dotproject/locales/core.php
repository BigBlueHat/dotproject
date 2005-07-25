<?php
ob_start();
	@readfile( "{$dPconfig['root_dir']}/locales/$AppUI->user_locale/common.inc" );
	
// language files for specific locales and specific modules (for external modules) should be 
// put in modules/[the-module]/locales/[the-locale]/[the-module].inc
// this allows for module specific translations to be distributed with the module
	
	if ( file_exists( "{$dPconfig['root_dir']}/modules/$m/locales/$AppUI->user_locale.inc" ) )
	{
		@readfile( "{$dPconfig['root_dir']}/modules/$m/locales/$AppUI->user_locale.inc" );
	}
	else
	{
		@readfile( "{$dPconfig['root_dir']}/locales/$AppUI->user_locale/$m.inc" );
	}

	//$all_tabs =& $_SESSION['all_tabs'][$m];
	foreach($all_tabs as $key => $tab)
	{
		if (is_int($key))
			$extra_modules[$tab['module']] = true;
		else 
			foreach($tab as $child_tab)
				$extra_modules[$child_tab['module']] = true;
	}
	
	foreach($extra_modules as $extra_module => $k)
	{
		if ( file_exists( "{$dPconfig['root_dir']}/modules/$extra_module/locales/$AppUI->user_locale.inc" ) )
    	@readfile( "{$dPconfig['root_dir']}/modules/$extra_module/locales/$AppUI->user_locale.inc" );
		else
			@readfile("{$dPconfig['root_dir']}/locales/$AppUI->user_locale/$extra_module.inc" );
	}

	switch ($m) {
	case 'departments':
		@readfile( "{$dPconfig['root_dir']}/locales/$AppUI->user_locale/companies.inc" );
		break;
	case 'system':
		@readfile( "{$dPconfig['root_dir']}/locales/{$dPconfig['host_locale']}/styles.inc" );
		break;
	}
	eval( "\$GLOBALS['translate']=array(".ob_get_contents()."\n'0');" );
ob_end_clean();
?>
