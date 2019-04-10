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
 * Unit test for HTML_QuickForm2_Renderer_Array class
 */
class HTML_QuickForm2_Renderer_ArrayTest extends PHPUnit_Framework_TestCase
{
    private function _assertHasKeys($array, $keys)
    {
        sort($keys);
        $realKeys = array_keys($array);
        sort($realKeys);
        $this->assertEquals($keys, $realKeys);
    }

    public function testRenderElementSeparately()
    {
        $element  = HTML_QuickForm2_Factory::createElement(
            'text', 'foo', array('id' => 'arrayRenderElement')
        );
        $renderer = HTML_QuickForm2_Renderer::factory('array');

        $array = $element->render($renderer)->toArray();

        $this->_assertHasKeys(
            $array,
            array('id', 'html', 'value', 'type', 'required', 'frozen')
        );

        $element->setLabel('Foo label:');
        $element->setError('an error!');
        $array = $element->render($renderer->reset())->toArray();
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('error', $array);
    }

    public function testRenderHidden()
    {
        $hidden = HTML_QuickForm2_Factory::createElement(
            'hidden', 'bar', array('id' => 'arrayRenderHidden')
        );
        $renderer = HTML_QuickForm2_Renderer::factory('array')
            ->setOption('group_hiddens', false);

        $array = $hidden->render($renderer)->toArray();
        $this->_assertHasKeys(
            $array,
            array('id', 'html', 'value', 'type', 'required', 'frozen')
        );

        $array = $hidden->render(
                    $renderer->setOption('group_hiddens', true)->reset()
                 )->toArray();
        $this->assertEquals(array('hidden'), array_keys($array));
        $this->assertEquals($hidden->__toString(), $array['hidden'][0]);
    }

    public function testRenderContainerSeparately()
    {
        $fieldset = HTML_QuickForm2_Factory::createElement(
            'fieldset', 'baz', array('id' => 'arrayRenderContainer')
        );
        $renderer = HTML_QuickForm2_Renderer::factory('array');

        $array = $fieldset->render($renderer)->toArray();
        $this->_assertHasKeys(
            $array,
            array('id', 'type', 'required', 'frozen', 'elements', 'attributes')
        );
        $this->assertEquals(array(), $array['elements']);

        $fieldset->setLabel('a label');
        $fieldset->setError('an error!');
        $text = $fieldset->addText('insideFieldset');
        $array = $fieldset->render($renderer->reset())->toArray();
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('error', $array);
        $this->assertEquals($array['elements'][0]['html'], $text->__toString());
    }

    public function testRenderNestedContainers()
    {
        $fieldset = HTML_QuickForm2_Factory::createElement(
            'fieldset', 'quux', array('id' => 'arrayNestedContainers')
        );
        $group = $fieldset->addElement('group', 'xyzzy', array('id' => 'arrayInnerContainer'))
                    ->setSeparator('<br />');
        $text  = $group->addElement('text', 'foobar', array('id' => 'arrayInnermost'));
        $renderer = HTML_QuickForm2_Renderer::factory('array');

        $array   = $fieldset->render($renderer)->toArray();
        $elArray = $text->render($renderer->reset())->toArray();
        $this->assertArrayHasKey('elements', $array['elements'][0]);
        $this->assertArrayHasKey('separator', $array['elements'][0]);
        $this->assertEquals($elArray, $array['elements'][0]['elements'][0]);
    }

    public function testRenderGroupedErrors()
    {
        $form     = new HTML_QuickForm2('arrayGroupedErrors');
        $element  = $form->addText('testArrayGroupedErrors')->setError('Some error');
        $renderer = HTML_QuickForm2_Renderer::factory('array')
                        ->setOption('group_errors', false);

        $this->assertArrayNotHasKey('errors', $form->render($renderer)->toArray());

        $array = $form->render($renderer->setOption('group_errors', true))->toArray();
        $this->assertArrayNotHasKey('error', $array['elements'][0]);
        $this->assertContains('Some error', $array['errors']);
    }

    public function testRenderRequiredNote()
    {
        $form = new HTML_QuickForm2('arrayReqnote');
        $element = $form->addText('testArrayReqnote');

        $renderer = HTML_Quickform2_Renderer::factory('array')
            ->setOption('required_note', 'This is requi-i-i-ired!');

        $this->assertArrayNotHasKey('required_note', $form->render($renderer)->toArray());

        $element->addRule('required', 'error message');
        $array = $form->render($renderer)->toArray();
        $this->assertEquals('This is requi-i-i-ired!', $array['required_note']);
    }

    public function testRenderWithStyle()
    {
        $form = new HTML_QuickForm2('arrayStyle');
        $text1 = $form->addText('foo', array('id' => 'testArrayWithStyle'));
        $text2 = $form->addText('bar', array('id' => 'testArrayWithoutStyle'));
        $renderer = HTML_Quickform2_Renderer::factory('array')
            ->setStyleForId('testArrayWithStyle', 'weird');

        $array = $form->render($renderer)->toArray();
        $this->assertEquals('weird', $array['elements'][0]['style']);
        $this->assertArrayNotHasKey('style', $array['elements'][1]);
    }

    public function testRenderStaticLabels()
    {
        $element  = HTML_QuickForm2_Factory::createElement('text', 'static')
                        ->setLabel(array('a label', 'another label', 'foo' => 'named label'));
        $renderer = HTML_QuickForm2_Renderer::factory('array')
                        ->setOption('static_labels', false);

        $array = $element->render($renderer)->toArray();
        $this->assertInternalType('array', $array['label']);

        $array = $element->render(
                    $renderer->setOption('static_labels', true)->reset()
                 )->toArray();
        $this->assertEquals('a label', $array['label']);
        $this->assertEquals('another label', $array['label_2']);
        $this->assertEquals('named label', $array['label_foo']);
    }
}
?>
