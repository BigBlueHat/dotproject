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
class Companies_List_Data implements DP_View_List_Source_Interface, SplSubject {
	private $query;
	private $count_query;
	private $filters;
	private $sort;
	private $observers;
	
	private $object_list;
	private $column_names;
	
	private $spl_observers;
	
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
		
		$cq = new DP_Query;
		$cq->addTable('companies', 'c');
		$cq->addQuery('count(*)');
		$cq->addJoin('projects', 'p', 'c.company_id = p.project_company AND p.project_status != 7');
		$cq->addJoin('users', 'u', 'c.company_owner = u.user_id');
		$cq->addJoin('contacts', 'con', 'u.user_contact = con.contact_id');
		$cq->addJoin('projects', 'p2', 'c.company_id = p2.project_company AND p2.project_status = 7');
		$cq->addGroup('c.company_id');		
		
		$this->count_query = $cq;
		
		$this->spl_observers = Array();
		
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
	
	public function setPage($page, $rows_per_page) {
		$this->query->setPageLimit($page, $rows_per_page);
	}
	
	public function loadList() {
		foreach($this->filters as $filter) {
			foreach($filter->filters as $rule) {
				switch($rule['filter_type']) {
					case DP_Filter::VALUE_EQUAL:
						$this->query->addWhere($rule['filter_field']." = ".$rule['field_value']);
						$this->count_query->addWhere($rule['filter_field']." = ".$rule['field_value']);
						break;
					case DP_Filter::VALUE_SUBSTR:
						$this->query->addWhere($rule['filter_field']." LIKE '%".$rule['field_value']."%'");
						$this->count_query->addWhere($rule['filter_field']." LIKE '%".$rule['field_value']."%'");
						break;
				}
			}
		}
		
		foreach ($this->sort as $field => $sort_rule) {
			switch($sort_rule) {
				case DP_Query_Sort::SORT_DESCENDING:
					$this->query->addOrder($field.' DESC');
					$this->count_query->addOrder($field.' DESC');
					break;
				case DP_Query_Sort::SORT_ASCENDING:
					$this->query->addOrder($field.' ASC');
					$this->count_query->addOrder($field.' ASC');
					break;
			}
		}

		$this->object_list = $this->query->loadList();
	}
	
	public function count() {
		$full_list = $this->count_query->loadList();
		return count($full_list);
	}
	
	
	/*
	// From DP_Observable_Interface
	// @deprecated DP_Observable functions for SplObserver
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
	*/
	// From DP_View_List_Source_Interface
	
	public function getIterator() {
		return new DP_Iterator($this->object_list);
	}
	
	public function getColumns() {
		return $this->column_names;
	}
	
	// From SplSubject
	
	/**
	 * Attach an observer
	 * 
	 * @param SplObserver $observer The observer to attach
	 * @return null
	 */
	public function attach (SplObserver $observer) {
		if (!in_array($this->spl_observers, $observer)) {
			$this->spl_observers[] = $observer;
		}		
	}
	
	/**
	 * Detach an observer
	 * 
	 * @param SplObserver $observer The observer to detach
	 * @return null
	 */
 	public function detach (SplObserver $observer) {
 		if (in_array($this->spl_observers, $observer)) {
			$observer_key = array_search($this->spl_observers, $observer);
			$this->spl_observers[$observer_key] = null;
			
			$reordered_observers = array_values($this->spl_observers);
			$this->spl_observers = $reordered_observers;
		}		
 	}
 	
 	/**
 	 * Notify all observers
 	 * 
 	 */
 	public function notify () {
 		foreach ($this->spl_observers as $obs) {
 			$obs->update(self);
 		}
 	}
	
}

?>