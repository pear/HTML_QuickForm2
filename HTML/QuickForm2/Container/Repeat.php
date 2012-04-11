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
     * @var HTML_QuickForm2_Container
     */
    protected $prototype = null;

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
        if ($this->prototype) {
            $this->prototype->setContainer(null);
        }
        $prototype->setContainer($this);
        $this->prototype = $prototype;
        return $this;
    }

   /**
    *
    * @return HTML_QuickForm2_Container
    * @throws HTML_QuickForm2_NotFoundException
    */
    public function getPrototype()
    {
        if (empty($this->prototype)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Repeat element needs a prototype"
            );
        }
        return $this->prototype;
    }

    //
    // The following methods just proxy prototype's
    //

    public function getElements()
    {
        return $this->getPrototype()->getElements();
    }

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

    public function getIterator()
    {
        return new HTML_QuickForm2_ContainerIterator($this->getPrototype());
    }

    public function getRecursiveIterator($mode = RecursiveIteratorIterator::SELF_FIRST)
    {
        return new RecursiveIteratorIterator(
            new HTML_QuickForm2_ContainerIterator($this->getPrototype()), $mode
        );
    }

    public function count()
    {
        return count($this->prototype);
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
            // checkboxes and radios can have index inside "value" attribute instead
            if (strlen($name) && false === strpos($name, self::INDEX_KEY)
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
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $key => $child) {
            $backup[$key] = array(
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
        /* @var HTML_QuickForm2_Node $child */
        foreach ($this->getRecursiveIterator() as $key => $child) {
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
                $values, $this->getPrototype()->getChildValues($filtered)
            );
            $this->restoreChildAttributes($backup);
        }
        return empty($values) ? null : $values;
    }

    /**
     * XXX: is it good to call parent::validate() here? Need to think some more
     * @return bool
     */
    protected function validate()
    {
        $backup = $this->backupChildAttributes();
        $this->childErrors = array();
        foreach ($this->rowIndexes as $index) {
            $this->replaceIndexTemplates($index);
            $this->getPrototype()->validate();
            /* @var HTML_QuickForm2_Node $child */
            foreach ($this->getRecursiveIterator() as $child) {
                if (strlen($error = $child->getError())) {
                    $this->childErrors[$child->getId()] = $error;
                }
            }
            $this->restoreChildAttributes($backup);
        }
        return parent::validate() && empty($this->childErrors);
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
        $this->prototype->addClass('repeatPrototype');
        $this->prototype->render($renderer);
        $this->prototype->removeClass('repeatPrototype');

        // next, render all available rows
        foreach ($this->rowIndexes as $index) {
            $this->replaceIndexTemplates($index);
            /* @var HTML_QuickForm2_Node $child */
            foreach ($this->getRecursiveIterator() as $child) {
                if (isset($this->childErrors[$id = $child->getId()])) {
                    $child->setError($this->childErrors[$id]);
                }
            }
            $this->prototype->render($renderer);
            $this->restoreChildAttributes($backup);
        }
        $this->renderClientRules($renderer->getJavascriptBuilder());
        $renderer->finishContainer($this);
        $renderer->setOption('group_hiddens', $hiddens);
        return $renderer;
    }
}