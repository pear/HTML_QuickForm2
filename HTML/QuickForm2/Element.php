<?php
/**
 * Base class for simple HTML_QuickForm2 elements (not Containers)
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2010, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for all HTML_QuickForm2 elements
 */
require_once 'HTML/QuickForm2/Node.php';

/**
 * Abstract base class for simple QuickForm2 elements (not Containers)
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_QuickForm2_Element extends HTML_QuickForm2_Node
{
    public function setName($name)
    {
        $this->attributes['name'] = (string)$name;
        $this->updateValue();
        return $this;
    }

   /**
    * Generates hidden form field containing the element's value
    *
    * This is used to pass the frozen element's value if 'persistent freeze'
    * feature is on
    *
    * @return string
    */
    protected function getPersistentContent()
    {
        if (!$this->persistent || null === ($value = $this->getValue())) {
            return '';
        }
        return '<input type="hidden"' . self::getAttributesString(array(
            'name'  => $this->getName(),
            'value' => $value,
            'id'    => $this->getId()
        )) . ' />';
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * The default behaviour is to go through the complete list of the data
    * sources until the non-null value is found.
    */
    protected function updateValue()
    {
        $name = $this->getName();
        foreach ($this->getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($name))) {
                $this->setValue($value);
                return;
            }
        }
    }

   /**
    * Renders the element using the given renderer
    *
    * @param    HTML_QuickForm2_Renderer    Renderer instance
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->renderElement($this);
        $this->renderClientRules($renderer->getJavascriptBuilder());
        return $renderer;
    }

   /**
    * Returns Javascript code for getting the element's value
    *
    * @param  bool  Whether it should return a parameter for qf.form.getContainerValue()
    * @return string
    */
    public function getJavascriptValue($inContainer = false)
    {
        return $inContainer? "'{$this->getId()}'": "qf.\$v('{$this->getId()}')";
    }

    /**
     * Adds a filter
     *
     * A filter is simply a PHP callback which will be applied to the element value
     * when getValue() is called. If the element value is an array, for example with 
     * selects of type 'multiple', the filter is applied to all values recursively.
     *
     * @param    callback    The PHP callback used for filter
     * @param    array       Optional arguments for the callback. The first parameter
     *                       will always be the element value, then these options will
     *                       be used as parameters for the callback.
     * @return   HTML_QuickForm2_Node    The element
     * @throws   HTML_QuickForm2_InvalidArgumentException    If callback is incorrect
     */
     public function addFilter($callback, array $options = null)
     {
         if (!is_callable($callback, false, $callbackName)) {
             throw new HTML_QuickForm2_InvalidArgumentException(
                 'Callback Filter requires a valid callback, \'' . $callbackName .
                 '\' was given'
             );
         }
         $this->filters[] = array($callback, $options, 'cascade' => false);
         return $this;
     }

    /**
     * Returns filters affecting the element only
     * This method will not return for the element's parent container filters.
     *
     * @see getFilterChain()
     * @return  array   Array of filters
     */
     public function getFilters()
     {
         return $this->filters;
     }

    /**
     * Returns all filters affecting the element, parent filters included
     *
     * Returns an array of filters including all the filters applied to
     * the ancestor containers.
     *
     * @see getFilters()
     * @return  array   Array of filters
     */
     public function getFilterChain()
     {
         $filters = array();
         $container = $this->getContainer();
         if (null !== $container) {
             $filters = $container->getFilterChain();
         }
         foreach ($this->filters as $filter) {
             $filters[] = $filter;
         }
         return $filters;
     }

     /**
      * Applies element filters on element value
      *
      * @param    mixed   Element value
      * @return   mixed   Filtered value
      */
      protected function applyFilters($value, $recursive = true)
      {
          $filters = $this->getFilterChain();
          foreach ($filters as $filter) {
              if (is_array($value)) {
                  array_walk_recursive($value,
                      array('HTML_QuickForm2_Node', 'applyFilter'), $filter);
              } else {
                  self::applyFilter($value, null, $filter);
              }
          }
          return $value;
      }
}
?>
