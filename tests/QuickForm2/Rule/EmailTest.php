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
 * @copyright 2006-2023 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Rule_Email class
 */
class HTML_QuickForm2_Rule_EmailTest extends TestCase
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
        return [
            // 016.phpt
            // array('a@b.c', true), need the means to enable / disable validating host
            ['abuse@example.com', true],
            ['test!.!@#$%^&*@example.com', false],
            ['test@@#$%^&*())).com', false],
            ['test@.com', false],
            ['test@com', false],
            ['@', false],
            ['[]()/@example.com', false],
            ['QWERTYUIOPASDFGHJKLZXCVBNM@QWERTYUIOPASDFGHJKLZXCVBNM.NET', true],
            ['e.x.a.m.p.l.e.@example.com', false],
            ['firstname.lastname@employee.2something.com', true],
            ['-@foo.com', true],
            ['foo@-.com', false],
            ['foo@bar.123', false],
            ['foo@bar.-', false],
            // Bug #44445 (email validator does not handle domains starting/ending with a -)
            ["foo@-foo.com", false],
            ["foo@foo-.com", false],
            // PMOPB-45-2007:PHP ext/filter Email Validation Vulnerability
            ["test@example.com\n", false],
            // Bug #50158 (FILTER_VALIDATE_EMAIL fails with valid addresses containing = or ?)
            ["test=mail@example.com", true],
            ["test-mail@example.com", true],
            ["test+mail@example.com", true],
            ["test?mail@example.com", true],
            // Bug #55478 (FILTER_VALIDATE_EMAIL fails with internationalized domain name addresses containing >1 -)
            ["test@xn--example--7za.de", true], // "example-?.de"
            // Bug #52929 (Segfault in filter_var with FILTER_VALIDATE_EMAIL with large amount of data)
            ['xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx@yyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyyy.zz', false],
            [str_repeat('x', 8000), false]
        ];
    }
}
?>
