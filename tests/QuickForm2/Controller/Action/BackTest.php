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
 * Unit test for HTML_QuickForm2_Controller_Action_Back class
 */
class HTML_QuickForm2_Controller_Action_BackTest
    extends PHPUnit_Framework_TestCase
{
    public function testPerform()
    {
        $formOne = new HTML_QuickForm2('formOne');
        $formOne->addElement('text', 'foo')->setValue('foo value');
        $pageOne = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formOne))
            ->getMock();
        $formTwo = new HTML_QuickForm2('formTwo');
        $formTwo->addElement('text', 'bar')->setValue('bar value');
        $pageTwo = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($formTwo))
            ->getMock();
        $mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockJump->expects($this->exactly(2))->method('perform')
                 ->will($this->returnValue('jump to foo'));
        $pageOne->addHandler('jump', $mockJump);
        $controller = new HTML_QuickForm2_Controller('testBackAction');
        $controller->addPage($pageOne);
        $controller->addPage($pageTwo);

        $this->assertEquals('jump to foo', $pageTwo->handle('back'));
        $this->assertEquals(array(), $controller->getSessionContainer()->getValues('formOne'));
        $this->assertContains('bar value', $controller->getSessionContainer()->getValues('formTwo'));

        $this->assertEquals('jump to foo', $pageOne->handle('back'));
        $this->assertContains('foo value', $controller->getSessionContainer()->getValues('formOne'));
    }

    public function testNoValidationForWizards()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('eternallyValid'))
            ->getMock();
        $mockForm->expects($this->once())->method('validate')
                 ->will($this->returnValue(true));
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($mockForm))
            ->getMock();
        $mockPage->addHandler(
            'jump',
            $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
                ->setMethods(array('perform'))
                ->getMock()
        );

        $wizard = new HTML_QuickForm2_Controller('wizard', true);
        $wizard->addPage($mockPage);
        $mockPage->handle('back');
        $this->assertNull($wizard->getSessionContainer()->getValidationStatus('eternallyValid'));

        $nonWizard = new HTML_QuickForm2_Controller('nonWizard', false);
        $nonWizard->addPage($mockPage);
        $mockPage->handle('back');
        $this->assertTrue($nonWizard->getSessionContainer()->getValidationStatus('eternallyValid'));
    }
}