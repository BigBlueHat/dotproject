<?php
ob_start();
	@readfile( "{$AppUI->cfg['root_dir']}/locales/$AppUI->user_locale/common.inc" );
	@readfile( "{$AppUI->cfg['root_dir']}/locales/$AppUI->user_locale/$m.inc" );
	eval( "\$GLOBALS['translate']=array(".ob_get_contents()."\n'0');" );
ob_end_clean();
?>