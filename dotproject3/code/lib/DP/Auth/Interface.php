<?php

interface DP_Auth_Interface
{
	public function authenticate($username, $password);

	public function supported();

	public function displayName();

	public function __get($var);
}
?>
