<?php

interface iFileManager {
	public function createFile();
	public function retrieveFile($file);
	public function updateFile();
	public function deleteFile();
	public function summarizeFile();
	public function checkinFile();
	public function checkoutFile();
}

?>