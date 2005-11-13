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
* 
* Parser for vCards.
*
* This class parses vCard 2.1 and 3.0 sources from file or text into a
* structured array.
* 
* Usage:
* 
* <code>
*     // include this class file
*     require_once 'File/IMC.php';
*     
*     // instantiate a parser object
*     $parse = new File_IMC::parse('vCard');
*     
*     // parse a vCard file and store the data
*     // in $cardinfo
*     $cardinfo = $parse->fromFile('sample.vcf');
*     
*     // view the card info array
*     echo '<pre>';
*     print_r($cardinfo);
*     echo '</pre>';
* </code>
* 
*
* @author Paul M. Jones <pjones@ciaweb.net>
* @package File_IMC
* 
*/

/**
 * The common IMC parser is needed
 */
require_once 'File/IMC/Parse.php';

class File_IMC_Parse_vCard extends File_IMC_Parse {

    /**
    *
    * Parses a vCard line value identified as being of the "N"
    * (structured name) type-defintion.
    *
    * @access private
    *
    * @param string $text The right-part (after-the-colon part) of a
    * vCard line.
    * 
    * @return array An array of key-value pairs where the key is the
    * portion-name and the value is the portion-value.  The value itself
    * may be an array as well if multiple comma-separated values were
    * indicated in the vCard source.
    *
    */
    function _parseN($text)
    {
        $tmp = $this->splitBySemi($text);
        return array(
            $this->splitByComma($tmp[0]), // family (last)
            $this->splitByComma($tmp[1]), // given (first)
            $this->splitByComma($tmp[2]), // addl (middle)
            $this->splitByComma($tmp[3]), // prefix
            $this->splitByComma($tmp[4])  // suffix
        );
    }
    
    
    /**
    *
    * Parses a vCard line value identified as being of the "ADR"
    * (structured address) type-defintion.
    *
    * @access private
    *
    * @param string $text The right-part (after-the-colon part) of a
    * vCard line.
    * 
    * @return array An array of key-value pairs where the key is the
    * portion-name and the value is the portion-value.  The value itself
    * may be an array as well if multiple comma-separated values were
    * indicated in the vCard source.
    *
    */
    
    function _parseADR($text)
    {
        $tmp = $this->splitBySemi($text);
        return array(
            $this->splitByComma($tmp[0]), // pob
            $this->splitByComma($tmp[1]), // extend
            $this->splitByComma($tmp[2]), // street
            $this->splitByComma($tmp[3]), // locality (city)
            $this->splitByComma($tmp[4]), // region (state)
            $this->splitByComma($tmp[5]), // postcode (ZIP)
            $this->splitByComma($tmp[6])  // country
        );
    }
    
    
    /**
    * 
    * Parses a vCard line value identified as being of the "NICKNAME"
    * (informal or descriptive name) type-defintion.
    *
    * @access private
    * 
    * @param string $text The right-part (after-the-colon part) of a
    * vCard line.
    * 
    * @return array An array of nicknames.
    *
    */
    
    function _parseNICKNAME($text)
    {
        return array($this->splitByComma($text));
    }
    
    
    /**
    * 
    * Parses a vCard line value identified as being of the "ORG"
    * (organizational info) type-defintion.
    *
    * @access private
    *
    * @param string $text The right-part (after-the-colon part) of a
    * vCard line.
    * 
    * @return array An array of organizations; each element of the array
    * is itself an array, which indicates primary organization and
    * sub-organizations.
    *
    */
    
    function _parseORG($text)
    {
        $tmp = $this->splitbySemi($text);
        $list = array();
        foreach ($tmp as $val) {
            $list[] = array($val);
        }
        
        return $list;
    }
    
    
    /**
    * 
    * Parses a vCard line value identified as being of the "CATEGORIES"
    * (card-category) type-defintion.
    *
    * @access private
    * 
    * @param string $text The right-part (after-the-colon part) of a
    * vCard line.
    * 
    * @return mixed An array of categories.
    *
    */
    
    function _parseCATEGORIES($text)
    {
        return array($this->splitByComma($text));
    }
    
    
    /**
    * 
    * Parses a vCard line value identified as being of the "GEO"
    * (geographic coordinate) type-defintion.
    *
    * @access private
    *
    * @param string $text The right-part (after-the-colon part) of a
    * vCard line.
    * 
    * @return mixed An array of lat-lon geocoords.
    *
    */
    
    function _parseGEO($text)
    {
        $tmp = $this->splitBySemi($text);
        return array(
            array($tmp[0]), // lat
            array($tmp[1])  // lon
        );
    }
}

?>
