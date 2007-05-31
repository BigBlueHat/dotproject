<?php

include_once DP_BASE_DIR.'/modules/files/storage/iFileManager.class.php';

class SVNFileManager implements iFileManager {

	public function createFile() {
		return true;
	}
	public function retrieveFile() {
		return true;
	}
	public function updateFile() {
		return true;
	}
	public function deleteFile() {
		return true;
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