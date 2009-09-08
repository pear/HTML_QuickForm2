<?php
/**
 * Default renderer for HTML_QuickForm2
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2009, Alexey Borzov <avb@php.net>,
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
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Abstract base class for QuickForm2 renderers
 */
require_once 'HTML/QuickForm2/Renderer.php';

/**
 * Default renderer for QuickForm2
 *
 * Mostly a direct port of Default renderer from QuickForm 3.x package
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
    * @var  bool
    */
    protected $hasRequired = false;

   /**
    * HTML generated for the form
    * @var  array
    */
    protected $html = array('');

   /**
    * HTML for hidden elements if 'group_hiddens' option is on
    * @var  string
    */
    protected $hiddenHtml = '';


   /**
    * Array of validation errors if 'group_errors' option is on
    * @var  array
    */
    protected $errors = array();


   /**
    * Renderer options
    * @var  array
    */
    protected $options = array(
        'group_hiddens' => true,
        'required_note' => '<strong>Note:</strong> <em>*</em> denotes required fields.',
        'errors_prefix' => 'Invalid information entered:',
        'errors_suffix' => 'Please correct these fields.',
        'group_errors'  => false
    );

   /**
    * Stores default templates for elements of the given class
    * @var  array
    */
    protected $templatesByClass = array(
        'html_quickform2_element_inputhidden' => '<div style="display: none;">{element}</div>',
        'html_quickform2' => array(
            'prefix' => '<form{attributes}><div class="qf-form">',
            'suffix' => '</div></form>'
        ),
        'html_quickform2_container_fieldset' => array(
            'prefix' => '<fieldset{attributes}><qf:label><legend id="{id}-legend">{label}</legend></qf:label>',
            'suffix' => '</fieldset>'
        ),
        'special:required_note' => '<div class="qf-note">{message}</div>',
        'special:error' => array(
            'prefix'    => '<div class="qf-errors"><qf:message><p>{message}</p></qf:message><ul><li>',
            'separator' => '</li><li>',
            'suffix'    => '</li></ul><qf:message><p>{message}</p></qf:message>'
        ),
        'html_quickform2_element' => '<div><label for="{id}" class="qf-label"><qf:required><span class="qf-required">* </span></qf:required>{label}</label><div class="qf-element<qf:error> qf-error</qf:error>"><qf:error><span class="qf-error">{error}</span><br /></qf:error>{element}</div></div>'
    );

   /**
    * Stores custom templates for elements with the given IDs
    * @var  array
    */
    protected $templatesById = array();


   /**
    * Sets the renderer options
    *
    * @param    array   Options affecting renderer behaviour:
    * <ul>
    *   <li>group_hiddens: whether to group hidden elements in a single div</li>
    *   <li>group_errors: group error messages on top of the form instead of inline</li>
    *   <li>errors_prefix: prefix sentence for grouped errors</li>
    *   <li>errors_suffix: suffix sentence for grouped errors</li>
    *   <li>required_note: note displayed if the form contains required elements</li>
    * </ul>
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setOptions(array $options = array())
    {
        foreach (array_keys($this->options) as $key) {
            if (isset($options[$key])) {
                $this->options[$key] = $options[$key];
            }
        }
        return $this;
    }

   /**
    * Sets template for elements of the given class
    *
    * When searching for a template to use, renderer will check for templates
    * set for element's class and its subclasses, until found. 
    *
    * @param    string  Class name
    * @param    mixed   Template to use for elements of that class
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setTemplateByClass($className, $template)
    {
        $this->templatesByClass[strtolower($className)] = $template;
        return $this;
    }

   /**
    * Sets template for element with the given id
    *
    * If a template is set for an element via this method, it will be used.
    * In the other case a generic template set by setTemplateByClass() will
    * be used.
    *
    * @param    string  Element's id
    * @param    mixed   Template to use for rendering of that element 
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setTemplateById($id, $template)
    {
        $this->templatesById[$id] = $template;
        return $this;
    }

   /**
    * Sets template for rendering validation errors
    *
    * This template will be used if 'group_errors' option is set to true. 
    * The template array should contain 'prefix', 'suffix' and 'separator'
    * keys.
    *
    * @param    array   Template for validation errors
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setErrorTemplate($template)
    {
        return $this->setTemplateByClass('special:error', $template);
    }

   /**
    * Sets template for a 'required' note
    *
    * Template will be used to output a note describing the appearance of
    * required elements, if the form contains some of these.
    *
    * @param    string  Template
    * @return   HTML_QuickForm2_Renderer_Default
    */
    public function setRequiredNoteTemplate($template)
    {
        return $this->setTemplateByClass('special:required_note', $template);
    }


    public function __toString()
    {
        return $this->html[0] . $this->hiddenHtml;
    }


    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $elTpl = $this->prepareTemplate($this->findTemplate($element), $element);
        $this->html[count($this->html) - 1] .= str_replace(array('{element}', '{id}'),
                                                           array($element, $element->getId()), $elTpl);
    }

    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        if ($this->options['group_hiddens']) {
            $this->hiddenHtml .= $element->__toString();
        } else {
            $this->html[count($this->html) - 1] .= str_replace('{element}', $element,
                                                               $this->findTemplate($element));
        }
    }

    public function renderContainer(HTML_QuickForm2_Node $container)
    {
        $cTpl = $this->findTemplate($container);
        $this->html[] = str_replace(array('{attributes}', '{id}'),
                                    array($container->getAttributes(true), $container->getId()),
                                    $this->prepareTemplate($cTpl['prefix'], $container));

        foreach ($container as $element) {
            $element->render($this);
        }

        $cHtml = array_pop($this->html);
        $this->html[count($this->html) - 1] .= $cHtml . $cTpl['suffix'];
    }

    public function renderForm(HTML_QuickForm2_Node $form)
    {
        $this->html        = array('');
        $this->hiddenHtml  = '';
        $this->errors      = array();
        $this->hasRequired = false;

        foreach ($form as $element) {
            $element->render($this);
        }

        // grouped errors
        if (!empty($this->errors)) {
            if (!empty($this->options['errors_prefix'])) {
                $errorHtml = str_replace(array('<qf:message>', '</qf:message>', '{message}'),
                                         array('', '', $this->options['errors_prefix']),
                                         $this->templatesByClass['special:error']['prefix']);
            } else {
                $errorHtml = preg_replace('!<qf:message>.*</qf:message>!isU', '',
                                          $this->templatesByClass['special:error']['prefix']);
            }
            $errorHtml .= implode($this->templatesByClass['special:error']['separator'], $this->errors);
            if (!empty($this->options['errors_suffix'])) {
                $errorHtml .= str_replace(array('<qf:message>', '</qf:message>', '{message}'),
                                          array('', '', $this->options['errors_suffix']),
                                          $this->templatesByClass['special:error']['suffix']);
            } else {
                $errorHtml .= preg_replace('!<qf:message>.*</qf:message>!isU', '',
                                          $this->templatesByClass['special:error']['suffix']);
            }
            $this->html[0] = $errorHtml . $this->html[0];
        }

        // grouped hidden stuff
        if (!empty($this->hiddenHtml)) {
            $this->html[0] = str_replace('{element}', $this->hiddenHtml,
                                      $this->templatesByClass['html_quickform2_element_inputhidden']) .
                          $this->html[0];
            $this->hiddenHtml = '';
        }

        // required note
        if ($this->hasRequired && !$form->toggleFrozen() &&
            !empty($this->options['required_note']))
        {
            $this->html[0] .= str_replace('{message}', $this->options['required_note'],
                                          $this->templatesByClass['special:required_note']);
        }

        $formTpl = $this->findTemplate($form);
        $this->html[0] = str_replace('{attributes}', $form->getAttributes(true),
                                  $formTpl['prefix']) .
                         $this->html[0] . $formTpl['suffix'];
    }

   /**
    * Finds a proper template for the element
    *
    * @param    HTML_QuickForm2_Node    Element being rendered
    * @return   string  Template
    */
    protected function findTemplate(HTML_QuickForm2_Node $element)
    {
        if (!empty($this->templatesById[$element->getId()])) {
            return $this->templatesById[$element->getId()];
        }
        $class = get_class($element);
        do {
            if (!empty($this->templatesByClass[strtolower($class)])) {
                return $this->templatesByClass[strtolower($class)];
            }
        } while ($class = get_parent_class($class));
        return '{element}';
    }

   /**
    * Processes the element's template, adding label(s), required note and error message
    *
    * @param    string                  Element template
    * @param    HTML_QuickForm2_Node    Element being rendered
    * @return   string  Template with some substitutions done
    */
    protected function prepareTemplate($elTpl, HTML_QuickForm2_Node $element)
    {
        // if element is required
        if ($element->isRequired()) {
            $this->hasRequired = true;
            $elTpl = str_replace(array('<qf:required>', '</qf:required>'),
                                  array('', ''), $elTpl);
        } else {
            $elTpl = preg_replace('!<qf:required>.*</qf:required>!isU', '', $elTpl);
        }
        // output element's error
        $error = $element->getError();
        if ($error && !$this->options['group_errors']) {
            $elTpl = str_replace(array('<qf:error>', '</qf:error>', '{error}'),
                                  array('', '', $error), $elTpl);
        } else {
            if ($error && $this->options['group_errors']) {
                $this->errors[] = $error;
            }
            $elTpl = preg_replace('!<qf:error>.*</qf:error>!isU', '', $elTpl);
        }
        // output labels
        $label     = $element->getLabel();
        $mainLabel = is_array($label)? array_shift($label): $label;
        $elTpl     = str_replace('{label}', $mainLabel, $elTpl);
        if (false !== strpos($elTpl, '<qf:label>')) {
            if ($mainLabel) {
                $elTpl = str_replace(array('<qf:label>', '</qf:label>'), array('', ''), $elTpl);
            } else {
                $elTpl = preg_replace('!<qf:label>.*</qf:label>!isU', '', $elTpl);
            }
        }
        if (is_array($label)) {
            foreach($label as $key => $text) {
                $key   = is_int($key)? $key + 2: $key;
                $elTpl = str_replace(array('<qf:label_' . $key . '>', '</qf:label_' . $key . '>', '{label_' . $key . '}'),
                                     array('', '', $text), $elTpl);
            }
        }
        if (strpos($elTpl, '{label_')) {
            $elTpl = preg_replace('!<qf:label_(\S+)>.*</qf:label_$1>!is', '', $elTpl);
        }
        return $elTpl;
    }
}

?>