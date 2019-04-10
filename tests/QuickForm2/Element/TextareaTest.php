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
 * Unit test for HTML_QuickForm2_Element_Textarea class
 */
class HTML_QuickForm2_Element_TextareaTest extends PHPUnit_Framework_TestCase
{
    public function testTextareaIsEmptyByDefault()
    {
        $area = new HTML_QuickForm2_Element_Textarea();
        $this->assertNull($area->getValue());
        $this->assertRegExp('!\\s*<textarea[^>]*></textarea>\\s*!', $area->__toString());
    }

    public function testSetAndGetValue()
    {
        $area = new HTML_QuickForm2_Element_Textarea();
        $this->assertSame($area, $area->setValue('Some string'));
        $this->assertEquals('Some string', $area->getValue());
        $this->assertRegExp('!\\s*<textarea[^>]*>Some string</textarea>\\s*!', $area->__toString());

        $area->setAttribute('disabled');
        $this->assertNull($area->getValue());
        $this->assertRegExp('!\\s*<textarea[^>]*>Some string</textarea>\\s*!', $area->__toString());
    }

    public function testValueOutputIsEscaped()
    {
        $area = new HTML_QuickForm2_Element_Textarea();
        $area->setValue('<foo>');
        $this->assertNotRegExp('/<foo>/', $area->__toString());

        $area->toggleFrozen(true);
        $this->assertNotRegExp('/<foo>/', $area->__toString());
    }

    public function testFrozenHtmlGeneration()
    {
        $area = new HTML_QuickForm2_Element_Textarea('freezeMe');
        $area->setValue('Some string');

        $area->toggleFrozen(true);
        $this->assertRegExp('/Some string/', $area->__toString());
        $this->assertRegExp('!<input[^>]*type="hidden"[^>]*/>!', $area->__toString());

        $area->persistentFreeze(false);
        $this->assertRegExp('/Some string/', $area->__toString());
        $this->assertNotRegExp('!<input[^>]*type="hidden"[^>]*/>!', $area->__toString());

        $area->persistentFreeze(true);
        $area->setAttribute('disabled');
        $this->assertRegExp('/Some string/', $area->__toString());
        $this->assertNotRegExp('!<input[^>]*type="hidden"[^>]*/>!', $area->__toString());
    }
}
?>
