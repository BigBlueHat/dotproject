<?php

include_once DP_BASE_DIR.'/modules/files/storage/iFileManager.class.php';

class SVNFileManager implements iFileManager {
	public function createFile($file, $upload) {
		return true;
	}
	public function retrieveFile($fileRow) {
		return true;
	}
	public function updateFile($file) {
		return true;
	}
	public function deleteFile($fileObject) {
		return true;
	}
	public function moveFile($file, $oldProject, $realname) {
		return true;
	}
	public function summarizeFile() {
		return true;
	}
	public function checkinFile($file) {
		return true;
	}
	public function checkoutFile($file) {
		return true;
	}
}

?>