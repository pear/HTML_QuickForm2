<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2012, Alexey Borzov <avb@php.net>,
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

/** Sets up includes */
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';
/** Handles a Container that can be repeated multiple times in the form */
require_once 'HTML/QuickForm2/Container/Repeat.php';
/** Class for <fieldset> elements */
require_once 'HTML/QuickForm2/Container/Fieldset.php';
/** Class for <input type="text" /> elements */
require_once 'HTML/QuickForm2/Element/InputText.php';
/** Class representing a HTML form  */
require_once 'HTML/QuickForm2.php';
/** Base class for HTML_QuickForm2 renderers */
require_once 'HTML/QuickForm2/Renderer.php';


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
        $repeat   = new HTML_QuickForm2_Container_Repeat();
        $fieldset = new HTML_QuickForm2_Container_Fieldset();
        $textOne  = new HTML_QuickForm2_Element_InputText('firstText');
        $textTwo  = new HTML_QuickForm2_Element_InputText('secondText');

        $repeat->setPrototype($fieldset);
        $repeat->appendChild($textOne);
        $this->assertSame($textOne->getContainer(), $fieldset);

        $repeat->insertBefore($textTwo, $textOne);
        $this->assertSame($textTwo->getContainer(), $fieldset);

        $repeat->removeChild($textOne);
        $this->assertNull($textOne->getContainer());
    }
}
?>