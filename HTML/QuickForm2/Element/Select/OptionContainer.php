<?php
/**
 * Collection of <option>s and <optgroup>s
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
// pear-package-only  * Implements a recursive iterator for options arrays
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Element/Select/OptionIterator.php';

/**
 * Collection of <option>s and <optgroup>s
 *
 * This class handles the output of <option> tags. The class is not intended to
 * be used directly.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 * @internal
 */
class HTML_QuickForm2_Element_Select_OptionContainer extends HTML_Common2
    implements IteratorAggregate, Countable
{
   /**
    * List of options and optgroups in this container
    *
    * Options are stored as arrays (for performance reasons), optgroups as
    * instances of Optgroup class.
    *
    * @var array
    */
    protected $options = [];

   /**
    * Reference to parent <select>'s values
    * @var array
    */
    protected $values;

   /**
    * Reference to parent <select>'s possible values
    * @var array
    */
    protected $possibleValues;


   /**
    * Class constructor
    *
    * @param array &$values         Reference to values of parent <select> element
    * @param array &$possibleValues Reference to possible values of parent <select> element
    */
    public function __construct(&$values, &$possibleValues)
    {
        $this->values         =& $values;
        $this->possibleValues =& $possibleValues;
    }

   /**
    * Adds a new option
    *
    * Please note that if you pass 'selected' attribute in the $attributes
    * parameter then this option's value will be added to <select>'s values.
    *
    * @param string       $text       Option text
    * @param string       $value      'value' attribute for <option> tag
    * @param string|array $attributes Additional attributes for <option> tag
    *                     (either as a string or as an associative array)
    */
    public function addOption($text, $value, $attributes = null)
    {
        if (null === $attributes) {
            $attributes = ['value' => (string)$value];
        } else {
            $attributes = self::prepareAttributes($attributes);
            if (isset($attributes['selected'])) {
                // the 'selected' attribute will be set in __toString()
                unset($attributes['selected']);
                if (!in_array($value, $this->values)) {
                    $this->values[] = $value;
                }
            }
            $attributes['value'] = (string)$value;
        }
        if (!isset($attributes['disabled'])) {
            $this->possibleValues[(string)$value] = true;
        }
        $this->options[] = ['text' => $text, 'attr' => $attributes];
    }

   /**
    * Adds a new optgroup
    *
    * @param string       $label      'label' attribute for optgroup tag
    * @param string|array $attributes Additional attributes for <optgroup> tag
    *                     (either as a string or as an associative array)
    *
    * @return   HTML_QuickForm2_Element_Select_Optgroup
    */
    public function addOptgroup($label, $attributes = null)
    {
        $optgroup = new HTML_QuickForm2_Element_Select_Optgroup(
            $this->values, $this->possibleValues, $label, $attributes
        );
        $this->options[] = $optgroup;
        return $optgroup;
    }

   /**
    * Returns an array of contained options
    *
    * @return   array
    */
    public function getOptions()
    {
        return $this->options;
    }

    public function __toString()
    {
        $indentLvl = $this->getIndentLevel();
        $indent    = $this->getIndent() . self::getOption(self::OPTION_INDENT);
        $linebreak = self::getOption(self::OPTION_LINEBREAK);
        $html      = '';
        $strValues = array_map('strval', $this->values);
        foreach ($this->options as $option) {
            if (is_array($option)) {
                if (in_array($option['attr']['value'], $strValues, true)) {
                    $option['attr']['selected'] = 'selected';
                }
                $html .= $indent . '<option' .
                         self::getAttributesString($option['attr']) .
                         '>' . $option['text'] . '</option>' . $linebreak;
            } elseif ($option instanceof HTML_QuickForm2_Element_Select_OptionContainer) {
                $option->setIndentLevel($indentLvl + 1);
                $html .= $option->__toString();
            }
        }
        return $html;
    }

    #[ReturnTypeWillChange]
   /**
    * Returns an iterator over contained elements
    *
    * @return   HTML_QuickForm2_Element_Select_OptionIterator
    */
    public function getIterator()
    {
        return new HTML_QuickForm2_Element_Select_OptionIterator($this->options);
    }

    #[ReturnTypeWillChange]
   /**
    * Returns a recursive iterator over contained elements
    *
    * @return   RecursiveIteratorIterator
    */
    public function getRecursiveIterator()
    {
        return new RecursiveIteratorIterator(
            new HTML_QuickForm2_Element_Select_OptionIterator($this->options),
            RecursiveIteratorIterator::SELF_FIRST
        );
    }

    #[ReturnTypeWillChange]
   /**
    * Returns the number of options in the container
    *
    * @return   int
    */
    public function count()
    {
        return count($this->options);
    }
}
?>