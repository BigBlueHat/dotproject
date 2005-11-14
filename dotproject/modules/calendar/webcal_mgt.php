<?php /* EVENTS $Id$ */
GLOBAL $AppUI, $company_id, $project_id;
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

$s = '<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">';
$s .= '<tr>';


	$s .= '<th>&nbsp;</th>';	

	$s .= '<th nowrap="nowrap">'.$AppUI->_( 'WebDAV Path' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'User' ).'&'.$AppUI->_( 'Pass' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'Calendars' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'Import' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'Publish' ).'</th>';
	$s .= '<th>'.$AppUI->_( 'Subscribe' ).'</th>';


$s .= '</tr>';


foreach ($wres as $row) {
	$cals = array();
	foreach ($wp as $w) {
		if ($w['webcal_id'] == $row['webcal_id']) {
			if ($w['project_id'] == 0) {
				$cals[] = '<a href="?m=calendar&calendar_filter=0">'.$AppUI->_('Unspecified').'</a>';
			} elseif ($w['project_id'] == -1 ) {
				$cals[] = '<a href="?m=calendar&calendar_filter=-1">'.$AppUI->_('Personal').'</a>';
			} elseif ($w['project_id'] >0 ) {
				$proj->load($w['project_id']);
				$cals[] = '<a href="?m=calendar&calendar_filter='.$proj->project_id.'">'.$proj->project_short_name.'</a>';
			}
		}
	}
	natcasesort($cals);
$s .= '<td nowrap="nowrap">';
	$s .= '<a href="javascript:subm(\'import\','.$row["webcal_id"].')" title="'.$AppUI->_( 'import' ).'">';
	$s .= dPshowImage( './images/icons/down.png', '16', '16' );
	$s .= '</a>';
	$s .= '<a href="javascript:subm(\'publish\','.$row["webcal_id"].')" title="'.$AppUI->_( 'publish' ).'">';
	$s .= dPshowImage( './images/icons/up.png', '16', '16' );
	$s .= '</a>';
	$s .= '<a href="?m=calendar&a=calmgt&webcal_id='.$row["webcal_id"].'" title="'.$AppUI->_( 'Edit' ).' '.$AppUI->_( 'Resource' ).'">';
	$s .= dPshowImage( './images/icons/stock_edit-16.png', '16', '16' );
	$s .= '</a><a href="javascript:subm(\'del\','.$row["webcal_id"].')" title="'.$AppUI->_( 'delete' ).'">';
	$s .= dPshowImage( './images/icons/stock_delete-16.png', '16', '16' );
	$s .= '</a>';
	$s .= '<td nowrap="nowrap" ><a href="http://'.$row['webcal_path'].'">'.$row['webcal_path'].'</a></td>';
	$s .= '<td nowrap="nowrap">'.$row['webcal_user'].'</td>';
	$s .= '<td nowrap="nowrap">'.implode(", ", $cals).'</td>';
	$imp = ($row['webcal_auto_import'] > 0) ?  '<span style="color:green">'.$AppUI->_('Auto').' ('.$row['webcal_auto_import'].') '.'</span>'  : '<strike style="color:darkgrey">'.$AppUI->_('Auto').'</strike>';
	$imp .= ', ';
	$imp .= ($row['webcal_purge_events']) ?  '<span style="color:green">'.$AppUI->_('Purge').'</span>' : '<strike style="color:darkgrey">'.$AppUI->_('Purge').'</strike>';
	$imp .= ', ';
	$imp .= ($row['webcal_preserve_id']) ?  '<span style="color:green">'.$AppUI->_('Pres.').'</span>' : '<strike style="color:darkgrey">'.$AppUI->_('Pres.').'</strike>';
	$s .= '<td nowrap="nowrap">'.$imp.'</td>';
	$pub = ($row['webcal_auto_publish']) ?  '<span style="color:green">'.$AppUI->_('Auto').'</span>' : '<strike style="color:darkgrey">'.$AppUI->_('Auto').'</strike>';
	$pub .= ', ';
	$pub .= ($row['webcal_private_events']) ?  '<span style="color:green">'.$AppUI->_('Priv.').'</span>' : '<strike style="color:darkgrey">'.$AppUI->_('Priv.').'</strike>';
	$show = ($row['webcal_auto_show']) ?  '<span style="color:green">'.$AppUI->_('Show').'</span>' : '<strike style="color:darkgrey">'.$AppUI->_('Show.').'</strike>';
	$s .= '<td nowrap="nowrap">'.$pub.'</td>';
	$s .= '<td nowrap="nowrap">'.$show.'</td>';
	$s .= '</td></tr>';
}
echo $s;

