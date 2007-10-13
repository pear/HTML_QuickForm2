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
 * Rule checking the value's length
 */
require_once 'HTML/QuickForm2/Rule/Length.php';

/**
 * Element class
 */
require_once 'HTML/QuickForm2/Element.php';

/**
 * Unit test for HTML_QuickForm2_Rule_Length class 
 */
class HTML_QuickForm2_Rule_LengthTest extends PHPUnit_Framework_TestCase
{
    public function testLengthIsRequired()
    {
        $mockEl = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                 'getValue', 'setValue', '__toString'));
        $length = new HTML_QuickForm2_Rule_Length($mockEl, 'an error');
        try {
            $length->validate();
        } catch (HTML_QuickForm2_Exception $e) {
            $this->assertRegexp('/Length Rule requires an allowed length parameter/', $e->getMessage());
            $length2 = new HTML_QuickForm2_Rule_Length($mockEl, 'another error', array());
            try {
                $length2->validate();
            } catch (HTML_QuickForm2_Exception $e) {
                $this->assertRegexp('/Length Rule requires an allowed length parameter/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
    }

    public function testScalarLengthIsPositive()
    {
        $mockEl = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                 'getValue', 'setValue', '__toString'));
        $lengthZero = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 0);
        try {
            $lengthZero->validate();
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Length Rule requires at least one non-zero parameter/', $e->getMessage());
            $lengthNegative = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', -1);
            try {
                $lengthNegative->validate();
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertRegexp('/Length Rule requires parameters to be nonnegative/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testMinMaxLengthIsNonnegative()
    {
        $mockEl = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                 'getValue', 'setValue', '__toString'));
        $lengthZeros = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                        array('min' => 0, 'max' => 0));
        try {
            $lengthZeros->validate();
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Length Rule requires at least one non-zero parameter/', $e->getMessage());
            $lengthNegative = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                              array('min' => -1, 'max' => 1));
            try {
                $lengthNegative->validate();
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertRegexp('/Length Rule requires parameters to be nonnegative/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testOptionsHandling()
    {
        $mockEl  = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockEl->expects($this->atLeastOnce())
               ->method('getValue')->will($this->returnValue('foo'));

        $length3 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 3);
        $length5 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', 5);
        $this->assertTrue($length3->validate());
        $this->assertFalse($length5->validate());

        $length2_4_assoc = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                           array('min' => 2, 'max' => 4));
        $length4_2_assoc = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                           array('min' => 4, 'max' => 2));
        $length2_4_num   = new HTML_QuickForm2_Rule_Length($mockEl, 'an error', array(2, 4));
        $this->assertTrue($length2_4_assoc->validate());
        $this->assertTrue($length4_2_assoc->validate());
        $this->assertTrue($length2_4_num->validate());

        $length5_6_assoc = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                           array('min' => 5, 'max' => 6));
        $this->assertFalse($length5_6_assoc->validate());

        $minLength2 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                      array('min' => 2));
        $maxLength2 = new HTML_QuickForm2_Rule_Length($mockEl, 'an error',
                                                      array('max' => 2));
        $this->assertTrue($minLength2->validate());
        $this->assertFalse($maxLength2->validate());
    }

    public function testConfigHandling()
    {
        $mockElFoo = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockElFoo->expects($this->atLeastOnce())
                  ->method('getValue')->will($this->returnValue('foo'));
        $mockElFoobar = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockElFoobar->expects($this->atLeastOnce())
                     ->method('getValue')->will($this->returnValue('foobar'));

        HTML_QuickForm2_Factory::registerRule('length-3', 'HTML_QuickForm2_Rule_Length',
                                              null, 3);
        $length3Foo    = $mockElFoo->addRule('length-3', 'an error');
        $length3Foobar = $mockElFoobar->addRule('length-3', 'an error');
        $this->assertTrue($length3Foo->validate());
        $this->assertFalse($length3Foobar->validate());

        HTML_QuickForm2_Factory::registerRule('length-2-4', 'HTML_QuickForm2_Rule_Length',
                                              null, array('min' => 2, 'max' => 4));
        $length24Foo    = $mockElFoo->addRule('length-2-4', 'an error');
        $length24Foobar = $mockElFoobar->addRule('length-2-4', 'an error');
        $this->assertTrue($length24Foo->validate());
        $this->assertFalse($length24Foobar->validate());
    }

    public function testMinlengthScalarHandling()
    {
        $mockElFoo = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockElFoo->expects($this->atLeastOnce())
                  ->method('getValue')->will($this->returnValue('foo'));

        HTML_QuickForm2_Factory::registerRule('test-minlength', 'HTML_QuickForm2_Rule_Length',
                                              null, array('max' => 0));
        $minLength2 = $mockElFoo->addRule('test-minlength', 'too short', 2);
        $minLength5 = $mockElFoo->addRule('test-minlength', 'too short', 5);
        $this->assertTrue($minLength2->validate());
        $this->assertFalse($minLength5->validate());
    }

    public function testConfigOverridesOptions()
    {
        HTML_QuickForm2_Factory::registerRule('length-1-5', 'HTML_QuickForm2_Rule_Length',
                                              null, array('min' => 1, 'max' => 5));
        $mockElFoo = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockElFoo->expects($this->atLeastOnce())
                  ->method('getValue')->will($this->returnValue('foo'));
        $mockElFoobar = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                  'getValue', 'setValue', '__toString'));
        $mockElFoobar->expects($this->atLeastOnce())
                     ->method('getValue')->will($this->returnValue('foobar'));

        $ruleFoo = $mockElFoo->addRule('length-1-5', 'not in range', array('min' => 4));
        $this->assertTrue($ruleFoo->validate());
        $ruleFoobar = $mockElFoobar->addRule('length-1-5', 'not in range', array('max' => 7));
        $this->assertFalse($ruleFoobar->validate());
    }

    public function testEmptyFieldsAreSkipped()
    {
        $mockEmpty = $this->getMock('HTML_QuickForm2_Element', array('getType', 
                                    'getValue', 'setValue', '__toString'));
        $mockEmpty->expects($this->once())->method('getValue')
                  ->will($this->returnValue(''));
        $length = new HTML_QuickForm2_Rule_Length($mockEmpty, 'an error', array('min' => 5));
        $this->assertTrue($length->validate());
    }
}
?>