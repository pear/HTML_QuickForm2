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
 * Unit test for HTML_QuickForm2_Container_Repeat class
 */
class HTML_QuickForm2_Container_RepeatTest extends PHPUnit_Framework_TestCase
{
    public function testCannotAddRepeatToRepeat()
    {
        $repeatOne = new HTML_QuickForm2_Container_Repeat();
        $repeatTwo = new HTML_QuickForm2_Container_Repeat();

        try {
            $repeatOne->setPrototype($repeatTwo);
            $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
        } catch (HTML_QuickForm2_Exception $e) {}

        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeatOne->setPrototype($fieldset);

        try {
            $fieldset->appendChild($repeatTwo);
            $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
        } catch (HTML_QuickForm2_Exception $e) {}
    }

    public function testPrototypeRequiredForDOMAndOutput()
    {
        $repeat = new HTML_QuickForm2_Container_Repeat();
        $text   = new HTML_QuickForm2_Element_InputText('aTextBox');

        try {
            $repeat->appendChild($text);
            $this->fail('Expected HTML_QuickForm2_NotFoundException not found');
        } catch (HTML_QuickForm2_NotFoundException $e) {}

        try {
            $repeat->insertBefore($text);
            $this->fail('Expected HTML_QuickForm2_NotFoundException not found');
        } catch (HTML_QuickForm2_NotFoundException $e) {}

        try {
            $repeat->render(HTML_QuickForm2_Renderer::factory('default'));
            $this->fail('Expected HTML_QuickForm2_NotFoundException not found');
        } catch (HTML_QuickForm2_NotFoundException $e) {}
    }

    public function testElementsAreAddedToPrototype()
    {
        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeat   = new HTML_QuickForm2_Container_Repeat(
            null, null, array('prototype' => $fieldset)
        );
        $textOne  = new HTML_QuickForm2_Element_InputText('firstText');
        $textTwo  = new HTML_QuickForm2_Element_InputText('secondText');

        $repeat->appendChild($textOne);
        $this->assertSame($textOne->getContainer(), $fieldset);

        $repeat->insertBefore($textTwo, $textOne);
        $this->assertSame($textTwo->getContainer(), $fieldset);

        $repeat->removeChild($textOne);
        $this->assertNull($textOne->getContainer());
    }

    public function testSetIndexesExplicitly()
    {
        $repeat = new HTML_QuickForm2_Container_Repeat();
        $this->assertEquals(array(), $repeat->getIndexes());

        $repeat->setIndexes(array('foo', 'bar', 'baz', 'qu\'ux', 'baz', 25));
        $this->assertEquals(array('foo', 'bar', 'baz', 25), $repeat->getIndexes());
    }

