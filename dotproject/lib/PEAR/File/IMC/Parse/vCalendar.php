<?php
/* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4: */ 
// +----------------------------------------------------------------------+ 
// | PHP version 4                                                        | 
// +----------------------------------------------------------------------+ 
// | Copyright (c) 1997-2002 The PHP Group                                | 
// +----------------------------------------------------------------------+ 
// | This source file is subject to version 2.0 of the PHP license,       | 
// | that is bundled with this package in the file LICENSE, and is        | 
// | available at through the world-wide-web at                           | 
// | http://www.php.net/license/2_02.txt.                                 | 
// | If you did not receive a copy of the PHP license and are unable to   | 
// | obtain it through the world-wide-web, please send a note to          | 
// | license@php.net so we can mail you a copy immediately.               | 
// +----------------------------------------------------------------------+ 
// | Authors: Marshall Roch <mroch@php.net>                               | 
// +----------------------------------------------------------------------+ 
// 
// $Id$

/**
*
* Parser for vCalendars.
*
* This class parses vCalendar sources from file or text into a
* structured array.
*
* Usage:
*
* <code>
*     // include this class file
*     require_once 'File/IMC.php';
*
*     // instantiate a parser object
*     $parse = new File_IMC::parse('vCalendar');
*
*     // parse a vCalendar file and store the data
*     // in $calinfo
*     $calinfo = $parse->fromFile('sample.vcs');
*
*     // view the card info array
*     echo '<pre>';
*     print_r($calinfo);
*     echo '</pre>';
* </code>
*
*
* @author Paul M. Jones <pjones@ciaweb.net>
* @package File_IMC
*/

/**
 * The common IMC parser is needed
 */
require_once 'File/IMC/Parse.php';

class File_IMC_Parse_vCalendar extends File_IMC_Parse {

    // nothing has needed parsing so far.

}

?>
