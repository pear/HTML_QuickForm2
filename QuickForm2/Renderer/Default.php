<?php
/**
 * Default renderer for HTML_QuickForm2
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006, 2008, Alexey Borzov <avb@php.net>,
 *                           Bertrand Mansion <golgote@mamasam.com>
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
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base renderer class for QuickForm2
 */
require_once 'HTML/QuickForm2/Renderer.php';

/**
 * Default renderer for QuickForm2
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Renderer_Default extends HTML_QuickForm2_Renderer
{
   /**
    * Whether the form contains required elements
    * @var bool
    */
    public $hasRequired = false;


   /**
    * Array of hidden elements rendering in case they have to be grouped
    * @var array
    */
    public $hiddens = array();


   /**
    * Array of errors in case they have to be grouped
    * @var array
    */
    public $errors = array();


   /**
    * Renderer options
    *
    * - group_hiddens: will define if hidden elements are grouped in a div
    * - required_note: displayed note if the form contains required elements
    * - errors_prefix: prefix sentence if the form contains errors
    * - errors_suffix: suffix sentence if the form contains errors
    * - group_errors: group error messages on top of the form instead of inline
    *
    * @var array
    */
    public $options = array(
        'group_hiddens' => true,
        'required_note' => '<strong>Note:</strong> <em>*</em> denotes required fields.',
        'errors_prefix' => 'Invalid information entered:',
        'errors_suffix' => 'Please correct these fields.',
        'group_errors'  => false
        );

   /**
    * Class constructor
    *
    * @param    array    Array of options
    */
    public function __construct($options = array())
    {
        while (list($k) = each($this->options)) {
            if (isset($options[$k])) {
                $this->options[$k] = $options[$k];
            }
        }
        $this->setByClassRenderer(
            'HTML_QuickForm2_Element_InputCheckable',
            array($this, 'renderCheckable')
            );
        $this->setByClassRenderer(
            'HTML_QuickForm2_Element',
            array($this, 'renderElement')
            );
        $this->setByClassRenderer(
            'HTML_QuickForm2_Container',
            array($this, 'renderContainer')
            );
        $this->setByClassRenderer(
            'HTML_QuickForm2',
            array($this, 'renderForm')
            );
        $this->setByClassRenderer(
            'HTML_QuickForm2_Element_InputHidden',
            array($this, 'renderHidden')
            );
    }

   /**
    * Renders a checkable node
    *
    * @param    HTML_QuickForm2_Element_InputCheckable  Element node to render
    * @return   string                                  HTML output
    */
    public function renderCheckable(HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Element_InputCheckable $checkable)
    {
        $labelClass = 'qf-label';
        $errorClass = '';
        $errorMsg   = '';

        if ($checkable->isRequired()) {
            $renderer->hasRequired = true;
            $labelClass .= ' qf-required';
        }

        $error = $checkable->getError();
        if ($error) {
            $errorClass = ' qf-error';
            if ($renderer->options['group_errors']) {
                $renderer->errors[] = $error;
            } else {
                $errorMsg = '<div class="qf-message">'.$error.'</div>';
            }
        }

        $html = '<div class="qf-checkable' . $errorClass . '">' .
            $errorMsg .
            '<span class="' . $labelClass . '">' . $checkable->getLabel() . '</span>' .
            $checkable .
            '</div>';

        return $html;
    }


   /**
    * Renders an element node
    *
    * @param    HTML_QuickForm2_Element    Element node to render
    * @return   string                     HTML output
    */
    public function renderElement(HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Element $element)
    {

        $labelClass = 'qf-label';
        $errorClass = '';
        $errorMsg   = '';

        if ($element->isRequired()) {
            $renderer->hasRequired = true;
            $labelClass .= ' qf-required';
        }

        $error = $element->getError();
        if ($error) {
            $errorClass = ' qf-error';
            if ($renderer->options['group_errors']) {
                $renderer->errors[] = $error;
            } else {
                $errorMsg = '<div class="qf-message">'.$error.'</div>';
            }
        }
        $html = '<div class="qf-element'.$errorClass.'">' .
            $errorMsg .
            '<label for="' . $element->getId() . '" class="' . $labelClass . '">' .
            $element->getLabel() . '</label>' .
            $element .
            '</div>';

        return $html;
    }


   /**
    * Renders a container node
    *
    * @param    HTML_QuickForm2_Container    Container node to render
    * @return   string                       HTML output
    */
    public function renderContainer(HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Container $container)
    {
        return $container->render($renderer);
    }


   /**
    * Renders hidden elements
    *
    * If the renderer option "group_hiddens" is true, hidden elements will
    * be rendered after the form and placed in a common div at the top the
    * the form HTML code. Otherwise, they will dispatched in the form where
    * they were added.
    *
    * @param    HTML_QuickForm2_Element_InputHidden    Hidden element to render
    * @return   string                                 HTML output
    */
    public function renderHidden(HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Element_InputHidden $hidden)
    {
        if ($renderer->options['group_hiddens']) {
            $renderer->hiddens[] = $hidden->render($renderer);
            return '';
        }
        return '<div style="display:none">' . $hidden . '</div>';
    }


   /**
    * Renders the form
    *
    * @param    HTML_QuickForm2    Form node to render
    * @return   string             HTML output
    */
    public function renderForm(HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2 $form)
    {
        $lf = HTML_Common2::getOption('linebreak');

        $formHtml = $form->render($renderer);
        $html[] = '<div class="qf-form">';

        // Group errors

        if (!empty($renderer->errors)) {
            $html[] = '<div class="qf-errors">';
            if (!empty($renderer->options['errors_prefix'])) {
                $html[] = '<p>' . $renderer->options['errors_prefix'] . '</p>';
            }
            $html[] = '<ul>';
            foreach ($renderer->errors as $error) {
                $html[] = '<li>' . $error . '</li>';
            }
            $html[] = '</ul>';
            if (!empty($renderer->options['errors_suffix'])) {
                $html[] = '<p>' . $renderer->options['errors_suffix'] . '</p>';
            }
            $html[] = '</div>';
        }

        // Required note

        if ($renderer->hasRequired && !empty($renderer->options['required_note'])) {
            $html[] = '<div class="qf-note">' . $renderer->options['required_note']. '</div>';
        }


        $html[] = $formHtml;
        $html[] = '</div>';
        $html = implode($lf, $html);

        // Group hidden elements

        if (!empty($renderer->hiddens)) {
            $hiddens[] = '<form$1>';
            $hiddens[] = '<div style="display:none">';
            $hiddens[] = implode($lf, $renderer->hiddens);
            $hiddens[] = '</div>';

            $html = preg_replace('/<form([^>]*)>/',
                    implode($lf, $hiddens),
                    $html);
        }

        return $html;
    }
}