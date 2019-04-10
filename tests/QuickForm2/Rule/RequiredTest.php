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
require_once dirname(dirname(dirname(__FILE__))) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Rule_Required class
 */
class HTML_QuickForm2_Rule_RequiredTest extends PHPUnit_Framework_TestCase
{
    protected $nodeAbstractMethods = array(
        'updateValue', 'getId', 'getName', 'getType', 'getRawValue', 'setId',
        'setName', 'setValue', '__toString', 'getJavascriptValue',
        'getJavascriptTriggers', 'render'
    );

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
                ->setMethods(array('validateOwner'))
                ->setConstructorArgs(array($mockNode, 'some message'))
                ->getMock()
        );
        try {
            $rule->and_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Cannot add a "required" rule/', $e->getMessage());
            try {
                $rule->or_(new HTML_QuickForm2_Rule_Required($mockNode, 'element is required'));
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertRegexp('/Cannot add a "required" rule/', $e->getMessage());
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
                    ->setMethods(array('validateOwner'))
                    ->setConstructorArgs(array($mockNode, 'some message'))
                    ->getMock()
            );
        } catch (HTML_QuickForm2_Exception $e) {
            $this->assertRegexp('/Cannot add a rule to "required" rule/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_Exception was not thrown');
    }

   /**
    * @link http://pear.php.net/bugs/18133
    * @expectedException HTML_QuickForm2_InvalidArgumentException
    */
    public function testCannotHaveEmptyMessage()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        $required = new HTML_QuickForm2_Rule_Required($mockNode);
    }
}
?>
