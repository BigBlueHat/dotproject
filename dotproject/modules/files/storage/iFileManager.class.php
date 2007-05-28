<?php

interface iFileManager {
	public function createFile();
	public function retrieveFile($fileRow);
	public function updateFile();
	public function deleteFile($fileObject);
	public function summarizeFile();
	public function checkinFile();
	public function checkoutFile();
}

?>