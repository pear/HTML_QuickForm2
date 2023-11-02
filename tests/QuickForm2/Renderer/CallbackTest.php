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
 * Unit test for HTML_QuickForm2_Renderer_Callback class
 */
class HTML_QuickForm2_Renderer_CallbackTest extends TestCase
{
    public static function _renderInputText($renderer, $element)
    {
        return 'InputText;id='.$element->getId().',html='.$element;
    }

    public static function _renderInput($renderer, $element)
    {
        return 'Input;id='.$element->getId().',html='.$element;
    }

    public static function _renderTestRenderElement($renderer, $element)
    {
        return 'testRenderElement;id='.$element->getId().',html='.$element;
    }

    public static function _renderTestRenderRequiredElement($renderer, $element)
    {
        if ($element->isRequired()) {
            return 'required!'.$element.'<em>*</em>';
        } else {
            return (string)$element;
        }
    }

    public static function _renderTestElementWithError($renderer, $element)
    {
        if (($error = $element->getError()) && $error &&
            !$renderer->getOption('group_errors')) {
            return 'an error!'.$element.$error;
        } else {
            return (string)$element;
        }
    }

    public static function _renderTestSingleLabel($renderer, $element)
    {
        if (($label = $element->getLabel()) && !empty($label)) {
            return 'A label: '.$element.$element->getLabel();
        }
        return (string)$element;
    }

    public static function _renderTestMultipleLabels($renderer, $element)
    {
        if (($label = $element->getLabel()) && !empty($label)) {
            $html = "";
            if (!empty($label[0])) {
                $html .= "First label: ".$label[0].$element;
            }
            if (!empty($label[1])) {
                $html .= "Second label: ".$label[1];
            }
            if (!empty($label['foo'])) {
                $html .= "Named label: ".$label['foo'];
            }
            return $html;
        }
        return (string)$element;
    }

    public function testRenderElementUsingMostAppropriateCallback()
    {
        $element = HTML_QuickForm2_Factory::createElement(
            'text', 'foo', ['id' => 'testRenderElement']
        );

        $class = get_class($this);
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForClass(
                'HTML_QuickForm2_Element_InputText', [$class, '_renderInputText']
            )->setCallbackForClass(
                'HTML_QuickForm2_Element_Input', [$class, '_renderInput']
            )->setCallbackForId(
                'testRenderElement', [$class, '_renderTestRenderElement']
            );

        $this->assertEquals(
            'testRenderElement;id=' . $element->getId() . ',html=' . $element->__toString(),
            $element->render($renderer->reset())->__toString()
        );

        $renderer->setCallbackForId('testRenderElement', null);
        $this->assertEquals(
            'InputText;id=' . $element->getId() . ',html=' . $element->__toString(),
            $element->render($renderer->reset())->__toString()
        );

        $renderer->setCallbackForClass('HTML_QuickForm2_Element_InputText', null);
        $this->assertEquals(
            'Input;id=' . $element->getId() . ',html=' . $element->__toString(),
            $element->render($renderer->reset())->__toString()
        );
    }

    public function testRenderRequiredElement()
    {
        $element = HTML_QuickForm2_Factory::createElement(
            'text', 'foo', ['id' => 'testRenderRequiredElement']
        );

        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForId(
                'testRenderRequiredElement',
                [get_class($this), '_renderTestRenderRequiredElement']
            );
        $this->assertEquals(
            $element->__toString(),
            $element->render($renderer->reset())->__toString()
        );

        $element->addRule('required', 'error message');
        $this->assertEquals(
            'required!' . $element->__toString() . '<em>*</em>',
            $element->render($renderer->reset())->__toString()
        );
    }

