<?php
/**
 * Validates email address
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
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
 * @category HTML
 * @package  HTML_QuickForm2
 * @author   Alexey Borzov <avb@php.net>
 * @author   Bertrand Mansion <golgote@mamasam.com>
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
require_once 'HTML/QuickForm2/Rule.php';

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
 * @license  http://opensource.org/licenses/bsd-license.php New BSD License
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/HTML_QuickForm2
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