<?php
ob_start();
	@readfile( "$root_dir/locales/$AppUI->user_locale/common.inc" );
	@readfile( "$root_dir/locales/$AppUI->user_locale/$m.inc" );
	switch ($m) {
	case 'departments':
		@readfile( "$root_dir/locales/$AppUI->user_locale/companies.inc" );
		break;
	case 'system':
		@readfile( "$root_dir/locales/$host_locale/styles.inc" );
		break;
	}
	eval( "\$GLOBALS['translate']=array(".ob_get_contents()."\n'0');" );
ob_end_clean();
?>