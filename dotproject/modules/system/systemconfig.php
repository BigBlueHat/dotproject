<?php  // $Id$
$dPcfg = new CConfig();

// retrieve the system configuration data
$rs = $dPcfg->loadAll();

// retrieve any state parameters
if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'ConfigIdxTab', $_GET['tab'] );
}
$tab = $AppUI->getState( 'ConfigIdxTab' ) !== NULL ? $AppUI->getState( 'ConfigIdxTab' ) : 0;
$active = intval( !$AppUI->getState( 'ConfigIdxTab' ) );

$titleBlock = new CTitleBlock('System Configuration', 'control-center.png', $m);
$titleBlock->show();

echo $AppUI->_("syscfg_intro");
echo "<br />&nbsp;<br />";


// prepare the automated form fields based on db system configuration data
$output  = null;
foreach ($rs as $c) {

	// extraparse the checkboxes
	$checked = ($c['config_value'] == 'true') ? "checked='checked'" : '';
	$properties = ($c['config_type'] == 'checkbox') ? "value='true' $checked" : "value='{$c['config_value']}'";
 	$tooltip = "title='".$AppUI->_($c['config_name'].'_tooltip')."'";

	$output .= "<tr>
			<td class='item' width='20%'>".$AppUI->_($c['config_name'].'_title')."</td>
            		<td align='left'>
				<input class='button' type='{$c['config_type']}'  name='dPcfg[{$c['config_name']}]' $properties $tooltip/>
				<a href='#' onClick=\"javascript:window.open('?m=system&a=systemconfig_help&dialog=1&cn={$c['config_name']}', 'contexthelp', 'width=400, height=200, left=50, top=50, scrollbars=yes, resizable=yes')\">(?)</a>
				<input class='button' type='hidden'  name='dPcfgId[{$c['config_name']}]' value='{$c['config_id']}' />
			</td>
        </tr>
	";

	}
echo '<form name="cfgFrm" action="index.php?m=system&a=systemconfig" method="post">';
?>
<input type="hidden" name="dosql" value="do_systemconfig_aed" />
<table cellspacing="0" cellpadding="3" border="0" class="std" width="100%" align="center">
	<?php
	echo $output;
	?>
	<tr>
 		<td align="right" colspan="2"><input class="button" type="submit" name="do_save_cfg" value="<?php echo $AppUI->_('Save');?>" /></td>
	</tr>
</table></form>
