<?php
/**
 * Static Factory class for HTML_QuickForm2 package
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
        'select'    => array('HTML_QuickForm2_Element_Select',
                             'HTML/QuickForm2/Element/Select.php'),
        'textarea'  => array('HTML_QuickForm2_Element_Textarea',
                             'HTML/QuickForm2/Element/Textarea.php'),
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
    * @param    mixed   Options for the element (passed to element's constructor)
    * @param    mixed   Element label (passed to element's constructor)
    * @param    mixed   Element attributes (passed to element's constructor)
    * @return   HTML_QuickForm2_AbstractElement     A created element
    * @throws   HTML_QuickForm2_InvalidArgumentException If type name is unknown
    * @throws   HTML_QuickForm2_NotFoundException If class for the element can 
    *           not be found and/or loaded from file 
    */
    public static function createElement($type, $name = null, $options = null, 
                                         $label = null, $attributes = null)
    {
        $type = strtolower($type);
        if (!isset(self::$elementTypes[$type])) {
            throw new HTML_QuickForm2_InvalidArgumentException("Element type '$type' is not known");
        }
        list($className, $includeFile) = self::$elementTypes[$type];
        if (!class_exists($className, false)) {
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
        return new $className($name, $options, $label, $attributes);
    }
}
?>
