<?
//addfile sql
set_time_limit(600);
ignore_user_abort(1);
if(empty($file_id))$file_id =0;
if(empty($file_task))$file_task =0;
if(empty($file_parent))$file_parent =0;
if(empty($file_project))$file_project =0;

if($HTTP_POST_FILES['formfile']['size'] >0){
	$fd = fopen($HTTP_POST_FILES['formfile']['tmp_name'], "r"); 
	//Create Unique name
	$newname = uniqid (rand()); 
	@mkdir($root_dir . "/files", 0777);
	@mkdir($root_dir . "/files/" . $file_project, 0777);
	$newfile = $root_dir . "/files/" . $file_project . "/" . $newname;
	move_uploaded_file($formfile, $newfile);
	
	$fsql=
	"INSERT INTO 
	files (
		file_project, 
		file_real_filename,
		file_task, 
		file_name , 
		file_parent, 
		file_description, 
		file_type,
		file_owner,
		file_date,
		file_size,
		file_version)
	values(
		'$file_project', 
		'$newname', 
		'$file_task', 
		'" . $HTTP_POST_FILES['formfile']['name'] ."', 
		'$file_parent', 
		'$file_description', 
		'" .$HTTP_POST_FILES['formfile']['type']. "',
		'$user_cookie',
		'" . strftime("%Y-%m-%d %H:%M:%S", time()) ."',
		'" .$HTTP_POST_FILES['formfile']['size'] . "', 
		'$file_version')";
	mysql_query($fsql);
	$filenum = mysql_insert_id();
	$message = mysql_error();
	
	if(!$message)$message = "File successfully uploaded";
	//header("Location: ./index.php?m=files&message=" . $message);
		$fd = fopen($newfile, "r"); 
		$x = fread($fd, $HTTP_POST_FILES['formfile']['size']); 
	//begin parsing file for indexing
	if(isset($ft[$HTTP_POST_FILES['formfile']['type']])){
		$parser = $ft[$HTTP_POST_FILES['formfile']['type']];
		$parser = $parser . " " . $newfile;
		if(strpos($parser, "/pdf")){
		$x = `$parser -`;
		}
		else{
		$x = `$parser`;
		}
		$x = str_replace(".", " ", $x);
		$x = str_replace(",", " ", $x);
		$x = str_replace("!", " ", $x);
		$x = str_replace("@", " ", $x);
		$x = str_replace("(", " ", $x);
		$x = str_replace(")", " ", $x);
		$warr = split("[[:space:]]", $x);
		
		for($x=0;$x<count($warr);$x++){
			$newword = $warr[$x];
		if(!ereg("[[:punct:]]", $newword) && strlen(trim($newword)) > 2 && !ereg("[[:digit:]]", $newword))
			{
				$wordarr[] = array("word"=>$newword, "wordplace"=>$x);
			}
		}
		mysql_query("lock tables files_index write");
		
		require "./dosql/stopwords.php";
		while(list($key, $val) = each($wordarr)){
			$sql = "insert into files_index values ('" . $filenum . "', '" . $wordarr[$key]['word'] . "', '" . $wordarr[$key]['wordplace'] . "')";
			mysql_query($sql);
		}
		mysql_query("UNLOCK TABLES;");
	}
}
else{
$message = "file upload failed";

}
header("Location: ./index.php?m=files&message=" . $message);
?>