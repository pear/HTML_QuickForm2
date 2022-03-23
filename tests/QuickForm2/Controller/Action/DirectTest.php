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
 * @copyright 2006-2021 Alexey Borzov <avb@php.net>, Bertrand Mansion <golgote@mamasam.com>
 * @license   https://opensource.org/licenses/BSD-3-Clause BSD 3-Clause License
 * @link      https://pear.php.net/package/HTML_QuickForm2
 */

/** Sets up includes */
require_once dirname(dirname(dirname(__DIR__))) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Controller_Action_Direct class
 */
class HTML_QuickForm2_Controller_Action_DirectTest extends TestCase
{
    public function testPerform()
    {
        $source = $this->getMockBuilder('HTML_QuickForm2')
            ->onlyMethods(['validate', 'getValue'])
            ->setConstructorArgs(['source'])
            ->getMock();
        $source->expects($this->once())->method('validate')
               ->will($this->returnValue(true));
        $source->expects($this->once())->method('getValue')
               ->will($this->returnValue(['foo' => 'bar']));
        $sourcePage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->onlyMethods(['populateForm'])
            ->setConstructorArgs([$source])
            ->getMock();
        $sourcePage->addHandler('destination', new HTML_QuickForm2_Controller_Action_Direct());
        $destPage = $this->getMockBuilder('HTML_QuickForm2_Controller_Page')
            ->onlyMethods(['populateForm'])
            ->setConstructorArgs([new HTML_QuickForm2('destination')])
            ->getMock();
        $mockJump = $this->getMockBuilder('HTML_QuickForm2_Controller_Action')
            ->onlyMethods(['perform'])
            ->getMock();
        $mockJump->expects($this->once())->method('perform')
                 ->will($this->returnValue('jump to destination'));
        $destPage->addHandler('jump', $mockJump);

        $controller = new HTML_QuickForm2_Controller('testDirectAction');
        $controller->addPage($sourcePage);
        $controller->addPage($destPage);

        $this->assertEquals('jump to destination', $sourcePage->handle('destination'));
        $this->assertTrue($controller->getSessionContainer()->getValidationStatus('source'));
        $this->assertEquals(['foo' => 'bar'], $controller->getSessionContainer()->getValues('source'));
    }
}
?>