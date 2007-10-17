<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {projectSelectWithOptGroup userId= name= extras= value= exclProj=} function plugin
 *
 * Type:     function
 * Name:     projectSelectWithOptGroup
 * Purpose:  create a select form for projects grouped by Company/Department through dp
 *
 * @param array Format: array(
 * 'var' => variable name, 
 * 'value' => value to assign,
 * )
 * @param Smarty
 */
function smarty_function_dPprojectSelectWithOptGroup($params, &$smarty)
{
	global $AppUI;
	extract($params);
	
	if (!isset($name)) {
	    $smarty->trigger_error('dParraySelect: missing parameter "name"');
	    return;
	}
	
	if (!isset($extras)){
		$extras = '';
	}

	if (!isset($value)){
		$value = null;
	}

	if (!isset($exclProj)){
		$exclProj = null;
	}
	
	if (!isset($userId)){
		$userId = $AppUI->user_id;
	}

	return projectSelectWithOptGroup( $userId, $name, $extras, $value, $exclProj);
}

/* vim: set expandtab: */

?>
