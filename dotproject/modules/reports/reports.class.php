<?php

	class dPReport {
		private $temp_dir = '/files/temp/';
		private $file_type = '';
		
		public function initializePDF() {
			$this->file_type = 'pdf';
		}

		public function write($filename, $contents) {
			global $AppUI;
			if ($fp = fopen( DP_BASE_DIR.$this->temp_dir.'/'.$filename, 'wb' )) {
				fwrite( $fp, $contents );
				fclose( $fp );
				echo '<a href="'.DP_BASE_URL.'/'.$this->temp_dir.'/'.$filename.'" target="'.$this->file_type.'">';
				echo $AppUI->_( "View PDF File" );
				echo "</a>";
			} else {
				echo 'Could not open file to save '.$this->file_type.'}.  ';
				if (!is_writable( $this->temp_dir )) {
					echo "The $destinationDir directory is not writable.  Check your file system permissions.";
				}				
			}
		}
	}