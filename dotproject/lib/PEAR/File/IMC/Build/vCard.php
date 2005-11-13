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
// | Authors: Paul M. Jones <pjones@ciaweb.net>                           | 
// +----------------------------------------------------------------------+ 
// 
// $Id$ 

/**
 * This class builds a single vCard (version 3.0 or 2.1).
 *
 * General note: we use the terms "set" "add" and "get" as function
 * prefixes.
 * 
 * "Set" means there is only one iteration of a component, and it has
 * only one value repetition, so you set the whole thing at once.
 * 
 * "Add" means eith multiple iterations of a component are allowed, or
 * that there is only one iteration allowed but there can be multiple
 * value repetitions, so you add iterations or repetitions to the current
 * stack.
 * 
 * "Get" returns the full vCard line for a single iteration.
 * 
 * @author Paul M. Jones <pjones@ciaweb.net>
 * @package File_IMC
 */

/**
 * The common builder functions are required
 */
require_once 'File/IMC/Build.php';

/**#@+
 * Part numbers for N components
 */
define('VCARD_N_FAMILY',     0);
define('VCARD_N_GIVEN',      1);
define('VCARD_N_ADDL',       2);
define('VCARD_N_PREFIX',     3);
define('VCARD_N_SUFFIX',     4);
/**#@-*/

/**#@+
 * Part numbers for ADR components
 */
define('VCARD_ADR_POB',      0);
define('VCARD_ADR_EXTEND',   1);
define('VCARD_ADR_STREET',   2);
define('VCARD_ADR_LOCALITY', 3);
define('VCARD_ADR_REGION',   4);
define('VCARD_ADR_POSTCODE', 5);
define('VCARD_ADR_COUNTRY',  6);
/**#@-*/

/**#@+
 * Part numbers for GEO components
 */
define('VCARD_GEO_LAT',      0);
define('VCARD_GEO_LON',      1);
/**#@-*/

class File_IMC_Build_vCard extends File_IMC_Build {

    
   /**
    * Sets the version of the the vCard.  Only one iteration.
    * 
    * @param string $text The text value of the verson text ('3.0' or '2.1').
    * @return mixed Void on success, or a PEAR_Error object on failure.
    * 
    * @access public
    */
    function setVersion($text = '3.0')
    {
        $this->autoparam = 'VERSION';
        if ($text != '3.0' && $text != '2.1') {
            return $this->raiseError(
                'Version must be 3.0 or 2.1 to be valid.',
                FILE_IMC_ERROR_INVALID_VCARD_VERSION);
        } else {
            $this->setValue('VERSION', 0, 0, $text);
        }
    }
    

