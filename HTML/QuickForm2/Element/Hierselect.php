<?php
/**
 * Hierarchical select element
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
 * @author    Herim Vasquez <vasquezh@iro.umontreal.ca>
 * @copyright 2006-2022 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Base class for HTML_QuickForm2 groups
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Container/Group.php';

// pear-package-only /**
// pear-package-only  * Classes for <select> elements
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Select.php';

// pear-package-only /**
// pear-package-only  * Class for adding inline javascript to the form
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Script.php';


/**
 * Hierarchical select element
 *
 * Class to dynamically create two or more HTML Select elements
 * The first select changes the content of the second select and so on.
 * This element is considered as a group. Selects will be named
 * groupName[0], groupName[1], groupName[2]...
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Herim Vasquez <vasquezh@iro.umontreal.ca>
 * @author   Bertrand Mansion <bmansion@mamasam.com>
 * @author   Alexey Borzov <avb@php.net>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Element_Hierselect extends HTML_QuickForm2_Container_Group
{
   /**
    * Options for all the select elements
    *
    * @see  loadOptions()
    * @var  array
    */
    protected $options = [];

   /**
    * PHP callback function for getting additional options
    *
    * @see  loadOptions()
    * @var  callable|null
    */
    protected $callback = null;

   /**
    * Javascript callback function for getting additional options
    *
    * @see  loadOptions()
    * @var  string|null
    */
    protected $jsCallback = null;

   /**
    * Number of select elements in hierselect
    * @var  int
    */
    protected $size = 0;

   /**
    * Values for child selects, needed for form reset handling in JS
    * @var  array
    * @see  _loadChildOptions()
    * @see  _generateInlineScript()
    */
    private $_values = [];

    public function getType()
    {
        return 'hierselect';
    }

   /**
    * Class constructor
    *
    * Hierselect element can understand the following keys in $data parameter:
    *   - 'options': data to populate child elements' options with. Passed to
    *     {@link loadOptions()} method.
    *   - 'size': number of selects in hierselect. If not given will be set
    *     from size of options array or size of array passed to setValue()
    * $data is propagated to created Select elements with these keys removed.
    *
    * @param string       $name       Element name
    * @param string|array $attributes Attributes (either a string or an array)
    * @param array        $data       Additional element data
    */
    public function __construct($name = null, $attributes = null, array $data = [])
    {
        if (!empty($data['size'])) {
            $this->size = $data['size'];
        }
        $options = isset($data['options'])? $data['options']: [];
        unset($data['options'], $data['size']);
        parent::__construct($name, $attributes, $data);
        $this->loadOptions($options);
    }

    /**
     * Initializes the the options for each select element.
     *
     * Format is a bit more complex than for a simple select as we need to know
     * which options are related to the ones in the previous select:
     *
     * Ex:
     * <code>
     * // first select
     * $select1[0] = 'Pop';
     * $select1[1] = 'Classical';
     * $select1[2] = 'Funeral doom';
     *
     * // second select
     * $select2[0][0] = 'Red Hot Chilly Peppers';
     * $select2[0][1] = 'The Pixies';
     * $select2[1][0] = 'Wagner';
     * $select2[1][1] = 'Strauss';
     * $select2[2][0] = 'Pantheist';
     * $select2[2][1] = 'Skepticism';
     *
     * // Two selects
     * $sel = $form->addElement('hierselect', 'cds')->setLabel('Choose CD:');
     * $sel->loadOptions(array($select1, $select2));
     *
     * // If you have a third select with prices for the cds
     * $select3[0][0][0] = '15.00$';
     * $select3[0][0][1] = '17.00$';
     * // etc
     *
     * // You can now use
     * $sel = $form->addElement('hierselect', 'cds')->setLabel('Choose CD:');
     * $sel->loadOptions(array($select1, $select2, $select3));
     * </code>
     *
     * @param array    $options    Array of options defining each element
     * @param callable $callback   Callback function to load additional options.
     *      It will receive an array of keys and should return associative
     *      array ('option value' => 'option text')
     * @param string   $jsCallback Javascript function to load additional options
     *      (presumably via some sort of AJAX request). It will receive an
     *      array of keys and should return {'values': [...], 'texts': [...]}
     *
     * @return  $this
     * @throws  HTML_QuickForm2_InvalidArgumentException
     */
    public function loadOptions(array $options, $callback = null, $jsCallback = null)
    {
        if (null !== $callback && !is_callable($callback, false, $callbackName)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                'Hierselect expects a valid callback for loading options, \'' .
                $callbackName . '\' was given'
            );
        }
        $this->options    = $options;
        $this->callback   = $callback;
        $this->jsCallback = $jsCallback;
        $this->size       = max($this->size, count($options));

        $this->_createSelects();
        $this->_loadChildOptions();

        return $this;
    }

   /**
    * Populates hierselect with Select elements
    */
    private function _createSelects()
    {
        for ($i = count($this); $i < $this->size; $i++) {
            $data = $this->getData();
            unset($data['label']);
            $this->appendChild(new HTML_QuickForm2_Element_Select(
                (string)$i,
                ['id' => self::generateId($this->getName() . "[{$i}]")]
                + $this->getAttributes(),
                $data
            ));
        }
    }

   /**
    * Loads options for child Select elements
    */
    private function _loadChildOptions()
    {
        $idx           = 0;
        $this->_values = [];
        /** @var HTML_QuickForm2_Element_Select $select */
        foreach ($this as $select) {
            if (empty($this->options[$idx])) {
                $this->options[$idx] = [];
            }
            $keys  =  $this->_values;
            $array =& $this->options[$idx++];
            while (!empty($keys)) {
                $key = array_shift($keys);
                if (!isset($array[$key])) {
                    if (!empty($keys)) {
                        $array[$key] = [];
                    } elseif (!empty($this->callback)) {
                        $array[$key] = call_user_func($this->callback, $this->_values);
                    } else {
                        // Most probably called from constructor with neither
                        // options nor callback provided
                        return;
                    }
                }
                $array =& $array[$key];
            }
            $select->loadOptions($array);
            $this->_values[] = null !== ($v = $select->getValue())? $v: key($array);
        }
    }

   /**
    * Sets the element's value
    *
    * This also creates missing selects and loads their options, in addition
    * to {@link HTML_QuickForm2_Container_Group::setValue()} behaviour
    *
    * @param mixed $value Actually accepts an array of selected values
    *
    * @return $this
    */
    public function setValue($value)
    {
        if (is_array($value)) {
            $this->size = max($this->size, count($value));
        }
        $this->_createSelects();
        parent::setValue($value);
        $this->_loadChildOptions();

        return $this;
    }

    /**
     * Sets the element's name
     *
     * Need to override group's implementation due to overridden updateValue()
     *
     * @param string|null $name
     *
     * @return $this
     */
    public function setName($name)
    {
        parent::setName($name);
        $this->updateValue();
        return $this;
    }

   /**
    * Called when the element needs to update its value from form's data sources
    *
    * Hierselect uses the Element's implementation of updateValue() since its
    * values need to be passed through setValue() to properly update options of
    * its child selects.
    */
    protected function updateValue()
    {
        $name = $this->getName();
        /* @var $ds HTML_QuickForm2_DataSource_NullAware */
        foreach ($this->getDataSources() as $ds) {
            if (null !== ($value = $ds->getValue($name))
                || $ds instanceof HTML_QuickForm2_DataSource_NullAware && $ds->hasValue($name)
            ) {
                $this->setValue($value);
                return;
            }
        }
    }

   /**
    * Prepares options for JS encoding
    *
    * We need to preserve order of options when adding them via javascript, so
    * cannot use object literal and for/in loop (see bug #16603). Therefore we
    * convert an associative array of options to two arrays of their values
    * and texts.
    *
    * @param array $ary   Options array
    * @param int   $depth Depth within options array
    *
    * @link     http://pear.php.net/bugs/bug.php?id=16603
    * @return   array   Array with separate options and texts
    */
    private function _prepareOptions($ary, $depth)
    {
        if (!is_array($ary)) {
            $ret = $ary;
        } elseif (0 == $depth) {
            $ret = ['values' => array_keys($ary), 'texts' => array_values($ary)];
        } else {
            $ret = [];
            foreach ($ary as $k => $v) {
                $ret[$k] = $this->_prepareOptions($v, $depth - 1);
            }
        }
        return $ret;
    }

   /**
    * Generates inline javascript containing element's defaults and (available) options
    *
    * @return   string
    */
    private function _generateInlineScript()
    {
        // we store values and options with id of first select rather than with
        // the element's name since the former has more chances to be unique
        $selectId = reset($this->elements)->getId();
        $cr       = self::getOption(self::OPTION_LINEBREAK);
        $js       = "qf.elements.hierselect.defaults['{$selectId}'] = " .
                    HTML_QuickForm2_JavascriptBuilder::encode($this->_values) . ";{$cr}";
        $jsParts  = [];
        for ($i = 1; $i < count($this->options); $i++) {
            $jsParts[] = empty($this->options[$i])
                         ? '{}' : HTML_QuickForm2_JavascriptBuilder::encode($this->_prepareOptions(
                                      $this->options[$i], $i
                                  ));
        }
        $js .= "qf.elements.hierselect.options['{$selectId}'] = [{$cr}" . implode(",{$cr}", $jsParts) . "{$cr}];";

        return $js;
    }

   /**
    * Generates a javascript function call to initialize hierselect behaviour
    *
    * @return string
    */
    private function _generateInitScript()
    {
        // pear-package-only HTML_QuickForm2_Loader::loadClass('HTML_QuickForm2_JavascriptBuilder');

        $ids = [];
        /* @var $element HTML_QuickForm2_Element */
        foreach ($this as $element) {
            $ids[] = $element->getId();
        }
        return 'qf.elements.hierselect.init(' . HTML_QuickForm2_JavascriptBuilder::encode($ids)
               . (empty($this->jsCallback)? '': ", {$this->jsCallback}") . ');';
    }

   /**
    * Renders the hierselect using the given renderer
    *
    * @param HTML_QuickForm2_Renderer $renderer
    *
    * @return   HTML_QuickForm2_Renderer
    * @throws   HTML_QuickForm2_Exception   if number of selects in hierselect cannot
    *                                       be determined
    */
    public function render(HTML_QuickForm2_Renderer $renderer)
    {
        if (0 == $this->size) {
            throw new HTML_QuickForm2_Exception(
                'Unable to determine number of selects in hierselect'
            );
        }
        if ($this->toggleFrozen()) {
            // frozen hierselect does not need any javascript
            return parent::render($renderer);
        }

        $jsBuilder = $renderer->getJavascriptBuilder();
        $jsBuilder->addLibrary('hierselect', 'quickform-hierselect.js');
        $jsBuilder->addElementJavascript($this->_generateInitScript());
        $script = (new HTML_QuickForm2_Element_Script('script'))
            ->setContent($this->_generateInlineScript());
        $script = $this->appendChild($script);

        parent::render($renderer);

        $this->removeChild($script);
        return $renderer;
    }
}
?>