<?php
function highlight($text, $key)
{
        return str_replace($key, '<span style="background: yellow">'.$key.'</span>', $text);
}

$files = $AppUI->readFiles( dPgetConfig( 'root_dir' )."/modules/smartsearch/searchobjects", "\.php$" );

require_once( $AppUI->getModuleClass('smartsearch') );

$search_keyword = @$_POST['keyword'];

if ($search_keyword != NULL && $search_keyword != '')
{ 
	$search = new smartsearch();
	$search->keyword = $search_keyword;

	$perms = &$AppUI->acl();
	sort($files);
	$results = ""; // html results
	foreach ($files as $tmp)
	{
		require_once('./modules/smartsearch/searchobjects/'.$tmp);
		$temp = substr($tmp,0,-8);
		$temp .= '()';	
		eval ("\$class_search = new $temp;");
		$class_search->setKeyword($search->keyword);
		$results .= $class_search->fetchResults($perms);
	}
}

$tpl->assign('search_results', $results);
$tpl->assign('search_keyword', $search_keyword);

$tpl->displayFile('index');
?>