   /**
    * Validates parameter names and values based on the vCard version
    * (2.1 or 3.0).
    * 
    * @param  string $name The parameter name (e.g., TYPE or ENCODING).
    * @param  string $text The parameter value (e.g., HOME or BASE64).
    * @param  string $comp Optional, the component name (e.g., ADR or
    *                      PHOTO).  Only used for error messaging.
    * @param  string $iter Optional, the iteration of the component. 
    *                      Only used for error messaging.
    * @return mixed        Boolean true if the parameter is valid, or a
    *                      PEAR_Error object if not.
    * 
    * @access public
    */
    function validateParam($name, $text, $comp = null, $iter = null)
    {
        $name = strtoupper($name);
        $text = strtoupper($text);
        
        // all param values must have only the characters A-Z 0-9 and -.
        if (preg_match('/[^a-zA-Z0-9\-]/i', $text)) {
            
            $result = $this->raiseError(
                "vCard [$comp] [$iter] [$name]: The parameter value may contain only a-z, A-Z, 0-9, and dashes (-).",
                FILE_IMC_ERROR_INVALID_PARAM);
        
        } elseif ($this->value['VERSION'][0][0][0] == '2.1') {
            
            // Validate against version 2.1 (pretty strict)
            
            $types = array (
                'DOM', 'INTL', 'POSTAL', 'PARCEL','HOME', 'WORK',
                'PREF', 'VOICE', 'FAX', 'MSG', 'CELL', 'PAGER',
                'BBS', 'MODEM', 'CAR', 'ISDN', 'VIDEO',
                'AOL', 'APPLELINK', 'ATTMAIL', 'CIS', 'EWORLD',
                'INTERNET', 'IBMMAIL', 'MCIMAIL',
                'POWERSHARE', 'PRODIGY', 'TLX', 'X400',
                'GIF', 'CGM', 'WMF', 'BMP', 'MET', 'PMB', 'DIB',
                'PICT', 'TIFF', 'PDF', 'PS', 'JPEG', 'QTIME',
                'MPEG', 'MPEG2', 'AVI',
                'WAVE', 'AIFF', 'PCM',
                'X509', 'PGP'
            );
            
            
            switch ($name) {
            
            case 'TYPE':
                if (! in_array($text, $types)) {
                    $result = $this->raiseError(
                        "vCard 2.1 [$comp] [$iter]: $text is not a recognized TYPE.",
                        FILE_IMC_ERROR_INVALID_PARAM);
                } else {
                    $result = true;
                }
                break;
            
            case 'ENCODING':
                if ($text != '7BIT' &&
                    $text != '8BIT' &&
                    $text != 'BASE64' &&
                    $text != 'QUOTED-PRINTABLE') {
                    $result = $this->raiseError(
                        "vCard 2.1 [$comp] [$iter]: $text is not a recognized ENCODING.",
                        FILE_IMC_ERROR_INVALID_PARAM);
                } else {
                    $result = true;
                }
                break;
            
            case 'CHARSET':
                // all charsets are OK
                $result = true;
                break;
                
            case 'LANGUAGE':
                // all languages are OK
                $result = true;
                break;
            
            case 'VALUE':
                if ($text != 'INLINE' &&
                    $text != 'CONTENT-ID' &&
                    $text != 'CID' &&
                    $text != 'URL' &&
                    $text != 'VCARD') {
                    $result = $this->raiseError(
                        "vCard 2.1 [$comp] [$iter]: $text is not a recognized VALUE.",
                        FILE_IMC_ERROR_INVALID_PARAM);
                } else {
                    $result = true;
                }
                break;
                
            default:
                $result = $this->raiseError(
                    "vCard 2.1 [$comp] [$iter]: $name is an unknown or invalid parameter name.",
                    FILE_IMC_ERROR_INVALID_PARAM);
                break;
            }
            
        } elseif ($this->value['VERSION'][0][0][0] == '3.0') {
            
            // Validate against version 3.0 (pretty lenient)
            
            switch ($name) {
            
            case 'TYPE':
                // all types are OK
                $result = true;
                break;
                
            case 'LANGUAGE':
                // all languages are OK
                $result = true;
                break;
            
            case 'ENCODING':
                if ($text != '8BIT' &&
                    $text != 'B') {
                    $result = $this->raiseError(
                        "vCard 3.0 [$comp] [$iter]: The only allowed ENCODING parameters are 8BIT and B.",
                        FILE_IMC_ERROR_INVALID_PARAM);
                } else {
                    $result = true;
                }
                break;
            
            case 'VALUE':
                if ($text != 'BINARY' &&
                    $text != 'PHONE-NUMBER' &&
                    $text != 'TEXT' &&
                    $text != 'URI' &&
                    $text != 'UTC-OFFSET' &&
                    $text != 'VCARD') {
                    $result = $this->raiseError(
                        "vCard 3.0 [$comp] [$iter]: The only allowed VALUE parameters are BINARY, PHONE-NUMBER, TEXT, URI, UTC-OFFSET, and VCARD.",
                        FILE_IMC_ERROR_INVALID_PARAM);
                } else {
                    $result = true;
                }
                break;
            
            default:
                $result = $this->raiseError(
                    "vCard 3.0 [$comp] [$iter]: Unknown or invalid parameter name ($name).",
                    FILE_IMC_ERROR_INVALID_PARAM);
                break;
                
            }
            
        } else {
        
            $result = $this->raiseError(
                "[$comp] [$iter] Unknown vCard version number or other error.",
                FILE_IMC_ERROR);
        
        }
        
        return $result;
            
    }
    
    /**
    * 
    * Sets the full N component of the vCard.  Will replace all other
    * values.  There can only be one N component per vCard.
    * 
    * @access public
    * 
    * @param mixed $family Single (string) or multiple (array)
    * family/last name.
    * 
    * @param mixed $given Single (string) or multiple (array)
    * given/first name.
    * 
    * @param mixed $addl Single (string) or multiple (array)
    * additional/middle name.
    * 
    * @param mixed $prefix Single (string) or multiple (array) honorific
    * prefix such as Mr., Miss, etc.
    * 
    * @param mixed $suffix Single (string) or multiple (array) honorific
    * suffix such as III, Jr., Ph.D., etc.
    * 
    * @return void
    * 
    */
    
