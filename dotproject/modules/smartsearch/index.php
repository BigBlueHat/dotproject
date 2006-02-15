<?php
$search_keyword = @$_POST['keyword'];

if ($search_keyword != NULL && $search_keyword != '')
{ 
	$perms = &$AppUI->acl();

	$modules = array(
	'projects' => 'CProject',
	'companies' => 'CCompany',
	'contacts' => 'CContact',
	'departments' => 'CDepartment',
	'calendar' => 'CEvent',
	'files' => 'CFile',
	'forums' => 'CForum',
	'tasks' => 'CTask',
	'admin' => 'CUser');
	foreach ($modules as $module => $class_name)
	{
		require_once( $AppUI->getModuleClass($module) );	
		$object = new $class_name();
		$results = $object->search($search_keyword);
		$results_array[$object->_tbl] = array('key' => $object->_tbl_key, 'results' => $results);
	}
	

	//require_once( $AppUI->getModuleClass('smartsearch') );	
	//$search = new smartsearch();
	//$search->keyword = $search_keyword;
	
	$files = $AppUI->readFiles( dPgetConfig( 'root_dir' )."/modules/smartsearch/searchobjects", "\.php$" );
	sort($files);
	$results_html = ''; // html results
	foreach ($files as $tmp)
	{
		require_once('./modules/smartsearch/searchobjects/'.$tmp);
		$temp = substr($tmp,0,-8);
		$temp .= '()';	
		eval ("\$class_search = new $temp;");
		$class_search->setKeyword($search_keyword);
		$results_html .= $class_search->searchResults($perms);
	} 
}

$tpl->assign('results', $results_array);
$tpl->assign('search_results', $results_html);
$tpl->assign('search_keyword', $search_keyword);

$tpl->displayFile('index');
?>
