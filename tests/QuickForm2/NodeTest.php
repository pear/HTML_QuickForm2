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
require_once dirname(__DIR__) . '/TestHelper.php';

/**
 * A non-abstract subclass of Node
 *
 * We can't instantiate the class directly and thus need to "implement" its
 * abstract methods. And also make validate() public to be able to test.
 */
class HTML_QuickForm2_NodeImpl extends HTML_QuickForm2_Node
{
    public function getType() { return 'concrete'; }
    public function getRawValue() { return ''; }
    public function setValue($value) { return ''; }
    public function __toString() { return ''; }

    public function getName() { return ''; }
    public function setName($name) { }

    protected function updateValue() { }

    public function validate() { return parent::validate(); }

    public function getJavascriptValue($inContainer = false) { return ''; }
    public function getJavascriptTriggers() { return []; }

    public function render(HTML_QuickForm2_Renderer $renderer) { }
}

/**
 * Unit test for HTML_QuickForm2_Node class,
 */
class HTML_QuickForm2_NodeTest extends PHPUnit_Framework_TestCase
{
    public function testCanSetLabel()
    {
        $obj = new HTML_QuickForm2_NodeImpl();
        $this->assertNull($obj->getLabel());

        $obj2 = new HTML_QuickForm2_NodeImpl(null, null, ['label' => 'a label']);
        $this->assertEquals('a label', $obj2->getLabel());

        $this->assertSame($obj2, $obj2->setLabel('another label'));
        $this->assertEquals('another label', $obj2->getLabel());
    }

    public function testCanFreezeAndUnfreeze()
    {
        $obj = new HTML_QuickForm2_NodeImpl();
        $this->assertFalse($obj->toggleFrozen(), 'Elements should NOT be frozen by default');

        $oldFrozen = $obj->toggleFrozen(true);
        $this->assertFalse($oldFrozen, 'toggleFrozen() should return previous frozen status');
        $this->assertTrue($obj->toggleFrozen());

        $this->assertTrue($obj->toggleFrozen(false), 'toggleFrozen() should return previous frozen status');
        $this->assertFalse($obj->toggleFrozen());
    }

    public function testCanSetPersistentFreeze()
    {
        $obj = new HTML_QuickForm2_NodeImpl();
        $this->assertFalse($obj->persistentFreeze(), 'Frozen element\'s data should NOT persist by default');

        $oldPersistent = $obj->persistentFreeze(true);
        $this->assertFalse($oldPersistent, 'persistentFreeze() should return previous persistence status');
        $this->assertTrue($obj->persistentFreeze());

        $this->assertTrue($obj->persistentFreeze(false), 'persistentFreeze() should return previous persistence status');
        $this->assertFalse($obj->persistentFreeze());
    }

    public function testCanSetAndGetError()
    {
        $obj = new HTML_QuickForm2_NodeImpl();
        $this->assertEquals('', $obj->getError(), 'Elements shouldn\'t have a error message by default');

        $this->assertSame($obj, $obj->setError('An error message'));
        $this->assertEquals('An error message', $obj->getError());
    }

