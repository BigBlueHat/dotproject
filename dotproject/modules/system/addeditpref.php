<?php /* SYSTEM $Id$ */
##
## add or edit a user preferences
##
$user_id = isset($HTTP_GET_VARS['user_id']) ? $HTTP_GET_VARS['user_id'] : 0;
// Why does this need to be different to $user_id?
$transmit_user_id = $_GET['user_id'];
// Check permissions
if (!$canEdit && $transmit_user_id != $AppUI->user_id) {
  $AppUI->redirect("m=public&a=access_denied" );
}

// load the preferences
$sql = "
SELECT pref_name, pref_value
FROM user_preferences
WHERE pref_user = $user_id
";
$prefs = db_loadHashList( $sql );

// get the user name
$user = dPgetUsernameFromID($user_id);

$titleBlock = new CTitleBlock( 'Edit User Preferences', 'myevo-weather.png', $m, "$m.$a" );
$titleBlock->addCrumb( "?m=system", "system admin" );
$titleBlock->show();
?>
<script language="javascript">
function submitIt(){
	var form = document.changeuser;
	//if (form.user_username.value.length < 3) {
	//	alert("Please enter a valid user name");
	//	form.user_username.focus();
	//} else {
		form.submit();
	//}
}
</script>

<table width="100%" border="0" cellpadding="1" cellspacing="1" class="std">

<form name="changeuser" action="./index.php?m=system" method="post">
	<input type="hidden" name="dosql" value="do_preference_aed" />
	<input type="hidden" name="pref_user" value="<?php echo $user_id;?>" />
	<input type="hidden" name="del" value="0" />

<tr height="20">
	<th colspan="2"><?php echo $AppUI->_('User Preferences');?>:
	<?php
		echo $user_id ? "$user" : $AppUI->_("Default");
	?></th>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Locale');?>:</td>
	<td>
<?php
	// read the installed languages
	$locales = $AppUI->readDirs( 'locales' );
	$temp = $AppUI->setWarning( false );
	echo arraySelect( $locales, 'pref_name[LOCALE]', 'class=text size=1', @$prefs['LOCALE'], true );
	$AppUI->setWarning( $temp );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Tabbed Box View');?>:</td>
	<td>
<?php
	$tabview = array( 'either', 'tabbed', 'flat' );
	echo arraySelect( $tabview, 'pref_name[TABVIEW]', 'class=text size=1', @$prefs['TABVIEW'], true );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Short Date Format');?>:</td>
	<td>
<?php
	// exmample date
	$ex = new CDate();

	$dates = array();
	$f = "%d/%m/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%d/%b/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%m/%d/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%b/%d/%Y"; $dates[$f]	= $ex->format( $f );
	$f = "%d.%m.%Y"; $dates[$f]	= $ex->format( $f );
        $f = "%Y/%b/%d"; $dates[$f]     = $ex->format( $f ); 
	echo arraySelect( $dates, 'pref_name[SHDATEFORMAT]', 'class=text size=1', @$prefs['SHDATEFORMAT'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Time Format');?>:</td>
	<td>
<?php
	// exmample date
	$times = array();
	$f = "%I:%M %p"; $times[$f]	= $ex->format( $f );
	$f = "%H:%M"; $times[$f]	= $ex->format( $f ).' (24)';
	$f = "%H:%M:%S"; $times[$f]	= $ex->format( $f ).' (24)';
	echo arraySelect( $times, 'pref_name[TIMEFORMAT]', 'class=text size=1', @$prefs['TIMEFORMAT'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('Currency Format');?>:</td>
	<td>
<?php
	$currencies = array();
	$currEx = 1234567.89;

    	// This is a server not using Windows
    	$f = "es_ES"; $currencies[$f]	= formatCurrency( $currEx, $f );
    	$f = "es_MX"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "en_US"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "en_GB"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "en_AU"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "en_CA"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "en_NZ"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "pt_PT"; $currencies[$f]	= formatCurrency( $currEx, $f );
	$f = "pt_BR"; $currencies[$f]	= formatCurrency( $currEx, $f );
	echo arraySelect( $currencies, 'pref_name[CURRENCYFORM]', 'class=text size=1', @$prefs['CURRENCYFORM'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('User Interface Style');?>:</td>
	<td>
<?php
        $uis = $prefs['UISTYLE'] ? $prefs['UISTYLE'] : 'default';
	$styles = $AppUI->readDirs( 'style' );
	$temp = $AppUI->setWarning( false );
	echo arraySelect( $styles, 'pref_name[UISTYLE]', 'class=text size=1', $uis, true );
	$AppUI->setWarning( $temp );
?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('User Task Assignment Maximum');?>:</td>
	<td>
<?php
        $tam = ($prefs['TASKASSIGNMAX'] > 0) ? $prefs['TASKASSIGNMAX'] : 100;
        $taskAssMax = array();
        for ($i = 5; $i <= 200; $i+=5) {
                $taskAssMax[$i] = $i.'%';
        }
	echo arraySelect( $taskAssMax, 'pref_name[TASKASSIGNMAX]', 'class=text size=1', $tam, false );

?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Default Event Filter');?>:</td>
	<td>
<?php
	require_once $AppUI->getModuleClass('calendar');
	echo arraySelect( $event_filter_list, 'pref_name[EVENTFILTER]', 'class=text size=1', @$prefs['EVENTFILTER'], true);
?>
	</td>
</tr>
<tr>
	<td align="right"><?php echo $AppUI->_('Notification Method');?>:</td>
	<td>
<?php
	$notify_filter = array( 
		0 => $AppUI->_('Do not include task/event owner'),
		1 => $AppUI->_('Include task/event owner')
	);
 
	echo arraySelect( $notify_filter, 'pref_name[MAILALL]', 'class=text size=1', @$prefs['MAILALL'], true);

?>
	</td>
</tr>

<tr>
	<td align="left"><input class="button"  type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" /></td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" /></td>
</tr>
</table>
