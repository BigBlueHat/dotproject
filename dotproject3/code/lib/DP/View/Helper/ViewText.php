<?php
class DP_View_Helper_ViewText extends Zend_View_Helper_FormElement
{
    /**
     * Generates a 'text' element.
     *
     * @access public
     *
     * @param string|array $name If a string, the element name.  If an
     * array, all other parameters are ignored, and the array elements
     * are used in place of added parameters.
     *
     * @param mixed $value The element value.
     *
     * @param array $attribs Attributes for the element tag.
     *
     * @return string The element XHTML.
     */
    public function viewText($name, $value = null, $attribs = null)
    {
    	$info = $this->_getInfo($name, $value, $attribs);
        extract($info); // name, value, attribs, options, listsep, disable
        $xhtml = '';
    	//$xhtml = '<div>';
    	$xhtml .= $this->view->escape($value);
    	//$xhtml .= '</div>';
    	return $xhtml;
    }
}
?>