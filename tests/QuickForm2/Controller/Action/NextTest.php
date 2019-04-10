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
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Controller_Action_Next class
 */
class HTML_QuickForm2_Controller_Action_NextTest
    extends PHPUnit_Framework_TestCase
{
    public function testWizardBehaviour()
    {
        $formOne = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('one'))
            ->getMock();
        $formOne->expects($this->exactly(2))->method('validate')
                ->will($this->onConsecutiveCalls(false, true));
        $formTwo = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('two'))
            ->getMock();
        $formTwo->expects($this->exactly(2))->method('validate')
                ->will($this->returnValue(true));

        $mockJumpOne = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockJumpOne->expects($this->any())->method('perform')
                    ->will($this->returnValue('jump to page one'));
        $mockJumpTwo = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockJumpTwo->expects($this->any())->method('perform')
                    ->will($this->returnValue('jump to page two'));
        $mockProcess = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockProcess->expects($this->any())->method('perform')
                    ->will($this->returnValue('do processing'));
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockDisplay->expects($this->any())->method('perform')
                    ->will($this->returnValue('output form'));

        $pageOne = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formOne))
            ->getMock();
        $pageOne->addHandler('display', $mockDisplay);
        $pageOne->addHandler('jump', $mockJumpOne);
        $pageTwo = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formTwo))
            ->getMock();
        $pageTwo->addHandler('jump', $mockJumpTwo);
        $pageTwo->addHandler('process', $mockProcess);

        $controller = new HTML_QuickForm2_Controller('wizard_next', true);
        $controller->addPage($pageOne);
        $controller->addPage($pageTwo);

        $this->assertEquals('output form', $pageOne->handle('next'));
        $this->assertEquals('jump to page two', $pageOne->handle('next'));
        $this->assertEquals('do processing', $pageTwo->handle('next'));

        $controller->getSessionContainer()->storeValidationStatus('one', false);
        $this->assertEquals('jump to page one', $pageTwo->handle('next'));
    }

    public function testNonWizardBehaviour()
    {
        $formOne = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('one'))
            ->getMock();
        $formOne->expects($this->exactly(2))->method('validate')
                ->will($this->onConsecutiveCalls(false, true));
        $formTwo = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('two'))
            ->getMock();
        $formTwo->expects($this->exactly(2))->method('validate')
                ->will($this->onConsecutiveCalls(false, true));
        $mockJumpTwo = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockJumpTwo->expects($this->any())->method('perform')
                    ->will($this->returnValue('jump to page two'));
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockDisplay->expects($this->any())->method('perform')
                    ->will($this->returnValue('output form'));

        $pageOne = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formOne))
            ->getMock();
        $pageTwo = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formTwo))
            ->getMock();
        $pageTwo->addHandler('jump', $mockJumpTwo);
        $pageTwo->addHandler('display', $mockDisplay);

        $controller = new HTML_QuickForm2_Controller('nonwizard_next', false);
        $controller->addPage($pageOne);
        $controller->addPage($pageTwo);

        // Don't bother whether the page is valid
        $this->assertEquals('jump to page two', $pageOne->handle('next'));
        $this->assertEquals('jump to page two', $pageOne->handle('next'));

        // Non-wizard form requires an explicit submit
        $this->assertEquals('output form', $pageTwo->handle('next'));
        $this->assertEquals('output form', $pageTwo->handle('next'));
    }
}
?>