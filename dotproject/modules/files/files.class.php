<?php /* FILES $Id$ */
require_once( $AppUI->getSystemClass( 'libmail' ) );
require_once( $AppUI->getSystemClass( 'dp' ) );
require_once( $AppUI->getModuleClass( 'tasks' ) );
require_once( $AppUI->getModuleClass( 'projects' ) );
/**
* File Class
*/
class CFile extends CDpObject {

	var $file_id = NULL;
	var $file_project = NULL;
	var $file_real_filename = NULL;
	var $file_task = NULL;
	var $file_name = NULL;
	var $file_parent = NULL;
	var $file_description = NULL;
	var $file_type = NULL;
	var $file_owner = NULL;
	var $file_date = NULL;
	var $file_size = NULL;
	var $file_version = NULL;

	
	function CFile() {
		$this->CDpObject( 'files', 'file_id' );
	}

	function check() {
	// ensure the integrity of some variables
		$this->file_id = intval( $this->file_id );
		$this->file_parent = intval( $this->file_parent );
                $this->file_category = intval( $this->file_category );
		$this->file_task = intval( $this->file_task );
		$this->file_project = intval( $this->file_project );

		return NULL; // object is ok
	}

	function delete() {
		global $AppUI;
		$this->_message = "deleted";
		
	// remove the file from the file system
		@unlink( "{$AppUI->cfg['root_dir']}/files/$this->file_project/$this->file_real_filename" );
	// delete any index entries
		$sql = "DELETE FROM files_index WHERE file_id = $this->file_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
	// delete the main table reference
		$sql = "DELETE FROM files WHERE file_id = $this->file_id";
		if (!db_exec( $sql )) {
			return db_error();
		}
		return NULL;
	}

// move a file from a temporary (uploaded) location to the file system
	function moveTemp( $upload ) {
		global $AppUI;
	// check that directories are created
		if (!is_dir("{$AppUI->cfg['root_dir']}/files")) {
		    $res = mkdir( "{$AppUI->cfg['root_dir']}/files", 0777 );
		    if (!$res) {
			     return false;
			 }
		}
		if (!is_dir("{$AppUI->cfg['root_dir']}/files/$this->file_project")) {
		    $res = mkdir( "{$AppUI->cfg['root_dir']}/files/$this->file_project", 0777 );
			 if (!$res) {
                                $AppUI->setMsg( "Upload folder not setup to accept uploads - change permission on files/ directory.", UI_MSG_ALLERT );
			     return false;
			 }
		}


		$this->_filepath = "{$AppUI->cfg['root_dir']}/files/$this->file_project/$this->file_real_filename";
	// move it
		$res = move_uploaded_file( $upload['tmp_name'], $this->_filepath );
		if (!$res) {
		    return false;
		}
		return true;
	}

// parse file for indexing
	function indexStrings() {
		GLOBAL $ft, $AppUI;
	// get the parser application
		$parser = @$ft[$this->file_type];
		if (!$parser) {
			return false;
		}
	// buffer the file
		$fp = fopen( $this->_filepath, "rb" );
		$x = fread( $fp, $this->file_size );
		fclose( $fp );
	// parse it
		$parser = $parser . " " . $this->_filepath;
		$pos = strpos( $parser, '/pdf' );
		if (false !== $pos) {
			$x = `$parser -`;
		} else {
			$x = `$parser`;
		}
	// if nothing, return
		if (strlen( $x ) < 1) {
			return 0;
		}
	// remove punctuation and parse the strings
		$x = str_replace( array( ".", ",", "!", "@", "(", ")" ), " ", $x );
		$warr = split( "[[:space:]]", $x );

		$wordarr = array();
		$nwords = count( $warr );
		for ($x=0; $x < $nwords; $x++) {
			$newword = $warr[$x];
			if (!ereg( "[[:punct:]]", $newword )
				&& strlen( trim( $newword ) ) > 2
				&& !ereg( "[[:digit:]]", $newword )) {
				$wordarr[] = array( "word" => $newword, "wordplace" => $x );
			}
		}
		db_exec( "LOCK TABLES files_index WRITE" );
	// filter out common strings
		$ignore = array();
		include "{$AppUI->cfg['root_dir']}/modules/files/file_index_ignore.php";
		foreach ($ignore as $w) {
			unset( $wordarr[$w] );
		}
	// insert the strings into the table
		while (list( $key, $val ) = each( $wordarr )) {
			$sql = "INSERT INTO files_index VALUES ('" . $this->file_id . "', '" . $wordarr[$key]['word'] . "', '" . $wordarr[$key]['wordplace'] . "')";
			db_exec( $sql );
		}

		db_exec( "UNLOCK TABLES;" );
		return nwords;
	}
	
