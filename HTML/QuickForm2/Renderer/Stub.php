<?php
/**
 * A stub renderer to use with HTML_QuickForm2 when actual form output is done manually
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
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
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 */
require_once 'HTML/QuickForm2/Renderer.php';

/**
 * A stub renderer to use with HTML_QuickForm2 when actual form output is done manually
 *
 * The rendering step is mandatory if the form uses client-side validation
 * or contains Javascript-backed elements. Using Array or Default renderer
 * will add unnecessary overhead if such a form will later be output by
 * e.g. echoing form elements in PHP-based template.
 *
 * This renderer does almost no form processing, serving as a container for
 * JavascriptBuilder instance. The only processing it does is grouping errors
 * and hidden elements if the relevant 'group_hiddens' and 'group_errors'
 * options are set to true. It also checks whether the form contains required
 * elements (and thus needs a required note).
 *
 * While almost everything in this class is defined as public, its properties
 * and those methods that are not published (i.e. not in array returned by
 * exportMethods()) will be available to renderer plugins only.
 *
 * The following methods are published:
 *   - {@link getErrors()}
 *   - {@link getHidden()}
 *   - {@link hasRequired()}
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Renderer_Stub extends HTML_QuickForm2_Renderer
{
   /**
    * Form errors if 'group_errors' option is true
    * @var array
    */
    public $errors = array();

   /**
    * Hidden elements if 'group_hiddens' option is true
    * @var array
    */
    public $hidden = array();

   /**
    * Whether the form contains required elements
    * @var bool
    */
    public $required = false;

    protected function exportMethods()
    {
        return array(
            'getErrors',
            'getHidden',
            'hasRequired'
        );
    }

   /**
    * Returns validation errors if 'group_errors' option is true
    *
    * @return array array('element id' => 'error message')
    */
    public function getErrors()
    {
        return $this->errors;
    }

   /**
    * Returns hidden elements' HTML if 'group_hiddens' option is true
    *
    * @return array
    */
    public function getHidden()
    {
        return $this->hidden;
    }

   /**
    * Checks whether form contains required elements
    *
    * @return bool
    */
    public function hasRequired()
    {
        return $this->required;
    }

    public function reset()
    {
        $this->errors   = array();
        $this->hidden   = array();
        $this->required = false;

        return $this;
    }

    /**
     * Renders a generic element
     *
     * @param HTML_QuickForm2_Node $element Element being rendered
     */
    public function renderElement(HTML_QuickForm2_Node $element)
    {
        if ($element->isRequired()) {
            $this->required = true;
        }
        if ($this->options['group_errors'] && ($error = $element->getError())) {
            $this->errors[$element->getId()] = $error;
        }
    }

    /**
     * Renders a hidden element
     *
     * @param HTML_QuickForm2_Node $element Hidden element being rendered
     */
    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        if ($this->options['group_hiddens']) {
            $this->hidden[] = $element->__toString();
        }
    }

    /**
     * Starts rendering a form, called before processing contained elements
     *
     * @param HTML_QuickForm2_Node $form Form being rendered
     */
    public function startForm(HTML_QuickForm2_Node $form)
    {
        $this->reset();
    }

    /**
     * Finishes rendering a form, called after processing contained elements
     *
     * @param HTML_QuickForm2_Node $form Form being rendered
     */
    public function finishForm(HTML_QuickForm2_Node $form)
    {
        $this->renderElement($form);
    }

    /**
     * Starts rendering a generic container, called before processing contained elements
     *
     * @param HTML_QuickForm2_Node $container Container being rendered
     */
    public function startContainer(HTML_QuickForm2_Node $container)
    {
    }

    /**
     * Finishes rendering a generic container, called after processing contained elements
     *
     * @param HTML_QuickForm2_Node $container Container being rendered
     */
    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        $this->renderElement($container);
    }

    /**
     * Starts rendering a group, called before processing grouped elements
     *
     * @param HTML_QuickForm2_Node $group Group being rendered
     */
    public function startGroup(HTML_QuickForm2_Node $group)
    {
    }

    /**
     * Finishes rendering a group, called after processing grouped elements
     *
     * @param HTML_QuickForm2_Node $group Group being rendered
     */
    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        $this->renderElement($group);
    }
}
?>