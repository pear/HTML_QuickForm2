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
 * Unit test for HTML_QuickForm2_Element_Input class
 */
class HTML_QuickForm2_Element_StaticTest extends PHPUnit_Framework_TestCase
{
    public function testSetContent()
    {
        $obj = new HTML_QuickForm2_Element_Static();
        $this->assertEquals('', (string)$obj);
        $obj->setContent('<b>content</b>');
        $this->assertEquals('<b>content</b>', (string)$obj);
    }

    public function testCanSetAndGetValue()
    {
        $obj = new HTML_QuickForm2_Element_Static();
        $obj->setValue('<b>content</b>');
        $this->assertEquals('<b>content</b>', (string)$obj);
        $this->assertNull($obj->getValue());
    }

    public function testUpdateValueNoInject()
    {
        $_POST = array(
            'foo' => '<b>exploit</b>',
            'bar' => 'exploit',
            'baz' => 'ok'
        );

        $form = new HTML_QuickForm2('submit', 'post', null, false);
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'foo' => '<b>foo</b>',
            'bar' => 'bar'
        )));

        $foo = $form->appendChild(new HTML_QuickForm2_Element_Static('foo'));
        $bar = $form->appendChild(new HTML_QuickForm2_Element_Static('bar'));
        $baz = $form->appendChild(new HTML_QuickForm2_Element_InputText('baz'));

        $this->assertEquals('<b>foo</b>', $foo->getContent());
        $this->assertEquals('bar', $bar->getContent());
        $this->assertEquals('ok', $baz->getValue());
    }

    public function testFrozenNoEffect()
    {
        $obj = new HTML_QuickForm2_Element_Static();
        $obj->setContent('<b>content</b>');
        $obj->toggleFrozen(true);
        $this->assertEquals('<b>content</b>', (string)$obj);
    }

    public function testCannotValidate()
    {
        $static = new HTML_QuickForm2_Element_Static('novalidate');
        try {
            $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
                ->setMethods(array('validateOwner'))
                ->setConstructorArgs(array($static, 'a message'))
                ->getMock();
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) { }
    }

    public function testCanRemoveName()
    {
        $foo = new HTML_QuickForm2_Element_Static('foo', array('id' => 'bar'));
        $foo->removeAttribute('name');
        $this->assertNull($foo->getAttribute('name'));

        $bar = new HTML_QuickForm2_Element_Static('bar');
        $bar->setName(null);
        $this->assertNull($bar->getAttribute('name'));
    }

    public function testTagName()
    {
        $img = new HTML_QuickForm2_Element_Static(
            'picture', array('alt' => 'foo', 'src' => 'pr0n.gif'),
            array('tagName' => 'img', 'forceClosingTag' => false)
        );
        $this->assertRegexp('!<img[^<>]*alt="foo" src="pr0n.gif"[^<>]*/>!', $img->__toString());

        $div = new HTML_QuickForm2_Element_Static(
            null, array('class' => 'foo'), array('tagName' => 'div')
        );
        $this->assertRegexp('!<div[^<>]*class="foo"[^<>]*></div>!', $div->__toString());
        $div->setContent('bar');
        $this->assertRegexp('!<div[^<>]*class="foo"[^<>]*>bar</div>!', $div->__toString());
    }

   /**
    * @expectedException HTML_QuickForm2_InvalidArgumentException
    */
    public function testDisallowedTagNames()
    {
        $static = new HTML_QuickForm2_Element_Static('foo', null, array('tagName' => 'input'));
    }

    /**
     * If data source contains explicitly provided null values, those should be used
     * @link http://pear.php.net/bugs/bug.php?id=20295
     */
    public function testBug20295()
    {
        $form   = new HTML_QuickForm2('bug20295');
        $static = $form->addStatic('foo', array(), array('content' => 'not empty'));

        $form->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'foo' => null
        )));
        $this->assertNull($static->getContent());
    }

    public function testErroneousContentRemovalAfterFixForBug20295()
    {
        $form = new HTML_QuickForm2('afterbug20295');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array());

        $static = $form->addStatic('foo', array(), array('content' => 'not empty'));

        $this->assertEquals('not empty', $static->getContent());
    }
}
?>