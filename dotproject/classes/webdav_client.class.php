<?php
// $Id$

/**
 * WebDAV client connection and operations abstraction class.
 *
 *  This class abstracts from a webDAV client class providing 
 *  e.g. GET and PUT methods for webDAV resources.
 *  Please note that only methods needed at creation time 
 *  have been abstracted from the underlying class. However
 *  the base class may be more powerful. Please do not use
 *  methods from the base class directly! Add abstraction code
 *  here instead!
 *
 * @author: gregorerhardt
 *
 * Copyright 2005, the dotProject team.
 */

require_once( $AppUI->getLibraryClass( 'webDAV/class_webdav_client' ) );

class WebDAVclient extends webdav_client {

	function WebDAVclient()
	{
	}
	
	function closeConnection ()
	{
		parent::close();
	}
	
	function getPath ()
	{
		return $this->_path;
	}
	
	function setPath ($path)
	{
		$this->_path = $path;
	}

	function setServer ($server)
	{
		parent::set_server($server);
	}
	
	function setPort ($port)
	{
		parent::set_port($port);
	}

	function setUser ($user)
	{
		parent::set_user($user);
	}
	
	function setPass ($pass)
	{
		parent::set_pass($pass);
	}

	function setProtocol ($prot)
	{
		parent::set_protocol($prot);
	}
	
	function openConnection ()
	{
		$c = parent::open();
		return $c;
	}

	function checkConnection ()
	{
		$c = parent::check_webdav();
		return $c;
	}

	function get ($source, &$buffer)
	{
		$g = parent::get($source, $buffer);
		return $g;
	}
	
	function put ($target, $data)
	{
		$p = parent::put($target, $data);
		return $p;
	}
	
	function ls ($path)
	{
		$list = parent::ls($path);
		return $list;
	}
	
	function iso8601toTimestamp($iso) 
	{
		return parent::iso8601totime($iso);
	}

	function hostInfo($fullserverpath) {
		$info = parse_url($fullserverpath);
		return $info['host'];
	}

	function pathInfo($fullserverpath) {
		$info = parse_url($fullserverpath);
		return $info['path'];
	}

}
?>
