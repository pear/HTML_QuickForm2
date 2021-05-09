<?php
/**
 * Static Factory class for HTML_QuickForm2 package
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
 * @copyright 2006-2021 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Class with static methods for loading classes and files
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Loader.php';

/**
 * Static factory class
 *
 * The class handles instantiation of Element and Rule objects as well as
 * registering of new Element and Rule classes.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Factory
{
   /**
    * List of element types known to Factory
    * @var array
    */
    protected static $elementTypes = [
        'button'        => ['HTML_QuickForm2_Element_Button', null],
        'checkbox'      => ['HTML_QuickForm2_Element_InputCheckbox', null],
        'date'          => ['HTML_QuickForm2_Element_Date', null],
        'fieldset'      => ['HTML_QuickForm2_Container_Fieldset', null],
        'group'         => ['HTML_QuickForm2_Container_Group', null],
        'file'          => ['HTML_QuickForm2_Element_InputFile', null],
        'hidden'        => ['HTML_QuickForm2_Element_InputHidden', null],
        'hierselect'    => ['HTML_QuickForm2_Element_Hierselect', null],
        'image'         => ['HTML_QuickForm2_Element_InputImage', null],
        'inputbutton'   => ['HTML_QuickForm2_Element_InputButton', null],
        'password'      => ['HTML_QuickForm2_Element_InputPassword', null],
        'radio'         => ['HTML_QuickForm2_Element_InputRadio', null],
        'repeat'        => ['HTML_QuickForm2_Container_Repeat', null],
        'reset'         => ['HTML_QuickForm2_Element_InputReset', null],
        'script'        => ['HTML_QuickForm2_Element_Script', null],
        'select'        => ['HTML_QuickForm2_Element_Select', null],
        'static'        => ['HTML_QuickForm2_Element_Static', null],
        'submit'        => ['HTML_QuickForm2_Element_InputSubmit', null],
        'text'          => ['HTML_QuickForm2_Element_InputText', null],
        'textarea'      => ['HTML_QuickForm2_Element_Textarea', null]
    ];

   /**
    * List of registered rules
    * @var array
    */
    protected static $registeredRules = [
        'nonempty'      => ['HTML_QuickForm2_Rule_Nonempty', null],
        'empty'         => ['HTML_QuickForm2_Rule_Empty', null],
        'required'      => ['HTML_QuickForm2_Rule_Required', null],
        'compare'       => ['HTML_QuickForm2_Rule_Compare', null],
        'eq'            => ['HTML_QuickForm2_Rule_Compare', null,
                                 ['operator' => '===']],
        'neq'           => ['HTML_QuickForm2_Rule_Compare', null,
                                 ['operator' => '!==']],
        'lt'            => ['HTML_QuickForm2_Rule_Compare', null,
                                 ['operator' => '<']],
        'lte'           => ['HTML_QuickForm2_Rule_Compare', null,
                                 ['operator' => '<=']],
        'gt'            => ['HTML_QuickForm2_Rule_Compare', null,
                                 ['operator' => '>']],
        'gte'           => ['HTML_QuickForm2_Rule_Compare', null,
                                 ['operator' => '>=']],
        'regex'         => ['HTML_QuickForm2_Rule_Regex', null],
        'callback'      => ['HTML_QuickForm2_Rule_Callback', null],
        'length'        => ['HTML_QuickForm2_Rule_Length', null],
        'minlength'     => ['HTML_QuickForm2_Rule_Length', null,
                                 ['max' => 0]],
        'maxlength'     => ['HTML_QuickForm2_Rule_Length', null,
                                 ['min' => 0]],
        'maxfilesize'   => ['HTML_QuickForm2_Rule_MaxFileSize', null],
        'mimetype'      => ['HTML_QuickForm2_Rule_MimeType', null],
        'each'          => ['HTML_QuickForm2_Rule_Each', null],
        'notcallback'   => ['HTML_QuickForm2_Rule_NotCallback', null],
        'notregex'      => ['HTML_QuickForm2_Rule_NotRegex', null],
        'email'         => ['HTML_QuickForm2_Rule_Email', null]
    ];


   /**
    * Registers a new element type
    *
    * @param string $type        Type name (treated case-insensitively)
    * @param string $className   Class name
    * @param string $includeFile File containing the class, leave empty if class already loaded
    */
    public static function registerElement($type, $className, $includeFile = null)
    {
        self::$elementTypes[strtolower($type)] = [$className, $includeFile];
    }


   /**
    * Checks whether an element type is known to factory
    *
    * @param string $type Type name (treated case-insensitively)
    *
    * @return   bool
    */
    public static function isElementRegistered($type)
    {
        return isset(self::$elementTypes[strtolower($type)]);
    }


   /**
    * Creates a new element object of the given type
    *
    * @param string       $type       Type name (treated case-insensitively)
    * @param string       $name       Element name (passed to element's constructor)
    * @param string|array $attributes Element attributes (passed to element's constructor)
    * @param array        $data       Element-specific data (passed to element's constructor)
    *
    * @return   HTML_QuickForm2_Node     A created element
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the element can
    *           not be found and/or loaded from file
    */
    public static function createElement(
        $type, $name = null, $attributes = null, array $data = []
    ) {
        $type = strtolower($type);
        if (!isset(self::$elementTypes[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Element type '$type' is not known");
        }
        list($className, $includeFile) = self::$elementTypes[$type];
        HTML_QuickForm2_Loader::loadClass($className, $includeFile, true);
        return new $className($name, $attributes, $data);
    }


   /**
    * Registers a new rule type
    *
    * @param string $type        Rule type name (treated case-insensitively)
    * @param string $className   Class name
    * @param string $includeFile File containing the class,
    *                            leave empty if class already loaded
    * @param mixed  $config      Configuration data for rules of the given type
    */
    public static function registerRule(
        $type, $className, $includeFile = null, $config = null
    ) {
        self::$registeredRules[strtolower($type)] = [$className, $includeFile, $config];
    }


   /**
    * Checks whether a rule type is known to Factory
    *
    * @param string $type Rule type name (treated case-insensitively)
    *
    * @return   bool
    */
    public static function isRuleRegistered($type)
    {
        return isset(self::$registeredRules[strtolower($type)]);
    }


   /**
    * Creates a new Rule of the given type
    *
    * @param string               $type    Rule type name (treated case-insensitively)
    * @param HTML_QuickForm2_Node $owner   Element to validate by the rule
    * @param string               $message Message to display if validation fails
    * @param mixed                $config  Configuration data for the rule
    *
    * @return   HTML_QuickForm2_Rule    A created Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException If rule type is unknown
    * @throws   HTML_QuickForm2_NotFoundException        If class for the rule
    *           can't be found and/or loaded from file
    */
    public static function createRule(
        $type, HTML_QuickForm2_Node $owner, $message = '', $config = null
    ) {
        $type = strtolower($type);
        if (!isset(self::$registeredRules[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Rule '$type' is not known");
        }
        list($className, $includeFile) = self::$registeredRules[$type];
        HTML_QuickForm2_Loader::loadClass($className, $includeFile, true);
        if (isset(self::$registeredRules[$type][2])) {
            $config = call_user_func(
                [$className, 'mergeConfig'],
                $config, self::$registeredRules[$type][2]
            );
        }
        return new $className($owner, $message, $config);
    }
}
?>
