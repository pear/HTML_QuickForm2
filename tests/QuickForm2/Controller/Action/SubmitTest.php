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
 * @copyright 2006-2023 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__DIR__))) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Controller_Action_Submit class
 */
class HTML_QuickForm2_Controller_Action_SubmitTest extends TestCase
{
    public function testProcessIfValid()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(['validate', 'getValue'])
            ->setConstructorArgs(['valid'])
            ->getMock();
        $mockForm->expects($this->once())->method('validate')
                 ->will($this->returnValue(true));
        $mockForm->expects($this->once())->method('getValue')
                 ->will($this->returnValue(['foo' => 'bar']));
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([$mockForm])
            ->getMock();
        $mockProcess = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(['perform'])
            ->getMock();
        $mockProcess->expects($this->once())->method('perform')
                    ->will($this->returnValue('do processing'));
        $mockPage->addHandler('process', $mockProcess);
        $controller = new HTML_QuickForm2_Controller('submit_valid');
        $controller->addPage($mockPage);

        $this->assertEquals('do processing', $mockPage->handle('submit'));
        $this->assertEquals(['foo' => 'bar'], $controller->getValue());
    }

    public function testDisplayIfInvalid()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(['validate', 'getValue'])
            ->setConstructorArgs(['invalid'])
            ->getMock();
        $mockForm->expects($this->once())->method('validate')
                 ->will($this->returnValue(false));
        $mockForm->expects($this->once())->method('getValue')
                 ->will($this->returnValue(['foo' => 'bar']));
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([$mockForm])
            ->getMock();
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(['perform'])
            ->getMock();
        $mockDisplay->expects($this->once())->method('perform')
                    ->will($this->returnValue('display form'));
        $mockPage->addHandler('display', $mockDisplay);
        $controller = new HTML_QuickForm2_Controller('submit_invalid');
        $controller->addPage($mockPage);

        $this->assertEquals('display form', $mockPage->handle('submit'));
        $this->assertEquals(['foo' => 'bar'], $controller->getValue());
    }

    public function testRedirectToInvalidPage()
    {
        $pageFirst = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('first')])
            ->getMock();
        $formSecond = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(['validate'])
            ->setConstructorArgs(['second'])
            ->getMock();
        $formSecond->expects($this->once())->method('validate')
                   ->will($this->returnValue(true));
        $pageSecond = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(['populateForm'])
            ->setConstructorArgs([$formSecond])
            ->getMock();
        $mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(['perform'])
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