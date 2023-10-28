<?php
/**
 * Base class for simple HTML_QuickForm2 containers
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
 * @copyright 2006-2022 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Base class for all HTML_QuickForm2 elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Node.php';
// pear-package-only /**
// pear-package-only  * Implements a recursive iterator for the container elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/ContainerIterator.php';

/**
 * Abstract base class for simple QuickForm2 containers
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 *
 * @method HTML_QuickForm2_Element_Button        addButton(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputCheckbox addCheckbox(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_Date          addDate(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Container_Fieldset    addFieldset(string $name = '', $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Container_Group       addGroup(string $name = '', $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputFile     addFile(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputHidden   addHidden(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_Hierselect    addHierselect(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputImage    addImage(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputButton   addInputButton(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputPassword addPassword(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputRadio    addRadio(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Container_Repeat      addRepeat(string $name = '', $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputReset    addReset(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_Script        addScript(string $name = '', $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_Select        addSelect(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_Static        addStatic(string $name = '', $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputSubmit   addSubmit(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_InputText     addText(string $name, $attributes = null, array $data = [])
 * @method HTML_QuickForm2_Element_Textarea      addTextarea(string $name, $attributes = null, array $data = [])
 *
 * @implements IteratorAggregate<int,HTML_QuickForm2_Node>
 */
