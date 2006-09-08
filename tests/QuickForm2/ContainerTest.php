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
 * Container class
 */
require_once 'HTML/QuickForm2/Container.php';
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
class HTML_QuickForm2_ElementImpl2 extends HTML_QuickForm2_Element
{
    public function getType() { return 'concrete'; }
    public function getValue() { return ''; }
    public function setValue($value) { return ''; }
    public function toHtml() { return ''; }
}

/**
 * A non-abstract subclass of Container 
 *
 * Container class is still abstract, we should "implement" the remaining methods
 */
class HTML_QuickForm2_ContainerImpl extends HTML_QuickForm2_Container
{
	public $idIndex;

    public function getType() { return 'concrete'; }
    public function getValue() { return ''; }
    public function setValue($value) { return ''; }
    public function toHtml() { return ''; }
}

/**
 * Unit test for HTML_QuickForm2_Container class
 */
//class HTML_QuickForm2_ContainerTest extends PHPUnit2_Framework_TestCase
class ContainerTest extends PHPUnit2_Framework_TestCase
{
    public function testCanSetName()
    {
        $obj = new HTML_QuickForm2_ContainerImpl();
        $this->assertNotNull($obj->getName(), 'Containers should always have \'name\' attribute');

        $obj = new HTML_QuickForm2_ContainerImpl('foo');
        $this->assertEquals('foo', $obj->getName());

        $obj->setName('bar');
        $this->assertEquals('bar', $obj->getName());

        $obj->setAttribute('name', 'baz');
        $this->assertEquals('baz', $obj->getName());

    }


    public function testCanSetId()
    {
        $obj = new HTML_QuickForm2_ContainerImpl(null, null, null, array('id' => 'manual'));
        $this->assertEquals('manual', $obj->getId());

        $obj->setId('another');
        $this->assertEquals('another', $obj->getId());

        $obj->setAttribute('id', 'yet-another');
        $this->assertEquals('yet-another', $obj->getId());
    }


    public function testAutogenerateId()
    {
        $obj = new HTML_QuickForm2_ContainerImpl('somename');
        $this->assertNotEquals('', $obj->getId(), 'Should have an auto-generated \'id\' attribute');

        $obj2 = new HTML_QuickForm2_ContainerImpl('somename');
        $this->assertNotEquals($obj2->getId(), $obj->getId(), 'Auto-generated \'id\' attributes should be unique');
    }


