<?php
/**
 * Unit tests for HTML_QuickForm2 package
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2014, Alexey Borzov <avb@php.net>,
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
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Controller_Action_Submit class
 */
class HTML_QuickForm2_Controller_Action_SubmitTest
    extends PHPUnit_Framework_TestCase
{
    public function testProcessIfValid()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate', 'getValue'))
            ->setConstructorArgs(array('valid'))
            ->getMock();
        $mockForm->expects($this->once())->method('validate')
                 ->will($this->returnValue(true));
        $mockForm->expects($this->once())->method('getValue')
                 ->will($this->returnValue(array('foo' => 'bar')));
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($mockForm))
            ->getMock();
        $mockProcess = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockProcess->expects($this->once())->method('perform')
                    ->will($this->returnValue('do processing'));
        $mockPage->addHandler('process', $mockProcess);
        $controller = new HTML_QuickForm2_Controller('submit_valid');
        $controller->addPage($mockPage);

        $this->assertEquals('do processing', $mockPage->handle('submit'));
        $this->assertEquals(array('foo' => 'bar'), $controller->getValue());
    }

    public function testDisplayIfInvalid()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate', 'getValue'))
            ->setConstructorArgs(array('invalid'))
            ->getMock();
        $mockForm->expects($this->once())->method('validate')
                 ->will($this->returnValue(false));
        $mockForm->expects($this->once())->method('getValue')
                 ->will($this->returnValue(array('foo' => 'bar')));
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($mockForm))
            ->getMock();
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockDisplay->expects($this->once())->method('perform')
                    ->will($this->returnValue('display form'));
        $mockPage->addHandler('display', $mockDisplay);
        $controller = new HTML_QuickForm2_Controller('submit_invalid');
        $controller->addPage($mockPage);

        $this->assertEquals('display form', $mockPage->handle('submit'));
        $this->assertEquals(array('foo' => 'bar'), $controller->getValue());
    }

    public function testRedirectToInvalidPage()
    {
        $pageFirst = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array(new HTML_QuickForm2('first')))
            ->getMock();
        $formSecond = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('second'))
            ->getMock();
        $formSecond->expects($this->once())->method('validate')
                   ->will($this->returnValue(true));
        $pageSecond = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formSecond))
            ->getMock();
        $mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockJump->expects($this->once())->method('perform')
                 ->will($this->returnValue('jump to first'));
        $pageFirst->addHandler('jump', $mockJump);
        $controller = new HTML_QuickForm2_Controller('redirect_invalid', false);
        $controller->addPage($pageFirst);
        $controller->addPage($pageSecond);
        $controller->getSessionContainer()->storeValidationStatus('first', false);

        $this->assertEquals('jump to first', $pageSecond->handle('submit'));
    }
}
?>