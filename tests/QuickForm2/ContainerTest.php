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
require_once dirname(__DIR__) . '/TestHelper.php';

// pear-package-only require_once __DIR__ . '/../stubs/ElementImpl.php';
// pear-package-only require_once __DIR__ . '/../stubs/ContainerImpl.php';
// pear-package-only require_once __DIR__ . '/../stubs/RuleRequest17576.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Container class
 */
class HTML_QuickForm2_ContainerTest extends TestCase
{
    public function testCanSetName()
    {
        $obj = new ContainerImpl();
        $this->assertNotNull($obj->getName(), 'Containers should always have \'name\' attribute');

        $obj = new ContainerImpl('foo');
        $this->assertEquals('foo', $obj->getName());

        $this->assertSame($obj, $obj->setName('bar'));
        $this->assertEquals('bar', $obj->getName());

        $obj->setAttribute('name', 'baz');
        $this->assertEquals('baz', $obj->getName());

    }


    public function testCanSetId()
    {
        $obj = new ContainerImpl(null, ['id' => 'manual']);
        $this->assertEquals('manual', $obj->getId());

        $this->assertSame($obj, $obj->setId('another'));
        $this->assertEquals('another', $obj->getId());

        $obj->setAttribute('id', 'yet-another');
        $this->assertEquals('yet-another', $obj->getId());
    }


    public function testAutogenerateId()
    {
        $obj = new ContainerImpl('somename');
        $this->assertNotEquals('', $obj->getId(), 'Should have an auto-generated \'id\' attribute');

        $obj2 = new ContainerImpl('somename');
        $this->assertNotEquals($obj2->getId(), $obj->getId(), 'Auto-generated \'id\' attributes should be unique');
    }


