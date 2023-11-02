<?php
/**
 * Callback renderer for HTML_QuickForm2
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to BSD 3-Clause License that is bundled
 * with this package in the file LICENSE and available at the URL
 * https://raw.githubusercontent.com/pear/HTML_QuickForm2/trunk/docs/LICENSE
 *
 * @category  HTML
 * @package   HTML_QuickForm2
 * @author    Alexey Borzov <avb@php.net>
 * @author    Bertrand Mansion <golgote@mamasam.com>
 * @copyright 2006-2023 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Abstract base class for QuickForm2 renderers
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Renderer.php';

/**
 * Callback renderer for QuickForm2
 *
 * This renderer uses PHP callbacks to render form elements
 *
 * While almost everything in this class is defined as public, its properties
 * and those methods that are not published (i.e. not in array returned by
 * exportMethods()) will be available to renderer plugins only.
 *
 * The following methods are published:
 *   - {@link setCallbackForClass()}
 *   - {@link setCallbackForId()}
 *   - {@link setErrorGroupCallback()}
 *   - {@link setElementCallbackForGroupClass()}
 *   - {@link setElementCallbackForGroupId()}
 *   - {@link setHiddenGroupCallback()}
 *   - {@link setRequiredNoteCallback()}
 *   - {@link setLabelCallback()}
 *
 * Using a callback to render a Submit button and a Cancel link:
 * <code>
 * function renderSubmitCancel($renderer, $submit) {
 *   $data = $submit->getData();
 *   $url = !empty($data['cancel']) ? $data['cancel'] : '/';
 *   return '<div>'.$submit.' or <a href="'.$url.'">Cancel</a></div>';
 * }
 * $renderer = HTML_QuickForm2_Renderer::factory('callback');
 * $renderer->setCallbackForId($submit->getId(), 'renderSubmitCancel');
 * </code>
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Renderer_Callback extends HTML_QuickForm2_Renderer
{
   /**
    * Whether the form contains required elements
    * @var  bool
    */
    public $hasRequired = false;

   /**
    * HTML generated for the form
    * @var  array
    */
    public $html = [[]];

   /**
    * HTML for hidden elements if 'group_hiddens' option is on
    * @var  string
    */
    public $hiddenHtml = '';

   /**
    * HTML for hidden elements if 'group_hiddens' option is on
    * @var  HTML_QuickForm2_Node[]
    */
    public $hidden = [];

   /**
    * Array of validation errors if 'group_errors' option is on
    * @var  string[]
    */
    public $errors = [];

    /**
    * Callback used to render errors if 'group_errors' is on
    * @var  callable
    */
    public $errorGroupCallback = ['HTML_QuickForm2_Renderer_Callback', '_renderErrorsGroup'];

    /**
    * Callback used to render hidden elements
    * @var  callable
    */
    public $hiddenGroupCallback = ['HTML_QuickForm2_Renderer_Callback', '_renderHiddenGroup'];

    /**
    * Callback used to render required note
    * @var  callable
    */
    public $requiredNoteCallback = ['HTML_QuickForm2_Renderer_Callback', '_renderRequiredNote'];

    /**
    * Callback used to render labels
    * @var  callable
    */
    public $labelCallback = ['HTML_QuickForm2_Renderer_Callback', '_renderLabel'];

   /**
    * Array of callbacks defined using an element or container ID
    * @var  array<string, callable|null>
    */
    public $callbacksForId = [];

    /**
    * Array of callbacks defined using an element class
    * @var  array<string, callable|null>
    */
    public $callbacksForClass = [
        'html_quickform2'                     => ['HTML_QuickForm2_Renderer_Callback', '_renderForm'],
        'html_quickform2_element'             => ['HTML_QuickForm2_Renderer_Callback', '_renderElement'],
        'html_quickform2_element_inputhidden' => ['HTML_QuickForm2_Renderer_Callback', '_renderHidden'],
        'html_quickform2_container'           => ['HTML_QuickForm2_Renderer_Callback', '_renderContainer'],
        'html_quickform2_container_group'     => ['HTML_QuickForm2_Renderer_Callback', '_renderGroup'],
        'html_quickform2_container_fieldset'  => ['HTML_QuickForm2_Renderer_Callback', '_renderFieldset'],
        'html_quickform2_container_repeat'    => ['HTML_QuickForm2_Renderer_Callback', '_renderRepeat']
    ];

    /**
    * Array of callbacks defined using a group ID
    * @var  array
    */
    public $elementCallbacksForGroupId = [];

    /**
    * Array of callbacks defined using a group class
    * @var  array
    */
    public $elementCallbacksForGroupClass = [
        'html_quickform2_container' => [
            'html_quickform2_element' => ['HTML_QuickForm2_Renderer_Callback', '_renderGroupedElement']
        ]
    ];

   /**
    * Array containing IDs of the groups being rendered
    * @var  array
    */
    public $groupId = [];

    protected function exportMethods()
    {
        return [
            'setCallbackForClass',
            'setCallbackForId',
            'setErrorGroupCallback',
            'setElementCallbackForGroupClass',
            'setElementCallbackForGroupId',
            'setHiddenGroupCallback',
            'setRequiredNoteCallback',
            'setLabelCallback'
        ];
    }

    public static function _renderForm(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2 $form
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $break = HTML_Common2::getOption(HTML_Common2::OPTION_LINEBREAK);
        $html = [
            '<div class="quickform">' .
            call_user_func($renderer->errorGroupCallback, $renderer, $form) .
            '<form'.$form->getAttributes(true).'><div>' .
            call_user_func($renderer->hiddenGroupCallback, $renderer, $form)
        ];
        $html[] = implode($break, array_pop($renderer->html));
        $html[] = '</div></form>';
        $html[] = call_user_func($renderer->requiredNoteCallback, $renderer, $form);
        $script = $renderer->getJavascriptBuilder()->getFormJavascript($form->getId());
        if (!empty($script)) {
            $html[] = $script;
        }
        $html[] = '</div>';
        return implode($break, $html) . $break;
    }

    public static function _renderElement(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2_Element $element
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $html   = ['<div class="row">'];
        $html[] = $renderer->renderLabel($element);
        $error  = $element->getError();
        if ($error) {
            $html[] = '<div class="element error">';
            if ($renderer->getOption('group_errors')) {
                $renderer->errors[] = $error;
            } else {
                $html[] = '<span class="error">'.$error.'</span><br />';
            }
        } else {
            $html[] = '<div class="element">';
        }
        $html[] = $element->__toString();
        $html[] = '</div>';
        $html[] = '</div>';
        return implode("", $html);
    }

    public static function _renderGroupedElement(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2_Element $element
    ) {
        return $element->__toString();
    }

    public static function _renderErrorsGroup(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2 $form
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $html = [];
        if (!empty($renderer->errors)) {
            $html[] = '<div class="errors">';
            if (($prefix = $renderer->getOption('errors_prefix'))
                && !empty($prefix)
            ) {
                $html[] = '<p>' . $prefix . '</p>';
            }
            $html[] = '<ul>';
            foreach ($renderer->errors as $error) {
                $html[] = '<li>' . $error . '</li>';
            }
            $html[] = '</ul>';
            if (($suffix = $renderer->getOption('errors_suffix'))
                && !empty($suffix)
            ) {
                $html[] = '<p>' . $suffix . '</p>';
            }
            $html[] = '</div>';
        }
        return implode("", $html);
    }

    public static function _renderHidden(
        HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Element_InputHidden $hidden
    ) {
        return '<div style="display: none;">'.$hidden->__toString().'</div>';
    }

    public static function _renderHiddenGroup(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2 $form
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        if (empty($renderer->hidden)) {
            return '';
        }
        $html = [];
        foreach ($renderer->hidden as $hidden) {
            $html[] = $hidden->__toString();
        }
        return '<div style="display: none;">'.implode('', $html).'</div>';
    }

    public static function _renderRequiredNote(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2 $form
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        if ($renderer->hasRequired && !$form->toggleFrozen(null)) {
            if (($note = $renderer->getOption('required_note')) && !empty($note)) {
                return '<div class="reqnote">'.$note.'</div>';
            }
        }
        return '';
    }

    public static function _renderContainer(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2_Container $container
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $break  = HTML_Common2::getOption(HTML_Common2::OPTION_LINEBREAK);
        return implode($break, array_pop($renderer->html));
    }

    public static function _renderGroup(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2_Container_Group $group
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $break = HTML_Common2::getOption(HTML_Common2::OPTION_LINEBREAK);
        $class = $group->getAttribute('class');
        $html   = ['<div class="row'.(!empty($class) ? ' '.$class : '').'">'];
        $html[] = $renderer->renderLabel($group);
        $error = $group->getError();
        if ($error) {
            $html[] = '<div class="element group error" id="'.$group->getId().'">';
            if ($renderer->getOption('group_errors')) {
                $renderer->errors[] = $error;
            } else {
                $html[] = '<span class="error">'.$error.'</span><br />';
            }
        } else {
            $html[] = '<div class="element group" id="'.$group->getId().'">';
        }

        $separator = $group->getSeparator();
        $elements  = array_pop($renderer->html);
        if (!is_array($separator)) {
            $content = implode((string)$separator, $elements);
        } else {
            $content    = '';
            $cSeparator = count($separator);
            for ($i = 0, $count = count($elements); $i < $count; $i++) {
                $content .= (0 == $i? '': $separator[($i - 1) % $cSeparator]) .
                            $elements[$i];
            }
        }
        $html[] = $content;
        $html[] = '</div>';
        $html[] = '</div>';
        return implode($break, $html) . $break;
    }

    public static function _renderRepeat(
        HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Container_Repeat $repeat
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $break = HTML_Common2::getOption(HTML_Common2::OPTION_LINEBREAK);
        $html  = ['<div class="row repeat" id="'.$repeat->getId().'">'];
        $label = $repeat->getLabel();
        if (!is_array($label)) {
            $label = [$label];
        }
        if (!empty($label[0])) {
            $html[] = '<p>'.array_shift($label).'</p>';
        }
        $elements  = array_pop($renderer->html);
        $content = implode($break, $elements);
        $html[] = $content;
        $html[] = '</div>';
        return implode($break, $html) . $break;
    }

    public static function _renderFieldset(
        HTML_QuickForm2_Renderer $renderer,
        HTML_QuickForm2_Container_Fieldset $fieldset
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $break     = HTML_Common2::getOption(HTML_Common2::OPTION_LINEBREAK);
        $html      = ['<fieldset'.$fieldset->getAttributes(true).'>'];
        $label     = $fieldset->getLabel();
        $mainLabel = is_array($label) ? array_shift($label) : (string)$label;
        if ('' !== $mainLabel) {
            $html[] = sprintf(
                '<legend id="%s-legend">%s</legend>',
                (string)$fieldset->getId(),
                $mainLabel
            );
        }
        $elements = array_pop($renderer->html);
        $html[] = implode($break, $elements);
        $html[] = '</fieldset>';
        return implode($break, $html) . $break;
    }

    public static function _renderLabel(
        HTML_QuickForm2_Renderer $renderer, HTML_QuickForm2_Node $node
    ) {
        /** @var HTML_QuickForm2_Renderer_Callback $renderer */
        $html = [];
        $label = $node->getLabel();
        if (!is_array($label)) {
            $label = [$label];
        }
        if ($node->isRequired()) {
            $renderer->hasRequired = true;
        }
        $html[] = '<p class="label">';
        if (!empty($label[0])) {
            if ($node->isRequired()) {
                $html[] = '<span class="required">*</span>';
            }
            if ($node instanceof HTML_QuickForm2_Container) {
                $html[] = '<label>';
            } else {
                $html[] = '<label for="'.$node->getId().'">';
            }
            $html[] = array_shift($label);
            $html[] = '</label>';
        }
        $html[] = '</p>';
        return implode('', $html);
    }

   /**
    * Renders a generic element
    *
    * @param HTML_QuickForm2_Node $element Element being rendered
    */
    public function renderElement(HTML_QuickForm2_Node $element)
    {
        $default = $this->callbacksForClass['html_quickform2_element'];
        $callback = $this->findCallback($element, $default);
        $res = call_user_func_array($callback, [$this, $element]);
        $this->html[count($this->html) - 1][] = $res;
    }

    /**
     * Renders an element label
     *
     * @param HTML_QuickForm2_Node $element Element being rendered
     *
     * @return string
     */
     public function renderLabel(HTML_QuickForm2_Node $element)
     {
         return call_user_func_array($this->labelCallback, [$this, $element]);
     }

   /**
    * Renders a hidden element
    *
    * @param HTML_QuickForm2_Node $element Hidden element being rendered
    */
    public function renderHidden(HTML_QuickForm2_Node $element)
    {
        if ($this->getOption('group_hiddens')) {
            $this->hidden[] = $element;
        } else {
            $default = $this->callbacksForClass['html_quickform2_element_inputhidden'];
            $callback = $this->findCallback($element, $default);
            $this->html[count($this->html) - 1][] = call_user_func_array(
                $callback, [$this, $element]
            );
        }
    }

   /**
    * Starts rendering a generic container, called before processing contained elements
    *
    * @param HTML_QuickForm2_Node $container Container being rendered
    */
    public function startContainer(HTML_QuickForm2_Node $container)
    {
        $this->html[]    = [];
        $this->groupId[] = false;
    }

   /**
    * Finishes rendering a generic container, called after processing contained elements
    *
    * @param HTML_QuickForm2_Node $container Container being rendered
    */
    public function finishContainer(HTML_QuickForm2_Node $container)
    {
        array_pop($this->groupId);
        $default = $this->callbacksForClass['html_quickform2_container'];
        $callback = $this->findCallback($container, $default);
        $res = call_user_func_array($callback, [$this, $container]);
        $this->html[count($this->html) - 1][] = $res;
    }

   /**
    * Starts rendering a group, called before processing grouped elements
    *
    * @param HTML_QuickForm2_Node $group Group being rendered
    */
    public function startGroup(HTML_QuickForm2_Node $group)
    {
        $this->html[]    = [];
        $this->groupId[] = $group->getId();
    }

   /**
    * Finishes rendering a group, called after processing grouped elements
    *
    * @param HTML_QuickForm2_Node $group Group being rendered
    */
    public function finishGroup(HTML_QuickForm2_Node $group)
    {
        array_pop($this->groupId);
        $default = $this->callbacksForClass['html_quickform2_container_group'];
        $callback = $this->findCallback($group, $default);
        $res = call_user_func_array($callback, [$this, $group]);
        $this->html[count($this->html) - 1][] = $res;
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
        $default = $this->callbacksForClass['html_quickform2'];
        $callback = $this->findCallback($form, $default);
        $this->html[0] = [
            call_user_func_array($callback, [$this, $form])
        ];
    }

    private function _validateCallback($callback)
    {
        if (is_callable($callback) || is_null($callback)) {
            return true;
        }
        throw new HTML_QuickForm2_InvalidArgumentException(
            "Renderer callback is invalid"
        );
    }

    /**
    * Sets callback for rendering labels
    *
    * @param callable|null $callback PHP callback
    *
    * @return $this
    */
    public function setLabelCallback($callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->labelCallback = $callback;
        }
        return $this;
    }

   /**
    * Sets callback for rendering hidden elements if option group_hiddens is true
    *
    * @param callable|null $callback PHP callback
    *
    * @return $this
    */
    public function setHiddenGroupCallback($callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->hiddenGroupCallback = $callback;
        }
        return $this;
    }

   /**
    * Sets callback for rendering required note
    *
    * @param callable|null $callback PHP callback
    *
    * @return $this
    */
    public function setRequiredNoteCallback($callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->requiredNoteCallback = $callback;
        }
        return $this;
    }

   /**
    * Sets callback for form elements that are instances of the given class
    *
    * When searching for a callback to use, renderer will check for callbacks
    * set for element's class and its parent classes, until found. Thus a more
    * specific callbacks will override a more generic one.
    *
    * @param string        $className Class name
    * @param callable|null $callback  Callback to use for elements of that class
    *
    * @return $this
    */
    public function setCallbackForClass($className, $callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->callbacksForClass[strtolower($className)] = $callback;
        }
        return $this;
    }

   /**
    * Sets callback for form element with the given id
    *
    * If a callback is set for an element via this method, it will be used.
    * In the other case a generic callback set by {@link setCallbackForClass()}
    * or {@link setElementCallbackForGroupClass()} will be used.
    *
    * @param string        $id       Element's id
    * @param callable|null $callback Callback to use for rendering of that element
    *
    * @return $this
    */
    public function setCallbackForId($id, $callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->callbacksForId[$id] = $callback;
        }
        return $this;
    }

   /**
    * Sets callback for rendering validation errors
    *
    * This callback will be used if 'group_errors' option is set to true.
    *
    * @param callable|null $callback Callback for validation errors
    *
    * @return $this
    */
    public function setErrorGroupCallback($callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->errorGroupCallback = $callback;
        }
        return $this;
    }

   /**
    * Sets grouped elements callbacks using group class
    *
    * Callbacks set via {@link setCallbackForClass()} will not be used for
    * grouped form elements. When searching for a callback to use, the renderer
    * will first consider callback set for a specific group id, then the
    * group callback set by group class.
    *
    * @param string        $groupClass   Group class name
    * @param string        $elementClass Element class name
    * @param callable|null $callback     Callback
    *
    * @return $this
    */
    public function setElementCallbackForGroupClass($groupClass, $elementClass, $callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->elementCallbacksForGroupClass[strtolower($groupClass)][strtolower($elementClass)] = $callback;
        }
        return $this;
    }

   /**
    * Sets grouped elements callback using group id
    *
    * Callbacks set via {@link setCallbackForClass()} will not be used for
    * grouped form elements. When searching for a callback to use, the renderer
    * will first consider callback set for a specific group id, then the
    * group callbacks set by group class.
    *
    * @param string        $groupId      Group id
    * @param string        $elementClass Element class name
    * @param callable|null $callback     Callback
    *
    * @return $this
    */
    public function setElementCallbackForGroupId($groupId, $elementClass, $callback)
    {
        if ($this->_validateCallback($callback)) {
            $this->elementCallbacksForGroupId[$groupId][strtolower($elementClass)] = $callback;
        }
        return $this;
    }

   /**
    * Resets the accumulated data
    *
    * This method is called automatically by startForm() method, but should
    * be called manually before calling other rendering methods separately.
    *
    * @return $this
    */
    public function reset()
    {
        $this->html        = [[]];
        $this->hiddenHtml  = '';
        $this->errors      = [];
        $this->hidden      = [];
        $this->hasRequired = false;
        $this->groupId     = [];

        return $this;
    }

   /**
    * Returns generated HTML
    *
    * @return string
    */
    public function __toString()
    {
        return (isset($this->html[0][0])? $this->html[0][0]: '');
    }

   /**
    * Finds a proper callback for the element
    *
    * Callbacks are scanned in a predefined order. First, if a callback was
    * set for a specific element by id, it is returned, no matter if the
    * element belongs to a group. If the element does not belong to a group,
    * we try to match a callback using the element class.
    * But, if the element belongs to a group, callbacks are first looked up
    * using the containing group id, then using the containing group class.
    * When no callback is found, the provided default callback is returned.
    *
    * @param HTML_QuickForm2_Node $element Element being rendered
    * @param callable|null        $default Default callback to use if not found
    *
    * @return callable
    */
    public function findCallback(HTML_QuickForm2_Node $element, $default = null)
    {
        $elementId = (string)$element->getId();
        if (!empty($this->callbacksForId[$elementId])) {
            return $this->callbacksForId[$elementId];
        }
        $class          = strtolower(get_class($element));
        $groupId        = end($this->groupId);
        $elementClasses = [];
        do {
            if (empty($groupId) && !empty($this->callbacksForClass[$class])) {
                return $this->callbacksForClass[$class];
            }
            $elementClasses[$class] = true;
        } while ($class = strtolower(get_parent_class($class)));

        if (!empty($groupId)) {
            if (!empty($this->elementCallbacksForGroupId[$groupId])) {
                foreach (array_keys($elementClasses) as $elClass) {
                    if (!empty($this->elementCallbacksForGroupId[$groupId][$elClass])) {
                        return $this->elementCallbacksForGroupId[$groupId][$elClass];
                    }
                }
            }

            if (null !== ($group = $element->getContainer())) {
                $grClass = strtolower(get_class($group));
                do {
                    if (!empty($this->elementCallbacksForGroupClass[$grClass])) {
                        foreach (array_keys($elementClasses) as $elClass) {
                            if (!empty($this->elementCallbacksForGroupClass[$grClass][$elClass])) {
                                return $this->elementCallbacksForGroupClass[$grClass][$elClass];
                            }
                        }
                    }
                } while ($grClass = strtolower(get_parent_class($grClass)));
            }
        }
        // default may be null, so we try to return a callback in any case
        return null === $default
               ? [self::class, '_renderElement']
               : $default;
    }
}
?>