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
 * Unit test for HTML_QuickForm2_Controller_Action_Display class
 */
class HTML_QuickForm2_Controller_Action_DisplayTest
    extends PHPUnit_Framework_TestCase
{
   /**
    * Do not allow displaying a wizard page if preceding page(s) are not valid
    *
    * @link http://pear.php.net/bugs/bug.php?id=2323
    */
    public function testBug2323()
    {
        $pageFirst = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array(new HTML_QuickForm2('first')))
            ->getMock();
        $mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->setMethods(array('perform'))
            ->getMock();
        $mockJump->expects($this->once())->method('perform')
                 ->will($this->returnValue('jump to first'));
        $pageFirst->addHandler('jump', $mockJump);

        $pageSecond = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array(new HTML_QuickForm2('second')))
            ->getMock();
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action_Display')
            ->setMethods(array('renderForm'))
            ->getMock();
        $mockDisplay->expects($this->never())->method('renderForm');
        $pageSecond->addHandler('display', $mockDisplay);

        $controller = new HTML_QuickForm2_Controller('bug2323', true);
        $controller->addPage($pageFirst);
        $controller->addPage($pageSecond);

        $this->assertEquals('jump to first', $pageSecond->handle('display'));
    }

    public function testLoadFromSessionContainerOnDisplay()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('load'))
            ->getMock();
        $foo = $mockForm->addElement('text', 'foo');
        $mockForm->expects($this->once())->method('validate')
                 ->will($this->returnValue(false));
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($mockForm))
            ->getMock();
        $mockPage->expects($this->once())->method('populateForm');
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action_Display')
            ->setMethods(array('renderForm'))
            ->getMock();
        $mockDisplay->expects($this->once())->method('renderForm')
                    ->will($this->returnValue('a form'));
        $mockPage->addHandler('display', $mockDisplay);

        $controller = new HTML_QuickForm2_Controller('loadValues');
        $controller->addPage($mockPage);
        $controller->getSessionContainer()->storeValues('load', array(
            'foo' => 'bar'
        ));
        $controller->getSessionContainer()->storeValidationStatus('load', false);

        $this->assertEquals('a form', $mockPage->handle('display'));
        $this->assertEquals('bar', $foo->getValue());
    }

    public function testNoLoadFromSessionContainerOnOtherActions()
    {
        $mockForm = $this->getMockBuilder('HTML_QuickForm2')
            ->setMethods(array('validate'))
            ->setConstructorArgs(array('noload'))
            ->getMock();
        $foo = $mockForm->addElement('text', 'foo');
        $mockForm->expects($this->never())->method('validate');
        $mockPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->setMethods(array('populateForm'))
            ->setConstructorArgs(array($mockForm))
            ->getMock();
        $mockDisplay = $this->getMockBuilder('HTML_QuickForm2_Controller_Action_Display')
            ->setMethods(array('renderForm'))
            ->getMock();
        $mockDisplay->expects($this->once())->method('renderForm')
                    ->will($this->returnValue('a form'));
        $mockPage->addHandler('display', $mockDisplay);

        $_REQUEST = array(
            $mockPage->getButtonName('submit') => 'Yes, submit!'
        );
        $controller = new HTML_QuickForm2_Controller('noLoadValues');
        $controller->addPage($mockPage);
        $controller->getSessionContainer()->storeValues('noload', array(
            'foo' => 'bar'
        ));
        $controller->getSessionContainer()->storeValidationStatus('noload', false);
        $controller->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'foo' => 'quux'
        )));

        $this->assertEquals('a form', $mockPage->handle('display'));
        $this->assertEquals('quux', $foo->getValue());
    }
}
?>