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
 * Element class
 */
require_once 'HTML/QuickForm2/Element.php';

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * A non-abstract subclass of Element 
 *
 * Element class is still abstract, we should "implement" the remaining methods
 */
class HTML_QuickForm2_ElementImpl extends HTML_QuickForm2_Element
{
    public function getType() { return 'concrete'; }
    public function getValue() { return ''; }
    public function setValue($value) { return ''; }
    public function toHtml() { return ''; }
}

/**
 * Unit test for HTML_QuickForm2_Element class, 
 */
class HTML_QuickForm2_ElementTest extends PHPUnit2_Framework_TestCase
{
    public function testCanSetName()
    {
        $obj = new HTML_QuickForm2_ElementImpl();
        $this->assertNotNull($obj->getName(), 'Elements should always have \'name\' attribute');

        $obj = new HTML_QuickForm2_ElementImpl('foo');
        $this->assertEquals('foo', $obj->getName());

        $obj->setName('bar');
        $this->assertEquals('bar', $obj->getName());

        $obj->setAttribute('name', 'baz');
        $this->assertEquals('baz', $obj->getName());
    }


    public function testCanSetId()
    {
        $obj = new HTML_QuickForm2_ElementImpl(null, null, null, array('id' => 'manual'));
        $this->assertEquals('manual', $obj->getId());

        $obj->setId('another');
        $this->assertEquals('another', $obj->getId());

        $obj->setAttribute('id', 'yet-another');
        $this->assertEquals('yet-another', $obj->getId());
    }


    public function testAutogenerateId()
    {
        $obj = new HTML_QuickForm2_ElementImpl('somename');
        $this->assertNotEquals('', $obj->getId(), 'Should have an auto-generated \'id\' attribute');

        $obj2 = new HTML_QuickForm2_ElementImpl('somename');
        $this->assertNotEquals($obj2->getId(), $obj->getId(), 'Auto-generated \'id\' attributes should be unique');
    }


    public function testCanNotRemoveNameOrId()
    {
        $obj = new HTML_QuickForm2_ElementImpl('somename', null, null, array('id' => 'someid'));
        try {
            $obj->removeAttribute('name');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegExp('/Required attribute(.*)can not be removed/', $e->getMessage());
            try {
                $obj->removeAttribute('id');
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertRegExp('/Required attribute(.*)can not be removed/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }
}
?>
