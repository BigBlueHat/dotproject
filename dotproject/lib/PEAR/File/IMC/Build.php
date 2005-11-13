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
 * This class helps build files in the vCard and vCalendar formats.
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

class File_IMC_Build {

   /**
    * Values for vCard components.
    * 
    * @access public
    * @var array
    */
    var $value = array();
     
   /**
    * Parameters for vCard components.
    * 
    * @access public
    * @var array
    */
    var $param = array();
       
   /**
    * Tracks which component (N, ADR, TEL, etc) value was last set or added.
    * Used when adding parameters automatically to the proper component.
    *
    * @var string
    */
    var $autoparam = null;

   /**
    * Constructor
    *
    * @param string $version The vCard version to build; affects which
    * parameters are allowed and which components are returned by
    * fetch().
    * @return void
    *
    * @see fetch()
    */
    function File_IMC_Build_vCard($version = '3.0') {
        $this->reset($version);
    }

    
   /**
    * Resets the vCard values and params to be blank.
    * 
    * @param string $version The vCard version to reset to ('2.1' or
    * '3.0' -- default is the same version as previously set).
    * @return void
    * 
    * @access public
    */
    
    function reset($version = null)
    {
        $prev = $this->value['VERSION'][0][0][0];
        
        $this->value = array();
        $this->param = array();
        $this->autoparam = null;
        
        if ($version === null) {
            $this->setVersion($prev);
        } else {
            $this->setVersion($version);
        }
    }
    
   /**
    * Sets the version of the specification to use.  Only one iteration.
    *
    * Overload this function in the driver to validate and set the version
    * 
    * @param string $text The text value of the verson text (e.g. '3.0' or '2.1').
    * @return mixed Void on success, or a PEAR_Error object on failure.
    * 
    * @access public
    * @abstract
    */
    function setVersion($text)
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
    * Gets back the version of the the vCard.  Only one iteration.
    * 
    * @return string The data-source of the vCard.
    *
    * @access public
    */
    function getVersion()
    {
        return $this->getMeta('VERSION', 0) .
            $this->getValue('VERSION', 0);
    }
    
   /**
    * Prepares a string so it may be safely used as vCard values.  DO
    * NOT use this with binary encodings.  Operates on text in-place;
    * does not return a value.  Recursively descends into arrays.
    * 
    * Escapes a string so that...
    *     ; => \;
    *     , => \,
    *     newline => literal \n
    * 
    * @param  mixed  $text The string or array or strings to escape.
    * @return mixed        Void on success, or a PEAR_Error object on failure.
    * 
    * @access public
    */
    function escape(&$text)
    {
        if (is_object($text)) {
        
            return $this->raiseError(
                'The escape() method works only with string literals and arrays.', 
                FILE_IMC_ERROR_INVALID_PARAM_TYPE);
        
        } elseif (is_array($text)) {
            
            foreach ($text as $key => $val) {
                $this->escape($val);
                $text[$key] = $val;
            }
            
        } else {
        
            // escape semicolons not led by a backslash
            $regex = '(?<!\\\\)(\;)';
            $text = preg_replace("/$regex/i", "\\;", $text);
            
            // escape commas not led by a backslash
            $regex = '(?<!\\\\)(\,)';
            $text = preg_replace("/$regex/i", "\\,", $text);
            
            // escape newlines
            $regex = '\\n';
            $text = preg_replace("/$regex/i", "\\n", $text);
            
        }
    }

