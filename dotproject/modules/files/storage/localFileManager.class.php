<?php

include_once DP_BASE_DIR.'/modules/files/storage/iFileManager.class.php';

class LocalFileManager implements iFileManager {

  public function moveFile($file, $oldProject, $realname) {
		if (!is_dir(DP_BASE_DIR.'/files/'.$file->file_project)) {
			$res = mkdir( DP_BASE_DIR.'/files/'.$file->file_project, 0777 );
			if (!$res) {
				$AppUI->setMsg( "Upload folder not setup to accept uploads - change permission on files/ directory.", UI_MSG_ALLERT );
				return false;
			}
		}
		$file->file_real_filename = ($file->file_real_filename != '') ? $file->file_real_filename : $realname;
		$res = rename(DP_BASE_DIR."/files/{$oldProject}/{$file->file_real_filename}", DP_BASE_DIR."/files/{$file->file_project}/{$file->file_real_filename}");
		if (!$res) {
			return false;
		}
		return true;
  }
  public function duplicateFile($file, $oldProject, $realname) {
		$file->file_project = 0;
		if (!is_dir(DP_BASE_DIR.'/files/0')) {
			$res = mkdir(DP_BASE_DIR.'/files/0', 0777);
			if (!$res) {
				$AppUI->setMsg( "Upload folder not setup to accept uploads - change permission on files/ directory.", UI_MSG_ALLERT );
				return false;
			}
		}
		$dest_realname = uniqid( rand() );
		$res = copy(DP_BASE_DIR . "/files/$oldProject/$realname", DP_BASE_DIR . '/files/0/'.$dest_realname);

		if (!$res) {
			return false;
		}
		return $dest_realname;
  }
	public function createFile($file, $upload) {
		//check that directories are created
		if (!is_dir(DP_BASE_DIR . '/files')) {
		    $res = mkdir(DP_BASE_DIR . '/files', 0777);
		    if (!$res) {
			     return false;
			 }
		}
		if (!is_dir(DP_BASE_DIR . '/files/'.$file->file_project)) {
			$res = mkdir(DP_BASE_DIR . '/files/'.$file->file_project, 0777 );
			if (!$res) {
				$AppUI->setMsg( "Upload folder not setup to accept uploads - change permission on files/ directory.", UI_MSG_ALLERT );
				return false;
			}
		}

		$file->_filepath = DP_BASE_DIR . "/files/$file->file_project/$file->file_real_filename";
		// move it
		$res = move_uploaded_file( $upload['tmp_name'], $file->_filepath );
		if (!$res) {
			return false;
		}
		return true;
	}

	public function retrieveFile($file) {
    $result = '';
    /*
     * MerlinYoda> 
     * some added lines from: 
     * http://www.dotproject.net/vbulletin/showpost.php?p=11975&postcount=13
     * along with "Pragma" header as suggested in: 
     * http://www.dotproject.net/vbulletin/showpost.php?p=14928&postcount=1. 
     * to fix the IE download issue for all for http and https
     * 
     */

		header('MIME-Version: 1.0');
		header('Pragma: ');
		header('Cache-Control: public');
		header('Content-length: ' . $file['file_size']);
		header('Content-type: ' . $file['file_type']);
		header('Content-transfer-encoding: 8bit');
		header('Content-disposition: attachment; filename="'.$file['file_name'].'"');
		
		// read and output the file in chunks to bypass limiting settings in php.ini
		$handle = fopen(DP_BASE_DIR . "/files/{$file['file_project']}/{$file['file_real_filename']}", 'rb');
		if ($handle) {
			while ( !feof($handle) ) {
				$result .= fread($handle, 8192);
			}
			fclose($handle);
		}
		return $result;
	}
	public function updateFile() {
		return true;
	}
	public function deleteFile($file) {
		return @unlink(DP_BASE_DIR.'/files/'.$file->file_project.'/'.$file->file_real_filename);
	}
	public function summarizeFile() {
		return true;
	}
	public function checkinFile() {
		return true;
	}
	public function checkoutFile() {
		return true;
	}
}

?>