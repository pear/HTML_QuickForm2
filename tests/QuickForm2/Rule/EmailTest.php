<?php
/**
 * Unit tests for HTML_QuickForm2 package
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
