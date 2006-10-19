<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 * 
 * Copyright (c) 2006, Alexey Borzov <avb@php.net>, 
 *                     Bertrand Mansion <golgote@mamasam.com> 
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
 * Class for <select> elements
 */
require_once 'HTML/QuickForm2/Element/Select.php';

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * Let's just make parseAttributes() public rather than copy and paste regex   
 */
abstract class HTML_QuickForm2_Element_SelectTest_AttributeParser extends HTML_Common2
{
    public static function parseAttributes($attrString)
    {
        return parent::parseAttributes($attrString);
    }
}

/**
 * Unit test for HTML_QuickForm2_Element_Select class
 */
class HTML_QuickForm2_Element_SelectTest extends PHPUnit2_Framework_TestCase
{
    public function testSelectIsEmptyByDefault()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $this->assertEquals(0, count($sel));
        $this->assertNull($sel->getValue());
        $this->assertRegExp(
            '!^<select[^>]*>\\s*</select>$!',
            $sel->__toString()
        );
    }

    public function testSelectSingleValueIsScalar()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $sel->addOption('Text', 'Value');
        $sel->setValue('Value');
        $this->assertEquals('Value', $sel->getValue());

        $sel->setValue('Nonextistent');
        $this->assertNull($sel->getValue());

        $sel2 = new HTML_QuickForm2_Element_Select();
        $sel2->addOption('Text', 'Value');
        $sel2->addOption('Other Text', 'Other Value');
        $sel2->addOption('Different Text', 'Different Value');

        $sel2->setValue(array('Different value', 'Value'));
        $this->assertEquals('Value', $sel2->getValue());
    }

    public function testSelectMultipleValueIsArray()
    {
        $sel = new HTML_QuickForm2_Element_Select('mult', null, null, array('multiple'));
        $sel->addOption('Text', 'Value');
        $sel->addOption('Other Text', 'Other Value');
        $sel->addOption('Different Text', 'Different Value');

        $sel->setValue('Other Value');
        $this->assertEquals(array('Other Value'), $sel->getValue());

        $sel->setValue('Nonexistent');
        $this->assertNull($sel->getValue());

        $sel->setValue(array('Value', 'Different Value', 'Nonexistent'));
        $this->assertEquals(array('Value', 'Different Value'), $sel->getValue());
    }

    public function testDisabledSelectHasNoValue()
    {
        $sel = new HTML_QuickForm2_Element_Select('disableMe', null, null, array('disabled'));
        $sel->addOption('Text', 'Value');
        $sel->setValue('Value');

        $this->assertNull($sel->getValue());
    }

    public function testDisabledOptionsDoNotProduceValues()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $sel->addOption('Disabled Text', 'Disabled Value', array('disabled'));
        $sel->setValue('Disabled Value');

        $this->assertNull($sel->getValue());
    }


    public function testAddOption()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $sel->addOption('Text', 'Value');
        $this->assertRegExp(
            '!^<select[^>]*>\\s*<option[^>]+value="Value"[^>]*>Text</option>\\s*</select>!',
            $sel->__toString() 
        );

        $sel2 = new HTML_QuickForm2_Element_Select();
        $sel2->addOption('Text', 'Value', array('class' => 'bar'));
        $this->assertRegExp(
            '!<option[^>]+class="bar"[^>]*>Text</option>!',
            $sel2->__toString() 
        );

        $sel3 = new HTML_QuickForm2_Element_Select();
        $sel3->addOption('Text', 'Value', array('selected'));
        $this->assertEquals('Value', $sel3->getValue());
        $this->assertRegExp(
            '!<option[^>]+selected="selected"[^>]*>Text</option>!', 
            $sel3->__toString()
        );
    }

    public function testAddOptgroup()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $optgroup = $sel->addOptgroup('Label');
        $this->assertType('HTML_QuickForm2_Element_Select_Optgroup', $optgroup);
        $this->assertRegExp(
            '!^<select[^>]*>\\s*<optgroup[^>]+label="Label"[^>]*>\\s*</optgroup>\\s*</select>!',
            $sel->__toString() 
        );

        $sel2 = new HTML_QuickForm2_Element_Select();
        $optgroup2 = $sel2->addOptgroup('Label', array('class' => 'bar'));
        $this->assertRegExp(
            '!<optgroup[^>]+class="bar"[^>]*>\\s*</optgroup>!',
            $sel2->__toString() 
        );
    }

    public function testAddOptionToOptgroup()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $optgroup = $sel->addOptgroup('Label');
        $optgroup->addOption('Text', 'Value');
        $this->assertRegExp(
            '!^<select[^>]*>\\s*<optgroup[^>]+label="Label"[^>]*>\\s*' .
            '<option[^>]+value="Value"[^>]*>Text</option>\\s*</optgroup>\\s*</select>!',
            $sel->__toString() 
        );

        $sel2 = new HTML_QuickForm2_Element_Select();
        $optgroup2 = $sel2->addOptgroup('Label');
        $optgroup2->addOption('Text', 'Value', array('class' => 'bar'));
        $this->assertRegExp(
            '!<optgroup[^>]+label="Label"[^>]*>\\s*<option[^>]+class="bar"[^>]*>Text</option>\\s*</optgroup>!',
            $sel2->__toString() 
        );

        $sel3 = new HTML_QuickForm2_Element_Select();
        $optgroup3 = $sel3->addOptgroup('Label');
        $optgroup3->addOption('Text', 'Value', array('selected'));
        $this->assertEquals('Value', $sel3->getValue());
        $this->assertRegExp(
            '!<optgroup[^>]+label="Label"[^>]*>\\s*<option[^>]+selected="selected"[^>]*>Text</option>\\s*</optgroup>!', 
            $sel3->__toString()
        );
    }

    public function testSelectMultipleName()
    {
        $sel = new HTML_QuickForm2_Element_Select('foo', null, null, array('multiple'));
        $this->assertRegExp('/name="foo\\[\\]"/', $sel->__toString());
    }

    public function testFrozenHtmlGeneration()
    {
        $sel = new HTML_QuickForm2_Element_Select('foo');
        $sel->addOption('Text', 'Value');
        $sel->setValue('Value');
        $sel->toggleFrozen(true);

        $sel->persistentFreeze(false);
        $this->assertNotRegExp('/[<>]/', $sel->__toString());
        $this->assertRegExp('/Text/', $sel->__toString());

        $sel->persistentFreeze(true);
        $this->assertRegExp('/Text/', $sel->__toString());
        $this->assertRegExp('!<input[^>]+type="hidden"[^>]*/>!', $sel->__toString());

        preg_match('!<input([^>]+)/>!', $sel->__toString(), $matches);
        $this->assertEquals(
            array('id' => $sel->getId(), 'name' => 'foo', 'value' => 'Value', 'type' => 'hidden'),
            HTML_QuickForm2_Element_SelectTest_AttributeParser::parseAttributes($matches[1])
        );

        $sel->setValue('Nonexistent');
        $this->assertNotRegExp('/Text/', $sel->__toString());
        $this->assertNotRegExp('/[<>]/', $sel->__toString());
    }

    public function testSelectMultipleFrozenHtmlGeneration()
    {
        $sel = new HTML_QuickForm2_Element_Select('foo', null, null, array('multiple'));
        $sel->addOption('FirstText', 'FirstValue');
        $sel->addOption('SecondText', 'SecondValue');
        $sel->setValue(array('FirstValue', 'SecondValue'));
        $sel->toggleFrozen(true);

        $this->assertRegExp('/FirstText.*SecondText/s', $sel->__toString());
        $this->assertRegExp('!<input[^>]+type="hidden"[^>]*/>!', $sel->__toString());

        preg_match_all('!<input([^>]+)/>!', $sel->__toString(), $matches, PREG_SET_ORDER);
        $this->assertEquals(
            array('name' => 'foo[]', 'value' => 'FirstValue', 'type' => 'hidden'),
            HTML_QuickForm2_Element_SelectTest_AttributeParser::parseAttributes($matches[0][1])
        );
        $this->assertEquals(
            array('name' => 'foo[]', 'value' => 'SecondValue', 'type' => 'hidden'),
            HTML_QuickForm2_Element_SelectTest_AttributeParser::parseAttributes($matches[1][1])
        );
    }
}
?>
