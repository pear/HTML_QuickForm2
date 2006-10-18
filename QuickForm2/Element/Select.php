<?php
/**
 * Classes for <select> elements
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Copyright (c) 2006, Alexey Borzov <avb@php.net>, 
 *                     Bertrand Mansion <golgote@mamasam.com> 
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
 * Base class for simple HTML_QuickForm2 elements  
 */
require_once 'HTML/QuickForm2/Element.php';


/**
 * Collection of <option>s and <optgroup>s
 *
 * This class handles the output of <option> tags. The class is not intended to 
 * be used directly.
 * 
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
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
    protected $options = array();

   /**
    * Reference to parent <select>'s values   
    * @var array
    */
    protected $values;


   /**
    * Class constructor  
    *
    * @param    array   Reference to values of parent <select> element
    */
    public function __construct(&$values)
    {
        $this->values =& $values;
    }

   /**
    * Adds a new option  
    *
    * Please note that if you pass 'selected' attribute in the $attributes
    * parameter then this option's value will be added to <select>'s values.
    *
    * @param    string  Option text
    * @param    string  'value' attribute for <option> tag
    * @param    mixed   Additional attributes for <option> tag (either as a
    *                   string or as an associative array)
    */
    public function addOption($text, $value, $attributes = null)
    {
        if (null === $attributes) {
            $attributes = array('value' => (string)$value);
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
        $this->options[] = array('text' => $text, 'attr' => $attributes);
    }

   /**
    * Adds a new optgroup  
    *
    * @param    string  'label' attribute for optgroup tag
    * @param    mixed   Additional attributes for <optgroup> tag (either as a
    *                   string or as an associative array)
    * @return   HTML_QuickForm2_Element_Select_Optgroup
    */
    public function addOptgroup($label, $attributes = null)
    {
        $optgroup = new HTML_QuickForm2_Element_Select_Optgroup(
                            $this->values, $label, $attributes
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
        $indent    = $this->getIndent() . self::getOption('indent');
        $linebreak = self::getOption('linebreak');
        $html      = '';
        foreach ($this->options as $option) {
            if (is_array($option)) {
                if (in_array($option['attr']['value'], $this->values)) {
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

   /**
    * Returns an iterator over contained elements
    *
    * @return   HTML_QuickForm2_Element_Select_OptionIterator
    */
    public function getIterator()
    {
        return new HTML_QuickForm2_Element_Select_OptionIterator($this->options);
    }

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


/**
 * Class representing an <optgroup> tag
 *
 * Do not instantiate this class yourself, use 
 * {@link HTML_QuickForm2_Element_Select::addOptgroup()} method
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select_Optgroup 
    extends HTML_QuickForm2_Element_Select_OptionContainer
{
   /**
    * Class constructor
    *
    * @param    array   Reference to values of parent <select> element
    * @param    string  'label' attribute for optgroup tag
    * @param    mixed   Additional attributes for <optgroup> tag (either as a
    *                   string or as an associative array)
    */
    public function __construct(&$values, $label, $attributes = null)
    {
        parent::__construct($values);
        $this->setAttributes($attributes);
        $this->attributes['label'] = (string)$label;
    }

    public function __toString()
    {
        $indent    = $this->getIndent();
        $linebreak = self::getOption('linebreak');
        return $indent . '<optgroup' . $this->getAttributes(true) . '>' .
               $linebreak . parent::__toString() . $indent . '</optgroup>' . $linebreak;
    }
}

/**
 * Implements a recursive iterator for options arrays
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select_OptionIterator extends RecursiveArrayIterator 
    implements RecursiveIterator
{
    public function hasChildren()
    {
        return $this->current() instanceof HTML_QuickForm2_Element_Select_OptionContainer;
    }
    
    public function getChildren()
    {
        return new HTML_QuickForm2_Element_Select_OptionIterator(
            $this->current()->getOptions()
        );
    }
}


/**
 * Class representing a <select> element
 *
 * Implementation-wise, this class is a Decorator around 
 * {@see HTML_QuickForm2_Element_Select_OptionContainer}
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Element_Select extends HTML_QuickForm2_Element
    implements IteratorAggregate, Countable
{
    protected $persistent = true;

   /**
    * Values for the select element (i.e. values of the selected options)  
    * @var  array
    */
    protected $values = array();

   /**
    * Object containing options for the <select> element
    * @var  HTML_QuickForm2_Element_Select_OptionContainer
    */
    protected $optionContainer;

   /**
    * Class constructor
    *
    * @param    string  Element name
    * @param    mixed   Data used to populate the element's options, passed to
    *                   {@link load()} method
    * @param    mixed   Label for the element (may be an array of labels)
    * @param    mixed   Attributes (either a string or an array)
    */
    public function __construct($name = null, $options = null, $label = null, $attributes = null)
    {
        parent::__construct($name, $options, $label, $attributes);
        if (null !== $options) {
            $this->load($options);
        }
        $this->optionContainer = new HTML_QuickForm2_Element_Select_OptionContainer($this->values);
    }

    public function getType()
    {
        return 'select';
    }

    public function __toString()
    {
        if ($this->frozen) {
            return $this->getFrozenHtml();
        } else {
            if (empty($this->attributes['multiple'])) {
                $attrString = $this->getAttributes(true);
            } else {
                $this->attributes['name'] .= '[]';
                $attrString = $this->getAttributes(true);
                $this->attributes['name']  = substr($this->attributes['name'], 0, -2);
            }
            $indent = $this->getIndent();
            return $indent . '<select' . $attrString . '>' .
                   self::getOption('linebreak') .
                   $this->optionContainer->__toString() .
                   $indent . '</select>';
        }
    }

    protected function getFrozenHtml()
    {
        $value = array();
        foreach ($this->values as $key => $val) {
            foreach ($this->getRecursiveIterator() as $child) {
                if (is_array($child) && (string)$val == $child['attr']['value']) {
                    $value[$key] = $child['text'];
                }
            }
        }

        $html = empty($value)? '&nbsp;': implode('<br />', $value);
        if ($this->persistent) {
            $name = $this->attributes['name'] . 
                    (empty($this->attributes['multiple'])? '': '[]');
            // Only use id attribute if doing single hidden input
            $idAttr = (1 == count($value))? array('id' => $this->getId()): array(); 
            foreach ($value as $key => $item) {
                $html .= '<input type="hidden"' . self::getAttributesString(array(
                             'name'  => $name,
                             'value' => $this->values[$key]
                         ) + $idAttr) . ' />';
            }
        }
        return $html;
    }

    public function getValue()
    {
        return $this->values;
    }

    public function setValue($value)
    {
        if (is_array($value)) {
            $this->values = array_values($value);
        } else {
            $this->values = array($value);
        }
    }

   /**
    * To be written  
    */
    public function load($options)
    {
        throw new HTML_QuickForm2_Exception('Not implemented yet');
    }

    //
    // The following methods decorate those of OptionContainer
    //

   /**
    * Adds a new option  
    *
    * Please note that if you pass 'selected' attribute in the $attributes
    * parameter then this option's value will be added to <select>'s values.
    *
    * @param    string  Option text
    * @param    string  'value' attribute for <option> tag
    * @param    mixed   Additional attributes for <option> tag (either as a
    *                   string or as an associative array)
    */
    public function addOption($text, $value, $attributes = null)
    {
        return $this->optionContainer->addOption($text, $value, $attributes);
    }

   /**
    * Adds a new optgroup  
    *
    * @param    string  'label' attribute for optgroup tag
    * @param    mixed   Additional attributes for <optgroup> tag (either as a
    *                   string or as an associative array)
    * @return   HTML_QuickForm2_Element_Select_Optgroup
    */
    public function addOptgroup($label, $attributes = null)
    {
        return $this->optionContainer->addOptgroup($label, $attributes);
    }

   /**
    * Returns an iterator over contained elements
    *
    * @return   HTML_QuickForm2_Element_Select_OptionIterator
    */
    public function getIterator()
    {
        return $this->optionContainer->getIterator();
    }

   /**
    * Returns a recursive iterator over contained elements  
    *
    * @return   RecursiveIteratorIterator
    */
    public function getRecursiveIterator()
    {
        return $this->optionContainer->getRecursiveIterator();
    }

   /**
    * Returns the number of options in the container
    *
    * @return   int
    */
    public function count()
    {
        return $this->optionContainer->count();
    }
}
?>
