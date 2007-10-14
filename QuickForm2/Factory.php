<?php
/**
 * Static Factory class for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006, 2007, Alexey Borzov <avb@php.net>,
 *                           Bertrand Mansion <golgote@mamasam.com>
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
 * Exception classes for HTML_QuickForm2
 */
require_once 'HTML/QuickForm2/Exception.php';

/**
 * Static factory class
 *
 * The class handles instantiation of Element and Rule objects as well as
 * registering of new Element and Rule classes.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Factory
{
   /**
    * List of element types known to Factory
    * @var array
    */
    protected static $elementTypes = array(
        'button'        => array('HTML_QuickForm2_Element_Button',
                                 'HTML/QuickForm2/Element/Button.php'),
        'checkbox'      => array('HTML_QuickForm2_Element_InputCheckbox',
                                 'HTML/QuickForm2/Element/InputCheckbox.php'),
        'fieldset'      => array('HTML_QuickForm2_Container_Fieldset',
                                 'HTML/QuickForm2/Container/Fieldset.php'),
        'file'          => array('HTML_QuickForm2_Element_InputFile',
                                 'HTML/QuickForm2/Element/InputFile.php'),
        'hidden'        => array('HTML_QuickForm2_Element_InputHidden',
                                 'HTML/QuickForm2/Element/InputHidden.php'),
        'image'         => array('HTML_QuickForm2_Element_InputImage',
                                 'HTML/QuickForm2/Element/InputImage.php'),
        'inputbutton'   => array('HTML_QuickForm2_Element_InputButton',
                                 'HTML/QuickForm2/Element/InputButton.php'),
        'password'      => array('HTML_QuickForm2_Element_InputPassword',
                                 'HTML/QuickForm2/Element/InputPassword.php'),
        'radio'         => array('HTML_QuickForm2_Element_InputRadio',
                                 'HTML/QuickForm2/Element/InputRadio.php'),
        'reset'         => array('HTML_QuickForm2_Element_InputReset',
                                 'HTML/QuickForm2/Element/InputReset.php'),
        'select'        => array('HTML_QuickForm2_Element_Select',
                                 'HTML/QuickForm2/Element/Select.php'),
        'submit'        => array('HTML_QuickForm2_Element_InputSubmit',
                                 'HTML/QuickForm2/Element/InputSubmit.php'),
        'text'          => array('HTML_QuickForm2_Element_InputText',
                                 'HTML/QuickForm2/Element/InputText.php'),
        'textarea'      => array('HTML_QuickForm2_Element_Textarea',
                                 'HTML/QuickForm2/Element/Textarea.php')
    );

   /**
    * List of registered rules
    * @var array
    */
    protected static $registeredRules = array(
        'nonempty'      => array('HTML_QuickForm2_Rule_Nonempty',
                                 'HTML/QuickForm2/Rule/Nonempty.php'),
        'empty'         => array('HTML_QuickForm2_Rule_Empty',
                                 'HTML/QuickForm2/Rule/Empty.php'),
        'required'      => array('HTML_QuickForm2_Rule_Required',
                                 'HTML/QuickForm2/Rule/Required.php'),
        'compare'       => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php'),
        'eq'            => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php',
                                 array('operator' => '===')),
        'neq'           => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php',
                                 array('operator' => '!==')),
        'lt'            => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php',
                                 array('operator' => '<')),
        'lte'           => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php',
                                 array('operator' => '<=')),
        'gt'            => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php',
                                 array('operator' => '>')),
        'gte'           => array('HTML_QuickForm2_Rule_Compare',
                                 'HTML/QuickForm2/Rule/Compare.php',
                                 array('operator' => '>=')),
        'regex'         => array('HTML_QuickForm2_Rule_Regex',
                                 'HTML/QuickForm2/Rule/Regex.php'),
        'callback'      => array('HTML_QuickForm2_Rule_Callback',
                                 'HTML/QuickForm2/Rule/Callback.php'),
        'length'        => array('HTML_QuickForm2_Rule_Length',
                                 'HTML/QuickForm2/Rule/Length.php'),
        'minlength'     => array('HTML_QuickForm2_Rule_Length',
                                 'HTML/QuickForm2/Rule/Length.php',
                                 array('max' => 0)),
        'maxlength'     => array('HTML_QuickForm2_Rule_Length',
                                 'HTML/QuickForm2/Rule/Length.php',
                                 array('min' => 0)),
        'maxfilesize'   => array('HTML_QuickForm2_Rule_MaxFileSize',
                                 'HTML/QuickForm2/Rule/MaxFileSize.php'),
        'mimetype'      => array('HTML_QuickForm2_Rule_MimeType',
                                 'HTML/QuickForm2/Rule/MimeType.php')
    );


   /**
    * Checks whether the file exists in the include path
    *
    * @param    string  file name
    * @return   bool
    */
    protected static function fileExists($fileName)
    {
        $fp = @fopen($fileName, 'r', true);
        if (is_resource($fp)) {
            fclose($fp);
            return true;
        }
        return false;
    }

   /**
    * Tries to load a given class from a given file
    *
    * @param    string  Class name to load
    * @param    string  Name of the file (supposedly) containing the given class
    * @throws   HTML_QuickForm2_NotFoundException   If the file either can't be
    *               loaded or doesn't contain the given class
    */
    protected static function loadClass($className, $includeFile)
    {
        if (empty($includeFile)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Class '$className' does not exist and no file to load"
            );
        } elseif (!self::fileExists($includeFile)) {
            throw new HTML_QuickForm2_NotFoundException("File '$includeFile' was not found");
        }
        // Do not silence the errors with @, parse errors will not be seen
        include_once $includeFile;
        // Still no class?
        if (!class_exists($className, false)) {
            throw new HTML_QuickForm2_NotFoundException(
                "Class '$className' was not found within file '$includeFile'"
            );
        }
    }

   /**
    * Registers a new element type
    *
    * @param    string  Type name (treated case-insensitively)
    * @param    string  Class name
    * @param    string  File containing the class, leave empty if class already loaded
    */
    public static function registerElement($type, $className, $includeFile = null)
    {
        self::$elementTypes[strtolower($type)] = array($className, $includeFile);
    }


   /**
    * Checks whether an element type is known to factory
    *
    * @param    string  Type name (treated case-insensitively)
    * @return   bool
    */
    public static function isElementRegistered($type)
    {
        return isset(self::$elementTypes[strtolower($type)]);
    }


   /**
    * Creates a new element object of the given type
    *
    * @param    string  Type name (treated case-insensitively)
    * @param    mixed   Element name (passed to element's constructor)
    * @param    mixed   Element attributes (passed to element's constructor)
    * @param    array   Element-specific data (passed to element's constructor)
    * @return   HTML_QuickForm2_Node     A created element
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the element can
    *           not be found and/or loaded from file
    */
    public static function createElement($type, $name = null, $attributes = null,
                                         array $data = array())
    {
        $type = strtolower($type);
        if (!isset(self::$elementTypes[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Element type '$type' is not known");
        }
        list($className, $includeFile) = self::$elementTypes[$type];
        if (!class_exists($className, false)) {
            self::loadClass($className, $includeFile);
        }
        return new $className($name, $attributes, $data);
    }


   /**
    * Registers a new rule type
    *
    * @param    string  Rule type name (treated case-insensitively)
    * @param    string  Class name
    * @param    string  File containing the class, leave empty if class already loaded
    * @param    mixed   Configuration data for rules of the given type
    */
    public static function registerRule($type, $className, $includeFile = null,
                                        $config = null)
    {
        self::$registeredRules[strtolower($type)] = array($className, $includeFile, $config);
    }


   /**
    * Returns configuration data for rules of the given type
    *
    * @param    string  Rule type name (treated case-insensitively)
    * @return   mixed   Configuration data (set when registering the rule)
    * @throws   HTML_QuickForm2_InvalidArgumentException If rule type is unknown
    */
    public static function getRuleConfig($type)
    {
        $type = strtolower($type);
        if (!isset(self::$registeredRules[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Rule '$type' is not known");
        } elseif (isset(self::$registeredRules[$type][2])) {
            return self::$registeredRules[$type][2];
        } else {
            return null;
        }
    }


   /**
    * Checks whether a rule type is known to Factory
    *
    * @param    string  Rule type name (treated case-insensitively)
    * @return   bool
    */
    public static function isRuleRegistered($type)
    {
        return isset(self::$registeredRules[strtolower($type)]);
    }


   /**
    * Creates a new Rule of the given type
    *
    * @param    string                  Rule type name (treated case-insensitively)
    * @param    HTML_QuickForm2_Node    Element to validate by the rule
    * @param    string                  Message to display if validation fails
    * @param    mixed                   Additional data for the rule
    * @return   HTML_QuickForm2_Rule    A created Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException If rule type is unknown
    * @throws   HTML_QuickForm2_NotFoundException        If class for the rule
    *           can't be found and/or loaded from file
    */
    public static function createRule($type, HTML_QuickForm2_Node $owner,
                                      $message = '', $options = null)
    {
        $type = strtolower($type);
        if (!isset(self::$registeredRules[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Rule '$type' is not known");
        }
        list($className, $includeFile) = self::$registeredRules[$type];
        if (!class_exists($className, false)) {
            self::loadClass($className, $includeFile);
        }
        return new $className($owner, $message, $options, $type);
    }
}
?>
