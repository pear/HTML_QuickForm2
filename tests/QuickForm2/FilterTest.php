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
 * @author     Bertrand Mansion <mansion@php.net>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id$
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * PHPUnit Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

require_once 'HTML/QuickForm2.php';
require_once 'HTML/QuickForm2/Element/InputCheckable.php';

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

function nonRecursiveFilter($value, $str = '')
{
    return implode('', $value);
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

    public function testContainerValidation()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $username = $form->addElement('text', 'foo');
        $username->addRule('required', 'Username is required');
        $form->addFilter('trim');
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

    public function testSelectMultiple()
    {
        $form = new HTML_QuickForm2('filters', 'post', null, false);
        $select = $form->addSelect('baz', array('multiple' => 'multiple'))->loadOptions(
            array('VALUE1' => 'VALUE1', 'VALUE2' => 'VALUE2', 'VALUE3' => 'VALUE3'));
        $select->addFilter('strtolower');
        $this->assertEquals(array('value1', 'value2'), $select->getValue());
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
        $area->removeFilters();
        $this->assertEquals('VALUE', $area->getValue());
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
        $f = $el1->getFilters();
        $this->assertEquals(1, count($f));
        $this->assertEquals('repeatFilter', $f[0][0]);
        $this->assertEquals(false, $f[0]['cascade']);

        $el2->setValue('B');
        $el3->setValue('C');

        $this->assertEquals(array(
            'foo' => 'AA',
            'bar' => 'B',
            'baz' => 'C'
        ), $c1->getValue());

        $c1->addFilter('strtolower');
        $f = $el1->getFilterChain();
        $this->assertEquals(2, count($f));
        $this->assertEquals('strtolower', $f[0][0]);
        $this->assertEquals('repeatFilter', $f[1][0]);

        $this->assertEquals('aa', $el1->getValue());
        $this->assertEquals('b',  $el2->getValue());
        $this->assertEquals('c',  $el3->getValue());

        $c1->addFilter('trim');
        $f = $el1->getFilterChain();
        $this->assertEquals(3, count($f));
        $this->assertEquals('strtolower', $f[0][0]);
        $this->assertEquals('trim', $f[1][0]);
        $this->assertEquals('repeatFilter', $f[2][0]);


        $c1->addFilter('repeatFilter');
        $f = $el1->getFilterChain();
        $this->assertEquals(4, count($f));
        $this->assertEquals('strtolower', $f[0][0]);
        $this->assertEquals('trim', $f[1][0]);
        $this->assertEquals('repeatFilter', $f[2][0]);
        $this->assertEquals('repeatFilter', $f[3][0]);

        $this->assertEquals('aaa', $el1->getValue());
        $this->assertEquals('bb',  $el2->getValue());
        $this->assertEquals('cc',  $el3->getValue());
        // Second run, just to make sure...
        $this->assertEquals('aaa', $el1->getValue());
        $this->assertEquals('bb',  $el2->getValue());
        $this->assertEquals('cc',  $el3->getValue());

        $c1->addFilter('nonRecursiveFilter', null, false);
        $f = $c1->getFilters();
        $this->assertEquals(1, count($f));
        $this->assertEquals('nonRecursiveFilter', $f[0][0]);
        $this->assertEquals('aaabbcc', $c1->getValue());

        $f = $el1->getFilterChain();
        $this->assertEquals(4, count($f));
        $this->assertEquals('strtolower', $f[0][0]);
        $this->assertEquals('trim', $f[1][0]);
        $this->assertEquals('repeatFilter', $f[2][0]);
        $this->assertEquals('repeatFilter', $f[3][0]);

        $f = $el1->getFilters();
        $this->assertEquals(1, count($f));
        $this->assertEquals('repeatFilter', $f[0][0]);

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
        $g1->addFilter('strtoupper');

        $el1 = $g1->addText('foo');
        // Trim O *after* strtoupper
        $el1->addFilter('trim', array('O'));

        $f = $el1->getFilterchain();
        $this->assertEquals(2, count($f));
        $this->assertEquals('strtoupper', $f[0][0]);
        $this->assertEquals('trim', $f[1][0]);

        $g2 = $form->addGroup('g2[i2]');
        $g2->addFilter('ucfirst');
        $g2->addText('bar');
        $g2->addText('baz[quux]');

        $anon = $form->addGroup();
        $anon->addText('e1');
        $anon->addFilter('substr', array(1, 1));

        $this->assertEquals($formValueF, $form->getValue());
    }
}
?>