    public function testCanNotRemoveNameOrId()
    {
        $obj = new HTML_QuickForm2_ContainerImpl('somename', null, null, array('id' => 'someid'));
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


	public function testAddAndGetElements()
	{
		$e1 = new HTML_QuickForm2_ElementImpl2('e1');
		$e2 = new HTML_QuickForm2_ElementImpl2('e2');
		$c1 = new HTML_QuickForm2_ContainerImpl('c1');
		$c1->addElement($e1);
		$c1->addElement($e2);
		$this->assertEquals(2, count($c1), 'Element count is incorrect');
		$e1b = $c1->getElementById('e1-0');
		$this->assertEquals($e1b->getId(), $e1->getId(), 'First element id differs');
		$e2b = $c1->getElementById('e2-0');
		$this->assertEquals($e2b->getId(), $e2->getId(), 'Second element id differs');
	}


	public function testNestedAddAndGetElements()
	{
		$e1 = new HTML_QuickForm2_ElementImpl2('a1');
		$e2 = new HTML_QuickForm2_ElementImpl2('a2');
		$c1 = new HTML_QuickForm2_ContainerImpl('b1');
		$c1->addElement($e1);
		$c1->addElement($e2);

		$e3 = new HTML_QuickForm2_ElementImpl2('a3');
		$e4 = new HTML_QuickForm2_ElementImpl2('a4');
		$c2 = new HTML_QuickForm2_ContainerImpl('b2');
		$c2->addElement($e3);
		$c2->addElement($e4);
		$c2->addElement($c1);

		$this->assertEquals(3, count($c2), 'Element count is incorrect');
		$e1b = $c2->getElementById('a1-0');
		$this->assertEquals($e1b->getId(), $e1->getId(), 'First element id differs');
		$e2b = $c2->getElementById('a2-0');
		$this->assertEquals($e2b->getId(), $e2->getId(), 'Second element id differs');
	}


	public function testCannotSetContainerOnSelf()
	{
		$e1 = new HTML_QuickForm2_ElementImpl2('d1');
		$e2 = new HTML_QuickForm2_ElementImpl2('d2');
		$c1 = new HTML_QuickForm2_ContainerImpl('f1');
		$c1->addElement($e1);
		$c1->addElement($e2);
        try {
            $c1->addElement($c1);
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertEquals('Cannot set an element or its child as its own container', $e->getMessage());
			$c2 = new HTML_QuickForm2_ContainerImpl('f2');
			$c2->addElement($c1);
            try {
                $c1->addElement($c2);
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertEquals('Cannot set an element or its child as its own container', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
	}


	public function testAddSameElementMoreThanOnce()
	{
		$e1 = new HTML_QuickForm2_ElementImpl2('g1');
		$e2 = new HTML_QuickForm2_ElementImpl2('g2');
		$c1 = new HTML_QuickForm2_ContainerImpl('h1');
		$c1->addElement($e1);
		$c1->addElement($e2);
		$c1->addElement($e1);

		$this->assertEquals(2, count($c1), 'Element count is incorrect');
		$e1b = $c1->getElementById('g1-0');
		$this->assertEquals($e1b->getId(), $e1->getId(), 'First element id differs');
		$e2b = $c1->getElementById('g2-0');
		$this->assertEquals($e2b->getId(), $e2->getId(), 'Second element id differs');

	}	

	public function testMoveAndRemove()
	{
		$e1 = new HTML_QuickForm2_ElementImpl2('i1');
		$e2 = new HTML_QuickForm2_ElementImpl2('i2');
		$e3 = new HTML_QuickForm2_ElementImpl2('i3');
		$e4 = new HTML_QuickForm2_ElementImpl2('i4');
		$e5 = new HTML_QuickForm2_ElementImpl2('i5');

		$c1 = new HTML_QuickForm2_ContainerImpl('j1');
		$c1->addElement($e1);
		$c1->addElement($e2);
		$c1->addElement($e3);
		$c1->addElement($e4);
		$c1->addElement($e5);

		$c2 = new HTML_QuickForm2_ContainerImpl('j2');
		$c2->addElement($c1);

		$g1 = $c1->getElementById('i3-0');
		$this->assertEquals($e3->getId(), $g1->getId(), 'Element should be found in container');

		$c2->addElement($e3);

		$g1 = $c1->getElementById('i3-0');
		$this->assertEquals(null, $g1, 'Element should not be a child of this container');
		$g1 = $c2->getElementById('i3-0');
		$this->assertEquals($e3->getId(), $g1->getId(), 'Element should be found in container');

		$c1->removeChild($e3);
		$g1 = $c2->getElementById('i3-0');
		$this->assertNotEquals(null, $g1, 'Element should still be found in container');
		$this->assertEquals($e3->getId(), $g1->getId(), 'Element should be found in container');

		$c2->removeChild($e3);
		$g1 = $c2->getElementById('i3-0');
		$this->assertEquals(null, $g1, 'Element should have been removed');

		$this->assertEquals(4, count($c1), 'Element count is incorrect');
		$this->assertEquals(1, count($c2), 'Element count is incorrect');

		$g2 = $c2->getElementById('i4-0');
		$this->assertEquals($e4->getId(), $g2->getId(), 'Element should have been found');

		$c2->removeChild($e4);
		$this->assertEquals(3, count($c1), 'Element count is incorrect');
		$g2 = $c2->getElementById('i4-0');
		$this->assertEquals(null, $g2, 'Element should have been removed');
	}

	public function testInsertBefore()
	{
		$e1 = new HTML_QuickForm2_ElementImpl2('k1');
		$e2 = new HTML_QuickForm2_ElementImpl2('k2');
		$e3 = new HTML_QuickForm2_ElementImpl2('k3');
		$e4 = new HTML_QuickForm2_ElementImpl2('k4');
		$e5 = new HTML_QuickForm2_ElementImpl2('k5');
		$e6 = new HTML_QuickForm2_ElementImpl2('k6');
		$e7 = new HTML_QuickForm2_ElementImpl2('k7');

		$c1 = new HTML_QuickForm2_ContainerImpl('l1');
		$c1->addElement($e1);
		$this->assertEquals(array('k1-0'=>0), $c1->idIndex);
		$c1->insertBefore($e2);
		$this->assertEquals(array('k1-0'=>0,'k2-0'=>1), $c1->idIndex);
		$c1->insertBefore($e3, $e2);
		$this->assertEquals(array('k1-0'=>0,'k3-0'=>1,'k2-0'=>2), $c1->idIndex);
		$c1->insertBefore($e4, $e3);
		$this->assertEquals(array('k1-0'=>0,'k4-0'=>1,'k3-0'=>2,'k2-0'=>3), $c1->idIndex);
		$c1->insertBefore($e5, $e1);
		$this->assertEquals(array('k5-0'=>0,'k1-0'=>1,'k4-0'=>2,'k3-0'=>3,'k2-0'=>4), $c1->idIndex);

		$c2 = new HTML_QuickForm2_ContainerImpl('l2');
		$c2->addElement($e6);
		$this->assertEquals(array('k6-0'=>0), $c2->idIndex);
		$c2->insertBefore($c1, $e6);
		$this->assertEquals(array('l1-0'=>0,'k1-0'=>0,'k2-0'=>0,'k3-0'=>0,'k4-0'=>0,'k5-0'=>0,'k6-0'=>1), $c2->idIndex);


	}




}
?>