if ($canEdit) {
	echo '<form name="webcalFrm" action="?m=calendar&a=calmgt" method="post">';
	echo '<input type="hidden" name="dosql" value="do_webcal_process" />';
	echo '<input type="hidden" name="webcal_id" value="'.$webcal_id.'" />';
	echo '<input type="hidden" name="webcal_res_type" value="1" />';	
	echo '<input type="hidden" name="del" value="0" />';
	echo '<input type="hidden" name="proc_method" value="" />';	
	echo '<tr>';
echo '<td nowrap="nowrap" align="center"><input type="button" class="button" onclick="javascript:subm(\'store\', -1)" value="'.$AppUI->_('submit').'"/><br/><a href="javascript:clear()">'.$AppUI->_('reset').'</a></td>';
	echo '<td nowrap="nowrap">';
		echo 'http://<input type="text" class="button" name="webcal_path" style="width:280px" value="'.$wdo->webcal_path.'"><br />Port: <input type="text" class="button" name="webcal_port" style="width:30px" value="'.$wdo->webcal_port.'">';
	echo '</td>';	
	echo '<td>';
	echo  $AppUI->_( 'User' ).':<input type="text" class="button" name="webcal_user" style="width:140px" value="'.$wdo->webcal_user.'"><br/>'.$AppUI->_( 'Pass' ).':<input type="password" class="button" name="webcal_pass" style="width:140px" value="'.$wdo->webcal_pass.'">';
	echo '</td>';	
	$opt = 'size="'.min(10, sizeof($calendar)).'" class="text" multiple="multiple"';
	echo '<td align="center">'.arraySelect( $calendar, 'calendars[]', $opt, $cal, false ).'</td>';
$purge_ex = ($wdo->webcal_purge_events == 1) ? 'checked="checked"' : '';
	$pres_id = ($wdo->webcal_preserve_id == 1) ? 'checked="checked"' : '';
echo '<td style="background-color:#ffdddd">'.$AppUI->_('Auto-Imp. every').'&nbsp;<nobr><input type="text" class="button" name="webcal_auto_import" style="width:20px" value="'.$wdo->webcal_auto_import.'">'.$AppUI->_('min').'</nobr><br/>'.$AppUI->_( 'Purge ex. Events' ).'?<input type="checkbox" name="webcal_purge_events" class="text" '.$purge_ex.'/><br/>'.$AppUI->_('Preserve Id').'?<input type="checkbox"  name="webcal_preserve_id" class="text" '.$pres_id.'/></td>';
	$autopub = ($wdo->webcal_auto_publish == 1) ? 'checked="checked"' : '';
	$priv_ev = ($wdo->webcal_private_events != 0) ? 'checked="checked"' : '';
echo '<td  style="background-color:#ffdddd">'.$AppUI->_( 'Auto' ).'?<input type="checkbox" name="webcal_auto_publish" class="text" '.$auto_pub.'/><br/>'.$AppUI->_('Private Events').'?<input type="checkbox" name="webcal_private_events" class="text" '.$priv_ev.'/></td>';
	//echo '</td>';
	$autoshow = ($wdo->webcal_auto_show == 1) ? 'checked="checked"' : '';
echo '<td  style="background-color:#ffdddd">'.$AppUI->_( 'Show' ).'?<input type="checkbox" name="webcal_auto_show" class="text" '.$auto_pub.'/></td>';
	//echo '</td>';
	echo '</tr>';
	echo '</form>';

}


echo '</table>';

?>
<script language="javascript">
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
</script>
