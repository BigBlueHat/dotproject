<?php /* $Id$ */

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

// Timings init
$time = array_sum(explode(' ', microtime()));
$acltime = 0;
$dbtime = 0;
$dbqueries = 0;

ini_set('display_errors', 1); // Ensure errors get to the user.
error_reporting(E_ALL & ~E_NOTICE);
// If you experience a 'white screen of death' or other problems,
// uncomment the following line of code:
//error_reporting( E_ALL );

require_once 'base.php';

clearstatcache();
if (is_file(DP_BASE_DIR . '/includes/config.php')) {
    require_once DP_BASE_DIR . '/includes/config.php';
} else {
    // Application not initialised - forwarding to installation procedures.
    include_once DP_BASE_DIR . '/classes/template.class.php';
    $tpl = new CTemplate();
    $tpl->init();
    $tpl->displayFile('install', '.');
    exit();
}

if (!isset($GLOBALS['OS_WIN'])) {
    $GLOBALS['OS_WIN'] = (stristr(PHP_OS, 'WIN') !== false);
}

// tweak for pathname consistence on windows machines
require_once DP_BASE_DIR . '/includes/main_functions.php';
require_once DP_BASE_DIR . '/includes/db_adodb.php';
require_once DP_BASE_DIR . '/includes/db_connect.php';
require_once DP_BASE_DIR . '/classes/ui.class.php';
require_once DP_BASE_DIR . '/classes/permissions.class.php';
require_once DP_BASE_DIR . '/includes/session.php';

// manage the session variable(s)
dPsessionStart(array('AppUI'));

// check if session has previously been initialised
if (!isset( $_SESSION['AppUI'] ) || isset($_GET['logout'])) {
    if (isset($_GET['logout']) && isset($_SESSION['AppUI']->user_id)) {
        $AppUI =& $_SESSION['AppUI'];
        $user_id = $AppUI->user_id;
        $details['name'] = $AppUI->user_first_name . ' ' . $AppUI->user_last_name;
        addHistory('login', $AppUI->user_id, 'logout', $details);
        dPsessionDestroy(dPgetConfig('session_name'));
    }
    $_SESSION['AppUI'] = new CAppUI();
}
$AppUI =& $_SESSION['AppUI'];
$AppUI->init();
?>