   /**
    * Adds a parameter value for a given component and parameter name.
    *
    * Note that although vCard 2.1 and vCalendar allow you to specify a 
    * parameter value without a name (e.g., "HOME" instead of "TYPE=HOME") this
    * class is not so lenient.  ;-)    You must specify a parameter name
    * (TYPE, ENCODING, etc) when adding a parameter.  Call multiple
    * times if you want to add multiple values to the same parameter.
    * E.g.:
    * 
    * $vcard = File_IMC::build('vCard');
    *
    * // set "TYPE=HOME,PREF" for the first TEL component
    * $vcard->addParam('TYPE', 'HOME', 'TEL', 0);
    * $vcard->addParam('TYPE', 'PREF', 'TEL', 0);
    * 
    * @param  string $param_name The parameter name, such as TYPE, VALUE,
    *                            or ENCODING.
    * @param  string $param_value The parameter value.
    * @param  string $comp The vCard component for which this is a
    *                      paramter (ADR, TEL, etc).  If null, will be the 
    *                      component that was last set or added-to.
    * @param  mixed  $iter An integer vCard component iteration that this
    *                      is a param for.  E.g., if you have more than one 
    *                      ADR component, 0 refers to the first ADR, 1 to
    *                      the second ADR, and so on.  If null, the parameter
    *                      will be added to the last component iteration 
    *                      available.
    * @return mixed        Void on success, or a PEAR_Error object on failure.
    *
    * @access public
    */
    function addParam($param_name, $param_value, $comp = null, $iter = null)
    {
        // if component is not specified, default to the last component
        // that was set or added.
        if ($comp === null) {
            $comp = $this->autoparam;
        }
        
        // using a null iteration means the param should be associated
        // with the latest/most-recent iteration of the selected
        // component.
        if ($iter === null) {
            $iter = count($this->value[$comp]) - 1;
        }
        
        // massage the text arguments
        $comp = strtoupper(trim($comp));
        $param_name = strtoupper(trim($param_name));
        $param_value = trim($param_value);
        
        if (! is_integer($iter) || $iter < 0) {
        
            return $this->raiseError("$iter is not a valid iteration number for $comp; must be a positive integer.");
            
        } else {
            
            $result = $this->validateParam($param_name, $param_value, $comp, $iter);

            if (is_a($result, 'PEAR_Error')) {
                return $result;
            } else {
                $this->param[$comp][$iter][$param_name][] = $param_value;
            }
            
        }
    }
    
   /**
    * Validates parameter names and values
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
    * @abstract
    */    
    function validateParam($name, $text, $comp = null, $iter = null)
    {
        // this must be overloaded in the drivers!
    }

   /**
    * Gets the left-side/prefix/before-the-colon (metadata) part of a
    * vCard line, including the component identifier, the parameter
    * list, and a colon.
    * 
    * @param  string $comp The component to get metadata for (ADR, TEL, etc).
    * @param  int    $iter The vCard component iteration to get the metadata
    *                      for.  E.g., if you have more than one ADR component, 
    *                      0 refers to the first ADR, 1 to the second ADR, and 
    *                      so on.
    * @return string The line prefix metadata.
    * 
    * @access public
    */
    function getMeta($comp, $iter = 0)
    {
        $params = $this->getParam($comp, $iter);
        
        if (trim($params) == '') {
            // no parameters
            $text = $comp . ':';
        } else {
            // has parameters.  put an extra semicolon in.
            $text = $comp . ';' . $params . ':';
        }
        
        return $text;
    }
    
   /**
    * Generic, all-purpose method to store a string or array in
    * $this->value, in a way suitable for later output as a vCard
    * element.  This forces the value to be the passed text or array
    * value, overriding any prior values.
    * 
    * @param  string $comp The component to set the value for ('N', 'ADR', etc).
    * @param  int    $iter The component-iteration to set the value for.
    * @param  int    $part The part number of the component-iteration to set
    *                      the value for.
    * @param  mixed  $text A string or array; the set of repeated values
    *                      for this component-iteration part.
    * @return void
    * 
    * @access public
    */
    function setValue($comp, $iter, $part, $text)
    {
        $comp = strtoupper($comp);
        settype($text, 'array');
        $this->value[$comp][$iter][$part] = $text;
        $this->autoparam = $comp;
    }
    
   /**
    * Generic, all-purpose method to add a repetition of a string or
    * array in $this->value, in a way suitable for later output as a
    * vCard element.  This appends the value to be the passed text or
    * array value, leaving any prior values in place.
    * 
    * @param  string $comp The component to set the value for ('N',
    *                      'ADR', etc).
    * @param  int    $iter The component-iteration to set the value for.
    * @param  int    $part The part number of the component-iteration to set
    *                      the value for.
    * @param  mixed  $text A string or array; the set of repeated values
    *                      for this component-iteration part.
    * @return void
    * 
    * @access public
    */
    function addValue($comp, $iter, $part, $text)
    {
        $comp = strtoupper($comp);
        settype($text, 'array');
        foreach ($text as $val) {
            $this->value[$comp][$iter][$part][] = $val;
        }
        $this->autoparam = $comp;
    }
    
