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
 * We need to set the element's type
 */
class HTML_QuickForm2_Element_InputImpl extends HTML_QuickForm2_Element_Input
{
    public function __construct($name = null, $attributes = null, array $data = array())
    {
        parent::__construct($name, $attributes, $data);
        $this->attributes['type'] = 'concrete';
    }
}


/**
 * Unit test for HTML_QuickForm2_Element_Input class
 */
class HTML_QuickForm2_Element_InputTest extends PHPUnit_Framework_TestCase
{
    public function testTypeAttributeIsReadonly()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();
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
        $obj = new HTML_QuickForm2_Element_InputImpl();

        $this->assertSame($obj, $obj->setValue('foo'));
        $this->assertEquals($obj->getValue(), 'foo');

        $obj->setAttribute('value', 'bar');
        $this->assertEquals($obj->getValue(), 'bar');

        $obj->setAttribute('disabled');
        $this->assertNull($obj->getValue());
    }

    public function testSetNullValue()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();
        $obj->setValue(null);

        $this->assertEquals('', $obj->getValue());
    }

    public function testHtmlGeneration()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl();
        $this->assertRegExp('!<input[^>]*type="concrete"[^>]*/>!', $obj->__toString());
    }

    public function testFrozenHtmlGeneration()
    {
        $obj = new HTML_QuickForm2_Element_InputImpl('test');
        $obj->setValue('bar');
        $obj->toggleFrozen(true);

        $obj->persistentFreeze(false);
        $this->assertNotRegExp('/[<>]/', $obj->__toString());
        $this->assertRegExp('/bar/', $obj->__toString());

        $obj->persistentFreeze(true);
        $this->assertRegExp('!<input[^>]*type="hidden"[^>]*/>!', $obj->__toString());

        $obj->setAttribute('disabled');
        $this->assertRegExp('/bar/', $obj->__toString());
        $this->assertNotRegExp('!<input[^>]*type="hidden"[^>]*/>!', $obj->__toString());
    }
}
?>