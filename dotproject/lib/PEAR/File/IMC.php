<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.0 of the PHP license,       |
// | that is bundled with this package in the file LICENSE, and is        |
// | available through the world-wide-web at                              |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Authors: Paul M. Jones <pmjones@ciaweb.net>                          |
// |          Marshall Roch <mroch@php.net>                               |
// +----------------------------------------------------------------------+
//
// $Id$

define('FILE_IMC_ERROR',                        100);
define('FILE_IMC_ERROR_INVALID_DRIVER',         101);
define('FILE_IMC_ERROR_INVALID_PARAM',          102);
define('FILE_IMC_ERROR_INVALID_VCARD_VERSION',  103);
define('FILE_IMC_ERROR_PARAM_NOT_SET',          104);
define('FILE_IMC_ERROR_INVALID_ITERATION',      105);

/**
 * This class handles vCard and vCalendar files, formats designed by the
 * Internet Mail Consortium (IMC).
 *
 * vCard automates the exchange of personal information typically found 
 * on a traditional business card. vCard is used in applications such as
 * Internet mail, voice mail, Web browsers, telephony applications, call 
 * centers, video conferencing, PIMs (Personal Information Managers), 
 * PDAs (Personal Data Assistants), pagers, fax, office equipment, and 
 * smart cards.
 *
 * vCalendar defines a transport and platform-independent format for 
 * exchanging calendaring and scheduling information in an easy, automated, 
 * and consistent manner. It captures information about event and "to-do" 
 * items that are normally used by applications such as a personal 
 * information managers (PIMs) and group schedulers. Programs that use 
 * vCalendar can exchange important data about events so that you can 
 * schedule meetings with anyone who has a vCalendar-aware program.
 *
 * This class is capable of building and parsing vCard 2.1, vCard 3.0, and 
 * vCalendar files.  The vCard code was moved from Contact_Vcard_Build 
 * and Contact_Vcard_Parse, and the API remains basically the same.
 * The only difference is that this package uses a factory pattern:
 *
 * <code>
 *     $parse = File_IMC::parse('vCard');
 *     $build = File_IMC::build('vCard', '3.0');
 * </code>
 * instead of
 * <code>
 *     $parse = new Contact_Vcard_Parse();
 *     $build = new Contact_Vcard_Build('3.0');
 * </code>
 *
 * @link http://www.imc.org/pdi/ IMC's vCard and vCalendar info page
 * @package File_IMC
 * @author Paul M. Jones <pmjones@ciaweb.net>
 * @author Marshall Roch <mroch@php.net>
 */
class File_IMC {

   /**
    * Builder factory
    *
    * Creates an instance of the correct parser class, based on the
    * parameter passed. For example, File_IMC::parse('vCard') creates
    * a new object to parse a vCard file.
    *
    * @param string Type of file to parse, vCard or vCalendar
    * @return object
    */
    function build($format, $version = null)
    {
        $filename = 'File/IMC/Build/'. $format . '.php';
        $classname = 'File_IMC_Build_'. $format;
    
        if (!file_exists($filename)) {
            return File_IMC::raiseError(
                'No builder driver exists for format: ' . $format,
                FILE_IMC_ERROR_INVALID_DRIVER);
        }

        include_once $filename;

        if (!class_exists($classname)) {
            return File_IMC::raiseError(
                'No builder driver exists for format: ' . $format,
                FILE_IMC_ERROR_INVALID_DRIVER);
        }

        if ($version !== null) {
            $class = new $classname($version);    
        } else {
            $class = new $classname;
        }
        
        return $class;
    }

   /**
    * Parser factory
    *
    * Creates an instance of the correct parser class, based on the
    * parameter passed. For example, File_IMC::parse('vCard') creates
    * a new object to parse a vCard file.
    *
    * @param string Type of file to parse, vCard or vCalendar
    * @return object
    */
    function parse($format)
    {
        $filename = 'File/IMC/Parse/'. $format . '.php';
        $classname = 'File_IMC_Parse_'. $format;

        if (!file_exists($filename)) {
            return File_IMC::raiseError(
                'No builder driver exists for format: ' . $format,
                FILE_IMC_ERROR_INVALID_DRIVER);
        }

        include_once $filename;
        
        if (!class_exists($classname)) {
            return File_IMC::raiseError(
                'No parser driver exists for format: ' . $format, 
                FILE_IMC_ERROR_INVALID_DRIVER);
        }
        
        $class = new $classname;
        
        return $class;
    }
    
   /**
    * Raises a PEAR error message
    *
    * Returns a PEAR_Error.  Doing it this way instead of extending
    * the PEAR class means that the extra overhead of the PEAR class
    * is only included when needed to throw an error.
    *
    * @param string Error message to display
    * @param int    Error code
    * @return object PEAR_Error
    */
    function raiseError($msg, $code)
    {
        include_once 'PEAR.php';
        
        return PEAR::raiseError($msg, $code, PEAR_ERROR_PRINT);
    }

}

?>
