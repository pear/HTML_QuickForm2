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
require_once dirname(dirname(__FILE__)) . '/TestHelper.php';

/**
 * Unit test for HTML_QuickForm2_Factory class
 */
class HTML_QuickForm2_FactoryTest extends PHPUnit_Framework_TestCase
{
    protected $phpError;
    protected $errorHandler;

    protected $nodeAbstractMethods = array(
        'updateValue', 'getId', 'getName', 'getType', 'getRawValue', 'setId',
        'setName', 'setValue', '__toString', 'getJavascriptValue',
        'getJavascriptTriggers', 'render'
    );

    protected function setUp()
    {
        $this->phpError     = null;
        $this->errorHandler = false;
    }

    protected function tearDown()
    {
        if ($this->errorHandler) {
            restore_error_handler();
        }
    }

    protected function setErrorHandler()
    {
        set_error_handler(array($this, 'handleError'));
        $this->errorHandler = true;
    }

    public function handleError($errno, $errstr)
    {
        $this->phpError = $errstr;
    }

    public function testNotRegisteredElement()
    {
        $this->assertFalse(HTML_QuickForm2_Factory::isElementRegistered('foo_' . mt_rand()));
    }

    public function testElementTypeCaseInsensitive()
    {
        HTML_QuickForm2_Factory::registerElement('fOo', 'Classname');
        $this->assertTrue(HTML_QuickForm2_Factory::isElementRegistered('foo'));
        $this->assertTrue(HTML_QuickForm2_Factory::isElementRegistered('FOO'));
    }

    public function testCreateNotRegisteredElement()
    {
        try {
            $el = HTML_QuickForm2_Factory::createElement('foo2');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Element type(.*)is not known/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testCreateElementNonExistingClass()
    {
        HTML_QuickForm2_Factory::registerElement('foo3', 'NonexistentClass');
        try {
            $this->setErrorHandler();
            $el = HTML_QuickForm2_Factory::createElement('foo3');
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertRegexp('/File(.*)was not found/', $e->getMessage());
            $this->assertContains('NonexistentClass.php', $this->phpError);
            return;
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testCreateElementNonExistingFile()
    {
        HTML_QuickForm2_Factory::registerElement('foo4', 'NonexistentClass', 'NonexistentFile.php');
        try {
            $this->setErrorHandler();
            $el = HTML_QuickForm2_Factory::createElement('foo4');
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertRegexp('/File(.*)was not found/', $e->getMessage());
            $this->assertContains('NonexistentFile.php', $this->phpError);
            return;
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testCreateElementInvalidFile()
    {
        HTML_QuickForm2_Factory::registerElement('foo5', 'NonexistentClass', dirname(__FILE__) . '/_files/InvalidFile.php');
        try {
            $el = HTML_QuickForm2_Factory::createElement('foo5');
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertRegexp('/Class(.*)was not found within file(.*)/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testCreateElementValid()
    {
        HTML_QuickForm2_Factory::registerElement('fakeelement', 'FakeElement', dirname(__FILE__) . '/_files/FakeElement.php');
        /* @var $el FakeElement */
        $el = HTML_QuickForm2_Factory::createElement('fakeelement',
                'fake', 'attributes', array('options' => '', 'label' => 'fake label'));
        $this->assertInstanceOf('FakeElement', $el);
        $this->assertEquals('fake', $el->name);
        $this->assertEquals(array('options' => '', 'label' => 'fake label'), $el->data);
        $this->assertEquals('attributes', $el->attributes);
    }

    public function testNotRegisteredRule()
    {
        $this->assertFalse(HTML_QuickForm2_Factory::isRuleRegistered('foo_' . mt_rand()));
    }

    public function testRuleNameCaseInsensitive()
    {
        HTML_QuickForm2_Factory::registerRule('fOo', 'RuleClassname');
        $this->assertTrue(HTML_QuickForm2_Factory::isRuleRegistered('FOO'));
        $this->assertTrue(HTML_QuickForm2_Factory::isRuleRegistered('foo'));
    }

    public function testCreateNotRegisteredRule()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        try {
            $rule = HTML_QuickForm2_Factory::createRule('foo2', $mockNode);
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/Rule(.*)is not known/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testCreateRuleNonExistingClass()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo3', 'NonexistentClass');
        try {
            $this->setErrorHandler();
            $rule = HTML_QuickForm2_Factory::createRule('foo3', $mockNode);
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertRegexp('/File(.*)was not found/', $e->getMessage());
            $this->assertContains('NonexistentClass.php', $this->phpError);
            return;
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testCreateRuleNonExistingFile()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo4', 'NonexistentClass', 'NonexistentFile.php');
        try {
            $this->setErrorHandler();
            $rule = HTML_QuickForm2_Factory::createRule('foo4', $mockNode);
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertRegexp('/File(.*)was not found/', $e->getMessage());
            $this->assertContains('NonexistentFile.php', $this->phpError);
            return;
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testCreateRuleInvalidFile()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        HTML_QuickForm2_Factory::registerRule('foo5', 'NonexistentClass', dirname(__FILE__) . '/_files/InvalidFile.php');
        try {
            $rule = HTML_QuickForm2_Factory::createRule('foo5', $mockNode);
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertRegexp('/Class(.*)was not found within file(.*)/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testCreateRuleValid()
    {
        $mockNode = $this->getMockBuilder('HTML_QuickForm2_Node')
            ->setMethods($this->nodeAbstractMethods)
            ->getMock();
        HTML_QuickForm2_Factory::registerRule(
            'fakerule', 'FakeRule', dirname(__FILE__) . '/_files/FakeRule.php'
        );
        /* @var $rule FakeRule */
        $rule = HTML_QuickForm2_Factory::createRule(
            'fakerule', $mockNode, 'An error message', 'Some options'
        );
        $this->assertInstanceOf('FakeRule', $rule);
        $this->assertSame($mockNode, $rule->owner);
        $this->assertEquals('An error message', $rule->getMessage());
        $this->assertEquals('Some options', $rule->getConfig());
    }
}
?>