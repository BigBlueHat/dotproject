<?php
// Translation Management

// check permissions
$denyEdit = getDenyEdit( $m );

if ($denyEdit) {
	$AppUI->redirect( "m=help&a=access_denied" );
}

$module = isset( $_REQUEST['module'] ) ? $_REQUEST['module'] : 0;
$lang = isset( $_REQUEST['lang'] ) ? $_REQUEST['lang'] : 'es';

$AppUI->savePlace( "m=system&a=translate&module=$module&lang=$lang" );

$modules = array(
	'common',
	'companies',
	'help'
);

ob_start();
	@readfile( "$root_dir/locales/en/$modules[$module].inc" );
	eval( "\$english=array(".ob_get_contents()."\n'0');" );
ob_end_clean();

$trans = array();
foreach( $english as $k => $v ) {
	if ($v != "0") {
		$trans[ (is_int($k) ? $v : $k) ] = array( 
			'english' => $v
		);
	}
}

//echo "<pre>";print_r($trans);echo "</pre>";die;

if ($lang != 'en') {
	ob_start();
		@readfile( "$root_dir/locales/$lang/$modules[$module].inc" );
		eval( "\$locale=array(".ob_get_contents()."\n'0');" );
	ob_end_clean();

	foreach( $locale as $k => $v ) {
		if ($v != "0") {
			$trans[$k]['lang'] = htmlspecialchars( $v, ENT_QUOTES );
		}
	}
}
ksort($trans);
?>

<img src="images/shim.gif" width="1" height="5" alt="" border="0"><br>
<table width="98%" border="0" cellpadding="0" cellspacing="1">
<form action="?m=system&a=translate" method="post" name="modlang">
<tr>
	<td><img src="./images/icons/world.gif" alt="" border="0"></td>
	<td nowrap valign="top"><span class="title">Translation Management</span></td>
	<td align="right" width="100%" nowrap>Module:</span></td>
	<td><?php
	echo arraySelect( $modules, 'module', 'size="1" class="text" onchange="document.modlang.submit();"', $module );
	?></td>
	<td align="right" width="100%" nowrap>&nbsp;Language:</span></td>
	<td><?php
	echo arraySelect( $AppUI->locales, 'lang', 'size="1" class="text" onchange="document.modlang.submit();"', $lang );
	?></td>
</tr>
</form>
</table>

<table border="0" cellpadding="4" cellspacing="0" width="98%">
<tr>
	<td nowrap><a href="?m=system">System Admin</a></td>
</tr>
</table>

<table width="98%" border="0" cellpadding="1" cellspacing="1" class="tbl">
<tr>
	<th width="15%" nowrap>Abbreviation</th>
	<th width="40%" nowrap>English String</th>
	<th width="40%" nowrap><?php echo $AppUI->locales[$lang];?> String</th>
	<th width="5%" nowrap>Delete</th>
</tr>
<form action="?m=system&a=translate_save" method="post" name="editlang">
<input type="hidden" name="module" value="<?php echo $modules[$module];?>">
<input type="hidden" name="lang" value="<?php echo $lang;?>">
<?php
$index = 0;
if ($lang == 'en') { 
	echo '<tr>';
	echo "<td><input type=\"text\" name=\"trans[$index][abbrev]\" value=\"\" size=\"20\" class=\"text\"></td>";
	echo "<td><input type=\"text\" name=\"trans[$index][english]\" value=\"\" size=\"40\" class=\"text\"></td>";
	echo '<td colspan="2">New Entry</td>';
	echo '</tr>';
}

$index++;
foreach ($trans as $k => $langs){
?>
<tr>
	<td><?php
		if ($k != $langs['english']) {
			$k = htmlspecialchars( $k, ENT_QUOTES );
			if ($lang == 'en') {
				echo "<input type=\"text\" name=\"trans[$index][abbrev]\" value=\"$k\" size=\"20\" class=\"text\">";
			} else {
				echo $k;
			}
		} else {
			echo '&nbsp;';
		}
	?></td>
	<td><?php 
		$langs['english'] = htmlspecialchars( $langs['english'], ENT_QUOTES );
		if ($lang == 'en') {
			echo "<input type=\"text\" name=\"trans[$index][english]\" value=\"{$langs['english']}\" size=\"40\" class=\"text\">";
		} else {
			echo $langs['english'];
			echo "<input type=\"hidden\" name=\"trans[$index][english]\" value=\""
				.($k ? $k : $langs['english'])
				."\" size=\"20\" class=\"text\">";
		}
	?></td>
	<td><?php 
		if ($lang != 'en') {
			$langs['lang'] = htmlspecialchars( $langs['lang'], ENT_QUOTES );
			echo "<input type=\"text\" name=\"trans[$index][lang]\" value=\"{$langs['lang']}\" size=\"40\" class=\"text\">";
		}
	?></td>
	<td align="center"><?php echo "<input type=\"checkbox\" name=\"trans[$index][del]\" />";?></td>
</tr>
<?php 
	$index++;
}
?>
<tr>
	<td colspan="4" align="right">
		<input type="submit" value="save changes" class="button">
	</td>
</tr>
</form>
</table>
