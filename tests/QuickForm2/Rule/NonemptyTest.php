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
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Rule_Nonempty class
 */
class HTML_QuickForm2_Rule_NonemptyTest extends TestCase
{
    function testValidateGenericElement()
    {
        $mockValid = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                    'getRawValue', 'setValue', '__toString'])
            ->getMock();
        $mockValid->expects($this->once())->method('getRawValue')
                  ->will($this->returnValue('a string'));
        $rule = new HTML_QuickForm2_Rule_Nonempty($mockValid, 'an error');
        $this->assertTrue($rule->validate());
        $this->assertEquals('', $mockValid->getError());

        $mockInvalid = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                      'getRawValue', 'setValue', '__toString'])
            ->getMock();
        $mockInvalid->expects($this->once())->method('getRawValue')
                    ->will($this->returnValue(''));
        $rule2 = new HTML_QuickForm2_Rule_Nonempty($mockInvalid, 'an error');
        $this->assertFalse($rule2->validate());
        $this->assertEquals('an error', $mockInvalid->getError());
    }

    function testValidateInputFileElement()
    {
        $mockValid = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(['getValue'])
            ->getMock();
        $mockValid->expects($this->once())->method('getValue')
                  ->will($this->returnValue([
                    'name'     => 'goodfile.php',
                    'type'     => 'application/octet-stream',
                    'tmp_name' => '/tmp/foobar',
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => 1234
                  ]));
        $rule = new HTML_QuickForm2_Rule_Nonempty($mockValid, 'an error');
        $this->assertTrue($rule->validate());
        $this->assertEquals('', $mockValid->getError());

        $mockInvalid = $this->getMockBuilder('HTML_QuickForm2_Element_InputFile')
            ->setMethods(['getValue'])
            ->getMock();
        $mockInvalid->expects($this->once())->method('getValue')
                    ->will($this->returnValue([
                        'name'     => '',
                        'type'     => '',
                        'tmp_name' => '',
                        'error'    => UPLOAD_ERR_NO_FILE,
                        'size'     => 0
                    ]));
        $rule2 = new HTML_QuickForm2_Rule_Nonempty($mockInvalid, 'an error');
        $this->assertFalse($rule2->validate());
        $this->assertEquals('an error', $mockInvalid->getError());
    }

    public function testDefaultConfig()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        $rule = new HTML_QuickForm2_Rule_Nonempty($mockEl);
        $this->assertEquals(1, $rule->getConfig());
    }

    public function testPositiveNumberRequired()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType',
                                 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        try {
            $rule = new HTML_QuickForm2_Rule_Nonempty($mockEl, 'an error', -1);
            $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {}
    }

   /**
    * @see  http://pear.php.net/bugs/bug.php?id=12610
    */
    function testValidateSelectMultiple()
    {
        $options     = ['1' => 'Option 1', '2' => 'Option 2'];
        $multiSelect = new HTML_QuickForm2_Element_Select(
            'mult', ['multiple'], ['options' => $options]
        );

        $nonEmpty = new HTML_QuickForm2_Rule_Nonempty($multiSelect, 'an error');
        $this->assertFalse($nonEmpty->validate());

        $multiSelect->setValue([1]);
        $this->assertTrue($nonEmpty->validate());

        $nonEmpty->setConfig(2);
        $this->assertFalse($nonEmpty->validate());
    }

    function testValidateContainer()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $bar = $mockContainer->addElement('text', 'bar[idx]')->setValue('I am not empty');

        $nonEmpty = new HTML_QuickForm2_Rule_Nonempty($mockContainer, 'an error');
        $this->assertTrue($nonEmpty->validate());

        $nonEmpty->setConfig(2);
        $this->assertFalse($nonEmpty->validate());
        $this->assertEquals('an error', $mockContainer->getError());
        $this->assertEquals('', $foo->getError());
        $this->assertEquals('', $bar->getError());
    }

    function testValidateNestedContainer()
    {
        $mockOuter = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $mockInner = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $foo = $mockOuter->addElement('text', 'foo[idx]')->setValue('');
        $bar = $mockInner->addElement('text', 'bar[idx]')->setValue('not empty');
        $mockOuter->appendChild($mockInner);

        $nonEmpty = new HTML_QuickForm2_Rule_Nonempty($mockOuter, 'an error');
        $this->assertTrue($nonEmpty->validate());

        $nonEmpty->setConfig(2);
        $this->assertFalse($nonEmpty->validate());
    }

    public function testContainerValidationTriggers()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $foo = $mockContainer->addElement('text', 'foo', ['id' => 'foo']);
        $bar = $mockContainer->addElement('text', 'bar', ['id' => 'bar']);

        $nonEmpty = new HTML_QuickForm2_Rule_Nonempty($mockContainer, 'an error');
        $this->assertContains('["foo","bar"]', $nonEmpty->getJavascript());
    }
}
?>