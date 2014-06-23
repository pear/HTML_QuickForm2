<?php
/**
 * Handles a Container that can be repeated multiple times in the form
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

/** Base class for all HTML_QuickForm2 containers */
require_once 'HTML/QuickForm2/Container.php';
/** Javascript aggregator and builder class */
require_once 'HTML/QuickForm2/JavascriptBuilder.php';

/**
 * Javascript builder used when rendering a repeat prototype
 *
 * Instead of returning form setup code and client-side rules as normal
 * Javascript code, it returns them as Javascript string literals. These are
 * expected to be eval()'d when adding a new repeat item.
 *
 * This class is not intended for normal use.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Container_Repeat_JavascriptBuilder
    extends HTML_QuickForm2_JavascriptBuilder
{
    /**
     * Fake "current form" ID
     * @var string
     */
    protected $formId = 'repeat';

    /**
     * Empty list of javascript libraries, base one(s) are in original builder
     * @var array
     */
    protected $libraries = array();


    /**
     * Returns rules and element setup code as Javascript string literals
     *
     * @return array array('rules', 'setup code')
     */
    public function getFormJavascriptAsStrings()
    {
        return array(
            self::encode(
                empty($this->rules['repeat'])
                ? '' : "[\n" . implode(",\n", $this->rules['repeat']) . "\n]"
            ),
            self::encode(
                empty($this->scripts['repeat'])
                ? '' : implode("\n", $this->scripts['repeat'])
            )
        );
    }

    /**
     * Passes Javascript libraries added by repeat prototype
     *
     * @param HTML_QuickForm2_JavascriptBuilder $recipient original Javascript builder
     */
    public function passLibraries(HTML_QuickForm2_JavascriptBuilder $recipient)
    {
        foreach ($this->libraries as $name => $library) {
            $recipient->addLibrary(
                $name, $library['file'], $library['webPath'], $library['absPath']
            );
        }
    }
}



