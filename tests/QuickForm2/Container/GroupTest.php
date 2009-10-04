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
 * Class for <group> elements
 */
require_once 'HTML/QuickForm2/Container/Group.php';

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * Class representing a HTML form
 */
require_once 'HTML/QuickForm2.php';

/**
 * Unit test for HTML_QuickForm2_Element_Group class
 */
class HTML_QuickForm2_Element_GroupTest extends PHPUnit_Framework_TestCase
{

    public function testNoRenameOnEmptyGroupName()
    {
        $g1 = new HTML_QuickForm2_Container_Group();

        $e = $g1->addText('e0');
        $this->assertEquals('e0', $e->getName());

        $e = $g1->addText('e1[e2]');
        $this->assertEquals('e1[e2]', $e->getName());
    }

    public function testGroupRename()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1[g4]');

        $e1 = $g1->addText('e1');
        $e2 = $g1->addText('e2[x]');
        $this->assertEquals('g1[g4][e1]', $e1->getName());
        $this->assertEquals('g1[g4][e2][x]', $e2->getName());

        $g1->setName('g2');
        $this->assertEquals('g2[e1]', $e1->getName());
        $this->assertEquals('g2[e2][x]', $e2->getName());

        $g1->setName('');
        $this->assertEquals('e1', $e1->getName());
        $this->assertEquals('e2[x]', $e2->getName());
    }

    public function testElementRename()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1');

        $e = $g1->addText('e0');
        $this->assertEquals('g1[e0]', $e->getName());

        $e = $g1->addText('e1[e2]');
        $this->assertEquals('g1[e1][e2]', $e->getName());

        $e = $g1->addText('e3[]');
        $this->assertEquals('g1[e3][]', $e->getName());

        $e = $g1->addText('[e4]');
        $this->assertEquals('g1[][e4]', $e->getName());

        $e = $g1->addText('');
        $this->assertEquals('g1[]', $e->getName());

        $e = $g1->addText();
        $this->assertEquals('g1[]', $e->getName());

        $e = $g1->addText('[]');
        $this->assertEquals('g1[][]', $e->getName());
    }

    public function testGroupedElementRename()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1');

        $e0 = $g1->addText('e0');
        $this->assertEquals('g1[e0]', $e0->getName());

        $g2 = new HTML_QuickForm2_Container_Group('g2');

        $e1 = $g2->addText('e1');
        $this->assertEquals('g2[e1]', $e1->getName());

        $g1->addElement($g2);
        $this->assertEquals('g1[g2]', $g2->getName());
        $this->assertEquals('g1[g2][e1]', $e1->getName());

        $g3 = new HTML_QuickForm2_Container_Group('g3');
        $g3->addElement($g1);
        $this->assertEquals('g3[g1]', $g1->getName());
        $this->assertEquals('g3[g1][e0]', $e0->getName());
        $this->assertEquals('g3[g1][g2]', $g2->getName());
        $this->assertEquals('g3[g1][g2][e1]', $e1->getName());

        $e2 = $g1->addText('e2');
        $this->assertEquals('g3[g1][e2]', $e2->getName());

        $e3 = $g1->addText('e3[x]');
        $this->assertEquals('g3[g1][e3][x]', $e3->getName());

        $e4 = $g1->addText('e4[]');
        $this->assertEquals('g3[g1][e4][]', $e4->getName());

        $e5 = $g1->addText('[e5]');
        $this->assertEquals('g3[g1][][e5]', $e5->getName());

        $e6 = $g1->addText('[e6]');
        $this->assertEquals('g3[g1][][e6]', $e6->getName());

        $e7 = $g1->addText('[]');
        $this->assertEquals('g3[g1][][]', $e7->getName());

        $e8 = $g1->addText('');
        $this->assertEquals('g3[g1][]', $e8->getName());

        $g4 = new HTML_QuickForm2_Container_Group('g4');
        $g4->addElement($g3);
        $this->assertEquals('g4[g3]', $g3->getName());
        $this->assertEquals('g4[g3][g1]', $g1->getName());
        $this->assertEquals('g4[g3][g1][e2]', $e2->getName());
        $this->assertEquals('g4[g3][g1][e3][x]', $e3->getName());
        $this->assertEquals('g4[g3][g1][e4][]', $e4->getName());
        $this->assertEquals('g4[g3][g1][][e5]', $e5->getName());
        $this->assertEquals('g4[g3][g1][][e6]', $e6->getName());
        $this->assertEquals('g4[g3][g1][][]', $e7->getName());
        $this->assertEquals('g4[g3][g1][]', $e8->getName());
    }

    public function testPrependGroupNameOnInsertBefore()
    {
        $foo = new HTML_QuickForm2_Container_Group('foo');
        $fooBar = $foo->insertBefore(
            HTML_QuickForm2_Factory::createElement('text', 'bar')
        );
        $this->assertEquals('foo[bar]', $fooBar->getName());

        $fooBaz = $foo->insertBefore(
            HTML_QuickForm2_Factory::createElement('text', 'baz'), $fooBar
        );
        $this->assertEquals('foo[baz]', $fooBaz->getName());
    }

    public function testRemoveGroupNameOnRemoveChild()
    {
        $foo  = new HTML_QuickForm2_Container_Group('foo');
        $bar  = $foo->addElement('group', 'bar');
        $baz  = $bar->addElement('text', 'baz');
        $quux = $bar->addElement('text', 'qu[ux]');
        $this->assertEquals('foo[bar][baz]', $baz->getName());
        $this->assertEquals('foo[bar][qu][ux]', $quux->getName());

        $foo->removeChild($bar);
        $this->assertEquals('bar[baz]', $baz->getName());
        $this->assertEquals('bar[qu][ux]', $quux->getName());

        $bar->removeChild($baz);
        $this->assertEquals('baz', $baz->getName());

        $bar->removeChild($quux);
        $this->assertEquals('qu[ux]', $quux->getName());
    }

    public function testRenameElementOnChangingGroups()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1');
        $g2 = new HTML_QuickForm2_Container_Group('g2');

        $e1 = $g1->addElement('text', 'e1');
        $this->assertEquals('g1[e1]', $e1->getName());

        $g2->addElement($e1);
        $this->assertEquals('g2[e1]', $e1->getName());
    }

    public function testSetValue()
    {
        $foo     = new HTML_QuickForm2_Container_Group('foo');
        $fooBar  = $foo->addText('bar');
        $fooBaz  = $foo->addText('baz');
        $fooQuux = $foo->addText('qu[ux]');

        $foo->setValue(array(
            'bar' => 'first value',
            'baz' => 'second value',
            'qu'  => array('ux' => 'third value')
        ));
        $this->assertEquals('first value', $fooBar->getValue());
        $this->assertEquals('second value', $fooBaz->getValue());
        $this->assertEquals('third value', $fooQuux->getValue());
    }

    public function testGetValue()
    {
        $value1    = array('foo' => 'foo value');
        $value2    = array('bar' => 'bar value', 'baz' => array('quux' => 'baz value'));
        $formValue = array('g1' => $value1, 'g2' => array('i2' => $value2));

        $form = new HTML_QuickForm2('testGroupGetValue');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array($formValue));
        $g1 = $form->addElement('group', 'g1');
        $g1->addElement('text', 'foo');
        $g2 = $form->addElement('group', 'g2[i2]');
        $g2->addElement('text', 'bar');
        $g2->addElement('text', 'baz[quux]');

        $this->assertEquals($formValue, $form->getValue());
        $this->assertEquals($value1, $g1->getValue());
        $this->assertEquals($value2, $g2->getValue());
    }
}
?>