    public function testRenderElementWithValidationError()
    {
        $element = HTML_QuickForm2_Factory::createElement(
            'text', 'foo', ['id' => 'testElementWithError']
        );
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForId(
                'testElementWithError',
                [get_class($this), '_renderTestElementWithError']
            );

        $this->assertEquals(
            $element->__toString(),
            $element->render($renderer->reset())->__toString()
        );

        $element->setError('some message');
        $this->assertEquals(
            'an error!' . $element->__toString() . 'some message',
            $element->render(
                $renderer->reset()->setOption('group_errors', false)
            )->__toString()
        );

        $this->assertEquals(
            $element->__toString(),
            $element->render(
                $renderer->reset()->setOption('group_errors', true)
            )->__toString()
        );
    }

    public function testRenderElementWithSingleLabel()
    {
        $element = HTML_QuickForm2_Factory::createElement(
            'text', 'foo', ['id' => 'testSingleLabel']
        );
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForId(
                'testSingleLabel',
                [get_class($this), '_renderTestSingleLabel']
            );

        $this->assertEquals(
            $element->__toString(),
            $element->render($renderer->reset())->__toString()
        );
        $element->setLabel('the label!');
        $this->assertEquals(
            'A label: ' . $element->__toString() . 'the label!',
            $element->render($renderer->reset())->__toString()
        );
    }

    public function testRenderElementWithMultipleLabels()
    {
        $element = HTML_QuickForm2_Factory::createElement(
            'text', 'foo', ['id' => 'testMultipleLabels']
        )->setLabel(['first', 'second']);
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForId(
                'testMultipleLabels',
                [get_class($this), '_renderTestMultipleLabels']
            );

        $this->assertEquals(
            'First label: first' . $element->__toString() . 'Second label: second',
            $element->render($renderer->reset())->__toString()
        );

        $element->setLabel(['another', 'foo' => 'foo']);
        $this->assertEquals(
            'First label: another' . $element->__toString() . 'Named label: foo',
            $element->render($renderer->reset())->__toString()
        );
    }

    public function testRenderRequiredNote()
    {
        $form = new HTML_QuickForm2('reqnote');
        $element = $form->addText('testReqnote');

        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setOption('required_note', 'This is requi-i-i-ired!');

        $this->assertStringNotContainsString('<div class="reqnote">', $form->render($renderer)->__toString());

        $element->addRule('required', 'error message');
        $this->assertStringContainsString(
            '<div class="reqnote">This is requi-i-i-ired!</div>',
            $form->render($renderer)->__toString()
        );
    }

    public function testRenderGroupedErrors()
    {
        $form     = new HTML_QuickForm2('groupedErrors');
        $element  = $form->addText('testGroupedErrors')->setError('Some error');
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setOption([
                'group_errors'  => true,
                'errors_prefix' => 'Your errors:',
                'errors_suffix' => ''
            ]);

        $this->assertStringContainsString(
            '<div class="errors"><p>Your errors:</p><ul><li>Some error</li></ul></div>',
            $form->render($renderer)->__toString()
        );
    }

    public function testRenderGroupedHiddens()
    {
        $form     = new HTML_QuickForm2('groupedHiddens');
        $hidden1  = $form->addHidden('hidden1');
        $hidden2  = $form->addHidden('hidden2');
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setOption('group_hiddens', false);

        $html = $form->render($renderer)->__toString();
        $this->assertStringContainsString('<div style="display: none;">' . $hidden1->__toString() . '</div>', $html);
        $this->assertStringContainsString('<div style="display: none;">' . $hidden2->__toString() . '</div>', $html);

        $renderer->setOption('group_hiddens', true);
        $html = $form->render($renderer)->__toString();

        // why not ?
        // $this->assertNotContains('<div style="display: none;">', $html);

        $this->assertStringContainsString($hidden1->__toString() . $hidden2->__toString(), $html);
    }

    public static function _renderGroupInputText($renderer, $element)
    {
        return 'IgnoreThis;html='.$element;
    }

    public static function _renderGroupInput($renderer, $element)
    {
        return 'GroupedInput;id='.$element->getId().',html='.$element;
    }

    public static function _renderGroup($renderer, $element)
    {
        return 'GroupedElement;id='.$element->getId().',html='.$element;
    }

    public static function _renderGroupedElement($renderer, $element)
    {
        return 'testRenderGroupedElement;id='.$element->getId().',html='.$element;
    }

