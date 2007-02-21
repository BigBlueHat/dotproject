<?php
/* $Id$ */

/** {{{ Copyright (c) 2003-2005 The dotProject Development Team <core-developers@dotproject.net>
 *
 *  This file is part of dotProject.

 *  dotProject is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  dotProject is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with dotProject; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @license		http://www.gnu.org/licenses/gpl.txt GNU Public License (GPL)
 * @copyright	2003-2005 The dotProject Development Team <core-developers@dotproject.net>
 * 
 * @package		dotProject
 * @version		CVS: $Id$
 * }}}
 */

global $baseDir;
global $baseUrl;

$baseDir = dirname(isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : __FILE__);

// automatically define the base url
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ? 'https://' : 'http://';
$baseUrl .= isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : getenv('HTTP_HOST');
$pathInfo = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : getenv('PATH_INFO');
if (@$pathInfo) {
	$baseUrl .= str_replace('\\','/',dirname($pathInfo));
} else {
	$baseUrl .= str_replace('\\','/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : getenv('SCRIPT_NAME')));
}

// If we are at the top level we will have a trailing slash, which we need to remove, otherwise we get invalid URLs for some servers (like IIS)
$baseUrl = preg_replace(':/*$:', '', $baseUrl);

// To avoid the usual problems with registered globals and other hacks, use a define rather
// than a global.  These are also used as a sentinel to stop direct calling of pages that shouldn't be.

define('DP_BASE_DIR', $baseDir);
define('DP_BASE_URL', $baseUrl);

// required includes for start-up
global $dPconfig;
$dPconfig = array();
?>