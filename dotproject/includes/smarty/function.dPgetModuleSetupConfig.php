<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/*
 * Smarty {require file=word} function plugin
 *
 * Type:     function<br>
 * Name:     configuration<br>
 * Purpose:  get module config array from modules/MODULENAME/setup.php
 *
 * @param array Format: array('file' => variable name)
 * @param Smarty
 */
function smarty_function_dPgetModuleSetupConfig($params, &$smarty)
{
	global $AppUI, $tpl;
    extract($params);

    if (empty($file)) {
        $smarty->trigger_error("dPrequire: missing 'file' parameter");
        return;
    }

    require $file;

    /* Alternative config gathering for the case that simply requiring the file fails
    $configured = false;
    $config = array();
    $handle = fopen ($file, "r");
    while (!feof($handle)) {
       $buffer = fgets($handle);
       if ($buf = stristr($buffer, '$config[')) {
        $p = strpos($buf, '[')+1;
        $k = substr($buf, $p, strpos($buf, ']')-$p);
        $k = trim(str_replace(array("'", '"'), '', $k));
        if ($b = stristr($buf, '=')) {
          $p = strpos($b, ';');
          $v = substr($b, 1, $p-1);
          $v = trim(str_replace(array("'", '"'), '', $v));
        }
        $config[$k] = $v;
        $configured = true;
       } elseif ($configured) // do not scan the rest of the file if $config def is finished
        break;
    }
    fclose ($handle);
    */

		$tpl->assign($modCon, $config);
}

/* vim: set expandtab: */

?>
