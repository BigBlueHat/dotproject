<?php /* COMPANIES $Id$ */
$AppUI->savePlace();

// retrieve any state parameters
if (isset( $_GET['orderby'] )) {
    $orderdir = $AppUI->getState( 'CompIdxOrderDir' ) ? ($AppUI->getState( 'CompIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';
	$AppUI->setState( 'CompIdxOrderBy', $_GET['orderby'] );
    $AppUI->setState( 'CompIdxOrderDir', $orderdir);
}
$orderby         = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';
$orderdir        = $AppUI->getState( 'CompIdxOrderDir' ) ? $AppUI->getState( 'CompIdxOrderDir' ) : 'asc';

$perms =& $AppUI->acl();
$filters_selection = array('company_owner' => $perms->getPermittedUsers('companies'));

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


// setup the title block
$titleBlock = new CTitleBlock( 'Companies', 'handshake.png', $m, "$m.$a" );
$search_form = $tpl->fetchFile('search', '.');
$search_string = $titleBlock->addSearchCell();
$filters = $titleBlock->addFiltersCell($filters_selection);

if ($canEdit) {
	$titleBlock->addCell(
		'
<form action="?m=companies&amp;a=addedit" method="post">
	<input type="submit" class="button" value="'.$AppUI->_('new company').'" />
</form>', '',	'', '');
}
$titleBlock->show();

if (isset( $_GET['tab'] )) {
	$AppUI->setState( 'CompaniesIdxTab', $_GET['tab'] );
}
$companiesTypeTab = defVal( $AppUI->getState( 'CompaniesIdxTab' ),  -1 );

// $tabTypes = array(getCompanyTypeID('Client'), getCompanyTypeID('Supplier'), 0);
$companiesType = $companiesTypeTab;

$tabBox = new CTabBox( "?m=companies", dPgetConfig('root_dir')."/modules/companies/", $companiesTypeTab );
if ($tabbed = $tabBox->isTabbed()) {
	if (isset($types[0])) 
		$types[] = $types[0];
	else // They have a Not Applicable entry.
		$types[] = "Not Applicable";

	$types[0] = "All Companies";
	// natab keeps track of which tab stores companies with no type set.
	$natab = count($types) - 1;
}
$type_filter = array();
foreach($types as $type => $type_name){
	$type_filter[] = $type;
	$tabBox->add('vw_companies', $type_name);
}

$tabBox->show();
?>