    public function testSetIndexFieldExplicitly()
    {
        $form = new HTML_QuickForm2('testIndexField');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'blah' => array(
                'blergh'    => 'a',
                'blurgh'    => 'b',
                'ba-a-a-ah' => 'c',
                42          => 'd'
            ),
            'argh' => array(
                'a'    => 'e',
                'b\'c' => 'f',
                'd'    => 'g'
            )
        )));

        $repeat = new HTML_QuickForm2_Container_Repeat();
        $repeat->setIndexField('blah');
        $repeat->setIndexes(array('foo', 'bar'));
        $form->appendChild($repeat);
        $this->assertEquals(array('blergh', 'blurgh', 42), $repeat->getIndexes());

        $repeat->setIndexField('argh');
        $this->assertEquals(array('a', 'd'), $repeat->getIndexes());
    }

    public function testGuessIndexField()
    {
        $form = new HTML_QuickForm2('guessIndexField');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'blah'   => array('foo' => 1),
            'bzz'    => array('bar' => array('a', 'b')),
            'aaargh' => array('foo' => ''),
            'blergh' => array('foo' => '', 'bar' => 'bar value')
        )));

        $repeat = new HTML_QuickForm2_Container_Repeat();
        $form->appendChild($repeat);

        $this->assertEquals(array(), $repeat->getIndexes());

        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeat->setPrototype($fieldset);
        $this->assertEquals(array(), $repeat->getIndexes());

        $fieldset->addCheckbox('blah');
        $this->assertEquals(array(), $repeat->getIndexes());

        $fieldset->addSelect('bzz', array('multiple'));
        $this->assertEquals(array(), $repeat->getIndexes());

        $fieldset->addText('aaargh', array('disabled'));
        $this->assertEquals(array(), $repeat->getIndexes());

        $fieldset->addText('blergh');
        $this->assertEquals(array('foo', 'bar'), $repeat->getIndexes());
    }

    public function testGetValue()
    {
        $values = array(
            'foo' => array('a' => 'a value', 'b' => 'b value', 'c' => 'c value'),
            'bar' => array(
                'baz' => array('a' => 'aa', 'b' => 'bb', 'c' => 'cc')
            )
        );

        $form   = new HTML_QuickForm2('repeatValue');
        $repeat = new HTML_QuickForm2_Container_Repeat();
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array($values));
        $form->appendChild($repeat);

        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeat->setPrototype($fieldset);

        $fieldset->addText('foo');
        $fieldset->addText('bar[baz]');

        $this->assertEquals($values, $repeat->getValue());

        $repeat->setIndexes(array('a', 'c'));
        unset($values['foo']['b'], $values['bar']['baz']['b']);
        $this->assertEquals($values, $repeat->getValue());
    }

    public function testFrozenRepeatShouldNotContainJavascript()
    {
        $repeat = new HTML_QuickForm2_Container_Repeat();
        $repeat->setPrototype(new HTML_QuickForm2_Container_Fieldset());
        $repeat->toggleFrozen(true);

        $this->assertNotContains('<script', $repeat->__toString());
    }

    public function testServerSideValidationErrors()
    {
        $ds = new HTML_QuickForm2_DataSource_Session(array(
            'foo' => array('', 'blah', '')
        ));
        $form = new HTML_QuickForm2('repeatValidate');
        $form->addDataSource($ds);

        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $text     = new HTML_QuickForm2_Element_InputText('foo');
        $repeat   = new HTML_QuickForm2_Container_Repeat(
            null, null, array('prototype' => $fieldset)
        );
        $fieldset->appendChild($text);
        $form->appendChild($repeat);

        $text->addRule('required', 'a message');
        $this->assertFalse($form->validate());

        $ary = $repeat->render(HTML_QuickForm2_Renderer::factory('array'))->toArray();
        $this->assertEquals('a message', $ary['elements'][1]['elements'][0]['error']);
        $this->assertArrayNotHasKey('error', $ary['elements'][2]['elements'][0]);
        $this->assertEquals('a message', $ary['elements'][3]['elements'][0]['error']);

        $text->setId('blah-:idx:');
        $ary = $repeat->render(HTML_QuickForm2_Renderer::factory('array'))->toArray();
        $this->assertEquals('a message', $ary['elements'][1]['elements'][0]['error']);
        $this->assertArrayNotHasKey('error', $ary['elements'][2]['elements'][0]);
        $this->assertEquals('a message', $ary['elements'][3]['elements'][0]['error']);
    }

    public function testForeachWarningOnGetValue()
    {
        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeat   = new HTML_QuickForm2_Container_Repeat(
            null, null, array('prototype' => $fieldset)
        );
        $fieldset->addText('foo');
        $repeat->setIndexes(array(1));

        $this->assertEquals(null, $repeat->getValue());
    }

    /**
     * Contents of static elements within repeat erroneously cleared
     * @link http://pear.php.net/bugs/bug.php?id=19802
     */
    public function testBug19802()
    {
        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeat   = new HTML_QuickForm2_Container_Repeat(
            null, null, array('prototype' => $fieldset)
        );
        $fieldset->addStatic()
            ->setContent('Content of static element')
            ->setTagName('p');

        $arrayOne = $repeat->render(HTML_QuickForm2_Renderer::factory('array'))->toArray();
        $arrayTwo = $repeat->render(HTML_QuickForm2_Renderer::factory('array'))->toArray();

        $this->assertEquals(
            $arrayOne['elements'][0]['elements'][0]['html'],
            $arrayTwo['elements'][0]['elements'][0]['html']
        );
    }

    /**
     * If defaults contain null values, previous values are reused
     * @link http://pear.php.net/bugs/bug.php?id=20295
     */
    public function testBug20295()
    {
        $form = new HTML_QuickForm2('repeat-bug');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'buggy' => array(
                'name'  => array(1 => 'First', 2 => 'Second'),
                'extra' => array(1 => 'Has extra', 2 => null)
            )
        )));

        $group = new HTML_QuickForm2_Container_Group('buggy');
        $group->addText('name');
        $group->addText('extra');

        $repeat = $form->addRepeat(null, array('id' => 'buggy-repeat'), array('prototype' => $group));

        $value = $repeat->getValue();
        $this->assertEquals('', $value['buggy']['extra'][2]);
    }

    public function testValidatorAlwaysPresentWhenClientRulesAdded()
    {
        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $repeat   = new HTML_QuickForm2_Container_Repeat(
            null, null, array('prototype' => $fieldset)
        );

        $fieldset->addText('foo')
            ->addRule('required', 'Required!', null, HTML_QuickForm2_Rule::CLIENT_SERVER);

        $repeat->setIndexes(array());
        $renderer = HTML_QuickForm2_Renderer::factory('array');
        $renderer->getJavascriptBuilder()->setFormId('fake-repeat');
        $repeat->render($renderer);

        $this->assertContains('new qf.Validator', $renderer->getJavascriptBuilder()->getValidator());
    }
}
?>