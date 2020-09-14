<?php
/**
 * Checks that the element's value does not match a regular expression
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
 * @copyright 2006-2020 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Validates values using regular expressions
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule/Regex.php';

/**
 * Checks that the element's value does not match a regular expression
 *
 * The Rule behaves like Regex Rule, but it considers the element valid if its
 * value does not match the given regular expression.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_NotRegex extends HTML_QuickForm2_Rule_Regex
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether element's value does not match given regular expression
    */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if ($this->owner instanceof HTML_QuickForm2_Element_InputFile) {
            if (!isset($value['error']) || UPLOAD_ERR_NO_FILE == $value['error']) {
                return true;
            }
            $value = $value['name'];
        } elseif (!strlen($value)) {
            return true;
        }
        return !preg_match($this->getConfig() . 'D', $value);
    }

   /**
    * Returns the client-side validation callback
    *
    * For this to work properly, slashes have to be used as regex delimiters.
    * The method takes care of transforming PHP unicode escapes in regexps to
    * JS unicode escapes if using 'u' modifier (see bug #12736)
    *
    * @return   string
    */
    protected function getJavascriptCallback()
    {
        $regex = $this->getConfig();

        if ($pos = strpos($regex, 'u', strrpos($regex, '/'))) {
            $regex = substr($regex, 0, $pos) . substr($regex, $pos + 1);
            $regex = preg_replace('/(?<!\\\\)(?>\\\\\\\\)*\\\\x{([a-fA-F0-9]+)}/', '\\u$1', $regex);
        }

        return "function() { var value = " . $this->owner->getJavascriptValue() .
               "; return qf.rules.empty(value) || !{$regex}.test(value); }";
    }
}
?>