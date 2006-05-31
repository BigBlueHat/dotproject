<?php /* SYSTEM $Id$ */
##
## add or edit a user preferences
##
$user_id = isset($HTTP_GET_VARS['user_id']) ? $HTTP_GET_VARS['user_id'] : 0;
// Check permissions
if (!$canEdit && $user_id != $AppUI->user_id)
	$AppUI->redirect('m=public&amp;a=access_denied');

// load the preferences
$q  = new DBQuery;
$q->addTable('user_preferences','up');
$q->addQuery('pref_name, pref_value, pref_group, pref_type');
$q->addOrder('pref_group');
$q->addWhere('up.pref_user ='.$user_id);
$prefs = $q->loadHashList('pref_name');
$q->clear();
// fallback to default prefs if no prefs are available yet
if (empty($prefs)) {
	$q  = new DBQuery;
	$q->addTable('user_preferences','up');
	$q->addQuery('pref_name, pref_value, pref_group, pref_type');
	$q->addWhere('up.pref_user = 0');
	$q->addOrder('pref_group');
	$prefs = $q->loadHashList('pref_name');
	$q->clear();
}

// get the user name
if ($user_id)
	$user = dPgetUsernameFromID($user_id);
else
	$user = 'Default';

$titleBlock = new CTitleBlock( 'Edit User Preferences', 'myevo-weather.png', $m, "$m.$a" );
$perms =& $AppUI->acl();
if ($perms->checkModule('system', 'edit')) {
	$titleBlock->addCrumb('?m=system', 'system admin');
	$titleBlock->addCrumb('?m=system&amp;a=systemconfig', 'system configuration');
}
$titleBlock->show();

// collect language options
$LANGUAGES = $AppUI->loadLanguages();
$temp = $AppUI->setWarning( false );
$langlist = array();
foreach ($LANGUAGES as $lang => $langinfo)
	$langlist[$lang] = $langinfo[1];
$AppUI->setWarning( $temp );

// collect dateformat options
$ex = new CDate();
$dates = array();
$f = "%d/%m/%Y"; $dates[$f]	= $ex->format( $f );
$f = "%d/%b/%Y"; $dates[$f]	= $ex->format( $f );
$f = "%m/%d/%Y"; $dates[$f]	= $ex->format( $f );
$f = "%b/%d/%Y"; $dates[$f]	= $ex->format( $f );
$f = "%d.%m.%Y"; $dates[$f]	= $ex->format( $f );
$f = "%Y/%b/%d"; $dates[$f] = $ex->format( $f ); 

// collect timeformat options
$times = array();
$f = "%I:%M %p"; $times[$f]	= $ex->format( $f );
$f = "%H:%M"; $times[$f]	= $ex->format( $f ).' (24)';
$f = "%H:%M:%S"; $times[$f]	= $ex->format( $f ).' (24)';
$f = ' '; $times[$f] = '&nbsp;';

// collect currencyformat options
$currencies = array();
$currEx = 1234567.89;
if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN')
	$is_win = true;
else
	$is_win = false;
foreach (array_keys($LANGUAGES) as $lang) {
	$currencies[$lang] = formatCurrency($currEx, $AppUI->setUserLocale($lang, false));
}

// collect UI template options
$uis = $prefs['UISTYLE'] ? $prefs['UISTYLE'] : 'default';
$styles = $AppUI->readDirs( 'style' );

// collect icon collection options
$icons = $prefs['ICONSTYLE'] ? $prefs['ICONSTYLE'] : '';
$icon_styles = $AppUI->readDirs( 'style/_iconsets', 'default');

// collect calendar options
// include calendar class providing $event_filter_list
require_once $AppUI->getModuleClass('calendar');

// collect task log email options
$tl_assign = $prefs['TASKLOGEMAIL'] & 1;
$tl_task = $prefs['TASKLOGEMAIL'] & 2;
$tl_proj = $prefs['TASKLOGEMAIL'] & 4;

// collect tabview options
$tabview = array( 'either', 'tabbed', 'flat' );

$last_group = '';

$tpl->assign('AppUI', $AppUI);
$tpl->assign('baseDir', $baseDir);
$tpl->assign('currencies', $currencies);
$tpl->assign('dates', $dates);
$tpl->assign('efl', $event_filter_list);
$tpl->assign('is', $icon_styles);
$tpl->assign('langlist', $langlist);
$tpl->assign('last_group', $last_group);
$tpl->assign('prefs', $prefs);
$tpl->assign('styles', $styles);
$tpl->assign('tabview', $tabview);
$tpl->assign('tl_assign', $tl_assign);
$tpl->assign('tl_task', $tl_task);
$tpl->assign('tl_proj', $tl_proj);
$tpl->assign('tle', $prefs['TASKLOGEMAIL']);
$tpl->assign('times', $times);
$tpl->assign('user', $user);
$tpl->assign('user_id', $user_id);
$tpl->displayFile('addeditpref', 'system');
?>
