<?php
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
class Companies_IndexController extends Zend_Controller_Action
{
	
	/**
     * FlashMessenger
     *
     * @var Zend_Controller_Action_Helper_FlashMessenger
     */
    protected $_flashMessenger = null;
	
	public function init()
	{
		$this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
	}
	
	
	/**
	 * Get an index of companies.
	 * 
	 * The rows are produced by companies_index, an instance of DP_List_Dynamic which
	 * observes changes in objects which modify its query.
	 */
	public function indexAction()
	{
		// TODO - temporary workaround for bypassing DP_Query
		$db = DP_Config::getDB();
		Zend_Db_Table_Abstract::setDefaultAdapter($db);

		
		// load the company types
		$types = DP_Config::getSysVal( 'CompanyType' );

		$this_url = $this->_helper->Url('index','index','companies');
		
		// Title block may not be needed, Module navigation already indicates we are in companies module.
		//$tb = $this->_helper->TitleBlock('Companies', '/img/_icons/companies/handshake.png');

		$companies_index = new Companies_Index();
		
		// Construct the view hierarchy from the inner to the outer elements.
		$company_search_view = DP_View_Factory::getSearchFilterView('dp-companies-list-searchfilter');
		$company_search_view->setSearchFieldTitle('Company name'); // TODO - better detection of available search fields through interface.
		$company_search_view->setSearchField('c.company_name');

		// Temporarily removed until implemented
		//$company_select_owner_view = DP_View_Factory::getSelectFilterView('dp-companies-list-selectowner', Array('Not Implemented'), 'Owner');
		
		$company_list_pager = DP_View_Factory::getPagerView('dp-companies-list-pager');
		$company_list_pager->setItemsPerPage(30);
		$company_list_pager->setPersistent(false);
		$company_list_pager->align = 'center';

		$list_view_id = 'dp-companies-list-view';
		$companies_list_view = DP_View_Factory::getListView($list_view_id);
		$companies_list_view->setUrlPrefix($this_url);
		
		// TODO - Find a better way of adding new object buttons to lists.
		$new_btn = new DP_View_Button('dp-companies-new','new-company');
		$new_btn->button->setLabel('+ New Company');
		$new_btn->button->onClick = "location = '/companies/edit/new'";
		
		// Create selection tools
		$select_tools = new DP_View_ObjectSelectTools('dp-companies-selection', $list_view_id, 'company_id');
		
		// Add buttons to horizontal box
		$btn_hbox = new DP_View_Hbox('dp-companies-hbox');
		$btn_hbox->add($new_btn);
		$btn_hbox->add($select_tools);
		
		$companies_list_view->add($btn_hbox, DP_View::APPEND);
		$companies_list_view->add($company_search_view, DP_View::PREPEND);
		$companies_list_view->add($company_list_pager, DP_View::APPEND);
		// Owner filter disabled until implemented.
		//$companies_list_view->add($company_select_owner_view, DP_View::PREPEND);

		
		// Access the default row iterator, you can set your own if preferred
		$companies_list_view->row_iterator->addRow(
								Array($select_tools->makeSelectCellView(),
									  new DP_View_Cell_ObjectLink('company_id','company_name', '/companies/view/object/id/','Company Name'),
									  new DP_View_Cell('company_projects_active', 'Projects Active', Array('width'=>'120px', 'align'=>'center')),
								      new DP_View_Cell('company_projects_inactive', 'Projects Archived', Array('width'=>'120px', 'align'=>'center')),
								      new DP_View_Cell_ArrayItem($types, 'company_type', 'Type', Array('align'=>'center','width'=>'120px')))
											);
											
		$companies_list_view->width = '100%';
 	
		$companies_tab_view = DP_View_Factory::getTabBoxView('companies_list_tabbox');	
		$companies_tab_view->setUrlPrefix($this_url);
		
		$companies_tab_view->add($companies_list_view, 'All Companies');
		// Use the same list view reference for every tab in GET/POST mode.
		foreach ($types as $company_type) {
			$companies_tab_view->add($companies_list_view, $company_type);
		}

		// DP_View_TabBox only calls render on its children.
		// The controller must make the logical link between the selected tab and
		// the filter rule.
		$companies_tab_filter = new DP_Filter('dp-companies-tab');

		// Attach the companies index dynamic list to all of the filtering/sorting elements		
		$companies_index->addModifier($companies_list_view->getSort());
		$companies_index->addModifier($company_search_view->getFilter());
		$companies_index->addModifier($company_list_pager->getPager());		
		
		// Update the view hierarchy with the request object.
		// (request object is passed down the hierarchy).
		$companies_tab_view->updateStateFromServer($this->getRequest());

		if ($select_tools->objectsChanged()) {
			$select_tools->updateObjects(new Db_Table_Companies());
		}
		
		// @todo Better way of determining Tab to Filter mapping.
		// Perhaps an array of tab indexes to filter rules.
		$types_keys = array_keys($types);
		// Do not include 'all companies' tab
		if ($companies_tab_view->selectedTab() > 0) {
			// Add company_type filter. Deduct 1 due to the All Companies tab
			$companies_tab_filter->fieldEquals('company_type', $types_keys[$companies_tab_view->selectedTab() - 1]);
		}
		$companies_index->addModifier($companies_tab_filter);	
		
		$companies_list_view->setDataSource($companies_index);

        // Show status message
		// TODO - flashmessenger expects Zend_Session as session handler
        //$this->view->messages = $this->_flashMessenger->getMessages();
		
		// TODO - Make root level container for all DP_Views
		$this->view->main = $companies_tab_view;
	}
	
	
	/**
	 * This method will be used to generate a json representation of the companies index. For use with the contact editor etc.
	 * 
	 * @todo Implement this
	 */
	public function jsonAction() {	
		$companies = new Companies_Index();
		
		// @todo item limit set temporarily - 5000 items exceeds memory usage limit
		$pager = new DP_Pager();
		$pager->setItemsPerPage(100);
		$pager->setPage(1);
		
		$companies->addModifier($pager);
		$companies->clientWillRender();
		
		$this->_helper->json(
			Array( 'identifier'=>'company_id', 'items'=>$companies->getArray() )
		);
	}
	
}
?>
