<?php
//require_once 'DP/Controller/Action.php';

require_once DP_BASE_CODE . '/modules/companies/models/Factory.php';
require_once DP_BASE_CODE . '/modules/companies/models/CompaniesList.php';
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
	/*
	public function indexAction()
	{*/
		
		/*
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
		*/
		// Probably don't need this anymore, except to set the default module name.
		//$tabBox = $this->tabBox();
		//$tabBox->show();
		//$this->appendRequest('/companies/list');
	//}

	public function indexAction()
	{
		$AppUI = DP_AppUI::getInstance();
		$AppUI->savePlace();
		$perms =& $AppUI->acl();
		
		// load the company types
		$types = DP_Config::getSysVal( 'CompanyType' );

		// Get the full URL without parameters.
		$req = $this->getRequest();
		$this_url = $req->getBaseUrl().'/'.$req->getModuleName().'/'.$req->getControllerName().'/'.$req->getActionName();
		// setup the title block
		$m = $req->getModuleName();
		$a = $req->getActionName();
		
		//$tb = $this->_helper->TitleBlock('Companies', '/img/_icons/companies/handshake.png');
		//$tb = $this->_helper->TitleBlock('');
		//$tb->addCrumb('','companies');

		// Construct the view hierarchy
		$company_search_view = DP_View_Factory::getSearchFilterView('dp-companies-list-searchfilter');
		$company_search_view->setSearchFieldTitle('Company name');
		// Temporarily removed until implemented
		//$company_select_owner_view = DP_View_Factory::getSelectFilterView('dp-companies-list-selectowner', Array('Not Implemented'), 'Owner');
		
		$company_list_pager = DP_View_Factory::getPagerView('dp-companies-list-pager');
		$company_list_pager->setItemsPerPage(30);
		$company_list_pager->setUrlPrefix($this_url);
		$company_list_pager->setPersistent(false);
		$company_list_pager->align = 'center';
		
		$companies_list_view = DP_View_Factory::getListView('companies_list_view');
		$companies_list_view->setUrlPrefix($this_url);
		
		$new_btn = new DP_View_Button('dp-companies-new','new-company');
		$new_btn->button->setLabel('+ New Company');
		$new_btn->button->onClick = "location = '/companies/edit/new'";
		$companies_list_view->add($new_btn, DP_View::APPEND);
		
		$companies_list_view->add($company_search_view, DP_View::PREPEND);
		//$companies_list_view->add($company_select_owner_view, DP_View::PREPEND);
		$companies_list_view->add($company_list_pager, DP_View::APPEND);
		
		// Access the default row iterator, you can set your own if preferred
		$companies_list_view->row_iterator->addRow(
								Array(new DP_View_Cell_ObjectLink('company_id','company_name', '/companies/view/object/id/'),
									  new DP_View_Cell('company_projects_active', Array('width'=>'120px', 'align'=>'center')),
								      new DP_View_Cell('company_projects_inactive', Array('width'=>'120px', 'align'=>'center')),
								      new DP_View_Cell_ArrayItem($types, 'company_type', Array('align'=>'center','width'=>'120px')))
											);
											
		$companies_list_view->width = '100%';
 	
		$companies_tab_view = DP_View_Factory::getTabBoxView('companies_list_tabbox');	
		$companies_tab_view->setUrlPrefix($this_url);
		
		$companies_tab_view->add($companies_list_view, 'All Companies');
		// Use the same list view reference for every tab in GET/POST mode.
		foreach ($types as $type_index => $company_type) {
			$companies_tab_view->add($companies_list_view, $company_type);
		}

		// Update the view hierarchy with the request object.
		// (request object is passed down the hierarchy).
		// This must be done before the model generates its data. So that
		// dp views can act upon the conditions in which the data is generated.
		$companies_tab_view->updateStateFromServer($this->getRequest());

		// Create the data source for the list. The data source here is an object that
		// encompasses the query and filter objects needed to generate the list.
		// @todo Create a reusable model class for object indexes
		$companies_list_data = new Companies_List_Data();
		$companies_list_data->addFilter($company_search_view->getFilter());

		// DP_View_TabBox only calls render on its children.
		// The controller must make the logical link between the selected tab and
		// the filter rule.
		$companies_tab_filter = new DP_Filter('dp-companies-tab');
		
		// @todo Better way of determining Tab to Filter mapping.
		// Perhaps an array of tab indexes to filter rules.
		$types_keys = array_keys($types);
		// Do not include 'all companies' tab
		if ($companies_tab_view->selectedTab() > 0) {
			// Add company_type filter. Deduct 1 due to the All Companies tab
			$companies_tab_filter->fieldEquals('company_type', $types_keys[$companies_tab_view->selectedTab() - 1]);
			$companies_list_data->addFilter($companies_tab_filter);
		}
		
		$companies_list_data->addSort($companies_list_view->getSort());
		$companies_list_data->setPage($company_list_pager->page(), $company_list_pager->itemsPerPage());
		
		$companies_list_data->loadList();
		$company_list_pager->setTotalItems(count($companies_list_data));	
		
		// Must be done after the data is generated
		$companies_list_view->setDataSource($companies_list_data);
		$companies_list_view->setColumnHeaders(Array('c.company_name'=>'Company Name', 
													 'company_projects_active'=>'Active Projects', 
													 'company_projects_inactive'=>'Inactive Projects',
													 'company_type'=>'Company Type'));
		
		// TODO - Make root level container for all DP_Views
		$this->view->main = $companies_tab_view;
	}
}
?>
