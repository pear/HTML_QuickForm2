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
 * @copyright 2006-2021 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

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
class HTML_QuickForm2_Element_SelectTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_POST = [
            'single1' => '1'
        ];
        $_GET = [];
    }

    public function testSelectIsEmptyByDefault()
    {
        $sel = new HTML_QuickForm2_Element_Select();
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
        $this->assertSame($sel, $sel->setValue('Value'));
        $this->assertEquals('Value', $sel->getValue());

        $this->assertSame($sel, $sel->setValue('Nonextistent'));
        $this->assertNull($sel->getValue());

        $sel2 = new HTML_QuickForm2_Element_Select();
        $sel2->addOption('Text', 'Value');
        $sel2->addOption('Other Text', 'Other Value');
        $sel2->addOption('Different Text', 'Different Value');

        $sel2->setValue(['Different value', 'Value']);
        $this->assertEquals('Value', $sel2->getValue());
    }

    public function testSelectMultipleValueIsArray()
    {
        $sel = new HTML_QuickForm2_Element_Select('mult', ['multiple']);
        $sel->addOption('Text', 'Value');
        $sel->addOption('Other Text', 'Other Value');
        $sel->addOption('Different Text', 'Different Value');

        $this->assertSame($sel, $sel->setValue('Other Value'));
        $this->assertEquals(['Other Value'], $sel->getValue());

        $this->assertSame($sel, $sel->setValue('Nonexistent'));
        $this->assertNull($sel->getValue());

        $this->assertSame($sel, $sel->setValue(['Value', 'Different Value', 'Nonexistent']));
        $this->assertEquals(['Value', 'Different Value'], $sel->getValue());
    }

    public function testDisabledSelectHasNoValue()
    {
        $sel = new HTML_QuickForm2_Element_Select('disableMe', ['disabled']);
        $sel->addOption('Text', 'Value');
        $sel->setValue('Value');

        $this->assertNull($sel->getValue());
    }

    public function testDisabledOptionsDoNotProduceValues()
    {
        $sel = new HTML_QuickForm2_Element_Select();
        $sel->addOption('Disabled Text', 'Disabled Value', ['disabled']);
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
        $sel2->addOption('Text', 'Value', ['class' => 'bar']);
        $this->assertRegExp(
            '!<option[^>]+class="bar"[^>]*>Text</option>!',
            $sel2->__toString()
        );

        $sel3 = new HTML_QuickForm2_Element_Select();
        $sel3->addOption('Text', 'Value', ['selected']);
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
        $this->assertInstanceOf('HTML_QuickForm2_Element_Select_Optgroup', $optgroup);
        $this->assertRegExp(
            '!^<select[^>]*>\\s*<optgroup[^>]+label="Label"[^>]*>\\s*</optgroup>\\s*</select>!',
            $sel->__toString()
        );

        $sel2 = new HTML_QuickForm2_Element_Select();
        $optgroup2 = $sel2->addOptgroup('Label', ['class' => 'bar']);
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
        $optgroup2->addOption('Text', 'Value', ['class' => 'bar']);
        $this->assertRegExp(
            '!<optgroup[^>]+label="Label"[^>]*>\\s*<option[^>]+class="bar"[^>]*>Text</option>\\s*</optgroup>!',
            $sel2->__toString()
        );

        $sel3 = new HTML_QuickForm2_Element_Select();
        $optgroup3 = $sel3->addOptgroup('Label');
        $optgroup3->addOption('Text', 'Value', ['selected']);
        $this->assertEquals('Value', $sel3->getValue());
        $this->assertRegExp(
            '!<optgroup[^>]+label="Label"[^>]*>\\s*<option[^>]+selected="selected"[^>]*>Text</option>\\s*</optgroup>!',
            $sel3->__toString()
        );
    }

    public function testLoadOptions()
    {
        $sel = new HTML_QuickForm2_Element_Select('loadOptions', ['multiple']);
        $this->assertSame($sel, $sel->loadOptions(['one' => 'First', 'two' => 'Second']));
        $sel->setValue(['one', 'two']);
        $this->assertRegexp(
            '!<option[^>]+value="one"[^>]*>First</option>\\s*<option[^>]+value="two"[^>]*>Second</option>!',
            $sel->__toString()
        );
        $this->assertEquals(['one', 'two'], $sel->getValue());

        $sel->loadOptions(['Label' => ['two' => 'Second', 'three' => 'Third']]);
        $this->assertRegexp(
            '!<optgroup[^>]+label="Label"[^>]*>\\s*<option[^>]+value="two"[^>]*>Second</option>\\s*' .
            '<option[^>]+value="three"[^>]*>Third</option>\\s*</optgroup>!',
            $sel->__toString()
        );
        $this->assertNotRegexp(
            '!<option[^>]+value="one"[^>]*>First</option>!',
            $sel->__toString()
        );
        $this->assertEquals(['two'], $sel->getValue());
    }

    public function testSelectMultipleName()
    {
        $sel = new HTML_QuickForm2_Element_Select('foo', ['multiple']);
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
            ['id' => $sel->getId(), 'name' => 'foo', 'value' => 'Value', 'type' => 'hidden'],
            HTML_QuickForm2_Element_SelectTest_AttributeParser::parseAttributes($matches[1])
        );

        $sel->setValue('Nonexistent');
        $this->assertNotRegExp('/Text/', $sel->__toString());
        $this->assertNotRegExp('/[<>]/', $sel->__toString());
    }

    public function testSelectMultipleFrozenHtmlGeneration()
    {
        $sel = new HTML_QuickForm2_Element_Select('foo', ['multiple']);
        $sel->addOption('FirstText', 'FirstValue');
        $sel->addOption('SecondText', 'SecondValue');
        $sel->setValue(['FirstValue', 'SecondValue']);
        $sel->toggleFrozen(true);

        $this->assertRegExp('/FirstText.*SecondText/s', $sel->__toString());
        $this->assertRegExp('!<input[^>]+type="hidden"[^>]*/>!', $sel->__toString());

        preg_match_all('!<input([^>]+)/>!', $sel->__toString(), $matches, PREG_SET_ORDER);
        $this->assertEquals(
            ['name' => 'foo[]', 'value' => 'FirstValue', 'type' => 'hidden'],
            HTML_QuickForm2_Element_SelectTest_AttributeParser::parseAttributes($matches[0][1])
        );
        $this->assertEquals(
            ['name' => 'foo[]', 'value' => 'SecondValue', 'type' => 'hidden'],
            HTML_QuickForm2_Element_SelectTest_AttributeParser::parseAttributes($matches[1][1])
        );
    }

    public function testSelectMultipleNoOptionsSelectedOnSubmit()
    {
        $options = ['1' => 'Option 1', '2' => 'Option 2'];

        $formPost = new HTML_QuickForm2('multiple', 'post', null, false);
        $single1  = $formPost->appendChild(new HTML_QuickForm2_Element_Select('single1', null, ['options' => $options]));
        $single2  = $formPost->appendChild(new HTML_QuickForm2_Element_Select('single2', null, ['options' => $options]));
        $multiple = $formPost->appendChild(new HTML_QuickForm2_Element_Select('mult', ['multiple'], ['options' => $options]));
        $this->assertEquals('1', $single1->getValue());
        $this->assertNull($single2->getValue());
        $this->assertNull($multiple->getValue());

        $formPost->addDataSource(new HTML_QuickForm2_DataSource_Array([
            'single1' => '2',
            'single2' => '2',
            'mult' => ['1', '2']
        ]));
        $this->assertEquals('1', $single1->getValue());
        $this->assertEquals('2', $single2->getValue());
        $this->assertNull($multiple->getValue());

        $formGet   = new HTML_QuickForm2('multiple2', 'get', null, false);
        $multiple2 = $formGet->appendChild(new HTML_QuickForm2_Element_Select('mult2', ['multiple'], ['options' => $options]));
        $this->assertNull($multiple2->getValue());

        $formGet->addDataSource(new HTML_QuickForm2_DataSource_Array([
            'mult2' => ['1', '2']
        ]));
        $this->assertEquals(['1', '2'], $multiple2->getValue());
    }

    public function testBug11138()
    {
        $options = ['2' => 'TwoWithoutZero', '02' => 'TwoWithZero'];

        $sel = new HTML_QuickForm2_Element_Select('bug11138');
        $sel->loadOptions($options);
        $sel->setValue('02');

        $selHtml = $sel->__toString();
        $this->assertRegExp(
            '!selected="selected"[^>]*>TwoWithZero!', $selHtml
        );
        $this->assertNotRegExp(
            '!selected="selected"[^>]*>TwoWithoutZero!', $selHtml
        );

        $sel->toggleFrozen(true);
        $selFrozen = $sel->__toString();
        $this->assertContains('TwoWithZero', $selFrozen);
        $this->assertContains('value="02"', $selFrozen);
        $this->assertNotContains('TwoWithoutZero', $selFrozen);
        $this->assertNotContains('value="2"', $selFrozen);
    }

   /**
    * Disable possibleValues checks in getValue()
    *
    * For lazy people who add options to selects on client side and do not
    * want to add the same stuff server-side
    *
    * @link http://pear.php.net/bugs/bug.php?id=13088
    * @link http://pear.php.net/bugs/bug.php?id=16974
    */
    public function testDisableIntrinsicValidation()
    {
        $selectSingle = new HTML_QuickForm2_Element_Select(
            'foo', null, ['intrinsic_validation' => false]
        );
        $selectSingle->setValue('foo');
        $this->assertEquals('foo', $selectSingle->getValue());

        $selectSingle->loadOptions(['one' => 'First', 'two' => 'Second']);
        $selectSingle->setValue('three');
        $this->assertEquals('three', $selectSingle->getValue());

        $selectMultiple = new HTML_QuickForm2_Element_Select(
            'bar', ['multiple'], ['intrinsic_validation' => false]
        );
        $selectMultiple->loadOptions(['one' => 'First', 'two' => 'Second']);
        $selectMultiple->setValue(['two', 'three']);
        $this->assertEquals(['two', 'three'], $selectMultiple->getValue());
    }

    /**
     * If data source contains explicitly provided null values, those should be used
     * @link http://pear.php.net/bugs/bug.php?id=20295
     */
    public function testBug20295()
    {
        $form = new HTML_QuickForm2('bug20295');
        $ms   = $form->addSelect('multiselect', ['multiple'])
                    ->loadOptions(['one' => 'First option', 'two' => 'Second option'])
                    ->setValue(['two']);

        // data source searching should stop on finding this null
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array([
            'multiselect' => null
        ]));
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array([
            'multiselect' => ['one']
        ]));

        $this->assertNull($ms->getValue());
    }
}
?>
