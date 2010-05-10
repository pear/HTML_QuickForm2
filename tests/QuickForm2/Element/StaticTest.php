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
 * Class for static elements
 */
require_once 'HTML/QuickForm2/Element/Static.php';

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';


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
}
?>