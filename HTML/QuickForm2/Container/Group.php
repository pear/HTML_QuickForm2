<?php
/**
 * Base class for HTML_QuickForm2 groups
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
// pear-package-only  * Base class for all HTML_QuickForm2 containers
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Container.php';

/**
 * Base class for QuickForm2 groups of elements
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Container_Group extends HTML_QuickForm2_Container
{
   /**
    * Group name
    * If set, group name will be used as prefix for contained
    * element names, like groupname[elementname].
    * @var string
    */
    protected $name = '';

   /**
    * Previous group name
    * Stores the previous group name when the group name is changed.
    * Used to restore children names if necessary.
    * @var string
    */
    protected $previousName = '';

    public function getType()
    {
        return 'group';
    }

    protected function prependsName()
    {
        return '' !== $this->name;
    }

    protected function getChildValues($filtered = false)
    {
        $value = parent::getChildValues($filtered);
        if (!$this->prependsName()) {
            return $value;

        } elseif (!strpos($this->getName(), '[')) {
            return isset($value[$this->getName()])? $value[$this->getName()]: null;

        } else {
            $tokens   =  explode('[', str_replace(']', '', $this->getName()));
            $valueAry =& $value;
            do {
                $token = array_shift($tokens);
                if (!isset($valueAry[$token])) {
                    return null;
                }
                $valueAry =& $valueAry[$token];
            } while ($tokens);
            return $valueAry;
        }
    }

    public function setValue($value)
    {
        // Prepare a mapper for element names as array
        $prefixLength = $this->prependsName() ? substr_count($this->getName(), '[') + 1 : 0;
        $nameParts = $groupValues = [];

        /* @var $child HTML_QuickForm2_Node */
        foreach ($this as $i => $child) {
            $tokens = explode('[', str_replace(']', '', $child->getName()));
            if ($prefixLength) {
                $tokens = array_slice($tokens, $prefixLength);
            }
            $nameParts[] = $tokens;
            if ($child instanceof self) {
                $groupValues[$i] = [];
            }
        }

        // Iterate over values to find corresponding element

        $index = 0;

        foreach ((array)$value as $k => $v) {
            foreach ($nameParts as $i => $tokens) {
                $val = [$k => $v];
                do {
                    $token = array_shift($tokens);
                    $numeric = false;
                    if ($token == "") {
                        // special case for a group of checkboxes
                        if (empty($tokens) && is_array($val)
                            && $this->elements[$i] instanceof HTML_QuickForm2_Element_InputCheckbox
                        ) {
                            if (in_array($this->elements[$i]->getAttribute('value'),
                                array_map('strval', $val), true)
                            ) {
                                $this->elements[$i]->setAttribute('checked');
                                // don't want to remove 'checked' on next iteration
                                unset($nameParts[$i]);
                            } else {
                                $this->elements[$i]->removeAttribute('checked');
                            }
                            continue 2;
                        }
                        // Deal with numeric indexes in values
                        $token = $index;
                        $numeric = true;
                    }
                    if (!is_array($val) || !isset($val[$token])) {
                        // Not found, skip next iterations
                        continue 2;

                    } else {
                        // Found a value
                        $val = $val[$token];
                        if ($numeric) {
                            $index += 1;
                        }
                    }

                } while (!empty($tokens));

                // Found a value corresponding to element name
                $child = $this->elements[$i];
                if ($child instanceof self) {
                    $groupValues[$i] += (array)$val;
                } else {
                    $child->setValue($val);
                    // Speed up next iterations
                    unset($nameParts[$i]);
                }
                if (!($child instanceof HTML_QuickForm2_Element_InputRadio)) {
                    break;
                }
            }
        }
        foreach (array_keys($nameParts) as $i) {
            $this->elements[$i]->setValue(isset($groupValues[$i]) ? $groupValues[$i] : null);
        }

        return $this;
    }


    /**
     * {@inheritDoc}
     *
     * Group's name is always a string
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->previousName = $this->name;
        $this->name = (string)$name;
        foreach ($this as $child) {
            $this->renameChild($child);
        }
        return $this;
    }

   /**
    * Prepends group's name to contained element's name
    *
    * Used when adding an element to the group or changing group's name
    *
    * @param HTML_QuickForm2_Node $element
    *
    * @return HTML_QuickForm2_Node
    */
    protected function renameChild(HTML_QuickForm2_Node $element)
    {
        $tokens = explode(
            '[',
            str_replace(']', '', (string)$element->getName())
        );
        // Child has already been renamed by its group before
        if ($this === $element->getContainer() && '' !== $this->previousName) {
            $gtokens = explode('[', str_replace(']', '', $this->previousName));
            if ($gtokens === array_slice($tokens, 0, count($gtokens))) {
                array_splice($tokens, 0, count($gtokens));
            }
        }

        if ('' !== $this->name) {
            $element->setName($this->name . '[' . implode('][', $tokens) . ']');
        } elseif ('' !== $this->previousName) {
            $elname = array_shift($tokens);
            foreach ($tokens as $token) {
                $elname .= '[' . $token . ']';
            }
            $element->setName($elname);
        }

        return $element;
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
        if (null !== ($container = $element->getContainer())) {
            $container->removeChild($element);
        }
        // Element can be renamed only after being removed from container
        $this->renameChild($element);

        $element->setContainer($this);
        $this->elements[] = $element;
        return $element;
    }

   /**
    * Removes the element from this container
    *
    * If the reference object is not given, the element will be appended.
    *
    * @param HTML_QuickForm2_Node $element Element to remove
    *
    * @return   HTML_QuickForm2_Node     Removed object
    */
    public function removeChild(HTML_QuickForm2_Node $element)
    {
        $element = parent::removeChild($element);
        if ($this->prependsName()) {
            $name = preg_replace(
                '/^' . preg_quote($this->getName(), '/') . '\[([^\]]*)\]/',
                '\1',
                (string)$element->getName()
            );
            $element->setName($name);
        }
        return $element;
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
        return parent::insertBefore($this->renameChild($element), $reference);
    }

   /**
    * Sets string(s) to separate grouped elements
    *
    * @param string|array $separator Use a string for one separator, array for
    *                                alternating separators
    *
    * @return $this
    */
    public function setSeparator($separator)
    {
        $this->data['separator'] = $separator;
        return $this;
    }

   /**
    * Returns string(s) to separate grouped elements
    *
    * @return   string|array|null    Separator, null if not set
    */
    public function getSeparator()
    {
        return isset($this->data['separator'])? $this->data['separator']: null;
    }

   /**
    * Renders the group using the given renderer
    *
    * @param HTML_QuickForm2_Renderer $renderer
    *
    * @return   HTML_QuickForm2_Renderer
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        $renderer->startGroup($this);
        foreach ($this as $element) {
            $element->render($renderer);
        }
        $this->renderClientRules($renderer->getJavascriptBuilder());
        $renderer->finishGroup($this);
        return $renderer;
    }

    public function __toString()
    {
        // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_Renderer');

        /** @var HTML_QuickForm2_Renderer_Default $renderer */
        $renderer = $this->render(
            HTML_QuickForm2_Renderer::factory('default')
                ->setTemplateForId($this->getId(), '{content}')
        );
        return $renderer->__toString()
               . $renderer->getJavascriptBuilder()->getSetupCode(null, true);
    }
}
?>