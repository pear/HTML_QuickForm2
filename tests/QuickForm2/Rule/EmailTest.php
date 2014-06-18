<?php
/**
 * Unit tests for HTML_QuickForm2 package
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
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Rule_Email class
 */
class HTML_QuickForm2_Rule_EmailTest extends PHPUnit_Framework_TestCase
{
   /**
    * @dataProvider emailProvider
    * @param string $address  "email address" being tested
    * @param bool   $expected whether given address should be valid
    */
    public function testValidate($address, $expected)
    {
        $element = new HTML_QuickForm2_Element_InputText('email');
        $element->setValue($address);
        $rule = new HTML_QuickForm2_Rule_Email($element, 'an error');
        $this->assertEquals($expected, $rule->validate());
    }

    public static function emailProvider()
    {
        // data lifted from PHP's ext/filter test suite
        return array(
            // 016.phpt
            // array('a@b.c', true), need the means to enable / disable validating host
            array('abuse@example.com', true),
            array('test!.!@#$%^&*@example.com', false),
            array('test@@#$%^&*())).com', false),
            array('test@.com', false),
            array('test@com', false),
            array('@', false),
            array('[]()/@example.com', false),
            array('QWERTYUIOPASDFGHJKLZXCVBNM@QWERTYUIOPASDFGHJKLZXCVBNM.NET', true),
            array('e.x.a.m.p.l.e.@example.com', false),
            array('firstname.lastname@employee.2something.com', true),
            array('-@foo.com', true),
            array('foo@-.com', false),
            array('foo@bar.123', false),
            array('foo@bar.-', false),
            // Bug #44445 (email validator does not handle domains starting/ending with a -)
            array("foo@-foo.com", false),
            array("foo@foo-.com", false),
            // PMOPB-45-2007:PHP ext/filter Email Validation Vulnerability
            array("test@example.com\n", false),
            // Bug #50158 (FILTER_VALIDATE_EMAIL fails with valid addresses containing = or ?)
            array("test=mail@example.com", true),
            array("test-mail@example.com", true),
            array("test+mail@example.com", true),
            array("test?mail@example.com", true),
            // Bug #55478 (FILTER_VALIDATE_EMAIL fails with internationalized domain name addresses containing >1 -)
            array("test@xn--example--7za.de", true), // "example-?.de"
            // Bug #52929 (Segfault in filter_var with FILTER_VALIDATE_EMAIL with large amount of data)
            array('xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx@yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy.zz', false),
            array(str_repeat('x', 8000), false)
        );
    }
}
?>
