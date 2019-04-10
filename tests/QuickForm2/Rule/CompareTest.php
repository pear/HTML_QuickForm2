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
 * Unit test for HTML_QuickForm2_Rule_Compare class
 */
class HTML_QuickForm2_Rule_CompareTest extends PHPUnit_Framework_TestCase
{
    public function testOperandRequired()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        try {
            $compare = new HTML_QuickForm2_Rule_Compare($mockEl, 'some error');
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Compare Rule requires an argument to compare with/', $e->getMessage());
        }
        try {
            $compare2 = new HTML_QuickForm2_Rule_Compare($mockEl, 'some error', array());
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e2) {
            $this->assertRegexp('/Compare Rule requires an argument to compare with/', $e2->getMessage(), 'Wrong exception');
        }
    }

    public function testDefaultOperatorIsEqual()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->exactly(2))->method('getRawValue')
               ->will($this->returnValue('foo'));

        $compareFoo = new HTML_QuickForm2_Rule_Compare($mockEl, 'bogus error', 'foo');
        $this->assertTrue($compareFoo->validate());

        $compareBar = new HTML_QuickForm2_Rule_Compare($mockEl, 'not equal', 'bar');
        $this->assertFalse($compareBar->validate());
    }

    public function testCompareToOtherElement()
    {
        $mockFirst = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockFirst->expects($this->once())->method('getRawValue')
                  ->will($this->returnValue('foo'));
        $mockSecond = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockSecond->expects($this->once())->method('getRawValue')
                   ->will($this->returnValue('bar'));

        $compareElements = new HTML_QuickForm2_Rule_Compare($mockFirst, 'not equal', $mockSecond);
        $this->assertFalse($compareElements->validate());
        $this->assertEquals('not equal', $mockFirst->getError());
    }

    public function testDisallowBogusOperators()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        try {
            $bogus = new HTML_QuickForm2_Rule_Compare($mockEl, 'bogus error', array('foo', 'bar'));
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Compare Rule requires a valid comparison operator/', $e->getMessage());
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

        $scalar = new HTML_QuickForm2_Rule_Compare($mockEl, '...', 'foo');
        $this->assertTrue($scalar->validate());

        $arraySingle = new HTML_QuickForm2_Rule_Compare($mockEl, '...', array('foo'));
        $this->assertTrue($arraySingle->validate());

        $arrayNumeric = new HTML_QuickForm2_Rule_Compare($mockEl, '...', array('!==', 'bar'));
        $this->assertTrue($arrayNumeric->validate());

        $arrayAssoc = new HTML_QuickForm2_Rule_Compare($mockEl, '...',
                            array('operator' => '!==', 'operand' => 'bar'));
        $this->assertTrue($arrayAssoc->validate());
    }

    public function testConfigHandling()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));

        HTML_QuickForm2_Factory::registerRule('compare-scalar', 'HTML_QuickForm2_Rule_Compare', null, '!==');
        $scalar = HTML_QuickForm2_Factory::createRule('compare-scalar', $mockEl, '...', 'bar');
        $this->assertTrue($scalar->validate());

        HTML_QuickForm2_Factory::registerRule('compare-single', 'HTML_QuickForm2_Rule_Compare', null, array('!=='));
        $arraySingle = HTML_QuickForm2_Factory::createRule('compare-single', $mockEl, '...', 'bar');
        $this->assertTrue($arraySingle->validate());

        HTML_QuickForm2_Factory::registerRule('compare-numeric', 'HTML_QuickForm2_Rule_Compare', null, array('!==', 'bar'));
        $arrayNumeric = HTML_QuickForm2_Factory::createRule('compare-numeric', $mockEl, '...');
        $this->assertTrue($arrayNumeric->validate());

        HTML_QuickForm2_Factory::registerRule('compare-assoc', 'HTML_QuickForm2_Rule_Compare', null,
                                              array('operator' => '!==', 'operand' => 'bar'));
        $arrayAssoc = HTML_QuickForm2_Factory::createRule('compare-assoc', $mockEl, '...');
        $this->assertTrue($arrayAssoc->validate());
    }

    public function testConfigOverridesOptions()
    {
        $mockEl  = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                  'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockEl->expects($this->atLeastOnce())
               ->method('getRawValue')->will($this->returnValue('foo'));
        HTML_QuickForm2_Factory::registerRule('compare-override', 'HTML_QuickForm2_Rule_Compare', null,
                                              array('operator' => '===', 'operand' => 'foo'));
        $rule1 = HTML_QuickForm2_Factory::createRule('compare-override', $mockEl, '...',
                                                     array('operator' => '===', 'operand' => 'bar'));
        $rule2 = HTML_QuickForm2_Factory::createRule('compare-override', $mockEl, '...',
                                                     array('operator' => '!==', 'operand' => 'foo'));
        $this->assertTrue($rule1->validate());
        $this->assertTrue($rule2->validate());
    }

    public function testBug10754()
    {
        $mockFrom = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                   'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockFrom->expects($this->once())->method('getRawValue')
                 ->will($this->returnValue('00080002310000114151'));
        $mockTo   = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                   'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockTo->expects($this->once())->method('getRawValue')
               ->will($this->returnValue('00080002310000114152'));

        $ruleAccounts = new HTML_QuickForm2_Rule_Compare(
            $mockFrom, 'You cannot transfer money to and from same account',
            array('!=', $mockTo)
        );
        $this->assertTrue($ruleAccounts->validate());
    }

    public function testValidationTriggers()
    {
        $foo = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType', 'getRawValue', 'setValue', '__toString'))
            ->setConstructorArgs(array('foo', array('id' => 'foo')))
            ->getMock();
        $bar = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType', 'getRawValue', 'setValue', '__toString'))
            ->setConstructorArgs(array('bar', array('id' => 'bar')))
            ->getMock();
        $compare = new HTML_QuickForm2_Rule_Compare($foo, '...', $bar);
        $this->assertContains('["foo","bar"]', $compare->getJavascript());
    }
}
?>
