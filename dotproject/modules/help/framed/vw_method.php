<?
// View OOP Class

$sql = "
SELECT *, class_title, class_entry
FROM {$AppUI->project_dbprefix}xmethods
LEFT JOIN {$AppUI->project_dbprefix}xclasses ON class_id = method_class AND class_lang = '$entry_lang'
WHERE method_entry = $entry_id
	AND method_lang = '$entry_lang'
";
$rc = db_exec( $sql );
if(!$rc) {
	echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
}
##echo "<pre>$sql</pre>";
$rows = db_fetch_assoc( $rc );

$sql = "SHOW COLUMNS FROM {$AppUI->project_dbprefix}xmethods LIKE 'method_type'";
$result = db_exec( $sql );
if(!$result) {
	echo '<font color=red>SQL Error:</font> '.db_errno() . ": " . db_error() . "\n";
}
$erow = db_fetch_assoc( $result );
$types =  @$erow["Type"];

$tok = strtok(@$rows["method_params"],"`");
$params = array();
while ($tok) {
	$pos1=0;
	$pos2=0;

	$pos2=strpos($tok,',',$pos1);
	$type=substr($tok,$pos1,$pos2-$pos1);

	$pos1=$pos2+1;
	$pos2=strpos($tok,',',$pos1);
	$name=substr($tok,$pos1,$pos2-$pos1);

	$pos1=$pos2+1;
	$pos2=strpos($tok,',',$pos1);
	$deft=substr($tok,$pos1,$pos2-$pos1);

	$pos1=$pos2+1;
	$desc=substr($tok,$pos1);

	$params[] = array($type,$name,$deft,$desc);
	$tok = strtok("`");
}
?>

<table width="600" cellspacing=0 cellpadding=0 border=0><tr><td style="padding-left:20px;">
<h1><?
	echo isset( $export ) ? '<a href="class'.@$rows["class_id"].'.html">'.@$rows["class_title"].'</a>'
		: '<a href="./main.php?entry_lang=' .$entry_lang .'&entry_type=class&entry_id=' .@$rows["class_entry"].'">' .@$rows["class_title"].'</a>';
?>::<?echo @$rows["method_name"]; ?></h1>

<p><?echo @$rows["method_desc"];?>

<pre class=syntax><strong><?
	echo @$rows["method_returns"];
	if(@$rows["method_retbyref"])
		echo ' &';
	echo ' '.@$rows["method_name"];
?>(
<?
	$n=count($params);
	for($i=0; $i < $n; $i++ ) {
		echo ($i>0)?",\n":'';
		echo '   '.$params[$i][0].' </strong><I>'.$params[$i][1].'</I><strong>';
		if($params[$i][2] != '')
			echo ' = '.$params[$i][2];
	}
?>
);</strong></pre>

<?
	if($n > 0) {
		echo '<h4>'.$AppUI->_( 'Parameters' ).'</h4><DL>';
		for($i=0; $i < $n; $i++ ) {
			echo '<DT><I>'.$params[$i][1].'</I>';
			echo '<DD>'.$params[$i][3].'</DD>';
		}
		echo '</DL>';
	}
?>

<h4><?php echo $AppUI->_( 'Remarks' );?></h4>
<?echo @$rows["method_remarks"];?>

<? if(@$rows["method_example"]!=''){ ?>
<H4><?php echo $AppUI->_( 'Example' );?></H4>

<pre class=code><?
	echo str_replace( array("<",">"), array("&lt;","&gt;"), @$rows["method_example"]);
?></pre>
<? } ?>

<? if(@$rows["method_output"]!=''){ ?>
<?php echo $AppUI->_( 'This ouputs' );?>
<pre class=output><?
	echo str_replace( array("<",">"), array("&lt;","&gt;"), @$rows["method_output"]);
?></pre>
<? } ?>
</td></tr></table>

