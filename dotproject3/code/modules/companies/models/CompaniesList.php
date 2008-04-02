<?php

/**
 * The companies list object reflects the current state of the companies list.
 *
 * This object acts as a mediator between the companies view objects and
 * the companies model objects involved in generating the companies list.
 * 
 * @package dotproject
 * @subpackage companies
 * @version not.even.alpha
 * 
 */
class Companies_List_Data implements DP_Observable_Interface, DP_View_List_Source_Interface {
	private $query;
	private $filters;
	private $sort;
	private $observers;
	
	private $object_list;
	private $column_names;
	
	public function __construct() {
		
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
		$this->query = $q;
		
		$this->observers = Array();
		
		$this->column_names = Array('Company', 'Projects Active', 'Projects Inactive');
	}
	
	public function getFilter() {
		return $this->filters;
	}
	
	public function addFilter(DP_Filter $filter) {
		$this->filters[$filter->id()] = $filter;
	}

	public function addSort(DP_Query_Sort $sort) {
		$this->sort = $sort;
	}
	
	public function loadList() {
		foreach($this->filters as $filter) {
			foreach($filter->filters as $rule) {
				switch($rule['filter_type']) {
					case DP_Filter::VALUE_EQUAL:
						$this->query->addWhere($rule['filter_field']." = ".$rule['field_value']);
						break;
					case DP_Filter::VALUE_SUBSTR:
						$this->query->addWhere($rule['filter_field']." LIKE '%".$rule['field_value']."%'");
						break;
				}
			}
		}
		
		foreach($this->sort->sorting_rules as $field => $sort_rule) {
			switch($sort_rule) {
				case DP_Query_Sort::SORT_DESCENDING:
					$this->query->addOrder($field.' DESC');
					break;
				case DP_Query_Sort::SORT_ASCENDING:
					$this->query->addOrder($field.' ASC');
					break;
			}
		}
		
		$this->object_list = $this->query->loadList();
	}
	
	public function count() {
		return count($this->object_list);
	}
	
	// From DP_Observable_Interface
	
	public function attach(DP_Observer_Interface $observer) {
		if (!in_array($this->observers, $observer)) {
			$this->observers[] = $observer;
		}
	}
	
	public function detach(DP_Observer_Interface $observer){
		if (in_array($this->observers, $observer)) {
			$observer_key = array_search($this->observers, $observer);
			$this->observers[$observer_key] = null;
			
			$reordered_observers = array_values($this->observers);
			$this->observers = $reordered_observers;
		}
	}
	
	public function notify() {
		$this->company_list = DP_Object_Factory::getListFromFilteredQuery($this->query, Array($this->filter, $this->sort, 0));
		
		foreach ($this->observer as $ob) {
			$ob->updateState($this);	
		}
	}
	
	// From DP_View_List_Source_Interface
	
	public function getIterator() {
		return new DP_Iterator($this->object_list);
	}
	
	public function getColumns() {
		return $this->column_names;
	}
	
	
	
}

?>