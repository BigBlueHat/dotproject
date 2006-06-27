<?php /* EVENTS $Id$ */
GLOBAL $AppUI, $company_id, $project_id, $tpl;
$canEdit = !getDenyEdit( 'calendar' );
if (!$canEdit) {
	$AppUI->redirect( "m=public&a=access_denied" );
}

require_once( $AppUI->getModuleClass( 'companies' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );

$wdo = new CWebCalresource();
$webcal_id = intval( dPgetParam( $_GET, "webcal_id", 0 ) );
if ($webcal_id > 0) {
	$wdo->load($webcal_id);
}
$comp = new CCompany();
$proj = new CProject();

$q  = new DBQuery;
$q->addTable('webcal_resources', 'w');
$q->addQuery('w.*');
$wres = $q->loadList();
$q->clear();

$r  = new DBQuery;
$r->addTable('projects');
$r->addQuery('project_id, CONCAT(c.company_name,"::", project_short_name) AS project_short_name');
$r->addJoin('companies', 'c', 'project_company = c.company_id'); 
if ($company_id > 0){
	$r->addWhere('project_company='.$company_id);
}
if ($project_id > 0){
	$r->addWhere('project_id='.$project_id);
}
$proj->setAllowedSQL($AppUI->user_id, $r);
$projects = $r->loadHashList();
$r->clear();
$calendar = arrayMerge( array( '-1'=>$AppUI->_('Personal Calendar'), '0'=>$AppUI->_('Unspecified Calendar') ) , $projects );

$r  = new DBQuery;
$r->addTable('companies');
$r->addQuery('company_id, company_name');
$comp->setAllowedSQL($AppUI->user_id, $r);
$companies = $r->loadHashList();
$r->clear();
$companies = arrayMerge( array( '0'=>$AppUI->_('All') ), $companies );

$r  = new DBQuery;
$r->addTable('webcal_projects');
$wp = $r->loadList();
$r->clear();
//var_export($wp);

$owners = array( '0'=>$AppUI->_('All'), "$AppUI->user_id" =>dPgetUsernameFromID($AppUI->user_id) );

$c = array();
$cals = array();
foreach ($wres as $row) {

	foreach ($wp as $w) {
		if ($w['webcal_id'] == $row['webcal_id']) {
			if ($w['project_id'] == 0) {
				$c[$row['webcal_id']][] = '<a href="?m=calendar&amp;calendar_filter=0">'.$AppUI->_('Unspecified').'</a>';
			} elseif ($w['project_id'] == -1 ) {
				$c[$row['webcal_id']][] = '<a href="?m=calendar&amp;calendar_filter=-1">'.$AppUI->_('Personal').'</a>';
			} elseif ($w['project_id'] >0 ) {
				$proj->load($w['project_id']);
				$c[$row['webcal_id']][] = '<a href="?m=calendar&amp;calendar_filter='.$proj->project_id.'">'.$proj->project_short_name.'</a>';
			}
		}
	}
	natcasesort($c[$row['webcal_id']]);
	$cals[$row['webcal_id']] = implode(", ", $c[$row['webcal_id']]);
}

$tpl->assign('cal', $cal);
$tpl->assign('cals', $cals);
$tpl->assign('calendar', $calendar);
$tpl->assign('cal_size', min(10, sizeof($calendar)) );
$tpl->assign('canEdit', $canEdit);
$tpl->assign('row', $row);
$tpl->assign('webcal_id', $webcal_id);
$tpl->assign('wp', $wp);
$tpl->assign('wdo', $wdo);
$tpl->assign('wres', $wres);
$tpl->displayFile('webcal_mgt', 'calendar');
?>
<script type="text/javascript" language="javascript">
<!--
function clear(){
	document.webcalFrm.webcal_id.value = 0;
	document.webcalFrm.webcal_path.value = '';
	document.webcalFrm.webcal_port.value = '80'; 
	document.webcalFrm.webcal_user.value = '<?php echo $AppUI->user_username;?>'; 
	document.webcalFrm.webcal_pass.value = ''; 
	document.webcalFrm.webcal_auto_import.value = ''; 
	document.webcalFrm.webcal_auto_publish.checked = false;
	document.webcalFrm.webcal_auto_show.checked = false;
	document.webcalFrm.webcal_preserve_id.checked = false; 
	document.webcalFrm.webcal_auto_private_events.checked = false;  
	document.webcalFrm.webcal_auto_purge_events.checked = true;  
}

// security improvement:
// some javascript functions may not appear on client side in case of user not having write permissions
// otherwise users would be able to arbitrarily run 'bad' functions
<?php

if ($canEdit) {
?>
function subm(p,i) {			
	document.webcalFrm.proc_method.value = p;	
	if (i > -1) {
		document.webcalFrm.webcal_id.value = i;
	}
	if (p != 'store') { 
		// prevent from browser side password manager auto-store alerts
		document.webcalFrm.webcal_pass.type = 'text';
	}
	if (p=='del') {
		if ( confirm( "<?php echo $AppUI->_('webcalResDelete');?>" )) {	

			document.webcalFrm.submit();
		} 
	} else {
		document.webcalFrm.submit();
	}
}
<?php } ?>
-->
</script>
