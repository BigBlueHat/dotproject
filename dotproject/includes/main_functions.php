<?
//Main Functions

function ptranslate($word){
	global $language_file,$root_dir;
	require "./includes/" . $language_file;
	if(empty($pt[$word]))
	{
		return $word;
	}
	else
	{
		return $pt[$word];
	}
}


//return Duration returns an array that
function returnDur($x){
	if(($x) % 24 ==0){
		$value= ($x / 24);
		$mulitpule = 24;
		if($value > 1){
			$type = "days";
		}
		else{
			$type = "day";
		}
	}
	else{
		$value = ($x);
		$mulitpule = 1;
		if($value > 1){
			$type = "hours";
		}
		else{
			$type = "hour";
		}

	}
	return array("value"=>$value, "mulitpule"=>$mulitpule, "type"=>$type);
}


function getPerms($module, $uid){
	global $user_cookie;
	$rp = array();
	$sql = "select permission_value, permission_item 
					from 
					permissions
					where 
					permission_user  = $uid and
					(permission_grant_on = '$module' or permission_grant_on = 'all')
					order by permission_value, permission_item";
	$perm =mysql_query($sql);
	while($row = mysql_fetch_array($perm)){
		$rp[] = $row;
	}
	
	return $rp;

}
?>