    function setName($family, $given, $addl, $prefix, $suffix)
    {
        $this->autoparam = 'N';
        $this->setValue('N', 0, VCARD_N_FAMILY, $family);
        $this->setValue('N', 0, VCARD_N_GIVEN, $given);
        $this->setValue('N', 0, VCARD_N_ADDL, $addl);
        $this->setValue('N', 0, VCARD_N_PREFIX, $prefix);
        $this->setValue('N', 0, VCARD_N_SUFFIX, $suffix);
    }
    
    
    /**
    * 
    * Gets back the full N component (first iteration only, since there
    * can only be one N component per vCard).
    * 
    * @access public
    * 
    * @return string The first N component-interation of the vCard.
    * 
    */
    
    function getName()
    {
        return $this->getMeta('N', 0) .
            $this->getValue('N', 0, VCARD_N_FAMILY) . ';' .
            $this->getValue('N', 0, VCARD_N_GIVEN) . ';' .
            $this->getValue('N', 0, VCARD_N_ADDL) . ';' .
            $this->getValue('N', 0, VCARD_N_PREFIX) . ';' .
            $this->getValue('N', 0, VCARD_N_SUFFIX);
    }
    
    
    
    /**
    * 
    * Sets the FN component of the card.  If no text is passed as the
    * FN value, constructs an FN automatically from N components.  There
    * is only one FN iteration per vCard.
    * 
    * @access public
    * 
    * @param string $text Override the automatic generation of FN from N
    * elements with the specified text.
    * 
    * @return mixed Void on success, or a PEAR_Error object on failure.
    * 
    */
    
    function setFormattedName($text = null)
    {
        $this->autoparam = 'FN';
        
        if ($text === null) {
            
            // no text was specified for the FN, so build it
            // from the current N components if an N exists
            if (is_array($this->value['N'])) {
                
                // build from N.
                // first (given) name, first iteration, first repetition
                $text .= $this->getValue('N', 0, VCARD_N_GIVEN, 0);
            
                // add a space after, if there was text
                if ($text != '') {
                    $text .= ' ';
                }
                
                // last (family) name, first iteration, first repetition
                $text .= $this->getValue('N', 0, VCARD_N_FAMILY, 0);
                
                // add a space after, if there was text
                if ($text != '') {
                    $text .= ' ';
                }
                
                // last-name suffix, first iteration, first repetition
                $text .= $this->getValue('N', 0, VCARD_N_SUFFIX, 0);
                
                
            } else {
                
                // no N exists, and no FN was set, so return.
                return $this->raiseError(
                    'FN not specified and N not set; cannot set FN.',
                    FILE_IMC_ERROR_PARAM_NOT_SET);
                
            }
        
        }
        
        $this->setValue('FN', 0, 0, $text);
        
    }
    
    
    /**
    * 
    * Gets back the full FN component value.  Only ever returns iteration
    * zero, because only one FN component is allowed per vCard.
    * 
    * @access public
    * 
    * @return string The FN value of the vCard.
    * 
    */
    
    function getFormattedName()
    {
        return $this->getMeta('FN', 0) . $this->getValue('FN', 0, 0);
    }
    
    
    /**
    * 
    * Sets the data-source of the the vCard.  Only one iteration.
    * 
    * @access public
    * 
    * @param string $text The text value of the data-source text.
    * 
    * @return void
    * 
    */
    
