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
	$owner_filter_id = $AppUI->getState( 'owner_filter_id') ? $AppUI->getState( 'owner_filter_id' ) : $AppUI->user_id;
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

$canEdit = !getDenyEdit( $m );
// retrieve list of records
/*
 The following query is actually made at vw_companies.php
 It was excuted twice, so I commented out this block
 - jcgonz
 
$sql = "
SELECT company_id, company_name, company_type, company_description,
	count(distinct projects.project_id) as countp, count(distinct projects2.project_id) as inactive,
	user_first_name, user_last_name
FROM permissions, companies
LEFT JOIN projects ON companies.company_id = projects.project_company and projects.project_active <> 0
LEFT JOIN users ON companies.company_owner = users.user_id
LEFT JOIN projects AS projects2 ON companies.company_id = projects2.project_company AND projects2.project_active = 0
WHERE permission_user = $AppUI->user_id
	AND permission_value <> 0
	AND (
		(permission_grant_on = 'all')
		OR (permission_grant_on = 'companies' and permission_item = -1)
		OR (permission_grant_on = 'companies' and permission_item = company_id)
		)
" . (count($deny) > 0 ? 'and company_id not in (' . implode( ',', $deny ) . ')' : '')
 . "GROUP BY company_id
    ORDER BY $orderby";

$rows = db_loadList( $sql );
*/
$search_string = dPformSafe($search_string, true);

$sql = "select user_id, concat_ws(' ', user_first_name, user_last_name)
		from users as u left join permissions as p on u.user_id = p.permission_user
		where !isnull(p.permission_user)
		group by user_id
		order by user_first_name";
$owner_list = array( 0 => $AppUI->_("All")) + db_loadHashList($sql);
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

if ( $companiesTypeTab != -1 ) {
	$types[0] = "All Companies";
	$types[] = "Not Defined";
}

$tabBox = new CTabBox( "?m=companies", "{$AppUI->cfg['root_dir']}/modules/companies/", $companiesTypeTab );
foreach($types as $type_name){
	$tabBox->add('vw_companies', $type_name);
}

/*$tabBox->add( 'vw_companies', 'Clients' );
$tabBox->add( 'vw_companies', 'Suppliers' );
$tabBox->add( 'vw_companies', 'All Companies' );
*/

$tabBox->show();
?>
