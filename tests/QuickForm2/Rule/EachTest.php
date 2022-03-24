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
 * Unit test for HTML_QuickForm2_Rule_Each class
 */
class HTML_QuickForm2_Rule_EachTest extends TestCase
{
    public function testDisallowMissingConfig()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();

        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);
        $this::expectExceptionMessage('Each Rule requires a template Rule to validate with');
        new HTML_QuickForm2_Rule_Each($mockEl);
    }

    public function testDisallowNonRuleConfig()
    {
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();

        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);
        $this::expectExceptionMessage('Each Rule requires a template Rule to validate with');
        new HTML_QuickForm2_Rule_Each($mockEl, '', 'A rule?');
    }

    public function testCannotUseRequiredAsTemplate()
    {
        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);
        $this::expectExceptionMessage('Cannot use "required" Rule as a template');
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        new HTML_QuickForm2_Rule_Each($mockEl, 'an error', $mockEl->createRule('required', 'an error'));
    }

    public function testCanOnlyValidateContainers()
    {
        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);
        $this::expectExceptionMessage('Each Rule can only validate Containers');
        $mockEl = $this->getMockBuilder('HTML_QuickForm2_Element')
            ->setMethods(['getType', 'getRawValue', 'setValue', '__toString'])
            ->getMock();
        new HTML_QuickForm2_Rule_Each($mockEl, '', $mockEl->createRule('empty'));
    }

    public function testValidatesWithTemplateRule()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $bar = $mockContainer->addElement('text', 'bar')->setValue('I am not empty');
        $baz = $mockContainer->addElement('text', 'baz')->setValue('');

        $each = new HTML_QuickForm2_Rule_Each(
            $mockContainer, 'an error', $mockContainer->createRule('empty')
        );
        $this->assertFalse($each->validate());

        $mockContainer->removeChild($bar);
        $this->assertTrue($each->validate());
    }

    public function testSetsErrorOnContainer()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $bar = $mockContainer->addElement('text', 'bar')->setValue('I am not empty');

        $each = new HTML_QuickForm2_Rule_Each(
            $mockContainer, 'Real error', $mockContainer->createRule('empty', 'Template error')
        );
        $this->assertFalse($each->validate());
        $this->assertEquals('Real error', $mockContainer->getError());
        $this->assertEquals('', $bar->getError());
    }

    public function testChainedRulesAreIgnored()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();

        $foo = $mockContainer->addElement('text', 'foo')->setValue('');
        $ruleIgnored = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$foo])
            ->getMock();
        $ruleIgnored->expects($this->never())->method('validateOwner');

        $each = new HTML_QuickForm2_Rule_Each(
            $mockContainer, 'an error', $mockContainer->createRule('empty')
                                                      ->and_($ruleIgnored)
        );
        $this->assertTrue($each->validate());
    }

    public function testValidateNestedContainer()
    {
        $mockOuter = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $mockInner = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $foo = $mockOuter->addElement('text', 'foo')->setValue('');
        $bar = $mockInner->addElement('text', 'bar')->setValue('not empty');
        $mockOuter->appendChild($mockInner);

        $each = new HTML_QuickForm2_Rule_Each(
            $mockOuter, 'Real error', $mockOuter->createRule('empty')
        );
        $this->assertFalse($each->validate());

        $bar->setValue('');
        $this->assertTrue($each->validate());
    }

    public function testIgnoresStaticServerSide()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $mockContainer->addElement('static', 'noValidateServer');

        $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$mockContainer, 'a message'])
            ->getMock();
        $rule->expects($this->any())->method('validateOwner')
             ->will($this->returnValue(false));

        $each = new HTML_QuickForm2_Rule_Each($mockContainer, 'an error', $rule);
        $this->assertTrue($each->validate());
    }

    public function testIgnoresStaticClientSide()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $mockContainer->addElement('static', 'noValidateClient');

        $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner', 'getJavascriptCallback'])
            ->setConstructorArgs([$mockContainer, 'a message'])
            ->getMock();
        $rule->expects($this->any())->method('getJavascriptCallback')
             ->will($this->returnValue('staticCallback'));

        $each = new HTML_QuickForm2_Rule_Each($mockContainer, 'an error', $rule);
        $this->assertStringNotContainsString('staticCallback', $each->getJavascript());
    }

    public function testValidationTriggers()
    {
        $mockContainer = $this->getMockBuilder('HTML_QuickForm2_Container')
            ->setMethods(['getType', 'setValue', '__toString'])
            ->getMock();
        $mockContainer->addElement('text', 'foo', ['id' => 'foo']);
        $mockContainer->addElement('text', 'bar', ['id' => 'bar']);

        $rule = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner', 'getJavascriptCallback'])
            ->setConstructorArgs([$mockContainer, 'a message'])
            ->getMock();
        $rule->expects($this->any())->method('getJavascriptCallback')
             ->will($this->returnValue('a callback'));
        $each = new HTML_QuickForm2_Rule_Each($mockContainer, 'an error', $rule);
        $this->assertStringContainsString('["foo","bar"]', $each->getJavascript());
    }
}
?>