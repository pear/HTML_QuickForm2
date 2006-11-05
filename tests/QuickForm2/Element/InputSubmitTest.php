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
 * Class for <input type="submit" /> elements
 */
require_once 'HTML/QuickForm2/Element/InputSubmit.php';

/**
 * PHPUnit2 Test Case
 */
require_once 'PHPUnit2/Framework/TestCase.php';

/**
 * Unit test for HTML_QuickForm2_Element_InputSubmit class
 */
class HTML_QuickForm2_Element_InputSubmitTest extends PHPUnit2_Framework_TestCase
{
    public function testConstructorSetsValue()
    {
        $submit = new HTML_QuickForm2_Element_InputSubmit('foo', 'Click me');
        $this->assertRegExp('/value="Click me"/', $submit->__toString());

        $submit2 = new HTML_QuickForm2_Element_InputSubmit('bar', null, null, array('value' => 'Click me now'));
        $this->assertRegExp('/value="Click me now"/', $submit2->__toString());

        $submit3 = new HTML_QuickForm2_Element_InputSubmit('bar', 'Click me', null, array('value' => 'Click me now'));
        $this->assertRegExp('/value="Click me"/', $submit3->__toString());
    }

    public function testCannotBeFrozen()
    {
        $submit = new HTML_QuickForm2_Element_InputSubmit('foo');
        $this->assertFalse($submit->toggleFrozen(true));
        $this->assertFalse($submit->toggleFrozen());
    }
}
?>