    public function testValidate()
    {
        $valid = new HTML_QuickForm2_NodeImpl();
        $ruleTrue = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$valid, 'A message'])
            ->getMock();
        $ruleTrue->expects($this->once())->method('validateOwner')
                 ->will($this->returnValue(true));
        $valid->addRule($ruleTrue);
        $this->assertTrue($valid->validate());
        $this->assertEquals('', $valid->getError());

        $invalid = new HTML_QuickForm2_NodeImpl();
        $ruleFalse = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$invalid, 'An error message'])
            ->getMock();
        $ruleFalse->expects($this->once())->method('validateOwner')
                  ->will($this->returnValue(false));
        $invalid->addRule($ruleFalse);
        $this->assertFalse($invalid->validate());
        $this->assertEquals('An error message', $invalid->getError());
    }

    public function testValidateUntilErrorMessage()
    {
        $preError = new HTML_QuickForm2_NodeImpl();
        $preError->setError('some message');
        $ruleIrrelevant = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$preError])
            ->getMock();
        $ruleIrrelevant->expects($this->never())->method('validateOwner');
        $preError->addRule($ruleIrrelevant);
        $this->assertFalse($preError->validate());

        $manyRules = new HTML_QuickForm2_NodeImpl();
        $ruleTrue = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$manyRules, 'irrelevant message'])
            ->getMock();
        $ruleTrue->expects($this->once())->method('validateOwner')
                 ->will($this->returnValue(true));
        $ruleFalseNoMessage = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$manyRules, ''])
            ->getMock();
        $ruleFalseNoMessage->expects($this->once())->method('validateOwner')
                           ->will($this->returnValue(false));
        $ruleFalseWithMessage = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$manyRules, 'some error'])
            ->getMock();
        $ruleFalseWithMessage->expects($this->once())->method('validateOwner')
                           ->will($this->returnValue(false));
        $ruleStillIrrelevant = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$manyRules, '...'])
            ->getMock();
        $ruleStillIrrelevant->expects($this->never())->method('validateOwner');
        $manyRules->addRule($ruleTrue);
        $manyRules->addRule($ruleFalseNoMessage);
        $manyRules->addRule($ruleFalseWithMessage);
        $manyRules->addRule($ruleStillIrrelevant);
        $this->assertFalse($manyRules->validate());
        $this->assertEquals('some error', $manyRules->getError());
    }

    public function testRemoveRule()
    {
        $node    = new HTML_QuickForm2_NodeImpl();
        $removed = $node->addRule(
            $this->getMockBuilder('HTML_QuickForm2_Rule')
                ->setMethods(['validateOwner'])
                ->setConstructorArgs([$node, '...'])
                ->getMock()
        );
        $removed->expects($this->never())->method('validateOwner');
        $node->removeRule($removed);
        $this->assertTrue($node->validate());
    }

    public function testAddRuleOnlyOnce()
    {
        $node = new HTML_QuickForm2_NodeImpl();
        $mock = $node->addRule(
            $this->getMockBuilder('HTML_QuickForm2_Rule')
                ->setMethods(['validateOwner'])
                ->setConstructorArgs([$node, '...'])
                ->getMock()
        );
        $mock->expects($this->once())->method('validateOwner')
             ->will($this->returnValue(false));

        $node->addRule($mock);
        $this->assertFalse($node->validate());
    }

    public function testRemoveRuleOnChangingOwner()
    {
        $nodeOne  = new HTML_QuickForm2_NodeImpl();
        $nodeTwo  = new HTML_QuickForm2_NodeImpl();
        $mockRule = $nodeOne->addRule(
            $this->getMockBuilder('HTML_QuickForm2_Rule')
                ->setMethods(['validateOwner'])
                ->setConstructorArgs([$nodeOne, '...'])
                ->getMock()
        );
        $mockRule->expects($this->once())->method('validateOwner')
                 ->will($this->returnValue(false));

        $nodeTwo->addRule($mockRule);
        $this->assertTrue($nodeOne->validate());
        $this->assertFalse($nodeTwo->validate());
    }

    public function testElementIsNotRequiredByDefault()
    {
        $node = new HTML_QuickForm2_NodeImpl();
        $this->assertFalse($node->isRequired());
    }

   /**
    * Disallow spaces in values of 'id' attributes
    *
    * @dataProvider invalidIdProvider
    * @expectedException HTML_QuickForm2_InvalidArgumentException
    * @link http://pear.php.net/bugs/17576
    */
    public function testRequest18683($id)
    {
        $node = new HTML_QuickForm2_NodeImpl();
        $node->setId($id);
    }

    public static function invalidIdProvider()
    {
        return [
            ["\x0C"],
            [" foo\n"],
            ["foo\rbar"],
            ['bar baz']
        ];
    }
}
?>
