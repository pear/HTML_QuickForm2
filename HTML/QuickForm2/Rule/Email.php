<?php
/**
 * Validates email address
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
// pear-package-only  * Base class for HTML_QuickForm2 rules
// pear-package-only  */
// pear-package-only require_once 'HTML/QuickForm2/Rule.php';

/**
 * Validates email address
 *
 * Email address format as defined in RFCs 822, 2822 and 5322 is quite complex
 * and has many uncommon features. This rule was designed with registration
 * forms in mind, where a user usually enters her email address
 * in order to receive a newsletter or a confirmation. So it currently
 * supports a most common subset of email address format, disallowing
 *  - domain literals like root@[127.0.0.1] or root@[IPv6:::]
 *  - quoted strings in local part "John Doe"@example.com
 *
 * Support for these may be added in the future, until then you may consider
 * using PHP filter_var() function with FILTER_VALIDATE_EMAIL. Note also that
 * the characters "-", "_", "+", "!", "#", "$", "%", "&", "'", "*", "/",  "=",
 * "?", "^", "`", "{", "|", "}", "~" are valid in the local part of email
 * address according to RFC, though most of these are highly improbable in any
 * reallife address. We may add a means of limiting this list in the future,
 * for now you may use the 'notregex' rule to disallow some of these if needed.
 *
 * As recommended in the RFC, the local part must
 * not be longer than 64 characters and the domain part 255. This means
 * that the address can be up to 320 characters long. The number of
 * subdomains is arbitrary limited to 10. You can use the maxlength rule in
 * conjunction if you need to enforce shorter addresses. A length of 100
 * for the whole address is usually far enough.
 *
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @version  Release: @package_version@
 * @link     https://pear.php.net/package/HTML_QuickForm2
 */
class HTML_QuickForm2_Rule_Email extends HTML_QuickForm2_Rule
{
   /**
    * Validates the owner element
    *
    * @return   bool    whether element's value is in a commonly used email address format
    */
    protected function validateOwner()
    {
        $value = $this->owner->getValue();
        $len = strlen($value);
        if (empty($len)) {
            return true;
        }
        $parts = explode('@', $value);
        if (count($parts) != 2) {
            return false;
        }
        $locallen = strlen($parts[0]);
        if ($locallen > 64) {
            return false;
        }
        $domainlen = $len - $locallen;
        if ($domainlen < 4 || $domainlen > 255) {
            return false;
        }
        foreach (explode('.', $parts[0]) as $local) {
            if (!preg_match('/^[A-Za-z0-9!#$%&\'*+\\/=?^_`{|}~-]+$/D', $local)) {
                return false;
            }
        }
        if (!preg_match('/^([a-z0-9][a-z0-9\-]*[a-z0-9]|[a-z0-9])(\.([a-z0-9][a-z0-9\-]*[a-z0-9]|[a-z0-9])){0,10}\.([a-z]{2,}){1}$/iD', $parts[1])) {
            return false;
        }
        return true;
    }

   /**
    * Returns the client-side validation callback
    *
    * @return   string
    */
    protected function getJavascriptCallback()
    {
        return "function() { return qf.rules.email(" . $this->owner->getJavascriptValue() . "); }";
    }
}
?>