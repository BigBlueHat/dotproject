<?php
ob_start();
	@readfile( "{$AppUI->cfg['root_dir']}/locales/$AppUI->user_locale/common.inc" );
	
// language files for specific locales and specific modules (for external modules) should be 
// put in modules/[the-module]/locales/[the-locale]/[the-module].inc
// this allows for module specific translations to be distributed with the module
	
	if ( file_exists( "{$AppUI->cfg['root_dir']}/modules/$m/locales/$AppUI->user_locale.inc" ) )
	{
		@readfile( "{$AppUI->cfg['root_dir']}/modules/$m/locales/$AppUI->user_locale.inc" );
	}
	else
	{
		@readfile( "{$AppUI->cfg['root_dir']}/locales/$AppUI->user_locale/$m.inc" );
	}
	
	switch ($m) {
	case 'departments':
		@readfile( "{$AppUI->cfg['root_dir']}/locales/$AppUI->user_locale/companies.inc" );
		break;
	case 'system':
		@readfile( "{$AppUI->cfg['root_dir']}/locales/{$AppUI->cfg['host_locale']}/styles.inc" );
		break;
	}
	eval( "\$GLOBALS['translate']=array(".ob_get_contents()."\n'0');" );
ob_end_clean();
?>