    function setSource($text)
    {
        $this->autoparam = 'SOURCE';
        $this->setValue('SOURCE', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the data-source of the the vCard.  Only one iteration.
    * 
    * @access public
    * 
    * @return string The data-source of the vCard.
    * 
    */
    
    function getSource()
    {
        return $this->getMeta('SOURCE', 0) .
            $this->getValue('SOURCE', 0, 0);
    }
    
    
    /**
    * 
    * Sets the displayed name of the vCard data-source.  Only one iteration.
    * If no name is specified, copies the value of SOURCE.
    * 
    * @access public
    * 
    * @param string $text The text value of the displayed data-source
    * name.  If null, copies the value of SOURCE.
    * 
    * @return mixed Void on success, or a PEAR_Error object on failure.
    * 
    */
    
    function setSourceName($text = null)
    {
        $this->autoparam = 'NAME';
        
        if ($text === null) {
            if (is_array($this->value['SOURCE'])) {
                $text = $this->getValue('SOURCE', 0, 0);
            } else {
                return $this->raiseError(
                    'NAME not specified and SOURCE not set; cannot set NAME.',
                    FILE_IMC_ERROR_PARAM_NOT_SET);
            }
        }
        
        $this->setValue('NAME', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the displayed data-source name of the the vCard.  Only
    * one iteration.
    * 
    * @access public
    * 
    * @return string The data-source name of the vCard.
    * 
    */
    
    function getSourceName()
    {
        return $this->getMeta('NAME', 0) .
            $this->getValue('NAME', 0, 0);
    }
    
    
    
    
    /**
    * 
    * Sets the value of the PHOTO component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setPhoto($text)
    {
        $this->autoparam = 'PHOTO';
        $this->setValue('PHOTO', 0, 0, $text);
    }
    
    
    
    /**
    * 
    * Gets back the value of the PHOTO component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getPhoto()
    {
        return $this->getMeta('PHOTO') .
            $this->getValue('PHOTO', 0, 0);
    }
    
    
    
    
    /**
    * 
    * Sets the value of the LOGO component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setLogo($text)
    {
        $this->autoparam = 'LOGO';
        $this->setValue('LOGO', 0, 0, $text);
    }
    
    
    
    /**
    * 
    * Gets back the value of the LOGO component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getLogo()
    {
        return $this->getMeta('LOGO') . $this->getValue('LOGO', 0, 0);
    }
    
    
    
    /**
    * 
    * Sets the value of the SOUND component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setSound($text)
    {
        $this->autoparam = 'SOUND';
        $this->setValue('SOUND', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the SOUND component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getSound()
    {
        return $this->getMeta('SOUND') .
            $this->getValue('SOUND', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the KEY component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setKey($text)
    {
        $this->autoparam = 'KEY';
        $this->setValue('KEY', 0, 0, $text);
    }
    
    
    
    /**
    * 
    * Gets back the value of the KEY component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getKey()
    {
        return $this->getMeta('KEY') . $this->getValue('KEY', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the BDAY component.  There is only one allowed
    * per vCard. Date format is "yyyy-mm-dd[Thh:ii[:ss[Z|-06:00]]]".
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    
    function setBirthday($text)
    {
        $this->autoparam = 'BDAY';
        $this->setValue('BDAY', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the BDAY component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getBirthday()
    {
        return $this->getMeta('BDAY') . $this->getValue('BDAY', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the TZ component.  There is only one allowed per
    * vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setTZ($text)
    {
        $this->autoparam = 'TZ';
        $this->setValue('TZ', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the TZ component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getTZ()
    {
        return $this->getMeta('TZ') . $this->getValue('TZ', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the MAILER component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setMailer($text)
    {
        $this->autoparam = 'MAILER';
        $this->setValue('MAILER', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the MAILER component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getMailer()
    {
        return $this->getMeta('MAILER') .
            $this->getValue('MAILER', 0, 0);
    }
    
    /**
    * 
    * Sets the value of the NOTE component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setNote($text)
    {
        $this->autoparam = 'NOTE';
        $this->setValue('NOTE', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the NOTE component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getNote()
    {
        return $this->getMeta('NOTE') . $this->getValue('NOTE', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the TITLE component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setTitle($text)
    {
        $this->autoparam = 'TITLE';
        $this->setValue('TITLE', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the TITLE component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getTitle()
    {
        return $this->getMeta('TITLE') .
            $this->getValue('TITLE', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the ROLE component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setRole($text)
    {
        $this->autoparam = 'ROLE';
        $this->setValue('ROLE', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the ROLE component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getRole()
    {
        return $this->getMeta('ROLE') . $this->getValue('ROLE', 0, 0);
    }
    
    
    
    
    /**
    * 
    * Sets the value of the URL component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setURL($text)
    {
        $this->autoparam = 'URL';
        $this->setValue('URL', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the URL component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getURL()
    {
        return $this->getMeta('URL') . $this->getValue('URL', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the CLASS component.  There is only one allowed
    * per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setClass($text)
    {
        $this->autoparam = 'CLASS';
        $this->setValue('CLASS', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the CLASS component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getClass()
    {
        return $this->getMeta('CLASS') .
            $this->getValue('CLASS', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the SORT-STRING component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function setSortString($text)
    {
        $this->autoparam = 'SORT-STRING';
        $this->setValue('SORT-STRING', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the SORT-STRING component.  There is only
    * one allowed per vCard.
    * 
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getSortString()
    {
        return $this->getMeta('SORT-STRING') .
            $this->getValue('SORT-STRING', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the PRODID component.  There is only one allowed
    * per vCard.
    * 
    * @access public
    * 
    * @param string $text The value to set for this component.
    * 
    * @return void
    * 
    */
    
    function setProductID($text)
    {
        $this->autoparam = 'PRODID';
        $this->setValue('PRODID', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the PRODID component.  There is only one
    * allowed per vCard.
    * 
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getProductID()
    {
        return $this->getMeta('PRODID') .
            $this->getValue('PRODID', 0, 0);
    }
    
    
    
    
    /**
    * 
    * Sets the value of the REV component.  There is only one allowed
    * per vCard.
    * 
    * @access public
    * 
    * @param string $text The value to set for this component.
    * 
    * @return void
    * 
    */
    
    function setRevision($text)
    {
        $this->autoparam = 'REV';
        $this->setValue('REV', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the REV component.  There is only one
    * allowed per vCard.
    * 
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getRevision()
    {
        return $this->getMeta('REV') . $this->getValue('REV', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the UID component.  There is only one allowed
    * per vCard.
    * 
    * @access public
    * 
    * @param string $text The value to set for this component.
    * 
    * @return void
    * 
    */
    
    function setUniqueID($text)
    {
        $this->autoparam = 'UID';
        $this->setValue('UID', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the UID component.  There is only one
    * allowed per vCard.
    * 
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getUniqueID()
    {
        return $this->getMeta('UID') . $this->getValue('UID', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of the AGENT component.  There is only one allowed
    * per vCard.
    * 
    * @access public
    * 
    * @param string $text The value to set for this component.
    * 
    * @return void
    * 
    */
    
    function setAgent($text)
    {
        $this->autoparam = 'AGENT';
        $this->setValue('AGENT', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the AGENT component.  There is only one
    * allowed per vCard.
    * 
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getAgent()
    {
        return $this->getMeta('AGENT') .
            $this->getValue('AGENT', 0, 0);
    }
    
    
    /**
    * 
    * Sets the value of both parts of the GEO component.  There is only
    * one GEO component allowed per vCard.
    * 
    * @access public
    * 
    * @param string $lat The value to set for the longitude part
    * (decimal, + or -).
    * 
    * @param string $lon The value to set for the latitude part
    * (decimal, + or -).
    * 
    * @return void
    * 
    */
    
    function setGeo($lat, $lon)
    {
        $this->autoparam = 'GEO';
        $this->setValue('GEO', 0, VCARD_GEO_LAT, $lat);
        $this->setValue('GEO', 0, VCARD_GEO_LON, $lon);
    }
    
    
    /**
    * 
    * Gets back the value of the GEO component.  There is only one
    * allowed per vCard.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getGeo()
    {
        return $this->getMeta('GEO', 0) .
            $this->getValue('GEO', 0, VCARD_GEO_LAT, 0) . ';' .
            $this->getValue('GEO', 0, VCARD_GEO_LON, 0);
    }

    /**
    * 
    * Sets the value of one entire ADR iteration.  There can be zero,
    * one, or more ADR components in a vCard.
    *
    * @access public
    * 
    * @param mixed $pob String (one repetition) or array (multiple
    * reptitions) of the p.o. box part of the ADR component iteration.
    * 
    * @param mixed $extend String (one repetition) or array (multiple
    * reptitions) of the "extended address" part of the ADR component
    * iteration.
    * 
    * @param mixed $street String (one repetition) or array (multiple
    * reptitions) of the street address part of the ADR component
    * iteration.
    * 
    * @param mixed $locality String (one repetition) or array (multiple
    * reptitions) of the locailty (e.g., city) part of the ADR component
    * iteration.
    * 
    * @param mixed $region String (one repetition) or array (multiple
    * reptitions) of the region (e.g., state, province, or governorate)
    * part of the ADR component iteration.
    * 
    * @param mixed $postcode String (one repetition) or array (multiple
    * reptitions) of the postal code (e.g., ZIP code) part of the ADR
    * component iteration.
    * 
    * @param mixed $country String (one repetition) or array (multiple
    * reptitions) of the country-name part of the ADR component
    * iteration.
    * 
    * @return void
    * 
    */
    
    function addAddress($pob, $extend, $street, $locality, $region,
        $postcode, $country)
    {
        $this->autoparam = 'ADR';
        $iter = count($this->value['ADR']);
        $this->setValue('ADR', $iter, VCARD_ADR_POB,       $pob);
        $this->setValue('ADR', $iter, VCARD_ADR_EXTEND,    $extend);
        $this->setValue('ADR', $iter, VCARD_ADR_STREET,    $street);
        $this->setValue('ADR', $iter, VCARD_ADR_LOCALITY,  $locality);
        $this->setValue('ADR', $iter, VCARD_ADR_REGION,    $region);
        $this->setValue('ADR', $iter, VCARD_ADR_POSTCODE,  $postcode);
        $this->setValue('ADR', $iter, VCARD_ADR_COUNTRY,   $country);
    }
    
    
    /**
    * 
    * Gets back the value of one ADR component iteration.
    *
    * @access public
    * 
    * @param int $iter The component iteration-number to get the value
    * for.
    * 
    * @return mixed The value of this component iteration, or a
    * PEAR_Error if the iteration is not valid.
    * 
    */
    
    function getAddress($iter)
    {
        if (! is_integer($iter) || $iter < 0) {
            
            return $this->raiseError(
                'ADR iteration number not valid.',
                FILE_IMC_ERROR_INVALID_ITERATION);
        
        } else {
            
            return $this->getMeta('ADR', $iter) .
                $this->getValue('ADR', $iter, VCARD_ADR_POB) . ';' .
                $this->getValue('ADR', $iter, VCARD_ADR_EXTEND) . ';' .
                $this->getValue('ADR', $iter, VCARD_ADR_STREET) . ';' .
                $this->getValue('ADR', $iter, VCARD_ADR_LOCALITY) . ';' .
                $this->getValue('ADR', $iter, VCARD_ADR_REGION) . ';' .
                $this->getValue('ADR', $iter, VCARD_ADR_POSTCODE) . ';' .
                $this->getValue('ADR', $iter, VCARD_ADR_COUNTRY);
        }
    }
    
    
    /**
    * 
    * Sets the value of one LABEL component iteration.  There can be
    * zero, one, or more component iterations in a vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function addLabel($text)
    {
        $this->autoparam = 'LABEL';
        $iter = count($this->value['LABEL']);
        $this->setValue('LABEL', $iter, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of one iteration of the LABEL component. 
    * There can be zero, one, or more component iterations in a vCard.
    *
    * @access public
    * 
    * @param int $iter The component iteration-number to get the value
    * for.
    *
    * @return mixed The value of this component, or a PEAR_Error if
    * the iteration number is not valid.
    * 
    */
    
    function getLabel($iter)
    {
        if (! is_integer($iter) || $iter < 0) {
            return $this->raiseError(
                'LABEL iteration number not valid.',
                FILE_IMC_ERROR_INVALID_ITERATION);
        } else {
            return $this->getMeta('LABEL', $iter) .
                $this->getValue('LABEL', $iter, 0);
        }
    }
    
    
    /**
    * 
    * Sets the value of one TEL component iteration.  There can be zero,
    * one, or more component iterations in a vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function addTelephone($text)
    {
        $this->autoparam = 'TEL';
        $iter = count($this->value['TEL']);
        $this->setValue('TEL', $iter, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of one iteration of the TEL component.  There
    * can be zero, one, or more component iterations in a vCard.
    *
    * @access public
    * 
    * @param int $iter The component iteration-number to get the value
    * for.
    *
    * @return mixed The value of this component, or a PEAR_Error if the
    * iteration number is not valid.
    * 
    */
    
    function getTelephone($iter)
    {
        if (! is_integer($iter) || $iter < 0) {
            return $this->raiseError(
                'TEL iteration number not valid.',
                FILE_IMC_ERROR_INVALID_ITERATION);
        } else {
            return $this->getMeta('TEL', $iter) .
                $this->getValue('TEL', $iter, 0);
        }
    }
    
    /**
    * 
    * Sets the value of one EMAIL component iteration.  There can be zero,
    * one, or more component iterations in a vCard.
    *
    * @access public
    * 
    * @param string $text The value to set for this component.
    *
    * @return void
    * 
    */
    
    function addEmail($text)
    {
        $this->autoparam = 'EMAIL';
        $iter = count($this->value['EMAIL']);
        $this->setValue('EMAIL', $iter, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of one iteration of the EMAIL component.  There can
    * be zero, one, or more component iterations in a vCard.
    *
    * @access public
    * 
    * @param int $iter The component iteration-number to get the value
    * for.
    *
    * @return mixed The value of this component, or a PEAR_Error if the
    * iteration number is not valid.
    * 
    */
    
    function getEmail($iter)
    {
        if (! is_integer($iter) || $iter < 0) {
            return $this->raiseError(
                'EMAIL iteration number not valid.',
                FILE_IMC_ERROR_INVALID_ITERATION);
        } else {
            return $this->getMeta('EMAIL', $iter) .
                $this->getValue('EMAIL', $iter, 0);
        }
    }
    
    
    /**
    * 
    * Sets the full value of the NICKNAME component.  There is only one
    * component iteration allowed per vCard, but there may be multiple
    * value repetitions in the iteration.
    *
    * @access public
    * 
    * @param mixed $text String (one repetition) or array (multiple
    * reptitions) of the component iteration value.
    *
    * @return void
    * 
    */
    
    function addNickname($text)
    {
        $this->autoparam = 'NICKNAME';
        $this->addValue('NICKNAME', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the NICKNAME component.  There is only one
    * component allowed per vCard, but there may be multiple value
    * repetitions in the iteration.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getNickname()
    {
        return $this->getMeta('NICKNAME') .
            $this->getValue('NICKNAME', 0, 0);
    }
    
    
    
    /**
    * 
    * Sets the full value of the CATEGORIES component.  There is only
    * one component iteration allowed per vCard, but there may be
    * multiple value repetitions in the iteration.
    *
    * @access public
    * 
    * @param mixed $text String (one repetition) or array (multiple
    * reptitions) of the component iteration value.
    *
    * @return void
    * 
    */
    
    function addCategories($text, $append = true)
    {
        $this->autoparam = 'CATEGORIES';
        $this->addValue('CATEGORIES', 0, 0, $text);
    }
    
    
    /**
    * 
    * Gets back the value of the CATEGORIES component.  There is only
    * one component allowed per vCard, but there may be multiple value
    * repetitions in the iteration.
    *
    * @access public
    * 
    * @return string The value of this component.
    * 
    */
    
    function getCategories()
    {
        return $this->getMeta('CATEGORIES', 0) .
            $this->getValue('CATEGORIES', 0, 0);
    }
    
    
    /**
    * 
    * Sets the full value of the ORG component.  There can be only one
    * ORG component in a vCard.
    * 
    * The ORG component can have one or more parts (as opposed to
    * repetitions of values within those parts).  The first part is the
    * highest-level organization, the second part is the next-highest,
    * the third part is the third-highest, and so on.  There can by any
    * number of parts in one ORG iteration.  (This is different from
    * other components, such as NICKNAME, where an iteration has only
    * one part but may have many repetitions within that part.)
    * 
    * @access public
    * 
    * @param mixed $text String (one ORG part) or array (of ORG
    * parts) to use as the value for the component iteration.
    * 
    * @return void
    * 
    */
    
    function addOrganization($text)
    {
        $this->autoparam = 'ORG';
        
        settype($text, 'array');
        
        $base = count($this->value['ORG'][0]);
        
        // start at the original base point, and add
        // new parts
        foreach ($text as $part => $val) {
            $this->setValue('ORG', 0, $base + $part, $val);
        }
    }
    
    
    /**
    * 
    * Gets back the value of the ORG component.
    * 
    * @return string The value of this component.
    * 
    */
    
    function getOrganization()
    {
        $text = $this->getMeta('ORG', 0);
        
        $k = count($this->value['ORG'][0]);
        $last = $k - 1;
        
        for ($part = 0; $part < $k; $part++) {
        
            $text .= $this->getValue('ORG', 0, $part);
            
            if ($part != $last) {
                $text .= ';';
            }
            
        }
        
        return $text;
    }
    
    /**
    *
    * Fetches a full vCard text block based on $this->value and
    * $this->param. The order of the returned components is similar to
    * their order in RFC 2426.  Honors the value of
    * $this->value['VERSION'] to determine which vCard components are
    * returned (2.1- or 3.0-compliant).
    *
    * @access public
    * @return string A properly formatted vCard text block.
    *
    */
    
    function fetch()
    {
        // vCard version is required
        if (! is_array($this->value['VERSION'])) {
            return $this->raiseError(
                'VERSION not set (required).',
                FILE_IMC_ERROR_PARAM_NOT_SET);
        }
        
        // FN component is required
        if (! is_array($this->value['FN'])) {
            return $this->raiseError(
                'FN component not set (required).',
                FILE_IMC_ERROR_PARAM_NOT_SET);
        }
        
        // N component is required
        if (! is_array($this->value['N'])) {
            return $this->raiseError(
                'N component not set (required).',
                FILE_IMC_ERROR_PARAM_NOT_SET);
        }
        
        // initialize the vCard lines
        $lines = array();
        
        // begin (required)
        $lines[] = "BEGIN:VCARD";
        
        // version (required)
        // available in both 2.1 and 3.0
        $lines[] = $this->getVersion();
        
        // formatted name (required)
        // available in both 2.1 and 3.0
        $lines[] = $this->getFormattedName();
        
        // structured name (required)
        // available in both 2.1 and 3.0
        $lines[] = $this->getName();
        
        // profile (3.0 only)
        if ($this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = "PROFILE:VCARD";
        }
        
        // displayed name of the data source  (3.0 only)
        if (is_array($this->value['NAME']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getSourceName();
        }
        
        // data source (3.0 only)
        if (is_array($this->value['SOURCE']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getSource();
        }
        
        // nicknames (3.0 only)
        if (is_array($this->value['NICKNAME']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getNickname();
        }
        
        // personal photo
        // available in both 2.1 and 3.0
        if (is_array($this->value['PHOTO'])) {
            $lines[] = $this->getPhoto();
        }
        
        // bday
        // available in both 2.1 and 3.0
        if (is_array($this->value['BDAY'])) {
            $lines[] = $this->getBirthday();
        }
        
        // adr
        // available in both 2.1 and 3.0
        if (is_array($this->value['ADR'])) {
            foreach ($this->value['ADR'] as $key => $val) {
                $lines[] = $this->getAddress($key);
            }
        }
        
        // label
        // available in both 2.1 and 3.0
        if (is_array($this->value['LABEL'])) {
            foreach ($this->value['LABEL'] as $key => $val) {
                $lines[] = $this->getLabel($key);
            }
        }
        
        // tel
        // available in both 2.1 and 3.0
        if (is_array($this->value['TEL'])) {
            foreach ($this->value['TEL'] as $key => $val) {
                $lines[] = $this->getTelephone($key);
            }
        }
        
        // email
        // available in both 2.1 and 3.0
        if (is_array($this->value['EMAIL'])) {
            foreach ($this->value['EMAIL'] as $key => $val) {
                $lines[] = $this->getEmail($key);
            }
        }
        
        // mailer
        // available in both 2.1 and 3.0
        if (is_array($this->value['MAILER'])) {
            $lines[] = $this->getMailer();
        }
        
        // tz
        // available in both 2.1 and 3.0
        if (is_array($this->value['TZ'])) {
            $lines[] = $this->getTZ();
        }
        
        // geo
        // available in both 2.1 and 3.0
        if (is_array($this->value['GEO'])) {
            $lines[] = $this->getGeo();
        }
        
        // title
        // available in both 2.1 and 3.0
        if (is_array($this->value['TITLE'])) {
            $lines[] = $this->getTitle();
        }
        
        // role
        // available in both 2.1 and 3.0
        if (is_array($this->value['ROLE'])) {
            $lines[] = $this->getRole();
        }
        
        // company logo
        // available in both 2.1 and 3.0
        if (is_array($this->value['LOGO'])) {
            $lines[] = $this->getLogo();
        }
        
        // agent
        // available in both 2.1 and 3.0
        if (is_array($this->value['AGENT'])) {
            $lines[] = $this->getAgent();
        }
        
        // org
        // available in both 2.1 and 3.0
        if (is_array($this->value['ORG'])) {
            $lines[] = $this->getOrganization();
        }
        
        // categories (3.0 only)
        if (is_array($this->value['CATEGORIES']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getCategories();
        }
        
        // note
        // available in both 2.1 and 3.0
        if (is_array($this->value['NOTE'])) {
            $lines[] = $this->getNote();
        }
        
        // prodid (3.0 only)
        if (is_array($this->value['PRODID']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getProductID();
        }
        
        // rev
        // available in both 2.1 and 3.0
        if (is_array($this->value['REV'])) {
            $lines[] = $this->getRevision();
        }
        
        // sort-string (3.0 only)
        if (is_array($this->value['SORT-STRING']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getSortString();
        }
        
        // name-pronounciation sound
        // available in both 2.1 and 3.0
        if (is_array($this->value['SOUND'])) {
            $lines[] = $this->getSound();
        }
        
        // uid
        // available in both 2.1 and 3.0
        if (is_array($this->value['UID'])) {
            $lines[] = $this->getUniqueID();
        }
        
        // url
        // available in both 2.1 and 3.0
        if (is_array($this->value['URL'])) {
            $lines[] = $this->getURL();
        }
        
        // class (3.0 only)
        if (is_array($this->value['CLASS']) &&
            $this->value['VERSION'][0][0][0] == '3.0') {
            $lines[] = $this->getClass();
        }
        
        // key
        // available in both 2.1 and 3.0
        if (is_array($this->value['KEY'])) {
            $lines[] = $this->getKey();
        }
        
        // required
        $lines[] = "END:VCARD";
        
        // version 3.0 uses \n for new lines,
        // version 2.1 uses \r\n
        $newline = "\n";
        if ($this->value['VERSION'][0][0][0] == '2.1') {
            $newline = "\r\n";
        }
        
        // fold lines at 75 characters
        $regex = "(.{1,75})";
           foreach ($lines as $key => $val) {
            if (strlen($val) > 75) {
                // we trim to drop the last newline, which will be added
                // again by the implode function at the end of fetch()
                $lines[$key] = trim(preg_replace("/$regex/i", "\\1$newline ", $val));
            }
        }
        
        // compile the array of lines into a single text block
        // and return
        return implode($newline, $lines);
    }
    
}

?>
