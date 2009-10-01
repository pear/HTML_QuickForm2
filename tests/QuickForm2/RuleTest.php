<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2009, Alexey Borzov <avb@php.net>,
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
 * Base class for HTML_QuickForm2 rules
 */
require_once 'HTML/QuickForm2/Rule.php';

/**
 * PHPUnit Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Class for <input type="text" /> elements
 *
 * We need a subclass of Node that can be instantiated
 */
require_once 'HTML/QuickForm2/Element/InputText.php';

/**
 * The non-abstract subclass of Rule
 */
class HTML_QuickForm2_Rule_ImplConst extends HTML_QuickForm2_Rule
{
    protected function checkValue($value)
    {
        // It just returns whatever value was passed to setOptions()
        return $this->options;
    }
}

/**
 * Unit test for HTML_QuickForm2_Rule class
 */
class HTML_QuickForm2_RuleTest extends PHPUnit_Framework_TestCase
{
    public function testSetAndGetOptions()
    {
        $rule = new HTML_QuickForm2_Rule_ImplConst(
            new HTML_QuickForm2_Element_InputText('foo'), 'a message', 'bar'
        );
        $this->assertEquals('bar', $rule->getOptions());

        $this->assertSame($rule, $rule->setOptions('baz'));
        $this->assertEquals('baz', $rule->getOptions());
    }

    public function testSetAndGetMessage()
    {
        $rule = new HTML_QuickForm2_Rule_ImplConst(
            new HTML_QuickForm2_Element_InputText('foo'), 'a message', 'bar'
        );
        $this->assertEquals('a message', $rule->getMessage());

        $this->assertSame($rule, $rule->setMessage('another message'));
        $this->assertEquals('another message', $rule->getMessage());
    }

    public function testValidateSingle()
    {
        $ruleTrue = new HTML_QuickForm2_Rule_ImplConst(
            new HTML_QuickForm2_Element_InputText('ruleTrue'), 'a message', true
        );
        $this->assertTrue($ruleTrue->validate());

        $ruleFalse = new HTML_QuickForm2_Rule_ImplConst(
            new HTML_QuickForm2_Element_InputText('ruleFalse'), 'a message', false
        );
        $this->assertFalse($ruleFalse->validate());
    }

    public function testValidateChained()
    {
        $elTest = new HTML_QuickForm2_Element_InputText('testAndOr');
        $ruleAnd = new HTML_QuickForm2_Rule_ImplConst($elTest, 'a message', true);
        $ruleAnd->and_(new HTML_QuickForm2_Rule_ImplConst($elTest, 'a message', false));
        $this->assertFalse($ruleAnd->validate());

        $ruleOr = new HTML_QuickForm2_Rule_ImplConst($elTest, 'a message', false);
        $ruleOr->or_(new HTML_QuickForm2_Rule_ImplConst($elTest, 'a message', true));
        $this->assertTrue($ruleOr->validate());
    }

    public function testOperatorPrecedence()
    {
        // true = true or true and false != ((true or true) and false) = false
        $elTest = new HTML_QuickForm2_Element_InputText('testPrecedence');
        $ruleTrue = new HTML_QuickForm2_Rule_ImplConst($elTest, 'a message', true);
        $ruleFalse = new HTML_QuickForm2_Rule_ImplConst($elTest, 'a message', false);

        $ruleTrue->or_(clone $ruleTrue)->and_($ruleFalse);
        $this->assertTrue($ruleTrue->validate());
    }

    public function testShortCircuitedEvaluationAnd()
    {
        $elTest = new HTML_QuickForm2_Element_InputText('testShortCircuitedAnd');
        $ruleTrue = new HTML_QuickForm2_Rule_ImplConst($elTest, '...', true);
        $ruleFalse = new HTML_QuickForm2_Rule_ImplConst($elTest, '...', false);

        $ruleAndTrue = $this->getMock(
            'HTML_QuickForm2_Rule', array('validate', 'checkValue'),
            array($elTest)
        );
        $ruleAndTrue->expects($this->once())->method('validate');
        $ruleTrue->and_($ruleAndTrue);
        $ruleTrue->validate();

        $ruleAndFalse = $this->getMock(
            'HTML_QuickForm2_Rule', array('validate', 'checkValue'),
            array($elTest)
        );
        $ruleAndFalse->expects($this->never())->method('validate');
        $ruleFalse->and_($ruleAndFalse);
        $ruleFalse->validate();
    }

    public function testShortCircuitedEvaluationOr()
    {
        $elTest = new HTML_QuickForm2_Element_InputText('testShortCircuitedOr');
        $ruleTrue = new HTML_QuickForm2_Rule_ImplConst($elTest, '...', true);
        $ruleFalse = new HTML_QuickForm2_Rule_ImplConst($elTest, '...', false);

        $ruleOrTrue = $this->getMock(
            'HTML_QuickForm2_Rule', array('validate', 'checkValue'),
            array($elTest)
        );
        $ruleOrTrue->expects($this->never())->method('validate');
        $ruleTrue->or_($ruleOrTrue);
        $ruleTrue->validate();

        $ruleOrFalse = $this->getMock(
            'HTML_QuickForm2_Rule', array('validate', 'checkValue'),
            array($elTest)
        );
        $ruleOrFalse->expects($this->once())->method('validate');
        $ruleFalse->or_($ruleOrFalse);
        $ruleFalse->validate();
    }

    public function testSetErrorOnlyOnChainFailure()
    {
        $elTest = new HTML_QuickForm2_Element_InputText('valid');
        $chain  = new HTML_QuickForm2_Rule_ImplConst($elTest, 'bogus error', false);
        $chain->or_(new HTML_QuickForm2_Rule_ImplConst($elTest, '', true));

        $this->assertTrue($chain->validate());
        $this->assertEquals('', $elTest->getError());

        $chain2 = new HTML_QuickForm2_Rule_ImplConst($elTest, 'genuine error', false);
        $chain2->or_(new HTML_QuickForm2_Rule_ImplConst($elTest, '', false));

        $this->assertFalse($chain2->validate());
        $this->assertEquals('genuine error', $elTest->getError());
    }
}
?>
