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
$sql = "
SELECT user_first_name, user_last_name
FROM users
WHERE user_id = $user_id
";
$res  = db_exec( $sql );
echo db_error();
$user = db_fetch_row( $res );

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
		echo $user_id ? "$user[0] $user[1]" : $AppUI->_("Default");
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
	$currEx = 123456789;

	if (! function_exists('money_format')) {
    	// This is a server using Windows, no php money_format capability
    	$f = "us_US"; $currencies[$f]	= "$ " . $currEx;
	} else {
    	// This is a server not using Windows
    	$f = "es_ES"; $currencies[$f]	= formatCurrency( $currEx, $f );
		$f = "us_US"; $currencies[$f]	= formatCurrency( $currEx, $f );
	}
	echo arraySelect( $currencies, 'pref_name[CURRENCYFORMAT]', 'class=text size=1', @$prefs['CURRENCYFORMAT'], false );
?>
	</td>
</tr>

<tr>
	<td align="right"><?php echo $AppUI->_('User Interface Style');?>:</td>
	<td>
<?php
	$styles = $AppUI->readDirs( 'style' );
	$temp = $AppUI->setWarning( false );
	echo arraySelect( $styles, 'pref_name[UISTYLE]', 'class=text size=1', @$prefs['UISTYLE'], true );
	$AppUI->setWarning( $temp );
?>
	</td>
</tr>

<tr>
	<td align="left"><input class="button"  type="button" value="<?php echo $AppUI->_('back');?>" onClick="javascript:history.back(-1);" /></td>
	<td align="right"><input class="button" type="button" value="<?php echo $AppUI->_('submit');?>" onClick="submitIt()" /></td>
</tr>
</table>
