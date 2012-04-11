<?php
/**
 * Repeats the given (prototype) Container
 */

/** Base class for all HTML_QuickForm2 containers */
require_once 'HTML/QuickForm2/Container.php';

class HTML_QuickForm2_Container_Repeat extends HTML_QuickForm2_Container
{
    const INDEX_KEY = ':idx:';

    /**
     * @var string
     */
    protected $identityField;

    /**
     * @var array
     */
    protected $rowIndexes = array();

    /**
     * @var array
     */
    protected $childErrors = array();

    /**
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
     * Sets the element's value
     *
     * @param mixed $value
     *
     * @return   HTML_QuickForm2_Node
     */
    public function setValue($value)
    {
        throw new HTML_QuickForm2_Exception('Not implemented');
    }

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
    *
    * @return HTML_QuickForm2_Container
    * @throws HTML_QuickForm2_NotFoundException
    */
    public function getPrototype()
    {
        if (empty($this->elements[0])) {
            throw new HTML_QuickForm2_NotFoundException(
                "Repeat element needs a prototype, use setPrototype()"
            );
        }
        return $this->elements[0];
    }

    //
    // The following methods just proxy prototype's
    //

    public function appendChild(HTML_QuickForm2_Node $element)
    {
        return $this->getPrototype()->appendChild($element);
    }

    public function removeChild(HTML_QuickForm2_Node $element)
    {
        return $this->getPrototype()->removeChild($element);
    }

    public function getElementById($id)
    {
        return $this->getPrototype()->getElementById($id);
    }

    public function getElementsByName($name)
    {
        return $this->getPrototype()->getElementsByName($name);
    }

    public function insertBefore(
        HTML_QuickForm2_Node $element, HTML_QuickForm2_Node $reference = null
    ) {
        return $this->getPrototype()->insertBefore($element, $reference);
    }

    //
    // End of methods that just proxy prototype's
    //

    protected function getDataSources()
    {
        if (!$this->passDataSources) {
            return array();
        } else {
            return parent::getDataSources();
        }
    }

    /**
     * XXX: currently will not work without this, maybe use the first field if not given explicitly?
     * @param string $field
     */
    public function setIdentityField($field)
    {
        $this->identityField = $field;
        $this->updateValue();
    }

    /**
     * Same as Element::updateValue(), but we just need used indexes, not values
     */
    protected function updateValue()
    {
        /* @var HTML_QuickForm2_DataSource $ds */
        foreach (parent::getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($this->identityField))) {
                unset($value[self::INDEX_KEY]);
                $this->rowIndexes = array_map('intval', array_keys($value));
                return;
            }
        }
    }

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
     * @return array
     */
    protected function backupChildAttributes()
    {
        $this->appendIndexTemplates();
        $backup = array();
        $key    = 0;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            $backup[$key++] = array(
                'name'  => $child->getName(),
                'id'    => $child->getId(),
                'value' => $child->getAttribute('value'),
                'error' => $child->getError()
            );
        }
        return $backup;
    }

    protected function restoreChildAttributes(array $backup)
    {
        $this->passDataSources = false;
        $key = 0;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            $child->setId($backup[$key]['id']);
            if (strlen($backup[$key]['name'])) {
                $child->setName($backup[$key]['name']);
            }
            if (null !== $backup[$key]['value']) {
                $child->setAttribute('value', $backup[$key]['value']);
            }
            if (strlen($backup[$key]['error'])) {
                $child->setError($backup[$key]['error']);
            }
            $key++;
        }
    }

    protected function replaceIndexTemplates($index)
    {
        $this->passDataSources = true;
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $child) {
            $child->setId(str_replace(self::INDEX_KEY, $index, $child->getId()));
            if (strlen($name = $child->getName())) {
                $child->setName(str_replace(self::INDEX_KEY, $index, $name));
            }
            if ($child instanceof HTML_QuickForm2_Element_InputCheckable) {
                $value = $child->getAttribute('value');
                $child->setAttribute('value', str_replace(self::INDEX_KEY, $index, $value));
            }
            $child->setError();
        }
    }

    protected function getChildValues($filtered = false)
    {
        $backup = $this->backupChildAttributes();
        $values = array();
        foreach ($this->rowIndexes as $index) {
            $this->replaceIndexTemplates($index);
            $values = self::arrayMerge(
                $values, parent::getChildValues($filtered)
            );
            $this->restoreChildAttributes($backup);
        }
        return empty($values) ? null : $values;
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        $backup = $this->backupChildAttributes();
        $valid  = true;
        $this->childErrors = array();
        foreach ($this->rowIndexes as $index) {
            $this->replaceIndexTemplates($index);
            $valid = $this->getPrototype()->validate() && $valid;
            /* @var HTML_QuickForm2_Node $child */
            foreach ($this->getRecursiveIterator() as $child) {
                if (strlen($error = $child->getError())) {
                    $this->childErrors[$child->getId()] = $error;
                }
            }
            $this->restoreChildAttributes($backup);
        }
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
     * XXX: Javascript for contained elements doesn't work!
     */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $hiddens = $renderer->getOption('group_hiddens');
        $backup = $this->backupChildAttributes();
        $renderer->setOption('group_hiddens', false);
        $renderer->startContainer($this);

        // first, render a (hidden) prototype
        $this->getPrototype()->addClass('repeatPrototype');
        $this->getPrototype()->render($renderer);
        $this->getPrototype()->removeClass('repeatPrototype');

        // next, render all available rows
        foreach ($this->rowIndexes as $index) {
            $this->replaceIndexTemplates($index);
            /* @var HTML_QuickForm2_Node $child */
            foreach ($this->getRecursiveIterator() as $child) {
                if (isset($this->childErrors[$id = $child->getId()])) {
                    $child->setError($this->childErrors[$id]);
                }
            }
            $this->getPrototype()->render($renderer);
            $this->restoreChildAttributes($backup);
        }
        $this->renderClientRules($renderer->getJavascriptBuilder());
        $renderer->finishContainer($this);
        $renderer->setOption('group_hiddens', $hiddens);
        return $renderer;
    }
}