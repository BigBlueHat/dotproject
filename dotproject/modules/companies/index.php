<?php /* COMPANIES $Id$ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
}

if(isset($_REQUEST["owner_filter_id"])){
	$AppUI->setState("owner_filter_id", $_REQUEST["owner_filter_id"]);
	$owner_filter_id = $_REQUEST["owner_filter_id"];
} else {
	$owner_filter_id = $AppUI->getState( 'owner_filter_id');
	if (! isset($owner_filter_id)) {
		$owner_filter_id = $AppUI->user_id;
		$AppUI->setState('owner_filter_id', $owner_filter_id);
	}
}

$orderby         = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';

// load the company types
$types = dPgetSysVal( 'CompanyType' );

// get any records denied from viewing
$obj = new CCompany();
$deny = $obj->getDeniedRecords( $AppUI->user_id );

// Company search by Kist
$search_string = dPgetParam( $_REQUEST, 'search_string', "" );
if($search_string != ""){
	$search_string = $search_string == "-1" ? "" : $search_string;
	$AppUI->setState("search_string", $search_string);
} else {
	$search_string = $AppUI->getState("search_string");
}

// $canEdit = !getDenyEdit( $m );
// retrieve list of records
$search_string = dPformSafe($search_string, true);

$perms =& $AppUI->acl();
$owner_list = array( 0 => $AppUI->_("All")) + $perms->getPermittedUsers("companies"); // db_loadHashList($sql);
$owner_combo = arraySelect($owner_list, "owner_filter_id", "class='text' onchange='javascript:document.searchform.submit()'", $owner_filter_id, false);

// setup the title block
$titleBlock = new CTitleBlock( 'Companies', 'handshake.png', $m, "$m.$a" );
$titleBlock->addCell("<form name='searchform' action='?m=companies&amp;search_string=$search_string' method='post'>
						<table>
							<tr>
                      			<td>
                                    <strong>".$AppUI->_('Search')."</strong>
                                    <input class='text' type='text' name='search_string' value='$search_string' /><br />
						<a href='index.php?m=companies&search_string=-1'>".$AppUI->_("Reset search")."</a></td>
								<td valign='top'>
									<strong>".$AppUI->_("Owner filter")."</strong> $owner_combo
								</td>
							</tr>
						</table>
                      </form>");
if ($canEdit) {
	$titleBlock->addCell(
		'<input type="submit" class="button" value="'.$AppUI->_('new company').'">', '',
		'<form action="?m=companies&a=addedit" method="post">', '</form>'
	);
}
$titleBlock->show();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompaniesIdxTab', $_GET['tab'] );
}
$companiesTypeTab = defVal( $AppUI->getState( 'CompaniesIdxTab' ),  0 );

/*function getCompanyTypeID($type) {
	global $types;
	$arr = array_keys($types, $type);
	return $arr[0];
}

$tabTypes = array();
foreach($types as $type_id => $type){
	$tabTypes[] = $type_id;
}
*/
// $tabTypes = array(getCompanyTypeID('Client'), getCompanyTypeID('Supplier'), 0);
$companiesType = $companiesTypeTab;

/*
if ( $companiesTypeTab != -1 ) {
	$types[0] = "All Companies";
	$types[] = "Not Applicable";
}
*/

$tabBox = new CTabBox( "?m=companies", dPgetConfig('root_dir')."/modules/companies/", $companiesTypeTab );
if ($tabbed = $tabBox->isTabbed()) {
	if (isset($types[0]))
		$types[] = $types[0];
	$types[0] = "All Companies";
	$types[] = "Not Applicable";
}
$type_filter = array();
foreach($types as $type => $type_name){
	$type_filter[] = $type;
	$tabBox->add('vw_companies', $type_name);
}

/*$tabBox->add( 'vw_companies', 'Clients' );
$tabBox->add( 'vw_companies', 'Suppliers' );
$tabBox->add( 'vw_companies', 'All Companies' );
*/

$tabBox->show();
?>
