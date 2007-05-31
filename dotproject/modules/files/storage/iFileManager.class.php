<?php

interface iFileManager {
	public function createFile($file, $upload);
	public function retrieveFile($fileRow);
	public function updateFile($file);
	public function deleteFile($fileObject);
	public function moveFile($file, $oldProject, $realname);
	public function summarizeFile();
	public function checkinFile($file);
	public function checkoutFile($file);
}

?>