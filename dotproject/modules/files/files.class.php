<?php /* FILES $Id$ */
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
		$this->file_task = intval( $this->file_task );
		$this->file_project = intval( $this->file_project );

		return NULL; // object is ok
	}

	function delete() {
		global $AppUI;
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
}
?>
