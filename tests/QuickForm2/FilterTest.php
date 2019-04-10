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
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

class HTML_QuickForm2_ContainerFilterImpl extends HTML_QuickForm2_Container
{
    public function getType() { return 'concrete'; }
    public function setValue($value) { return ''; }
    public function __toString() { return ''; }
    public function validate() { return parent::validate(); }
}

/**
 * A filter that modifies the value on every iteration
 * To make sure it is not called more times than it should.
 */
function repeatFilter($value)
{
    return substr($value, 0, 1).$value;
}

/**
 * Unit test for HTML_QuickForm2_Rule class
 */
class HTML_QuickForm2_FilterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $_REQUEST['_qf__filters'] = '';
        $_POST = array(
            'foo' => '  ',
            'bar' => 'VALUE',
            'baz' => array('VALUE1', 'VALUE2'),
            'sel' => 'VALUE2'
        );
    }

    public function testFiltersShouldPreserveNulls()
    {
        $mockElement = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(array('getType',
                                      'getRawValue', 'setValue', '__toString'))
            ->getMock();
        $mockElement->expects($this->atLeastOnce())
                    ->method('getRawValue')->will($this->returnValue(null));
        $mockElement->addFilter('trim');
        $this->assertNull($mockElement->getValue());

        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(array('getType', 'setValue', '__toString'))
            ->getMock();
        $mockContainer->appendChild($mockElement);
        $mockContainer->addRecursiveFilter('intval');
        $mockContainer->addFilter('count');

        $this->assertNull($mockContainer->getValue());
    }

    public function testContainerValidation()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $username = $form->addElement('text', 'foo');
        $username->addRule('required', 'Username is required');
        $form->addRecursiveFilter('trim');
        $this->assertFalse($form->validate());
        $this->assertSame('', $username->getValue());
    }

    public function testSelect()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $select = $form->addSelect('sel')->loadOptions(
            array('VALUE1' => 'VALUE1', 'VALUE2' => 'VALUE2', 'VALUE3' => 'VALUE3'));
        $select->addFilter('strtolower');
        $this->assertEquals('value2', $select->getValue());
    }

    public function testSelectMultipleRecursive()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $select = $form->addSelect('baz', array('multiple' => 'multiple'))->loadOptions(
            array('VALUE1' => 'VALUE1', 'VALUE2' => 'VALUE2', 'VALUE3' => 'VALUE3'));
        $select->addRecursiveFilter('strtolower');
        $this->assertEquals(array('value1', 'value2'), $select->getValue());
    }

    public function testSelectMultipleNonRecursive()
    {
        $s = new HTML_QuickForm2_Element_Select('foo', array('multiple' => 'multiple'),
                                                array('intrinsic_validation' => false));
        $s->setValue(array('foo', 'bar'));
        $s->addFilter('count');

        $this->assertEquals(2, $s->getValue());
    }

    public function testInputCheckable()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $check = $form->appendChild(
            new HTML_QuickForm2_Element_InputCheckable('bar'));
        $check->setAttribute('value', 'VALUE');
        $check->addFilter('strtolower');
        $this->assertEquals('value', $check->getValue());
        // in order to be set, the value must be equal to the one in
        // the value attribute
        $check->setValue('value');
        $this->assertNull($check->getValue());
        $check->setValue('VALUE');
        $this->assertEquals('value', $check->getValue());
    }

    public function testButton()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'bar' => 'VALUE'
        )));
        $button = $form->addButton('bar', array('type' => 'submit'));
        $button->addFilter('strtolower');
        $this->assertEquals('value', $button->getValue());
    }

    public function testInput()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $foo = $form->addText('foo');
        $this->assertEquals($_POST['foo'], $foo->getValue());
        $foo->addFilter('trim');
        $this->assertEquals(trim($_POST['foo']), $foo->getValue());
    }

    public function testTextarea()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $area = $form->addTextarea('bar');
        $area->addFilter('strtolower');
        $this->assertEquals('value', $area->getValue());
    }

    public function testContainer()
    {
        $c1 = new HTML_QuickForm2_ContainerFilterImpl('filter');
        $this->assertNull($c1->getValue());

        $el1 = $c1->addText('foo');
        $el2 = $c1->addText('bar');
        $el3 = $c1->addText('baz');
        $this->assertNull($c1->getValue());

        $el1->setValue('A');
        $el1->addFilter('repeatFilter');

        $el2->setValue('B');
        $el3->setValue('C');

        $this->assertEquals(array(
            'foo' => 'AA',
            'bar' => 'B',
            'baz' => 'C'
        ), $c1->getValue());

        $c1->addRecursiveFilter('strtolower');

        $this->assertEquals('aa', $el1->getValue());
        $this->assertEquals('b',  $el2->getValue());
        $this->assertEquals('c',  $el3->getValue());

        $c1->addRecursiveFilter('trim');
        $c1->addRecursiveFilter('repeatFilter');

        $this->assertEquals('aaa', $el1->getValue());
        $this->assertEquals('bb',  $el2->getValue());
        $this->assertEquals('cc',  $el3->getValue());
        // Second run, just to make sure...
        $this->assertEquals('aaa', $el1->getValue());
        $this->assertEquals('bb',  $el2->getValue());
        $this->assertEquals('cc',  $el3->getValue());
    }

    public function testGroup()
    {
        $value1     = array('foo' => 'foo');
        $value1F    = array('foo' => 'F');
        $value2     = array('bar' => 'bar', 'baz' => array('quux' => 'baz'));
        $value2F    = array('bar' => 'Bar', 'baz' => array('quux' => 'Baz'));
        $valueAnon  = array('e1' => 'e1');
        $valueAnonF = array('e1' => '1');
        $formValue  = array('g1' => $value1, 'g2' => array('i2' => $value2)) + $valueAnon;
        $formValueF = array('g1' => $value1F, 'g2' => array('i2' => $value2F)) + $valueAnonF;

        $form = new HTML_QuickForm2('testGroupGetValue');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array($formValue));

        $g1 = $form->addGroup('g1');
        $g1->addRecursiveFilter('strtoupper');

        $el1 = $g1->addText('foo');
        // Trim O *after* strtoupper
        $el1->addFilter('trim', array('O'));

        $g2 = $form->addGroup('g2[i2]');
        $g2->addRecursiveFilter('ucfirst');
        $g2->addText('bar');
        $g2->addText('baz[quux]');

        $anon = $form->addGroup();
        $anon->addText('e1');
        $anon->addRecursiveFilter('substr', array(1, 1));

        $this->assertEquals($formValueF, $form->getValue());
    }

    public function testContainerNonRecursive()
    {
        $c = new HTML_QuickForm2_ContainerFilterImpl('nonrecursive');
        $el1 = $c->addElement('text', 'el1')->setValue(' foo');
        $el2 = $c->addElement('text', 'el2')->setValue('bar ');

        $c->addRecursiveFilter('trim');
        $c->addFilter('count');

        $this->assertEquals(2, $c->getValue());
        $this->assertEquals('foo', $el1->getValue());
    }
}
?>
