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
 * An "implementation" of renderer, to be able to create an instance
 */
class HTML_QuickForm2_FakeRenderer extends HTML_QuickForm2_Renderer
{
    public $name = 'fake';

    public function renderElement(HTML_QuickForm2_Node $element) {}
    public function renderHidden(HTML_QuickForm2_Node $element) {}
    public function startForm(HTML_QuickForm2_Node $form) {}
    public function finishForm(HTML_QuickForm2_Node $form) {}
    public function startContainer(HTML_QuickForm2_Node $container) {}
    public function finishContainer(HTML_QuickForm2_Node $container) {}
    public function startGroup(HTML_QuickForm2_Node $group) {}
    public function finishGroup(HTML_QuickForm2_Node $group) {}
    public function reset() {}
}

/**
 * Plugin for FakeRenderer
 */
class HTML_QuickForm2_FakeRenderer_HelloPlugin
    extends HTML_QuickForm2_Renderer_Plugin
{
    public function sayHello()
    {
        return sprintf('Hello, %s!', $this->renderer->name);
    }
}

/**
 * Another plugin for FakeRenderer
 */
class HTML_QuickForm2_FakeRenderer_GoodbyePlugin
    extends HTML_QuickForm2_Renderer_Plugin
{
    public function sayGoodbye()
    {
        return sprintf('Goodbye, %s!', $this->renderer->name);
    }
}

/**
 * Yet another plugin for FakeRenderer with duplicate method name
 */
class HTML_QuickForm2_FakeRenderer_AnotherHelloPlugin
    extends HTML_QuickForm2_Renderer_Plugin
{
    public function sayHello()
    {
        return 'Hello, world!';
    }
}

/**
 * Unit test for HTML_QuickForm2_Renderer class
 */
class HTML_QuickForm2_RendererTest extends PHPUnit_Framework_TestCase
{
    public function testRegisterRenderer()
    {
        $type = 'fake' . mt_rand();
        HTML_Quickform2_Renderer::register($type, 'HTML_QuickForm2_FakeRenderer');

        $renderer = HTML_Quickform2_Renderer::factory($type);
        $this->assertInstanceOf('HTML_QuickForm2_Renderer', $renderer);
    }

    public function testRegisterPlugin()
    {
        $type = 'fake' . mt_rand();
        HTML_QuickForm2_Renderer::register($type, 'HTML_QuickForm2_FakeRenderer');
        HTML_QuickForm2_Renderer::registerPlugin($type, 'HTML_QuickForm2_FakeRenderer_HelloPlugin');

        $renderer = HTML_Quickform2_Renderer::factory($type);
        $this->assertTrue($renderer->methodExists('renderElement'));
        $this->assertTrue($renderer->methodExists('sayHello'));
        $this->assertFalse($renderer->methodExists('sayGoodbye'));
        HTML_QuickForm2_Renderer::registerPlugin($type, 'HTML_QuickForm2_FakeRenderer_GoodbyePlugin');
        $this->assertTrue($renderer->methodExists('sayGoodbye'));

        $this->assertEquals('Hello, fake!', $renderer->sayHello());
        $this->assertEquals('Goodbye, fake!', $renderer->sayGoodbye());
    }

    public function testRegisterPluginOnlyOnce()
    {
        $type = 'fake' . mt_rand();
        HTML_QuickForm2_Renderer::register($type, 'HTML_QuickForm2_FakeRenderer');
        HTML_QuickForm2_Renderer::registerPlugin($type, 'HTML_QuickForm2_FakeRenderer_HelloPlugin');

        try {
            HTML_QuickForm2_Renderer::registerPlugin($type, 'HTML_QuickForm2_FakeRenderer_HelloPlugin');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/already registered/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testDuplicateMethodNamesDisallowed()
    {
        $type = 'fake' . mt_rand();
        HTML_QuickForm2_Renderer::register($type, 'HTML_QuickForm2_FakeRenderer');
        HTML_QuickForm2_Renderer::registerPlugin($type, 'HTML_QuickForm2_FakeRenderer_HelloPlugin');
        HTML_QuickForm2_Renderer::registerPlugin($type, 'HTML_QuickForm2_FakeRenderer_AnotherHelloPlugin');

        try {
            $renderer = HTML_Quickform2_Renderer::factory($type);
            $renderer->sayHello();
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertRegexp('/^Duplicate method name/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }
}
?>