   /**
    * Generic, all-purpose method to get back the data stored in $this->value.
    * 
    * @param  string $comp The component to set the value for ('N',
    *                      'ADR', etc).
    * @param  int    $iter The component-iteration to set the value for.
    * @param  int    $part The part number of the component-iteration to get
    *                      the value for.
    * @param  mixed  $rept The repetition number within the part to get;
    *                      if null, get all repetitions of the part within the 
    *                      iteration.
    * @return string       The value, escaped and delimited, of all
    *                      repetitions in the component-iteration part (or 
    *                      specific repetition within the part).
    * 
    * @access public
    */
    function getValue($comp, $iter = 0, $part = 0, $rept = null)
    {
        if ($rept === null &&
            is_array($this->value[$comp][$iter][$part]) ) {
            
            // get all repetitions of a part
            $list = array();
            foreach ($this->value[$comp][$iter][$part] as $key => $val) {
                $list[] = trim($val);
            }
            
            $this->escape($list);
            return implode(',', $list);
            
        } else {
            
            // get a specific repetition of a part
            $text = trim($this->value[$comp][$iter][$part][$rept]);
            $this->escape($text);
            return $text;
            
        }
    }
    
   /**
    * Gets back the parameter string for a given component.
    * 
    * @param  string $comp The component to get parameters for (ADR, TEL, etc).
    * @param  int    $iter The vCard component iteration to get the param
    *                      list for.  E.g., if you have more than one ADR 
    *                      component, 0 refers to the first ADR, 1 to the 
    *                      second ADR, and so on.
    * @return string
    * 
    * @access public
    */
    function getParam($comp, $iter = 0)
    {
        $comp = strtoupper($comp);
        $text = '';
        
        if (is_array($this->param[$comp][$iter])) {
            
            // loop through the array of parameters for
            // the component
            
            foreach ($this->param[$comp][$iter] as $param_name => $param_val) {
                
                // if there were previous parameter names, separate with
                // a semicolon
                if ($text != '') {
                    $text .= ';';
                }
                
                if ($param_val === null) {
                    
                    // no parameter value was specified, which is typical
                    // for vCard version 2.1 -- the name is the value.
                    $this->escape($param_name);
                    $text .= $param_name;
                    
                } else {
                    // set the parameter name...
                    $text .= strtoupper($param_name) . '=';
                    
                    // ...then escape and comma-separate the parameter
                    // values.
                    $this->escape($param_val);
                    $text .= implode(',', $param_val);
                }
            }
        }
        
        // if there were no parameters, this will be blank.
        return $text;
    }
    
    /**
    * 
    * Builds a vCard/vCal from a parser result array.  Only send
    * one vCard from the parse-results.
    *
    * Usage (to build from first vCard in parsed results):
    * 
    * $parse = File_IMC::parse('vCard'); // new parser
    * $info = $parse->fromFile('sample.vcf'); // parse file
    * 
    * $vcard = File_IMC::build('vCard'); // new builder
    * $vcard->setFromArray($info[0]); // [0] is the first card
    * 
    * @param  array  $src One vCard entry as parsed using File_IMC::parse()
    * @return void
    * 
    * @see File_IMC_Parse::fromFile()
    * @see File_IMC_Parse::fromText()
    * @access public
    */
    function setFromArray($src)
    {
        // reset to a blank values and params
        $this->value = array();
        $this->param = array();
        
        // loop through components (N, ADR, TEL, etc)
        foreach ($src AS $comp => $comp_val) {
            
            // set the autoparam property. not really needed, but let's
            // behave after an expected fashion, shall we?  ;-)
            $this->autoparam = $comp; 
            
            // iteration number of each component
            foreach ($comp_val AS $iter => $iter_val) {
                
                // value or param?
                foreach ($iter_val AS $kind => $kind_val) {
                
                    // part number
                    foreach ($kind_val AS $part => $part_val) {
                        
                        // repetition number and text value
                        foreach ($part_val AS $rept => $text) {
                            
                            // ignore data when $kind is neither 'value'
                            // nor 'param'
                            if (strtolower($kind) == 'value') {
                                $this->value[strtoupper($comp)][$iter][$part][$rept] = $text;
                            } elseif (strtolower($kind) == 'param') {
                                $this->param[strtoupper($comp)][$iter][$part][$rept] = $text;
                            }
                            
                        }
                    }
                }
            }
        }
    }
    
   /**
    * Fetches a full vCard/vCal text block based on $this->value and
    * $this->param.
    *
    * @return string A properly formatted vCard/vCalendar text block.
    *
    * @access public
    */
    function fetch()
    {
        // this method must be overloaded in a driver!
    }

    function raiseError($msg, $code)
    {
        include_once 'PEAR.php';

        PEAR::raiseError($msg, $code);
    }

}

if (!function_exists('is_a')) {
    /**
     * Emulate is_a() for PHP < 4.2.0
     */
    function is_a($obj, $classname)
    {
        return get_class($obj) == strtolower($classname) || is_subclass_of($obj, $classname);
    }
}

?>
