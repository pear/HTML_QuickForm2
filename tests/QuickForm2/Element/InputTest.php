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
require_once dirname(dirname(__DIR__)) . '/TestHelper.php';

// pear-package-only require_once __DIR__ . '/../../stubs/InputImpl.php';

use Yoast\PHPUnitPolyfills\TestCases\TestCase;

/**
 * Unit test for HTML_QuickForm2_Element_Input class
 */
class HTML_QuickForm2_Element_InputTest extends TestCase
{
    public function testTypeAttributeIsReadonly()
    {
        $obj = new InputImpl();
        try {
            $obj->removeAttribute('type');
        } catch (HTML_QuickForm2_InvalidArgumentException $e) {
            $this->assertEquals("Attribute 'type' is read-only", $e->getMessage());
            try {
                $obj->setAttribute('type', 'bogus');
            } catch (HTML_QuickForm2_InvalidArgumentException $e) {
                $this->assertEquals("Attribute 'type' is read-only", $e->getMessage());
                return;
            }
        }
        $this->fail('Expected HTML_QuickForm2_InvalidArgumentException was not thrown');
    }

    public function testCanSetAndGetValue()
    {
        $obj = new InputImpl();

        $this->assertSame($obj, $obj->setValue('foo'));
        $this->assertEquals($obj->getValue(), 'foo');

        $obj->setAttribute('value', 'bar');
        $this->assertEquals($obj->getValue(), 'bar');

        $obj->setAttribute('disabled');
        $this->assertNull($obj->getValue());
    }

    public function testSetNullValue()
    {
        $obj = new InputImpl();
        $obj->setValue(null);

        $this->assertEquals('', $obj->getValue());
    }

    public function testHtmlGeneration()
    {
        $obj = new InputImpl();
        $this->assertMatchesRegularExpression('!<input[^>]*type="concrete"[^>]*/>!', $obj->__toString());
    }

    public function testFrozenHtmlGeneration()
    {
        $obj = new InputImpl('test');
        $obj->setValue('bar');
        $obj->toggleFrozen(true);

        $obj->persistentFreeze(false);
        $this->assertDoesNotMatchRegularExpression('/[<>]/', $obj->__toString());
        $this->assertMatchesRegularExpression('/bar/', $obj->__toString());

        $obj->persistentFreeze(true);
        $this->assertMatchesRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $obj->__toString());

        $obj->setAttribute('disabled');
        $this->assertMatchesRegularExpression('/bar/', $obj->__toString());
        $this->assertDoesNotMatchRegularExpression('!<input[^>]*type="hidden"[^>]*/>!', $obj->__toString());
    }
}
?>