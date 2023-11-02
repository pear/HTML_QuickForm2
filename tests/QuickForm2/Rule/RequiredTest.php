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
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Rule_Required class
 */
class HTML_QuickForm2_Rule_RequiredTest extends TestCase
{
    protected $nodeAbstractMethods = [
        'updateValue', 'getId', 'getName', 'getType', 'getRawValue', 'setId',
        'setName', 'setValue', '__toString', 'getJavascriptValue',
        'getJavascriptTriggers', 'render'
    ];

    public function testMakesElementRequired()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        $mockNode->addRule(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        $this->assertTrue($mockNode->isRequired());
    }

    public function testMustBeFirstInChain()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        $rule = $mockNode->addRule(
            $this->getMockBuilder('HTML_QuickForm2_Rule')
                ->setMethods(['validateOwner'])
                ->setConstructorArgs([$mockNode, 'some message'])
                ->getMock()
        );
        try {
            $rule->and_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Cannot add a "required" rule/', $e->getMessage());
            try {
                $rule->or_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertMatchesRegularExpression('/Cannot add a "required" rule/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testCannotAppendWithOr_()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        $required = new HTML_QuickForm2_Rule_Required($mockNode, 'element is required');
        try {
            $required->or_(
                $this->getMockBuilder('HTML_QuickForm2_Rule')
                    ->setMethods(['validateOwner'])
                    ->setConstructorArgs([$mockNode, 'some message'])
                    ->getMock()
            );
        } catch (HTML_QuickForm2_Exception $e) {
            $this->assertMatchesRegularExpression('/Cannot add a rule to "required" rule/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
    }

   /**
    * @link http://pear.php.net/bugs/18133
    */
    public function testCannotHaveEmptyMessage()
    {
        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);

        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        new HTML_QuickForm2_Rule_Required($mockNode);
    }
}
?>
