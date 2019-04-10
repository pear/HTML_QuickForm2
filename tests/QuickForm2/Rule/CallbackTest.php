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
 * Unit test for HTML_QuickForm2_Rule_Callback class
 */
class HTML_QuickForm2_Rule_CallbackTest extends PHPUnit_Framework_TestCase
{
    public function checkNotFoo($value)
    {
        return $value != 'foo';
    }

    public function testValidCallbackRequired()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        try {
            $callbackMissing = new HTML_QuickForm2_Rule_Callback($mockEl, 'an error');
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Callback Rule requires a valid callback/', $e->getMessage());
        }
        try {
            $callbackBogus = new HTML_QuickForm2_Rule_Callback($mockEl, 'an error',
                                    array('callback' => 'bogusfunctionname'));
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Callback Rule requires a valid callback/', $e->getMessage());
        }
    }

    public function testOptionsHandling()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));

        $strlen = new HTML_QuickForm2_Rule_Callback($mockEl, 'an error', 'strlen');
        $this->assertTrue($strlen->validate());

        $notFoo = new HTML_QuickForm2_Rule_Callback($mockEl, 'an error', array($this, 'checkNotFoo'));
        $this->assertFalse($notFoo->validate());

        $inArray = new HTML_QuickForm2_Rule_Callback($mockEl, 'an error',
                        array('callback' => 'in_array',
                              'arguments' => array(array('foo', 'bar', 'baz'))));
        $this->assertTrue($inArray->validate());
    }

    public function testConfigHandling()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));

        HTML_QuickForm2_Factory::registerRule('strlen', 'HTML_QuickForm2_Rule_Callback', null, 'strlen');
        $strlen = HTML_QuickForm2_Factory::createRule('strlen', $mockEl, 'an error');
        $this->assertTrue($strlen->validate());

        HTML_QuickForm2_Factory::registerRule('inarray', 'HTML_QuickForm2_Rule_Callback', null,
                                    array('callback' => 'in_array',
                                          'arguments' => array(array('foo', 'bar', 'baz'))));
        $inArray = HTML_QuickForm2_Factory::createRule('inarray', $mockEl, 'an error');
        $this->assertTrue($inArray->validate());

        HTML_QuickForm2_Factory::registerRule('inarray2', 'HTML_QuickForm2_Rule_Callback', null, 'in_array');
        $inArray2 = HTML_QuickForm2_Factory::createRule('inarray2', $mockEl, 'an error',
                                array(array('one', 'two', 'three')));
        $this->assertFalse($inArray2->validate());
    }

    public function testConfigOverridesOptions()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));

        HTML_QuickForm2_Factory::registerRule('inarray-override', 'HTML_QuickForm2_Rule_Callback', null,
                                    array('callback' => 'in_array',
                                          'arguments' => array(array('foo', 'bar', 'baz'))));
        $rule1 = HTML_QuickForm2_Factory::createRule('inarray-override', $mockEl, 'an error',
                                    array('callback' => array($this, 'checkNotFoo')));
        $rule2 = HTML_QuickForm2_Factory::createRule('inarray-override', $mockEl, 'an error',
                                    array('arguments' => array(array('one', 'two', 'three'))));
        $this->assertTrue($rule1->validate());
        $this->assertTrue($rule2->validate());
    }
}
?>
