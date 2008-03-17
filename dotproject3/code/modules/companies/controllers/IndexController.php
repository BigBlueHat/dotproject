<?php
require_once 'DP/Controller/Action.php';

/**
 * This is the file containing the definition of the index controller for the companies module
 * @author ajdonnison
 * @version 3.0
 * @package dotproject
 * @subpackage companies
 */

/**
 * Defines methods that handle the display of company lists
 * 
 * 
 * @package dotproject
 * @subpackage companies
 */
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

		// load the company types
		$types = DP_Config::getSysVal( 'CompanyType' );
		$obj = $this->moduleClass();
				
		$allowedCompanies = $obj->getAllowedRecords($AppUI->user_id, 'company_id, company_name');

		//$tabBox = $this->tabBox();
		//$currentTabId = $tabBox->getActive();
		//$company_type_filter = $tabBox->getActiveParam('list', 0);

		//Listing all types if ALL (-1) is selected
		$companiesType = ($currentTabId != 0);

		// natab keeps track of which tab stores companies with no type set.
		if ($currentTabId == $natab) {
			$company_type_filter = 0;
		}
		
		// Create list sort
		$companies_list_sort = $AppUI->getState('companies_list_sort', new DP_Query_Sort());

		// Create list filter
		$companies_list_filter = $AppUI->getState('companies_list_searchfilter_filter', new DP_Filter());
		// Create the SearchFilter view
		$company_search_view = DP_View_Creator::getSearchFilterView('companies_list_searchfilter', $companies_list_filter);
		
		// Create the "select owner" view - not yet implemented
		$company_select_owner_view = DP_View_Creator::getSelectFilterView('companies_list_ownerfilter', Array('Not Implemented'), 'Owner');

		// Create main list view.
		$companies_list_view = DP_View_Creator::getListView('companies_list_view', $companies_list_sort);		
		
		// Insert search tools into the companies list view
		$companies_list_view->add($company_search_view);
		$companies_list_view->add($company_select_owner_view);
		
		// Update list view and all children with request object.
		$companies_list_view->updateStateFromServer($this->getRequest());
		
		// Define column formatting
		// TODO - custom cell class - class which takes a row and then spits out the output for the current <td> element
		// The cell class would avoid specific methods for each type of column, the tradeoff is abstraction vs speed
		// nonetheless the list view class needs a way to display custom content
		$companies_list_view->addObjectLinkColumn('company_id', 'company_name', '/companies/view/id/','Company Name');
		$companies_list_view->addTextColumn('company_projects_active','Active Projects');
		$companies_list_view->addTextColumn('company_projects_inactive','Inactive Projects');
		
		// Create query to generate list data
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
		$q->addGroup('c.company_id');	
		
		if ($companiesType) { 
			$companies_list_filter->fieldEquals('c.company_type', $company_type_filter); 
		}

		// Save the state of the objects used to filter the query
		// TODO - DP_View objects should save the underlying state any time they are modified.
		$AppUI->setState('companies_list_sort',$companies_list_sort);
		$AppUI->setState('companies_list_searchfilter_filter', $companies_list_filter);
		
		// Get list object (which adheres to DP_View_List_Source)		
		$companies_list_obj = DP_Object_Creator::getListFromFilteredQuery($q, Array($companies_list_filter, $companies_list_sort, $tpl->page));
		$companies_list_view->setDataSource($companies_list_obj);

		// TODO - When the tab view calls render() on the selected child, the child should generate the data.
		$companies_tab_view = DP_View_Creator::getTabBoxView('companies_list_tabbox');
		$companies_tab_view->add($companies_list_view,'All Companies');
		$companies_tab_view->add($companies_list_view,'Complete Companies');
		$companies_tab_view->add($companies_list_view,'Not Filtered Companies');
		
		$companies_tab_view->updateStateFromServer($this->getRequest());
		// Add DP_View_List to view
		$tpl->add($companies_tab_view);
		$tpl->displayObjects();
	}
}
?>
