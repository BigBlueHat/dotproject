<?php
require_once 'DP/Controller/Action.php';

class Companies_IndexController extends DP_Controller_Action
{
	public function indexAction()
	{
		$AppUI = DP_AppUI::getInstance();
		$AppUI->savePlace();

		$perms =& $AppUI->acl();
		$filters_selection = array('company_owner' => $perms->getPermittedUsers('companies'));

		// load the company types
		$types = DP_Config::getSysVal( 'CompanyType' );

		// get any records denied from viewing
		$obj =& $this->moduleClass();
		$deny = $obj->getDeniedRecords( $AppUI->user_id );

		// Company search by Kist
		$search_string = $this->getRequest()->getParam( 'search_string', "" );
		if($search_string != ""){
			$search_string = $search_string == "-1" ? "" : $search_string;
			$AppUI->setState("search_string", $search_string);
		} else {
			$search_string = $AppUI->getState("search_string");
		}

		// setup the title block
		$tpl = $this->getView();
		$m = $this->getRequest()->getModuleName();
		$a = $this->getRequest()->getActionName();
		$titleBlock = $this->titleBlock();
		$titleBlock->init( 'Companies', 'handshake.png', $m, "$m.$a" );
		$search_form = $tpl->fetch('search.html');
		$search_string = $titleBlock->addSearchCell();
		$filters = $titleBlock->addFiltersCell($filters_selection);

		if ($canEdit) {
			$titleBlock->addCell(
				'
			<form action="'. DP_BASE_URL . '/companies/addedit/" method="post">
			<input type="submit" class="button" value="'.$AppUI->_('new company').'" />
			</form>', '',	'', '');
		}
		$titleBlock->show();

		// Probably don't need this anymore, except to set the default module name.
		$tabBox = $this->tabBox();
		$tabBox->show();
	}

	public function listAction()
	{
		$AppUI = DP_AppUI::getInstance();
		$tpl = $this->getView();
		if (isset($this->getRequest()->orderby)) {
			$orderdir = $AppUI->getState( 'CompIdxOrderDir' ) ? ($AppUI->getState( 'CompIdxOrderDir' )== 'asc' ? 'desc' : 'asc' ) : 'desc';
			$AppUI->setState( 'CompIdxOrderBy', $this->getRequest()->orderby );
			$AppUI->setState( 'CompIdxOrderDir', $orderdir);
		}
		$orderby         = $AppUI->getState( 'CompIdxOrderBy' ) ? $AppUI->getState( 'CompIdxOrderBy' ) : 'company_name';
		$orderdir        = $AppUI->getState( 'CompIdxOrderDir' ) ? $AppUI->getState( 'CompIdxOrderDir' ) : 'asc';
		// load the company types
		$types = DP_Config::getSysVal( 'CompanyType' );
		$obj = $this->moduleClass();
		$allowedCompanies = $obj->getAllowedRecords($AppUI->user_id, 'company_id, company_name');
		$tabBox = $this->tabBox();
		$currentTabId = $tabBox->getActive();
		$company_type_filter = $tabBox->getActiveParam('list', 0);

		//Listing all types if ALL (-1) is selected
		$companiesType = ($currentTabId != 0);

		// natab keeps track of which tab stores companies with no type set.
		if ($currentTabId == $natab) {
			$company_type_filter = 0;
		}

		// retrieve list of records
		$q  = new DP_Query;
		$q->addTable('companies', 'c');
		$q->addQuery('c.company_id, c.company_name, c.company_type');
		$q->addQuery('c.company_description');
		$q->addQuery('count(distinct p.project_id) as company_projects_active');
		$q->addQuery('count(distinct p2.project_id) as company_projects_inactive');
		$q->addQuery('con.contact_first_name, con.contact_last_name');
		$q->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_status != 7');
		$q->addJoin('users', 'u', 'c.company_owner = u.user_id');
		$q->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$q->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_status = 7');
		if (count($allowedCompanies) > 0) { $q->addWhere('c.company_id IN (' . implode(',', array_keys($allowedCompanies)) . ')'); }
		if ($companiesType) { $q->addWhere('c.company_type = '.$company_type_filter); }
		if ($search_string != "") { $q->addWhere("c.company_name LIKE '%$search_string%'"); }
		foreach($filters as $field => $filter)
			if ($filter > 0)
				$q->addWhere("c.$field = $filter ");
		$q->addGroup('c.company_id');
		$q->addOrder($orderby.' '.$orderdir);
		$q->setPageLimit($tpl->page);
		$rows = $q->loadList();

		$q->addTable('companies', 'c');
		$q->addQuery('count(*)');
		if (count($allowedCompanies) > 0) { $q->addWhere('c.company_id IN (' . implode(',', array_keys($allowedCompanies)) . ')'); }
		if ($companiesType) { $q->addWhere('c.company_type = '.$company_type_filter); }
		if ($search_string != "") { $q->addWhere("c.company_name LIKE '%$search_string%'"); }
		foreach($filters as $field => $filter)
			if ($filter > 0)
				$q->addWhere("c.$field = $filter ");
		$count_rows = $q->loadResult();

		foreach($rows as $key => $value)
			$rows[$key]['company_type_name'] = $types[$rows[$key]['company_type']];

		$show = array('company_name', 'company_projects_active', 'company_projects_inactive', 'company_type');
		$tpl->displayList('companies', $rows, $count_rows, $show);
	}

}
?>