    public function testCanNotRemoveNameOrId()
    {
        $obj = new ContainerImpl('somename', [], ['id' => 'someid']);
        try {
            $obj->removeAttribute('name');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/Required attribute(.*)can not be removed/', $e->getMessage());
            try {
                $obj->removeAttribute('id');
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertMatchesRegularExpression('/Required attribute(.*)can not be removed/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }


    public function testAddAndGetElements()
    {
        $e1 = new ElementImpl('e1');
        $e2 = new ElementImpl('e2');
        $c1 = new ContainerImpl('c1');
        $c1->appendChild($e1);
        $c1->appendChild($e2);
        $this->assertEquals(2, count($c1), 'Element count is incorrect');
        $this->assertSame($e1, $c1->getElementById($e1->getId()));
        $this->assertSame($e2, $c1->getElementById($e2->getId()));
    }


    public function testNestedAddAndGetElements()
    {
        $e1 = new ElementImpl('a1');
        $e2 = new ElementImpl('a2');
        $c1 = new ContainerImpl('b1');
        $c1->appendChild($e1);
        $c1->appendChild($e2);

        $e3 = new ElementImpl('a3');
        $e4 = new ElementImpl('a4');
        $c2 = new ContainerImpl('b2');
        $c2->appendChild($e3);
        $c2->appendChild($e4);
        $c2->appendChild($c1);

        $this->assertEquals(3, count($c2), 'Element count is incorrect');
        $this->assertSame($e1, $c2->getElementById($e1->getId()));
        $this->assertSame($e2, $c2->getElementById($e2->getId()));
    }


    public function testCannotSetContainerOnSelf()
    {
        $e1 = new ElementImpl('d1');
        $e2 = new ElementImpl('d2');
        $c1 = new ContainerImpl('f1');
        $c1->appendChild($e1);
        $c1->appendChild($e2);
        try {
            $c1->appendChild($c1);
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertEquals('Cannot set an element or its child as its own container', $e->getMessage());
            $c2 = new ContainerImpl('f2');
            $c2->appendChild($c1);
            try {
                $c1->appendChild($c2);
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertEquals('Cannot set an element or its child as its own container', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }


    public function testAddSameElementMoreThanOnce()
    {
        $e1 = new ElementImpl('g1');
        $e2 = new ElementImpl('g2');
        $c1 = new ContainerImpl('h1');
        $c1->appendChild($e1);
        $c1->appendChild($e2);
        $c1->appendChild($e1);

        $this->assertEquals(2, count($c1), 'Element count is incorrect');
        $this->assertSame($e1, $c1->getElementById($e1->getId()));
        $this->assertSame($e2, $c1->getElementById($e2->getId()));
    }

    public function testMoveElement()
    {
        $e1 = new ElementImpl('move1');

        $c1 = new ContainerImpl('cmove1');
        $c2 = new ContainerImpl('cmove2');

        $c1->appendChild($e1);
        $this->assertSame($e1, $c1->getElementById($e1->getId()));
        $this->assertNull($c2->getElementById($e1->getId()), 'Element should not be found in container');

        $c2->appendChild($e1);
        $this->assertNull($c1->getElementById($e1->getId()), 'Element should be removed from container');
        $this->assertSame($e1, $c2->getElementById($e1->getId()));
    }

    public function testRemoveElement()
    {
        $e1 = new ElementImpl('i1');
        $e2 = new ElementImpl('i2');

        $c1 = new ContainerImpl('j1');

        $c1->appendChild($e1);
        $c1->appendChild($e2);

        $removed = $c1->removeChild($e1);
        $this->assertEquals(1, count($c1), 'Element count is incorrect');
        $this->assertNull($c1->getElementById($e1->getId()), 'Element should be removed from container');
        $this->assertSame($e1, $removed, 'removeChild() should return the old child');
    }

    public function testCannotRemoveNonExisting()
    {
        $e1 = new ElementImpl('remove1');
        $e2 = new ElementImpl('remove2');

        $c1 = new ContainerImpl('cremove1');
        $c2 = new ContainerImpl('cremove2');

        $c1->appendChild($c2);
        $c2->appendChild($e1);

        try {
            $c1->removeChild($e1);
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertMatchesRegularExpression('/Element(.*)was not found/', $e->getMessage());
            try {
                $c1->removeChild($e2);
            } catch (HTML_QuickForm2_NotFoundException $e) {
                $this->assertMatchesRegularExpression('/Element(.*)was not found/', $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testInsertBefore()
    {
        $e1 = new ElementImpl('k1');
        $e2 = new ElementImpl('k2');
        $e3 = new ElementImpl('k3');
        $e4 = new ElementImpl('k4');

        $c1 = new ContainerImpl('l1');
        $c2 = new ContainerImpl('l2');

        $c1->appendChild($e1);
        $c1->appendChild($e2);
        $c2->appendChild($e4);

        $e3Insert = $c1->insertBefore($e3, $e1);
        $c1->insertBefore($e4, $e1);
        $c1->insertBefore($e2, $e3);

        $this->assertSame($e3, $e3Insert, 'insertBefore() should return the inserted element');
        $this->assertNull($c2->getElementById($e4->getId()), 'Element should be removed from container');

        $test = [$e2, $e3, $e4, $e1];
        $i = 0;
        foreach ($c1 as $element) {
            $this->assertSame($test[$i++], $element, 'Elements are in the wrong order');
        }
    }

    public function testInsertBeforeNonExistingElement()
    {
        $e1 = new ElementImpl('m1');
        $e2 = new ElementImpl('m2');
        $e3 = new ElementImpl('m3');

        $c1 = new ContainerImpl('n1');
        $c1->appendChild($e1);
        $c2 = new ContainerImpl('n2');
        $c2->appendChild($c1);
        try {
            $c1->insertBefore($e2, $e3);
        } catch (HTML_QuickForm2_NotFoundException $e) {
            $this->assertEquals("Reference element with name '".$e3->getName()."' was not found", $e->getMessage());
            try {
                $c2->insertBefore($e2, $e1);
            } catch (HTML_QuickForm2_NotFoundException $e) {
                $this->assertEquals("Reference element with name '".$e1->getName()."' was not found", $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_NotFoundException was not thrown');
    }

    public function testGetElementsByName()
    {
        $e1 = new ElementImpl('foo');
        $e2 = new ElementImpl('bar');
        $e3 = new ElementImpl('foo');
        $e4 = new ElementImpl('baz');
        $e5 = new ElementImpl('foo');

        $c1 = new ContainerImpl('fooContainer1');
        $c2 = new ContainerImpl('fooContainer2');

        $c1->appendChild($e1);
        $c1->appendChild($e2);
        $c1->appendChild($e3);

        $c2->appendChild($e4);
        $c2->appendChild($e5);
        $c2->appendChild($c1);

        $this->assertEquals([$e1, $e3], $c1->getElementsByName('foo'));
        $this->assertEquals([$e5, $e1, $e3], $c2->getElementsByName('foo'));
    }

    public function testDuplicateIdHandling()
    {
        $e1 = new ElementImpl('dup1', ['id' => 'dup']);
        $e2 = new ElementImpl('dup2', ['id' => 'dup']);

        $c1 = new ContainerImpl('dupContainer1');
        $c2 = new ContainerImpl('dupContainer2');

        $c1->appendChild($e1);
        $c1->appendChild($e2);
        $this->assertEquals(2, count($c1), 'Element count is incorrect');
        $c1->removeChild($e1);
        $this->assertEquals(1, count($c1), 'Element count is incorrect');
        $this->assertSame($e2, $c1->getElementById('dup'));

        $c2->appendChild($e1);
        $c2->appendChild($e2);
        $c2->removeChild($e2);
        $this->assertEquals(1, count($c2), 'Element count is incorrect');
        $this->assertSame($e1, $c2->getElementById('dup'));
    }

    public function testFrozenStatusPropagates()
    {
        $cFreeze = new ContainerImpl('cFreeze');
        $elFreeze = $cFreeze->appendChild(new ElementImpl('elFreeze'));

        $cFreeze->toggleFrozen(true);
        $this->assertTrue($cFreeze->toggleFrozen(), 'Container should be frozen');
        $this->assertTrue($elFreeze->toggleFrozen(), 'Contained element should be frozen');

        $cFreeze->toggleFrozen(false);
        $this->assertFalse($cFreeze->toggleFrozen(), 'Container should not be frozen');
        $this->assertFalse($elFreeze->toggleFrozen(), 'Contained element should not be frozen');
    }

    public function testPersistentFreezePropagates()
    {
        $cPers = new ContainerImpl('cPersistent');
        $elPers = $cPers->appendChild(new ElementImpl('elPersistent'));

        $cPers->persistentFreeze(true);
        $this->assertTrue($cPers->persistentFreeze(), 'Container should have persistent freeze behaviour');
        $this->assertTrue($elPers->persistentFreeze(), 'Contained element should have persistent freeze behaviour');

        $cPers->persistentFreeze(false);
        $this->assertFalse($cPers->persistentFreeze(), 'Container should not have persistent freeze behaviour');
        $this->assertFalse($elPers->persistentFreeze(), 'Contained element should not have persistent freeze behaviour');
    }

    public function testGetValue()
    {
        $c1 = new ContainerImpl('hasValues');
        $this->assertNull($c1->getValue());

        $c2 = $c1->appendChild(new ContainerImpl('sub'));
        $this->assertNull($c1->getValue());

        $el1 = $c1->appendChild(new ElementImpl('foo[idx]'));
        $el2 = $c1->appendChild(new ElementImpl('bar'));
        $el3 = $c2->appendChild(new ElementImpl('baz'));
        $this->assertNull($c1->getValue());

        $el1->setValue('a value');
        $el2->setValue('other value');
        $el3->setValue('yet another value');
        $this->assertEquals([
            'foo' => ['idx' => 'a value'],
            'bar' => 'other value',
            'baz' => 'yet another value'
        ], $c1->getValue());
    }

    public function testGetRawValue()
    {
        $c = new ContainerImpl('filtered');

        $foo = $c->appendChild(new ElementImpl('foo'));
        $bar = $c->appendChild(new ElementImpl('bar'));

        $foo->setValue(' foo value ');
        $bar->setValue(' BAR VALUE ');
        $this->assertEquals([
            'foo' => ' foo value ',
            'bar' => ' BAR VALUE '
        ], $c->getRawValue());

        $c->addRecursiveFilter('trim');
        $bar->addFilter('strtolower');
        $this->assertEquals([
            'foo' => ' foo value ',
            'bar' => ' BAR VALUE '
        ], $c->getRawValue());

        $c->addFilter('count');
        $this->assertEquals([
            'foo' => ' foo value ',
            'bar' => ' BAR VALUE '
        ], $c->getRawValue());
    }

    public function testValidate()
    {
        $cValidate = new ContainerImpl('validate');
        $el1 = $cValidate->appendChild(new ElementImpl('foo'));
        $el2 = $cValidate->appendChild(new ElementImpl('bar'));

        $ruleTrue1 = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$cValidate, 'irrelevant message'])
            ->getMock();
        $ruleTrue1->expects($this->once())->method('validateOwner')
                  ->will($this->returnValue(true));
        $ruleFalse = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$el1, 'some error'])
            ->getMock();
        $ruleFalse->expects($this->once())->method('validateOwner')
                  ->will($this->returnValue(false));
        $ruleTrue2 = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$el2, 'irrelevant message'])
            ->getMock();
        $ruleTrue2->expects($this->once())->method('validateOwner')
                  ->will($this->returnValue(true));

        $cValidate->addRule($ruleTrue1);
        $el1->addRule($ruleFalse);
        $el2->addRule($ruleTrue2);
        $this->assertFalse($cValidate->validate());
        $this->assertEquals('', $cValidate->getError());
    }

   /**
    * Container rules should be called after element rules
    *
    * @link http://pear.php.net/bugs/17576
    */
    public function testRequest17576()
    {
        $container = new ContainerImpl('last');
        $element   = $container->appendChild(new ElementImpl('foo'));

        $ruleChange = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner'])
            ->setConstructorArgs([$element, 'a message'])
            ->getMock();
        $ruleChange->expects($this->exactly(2))->method('validateOwner')
                   ->will($this->onConsecutiveCalls(true, false));
        $element->addRule($ruleChange);

        $container->addRule(new RuleRequest17576(
            $container, 'a contained element is invalid'
        ));

        // first call
        $this->assertTrue($container->validate());
        // second call
        $this->assertFalse($container->validate());
        $this->assertEquals('a contained element is invalid', $container->getError());
    }

   /**
    * Checks that JS for container rules comes after js for rules on contained elements
    */
    public function testRequest17576Client()
    {
        $container = new ContainerImpl('aContainer');
        $element   = $container->appendChild(new ElementImpl('anElement'));

        $ruleContainer = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner', 'getJavascriptCallback'])
            ->setConstructorArgs([$container])
            ->getMock();
        $ruleContainer->expects($this->once())->method('getJavascriptCallback')
                      ->will($this->returnValue('containerCallback'));
        $ruleElement = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner', 'getJavascriptCallback'])
            ->setConstructorArgs([$element])
            ->getMock();
        $ruleElement->expects($this->once())->method('getJavascriptCallback')
                    ->will($this->returnValue('elementCallback'));

        $container->addRule($ruleContainer, HTML_QuickForm2_Rule::CLIENT);
        $element->addRule($ruleElement, HTML_QuickForm2_Rule::CLIENT);
        $this->assertMatchesRegularExpression(
            '/elementCallback.*containerCallback/s',
            $container->render(HTML_QuickForm2_Renderer::factory('default'))
                      ->getJavascriptBuilder()->getFormJavascript()
        );
    }

    public function testFrozenContainersHaveNoClientValidation()
    {
        $container = new ContainerImpl('aContainer');
        $ruleContainer = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(['validateOwner', 'getJavascriptCallback'])
            ->setConstructorArgs([$container])
            ->getMock();
        $ruleContainer->expects($this->never())->method('getJavascriptCallback');

        $container->addRule($ruleContainer, HTML_QuickForm2_Rule::CLIENT);
        $container->toggleFrozen(true);
        $this->assertEquals(
            '',
            $container->render(HTML_QuickForm2_Renderer::factory('default'))
                      ->getJavascriptBuilder()->getFormJavascript()
        );
    }

    public function testGetValueBrackets()
    {
        $c = new ContainerImpl('withBrackets');
        $el1 = $c->appendChild(new ElementImpl('foo[]'));
        $el2 = $c->appendChild(new ElementImpl('foo[]'));

        $el1->setValue('first');
        $el2->setValue('second');
        $this->assertEquals(['foo' => ['first', 'second']], $c->getValue());
    }
}
?>
