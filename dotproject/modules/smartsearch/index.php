<?php
function getArchivosDirectorio($directorio, $solo_directorios = false){
	if(!is_dir($directorio)) return array();
	
	if($directorio == "") $directorio = "./";
	
	$this_dir     = dir($directorio);
	$result_array = array();
	while ($file = $this_dir->read()){
		if($solo_directorios && !strstr($file, ".")){
			$result_array[] = $file;
		} else if(strstr($file, ".") && !$solo_directorios && !is_dir($file)){
			$result_array[] = $file;
		}
	}
	return $result_array;
}
                               
$files = getArchivosDirectorio("./modules/smartsearch/searchobjects");

require_once( $AppUI->getSystemClass( 'ui' ) );
require_once( $AppUI->getModuleClass('smartsearch') );
?>
<table width="100%" border="0" cellpadding="0" cellspacing=1>
	<tr>
		<td nowrap width="100%"><h1><?php echo $AppUI->_('Search')?></h1></td>
	</tr>
</table>
	<form name="frmSearch" action="?m=smartsearch"  method="POST">
			<table cellspacing="10" cellpadding="0" border="0">
				<tr>
					<td align="left"><INPUT class="text" type="text" id="keyword" name="keyword"></td>
					<td align="left"><input class="button" type="submit" value="<?php echo $AppUI->_('Search')?>"></td>
				</tr>
			</table>
	</form>
<script language="JavaScript">
	document.frmSearch.keyword.focus();
</script>
<?php
if (isset ($_POST['keyword']))
{ 
	$search = new smartsearch();
	$search->keyword = ($_POST['keyword']);
?>

	<table width="100%" border="0" cellpadding="2" cellspacing="1" class="tbl">
<?php

	foreach ($files as $tmp){
		require_once('./modules/smartsearch/searchobjects/'.$tmp);
		$temp = substr($tmp,0,-8);
		$temp .= '()';	
		eval ("\$class_search = new $temp;");
		$class_search->setKeyword($search->keyword);
		$results = $class_search->fetchResults();
		echo $results;
	}
?>
</table>
<?php 
unset ($_POST['keyword']);
} 
?>