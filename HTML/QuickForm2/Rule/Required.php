<?php
/**
 * Rule for required elements
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
 * @copyright 2006-2019 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

// pear-package-only /**
// pear-package-only  * Rule checking that the form field is not empty
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule/Nonempty.php';

/**
 * Rule for required elements
 *
 * The main difference from "nonempty" Rule is that
 * - elements to which this Rule is attached will be considered required
 *   ({@link HTML_QuickForm2_Node::isRequired()} will return true for them) and
 *   marked accordingly when outputting the form
 * - this Rule can only be added directly to the element and other Rules can
 *   only be added to it via and_() method
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Required extends HTML_QuickForm2_Rule_Nonempty
{
   /**
    * Disallows adding a rule to the chain with an "or" operator
    *
    * Required rules are different from all others because they affect the
    * visual representation of an element ("* denotes required field").
    * Therefore we cannot allow chaining other rules to these via or_(), since
    * this will effectively mean that the field is not required anymore and the
    * visual difference is bogus.
    *
    * @param HTML_QuickForm2_Rule $next
    *
    * @throws   HTML_QuickForm2_Exception
    */
    public function or_(HTML_QuickForm2_Rule $next)
    {
        throw new HTML_QuickForm2_Exception(
            'or_(): Cannot add a rule to "required" rule'
        );
    }

   /**
    * Sets the error message output by the rule
    *
    * Required rules cannot have an empty error message as that may allow
    * validation to succeed even if the element is empty, and that will make
    * visual difference ("* denotes required field") bogus.
    *
    * @param string $message Error message to display if validation fails
    *
    * @return   HTML_QuickForm2_Rule
    * @throws   HTML_QuickForm2_InvalidArgumentException
    */
    public function setMessage($message)
    {
        if (!strlen($message)) {
            throw new HTML_QuickForm2_InvalidArgumentException(
                '"required" rule cannot have an empty error message'
            );
        }
        return parent::setMessage($message);
    }
}
?>
