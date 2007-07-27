<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006, 2007, Alexey Borzov <avb@php.net>,
 *                           Bertrand Mansion <golgote@mamasam.com>
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
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * PHPUnit Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Rule checking that the form field is not empty
 */
require_once 'HTML/QuickForm2/Rule/Compare.php';

/**
 * Element class
 */
require_once 'HTML/QuickForm2/Element.php';

/**
 * Unit test for HTML_QuickForm2_Rule_Compare class 
 */
class HTML_QuickForm2_Rule_CompareTest extends PHPUnit_Framework_TestCase
{
    public function testOperandRequired()
    {
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $compare = new HTML_QuickForm2_Rule_Compare($mockEl, 'some error');
        try {
            $compare->validate();
        } catch (HTML_QuickForm2_Exception $e) {
            $this->assertRegexp('/Compare Rule needs an argument to compare with/', $e->getMessage());
            $compare2 = new HTML_QuickForm2_Rule_Compare($mockEl, 'some error', array());
            try {
                $compare2->validate();
            } catch (HTML_QuickForm2_Exception $e2) {
                $this->assertRegexp('/Compare Rule needs an argument to compare with/', $e2->getMessage(), 'Wrong exception');
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
    }

    public function testDefaultOperatorIsEqual()
    {
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockEl->expects($this->exactly(2))->method('getValue')
               ->will($this->returnValue('foo'));

        $compareFoo = new HTML_QuickForm2_Rule_Compare($mockEl, 'bogus error', 'foo');
        $this->assertTrue($compareFoo->validate());

        $compareBar = new HTML_QuickForm2_Rule_Compare($mockEl, 'not equal', 'bar');
        $this->assertFalse($compareBar->validate());
    }

    public function testCompareToOtherElement()
    {
        $mockFirst = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockFirst->expects($this->once())->method('getValue')
                  ->will($this->returnValue('foo'));
        $mockSecond = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockSecond->expects($this->once())->method('getValue')
                   ->will($this->returnValue('bar'));

        $compareElements = new HTML_QuickForm2_Rule_Compare($mockFirst, 'not equal', $mockSecond);
        $this->assertFalse($compareElements->validate());
        $this->assertEquals('not equal', $mockFirst->getError());
    }

    public function testDisallowBogusOperators()
    {
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $bogus = new HTML_QuickForm2_Rule_Compare($mockEl, 'bogus error', array('foo', 'bar'));
        try {
            $bogus->validate();
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/is not a valid comparison operator/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testOptionsHandling()
    {
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockEl->expects($this->atLeastOnce())
               ->method('getValue')->will($this->returnValue('foo'));

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
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockEl->expects($this->atLeastOnce())
               ->method('getValue')->will($this->returnValue('foo'));

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
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockEl->expects($this->atLeastOnce())
               ->method('getValue')->will($this->returnValue('foo'));
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
        $mockFrom = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                   'getValue', 'setValue', '__toString'));
        $mockFrom->expects($this->once())->method('getValue')
                 ->will($this->returnValue('00080002310000114151'));
        $mockTo   = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                   'getValue', 'setValue', '__toString'));
        $mockTo->expects($this->once())->method('getValue')
               ->will($this->returnValue('00080002310000114152'));

        $ruleAccounts = new HTML_QuickForm2_Rule_Compare(
            $mockFrom, 'You cannot transfer money to and from same account',
            array('!=', $mockTo)
        );
        $this->assertTrue($ruleAccounts->validate());
    }
}
?>
