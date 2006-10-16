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
require_once 'HTML/QuickForm2/AbstractElement.php';

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * A non-abstract subclass of AbstractElement 
 *
 * We can't instantiate the class directly and thus need to "implement" its
 * abstract methods
 */
class HTML_QuickForm2_AbstractElementImpl extends HTML_QuickForm2_AbstractElement
{
    public function getType() { return 'concrete'; }
    public function getValue() { return ''; }
    public function setValue($value) { return ''; }
    public function __toString() { return ''; }

    public function getName() { return ''; }
    public function setName($name) { }
    public function getId() { return ''; }
    public function setId($id = null) { }
}

/**
 * Unit test for HTML_QuickForm2_AbstractElement class, 
 */
class HTML_QuickForm2_AbstractElementTest extends PHPUnit2_Framework_TestCase
{
    public function testCanSetLabel()
    {
        $obj = new HTML_QuickForm2_AbstractElementImpl();
        $this->assertNull($obj->getLabel());

        $obj2 = new HTML_QuickForm2_AbstractElementImpl(null, null, 'a label');
        $this->assertEquals('a label', $obj2->getLabel());

        $obj2->setLabel('another label');
        $this->assertEquals('another label', $obj2->getLabel());
    }

    public function testCanFreezeAndUnfreeze()
    {
        $obj = new HTML_QuickForm2_AbstractElementImpl();
        $this->assertFalse($obj->toggleFrozen(), 'Elements should NOT be frozen by default');

        $oldFrozen = $obj->toggleFrozen(true);
        $this->assertFalse($oldFrozen, 'toggleFrozen() should return previous frozen status');
        $this->assertTrue($obj->toggleFrozen());
        
        $this->assertTrue($obj->toggleFrozen(false), 'toggleFrozen() should return previous frozen status');
        $this->assertFalse($obj->toggleFrozen());
    }

    public function testCanSetPersistentFreeze()
    {
        $obj = new HTML_QuickForm2_AbstractElementImpl();
        $this->assertFalse($obj->persistentFreeze(), 'Frozen element\'s data should NOT persist by default');

        $oldPersistent = $obj->persistentFreeze(true);
        $this->assertFalse($oldPersistent, 'persistentFreeze() should return previous persistence status');
        $this->assertTrue($obj->persistentFreeze());
        
        $this->assertTrue($obj->persistentFreeze(false), 'persistentFreeze() should return previous persistence status');
        $this->assertFalse($obj->persistentFreeze());
    }
}
?>
