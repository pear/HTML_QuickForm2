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
 * Unit test for HTML_QuickForm2_Rule_Length class
 */
class HTML_QuickForm2_Rule_LengthTest extends TestCase
{
    public function testLimitsAreRequired()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        try {
            $length = new HTML_QuickForm2_Rule_Length($mockEl, 'an error');
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Length Rule requires at least one non-zero limit/', $e->getMessage());
        }
        try {
            $length2 = new HTML_QuickForm2_Rule_Length($mockEl, 'another error', []);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Length Rule requires at least one non-zero limit/', $e->getMessage());
        }
    }

    public function testScalarLengthIsPositive()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        try {
            $lengthZero = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 0);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Length Rule requires at least one non-zero limit/', $e->getMessage());
        }
        try {
            $lengthNegative = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', -1);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Length Rule requires limits to be nonnegative/', $e->getMessage());
            return;
        }
    }

    public function testMinMaxLengthIsNonnegative()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        try {
            $lengthZeros = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                           ['min' => 0, 'max' => 0]);
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Length Rule requires at least one non-zero limit/', $e->getMessage());
        }
        try {
            $lengthNegative = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                              ['min' => -1, 'max' => 1]);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Length Rule requires limits to be nonnegative/', $e->getMessage());
        }
    }

    public function testLimitsHandling()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                  'getRawValue', 'setValue', '__toString'])
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));

        $length3 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 3);
        $this->assertTrue($length3->validate());

        $length5 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 5);
        $this->assertFalse($length5->validate());

        $length2_4 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', ['min' => 2, 'max' => 4]);
        $this->assertTrue($length2_4->validate());

        $length5_6 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                     ['min' => 5, 'max' => 6]);
        $this->assertFalse($length5_6->validate());

        $minLength2 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                      ['min' => 2]);
        $this->assertTrue($minLength2->validate());

        $maxLength2 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                      ['max' => 2]);
        $this->assertFalse($maxLength2->validate());
    }

    public function testConfigCanonicalForm()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        $length = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', ['min' => 4, 'max' => 2]);
        $this->assertEquals(['min' => 2, 'max' => 4], $length->getConfig());

        $length->setConfig([2, 4]);
        $this->assertEquals(['min' => 2, 'max' => 4], $length->getConfig());

        $length->setConfig(['min' => 2]);
        $this->assertEquals(['min' => 2, 'max' => 0], $length->getConfig());

        $length->setConfig(['max' => 2]);
        $this->assertEquals(['min' => 0, 'max' => 2], $length->getConfig());
    }

    public function testGlobalConfigOverrides()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();

        $scalar = new HTML_QuickForm2_Rule_Length(
            $mockEl, 'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(3, 4)
        );
        $this->assertEquals(4, $scalar->getConfig());

        $scalar2 = new HTML_QuickForm2_Rule_Length(
            $mockEl, 'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(['min' => 1, 'max' => 2], 3)
        );
        $this->assertEquals(3, $scalar2->getConfig());

        $array = new HTML_QuickForm2_Rule_Length(
            $mockEl, 'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(['min' => 1, 'max' => 2],
                                                     ['min' => 3, 'max' => 4])
        );
        $this->assertEquals(['min' => 3, 'max' => 4], $array->getConfig());

        $array2 = new HTML_QuickForm2_Rule_Length(
            $mockEl, 'an error',
            HTML_QuickForm2_Rule_Length::mergeConfig(123, ['min' => 3, 'max' => 4])
        );
        $this->assertEquals(['min' => 3, 'max' => 4], $array2->getConfig());
    }

    public function testConfigMerging()
    {
        $this->assertEquals(
            ['min' => 1, 'max' => 0],
            HTML_QuickForm2_Rule_Length::mergeConfig(1, ['max' => 0])
        );

        $this->assertEquals(
            ['min' => 1, 'max' => 0],
            HTML_QuickForm2_Rule_Length::mergeConfig(['min' => 1], ['max' => 0])
        );

        $this->assertEquals(
            ['min' => 1, 'max' => 0],
            HTML_QuickForm2_Rule_Length::mergeConfig(['min' => 1, 'max' => 5],
                                                     ['max' => 0])
        );
    }

    public function testEmptyFieldsAreSkipped()
    {
        $mockEmpty = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                    'getRawValue', 'setValue', '__toString'])
            ->getMock();
        $mockEmpty->expects($this->once())->method('getRawValue')
                  ->will($this->returnValue(''));
        $length = new HTML_QuickForm2_Rule_Length($mockEmpty, 'an error', ['min' => 5]);
        $this->assertTrue($length->validate());
    }
}
?>