/**
 * Handles a Container that can be repeated multiple times in the form
 *
 * This element accepts a Container (a Fieldset, a Group, but not another
 * Repeat) serving as a "prototype" and repeats it several times. Repeated
 * items can be dynamically added / removed via Javascript, with the benefit
 * that server-side part automatically knows about these changes and that
 * server-side and client-side validation can be easily leveraged.
 *
 * Example:
 * <code>
 * $group  = new HTML_QuickForm2_Container_Group()
 * $repeat = $form->addRepeat('related');
 *                ->setPrototype($group);
 * // repeat indexes will be automatically appended to elements in prototype
 * $group->addHidden('related_id');
 * $group->addText('related_title');
 * // this is identical to $group->addCheckbox('related_active');
 * $repeat->addCheckbox('related_active');
 *
 * // value of this field will be used to find the indexes of repeated items
 * $repeat->setIndexField('related_id');
 * </code>
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Container_Repeat extends HTML_QuickForm2_Container
{
    /**
     * Key to replace by actual item index in elements' names / ids / values
     */
    const INDEX_KEY = ':idx:';

    /**
     * Regular expression used to check for valid indexes
     */
    const INDEX_REGEXP = '/^[a-zA-Z0-9_]+$/';

    /**
     * Field used to search for available indexes
     * @var string
     */
    protected $indexField = null;

    /**
     * Available indexes
     * @var array
     */
    protected $itemIndexes = array();

    /**
     * Errors for (repeated) child elements set during validate() call
     * @var array
     */
    protected $childErrors = array();

    /**
     * Whether getDataSources() should return Container's data sources
     *
     * This is done to prevent useless updateValue() activity in child
     * elements when their values are not going to be needed.
     *
     * @var bool
     */
    protected $passDataSources = false;

    /**
     * Returns the element's type
     *
     * @return   string
     */
    public function getType()
    {
        return 'repeat';
    }

    /**
     * Sets the element's value (not implemented)
     *
     * @param mixed $value element's value
     *
     * @throws HTML_QuickForm2_Exception
     */
    public function setValue($value)
    {
        throw new HTML_QuickForm2_Exception('Not implemented');
    }

    /**
     * Class constructor
     *
     * Repeat element can understand the following keys in $data parameter:
     *   - 'prototype': a Container to be repeated. Passed to {@link setPrototype()}.
     *
     * @param string       $name       Element name
     * @param string|array $attributes Attributes (either a string or an array)
     * @param array        $data       Additional element data
     */
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        if (!empty($data['prototype'])) {
            $this->setPrototype($data['prototype']);
        }
        unset($data['prototype']);
        parent::__construct($name, $attributes, $data);
    }

    /**
     * Sets the Container that will be used as a prototype for repeating
     *
     * @param HTML_QuickForm2_Container $prototype prototype container
     *
     * @return $this
     */
    public function setPrototype(HTML_QuickForm2_Container $prototype)
    {
        if (!empty($this->elements[0])) {
            parent::removeChild($this->elements[0]);
            $this->elements = array();
        }
        parent::appendChild($prototype);
        return $this;
    }

    /**
     * Returns the prototype Container
     *
     * @return HTML_QuickForm2_Container prototype
     * @throws HTML_QuickForm2_NotFoundException if prototype was not set
     */
    protected function getPrototype()
    {
        if (empty($this->elements[0])) {
            throw new HTML_QuickForm2_NotFoundException(
                "Repeat element needs a prototype, use setPrototype()"
            );
        }
        return $this->elements[0];
    }

    /**
     * Appends an element to the prototype container
     *
     * Elements are kept in the prototype rather than directly in repeat
     *
     * @param HTML_QuickForm2_Node $element Element to add
     *
     * @return   HTML_QuickForm2_Node     Added element
     * @throws   HTML_QuickForm2_InvalidArgumentException
     */
    public function appendChild(HTML_QuickForm2_Node $element)
    {
        return $this->getPrototype()->appendChild($element);
    }

    /**
     * Removes the element from the prototype container
     *
     * Elements are kept in the prototype rather than directly in repeat
     *
     * @param HTML_QuickForm2_Node $element Element to remove
     *
     * @return   HTML_QuickForm2_Node     Removed object
     * @throws   HTML_QuickForm2_NotFoundException
     */
    public function removeChild(HTML_QuickForm2_Node $element)
    {
        return $this->getPrototype()->removeChild($element);
    }


    /**
     * Inserts an element to the prototype container
     *
     * Elements are kept in the prototype rather than directly in repeat
     *
     * @param HTML_QuickForm2_Node $element   Element to insert
     * @param HTML_QuickForm2_Node $reference Reference to insert before
     *
     * @return   HTML_QuickForm2_Node     Inserted element
     */
    public function insertBefore(
        HTML_QuickForm2_Node $element, HTML_QuickForm2_Node $reference = null
    ) {
        return $this->getPrototype()->insertBefore($element, $reference);
    }


    /**
     * Returns the data sources for this element
     *
     * @return array
     * @see $passDataSources
     */
    protected function getDataSources()
    {
        if (!$this->passDataSources) {
            return array();
        } else {
            return parent::getDataSources();
        }
    }

    /**
     * Sets a field to check for available indexes
     *
     * Form data sources will be searched for this field's value, indexes present
     * in the array will be used for repeated elements. Use the field that will be
     * always present in submit data: checkboxes, multiple selects and fields that
     * may be disabled are bad choices
     *
     * @param string $field field name
     *
     * @return $this
     */
    public function setIndexField($field)
    {
        $this->indexField = $field;
        $this->updateValue();
        return $this;
    }

    /**
     * Tries to guess a field name to use for getting indexes of repeated items
     *
     * @return bool Whether we were able to guess something
     * @see setIndexField()
     */
    private function _guessIndexField()
    {
        $this->appendIndexTemplates();
        $this->passDataSources = false;
        /* @var $child HTML_QuickForm2_Node */
        foreach ($this->getRecursiveIterator(RecursiveIteratorIterator::LEAVES_ONLY) as $child) {
            $name = $child->getName();
            if (false === ($pos = strpos($name, '[' . self::INDEX_KEY . ']'))
                || $child->getAttribute('disabled')
            ) {
                continue;
            }
            // The list is somewhat future-proof for HTML5 input elements
            if ($child instanceof HTML_QuickForm2_Element_Input
                && !($child instanceof HTML_QuickForm2_Element_InputButton
                     || $child instanceof HTML_QuickForm2_Element_InputCheckable
                     || $child instanceof HTML_QuickForm2_Element_InputFile
                     || $child instanceof HTML_QuickForm2_Element_InputImage
                     || $child instanceof HTML_QuickForm2_Element_InputReset
                     || $child instanceof HTML_QuickForm2_Element_InputSubmit)
                || ($child instanceof HTML_QuickForm2_Element_Select
                    && !$child->getAttribute('multiple'))
                || $child instanceof HTML_QuickForm2_Element_Textarea
            ) {
                $this->indexField = substr($name, 0, $pos);
                return true;
            }
        }
        return false;
    }

    /**
     * Returns the indexes for repeated items
     *
     * @return array
     */
    public function getIndexes()
    {
        if (null === $this->indexField && $this->_guessIndexField()) {
            $this->updateValue();
        }
        return $this->itemIndexes;
    }

    /**
     * Sets the indexes for repeated items
     *
     * As is the case with elements' values, the indexes will be updated
     * from data sources, so use this after all possible updates were done.
     *
     * @param array $indexes
     *
     * @return $this
     */
    public function setIndexes(array $indexes)
    {
        $hash = array();
        foreach ($indexes as $index) {
            if (preg_match(self::INDEX_REGEXP, $index)) {
                $hash[$index] = true;
            }
        }
        $this->itemIndexes = array_keys($hash);
        return $this;
    }

    /**
     * Called when the element needs to update its value from form's data sources
     *
     * Behaves similar to Element::updateValue(), the field's value is used to
     * deduce indexes taken by repeat items.
     *
     * @see setIndexField()
     * @throws HTML_QuickForm2_Exception
     */
    protected function updateValue()
    {
        // check that we are not added to another Repeat
        // done here instead of in setContainer() for reasons outlined in InputFile
        $container = $this->getContainer();
        while (!empty($container)) {
            if ($container instanceof self) {
                throw new HTML_QuickForm2_Exception(
                    "Repeat element cannot be added to another Repeat element"
                );
            }
            $container = $container->getContainer();
        }

        if (null === $this->indexField && !$this->_guessIndexField()) {
            return;
        }
        /* @var HTML_QuickForm2_DataSource $ds */
        foreach (parent::getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($this->indexField))) {
                $this->setIndexes(array_keys($value));
                return;
            }
        }
    }

    /**
     * Appends the template to elements' names and ids that will be later replaced by index
     *
     * Default behaviour is to append '[:idx:]' to element names and '_:idx:' to
     * element ids. If the string ':idx:' is already present in the attribute,
     * then it will not be changed.
     *
     * Checkboxes and radios may contain ':idx:' in their 'value' attribute,
     * in this case their 'name' attribute is left alone. Names of groups are
     * also not touched.
     */
    protected function appendIndexTemplates()
    {
        $this->passDataSources = true;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            $id = $child->getId();
            if (false === strpos($id, self::INDEX_KEY)) {
                $child->setId($id . '_' . self::INDEX_KEY);
            }
            $name = $child->getName();
            // checkboxes and radios can have index inside "value" attribute instead,
            // group names should not be touched
            if (strlen($name) && false === strpos($name, self::INDEX_KEY)
                && (!$child instanceof HTML_QuickForm2_Container || !$child->prependsName())
                && (!$child instanceof HTML_QuickForm2_Element_InputCheckable
                    || false === strpos($child->getAttribute('value'), self::INDEX_KEY))
            ) {
                $child->setName($name . '[' . self::INDEX_KEY . ']');
            }
        }
    }

    /**
     * Backs up child attributes
     *
     * @param bool $backupId    whether to backup id attribute
     * @param bool $backupError whether to backup error message
     *
     * @return array backup array
     */
    protected function backupChildAttributes($backupId = false, $backupError = false)
    {
        $this->appendIndexTemplates();
        $backup = array();
        $key    = 0;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            $backup[$key] = array('name' => $child->getName());
            if ($child instanceof HTML_QuickForm2_Element_InputCheckable) {
                $backup[$key]['valueAttr'] = $child->getAttribute('value');
            }
            if (!($child instanceof HTML_QuickForm2_Container)
                && !($child instanceof HTML_QuickForm2_Element_Static)
            ) {
                $backup[$key]['value'] = $child->getValue();
            }
            if ($backupId) {
                $backup[$key]['id'] = $child->getId();
            }
            if ($backupError) {
                $backup[$key]['error'] = $child->getError();
            }
            $key++;
        }
        return $backup;
    }

    /**
     * Restores child attributes from backup array
     *
     * @param array $backup backup array
     *
     * @see backupChildAttributes()
     */
    protected function restoreChildAttributes(array $backup)
    {
        $key = 0;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            if (array_key_exists('value', $backup[$key])) {
                $child->setValue($backup[$key]['value']);
            }
            if (false !== strpos($backup[$key]['name'], self::INDEX_KEY)) {
                $child->setName($backup[$key]['name']);
            }
            if ($child instanceof HTML_QuickForm2_Element_InputCheckable
                && false !== strpos($backup[$key]['valueAttr'], self::INDEX_KEY)
            ) {
                $child->setAttribute('value', $backup[$key]['valueAttr']);
            }
            if (array_key_exists('id', $backup[$key])) {
                $child->setId($backup[$key]['id']);
            }
            if (array_key_exists('error', $backup[$key])) {
                $child->setError($backup[$key]['error']);
            }
            $key++;
        }
        $this->passDataSources = false;
    }

    /**
     * Replaces a template in elements' attributes by a numeric index
     *
     * @param int   $index  numeric index
     * @param array $backup backup array, contains attributes with templates
     *
     * @see backupChildAttributes()
     */
    protected function replaceIndexTemplates($index, array $backup)
    {
        $this->passDataSources = true;
        $key = 0;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            if (false !== strpos($backup[$key]['name'], self::INDEX_KEY)) {
                $child->setName(str_replace(self::INDEX_KEY, $index, $backup[$key]['name']));
            }
            if ($child instanceof HTML_QuickForm2_Element_InputCheckable
                && false !== strpos($backup[$key]['valueAttr'], self::INDEX_KEY)
            ) {
                $child->setAttribute(
                    'value', str_replace(self::INDEX_KEY, $index, $backup[$key]['valueAttr'])
                );
            }
            if (array_key_exists('id', $backup[$key])) {
                $child->setId(str_replace(self::INDEX_KEY, $index, $backup[$key]['id']));
            }
            if (array_key_exists('error', $backup[$key])) {
                $child->setError();
            }
            $key++;
        }
    }

    /**
     * Returns the array containing child elements' values
     *
     * Iterates over all available repeat indexes to get values
     *
     * @param bool $filtered Whether child elements should apply filters on values
     *
     * @return   array|null
     */
    protected function getChildValues($filtered = false)
    {
        $backup = $this->backupChildAttributes();
        $values = array();
        foreach ($this->getIndexes() as $index) {
            $this->replaceIndexTemplates($index, $backup);
            if (null !== ($itemValues = parent::getChildValues($filtered))) {
                $values = self::arrayMerge($values, $itemValues);
            }
        }
        $this->restoreChildAttributes($backup);
        return empty($values) ? null : $values;
    }

    /**
     * Performs the server-side validation
     *
     * Iterates over all available repeat indexes and calls validate() on
     * prototype container.
     *
     * @return   boolean Whether the repeat and all repeated items are valid
     */
    protected function validate()
    {
        $backup = $this->backupChildAttributes(false, true);
        $valid  = true;
        $this->childErrors = array();
        foreach ($this->getIndexes() as $index) {
            $this->replaceIndexTemplates($index, $backup);
            $valid = $this->getPrototype()->validate() && $valid;
            /* @var HTML_QuickForm2_Node $child */
            foreach ($this->getRecursiveIterator() as $child) {
                if (strlen($error = $child->getError())) {
                    $this->childErrors[spl_object_hash($child)][$index] = $error;
                }
            }
        }
        $this->restoreChildAttributes($backup);
        foreach ($this->rules as $rule) {
            if (strlen($this->error)) {
                break;
            }
            if ($rule[1] & HTML_QuickForm2_Rule::SERVER) {
                $rule[0]->validate();
            }
        }
        return !strlen($this->error) && $valid;
    }

    /**
     * Generates Javascript code to initialize repeat behaviour
     *
     * @param HTML_QuickForm2_Container_Repeat_JavascriptBuilder $evalBuilder
     *        Javascript builder returning JS string literals
     *
     * @return string javascript
     */
    private function _generateInitScript(
        HTML_QuickForm2_Container_Repeat_JavascriptBuilder $evalBuilder
    ) {
        $myId     = HTML_QuickForm2_JavascriptBuilder::encode($this->getId());
        $protoId  = HTML_QuickForm2_JavascriptBuilder::encode($this->getPrototype()->getId());

        $triggers = array();
        /* @var $child HTML_QuickForm2_Node */
        foreach ($this->getRecursiveIterator() as $child) {
            $triggers[] = $child->getId();
        }
        $triggers = HTML_QuickForm2_JavascriptBuilder::encode($triggers);

        list ($rules, $scripts) = $evalBuilder->getFormJavascriptAsStrings();

        return "new qf.elements.Repeat(document.getElementById({$myId}), {$protoId}, "
               . "{$triggers},\n{$rules},\n{$scripts}\n);";
    }

    /**
     * Adds element's client-side validation rules to a builder object
     *
     * This will also call forceValidator() if the repeat does not contain
     * any (visible) items but some of the child elements define client-side rules
     *
     * @param HTML_QuickForm2_JavascriptBuilder $builder
     */
    protected function renderClientRules(HTML_QuickForm2_JavascriptBuilder $builder)
    {
        if ($this->toggleFrozen()) {
            return;
        }
        if (!$this->getIndexes()) {
            $fakeBuilder = new HTML_QuickForm2_JavascriptBuilder();
            /* @var $child HTML_QuickForm2_Node */
            foreach ($this->getRecursiveIterator() as $child) {
                $child->renderClientRules($fakeBuilder);
            }
            if ($fakeBuilder->getValidator()) {
                $builder->forceValidator();
            }
        }

        parent::renderClientRules($builder);
    }

    /**
     * Renders the container using the given renderer
     *
     * Container will be output N + 1 times, where N are visible items and 1 is
     * the hidden prototype used by Javascript code to create new items.
     *
     * @param HTML_QuickForm2_Renderer $renderer renderer to use
     *
     * @return   HTML_QuickForm2_Renderer
     */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $backup      = $this->backupChildAttributes(true, true);
        $hiddens     = $renderer->getOption('group_hiddens');
        $jsBuilder   = $renderer->getJavascriptBuilder();
        $evalBuilder = new HTML_QuickForm2_Container_Repeat_JavascriptBuilder();

        $renderer->setJavascriptBuilder($evalBuilder)
            ->setOption('group_hiddens', false)
            ->startContainer($this);

        // first, render a (hidden) prototype
        $this->getPrototype()->addClass('repeatItem repeatPrototype');
        $this->getPrototype()->render($renderer);
        $this->getPrototype()->removeClass('repeatPrototype');

        // restore original JS builder
        $evalBuilder->passLibraries($jsBuilder);
        $renderer->setJavascriptBuilder($jsBuilder);

        // next, render all available rows
        foreach ($this->getIndexes() as $index) {
            $this->replaceIndexTemplates($index, $backup);
            /* @var HTML_QuickForm2_Node $child */
            foreach ($this->getRecursiveIterator() as $child) {
                if (isset($this->childErrors[$hash = spl_object_hash($child)])
                    && isset($this->childErrors[$hash][$index])
                ) {
                    $child->setError($this->childErrors[$hash][$index]);
                }
            }
            $this->getPrototype()->render($renderer);
        }
        $this->restoreChildAttributes($backup);

        // only add javascript if not frozen
        if (!$this->toggleFrozen()) {
            $jsBuilder->addLibrary('repeat', 'quickform-repeat.js');
            $jsBuilder->addElementJavascript($this->_generateInitScript($evalBuilder));
            $this->renderClientRules($jsBuilder);
        }

        $renderer->finishContainer($this);
        $renderer->setOption('group_hiddens', $hiddens);
        return $renderer;
    }
}
?>