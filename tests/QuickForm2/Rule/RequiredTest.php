<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2010, Alexey Borzov <avb@php.net>,
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
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Rule for required form fields
 */
require_once 'HTML/QuickForm2/Rule/Required.php';

/**
 * Element class
 */
require_once 'HTML/QuickForm2/Node.php';

/**
 * Unit test for HTML_QuickForm2_Rule_Required class
 */
class HTML_QuickForm2_Rule_RequiredTest extends PHPUnit_Framework_TestCase
{
    public function testMakesElementRequired()
    {
        $mockNode = $this->getMock(
            'HTML_QuickForm2_Node', array('updateValue', 'getId', 'getName',
            'getType', 'getValue', 'setId', 'setName', 'setValue', '__toString', 'getJavascriptValue')
        );
        $mockNode->addRule(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        $this->assertTrue($mockNode->isRequired());
    }

    public function testMustBeFirstInChain()
    {
        $mockNode = $this->getMock(
            'HTML_QuickForm2_Node', array('updateValue', 'getId', 'getName',
            'getType', 'getValue', 'setId', 'setName', 'setValue', '__toString', 'getJavascriptValue')
        );
        $rule = $mockNode->addRule($this->getMock('HTML_QuickForm2_Rule', array('validateOwner'),
                                                   array($mockNode, 'some message')));
        try {
            $rule->and_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Cannot add a "required" rule/', $e->getMessage());
            try {
                $rule->or_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertRegexp('/Cannot add a "required" rule/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testCannotAppendWithOr_()
    {
        $mockNode = $this->getMock(
            'HTML_QuickForm2_Node', array('updateValue', 'getId', 'getName',
            'getType', 'getValue', 'setId', 'setName', 'setValue', '__toString', 'getJavascriptValue')
        );
        $required = new HTML_QuickForm2_Rule_Required($mockNode, 'element is required');
        try {
            $required->or_($this->getMock('HTML_QuickForm2_Rule', array('validateOwner'),
                                          array($mockNode, 'some message')));
        } catch (HTML_QuickForm2_Exception $e) {
            $this->assertRegexp('/Cannot add a rule to "required" rule/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
    }
}
?>
