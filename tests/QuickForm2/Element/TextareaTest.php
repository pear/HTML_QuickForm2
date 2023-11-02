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
 * Unit test for HTML_QuickForm2_Element_Textarea class
 */
class HTML_QuickForm2_Element_TextareaTest extends TestCase
{
    public function testTextareaIsEmptyByDefault()
    {
        $area = new HTML_QuickForm2_Element_Textarea();
        $this->assertNull($area->getValue());
        $this->assertMatchesRegularExpression('!\\s*<textarea[^>]*></textarea>\\s*!', $area->__toString());
    }

    public function testSetAndGetValue()
    {
        $area = new HTML_QuickForm2_Element_Textarea();
        $this->assertSame($area, $area->setValue('Some string'));
        $this->assertEquals('Some string', $area->getValue());
        $this->assertMatchesRegularExpression('!\\s*<textarea[^>]*>Some string</textarea>\\s*!', $area->__toString());

        $area->setAttribute('disabled');
        $this->assertNull($area->getValue());
        $this->assertMatchesRegularExpression('!\\s*<textarea[^>]*>Some string</textarea>\\s*!', $area->__toString());
    }

    public function testValueOutputIsEscaped()
    {
        $area = new HTML_QuickForm2_Element_Textarea();
        $area->setValue('<foo>');
        $this->assertDoesNotMatchRegularExpression('/<foo>/', $area->__toString());

        $area->toggleFrozen(true);
        $this->assertDoesNotMatchRegularExpression('/<foo>/', $area->__toString());
    }

    public function testFrozenHtmlGeneration()
    {
        $area = new HTML_QuickForm2_Element_Textarea('freezeMe');
        $area->setValue('Some string');

        $area->toggleFrozen(true);
        $this->assertMatchesRegularExpression('/Some string/', $area->__toString());
        $this->assertMatchesRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $area->__toString());

        $area->persistentFreeze(false);
        $this->assertMatchesRegularExpression('/Some string/', $area->__toString());
        $this->assertDoesNotMatchRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $area->__toString());

        $area->persistentFreeze(true);
        $area->setAttribute('disabled');
        $this->assertMatchesRegularExpression('/Some string/', $area->__toString());
        $this->assertDoesNotMatchRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $area->__toString());
    }
}
?>
