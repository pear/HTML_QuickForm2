<?php
/**
 * Validates email address
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2012, Alexey Borzov <avb@php.net>,
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
 * @version  SVN: $Id: Regex.php 323363 2012-02-19 15:09:07Z avb $
 * @link     http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Base class for HTML_QuickForm2 rules
 */
require_once 'HTML/QuickForm2/Rule.php';

/**
 * Validates email address
 *
 * There are many different ways to validate an email address.
 * Our goal is not to fully be RFC822 compatible, if this is your need, 
 * you can try the PHP Filter function with FILTER_VALIDATE_EMAIL.
 * Our method was designed with registration forms in mind, where a user 
 * usually enters her email address in order to receive a newsletter or
 * a confirmation, for example.
 * So unlike FILTER_VALIDATE_EMAIL, we do not consider addresses like 
 * root@[127.0.0.1] or root@[IPv6:::] valid. We also do not accept addresses 
 * with comments, quotes, escapes. We only accept the following
 * special characters +_-. As recommended in the RFC, the local part must 
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
        if ($domainlen < 4 && $domainlen > 255) {
            return false;
        }
        $locals = explode('.', $parts[0]);
        foreach ($locals as $local) {
            if (!preg_match('/^[a-z0-9\_\+\-]+$/iD', $local)) {
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