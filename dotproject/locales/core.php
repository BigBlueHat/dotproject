<?php
ob_start();
	@readfile( "$root_dir/locales/$AppUI->user_locale/common.inc" );
	@readfile( "$root_dir/locales/$AppUI->user_locale/$m.inc" );
	if ($m == 'departments') {
		@readfile( "$root_dir/locales/$AppUI->user_locale/companies.inc" );
	}
	eval( "\$GLOBALS['translate']=array(".ob_get_contents()."\n'0');" );
ob_end_clean();
?>