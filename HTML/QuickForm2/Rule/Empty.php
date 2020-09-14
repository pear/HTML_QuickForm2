<?php
/**
 * Rule checking that the field is empty
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
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Rule checking that the field is empty
 *
 * Handles both simple form fields and file uploads, the latter are considered
 * valid iff no file upload was attempted.
 *
 * The rule doesn't make much sense if used separately, but can be very helpful
 * if chained:
 * <code>
 * $spamCheck->addRule('empty')
 *           ->or_($email->createRule('nonempty', 'Supply a valid email if you want to receive our spam')
 *                       ->and_($email->createRule('email')));
 * </code>
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Empty extends HTML_QuickForm2_Rule
{
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        if ($this->owner instanceof HTML_QuickForm2_Element_InputFile) {
            return isset($value['error']) && UPLOAD_ERR_NO_FILE == $value['error'];
        } elseif (is_array($value)) {
            return 0 == count(array_filter($value, 'strlen'));
        } else {
            return 0 == strlen($value);
        }
    }

    protected function getJavascriptCallback()
    {
        return "function() { return qf.rules.empty(" . $this->owner->getJavascriptValue() . "); }";
    }
}

?>