    public function testRenderGroupedElementUsingMostAppropriateTemplate()
    {
        $group   = HTML_QuickForm2_Factory::createElement('group', 'foo', ['id' => 'testRenderGroup']);
        $element = $group->addElement('text', 'bar', ['id' => 'testRenderGroupedElement']);

        $class= get_class($this);
        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForClass(
                'HTML_QuickForm2_Element_InputText',
                [$class, '_renderGroupInputText']
            )->setElementCallbackForGroupClass(
                'HTML_QuickForm2_Container_Group', 'HTML_QuickForm2_Element_Input',
                [$class, '_renderGroupInput']
            )->setElementCallbackForGroupId(
                'testRenderGroup', 'HTML_QuickForm2_Element',
                [$class, '_renderGroup']
            )->setCallbackForId(
                'testRenderGroupedElement',
                [$class, '_renderGroupedElement']
            );

        $this->assertStringContainsString(
            'testRenderGroupedElement;id=' . $element->getId() . ',html=' . $element->__toString(),
            $group->render($renderer->reset())->__toString()
        );

        $renderer->setCallbackForId('testRenderGroupedElement', null);
        $this->assertStringContainsString(
            'GroupedElement;id=' . $element->getId() . ',html=' . $element->__toString(),
            $group->render($renderer->reset())->__toString()
        );

        $renderer->setElementCallbackForGroupId('testRenderGroup', 'HTML_QuickForm2_Element', null);
        $this->assertStringContainsString(
            'GroupedInput;id=' . $element->getId() . ',html=' . $element->__toString(),
            $group->render($renderer->reset())->__toString()
        );

        $renderer->setElementCallbackForGroupClass('HTML_QuickForm2_Container_Group', 'HTML_QuickForm2_Element_Input', null);
        $this->assertStringNotContainsString(
            'IgnoreThis', $group->render($renderer->reset())->__toString()
        );
    }

    public static function _renderTestSeparators($renderer, $group)
    {
        $separator = $group->getSeparator();
        $elements  = array_pop($renderer->html);
        if (!is_array($separator)) {
            $content = implode((string)$separator, $elements);
        } else {
            $content    = '';
            $cSeparator = count($separator);
            for ($i = 0, $count = count($elements); $i < $count; $i++) {
                $content .= (0 == $i? '': $separator[($i - 1) % $cSeparator]) .
                            $elements[$i];
            }
        }
        return $content;
    }

    public static function _renderTestSeparators2($renderer, $element)
    {
        return '<foo>'.$element.'</foo>';
    }

    public function testRenderGroupedElementsWithSeparators()
    {
        $group = HTML_QuickForm2_Factory::createElement('group', 'foo', ['id' => 'testSeparators']);
        $element1 = $group->addElement('text', 'bar');
        $element2 = $group->addElement('text', 'baz');
        $element3 = $group->addElement('text', 'quux');

        $renderer = HTML_Quickform2_Renderer::factory('callback')
            ->setCallbackForId('testSeparators', [get_class($this), '_renderTestSeparators'])
            ->setElementCallbackForGroupId(
                'testSeparators', 'HTML_QuickForm2_Element_InputText', [get_class($this), '_renderTestSeparators2']
            );

        $this->assertEquals(
            '<foo>' . $element1 . '</foo><foo>' . $element2 . '</foo><foo>' . $element3 . '</foo>',
            $group->render($renderer->reset())->__toString()
        );

        $group->setSeparator('&nbsp;');
        $this->assertEquals(
            '<foo>' . $element1 . '</foo>&nbsp;<foo>' . $element2 . '</foo>&nbsp;<foo>' . $element3 . '</foo>',
            $group->render($renderer->reset())->__toString()
        );

        $group->setSeparator(['<br />', '&nbsp;']);
        $this->assertEquals(
            '<foo>' . $element1 . '</foo><br /><foo>' . $element2 . '</foo>&nbsp;<foo>' . $element3 . '</foo>',
            $group->render($renderer->reset())->__toString()
        );
    }
}
?>