	//function notifies about file changing
	function notify() {	
		GLOBAL $AppUI, $locale_char_set;
		//if no project specified than we will not do anything
		if ($this->file_project != 0) {
			$this->_project = new CProject();
			$this->_project->load($this->file_project);
			$mail = new Mail;		

			if ($this->file_task == 0) {//notify all developers
				$mail->Subject( $this->_project->project_name."::".$this->file_name, $locale_char_set);
			} else { //notify all assigned users			
				$this->_task = new CTask();
				$this->_task->load($this->file_task);
				$mail->Subject( $this->_project->project_name."::".$this->_task->task_name."::".$this->file_name, $locale_char_set);
			}
			
			$body = $AppUI->_('Project').": ".$this->_project->project_name;
			$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=projects&a=view&project_id=".$this->_project->project_id;
			
			if (intval($this->_task->task_id) != 0) {
				$body .= "\n\n".$AppUI->_('Task').":    ".$this->_task->task_name;
				$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/index.php?m=tasks&a=view&task_id=".$this->_task->task_id;
				$body .= "\n" . $AppUI->_('Description') . ":" . "\n".$this->_task->task_description;
				
				//preparing users array
				$sql = "SELECT t.task_id,"
				."\nc.user_email as creator_email,"
				."\nc.user_first_name as creator_first_name,"
				."\nc.user_last_name as creator_last_name,"
				."\no.user_email as owner_email,"
				."\no.user_first_name as owner_first_name,"
				."\no.user_last_name as owner_last_name,"
				."\na.user_id as assignee_id,"
				."\na.user_email as assignee_email,"
				."\na.user_first_name as assignee_first_name,"
				."\na.user_last_name as assignee_last_name"
				."\nFROM tasks t"
				."\nLEFT JOIN user_tasks u ON u.task_id = t.task_id"
				."\nLEFT JOIN users o ON o.user_id = t.task_owner"
				."\nLEFT JOIN users c ON c.user_id = t.task_creator"
				."\nLEFT JOIN users a ON a.user_id = u.user_id"
				."\nWHERE t.task_id = ".$this->_task->task_id;
				$this->_users = db_loadList( $sql );
			} else {
				//find project owner and notify him about new or modified file
				$sql = "select u.* from users u, projects p where p.project_owner = u.user_id and p.project_id = ".$this->file_project;
				$this->_users = db_loadList( $sql );							
			}
			$body .= "\n\nFile ".$this->file_name." was ".$this->_message." by ".$AppUI->user_first_name . " " . $AppUI->user_last_name;
			if ($this->_message != "deleted") {
				$body .= "\n".$AppUI->_('URL').":     {$AppUI->cfg['base_url']}/fileviewer.php?file_id=".$this->file_id;
				$body .= "\n" . $AppUI->_('Description') . ":" . "\n".$this->file_description;	
			}
			
			//send mail			
			$mail->Body( $body, isset( $GLOBALS['locale_char_set']) ? $GLOBALS['locale_char_set'] : "" );
			$mail->From ( '"' . $AppUI->user_first_name . " " . $AppUI->user_last_name . '" <' . $AppUI->user_email . '>');
			
			if (intval($this->_task->task_id) != 0) {
				foreach ($this->_users as $row) {
					if ($row['assignee_id'] != $AppUI->user_id) {
						if ($mail->ValidEmail($row['assignee_email'])) {
							$mail->To( $row['assignee_email'], true );
							$mail->Send();
						}
					}
				}
			} else { //sending mail to project owner
				foreach ($this->_users as $row) { //there should be only one row
					if ($row['user_id'] != $AppUI->user_id) {
						if ($mail->ValidEmail($row['user_email'])) {
							$mail->To( $row['user_email'], true );
							$mail->Send();
						}
					}
				}				
			}
		}
	}//notify
}
?>
