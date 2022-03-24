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

// pear-package-only require_once __DIR__ . '/../stubs/FakeRenderer.php';
// pear-package-only require_once __DIR__ . '/../stubs/FakeRendererHelloPlugin.php';
// pear-package-only require_once __DIR__ . '/../stubs/FakeRendererGoodbyePlugin.php';
// pear-package-only require_once __DIR__ . '/../stubs/FakeRendererAnotherHelloPlugin.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Renderer class
 */
class HTML_QuickForm2_RendererTest extends TestCase
{
    public function testRegisterRenderer()
    {
        $type = 'fake' . mt_rand();
        HTML_Quickform2_Renderer::register($type, FakeRenderer::class);

        $renderer = HTML_Quickform2_Renderer::factory($type);
        $this->assertInstanceOf('HTML_QuickForm2_Renderer', $renderer);
    }

    public function testRegisterPlugin()
    {
        $type = 'fake' . mt_rand();
        HTML_QuickForm2_Renderer::register($type, FakeRenderer::class);
        HTML_QuickForm2_Renderer::registerPlugin($type, FakeRendererHelloPlugin::class);

        $renderer = HTML_Quickform2_Renderer::factory($type);
        $this->assertTrue($renderer->methodExists('renderElement'));
        $this->assertTrue($renderer->methodExists('sayHello'));
        $this->assertFalse($renderer->methodExists('sayGoodbye'));
        HTML_QuickForm2_Renderer::registerPlugin($type, FakeRendererGoodbyePlugin::class);
        $this->assertTrue($renderer->methodExists('sayGoodbye'));

        $this->assertEquals('Hello, fake!', $renderer->sayHello());
        $this->assertEquals('Goodbye, fake!', $renderer->sayGoodbye());
    }

    public function testRegisterPluginOnlyOnce()
    {
        $type = 'fake' . mt_rand();
        HTML_QuickForm2_Renderer::register($type, FakeRenderer::class);
        HTML_QuickForm2_Renderer::registerPlugin($type, FakeRendererHelloPlugin::class);

        try {
            HTML_QuickForm2_Renderer::registerPlugin($type, FakeRendererHelloPlugin::class);
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/already registered/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testDuplicateMethodNamesDisallowed()
    {
        $type = 'fake' . mt_rand();
        HTML_QuickForm2_Renderer::register($type, FakeRenderer::class);
        HTML_QuickForm2_Renderer::registerPlugin($type, FakeRendererHelloPlugin::class);
        HTML_QuickForm2_Renderer::registerPlugin($type, FakeRendererAnotherHelloPlugin::class);

        try {
            $renderer = HTML_Quickform2_Renderer::factory($type);
            $renderer->sayHello();
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertMatchesRegularExpression('/^Duplicate method name/', $e->getMessage());
            return;
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }
}
?>
