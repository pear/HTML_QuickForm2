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
require_once dirname(__DIR__) . '/TestHelper.php';

// pear-package-only require_once __DIR__ . '/../stubs/NodeImpl.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Node class,
 */
class HTML_QuickForm2_NodeTest extends TestCase
{
    public function testCanSetLabel()
    {
        $obj = new NodeImpl();
        $this->assertNull($obj->getLabel());

        $obj2 = new NodeImpl(null, null, ['label' => 'a label']);
        $this->assertEquals('a label', $obj2->getLabel());

        $this->assertSame($obj2, $obj2->setLabel('another label'));
        $this->assertEquals('another label', $obj2->getLabel());
    }

    public function testCanFreezeAndUnfreeze()
    {
        $obj = new NodeImpl();
        $this->assertFalse($obj->toggleFrozen(), 'Elements should NOT be frozen by default');

        $oldFrozen = $obj->toggleFrozen(true);
        $this->assertFalse($oldFrozen, 'toggleFrozen() should return previous frozen status');
        $this->assertTrue($obj->toggleFrozen());

        $this->assertTrue($obj->toggleFrozen(false), 'toggleFrozen() should return previous frozen status');
        $this->assertFalse($obj->toggleFrozen());
    }

    public function testCanSetPersistentFreeze()
    {
        $obj = new NodeImpl();
        $this->assertFalse($obj->persistentFreeze(), 'Frozen element\'s data should NOT persist by default');

        $oldPersistent = $obj->persistentFreeze(true);
        $this->assertFalse($oldPersistent, 'persistentFreeze() should return previous persistence status');
        $this->assertTrue($obj->persistentFreeze());

        $this->assertTrue($obj->persistentFreeze(false), 'persistentFreeze() should return previous persistence status');
        $this->assertFalse($obj->persistentFreeze());
    }

    public function testCanSetAndGetError()
    {
        $obj = new NodeImpl();
        $this->assertEquals('', $obj->getError(), 'Elements shouldn\'t have a error message by default');

        $this->assertSame($obj, $obj->setError('An error message'));
        $this->assertEquals('An error message', $obj->getError());
    }

    public function testValidate()
    {
        $valid = new NodeImpl();
        $ruleTrue = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$valid, 'A message'])
            ->getMock();
        $ruleTrue->expects($this->once())->method('validateOwner')
                 ->will($this->returnValue(true));
        $valid->addRule($ruleTrue);
        $this->assertTrue($valid->validate());
        $this->assertEquals('', $valid->getError());

        $invalid = new NodeImpl();
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
        $preError = new NodeImpl();
        $preError->setError('some message');
        $ruleIrrelevant = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$preError])
            ->getMock();
        $ruleIrrelevant->expects($this->never())->method('validateOwner');
        $preError->addRule($ruleIrrelevant);
        $this->assertFalse($preError->validate());

        $manyRules = new NodeImpl();
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
        $node    = new NodeImpl();
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
        $node = new NodeImpl();
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
        $nodeOne  = new NodeImpl();
        $nodeTwo  = new NodeImpl();
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
        $node = new NodeImpl();
        $this->assertFalse($node->isRequired());
    }

   /**
    * Disallow spaces in values of 'id' attributes
    *
    * @dataProvider invalidIdProvider
    * @link http://pear.php.net/bugs/17576
    */
    public function testRequest18683($id)
    {
        $this::expectException(\HTML_QuickForm2_InvalidArgumentException::class);
        $node = new NodeImpl();
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
