<?php
##
## CFile Class
##

class CFile {
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
		// empty constructor
	}

	function load( $oid ) {
		$sql = "SELECT * FROM files WHERE file_id = $oid";
		return db_loadObject( $sql, $this );
	}

	function bind( $hash ) {
		if (!is_array( $hash )) {
			return get_class( $this )."::bind failed";
		} else {
			bindHashToObject( $hash, $this );
			return NULL;
		}
	}

	function check() {
	// ensure the integrity of some variables
		if (!isset( $this->file_id )) {
			$this->file_id = 0;
		}
		if (!isset( $this->file_parent )) {
			$this->file_parent = 0;
		}
		if (!isset( $this->file_task )) {
			$this->file_task = 0;
		}
		if (!isset( $this->file_project )) {
			$this->file_project = 0;
		}
	// TODO MORE
		return NULL; // object is ok
	}

	function store() {
		$msg = $this->check();
		if( $msg ) {
			return get_class( $this )."::store-check failed";
		}
		if( $this->file_id ) {
			$ret = db_updateObject( 'files', $this, 'file_id' );
		} else {
			$ret = db_insertObject( 'files', $this, 'file_id' );
		}
		if( !$ret ) {
			return get_class( $this )."::store failed <br>" . db_error();
		} else {
			return NULL;
		}
	}

	function delete() {
		GLOBAL $root_dir;
	// remove the file from the file system
		@unlink( "$root_dir/files/$this->file_project/$this->file_real_filename" );
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
		GLOBAL $root_dir;
	// check that directories are created
		@mkdir( "$root_dir/files", 0777 );
		@mkdir( "$root_dir/files/$this->file_project", 0777 );
		$this->_filepath = "$root_dir/files/$this->file_project/$this->file_real_filename";
	// move it
		move_uploaded_file( $upload['tmp_name'], $this->_filepath );
	}

// parse file for indexing
	function indexStrings() {
		GLOBAL $root_dir, $ft;
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
		include "$root_dir/dosql/file_index_ignore.php";
		foreach ($ignore as $w) {
			unset( $wordarr[$w] );
		}
	// insert the strings into the table
		while (list( $key, $val ) = each( $wordarr )) {
			$sql = "INSERT INTO files_index VALUES ('" . $filenum . "', '" . $wordarr[$key]['word'] . "', '" . $wordarr[$key]['wordplace'] . "')";
			db_exec( $sql );
		}

		db_exec( "UNLOCK TABLES;" );
		return nwords;
	}
}
?>