abstract class HTML_QuickForm2_Container extends HTML_QuickForm2_Node
    implements IteratorAggregate, Countable
{
   /**
    * Array of elements contained in this container
    * @var HTML_QuickForm2_Node[]
    */
    protected $elements = [];


    public function setName($name)
    {
        $this->attributes['name'] = (string)$name;
        return $this;
    }

    public function toggleFrozen($freeze = null)
    {
        if (null !== $freeze) {
            foreach ($this as $child) {
                $child->toggleFrozen($freeze);
            }
        }
        return parent::toggleFrozen($freeze);
    }

    public function persistentFreeze($persistent = null)
    {
        if (null !== $persistent) {
            foreach ($this as $child) {
                $child->persistentFreeze($persistent);
            }
        }
        return parent::persistentFreeze($persistent);
    }

   /**
    * Whether container prepends its name to names of contained elements
    *
    * @return   bool
    */
    protected function prependsName()
    {
        return false;
    }

   /**
    * Returns the array containing child elements' values
    *
    * @param bool $filtered Whether child elements should apply filters on values
    *
    * @return   array|null
    */
    protected function getChildValues($filtered = false)
    {
        $method = $filtered? 'getValue': 'getRawValue';
        $values = $forceKeys = [];
        foreach ($this as $child) {
            $value = $child->$method();
            if (null !== $value) {
                if ($child instanceof HTML_QuickForm2_Container
                    && !$child->prependsName()
                ) {
                    $values = self::arrayMerge($values, $value);
                } else {
                    $name = (string)$child->getName();
                    if (!strpos($name, '[')) {
                        $values[$name] = $value;
                    } else {
                        $tokens   =  explode('[', str_replace(']', '', $name));
                        $valueAry =& $values;
                        do {
                            $token = array_shift($tokens);
                            if (!isset($valueAry[$token])) {
                                $valueAry[$token] = [];
                            }
                            $valueAry =& $valueAry[$token];
                        } while (count($tokens) > 1);
                        if ('' !== ($token = array_shift($tokens))) {
                            $valueAry[$token] = $value;
                        } else {
                            if (!isset($forceKeys[$name])) {
                                $forceKeys[$name] = 0;
                            }
                            $valueAry[$forceKeys[$name]++] = $value;
                        }
                    }
                }
            }
        }
        return empty($values)? null: $values;
    }

   /**
    * Returns the container's value without filters applied
    *
    * The default implementation for Containers is to return an array with
    * contained elements' values. The array is indexed the same way $_GET and
    * $_POST arrays would be for these elements.
    *
    * @return   array|null
    */
    public function getRawValue()
    {
        return $this->getChildValues(false);
    }

   /**
    * Returns the container's value, possibly with filters applied
    *
    * The default implementation for Containers is to return an array with
    * contained elements' values. The array is indexed the same way $_GET and
    * $_POST arrays would be for these elements.
    *
    * @return   array|null
    */
    public function getValue()
    {
        $value = $this->getChildValues(true);
        return is_null($value)? null: $this->applyFilters($value);
    }

   /**
    * Merges two arrays
    *
    * Merges two arrays like the PHP function array_merge_recursive does,
    * the difference being that existing integer keys will not be renumbered.
    *
    * @param array $a
    * @param array $b
    *
    * @return   array   resulting array
    */
    public static function arrayMerge($a, $b)
    {
        foreach ($b as $k => $v) {
            if (!is_array($v) || isset($a[$k]) && !is_array($a[$k])) {
                $a[$k] = $v;
            } else {
                $a[$k] = self::arrayMerge(isset($a[$k])? $a[$k]: [], $v);
            }
        }
        return $a;
    }

   /**
    * Returns an array of this container's elements
    *
    * @return HTML_QuickForm2_Node[] Container elements
    */
    public function getElements()
    {
        return $this->elements;
    }

   /**
    * Appends an element to the container
    *
    * If the element was previously added to the container or to another
    * container, it is first removed there.
    *
    * @param HTML_QuickForm2_Node $element Element to add
    *
    * @return   HTML_QuickForm2_Node     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    */
    public function appendChild(HTML_QuickForm2_Node $element)
    {
        if ($this === $element->getContainer()) {
            $this->removeChild($element);
        }
        $element->setContainer($this);
        $this->elements[] = $element;
        return $element;
    }

   /**
    * Appends an element to the container (possibly creating it first)
    *
    * If the first parameter is an instance of HTML_QuickForm2_Node then all
    * other parameters are ignored and the method just calls {@link appendChild()}.
    * In the other case the element is first created via
    * {@link HTML_QuickForm2_Factory::createElement()} and then added via the
    * same method. This is a convenience method to reduce typing and ease
    * porting from HTML_QuickForm.
    *
    * @param string|HTML_QuickForm2_Node $elementOrType Either type name (treated
    *               case-insensitively) or an element instance
    * @param string                      $name          Element name
    * @param string|array                $attributes    Element attributes
    * @param array                       $data          Element-specific data
    *
    * @return   HTML_QuickForm2_Node     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    * @throws   HTML_QuickForm2_NotFoundException
    */
    public function addElement(
        $elementOrType, $name = null, $attributes = null, array $data = []
    ) {
        if ($elementOrType instanceof HTML_QuickForm2_Node) {
            return $this->appendChild($elementOrType);
        } else {
            return $this->appendChild(HTML_QuickForm2_Factory::createElement(
                $elementOrType, $name, $attributes, $data
            ));
        }
    }

   /**
    * Removes the element from this container
    *
    * @param HTML_QuickForm2_Node $element Element to remove
    *
    * @return   HTML_QuickForm2_Node     Removed object
    * @throws   HTML_QuickForm2_NotFoundException
    */
    public function removeChild(HTML_QuickForm2_Node $element)
    {

        if ($element->getContainer() !== $this) {
            throw new HTML_QuickForm2_NotFoundException(
                "Element with name '".$element->getName()."' was not found"
            );
        }
        $unset = false;
        foreach ($this as $key => $child) {
            if ($child === $element) {
                unset($this->elements[$key]);
                $element->setContainer(null);
                $unset = true;
                break;
            }
        }
        if ($unset) {
            $this->elements = array_values($this->elements);
        }
        return $element;
    }


   /**
    * Returns an element if its id is found
    *
    * @param string $id Element id to search for
    *
    * @return   HTML_QuickForm2_Node|null
    */
    public function getElementById($id)
    {
        foreach ($this->getRecursiveIterator() as $element) {
            if ($id == $element->getId()) {
                return $element;
            }
        }
        return null;
    }

   /**
    * Returns an array of elements which name corresponds to element
    *
    * @param string $name Element name to search for
    *
    * @return HTML_QuickForm2_Node[]
    */
    public function getElementsByName($name)
    {
        $found = [];
        foreach ($this->getRecursiveIterator() as $element) {
            if ($element->getName() == $name) {
                $found[] = $element;
            }
        }
        return $found;
    }

   /**
    * Inserts an element in the container
    *
    * If the reference object is not given, the element will be appended.
    *
    * @param HTML_QuickForm2_Node $element   Element to insert
    * @param HTML_QuickForm2_Node $reference Reference to insert before
    *
    * @return   HTML_QuickForm2_Node     Inserted element
    */
    public function insertBefore(HTML_QuickForm2_Node $element, HTML_QuickForm2_Node $reference = null)
    {
        if (null === $reference) {
            return $this->appendChild($element);
        }
        $offset = 0;
        foreach ($this as $child) {
            if ($child === $reference) {
                if ($this === $element->getContainer()) {
                    $this->removeChild($element);
                }
                $element->setContainer($this);
                array_splice($this->elements, $offset, 0, [$element]);
                return $element;
            }
            $offset++;
        }
        throw new HTML_QuickForm2_NotFoundException(
            "Reference element with name '".$reference->getName()."' was not found"
        );
    }

    #[ReturnTypeWillChange]
   /**
    * Returns a recursive iterator for the container elements
    *
    * @return    HTML_QuickForm2_ContainerIterator
    */
    public function getIterator()
    {
        return new HTML_QuickForm2_ContainerIterator($this);
    }

   /**
    * Returns a recursive iterator iterator for the container elements
    *
    * @param int $mode mode passed to RecursiveIteratorIterator
    *
    * @return   RecursiveIteratorIterator
    */
    public function getRecursiveIterator($mode = RecursiveIteratorIterator::SELF_FIRST)
    {
        return new RecursiveIteratorIterator(
            new HTML_QuickForm2_ContainerIterator($this), $mode
        );
    }

    #[ReturnTypeWillChange]
   /**
    * Returns the number of elements in the container
    *
    * @return    int
    */
    public function count()
    {
        return count($this->elements);
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * The default behaviour is just to call the updateValue() methods of
    * contained elements, since default Container doesn't have any value itself
    */
    protected function updateValue()
    {
        foreach ($this as $child) {
            $child->updateValue();
        }
    }


   /**
    * Performs the server-side validation
    *
    * This method also calls validate() on all contained elements.
    *
    * @return   boolean Whether the container and all contained elements are valid
    */
    protected function validate()
    {
        $valid = true;
        foreach ($this as $child) {
            $valid = $child->validate() && $valid;
        }
        $valid = parent::validate() && $valid;
        // additional check is needed as a Rule on Container may set errors
        // on contained elements, see HTML_QuickForm2Test::testFormRule()
        if ($valid) {
            foreach ($this->getRecursiveIterator() as $item) {
                if ('' !== (string)$item->getError()) {
                    return false;
                }
            }
        }
        return $valid;
    }

   /**
    * Appends an element to the container, creating it first
    *
    * The element will be created via {@link HTML_QuickForm2_Factory::createElement()}
    * and then added via the {@link appendChild()} method.
    * The element type is deduced from the method name.
    * This is a convenience method to reduce typing.
    *
    * @param string $m Method name
    * @param array  $a Method arguments
    *
    * @return   HTML_QuickForm2_Node     Added element
    * @throws   HTML_QuickForm2_InvalidArgumentException
    * @throws   HTML_QuickForm2_NotFoundException
    */
    public function __call($m, $a)
    {
        if (preg_match('/^(add)([a-zA-Z0-9_]+)$/', $m, $match)) {
            if ($match[1] == 'add') {
                $type = strtolower($match[2]);
                $name = isset($a[0]) ? $a[0] : null;
                $attr = isset($a[1]) ? $a[1] : null;
                $data = isset($a[2]) ? $a[2] : [];
                return $this->addElement($type, $name, $attr, $data);
            }
        }
        trigger_error("Fatal error: Call to undefined method ".get_class($this)."::".$m."()", E_USER_ERROR);
    }

   /**
    * Renders the container using the given renderer
    *
    * @param HTML_QuickForm2_Renderer $renderer
    *
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->startContainer($this);
        foreach ($this as $element) {
            $element->render($renderer);
        }
        $this->renderClientRules($renderer->getJavascriptBuilder());
        $renderer->finishContainer($this);
        return $renderer;
    }

    public function __toString()
    {
        // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_Renderer');

        /** @var HTML_QuickForm2_Renderer_Default $renderer */
        $renderer = $this->render(HTML_QuickForm2_Renderer::factory('default'));
        return $renderer->__toString()
               . $renderer->getJavascriptBuilder()->getSetupCode(null, true);
    }

   /**
    * Returns Javascript code for getting the element's value
    *
    * @param bool $inContainer Whether it should return a parameter
    *                          for qf.form.getContainerValue()
    *
    * @return   string
    */
    public function getJavascriptValue($inContainer = false)
    {
        $args = [];
        foreach ($this as $child) {
            if ('' != ($value = $child->getJavascriptValue(true))) {
                $args[] = $value;
            }
        }
        return 'qf.$cv(' . implode(', ', $args) . ')';
    }

    public function getJavascriptTriggers()
    {
        $triggers = [];
        foreach ($this as $child) {
            foreach ($child->getJavascriptTriggers() as $trigger) {
                $triggers[$trigger] = true;
            }
        }
        return array_keys($triggers);
    }
}


?>