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
 * Unit test for HTML_QuickForm2_Element_Group class
 */
class HTML_QuickForm2_Element_GroupTest extends PHPUnit_Framework_TestCase
{
    public function testNoRenameOnEmptyGroupName()
    {
        $g1 = new HTML_QuickForm2_Container_Group();

        $e = $g1->addText('e0');
        $this->assertEquals('e0', $e->getName());

        $e = $g1->addText('e1[e2]');
        $this->assertEquals('e1[e2]', $e->getName());
    }

    public function testGroupRename()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1[g4]');

        $e1 = $g1->addText('e1');
        $e2 = $g1->addText('e2[x]');
        $this->assertEquals('g1[g4][e1]', $e1->getName());
        $this->assertEquals('g1[g4][e2][x]', $e2->getName());

        $g1->setName('g2');
        $this->assertEquals('g2[e1]', $e1->getName());
        $this->assertEquals('g2[e2][x]', $e2->getName());

        $g1->setName('');
        $this->assertEquals('e1', $e1->getName());
        $this->assertEquals('e2[x]', $e2->getName());
    }

    public function testElementRename()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1');

        $e = $g1->addText('e0');
        $this->assertEquals('g1[e0]', $e->getName());

        $e = $g1->addText('e1[e2]');
        $this->assertEquals('g1[e1][e2]', $e->getName());

        $e = $g1->addText('e3[]');
        $this->assertEquals('g1[e3][]', $e->getName());

        $e = $g1->addText('[e4]');
        $this->assertEquals('g1[][e4]', $e->getName());

        $e = $g1->addText('');
        $this->assertEquals('g1[]', $e->getName());

        $e = $g1->addText();
        $this->assertEquals('g1[]', $e->getName());

        $e = $g1->addText('[]');
        $this->assertEquals('g1[][]', $e->getName());
    }

    public function testGroupedElementRename()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1');

        $e0 = $g1->addText('e0');
        $this->assertEquals('g1[e0]', $e0->getName());

        $g2 = new HTML_QuickForm2_Container_Group('g2');

        $e1 = $g2->addText('e1');
        $this->assertEquals('g2[e1]', $e1->getName());

        $g1->addElement($g2);
        $this->assertEquals('g1[g2]', $g2->getName());
        $this->assertEquals('g1[g2][e1]', $e1->getName());

        $g3 = new HTML_QuickForm2_Container_Group('g3');
        $g3->addElement($g1);
        $this->assertEquals('g3[g1]', $g1->getName());
        $this->assertEquals('g3[g1][e0]', $e0->getName());
        $this->assertEquals('g3[g1][g2]', $g2->getName());
        $this->assertEquals('g3[g1][g2][e1]', $e1->getName());

        $e2 = $g1->addText('e2');
        $this->assertEquals('g3[g1][e2]', $e2->getName());

        $e3 = $g1->addText('e3[x]');
        $this->assertEquals('g3[g1][e3][x]', $e3->getName());

        $e4 = $g1->addText('e4[]');
        $this->assertEquals('g3[g1][e4][]', $e4->getName());

        $e5 = $g1->addText('[e5]');
        $this->assertEquals('g3[g1][][e5]', $e5->getName());

        $e6 = $g1->addText('[e6]');
        $this->assertEquals('g3[g1][][e6]', $e6->getName());

        $e7 = $g1->addText('[]');
        $this->assertEquals('g3[g1][][]', $e7->getName());

        $e8 = $g1->addText('');
        $this->assertEquals('g3[g1][]', $e8->getName());

        $g4 = new HTML_QuickForm2_Container_Group('g4');
        $g4->addElement($g3);
        $this->assertEquals('g4[g3]', $g3->getName());
        $this->assertEquals('g4[g3][g1]', $g1->getName());
        $this->assertEquals('g4[g3][g1][e2]', $e2->getName());
        $this->assertEquals('g4[g3][g1][e3][x]', $e3->getName());
        $this->assertEquals('g4[g3][g1][e4][]', $e4->getName());
        $this->assertEquals('g4[g3][g1][][e5]', $e5->getName());
        $this->assertEquals('g4[g3][g1][][e6]', $e6->getName());
        $this->assertEquals('g4[g3][g1][][]', $e7->getName());
        $this->assertEquals('g4[g3][g1][]', $e8->getName());
    }

    public function testPrependGroupNameOnInsertBefore()
    {
        $foo = new HTML_QuickForm2_Container_Group('foo');
        $fooBar = $foo->insertBefore(
            HTML_QuickForm2_Factory::createElement('text', 'bar')
        );
        $this->assertEquals('foo[bar]', $fooBar->getName());

        $fooBaz = $foo->insertBefore(
            HTML_QuickForm2_Factory::createElement('text', 'baz'), $fooBar
        );
        $this->assertEquals('foo[baz]', $fooBaz->getName());
    }

    public function testRemoveGroupNameOnRemoveChild()
    {
        $foo  = new HTML_QuickForm2_Container_Group('foo');
        $bar  = $foo->addElement('group', 'bar');
        $baz  = $bar->addElement('text', 'baz');
        $quux = $bar->addElement('text', 'qu[ux]');
        $xy   = $bar->addElement('group');
        $zzy  = $xy->addElement('text', 'xyzzy');

        $this->assertEquals('foo[bar][baz]', $baz->getName());
        $this->assertEquals('foo[bar][qu][ux]', $quux->getName());
        $this->assertEquals('foo[bar][]', $xy->getName());
        $this->assertEquals('foo[bar][][xyzzy]', $zzy->getName());

        $foo->removeChild($bar);
        $this->assertEquals('bar[baz]', $baz->getName());
        $this->assertEquals('bar[qu][ux]', $quux->getName());
        $this->assertEquals('bar[][xyzzy]', $zzy->getName());

        $bar->removeChild($xy);
        $this->assertEquals('', $xy->getName());
        $this->assertEquals('xyzzy', $zzy->getName());

        $bar->removeChild($baz);
        $this->assertEquals('baz', $baz->getName());

        $bar->removeChild($quux);
        $this->assertEquals('qu[ux]', $quux->getName());
    }

    public function testRenameElementOnChangingGroups()
    {
        $g1 = new HTML_QuickForm2_Container_Group('g1');
        $g2 = new HTML_QuickForm2_Container_Group('g2');

        $e1 = $g1->addElement('text', 'e1');
        $this->assertEquals('g1[e1]', $e1->getName());

        $g2->addElement($e1);
        $this->assertEquals('g2[e1]', $e1->getName());
    }

    public function testSetValue()
    {
        $foo      = new HTML_QuickForm2_Container_Group('foo');
        $fooBar   = $foo->addText('bar');
        $fooBaz   = $foo->addText('ba[z]');
        $fooQuux  = $foo->addGroup('qu')->addText('ux');
        $fooNop   = $foo->addGroup();
        $fooXyzzy = $fooNop->addText('xyzzy');
        $fooYzzyx = $fooNop->addText('yzzyx');

        $foo->setValue(array(
            'bar'   => 'first value',
            'ba'    => array('z' => 'second value'),
            'qu'    => array('ux' => 'third value'),
                       array('xyzzy' => 'fourth value'),
                       array('yzzyx' => 'fifth value')
        ));
        $this->assertEquals('first value', $fooBar->getValue());
        $this->assertEquals('second value', $fooBaz->getValue());
        $this->assertEquals('third value', $fooQuux->getValue());
        $this->assertEquals('fourth value', $fooXyzzy->getValue());
        $this->assertEquals('fifth value', $fooYzzyx->getValue());

        $anon = new HTML_QuickForm2_Container_Group();
        $e1   = $anon->addText('e1');
        $e2   = $anon->addText('e2[i1]');
        $e3   = $anon->addGroup('g1')->addText('e3');
        $g2   = $anon->addGroup();
        $e4   = $g2->addText('e4');
        $e5   = $g2->addText('e5');
        $anon->setValue(array(
            'e1' => 'first value',
            'e2' => array('i1' => 'second value'),
            'g1' => array('e3' => 'third value'),
                    array('e4' => 'fourth value'),
                    array('e5' => 'fifth value')
        ));
        $this->assertEquals('first value', $e1->getValue());
        $this->assertEquals('second value', $e2->getValue());
        $this->assertEquals('third value', $e3->getValue());
        $this->assertEquals('fourth value', $e4->getValue());
        $this->assertEquals('fifth value', $e5->getValue());
    }

   /**
    * Should be possible to use setValue() fluently
    *
    * @link https://pear.php.net/bugs/bug.php?id=19307
    */
    public function testBug19307()
    {
        $foo = new HTML_QuickForm2_Container_Group('foo');
        $foo->addText('bar');

        $this->assertSame($foo, $foo->setValue(array('bar' => 'a value')));
    }

    public function testGetValue()
    {
        $value1    = array('foo' => 'foo value');
        $value2    = array('bar' => 'bar value', 'baz' => array('quux' => 'baz value'));
        $valueAnon = array('e1' => 'e1 value');
        $formValue = array('g1' => $value1, 'g2' => array('i2' => $value2)) + $valueAnon;

        $form = new HTML_QuickForm2('testGroupGetValue');
        $form->addDataSource(new HTML_QuickForm2_DataSource_Array($formValue));
        $g1 = $form->addElement('group', 'g1');
        $g1->addElement('text', 'foo');
        $g2 = $form->addElement('group', 'g2[i2]');
        $g2->addElement('text', 'bar');
        $g2->addElement('text', 'baz[quux]');
        $anon = $form->addElement('group');
        $anon->addElement('text', 'e1');

        $this->assertEquals($formValue, $form->getValue());
        $this->assertEquals($value1, $g1->getValue());
        $this->assertEquals($value2, $g2->getValue());
        $this->assertEquals($valueAnon, $anon->getValue());
    }

    public function testGetRawValue()
    {
        $unfiltered = array(
            'foo' => ' foo value ',
            'bar' => ' BAR VALUE '
        );

        $g = new HTML_QuickForm2_Container_Group('filtered');
        $foo = $g->addElement('text', 'foo');
        $bar = $g->addElement('text', 'bar');

        $g->setValue($unfiltered);
        $this->assertEquals($unfiltered, $g->getRawValue());

        $g->addRecursiveFilter('trim');
        $bar->addFilter('strtolower');
        $this->assertEquals($unfiltered, $g->getRawValue());

        $g->addFilter('count');
        $this->assertEquals($unfiltered, $g->getRawValue());
    }

   /**
    * Checks that JS for group rules comes after js for rules on contained elements
    */
    public function testRequest17576Client()
    {
        $group   = new HTML_QuickForm2_Container_Group('aGroup');
        $element = $group->addElement('text', 'anElement');

        $ruleGroup = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(array('validateOwner', 'getJavascriptCallback'))
            ->setConstructorArgs(array($group))
            ->getMock();
        $ruleGroup->expects($this->once())->method('getJavascriptCallback')
                  ->will($this->returnValue('groupCallback'));
        $ruleElement = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(array('validateOwner', 'getJavascriptCallback'))
            ->setConstructorArgs(array($element))
            ->getMock();
        $ruleElement->expects($this->once())->method('getJavascriptCallback')
                    ->will($this->returnValue('elementCallback'));

        $group->addRule($ruleGroup, HTML_QuickForm2_Rule::CLIENT);
        $element->addRule($ruleElement, HTML_QuickForm2_Rule::CLIENT);
        $this->assertRegexp(
            '/elementCallback.*groupCallback/s',
            $group->render(HTML_QuickForm2_Renderer::factory('default'))
                  ->getJavascriptBuilder()->getFormJavascript()
        );
    }

    public function testFrozenGroupsHaveNoClientValidation()
    {
        $group = new HTML_QuickForm2_Container_Group('aGroup');
        $ruleGroup = $this->getMockBuilder('HTML_QuickForm2_Rule')
            ->setMethods(array('validateOwner', 'getJavascriptCallback'))
            ->setConstructorArgs(array($group))
            ->getMock();
        $ruleGroup->expects($this->never())->method('getJavascriptCallback');

        $group->addRule($ruleGroup, HTML_QuickForm2_Rule::CLIENT);
        $group->toggleFrozen(true);
        $this->assertEquals(
            '',
            $group->render(HTML_QuickForm2_Renderer::factory('default'))
                  ->getJavascriptBuilder()->getFormJavascript()
        );
    }

   /**
    * removeElement() could break with a warning if element name contained special regexp characters
    *
    * @link http://pear.php.net/bugs/18182
    */
    public function testBug18182()
    {
        $group = new HTML_QuickForm2_Container_Group('foo[a-b]');
        $el1 = $group->addElement('text', 'bar');
        $this->assertEquals('foo[a-b][bar]', $el1->getName());

        $group->removeChild($el1);
        $this->assertEquals('bar', $el1->getName());

        $group->setName('foo[c/d]');
        $el2 = $group->addElement('text', 'baz');
        $this->assertEquals('foo[c/d][baz]', $el2->getName());

        $group->removeChild($el2);
        $this->assertEquals('baz', $el2->getName());
    }

   /**
    * Similar to bug #16806, properly set value for group of checkboxes having names like foo[]
    * @link http://pear.php.net/bugs/bug.php?id=16806
    */
    public function testCheckboxGroupSetValue()
    {
        $group = new HTML_QuickForm2_Container_Group('boxGroup');
        $group->addCheckbox('', array('value' => 'red'));
        $group->addCheckbox('', array('value' => 'green'));
        $group->addCheckbox('', array('value' => 'blue'));

        $group->setValue(array('red', 'blue'));
        $this->assertEquals(array('red', 'blue'), $group->getValue());
    }

    /**
     * Renaming groups with names like 'foo[foo]' or '1[1]' resulted in wrong names for grouped elements
     * @link http://pear.php.net/bugs/bug.php?id=19477
     */
    public function testBug19477()
    {
        $group = new HTML_QuickForm2_Container_Group('foo[foo]');
        $text  = $group->addElement(new HTML_QuickForm2_Element_InputText('bar'));
        $group->setName('test[foo][foo]');

        $this->assertEquals('test[foo][foo][bar]', $text->getName());
    }

    /**
     * Special case for a setValue() on a group of radiobuttons
     * @link http://pear.php.net/bugs/bug.php?id=20103
     */
    public function testRadioGroupSetValue()
    {
        $group = new HTML_QuickForm2_Container_Group();
        $group->addRadio('request20103', array('value' => 'first'));
        $group->addRadio('request20103', array('value' => 'second'));
        $group->addRadio('request20103', array('value' => 'third'));

        $group->setValue(array('request20103' => 'second'));
        $this->assertEquals(array('request20103' => 'second'), $group->getValue());

        $namedGroup = new HTML_QuickForm2_Container_Group('named');
        $namedGroup->addRadio('request20103[sub]', array('value' => 'first'));
        $namedGroup->addRadio('request20103[sub]', array('value' => 'second'));
        $namedGroup->addRadio('request20103[sub]', array('value' => 'third'));

        $namedGroup->setValue(array('request20103' => array('sub' => 'third')));
        $this->assertEquals(array('request20103' => array('sub' => 'third')), $namedGroup->getValue());
    }

    public function testScalarValueBug()
    {
        $group = new HTML_QuickForm2_Container_Group();
        $text  = $group->addText('foo[bar]');
        $group->setValue(array('foo' => 'foo value'));

        $this->assertEquals('', $text->getValue());
    }

    public function testSetValueUpdatesAllElements()
    {
        $group = new HTML_QuickForm2_Container_Group();
        $foo   = $group->addText('foo')->setValue('foo value');
        $bar   = $group->addText('bar')->setValue('bar value');

        $group->setValue(array('foo' => 'new foo value'));
        $this->assertEquals('new foo value', $foo->getValue());
        $this->assertEquals('', $bar->getValue());

        $group->setValue(null);
        $this->assertEquals('', $foo->getValue());
        $this->assertEquals('', $bar->getValue());
    }
}
?>
